<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\CajaMovimiento;
use App\Models\Comercial\CajaSesion;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Comercial\ReporteArqueoCajaPdfService;
use App\Services\Facturacion\SiatSoapService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CajaSesionController extends Controller
{
    public function __construct(
        private readonly ReporteArqueoCajaPdfService $reportePdfService,
        private readonly SiatSoapService $siatSoapService
    ) {}

    /**
     * Devuelve el estado actual de la sesión del usuario autenticado.
     */
    public function estadoActual(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;

        $sesionActiva = CajaSesion::with([
            'puntoVenta',
            'sucursal',
            'cajero',
            'facturas' => function ($q) {
                $q->with('abonado')
                    ->where('estado_factura', '!=', 'ANULADA')
                    ->latest('id')
                    ->limit(10);
            },
        ])
            ->where('id_cajero', $userId)
            ->where('estado', 'ABIERTA')
            ->latest('id')
            ->first();

        if ($sesionActiva) {
            $sesionActiva->recalcularTotales();
            $sesionActiva->refresh();
            $sesionActiva->load([
                'puntoVenta',
                'sucursal',
                'cajero',
                'facturas' => function ($q) {
                    $q->with('abonado')
                        ->where('estado_factura', '!=', 'ANULADA')
                        ->latest('id')
                        ->limit(10);
                },
            ]);

            $fechaApertura = $sesionActiva->fecha_apertura ? Carbon::parse($sesionActiva->fecha_apertura) : null;
            $esDiaAnterior = $fechaApertura ? !$fechaApertura->isToday() : false;

            return response()->json([
                'success' => true,
                'tiene_sesion_activa' => true,
                'es_dia_anterior' => $esDiaAnterior,
                'fecha_apertura_legible' => $fechaApertura ? $fechaApertura->format('d/m/Y H:i:s') : null,
                'sesion' => $sesionActiva,
            ], Response::HTTP_OK);
        }

        // Si no tiene sesión activa, buscar si tiene una caja asignada por defecto
        $cajaAsignada = SiatPuntoVenta::where('id_cajero_defecto', $userId)
            ->where('_estado', 'ACTIVO')
            ->first();

        return response()->json([
            'success' => true,
            'tiene_sesion_activa' => false,
            'caja_defecto_id' => $cajaAsignada?->id,
            'sesion' => null,
        ], Response::HTTP_OK);
    }

    /**
     * Lista todas las cajas (Puntos de Venta) con su disponibilidad para ser abiertas.
     */
    public function cajasDisponibles(Request $request): JsonResponse
    {
        $cajas = SiatPuntoVenta::with(['sucursal', 'cajeroDefecto'])
            ->where('_estado', 'ACTIVO')
            ->orderBy('codigo_punto_venta')
            ->get();

        $cajasMapeadas = $cajas->map(function ($caja) {
            $sesionActiva = CajaSesion::with('cajero')
                ->where('id_punto_venta', $caja->id)
                ->where('estado', 'ABIERTA')
                ->latest('id')
                ->first();

            return [
                'id' => $caja->id,
                'codigo_punto_venta' => $caja->codigo_punto_venta,
                'nombre' => $caja->nombre,
                'descripcion' => $caja->descripcion,
                'id_sucursal' => $caja->id_sucursal,
                'sucursal_nombre' => $caja->sucursal?->nombre,
                'cajero_defecto' => $caja->cajeroDefecto ? [
                    'id' => $caja->cajeroDefecto->id,
                    'nombre' => $caja->cajeroDefecto->name,
                ] : null,
                'id_cajero_defecto' => $caja->id_cajero_defecto,
                'esta_abierta' => $sesionActiva !== null,
                'sesion_activa' => $sesionActiva ? [
                    'id' => $sesionActiva->id,
                    'numero_sesion' => $sesionActiva->numero_sesion,
                    'cajero_nombre' => $sesionActiva->cajero?->name,
                    'fecha_apertura' => $sesionActiva->fecha_apertura?->format('H:i d/m/Y'),
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $cajasMapeadas,
        ], Response::HTTP_OK);
    }

    /**
     * Apertura de turno de caja.
     */
    public function abrir(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_punto_venta' => 'required|integer|exists:pgsql.facturacion.puntos_venta,id',
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones_apertura' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userId = $request->user()?->id ?? 1;

        // 1. Verificar si el usuario ya tiene una sesión abierta
        $sesionExistente = CajaSesion::where('id_cajero', $userId)
            ->where('estado', 'ABIERTA')
            ->first();

        if ($sesionExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya cuenta con un turno abierto activo (' . $sesionExistente->numero_sesion . '). Debe cerrarlo antes de abrir otro.',
            ], Response::HTTP_CONFLICT);
        }

        // 2. Verificar si la caja ya está abierta por otro cajero
        $cajaOcupada = CajaSesion::with('cajero')
            ->where('id_punto_venta', $request->input('id_punto_venta'))
            ->where('estado', 'ABIERTA')
            ->first();

        if ($cajaOcupada) {
            $nombreOtro = $cajaOcupada->cajero?->name ?? 'otro funcionario';
            return response()->json([
                'success' => false,
                'message' => "Esta caja ya se encuentra abierta por {$nombreOtro} ({$cajaOcupada->numero_sesion}).",
            ], Response::HTTP_CONFLICT);
        }

        $puntoVenta = SiatPuntoVenta::with('sucursal')->findOrFail($request->input('id_punto_venta'));
        $sucursal = $puntoVenta->sucursal ?: SiatSucursal::first();

        // 3. Validar / renovar CUFD para este punto de venta ante el SIAT
        $cufdVigente = SiatCufd::where('id_sucursal', $sucursal->id)
            ->where('id_punto_venta', $puntoVenta->id)
            ->where('fecha_vigencia', '>', Carbon::now())
            ->latest('id')
            ->first();

        if (!$cufdVigente) {
            $cuis = SiatCuis::where('id_sucursal', $sucursal->id)
                ->where('id_punto_venta', $puntoVenta->id)
                ->where('fecha_vigencia', '>', Carbon::now())
                ->latest('id')
                ->first();
            $cuisCodigo = $cuis ? $cuis->codigo : 'CUIS_EMAPAP_DEFAULT';

            $respCufd = $this->siatSoapService->solicitarCufd($cuisCodigo, (int) $sucursal->codigo_sucursal, (int) $puntoVenta->codigo_punto_venta);
            if (!empty($respCufd['success'])) {
                SiatCufd::create([
                    'id_sucursal' => $sucursal->id,
                    'id_punto_venta' => $puntoVenta->id,
                    'codigo' => $respCufd['cufd'],
                    'codigo_control' => $respCufd['codigo_control'],
                    'direccion' => $respCufd['direccion'] ?? $sucursal->direccion,
                    'fecha_vigencia' => Carbon::parse($respCufd['fecha_vigencia']),
                ]);
            }
        }

        // 4. Generar correlativo
        $year = date('Y');
        $countYear = CajaSesion::whereYear('fecha_apertura', $year)->count() + 1;
        $numeroSesion = sprintf('TURNO-%s-%05d', $year, $countYear);

        $montoApertura = (float) $request->input('monto_apertura', 0.00);

        $sesion = CajaSesion::create([
            'numero_sesion' => $numeroSesion,
            'id_cajero' => $userId,
            'id_sucursal' => $sucursal->id,
            'id_punto_venta' => $puntoVenta->id,
            'fecha_apertura' => Carbon::now(),
            'monto_apertura' => $montoApertura,
            'monto_ventas_efectivo' => 0.00,
            'monto_ventas_qr_banco' => 0.00,
            'monto_ingresos_extra' => 0.00,
            'monto_egresos_extra' => 0.00,
            'monto_esperado_efectivo' => $montoApertura,
            'estado' => 'ABIERTA',
            'observaciones_apertura' => $request->input('observaciones_apertura'),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'ABRIR_TURNO',
            '_usuario_creacion' => $userId,
        ]);

        $sesion->load(['puntoVenta', 'sucursal', 'cajero']);

        return response()->json([
            'success' => true,
            'message' => "Turno {$numeroSesion} abierto exitosamente en {$puntoVenta->nombre}.",
            'data' => $sesion,
        ], Response::HTTP_CREATED);
    }

    /**
     * Resumen de arqueo en tiempo real de la sesión activa del cajero.
     */
    public function resumenArqueo(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? 1;

        $sesion = CajaSesion::with(['puntoVenta', 'sucursal', 'cajero', 'movimientos'])
            ->where('id_cajero', $userId)
            ->where('estado', 'ABIERTA')
            ->latest('id')
            ->first();

        if (!$sesion) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene un turno de caja abierto en este momento.',
            ], Response::HTTP_NOT_FOUND);
        }

        $sesion->recalcularTotales();
        $sesion->refresh();

        // Desglose por rubros
        $aguaEfectivo = (float) $sesion->lecturas()
            ->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', 1))
            ->sum('total_facturado');

        $aguaQr = (float) $sesion->lecturas()
            ->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', '!=', 1))
            ->sum('total_facturado');

        $cuotasEfectivo = (float) $sesion->cuotas()
            ->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', 1))
            ->sum('monto_cuota');

        $cuotasQr = (float) $sesion->cuotas()
            ->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', '!=', 1))
            ->sum('monto_cuota');

        $recibosEfectivo = (float) $sesion->recibos()
            ->where('estado', 'VALIDO')
            ->sum('monto_total');

        return response()->json([
            'success' => true,
            'data' => [
                'sesion' => $sesion,
                'totales' => [
                    'monto_apertura' => $sesion->monto_apertura,
                    'agua_efectivo' => $aguaEfectivo,
                    'agua_qr' => $aguaQr,
                    'cuotas_efectivo' => $cuotasEfectivo,
                    'cuotas_qr' => $cuotasQr,
                    'recibos_efectivo' => $recibosEfectivo,
                    'total_efectivo' => $sesion->monto_ventas_efectivo,
                    'total_qr_banco' => $sesion->monto_ventas_qr_banco,
                    'total_ingresos_extra' => $sesion->monto_ingresos_extra,
                    'total_egresos_extra' => $sesion->monto_egresos_extra,
                    'monto_esperado_efectivo' => $sesion->monto_esperado_efectivo,
                    'cantidad_lecturas' => $sesion->lecturas()->count(),
                    'cantidad_cuotas' => $sesion->cuotas()->count(),
                    'cantidad_recibos' => $sesion->recibos()->where('estado', 'VALIDO')->count(),
                    'cantidad_facturas' => $sesion->facturas()->count(),
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Cierre de turno y arqueo de caja.
     */
    public function cerrar(Request $request): JsonResponse
    {
        $declaradoInput = $request->input('monto_cierre_declarado') ?? $request->input('monto_cierre_fisico');
        $desgloseInput = $request->input('desglose_billetes') ?? $request->input('desglose_efectivo');

        $validator = Validator::make([
            'id_sesion' => $request->input('id_sesion'),
            'monto_cierre_declarado' => $declaradoInput,
            'desglose_billetes' => $desgloseInput,
            'observaciones_cierre' => $request->input('observaciones_cierre'),
        ], [
            'id_sesion' => 'required|integer|exists:pgsql.comercial.caja_sesiones,id',
            'monto_cierre_declarado' => 'required|numeric|min:0',
            'desglose_billetes' => 'nullable|array',
            'observaciones_cierre' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sesion = CajaSesion::findOrFail($request->input('id_sesion'));

        if ($sesion->estado !== 'ABIERTA') {
            return response()->json([
                'success' => false,
                'message' => 'Esta sesión ya se encuentra cerrada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $sesion->recalcularTotales();
        $sesion->refresh();

        $declarado = (float) $declaradoInput;
        $esperado = (float) $sesion->monto_esperado_efectivo;
        $diferencia = round($declarado - $esperado, 2);

        $userId = $request->user()?->id ?? 1;

        $sesion->update([
            'fecha_cierre' => Carbon::now(),
            'monto_cierre_declarado' => $declarado,
            'diferencia' => $diferencia,
            'desglose_billetes' => $desgloseInput ?? [],
            'observaciones_cierre' => $request->input('observaciones_cierre'),
            'id_supervisor_cierre' => $userId,
            'estado' => 'CERRADA',
            '_transaccion' => 'CERRAR_TURNO',
            '_usuario_modificacion' => $userId,
            '_fecha_modificacion' => Carbon::now(),
        ]);

        $sesion->load(['puntoVenta', 'sucursal', 'cajero']);

        return response()->json([
            'success' => true,
            'message' => "Turno {$sesion->numero_sesion} cerrado exitosamente. Arqueo completado.",
            'data' => $sesion,
            'diferencia' => $diferencia,
        ], Response::HTTP_OK);
    }

    /**
     * Registrar entrada o salida menor de caja chica durante el turno.
     */
    public function registrarMovimiento(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_sesion' => 'required|integer|exists:pgsql.comercial.caja_sesiones,id',
            'tipo' => 'required|in:INGRESO,EGRESO',
            'concepto' => 'required|string|max:150',
            'monto' => 'required|numeric|min:0.01',
            'beneficiario' => 'nullable|string|max:150',
            'comprobante' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sesion = CajaSesion::findOrFail($request->input('id_sesion'));
        if ($sesion->estado !== 'ABIERTA') {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden registrar movimientos en una caja cerrada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $userId = $request->user()?->id ?? 1;

        $movimiento = CajaMovimiento::create([
            'id_sesion' => $sesion->id,
            'tipo' => $request->input('tipo'),
            'concepto' => mb_strtoupper($request->input('concepto')),
            'monto' => (float) $request->input('monto'),
            'beneficiario' => $request->input('beneficiario'),
            'comprobante' => $request->input('comprobante'),
            'observaciones' => $request->input('observaciones'),
            'fecha' => Carbon::now(),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'MOV_CAJA',
            '_usuario_creacion' => $userId,
        ]);

        $sesion->recalcularTotales();

        return response()->json([
            'success' => true,
            'message' => "Movimiento de {$movimiento->tipo} registrado correctamente.",
            'data' => $movimiento,
        ], Response::HTTP_CREATED);
    }

    /**
     * Descarga de la Planilla Oficial de Arqueo en PDF.
     */
    public function descargarReportePdf(int $id): Response
    {
        $sesion = CajaSesion::findOrFail($id);
        $pdf = $this->reportePdfService->generarPdf($sesion);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Arqueo_Caja_{$sesion->numero_sesion}.pdf\"",
        ]);
    }

    /**
     * Asigna un cajero habitual por defecto a un Punto de Venta / Caja física.
     */
    public function asignarCajeroDefecto(Request $request, int $idPuntoVenta): JsonResponse
    {
        $idCajero = $request->input('id_cajero') ?? $request->input('id_cajero_defecto');

        $validator = Validator::make(['id_cajero' => $idCajero], [
            'id_cajero' => 'nullable|integer|exists:pgsql.users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $punto = SiatPuntoVenta::findOrFail($idPuntoVenta);
        $punto->update([
            'id_cajero_defecto' => $idCajero,
            '_usuario_modificacion' => $request->user()?->id ?? 1,
            '_fecha_modificacion' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cajero habitual asignado a la caja correctamente.',
            'data' => $punto,
        ], Response::HTTP_OK);
    }

    /**
     * Historial de sesiones / turnos de caja para auditoría y supervisión.
     */
    public function historial(Request $request): JsonResponse
    {
        $query = CajaSesion::with(['puntoVenta', 'sucursal', 'cajero', 'supervisor'])
            ->orderByDesc('id');

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('id_cajero')) {
            $query->where('id_cajero', $request->input('id_cajero'));
        }
        if ($request->filled('id_punto_venta')) {
            $query->where('id_punto_venta', $request->input('id_punto_venta'));
        }

        $sesiones = $query->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sesiones,
        ], Response::HTTP_OK);
    }

    /**
     * Lista de usuarios disponibles para ser asignados como cajeros.
     */
    public function listarCajeros(): JsonResponse
    {
        $usuarios = \App\Models\User::select('id', 'name', 'usr_usuario')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $usuarios,
        ], Response::HTTP_OK);
    }
}
