<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ReporteMovimientoLogisticaDetalladoExport implements FromCollection, WithHeadings
{
    protected $planta_id;
    protected $almacen_id;
    protected $gestion_id;
    protected $grano_id;

    public function __construct($planta_id, $almacen_id, $gestion_id, $grano_id)
    {
        $this->planta_id = $planta_id;
        $this->almacen_id = $almacen_id;
        $this->gestion_id = $gestion_id;
        $this->grano_id = $grano_id;
    }
    public function headings(): array
    {
        return [
            'Codigo',
            'Nro_solicitud',
            'Codigo_solicitud',
            'Estado Logisitica',
            'Tipo',
            'fecha_solicitud',
            'Estado_solicitud',
            'Solicitud Origen',
            'Solictud Destino',
            'Tipo Producto',
            'Codigo Producto',
            'Producto',
            'Cantidad Solicitada',
            'Usuario_registro_solicitud',
            'cantidad_asignado_logistica',
            'usaurio_registro_logistica',
            'Contrato',
            'Tipo_contrato',
            'Transportadora',
            'Placa',
            'Marca_vehiculo',
            'Tipo_vehiculo',
            'Color_vehiculo',
            'Conductor',
            'CI_conductor', 
            'Nro_boleta_salida',
            'Origen',
            'Fecha_origen',
            'Cantidad_origen',
            'Usuario_registro_detalle',
            'Nro_boleta_ingreso',
            'Destino',
            'Fecha_destino',
            'Cantidad_destino',
            'Usuario_registro_ingreso',
            'Saldo',
        ];
    }

    public function collection()
    {
        ini_set('memory_limit', '-1');
        $reporteKardex = DB::select("SELECT * FROM inventario.sp_reporte_movimiento_logistica_detallado(" . $this->planta_id . "," . $this->almacen_id . "," . $this->gestion_id . "," . $this->grano_id . ")");

        return collect($reporteKardex);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:M1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00008B'],
                    ],
                ]);
            },
        ];
    }
}