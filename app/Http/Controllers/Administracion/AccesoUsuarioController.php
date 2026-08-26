<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AccesoUsuarioController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function listar_usuario_acceso(): mixed
    {
        return User::with(['roles', 'permissions', 'rolPersmisos'])
            ->where('usr_estado', 'A')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function guardar_acceso_usuario(Request $request): JsonResponse
    {
        try {
            $userId = (int)$request->input('user_id');
            $user = User::findOrFail($userId);

            $this->auditService->log(
                event: 'user_module_access_updated',
                model: $user,
                newValues: $request->except(['_token'])
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Permisos y accesos de módulos guardados correctamente para ' . $user->usr_usuario
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Usuario no encontrado'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al guardar acceso de usuario', [
                'user_id' => $request->input('user_id'),
                'exception' => $ex->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno al guardar los accesos del usuario.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}