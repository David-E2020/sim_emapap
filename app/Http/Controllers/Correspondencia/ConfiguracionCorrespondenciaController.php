<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\ComponentePlantilla;
use App\Models\Correspondencia\Correlativo;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Correspondencia\UsuarioSecretario;
use App\Models\Parametrica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfiguracionCorrespondenciaController extends Controller
{
    /**
     * Listar todas las plantillas y formatos
     */
    public function plantillas(): JsonResponse
    {
        $plantillas = PlantillaDocumento::with('componentes')
            ->where('_estado', 'ACTIVO')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $plantillas], Response::HTTP_OK);
    }

    /**
     * Obtener detalle de una plantilla para el diseñador visual
     */
    public function showPlantilla(int $id): JsonResponse
    {
        $plantilla = PlantillaDocumento::with(['componentes' => fn ($q) => $q->where('_estado', 'ACTIVO')->orderBy('orden')])
            ->find($id);

        if (! $plantilla) {
            return response()->json(['success' => false, 'message' => 'Plantilla no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['success' => true, 'data' => $plantilla], Response::HTTP_OK);
    }

    /**
     * Guardar o crear una nueva plantilla con su composición de elementos
     */
    public function storePlantilla(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:200',
            'sigla' => 'required|string|max:20',
            'param_tipo_plantilla' => 'required|string',
            'componentes' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $plantilla = DB::transaction(function () use ($request) {
            $id = $request->input('id');
            $sigla = strtoupper(trim((string) $request->input('sigla')));
            $version = (int) $request->input('version', 1);

            if ($id) {
                $plantilla = PlantillaDocumento::find($id) ?: new PlantillaDocumento;
            } else {
                $plantilla = PlantillaDocumento::where('sigla', $sigla)->where('version', $version)->first() ?: new PlantillaDocumento;
            }

            $plantilla->nombre = strtoupper(trim((string) $request->input('nombre')));
            $plantilla->sigla = $sigla;
            $plantilla->version = $version;
            $plantilla->param_tipo_plantilla = strtoupper(trim((string) $request->input('param_tipo_plantilla')));
            $plantilla->param_validez_legal = $request->input('param_validez_legal', 'FIRMA_ELECTRONICA');
            $plantilla->multiples_para = (bool) $request->input('multiples_para', true);
            $plantilla->cabecera_html = $request->input('cabecera_html');
            $plantilla->cuerpo_base = $request->input('cuerpo_base');
            $plantilla->pie_html = $request->input('pie_html');
            $plantilla->config_pagina = $request->input('config_pagina', [
                'orientacion' => 'VERTICAL',
                'formato' => 'A4',
                'margen_superior' => '2.5cm',
                'margen_inferior' => '2.5cm',
                'margen_izquierdo' => '3cm',
                'margen_derecho' => '2.5cm',
                'mostrar_membrete' => true,
                'mostrar_pie' => true,
            ]);
            $plantilla->_estado = 'ACTIVO';

            if (! $id) {
                $plantilla->_usuario_creacion = auth()->id() ?? 1;
                $plantilla->_fecha_creacion = now();
                $plantilla->_transaccion = 'CREAR';
            } else {
                $plantilla->_usuario_modificacion = auth()->id() ?? 1;
                $plantilla->_fecha_modificacion = now();
                $plantilla->_transaccion = 'ACTUALIZAR';
            }

            $plantilla->save();

            // Sincronizar componentes
            $componentesInput = $request->input('componentes', []);
            if (! empty($componentesInput)) {
                ComponentePlantilla::where('id_plantilla', $plantilla->id)->update(['_estado' => 'INACTIVO']);

                foreach ($componentesInput as $index => $comp) {
                    ComponentePlantilla::updateOrCreate(
                        [
                            'id_plantilla' => $plantilla->id,
                            'nombre' => $comp['nombre'] ?? 'Componente '.($index + 1),
                            'tipo_componente' => $comp['tipo_componente'] ?? 'TEXTO_HTML',
                        ],
                        [
                            'orden' => $index + 1,
                            'es_obligatorio' => $comp['es_obligatorio'] ?? true,
                            'config_inicial' => $comp['config_inicial'] ?? [],
                            '_estado' => 'ACTIVO',
                            '_usuario_creacion' => auth()->id() ?? 1,
                            '_fecha_creacion' => now(),
                        ]
                    );
                }
            }

            return $plantilla->fresh(['componentes']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Plantilla oficial guardada exitosamente.',
            'data' => $plantilla,
        ], Response::HTTP_OK);
    }

    /**
     * Listar estado de correlativos anuales
     */
    public function correlativos(Request $request): JsonResponse
    {
        $gestion = (int) $request->input('gestion', date('Y'));
        $correlativos = Correlativo::with('unidadOrganizacional')
            ->where('gestion', $gestion)
            ->where('_estado', 'ACTIVO')
            ->orderBy('id_unidad_organizacional')
            ->get();

        return response()->json(['success' => true, 'data' => $correlativos], Response::HTTP_OK);
    }

    /**
     * Listar catálogo de proveídos institucionales desde Paramétricas
     */
    public function proveidos(): JsonResponse
    {
        $proveidos = Parametrica::whereIn('param_tabla', ['TABLA_CORRESPONDENCIA_PROVEIDOS', 'TABLA_LONDRA_PROVEIDOS'])
            ->where('param_estado', 'A')
            ->where('param_valor', '>', 0)
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
            'id_puesto_titular' => 'required|integer|exists:App\Models\Rrhh\Puesto,id',
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
