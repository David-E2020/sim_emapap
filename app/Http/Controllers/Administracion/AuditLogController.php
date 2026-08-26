<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogController extends Controller
{
    /**
     * Listado paginado y filtrable de eventos de auditoría inmutables.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,usr_usuario,email')
            ->orderBy('created_at', 'desc');

        // Filtro por evento (login, role_assigned, parametrica_created, etc.)
        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        // Filtro por usuario ejecutor
        if ($request->filled('user_id')) {
            $query->where('user_id', (int)$request->input('user_id'));
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Búsqueda por texto (IP o tipo de modelo)
        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('event', 'ILIKE', $search)
                  ->orWhere('ip_address', 'ILIKE', $search)
                  ->orWhere('auditable_type', 'ILIKE', $search);
            });
        }

        $perPage = (int)$request->input('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ], Response::HTTP_OK);
    }
}
