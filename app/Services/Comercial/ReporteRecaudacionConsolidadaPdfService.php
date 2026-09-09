<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Facturacion\ConfiguracionEmpresa;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\View;

class ReporteRecaudacionConsolidadaPdfService
{
    /**
     * Genera la Planilla Oficial de Recaudación Consolidada de Caja en PDF.
     *
     * @param array $datos Resumen financiero, métricas, tablas por rubro, caja, cajero y turnos.
     * @return string Contenido binario del PDF.
     */
    public function generarPdf(array $datos): string
    {
        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $html = View::make('reportes.comercial.recaudacion-consolidada', [
            'datos' => $datos,
            'empresa' => $empresa,
            'generadoPor' => auth()->user()?->name ?? 'Administración EMAPAP',
            'fechaImpresion' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape') // Horizontal para tabular columnas limpiamente
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();
    }
}
