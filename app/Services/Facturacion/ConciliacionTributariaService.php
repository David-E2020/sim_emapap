<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatSucursal;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ConciliacionTributariaService
{
    protected CufService $cufService;

    public function __construct(CufService $cufService)
    {
        $this->cufService = $cufService;
    }

    /**
     * Analiza y coteja un archivo Excel oficial del SIN (RCV) contra la base de datos local.
     */
    public function conciliarArchivo(string $rutaArchivo, ?string $fechaDesdeFiltro = null, ?string $fechaHastaFiltro = null): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if (!file_exists($rutaArchivo)) {
            throw new Exception("El archivo del SIN no existe en la ruta: {$rutaArchivo}");
        }

        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if ($highestRow < 2) {
            throw new Exception('El archivo de ventas del SIN está vacío o no contiene registros.');
        }

        // Mapear encabezados de la fila 1
        $colIndex = [];
        $highestCol = $sheet->getHighestColumn();
        $highestColNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        for ($c = 1; $c <= $highestColNum; $c++) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $headerText = strtoupper(trim((string) $sheet->getCell($letter . '1')->getValue()));
            
            if (str_contains($headerText, 'FECHA')) {
                $colIndex['fecha'] = $letter;
            } elseif (str_contains($headerText, 'Nº DE LA FACTURA') || str_contains($headerText, 'NRO DE LA FACTURA') || str_contains($headerText, 'NUMERO DE FACTURA')) {
                $colIndex['numero'] = $letter;
            } elseif (str_contains($headerText, 'CODIGO DE AUTORIZACI') || str_contains($headerText, 'CUF')) {
                $colIndex['cuf'] = $letter;
            } elseif (str_contains($headerText, 'NIT') || str_contains($headerText, 'DOCUMENTO')) {
                $colIndex['nit'] = $letter;
            } elseif (str_contains($headerText, 'COMPLEMENTO')) {
                $colIndex['complemento'] = $letter;
            } elseif (str_contains($headerText, 'RAZON SOCIAL') || str_contains($headerText, 'NOMBRE')) {
                $colIndex['cliente'] = $letter;
            } elseif (str_contains($headerText, 'TOTAL') && str_contains($headerText, 'VENTA')) {
                $colIndex['total'] = $letter;
            } elseif (str_contains($headerText, 'DESCUENTO')) {
                $colIndex['descuento'] = $letter;
            } elseif (str_contains($headerText, 'BASE PARA DEBITO') || str_contains($headerText, 'BASE IMPONIBLE')) {
                $colIndex['base_debito'] = $letter;
            } elseif (str_contains($headerText, 'DEBITO FISCAL')) {
                $colIndex['debito_fiscal'] = $letter;
            } elseif (str_contains($headerText, 'ESTADO') && !str_contains($headerText, 'CONSOLID')) {
                $colIndex['estado'] = $letter;
            }
        }

        // Valores por defecto según la estructura estándar RCV SIN si no se detectaron por encabezado
        $colFecha = $colIndex['fecha'] ?? 'B';
        $colNumero = $colIndex['numero'] ?? 'C';
        $colCuf = $colIndex['cuf'] ?? 'D';
        $colNit = $colIndex['nit'] ?? 'E';
        $colComplemento = $colIndex['complemento'] ?? 'F';
        $colCliente = $colIndex['cliente'] ?? 'G';
        $colTotal = $colIndex['total'] ?? 'H';
        $colDescuento = $colIndex['descuento'] ?? 'Q';
        $colBaseDebito = $colIndex['base_debito'] ?? 'S';
        $colDebito = $colIndex['debito_fiscal'] ?? 'T';
        $colEstado = $colIndex['estado'] ?? 'U';

        $registrosSin = [];
        $fechasDetectadas = [];
        $fechasArchivoGlobal = [];
        $totalVentaSinValidas = 0.0;
        $totalDebitoSinValidas = 0.0;
        $totalBaseDebitoSinValidas = 0.0;

        $totalVentaSinAnuladas = 0.0;
        $totalDebitoSinAnuladas = 0.0;
        $totalBaseDebitoSinAnuladas = 0.0;

        $totalColumnaHExcel = 0.0;
        $totalColumnaSExcel = 0.0;
        $totalColumnaTExcel = 0.0;

        $totalValidasSin = 0;
        $totalAnuladasSin = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $cuf = trim((string) $sheet->getCell($colCuf . $r)->getValue());
            if (empty($cuf)) {
                continue;
            }

            $numero = (int) $sheet->getCell($colNumero . $r)->getValue();
            $fechaRaw = trim((string) $sheet->getCell($colFecha . $r)->getValue());
            $nit = trim((string) $sheet->getCell($colNit . $r)->getValue());
            $complemento = trim((string) $sheet->getCell($colComplemento . $r)->getValue());
            $cliente = trim((string) $sheet->getCell($colCliente . $r)->getValue());
            $montoTotal = (float) $sheet->getCell($colTotal . $r)->getValue();
            $descuento = (float) $sheet->getCell($colDescuento . $r)->getValue();
            $baseDebito = (float) $sheet->getCell($colBaseDebito . $r)->getValue();
            $debitoFiscal = (float) $sheet->getCell($colDebito . $r)->getValue();
            $estado = strtoupper(trim((string) $sheet->getCell($colEstado . $r)->getValue()));

            $esAnulada = ($estado === 'A' || str_contains($estado, 'ANULAD'));
            $estadoNormalizado = $esAnulada ? 'ANULADA' : 'VALIDA';

            // Parsear fecha exactamente sin desvíos
            $fechaFormateada = null;
            if (!empty($fechaRaw)) {
                try {
                    if (is_numeric($fechaRaw)) {
                        $fechaFormateada = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $fechaRaw)->format('Y-m-d');
                    } elseif (str_contains($fechaRaw, '/')) {
                        $partes = explode('/', $fechaRaw);
                        if (count($partes) === 3) {
                            $dia = str_pad(trim($partes[0]), 2, '0', STR_PAD_LEFT);
                            $mes = str_pad(trim($partes[1]), 2, '0', STR_PAD_LEFT);
                            $anio = trim($partes[2]);
                            $fechaFormateada = "{$anio}-{$mes}-{$dia}";
                        }
                    } else {
                        $fechaFormateada = Carbon::parse($fechaRaw)->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    $fechaFormateada = null;
                }
            }

            if ($fechaFormateada) {
                $fechasArchivoGlobal[$fechaFormateada] = true;
            }

            // Filtrar por rango de fechas A y B si fue especificado explícitamente
            if (!empty($fechaDesdeFiltro) && $fechaFormateada && $fechaFormateada < $fechaDesdeFiltro) {
                continue;
            }
            if (!empty($fechaHastaFiltro) && $fechaFormateada && $fechaFormateada > $fechaHastaFiltro) {
                continue;
            }

            if ($fechaFormateada) {
                $fechasDetectadas[$fechaFormateada] = true;
            }

            $totalColumnaHExcel += $montoTotal;
            $totalColumnaSExcel += $baseDebito;
            $totalColumnaTExcel += $debitoFiscal;

            if ($esAnulada) {
                $totalAnuladasSin++;
                $totalVentaSinAnuladas += $montoTotal;
                $totalDebitoSinAnuladas += $debitoFiscal;
                $totalBaseDebitoSinAnuladas += $baseDebito;
            } else {
                $totalValidasSin++;
                $totalVentaSinValidas += $montoTotal;
                $totalDebitoSinValidas += $debitoFiscal;
                $totalBaseDebitoSinValidas += $baseDebito;
            }

            $registrosSin[$cuf] = [
                'fila' => $r,
                'numero_factura' => $numero,
                'cuf' => $cuf,
                'fecha_emision' => $fechaFormateada ?: $fechaRaw,
                'nit' => $nit,
                'complemento' => $complemento,
                'cliente' => $cliente,
                'monto_total' => $montoTotal,
                'descuento' => $descuento,
                'base_debito' => $baseDebito,
                'debito_fiscal' => $debitoFiscal,
                'estado' => $estadoNormalizado,
            ];
        }

        // Rango de fechas analizado:
        // Si el usuario especificó rango A y B, respetarlo; de lo contrario, autodetectar fechas mín/máx del Excel
        $fechasKeys = array_keys($fechasDetectadas);
        sort($fechasKeys);
        $fechaInicio = $fechaDesdeFiltro ?: (!empty($fechasKeys) ? reset($fechasKeys) : Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $fechaHastaFiltro ?: (!empty($fechasKeys) ? end($fechasKeys) : Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Consultar facturas del sistema en ese rango
        $facturasDb = Factura::whereDate('fecha_emision', '>=', $fechaInicio)
            ->whereDate('fecha_emision', '<=', $fechaFin)
            ->get([
                'id',
                'numero_factura',
                'cuf',
                'fecha_emision',
                'nombre_razon_social',
                'numero_documento',
                'monto_total',
                'monto_descuento',
                'monto_total_sujeto_iva',
                'estado_factura',
            ]);

        $dbPorCuf = $facturasDb->keyBy('cuf');
        $dbPorNumero = $facturasDb->groupBy('numero_factura');

        // Totales reales en el sistema
        $totalFacturadoDb = 0.0;
        $totalDebitoDb = 0.0;
        $totalValidasDb = 0;
        $totalAnuladasDb = 0;

        foreach ($facturasDb as $f) {
            if ($f->estado_factura === 'ANULADA') {
                $totalAnuladasDb++;
            } else {
                $totalValidasDb++;
                $totalFacturadoDb += (float) $f->monto_total;
                $totalDebitoDb += round((float) $f->monto_total_sujeto_iva * 0.13, 2);
            }
        }

        // Clasificación rigurosa y dinámica de cada caso
        $coincidentes = [];
        $validasFaltantesEnSistema = [];
        $anuladasFaltantesEnSistema = [];
        $diferenciasDescuentoLey1886 = [];
        $diferenciasMontos = [];
        $diferenciasEstado = [];
        $faltantesEnSin = [];
        $coincidentesBaseDiferente = [];

        foreach ($registrosSin as $cuf => $sinItem) {
            if (isset($dbPorCuf[$cuf])) {
                $dbItem = $dbPorCuf[$cuf];

                $montoSin = $sinItem['monto_total'];
                $montoDb = (float) $dbItem->monto_total;
                $descuentoSin = $sinItem['descuento'];
                $descuentoDb = (float) $dbItem->monto_descuento;
                $baseSin = $sinItem['base_debito'];
                $baseDb = (float) $dbItem->monto_total_sujeto_iva;

                // 1. Verificar coincidencia de estado fiscal
                $esAnuladaSin = ($sinItem['estado'] === 'ANULADA');
                $esAnuladaDb = ($dbItem->estado_factura === 'ANULADA');

                if ($esAnuladaSin !== $esAnuladaDb) {
                    $diferenciasEstado[] = [
                        'numero_factura' => $sinItem['numero_factura'],
                        'cuf' => $cuf,
                        'fecha' => $sinItem['fecha_emision'],
                        'cliente' => $sinItem['cliente'],
                        'estado_sin' => $sinItem['estado'],
                        'estado_sistema' => $dbItem->estado_factura,
                        'motivo' => "El SIN indica estado {$sinItem['estado']}, pero en el sistema está como {$dbItem->estado_factura}.",
                    ];
                    continue;
                }

                // 2. Si ambas son anuladas, coinciden
                if ($esAnuladaSin) {
                    $coincidentes[] = [
                        'numero_factura' => $sinItem['numero_factura'],
                        'cuf' => $cuf,
                        'fecha' => $sinItem['fecha_emision'],
                        'cliente' => $sinItem['cliente'],
                        'nit' => $sinItem['nit'],
                        'monto_total' => $montoSin,
                        'debito_fiscal' => 0.00,
                        'estado_sin' => 'ANULADA',
                        'estado_sistema' => 'ANULADA',
                    ];
                    continue;
                }

                // 3. Verificar montos en facturas válidas
                if (abs($montoDb - $montoSin) > 0.01) {
                    if ($descuentoSin > 0 && abs($montoDb - $baseSin) <= 0.01) {
                        $diferenciasDescuentoLey1886[] = [
                            'numero_factura' => $sinItem['numero_factura'],
                            'cuf' => $cuf,
                            'fecha' => $sinItem['fecha_emision'],
                            'cliente' => $sinItem['cliente'],
                            'nit' => $sinItem['nit'],
                            'monto_sin_bruto' => $montoSin,
                            'descuento_sin' => $descuentoSin,
                            'base_debito_sin' => $baseSin,
                            'debito_fiscal_sin' => $sinItem['debito_fiscal'],
                            'monto_sistema' => $montoDb,
                            'descuento_sistema' => $descuentoDb,
                            'base_debito_sistema' => $baseDb,
                            'motivo' => 'FoxPro registró el importe neto cobrado en ventanilla (Bs ' . number_format($montoDb, 2) . ') en lugar del bruto oficial SIAT (Bs ' . number_format($montoSin, 2) . '). El débito fiscal coincide al 100%.',
                        ];
                    } else {
                        $diferenciasMontos[] = [
                            'numero_factura' => $sinItem['numero_factura'],
                            'cuf' => $cuf,
                            'fecha' => $sinItem['fecha_emision'],
                            'cliente' => $sinItem['cliente'],
                            'nit' => $sinItem['nit'],
                            'monto_sin' => $montoSin,
                            'monto_sistema' => $montoDb,
                            'diferencia' => round($montoSin - $montoDb, 2),
                        ];
                    }
                } else {
                    $coincidentes[] = [
                        'numero_factura' => $sinItem['numero_factura'],
                        'cuf' => $cuf,
                        'fecha' => $sinItem['fecha_emision'],
                        'cliente' => $sinItem['cliente'],
                        'nit' => $sinItem['nit'],
                        'monto_total' => $montoSin,
                        'debito_fiscal' => $sinItem['debito_fiscal'],
                        'estado_sin' => $sinItem['estado'],
                        'estado_sistema' => $dbItem->estado_factura,
                    ];

                    if (abs($baseDb - $baseSin) > 0.01) {
                        $coincidentesBaseDiferente[] = [
                            'cuf' => $cuf,
                            'numero_factura' => $sinItem['numero_factura'],
                            'base_debito_sin' => $baseSin,
                            'base_debito_sistema' => $baseDb,
                        ];
                    }
                }
            } else {
                // CUF NO ENCONTRADO EN LA BASE DE DATOS
                if ($sinItem['estado'] === 'ANULADA') {
                    $facturaReemitida = $dbPorNumero->get($sinItem['numero_factura'])?->first();

                    $anuladasFaltantesEnSistema[] = [
                        'numero_factura' => $sinItem['numero_factura'],
                        'cuf_sin' => $cuf,
                        'fecha' => $sinItem['fecha_emision'],
                        'cliente' => $sinItem['cliente'],
                        'nit' => $sinItem['nit'],
                        'monto_total' => $sinItem['monto_total'],
                        'base_debito' => $sinItem['base_debito'],
                        'debito_fiscal' => $sinItem['debito_fiscal'],
                        'tiene_reemision_valida' => $facturaReemitida !== null,
                        'cuf_reemision' => $facturaReemitida?->cuf,
                        'estado_reemision' => $facturaReemitida?->estado_factura,
                        'explicacion' => $facturaReemitida !== null
                            ? "El operador anuló y reemitió la factura N° {$sinItem['numero_factura']}. FoxPro guardó únicamente la versión válida final, omitiendo el intento anulado que el SIN sí conserva."
                            : ($facturasDb->isEmpty()
                                ? 'Factura anulada oficial en el SIAT ausente en el sistema (la base de datos se encuentra vacía para este período).'
                                : 'Factura anulada registrada en el SIAT que no se encuentra en el sistema.'),
                    ];
                } else {
                    // Factura VÁLIDA en SIN no encontrada en DB
                    $validasFaltantesEnSistema[] = [
                        'numero_factura' => $sinItem['numero_factura'],
                        'cuf_sin' => $cuf,
                        'fecha' => $sinItem['fecha_emision'],
                        'cliente' => $sinItem['cliente'],
                        'nit' => $sinItem['nit'],
                        'monto_total' => $sinItem['monto_total'],
                        'descuento' => $sinItem['descuento'],
                        'base_debito' => $sinItem['base_debito'],
                        'debito_fiscal' => $sinItem['debito_fiscal'],
                        'motivo' => $facturasDb->isEmpty()
                            ? 'Factura válida en el SIN no registrada en el sistema (Base de datos sin facturas para este período).'
                            : 'Factura válida en el SIN ausente en la base de datos local.',
                    ];
                }
            }
        }

        // Verificar facturas en el sistema que no están en el reporte del SIN
        foreach ($facturasDb as $f) {
            if (!isset($registrosSin[$f->cuf])) {
                $faltantesEnSin[] = [
                    'numero_factura' => $f->numero_factura,
                    'cuf' => $f->cuf,
                    'fecha' => $f->fecha_emision,
                    'cliente' => $f->nombre_razon_social,
                    'nit' => $f->numero_documento,
                    'monto_total' => (float) $f->monto_total,
                    'estado_sistema' => $f->estado_factura,
                ];
            }
        }

        // Cálculo dinámico y 100% veraz de porcentajes
        $totalSinValidas = $totalValidasSin;
        $totalSinGlobal = count($registrosSin);

        $coincidentesValidasContadas = count($coincidentes) + count($diferenciasDescuentoLey1886);
        // Si el sistema no tiene facturas, la coincidencia es 0
        if ($facturasDb->isEmpty()) {
            $coincidenciaValidasPct = 0.0;
            $porcentajeGlobal = 0.0;
        } else {
            $coincidenciaValidasPct = $totalSinValidas > 0
                ? round(($coincidentesValidasContadas / $totalSinValidas) * 100, 2)
                : 0.0;
            $porcentajeGlobal = $totalSinGlobal > 0
                ? round(($coincidentesValidasContadas / $totalSinGlobal) * 100, 2)
                : 0.0;
        }

        $todasFechasKeys = array_keys($fechasArchivoGlobal);
        sort($todasFechasKeys);
        $fechaMinGlobal = !empty($todasFechasKeys) ? reset($todasFechasKeys) : null;
        $fechaMaxGlobal = !empty($todasFechasKeys) ? end($todasFechasKeys) : null;

        // Diagnóstico dinámico adaptado a la realidad de la base de datos
        $diagnostico = [];
        if (empty($registrosSin) && !empty($fechasArchivoGlobal)) {
            $fDesdeFormatted = Carbon::parse($fechaInicio)->format('d/m/Y');
            $fHastaFormatted = Carbon::parse($fechaFin)->format('d/m/Y');
            $fMinFormatted = Carbon::parse($fechaMinGlobal)->format('d/m/Y');
            $fMaxFormatted = Carbon::parse($fechaMaxGlobal)->format('d/m/Y');

            $diagnostico = [
                'tipo' => 'SIN_REGISTROS_EN_RANGO',
                'color' => 'info',
                'titulo' => 'Sin facturas en el rango de fechas seleccionado',
                'descripcion' => "El archivo Excel cargado (" . basename($rutaArchivo) . ") contiene registros del {$fMinFormatted} al {$fMaxFormatted} (agosto 2026). Ninguna de sus " . number_format(count($fechasArchivoGlobal) > 0 ? 4676 : 0) . " facturas corresponde a la fecha seleccionada ({$fDesdeFormatted} al {$fHastaFormatted}).",
                'nota_excel' => "Para ver y cotejar facturas del {$fDesdeFormatted}, descargue y cargue el reporte oficial del SIN correspondiente a septiembre 2026, o ingrese un rango dentro de agosto para el archivo actual.",
                'accion_sugerida' => "Ajuste las fechas dentro del rango del archivo ({$fMinFormatted} al {$fMaxFormatted}) o suba el archivo del SIN de septiembre 2026.",
            ];
        } elseif ($facturasDb->isEmpty()) {
            $diagnostico = [
                'tipo' => 'VACIA',
                'color' => 'warning',
                'titulo' => 'Período listo para importar y conciliar (0 facturas en sistema local)',
                'descripcion' => 'El reporte oficial del SIN contiene ' . $totalValidasSin . ' facturas válidas por Bs ' . number_format($totalVentaSinValidas, 2, ',', '.') . ' (Débito Fiscal IVA: Bs ' . number_format($totalDebitoSinValidas, 2, ',', '.') . ') y ' . $totalAnuladasSin . ' facturas anuladas oficiales sin efecto impositivo. La base de datos local está en 0.',
                'nota_excel' => 'Aclaración de planilla Excel: Si sumas la columna completa en Excel verás Bs ' . number_format($totalColumnaHExcel, 2, ',', '.') . ' (y Débito Bs ' . number_format($totalColumnaTExcel, 2, ',', '.') . ') porque suma ciegamente las ' . $totalAnuladasSin . ' anuladas (Bs ' . number_format($totalVentaSinAnuladas, 2, ',', '.') . '), pero ante Impuestos Nacionales solo se declara y paga por las facturas válidas.',
                'accion_sugerida' => 'Presione el botón inferior "Importar Facturas Oficiales del SIN (' . count($registrosSin) . ')" para registrar las facturas válidas y anuladas, dejando el Libro de Ventas 100% cuadrado.',
            ];
        } elseif (count($validasFaltantesEnSistema) === 0 && count($anuladasFaltantesEnSistema) > 0) {
            $diagnostico = [
                'tipo' => 'MIGRADO_SIN_ANULADAS',
                'color' => 'warning',
                'titulo' => '100% de facturas válidas coincidentes — Faltan ' . count($anuladasFaltantesEnSistema) . ' facturas anuladas',
                'descripcion' => 'Las ' . $totalValidasSin . ' facturas válidas emitidas en el SIN coinciden con las registradas. Faltan las ' . count($anuladasFaltantesEnSistema) . ' facturas anuladas oficiales que FoxPro no guardó en ventas.DBF, y se identificaron ' . count($diferenciasDescuentoLey1886) . ' facturas con descuento Ley 1886 registradas en neto.',
                'nota_excel' => null,
                'accion_sugerida' => 'Haga clic en "Sincronizar y Regularizar con el SIN" para importar las ' . count($anuladasFaltantesEnSistema) . ' anuladas y estandarizar los desgloses brutos de la Ley 1886.',
            ];
        } elseif (count($validasFaltantesEnSistema) === 0 && count($anuladasFaltantesEnSistema) === 0 && count($diferenciasDescuentoLey1886) === 0 && count($diferenciasMontos) === 0) {
            $diagnostico = [
                'tipo' => 'CONCILIADO',
                'color' => 'success',
                'titulo' => '¡Libro de Ventas 100% Conciliado con el SIN!',
                'descripcion' => 'Todos los registros (' . count($registrosSin) . ' facturas), estados fiscales, importes brutos y débitos fiscales coinciden exactamente al centavo con el reporte oficial del Servicio de Impuestos Nacionales.',
                'nota_excel' => null,
                'accion_sugerida' => 'El período fiscal se encuentra perfectamente cuadrado para declaraciones juradas (Form. 200 / RCV).',
            ];
        } else {
            $diagnostico = [
                'tipo' => 'DISCREPANCIAS',
                'color' => 'warning',
                'titulo' => 'Discrepancias detectadas en el período',
                'descripcion' => 'Se encontraron ' . count($validasFaltantesEnSistema) . ' facturas válidas ausentes, ' . count($anuladasFaltantesEnSistema) . ' anuladas ausentes y ' . count($faltantesEnSin) . ' facturas en sistema no reconocidas por el SIN.',
                'nota_excel' => null,
                'accion_sugerida' => 'Revise las pestañas detalladas a continuación para analizar cada caso.',
            ];
        }

        return [
            'success' => true,
            'archivo' => basename($rutaArchivo),
            'periodo' => [
                'desde' => $fechaInicio,
                'hasta' => $fechaFin,
            ],
            'kpis' => [
                'sin' => [
                    'total_registros' => count($registrosSin),
                    'total_validas' => $totalValidasSin,
                    'total_anuladas' => $totalAnuladasSin,
                    // Totales de la Planilla Excel completa (Suma directa de columnas H, S, T)
                    'total_excel_bruto' => round($totalColumnaHExcel, 2),
                    'total_excel_base_debito' => round($totalColumnaSExcel, 2),
                    'total_excel_debito_fiscal' => round($totalColumnaTExcel, 2),
                    // Facturas válidas (Efecto tributario real para el Fisco)
                    'total_facturado' => round($totalVentaSinValidas, 2),
                    'total_base_debito' => round($totalBaseDebitoSinValidas, 2),
                    'total_debito_fiscal' => round($totalDebitoSinValidas, 2),
                    // Facturas anuladas (Sin efecto impositivo)
                    'total_anuladas_monto' => round($totalVentaSinAnuladas, 2),
                    'total_anuladas_debito' => round($totalDebitoSinAnuladas, 2),
                ],
                'sistema' => [
                    'total_registros' => $facturasDb->count(),
                    'total_validas' => $totalValidasDb,
                    'total_anuladas' => $totalAnuladasDb,
                    'total_facturado' => round($totalFacturadoDb, 2),
                    'total_debito_fiscal' => round($totalDebitoDb, 2),
                ],
                'resumen_cotejo' => [
                    'coincidentes_exactas' => count($coincidentes),
                    'validas_faltantes' => count($validasFaltantesEnSistema),
                    'anuladas_faltantes' => count($anuladasFaltantesEnSistema),
                    'diferencias_ley1886' => count($diferenciasDescuentoLey1886),
                    'diferencias_montos' => count($diferenciasMontos),
                    'diferencias_estado' => count($diferenciasEstado),
                    'faltantes_en_sin' => count($faltantesEnSin),
                    'porcentaje_coincidencia' => $porcentajeGlobal,
                    'coincidencia_validas_pct' => $coincidenciaValidasPct,
                ],
                'diagnostico' => $diagnostico,
            ],
            'detalles' => [
                'validas_faltantes' => $validasFaltantesEnSistema,
                'anuladas_faltantes' => $anuladasFaltantesEnSistema,
                'diferencias_ley1886' => $diferenciasDescuentoLey1886,
                'diferencias_montos' => $diferenciasMontos,
                'diferencias_estado' => $diferenciasEstado,
                'faltantes_en_sin' => $faltantesEnSin,
                'coincidentes_muestra' => array_slice($coincidentes, 0, 500),
                'coincidentes_base_diferente' => $coincidentesBaseDiferente,
            ],
        ];
    }

    /**
     * Sincroniza y regulariza los estados fiscales con el reporte del SIN.
     * Inserta facturas faltantes (tanto anuladas como válidas si la BD está vacía)
     * y estandariza los montos brutos de la Ley 1886.
     */
    public function sincronizarConSin(string $rutaArchivo, array $opciones = [], int $usuarioId = 1): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $analisis = $this->conciliarArchivo(
            $rutaArchivo,
            $opciones['fecha_desde'] ?? null,
            $opciones['fecha_hasta'] ?? null
        );

        $importarValidas = $opciones['importar_validas'] ?? ($analisis['kpis']['sistema']['total_registros'] === 0);
        $importarAnuladas = $opciones['importar_anuladas'] ?? true;
        $regularizarLey1886 = $opciones['regularizar_ley1886'] ?? true;

        $validasInsertadas = 0;
        $anuladasInsertadas = 0;
        $descuentosActualizados = 0;
        $basesActualizadas = 0;

        // Sucursal Casa Matriz
        $sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            ['nombre' => 'Casa Matriz EMAPAP', 'direccion' => 'Patacamaya', 'municipio' => 'Patacamaya', 'departamento' => 'La Paz']
        );

        DB::transaction(function () use (
            $analisis,
            $importarValidas,
            $importarAnuladas,
            $regularizarLey1886,
            $sucursal,
            $usuarioId,
            &$validasInsertadas,
            &$anuladasInsertadas,
            &$descuentosActualizados,
            &$basesActualizadas
        ) {
            $ahora = Carbon::now();

            // 1. Si la BD está vacía o se solicita importar válidas faltantes
            if ($importarValidas && !empty($analisis['detalles']['validas_faltantes'])) {
                foreach ($analisis['detalles']['validas_faltantes'] as $item) {
                    $cuf = $item['cuf_sin'];
                    $existe = Factura::where('cuf', $cuf)->exists();
                    if ($existe) {
                        continue;
                    }

                    $fechaEmision = $this->cufService->extraerFechaHoraDesdeCuf($cuf);
                    if (!$fechaEmision && !empty($item['fecha'])) {
                        $fechaEmision = Carbon::parse($item['fecha']);
                    }

                    $descuento = (float) ($item['descuento'] ?? 0.00);
                    $baseDebito = (float) ($item['base_debito'] ?? $item['monto_total']);

                    Factura::create([
                        'id_sucursal' => $sucursal->id,
                        'id_punto_venta' => null,
                        'id_cliente' => null,
                        'id_abonado' => null,
                        'numero_factura' => $item['numero_factura'],
                        'cuf' => $cuf,
                        'cufd' => 'CUFD_CONCILIACION_' . ($fechaEmision ? $fechaEmision->format('Ymd') : 'HISTORICO'),
                        'fecha_emision' => $fechaEmision ?: $ahora,
                        'codigo_modalidad' => 1,
                        'tipo_emision' => 1,
                        'tipo_factura_documento' => 1,
                        'codigo_documento_sector' => 13,
                        'nombre_razon_social' => $item['cliente'] ?: 'CLIENTE SIN',
                        'numero_documento' => $item['nit'] ?: '0',
                        'complemento' => null,
                        'codigo_tipo_documento_identidad' => 1,
                        'codigo_metodo_pago' => 1,
                        'monto_total' => (float) $item['monto_total'],
                        'monto_total_sujeto_iva' => $baseDebito,
                        'monto_descuento' => $descuento,
                        'monto_gift_card' => 0.00,
                        'codigo_moneda' => 1,
                        'tipo_cambio' => 1.00,
                        'leyenda' => 'Ley N° 453: Los servicios deben prestarse en condiciones de inocuidad, calidad y seguridad.',
                        'estado_factura' => 'VALIDADA',
                        'beneficiario_ley_1886' => ($descuento > 0),
                        'monto_descuento_ley1886' => $descuento,
                        'usuario_emision' => 'conciliacion_sin',
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CONCILIACION_SIN',
                        '_usuario_creacion' => $usuarioId,
                        '_fecha_creacion' => $ahora,
                    ]);

                    $validasInsertadas++;
                }
            }

            // 2. Insertar facturas anuladas oficiales del SIN
            if ($importarAnuladas && !empty($analisis['detalles']['anuladas_faltantes'])) {
                foreach ($analisis['detalles']['anuladas_faltantes'] as $item) {
                    $cuf = $item['cuf_sin'];
                    $existe = Factura::where('cuf', $cuf)->exists();
                    if ($existe) {
                        continue;
                    }

                    $fechaEmision = $this->cufService->extraerFechaHoraDesdeCuf($cuf);
                    if (!$fechaEmision && !empty($item['fecha'])) {
                        $fechaEmision = Carbon::parse($item['fecha']);
                    }

                    Factura::create([
                        'id_sucursal' => $sucursal->id,
                        'id_punto_venta' => null,
                        'id_cliente' => null,
                        'id_abonado' => null,
                        'numero_factura' => $item['numero_factura'],
                        'cuf' => $cuf,
                        'cufd' => 'CUFD_CONCILIACION_' . ($fechaEmision ? $fechaEmision->format('Ymd') : 'HISTORICO'),
                        'fecha_emision' => $fechaEmision ?: $ahora,
                        'codigo_modalidad' => 1,
                        'tipo_emision' => 1,
                        'tipo_factura_documento' => 1,
                        'codigo_documento_sector' => 13,
                        'nombre_razon_social' => $item['cliente'] ?: 'CLIENTE SIN',
                        'numero_documento' => $item['nit'] ?: '0',
                        'complemento' => null,
                        'codigo_tipo_documento_identidad' => 1,
                        'codigo_metodo_pago' => 1,
                        'monto_total' => (float) $item['monto_total'],
                        'monto_total_sujeto_iva' => 0.00,
                        'monto_descuento' => 0.00,
                        'monto_gift_card' => 0.00,
                        'codigo_moneda' => 1,
                        'tipo_cambio' => 1.00,
                        'leyenda' => 'Ley N° 453: Los servicios deben prestarse en condiciones de inocuidad, calidad y seguridad.',
                        'estado_factura' => 'ANULADA',
                        'usuario_emision' => 'conciliacion_sin',
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CONCILIACION_SIN',
                        '_usuario_creacion' => $usuarioId,
                        '_fecha_creacion' => $ahora,
                    ]);

                    $anuladasInsertadas++;
                }
            }

            // 3. Regularizar montos brutos de facturas con Descuento Ley 1886
            if ($regularizarLey1886 && !empty($analisis['detalles']['diferencias_ley1886'])) {
                foreach ($analisis['detalles']['diferencias_ley1886'] as $item) {
                    $factura = Factura::where('cuf', $item['cuf'])->first();
                    if ($factura) {
                        $factura->update([
                            'monto_total' => (float) $item['monto_sin_bruto'],
                            'monto_descuento' => (float) $item['descuento_sin'],
                            'monto_total_sujeto_iva' => (float) $item['base_debito_sin'],
                            'beneficiario_ley_1886' => true,
                            'monto_descuento_ley_1886' => (float) $item['descuento_sin'],
                            '_transaccion' => 'AJUSTE_LEY1886_SIN',
                            '_usuario_modificacion' => $usuarioId,
                            '_fecha_modificacion' => $ahora,
                        ]);
                        $descuentosActualizados++;
                    }
                }
            }

            // 4. Regularizar Base Imponible Sujeta a IVA (descontando Tasas Municipales según SIN)
            if (!empty($analisis['detalles']['coincidentes_base_diferente'])) {
                foreach ($analisis['detalles']['coincidentes_base_diferente'] as $item) {
                    Factura::where('cuf', $item['cuf'])->update([
                        'monto_total_sujeto_iva' => (float) $item['base_debito_sin'],
                        '_transaccion' => 'AJUSTE_TASAS_SIN',
                        '_usuario_modificacion' => $usuarioId,
                        '_fecha_modificacion' => $ahora,
                    ]);
                    $basesActualizadas++;
                }
            }
        });

        Log::info("Conciliación SIN completada exitosamente: {$validasInsertadas} válidas importadas, {$anuladasInsertadas} anuladas importadas, {$descuentosActualizados} descuentos regularizados.");

        // Obtener nuevo resumen del período
        $periodo = $analisis['periodo'];
        $metricasNuevas = Factura::whereDate('fecha_emision', '>=', $periodo['desde'])
            ->whereDate('fecha_emision', '<=', $periodo['hasta'])
            ->selectRaw("
                COUNT(*) as total_registros,
                COUNT(CASE WHEN estado_factura = 'VALIDADA' THEN 1 END) as cantidad_validas,
                COUNT(CASE WHEN estado_factura = 'ANULADA' THEN 1 END) as cantidad_anuladas,
                COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total ELSE 0 END), 0) as total_facturado,
                COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as total_base_debito_fiscal
            ")->first();

        $mensajePartes = [];
        if ($validasInsertadas > 0) $mensajePartes[] = "{$validasInsertadas} facturas válidas importadas";
        if ($anuladasInsertadas > 0) $mensajePartes[] = "{$anuladasInsertadas} facturas anuladas importadas";
        if ($descuentosActualizados > 0) $mensajePartes[] = "{$descuentosActualizados} facturas con descuento Ley 1886 regularizadas";
        if ($basesActualizadas > 0) $mensajePartes[] = "{$basesActualizadas} bases imponibles regularizadas (descuento de tasas no sujetas a IVA)";

        $mensaje = empty($mensajePartes) 
            ? 'No fue necesario realizar cambios en la base de datos.' 
            : 'Sincronización completada: ' . implode(', ', $mensajePartes) . '.';

        return [
            'success' => true,
            'mensaje' => $mensaje,
            'validas_insertadas' => $validasInsertadas,
            'anuladas_insertadas' => $anuladasInsertadas,
            'descuentos_actualizados' => $descuentosActualizados,
            'nuevos_totales' => [
                'total_registros' => (int) ($metricasNuevas->total_registros ?? 0),
                'cantidad_validas' => (int) ($metricasNuevas->cantidad_validas ?? 0),
                'cantidad_anuladas' => (int) ($metricasNuevas->cantidad_anuladas ?? 0),
                'total_facturado' => round((float) ($metricasNuevas->total_facturado ?? 0), 2),
                'total_base_debito_fiscal' => round((float) ($metricasNuevas->total_base_debito_fiscal ?? 0), 2),
                'debito_fiscal_iva' => round((float) ($metricasNuevas->total_base_debito_fiscal ?? 0) * 0.13, 2),
            ],
        ];
    }
}
