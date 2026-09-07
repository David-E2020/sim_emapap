<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\FirmaAprobacion;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Rrhh\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class FirmaDigitalService
{
    public function __construct(
        protected readonly CiteGeneratorService $citeService
    ) {}

    /**
     * Firma o aprueba electrónicamente un documento oficial (LONDRA: Secuencial VIA -> DE)
     */
    public function firmarDocumento(int $idDocumento, int $idPersona, ?string $pin = null, string $tipoFirma = 'PIN_ELECTRONICO'): FirmaAprobacion
    {
        return DB::transaction(function () use ($idDocumento, $idPersona, $tipoFirma) {
            $documento = Documento::with(['participantes.persona', 'firmasAprobaciones'])->findOrFail($idDocumento);
            $persona = Persona::findOrFail($idPersona);

            if ($documento->estado === 'FIRMADO') {
                throw new PreconditionFailedHttpException('El documento ya ha sido firmado y concluido anteriormente.');
            }

            if ($documento->estado === 'ANULADO') {
                throw new PreconditionFailedHttpException('No se puede firmar un documento que se encuentra anulado.');
            }

            // Validar si la persona es participante habilitado
            $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->where('id_persona', $idPersona)
                ->first();

            if (! $participante) {
                // Si no se encuentra participante con ese id_persona, buscar el participante pendiente correspondiente
                $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                    ->whereIn('tipo_participacion', ['VIA', 'REMITENTE_DE'])
                    ->whereDoesntHave('firmaAprobacion', function ($q) {
                        $q->where('estado', 'FIRMADO');
                    })
                    ->orderByRaw("CASE WHEN tipo_participacion = 'VIA' THEN 1 ELSE 2 END")
                    ->first();

                if (! $participante) {
                    $participante = ParticipanteDocumento::where('id_documento', $idDocumento)->firstOrFail();
                }
                $persona = Persona::find($participante->id_persona) ?: $persona;
                $idPersona = $participante->id_persona;
            }

            // REGLA LONDRA: Si el participante es REMITENTE_DE, verificar que todos los VIA hayan firmado primero
            if ($participante->tipo_participacion === 'REMITENTE_DE') {
                $viasPendientes = ParticipanteDocumento::where('id_documento', $idDocumento)
                    ->where('tipo_participacion', 'VIA')
                    ->whereDoesntHave('firmaAprobacion', function ($q) {
                        $q->where('estado', 'FIRMADO');
                    })
                    ->count();

                if ($viasPendientes > 0) {
                    throw new PreconditionFailedHttpException('El documento requiere el visto bueno de todos los participantes VIA antes de la firma de emisión.');
                }
            }

            // Calcular hash inmutable del contenido del documento
            $contenidoConcat = $documento->cite.'|'.$documento->asunto.'|'.$documento->contenido_html.'|'.$persona->nro_documento;
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
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MODIFICAR',
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]
            );

            // Actualizar estado del participante
            $participante->bandeja = 'FIRMADOS';
            $participante->save();

            // Verificar si todos los firmantes obligatorios (REMITENTE_DE y VIA) han completado su firma
            $firmantesObligatoriosPendientes = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->whereIn('tipo_participacion', ['REMITENTE_DE', 'VIA'])
                ->whereDoesntHave('firmaAprobacion', function ($q) {
                    $q->where('estado', 'FIRMADO');
                })
                ->count();

            if ($firmantesObligatoriosPendientes === 0) {
                $documento->estado = 'FIRMADO';
                if (! $documento->codigo_verificacion) {
                    $documento->codigo_verificacion = $this->citeService->generarCodigoVerificacion();
                }
                if (! $documento->cite) {
                    $sigla = $documento->plantilla?->sigla ?? 'MEM';
                    $documento->cite = $this->citeService->generarCiteDocumento($documento->id_unidad_generadora, $sigla, (int) date('Y'));
                }
                $documento->_usuario_modificacion = auth()->id() ?? 1;
                $documento->_fecha_modificacion = now();
                $documento->save();
            } else {
                $documento->estado = 'EN_REVISION';
                $documento->_usuario_modificacion = auth()->id() ?? 1;
                $documento->_fecha_modificacion = now();
                $documento->save();
            }

            return $firma;
        });
    }

    /**
     * Rechazar u observar un documento (LONDRA: Retorno al creador en estado OBSERVADO)
     */
    public function observarDocumento(int $idDocumento, int $idPersona, string $motivo): FirmaAprobacion
    {
        return DB::transaction(function () use ($idDocumento, $idPersona, $motivo) {
            $documento = Documento::findOrFail($idDocumento);
            $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                ->where('id_persona', $idPersona)
                ->first();

            if (! $participante) {
                $participante = ParticipanteDocumento::where('id_documento', $idDocumento)
                    ->whereIn('tipo_participacion', ['VIA', 'REMITENTE_DE'])
                    ->first();

                if (! $participante) {
                    $participante = ParticipanteDocumento::where('id_documento', $idDocumento)->firstOrFail();
                }
                $idPersona = $participante->id_persona;
            }

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
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MODIFICAR',
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]
            );

            // Documento pasa a estado OBSERVADO y se notifica al autor
            $documento->estado = 'OBSERVADO';
            $documento->motivo_anulacion = "Observación de revisión: {$motivo}";
            $documento->_usuario_modificacion = auth()->id() ?? 1;
            $documento->_fecha_modificacion = now();
            $documento->save();

            $participante->bandeja = 'OBSERVADOS';
            $participante->save();

            return $firma;
        });
    }

    public function rechazarDocumento(int $idDocumento, int $idPersona, string $motivo): FirmaAprobacion
    {
        return $this->observarDocumento($idDocumento, $idPersona, $motivo);
    }

    /**
     * Anular un documento oficial (LONDRA: ANULADO)
     */
    public function anularDocumento(int $idDocumento, int $idPersona, string $motivo): Documento
    {
        return DB::transaction(function () use ($idDocumento, $idPersona, $motivo) {
            $documento = Documento::findOrFail($idDocumento);

            if ($documento->estado === 'ANULADO') {
                throw new PreconditionFailedHttpException('El documento ya se encuentra anulado.');
            }

            $documento->estado = 'ANULADO';
            $documento->fecha_anulacion = now();
            $documento->id_usuario_anulacion = auth()->id() ?? 1;
            $documento->id_cargo_anulacion = $idPersona;
            $documento->motivo_anulacion = $motivo;
            $documento->_usuario_modificacion = auth()->id() ?? 1;
            $documento->_fecha_modificacion = now();
            $documento->save();

            return $documento;
        });
    }

    /**
     * Calcular acciones permitidas sobre un documento según rol del usuario
     */
    public function calcularAccionesDocumento(Documento $doc, int $idPersona): array
    {
        $esCreador = ($doc->id_creador === $idPersona);
        $participacion = $doc->participantes->where('id_persona', $idPersona)->first();
        $haFirmado = $doc->firmasAprobaciones->where('id_persona', $idPersona)->where('estado', 'FIRMADO')->isNotEmpty();

        $puedeEditar = ($esCreador && in_array($doc->estado, ['BORRADOR', 'OBSERVADO']));
        $puedeEliminar = ($esCreador && $doc->estado === 'BORRADOR');
        $puedeEnviarRevision = ($esCreador && in_array($doc->estado, ['BORRADOR', 'OBSERVADO']));
        $puedeFirmar = ($participacion && in_array($participacion->tipo_participacion, ['REMITENTE_DE', 'VIA']) && ! $haFirmado && in_array($doc->estado, ['EN_REVISION', 'BORRADOR']));
        $puedeObservar = ($participacion && ! $haFirmado && in_array($doc->estado, ['EN_REVISION', 'BORRADOR']));
        $puedeAnular = ($esCreador && in_array($doc->estado, ['BORRADOR', 'EN_REVISION', 'OBSERVADO', 'FIRMADO']));

        return [
            'puede_editar' => $puedeEditar,
            'puede_eliminar' => $puedeEliminar,
            'puede_enviar_revision' => $puedeEnviarRevision,
            'puede_firmar' => $puedeFirmar,
            'puede_observar' => $puedeObservar,
            'puede_anular' => $puedeAnular,
            'puede_ver' => true,
        ];
    }
}
