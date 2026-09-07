<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion\Parametricas;

use App\Http\Controllers\Controller;
use App\Services\Administracion\ParametricaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ParametricaController extends Controller
{
    public function __construct(
        private readonly ParametricaService $parametricaService
    ) {}

    /**
     * Listado de tablas paramétricas principales con conteo optimizado.
     */
    public function index(): mixed
    {
        return $this->parametricaService->getOriginTables();
    }

    /**
     * Registro/Actualización de tabla paramétrica base.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = [
                'id' => $request->input('id'),
                'param_tabla' => $request->input('param_tabla', ''),
                'param_nombre' => $request->input('param_nombre', ''),
                'param_descripcion' => $request->input('param_descripcion', ''),
                'param_codigo' => 'ORIGEN',
                'param_valor' => 0,
            ];

            $parametrica = $this->parametricaService->save($data, auth()->id());

            return response()->json([
                'success' => true,
                'mensaje' => 'La paramétrica se registró correctamente',
                'data' => $request->all(),
                'resultado_campo' => $parametrica,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al guardar paramétrica base', [
                'request' => $request->all(),
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudo registrar la paramétrica. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Muestra los campos específicos de una tabla paramétrica.
     */
    public function show(string $param_tabla): mixed
    {
        try {
            return $this->parametricaService->getFieldsByTable(strtoupper($param_tabla));
        } catch (\Throwable $ex) {
            Log::error('Error al obtener campos de paramétrica', [
                'param_tabla' => $param_tabla,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudo mostrar la paramétrica. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminación lógica de una paramétrica o campo.
     */
    public function destroy(int|string $id): JsonResponse
    {
        try {
            $parametrica = $this->parametricaService->delete((int) $id, auth()->id());

            return response()->json([
                'success' => true,
                'mensaje' => 'La paramétrica se pudo eliminar correctamente',
                'data' => $parametrica,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Paramétrica no encontrada.',
            ], Response::HTTP_NOT_FOUND);
        } catch (RuntimeException $ex) {
            return response()->json([
                'success' => false,
                'mensaje' => $ex->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $ex) {
            Log::error('Error al eliminar paramétrica', [
                'id' => $id,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudo eliminar la paramétrica. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Registra o actualiza un sub-campo perteneciente a una tabla paramétrica.
     */
    public function registrar_campo(Request $request): JsonResponse
    {
        try {
            $data = [
                'id' => $request->input('id'),
                'param_tabla' => $request->input('param_tabla', ''),
                'param_nombre' => $request->input('param_nombre', ''),
                'param_descripcion' => $request->input('param_detalle', $request->input('param_descripcion', '')),
                'param_codigo' => $request->input('param_codigo', ''),
                'param_valor' => $request->input('param_valor', 1),
            ];

            $parametrica = $this->parametricaService->save($data, auth()->id());

            return response()->json([
                'success' => true,
                'mensaje' => 'El campo de la paramétrica se registró correctamente',
                'data' => $request->all(),
                'resultado_campo' => $parametrica,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar subcampo de paramétrica', [
                'request' => $request->all(),
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudo registrar el campo. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
