<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\ReciboCaja;
use App\Services\Comercial\CobranzaAguaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CobranzaCajaController extends Controller
{
    public function __construct(
        protected CobranzaAguaService $cobranzaService
    ) {}

    /**
     * Consulta el estado de cuenta y facturas pendientes de un abonado para ventanilla.
     */
    public function estadoCuenta(string $codigo): JsonResponse
    {
        try {
            $estado = $this->cobranzaService->obtenerEstadoCuenta($codigo);

            return response()->json([
                'success' => true,
                'data' => $estado,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró al abonado con código: ' . $codigo,
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Búsqueda predictiva de abonados para ventanilla por Código, CI/NIT o Nombre.
     */
    public function buscarAbonados(Request $request): JsonResponse
    {
        $criterio = (string) $request->input('q', '');
        $resultados = $this->cobranzaService->buscarAbonadosParaCaja($criterio);

        return response()->json([
            'success' => true,
            'data' => $resultados,
            'total' => count($resultados),
        ], Response::HTTP_OK);
    }

    /**
     * Procesa el cobro en caja en ventanilla y emite la Factura Electrónica SIAT.
     */
    public function cobrar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_abonado' => 'required|integer|exists:pgsql.comercial.abonados,id',
            'lecturas_ids' => 'nullable|array',
            'cuotas_ids' => 'nullable|array',
            'codigo_metodo_pago' => 'required|integer', // 1=Efectivo, 2=Tarjeta, 7=Transferencia/QR
            'nombre_razon_social' => 'required|string|max:200',
            'numero_documento' => 'required|string|max:50',
            'codigo_tipo_documento_identidad' => 'required|integer',
            'complemento' => 'nullable|string|max:5',
            'correo_electronico' => 'nullable|email|max:150',
            'id_sucursal' => 'nullable|integer',
            'id_punto_venta' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $resultado = $this->cobranzaService->cobrarEnVentanilla(
                (int) $request->input('id_abonado'),
                (array) $request->input('lecturas_ids', []),
                (array) $request->input('cuotas_ids', []),
                (int) $request->input('codigo_metodo_pago'),
                (string) $request->input('nombre_razon_social'),
                (string) $request->input('numero_documento'),
                (int) $request->input('codigo_tipo_documento_identidad', 1),
                $request->input('complemento'),
                $request->input('correo_electronico'),
                $request->user()?->id ?? 1,
                (int) $request->input('id_sucursal', 0),
                (int) $request->input('id_punto_venta', 0)
            );

            return response()->json([
                'success' => true,
                'message' => 'Cobro procesado exitosamente y Factura SIAT emitida.',
                'data' => $resultado,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el cobro: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Emisión de Recibo de Caja para conceptos no sujetos a crédito fiscal (ej: derechos de conexión, aportes).
     */
    public function emitirReciboCaja(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_abonado' => 'nullable|integer|exists:pgsql.comercial.abonados,id',
            'nombre_cliente' => 'required|string|max:150',
            'documento_cliente' => 'nullable|string|max:25',
            'concepto_tipo' => 'required|in:DERECHO_CONEXION,INSTALACION,RECONEXION,CAMBIO_MEDIDOR,APORTE',
            'descripcion' => 'required|string',
            'monto_total' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $correlativo = ReciboCaja::count() + 1;
        $numeroRecibo = sprintf('REC-%s-%05d', date('Y'), $correlativo);

        $recibo = ReciboCaja::create(array_merge($request->all(), [
            'numero_recibo' => $numeroRecibo,
            'id_cajero' => $request->user()?->id ?? 1,
            'estado' => 'VALIDO',
        ]));

        return response()->json([
            'success' => true,
            'message' => "Recibo de caja {$numeroRecibo} emitido correctamente.",
            'data' => $recibo,
        ], Response::HTTP_CREATED);
    }

    /**
     * Descarga del Aviso de Cobranza mensual (Prefactura) en PDF.
     */
    public function descargarAvisoCobranza(
        int $idLectura,
        \App\Services\Comercial\DocumentoComercialPdfService $pdfService
    ): \Illuminate\Http\Response {
        $lectura = \App\Models\Comercial\LecturaMensual::findOrFail($idLectura);
        $pdf = $pdfService->generarAvisoCobranzaPdf($lectura);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Aviso_Cobranza_{$lectura->abonado?->codigo}.pdf\"",
        ]);
    }

    /**
     * Descarga del Recibo de Caja en PDF.
     */
    public function descargarReciboCaja(
        int $idRecibo,
        \App\Services\Comercial\DocumentoComercialPdfService $pdfService
    ): \Illuminate\Http\Response {
        $recibo = \App\Models\Comercial\ReciboCaja::findOrFail($idRecibo);
        $pdf = $pdfService->generarReciboCajaPdf($recibo);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Recibo_Caja_{$recibo->numero_recibo}.pdf\"",
        ]);
    }
}

