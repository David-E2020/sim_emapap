<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\RolUser;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('usr_usuario', 'password');
        $rules = [
            'usr_usuario' => 'required|string',
            'password' => 'required|string',
        ];
        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $token = JWTAuth::attempt($credentials);

            if (! $token) {
                $this->auditService->log(
                    event: 'auth_login_failed',
                    newValues: ['username_attempted' => $credentials['usr_usuario']]
                );

                return response()->json([
                    'status' => 'error',
                    'message' => 'Las credenciales son incorrectas.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            Log::error('Error generando token JWT', ['exception' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo iniciar sesión, intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = Auth::user();

        // Validar estado de la cuenta institucional (solo usuarios activos 'A')
        if ($user->usr_estado !== 'A') {
            try {
                JWTAuth::invalidate($token);
            } catch (\Exception $e) {
                // Token invalidation failure fallback
            }

            $this->auditService->log(
                event: 'auth_login_blocked_inactive',
                model: $user,
                userId: $user->id,
                newValues: ['username_attempted' => $credentials['usr_usuario']]
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Su cuenta institucional se encuentra inactiva o suspendida. Contacte con Administración.',
            ], Response::HTTP_FORBIDDEN);
        }

        $usuarioId_ = $user->id;
        $rolUser_ = RolUser::where('usuario_id', $usuarioId_)->first();
        $rol_ = $rolUser_ ? Rol::find($rolUser_->rol_id) : null;

        // Permisos Spatie (directos + heredados)
        $spatiePermissions = $user->getAllPermissions()->pluck('name');

        // Registrar auditoría de inicio de sesión exitoso
        $this->auditService->log(
            event: 'auth_login_success',
            model: $user,
            userId: $user->id
        );

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
            'permissions' => $spatiePermissions,
            'roles' => $user->getRoleNames(),
            'rol' => $rol_ ? $rol_->name : ($user->roles->first() ? $user->roles->first()->name : 'Usuario'),
            'rute_home' => 'dashboard',
        ], Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->header('Authorization');
        $user = Auth::user();

        try {
            if ($token) {
                JWTAuth::invalidate(JWTAuth::getToken());
            }

            if ($user) {
                $this->auditService->log(
                    event: 'auth_logout',
                    model: $user,
                    userId: $user->id
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.',
            ], Response::HTTP_OK);
        } catch (JWTException $e) {
            Log::error('Error al invalidar token JWT en logout', ['exception' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Fallo al cerrar sesión, intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
