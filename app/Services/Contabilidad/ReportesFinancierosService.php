<?php

declare(strict_types=1);

namespace App\Services\Contabilidad;

use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\ComprobanteDetalle;
use App\Models\Contabilidad\PlanCuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesFinancierosService
{
    /**
     * 1. Libro Diario General.
     */
    public function obtenerLibroDiario(string $desde, string $hasta, ?string $tipo = null): array
    {
        $query = Comprobante::with(['detalles.cuenta', 'detalles.centroCosto', 'usuarioElaboracion'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->whereIn('estado', ['APROBADO', 'CONTABILIZADO'])
            ->where('_estado', 'ACTIVO')
            ->orderBy('fecha')
            ->orderBy('numero_comprobante');

        if ($tipo && $tipo !== 'TODOS') {
            $query->where('tipo', strtoupper($tipo));
        }

        $comprobantes = $query->get();

        $totalDebe = (float) $comprobantes->sum('total_debe');
        $totalHaber = (float) $comprobantes->sum('total_haber');

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'total_comprobantes' => $comprobantes->count(),
            'total_debe' => round($totalDebe, 2),
            'total_haber' => round($totalHaber, 2),
            'comprobantes' => $comprobantes,
        ];
    }

    /**
     * 2. Libro Mayor Analítico por Cuenta.
     */
    public function obtenerLibroMayor(int $cuentaId, string $desde, string $hasta): array
    {
        $cuenta = PlanCuenta::findOrFail($cuentaId);

        // Saldo anterior a la fecha inicial
        $movimientosAnteriores = ComprobanteDetalle::where('id_cuenta', $cuentaId)
            ->whereHas('comprobante', function ($q) use ($desde) {
                $q->where('fecha', '<', $desde)
                    ->whereIn('estado', ['APROBADO', 'CONTABILIZADO'])
                    ->where('_estado', 'ACTIVO');
            })
            ->get();

        $debeAnterior = (float) $movimientosAnteriores->sum('debe');
        $haberAnterior = (float) $movimientosAnteriores->sum('haber');
        $saldoInicial = $cuenta->naturaleza === 'DEUDORA'
            ? round($debeAnterior - $haberAnterior, 2)
            : round($haberAnterior - $debeAnterior, 2);

        // Movimientos del período
        $detalles = ComprobanteDetalle::with(['comprobante', 'centroCosto'])
            ->where('id_cuenta', $cuentaId)
            ->whereHas('comprobante', function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha', [$desde, $hasta])
                    ->whereIn('estado', ['APROBADO', 'CONTABILIZADO'])
                    ->where('_estado', 'ACTIVO');
            })
            ->join('contabilidad.comprobantes as c', 'c.id', '=', 'contabilidad.comprobante_detalles.id_comprobante')
            ->orderBy('c.fecha')
            ->orderBy('c.numero_comprobante')
            ->select('contabilidad.comprobante_detalles.*')
            ->get();

        $saldoProgresivo = $saldoInicial;
        $movimientosFormateados = [];

        foreach ($detalles as $d) {
            $debe = (float) $d->debe;
            $haber = (float) $d->haber;

            if ($cuenta->naturaleza === 'DEUDORA') {
                $saldoProgresivo += ($debe - $haber);
            } else {
                $saldoProgresivo += ($haber - $debe);
            }

            $movimientosFormateados[] = [
                'id' => $d->id,
                'fecha' => $d->comprobante->fecha,
                'numero_comprobante' => $d->comprobante->numero_comprobante,
                'tipo' => $d->comprobante->tipo,
                'glosa' => $d->glosa_linea ?: $d->comprobante->glosa_principal,
                'centro_costo' => $d->centroCosto?->nombre,
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => round($saldoProgresivo, 2),
            ];
        }

        $totalDebePeriodo = (float) $detalles->sum('debe');
        $totalHaberPeriodo = (float) $detalles->sum('haber');

        return [
            'cuenta' => [
                'id' => $cuenta->id,
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'naturaleza' => $cuenta->naturaleza,
                'tipo' => $cuenta->tipo,
            ],
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'saldo_inicial' => $saldoInicial,
            'total_debe' => round($totalDebePeriodo, 2),
            'total_haber' => round($totalHaberPeriodo, 2),
            'total_debe_periodo' => round($totalDebePeriodo, 2),
            'total_haber_periodo' => round($totalHaberPeriodo, 2),
            'saldo_final' => round($saldoProgresivo, 2),
            'movimientos' => $movimientosFormateados,
        ];
    }

    /**
     * 3. Balance de Comprobación de Sumas y Saldos.
     */
    public function obtenerBalanceComprobacion(string $desde, string $hasta): array
    {
        $cuentasImputables = PlanCuenta::where('permite_movimiento', true)
            ->where('estado', 'ACTIVO')
            ->orderBy('codigo')
            ->get();

        $filas = [];
        $totalSumasDebe = 0.0;
        $totalSumasHaber = 0.0;
        $totalSaldosDeudor = 0.0;
        $totalSaldosAcreedor = 0.0;

        foreach ($cuentasImputables as $cta) {
            $sumas = DB::table('contabilidad.comprobante_detalles as d')
                ->join('contabilidad.comprobantes as c', 'c.id', '=', 'd.id_comprobante')
                ->where('d.id_cuenta', $cta->id)
                ->whereBetween('c.fecha', [$desde, $hasta])
                ->whereIn('c.estado', ['APROBADO', 'CONTABILIZADO'])
                ->where('c._estado', 'ACTIVO')
                ->selectRaw('COALESCE(SUM(d.debe), 0) as total_debe, COALESCE(SUM(d.haber), 0) as total_haber')
                ->first();

            $debe = (float) ($sumas->total_debe ?? 0);
            $haber = (float) ($sumas->total_haber ?? 0);

            // Si no tiene movimientos en el período, omitir para no saturar
            if ($debe == 0 && $haber == 0) {
                continue;
            }

            $saldoDeudor = 0.0;
            $saldoAcreedor = 0.0;

            if ($cta->naturaleza === 'DEUDORA') {
                $dif = $debe - $haber;
                if ($dif >= 0) {
                    $saldoDeudor = $dif;
                } else {
                    $saldoAcreedor = abs($dif);
                }
            } else {
                $dif = $haber - $debe;
                if ($dif >= 0) {
                    $saldoAcreedor = $dif;
                } else {
                    $saldoDeudor = abs($dif);
                }
            }

            $totalSumasDebe += $debe;
            $totalSumasHaber += $haber;
            $totalSaldosDeudor += $saldoDeudor;
            $totalSaldosAcreedor += $saldoAcreedor;

            $filas[] = [
                'id_cuenta' => $cta->id,
                'codigo' => $cta->codigo,
                'nombre' => $cta->nombre,
                'tipo' => $cta->tipo,
                'naturaleza' => $cta->naturaleza,
                'sumas_debe' => round($debe, 2),
                'sumas_haber' => round($haber, 2),
                'suma_debe' => round($debe, 2),
                'suma_haber' => round($haber, 2),
                'saldo_deudor' => round($saldoDeudor, 2),
                'saldo_acreedor' => round($saldoAcreedor, 2),
            ];
        }

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'totales' => [
                'sumas_debe' => round($totalSumasDebe, 2),
                'sumas_haber' => round($totalSumasHaber, 2),
                'saldos_deudor' => round($totalSaldosDeudor, 2),
                'saldos_acreedor' => round($totalSaldosAcreedor, 2),
                'total_debe' => round($totalSumasDebe, 2),
                'total_haber' => round($totalSumasHaber, 2),
                'total_deudor' => round($totalSaldosDeudor, 2),
                'total_acreedor' => round($totalSaldosAcreedor, 2),
                'cuadrado' => round(abs($totalSumasDebe - $totalSumasHaber), 2) === 0.00
                    && round(abs($totalSaldosDeudor - $totalSaldosAcreedor), 2) === 0.00,
            ],
            'cuentas' => $filas,
        ];
    }

    /**
     * 4. Estado de Rendimiento / Resultados (Pérdidas y Ganancias).
     */
    public function obtenerEstadoResultados(string $desde, string $hasta): array
    {
        $comprobacion = $this->obtenerBalanceComprobacion($desde, $hasta);
        $recursos = [];
        $gastos = [];

        $totalRecursos = 0.0;
        $totalGastos = 0.0;

        foreach ($comprobacion['cuentas'] as $c) {
            if ($c['tipo'] === 'RECURSO') {
                $saldo = $c['saldo_acreedor'] - $c['saldo_deudor'];
                $totalRecursos += $saldo;
                $recursos[] = array_merge($c, ['saldo_neto' => round($saldo, 2)]);
            } elseif ($c['tipo'] === 'GASTO') {
                $saldo = $c['saldo_deudor'] - $c['saldo_acreedor'];
                $totalGastos += $saldo;
                $gastos[] = array_merge($c, ['saldo_neto' => round($saldo, 2)]);
            }
        }

        $resultadoEjercicio = round($totalRecursos - $totalGastos, 2);

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'total_recursos' => round($totalRecursos, 2),
            'total_gastos' => round($totalGastos, 2),
            'total_ingresos' => round($totalRecursos, 2),
            'resultado_ejercicio' => $resultadoEjercicio,
            'resultado_neto' => $resultadoEjercicio,
            'superavit_deficit' => $resultadoEjercicio >= 0 ? 'SUPERÁVIT OPERATIVO' : 'DÉFICIT OPERATIVO',
            'tipo_resultado' => $resultadoEjercicio >= 0 ? 'SUPERÁVIT OPERATIVO' : 'DÉFICIT OPERATIVO',
            'recursos' => $recursos,
            'gastos' => $gastos,
            'cuentas_ingreso' => $recursos,
            'cuentas_gasto' => $gastos,
        ];
    }

    /**
     * 5. Balance General Clasificado.
     */
    public function obtenerBalanceGeneral(string $fechaCorte): array
    {
        $desdeInicio = Carbon::parse($fechaCorte)->startOfYear()->toDateString();
        $comprobacion = $this->obtenerBalanceComprobacion($desdeInicio, $fechaCorte);
        $resultados = $this->obtenerEstadoResultados($desdeInicio, $fechaCorte);

        $activos = [];
        $pasivos = [];
        $patrimonio = [];

        $totalActivo = 0.0;
        $totalPasivo = 0.0;
        $totalPatrimonio = 0.0;

        foreach ($comprobacion['cuentas'] as $c) {
            if ($c['tipo'] === 'ACTIVO') {
                $saldo = $c['saldo_deudor'] - $c['saldo_acreedor'];
                $totalActivo += $saldo;
                $activos[] = array_merge($c, ['saldo_neto' => round($saldo, 2), 'saldo' => round($saldo, 2)]);
            } elseif ($c['tipo'] === 'PASIVO') {
                $saldo = $c['saldo_acreedor'] - $c['saldo_deudor'];
                $totalPasivo += $saldo;
                $pasivos[] = array_merge($c, ['saldo_neto' => round($saldo, 2), 'saldo' => round($saldo, 2)]);
            } elseif ($c['tipo'] === 'PATRIMONIO') {
                $saldo = $c['saldo_acreedor'] - $c['saldo_deudor'];
                $totalPatrimonio += $saldo;
                $patrimonio[] = array_merge($c, ['saldo_neto' => round($saldo, 2), 'saldo' => round($saldo, 2)]);
            }
        }

        // Agregar el resultado de la gestión actual al patrimonio
        $resultadoGestion = $resultados['resultado_ejercicio'];
        $totalPatrimonioConResultado = round($totalPatrimonio + $resultadoGestion, 2);
        $totalPasivoMasPatrimonio = round($totalPasivo + $totalPatrimonioConResultado, 2);

        return [
            'fecha_corte' => $fechaCorte,
            'total_activo' => round($totalActivo, 2),
            'total_pasivo' => round($totalPasivo, 2),
            'total_patrimonio_base' => round($totalPatrimonio, 2),
            'resultado_gestion' => $resultadoGestion,
            'total_patrimonio' => $totalPatrimonioConResultado,
            'total_pasivo_y_patrimonio' => $totalPasivoMasPatrimonio,
            'balance_cuadrado' => round(abs($totalActivo - $totalPasivoMasPatrimonio), 2) === 0.00,
            'cuadrado' => round(abs($totalActivo - $totalPasivoMasPatrimonio), 2) === 0.00,
            'activos' => $activos,
            'pasivos' => $pasivos,
            'patrimonio' => $patrimonio,
            'cuentas_activo' => $activos,
            'cuentas_pasivo' => $pasivos,
            'cuentas_patrimonio' => $patrimonio,
        ];
    }
}
