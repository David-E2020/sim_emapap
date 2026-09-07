<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\HojaRuta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerificacionPublicaController extends Controller
{
    /**
     * Verificar autenticidad de un documento mediante código QR o Token alfanumérico
     */
    public function verificarDocumento(string $codigo): JsonResponse
    {
        $doc = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'firmasAprobaciones.persona',
        ])
            ->where('codigo_verificacion', strtoupper(trim($codigo)))
            ->orWhere('cite', strtoupper(trim($codigo)))
            ->first();

        if (! $doc) {
            return response()->json([
                'success' => false,
                'message' => 'El código de verificación o CITE no corresponde a ningún documento oficial emitido por EMAPA.',
            ], Response::HTTP_NOT_FOUND);
        }

        $firmantes = [];
        foreach ($doc->firmasAprobaciones as $f) {
            if ($f->estado === 'FIRMADO') {
                $firmantes[] = [
                    'funcionario' => $f->persona ? $f->persona->nombre_completo : 'N/A',
                    'ci' => $f->persona ? $f->persona->nro_documento : 'N/A',
                    'fecha_firma' => Carbon::parse($f->fecha_firma_aprobacion)->format('d/m/Y H:i:s'),
                    'tipo_firma' => $f->tipo_firma,
                    'hash_sha256' => $f->hash_documento_sha256,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'valido' => true,
            'data' => [
                'cite' => $doc->cite,
                'codigo_verificacion' => $doc->codigo_verificacion,
                'tipo_documento' => $doc->tipo_documento,
                'asunto' => $doc->asunto,
                'unidad_emisora' => $doc->unidadGeneradora ? $doc->unidadGeneradora->nombre : 'EMAPA',
                'estado' => $doc->estado,
                'fecha_emision' => Carbon::parse($doc->_fecha_creacion)->format('d/m/Y H:i'),
                'firmantes' => $firmantes,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Verificar autenticidad de una Hoja de Ruta pública por CITE
     */
    public function verificarHojaRuta(Request $request): JsonResponse
    {
        $cite = trim((string) $request->input('cite', ''));
        $hojaRuta = HojaRuta::with(['unidadOrigen', 'personaOrigen'])->where('nro_hoja_ruta', $cite)->first();

        if (! $hojaRuta) {
            return response()->json(['success' => false, 'message' => 'Hoja de ruta no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nro_hoja_ruta' => $hojaRuta->nro_hoja_ruta,
                'asunto' => $hojaRuta->asunto,
                'tipo_hr' => $hojaRuta->tipo_hr,
                'estado' => $hojaRuta->estado,
                'prioridad' => $hojaRuta->prioridad,
                'fecha_ingreso' => Carbon::parse($hojaRuta->fecha_solicitud)->format('d/m/Y H:i'),
            ],
        ], Response::HTTP_OK);
    }
}
