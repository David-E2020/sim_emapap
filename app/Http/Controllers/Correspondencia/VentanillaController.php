<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\Ventanilla;
use App\Models\Rrhh\Entidad;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class VentanillaController extends Controller
{
    protected CiteGeneratorService $citeService;
    protected DerivacionWorkflowService $workflowService;

    public function __construct(CiteGeneratorService $citeService, DerivacionWorkflowService $workflowService)
    {
        $this->citeService = $citeService;
        $this->workflowService = $workflowService;
    }

    /**
     * Listar ventanillas habilitadas
     */
    public function index(): JsonResponse
    {
        $ventanillas = Ventanilla::with(['regional', 'unidadOrganizacional'])->where('_estado', 'ACTIVO')->get();
        return response()->json(['success' => true, 'data' => $ventanillas], Response::HTTP_OK);
    }

    /**
     * Registro de correspondencia externa entrante (Ventanilla Única)
     */
    public function registrarEntrada(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'remitente_externo' => 'required|string|max:255',
            'asunto' => 'required|string|max:500',
            'referencia' => 'nullable|string|max:500',
            'nro_fojas' => 'nullable|integer|min:1',
            'nro_anexos' => 'nullable|integer|min:0',
            'id_ventanilla' => 'nullable|integer',
            'id_unidad_destino' => 'required|integer',
            'id_funcionario_destino' => 'nullable|integer',
            'proveido' => 'nullable|string',
            'dias_plazo' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idVentanilla = $request->input('id_ventanilla');
        $ventanilla = $idVentanilla ? Ventanilla::find($idVentanilla) : null;
        $idRegional = $ventanilla ? $ventanilla->id_regional : null;

        $cite = $this->citeService->generarCiteHojaRuta($idRegional, (int)date('Y'));

        $hojaRuta = HojaRuta::create([
            'nro_hoja_ruta' => $cite,
            'gestion' => (int)date('Y'),
            'tipo_hr' => 'EXTERNA',
            'origen' => $ventanilla && $ventanilla->tipo_atencion === 'DIGITAL' ? 'VENTANILLA_DIGITAL' : 'VENTANILLA_FISICA',
            'asunto' => strtoupper(trim((string)$request->input('asunto'))),
            'referencia' => $request->input('referencia'),
            'remitente_externo' => strtoupper(trim((string)$request->input('remitente_externo'))),
            'id_ventanilla_origen' => $idVentanilla,
            'prioridad' => $request->input('prioridad', 'ALTA'),
            'nro_fojas' => (int)$request->input('nro_fojas', 1),
            'nro_anexos' => (int)$request->input('nro_anexos', 0),
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now(),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        // Realizar primera derivación automática hacia la unidad receptora
        $this->workflowService->derivar([
            'id_hoja_ruta' => $hojaRuta->id,
            'proveido' => $request->input('proveido', 'PASE A SUS EFECTOS'),
            'instruccion_detalle' => 'Ingreso por Ventanilla Única de Correspondencia Externa.',
            'dias_plazo' => (int)$request->input('dias_plazo', 2),
            'destinatarios' => [
                [
                    'id_unidad_destino' => $request->input('id_unidad_destino'),
                    'id_funcionario_destino' => $request->input('id_funcionario_destino'),
                    'es_copia' => false,
                ]
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correspondencia externa registrada y derivada exitosamente.',
            'data' => $hojaRuta->fresh(['derivaciones']),
        ], Response::HTTP_CREATED);
    }
}
