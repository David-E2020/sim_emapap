<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\OrdenTrabajo;
use App\Models\Facturacion\Factura;
use App\Services\Facturacion\EmisionFacturaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CobranzaAguaService
{
    public function __construct(
        protected EmisionFacturaService $emisionFacturaService
    ) {}

    /**
     * Obtiene el estado de cuenta completo del abonado (deudas de lecturas y convenios).
     * Permite consultar por Código, CI/NIT, ID o Nombre.
     */
    public function obtenerEstadoCuenta(string $codigoOId): array
    {
        $criterio = trim($codigoOId);

        // 1. Buscar prioritariamente por código exacto o código con relleno de ceros (ej. 5004 -> 05004)
        $codigoPadded = str_pad($criterio, 5, '0', STR_PAD_LEFT);
        $abonado = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->where('codigo', $criterio)
            ->orWhere('codigo', $codigoPadded)
            ->first();

        // 2. Si no coincide con código, buscar por número de documento exacto (CI/NIT)
        if (!$abonado) {
            $abonado = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
                ->where('numero_documento', $criterio)
                ->first();
        }

        // 3. Si no coincide, buscar por nombre exacto o aproximado
        if (!$abonado) {
            $abonado = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
                ->where('nombre_completo', 'ilike', "%{$criterio}%")
                ->first();
        }

        // 4. Como último recurso, si es estrictamente numérico y no se encontró por código/doc, buscar por ID interno
        if (!$abonado && is_numeric($criterio)) {
            $abonado = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
                ->where('id', (int) $criterio)
                ->first();
        }

        if (!$abonado) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("No se encontró al abonado con el criterio: {$criterio}");
        }

        $lecturasPendientes = LecturaMensual::with('periodo')
            ->where('id_abonado', $abonado->id)
            ->where('estado_pago', 'PENDIENTE')
            ->get()
            ->sortBy(function ($lec) {
                $gestion = $lec->periodo?->gestion ?? 9999;
                $mes = $lec->periodo?->mes ?? 99;
                return sprintf('%04d-%02d-%08d', $gestion, $mes, $lec->id);
            })
            ->values();

        $cuotasConvenioPendientes = ConvenioCuota::with('convenio')
            ->whereHas('convenio', fn($q) => $q->where('id_abonado', $abonado->id)->where('estado', 'VIGENTE'))
            ->where('estado_pago', 'PENDIENTE')
            ->orderBy('numero_cuota')
            ->get();

        $totalLecturas = (float) $lecturasPendientes->sum('total_facturado');
        $totalCuotas = (float) $cuotasConvenioPendientes->sum('monto_cuota');
        $deudaTotal = round($totalLecturas + $totalCuotas, 2);
        $mesesMora = $lecturasPendientes->count();

        return [
            'abonado' => $abonado,
            'lecturas_pendientes' => $lecturasPendientes,
            'cuotas_convenio_pendientes' => $cuotasConvenioPendientes,
            'total_lecturas' => $totalLecturas,
            'total_cuotas' => $totalCuotas,
            'deuda_total' => $deudaTotal,
            'meses_mora' => $mesesMora,
            'en_riesgo_corte' => ($mesesMora >= 2 && $abonado->estado_servicio !== 'CORTADO'),
            'esta_cortado' => ($abonado->estado_servicio === 'CORTADO'),
        ];
    }

    /**
     * Búsqueda predictiva de abonados para ventanilla de caja por Código, CI/NIT o Nombre/Apellido.
     * Retorna lista de coincidencias con saldo de deuda real para selección en caso de múltiples registros.
     *
     * @return array<array<string, mixed>>
     */
    public function buscarAbonadosParaCaja(string $criterio, string $tipo = 'todos'): array
    {
        $criterio = trim($criterio);
        if (strlen($criterio) < 1) {
            return [];
        }

        $codigoPad = str_pad($criterio, 5, '0', STR_PAD_LEFT);
        $query = Abonado::with(['zona', 'categoria', 'medidorActual']);

        if ($tipo === 'codigo_abonado') {
            $query->where(function ($q) use ($criterio, $codigoPad) {
                $q->where('codigo', $criterio)
                    ->orWhere('codigo', $codigoPad)
                    ->orWhere('codigo', 'like', "%{$criterio}%");
            })->orderByRaw("CASE WHEN codigo = ? THEN 1 WHEN codigo = ? THEN 2 ELSE 3 END", [$criterio, $codigoPad]);
        } elseif ($tipo === 'carnet_nit') {
            $query->where('numero_documento', 'like', "%{$criterio}%");
        } elseif ($tipo === 'numero_factura') {
            $query->where(function ($q) use ($criterio) {
                $q->whereHas('facturas', function ($qf) use ($criterio) {
                    $qf->where('numero_factura', 'like', "%{$criterio}%");
                })->orWhereHas('lecturas', function ($ql) use ($criterio) {
                    $ql->whereHas('facturaSiat', function ($qf) use ($criterio) {
                        $qf->where('numero_factura', 'like', "%{$criterio}%");
                    });
                });
            });
        } elseif ($tipo === 'cliente' || $tipo === 'nombre') {
            $query->where(function ($q) use ($criterio) {
                $q->where('nombre_completo', 'ilike', "%{$criterio}%")
                    ->orWhere('primer_apellido', 'ilike', "%{$criterio}%")
                    ->orWhere('segundo_apellido', 'ilike', "%{$criterio}%")
                    ->orWhere('nombres', 'ilike', "%{$criterio}%");
            })->orderBy('nombre_completo');
        } else {
            // Todos los campos
            $query->where(function ($q) use ($criterio, $codigoPad) {
                $q->where('codigo', $criterio)
                    ->orWhere('codigo', $codigoPad)
                    ->orWhere('codigo', 'like', "%{$criterio}%")
                    ->orWhere('numero_documento', 'like', "%{$criterio}%")
                    ->orWhere('nombre_completo', 'ilike', "%{$criterio}%")
                    ->orWhere('primer_apellido', 'ilike', "%{$criterio}%")
                    ->orWhere('segundo_apellido', 'ilike', "%{$criterio}%")
                    ->orWhere('nombres', 'ilike', "%{$criterio}%")
                    ->orWhereHas('facturas', function ($qf) use ($criterio) {
                        $qf->where('numero_factura', 'like', "%{$criterio}%");
                    });
            })->orderByRaw("CASE 
                WHEN codigo = ? THEN 1 
                WHEN codigo = ? THEN 2 
                WHEN numero_documento = ? THEN 3 
                ELSE 4 END", [$criterio, $codigoPad, $criterio]);
        }

        return $query->orderBy('codigo')
            ->limit(15)
            ->get()
            ->toArray();
    }

    /**
     * Procesa el cobro en ventanilla, emite la Factura SIAT y actualiza las lecturas/abonado.
     *
     * @param array<int> $lecturasIds
     * @param array<int> $cuotasIds
     */
    public function cobrarEnVentanilla(
        int $idAbonado,
        array $lecturasIds,
        array $cuotasIds,
        int $codigoMetodoPago,
        string $razonSocial,
        string $numeroDocumento,
        int $codigoTipoDocumento = 1,
        ?string $complemento = null,
        ?string $correoElectronico = null,
        int $idCajero = 1,
        int $idSucursal = 0,
        int $idPuntoVenta = 0
    ): array {
        if (empty($lecturasIds) && empty($cuotasIds)) {
            throw new InvalidArgumentException('Debe seleccionar al menos una lectura o cuota de convenio para cobrar.');
        }

        return DB::transaction(function () use (
            $idAbonado,
            $lecturasIds,
            $cuotasIds,
            $codigoMetodoPago,
            $razonSocial,
            $numeroDocumento,
            $codigoTipoDocumento,
            $complemento,
            $correoElectronico,
            $idCajero,
            $idSucursal,
            $idPuntoVenta
        ) {
            /** @var Abonado $abonado */
            $abonado = Abonado::findOrFail($idAbonado);

            // Identificar sesión activa de caja para este cajero
            $sesionActiva = \App\Models\Comercial\CajaSesion::with('puntoVenta')
                ->where('id_cajero', $idCajero)
                ->where('estado', 'ABIERTA')
                ->latest('id')
                ->first();

            $idSesionCaja = $sesionActiva?->id;
            if ($sesionActiva) {
                $idSucursal = (int) $sesionActiva->id_sucursal;
                $idPuntoVenta = (int) ($sesionActiva->puntoVenta?->codigo_punto_venta ?? $idPuntoVenta);
            }

            // Validar cobro en estricto orden cronológico (de la más antigua a la más moderna)
            if (!empty($lecturasIds)) {
                $todasPendientes = LecturaMensual::with('periodo')
                    ->where('id_abonado', $abonado->id)
                    ->where('estado_pago', 'PENDIENTE')
                    ->get()
                    ->sortBy(function ($lec) {
                        $gestion = $lec->periodo?->gestion ?? 9999;
                        $mes = $lec->periodo?->mes ?? 99;
                        return sprintf('%04d-%02d-%08d', $gestion, $mes, $lec->id);
                    })
                    ->values();

                $cant = count($lecturasIds);
                $esperados = $todasPendientes->take($cant)->pluck('id')->all();
                $diff = array_diff($lecturasIds, $esperados);

                if (!empty($diff)) {
                    throw new InvalidArgumentException('Las facturas deben cancelarse en orden cronológico estricto (de la más antigua a la más moderna). No puede saltear meses anteriores impagos.');
                }
            }

            $lecturas = LecturaMensual::with('periodo')
                ->whereIn('id', $lecturasIds)
                ->where('id_abonado', $abonado->id)
                ->where('estado_pago', 'PENDIENTE')
                ->get();

            $cuotas = ConvenioCuota::whereIn('id', $cuotasIds)
                ->where('estado_pago', 'PENDIENTE')
                ->get();

            // Preparar los ítems para la Factura Electrónica SIAT
            $itemsFactura = [];
            $totalMonto = 0.00;

            $totalConsumoM3 = 0.00;
            $totalAlcantarillado = 0.00;
            $totalDescuentoLey1886 = 0.00;
            $primerPeriodo = null;

            foreach ($lecturas as $lec) {
                if (!$primerPeriodo && $lec->periodo) {
                    $primerPeriodo = $lec->periodo;
                }
                $periodoNom = $lec->periodo ? $lec->periodo->periodo : 'S/P';
                $consumo = (float) $lec->consumo_m3;
                $totalConsumoM3 += $consumo;
                $totalAlcantarillado += (float) $lec->monto_alcantarillado;
                $totalDescuentoLey1886 += (float) $lec->monto_descuento_ley1886;
                $montoItem = (float) $lec->total_facturado;
                $totalMonto += $montoItem;

                $itemsFactura[] = [
                    'codigo_producto_empresa' => 'AGUA-' . $lec->id,
                    'codigo_actividad' => '360000', // Captación, tratamiento y distribución de agua
                    'codigo_producto_sin' => '99100', // Otros servicios de agua y saneamiento
                    'descripcion' => "Servicio de Agua Potable y Alcantarillado - Periodo {$periodoNom} (Consumo: {$consumo} m³)",
                    'cantidad' => 1.00,
                    'codigo_unidad_medida' => 58, // UNIDAD (SERVICIOS)
                    'precio_unitario' => $montoItem,
                    'monto_descuento' => 0.00,
                    'subtotal' => $montoItem,
                ];
            }

            foreach ($cuotas as $cuota) {
                $montoCuota = (float) $cuota->monto_cuota;
                $totalMonto += $montoCuota;

                $itemsFactura[] = [
                    'codigo_producto_empresa' => 'CONV-' . $cuota->id,
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '99100',
                    'descripcion' => "Cuota {$cuota->numero_cuota} de Convenio de Pago de Agua - Periodo {$cuota->periodo}",
                    'cantidad' => 1.00,
                    'codigo_unidad_medida' => 58,
                    'precio_unitario' => $montoCuota,
                    'monto_descuento' => 0.00,
                    'subtotal' => $montoCuota,
                ];
            }

            // 1. Emitir la Factura Fiscal directamente a través de EmisionFacturaService (Sector 13)
            $nombreMes = null;
            if ($primerPeriodo && $primerPeriodo->mes) {
                $mesesNombres = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];
                $nombreMes = $mesesNombres[$primerPeriodo->mes] ?? null;
            }

            $facturaData = [
                'id_sucursal' => $idSucursal,
                'id_punto_venta' => $idPuntoVenta,
                'id_sesion_caja' => $idSesionCaja,
                'id_abonado' => $abonado->id,
                'codigo_documento_sector' => 13, // Servicios Básicos
                'mes' => $nombreMes,
                'gestion' => $primerPeriodo ? (int) $primerPeriodo->gestion : Carbon::now()->year,
                'ciudad' => 'Patacamaya',
                'zona' => $abonado->zona?->nombre,
                'numero_medidor' => $abonado->medidorActual?->numero_serie ?? '0',
                'domicilio_cliente' => trim(($abonado->calle?->nombre ?? '') . ' ' . ($abonado->numero_vivienda ?? '')),
                'consumo_periodo' => $totalConsumoM3,
                'beneficiario_ley_1886' => $abonado->es_tercera_edad,
                'monto_descuento_ley_1886' => $totalDescuentoLey1886,
                'ajuste_no_sujeto_iva' => $totalAlcantarillado,
                'codigo_tipo_documento_identidad' => $codigoTipoDocumento,
                'numero_documento' => $numeroDocumento,
                'complemento' => $complemento,
                'nombre_razon_social' => $razonSocial,
                'correo_electronico' => $correoElectronico,
                'codigo_metodo_pago' => $codigoMetodoPago,
                'monto_total' => $totalMonto,
                'monto_total_sujeto_iva' => max(0, $totalMonto - $totalAlcantarillado),
                'monto_descuento' => $totalDescuentoLey1886,
                'items' => $itemsFactura,
            ];

            $factura = $this->emisionFacturaService->emitir($facturaData);
            $idFactura = $factura->id;

            // 2. Actualizar las lecturas pagadas
            $ahora = Carbon::now();
            foreach ($lecturas as $lec) {
                $lec->update([
                    'estado_pago' => 'PAGADO',
                    'id_factura' => $idFactura,
                    'fecha_pago' => $ahora,
                    'id_cajero' => $idCajero,
                    'id_sesion_caja' => $idSesionCaja,
                ]);
            }

            // 3. Actualizar las cuotas de convenio pagadas
            foreach ($cuotas as $cuota) {
                $cuota->update([
                    'estado_pago' => 'PAGADO',
                    'id_factura' => $idFactura,
                    'fecha_pago' => $ahora,
                    'id_cajero' => $idCajero,
                    'id_sesion_caja' => $idSesionCaja,
                ]);
            }

            // Actualizar acumulados de la sesión de caja en tiempo real
            $sesionActiva?->recalcularTotales();

            // 4. Recalcular deuda y meses de mora del abonado
            $lecturasRestantes = LecturaMensual::where('id_abonado', $abonado->id)
                ->where('estado_pago', 'PENDIENTE')
                ->get();

            $cuotasRestantes = ConvenioCuota::whereHas('convenio', fn($q) => $q->where('id_abonado', $abonado->id)->where('estado', 'VIGENTE'))
                ->where('estado_pago', 'PENDIENTE')
                ->get();

            $nuevaDeuda = round($lecturasRestantes->sum('total_facturado') + $cuotasRestantes->sum('monto_cuota'), 2);
            $nuevosMesesMora = $lecturasRestantes->count();

            $estadoAnterior = $abonado->estado_servicio;
            $nuevoEstado = $estadoAnterior;

            // Si estaba en mora y ahora tiene < 2 meses o 0, vuelve a ACTIVO
            if ($estadoAnterior === 'EN_MORA' && $nuevosMesesMora < 2) {
                $nuevoEstado = 'ACTIVO';
            }

            // Si estaba cortado y saldó toda su mora de agua
            $ordenReconexion = null;
            if ($estadoAnterior === 'CORTADO' && $nuevosMesesMora === 0) {
                $nuevoEstado = 'ACTIVO';
                $abonado->fecha_ultima_rehabilitacion = $ahora->toDateString();

                // Generar automáticamente la Orden de Trabajo de Reconexión
                $ordenReconexion = OrdenTrabajo::create([
                    'numero_orden' => 'REC-' . date('Y') . '-' . sprintf('%04d', $abonado->id),
                    'id_abonado' => $abonado->id,
                    'tipo_orden' => 'RECONEXION',
                    'motivo' => "Reconexión automática tras liquidación total de mora en Caja (Factura #{$factura->numero_factura})",
                    'fecha_programada' => $ahora->toDateString(),
                    'estado' => 'PENDIENTE',
                ]);
            }

            $abonado->update([
                'saldo_deuda' => $nuevaDeuda,
                'meses_mora' => $nuevosMesesMora,
                'estado_servicio' => $nuevoEstado,
                'fecha_ultima_rehabilitacion' => $abonado->fecha_ultima_rehabilitacion,
            ]);

            return [
                'success' => true,
                'factura' => $factura,
                'monto_total_cobrado' => $totalMonto,
                'lecturas_cobradas' => $lecturas->count(),
                'cuotas_cobradas' => $cuotas->count(),
                'saldo_restante' => $nuevaDeuda,
                'meses_mora_restantes' => $nuevosMesesMora,
                'orden_reconexion' => $ordenReconexion,
            ];
        });
    }
}
