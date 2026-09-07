<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\DespachoSalida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DespachoSalidaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estado = $request->input('estado');
        $query = DespachoSalida::with(['hojaRuta.documentos', 'documento'])->where('_estado', 'ACTIVO');

        if ($estado) {
            $query->where('estado_despacho', $estado);
        }

        $despachos = $query->orderBy('id', 'desc')->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $despachos->items(),
            'meta' => [
                'total' => $despachos->total(),
                'current_page' => $despachos->currentPage(),
                'last_page' => $despachos->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_hoja_ruta' => 'nullable|integer|exists:App\Models\Correspondencia\HojaRuta,id',
            'id_documento' => 'nullable|integer|exists:App\Models\Correspondencia\Documento,id',
            'tipo_despacho' => 'required|string|in:MENSAJERIA_INTERNA,COURIER_POSTAL,ENTREGA_DIRECTA',
            'destinatario_institucion' => 'required|string|max:255',
            'destinatario_persona' => 'nullable|string|max:255',
            'destinatario_direccion' => 'nullable|string|max:255',
            'destinatario_ciudad' => 'nullable|string|max:100',
            'nro_guia_despacho' => 'nullable|string|max:100',
            'empresa_courier' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $despacho = DespachoSalida::create([
            'id_hoja_ruta' => $request->input('id_hoja_ruta'),
            'id_documento' => $request->input('id_documento'),
            'tipo_despacho' => $request->input('tipo_despacho'),
            'destinatario_institucion' => strtoupper(trim((string) $request->input('destinatario_institucion'))),
            'destinatario_persona' => $request->input('destinatario_persona'),
            'destinatario_direccion' => $request->input('destinatario_direccion'),
            'destinatario_ciudad' => $request->input('destinatario_ciudad', 'La Paz'),
            'nro_guia_despacho' => $request->input('nro_guia_despacho'),
            'empresa_courier' => $request->input('empresa_courier'),
            'estado_despacho' => 'PENDIENTE_DESPACHO',
            'fecha_despacho' => now(),
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Despacho registrado.', 'data' => $despacho], Response::HTTP_CREATED);
    }

    public function entregar(Request $request, int $id): JsonResponse
    {
        $despacho = DespachoSalida::findOrFail($id);
        $despacho->estado_despacho = 'ENTREGADO_CON_ACUSE';
        $despacho->fecha_entrega = now();
        $despacho->observaciones = $request->input('observaciones', 'Entregado y recibido por destinatario.');

        if ($request->hasFile('acuse')) {
            $file = $request->file('acuse');
            $path = $file->store("correspondencia/acuses/{$despacho->id}", 'public');
            $despacho->ruta_archivo_acuse = $path;
        }

        $despacho->save();

        return response()->json(['success' => true, 'message' => 'Despacho marcado como entregado.', 'data' => $despacho], Response::HTTP_OK);
    }
}
