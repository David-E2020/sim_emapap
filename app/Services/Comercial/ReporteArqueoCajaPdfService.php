<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\CajaSesion;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\View;

class ReporteArqueoCajaPdfService
{
    /**
     * Genera la Planilla Oficial de Arqueo y Cierre de Caja en PDF.
     */
    public function generarPdf(CajaSesion $sesion): string
    {
        $sesion->load(['cajero', 'sucursal', 'puntoVenta', 'supervisor', 'movimientos']);

        // Calcular desglose por rubro y método de pago
        $aguaEfectivo = (float) $sesion->lecturas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', 1);
            })->sum('total_facturado');

        $aguaQr = (float) $sesion->lecturas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', '!=', 1);
            })->sum('total_facturado');

        $cuotasEfectivo = (float) $sesion->cuotas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', 1);
            })->sum('monto_cuota');

        $cuotasQr = (float) $sesion->cuotas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', '!=', 1);
            })->sum('monto_cuota');

        $recibosEfectivo = (float) $sesion->recibos()
            ->where('estado', 'VALIDO')
            ->sum('monto_total');

        $totalesRubro = [
            'agua_efectivo' => $aguaEfectivo,
            'agua_qr' => $aguaQr,
            'cuotas_efectivo' => $cuotasEfectivo,
            'cuotas_qr' => $cuotasQr,
            'recibos_efectivo' => $recibosEfectivo,
        ];

        $html = View::make('reportes.comercial.arqueo-caja', [
            'sesion' => $sesion,
            'totalesRubro' => $totalesRubro,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();
    }
}
