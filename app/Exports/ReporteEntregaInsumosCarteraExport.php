<?php

namespace App\Exports;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteEntregaInsumosCarteraExport implements FromCollection, WithHeadings
{

    public function __construct()
    {

    }
    public function headings(): array
    {
        return [
            'Boleta nro pago',
            'boleta cantidad',
            'boleta precio',
            'boleta total',
            'boleta descuento',
            'producto id',
            'categoria nombre',
            'productor nombre',
            'productor paterno',
            'productor materno',
            'contrato id',
            'codigo contrato',
            'contrato subencion'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1'); 

        $reporteMovimiento = DB::select("SELECT * FROM inventario.sp_reporte_entrega_insumos_cartera()");

        return collect($reporteMovimiento);
    }
}