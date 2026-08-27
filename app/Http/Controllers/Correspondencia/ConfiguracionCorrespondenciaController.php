<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Correlativo;
use App\Models\Correspondencia\PermisoDerivacion;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Correspondencia\UsuarioSecretario;
use App\Models\Parametrica;
use App\Models\Rrhh\Puesto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ConfiguracionCorrespondenciaController extends Controller
{
    /**
     * Listar todas las plantillas y formatos
     */
    public function plantillas(): JsonResponse
    {
        $plantillas = PlantillaDocumento::with('componentes')->where('_estado', 'ACTIVO')->get();
        return response()->json(['success' => true, 'data' => $plantillas], Response::HTTP_OK);
    }

    /**
     * Listar estado de correlativos anuales
     */
    public function correlativos(Request $request): JsonResponse
    {
        $gestion = (int)$request->input('gestion', date('Y'));
        $correlativos = Correlativo::with('unidadOrganizacional')
            ->where('gestion', $gestion)
            ->where('_estado', 'ACTIVO')
            ->orderBy('id_unidad_organizacional')
            ->get();

        return response()->json(['success' => true, 'data' => $correlativos], Response::HTTP_OK);
    }

    /**
     * Listar catálogo de proveídos institucionales
     */
    public function proveidos(): JsonResponse
    {
        $proveidos = Parametrica::where('param_tabla', 'TABLA_LONDRA_PROVEIDOS')
            ->where('param_estado', 'A')
            ->orderBy('param_valor')
            ->get();

        return response()->json(['success' => true, 'data' => $proveidos], Response::HTTP_OK);
    }

    /**
     * Listar secretarios y asistentes ejecutivos asignados
     */
    public function secretarios(): JsonResponse
    {
        $secretarios = UsuarioSecretario::with(['user.persona', 'puestoTitular.unidadOrganizacional'])
            ->where('_estado', 'ACTIVO')
            ->get();

        return response()->json(['success' => true, 'data' => $secretarios], Response::HTTP_OK);
    }

    /**
     * Asignar nuevo secretario/asistente delegado
     */
    public function storeSecretario(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_usuario' => 'required|integer|exists:users,id',
            'id_puesto_titular' => 'required|integer|exists:rrhh.puestos,id',
            'permisos' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $secretario = UsuarioSecretario::updateOrCreate(
            [
                'id_usuario' => $request->input('id_usuario'),
                'id_puesto_titular' => $request->input('id_puesto_titular'),
            ],
            [
                'permisos' => $request->input('permisos', ['ver_bandeja' => true, 'derivar' => true, 'redactar' => true, 'recibir' => true]),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Delegación secretarial guardada exitosamente.',
            'data' => $secretario->fresh(['user.persona', 'puestoTitular']),
        ], Response::HTTP_CREATED);
    }
}
