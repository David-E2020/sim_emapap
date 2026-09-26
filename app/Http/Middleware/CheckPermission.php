<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Valida si el usuario autenticado tiene al menos uno de los permisos o roles requeridos.
     * Los superadministradores ('Administrador General') tienen pase irrestricto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = Auth::guard('api')->user() ?: Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'status' => 'unauthorized',
                'message' => 'No autenticado en el sistema.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 1. Validar que la cuenta institucional esté activa
        if ($user->usr_estado !== 'A') {
            return response()->json([
                'success' => false,
                'status' => 'forbidden',
                'message' => 'Su cuenta institucional se encuentra inactiva o sin acceso al sistema.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Administrador General siempre tiene acceso completo
        if ($user->hasAnyRole(['Administrador General', 'Administrador', 'Super Admin'])) {
            return $next($request);
        }

        // 3. Verificar que el usuario cuente con al menos un rol activo asignado
        $hasAnyRole = $user->roles()->exists() || \App\Models\RolUser::where('usuario_id', $user->id)->where('estado', true)->exists();
        if (! $hasAnyRole) {
            return response()->json([
                'success' => false,
                'status' => 'forbidden',
                'message' => 'Su usuario no cuenta con roles ni permisos asignados en el sistema.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 4. Si no se especificaron permisos, permitir el paso (requería autenticación y rol activo)
        if (empty($permissions)) {
            return $next($request);
        }

        // 3. Aplanar lista de permisos (soporta 'permiso.a|permiso.b' o varios argumentos)
        $flatPermissions = [];
        foreach ($permissions as $p) {
            foreach (explode('|', $p) as $subP) {
                $trimmed = trim($subP);
                if ($trimmed !== '') {
                    $flatPermissions[] = $trimmed;
                }
            }
        }

        // 4. Verificar si el usuario cuenta con alguno de los permisos requeridos
        if ($user->hasAnyPermission($flatPermissions)) {
            return $next($request);
        }

        // Registrar intento de acceso no autorizado en bitácora de seguridad
        Log::warning('Intento de acceso denegado por permisos insuficientes', [
            'user_id' => $user->id,
            'username' => $user->usr_usuario,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'required_permissions' => $flatPermissions,
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);

        return response()->json([
            'success' => false,
            'status' => 'forbidden',
            'message' => 'Acceso denegado. No dispone de los privilegios o rol necesarios para realizar esta acción.',
        ], Response::HTTP_FORBIDDEN);
    }
}
