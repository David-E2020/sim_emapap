<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\OrdenTrabajo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CorteReconexionService
{
    /**
     * Obtiene los abonados que cumplen los criterios de corte por mora (>= 2 meses).
     */
    public function obtenerAbonadosParaCorte(int $mesesMoraMin = 2, ?int $idZona = null): Collection
    {
        $query = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->where('meses_mora', '>=', $mesesMoraMin)
            ->where('estado_servicio', '!=', 'CORTADO')
            ->where('estado_servicio', '!=', 'BAJA');

        if ($idZona) {
            $query->where('id_zona', $idZona);
        }

        return $query->orderByDesc('meses_mora')->orderByDesc('saldo_deuda')->get();
    }

    /**
     * Genera órdenes de corte masivas para abonados morosos.
     */
    public function generarOrdenesCorte(
        array $abonadosIds,
        string $fechaProgramada,
        ?int $idTecnico = null,
        ?string $motivo = null
    ): array {
        return DB::transaction(function () use ($abonadosIds, $fechaProgramada, $idTecnico, $motivo) {
            $abonados = Abonado::whereIn('id', $abonadosIds)->get();
            $ordenesCreadas = [];

            foreach ($abonados as $ab) {
                // Verificar que no tenga ya una orden de corte pendiente
                $ordenExiste = OrdenTrabajo::where('id_abonado', $ab->id)
                    ->where('tipo_orden', 'CORTE_POR_MORA')
                    ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                    ->first();

                if ($ordenExiste) {
                    continue;
                }

                $correlativo = OrdenTrabajo::count() + 1;
                $numeroOrden = sprintf('CORTE-%s-%05d', date('Y'), $correlativo);

                $orden = OrdenTrabajo::create([
                    'numero_orden' => $numeroOrden,
                    'id_abonado' => $ab->id,
                    'tipo_orden' => 'CORTE_POR_MORA',
                    'motivo' => $motivo ?? "Corte por mora acumulada de {$ab->meses_mora} meses (Bs {$ab->saldo_deuda})",
                    'id_tecnico_asignado' => $idTecnico,
                    'fecha_programada' => $fechaProgramada,
                    'estado' => 'PENDIENTE',
                ]);

                $ordenesCreadas[] = $orden;
            }

            return [
                'total_generadas' => count($ordenesCreadas),
                'ordenes' => $ordenesCreadas,
            ];
        });
    }

    /**
     * Registra la ejecución física del corte de servicio de agua en campo.
     */
    public function ejecutarCorte(
        int $idOrden,
        float $lecturaCorte,
        string $numeroPrecinto,
        ?string $informe = null
    ): OrdenTrabajo {
        return DB::transaction(function () use ($idOrden, $lecturaCorte, $numeroPrecinto, $informe) {
            /** @var OrdenTrabajo $orden */
            $orden = OrdenTrabajo::with('abonado')->findOrFail($idOrden);

            if ($orden->tipo_orden !== 'CORTE_POR_MORA') {
                throw new InvalidArgumentException('La orden especificada no es una orden de corte.');
            }

            $ahora = Carbon::now();

            $orden->update([
                'estado' => 'EJECUTADO',
                'fecha_ejecucion' => $ahora,
                'lectura_en_corte' => $lecturaCorte,
                'numero_precinto' => $numeroPrecinto,
                'informe_tecnico' => $informe,
            ]);

            // Actualizar estado del abonado a CORTADO
            $orden->abonado->update([
                'estado_servicio' => 'CORTADO',
                'fecha_ultimo_corte' => $ahora->toDateString(),
            ]);

            return $orden->fresh(['abonado']);
        });
    }

    /**
     * Registra la reconexión física de agua ejecutada en campo.
     */
    public function ejecutarReconexion(int $idOrden, ?string $informe = null): OrdenTrabajo
    {
        return DB::transaction(function () use ($idOrden, $informe) {
            /** @var OrdenTrabajo $orden */
            $orden = OrdenTrabajo::with('abonado')->findOrFail($idOrden);

            if ($orden->tipo_orden !== 'RECONEXION') {
                throw new InvalidArgumentException('La orden especificada no es una orden de reconexión.');
            }

            $ahora = Carbon::now();

            $orden->update([
                'estado' => 'EJECUTADO',
                'fecha_ejecucion' => $ahora,
                'informe_tecnico' => $informe,
            ]);

            $orden->abonado->update([
                'estado_servicio' => 'ACTIVO',
                'fecha_ultima_rehabilitacion' => $ahora->toDateString(),
            ]);

            return $orden->fresh(['abonado']);
        });
    }
}
