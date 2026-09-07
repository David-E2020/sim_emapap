<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\ClienteFactura;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClienteFacturaController extends Controller
{
    /**
     * Listado paginado de clientes / abonados de facturación.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = ClienteFactura::query()
            ->where('_estado', 'ACTIVO')
            ->orderBy('nombre_razon_social', 'asc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_razon_social', 'ilike', "%{$search}%")
                    ->orWhere('numero_documento', 'ilike', "%{$search}%")
                    ->orWhere('correo_electronico', 'ilike', "%{$search}%");
            });
        }

        $clientes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clientes->items(),
            'total' => $clientes->total(),
            'current_page' => $clientes->currentPage(),
            'last_page' => $clientes->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Registro o actualización de cliente en el padrón local.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_tipo_documento_identidad' => 'required|integer',
            'numero_documento' => 'required|string|max:30',
            'complemento' => 'nullable|string|max:10',
            'nombre_razon_social' => 'required|string|max:200',
            'correo_electronico' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
        ]);

        $cliente = ClienteFactura::updateOrCreate(
            ['numero_documento' => $validated['numero_documento']],
            [
                'codigo_tipo_documento_identidad' => $validated['codigo_tipo_documento_identidad'],
                'complemento' => $validated['complemento'] ?? null,
                'nombre_razon_social' => mb_strtoupper($validated['nombre_razon_social']),
                'correo_electronico' => $validated['correo_electronico'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'REG_CLIENTE',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $cliente,
            'message' => 'Cliente registrado satisfactoriamente en el padrón de EMAPAP.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Obtener detalle de un cliente específico.
     */
    public function show(int $id): JsonResponse
    {
        $cliente = ClienteFactura::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ], Response::HTTP_OK);
    }

    /**
     * Desactivar cliente del padrón activo.
     */
    public function destroy(int $id): JsonResponse
    {
        $cliente = ClienteFactura::findOrFail($id);
        $cliente->_estado = 'INACTIVO';
        $cliente->_transaccion = 'DESACT_CLI';
        $cliente->save();

        return response()->json([
            'success' => true,
            'message' => 'Cliente desactivado del padrón.',
        ], Response::HTTP_OK);
    }
}
