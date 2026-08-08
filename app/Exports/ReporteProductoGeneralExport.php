<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteProductoGeneralExport implements FromCollection, WithHeadings, WithStyles
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $product_id;
    protected $xlote_id;
    protected $selling_point_id;

    public function __construct($fechaInicio, $fechaFin, $selling_point_id, $product_id, $xlote_id)
    {
        // var_dump($intervalo, $selling_point_id, $product_id, $xlote_id);
        // exit;
        $this->selling_point_id = $selling_point_id;
        $this->product_id = $product_id;
        $this->xlote_id = $xlote_id;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;

    }
    public function headings(): array
    {
        return [
            'Nº Boleta',
            'Fecha',
            'Tipo',
            'Codigo',
            'Tipo de Orden',
            'Nombre Origen',
            'Nombre Destino',
            'Productor',
            'Asociacion',
            'Transportadora',
            'Nombre Conductor',
            'Placa',
            'Entrada Cantidad',
            'Salida Cantidad',
            'Saldo Cantidad',
            'Lote',
            'Programa',
            'Campania',
            'mv_'
        ];
    }

    public function collection()
    {
        ini_set('memory_limit', '-1');
        $reporteKardex = DB::select("SELECT * FROM inventario.sp_calcular_kardex_valorado_lote_detallado(" . $this->selling_point_id . "," . $this->product_id . "," . $this->xlote_id . ",'" . $this->fechaInicio . "','" . $this->fechaFin . "')");

        return collect($reporteKardex);
    }
    public function styles(Worksheet $sheet)
    // estilos para el archivo excel
    {
        $highestRow = $sheet->getHighestRow();
        // $sheet->getStyle('A2:A' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '28A745'],
        //     ],
        // ]);
        // $sheet->getStyle('B2:B' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => 'D3D3D3'],
        //     ],
        // ]);
        // $sheet->getStyle('C2:C' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['argb' => '78A083'],
        //     ],
        // ]);
        // $sheet->getStyle('I2:I' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '007BFF'],
        //     ],
        // ]);
        // $sheet->getStyle('J2:J' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '007BFF'],
        //     ],
        // ]);
        // $sheet->getStyle('K2:K' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => '007BFF'],
        //     ],
        // ]);
        // $sheet->getStyle('E2:E' . $highestRow)->applyFromArray([
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'startColor' => ['rgb' => 'C70039'],
        //     ],
        // ]);
        // $sheet->getStyle('A1:R' . $highestRow)->applyFromArray([
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => Border::BORDER_THIN,
        //             'color' => ['argb' => '000000'],
        //         ],
        //     ],
        // ]);

        // Estilo de encabezado en negrita y tamaño 12 choco
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}