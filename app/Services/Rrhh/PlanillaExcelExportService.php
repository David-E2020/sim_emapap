<?php

declare(strict_types=1);

namespace App\Services\Rrhh;

use App\Http\Controllers\Rrhh\ReporteRrhhController;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlanillaExcelExportService
{
    protected ReporteRrhhController $reporteController;

    public function __construct(ReporteRrhhController $reporteController)
    {
        $this->reporteController = $reporteController;
    }

    /**
     * Genera el libro Excel (.xlsx) oficial multi-hoja de sueldos y aportes patronales.
     */
    public function exportarPlanillaMensual(int $mes, int $anio): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('EMAPAP - Sistema Integrado RRHH')
            ->setTitle("Planilla de Sueldos {$mes}-{$anio}")
            ->setSubject('Planilla Salarial y Aportes Patronales EMAPAP')
            ->setDescription('Reporte oficial generado automáticamente por el Sistema Integrado EMAPAP Patacamaya');

        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $mesNombre = $meses[$mes] ?? 'MES';

        // 1. Obtener datos de cada grupo
        $reqPlanta = new Request(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => 'PLANTA_PERMANENTE']);
        $resPlanta = $this->reporteController->planillaSueldosMensual($reqPlanta)->getData(true);
        $datosPlanta = $resPlanta['data'] ?? [];
        $patronal = $resPlanta['patronal'] ?? [];

        $reqEventual = new Request(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => 'PERSONAL_EVENTUAL']);
        $resEventual = $this->reporteController->planillaSueldosMensual($reqEventual)->getData(true);
        $datosEventual = $resEventual['data'] ?? [];

        $reqDir = new Request(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => 'DIETAS_DIRECTORIO']);
        $resDir = $this->reporteController->planillaSueldosMensual($reqDir)->getData(true);
        $datosDir = $resDir['data'] ?? [];

        // HOJA 1: PLANILLA PERSONAL (PLANTA)
        $sheetPlanta = $spreadsheet->getActiveSheet();
        $sheetPlanta->setTitle('PLANILLA PERSONAL');
        $this->construirHojaPlanta($sheetPlanta, $datosPlanta, $mesNombre, $mes, $anio);

        // HOJA 2: PERSONAL EVENTUAL
        $sheetEventual = $spreadsheet->createSheet();
        $sheetEventual->setTitle('PLANILLA EVENTUAL');
        $this->construirHojaEventual($sheetEventual, $datosEventual, $mesNombre, $mes, $anio);

        // HOJA 3: DIETAS DIRECTORIO
        $sheetDir = $spreadsheet->createSheet();
        $sheetDir->setTitle('PLANILLA DIETAS DIRECTORIO');
        $this->construirHojaDirectorio($sheetDir, $datosDir, $mesNombre, $mes, $anio);

        // HOJA 4: APORTES PATRONALES (17.21%)
        $sheetPat = $spreadsheet->createSheet();
        $sheetPat->setTitle('APORTES PATRONALES');
        $this->construirHojaPatronal($sheetPat, $patronal, (float)($resPlanta['total_ganado_bs'] ?? 0), $mesNombre, $mes, $anio);

        // Regresar a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = "PLANILLA_SUELDOS_EMAPA_{$mesNombre}_{$anio}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function construirHojaPlanta($sheet, array $items, string $mesNombre, int $mes, int $anio): void
    {
        $configLaboral = \App\Models\Rrhh\ConfiguracionLaboral::where('gestion', $anio)->first() 
            ?: \App\Models\Rrhh\ConfiguracionLaboral::orderBy('id', 'desc')->first();

        $nroMinTrabajo = $configLaboral?->nro_patronal_min_trabajo ?? '1002393029-1';
        $nroCns = $configLaboral?->nro_patronal_cns ?? '01-521-00002';
        $nitInst = $configLaboral?->nit_institucional ?? '1002393029';
        $dirInst = $configLaboral?->direccion_institucional ?? 'PLAZA BOLIVAR - ZONA ESTACION';
        $ubiInst = $configLaboral?->ubicacion_geografica ?? 'PATACAMAYA-LA PAZ-BOLIVIA';

        // Bloque Identificación Patronal a la Derecha
        $sheet->setCellValue('M1', "Nº EMPLEADOR MINIST. TRABAJO: {$nroMinTrabajo}");
        $sheet->mergeCells('M1:Q1');
        $sheet->setCellValue('M2', "Nº EMPLEADOR CAJA NACIONAL DE SALUD: {$nroCns}");
        $sheet->mergeCells('M2:Q2');
        $sheet->setCellValue('M3', "NIT: {$nitInst}");
        $sheet->mergeCells('M3:Q3');
        $sheet->setCellValue('M4', $ubiInst);
        $sheet->mergeCells('M4:Q4');
        $sheet->setCellValue('M5', $dirInst);
        $sheet->mergeCells('M5:Q5');
        $sheet->getStyle('M1:Q5')->getFont()->setSize(8)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));
        $sheet->getStyle('M1:Q5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Encabezado Institucional Principal
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO (EMAPAP)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F2942'));

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'PLANILLA DE PAGO DE SUELDOS Y SALARIOS — PERSONAL DE PLANTA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));

        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', "CORRESPONDIENTE AL MES DE {$mesNombre} DE {$anio}");
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0369A1'));

        $sheet->mergeCells('A4:L4');
        $sheet->setCellValue('A4', '(Expresado en Bolivianos)');
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(8)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Cabeceras de Columnas
        $headers = [
            'A5' => 'N°',
            'B5' => 'ITEM',
            'C5' => 'NOMBRES Y APELLIDOS',
            'D5' => 'CARGO',
            'E5' => 'C.I.',
            'F5' => 'DÍAS',
            'G5' => 'HABER BÁSICO',
            'H5' => 'BONO ANTIG.',
            'I5' => 'TOTAL GANADO',
            'J5' => 'VEJEZ (10%)',
            'K5' => 'RIESGO (1.71%)',
            'L5' => 'COMIS. (0.5%)',
            'M5' => 'SOLID. (0.5%)',
            'N5' => 'TOTAL GESTORA',
            'O5' => 'ATRASOS/OTROS',
            'P5' => 'TOTAL DESC.',
            'Q5' => 'LÍQUIDO PAGABLE',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A5:Q5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Filas de Datos
        $row = 6;
        $idx = 1;
        foreach ($items as $it) {
            $totGanado = (float)($it['total_ganado'] ?? 0);
            $desglose = $it['desglose_gestora'] ?? [];
            $v10 = !empty($desglose['vejez_10']) ? (float)$desglose['vejez_10'] : round($totGanado * 0.10, 2);
            $r171 = !empty($desglose['riesgo_1_71']) ? (float)$desglose['riesgo_1_71'] : round($totGanado * 0.0171, 2);
            $c05 = !empty($desglose['comision_0_5']) ? (float)$desglose['comision_0_5'] : round($totGanado * 0.005, 2);
            $s05 = !empty($desglose['solidario_0_5']) ? (float)$desglose['solidario_0_5'] : round($totGanado * 0.005, 2);
            $totGestora = (float)($it['gestora_12_71'] ?? round($v10 + $r171 + $c05 + $s05, 2));

            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", $it['item'] ?? '-');
            $sheet->setCellValue("C{$row}", $it['funcionario'] ?? '');
            $sheet->setCellValue("D{$row}", $it['cargo'] ?? '');
            $sheet->setCellValue("E{$row}", $it['ci'] ?? '');
            $sheet->setCellValue("F{$row}", $it['dias_trabajados'] ?? 30);
            $sheet->setCellValue("G{$row}", (float)($it['haber_basico'] ?? 0));
            $sheet->setCellValue("H{$row}", (float)($it['bono_antiguedad'] ?? 0));
            $sheet->setCellValue("I{$row}", $totGanado);
            $sheet->setCellValue("J{$row}", $v10);
            $sheet->setCellValue("K{$row}", $r171);
            $sheet->setCellValue("L{$row}", $c05);
            $sheet->setCellValue("M{$row}", $s05);
            $sheet->setCellValue("N{$row}", $totGestora);
            $sheet->setCellValue("O{$row}", (float)($it['descuento_atraso'] ?? 0));
            $sheet->setCellValue("P{$row}", (float)($it['total_descuentos'] ?? $totGestora));
            $sheet->setCellValue("Q{$row}", (float)($it['liquido_salarial'] ?? ($totGanado - ($it['total_descuentos'] ?? $totGestora))));

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:Q{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
            $idx++;
        }

        // Fila Totalizadora
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTALES GENERALES EN BOLIVIANOS:');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $startRow = 6;
        $endRow = $row - 1;
        $sheet->setCellValue("G{$row}", "=SUM(G{$startRow}:G{$endRow})");
        $sheet->setCellValue("H{$row}", "=SUM(H{$startRow}:H{$endRow})");
        $sheet->setCellValue("I{$row}", "=SUM(I{$startRow}:I{$endRow})");
        $sheet->setCellValue("J{$row}", "=SUM(J{$startRow}:J{$endRow})");
        $sheet->setCellValue("K{$row}", "=SUM(K{$startRow}:K{$endRow})");
        $sheet->setCellValue("L{$row}", "=SUM(L{$startRow}:L{$endRow})");
        $sheet->setCellValue("M{$row}", "=SUM(M{$startRow}:M{$endRow})");
        $sheet->setCellValue("N{$row}", "=SUM(N{$startRow}:N{$endRow})");
        $sheet->setCellValue("O{$row}", "=SUM(O{$startRow}:O{$endRow})");
        $sheet->setCellValue("P{$row}", "=SUM(P{$startRow}:P{$endRow})");
        $sheet->setCellValue("Q{$row}", "=SUM(Q{$startRow}:Q{$endRow})");

        $totRange = "A{$row}:Q{$row}";
        $sheet->getStyle($totRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($totRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("G{$row}:Q{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Bordes de la tabla principal
        $sheet->getStyle("A5:Q{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        // Cuadro Resumen Institucional Aportes (filas 25 a 32 del Excel oficial)
        $rTot = $row;
        $rRes = $row + 2;

        $resumenItems = [
            ['AFP Laboral 12,71%', "=N{$rTot}"],
            ['Total AFP Laboral', "=D{$rRes}"],
            ['AFP Patronal (GESTORA PUBLICA) 7,21%', "=ROUND(I{$rTot}*0.0721, 2)"],
            ['CNS Aporte Patronal 10%', "=ROUND(I{$rTot}*0.10, 2)"],
            ['Total aporte patronal', "=D" . ($rRes + 2) . "+D" . ($rRes + 3)],
            ['Costo Total Empresa', "=I{$rTot}+D" . ($rRes + 4)],
        ];

        foreach ($resumenItems as $item) {
            $sheet->mergeCells("B{$rRes}:C{$rRes}");
            $sheet->setCellValue("B{$rRes}", $item[0]);
            $sheet->setCellValue("D{$rRes}", $item[1]);
            $sheet->getStyle("B{$rRes}:D{$rRes}")->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle("D{$rRes}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("B{$rRes}:D{$rRes}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
            $rRes++;
        }

        // Bloque de Firma Institucional
        $rFirma = $rRes + 2;
        $sheet->mergeCells("C{$rFirma}:G{$rFirma}");
        $sheet->setCellValue("C{$rFirma}", 'DAVID FERNANDO RAMIREZ CAPIA');
        $sheet->getStyle("C{$rFirma}")->getFont()->setBold(true)->setSize(9);

        $rFirma++;
        $sheet->mergeCells("C{$rFirma}:G{$rFirma}");
        $sheet->setCellValue("C{$rFirma}", 'GERENTE GENERAL / REPRESENTANTE LEGAL');
        $sheet->getStyle("C{$rFirma}")->getFont()->setSize(8)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $sheet->mergeCells("I" . ($rFirma - 1) . ":L" . ($rFirma - 1));
        $sheet->setCellValue("I" . ($rFirma - 1), 'C.I. 4041212 QR');
        $sheet->getStyle("I" . ($rFirma - 1))->getFont()->setBold(true)->setSize(9);

        $sheet->mergeCells("I{$rFirma}:L{$rFirma}");
        $sheet->setCellValue("I{$rFirma}", 'Nº DE DOCUMENTO DE IDENTIDAD');
        $sheet->getStyle("I{$rFirma}")->getFont()->setSize(8)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function construirHojaEventual($sheet, array $items, string $mesNombre, int $mes, int $anio): void
    {
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA (EMAPAP)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "PLANILLA DE SUELDOS - PERSONAL EVENTUAL - MES DE {$mesNombre} DE {$anio}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'N°',
            'B4' => 'ITEM',
            'C4' => 'NOMBRES Y APELLIDOS',
            'D4' => 'CARGO',
            'E4' => 'C.I.',
            'F4' => 'DÍAS',
            'G4' => 'HABER MENSUAL',
            'H4' => 'TOTAL GANADO',
            'I4' => 'LÍQUIDO PAGABLE',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A4:I4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0369A1');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $idx = 1;
        foreach ($items as $it) {
            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", $it['item'] ?? '-');
            $sheet->setCellValue("C{$row}", $it['funcionario'] ?? '');
            $sheet->setCellValue("D{$row}", $it['cargo'] ?? '');
            $sheet->setCellValue("E{$row}", $it['ci'] ?? '');
            $sheet->setCellValue("F{$row}", $it['dias_trabajados'] ?? 30);
            $sheet->setCellValue("G{$row}", (float)($it['haber_basico'] ?? 0));
            $sheet->setCellValue("H{$row}", (float)($it['total_ganado'] ?? 0));
            $sheet->setCellValue("I{$row}", (float)($it['liquido_pagable_total'] ?? $it['total_ganado']));

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
            $idx++;
        }

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL PERSONAL EVENTUAL:');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $startRow = 5;
        $endRow = max(5, $row - 1);
        $sheet->setCellValue("G{$row}", "=SUM(G{$startRow}:G{$endRow})");
        $sheet->setCellValue("H{$row}", "=SUM(H{$startRow}:H{$endRow})");
        $sheet->setCellValue("I{$row}", "=SUM(I{$startRow}:I{$endRow})");

        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A4:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function construirHojaDirectorio($sheet, array $items, string $mesNombre, int $mes, int $anio): void
    {
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA (EMAPAP)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', "PLANILLA DE DIETAS - HONORABLE DIRECTORIO - MES DE {$mesNombre} DE {$anio}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'N°',
            'B4' => 'ITEM',
            'C4' => 'NOMBRES Y APELLIDOS',
            'D4' => 'CARGO DIRECTORIO',
            'E4' => 'C.I.',
            'F4' => 'SESIONES',
            'G4' => 'DIETA POR SESIÓN',
            'H4' => 'TOTAL DIETAS (BS)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A4:H4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF7C3AED');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $idx = 1;
        foreach ($items as $it) {
            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", $it['item'] ?? '-');
            $sheet->setCellValue("C{$row}", $it['funcionario'] ?? '');
            $sheet->setCellValue("D{$row}", $it['cargo'] ?? '');
            $sheet->setCellValue("E{$row}", $it['ci'] ?? '');
            $sheet->setCellValue("F{$row}", $it['dias_trabajados'] ?? 1);
            $sheet->setCellValue("G{$row}", (float)($it['haber_basico'] ?? 0));
            $sheet->setCellValue("H{$row}", (float)($it['total_ganado'] ?? 0));

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
            $idx++;
        }

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL DIETAS DIRECTORIO:');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $startRow = 5;
        $endRow = max(5, $row - 1);
        $sheet->setCellValue("G{$row}", "-");
        $sheet->setCellValue("H{$row}", "=SUM(H{$startRow}:H{$endRow})");

        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A4:H{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function construirHojaPatronal($sheet, array $patronal, float $totalGanado, string $mesNombre, int $mes, int $anio): void
    {
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA (EMAPAP)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', "RESUMEN INSTITUCIONAL DE APORTES PATRONALES (17.21%) - MES DE {$mesNombre} DE {$anio}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'N°',
            'B4' => 'CONCEPTO / INSTITUCIÓN BENEFICIARIA',
            'C4' => 'BASE DE CÁLCULO (TOTAL GANADO)',
            'D4' => 'PORCENTAJE DE LEY',
            'E4' => 'APORTE PATRONAL (BS.)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A4:E4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $filas = [
            [
                'nro' => 1,
                'concepto' => 'Caja Nacional de Salud (C.N.S. Patronal)',
                'base' => $totalGanado,
                'pct' => '10.00%',
                'monto' => (float)($patronal['cns_10_bs'] ?? 0),
            ],
            [
                'nro' => 2,
                'concepto' => 'Gestora - Riesgo Profesional Patronal',
                'base' => $totalGanado,
                'pct' => '1.71%',
                'monto' => (float)($patronal['riesgo_profesional_1_71_bs'] ?? 0),
            ],
            [
                'nro' => 3,
                'concepto' => 'Gestora - Fondo Pro-Vivienda Social Patronal',
                'base' => $totalGanado,
                'pct' => '2.00%',
                'monto' => (float)($patronal['pro_vivienda_2_bs'] ?? 0),
            ],
            [
                'nro' => 4,
                'concepto' => 'Gestora - Fondo Solidario Patronal',
                'base' => $totalGanado,
                'pct' => '3.00%',
                'monto' => (float)($patronal['solidario_patronal_3_bs'] ?? 0),
            ],
            [
                'nro' => 5,
                'concepto' => 'Gestora - Comisión Patronal Gestora',
                'base' => $totalGanado,
                'pct' => '0.50%',
                'monto' => (float)($patronal['comision_patronal_0_5_bs'] ?? round($totalGanado * 0.005, 2)),
            ],
        ];

        $row = 5;
        foreach ($filas as $f) {
            $sheet->setCellValue("A{$row}", $f['nro']);
            $sheet->setCellValue("B{$row}", $f['concepto']);
            $sheet->setCellValue("C{$row}", $f['base']);
            $sheet->setCellValue("D{$row}", $f['pct']);
            $sheet->setCellValue("E{$row}", $f['monto']);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
        }

        // Subtotal Gestora Patronal 7.21%
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'SUBTOTAL GESTORA PÚBLICA PATRONAL (7.21%):');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("D{$row}", '7.21%');
        $sheet->setCellValue("E{$row}", (float)($patronal['gestora_7_21_bs'] ?? round($totalGanado * 0.0721, 2)));
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        // Total Patronal 17.21%
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL CARGA SOCIAL PATRONAL (17.21%):');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("D{$row}", '17.21%');
        $sheet->setCellValue("E{$row}", (float)($patronal['total_patronal_bs'] ?? 0));
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        // Costo Total Empresa
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'COSTO TOTAL EMPRESA (TOTAL GANADO + APORTES PATRONALES):');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("D{$row}", '-');
        $sheet->setCellValue("E{$row}", (float)($patronal['costo_total_empresa_bs'] ?? 0));
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5');
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A4:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
