<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\OrdenTrabajo;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\ReciboCaja;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\View;

class DocumentoComercialPdfService
{
    /**
     * Genera el PDF del Aviso de Cobranza (Prefactura) para una lectura.
     */
    public function generarAvisoCobranzaPdf(LecturaMensual $lectura): string
    {
        $lectura->load(['abonado.zona', 'abonado.calle', 'abonado.categoria', 'abonado.medidorActual', 'periodo']);

        $historico = LecturaMensual::with('periodo')
            ->where('id_abonado', $lectura->id_abonado)
            ->where('id', '<=', $lectura->id)
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $items = [
            [
                'lectura' => $lectura,
                'historico' => $historico,
            ],
        ];

        $html = View::make('reportes.comercial.aviso-cobranza', [
            'abonado' => $lectura->abonado,
            'lecturas' => $items,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm')
            ->output();
    }

    /**
     * Genera el PDF de avisos de cobranza en lote para toda una zona.
     */
    public function generarAvisosZonaPdf(int $idPeriodo, ?int $idZona = null): string
    {
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

        $query = LecturaMensual::with(['abonado.zona', 'abonado.calle', 'abonado.categoria', 'abonado.medidorActual', 'periodo'])
            ->where('id_periodo', $idPeriodo);

        if ($idZona) {
            $query->whereHas('abonado', fn($q) => $q->where('id_zona', $idZona));
        }

        $lecturas = $query->join('comercial.abonados', 'lecturas_mensuales.id_abonado', '=', 'abonados.id')
            ->orderBy('abonados.codigo')
            ->select('comercial.lecturas_mensuales.*')
            ->get();

        $items = [];
        foreach ($lecturas as $lec) {
            $historico = LecturaMensual::with('periodo')
                ->where('id_abonado', $lec->id_abonado)
                ->where('id', '<=', $lec->id)
                ->orderByDesc('id')
                ->limit(6)
                ->get();

            $items[] = [
                'lectura' => $lec,
                'historico' => $historico,
            ];
        }

        $primerAbonado = $lecturas->first()?->abonado;

        $html = View::make('reportes.comercial.aviso-cobranza', [
            'abonado' => $primerAbonado,
            'lecturas' => $items,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm')
            ->output();
    }

    /**
     * Genera el PDF del Extracto Histórico de Cuenta de un Abonado (basado en extracto.frx de FoxPro).
     */
    public function generarExtractoHistoricoPdf(Abonado $abonado): string
    {
        $abonado->load(['zona', 'calle', 'categoria', 'medidorActual']);

        $lecturas = LecturaMensual::with(['periodo', 'facturaSiat'])
            ->where('id_abonado', $abonado->id)
            ->orderByDesc('id')
            ->get();

        $convenios = $abonado->convenios()
            ->with('cuotas')
            ->orderByDesc('id')
            ->get();

        $html = View::make('reportes.comercial.extracto-cuenta', [
            'abonado' => $abonado,
            'lecturas' => $lecturas,
            'convenios' => $convenios,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->output();
    }

    /**
     * Genera el PDF del Recibo de Caja (conceptos no sujetos a crédito fiscal / derechos de conexión).
     */
    public function generarReciboCajaPdf(ReciboCaja $recibo): string
    {
        $recibo->load('abonado.zona');

        $html = View::make('reportes.comercial.recibo-caja', [
            'recibo' => $recibo,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm')
            ->output();
    }

    /**
     * Genera la Orden de Trabajo Técnica para cuadrillas de campo (corte, reconexión, reemplazo).
     */
    public function generarOrdenTrabajoPdf(OrdenTrabajo $orden): string
    {
        $orden->load(['abonado.zona', 'abonado.calle', 'abonado.categoria', 'abonado.medidorActual', 'tecnico']);

        $html = View::make('reportes.comercial.orden-trabajo', [
            'orden' => $orden,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->output();
    }
}
