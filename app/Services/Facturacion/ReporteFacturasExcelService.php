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
     *
     * @param Builder $query Consulta Eloquent con los filtros ya aplicados
     * @param array $filtros Datos de los filtros para la cabecera
     * @param string $generadoPor Nombre del usuario que genera el reporte
     * @param int $limite Límite de seguridad para el archivo Excel (ej. 25000)
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

        $razonSocial = $empresa->razon_social ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO DE POOPÓ';
        $nitEmpresa = $empresa->nit ?? '384910023';

        // Clonar para obtener métricas totales
        $totalRegistrosEnBd = (clone $query)->count();
        $facturas = $query->reorder('id', 'desc')->limit($limite)->get();

        $totalValidadas = 0;
        $totalAnuladas = 0;
        $totalContingencia = 0;
        $montoTotalValidadas = 0.0;

        foreach ($facturas as $f) {
            $st = strtoupper((string) $f->estado_factura);
            if ($st === 'VALIDADA' || $st === 'VALIDATED') {
                $totalValidadas++;
                $montoTotalValidadas += (float) $f->monto_total;
            } elseif ($st === 'ANULADA' || $st === 'CANCELLED') {
                $totalAnuladas++;
            } elseif ($st === 'CONTINGENCIA' || (int) $f->tipo_emision === 2) {
                $totalContingencia++;
                $montoTotalValidadas += (float) $f->monto_total;
            }
        }

        $spreadsheet = new Spreadsheet();
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->disableBranchPruning();
        $spreadsheet->getProperties()
            ->setCreator('EMAPAP - Sistema Integrado')
            ->setLastModifiedBy($generadoPor)
            ->setTitle('Planilla Oficial de Facturas Emitidas')
            ->setSubject('Reporte Fiscal SIAT')
            ->setDescription('Documento oficial de facturación generado por el Sistema EMAPAP.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Facturas Emitidas');
        $sheet->setShowGridLines(true);

        // ==========================================
        // 1. CABECERA INSTITUCIONAL (Filas 1 a 3)
        // ==========================================
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', "{$razonSocial} (NIT: {$nitEmpresa})");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '004D40']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'PLANILLA OFICIAL DE FACTURAS EMITIDAS - SISTEMA DE FACTURACIÓN ELECTRÓNICA SIAT');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '004D40']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Metadatos y filtros
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

        $sheet->mergeCells('A3:N3');
        $sheet->setCellValue('A3', $infoAuditoria);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '546E7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F7F8']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // ==========================================
        // 2. TARJETAS KPI RESUMEN (Filas 5 y 6)
        // ==========================================
        $kpiCols = [
            ['cols' => 'B', 'colFin' => 'C', 'titulo' => 'TOTAL FACTURAS', 'valor' => count($facturas), 'color' => '004D40', 'bg' => 'E0F2F1'],
            ['cols' => 'E', 'colFin' => 'F', 'titulo' => 'VALIDADAS SIN', 'valor' => $totalValidadas, 'color' => '2E7D32', 'bg' => 'E8F5E9'],
            ['cols' => 'H', 'colFin' => 'I', 'titulo' => 'CONTINGENCIA', 'valor' => $totalContingencia, 'color' => 'E65100', 'bg' => 'FFF3E0'],
            ['cols' => 'K', 'colFin' => 'L', 'titulo' => 'ANULADAS', 'valor' => $totalAnuladas, 'color' => 'C62828', 'bg' => 'FFEBEE'],
            ['cols' => 'M', 'colFin' => 'N', 'titulo' => 'TOTAL FACTURADO', 'valor' => 'Bs ' . number_format($montoTotalValidadas, 2), 'color' => '004D40', 'bg' => 'E0F2F1'],
        ];

        foreach ($kpiCols as $kpi) {
            $cIni = $kpi['cols'];
            $cFin = $kpi['colFin'];

            $sheet->mergeCells("{$cIni}5:{$cFin}5");
            $sheet->setCellValue("{$cIni}5", $kpi['titulo']);
            $sheet->getStyle("{$cIni}5:{$cFin}5")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '546E7A']],
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
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $kpi['color']]],
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

        // ==========================================
        // 3. ENCABEZADOS DE TABLA (Fila 8)
        // ==========================================
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
            'L' => 'SUJETO IVA (BS)',
            'M' => 'DESCUENTO (BS)',
            'N' => 'CÓDIGO AUTORIZACIÓN (CUF)',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue("{$col}8", $text);
        }

        $sheet->getStyle('A8:N8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00695C']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '004D40']],
            ],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(28);

        // ==========================================
        // 4. FILAS DE DATOS (Fila 9 en adelante)
        // ==========================================
        $row = 9;
        $startDataRow = $row;

        foreach ($facturas as $index => $f) {
            $esAnulada = in_array(strtoupper((string) $f->estado_factura), ['ANULADA', 'CANCELLED'], true);
            $tipoEmisionStr = ((int) $f->tipo_emision === 2) ? 'Contingencia' : 'En Línea';
            $codigoAbonado = $f->abonado ? (string) $f->abonado->codigo : '';
            $fechaFmt = $f->fecha_emision ? Carbon::parse($f->fecha_emision)->format('d/m/Y H:i') : '-';

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
            $sheet->setCellValue("K{$row}", (float) $f->monto_total);
            $sheet->setCellValue("L{$row}", (float) $f->monto_total_sujeto_iva);
            $sheet->setCellValue("M{$row}", (float) $f->monto_descuento);
            $sheet->setCellValueExplicit("N{$row}", (string) $f->cuf, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Colores zebra alternados
            $bgRow = ($row % 2 === 0) ? 'F9FBFC' : 'FFFFFF';
            $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgRow]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Alineaciones específicas
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Formato monetario
            $sheet->getStyle("K{$row}:M{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            // Formato visual para anuladas
            if ($esAnulada) {
                $sheet->getStyle("A{$row}:N{$row}")->getFont()->getColor()->setRGB('9E9E9E');
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('C62828');
                $sheet->getStyle("I{$row}")->getFont()->setBold(true);
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $lastDataRow = $row - 1;

        // ==========================================
        // 5. FILA DE TOTALES GENERALES (Fórmulas)
        // ==========================================
        $sheet->mergeCells("A{$row}:J{$row}");
        $sheet->setCellValue("A{$row}", 'TOTALES GENERALES FACTURADOS (BS):');

        if ($lastDataRow >= $startDataRow) {
            $sheet->setCellValue("K{$row}", "=SUM(K{$startDataRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$row}", "=SUM(L{$startDataRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$row}", "=SUM(M{$startDataRow}:M{$lastDataRow})");
        } else {
            $sheet->setCellValue("K{$row}", 0.00);
            $sheet->setCellValue("L{$row}", 0.00);
            $sheet->setCellValue("M{$row}", 0.00);
        }

        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
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
        $sheet->getStyle("K{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("K{$row}:M{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getRowDimension($row)->setRowHeight(26);

        // Inmovilizar paneles en fila de datos
        $sheet->freezePane('A9');

        // Ajustar ancho automático de columnas con holgura
        $columnasAuto = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
        foreach ($columnasAuto as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fechaArchivo = Carbon::now()->format('Ymd_His');
        $filename = "Planilla_Facturas_EMAPAP_{$fechaArchivo}.xlsx";

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
