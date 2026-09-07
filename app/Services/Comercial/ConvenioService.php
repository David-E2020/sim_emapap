<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\ConvenioPago;
use App\Models\Comercial\LecturaMensual;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConvenioService
{
    /**
     * Simula el plan de cuotas de un convenio sin persistir en la base de datos.
     */
    public function simularConvenio(float $montoDeuda, float $pagoInicial, int $plazoMeses): array
    {
        if ($plazoMeses <= 0) {
            throw new InvalidArgumentException('El plazo en meses debe ser mayor a 0.');
        }

        if ($pagoInicial >= $montoDeuda) {
            throw new InvalidArgumentException('El pago inicial no puede ser igual o mayor a la deuda total.');
        }

        $saldoFinanciado = round($montoDeuda - $pagoInicial, 2);
        $montoCuotaBase = round($saldoFinanciado / $plazoMeses, 2);

        $cuotasSimuladas = [];
        $fechaBase = Carbon::now()->addMonth();

        $acumulado = 0.00;
        for ($i = 1; $i <= $plazoMeses; $i++) {
            $monto = ($i === $plazoMeses) 
                ? round($saldoFinanciado - $acumulado, 2) 
                : $montoCuotaBase;

            $acumulado += $monto;
            $fechaVenc = $fechaBase->copy()->addMonths($i - 1)->endOfMonth();

            $cuotasSimuladas[] = [
                'numero_cuota' => $i,
                'periodo' => $fechaVenc->format('m/Y'),
                'monto_cuota' => $monto,
                'fecha_vencimiento' => $fechaVenc->toDateString(),
            ];
        }

        return [
            'monto_deuda' => $montoDeuda,
            'pago_inicial' => $pagoInicial,
            'saldo_financiado' => $saldoFinanciado,
            'plazo_meses' => $plazoMeses,
            'monto_cuota_promedio' => $montoCuotaBase,
            'cuotas' => $cuotasSimuladas,
        ];
    }

    /**
     * Suscribe formalmente un convenio de pago de agua para un abonado con deuda.
     */
    public function suscribirConvenio(
        int $idAbonado,
        float $pagoInicial,
        int $plazoMeses,
        ?string $glosa = null,
        int $idUsuario = 1
    ): ConvenioPago {
        return DB::transaction(function () use ($idAbonado, $pagoInicial, $plazoMeses, $glosa, $idUsuario) {
            /** @var Abonado $abonado */
            $abonado = Abonado::with('lecturas')->findOrFail($idAbonado);

            // Obtener total de deuda de lecturas pendientes
            $lecturasPendientes = LecturaMensual::where('id_abonado', $abonado->id)
                ->where('estado_pago', 'PENDIENTE')
                ->get();

            $montoDeudaTotal = (float) $lecturasPendientes->sum('total_facturado');

            if ($montoDeudaTotal <= 0 && (float) $abonado->saldo_deuda > 0) {
                $montoDeudaTotal = (float) $abonado->saldo_deuda;
            }

            if ($montoDeudaTotal <= 0) {
                throw new InvalidArgumentException('El abonado no cuenta con deuda pendiente para convenir.');
            }

            $simulacion = $this->simularConvenio($montoDeudaTotal, $pagoInicial, $plazoMeses);

            $correlativo = ConvenioPago::count() + 1;
            $numeroConvenio = sprintf('CONV-%s-%04d', date('Y'), $correlativo);

            /** @var ConvenioPago $convenio */
            $convenio = ConvenioPago::create([
                'id_abonado' => $abonado->id,
                'numero_convenio' => $numeroConvenio,
                'monto_deuda_total' => $montoDeudaTotal,
                'pago_inicial' => $pagoInicial,
                'saldo_financiado' => $simulacion['saldo_financiado'],
                'plazo_meses' => $plazoMeses,
                'monto_cuota_mensual' => $simulacion['monto_cuota_promedio'],
                'fecha_suscripcion' => Carbon::now()->toDateString(),
                'estado' => 'VIGENTE',
                'glosa' => $glosa,
                '_usuario_creacion' => $idUsuario,
            ]);

            // Generar las cuotas fijas
            foreach ($simulacion['cuotas'] as $c) {
                ConvenioCuota::create([
                    'id_convenio' => $convenio->id,
                    'numero_cuota' => $c['numero_cuota'],
                    'periodo' => $c['periodo'],
                    'monto_cuota' => $c['monto_cuota'],
                    'fecha_vencimiento' => $c['fecha_vencimiento'],
                    'estado_pago' => 'PENDIENTE',
                    '_usuario_creacion' => $idUsuario,
                ]);
            }

            // Cambiar las lecturas a EN_CONVENIO
            foreach ($lecturasPendientes as $lec) {
                $lec->update(['estado_pago' => 'EN_CONVENIO']);
            }

            // Si estaba cortado, reactivar temporalmente por suscripción de convenio
            if ($abonado->estado_servicio === 'CORTADO') {
                $abonado->update([
                    'estado_servicio' => 'ACTIVO',
                    'fecha_ultima_rehabilitacion' => Carbon::now()->toDateString(),
                ]);
            }

            return $convenio->fresh(['cuotas', 'abonado']);
        });
    }
}
