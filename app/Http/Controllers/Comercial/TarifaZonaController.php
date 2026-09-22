<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Calle;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\PaqueteTarifario;
use App\Models\Comercial\TarifaEscalonada;
use App\Models\Comercial\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TarifaZonaController extends Controller
{
    /**
     * Definición canónica de los 11 rangos de consumo de FoxPro / EMAPAP.
     */
    public const RANGOS_ESCALONADOS = [
        ['index' => 1, 'campo' => 'tarifa1', 'label' => 'De 7-10', 'desde' => 7, 'hasta' => 10],
        ['index' => 2, 'campo' => 'tarifa2', 'label' => 'De 11-15', 'desde' => 11, 'hasta' => 15],
        ['index' => 3, 'campo' => 'tarifa3', 'label' => 'De 16-20', 'desde' => 16, 'hasta' => 20],
        ['index' => 4, 'campo' => 'tarifa4', 'label' => 'De 21-25', 'desde' => 21, 'hasta' => 25],
        ['index' => 5, 'campo' => 'tarifa5', 'label' => 'De 26-30', 'desde' => 26, 'hasta' => 30],
        ['index' => 6, 'campo' => 'tarifa6', 'label' => 'De 31-35', 'desde' => 31, 'hasta' => 35],
        ['index' => 7, 'campo' => 'tarifa7', 'label' => 'De 36-40', 'desde' => 36, 'hasta' => 40],
        ['index' => 8, 'campo' => 'tarifa8', 'label' => 'De 41-50', 'desde' => 41, 'hasta' => 50],
        ['index' => 9, 'campo' => 'tarifa9', 'label' => 'De 51-55', 'desde' => 51, 'hasta' => 55],
        ['index' => 10, 'campo' => 'tarifa10', 'label' => 'De 56-60', 'desde' => 56, 'hasta' => 60],
        ['index' => 11, 'campo' => 'tarifa11', 'label' => '+ de 61', 'desde' => 61, 'hasta' => 999999],
    ];

    // =========================================================================
    // 1. PAQUETES / PLIEGOS TARIFARIOS
    // =========================================================================

    /**
     * Listado de todos los pliegos tarifarios.
     */
    public function indexPaquetes(): JsonResponse
    {
        $paquetes = PaqueteTarifario::withCount('categorias')
            ->orderByDesc('es_vigente')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paquetes,
        ], Response::HTTP_OK);
    }

    /**
     * Registra un nuevo paquete/pliego tarifario y siembra las 6 categorías base.
     */
    public function storePaquete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:pgsql.comercial.paquetes_tarifarios,codigo',
            'nombre' => 'required|string|max:150',
            'resolucion_legal' => 'nullable|string|max:100',
            'fecha_inicio_vigencia' => 'nullable|date',
            'fecha_fin_vigencia' => 'nullable|date',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $paquete = DB::transaction(function () use ($request) {
            $user = auth()->user()?->name ?? 'sistema';

            $paquete = PaqueteTarifario::create([
                'codigo' => trim($request->input('codigo')),
                'nombre' => trim($request->input('nombre')),
                'resolucion_legal' => $request->input('resolucion_legal'),
                'fecha_inicio_vigencia' => $request->input('fecha_inicio_vigencia') ?: now()->toDateString(),
                'fecha_fin_vigencia' => $request->input('fecha_fin_vigencia'),
                'es_vigente' => false,
                'descripcion' => $request->input('descripcion'),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREACION',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]);

            // Categorías base oficiales de EMAPAP
            $categoriasBase = [
                ['codigo' => 'A', 'nombre' => 'COMERCIAL A', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 2.49, 'tarifa_minima' => 14.94, 'tarifa_alcantarillado' => 2.00, 'aplica_ley_1886' => false],
                ['codigo' => 'B', 'nombre' => 'COMERCIAL B', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 3.52, 'tarifa_minima' => 21.12, 'tarifa_alcantarillado' => 2.00, 'aplica_ley_1886' => false],
                ['codigo' => 'D', 'nombre' => 'DOMICILIARIA', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 2.10, 'tarifa_minima' => 12.60, 'tarifa_alcantarillado' => 2.00, 'aplica_ley_1886' => true],
                ['codigo' => 'E', 'nombre' => 'ESPECIAL', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 3.58, 'tarifa_minima' => 21.48, 'tarifa_alcantarillado' => 10.00, 'aplica_ley_1886' => false],
                ['codigo' => 'L', 'nombre' => 'LAVADO DE AUTOS', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 3.58, 'tarifa_minima' => 21.48, 'tarifa_alcantarillado' => 10.00, 'aplica_ley_1886' => false],
                ['codigo' => 'P', 'nombre' => 'ESTATAL O PUBLICA', 'volumen_base' => 6.0, 'tarifa_excedente_base' => 3.52, 'tarifa_minima' => 21.12, 'tarifa_alcantarillado' => 2.00, 'aplica_ley_1886' => false],
            ];

            foreach ($categoriasBase as $catData) {
                $cat = CategoriaTarifaria::create([
                    'id_paquete' => $paquete->id,
                    'codigo' => $catData['codigo'],
                    'nombre' => $catData['nombre'],
                    'volumen_base' => $catData['volumen_base'],
                    'tarifa_excedente_base' => $catData['tarifa_excedente_base'],
                    'tarifa_minima' => $catData['tarifa_minima'],
                    'tarifa_alcantarillado' => $catData['tarifa_alcantarillado'],
                    'aplica_ley_1886' => $catData['aplica_ley_1886'],
                    'activo' => true,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREACION',
                ]);

                foreach (self::RANGOS_ESCALONADOS as $rango) {
                    TarifaEscalonada::create([
                        'id_categoria' => $cat->id,
                        'desde_m3' => $rango['desde'],
                        'hasta_m3' => $rango['hasta'],
                        'precio_m3' => $catData['tarifa_excedente_base'],
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CREACION',
                    ]);
                }
            }

            return $paquete;
        });

        return response()->json([
            'success' => true,
            'message' => 'Paquete tarifario creado exitosamente con sus 6 categorías base.',
            'data' => $paquete->loadCount('categorias'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Clona un paquete tarifario existente con todas sus categorías y tarifas escalonadas.
     */
    public function clonarPaquete(Request $request, int $id): JsonResponse
    {
        $origen = PaqueteTarifario::with(['categorias.tarifasEscalonadas'])->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'codigo' => 'nullable|string|max:50|unique:pgsql.comercial.paquetes_tarifarios,codigo',
            'nombre' => 'nullable|string|max:150',
            'resolucion_legal' => 'nullable|string|max:100',
            'fecha_inicio_vigencia' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $nuevo = DB::transaction(function () use ($origen, $request) {
            $nuevoCodigo = $request->input('codigo') ?: ($origen->codigo . '_COPIA_' . strtoupper(substr(uniqid(), -4)));
            $nuevoNombre = $request->input('nombre') ?: ($origen->nombre . ' (Copia)');

            $nuevo = PaqueteTarifario::create([
                'codigo' => $nuevoCodigo,
                'nombre' => $nuevoNombre,
                'resolucion_legal' => $request->input('resolucion_legal') ?: $origen->resolucion_legal,
                'fecha_inicio_vigencia' => $request->input('fecha_inicio_vigencia') ?: now()->toDateString(),
                'fecha_fin_vigencia' => null,
                'es_vigente' => false,
                'descripcion' => "Clonado a partir del pliego: {$origen->nombre}",
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CLONACION',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]);

            foreach ($origen->categorias as $cat) {
                $nuevaCat = CategoriaTarifaria::create([
                    'id_paquete' => $nuevo->id,
                    'codigo' => $cat->codigo,
                    'nombre' => $cat->nombre,
                    'volumen_base' => $cat->volumen_base,
                    'tarifa_minima' => $cat->tarifa_minima,
                    'tarifa_excedente_base' => $cat->tarifa_excedente_base,
                    'tarifa_alcantarillado' => $cat->tarifa_alcantarillado,
                    'aplica_ley_1886' => $cat->aplica_ley_1886,
                    'activo' => true,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CLONACION',
                ]);

                foreach ($cat->tarifasEscalonadas as $esc) {
                    TarifaEscalonada::create([
                        'id_categoria' => $nuevaCat->id,
                        'desde_m3' => $esc->desde_m3,
                        'hasta_m3' => $esc->hasta_m3,
                        'precio_m3' => $esc->precio_m3,
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CLONACION',
                    ]);
                }
            }

            return $nuevo;
        });

        return response()->json([
            'success' => true,
            'message' => "Pliego clonado exitosamente como '{$nuevo->nombre}'.",
            'data' => $nuevo->loadCount('categorias'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Activa un pliego como vigente y desactiva los demás.
     */
    public function activarPaquete(int $id): JsonResponse
    {
        $paquete = PaqueteTarifario::findOrFail($id);

        DB::transaction(function () use ($id) {
            PaqueteTarifario::where('id', '!=', $id)->update(['es_vigente' => false]);
            PaqueteTarifario::where('id', $id)->update(['es_vigente' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => "El pliego '{$paquete->nombre}' ha sido activado como VIGENTE OFICIAL.",
            'data' => $paquete->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Obtiene la matriz completa (exacta a FoxPro) para un paquete tarifario.
     */
    public function getMatriz(int $id): JsonResponse
    {
        $paquete = PaqueteTarifario::with(['categorias' => function ($q) {
            $q->orderBy('codigo');
        }, 'categorias.tarifasEscalonadas'])->findOrFail($id);

        $matriz = [];

        foreach ($paquete->categorias as $cat) {
            $row = [
                'id' => $cat->id,
                'codigo' => $cat->codigo,
                'nombre' => $cat->nombre,
                'volumen_base' => (float) $cat->volumen_base,
                'tarifa_excedente_base' => (float) $cat->tarifa_excedente_base,
                'tarifa_minima' => (float) $cat->tarifa_minima,
                'tarifa_alcantarillado' => (float) $cat->tarifa_alcantarillado,
                'aplica_ley_1886' => (bool) $cat->aplica_ley_1886,
                'activo' => (bool) $cat->activo,
            ];

            // Mapear los 11 escalones a tarifa1 .. tarifa11
            $escalonesMap = $cat->tarifasEscalonadas->keyBy(function ($item) {
                return (int) $item->desde_m3;
            });

            foreach (self::RANGOS_ESCALONADOS as $rango) {
                $match = $escalonesMap->get($rango['desde']);
                $row[$rango['campo']] = $match ? (float) $match->precio_m3 : (float) $cat->tarifa_excedente_base;
            }

            $matriz[] = $row;
        }

        return response()->json([
            'success' => true,
            'paquete' => $paquete,
            'rangos' => self::RANGOS_ESCALONADOS,
            'data' => $matriz,
        ], Response::HTTP_OK);
    }

    /**
     * Guarda / actualiza de forma atómica la matriz de categorías y escalones de un paquete.
     */
    public function updateMatriz(Request $request, int $id): JsonResponse
    {
        $paquete = PaqueteTarifario::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'categorias' => 'required|array|min:1',
            'categorias.*.codigo' => 'required|string|max:10',
            'categorias.*.nombre' => 'required|string|max:100',
            'categorias.*.volumen_base' => 'required|numeric|min:0',
            'categorias.*.tarifa_excedente_base' => 'required|numeric|min:0',
            'categorias.*.tarifa_minima' => 'required|numeric|min:0',
            'categorias.*.tarifa_alcantarillado' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($request, $paquete) {
            $categoriasInput = $request->input('categorias');

            foreach ($categoriasInput as $catData) {
                $catId = $catData['id'] ?? null;

                if ($catId) {
                    $cat = CategoriaTarifaria::where('id_paquete', $paquete->id)->find($catId);
                } else {
                    $cat = new CategoriaTarifaria();
                    $cat->id_paquete = $paquete->id;
                }

                if (!$cat) {
                    $cat = new CategoriaTarifaria();
                    $cat->id_paquete = $paquete->id;
                }

                $cat->codigo = strtoupper(trim($catData['codigo']));
                $cat->nombre = strtoupper(trim($catData['nombre']));
                $cat->volumen_base = $catData['volumen_base'];
                $cat->tarifa_excedente_base = $catData['tarifa_excedente_base'];
                $cat->tarifa_minima = $catData['tarifa_minima'];
                $cat->tarifa_alcantarillado = $catData['tarifa_alcantarillado'];
                $cat->aplica_ley_1886 = !empty($catData['aplica_ley_1886']);
                $cat->activo = isset($catData['activo']) ? (bool) $catData['activo'] : true;
                $cat->_estado = 'ACTIVO';
                $cat->_transaccion = 'ACTUALIZACION';
                $cat->save();

                // Actualizar o crear los 11 escalones
                foreach (self::RANGOS_ESCALONADOS as $rango) {
                    $precioM3 = isset($catData[$rango['campo']])
                        ? (float) $catData[$rango['campo']]
                        : (float) $cat->tarifa_excedente_base;

                    TarifaEscalonada::updateOrCreate(
                        [
                            'id_categoria' => $cat->id,
                            'desde_m3' => $rango['desde'],
                        ],
                        [
                            'hasta_m3' => $rango['hasta'],
                            'precio_m3' => $precioM3,
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'ACTUALIZACION',
                        ]
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Matriz de tarifas del pliego '{$paquete->nombre}' guardada exitosamente.",
        ], Response::HTTP_OK);
    }

    // =========================================================================
    // 2. CATEGORÍAS INDIVIDUALES (LEGACY COMPATIBILITY)
    // =========================================================================

    /**
     * Listado de categorías tarifarias con sus escalones.
     */
    public function indexCategorias(): JsonResponse
    {
        $paqueteVigente = PaqueteTarifario::where('es_vigente', true)->first();
        $idPaquete = $paqueteVigente ? $paqueteVigente->id : 1;

        $categorias = CategoriaTarifaria::with(['tarifasEscalonadas'])
            ->where('id_paquete', $idPaquete)
            ->withCount('abonados')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias,
            'paquete_vigente' => $paqueteVigente,
        ], Response::HTTP_OK);
    }

    /**
     * Actualiza los parámetros de una categoría tarifaria individual.
     */
    public function updateCategoria(Request $request, int $id): JsonResponse
    {
        $categoria = CategoriaTarifaria::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tarifa_minima' => 'sometimes|numeric|min:0',
            'tarifa_excedente_base' => 'sometimes|numeric|min:0',
            'tarifa_alcantarillado' => 'sometimes|numeric|min:0',
            'volumen_base' => 'sometimes|numeric|min:1',
            'aplica_ley_1886' => 'boolean',
            'activo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $categoria->update($request->all());

        return response()->json([
            'success' => true,
            'message' => "Categoría {$categoria->nombre} actualizada correctamente.",
            'data' => $categoria->fresh('tarifasEscalonadas'),
        ], Response::HTTP_OK);
    }

    // =========================================================================
    // 3. ZONAS Y CALLES
    // =========================================================================

    /**
     * Listado de zonas con conteo de calles y abonados.
     */
    public function indexZonas(): JsonResponse
    {
        $zonas = Zona::withCount(['calles', 'abonados'])
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $zonas,
        ], Response::HTTP_OK);
    }

    /**
     * Crear una nueva zona.
     */
    public function storeZona(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:pgsql.comercial.zonas,codigo',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $zona = Zona::create([
            'codigo' => strtoupper(trim($request->input('codigo'))),
            'nombre' => strtoupper(trim($request->input('nombre'))),
            'descripcion' => $request->input('descripcion'),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREACION',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Zona '{$zona->nombre}' creada exitosamente.",
            'data' => $zona->loadCount(['calles', 'abonados']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Actualizar una zona.
     */
    public function updateZona(Request $request, int $id): JsonResponse
    {
        $zona = Zona::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:20|unique:pgsql.comercial.zonas,codigo,' . $id,
            'nombre' => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('codigo')) {
            $zona->codigo = strtoupper(trim($request->input('codigo')));
        }
        if ($request->has('nombre')) {
            $zona->nombre = strtoupper(trim($request->input('nombre')));
        }
        if ($request->has('descripcion')) {
            $zona->descripcion = $request->input('descripcion');
        }
        $zona->_transaccion = 'ACTUALIZACION';
        $zona->save();

        return response()->json([
            'success' => true,
            'message' => "Zona '{$zona->nombre}' actualizada correctamente.",
            'data' => $zona->fresh()->loadCount(['calles', 'abonados']),
        ], Response::HTTP_OK);
    }

    /**
     * Listado de calles filtrables por zona.
     */
    public function indexCalles(Request $request): JsonResponse
    {
        $idZona = $request->input('id_zona');

        $query = Calle::with('zona')->withCount('abonados')->orderBy('nombre');

        if (!empty($idZona)) {
            $query->where('id_zona', $idZona);
        }

        $calles = $query->get();

        return response()->json([
            'success' => true,
            'data' => $calles,
        ], Response::HTTP_OK);
    }

    /**
     * Crear una nueva calle en una zona.
     */
    public function storeCalle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_zona' => 'required|integer|exists:pgsql.comercial.zonas,id',
            'nombre' => 'required|string|max:150',
            'referencia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $calle = Calle::create([
            'id_zona' => $request->input('id_zona'),
            'nombre' => strtoupper(trim($request->input('nombre'))),
            'referencia' => $request->input('referencia'),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREACION',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Calle '{$calle->nombre}' registrada exitosamente.",
            'data' => $calle->load('zona')->loadCount('abonados'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Actualizar una calle.
     */
    public function updateCalle(Request $request, int $id): JsonResponse
    {
        $calle = Calle::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_zona' => 'sometimes|integer|exists:pgsql.comercial.zonas,id',
            'nombre' => 'sometimes|string|max:150',
            'referencia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('id_zona')) {
            $calle->id_zona = $request->input('id_zona');
        }
        if ($request->has('nombre')) {
            $calle->nombre = strtoupper(trim($request->input('nombre')));
        }
        if ($request->has('referencia')) {
            $calle->referencia = $request->input('referencia');
        }
        $calle->_transaccion = 'ACTUALIZACION';
        $calle->save();

        return response()->json([
            'success' => true,
            'message' => "Calle '{$calle->nombre}' actualizada correctamente.",
            'data' => $calle->fresh('zona')->loadCount('abonados'),
        ], Response::HTTP_OK);
    }
}
