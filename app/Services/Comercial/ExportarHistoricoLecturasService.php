<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Facturacion\ConfiguracionEmpresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExportarHistoricoLecturasService
{
    /**
     * Definición del catálogo oficial de columnas exportables con anchos y tipos de formato.
     */
    public static function obtenerDefinicionColumnas(): array
    {
        return [
            'nro' => [
                'id' => 'nro',
                'label' => 'N° Correlativo',
                'width' => 7,
                'type' => 'counter',
                'align' => 'center',
                'sumable' => false,
            ],
            'gestion' => [
                'id' => 'gestion',
                'label' => 'Gestión',
                'width' => 10,
                'type' => 'gestion',
                'align' => 'center',
                'sumable' => false,
            ],
            'mes' => [
                'id' => 'mes',
                'label' => 'Mes',
                'width' => 8,
                'type' => 'mes',
                'align' => 'center',
                'sumable' => false,
            ],
            'periodo' => [
                'id' => 'periodo',
                'label' => 'Período',
                'width' => 12,
                'type' => 'periodo',
                'align' => 'center',
                'sumable' => false,
            ],
            'codigo' => [
                'id' => 'codigo',
                'label' => 'Código Socio',
                'width' => 16,
                'type' => 'string',
                'field' => 'codigo',
                'align' => 'center',
                'destacado' => 'primary',
                'sumable' => false,
            ],
            'nombre_completo' => [
                'id' => 'nombre_completo',
                'label' => 'Nombre Abonado / Titular',
                'width' => 36,
                'type' => 'string',
                'field' => 'nombre_completo',
                'align' => 'left',
                'destacado' => 'primary',
                'sumable' => false,
            ],
            'numero_documento' => [
                'id' => 'numero_documento',
                'label' => 'C.I. / NIT',
                'width' => 16,
                'type' => 'documento',
                'align' => 'center',
                'sumable' => false,
            ],
            'zona_nombre' => [
                'id' => 'zona_nombre',
                'label' => 'Zona / Barrio',
                'width' => 22,
                'type' => 'string',
                'field' => 'zona_nombre',
                'default' => 'SIN ZONA',
                'align' => 'left',
                'sumable' => false,
            ],
            'calle_nombre' => [
                'id' => 'calle_nombre',
                'label' => 'Calle / Dirección',
                'width' => 28,
                'type' => 'string',
                'field' => 'calle_nombre',
                'default' => 'SIN DIRECCION',
                'align' => 'left',
                'sumable' => false,
            ],
            'numero_vivienda' => [
                'id' => 'numero_vivienda',
                'label' => 'N° Casa',
                'width' => 12,
                'type' => 'string',
                'field' => 'numero_vivienda',
                'align' => 'center',
                'sumable' => false,
            ],
            'categoria_nombre' => [
                'id' => 'categoria_nombre',
                'label' => 'Categoría',
                'width' => 18,
                'type' => 'string',
                'field' => 'categoria_nombre',
                'default' => 'GENERAL',
                'align' => 'left',
                'sumable' => false,
            ],
            'tiene_medidor' => [
                'id' => 'tiene_medidor',
                'label' => 'Tiene Medidor',
                'width' => 12,
                'type' => 'boolean_si_no',
                'field' => 'tiene_medidor',
                'align' => 'center',
                'sumable' => false,
            ],
            'medidor_serie' => [
                'id' => 'medidor_serie',
                'label' => 'N° Serie Medidor',
                'width' => 18,
                'type' => 'medidor_serie',
                'align' => 'center',
                'sumable' => false,
            ],
            'lectura_anterior' => [
                'id' => 'lectura_anterior',
                'label' => 'Lect. Ant. (m³)',
                'width' => 14,
                'type' => 'numeric',
                'field' => 'lectura_anterior',
                'align' => 'right',
                'destacado' => 'info',
                'sumable' => false,
            ],
            'lectura_actual' => [
                'id' => 'lectura_actual',
                'label' => 'Lect. Act. (m³)',
                'width' => 14,
                'type' => 'numeric',
                'field' => 'lectura_actual',
                'align' => 'right',
                'destacado' => 'info',
                'sumable' => false,
            ],
            'consumo_m3' => [
                'id' => 'consumo_m3',
                'label' => 'Consumo (m³)',
                'width' => 14,
                'type' => 'numeric',
                'field' => 'consumo_m3',
                'align' => 'right',
                'destacado' => 'info',
                'sumable' => true,
            ],
            'es_estimada' => [
                'id' => 'es_estimada',
                'label' => 'Estimada',
                'width' => 10,
                'type' => 'boolean_si_no',
                'field' => 'es_estimada',
                'align' => 'center',
                'sumable' => false,
            ],
            'monto_agua' => [
                'id' => 'monto_agua',
                'label' => 'Monto Agua (Bs)',
                'width' => 14,
                'type' => 'currency',
                'field' => 'monto_agua',
                'align' => 'right',
                'sumable' => true,
            ],
            'monto_alcantarillado' => [
                'id' => 'monto_alcantarillado',
                'label' => 'Alcantarillado (Bs)',
                'width' => 14,
                'type' => 'currency',
                'field' => 'monto_alcantarillado',
                'align' => 'right',
                'sumable' => true,
            ],
            'monto_descuento_ley1886' => [
                'id' => 'monto_descuento_ley1886',
                'label' => 'Ley 1886 (Bs)',
                'width' => 16,
                'type' => 'currency',
                'field' => 'monto_descuento_ley1886',
                'align' => 'right',
                'sumable' => true,
            ],
            'total_facturado' => [
                'id' => 'total_facturado',
                'label' => 'Total Facturado (Bs)',
                'width' => 18,
                'type' => 'currency',
                'field' => 'total_facturado',
                'align' => 'right',
                'destacado' => 'success',
                'sumable' => true,
            ],
            'estado_pago' => [
                'id' => 'estado_pago',
                'label' => 'Estado Pago',
                'width' => 14,
                'type' => 'string',
                'field' => 'estado_pago',
                'default' => 'PENDIENTE',
                'align' => 'center',
                'sumable' => false,
            ],
            'fecha_lectura' => [
                'id' => 'fecha_lectura',
                'label' => 'Fecha Lectura',
                'width' => 18,
                'type' => 'fecha_hora',
                'field' => 'fecha_lectura',
                'align' => 'center',
                'sumable' => false,
            ],
            'observacion_lectura' => [
                'id' => 'observacion_lectura',
                'label' => 'Observaciones',
                'width' => 30,
                'type' => 'string',
                'field' => 'observacion_lectura',
                'align' => 'left',
                'sumable' => false,
            ],
        ];
    }

    /**
     * Ejecuta la exportación masiva para un job específico.
     */
    public function procesarJob(string $jobId): void
    {
        @ini_set('memory_limit', '2048M');
        @ini_set('max_execution_time', '0');

        $jobFile = storage_path("app/reportes_jobs/{$jobId}.json");
        $cancelFile = storage_path("app/reportes_jobs/{$jobId}.cancel");

        // Registrar shutdown function para registrar cualquier error fatal y evitar que la UI quede congelada
        register_shutdown_function(function () use ($jobId, $jobFile) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $actual = file_exists($jobFile) ? json_decode(file_get_contents($jobFile), true) : [];
                if (($actual['estado'] ?? '') === 'PROCESANDO' || ($actual['estado'] ?? '') === 'EN_COLA') {
                    $actual['estado'] = 'ERROR';
                    $actual['error'] = 'Error fatal del servidor: ' . $error['message'];
                    $actual['mensaje'] = 'El proceso se detuvo inesperadamente: ' . substr($error['message'], 0, 150);
                    $actual['actualizado_en'] = Carbon::now()->toDateTimeString();
                    @file_put_contents($jobFile, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            }
        });

        if (!file_exists($jobFile)) {
            throw new \RuntimeException("El archivo de configuración del job {$jobId} no existe.");
        }

        $config = json_decode(file_get_contents($jobFile), true);
        if (!$config) {
            throw new \RuntimeException("Configuración inválida para el job {$jobId}.");
        }

        $formato = strtolower((string) ($config['formato'] ?? 'xlsx'));
        $periodoIds = (array) ($config['periodo_ids'] ?? []);
        $idZona = !empty($config['id_zona']) ? (int) $config['id_zona'] : null;
        $idCategoria = !empty($config['id_categoria']) ? (int) $config['id_categoria'] : null;
        $estadoPago = !empty($config['estado_pago']) && $config['estado_pago'] !== 'TODOS' ? (string) $config['estado_pago'] : null;
        $columnasSolicitadas = (array) ($config['columnas'] ?? []);
        $columnasActivas = $this->filtrarColumnasActivas($columnasSolicitadas);

        if (empty($periodoIds)) {
            $this->actualizarEstadoJob($jobId, [
                'estado' => 'ERROR',
                'error' => 'No se seleccionaron períodos válidos para la exportación.',
            ]);
            return;
        }

        // Obtener los períodos ordenados cronológicamente
        $periodos = PeriodoFacturacion::whereIn('id', $periodoIds)
            ->orderBy('gestion', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        if ($periodos->isEmpty()) {
            $this->actualizarEstadoJob($jobId, [
                'estado' => 'ERROR',
                'error' => 'No se encontraron los períodos solicitados en la base de datos.',
            ]);
            return;
        }

        $totalPeriodos = $periodos->count();
        $primerPeriodo = $periodos->first()->periodo;
        $ultimoPeriodo = $periodos->last()->periodo;
        $gestionInicio = $periodos->first()->gestion;
        $gestionFin = $periodos->last()->gestion;

        $rangoLabel = ($gestionInicio === $gestionFin)
            ? "Gestion_{$gestionInicio}"
            : "Periodos_{$gestionInicio}_a_{$gestionFin}";

        $nombreBase = "EMAPAP_Historico_Lecturas_{$rangoLabel}_" . date('Ymd_His');
        $extension = ($formato === 'csv') ? 'csv' : 'xlsx';
        $nombreArchivo = "{$nombreBase}.{$extension}";
        $rutaArchivo = storage_path("app/reportes_jobs/{$nombreArchivo}");

        $this->actualizarEstadoJob($jobId, [
            'estado' => 'PROCESANDO',
            'progreso' => 2,
            'total_periodos' => $totalPeriodos,
            'periodos_procesados' => 0,
            'filas_exportadas' => 0,
            'nombre_archivo' => $nombreArchivo,
            'mensaje' => 'Iniciando generación de datos...',
        ]);

        try {
            if ($formato === 'csv') {
                $this->generarCsv($jobId, $rutaArchivo, $periodos, $idZona, $idCategoria, $estadoPago, $cancelFile, $columnasActivas);
            } else {
                $this->generarXlsx($jobId, $rutaArchivo, $periodos, $idZona, $idCategoria, $estadoPago, $cancelFile, $primerPeriodo, $ultimoPeriodo, $columnasActivas);
            }

            // Comprobar si fue cancelado
            if (file_exists($cancelFile)) {
                @unlink($rutaArchivo);
                @unlink($cancelFile);
                $this->actualizarEstadoJob($jobId, [
                    'estado' => 'CANCELADO',
                    'mensaje' => 'Exportación cancelada por el usuario.',
                ]);
                return;
            }

            $pesoBytes = file_exists($rutaArchivo) ? filesize($rutaArchivo) : 0;
            $pesoHumano = $this->formatearBytes($pesoBytes);

            $this->actualizarEstadoJob($jobId, [
                'estado' => 'COMPLETADO',
                'progreso' => 100,
                'periodo_actual' => 'Finalizado',
                'ruta_archivo' => $rutaArchivo,
                'nombre_archivo' => $nombreArchivo,
                'peso_archivo' => $pesoHumano,
                'finalizado_en' => Carbon::now()->toDateTimeString(),
                'mensaje' => 'Archivo generado con éxito y listo para descargar.',
            ]);
        } catch (\Throwable $e) {
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }
            $this->actualizarEstadoJob($jobId, [
                'estado' => 'ERROR',
                'error' => $e->getMessage(),
                'mensaje' => 'Error al generar el archivo: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Filtra y valida las columnas solicitadas contra el catálogo oficial.
     */
    protected function filtrarColumnasActivas(array $solicitadas): array
    {
        $definiciones = self::obtenerDefinicionColumnas();
        $disponibles = array_keys($definiciones);

        if (empty($solicitadas)) {
            return $disponibles;
        }

        $filtradas = [];
        foreach ($solicitadas as $colId) {
            if (isset($definiciones[$colId]) && !in_array($colId, $filtradas, true)) {
                $filtradas[] = $colId;
            }
        }

        return !empty($filtradas) ? $filtradas : $disponibles;
    }

    /**
     * Extrae el valor y tipo formateado de una celda según la definición de su columna.
     */
    protected function obtenerValorCelda(string $colId, $row, $periodo, int $contadorFila): array
    {
        $defs = self::obtenerDefinicionColumnas();
        $colDef = $defs[$colId] ?? null;

        if (!$colDef) {
            return ['valor' => '', 'tipo' => 'string', 'align' => 'left'];
        }

        switch ($colDef['type']) {
            case 'counter':
                return ['valor' => $contadorFila, 'tipo' => 'number', 'align' => 'center'];
            case 'gestion':
                return ['valor' => (int) $periodo->gestion, 'tipo' => 'number', 'align' => 'center'];
            case 'mes':
                return ['valor' => (int) $periodo->mes, 'tipo' => 'number', 'align' => 'center'];
            case 'periodo':
                return ['valor' => (string) $periodo->periodo, 'tipo' => 'string', 'align' => 'center'];
            case 'documento':
                $doc = (string) ($row->numero_documento ?? '');
                if (!empty($row->complemento)) {
                    $doc .= '-' . $row->complemento;
                }
                return ['valor' => $doc, 'tipo' => 'string', 'align' => 'center'];
            case 'boolean_si_no':
                $val = !empty($row->{$colDef['field']}) ? 'SI' : 'NO';
                return ['valor' => $val, 'tipo' => 'string', 'align' => 'center'];
            case 'medidor_serie':
                $serie = (string) ($row->medidor_serie ?? ($row->numero_medidor ?? 'S/M'));
                return ['valor' => $serie, 'tipo' => 'string', 'align' => 'center'];
            case 'numeric':
                $num = (float) ($row->{$colDef['field']} ?? 0);
                return ['valor' => $num, 'tipo' => 'numeric', 'align' => 'right'];
            case 'currency':
                $curr = (float) ($row->{$colDef['field']} ?? 0);
                return ['valor' => $curr, 'tipo' => 'currency', 'align' => 'right'];
            case 'fecha_hora':
                $f = $row->{$colDef['field']} ?? null;
                $fStr = $f ? date('d/m/Y H:i', strtotime((string) $f)) : '';
                return ['valor' => $fStr, 'tipo' => 'string', 'align' => 'center'];
            case 'string':
            default:
                $field = $colDef['field'] ?? $colId;
                $s = (string) ($row->{$field} ?? ($colDef['default'] ?? ''));
                return ['valor' => $s, 'tipo' => 'string', 'align' => $colDef['align'] ?? 'left'];
        }
    }

    /**
     * Genera el archivo en formato CSV con BOM UTF-8 y delimitador punto y coma.
     */
    protected function generarCsv(
        string $jobId,
        string $rutaArchivo,
        $periodos,
        ?int $idZona,
        ?int $idCategoria,
        ?string $estadoPago,
        string $cancelFile,
        array $columnasActivas
    ): void {
        $handle = fopen($rutaArchivo, 'w');
        if (!$handle) {
            throw new \RuntimeException("No se pudo abrir el archivo para escritura: {$rutaArchivo}");
        }

        // BOM UTF-8 para que Excel abra acentos automáticamente
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $defs = self::obtenerDefinicionColumnas();
        $headers = [];
        foreach ($columnasActivas as $colId) {
            $headers[] = $defs[$colId]['label'] ?? $colId;
        }
        fputcsv($handle, $headers, ';');

        $totalFilas = 0;
        $totalPeriodos = $periodos->count();
        $periodosProcesados = 0;

        foreach ($periodos as $periodo) {
            if (file_exists($cancelFile)) {
                fclose($handle);
                return;
            }

            $query = $this->construirQueryLecturas($periodo->id, $idZona, $idCategoria, $estadoPago);

            foreach ($query->cursor() as $row) {
                $totalFilas++;
                $filaCsv = [];
                foreach ($columnasActivas as $colId) {
                    $celda = $this->obtenerValorCelda($colId, $row, $periodo, $totalFilas);
                    if ($celda['tipo'] === 'numeric' || $celda['tipo'] === 'currency') {
                        $filaCsv[] = number_format((float) $celda['valor'], 2, '.', '');
                    } else {
                        $filaCsv[] = (string) $celda['valor'];
                    }
                }
                fputcsv($handle, $filaCsv, ';');
            }

            $periodosProcesados++;
            $progreso = min(98, (int) round(($periodosProcesados / $totalPeriodos) * 100));

            $this->actualizarEstadoJob($jobId, [
                'progreso' => $progreso,
                'periodo_actual' => $periodo->periodo,
                'periodos_procesados' => $periodosProcesados,
                'filas_exportadas' => $totalFilas,
                'mensaje' => "Procesando período {$periodosProcesados}/{$totalPeriodos} ({$periodo->periodo}) · {$totalFilas} registros exportados",
            ]);
        }

        fclose($handle);
    }

    /**
     * Genera el archivo en formato Excel XLSX estilizado con hojas organizadas por Gestión mediante streaming OpenXML de cero consumo de RAM.
     */
    protected function generarXlsx(
        string $jobId,
        string $rutaArchivo,
        $periodos,
        ?int $idZona,
        ?int $idCategoria,
        ?string $estadoPago,
        string $cancelFile,
        string $primerPeriodo,
        string $ultimoPeriodo,
        array $columnasActivas
    ): void {
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

        $tempDir = storage_path("app/reportes_jobs/tmp_{$jobId}_" . uniqid());
        @mkdir($tempDir . '/_rels', 0777, true);
        @mkdir($tempDir . '/xl/_rels', 0777, true);
        @mkdir($tempDir . '/xl/worksheets', 0777, true);

        $periodosPorGestion = $periodos->groupBy('gestion');
        $sheetIndex = 0;
        $totalFilas = 0;
        $totalPeriodos = $periodos->count();
        $periodosProcesados = 0;
        $sheetsMeta = [];

        $defs = self::obtenerDefinicionColumnas();
        $mapaColumnas = [];
        $colNum = 0;
        foreach ($columnasActivas as $colId) {
            $colNum++;
            $mapaColumnas[$colId] = [
                'colNum' => $colNum,
                'letra' => $this->obtenerLetraColumna($colNum),
                'def' => $defs[$colId],
            ];
        }

        $ultimaColLetra = $this->obtenerLetraColumna(count($columnasActivas));

        try {
            foreach ($periodosPorGestion as $gestion => $periodosDeGestion) {
                if (file_exists($cancelFile)) {
                    $this->eliminarDirectorioRecursivo($tempDir);
                    return;
                }

                $sheetIndex++;
                $sheetName = "Gestión {$gestion}";
                $sheetRelId = "rId{$sheetIndex}";
                $sheetFileName = "sheet{$sheetIndex}.xml";
                $sheetPath = "{$tempDir}/xl/worksheets/{$sheetFileName}";

                $sheetsMeta[] = [
                    'index' => $sheetIndex,
                    'name' => $sheetName,
                    'relId' => $sheetRelId,
                    'file' => $sheetFileName,
                ];

                $fp = fopen($sheetPath, 'w');
                if (!$fp) {
                    throw new \RuntimeException("No se pudo crear la hoja temporal: {$sheetPath}");
                }

                // 1. Cabecera del Worksheet
                $tabSelected = ($sheetIndex === 1) ? '1' : '0';
                fwrite($fp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
                fwrite($fp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n");
                fwrite($fp, '<sheetViews><sheetView tabSelected="' . $tabSelected . '" workbookViewId="0" showGridLines="1"><pane ySplit="5" topLeftCell="A6" state="frozen"/></sheetView></sheetViews>' . "\n");
                fwrite($fp, '<sheetFormatPr defaultRowHeight="16"/>' . "\n");

                // Anchos de columna dinámicos
                fwrite($fp, '<cols>' . "\n");
                foreach ($mapaColumnas as $info) {
                    fwrite($fp, '<col min="' . $info['colNum'] . '" max="' . $info['colNum'] . '" width="' . $info['def']['width'] . '" customWidth="1"/>' . "\n");
                }
                fwrite($fp, '</cols>' . "\n");

                fwrite($fp, '<sheetData>' . "\n");

                // Fila 1: Título Empresa
                $titEmpresa = $this->xmlEscape("{$razonSocial} (NIT: {$nitEmpresa})");
                fwrite($fp, '<row r="1" ht="28" customHeight="1"><c r="A1" s="1" t="inlineStr"><is><t>' . $titEmpresa . '</t></is></c></row>' . "\n");

                // Fila 2: Subtítulo
                $subTit = $this->xmlEscape("HISTORIAL CONSOLIDADO DE LECTURAS, CONSUMO Y FACTURACIÓN COMERCIAL - GESTIÓN {$gestion}");
                fwrite($fp, '<row r="2" ht="22" customHeight="1"><c r="A2" s="2" t="inlineStr"><is><t>' . $subTit . '</t></is></c></row>' . "\n");

                // Fila 3: Metadatos
                $metaTxt = $this->xmlEscape("Rango analizado: {$primerPeriodo} al {$ultimoPeriodo} | Columnas: " . count($columnasActivas) . " | Generado el: " . date('d/m/Y H:i:s'));
                fwrite($fp, '<row r="3" ht="18" customHeight="1"><c r="A3" s="3" t="inlineStr"><is><t>' . $metaTxt . '</t></is></c></row>' . "\n");

                // Fila 5: Encabezados de Columna
                fwrite($fp, '<row r="5" ht="24" customHeight="1">' . "\n");
                foreach ($mapaColumnas as $info) {
                    $ref = $info['letra'] . '5';
                    fwrite($fp, '<c r="' . $ref . '" s="4" t="inlineStr"><is><t>' . $this->xmlEscape($info['def']['label']) . '</t></is></c>');
                }
                fwrite($fp, '</row>' . "\n");

                // 2. Filas de Datos
                $filaActual = 5;
                $filaInicioDatos = 6;

                foreach ($periodosDeGestion as $periodo) {
                    if (file_exists($cancelFile)) {
                        fclose($fp);
                        $this->eliminarDirectorioRecursivo($tempDir);
                        return;
                    }

                    $query = $this->construirQueryLecturas($periodo->id, $idZona, $idCategoria, $estadoPago);

                    foreach ($query->cursor() as $row) {
                        $filaActual++;
                        $totalFilas++;
                        $r = $filaActual;
                        $isZebra = ($r % 2 === 0);

                        fwrite($fp, '<row r="' . $r . '">');
                        foreach ($mapaColumnas as $colId => $info) {
                            $celda = $this->obtenerValorCelda($colId, $row, $periodo, $totalFilas);
                            $coord = $info['letra'] . $r;

                            if ($celda['tipo'] === 'numeric') {
                                $s = $isZebra ? '10' : '9';
                                $numVal = number_format((float) $celda['valor'], 2, '.', '');
                                fwrite($fp, '<c r="' . $coord . '" s="' . $s . '"><v>' . $numVal . '</v></c>');
                            } elseif ($celda['tipo'] === 'currency') {
                                $s = $isZebra ? '12' : '11';
                                $numVal = number_format((float) $celda['valor'], 2, '.', '');
                                fwrite($fp, '<c r="' . $coord . '" s="' . $s . '"><v>' . $numVal . '</v></c>');
                            } else {
                                $align = $celda['align'] ?? 'left';
                                if ($align === 'center') {
                                    $s = $isZebra ? '8' : '7';
                                } else {
                                    $s = $isZebra ? '6' : '5';
                                }
                                fwrite($fp, '<c r="' . $coord . '" s="' . $s . '" t="inlineStr"><is><t>' . $this->xmlEscape((string) $celda['valor']) . '</t></is></c>');
                            }
                        }
                        fwrite($fp, '</row>' . "\n");
                    }

                    $periodosProcesados++;
                    $progreso = min(95, (int) round(($periodosProcesados / $totalPeriodos) * 100));

                    $this->actualizarEstadoJob($jobId, [
                        'progreso' => $progreso,
                        'periodo_actual' => $periodo->periodo,
                        'periodos_procesados' => $periodosProcesados,
                        'filas_exportadas' => $totalFilas,
                        'mensaje' => "Procesando período {$periodosProcesados}/{$totalPeriodos} ({$periodo->periodo}) · {$totalFilas} registros agregados",
                    ]);
                }

                // 3. Fila de Totales de la Gestión (si hay columnas numéricas sumables)
                $ultimaFilaDatos = $filaActual;
                $filaTotal = $ultimaFilaDatos + 1;

                $primeraColSumable = null;
                $colsSumables = [];
                foreach ($mapaColumnas as $colId => $info) {
                    if (!empty($info['def']['sumable'])) {
                        if ($primeraColSumable === null) {
                            $primeraColSumable = $info;
                        }
                        $colsSumables[$colId] = $info;
                    }
                }

                if ($ultimaFilaDatos >= $filaInicioDatos && !empty($colsSumables)) {
                    fwrite($fp, '<row r="' . $filaTotal . '" ht="22" customHeight="1">');

                    // Etiqueta de total en la primera celda
                    fwrite($fp, '<c r="A' . $filaTotal . '" s="13" t="inlineStr"><is><t>TOTAL GESTIÓN ' . $gestion . ':</t></is></c>');

                    // Fórmulas para cada columna sumable
                    foreach ($colsSumables as $info) {
                        $sTotal = ($info['def']['type'] === 'currency') ? '15' : '14';
                        fwrite($fp, '<c r="' . $info['letra'] . $filaTotal . '" s="' . $sTotal . '"><f>SUM(' . $info['letra'] . '6:' . $info['letra'] . $ultimaFilaDatos . ')</f><v>0</v></c>');
                    }
                    fwrite($fp, '</row>' . "\n");
                }

                fwrite($fp, '</sheetData>' . "\n");

                // Celdas combinadas
                fwrite($fp, '<mergeCells>' . "\n");
                fwrite($fp, '<mergeCell ref="A1:' . $ultimaColLetra . '1"/>' . "\n");
                fwrite($fp, '<mergeCell ref="A2:' . $ultimaColLetra . '2"/>' . "\n");
                fwrite($fp, '<mergeCell ref="A3:' . $ultimaColLetra . '3"/>' . "\n");

                if ($ultimaFilaDatos >= $filaInicioDatos && $primeraColSumable && $primeraColSumable['colNum'] > 1) {
                    $colFinEtiqueta = $this->obtenerLetraColumna($primeraColSumable['colNum'] - 1);
                    fwrite($fp, '<mergeCell ref="A' . $filaTotal . ':' . $colFinEtiqueta . $filaTotal . '"/>' . "\n");
                }
                fwrite($fp, '</mergeCells>' . "\n");

                fwrite($fp, '</worksheet>' . "\n");
                fclose($fp);
            }

            // 4. Escribir archivos auxiliares del OpenXML
            $this->escribirArchivosEstructuraXlsx($tempDir, $sheetsMeta);

            $this->actualizarEstadoJob($jobId, [
                'progreso' => 97,
                'mensaje' => 'Empaquetando libro de Excel...',
            ]);

            // 5. Comprimir todo en el archivo final XLSX
            $zip = new \ZipArchive();
            if ($zip->open($rutaArchivo, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("No se pudo crear el archivo comprimido Excel: {$rutaArchivo}");
            }

            $archivos = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($archivos as $archivo) {
                if (!$archivo->isDir()) {
                    $rutaReal = $archivo->getRealPath();
                    $rutaRelativa = substr($rutaReal, strlen($tempDir) + 1);
                    $zip->addFile($rutaReal, $rutaRelativa);
                }
            }

            $zip->close();
        } finally {
            $this->eliminarDirectorioRecursivo($tempDir);
        }
    }

    /**
     * Escribe los archivos XML de estructura, relaciones y estilos del archivo XLSX.
     */
    protected function escribirArchivosEstructuraXlsx(string $tempDir, array $sheetsMeta): void
    {
        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n" .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n" .
            '<Default Extension="xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n" .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n" .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n";

        foreach ($sheetsMeta as $sheet) {
            $contentTypes .= '<Override PartName="/xl/worksheets/' . $sheet['file'] . '" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }
        $contentTypes .= '</Types>';
        file_put_contents("{$tempDir}/[Content_Types].xml", $contentTypes);

        // 2. _rels/.rels
        file_put_contents("{$tempDir}/_rels/.rels", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n" .
            '</Relationships>');

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        foreach ($sheetsMeta as $sheet) {
            $wbRels .= '<Relationship Id="' . $sheet['relId'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/' . $sheet['file'] . '"/>' . "\n";
        }
        $styleRelId = 'rIdStyles';
        $wbRels .= '<Relationship Id="' . $styleRelId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n";
        $wbRels .= '</Relationships>';
        file_put_contents("{$tempDir}/xl/_rels/workbook.xml.rels", $wbRels);

        // 4. xl/workbook.xml
        $wbXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n" .
            '<sheets>' . "\n";
        foreach ($sheetsMeta as $sheet) {
            $wbXml .= '<sheet name="' . $this->xmlEscape($sheet['name']) . '" sheetId="' . $sheet['index'] . '" r:id="' . $sheet['relId'] . '"/>' . "\n";
        }
        $wbXml .= '</sheets>' . "\n" . '</workbook>';
        file_put_contents("{$tempDir}/xl/workbook.xml", $wbXml);

        // 5. xl/styles.xml con paleta teal, bordes, zebra y formatos numéricos
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
            '<numFmts count="2">' . "\n" .
            '  <numFmt numFmtId="164" formatCode="#,##0.00"/>' . "\n" .
            '  <numFmt numFmtId="165" formatCode="&quot;Bs &quot;#,##0.00"/>' . "\n" .
            '</numFmts>' . "\n" .
            '<fonts count="6">' . "\n" .
            '  <font><sz val="9"/><color rgb="FF1E293B"/><name val="Calibri"/></font>' . "\n" . // 0: Regular
            '  <font><b/><sz val="14"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' . "\n" . // 1: Título 14pt blanco
            '  <font><b/><sz val="11"/><color rgb="FF004D40"/><name val="Calibri"/></font>' . "\n" . // 2: Subtítulo 11pt verde
            '  <font><i/><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/></font>' . "\n" . // 3: Meta 9pt gris
            '  <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' . "\n" . // 4: Header 10pt blanco
            '  <font><b/><sz val="10"/><color rgb="FF004D40"/><name val="Calibri"/></font>' . "\n" . // 5: Total 10pt teal
            '</fonts>' . "\n" .
            '<fills count="7">' . "\n" .
            '  <fill><patternFill patternType="none"/></fill>' . "\n" . // 0
            '  <fill><patternFill patternType="gray125"/></fill>' . "\n" . // 1
            '  <fill><patternFill patternType="solid"><fgColor rgb="FF004D40"/></patternFill></fill>' . "\n" . // 2: Verde Oscuro Corporativo
            '  <fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/></patternFill></fill>' . "\n" . // 3: Teal Encabezados
            '  <fill><patternFill patternType="none"/></fill>' . "\n" . // 4: Blanco
            '  <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . "\n" . // 5: Cebra suave
            '  <fill><patternFill patternType="solid"><fgColor rgb="FFCCFBF1"/></patternFill></fill>' . "\n" . // 6: Menta Total
            '</fills>' . "\n" .
            '<borders count="4">' . "\n" .
            '  <border><left/><right/><top/><bottom/><diagonal/></border>' . "\n" . // 0: Sin bordes
            '  <border>' . "\n" . // 1: Borde fino grilla
            '    <left style="thin"><color rgb="FFE2E8F0"/></left>' . "\n" .
            '    <right style="thin"><color rgb="FFE2E8F0"/></right>' . "\n" .
            '    <top style="thin"><color rgb="FFE2E8F0"/></top>' . "\n" .
            '    <bottom style="thin"><color rgb="FFE2E8F0"/></bottom>' . "\n" .
            '  </border>' . "\n" .
            '  <border>' . "\n" . // 2: Borde total
            '    <left style="thin"><color rgb="FFE2E8F0"/></left>' . "\n" .
            '    <right style="thin"><color rgb="FFE2E8F0"/></right>' . "\n" .
            '    <top style="medium"><color rgb="FF0F766E"/></top>' . "\n" .
            '    <bottom style="double"><color rgb="FF0F766E"/></bottom>' . "\n" .
            '  </border>' . "\n" .
            '  <border>' . "\n" . // 3: Borde cabecera teal
            '    <left style="thin"><color rgb="FF0D9488"/></left>' . "\n" .
            '    <right style="thin"><color rgb="FF0D9488"/></right>' . "\n" .
            '    <top style="thin"><color rgb="FF0D9488"/></top>' . "\n" .
            '    <bottom style="thin"><color rgb="FF0D9488"/></bottom>' . "\n" .
            '  </border>' . "\n" .
            '</borders>' . "\n" .
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' . "\n" .
            '<cellXfs count="16">' . "\n" .
            '  <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n" . // 0: Default
            '  <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 1: Título
            '  <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 2: Subtítulo
            '  <xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 3: Meta
            '  <xf numFmtId="0" fontId="4" fillId="3" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' . "\n" . // 4: Header
            '  <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' . "\n" . // 5: Text Left
            '  <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' . "\n" . // 6: Text Left Zebra
            '  <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 7: Text Center
            '  <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 8: Text Center Zebra
            '  <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 9: Num 2 Dec
            '  <xf numFmtId="164" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 10: Num 2 Dec Zebra
            '  <xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 11: Currency
            '  <xf numFmtId="165" fontId="0" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 12: Currency Zebra
            '  <xf numFmtId="0" fontId="5" fillId="6" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 13: Total Label
            '  <xf numFmtId="164" fontId="5" fillId="6" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 14: Total Num
            '  <xf numFmtId="165" fontId="5" fillId="6" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 15: Total Currency
            '</cellXfs>' . "\n" .
            '</styleSheet>';

        file_put_contents("{$tempDir}/xl/styles.xml", $stylesXml);
    }

    /**
     * Convierte un número ordinal 1..N a letra de columna de Excel (1->A, 24->X, 27->AA).
     */
    protected function obtenerLetraColumna(int $colNum): string
    {
        $letter = '';
        while ($colNum > 0) {
            $mod = ($colNum - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colNum = (int) (($colNum - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Escapa cadenas para XML válido.
     */
    protected function xmlEscape(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value);
        return htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Elimina recursivamente un directorio temporal.
     */
    protected function eliminarDirectorioRecursivo(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $archivos = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($archivos as $archivo) {
            if ($archivo->isDir()) {
                @rmdir($archivo->getRealPath());
            } else {
                @unlink($archivo->getRealPath());
            }
        }
        @rmdir($dir);
    }

    /**
     * Construye la consulta SQL optimizada con JOINs para traer los datos planos de lecturas y abonados.
     */
    protected function construirQueryLecturas(int $idPeriodo, ?int $idZona, ?int $idCategoria, ?string $estadoPago)
    {
        $query = DB::table('comercial.lecturas_mensuales as l')
            ->join('comercial.abonados as a', 'a.id', '=', 'l.id_abonado')
            ->leftJoin('comercial.zonas as z', 'z.id', '=', 'a.id_zona')
            ->leftJoin('comercial.calles as c', 'c.id', '=', 'a.id_calle')
            ->leftJoin('comercial.categorias_tarifarias as cat', 'cat.id', '=', 'a.id_categoria')
            ->leftJoin('comercial.medidores as m', 'm.id', '=', 'l.id_medidor')
            ->where('l.id_periodo', $idPeriodo)
            ->select([
                'l.id',
                'l.lectura_anterior',
                'l.lectura_actual',
                'l.consumo_m3',
                'l.es_estimada',
                'l.observacion_lectura',
                'l.fecha_lectura',
                'l.monto_agua',
                'l.monto_alcantarillado',
                'l.monto_descuento_ley1886',
                'l.monto_otros',
                'l.total_facturado',
                'l.estado_pago',
                'a.codigo',
                'a.nombre_completo',
                'a.numero_documento',
                'a.complemento',
                'a.numero_vivienda',
                'a.tiene_medidor',
                'z.nombre as zona_nombre',
                'c.nombre as calle_nombre',
                'cat.nombre as categoria_nombre',
                'm.numero_serie as medidor_serie',
            ])
            ->orderBy('z.nombre', 'asc')
            ->orderBy('c.nombre', 'asc')
            ->orderBy('a.codigo', 'asc');

        if ($idZona) {
            $query->where('a.id_zona', $idZona);
        }
        if ($idCategoria) {
            $query->where('a.id_categoria', $idCategoria);
        }
        if ($estadoPago) {
            $query->where('l.estado_pago', $estadoPago);
        }

        return $query;
    }

    /**
     * Actualiza el archivo de estado JSON del Job.
     */
    public function actualizarEstadoJob(string $jobId, array $datos): void
    {
        $jobFile = storage_path("app/reportes_jobs/{$jobId}.json");
        $actual = file_exists($jobFile) ? json_decode(file_get_contents($jobFile), true) : [];
        $nuevo = array_merge($actual ?: [], $datos, ['actualizado_en' => Carbon::now()->toDateTimeString()]);
        file_put_contents($jobFile, json_encode($nuevo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Formatea bytes a cadena legible.
     */
    protected function formatearBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
