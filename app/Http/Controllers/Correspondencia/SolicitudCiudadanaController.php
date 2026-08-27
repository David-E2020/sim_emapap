<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\SolicitudCiudadana;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SolicitudCiudadanaController extends Controller
{
    protected CiteGeneratorService $citeService;
    protected DerivacionWorkflowService $workflowService;

    public function __construct(CiteGeneratorService $citeService, DerivacionWorkflowService $workflowService)
    {
        $this->citeService = $citeService;
        $this->workflowService = $workflowService;
    }

    public function index(Request $request): JsonResponse
    {
        $estado = $request->input('estado');
        $query = SolicitudCiudadana::with('hojaRuta')->where('_estado', 'ACTIVO');

        if ($estado) {
            $query->where('estado_solicitud', $estado);
        }

        $solicitudes = $query->orderBy('id', 'desc')->paginate((int)$request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $solicitudes->items(),
            'meta' => [
                'total' => $solicitudes->total(),
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Registro público de solicitud ciudadana (desde el portal web)
     */
    public function registrarPublico(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solicitante_nombre' => 'required|string|max:200',
            'solicitante_ci_nit' => 'required|string|max:50',
            'solicitante_telefono' => 'nullable|string|max:50',
            'solicitante_correo' => 'nullable|email|max:150',
            'tipo_solicitud' => 'required|string|max:100',
            'descripcion_solicitud' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $codigo = 'SOL-' . strtoupper(Str::random(8));

        $solicitud = SolicitudCiudadana::create([
            'codigo_solicitud' => $codigo,
            'solicitante_nombre' => strtoupper(trim((string)$request->input('solicitante_nombre'))),
            'solicitante_ci_nit' => trim((string)$request->input('solicitante_ci_nit')),
            'solicitante_telefono' => $request->input('solicitante_telefono'),
            'solicitante_correo' => $request->input('solicitante_correo'),
            'tipo_solicitud' => $request->input('tipo_solicitud'),
            'descripcion_solicitud' => $request->input('descripcion_solicitud'),
            'estado_solicitud' => 'REGISTRADA',
            'fecha_solicitud' => now(),
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud ciudadana registrada con éxito.',
            'data' => $solicitud,
        ], Response::HTTP_CREATED);
    }

    /**
     * Aprobar solicitud ciudadana y convertirla en Hoja de Ruta institucional
     */
    public function convertirEnHojaRuta(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_unidad_destino' => 'required|integer',
            'id_funcionario_destino' => 'nullable|integer',
            'proveido' => 'nullable|string',
            'dias_plazo' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $solicitud = SolicitudCiudadana::findOrFail($id);
        $cite = $this->citeService->generarCiteHojaRuta(null, (int)date('Y'));

        $hojaRuta = HojaRuta::create([
            'nro_hoja_ruta' => $cite,
            'gestion' => (int)date('Y'),
            'tipo_hr' => 'EXTERNA',
            'origen' => 'VENTANILLA_DIGITAL',
            'asunto' => "SOLICITUD CIUDADANA ({$solicitud->codigo_solicitud}): " . substr($solicitud->descripcion_solicitud, 0, 300),
            'remitente_externo' => "{$solicitud->solicitante_nombre} (CI: {$solicitud->solicitante_ci_nit})",
            'prioridad' => 'MEDIA',
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now(),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        // Derivar
        $this->workflowService->derivar([
            'id_hoja_ruta' => $hojaRuta->id,
            'proveido' => $request->input('proveido', 'PARA SU ATENCIÓN Y RESPUESTA'),
            'instruccion_detalle' => 'Trámite digital ingresado por Portal Ciudadano.',
            'dias_plazo' => (int)$request->input('dias_plazo', 3),
            'destinatarios' => [
                [
                    'id_unidad_destino' => $request->input('id_unidad_destino'),
                    'id_funcionario_destino' => $request->input('id_funcionario_destino'),
                    'es_copia' => false,
                ]
            ],
        ]);

        $solicitud->estado_solicitud = 'HOJA_RUTA_GENERADA';
        $solicitud->id_hoja_ruta_generada = $hojaRuta->id;
        $solicitud->fecha_atencion = now();
        $solicitud->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud ciudadana admitida y Hoja de Ruta generada.',
            'data' => [
                'solicitud' => $solicitud,
                'hoja_ruta' => $hojaRuta,
            ],
        ], Response::HTTP_OK);
    }
}
