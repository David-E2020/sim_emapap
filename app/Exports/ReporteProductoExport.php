<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteProductoExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    private $planta_id;
    private $almacen_id;
    private $gestion_id;
    private $grano_id;

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
            ['REPORTE GENERAL DEL MOVIMIENTO DE PRODUCTO'], [
                'CODIGO SOLICITUD',
                'TIPO DE SOLICITUD',
                'PRODUCTO',
                'FECHA DE SOLICITUD',
                'NRO. BOLETA SALIDA',
                'ORIGEN',
                'FECHA SALIDA',
                'CONDUCTOR/CHOFER',
                'PLACA',
                'CANTIDAD SALIDA',
                'NRO. BOLETA INGRESO',
                'DESTINO',
                'FECHA INGRESO',
                'PLACA',
                'CANTIDAD INGRESO',
                'DIFERENCIA',
            ]
        ];
    }

    public function collection()
    {
        ini_set('memory_limit', '-1');
        /*$fecha_inicio = Carbon::parse($this->intervalo[0])->setTimezone('America/La_Paz');
        $fecha_ini = $fecha_inicio->format('Y-m-d');
        $fecha_final = Carbon::parse($this->intervalo[1])->setTimezone('America/La_Paz');
        $fecha_fin = $fecha_final->format('Y-m-d');*/
        $reporteSaldos = DB::select("SELECT * FROM inventario.sp_reporte_movimiento_productos_almacen(" . $this->planta_id . "," . $this->almacen_id . "," . $this->gestion_id . "," . $this->grano_id . ")");
        return collect($reporteSaldos);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal('center');

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2874A6']],
        ]);
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D2B4DE'
                ]
            ]
        ]);
        $sheet->getStyle('E2:I2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '7DCEA0'
                ]
            ]
        ]);
        $sheet->getStyle('J2:M2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F4D03F'
                ]
            ]
        ]);
        $sheet->getStyle('N2:N2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'EC7063'
                ]
            ]
        ]);
        $sheet->getStyle('A1:N2')->applyFromArray($borderStyle);
        return $sheet;
    }
}
