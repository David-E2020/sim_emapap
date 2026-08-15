<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RolUser;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Verfica acceso administrativo considerando rol_users de la base de datos y Spatie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Verificar si el usuario tiene rol en acopio.rol_users o permisos en Spatie
        $hasRolUser = RolUser::where('usuario_id', $user->id)->exists();
        $hasSpatieRole = ($user->roles && $user->roles->count() > 0) || ($user->permissions && $user->permissions->count() > 0);

        if (!$hasRolUser && !$hasSpatieRole) {
            return response()->json(['message' => 'Acceso denegado. Se requieren permisos de administración.'], 403);
        }

        return $next($request);
    }
}
