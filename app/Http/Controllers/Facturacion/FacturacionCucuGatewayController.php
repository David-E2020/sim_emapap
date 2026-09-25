<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\TransaccionQr;
use App\Services\Facturacion\CobroQrSimpleService;
use App\Services\Facturacion\SiatSoapService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class FacturacionCucuGatewayController extends Controller
{
    public function __construct(
        protected CobroQrSimpleService $cobroQrService,
        protected SiatSoapService $siatSoapService
    ) {}

    /**
     * Genera un Cobro QR Interoperable (Simple QR - BCB/ASOBAN).
     */
    public function generarQr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'monto' => 'required|numeric|min:0.10',
            'glosa' => 'required|string|max:150',
            'id_abonado' => 'nullable|integer',
            'id_caja_sesion' => 'nullable|integer',
            'minutos_vigencia' => 'nullable|integer|min:1|max:60',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $transaccion = $this->cobroQrService->generarTransaccionQr($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaccion->id,
                    'uuid' => $transaccion->uuid,
                    'monto' => (float) $transaccion->monto,
                    'moneda' => $transaccion->moneda,
                    'glosa' => $transaccion->glosa,
                    'qr_payload' => $transaccion->qr_payload,
                    'qr_imagen_base64' => $transaccion->qr_imagen_base64,
                    'banco_destino' => $transaccion->banco_destino,
                    'cuenta_destino' => $transaccion->cuenta_destino,
                    'estado' => $transaccion->estado,
                    'expira_at' => $transaccion->expira_at->toIso8601String(),
                ],
                'message' => 'Cobro QR generado exitosamente.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar código QR de cobro: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Polling de estado del QR en tiempo real para la pantalla del cajero.
     */
    public function consultarEstadoQr(string $uuid): JsonResponse
    {
        $estado = $this->cobroQrService->consultarEstado($uuid);

        if (!$estado['success']) {
            return response()->json($estado, Response::HTTP_NOT_FOUND);
        }

        return response()->json($estado, Response::HTTP_OK);
    }

    /**
     * Confirmación de pago QR (manual en ventanilla o desde terminal).
     */
    public function confirmarPagoQr(Request $request, string $uuid): JsonResponse
    {
        try {
            $transaccionId = $request->input('transaccion_banco_id');
            $banco = $request->input('banco_origen', 'Banca Móvil - Simple QR');

            $tx = $this->cobroQrService->confirmarPago($uuid, $transaccionId, $banco);

            return response()->json([
                'success' => true,
                'data' => $tx,
                'message' => 'Pago confirmado exitosamente.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Webhook bancario para recepción de pagos QR automáticos.
     */
    public function webhookBancoQr(Request $request): JsonResponse
    {
        $uuid = $request->input('uuid') ?? $request->input('reference_id');
        $transaccionBancoId = $request->input('transaction_id') ?? $request->input('auth_code');
        $banco = $request->input('bank_name') ?? 'Switch BCB Interbancario';

        if (!$uuid) {
            return response()->json(['success' => false, 'message' => 'Falta el identificador de transacción.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->cobroQrService->confirmarPago($uuid, $transaccionBancoId, $banco);
            Log::info("Pago QR confirmado por webhook bancario: UUID {$uuid}, Ref: {$transaccionBancoId}");

            return response()->json(['success' => true, 'message' => 'Webhook procesado.'], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error("Error en webhook de cobro QR: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Anulación Administrativa fuera de plazo autorizada por el SIN (RND 102600000025).
     */
    public function anularAdministrativa(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo_motivo' => 'required|integer',
            'nro_resolucion' => 'required|string|max:100',
            'fecha_resolucion' => 'required|date',
            'justificacion' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var Factura|null $factura */
        $factura = Factura::with(['sucursal', 'puntoVenta'])->find($id);
        if (!$factura) {
            return response()->json(['success' => false, 'message' => 'Factura no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if ($factura->estado_factura === Factura::ESTADO_CANCELLED) {
            return response()->json(['success' => false, 'message' => 'La factura ya se encuentra anulada.'], Response::HTTP_BAD_REQUEST);
        }

        $motivo = (int) $request->input('codigo_motivo');
        $nroResolucion = trim((string) $request->input('nro_resolucion'));
        $fechaResolucion = trim((string) $request->input('fecha_resolucion'));
        $cuis = 'CUIS_EMAPAP_GENERAL';

        $cufd = !empty($factura->cufd) ? (string) $factura->cufd : 'CUFD_EMAPAP_VIGENTE';

        $resp = $this->siatSoapService->anularFacturaAdministrativa(
            $factura->cuf,
            $motivo,
            $nroResolucion,
            $fechaResolucion,
            $cuis,
            $cufd,
            $factura->sucursal->codigo_sucursal ?? 0,
            $factura->puntoVenta->codigo_punto_venta ?? 0,
            (int) ($factura->codigo_documento_sector ?? 1),
            (int) ($factura->tipo_factura_documento ?? 1)
        );

        $factura->update([
            'estado_factura' => Factura::ESTADO_CANCELLED,
            'codigo_motivo_anulacion' => $motivo,
            'fecha_anulacion' => Carbon::now(),
            'es_anulacion_administrativa' => true,
            'nro_resolucion_administrativa' => $nroResolucion,
            'fecha_resolucion_administrativa' => $fechaResolucion,
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => Carbon::now(),
            '_transaccion' => 'ANULAR_ADM',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Factura anulada administrativamente con éxito al amparo de la RND 102600000025 (Resolución N° {$nroResolucion}).",
            'data' => $factura,
        ], Response::HTTP_OK);
    }

    /**
     * Reversión de Anulación (Restaura la factura a estado VALIDADA ante el SIN).
     */
    public function revertirAnulacion(Request $request, int $id): JsonResponse
    {
        /** @var Factura|null $factura */
        $factura = Factura::with(['sucursal', 'puntoVenta'])->find($id);
        if (!$factura) {
            return response()->json(['success' => false, 'message' => 'Factura no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if ($factura->estado_factura !== Factura::ESTADO_CANCELLED && $factura->estado_factura !== 'ANULADA') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden revertir facturas que se encuentren en estado ANULADA.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $cuis = 'CUIS_EMAPAP_GENERAL';
        $cufd = !empty($factura->cufd) ? (string) $factura->cufd : 'CUFD_EMAPAP_VIGENTE';

        $resp = $this->siatSoapService->revertirAnulacionFactura(
            $factura->cuf,
            $cuis,
            $cufd,
            $factura->sucursal->codigo_sucursal ?? 0,
            $factura->puntoVenta->codigo_punto_venta ?? 0,
            (int) ($factura->codigo_documento_sector ?? 1),
            (int) ($factura->tipo_factura_documento ?? 1)
        );

        $factura->update([
            'estado_factura' => Factura::ESTADO_VALIDATED,
            'codigo_motivo_anulacion' => null,
            'reversion_anulacion_fecha' => Carbon::now(),
            'reversion_anulacion_usuario' => auth()->id() ?? 1,
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => Carbon::now(),
            '_transaccion' => 'REVERTIR_ANUL',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Factura restituida exitosamente al estado VALIDADA.',
            'data' => $factura,
        ], Response::HTTP_OK);
    }

    /**
     * Métricas en tiempo real homologadas con CUCU Dashboard.
     */
    public function metricasDashboard(): JsonResponse
    {
        $hoy = Carbon::today();

        $stats = DB::table('facturacion.facturas')
            ->whereDate('fecha_emision', $hoy)
            ->selectRaw("
                COUNT(*) as total_emitidas,
                COUNT(CASE WHEN estado_factura IN ('VALIDADA', 'VALIDATED') THEN 1 END) as validadas,
                COUNT(CASE WHEN estado_factura IN ('CONTINGENCIA', 'CONTINGENCY', 'OFFLINE') THEN 1 END) as contingencias,
                COUNT(CASE WHEN estado_factura IN ('ANULADA', 'CANCELLED') THEN 1 END) as anuladas,
                COUNT(CASE WHEN estado_factura IN ('RECHAZADA', 'REJECTED') THEN 1 END) as rechazadas,
                COALESCE(SUM(CASE WHEN estado_factura NOT IN ('ANULADA', 'CANCELLED') THEN monto_total ELSE 0 END), 0) as total_monto,
                COALESCE(AVG(tiempo_respuesta_ms), 0) as latencia_promedio_ms
            ")
            ->first();

        $qrStats = DB::table('facturacion.transacciones_qr')
            ->whereDate('created_at', $hoy)
            ->selectRaw("
                COUNT(*) as qr_total,
                COUNT(CASE WHEN estado = 'COMPLETED' THEN 1 END) as qr_completados,
                COALESCE(SUM(CASE WHEN estado = 'COMPLETED' THEN monto ELSE 0 END), 0) as qr_monto_total
            ")
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'fecha' => $hoy->toDateString(),
                'facturacion' => [
                    'total_emitidas' => (int) ($stats->total_emitidas ?? 0),
                    'validadas' => (int) ($stats->validadas ?? 0),
                    'contingencias' => (int) ($stats->contingencias ?? 0),
                    'anuladas' => (int) ($stats->anuladas ?? 0),
                    'rechazadas' => (int) ($stats->rechazadas ?? 0),
                    'total_monto' => round((float) ($stats->total_monto ?? 0), 2),
                    'latencia_promedio_ms' => round((float) ($stats->latencia_promedio_ms ?? 0), 0),
                ],
                'cobros_qr' => [
                    'total_generados' => (int) ($qrStats->qr_total ?? 0),
                    'completados' => (int) ($qrStats->qr_completados ?? 0),
                    'monto_total' => round((float) ($qrStats->qr_monto_total ?? 0), 2),
                ],
            ],
        ], Response::HTTP_OK);
    }
}
