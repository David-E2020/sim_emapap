<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LecturacionService
{
    public function __construct(
        protected TarifarioAguaService $tarifarioService
    ) {}

    /**
     * Abre un nuevo periodo mensual de consumo y genera las órdenes de lectura base para los abonados activos.
     */
    public function abrirPeriodo(
        int $mes,
        int $gestion,
        string $fechaInicio,
        string $fechaFin,
        string $fechaVencimiento,
        ?string $obs = null
    ): PeriodoFacturacion {
        $periodoCodigo = sprintf('%02d/%d', $mes, $gestion);

        return DB::transaction(function () use ($periodoCodigo, $mes, $gestion, $fechaInicio, $fechaFin, $fechaVencimiento, $obs) {
            $periodo = PeriodoFacturacion::firstOrCreate(
                ['periodo' => $periodoCodigo],
                [
                    'mes' => $mes,
                    'gestion' => $gestion,
                    'fecha_inicio_consumo' => $fechaInicio,
                    'fecha_fin_consumo' => $fechaFin,
                    'fecha_vencimiento_pago' => $fechaVencimiento,
                    'estado' => 'ABIERTO',
                    'observaciones' => $obs,
                ]
            );

            // Generar o actualizar pre-registros de lecturas para abonados activos de forma masiva y eficiente
            $abonados = Abonado::with('medidorActual')
                ->whereIn('estado_servicio', ['ACTIVO', 'CORTADO', 'EN_MORA'])
                ->get();

            $lecturasExistentes = LecturaMensual::where('id_periodo', $periodo->id)
                ->pluck('id_abonado')
                ->flip();

            // Obtener en una sola consulta indexada la última lectura previa de cada abonado
            $ultimasLecturas = DB::table('comercial.lecturas_mensuales')
                ->select(DB::raw('DISTINCT ON (id_abonado) id_abonado, lectura_actual'))
                ->where('id_periodo', '!=', $periodo->id)
                ->orderBy('id_abonado')
                ->orderByDesc('id')
                ->pluck('lectura_actual', 'id_abonado');

            $nuevasLecturas = [];

            foreach ($abonados as $abonado) {
                if (isset($lecturasExistentes[$abonado->id])) {
                    continue;
                }

                $lecturaAnterior = isset($ultimasLecturas[$abonado->id])
                    ? (float) $ultimasLecturas[$abonado->id]
                    : (float) ($abonado->medidorActual?->lectura_inicial ?? 0.00);

                $nuevasLecturas[] = [
                    'id_periodo' => $periodo->id,
                    'id_abonado' => $abonado->id,
                    'id_medidor' => $abonado->id_medidor_actual,
                    'lectura_anterior' => $lecturaAnterior,
                    'lectura_actual' => $lecturaAnterior,
                    'consumo_m3' => 0.00,
                    'monto_agua' => 0.00,
                    'monto_alcantarillado' => 0.00,
                    'monto_descuento_ley1886' => 0.00,
                    'total_facturado' => 0.00,
                    'estado_pago' => 'PENDIENTE',
                ];
            }

            if (!empty($nuevasLecturas)) {
                foreach (array_chunk($nuevasLecturas, 500) as $chunk) {
                    LecturaMensual::insert($chunk);
                }
            }

            return $periodo;
        });
    }

    /**
     * Registra o actualiza la lectura tomada por el lecturador para un abonado en un periodo.
     */
    public function registrarLectura(
        int $idLectura,
        float $lecturaActual,
        bool $esEstimada = false,
        ?string $obs = null,
        ?int $idLecturador = null,
        float $otrosCargos = 0.00
    ): LecturaMensual {
        /** @var LecturaMensual $lectura */
        $lectura = LecturaMensual::with('abonado.categoria')->findOrFail($idLectura);

        if ($lecturaActual < (float) $lectura->lectura_anterior && !$esEstimada) {
            throw new InvalidArgumentException(
                "La lectura actual ({$lecturaActual} m³) no puede ser menor a la lectura anterior ({$lectura->lectura_anterior} m³)."
            );
        }

        $calculo = $this->tarifarioService->calcularLiquidacion(
            $lectura->abonado,
            (float) $lectura->lectura_anterior,
            $lecturaActual,
            $otrosCargos
        );

        $lectura->update([
            'lectura_actual' => $lecturaActual,
            'consumo_m3' => $calculo['consumo_m3'],
            'es_estimada' => $esEstimada,
            'observacion_lectura' => $obs,
            'fecha_lectura' => Carbon::now(),
            'id_lecturador' => $idLecturador,
            'monto_agua' => $calculo['monto_agua'],
            'monto_alcantarillado' => $calculo['monto_alcantarillado'],
            'monto_descuento_ley1886' => $calculo['monto_descuento_ley1886'],
            'monto_otros' => $calculo['monto_otros'],
            'total_facturado' => $calculo['total_facturado'],
        ]);

        return $lectura->fresh(['abonado', 'periodo']);
    }

    /**
     * Liquida masivamente el periodo y actualiza los saldos y meses de mora de los abonados.
     */
    public function liquidarPeriodo(int $idPeriodo): PeriodoFacturacion
    {
        return DB::transaction(function () use ($idPeriodo) {
            /** @var PeriodoFacturacion $periodo */
            $periodo = PeriodoFacturacion::with('lecturas.abonado.categoria')->findOrFail($idPeriodo);

            foreach ($periodo->lecturas as $lectura) {
                // Si la lectura actual es igual a la anterior y no se liquidó aún, calcular con consumo mínimo
                if ((float) $lectura->total_facturado <= 0) {
                    $calculo = $this->tarifarioService->calcularLiquidacion(
                        $lectura->abonado,
                        (float) $lectura->lectura_anterior,
                        (float) $lectura->lectura_actual
                    );

                    $lectura->update([
                        'monto_agua' => $calculo['monto_agua'],
                        'monto_alcantarillado' => $calculo['monto_alcantarillado'],
                        'monto_descuento_ley1886' => $calculo['monto_descuento_ley1886'],
                        'total_facturado' => $calculo['total_facturado'],
                    ]);
                }

            }

            // Actualización masiva de deudas y moras para todos los abonados en una sola transacción SQL
            DB::statement("
                UPDATE comercial.abonados a
                SET saldo_deuda = COALESCE(sub.total_deuda, 0),
                    meses_mora = COALESCE(sub.meses_mora, 0),
                    estado_servicio = CASE 
                        WHEN COALESCE(sub.meses_mora, 0) >= 2 AND a.estado_servicio NOT IN ('CORTADO', 'BAJA') THEN 'EN_MORA'
                        WHEN COALESCE(sub.meses_mora, 0) < 2 AND a.estado_servicio = 'EN_MORA' THEN 'ACTIVO'
                        ELSE a.estado_servicio
                    END
                FROM (
                    SELECT id_abonado, SUM(total_facturado) as total_deuda, COUNT(id) as meses_mora
                    FROM comercial.lecturas_mensuales
                    WHERE estado_pago = 'PENDIENTE' AND total_facturado > 0
                    GROUP BY id_abonado
                ) sub
                WHERE a.id = sub.id_abonado;
            ");

            $periodo->update(['estado' => 'FACTURADO']);

            return $periodo->fresh();
        });
    }

    /**
     * Cierra formalmente un periodo mensual, consolidando la mora y actualizando candidatos a corte.
     */
    public function cerrarPeriodo(int $idPeriodo): PeriodoFacturacion
    {
        return DB::transaction(function () use ($idPeriodo) {
            /** @var PeriodoFacturacion $periodo */
            $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

            // Actualizar saldos y estado de mora de los abonados
            DB::statement("
                UPDATE comercial.abonados a
                SET saldo_deuda = COALESCE(sub.total_deuda, 0),
                    meses_mora = COALESCE(sub.meses_mora, 0),
                    estado_servicio = CASE 
                        WHEN COALESCE(sub.meses_mora, 0) >= 2 AND a.estado_servicio NOT IN ('CORTADO', 'BAJA') THEN 'EN_MORA'
                        WHEN COALESCE(sub.meses_mora, 0) < 2 AND a.estado_servicio = 'EN_MORA' THEN 'ACTIVO'
                        ELSE a.estado_servicio
                    END
                FROM (
                    SELECT id_abonado, SUM(total_facturado) as total_deuda, COUNT(id) as meses_mora
                    FROM comercial.lecturas_mensuales
                    WHERE estado_pago = 'PENDIENTE' AND total_facturado > 0
                    GROUP BY id_abonado
                ) sub
                WHERE a.id = sub.id_abonado;
            ");

            $periodo->update(['estado' => 'CERRADO']);

            return $periodo->fresh();
        });
    }

    /**
     * Cambia el estado de un periodo (LECTURA, FACTURACION, CERRADO).
     */
    public function cambiarEstadoPeriodo(int $idPeriodo, string $nuevoEstado): PeriodoFacturacion
    {
        $estado = strtoupper(trim($nuevoEstado));
        if ($estado === 'FACTURACION' || $estado === 'FACTURADO') {
            return $this->liquidarPeriodo($idPeriodo);
        }

        if ($estado === 'CERRADO') {
            return $this->cerrarPeriodo($idPeriodo);
        }

        // Si es LECTURA o ABIERTO
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);
        $periodo->update(['estado' => 'LECTURA']);
        return $periodo->fresh();
    }

    /**
     * Actualiza fechas y observaciones de un periodo existente.
     */
    public function actualizarPeriodo(int $idPeriodo, array $datos): PeriodoFacturacion
    {
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

        $periodo->update(array_filter([
            'fecha_inicio_consumo' => $datos['fecha_inicio_consumo'] ?? null,
            'fecha_fin_consumo' => $datos['fecha_fin_consumo'] ?? null,
            'fecha_vencimiento_pago' => $datos['fecha_vencimiento_pago'] ?? null,
            'estado' => isset($datos['estado']) ? strtoupper($datos['estado']) : null,
            'observaciones' => $datos['observaciones'] ?? null,
        ], fn($v) => !is_null($v)));

        return $periodo->fresh();
    }

    /**
     * Elimina un periodo si no contiene lecturas facturadas o pagos cobrados.
     */
    public function eliminarPeriodo(int $idPeriodo): bool
    {
        return DB::transaction(function () use ($idPeriodo) {
            $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

            $tienePagados = LecturaMensual::where('id_periodo', $idPeriodo)
                ->where('estado_pago', 'PAGADO')
                ->exists();

            if ($tienePagados) {
                throw new \Exception("No se puede eliminar el periodo {$periodo->periodo} porque ya registra lecturas con cobros pagados.");
            }

            // Eliminar registros de lecturas del periodo
            LecturaMensual::where('id_periodo', $idPeriodo)->delete();
            return $periodo->delete();
        });
    }

    /**
     * Obtiene la planilla de lecturas de un periodo para campo o carga masiva.
     */
    public function obtenerPlanilla(int $idPeriodo, ?int $idZona = null, ?int $idCalle = null): Collection
    {
        $query = LecturaMensual::with(['abonado.zona', 'abonado.calle', 'abonado.categoria', 'medidor'])
            ->where('id_periodo', $idPeriodo);

        if ($idZona) {
            $query->whereHas('abonado', fn($q) => $q->where('id_zona', $idZona));
        }

        if ($idCalle) {
            $query->whereHas('abonado', fn($q) => $q->where('id_calle', $idCalle));
        }

        return $query->join('comercial.abonados', 'lecturas_mensuales.id_abonado', '=', 'abonados.id')
            ->orderBy('abonados.codigo')
            ->select('comercial.lecturas_mensuales.*')
            ->get();
    }
}
