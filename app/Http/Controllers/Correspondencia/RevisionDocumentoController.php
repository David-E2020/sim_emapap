<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\RevisionDocumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RevisionDocumentoController extends Controller
{
    public function index(int $idDocumento): JsonResponse
    {
        $revisiones = RevisionDocumento::with('persona')
            ->where('id_documento', $idDocumento)
            ->where('_estado', 'ACTIVO')
            ->orderBy('numero_revision', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $revisiones], Response::HTTP_OK);
    }

    public function store(Request $request, int $idDocumento): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contenido_html' => 'required|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $doc = Documento::findOrFail($idDocumento);
        $ultimaRev = RevisionDocumento::where('id_documento', $idDocumento)->max('numero_revision') ?? 0;
        $numRev = $ultimaRev + 1;

        $rev = RevisionDocumento::create([
            'id_documento' => $doc->id,
            'numero_revision' => $numRev,
            'version' => "v{$numRev}.0",
            'contenido_html_revision' => $request->input('contenido_html'),
            'observaciones' => $request->input('observaciones', 'Nueva versión de revisión generada.'),
            'id_persona' => auth()->user()?->id_persona ?: 1,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        // Actualizar contenido en documento principal
        $doc->contenido_html = $request->input('contenido_html');
        $doc->numero_revision = $numRev;
        $doc->save();

        return response()->json([
            'success' => true,
            'message' => "Revisión v{$numRev}.0 guardada exitosamente.",
            'data' => $rev,
        ], Response::HTTP_CREATED);
    }
}
