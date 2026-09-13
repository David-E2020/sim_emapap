<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeadersMiddleware
{
    /**
     * Inyecta encabezados de seguridad HTTP defensivos en cada respuesta.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Remover huella digital del runtime para evitar fingerprinting
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        // Encabezados estándar de endurecimiento (OWASP ASVS / DAST)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
