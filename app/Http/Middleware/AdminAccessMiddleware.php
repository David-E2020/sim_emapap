<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\RolUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Verifica acceso administrativo considerando Spatie RBAC y roles internos.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'No autenticado.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 1. Verificación de rol y permisos Spatie
        $hasSpatieAdmin = $user->hasRole('Administrador General') ||
                          $user->hasAnyPermission([
                              'SIGP',
                              'admin.usuarios.ver',
                              'admin.menus.ver',
                              'admin.control_acceso.ver'
                          ]);

        // 2. Verificación de rol activo en tabla pivote
        $hasActiveRolUser = RolUser::where('usuario_id', $user->id)
            ->where('estado', true)
            ->exists();

        if (!$hasSpatieAdmin && !$hasActiveRolUser) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Acceso denegado. Se requieren permisos de administración.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
