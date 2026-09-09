<?php

declare(strict_types=1);

namespace App\Services\Contabilidad;

use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\LecturaMensual;
use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\ComprobanteDetalle;
use App\Models\Contabilidad\GestionContable;
use App\Models\Contabilidad\MapeoEnlace;
use App\Models\Contabilidad\PlanCuenta;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AsientoAutomaticoService
{
    /**
     * Genera el correlativo oficial único para el comprobante (ej. CI-2026-00001).
     */
    public function generarNumeroCorrelativo(string $tipo, int $gestion): string
    {
        $prefijo = match (strtoupper($tipo)) {
            'INGRESO' => 'CI',
            'EGRESO' => 'CE',
            default => 'CD',
        };

        $ultimo = Comprobante::where('tipo', strtoupper($tipo))
            ->whereYear('fecha', $gestion)
            ->latest('id')
            ->first();

        $secuencia = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo->numero_comprobante, $matches)) {
            $secuencia = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%05d', $prefijo, $gestion, $secuencia);
    }

    /**
     * Obtiene una cuenta contable a partir de su código de enlace parametrizado.
     */
    private function obtenerCuentaPorEnlace(string $codigoEnlace): array
    {
        $mapeo = MapeoEnlace::with('cuentaDefecto')->where('codigo_enlace', $codigoEnlace)->first();

        if (!$mapeo || !$mapeo->cuentaDefecto) {
            throw new Exception("No se encontró la parametrización contable para el enlace '{$codigoEnlace}'.");
        }

        return [
            'id_cuenta' => $mapeo->id_cuenta_defecto,
            'id_centro_costo' => $mapeo->id_centro_costo_defecto,
            'nombre' => $mapeo->cuentaDefecto->nombre,
            'codigo' => $mapeo->cuentaDefecto->codigo,
        ];
    }

    /**
     * 1. Genera Comprobante de Ingreso (CI) a partir de una Sesión de Caja cerrada (Comercial).
     */
    public function generarAsientoArqueoCaja(CajaSesion $sesion, ?int $usuarioId = null): Comprobante
    {
        // Verificar si ya fue contabilizada
        $existente = Comprobante::where('origen_modulo', 'COMERCIAL_CAJA')
            ->where('id_referencia_origen', $sesion->id)
            ->where('_estado', 'ACTIVO')
            ->first();

        if ($existente) {
            return $existente;
        }

        $gestionAnio = (int) Carbon::parse($sesion->fecha_cierre ?? $sesion->fecha_apertura)->year;
        $gestion = GestionContable::where('gestion', $gestionAnio)->first();
        if (!$gestion) {
            $gestion = GestionContable::firstOrCreate(['gestion' => $gestionAnio], [
                'fecha_inicio' => "{$gestionAnio}-01-01",
                'fecha_fin' => "{$gestionAnio}-12-31",
                'estado' => 'ABIERTA',
            ]);
        }

        $mes = (int) Carbon::parse($sesion->fecha_cierre ?? $sesion->fecha_apertura)->month;
        $numeroComp = $this->generarNumeroCorrelativo('INGRESO', $gestionAnio);

        return DB::transaction(function () use ($sesion, $gestion, $mes, $numeroComp, $usuarioId) {
            $cajaEfectivo = $this->obtenerCuentaPorEnlace('CAJA_CENTRAL_EFECTIVO');
            $bancoQr = $this->obtenerCuentaPorEnlace('BANCO_UNION_QR');
            $cxCobrarAgua = $this->obtenerCuentaPorEnlace('CUENTAS_COBRAR_AGUA');
            $cxCobrarConvenio = $this->obtenerCuentaPorEnlace('CUENTAS_COBRAR_CONVENIOS');
            $ingresosVarios = $this->obtenerCuentaPorEnlace('INGRESO_RECONEXION_MULTAS');

            $montoEfectivo = round((float) ($sesion->monto_ventas_efectivo ?? $sesion->total_efectivo ?? 0), 2);
            $montoQr = round((float) ($sesion->monto_ventas_qr_banco ?? $sesion->total_digital ?? 0), 2);
            $totalCobrado = round($montoEfectivo + $montoQr, 2);

            if ($totalCobrado == 0 && (float) ($sesion->total_recaudado ?? 0) > 0) {
                $montoEfectivo = round((float) $sesion->total_recaudado, 2);
                $totalCobrado = $montoEfectivo;
            }

            $glosa = "Recaudación del turno {$sesion->numero_sesion} en ventanilla {$sesion->puntoVenta?->nombre}. Cajero(a): {$sesion->cajero?->name}.";

            $comprobante = Comprobante::create([
                'numero_comprobante' => $numeroComp,
                'tipo' => 'INGRESO',
                'fecha' => Carbon::parse($sesion->fecha_cierre ?? now())->toDateString(),
                'id_gestion' => $gestion->id,
                'mes' => $mes,
                'glosa_principal' => $glosa,
                'beneficiario' => $sesion->cajero?->name ?? 'Ventanilla Recaudadora',
                'tipo_documento_respaldo' => 'PLANILLA_ARQUEO_CAJA',
                'numero_documento_respaldo' => $sesion->numero_sesion,
                'total_debe' => $totalCobrado,
                'total_haber' => $totalCobrado,
                'diferencia' => 0.00,
                'estado' => 'APROBADO',
                'origen_modulo' => 'COMERCIAL_CAJA',
                'id_referencia_origen' => $sesion->id,
                'id_usuario_elaboracion' => $usuarioId ?? $sesion->id_cajero,
                'id_usuario_aprobacion' => $usuarioId ?? 1,
                'fecha_aprobacion' => now(),
            ]);

            $orden = 1;

            // DEBE: Efectivo en Caja
            if ($montoEfectivo > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $cajaEfectivo['id_cuenta'],
                    'id_centro_costo' => $cajaEfectivo['id_centro_costo'],
                    'glosa_linea' => 'Ingreso de efectivo recaudado en ventanilla física',
                    'debe' => $montoEfectivo,
                    'haber' => 0.00,
                    'orden' => $orden++,
                ]);
            }

            // DEBE: QR y Transferencias Bancarias
            if ($montoQr > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $bancoQr['id_cuenta'],
                    'id_centro_costo' => $bancoQr['id_centro_costo'],
                    'glosa_linea' => 'Ingresos por recaudación digital QR / Banco Unión',
                    'debe' => $montoQr,
                    'haber' => 0.00,
                    'orden' => $orden++,
                ]);
            }

            // HABER: Desglose de recaudación según origen (lecturas, cuotas de convenio y recibos)
            $totalLecturas = round((float) $sesion->lecturas()->sum('total_facturado'), 2);
            $totalCuotas = round((float) $sesion->cuotas()->sum('monto_cuota'), 2);
            $totalRecibos = round((float) $sesion->recibos()->where('estado', 'VALIDO')->sum('monto_total'), 2);

            // Ajuste por si no hay desglose específico
            if ($totalLecturas == 0 && $totalCuotas == 0 && $totalRecibos == 0) {
                $totalLecturas = $totalCobrado;
            }

            if ($totalLecturas > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $cxCobrarAgua['id_cuenta'],
                    'id_centro_costo' => $cxCobrarAgua['id_centro_costo'],
                    'glosa_linea' => 'Descargo de cuentas por cobrar por cobro de agua potable',
                    'debe' => 0.00,
                    'haber' => min($totalLecturas, $totalCobrado),
                    'orden' => $orden++,
                ]);
            }

            $remanente = round($totalCobrado - min($totalLecturas, $totalCobrado), 2);

            if ($totalCuotas > 0 && $remanente > 0) {
                $montoCuota = min($totalCuotas, $remanente);
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $cxCobrarConvenio['id_cuenta'],
                    'id_centro_costo' => $cxCobrarConvenio['id_centro_costo'],
                    'glosa_linea' => 'Descargo de cuotas de convenios de pago refinanciados',
                    'debe' => 0.00,
                    'haber' => $montoCuota,
                    'orden' => $orden++,
                ]);
                $remanente = round($remanente - $montoCuota, 2);
            }

            if ($remanente > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $ingresosVarios['id_cuenta'],
                    'id_centro_costo' => $ingresosVarios['id_centro_costo'],
                    'glosa_linea' => 'Recaudación por reconexiones, reposiciones y otros servicios',
                    'debe' => 0.00,
                    'haber' => $remanente,
                    'orden' => $orden++,
                ]);
            }

            $comprobante->recalcularTotales();

            return $comprobante;
        });
    }

    /**
     * 2. Genera Comprobante de Diario (CD) para el Devengado Mensual de Planilla de Agua.
     */
    public function generarAsientoDevengadoAgua(int $periodoId, ?int $usuarioId = null): Comprobante
    {
        $existente = Comprobante::where('origen_modulo', 'COMERCIAL_DEVENGADO')
            ->where('id_referencia_origen', $periodoId)
            ->where('_estado', 'ACTIVO')
            ->first();

        if ($existente) {
            return $existente;
        }

        $periodo = DB::table('comercial.periodos_facturacion')->where('id', $periodoId)->first();
        if (!$periodo) {
            throw new Exception("El período de facturación #{$periodoId} no existe.");
        }

        $lecturas = LecturaMensual::where('id_periodo', $periodoId)->where('_estado', 'ACTIVO')->get();
        if ($lecturas->isEmpty()) {
            throw new Exception("No hay lecturas registradas para el período {$periodo->periodo}.");
        }

        $gestionAnio = (int) ($periodo->gestion ?? Carbon::now()->year);
        $gestion = GestionContable::firstOrCreate(['gestion' => $gestionAnio], [
            'fecha_inicio' => "{$gestionAnio}-01-01",
            'fecha_fin' => "{$gestionAnio}-12-31",
            'estado' => 'ABIERTA',
        ]);

        $mes = (int) ($periodo->mes ?? Carbon::now()->month);
        $numeroComp = $this->generarNumeroCorrelativo('DIARIO', $gestionAnio);

        return DB::transaction(function () use ($periodo, $lecturas, $gestion, $mes, $numeroComp, $usuarioId) {
            $cxCobrar = $this->obtenerCuentaPorEnlace('CUENTAS_COBRAR_AGUA');
            $ingresoAgua = $this->obtenerCuentaPorEnlace('INGRESO_AGUA_POTABLE');
            $ingresoAlcantarillado = $this->obtenerCuentaPorEnlace('INGRESO_ALCANTARILLADO');
            $debitoFiscal = $this->obtenerCuentaPorEnlace('DEBITO_FISCAL_IVA');
            $ingresosVarios = $this->obtenerCuentaPorEnlace('INGRESO_RECONEXION_MULTAS');

            $totalFacturado = round((float) $lecturas->sum('total_facturado'), 2);
            $montoAgua = round((float) $lecturas->sum('monto_agua'), 2);
            $montoAlcantarillado = round((float) $lecturas->sum('monto_alcantarillado'), 2);
            $montoReposicion = round((float) $lecturas->sum('monto_reposicion'), 2);
            $montoRecargo = round((float) $lecturas->sum('monto_recargo'), 2);

            // Débito fiscal IVA aproximado (13% de consumo facturado)
            $totalIva = round(($montoAgua + $montoAlcantarillado) * 0.13, 2);
            $netoAgua = round($montoAgua - ($montoAgua * 0.13), 2);
            $netoAlcantarillado = round($montoAlcantarillado - ($montoAlcantarillado * 0.13), 2);

            // Ajuste de cuadratura exacta
            $haberTotal = $netoAgua + $netoAlcantarillado + $totalIva + $montoReposicion + $montoRecargo;
            $diferencia = round($totalFacturado - $haberTotal, 2);
            $netoAgua = round($netoAgua + $diferencia, 2);

            $glosa = "Devengamiento de derechos de cobro por servicios de agua potable y alcantarillado período {$periodo->periodo}. Abonados: {$lecturas->count()}.";

            $comprobante = Comprobante::create([
                'numero_comprobante' => $numeroComp,
                'tipo' => 'DIARIO',
                'fecha' => Carbon::now()->toDateString(),
                'id_gestion' => $gestion->id,
                'mes' => $mes,
                'glosa_principal' => $glosa,
                'beneficiario' => 'Padrón de Abonados EMAPAP',
                'tipo_documento_respaldo' => 'PLANILLA_CONSUMOS_MENSUALES',
                'numero_documento_respaldo' => $periodo->periodo,
                'total_debe' => $totalFacturado,
                'total_haber' => $totalFacturado,
                'diferencia' => 0.00,
                'estado' => 'APROBADO',
                'origen_modulo' => 'COMERCIAL_DEVENGADO',
                'id_referencia_origen' => $periodo->id,
                'id_usuario_elaboracion' => $usuarioId ?? 1,
                'id_usuario_aprobacion' => $usuarioId ?? 1,
                'fecha_aprobacion' => now(),
            ]);

            $orden = 1;

            // DEBE: Cuentas por Cobrar
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $cxCobrar['id_cuenta'],
                'id_centro_costo' => $cxCobrar['id_centro_costo'],
                'glosa_linea' => 'Derechos exigibles a abonados por consumo de agua del mes',
                'debe' => $totalFacturado,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            // HABER: Neto Agua
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $ingresoAgua['id_cuenta'],
                'id_centro_costo' => $ingresoAgua['id_centro_costo'],
                'glosa_linea' => 'Ingreso neto devengado por suministro de agua potable',
                'debe' => 0.00,
                'haber' => $netoAgua,
                'orden' => $orden++,
            ]);

            // HABER: Neto Alcantarillado
            if ($netoAlcantarillado > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $ingresoAlcantarillado['id_cuenta'],
                    'id_centro_costo' => $ingresoAlcantarillado['id_centro_costo'],
                    'glosa_linea' => 'Ingreso neto devengado por servicio de alcantarillado',
                    'debe' => 0.00,
                    'haber' => $netoAlcantarillado,
                    'orden' => $orden++,
                ]);
            }

            // HABER: Débito Fiscal IVA
            if ($totalIva > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $debitoFiscal['id_cuenta'],
                    'id_centro_costo' => $debitoFiscal['id_centro_costo'],
                    'glosa_linea' => 'Débito Fiscal IVA 13% sobre servicios básicos facturados',
                    'debe' => 0.00,
                    'haber' => $totalIva,
                    'orden' => $orden++,
                ]);
            }

            // HABER: Reposición / Recargos
            $otrosConceptos = round($montoReposicion + $montoRecargo, 2);
            if ($otrosConceptos > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $ingresosVarios['id_cuenta'],
                    'id_centro_costo' => $ingresosVarios['id_centro_costo'],
                    'glosa_linea' => 'Ingresos por reposición de formularios y recargos',
                    'debe' => 0.00,
                    'haber' => $otrosConceptos,
                    'orden' => $orden++,
                ]);
            }

            $comprobante->recalcularTotales();

            return $comprobante;
        });
    }

    /**
     * 3. Genera Comprobante de Diario (CD) para el Devengado de Planilla Salarial (RRHH).
     */
    public function generarAsientoPlanillaSueldos(int $planillaConsolidadaId, ?int $usuarioId = null): Comprobante
    {
        $existente = Comprobante::where('origen_modulo', 'RRHH_PLANILLA')
            ->where('id_referencia_origen', $planillaConsolidadaId)
            ->where('_estado', 'ACTIVO')
            ->first();

        if ($existente) {
            return $existente;
        }

        $planilla = DB::table('rrhh.planillas_consolidadas')->where('id', $planillaConsolidadaId)->first();
        if (!$planilla) {
            throw new Exception("La planilla consolidada #{$planillaConsolidadaId} no existe.");
        }

        $detalles = DB::table('rrhh.detalles_planillas')
            ->where('id_planilla_consolidada', $planillaConsolidadaId)
            ->get();

        if ($detalles->isEmpty()) {
            throw new Exception("La planilla {$planilla->cite_oficial} no tiene funcionarios detallados.");
        }

        $gestionAnio = (int) ($planilla->gestion ?? Carbon::now()->year);
        $gestion = GestionContable::firstOrCreate(['gestion' => $gestionAnio], [
            'fecha_inicio' => "{$gestionAnio}-01-01",
            'fecha_fin' => "{$gestionAnio}-12-31",
            'estado' => 'ABIERTA',
        ]);

        $mes = (int) ($planilla->mes ?? Carbon::now()->month);
        $numeroComp = $this->generarNumeroCorrelativo('DIARIO', $gestionAnio);

        return DB::transaction(function () use ($planilla, $detalles, $gestion, $mes, $numeroComp, $usuarioId, $gestionAnio) {
            // Cuentas de Gasto (DEBE)
            $gastoBasico = $this->obtenerCuentaPorEnlace('GASTO_SUELDOS_BASICOS');
            $gastoAntiguedad = $this->obtenerCuentaPorEnlace('GASTO_BONO_ANTIGUEDAD');
            $gastoRefrigerio = $this->obtenerCuentaPorEnlace('GASTO_REFRIGERIOS');
            $gastoCns = $this->obtenerCuentaPorEnlace('GASTO_PATRONAL_CNS');
            $gastoSolidario = $this->obtenerCuentaPorEnlace('GASTO_PATRONAL_SOLIDARIO');
            $gastoVivienda = $this->obtenerCuentaPorEnlace('GASTO_PATRONAL_VIVIENDA');
            $gastoAguinaldo = $this->obtenerCuentaPorEnlace('GASTO_PROVISION_AGUINALDO');
            $gastoIndemnizacion = $this->obtenerCuentaPorEnlace('GASTO_PREVISION_INDEMNIZACION');

            // Cuentas de Pasivo (HABER)
            $sueldosPorPagar = $this->obtenerCuentaPorEnlace('SUELDOS_POR_PAGAR');
            $gestoraLaboral = $this->obtenerCuentaPorEnlace('RETENCIONES_GESTORA_LABORAL');
            $patronalesPorPagar = $this->obtenerCuentaPorEnlace('APORTES_PATRONALES_POR_PAGAR');
            $descuentosAtraso = $this->obtenerCuentaPorEnlace('DESCUENTOS_ATRASOS_RETENCIONES');
            $pasivoIndemnizacion = $this->obtenerCuentaPorEnlace('PREVISION_INDEMNIZACION_PASIVO');

            // Sumatorias de planilla
            $totalBasico = round((float) $detalles->sum('haber_basico'), 2);
            $totalAntiguedad = round((float) $detalles->sum('bono_antiguedad'), 2);
            $totalRefrigerios = round((float) $detalles->sum('refrigerio_bs'), 2);
            $totalGanado = round((float) $detalles->sum('total_ganado'), 2);

            $totalGestora1271 = round((float) $detalles->sum('gestora_12_71'), 2);
            $totalDescuentoAtraso = round((float) $detalles->sum('descuento_atraso'), 2);
            $totalLiquido = round((float) $detalles->sum('liquido_pagable_total'), 2);

            // Aportes Patronales sobre Total Ganado
            $patronalCns10 = round($totalGanado * 0.10, 2);
            $patronalSol3 = round($totalGanado * 0.03, 2);
            $patronalViv2 = round($totalGanado * 0.02, 2);
            $provisionAguinaldo = round($totalGanado * 0.0833, 2);
            $previsionIndemnizacion = round($totalGanado * 0.0833, 2);

            $totalPatronalPagar = round($patronalCns10 + $patronalSol3 + $patronalViv2, 2);

            // Totales de Debe y Haber
            $totalDebe = round($totalBasico + $totalAntiguedad + $totalRefrigerios + $patronalCns10 + $patronalSol3 + $patronalViv2 + $provisionAguinaldo + $previsionIndemnizacion, 2);
            $totalHaber = round($totalLiquido + $totalGestora1271 + $totalPatronalPagar + $totalDescuentoAtraso + $previsionIndemnizacion + $provisionAguinaldo, 2);

            // Ajuste por redondeos centesimales
            $ajuste = round($totalDebe - $totalHaber, 2);
            if ($ajuste != 0) {
                $totalLiquido = round($totalLiquido + $ajuste, 2);
                $totalHaber = $totalDebe;
            }

            $glosa = "Devengamiento de planilla mensual de sueldos y aportes patronales {$planilla->cite_oficial} mes {$mes}/{$gestionAnio}. Personal: {$detalles->count()} funcionarios.";

            $comprobante = Comprobante::create([
                'numero_comprobante' => $numeroComp,
                'tipo' => 'DIARIO',
                'fecha' => Carbon::parse($planilla->fecha_cierre ?? now())->toDateString(),
                'id_gestion' => $gestion->id,
                'mes' => $mes,
                'glosa_principal' => $glosa,
                'beneficiario' => 'Personal y Funcionarios de EMAPAP',
                'tipo_documento_respaldo' => 'PLANILLA_SUELDOS_OFICIAL',
                'numero_documento_respaldo' => $planilla->cite_oficial,
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'diferencia' => 0.00,
                'estado' => 'APROBADO',
                'origen_modulo' => 'RRHH_PLANILLA',
                'id_referencia_origen' => $planilla->id,
                'id_usuario_elaboracion' => $usuarioId ?? $planilla->id_usuario_cierre ?? 1,
                'id_usuario_aprobacion' => $usuarioId ?? 1,
                'fecha_aprobacion' => now(),
            ]);

            $orden = 1;

            // DEBE: Gastos de Personal
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoBasico['id_cuenta'],
                'id_centro_costo' => $gastoBasico['id_centro_costo'],
                'glosa_linea' => 'Haber básico mensual personal EMAPAP',
                'debe' => $totalBasico,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            if ($totalAntiguedad > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $gastoAntiguedad['id_cuenta'],
                    'id_centro_costo' => $gastoAntiguedad['id_centro_costo'],
                    'glosa_linea' => 'Bono de antigüedad legal según años de servicio',
                    'debe' => $totalAntiguedad,
                    'haber' => 0.00,
                    'orden' => $orden++,
                ]);
            }

            if ($totalRefrigerios > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $gastoRefrigerio['id_cuenta'],
                    'id_centro_costo' => $gastoRefrigerio['id_centro_costo'],
                    'glosa_linea' => 'Asignación de refrigerios diarios de asistencia',
                    'debe' => $totalRefrigerios,
                    'haber' => 0.00,
                    'orden' => $orden++,
                ]);
            }

            // Gasto Aportes Patronales
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoCns['id_cuenta'],
                'id_centro_costo' => $gastoCns['id_centro_costo'],
                'glosa_linea' => 'Aporte patronal seguro de salud (CNS 10%)',
                'debe' => $patronalCns10,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoSolidario['id_cuenta'],
                'id_centro_costo' => $gastoSolidario['id_centro_costo'],
                'glosa_linea' => 'Aporte patronal fondo solidario Gestora (3%)',
                'debe' => $patronalSol3,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoVivienda['id_cuenta'],
                'id_centro_costo' => $gastoVivienda['id_centro_costo'],
                'glosa_linea' => 'Aporte patronal fondo pro-vivienda Gestora (2%)',
                'debe' => $patronalViv2,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            // Provisiones sociales
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoAguinaldo['id_cuenta'],
                'id_centro_costo' => $gastoAguinaldo['id_centro_costo'],
                'glosa_linea' => 'Provisión mensual aguinaldo de navidad (8.33%)',
                'debe' => $provisionAguinaldo,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gastoIndemnizacion['id_cuenta'],
                'id_centro_costo' => $gastoIndemnizacion['id_centro_costo'],
                'glosa_linea' => 'Previsión mensual indemnización por años de servicio (8.33%)',
                'debe' => $previsionIndemnizacion,
                'haber' => 0.00,
                'orden' => $orden++,
            ]);

            // HABER: Pasivos por Pagar
            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $sueldosPorPagar['id_cuenta'],
                'id_centro_costo' => $sueldosPorPagar['id_centro_costo'],
                'glosa_linea' => 'Líquido pagable a favor de funcionarios de EMAPAP',
                'debe' => 0.00,
                'haber' => $totalLiquido,
                'orden' => $orden++,
            ]);

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $gestoraLaboral['id_cuenta'],
                'id_centro_costo' => $gestoraLaboral['id_centro_costo'],
                'glosa_linea' => 'Retención laboral Gestora Pública SIP (12.71%)',
                'debe' => 0.00,
                'haber' => $totalGestora1271,
                'orden' => $orden++,
            ]);

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $patronalesPorPagar['id_cuenta'],
                'id_centro_costo' => $patronalesPorPagar['id_centro_costo'],
                'glosa_linea' => 'Obligación patronal por pagar (CNS 10% + Gestora 5%)',
                'debe' => 0.00,
                'haber' => $totalPatronalPagar,
                'orden' => $orden++,
            ]);

            if ($totalDescuentoAtraso > 0) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $descuentosAtraso['id_cuenta'],
                    'id_centro_costo' => $descuentosAtraso['id_centro_costo'],
                    'glosa_linea' => 'Descuentos por minutos de atraso según control biométrico',
                    'debe' => 0.00,
                    'haber' => $totalDescuentoAtraso,
                    'orden' => $orden++,
                ]);
            }

            ComprobanteDetalle::create([
                'id_comprobante' => $comprobante->id,
                'id_cuenta' => $pasivoIndemnizacion['id_cuenta'],
                'id_centro_costo' => $pasivoIndemnizacion['id_centro_costo'],
                'glosa_linea' => 'Previsión acumulada para indemnizaciones futuras',
                'debe' => 0.00,
                'haber' => $previsionIndemnizacion,
                'orden' => $orden++,
            ]);

            // Pasivo provisión aguinaldo (2.1.1.03)
            $cuentaAguinaldoPasivo = PlanCuenta::where('codigo', '2.1.1.03')->first();
            if ($cuentaAguinaldoPasivo) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'id_cuenta' => $cuentaAguinaldoPasivo->id,
                    'id_centro_costo' => $sueldosPorPagar['id_centro_costo'],
                    'glosa_linea' => 'Provisión acumulada para pago de aguinaldo anual',
                    'debe' => 0.00,
                    'haber' => $provisionAguinaldo,
                    'orden' => $orden++,
                ]);
            }

            $comprobante->recalcularTotales();

            return $comprobante;
        });
    }
}
