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
use App\Models\Parametrica;
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
        @set_time_limit(0);
        DB::disableQueryLog();

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
     * Analiza o migra las zonas desde zonas.dbf.
     */
    public function migrarZonas(string $rutaZonasDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaZonasDbf);
        $total = count($data['records']);
        $insertadas = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $nombreZona = trim($r['ZONA'] ?? '');
                if (empty($nombreZona)) {
                    continue;
                }

                if (!$dryRun) {
                    $zona = Zona::firstOrCreate(
                        ['codigo' => $nombreZona],
                        ['nombre' => "Zona {$nombreZona}"]
                    );
                    if ($zona->wasRecentlyCreated) {
                        $insertadas++;
                    }
                } else {
                    $insertadas++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertadas,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Analiza o migra los estados de abonados desde estado.dbf a la paramétrica TABLA_COMERCIAL_ESTADOS_ABONADO.
     */
    public function migrarEstadosAbonados(string $rutaEstadoDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaEstadoDbf);
        $total = count($data['records']);
        $insertados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            if (!$dryRun) {
                Parametrica::updateOrCreate(
                    ['param_tabla' => 'TABLA_COMERCIAL_ESTADOS_ABONADO', 'param_codigo' => 'ORIGEN', 'param_valor' => 0],
                    [
                        'param_nombre' => 'ESTADOS DE SERVICIO DEL ABONADO',
                        'param_descripcion' => 'Estados operativos del suministro de agua potable: Activo, Corte, Suspendido, Permiso',
                        'param_estado' => 'A',
                        'param_usr_registrado' => 1,
                    ]
                );
            }

            $idx = 1;
            foreach ($data['records'] as $r) {
                $cod = strtoupper(trim($r['ESTADO'] ?? ''));
                $desc = strtoupper(trim($r['DESCRIP'] ?? ''));
                if (empty($cod)) {
                    continue;
                }

                if (!$dryRun) {
                    Parametrica::updateOrCreate(
                        [
                            'param_tabla' => 'TABLA_COMERCIAL_ESTADOS_ABONADO',
                            'param_codigo' => $cod,
                        ],
                        [
                            'param_nombre' => $desc ?: $cod,
                            'param_descripcion' => "Estado de servicio: {$desc} ({$cod}) migrado de FoxPro",
                            'param_valor' => $idx++,
                            'param_estado' => 'A',
                            'param_usr_registrado' => 1,
                        ]
                    );
                    $insertados++;
                } else {
                    $insertados++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Analiza o migra los conceptos de otros ingresos desde concepin.dbf a la paramétrica TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS.
     */
    public function migrarConceptosIngresos(string $rutaConcepinDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaConcepinDbf);
        $total = count($data['records']);
        $insertados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            if (!$dryRun) {
                Parametrica::updateOrCreate(
                    ['param_tabla' => 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS', 'param_codigo' => 'ORIGEN', 'param_valor' => 0],
                    [
                        'param_nombre' => 'CONCEPTOS DE OTROS INGRESOS Y SERVICIOS',
                        'param_descripcion' => 'Catálogo de cobros no tarifarios: Reconexión, Multas, Cambio de Medidor, etc.',
                        'param_estado' => 'A',
                        'param_usr_registrado' => 1,
                    ]
                );
            }

            $idx = 1;
            foreach ($data['records'] as $r) {
                $cod = trim($r['CODIGO'] ?? '');
                $desc = strtoupper(trim($r['DESCRIP'] ?? ''));
                if (empty($cod)) {
                    continue;
                }

                if (!$dryRun) {
                    $valNum = is_numeric($cod) ? (int)$cod : $idx;
                    $param = Parametrica::updateOrCreate(
                        [
                            'param_tabla' => 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS',
                            'param_codigo' => $cod,
                        ],
                        [
                            'param_nombre' => $desc ?: "CONCEPTO {$cod}",
                            'param_descripcion' => "Concepto de ingreso {$cod}: {$desc} migrado de FoxPro",
                            'param_valor' => $valNum,
                            'param_estado' => 'A',
                            'param_usr_registrado' => 1,
                        ]
                    );
                    $insertados++;
                } else {
                    $insertados++;
                }
                $idx++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
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
            $callesMap = Calle::pluck('id', 'nombre')->toArray();

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

                $codCalle = trim($r['CALLE'] ?? '');
                $idCalle = $callesMap[$codCalle] ?? null;
                if (!$idCalle && !empty($codCalle) && !$dryRun) {
                    $calleObj = Calle::firstOrCreate(
                        ['nombre' => $codCalle, 'id_zona' => $idZona],
                        ['_estado' => 'ACTIVO', '_transaccion' => 'MIGRACION']
                    );
                    $idCalle = $calleObj->id;
                    $callesMap[$codCalle] = $idCalle;
                }

                $codCat = trim($r['CATEGOR'] ?? 'D');
                $idCat = $categoriasMap[$codCat] ?? ($categoriasMap['D'] ?? 1);

                $estadoFox = strtoupper(trim($r['ESTADO'] ?? 'A'));
                $estadoServicio = match ($estadoFox) {
                    'C' => 'CORTE',
                    'S' => 'SUSPENDIDO',
                    'P' => 'PERMISO',
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
                            'complemento' => trim($r['COMP_CI'] ?? '') ?: null,
                            'telefono' => trim($r['TELEFONO1'] ?? '') ?: null,
                            'celular' => trim($r['CELULAR'] ?? '') ?: null,
                            'id_zona' => $idZona,
                            'id_calle' => $idCalle,
                            'numero_vivienda' => trim($r['NUMERO1'] ?? '') ?: null,
                            'edificio' => trim($r['EDIFICIO1'] ?? '') ?: null,
                            'departamento' => trim($r['DEPTO1'] ?? '') ?: null,
                            'id_categoria' => $idCat,
                            'tiene_alcantarillado' => $tieneAlcantarillado,
                            'es_tercera_edad' => $esTerceraEdad,
                            'tiene_medidor' => ($idMedidor !== null),
                            'id_medidor_actual' => $idMedidor,
                            'estado_servicio' => $estadoServicio,
                            'fecha_ingreso' => (!empty($r['FECHAING']) && strlen(trim($r['FECHAING'])) === 8)
                                ? substr($r['FECHAING'], 0, 4) . '-' . substr($r['FECHAING'], 4, 2) . '-' . substr($r['FECHAING'], 6, 2)
                                : null,
                            'saldo_deuda' => $saldoDeuda,
                            'meses_mora' => $mesesMora,
                            'observaciones' => (!empty(trim($r['FAX'] ?? '')))
                                ? (trim($r['OBS'] ?? '') ? trim($r['OBS']) . ' | Email: ' . trim($r['FAX']) : 'Email: ' . trim($r['FAX']))
                                : (trim($r['OBS'] ?? '') ?: null),
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
                    $safeObs = DB::connection()->getPdo()->quote(" [BAJA FOX: {$motivo}]");
                    $updated = Abonado::where('codigo', $codigoPad)->update([
                        'estado_servicio' => 'BAJA',
                        'observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), {$safeObs})"),
                    ]);
                    if ($updated > 0) {
                        $actualizados++;
                    }

                    // Registrar en comercial.abonados_bajas
                    $abonado = Abonado::where('codigo', $codigoPad)->first();
                    $fechaRaw = trim($r['FECHA'] ?? '');
                    $fechaBaja = (!empty($fechaRaw) && strlen($fechaRaw) === 8)
                        ? Carbon::createFromFormat('Ymd', $fechaRaw)->toDateString()
                        : Carbon::now()->toDateString();

                    DB::table('comercial.abonados_bajas')->updateOrInsert(
                        [
                            'codigo_socio' => $codigoPad,
                            'factura' => trim($r['FACTURA'] ?? '') ?: '0',
                        ],
                        [
                            'nombre_socio' => trim($r['NOMBRE'] ?? '') ?: ($abonado ? $abonado->nombre : 'ABONADO ' . $codigoPad),
                            'ci_ruc' => trim($r['RUC'] ?? '') ?: null,
                            'fecha_baja' => $fechaBaja,
                            'motivo' => $motivo,
                            'importe' => (float) ($r['IMPORTE'] ?? 0),
                            'saldo' => (float) ($r['NETO'] ?? 0),
                            'observaciones' => trim(($r['AUTORIZ'] ?? '') . ' ' . $motivo),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
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
                'total_en_dbf' => count($data['records']),
                'abonados_dados_de_baja' => $actualizados,
                'insertados' => $actualizados,
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

        $seenKeys = [];
        $chunkHandler = function (array $chunk, int $procesados, int $total) use (
            &$insertados,
            &$totalRecords,
            &$seenKeys,
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
                $nroId = $factura ?: ($numero ?: (string) ($procesados + count($batch) + 1));
                $codcon = trim($r['CODCON'] ?? '');
                $conceptoTipo = $conceptosMap[$codcon] ?? 'OTROS_INGRESOS';

                $baseKey = 'REC-' . substr($nroId, 0, 10);
                if (!empty($codcon)) {
                    $baseKey .= '-' . substr($codcon, 0, 2);
                }
                if (isset($seenKeys[$baseKey])) {
                    $seenKeys[$baseKey]++;
                    $nroRecibo = substr($baseKey, 0, 20) . '-' . $seenKeys[$baseKey];
                } else {
                    $seenKeys[$baseKey] = 1;
                    $nroRecibo = $baseKey;
                }

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
                DB::transaction(function () use ($batch) {
                    DB::table('comercial.recibos_caja')->insertOrIgnore($batch);
                });
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
            'insertados' => $insertados,
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
                DB::transaction(function () use ($batchFacturas) {
                    DB::table('facturacion.facturas')->insertOrIgnore($batchFacturas);
                });
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
                    if (!$dryRun) {
                        $nomSocio = trim($r['NOMBRE'] ?? "ABONADO HISTORICO {$codigoPad}");
                        $zonaCod = trim($r['ZONA'] ?? '');
                        $calleNom = trim($r['CALLE'] ?? '');
                        $idZona = null;
                        if (!empty($zonaCod)) {
                            $idZona = DB::table('comercial.zonas')->where('codigo', $zonaCod)->value('id');
                            if (!$idZona) {
                                $idZona = DB::table('comercial.zonas')->insertGetId([
                                    'codigo' => $zonaCod,
                                    'nombre' => "Zona {$zonaCod}",
                                    '_estado' => 'ACTIVO',
                                    '_transaccion' => 'MIG_AUTO_FK',
                                    '_fecha_creacion' => $ahora,
                                ]);
                            }
                        } else {
                            $idZona = DB::table('comercial.zonas')->value('id');
                        }
                        $idCat = DB::table('comercial.categorias_tarifarias')->value('id');

                        $idAbonado = DB::table('comercial.abonados')->insertGetId([
                            'codigo' => $codigoPad,
                            'tipo_persona' => 'NATURAL',
                            'nombre_completo' => substr($nomSocio, 0, 200) ?: "ABONADO {$codigoPad}",
                            'nombres' => substr($nomSocio, 0, 100),
                            'primer_apellido' => null,
                            'segundo_apellido' => null,
                            'numero_documento' => null,
                            'referencia_direccion' => substr($calleNom ?: 'PATACAMAYA', 0, 150),
                            'id_categoria' => $idCat,
                            'id_zona' => $idZona,
                            'tiene_alcantarillado' => false,
                            'es_tercera_edad' => false,
                            'tiene_medidor' => false,
                            'estado_servicio' => 'BAJA',
                            'saldo_deuda' => 0.00,
                            'meses_mora' => 0,
                            'observaciones' => 'Abonado histórico recuperado desde operacio.dbf para preservar integridad referencial',
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIG_HISTORICO',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => $ahora,
                        ]);
                        $abonadosMap[$codigoPad] = $idAbonado;
                    } else {
                        $idAbonado = 999999;
                        $abonadosMap[$codigoPad] = 999999;
                    }
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
                $esPagado = ($pagado === 'S') || ($totalFacturado <= 0.001);

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
                DB::transaction(function () use ($batch, $ahora) {
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
                });
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

    /**
     * Migra aportes de conexión de agua o alcantarillado (apagua.dbf / alcanta.dbf).
     */
    public function migrarAportesConexiones(string $rutaDbf, string $tipoServicio, bool $dryRun = true, int $limite = 0): array
    {
        $data = $this->leerDbf($rutaDbf, $limite);
        $total = count($data['records']);
        $insertados = 0;
        $batch = [];
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codigo = trim($r['CODIGO'] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                $fechaStr = trim($r['FECHA'] ?? '');
                $fecha = null;
                if (!empty($fechaStr) && strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                    $fecha = Carbon::createFromFormat('Ymd', $fechaStr)->format('Y-m-d');
                }

                $fechaPagoStr = trim($r['FECHAPA'] ?? '');
                $fechaPago = null;
                if (!empty($fechaPagoStr) && strlen($fechaPagoStr) === 8 && is_numeric($fechaPagoStr)) {
                    $fechaPago = Carbon::createFromFormat('Ymd', $fechaPagoStr)->format('Y-m-d');
                }

                $pagado = strtoupper(trim($r['PAGADO'] ?? ''));
                $esPagado = ($pagado === 'S' || $pagado === 'T' || $pagado === '1');

                $batch[] = [
                    'tipo_servicio' => $tipoServicio,
                    'periodo' => substr(trim($r['PERIODO'] ?? ''), 0, 20),
                    'codigo_socio' => substr($codigo, 0, 50),
                    'nombre_socio' => substr(trim($r['NOMBRE'] ?? ''), 0, 255),
                    'zona' => substr(trim($r['ZONA'] ?? ''), 0, 50),
                    'estado' => substr(trim($r['ESTADO'] ?? 'ACTIVO'), 0, 20),
                    'fecha' => $fecha,
                    'aporte' => (float) trim($r['APORTE'] ?? 0),
                    'instalacion' => (float) trim($r['INSTAL'] ?? 0),
                    'total' => (float) trim($r['TOTAL'] ?? 0),
                    'abono' => (float) trim($r['ABONO'] ?? 0),
                    'saldo' => (float) trim($r['SALDO'] ?? 0),
                    'plazo' => (int) trim($r['PLAZO'] ?? 0),
                    'pagado' => $esPagado,
                    'fecha_pago' => $fechaPago,
                    'orden' => substr(trim($r['ORDEN'] ?? ''), 0, 50),
                    'factura' => substr(trim($r['FACTURA'] ?? ''), 0, 50),
                    'observaciones' => trim($r['OBSER'] ?? ''),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                if (count($batch) >= 500) {
                    if (!$dryRun) {
                        DB::table('comercial.aportes_conexiones')->insert($batch);
                    }
                    $insertados += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                if (!$dryRun) {
                    DB::table('comercial.aportes_conexiones')->insert($batch);
                }
                $insertados += count($batch);
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
                'tipo_servicio' => $tipoServicio,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra el libro de compras de FoxPro (compras.dbf).
     */
    public function migrarFacturasCompra(string $rutaComprasDbf, bool $dryRun = true, int $limite = 0): array
    {
        $data = $this->leerDbf($rutaComprasDbf, $limite);
        $total = count($data['records']);
        $insertados = 0;
        $batch = [];
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $numFactura = trim((string) ($r['FACTURA'] ?? ''));
                if (empty($numFactura)) {
                    continue;
                }

                $fechaStr = trim((string) ($r['FECHA'] ?? ''));
                $fecha = Carbon::now()->format('Y-m-d');
                if (!empty($fechaStr) && strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                    $fecha = Carbon::createFromFormat('Ymd', $fechaStr)->format('Y-m-d');
                }

                $batch[] = [
                    'especificacion' => substr(trim((string) ($r['ESPECIF'] ?? '1')), 0, 50),
                    'numero_factura' => substr($numFactura, 0, 50),
                    'fecha_factura' => $fecha,
                    'nit_proveedor' => substr(trim((string) ($r['RUC'] ?? '0')), 0, 50),
                    'razon_social_proveedor' => substr(utf8_encode(trim((string) ($r['NOMBRE'] ?? 'SIN PROVEEDOR'))), 0, 255),
                    'codigo_autorizacion' => substr(trim((string) ($r['ALFANUMERI'] ?? ($r['ORDEN'] ?? ''))), 0, 255),
                    'codigo_control' => substr(trim((string) ($r['CCONTROL'] ?? '')), 0, 50),
                    'importe_total' => (float) trim((string) ($r['IMPORTE'] ?? 0)),
                    'importe_ice' => (float) trim((string) ($r['ICE'] ?? 0)),
                    'importe_exento' => (float) trim((string) ($r['EXCENTO'] ?? 0)),
                    'importe_tasa_cero' => (float) trim((string) ($r['IMPCERO'] ?? 0)),
                    'subtotal' => (float) trim((string) ($r['SUBTOTAL'] ?? 0)),
                    'descuentos' => (float) trim((string) ($r['DESCTOS'] ?? 0)),
                    'importe_base_cf' => (float) trim((string) ($r['IMPORBA'] ?? 0)),
                    'credito_fiscal' => (float) trim((string) ($r['DEBITO'] ?? 0)),
                    'tipo_compra' => substr(trim((string) ($r['TIPO'] ?? '1')), 0, 50),
                    'gestion' => (int) trim((string) ($r['YEA'] ?? Carbon::now()->year)),
                    'mes' => (int) trim((string) ($r['MES'] ?? Carbon::now()->month)),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                if (count($batch) >= 500) {
                    if (!$dryRun) {
                        DB::table('contabilidad.facturas_compra')->insert($batch);
                    }
                    $insertados += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                if (!$dryRun) {
                    DB::table('contabilidad.facturas_compra')->insert($batch);
                }
                $insertados += count($batch);
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra catálogo de materiales e insumos de Almacén (almacen.dbf).
     */
    public function migrarMaterialesAlmacen(string $rutaAlmacenDbf, bool $dryRun = true, int $limite = 0): array
    {
        $data = $this->leerDbf($rutaAlmacenDbf, $limite);
        $total = count($data['records']);
        $insertados = 0;
        $batch = [];
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codItem = trim($r['CODITEM'] ?? '');
                if (empty($codItem)) {
                    continue;
                }

                $batch[] = [
                    'codigo_item' => substr($codItem, 0, 50),
                    'nombre' => substr(utf8_encode(trim($r['NOMBRE'] ?? '')), 0, 255),
                    'unidad_medida' => substr(trim($r['UNIDAD'] ?? 'PZA'), 0, 50),
                    'stock_minimo' => (float) trim($r['MINIMO'] ?? 0),
                    'stock_actual' => (float) trim($r['SALDO'] ?? 0),
                    'precio_promedio' => (float) trim($r['PROMEDIO'] ?? 0),
                    'precio_venta' => (float) trim($r['PVENTA'] ?? 0),
                    'moneda' => substr(trim($r['MONEDA'] ?? 'BS'), 0, 10),
                    'grupo' => substr(utf8_encode(trim($r['GRUPO'] ?? '')), 0, 50),
                    'subgrupo' => substr(utf8_encode(trim($r['SUBGRUPO'] ?? '')), 0, 50),
                    'estado' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                if (count($batch) >= 200) {
                    if (!$dryRun) {
                        DB::table('almacen.materiales')->upsert($batch, ['codigo_item'], [
                            'nombre', 'unidad_medida', 'stock_minimo', 'stock_actual',
                            'precio_promedio', 'precio_venta', 'grupo', 'subgrupo', 'updated_at'
                        ]);
                    }
                    $insertados += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                if (!$dryRun) {
                    DB::table('almacen.materiales')->upsert($batch, ['codigo_item'], [
                        'nombre', 'unidad_medida', 'stock_minimo', 'stock_actual',
                        'precio_promedio', 'precio_venta', 'grupo', 'subgrupo', 'updated_at'
                    ]);
                }
                $insertados += count($batch);
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra rubros de Activos Fijos (afrubros.dbf).
     */
    public function migrarRubrosActivos(string $rutaRubrosDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaRubrosDbf);
        $total = count($data['records']);
        $insertados = 0;
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codigo = trim($r['RUBRO'] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                if (!$dryRun) {
                    DB::table('activos_fijos.rubros')->updateOrInsert(
                        ['codigo' => substr($codigo, 0, 20)],
                        [
                            'nombre' => substr(utf8_encode(trim($r['NOMBRE'] ?? '')), 0, 100),
                            'tasa_depreciacion' => (float) trim($r['TASDEP'] ?? 0),
                            'actualiza' => (strtoupper(trim($r['ACTUALIZ'] ?? '')) === 'S'),
                            'updated_at' => $ahora,
                        ]
                    );
                }
                $insertados++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra catálogo de cuentas contables desde cuentas.dbf.
     */
    public function migrarPlanCuentas(string $rutaCuentasDbf, bool $dryRun = true, int $limite = 0): array
    {
        $data = $this->leerDbf($rutaCuentasDbf, $limite);
        $total = count($data['records']);
        $insertados = 0;
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codigo = trim($r['CODIGO'] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                $nombre = substr(utf8_encode(trim($r['NOMBRE'] ?? '')), 0, 255);
                $tipoChar = strtoupper(trim($r['TIPO'] ?? ''));
                $nivel = strlen($codigo) <= 1 ? 1 : (strlen($codigo) <= 2 ? 2 : (strlen($codigo) <= 4 ? 3 : 4));
                $primerDigito = substr($codigo, 0, 1);
                $naturaleza = in_array($primerDigito, ['1', '5', '6']) ? 'DEUDORA' : 'ACREEDORA';
                $tipo = match($primerDigito) {
                    '1' => 'ACTIVO',
                    '2' => 'PASIVO',
                    '3' => 'PATRIMONIO',
                    '4' => 'INGRESO',
                    '5' => 'GASTO',
                    '6' => 'COSTO',
                    default => 'ACTIVO',
                };

                if (!$dryRun) {
                    DB::table('contabilidad.plan_cuentas')->updateOrInsert(
                        ['codigo' => $codigo],
                        [
                            'nombre' => $nombre,
                            'nivel' => $nivel,
                            'naturaleza' => $naturaleza,
                            'tipo' => $tipo,
                            'permite_movimiento' => ($tipoChar === 'D' || strlen($codigo) >= 6),
                            'estado' => 'ACTIVO',
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIG_FOXPRO',
                            '_fecha_modificacion' => $ahora,
                        ]
                    );
                }
                $insertados++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra bienes de Activos Fijos desde afijo.dbf.
     */
    public function migrarBienesActivos(string $rutaAfijoDbf, bool $dryRun = true, int $limite = 0): array
    {
        $data = $this->leerDbf($rutaAfijoDbf, $limite);
        $total = count($data['records']);
        $insertados = 0;
        $batch = [];
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codItem = trim($r['CODITEM'] ?? '');
                if (empty($codItem)) {
                    continue;
                }

                $batch[] = [
                    'codigo_item' => substr($codItem, 0, 50),
                    'nombre' => substr(utf8_encode(trim($r['NOMBRE'] ?? '')), 0, 255),
                    'unidad' => substr(trim($r['UNIDAD'] ?? 'PZA'), 0, 50),
                    'cantidad' => (float) trim($r['CANTIDAD'] ?? 1),
                    'valor_inicial' => (float) trim($r['VALOR'] ?? 0),
                    'valor_actualizado' => (float) trim($r['VALORACT'] ?? 0),
                    'depreciacion_acumulada' => (float) trim($r['DEPACUM'] ?? 0),
                    'valor_residual' => (float) trim($r['VALRES'] ?? 0),
                    'estado' => substr(trim($r['ESTADO'] ?? 'BUENO'), 0, 20),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                if (count($batch) >= 100) {
                    if (!$dryRun) {
                        DB::table('activos_fijos.bienes')->upsert($batch, ['codigo_item'], [
                            'nombre', 'unidad', 'cantidad', 'valor_inicial', 'valor_actualizado',
                            'depreciacion_acumulada', 'valor_residual', 'estado', 'updated_at'
                        ]);
                    }
                    $insertados += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                if (!$dryRun) {
                    DB::table('activos_fijos.bienes')->upsert($batch, ['codigo_item'], [
                        'nombre', 'unidad', 'cantidad', 'valor_inicial', 'valor_actualizado',
                        'depreciacion_acumulada', 'valor_residual', 'estado', 'updated_at'
                    ]);
                }
                $insertados += count($batch);
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra y sincroniza las categorías tarifarias y tarifas escalonadas desde categor.DBF.
     */
    public function migrarTarifas(string $rutaCategorDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaCategorDbf);
        $total = count($data['records']);
        $insertados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $codigo = trim($r['CODIGO'] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                $nombre = trim($r['DESCRIP'] ?? "CATEGORIA {$codigo}");
                $volumenBase = (float) ($r['METROS'] ?? 6.00);
                $tarifaMinima = (float) ($r['MINIMO'] ?? 12.60);
                $tarifaExcedenteBase = (float) ($r['MINMET'] ?? ($r['TARIFA1'] ?? 2.10));
                $tarifaAlcanta = (float) ($r['ALCANTA'] ?? 2.00);

                if (!$dryRun) {
                    $paqueteVigenteId = DB::table('comercial.paquetes_tarifarios')->where('es_vigente', true)->value('id') ?: 1;

                    $catId = DB::table('comercial.categorias_tarifarias')
                        ->where('codigo', $codigo)
                        ->where(function ($q) use ($paqueteVigenteId) {
                            $q->where('id_paquete', $paqueteVigenteId)->orWhereNull('id_paquete');
                        })
                        ->value('id');

                    if ($catId) {
                        DB::table('comercial.categorias_tarifarias')->where('id', $catId)->update([
                            'id_paquete' => $paqueteVigenteId,
                            'nombre' => $nombre,
                            'volumen_base' => $volumenBase,
                            'tarifa_minima' => $tarifaMinima,
                            'tarifa_excedente_base' => $tarifaExcedenteBase,
                            'tarifa_alcantarillado' => $tarifaAlcanta,
                            'activo' => true,
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_fecha_modificacion' => Carbon::now(),
                        ]);
                    } else {
                        $catId = DB::table('comercial.categorias_tarifarias')->insertGetId([
                            'id_paquete' => $paqueteVigenteId,
                            'codigo' => $codigo,
                            'nombre' => $nombre,
                            'volumen_base' => $volumenBase,
                            'tarifa_minima' => $tarifaMinima,
                            'tarifa_excedente_base' => $tarifaExcedenteBase,
                            'tarifa_alcantarillado' => $tarifaAlcanta,
                            'aplica_ley_1886' => ($codigo === 'D'),
                            'activo' => true,
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => Carbon::now(),
                        ]);
                    }

                    // Sincronizar escalones en tarifas_escalonadas
                    DB::table('comercial.tarifas_escalonadas')->where('id_categoria', $catId)->delete();
                    $escalones = [
                        ['desde' => 7, 'hasta' => 10, 'precio' => (float) ($r['TARIFA1'] ?? 2.10)],
                        ['desde' => 11, 'hasta' => 15, 'precio' => (float) ($r['TARIFA2'] ?? 2.10)],
                        ['desde' => 16, 'hasta' => 20, 'precio' => (float) ($r['TARIFA3'] ?? 2.10)],
                        ['desde' => 21, 'hasta' => 25, 'precio' => (float) ($r['TARIFA4'] ?? 2.10)],
                        ['desde' => 26, 'hasta' => 30, 'precio' => (float) ($r['TARIFA5'] ?? 2.10)],
                        ['desde' => 31, 'hasta' => 35, 'precio' => (float) ($r['TARIFA6'] ?? 2.20)],
                        ['desde' => 36, 'hasta' => 40, 'precio' => (float) ($r['TARIFA7'] ?? 2.20)],
                        ['desde' => 41, 'hasta' => 50, 'precio' => (float) ($r['TARIFA8'] ?? 2.30)],
                        ['desde' => 51, 'hasta' => 55, 'precio' => (float) ($r['TARIFA9'] ?? 2.30)],
                        ['desde' => 56, 'hasta' => 60, 'precio' => (float) ($r['TARIFA10'] ?? 2.40)],
                        ['desde' => 61, 'hasta' => null, 'precio' => (float) ($r['TARIFA11'] ?? 2.40)],
                    ];

                    $batchEscalones = [];
                    foreach ($escalones as $esc) {
                        $batchEscalones[] = [
                            'id_categoria' => $catId,
                            'desde_m3' => $esc['desde'],
                            'hasta_m3' => $esc['hasta'],
                            'precio_m3' => $esc['precio'],
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => Carbon::now(),
                        ];
                    }
                    DB::table('comercial.tarifas_escalonadas')->insert($batchEscalones);
                }

                $insertados++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra períodos de facturación y cronogramas de lectura/vencimiento desde periodos.dbf.
     */
    public function migrarPeriodos(string $rutaPeriodosDbf, bool $dryRun = true): array
    {
        $data = $this->leerDbf($rutaPeriodosDbf);
        $total = count($data['records']);
        $insertados = 0;
        $ahora = Carbon::now();

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($data['records'] as $r) {
                $perStr = trim($r['PERIODO'] ?? '');
                if (empty($perStr) || !str_contains($perStr, '/')) {
                    continue;
                }

                $partesPer = explode('/', $perStr);
                $mesPer = max(1, min(12, (int) ($partesPer[0] ?? 1)));
                $gestionPer = max(2000, (int) ($partesPer[1] ?? 2026));

                $fechaD = trim($r['FECHAD'] ?? '');
                $fechaH = trim($r['FECHAH'] ?? '');
                $fechaV = trim($r['FECHAV'] ?? '');

                $inicioConsumo = (strlen($fechaD) === 8 && is_numeric($fechaD))
                    ? substr($fechaD, 0, 4) . '-' . substr($fechaD, 4, 2) . '-' . substr($fechaD, 6, 2)
                    : sprintf('%04d-%02d-01', $gestionPer, $mesPer);

                $finConsumo = (strlen($fechaH) === 8 && is_numeric($fechaH))
                    ? substr($fechaH, 0, 4) . '-' . substr($fechaH, 4, 2) . '-' . substr($fechaH, 6, 2)
                    : date('Y-m-t', strtotime($inicioConsumo));

                $vencimiento = (strlen($fechaV) === 8 && is_numeric($fechaV))
                    ? substr($fechaV, 0, 4) . '-' . substr($fechaV, 4, 2) . '-' . substr($fechaV, 6, 2)
                    : sprintf('%04d-%02d-25', $gestionPer, $mesPer);

                $estadoFox = strtoupper(trim($r['ESTADO'] ?? 'C'));
                $estado = match ($estadoFox) {
                    'C' => 'CERRADO',
                    'F' => 'FACTURADO',
                    'L' => 'EN_LECTURACION',
                    default => 'ABIERTO',
                };

                if (!$dryRun) {
                    DB::table('comercial.periodos_facturacion')->updateOrInsert(
                        ['periodo' => $perStr],
                        [
                            'mes' => $mesPer,
                            'gestion' => $gestionPer,
                            'fecha_inicio_consumo' => $inicioConsumo,
                            'fecha_fin_consumo' => $finConsumo,
                            'fecha_vencimiento_pago' => $vencimiento,
                            'estado' => $estado,
                            'observaciones' => trim($r['OBS'] ?? '') ?: null,
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => $ahora,
                        ]
                    );
                    $insertados++;
                } else {
                    $insertados++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $total,
                'insertados' => $insertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Migra comprobantes de diario y sus líneas de detalle contables desde diariotr.DBF y glosastr.DBF.
     */
    public function migrarComprobantesDiario(
        string $rutaDiarioDbf,
        ?string $rutaGlosasDbf = null,
        bool $dryRun = true,
        int $limite = 0
    ): array {
        $diarioData = $this->leerDbf($rutaDiarioDbf, $limite);
        $totalLineas = count($diarioData['records']);

        // Cargar glosas de cabecera si existe glosastr.DBF
        $glosasMap = [];
        if ($rutaGlosasDbf && file_exists($rutaGlosasDbf)) {
            $glosasData = $this->leerDbf($rutaGlosasDbf);
            foreach ($glosasData['records'] as $g) {
                $k = trim($g['TIPO'] ?? '') . '-' . trim($g['NUMERO'] ?? '') . '-' . trim($g['FECHA'] ?? '');
                $glosasMap[$k] = [
                    'glosa' => trim($g['GLOSA1'] ?? ''),
                    'cliente' => trim($g['CLIENTE'] ?? ''),
                ];
            }
        }

        // Cache de cuentas contables existentes por código
        $cuentasMap = DB::table('contabilidad.plan_cuentas')->pluck('id', 'codigo')->toArray();

        // Agrupar detalles por comprobante: tipo-numero-fecha
        $comprobantesAgrupados = [];
        foreach ($diarioData['records'] as $idx => $r) {
            $tipoNum = trim($r['TIPO'] ?? '3');
            $tipoTexto = match ($tipoNum) {
                '1' => 'INGRESO',
                '2' => 'EGRESO',
                default => 'TRASPASO',
            };
            $numero = trim($r['NUMERO'] ?? '1');
            $fechaRaw = trim($r['FECHA'] ?? '');
            $key = "{$tipoNum}-{$numero}-{$fechaRaw}";

            if (!isset($comprobantesAgrupados[$key])) {
                $comprobantesAgrupados[$key] = [
                    'tipo' => $tipoTexto,
                    'numero' => $numero,
                    'fecha' => $fechaRaw,
                    'lineas' => [],
                ];
            }
            $comprobantesAgrupados[$key]['lineas'][] = $r;
        }

        $comprobantesInsertados = 0;
        $detallesInsertados = 0;

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            $gestionesCache = DB::table('contabilidad.gestiones')->pluck('id', 'gestion')->toArray();

            foreach ($comprobantesAgrupados as $key => $comp) {
                $fechaStr = $comp['fecha'];
                $fecha = (!empty($fechaStr) && strlen($fechaStr) === 8)
                    ? Carbon::createFromFormat('Ymd', $fechaStr)->startOfDay()
                    : Carbon::now()->startOfDay();

                $year = (int) $fecha->format('Y');
                $mes = (int) $fecha->format('n');

                if (!isset($gestionesCache[$year])) {
                    if (!$dryRun) {
                        $gestionesCache[$year] = DB::table('contabilidad.gestiones')->insertGetId([
                            'gestion' => $year,
                            'fecha_inicio' => "{$year}-01-01",
                            'fecha_fin' => "{$year}-12-31",
                            'estado' => 'CERRADA',
                            'observaciones' => "Gestión Fiscal {$year} FoxPro",
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => Carbon::now(),
                        ]);
                    } else {
                        $gestionesCache[$year] = 1;
                    }
                }
                $idGestion = $gestionesCache[$year];

                $glosaCabecera = $glosasMap[$key]['glosa'] ?? "Comprobante {$comp['tipo']} Nro {$comp['numero']}";
                $beneficiario = $glosasMap[$key]['cliente'] ?? null;

                $totalDebe = 0.0;
                $totalHaber = 0.0;
                foreach ($comp['lineas'] as $lin) {
                    $totalDebe += (float) ($lin['DBOL'] ?? 0);
                    $totalHaber += (float) ($lin['HBOL'] ?? 0);
                }
                $diferencia = round($totalDebe - $totalHaber, 2);

                $nroComprobante = sprintf('%s-%04d-%04d', strtoupper(substr($comp['tipo'], 0, 1)), $year, (int) $comp['numero']);

                if (!$dryRun) {
                    $idComp = DB::table('contabilidad.comprobantes')
                        ->where('numero_comprobante', $nroComprobante)
                        ->where('id_gestion', $idGestion)
                        ->value('id');

                    if (!$idComp) {
                        $idComp = DB::table('contabilidad.comprobantes')->insertGetId([
                            'numero_comprobante' => $nroComprobante,
                            'tipo' => $comp['tipo'],
                            'fecha' => $fecha->toDateString(),
                            'id_gestion' => $idGestion,
                            'mes' => $mes,
                            'glosa_principal' => $glosaCabecera ?: "Comprobante {$nroComprobante}",
                            'beneficiario' => $beneficiario,
                            'total_debe' => $totalDebe,
                            'total_haber' => $totalHaber,
                            'diferencia' => $diferencia,
                            'estado' => 'APROBADO',
                            'origen_modulo' => 'MIGRACION_FOXPRO',
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'MIGRACION',
                            '_usuario_creacion' => 1,
                            '_fecha_creacion' => Carbon::now(),
                        ]);
                        $comprobantesInsertados++;
                    }

                    $detallesBatch = [];
                    foreach ($comp['lineas'] as $orden => $lin) {
                        $codCuenta = trim($lin['CODIGO'] ?? '');
                        $idCuenta = $cuentasMap[$codCuenta] ?? null;
                        if (!$idCuenta) {
                            continue;
                        }

                        $detallesBatch[] = [
                            'id_comprobante' => $idComp,
                            'id_cuenta' => $idCuenta,
                            'id_centro_costo' => null,
                            'glosa_linea' => trim($lin['GLOSA'] ?? '') ?: $glosaCabecera,
                            'debe' => (float) ($lin['DBOL'] ?? 0),
                            'haber' => (float) ($lin['HBOL'] ?? 0),
                            'orden' => $orden + 1,
                            '_estado' => 'ACTIVO',
                            '_fecha_creacion' => Carbon::now(),
                        ];
                    }

                    if (!empty($detallesBatch)) {
                        DB::table('contabilidad.comprobante_detalles')->insert($detallesBatch);
                        $detallesInsertados += count($detallesBatch);
                    }
                } else {
                    $comprobantesInsertados++;
                    $detallesInsertados += count($comp['lineas']);
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            return [
                'dry_run' => $dryRun,
                'total_en_dbf' => $totalLineas,
                'comprobantes_cabeceras' => $comprobantesInsertados,
                'insertados' => $detallesInsertados,
            ];
        } catch (Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Reversión segura y transaccional de una migración por Job ID.
     * Ejecuta eliminaciones en orden inverso de dependencias de Foreign Key.
     */
    public function revertirJob(string $jobId): array
    {
        $logs = DB::table('migracion.logs')->where('job_id', $jobId)->get();
        if ($logs->isEmpty()) {
            throw new Exception("No se encontraron registros de migración para el Job ID: {$jobId}");
        }

        $modulos = $logs->pluck('modulo')->unique()->toArray();
        $esSimulacion = (bool) $logs->first()->es_simulacion;

        $eliminados = [];

        if ($esSimulacion) {
            DB::table('migracion.logs')->where('job_id', $jobId)->update([
                'mensaje' => DB::raw("CONCAT(COALESCE(mensaje, ''), ' [REVERTIDO: " . Carbon::now()->format('Y-m-d H:i:s') . "]')"),
                'updated_at' => Carbon::now(),
            ]);

            return [
                'status' => 'success',
                'job_id' => $jobId,
                'es_simulacion' => true,
                'mensaje' => 'Simulación anulada del historial correctamente.',
                'registros_eliminados' => [],
                'total_eliminados' => 0,
            ];
        }

        DB::beginTransaction();
        try {
            // Revertir únicamente los módulos incluidos en este Job ID en orden inverso
            if (in_array('lecturas', $modulos)) {
                $eliminados['comercial.lecturas_mensuales'] = DB::table('comercial.lecturas_mensuales')
                    ->where('_transaccion', 'MIG_OPERACIO')
                    ->delete();
            }

            if (in_array('facturas', $modulos)) {
                $eliminados['facturacion.facturas'] = DB::table('facturacion.facturas')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('recibos', $modulos)) {
                $eliminados['comercial.recibos_caja'] = DB::table('comercial.recibos_caja')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('convenios', $modulos)) {
                $eliminados['comercial.convenio_cuotas'] = DB::table('comercial.convenio_cuotas')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
                $eliminados['comercial.convenios_pago'] = DB::table('comercial.convenios_pago')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('aportes_agua', $modulos) || in_array('aportes_alcantarillado', $modulos)) {
                $query = DB::table('comercial.aportes_conexiones')->where('_transaccion', 'MIGRACION');
                if (in_array('aportes_agua', $modulos) && !in_array('aportes_alcantarillado', $modulos)) {
                    $query->where('tipo_servicio', 'AGUA');
                } elseif (!in_array('aportes_agua', $modulos) && in_array('aportes_alcantarillado', $modulos)) {
                    $query->where('tipo_servicio', 'ALCANTARILLADO');
                }
                $eliminados['comercial.aportes_conexiones'] = $query->delete();
            }

            if (in_array('bajas_socios', $modulos)) {
                $eliminados['comercial.abonados_bajas'] = DB::table('comercial.abonados_bajas')->delete();
            }

            if (in_array('comprobantes', $modulos)) {
                $eliminados['contabilidad.comprobante_detalles'] = DB::table('contabilidad.comprobante_detalles')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('contabilidad.comprobantes')
                            ->whereColumn('comprobantes.id', 'comprobante_detalles.id_comprobante')
                            ->where('comprobantes._transaccion', 'MIGRACION');
                    })
                    ->delete();
                $eliminados['contabilidad.comprobantes'] = DB::table('contabilidad.comprobantes')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('compras', $modulos)) {
                $eliminados['contabilidad.facturas_compra'] = DB::table('contabilidad.facturas_compra')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('materiales_almacen', $modulos)) {
                $eliminados['almacen.materiales'] = DB::table('almacen.materiales')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('bienes_activos', $modulos)) {
                $eliminados['activos_fijos.bienes'] = DB::table('activos_fijos.bienes')->delete();
            }

            if (in_array('calles', $modulos)) {
                $eliminados['comercial.calles'] = DB::table('comercial.calles')
                    ->where('_transaccion', 'MIGRACION')
                    ->delete();
            }

            if (in_array('estados_abonado', $modulos)) {
                $eliminados['parametricas.estados_abonado'] = DB::table('parametricas')
                    ->where('param_tabla', 'TABLA_COMERCIAL_ESTADOS_ABONADO')
                    ->delete();
            }

            if (in_array('conceptos_ingresos', $modulos)) {
                $eliminados['parametricas.conceptos_ingresos'] = DB::table('parametricas')
                    ->where('param_tabla', 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS')
                    ->delete();
            }

            // Marcar el log como revertido
            DB::table('migracion.logs')->where('job_id', $jobId)->update([
                'mensaje' => DB::raw("CONCAT(COALESCE(mensaje, ''), ' [REVERTIDO: " . Carbon::now()->format('Y-m-d H:i:s') . "]')"),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            return [
                'status' => 'success',
                'job_id' => $jobId,
                'es_simulacion' => false,
                'mensaje' => 'Reversión transaccional completada con éxito.',
                'registros_eliminados' => $eliminados,
                'total_eliminados' => array_sum($eliminados),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

