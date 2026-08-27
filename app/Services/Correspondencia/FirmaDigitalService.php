<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\FirmaAprobacion;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Rrhh\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirmaDigitalService
{
    /**
     * Firma o aprueba electrónicamente un documento oficial
     */
    public function firmarDocumento(int $idDocumento, int $idPersona, ?string $pin = null, string $tipoFirma = 'PIN_ELECTRONICO'): FirmaAprobacion
    {
        return DB::transaction(function () use ($idDocumento, $idPersona, $pin, $tipoFirma) {
            $documento = Documento::with(['participantes', 'plantilla'])->findOrFail($idDocumento);
            $persona = Persona::findOrFail($idPersona);

            // Validar si la persona es participante habilitado
            $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->where('id_persona', $idPersona)
                ->firstOrFail();

            // Calcular hash inmutable del contenido del documento
            $contenidoConcat = $documento->cite . '|' . $documento->asunto . '|' . $documento->contenido_html . '|' . $persona->nro_documento;
            $hashSha256 = hash('sha256', $contenidoConcat);

            // Registrar la firma
            $firma = FirmaAprobacion::updateOrCreate(
                [
                    'id_documento' => $idDocumento,
                    'id_participante_doc' => $participante->id,
                ],
                [
                    'id_persona' => $idPersona,
                    'estado' => 'FIRMADO',
                    'tipo_firma' => $tipoFirma,
                    'fecha_firma_aprobacion' => now(),
                    'hash_documento_sha256' => $hashSha256,
                    'sello_tiempo' => now(),
                    'codigo_solicitud_aprobacion' => Str::uuid()->toString(),
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]
            );

            // Actualizar estado del participante
            $participante->bandeja = 'FIRMADOS';
            $participante->save();

            // Verificar si todos los participantes obligatorios (REMITENTE_DE, VIA, DESTINATARIO_A) han firmado
            $pendientes = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->whereIn('tipo_participacion', ['REMITENTE_DE', 'VIA', 'DESTINATARIO_A'])
                ->whereDoesntHave('firmaAprobacion', function ($q) {
                    $q->where('estado', 'FIRMADO');
                })
                ->count();

            if ($pendientes === 0) {
                $documento->estado = 'FIRMADO';
                $documento->save();
            } else {
                $documento->estado = 'EN_REVISION';
                $documento->save();
            }

            return $firma;
        });
    }

    /**
     * Rechazar u observar un documento
     */
    public function observarDocumento(int $idDocumento, int $idPersona, string $motivo): FirmaAprobacion
    {
        return DB::transaction(function () use ($idDocumento, $idPersona, $motivo) {
            $documento = Documento::findOrFail($idDocumento);
            $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->where('id_persona', $idPersona)
                ->firstOrFail();

            $firma = FirmaAprobacion::updateOrCreate(
                [
                    'id_documento' => $idDocumento,
                    'id_participante_doc' => $participante->id,
                ],
                [
                    'id_persona' => $idPersona,
                    'estado' => 'OBSERVADO_RECHAZADO',
                    'motivo_observacion' => $motivo,
                    'fecha_firma_aprobacion' => now(),
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]
            );

            // Devolver estado del documento a BORRADOR con observaciones
            $documento->estado = 'BORRADOR';
            $documento->save();

            // Retornar a bandeja borrador
            $participante->bandeja = 'BORRADOR';
            $participante->save();

            return $firma;
        });
    }
}
