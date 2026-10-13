<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\ConfiguracionEmpresa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteFacturasExcelService
{
    /**
     * Genera y transmite la Planilla Oficial de Facturas Emitidas en formato Excel XLSX profesional.
     */
    public function exportarXlsx(
        Builder $query,
        array $filtros,
        string $generadoPor = 'Administración EMAPAP',
        int $limite = 25000
    ): StreamedResponse {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $razonSocial = $empresa->razon_social ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - EMAPA';
        $nitEmpresa = $empresa->nit ?? '1002393029';

        $totalRegistrosEnBd = (clone $query)->count();
        $facturas = $query->reorder('numero_factura', 'desc')->limit($limite)->get();

        $totalValidadas = 0;
        $totalAnuladas = 0;
        $totalContingencia = 0;
        $montoTotalValidadas = 0.0;
        $totalTasasValidas = 0.0;
        $totalBaseDebitoValidas = 0.0;
        $totalDebitoValidas = 0.0;

        foreach ($facturas as $f) {
            $st = strtoupper((string) $f->estado_factura);
            if ($st === 'ANULADA' || $st === 'CANCELLED') {
                $totalAnuladas++;
            } else {
                if ($st === 'VALIDADA' || $st === 'VALIDATED') {
                    $totalValidadas++;
                } elseif ($st === 'CONTINGENCIA' || (int) $f->tipo_emision === 2) {
                    $totalContingencia++;
                }
                $tot = (float) $f->monto_total;
                $base = (float) $f->monto_total_sujeto_iva;
                $desc = (float) $f->monto_descuento;
                $tasas = ($f->tasa_aseo !== null && (float) $f->tasa_aseo > 0)
                    ? (float) $f->tasa_aseo
                    : max(0.0, round($tot - $base - $desc, 2));

                $montoTotalValidadas += $tot;
                $totalTasasValidas += $tasas;
                $totalBaseDebitoValidas += $base;
            }
        }
        $totalDebitoValidas = round($totalBaseDebitoValidas * 0.13, 2);

        $spreadsheet = new Spreadsheet();
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->disableBranchPruning();
        $spreadsheet->getProperties()
            ->setCreator('EMAPA - Sistema Integrado')
            ->setLastModifiedBy($generadoPor)
            ->setTitle('Planilla Oficial de Facturas Emitidas')
            ->setSubject('Reporte Fiscal SIAT')
            ->setDescription('Documento oficial de facturación generado por el Sistema EMAPA.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Facturas Emitidas');
        $sheet->setShowGridLines(true);

        // 1. CABECERA INSTITUCIONAL (Filas 1 a 3)
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', "{$razonSocial} (NIT: {$nitEmpresa})");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '004D40']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'BANDEJA OFICIAL DE FACTURAS EMITIDAS - SISTEMA DE FACTURACIÓN ELECTRÓNICA SIAT');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '004D40']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        $rangoTexto = (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']))
            ? ('Desde: ' . ($filtros['fecha_inicio'] ?? 'Inicio') . ' Hasta: ' . ($filtros['fecha_fin'] ?? 'Hoy'))
            : 'Histórico General';
        $criterioTexto = !empty($filtros['search']) ? (' | Búsqueda: ' . $filtros['search']) : '';
        $estadoTexto = (!empty($filtros['estado']) && $filtros['estado'] !== 'TODOS') ? (' | Estado: ' . $filtros['estado']) : '';
        $fechaHora = Carbon::now()->format('d/m/Y H:i:s');
        $infoAuditoria = "Período: {$rangoTexto}{$criterioTexto}{$estadoTexto} | Generado por: {$generadoPor} el {$fechaHora}";

        if ($totalRegistrosEnBd > count($facturas)) {
            $infoAuditoria .= " | [Mostrando " . number_format(count($facturas)) . " de " . number_format($totalRegistrosEnBd) . " registros]";
        }

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3', $infoAuditoria);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '546E7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F7F8']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // 2. TARJETAS KPI RESUMEN (Filas 5 y 6)
        $kpiCols = [
            ['cols' => 'B', 'colFin' => 'C', 'titulo' => 'TOTAL FACTURAS', 'valor' => count($facturas), 'color' => '004D40', 'bg' => 'E0F2F1'],
            ['cols' => 'D', 'colFin' => 'E', 'titulo' => 'VALIDADAS SIN', 'valor' => $totalValidadas, 'color' => '2E7D32', 'bg' => 'E8F5E9'],
            ['cols' => 'F', 'colFin' => 'G', 'titulo' => 'CONTINGENCIA', 'valor' => $totalContingencia, 'color' => 'E65100', 'bg' => 'FFF3E0'],
            ['cols' => 'H', 'colFin' => 'I', 'titulo' => 'ANULADAS', 'valor' => $totalAnuladas, 'color' => 'C62828', 'bg' => 'FFEBEE'],
            ['cols' => 'J', 'colFin' => 'K', 'titulo' => 'TOTAL FACTURADO', 'valor' => 'Bs ' . number_format($montoTotalValidadas, 2), 'color' => '004D40', 'bg' => 'E0F2F1'],
            ['cols' => 'L', 'colFin' => 'M', 'titulo' => 'TASAS NO IVA', 'valor' => 'Bs ' . number_format($totalTasasValidas, 2), 'color' => '0277BD', 'bg' => 'E1F5FE'],
            ['cols' => 'N', 'colFin' => 'O', 'titulo' => 'BASE DÉBITO FISCAL', 'valor' => 'Bs ' . number_format($totalBaseDebitoValidas, 2), 'color' => '2E7D32', 'bg' => 'E8F5E9'],
            ['cols' => 'P', 'colFin' => 'P', 'titulo' => 'IVA DF (13%)', 'valor' => 'Bs ' . number_format($totalDebitoValidas, 2), 'color' => '4527A0', 'bg' => 'EDE7F6'],
        ];

        foreach ($kpiCols as $kpi) {
            $cIni = $kpi['cols'];
            $cFin = $kpi['colFin'];

            $sheet->mergeCells("{$cIni}5:{$cFin}5");
            $sheet->setCellValue("{$cIni}5", $kpi['titulo']);
            $sheet->getStyle("{$cIni}5:{$cFin}5")->applyFromArray([
                'font' => ['bold' => true, 'size' => 7.5, 'color' => ['rgb' => '546E7A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FAFAFA']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
                ],
            ]);

            $sheet->mergeCells("{$cIni}6:{$cFin}6");
            $sheet->setCellValue("{$cIni}6", $kpi['valor']);
            $sheet->getStyle("{$cIni}6:{$cFin}6")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $kpi['color']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $kpi['bg']]],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $kpi['color']]],
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
                ],
            ]);
        }
        $sheet->getRowDimension(5)->setRowHeight(16);
        $sheet->getRowDimension(6)->setRowHeight(22);

        // 3. ENCABEZADOS DE TABLA (Fila 8)
        $headers = [
            'A' => 'N°',
            'B' => 'N° FACTURA',
            'C' => 'FECHA EMISIÓN',
            'D' => 'CÓDIGO ABONADO',
            'E' => 'TIPO DOC',
            'F' => 'N° DOC / NIT',
            'G' => 'COMPL.',
            'H' => 'RAZÓN SOCIAL / CLIENTE',
            'I' => 'ESTADO SIN',
            'J' => 'MODALIDAD',
            'K' => 'MONTO TOTAL (BS)',
            'L' => 'TASAS NO IVA (BS)',
            'M' => 'BASE DÉBITO (BS)',
            'N' => 'DÉBITO FISCAL 13%',
            'O' => 'DESCUENTO (BS)',
            'P' => 'CÓDIGO AUTORIZACIÓN (CUF)',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue("{$col}8", $text);
        }

        $sheet->getStyle('A8:P8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00695C']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '004D40']],
            ],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(28);

        // 4. FILAS DE DATOS (Fila 9 en adelante)
        $row = 9;
        $startDataRow = $row;

        foreach ($facturas as $index => $f) {
            $esAnulada = in_array(strtoupper((string) $f->estado_factura), ['ANULADA', 'CANCELLED'], true);
            $tipoEmisionStr = ((int) $f->tipo_emision === 2) ? 'Contingencia' : 'En Línea';
            $codigoAbonado = $f->abonado ? (string) $f->abonado->codigo : '';
            $fechaFmt = $f->fecha_emision ? Carbon::parse($f->fecha_emision)->format('d/m/Y H:i') : '-';

            $totalVenta = $esAnulada ? 0.00 : (float) $f->monto_total;
            $baseFiscal = $esAnulada ? 0.00 : (float) $f->monto_total_sujeto_iva;
            $descuento = $esAnulada ? 0.00 : (float) $f->monto_descuento;
            $tasas = $esAnulada ? 0.00 : (($f->tasa_aseo !== null && (float) $f->tasa_aseo > 0) ? (float) $f->tasa_aseo : max(0.00, round($totalVenta - $baseFiscal - $descuento, 2)));
            $debitoFiscal = round($baseFiscal * 0.13, 2);

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $f->numero_factura);
            $sheet->setCellValue("C{$row}", $fechaFmt);
            $sheet->setCellValueExplicit("D{$row}", $codigoAbonado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", $f->codigo_tipo_documento_identidad ?? 1);
            $sheet->setCellValueExplicit("F{$row}", (string) $f->numero_documento, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", $f->complemento ?? '');
            $sheet->setCellValue("H{$row}", $f->nombre_razon_social);
            $sheet->setCellValue("I{$row}", $f->estado_factura);
            $sheet->setCellValue("J{$row}", $tipoEmisionStr);
            $sheet->setCellValue("K{$row}", $totalVenta);
            $sheet->setCellValue("L{$row}", $tasas);
            $sheet->setCellValue("M{$row}", $baseFiscal);
            $sheet->setCellValue("N{$row}", $debitoFiscal);
            $sheet->setCellValue("O{$row}", $descuento);
            $sheet->setCellValueExplicit("P{$row}", (string) $f->cuf, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $bgRow = ($row % 2 === 0) ? 'F9FBFC' : 'FFFFFF';
            $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgRow]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("I{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}:O{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle("K{$row}:O{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            if ($esAnulada) {
                $sheet->getStyle("A{$row}:P{$row}")->getFont()->getColor()->setRGB('9E9E9E');
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('C62828');
                $sheet->getStyle("I{$row}")->getFont()->setBold(true);
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $lastDataRow = $row - 1;

        // 5. FILA DE TOTALES GENERALES (Fórmulas)
        $sheet->mergeCells("A{$row}:J{$row}");
        $sheet->setCellValue("A{$row}", 'TOTALES GENERALES FACTURADOS (BS):');

        if ($lastDataRow >= $startDataRow) {
            $sheet->setCellValue("K{$row}", "=SUM(K{$startDataRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$row}", "=SUM(L{$startDataRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$row}", "=SUM(M{$startDataRow}:M{$lastDataRow})");
            $sheet->setCellValue("N{$row}", "=SUM(N{$startDataRow}:N{$lastDataRow})");
            $sheet->setCellValue("O{$row}", "=SUM(O{$startDataRow}:O{$lastDataRow})");
        } else {
            $sheet->setCellValue("K{$row}", 0.00);
            $sheet->setCellValue("L{$row}", 0.00);
            $sheet->setCellValue("M{$row}", 0.00);
            $sheet->setCellValue("N{$row}", 0.00);
            $sheet->setCellValue("O{$row}", 0.00);
        }

        $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '004D40']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '004D40']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '004D40']],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B2DFDB']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("K{$row}:O{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("K{$row}:O{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getRowDimension($row)->setRowHeight(26);

        $sheet->freezePane('A9');

        $columnasAuto = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
        foreach ($columnasAuto as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fechaArchivo = Carbon::now()->format('Ymd_His');
        $filename = "Planilla_Facturas_EMAPA_{$fechaArchivo}.xlsx";

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headersResponse);
    }

    /**
     * Genera el Libro de Ventas RCV Oficial con 2 Hojas:
     * Hoja 1: "Constancia RCV" (Idéntica al formato oficial del SIN según la fotografía)
     * Hoja 2: "Libro de Ventas" (Las 22 columnas oficiales de la RND RCV del SIN)
     */
    public function exportarLibroVentasRcvXlsx(
        Builder $query,
        array $filtros,
        array $resumen,
        array $consolidacionRcv,
        string $generadoPor = 'Administración EMAPA',
        int $limite = 25000
    ): StreamedResponse {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $spreadsheet = new Spreadsheet();
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->disableBranchPruning();

        $spreadsheet->getProperties()
            ->setCreator('EMAPA - Sistema Integrado')
            ->setLastModifiedBy($generadoPor)
            ->setTitle('Constancia y Libro de Ventas RCV - SIN')
            ->setSubject('Registro de Compras y Ventas IVA')
            ->setDescription('Documento oficial RCV generado conforme a la normativa del Servicio de Impuestos Nacionales.');

        // =========================================================================
        // HOJA 1: CONSTANCIA DE REGISTRO DE COMPRAS Y VENTAS (FORMATO OFICIAL SIN)
        // =========================================================================
        $sheetConstancia = $spreadsheet->getActiveSheet();
        $sheetConstancia->setTitle('Constancia RCV');
        $sheetConstancia->setShowGridLines(true);

        // Título del reporte
        $sheetConstancia->mergeCells('B2:G2');
        $sheetConstancia->setCellValue('B2', 'CONSTANCIA DE REGISTRO DE COMPRAS Y VENTAS');
        $sheetConstancia->getStyle('B2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '212121']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheetConstancia->getRowDimension(2)->setRowHeight(25);

        $sheetConstancia->mergeCells('B3:G3');
        $sheetConstancia->setCellValue('B3', 'N°: 1');
        $sheetConstancia->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '424242']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Tabla de Metadatos Institucionales (B5:G10)
        $metaData = [
            ['campo' => 'NIT:', 'valor' => $consolidacionRcv['nit'] ?? '1002393029'],
            ['campo' => 'Razón Social:', 'valor' => $consolidacionRcv['razon_social'] ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - EMAPA'],
            ['campo' => 'Periodo:', 'valor' => $consolidacionRcv['periodo'] ?? Carbon::now()->month],
            ['campo' => 'Gestión:', 'valor' => $consolidacionRcv['gestion'] ?? Carbon::now()->year],
            ['campo' => 'Fecha y Hora:', 'valor' => $consolidacionRcv['fecha_hora'] ?? Carbon::now()->format('d/m/Y h:i:s A')],
            ['campo' => 'Estado:', 'valor' => $consolidacionRcv['estado'] ?? 'ORIGINAL'],
        ];

        $rMeta = 5;
        foreach ($metaData as $m) {
            $sheetConstancia->mergeCells("B{$rMeta}:C{$rMeta}");
            $sheetConstancia->setCellValue("B{$rMeta}", $m['campo']);
            $sheetConstancia->mergeCells("D{$rMeta}:G{$rMeta}");
            $sheetConstancia->setCellValueExplicit("D{$rMeta}", (string) $m['valor'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheetConstancia->getStyle("B{$rMeta}:C{$rMeta}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '757575']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']],
            ]);
            $sheetConstancia->getStyle("D{$rMeta}:G{$rMeta}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '757575']]],
            ]);
            $sheetConstancia->getRowDimension($rMeta)->setRowHeight(18);
            $rMeta++;
        }

        // Encabezado INFORMACIÓN DE CONSOLIDACIÓN
        $rMeta++;
        $sheetConstancia->mergeCells("B{$rMeta}:G{$rMeta}");
        $sheetConstancia->setCellValue("B{$rMeta}", 'INFORMACIÓN DE CONSOLIDACIÓN');
        $sheetConstancia->getStyle("B{$rMeta}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '212121']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '757575']]],
        ]);
        $sheetConstancia->getRowDimension($rMeta)->setRowHeight(22);

        // Subtítulo VENTAS y COMPRAS
        $rMeta++;
        $sheetConstancia->mergeCells("B{$rMeta}:D{$rMeta}");
        $sheetConstancia->setCellValue("B{$rMeta}", 'VENTAS');
        $sheetConstancia->mergeCells("E{$rMeta}:G{$rMeta}");
        $sheetConstancia->setCellValue("E{$rMeta}", 'COMPRAS');
        $sheetConstancia->getStyle("B{$rMeta}:G{$rMeta}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '757575']]],
        ]);
        $sheetConstancia->getRowDimension($rMeta)->setRowHeight(20);

        // Filas de Consolidación (Exactas al SIN)
        $ventasDatos = [
            ['desc' => 'Cantidad Facturas:', 'valor' => $consolidacionRcv['ventas']['cantidad_facturas'] ?? $resumen['total_registros'] ?? 0, 'es_monto' => false],
            ['desc' => 'Facturas Válidas:', 'valor' => $consolidacionRcv['ventas']['facturas_validas'] ?? ($resumen['cantidad_validas'] + ($resumen['cantidad_contingencias'] ?? 0)), 'es_monto' => false],
            ['desc' => 'Facturas Anuladas:', 'valor' => $consolidacionRcv['ventas']['facturas_anuladas'] ?? $resumen['cantidad_anuladas'] ?? 0, 'es_monto' => false],
            ['desc' => 'Importe Total:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_total'] ?? $resumen['total_facturado'] ?? 0), 'es_monto' => true],
            ['desc' => 'Importe No Sujetas IVA:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_no_sujetas_iva'] ?? $resumen['total_tasas'] ?? 0), 'es_monto' => true],
            ['desc' => 'Importe ICE:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe IEHD:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe IPJ:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Tasa Cero:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Tasas:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_tasas'] ?? $resumen['total_tasas'] ?? 0), 'es_monto' => true],
            ['desc' => 'Importe Otros No IVA:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Exentas:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Descuentos:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_descuentos'] ?? $resumen['total_descuento'] ?? 0), 'es_monto' => true],
            ['desc' => 'Importe Gift Card:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Sin Derecho DF:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Con Derecho DF:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_con_derecho_df'] ?? $resumen['total_base_debito_fiscal'] ?? 0), 'es_monto' => true],
            ['desc' => 'Importe IVA DF:', 'valor' => (float) ($consolidacionRcv['ventas']['importe_iva_df'] ?? $resumen['debito_fiscal_iva'] ?? 0), 'es_monto' => true],
        ];

        $comprasDatos = [
            ['desc' => 'Cantidad Facturas:', 'valor' => 0, 'es_monto' => false],
            ['desc' => 'Importe Total:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe No Sujetas IVA:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe ICE:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe IPJ:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe IEHD:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Tasa Cero:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Tasas:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Otros No IVA:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Exentas:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Descuentos:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Gift Card:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Sin Derecho CF:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe Con Derecho CF:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => 'Importe IVA CF:', 'valor' => 0.00, 'es_monto' => true],
            ['desc' => '', 'valor' => '', 'es_monto' => false],
            ['desc' => '', 'valor' => '', 'es_monto' => false],
        ];

        $rMeta++;
        for ($i = 0; $i < count($ventasDatos); $i++) {
            $v = $ventasDatos[$i];
            $c = $comprasDatos[$i] ?? ['desc' => '', 'valor' => '', 'es_monto' => false];

            // Columna B-C: Descripción Ventas
            $sheetConstancia->mergeCells("B{$rMeta}:C{$rMeta}");
            $sheetConstancia->setCellValue("B{$rMeta}", $v['desc']);
            $sheetConstancia->setCellValue("D{$rMeta}", $v['valor']);

            // Columna E-F: Descripción Compras
            $sheetConstancia->mergeCells("E{$rMeta}:F{$rMeta}");
            $sheetConstancia->setCellValue("E{$rMeta}", $c['desc']);
            $sheetConstancia->setCellValue("G{$rMeta}", $c['valor']);

            $sheetConstancia->getStyle("B{$rMeta}:G{$rMeta}")->applyFromArray([
                'font' => ['size' => 8.5],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDBDBD']]],
            ]);
            $sheetConstancia->getStyle("B{$rMeta}")->getFont()->setBold(true);
            $sheetConstancia->getStyle("E{$rMeta}")->getFont()->setBold(true);

            if ($v['es_monto']) {
                $sheetConstancia->getStyle("D{$rMeta}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheetConstancia->getStyle("D{$rMeta}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            } else {
                $sheetConstancia->getStyle("D{$rMeta}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            if ($c['es_monto']) {
                $sheetConstancia->getStyle("G{$rMeta}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheetConstancia->getStyle("G{$rMeta}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            } else {
                $sheetConstancia->getStyle("G{$rMeta}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Resaltar totales y base imponible
            if (in_array($v['desc'], ['Importe Total:', 'Importe Con Derecho DF:', 'Importe IVA DF:'], true)) {
                $sheetConstancia->getStyle("B{$rMeta}:D{$rMeta}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1B5E20']],
                ]);
            }

            $sheetConstancia->getRowDimension($rMeta)->setRowHeight(18);
            $rMeta++;
        }

        // NOTAS CRÉDITO DÉBITO
        $rMeta++;
        $sheetConstancia->mergeCells("B{$rMeta}:D{$rMeta}");
        $sheetConstancia->setCellValue("B{$rMeta}", 'NOTAS CRÉDITO DÉBITO EMITIDAS');
        $sheetConstancia->mergeCells("E{$rMeta}:G{$rMeta}");
        $sheetConstancia->setCellValue("E{$rMeta}", 'NOTAS CRÉDITO DÉBITO RECIBIDAS');
        $sheetConstancia->getStyle("B{$rMeta}:G{$rMeta}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '757575']]],
        ]);
        $sheetConstancia->getRowDimension($rMeta)->setRowHeight(20);

        $notasFilas = [
            ['emitidas_desc' => 'Cantidad Notas C/D:', 'emitidas_val' => 0, 'recibidas_desc' => 'Cantidad Notas C/D:', 'recibidas_val' => 0],
            ['emitidas_desc' => 'Cantidad Válidas:', 'emitidas_val' => 0, 'recibidas_desc' => 'Importe Notas:', 'recibidas_val' => 0.00],
            ['emitidas_desc' => 'Cantidad Anuladas:', 'emitidas_val' => 0, 'recibidas_desc' => '', 'recibidas_val' => ''],
            ['emitidas_desc' => 'Importe Válidas:', 'emitidas_val' => 0.00, 'recibidas_desc' => '', 'recibidas_val' => ''],
            ['emitidas_desc' => 'Importe Anuladas:', 'emitidas_val' => 0.00, 'recibidas_desc' => '', 'recibidas_val' => ''],
        ];

        $rMeta++;
        foreach ($notasFilas as $nf) {
            $sheetConstancia->mergeCells("B{$rMeta}:C{$rMeta}");
            $sheetConstancia->setCellValue("B{$rMeta}", $nf['emitidas_desc']);
            $sheetConstancia->setCellValue("D{$rMeta}", $nf['emitidas_val']);

            $sheetConstancia->mergeCells("E{$rMeta}:F{$rMeta}");
            $sheetConstancia->setCellValue("E{$rMeta}", $nf['recibidas_desc']);
            $sheetConstancia->setCellValue("G{$rMeta}", $nf['recibidas_val']);

            $sheetConstancia->getStyle("B{$rMeta}:G{$rMeta}")->applyFromArray([
                'font' => ['size' => 8.5],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDBDBD']]],
            ]);
            $sheetConstancia->getRowDimension($rMeta)->setRowHeight(18);
            $rMeta++;
        }

        // Anchos de columnas en Hoja 1
        $sheetConstancia->getColumnDimension('A')->setWidth(4);
        $sheetConstancia->getColumnDimension('B')->setWidth(26);
        $sheetConstancia->getColumnDimension('C')->setWidth(8);
        $sheetConstancia->getColumnDimension('D')->setWidth(18);
        $sheetConstancia->getColumnDimension('E')->setWidth(26);
        $sheetConstancia->getColumnDimension('F')->setWidth(8);
        $sheetConstancia->getColumnDimension('G')->setWidth(18);

        // =========================================================================
        // HOJA 2: LIBRO DE VENTAS (22 COLUMNAS OFICIALES DE LA RND RCV DEL SIN)
        // =========================================================================
        $sheetLibro = $spreadsheet->createSheet();
        $sheetLibro->setTitle('Libro de Ventas');
        $sheetLibro->setShowGridLines(true);

        $facturas = $query->reorder('numero_factura', 'asc')->limit($limite)->get();

        // Cabecera Libro de Ventas
        $sheetLibro->mergeCells('A1:V1');
        $sheetLibro->setCellValue('A1', "LIBRO DE VENTAS IVA - {$consolidacionRcv['razon_social']} (NIT: {$consolidacionRcv['nit']})");
        $sheetLibro->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '004D40']],
        ]);
        $sheetLibro->getRowDimension(1)->setRowHeight(28);

        $sheetLibro->mergeCells('A2:V2');
        $sheetLibro->setCellValue('A2', "PERÍODO: {$consolidacionRcv['periodo']} / {$consolidacionRcv['gestion']} | Registros: " . count($facturas) . " facturas computadas");
        $sheetLibro->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9.5, 'color' => ['rgb' => '004D40'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
        ]);
        $sheetLibro->getRowDimension(2)->setRowHeight(20);

        // Encabezados oficiales de las 22 columnas
        $headersRnd = [
            'A' => 'N°',
            'B' => 'ESPECIFICACIÓN',
            'C' => 'FECHA FACTURA',
            'D' => 'N° FACTURA',
            'E' => 'CÓDIGO AUTORIZACIÓN (CUF)',
            'F' => 'NIT / CI / CEX CLIENTE',
            'G' => 'COMPLEMENTO',
            'H' => 'NOMBRE O RAZÓN SOCIAL',
            'I' => 'IMPORTE TOTAL VENTA',
            'J' => 'IMPORTE ICE',
            'K' => 'IMPORTE IEHD',
            'L' => 'IMPORTE IPJ',
            'M' => 'TASAS',
            'N' => 'OTROS NO SUJETOS CF',
            'O' => 'EXPORTACIONES Y EXENTAS',
            'P' => 'VENTAS TASA CERO',
            'Q' => 'SUBTOTAL',
            'R' => 'DESCUENTOS / BONIFICACIONES',
            'S' => 'IMPORTE GIFT CARD',
            'T' => 'BASE PARA DÉBITO FISCAL',
            'U' => 'DÉBITO FISCAL (13%)',
            'V' => 'ESTADO',
        ];

        foreach ($headersRnd as $col => $text) {
            $sheetLibro->setCellValue("{$col}4", $text);
        }

        $sheetLibro->getStyle('A4:V4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00695C']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '004D40']]],
        ]);
        $sheetLibro->getRowDimension(4)->setRowHeight(32);

        $rowL = 5;
        $startDataRowL = $rowL;

        foreach ($facturas as $idx => $f) {
            $esAnulada = in_array(strtoupper((string) $f->estado_factura), ['ANULADA', 'CANCELLED'], true);
            $totalVenta = $esAnulada ? 0.00 : (float) $f->monto_total;
            $baseFiscal = $esAnulada ? 0.00 : (float) $f->monto_total_sujeto_iva;
            $descuento = $esAnulada ? 0.00 : (float) $f->monto_descuento;
            $tasas = $esAnulada ? 0.00 : max(0.00, round($totalVenta - $baseFiscal - $descuento, 2));
            $subtotal = $esAnulada ? 0.00 : max(0.00, round($totalVenta - $tasas, 2));
            $debitoFiscal = round($baseFiscal * 0.13, 2);

            $estadoSin = $esAnulada ? 'A' : (((int) $f->tipo_emision === 2) ? 'E' : 'V');
            $fechaFmt = $f->fecha_emision ? Carbon::parse($f->fecha_emision)->format('d/m/Y') : '-';

            $sheetLibro->setCellValue("A{$rowL}", $idx + 1);
            $sheetLibro->setCellValue("B{$rowL}", 1);
            $sheetLibro->setCellValue("C{$rowL}", $fechaFmt);
            $sheetLibro->setCellValue("D{$rowL}", $f->numero_factura);
            $sheetLibro->setCellValueExplicit("E{$rowL}", (string) $f->cuf, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetLibro->setCellValueExplicit("F{$rowL}", (string) $f->numero_documento, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetLibro->setCellValue("G{$rowL}", $f->complemento ?? '');
            $sheetLibro->setCellValue("H{$rowL}", $f->nombre_razon_social);
            $sheetLibro->setCellValue("I{$rowL}", $totalVenta);
            $sheetLibro->setCellValue("J{$rowL}", 0.00);
            $sheetLibro->setCellValue("K{$rowL}", 0.00);
            $sheetLibro->setCellValue("L{$rowL}", 0.00);
            $sheetLibro->setCellValue("M{$rowL}", $tasas);
            $sheetLibro->setCellValue("N{$rowL}", 0.00);
            $sheetLibro->setCellValue("O{$rowL}", 0.00);
            $sheetLibro->setCellValue("P{$rowL}", 0.00);
            $sheetLibro->setCellValue("Q{$rowL}", $subtotal);
            $sheetLibro->setCellValue("R{$rowL}", $descuento);
            $sheetLibro->setCellValue("S{$rowL}", 0.00);
            $sheetLibro->setCellValue("T{$rowL}", $baseFiscal);
            $sheetLibro->setCellValue("U{$rowL}", $debitoFiscal);
            $sheetLibro->setCellValue("V{$rowL}", $estadoSin);

            $bgRow = ($rowL % 2 === 0) ? 'F9FBFC' : 'FFFFFF';
            $sheetLibro->getStyle("A{$rowL}:V{$rowL}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgRow]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'font' => ['size' => 8.5],
            ]);

            $sheetLibro->getStyle("A{$rowL}:D{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetLibro->getStyle("E{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheetLibro->getStyle("F{$rowL}:G{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetLibro->getStyle("H{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheetLibro->getStyle("I{$rowL}:U{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheetLibro->getStyle("V{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheetLibro->getStyle("I{$rowL}:U{$rowL}")->getNumberFormat()->setFormatCode('#,##0.00');

            if ($esAnulada) {
                $sheetLibro->getStyle("A{$rowL}:V{$rowL}")->getFont()->getColor()->setRGB('9E9E9E');
                $sheetLibro->getStyle("V{$rowL}")->getFont()->getColor()->setRGB('C62828');
                $sheetLibro->getStyle("V{$rowL}")->getFont()->setBold(true);
            }

            $sheetLibro->getRowDimension($rowL)->setRowHeight(18);
            $rowL++;
        }

        $lastRowL = $rowL - 1;

        // Fila de totales
        $sheetLibro->mergeCells("A{$rowL}:H{$rowL}");
        $sheetLibro->setCellValue("A{$rowL}", 'TOTALES GENERALES RCV (BS):');

        if ($lastRowL >= $startDataRowL) {
            $sheetLibro->setCellValue("I{$rowL}", "=SUM(I{$startDataRowL}:I{$lastRowL})");
            $sheetLibro->setCellValue("J{$rowL}", 0.00);
            $sheetLibro->setCellValue("K{$rowL}", 0.00);
            $sheetLibro->setCellValue("L{$rowL}", 0.00);
            $sheetLibro->setCellValue("M{$rowL}", "=SUM(M{$startDataRowL}:M{$lastRowL})");
            $sheetLibro->setCellValue("N{$rowL}", 0.00);
            $sheetLibro->setCellValue("O{$rowL}", 0.00);
            $sheetLibro->setCellValue("P{$rowL}", 0.00);
            $sheetLibro->setCellValue("Q{$rowL}", "=SUM(Q{$startDataRowL}:Q{$lastRowL})");
            $sheetLibro->setCellValue("R{$rowL}", "=SUM(R{$startDataRowL}:R{$lastRowL})");
            $sheetLibro->setCellValue("S{$rowL}", 0.00);
            $sheetLibro->setCellValue("T{$rowL}", "=SUM(T{$startDataRowL}:T{$lastRowL})");
            $sheetLibro->setCellValue("U{$rowL}", "=SUM(U{$startDataRowL}:U{$lastRowL})");
            $sheetLibro->setCellValue("V{$rowL}", '');
        }

        $sheetLibro->getStyle("A{$rowL}:V{$rowL}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '004D40']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '004D40']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '004D40']],
            ],
        ]);
        $sheetLibro->getStyle("A{$rowL}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheetLibro->getStyle("I{$rowL}:U{$rowL}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheetLibro->getRowDimension($rowL)->setRowHeight(24);

        $sheetLibro->freezePane('A5');

        foreach (range('A', 'V') as $c) {
            $sheetLibro->getColumnDimension($c)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $fechaArchivo = Carbon::now()->format('Ymd_His');
        $filename = "Constancia_y_Libro_Ventas_RCV_EMAPA_{$fechaArchivo}.xlsx";

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headersResponse);
    }
}
