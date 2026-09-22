<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\Zona;
use App\Models\Facturacion\ConfiguracionEmpresa;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ReportesOperativosPdfService
{
    /**
     * Obtiene la empresa activa para membretes.
     */
    protected function getEmpresa(): ?ConfiguracionEmpresa
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                return ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            // Ignorar
        }
        return null;
    }

    /**
     * Genera la Planilla de Campo para Toma de Lecturas (replectu.frx / replectu1.frx).
     */
    public function generarPlanillaLecturasPdf(
        int $idPeriodo,
        ?int $idZona = null,
        ?int $idCalle = null,
        bool $aCiegas = false
    ): string {
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

        $query = LecturaMensual::with([
            'abonado.zona',
            'abonado.calle',
            'abonado.categoria',
            'medidor',
        ])->where('id_periodo', $idPeriodo);

        if ($idZona) {
            $query->whereHas('abonado', fn($q) => $q->where('id_zona', $idZona));
        }

        if ($idCalle) {
            $query->whereHas('abonado', fn($q) => $q->where('id_calle', $idCalle));
        }

        $lecturas = $query->join('comercial.abonados', 'lecturas_mensuales.id_abonado', '=', 'abonados.id')
            ->leftJoin('comercial.zonas', 'abonados.id_zona', '=', 'zonas.id')
            ->leftJoin('comercial.calles', 'abonados.id_calle', '=', 'calles.id')
            ->orderBy('zonas.nombre')
            ->orderBy('calles.nombre')
            ->orderBy('abonados.codigo')
            ->select('comercial.lecturas_mensuales.*')
            ->get();

        $zonaNombre = $idZona ? Zona::find($idZona)?->nombre : 'TODAS LAS ZONAS';

        $html = View::make('reportes.comercial.planilla-lecturas', [
            'periodo' => $periodo,
            'lecturas' => $lecturas,
            'zonaNombre' => $zonaNombre,
            'aCiegas' => $aCiegas,
            'empresa' => $this->getEmpresa(),
            'generadoPor' => auth()->user()?->name ?? 'Operador Comercial EMAPAP',
            'fechaImpresion' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();
    }

    /**
     * Genera el Resumen de Operaciones y Facturación por Zonas (repzonas.frx).
     */
    public function generarResumenZonasPdf(int $idPeriodo): string
    {
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

        $filas = DB::table('comercial.lecturas_mensuales as l')
            ->join('comercial.abonados as a', 'l.id_abonado', '=', 'a.id')
            ->leftJoin('comercial.zonas as z', 'a.id_zona', '=', 'z.id')
            ->where('l.id_periodo', $idPeriodo)
            ->groupBy('z.id', 'z.codigo', 'z.nombre')
            ->orderBy('z.nombre')
            ->select([
                'z.id as id_zona',
                DB::raw("COALESCE(z.nombre, 'SIN ZONA ASIGNADA') as zona_nombre"),
                DB::raw("COALESCE(z.codigo, 'S/Z') as zona_codigo"),
                DB::raw("COUNT(l.id) as total_abonados"),
                DB::raw("SUM(COALESCE(l.consumo_m3, 0)) as consumo_total_m3"),
                DB::raw("SUM(COALESCE(l.monto_agua, 0)) as total_agua_bs"),
                DB::raw("SUM(COALESCE(l.monto_alcantarillado, 0)) as total_alcantarillado_bs"),
                DB::raw("SUM(COALESCE(l.monto_otros, 0)) as total_otros_cargos_bs"),
                DB::raw("SUM(COALESCE(l.monto_descuento_ley1886, 0)) as total_ley1886_bs"),
                DB::raw("SUM(COALESCE(l.total_facturado, 0)) as total_facturado_bs"),
                DB::raw("COUNT(CASE WHEN l.estado_pago = 'PAGADO' THEN 1 END) as abonados_pagados"),
                DB::raw("SUM(CASE WHEN l.estado_pago = 'PAGADO' THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as total_cobrado_bs"),
            ])
            ->get();

        $totales = [
            'abonados' => $filas->sum('total_abonados'),
            'consumo_m3' => $filas->sum('consumo_total_m3'),
            'agua_bs' => $filas->sum('total_agua_bs'),
            'alcantarillado_bs' => $filas->sum('total_alcantarillado_bs'),
            'otros_cargos_bs' => $filas->sum('total_otros_cargos_bs'),
            'ley1886_bs' => $filas->sum('total_ley1886_bs'),
            'facturado_bs' => $filas->sum('total_facturado_bs'),
            'cobrado_bs' => $filas->sum('total_cobrado_bs'),
        ];

        $html = View::make('reportes.comercial.resumen-operaciones-zonas', [
            'periodo' => $periodo,
            'filas' => $filas,
            'totales' => $totales,
            'empresa' => $this->getEmpresa(),
            'generadoPor' => auth()->user()?->name ?? 'Administración EMAPAP',
            'fechaImpresion' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();
    }

    /**
     * Genera la Planilla / Nómina de Cortes Masivos por Zona (repcorta.frx).
     */
    public function generarNominaCortesPdf(?int $idZona = null, int $mesesMoraMin = 2): string
    {
        $query = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->where('meses_mora', '>=', $mesesMoraMin)
            ->where('saldo_deuda', '>', 0)
            ->whereNotIn('estado_servicio', ['BAJA', 'CORTADO']);

        if ($idZona) {
            $query->where('id_zona', $idZona);
        }

        $abonados = $query->leftJoin('comercial.zonas', 'abonados.id_zona', '=', 'zonas.id')
            ->leftJoin('comercial.calles', 'abonados.id_calle', '=', 'calles.id')
            ->orderBy('zonas.nombre')
            ->orderBy('calles.nombre')
            ->orderBy('abonados.codigo')
            ->select('comercial.abonados.*')
            ->get();

        $zonaNombre = $idZona ? Zona::find($idZona)?->nombre : 'TODAS LAS ZONAS';

        $html = View::make('reportes.comercial.nomina-cortes', [
            'abonados' => $abonados,
            'zonaNombre' => $zonaNombre,
            'mesesMoraMin' => $mesesMoraMin,
            'totalDeuda' => $abonados->sum('saldo_deuda'),
            'empresa' => $this->getEmpresa(),
            'generadoPor' => auth()->user()?->name ?? 'Cuadrilla Operativa EMAPAP',
            'fechaImpresion' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();
    }
}
