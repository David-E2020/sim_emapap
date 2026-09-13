<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\ParticipanteDocumento;
use Illuminate\Support\Facades\DB;

class TransferenciaBandejaService
{
    /**
     * Transfiere la bandeja completa de un funcionario saliente a un nuevo funcionario
     */
    public function transferirBandeja(int $idPersonaOrigen, int $idPersonaDestino, ?int $idCargoDestino = null, ?string $motivo = null): array
    {
        return DB::transaction(function () use ($idPersonaOrigen, $idPersonaDestino, $idCargoDestino, $motivo) {
            $safeSufijo = DB::connection()->getPdo()->quote(' [TRANSFERIDO POR: ' . ($motivo ?? 'TRANSFERENCIA') . ']');

            $derivacionesAfectadas = Derivacion::where('id_funcionario_destino', $idPersonaOrigen)
                ->whereIn('estado_derivacion', ['PENDIENTE_RECEPCION', 'RECIBIDO'])
                ->update([
                    'id_funcionario_destino' => $idPersonaDestino,
                    'id_cargo_destino' => $idCargoDestino,
                    'instruccion_detalle' => DB::raw("CONCAT(COALESCE(instruccion_detalle, ''), {$safeSufijo})"),
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]);

            // 2. Reasignar documentos en revisión o pendientes de firma
            $participacionesAfectadas = ParticipanteDocumento::where('id_persona', $idPersonaOrigen)
                ->whereIn('bandeja', ['BORRADOR', 'EN_REVISION'])
                ->update([
                    'id_persona' => $idPersonaDestino,
                    'id_puesto' => $idCargoDestino,
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]);

            return [
                'success' => true,
                'derivaciones_transferidas' => $derivacionesAfectadas,
                'documentos_transferidos' => $participacionesAfectadas,
                'motivo' => $motivo,
            ];
        });
    }
}
