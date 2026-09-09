<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\Calle;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\ConvenioPago;
use App\Models\Comercial\Medidor;
use App\Models\Comercial\ReciboCaja;
use App\Models\Comercial\Zona;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatSucursal;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FoxProMigradorService
{
    /**
     * Lee encabezados y registros de un archivo .DBF pequeño/mediano en memoria.
     *
     * @return array{total_records: int, fields: array, records: array}
     */
    public function leerDbf(string $rutaArchivo, int $limite = 0): array
    {
        if (!file_exists($rutaArchivo)) {
            throw new Exception("El archivo DBF no existe en la ruta: {$rutaArchivo}");
        }

        $fp = fopen($rutaArchivo, 'rb');
        if (!$fp) {
            throw new Exception("No se pudo abrir el archivo DBF: {$rutaArchivo}");
        }

        $header = fread($fp, 32);
        $unpacked = unpack('Vnum_records/vheader_len/vrecord_len', substr($header, 4, 8));
        $numRecords = $unpacked['num_records'];
        $headerLen = $unpacked['header_len'];
        $recordLen = $unpacked['record_len'];

        $fields = [];
        while (true) {
            $fieldData = fread($fp, 32);
            if (!$fieldData || ord($fieldData[0]) === 0x0D) {
                break;
            }
            $nombre = trim(substr($fieldData, 0, 11));
            $tipo = chr(ord($fieldData[11]));
            $longitud = ord($fieldData[16]);
            $decimales = ord($fieldData[17]);
            $fields[] = [
                'name' => $nombre,
                'type' => $tipo,
                'length' => $longitud,
                'decimals' => $decimales,
            ];
        }

        fseek($fp, $headerLen);
        $records = [];
        $count = 0;

        while (!feof($fp)) {
            if ($limite > 0 && $count >= $limite) {
                break;
            }

            $recordBytes = fread($fp, $recordLen);
            if (strlen($recordBytes) < $recordLen || ord($recordBytes[0]) === 0x1A) {
                break;
            }

            // Registro marcado como eliminado en dBase
            if (ord($recordBytes[0]) === 0x2A) {
                continue;
            }

            $rec = [];
            $offset = 1;
            foreach ($fields as $f) {
                $val = substr($recordBytes, $offset, $f['length']);
                $rec[$f['name']] = trim(mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1'));
                $offset += $f['length'];
            }

            $records[] = $rec;
            $count++;
        }

        fclose($fp);

        return [
            'total_records' => $numRecords,
            'fields' => $fields,
            'records' => $records,
        ];
    }

    /**
     * Itera un archivo DBF por bloques (chunks) de manera altamente eficiente en memoria.
     */
    public function iterarDbf(string $rutaArchivo, callable $callback, int $chunkSize = 1000, int $limite = 0): int
    {
        if (!file_exists($rutaArchivo)) {
            throw new Exception("El archivo DBF no existe en la ruta: {$rutaArchivo}");
        }

        $fp = fopen($rutaArchivo, 'rb');
        if (!$fp) {
            throw new Exception("No se pudo abrir el archivo DBF: {$rutaArchivo}");
        }

        $header = fread($fp, 32);
        $unpacked = unpack('Vnum_records/vheader_len/vrecord_len', substr($header, 4, 8));
        $numRecords = (int) ($unpacked['num_records'] ?? 0);
        $headerLen = (int) ($unpacked['header_len'] ?? 0);
        $recordLen = (int) ($unpacked['record_len'] ?? 0);

        $fields = [];
        while (true) {
            $fieldData = fread($fp, 32);
            if (!$fieldData || ord($fieldData[0]) === 0x0D) {
                break;
            }
            $fields[] = [
                'name' => trim(substr($fieldData, 0, 11)),
                'length' => ord($fieldData[16]),
            ];
        }

        fseek($fp, $headerLen);
        $chunk = [];
        $totalProcesados = 0;

        while (!feof($fp)) {
            if ($limite > 0 && $totalProcesados >= $limite) {
                break;
            }

            $recordBytes = fread($fp, $recordLen);
            if (strlen($recordBytes) < $recordLen || ord($recordBytes[0]) === 0x1A) {
                break;
            }

            $isDeleted = (ord($recordBytes[0]) === 0x2A);

            $rec = ['_deleted' => $isDeleted];
            $offset = 1;
            foreach ($fields as $f) {
                $val = substr($recordBytes, $offset, $f['length']);
                $rec[$f['name']] = trim(mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1'));
                $offset += $f['length'];
            }

            $chunk[] = $rec;
            $totalProcesados++;

            if (count($chunk) >= $chunkSize) {
                $callback($chunk, $totalProcesados, $numRecords);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            $callback($chunk, $totalProcesados, $numRecords);
        }

        fclose($fp);

        return $totalProcesados;
    }

    /**
     * Analiza o migra las calles desde calles.DBF.
     */
    public function migrarCalles(string $rutaCallesDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaCallesDbf);
        $total = count($data['records']);
        $procesadas = 0;
        $insertadas = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $nombreZona = trim($r['ZONA'] ?? '');
                $nombreCalle = trim($r['CALLE'] ?? '');

                if (empty($nombreCalle)) {
                    continue;
                }

                $zona = Zona::where('codigo', $nombreZona)->first();
                if (!$zona) {
                    $zona = Zona::firstOrCreate(
                        ['codigo' => $nombreZona],
                        ['nombre' => "Zona {$nombreZona}"]
                    );
                }

                if (!$dryRun) {
                    $calle = Calle::firstOrCreate(
                        [
                            'id_zona' => $zona->id,
                            'nombre' => $nombreCalle,
                        ]
                    );
                    if ($calle->wasRecentlyCreated) {
                        $insertadas++;
                    }
                } else {
                    $insertadas++;
                }

                $procesadas++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'calles_procesadas' => $procesadas,
                'calles_insertadas' => $insertadas,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Analiza o migra los abonados desde socios.DBF.
     */
    public function migrarAbonados(string $rutaSociosDbf, bool $dryRun = true, int $limite = 0, ?string $rutaApaguaDbf = null): array
    {
        $data = $this->leerDbf($rutaSociosDbf, $limite);
        $total = count($data['records']);
        $procesados = 0;
        $insertados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            $categoriasMap = CategoriaTarifaria::pluck('id', 'codigo')->toArray();
            $zonasMap = Zona::pluck('id', 'codigo')->toArray();

            foreach ($data['records'] as $r) {
                $codigo = trim($r['CODIGO'] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                $codigoPad = str_pad($codigo, 5, '0', STR_PAD_LEFT);
                $codZona = trim($r['ZONA'] ?? '');
                $idZona = $zonasMap[$codZona] ?? null;

                if (!$idZona) {
                    $zonaObj = Zona::firstOrCreate(['codigo' => $codZona], ['nombre' => "Zona {$codZona}"]);
                    $idZona = $zonaObj->id;
                    $zonasMap[$codZona] = $idZona;
                }

                $codCat = trim($r['CATEGOR'] ?? 'D');
                $idCat = $categoriasMap[$codCat] ?? ($categoriasMap['D'] ?? 1);

                $estadoFox = strtoupper(trim($r['ESTADO'] ?? 'A'));
                $estadoServicio = match ($estadoFox) {
                    'C' => 'CORTADO',
                    'B' => 'BAJA',
                    default => 'ACTIVO',
                };

                $esTerceraEdad = ((float) ($r['TEREDAD'] ?? 0) == 1.00);
                $tieneAlcantarillado = (strtoupper(trim($r['ALCANTA'] ?? 'SI')) === 'SI' || (int) ($r['ALCANTARI'] ?? 1) === 1);
                $saldoDeuda = (float) ($r['DEUDA'] ?? 0.00);
                $mesesMora = (int) ($r['PENDIENTES'] ?? 0);

                if (!$dryRun) {
                    // Gestión de Medidor si aplica
                    $idMedidor = null;
                    $nroMedidor = trim($r['MEDIDOR'] ?? '');
                    if (!empty($nroMedidor) && $nroMedidor !== '0' && strtoupper($nroMedidor) !== 'SIN MEDIDOR') {
                        $medidor = Medidor::firstOrCreate(
                            ['numero_serie' => $nroMedidor],
                            [
                                'marca' => 'Sensus',
                                'diametro' => '1/2"',
                                'estado' => 'OPERATIVO',
                            ]
                        );
                        $idMedidor = $medidor->id;
                    }

                    $nomCompleto = trim($r['NOMBRE'] ?? '');
                    $pat = trim($r['PATERNO'] ?? '');
                    $mat = trim($r['MATERNO'] ?? '');
                    $nom = trim($r['NOMBRES'] ?? '');

                    if (empty($nomCompleto)) {
                        $nomCompleto = trim("{$pat} {$mat} {$nom}");
                    }

                    $abonado = Abonado::updateOrCreate(
                        ['codigo' => $codigoPad],
                        [
                            'tipo_persona' => ((int) ($r['TIPOPER'] ?? 1) === 2) ? 'JURIDICA' : 'NATURAL',
                            'primer_apellido' => $pat ?: null,
                            'segundo_apellido' => $mat ?: null,
                            'nombres' => $nom ?: null,
                            'nombre_completo' => $nomCompleto ?: "ABONADO {$codigoPad}",
                            'numero_documento' => trim($r['NIT'] ?? '') ?: null,
                            'telefono' => trim($r['TELEFONO1'] ?? '') ?: null,
                            'celular' => trim($r['CELULAR'] ?? '') ?: null,
                            'id_zona' => $idZona,
                            'numero_vivienda' => trim($r['NUMERO1'] ?? '') ?: null,
                            'edificio' => trim($r['EDIFICIO1'] ?? '') ?: null,
                            'departamento' => trim($r['DEPTO1'] ?? '') ?: null,
                            'id_categoria' => $idCat,
                            'tiene_alcantarillado' => $tieneAlcantarillado,
                            'es_tercera_edad' => $esTerceraEdad,
                            'tiene_medidor' => ($idMedidor !== null),
                            'id_medidor_actual' => $idMedidor,
                            'estado_servicio' => $estadoServicio,
                            'saldo_deuda' => $saldoDeuda,
                            'meses_mora' => $mesesMora,
                            'observaciones' => trim($r['OBS'] ?? '') ?: null,
                        ]
                    );

                    if ($abonado->wasRecentlyCreated) {
                        $insertados++;
                    }
                } else {
                    $insertados++;
                }

                $procesados++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'abonados_procesados' => $procesados,
                'abonados_insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra las bajas registradas desde bajasoc.DBF.
     */
    public function migrarBajas(string $rutaBajasDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaBajasDbf);
        $actualizados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $socio = trim($r['SOCIO'] ?? '');
                if (empty($socio)) {
                    continue;
                }
                $codigoPad = str_pad($socio, 5, '0', STR_PAD_LEFT);
                $motivo = trim($r['OTROSCB'] ?? 'BAJA REGISTRADA EN FOXPRO');

                if (!$dryRun) {
                    $updated = Abonado::where('codigo', $codigoPad)->update([
                        'estado_servicio' => 'BAJA',
                        'observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), ' [BAJA FOX: ' || '{$motivo}' || ']')"),
                    ]);
                    if ($updated > 0) {
                        $actualizados++;
                    }
                } else {
                    $actualizados++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_bajas_dbf' => count($data['records']),
                'abonados_dados_de_baja' => $actualizados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra convenios y sus cuotas desde convenio.DBF y detconve.dbf.
     */
    public function migrarConvenios(string $rutaConvenioDbf, string $rutaDetconveDbf, bool $dryRun = true): array
    {
        $dataConvenio = $this->leerDbf($rutaConvenioDbf);
        $totalConvenios = count($dataConvenio['records']);
        $conveniosInsertados = 0;
        $cuotasInsertadas = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            $abonadosMap = Abonado::pluck('id', 'codigo')->toArray();

            foreach ($dataConvenio['records'] as $r) {
                $socio = trim($r['CODIGO'] ?? '');
                if (empty($socio)) {
                    continue;
                }
                $codigoPad = str_pad($socio, 5, '0', STR_PAD_LEFT);
                $idAbonado = $abonadosMap[$codigoPad] ?? null;
                if (!$idAbonado) {
                    continue;
                }

                $nroConvenio = 'CONV-FOX-' . $codigoPad . '-' . trim($r['PERIODO'] ?? '0');
                $montoTotal = (float) ($r['CARGO'] ?: ($r['IMPORTE'] ?? 0.00));
                $abono = (float) ($r['ABONO'] ?? 0.00);
                $saldo = (float) ($r['SALDO'] ?? 0.00);
                $plazo = max(1, (int) ($r['PLAZO'] ?? 1));
                $cuotaMensual = $plazo > 0 ? round($montoTotal / $plazo, 2) : $montoTotal;
                $estadoConvenio = (strtoupper(trim($r['PAGADO'] ?? '')) === 'S' || $saldo <= 0) ? 'CUMPLIDO' : 'VIGENTE';

                $fechaSuscripcion = !empty($r['FECHA']) && strlen($r['FECHA']) === 8
                    ? Carbon::createFromFormat('Ymd', $r['FECHA'])
                    : Carbon::now();

                if (!$dryRun) {
                    $convenio = ConvenioPago::firstOrCreate(
                        ['numero_convenio' => $nroConvenio],
                        [
                            'id_abonado' => $idAbonado,
                            'monto_deuda_total' => $montoTotal,
                            'pago_inicial' => $abono,
                            'saldo_financiado' => $saldo,
                            'plazo_meses' => $plazo,
                            'monto_cuota_mensual' => $cuotaMensual,
                            'fecha_suscripcion' => $fechaSuscripcion,
                            'estado' => $estadoConvenio,
                            'glosa' => trim($r['OBSER'] ?? '') ?: "Migrado desde FoxPro (Periodo {$r['PERIODO']})",
                        ]
                    );

                    if ($convenio->wasRecentlyCreated) {
                        $conveniosInsertados++;
                    }
                } else {
                    $conveniosInsertados++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_convenios_dbf' => $totalConvenios,
                'convenios_insertados' => $conveniosInsertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra los comprobantes de otros ingresos y trámites administrativos desde recibos.DBF.
     */
    public function migrarRecibosOtros(string $rutaRecibosDbf, bool $dryRun = true, ?callable $progressCallback = null): array
    {
        $abonadosMap = Abonado::pluck('id', 'codigo')->toArray();
        $insertados = 0;
        $totalRecords = 0;

        $conceptosMap = [
            '01' => 'FORMULARIO_SUSPENSION',
            '02' => 'RECONEXION',
            '03' => 'DERECHO_CONEXION',
            '04' => 'CAMBIO_NOMBRE',
            '05' => 'APORTE',
            '06' => 'INSTALACION',
            '07' => 'CAMBIO_MEDIDOR',
        ];

        $chunkHandler = function (array $chunk, int $procesados, int $total) use (
            &$insertados,
            &$totalRecords,
            $dryRun,
            $abonadosMap,
            $conceptosMap,
            $progressCallback
        ) {
            $totalRecords = $total;
            $batch = [];

            foreach ($chunk as $r) {
                $factura = trim($r['FACTURA'] ?? '');
                $numero = trim($r['NUMERO'] ?? '');
                $nroId = $factura ?: ($numero ?: (string) rand(100000, 999999));
                $codcon = trim($r['CODCON'] ?? '');
                $conceptoTipo = $conceptosMap[$codcon] ?? 'OTROS_INGRESOS';

                $nroRecibo = "REC-{$nroId}-{$codcon}";

                $socio = trim($r['SOCIO'] ?? '');
                $idAbonado = null;
                if (!empty($socio)) {
                    $codigoPad = str_pad($socio, 5, '0', STR_PAD_LEFT);
                    $idAbonado = $abonadosMap[$codigoPad] ?? null;
                }

                $monto = (float) ($r['IMPORTE'] ?: ($r['TOTAL'] ?? 0.00));
                $fechaStr = trim($r['FECHA'] ?? '');
                $fechaCobro = (!empty($fechaStr) && strlen($fechaStr) === 8)
                    ? Carbon::createFromFormat('Ymd', $fechaStr)->startOfDay()
                    : Carbon::now();

                $batch[] = [
                    'numero_recibo' => $nroRecibo,
                    'id_abonado' => $idAbonado,
                    'nombre_cliente' => trim($r['NOMBRE'] ?? '') ?: 'CLIENTE GENERAL',
                    'documento_cliente' => trim($r['RUC'] ?? '') ?: null,
                    'concepto_tipo' => $conceptoTipo,
                    'descripcion' => trim($r['CONCEPTO'] ?? '') ?: "Concepto código {$codcon}",
                    'monto_total' => $monto,
                    'fecha_cobro' => $fechaCobro,
                    'id_cajero' => 1,
                    'estado' => 'VALIDO',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MIGRACION',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => Carbon::now(),
                ];
            }

            if (!$dryRun && !empty($batch)) {
                DB::table('comercial.recibos_caja')->insertOrIgnore($batch);
            }

            $insertados += count($batch);

            if ($progressCallback) {
                $progressCallback($procesados, $total);
            }
        };

        $this->iterarDbf($rutaRecibosDbf, $chunkHandler, 1000);

        return [
            'dry_run' => $dryRun,
            'total_en_dbf' => $totalRecords,
            'recibos_migrados' => $insertados,
        ];
    }

    /**
     * Migra el histórico real de facturas emitidas desde ventas.DBF (268,137 registros a 2026).
     */
    public function migrarFacturasVentas(string $rutaVentasDbf, bool $dryRun = true, int $limite = 0, ?callable $progressCallback = null): array
    {
        // 1. Asegurar Sucursal Casa Matriz EMAPAP
        $sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'Casa Matriz EMAPAP',
                'direccion' => 'Av. Panamericana s/n, Plaza 15 de Agosto',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
            ]
        );

        $abonadosMap = Abonado::pluck('id', 'codigo')->toArray();
        $insertados = 0;
        $totalRecords = 0;

        $chunkHandler = function (array $chunk, int $procesados, int $total) use (
            &$insertados,
            &$totalRecords,
            $dryRun,
            $sucursal,
            $abonadosMap,
            $progressCallback
        ) {
            $totalRecords = $total;
            $batchFacturas = [];
            $ahora = Carbon::now();

            foreach ($chunk as $idx => $r) {
                $facturaNum = (int) ($r['FACTURA'] ?? 0);
                $orden = trim($r['ORDEN'] ?? '');
                $cufRaw = trim($r['CUF'] ?? '');
                $fechaStr = trim($r['FECHA'] ?? '');

                if ($facturaNum <= 0 && empty($cufRaw)) {
                    continue;
                }

                // Generar identificador CUF unívoco
                if (!empty($cufRaw)) {
                    $cufFinal = $cufRaw;
                    $esSiat = true;
                } else {
                    $ordPart = $orden ?: 'SFV';
                    $cufFinal = "SFV-{$ordPart}-{$facturaNum}-{$fechaStr}-" . ($procesados - count($chunk) + $idx);
                    $esSiat = false;
                }

                $socio = trim($r['SOCIO'] ?? '');
                $idAbonado = null;
                if (!empty($socio)) {
                    $codigoPad = str_pad($socio, 5, '0', STR_PAD_LEFT);
                    $idAbonado = $abonadosMap[$codigoPad] ?? null;
                }

                $fechaEmision = (!empty($fechaStr) && strlen($fechaStr) === 8)
                    ? Carbon::createFromFormat('Ymd', $fechaStr)->startOfDay()
                    : $ahora;

                $montoTotal = (float) ($r['IMPORTE'] ?? 0.00);
                $excento = (float) ($r['EXCENTO'] ?? 0.00);
                $descuentos = (float) ($r['DESCTOS'] ?? 0.00);
                $montoSujetoIva = max(0, $montoTotal - $excento);

                $batchFacturas[] = [
                    'id_sucursal' => $sucursal->id,
                    'id_punto_venta' => null,
                    'id_cliente' => null,
                    'id_abonado' => $idAbonado,
                    'id_cufd' => null,
                    'numero_factura' => $facturaNum > 0 ? $facturaNum : 1,
                    'cuf' => $cufFinal,
                    'cufd' => $esSiat ? "CUFD_HISTORICO_{$fechaStr}" : null,
                    'codigo_control' => trim($r['CCONTROL'] ?? '') ?: '0',
                    'codigo_autorizacion_sfv' => $orden ?: null,
                    'fecha_emision' => $fechaEmision,
                    'codigo_modalidad' => $esSiat ? 1 : 2,
                    'tipo_emision' => 1,
                    'tipo_factura_documento' => 1,
                    'codigo_documento_sector' => 13, // Servicios Básicos
                    'nombre_razon_social' => trim($r['NOMBRE'] ?? '') ?: 'CLIENTE GENERAL',
                    'numero_documento' => trim($r['RUC'] ?? '') ?: '0',
                    'complemento' => trim($r['ALFANUMERI'] ?? '') ?: null,
                    'codigo_tipo_documento_identidad' => 1,
                    'codigo_metodo_pago' => 1,
                    'monto_total' => $montoTotal,
                    'monto_total_sujeto_iva' => $montoSujetoIva,
                    'monto_descuento' => $descuentos,
                    'monto_gift_card' => 0.00,
                    'codigo_moneda' => 1,
                    'tipo_cambio' => 1.00,
                    'ajuste_no_sujeto_iva' => $excento,
                    'otros_pagos_no_sujeto_iva' => (float) ($r['OTROSCOB'] ?? 0.00),
                    'detalle_otros_pagos_no_sujeto_iva' => trim($r['OTROSCB'] ?? '') ?: null,
                    'leyenda' => 'Ley N° 453: Los servicios deben prestarse en condiciones de inocuidad, calidad y seguridad.',
                    'usuario_emision' => 'migracion_foxpro',
                    'estado_factura' => !empty($r['_deleted']) ? 'ANULADA' : 'VALIDADA',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MIGRACION',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => $ahora,
                ];
            }

            if (!$dryRun && !empty($batchFacturas)) {
                DB::table('facturacion.facturas')->insertOrIgnore($batchFacturas);
            }

            $insertados += count($batchFacturas);

            if ($progressCallback) {
                $progressCallback($procesados, $total);
            }
        };

        $this->iterarDbf($rutaVentasDbf, $chunkHandler, 1000, $limite);

        return [
            'dry_run' => $dryRun,
            'total_en_dbf' => $totalRecords,
            'facturas_migradas' => $insertados,
        ];
    }

    /**
     * Migra lecturas mensuales históricas y pagos desde operacio.DBF hacia comercial.lecturas_mensuales.
     */
    public function migrarOperacionesDbf(
        string $rutaOperacioDbf,
        bool $dryRun = true,
        ?string $fechaLimite = null,
        int $limite = 0,
        ?callable $progressCallback = null
    ): array {
        if (!file_exists($rutaOperacioDbf)) {
            throw new Exception("El archivo operacio.DBF no existe en: {$rutaOperacioDbf}");
        }

        $abonadosMap = DB::table('comercial.abonados')->pluck('id', 'codigo')->toArray();
        $periodosMap = DB::table('comercial.periodos_facturacion')->pluck('id', 'periodo')->toArray();

        $insertados = 0;
        $omitidos = 0;
        $ahora = now();

        $chunkHandler = function (array $chunk, int $totalProcesados, int $totalRecordsDbf) use (
            &$abonadosMap,
            &$periodosMap,
            $dryRun,
            $fechaLimite,
            &$insertados,
            &$omitidos,
            $ahora,
            $progressCallback
        ) {
            $batch = [];

            foreach ($chunk as $r) {
                $codigo = trim($r['CODIGO'] ?? '');
                if (empty($codigo)) {
                    $omitidos++;
                    continue;
                }

                $codigoPad = str_pad($codigo, 5, '0', STR_PAD_LEFT);
                $idAbonado = $abonadosMap[$codigoPad] ?? null;
                if (!$idAbonado) {
                    $omitidos++;
                    continue;
                }

                $perStr = trim($r['PERIODO'] ?? '');
                if (empty($perStr) || !str_contains($perStr, '/')) {
                    $omitidos++;
                    continue;
                }

                $fechapaRaw = trim($r['FECHAPA'] ?? '');
                $fechaPago = null;
                if (!empty($fechapaRaw)) {
                    if (strlen($fechapaRaw) === 8 && ctype_digit($fechapaRaw)) {
                        $y = substr($fechapaRaw, 0, 4);
                        $m = substr($fechapaRaw, 4, 2);
                        $d = substr($fechapaRaw, 6, 2);
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $fechaPago = "{$y}-{$m}-{$d}";
                        }
                    } elseif (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $fechapaRaw, $m)) {
                        $fechaPago = "{$m[1]}-{$m[2]}-{$m[3]}";
                    } elseif (preg_match('/^(\d{2})[-\/](\d{2})[-\/](\d{4})$/', $fechapaRaw, $m)) {
                        $fechaPago = "{$m[3]}-{$m[2]}-{$m[1]}";
                    }
                }

                $fechaLecturaRaw = trim($r['FECHA'] ?? '');
                $fechaLectura = null;
                if (!empty($fechaLecturaRaw)) {
                    if (strlen($fechaLecturaRaw) === 8 && ctype_digit($fechaLecturaRaw)) {
                        $y = substr($fechaLecturaRaw, 0, 4);
                        $m = substr($fechaLecturaRaw, 4, 2);
                        $d = substr($fechaLecturaRaw, 6, 2);
                        if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                            $fechaLectura = "{$y}-{$m}-{$d}";
                        }
                    } elseif (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $fechaLecturaRaw, $m)) {
                        $fechaLectura = "{$m[1]}-{$m[2]}-{$m[3]}";
                    }
                }

                $partesPer = explode('/', $perStr);
                $mesPer = max(1, min(12, (int) ($partesPer[0] ?? 1)));
                $gestionPer = max(2000, (int) ($partesPer[1] ?? 2026));
                $fechaPeriodoFin = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $gestionPer, $mesPer)));

                if ($fechaLimite) {
                    $fechaComparacion = $fechaPago ?: $fechaPeriodoFin;
                    if ($fechaComparacion > $fechaLimite) {
                        $omitidos++;
                        continue;
                    }
                }

                if (!isset($periodosMap[$perStr])) {
                    if (!$dryRun) {
                        $nuevoPeriodoId = DB::table('comercial.periodos_facturacion')->insertGetId([
                            'periodo' => $perStr,
                            'mes' => $mesPer,
                            'gestion' => $gestionPer,
                            'fecha_inicio_consumo' => sprintf('%04d-%02d-01', $gestionPer, $mesPer),
                            'fecha_fin_consumo' => $fechaPeriodoFin,
                            'fecha_vencimiento_pago' => sprintf('%04d-%02d-25', $gestionPer, $mesPer),
                            'estado' => ($gestionPer < 2026 || ($gestionPer == 2026 && $mesPer < 8)) ? 'CERRADO' : 'ABIERTO',
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIG_OPERACIO',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => $ahora,
                        ]);
                        $periodosMap[$perStr] = $nuevoPeriodoId;
                    } else {
                        $periodosMap[$perStr] = 999999;
                    }
                }
                $idPeriodo = $periodosMap[$perStr];

                $anterior = (float) trim($r['ANTERIOR'] ?? 0);
                $actual = (float) trim($r['ACTUAL'] ?? 0);
                $consumo = (float) trim($r['CONSUMO'] ?? 0);
                $montoAgua = (float) trim($r['IMPAGUA'] ?? 0);
                $montoAlca = (float) trim($r['IMPALCA'] ?? 0);
                $descto = (float) trim($r['DESCTO3'] ?? ($r['DESCTO'] ?? 0));
                $otros = (float) trim($r['OTROS'] ?? 0);
                $totalFacturado = $montoAgua + $montoAlca + $otros - $descto;

                $pagado = strtoupper(trim($r['PAGADO'] ?? ''));
                $esPagado = ($pagado === 'S');

                $batch[] = [
                    'id_periodo' => $idPeriodo,
                    'id_abonado' => $idAbonado,
                    'lectura_anterior' => $anterior,
                    'lectura_actual' => $actual,
                    'consumo_m3' => max(0, $consumo),
                    'es_estimada' => false,
                    'fecha_lectura' => $fechaLectura,
                    'monto_agua' => $montoAgua,
                    'monto_alcantarillado' => $montoAlca,
                    'monto_descuento_ley1886' => $descto,
                    'monto_otros' => $otros,
                    'total_facturado' => max(0, $totalFacturado),
                    'estado_pago' => $esPagado ? 'PAGADO' : 'PENDIENTE',
                    'fecha_pago' => $fechaPago,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MIG_OPERACIO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => $ahora,
                ];
            }

            if (!$dryRun && !empty($batch)) {
                DB::table('comercial.lecturas_mensuales')->upsert(
                    $batch,
                    ['id_periodo', 'id_abonado'],
                    [
                        'lectura_anterior',
                        'lectura_actual',
                        'consumo_m3',
                        'fecha_lectura',
                        'monto_agua',
                        'monto_alcantarillado',
                        'monto_descuento_ley1886',
                        'monto_otros',
                        'total_facturado',
                        'estado_pago',
                        'fecha_pago',
                        '_fecha_modificacion' => $ahora,
                    ]
                );
            }

            $insertados += count($batch);

            if ($progressCallback) {
                $progressCallback($totalProcesados, $totalRecordsDbf);
            }
        };

        $totalEnDbf = $this->iterarDbf($rutaOperacioDbf, $chunkHandler, 1000, $limite);

        return [
            'dry_run' => $dryRun,
            'total_en_dbf' => $totalEnDbf,
            'lecturas_migradas' => $insertados,
            'omitidos' => $omitidos,
        ];
    }
}
