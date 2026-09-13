<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Administracion\UserAccessService;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class RolUserController extends Controller
{
    public function __construct(
        private readonly UserAccessService $userAccessService,
        private readonly AuditService $auditService
    ) {}

    /**
     * Asigna un rol a un usuario de forma atómica y auditada.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rol_id' => 'required|integer',
            'usuario_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rolId = (int) $request->input('rol_id');
        $usuarioId = (int) $request->input('usuario_id');

        try {
            $rolUser = $this->userAccessService->assignRole($usuarioId, $rolId);

            return response()->json($rolUser, Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al asignar rol a usuario', [
                'usuario_id' => $usuarioId,
                'rol_id' => $rolId,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo asignar el rol. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualización segura de contraseña de usuario con validación IDOR y auditoría inmutable.
     */
    public function update_user_password(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();

        if (! $authUser) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'current_password' => 'nullable|string',
            'id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'mensaje' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $targetUserId = $request->input('id');

        // REGLA DE SEGURIDAD IDOR/BOLA:
        if (! $targetUserId || (int) $targetUserId === (int) $authUser->id) {
            $userToUpdate = User::findOrFail($authUser->id);

            // Validar de forma obligatoria la contraseña actual para cambio de credencial propio
            if (! $request->filled('current_password') || ! Hash::check((string) $request->input('current_password'), $userToUpdate->password)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'La contraseña actual es requerida y debe ser correcta para efectuar el cambio.',
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            // Si intenta cambiar la clave de OTRO usuario, debe ser Administrador
            if (! $authUser->hasRole('Administrador General')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Acceso denegado: No tiene permisos para modificar este usuario',
                ], Response::HTTP_FORBIDDEN);
            }

            $userToUpdate = User::find($targetUserId);
            if (! $userToUpdate) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Usuario no encontrado',
                ], Response::HTTP_NOT_FOUND);
            }
        }

        try {
            $userToUpdate->password = Hash::make($request->password);
            $userToUpdate->save();

            // Auditoría inmutable de cambio de credencial
            $this->auditService->log(
                event: 'user_password_changed',
                model: $userToUpdate,
                newValues: ['changed_by' => $authUser->id, 'timestamp' => (string) now()]
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Contraseña actualizada de forma segura',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar contraseña', [
                'user_id' => $userToUpdate->id,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno al actualizar la contraseña',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
