<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Calle;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TarifaZonaController extends Controller
{
    /**
     * Listado de categorías tarifarias con sus escalones.
     */
    public function indexCategorias(): JsonResponse
    {
        $categorias = CategoriaTarifaria::with(['tarifasEscalonadas'])
            ->withCount('abonados')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias,
        ], Response::HTTP_OK);
    }

    /**
     * Actualiza los parámetros de una categoría tarifaria.
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

        $calle = Calle::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Calle registrada exitosamente.',
            'data' => $calle->load('zona'),
        ], Response::HTTP_CREATED);
    }
}
