<?php

declare(strict_types=1);

namespace App\Services\Rrhh;

use App\Models\Rrhh\AsignacionPuesto;
use App\Models\Rrhh\DatoLaboral;
use App\Models\Rrhh\EscalaSalarial;
use App\Models\Rrhh\FichaPersonal;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PlanillaExcelImportService
{
    /**
     * Previsualiza los datos de la planilla Excel sin alterar la base de datos.
     */
    public function previsualizar(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("El archivo no existe en la ruta: {$filePath}");
        }

        try {
            $reader = IOFactory::createReader('Xlsx');
        } catch (\Throwable) {
            $reader = IOFactory::createReaderForFile($filePath);
        }
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $planta = $this->extraerPlanta($spreadsheet);
        $eventual = $this->extraerEventual($spreadsheet);
        $directorio = $this->extraerDirectorio($spreadsheet);

        return [
            'success' => true,
            'hojas_detectadas' => $sheetNames,
            'archivo' => basename($filePath),
            'totales' => [
                'planta_count' => count($planta),
                'eventual_count' => count($eventual),
                'directorio_count' => count($directorio),
                'total_general_personas' => count($planta) + count($eventual) + count($directorio),
                'total_ganado_planta' => array_sum(array_column($planta, 'total_ganado')),
                'total_ganado_eventual' => array_sum(array_column($eventual, 'total_ganado')),
                'total_dietas_directorio' => array_sum(array_column($directorio, 'total_dietas')),
            ],
            'datos' => [
                'planta' => $planta,
                'eventual' => $eventual,
                'directorio' => $directorio,
            ],
        ];
    }

    /**
     * Importa y sincroniza la estructura completa de personal, cargos y escalas salariales.
     * Garantiza cero pérdida de datos mediante updateOrCreate.
     */
    public function importar(string $filePath, int $idUsuario = 1): array
    {
        $preview = $this->previsualizar($filePath);
        $planta = $preview['datos']['planta'];
        $eventual = $preview['datos']['eventual'];
        $directorio = $preview['datos']['directorio'];

        return DB::transaction(function () use ($planta, $eventual, $directorio, $idUsuario, $filePath) {
            // 1. Garantizar catálogos base en rrhh (gestión, regional, niveles, unidades)
            $idGestion = $this->asegurarGestion($idUsuario);
            $idRegional = $this->asegurarRegional($idGestion, $idUsuario);
            $niveles = $this->asegurarNiveles($idGestion, $idUsuario);
            $unidades = $this->asegurarUnidades($idGestion, $idRegional, $niveles, $idUsuario);

            $creados = 0;
            $actualizados = 0;
            $puestosAsignados = 0;

            // 2. Procesar Personal de Planta Permanente (Ítems P-01 a P-10)
            foreach ($planta as $p) {
                $res = $this->procesarFuncionario($p, 'PLANTA', $unidades, $niveles, $idGestion, $idUsuario);
                if ($res['nuevo']) {
                    $creados++;
                } else {
                    $actualizados++;
                }
                if ($res['asignado']) {
                    $puestosAsignados++;
                }
            }

            // 3. Procesar Personal Eventual (Ítem E-01)
            foreach ($eventual as $e) {
                $res = $this->procesarFuncionario($e, 'EVENTUAL', $unidades, $niveles, $idGestion, $idUsuario);
                if ($res['nuevo']) {
                    $creados++;
                } else {
                    $actualizados++;
                }
                if ($res['asignado']) {
                    $puestosAsignados++;
                }
            }

            // 4. Procesar Directorio (Ítems D-01 a D-05)
            foreach ($directorio as $d) {
                if (empty($d['ci']) && in_array($d['cargo'], ['Presidente', 'V. Presidente'])) {
                    // Cargos ex-officio que no cobran dieta o sin CI en el libro
                    continue;
                }
                $res = $this->procesarFuncionario($d, 'DIRECTORIO', $unidades, $niveles, $idGestion, $idUsuario);
                if ($res['nuevo']) {
                    $creados++;
                } else {
                    $actualizados++;
                }
                if ($res['asignado']) {
                    $puestosAsignados++;
                }
            }

            // Registrar log de auditoría
            Log::info("Migración desde Excel Planilla completada con éxito. Archivo: {$filePath}. Creados: {$creados}, Actualizados: {$actualizados}.");

            return [
                'success' => true,
                'message' => "Sincronización completada exitosamente. Se procesaron {$creados} nuevos funcionarios y {$actualizados} actualizados sin pérdida de datos.",
                'estadisticas' => [
                    'planta_procesados' => count($planta),
                    'eventual_procesados' => count($eventual),
                    'directorio_procesados' => count($directorio),
                    'total_procesados' => $creados + $actualizados,
                    'nuevos_creados' => $creados,
                    'actualizados' => $actualizados,
                    'puestos_asignados' => $puestosAsignados,
                ],
            ];
        });
    }

    /**
     * Extrae funcionarios de la hoja 'PLANILLA PERSONAL'.
     */
    private function extraerPlanta($spreadsheet): array
    {
        $sheet = $spreadsheet->getSheetByName('PLANILLA PERSONAL');
        if (!$sheet) {
            return [];
        }

        // Mapeo conocido de ítems y áreas provenientes de las boletas oficiales
        $itemsMap = [
            '4041212 QR' => ['item' => 'P-01', 'unidad' => 'GERENCIA GENERAL'],
            '6814327 LP' => ['item' => 'P-02', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
            '8408015 Qr.' => ['item' => 'P-03', 'unidad' => 'UNIDAD ADMINISTRATIVA Y FINANCIERA'],
            '8408015 QR' => ['item' => 'P-03', 'unidad' => 'UNIDAD ADMINISTRATIVA Y FINANCIERA'],
            '11107922 QR' => ['item' => 'P-04', 'unidad' => 'UNIDAD COMERCIAL'],
            '6993628 LP' => ['item' => 'P-05', 'unidad' => 'UNIDAD ADMINISTRATIVA Y FINANCIERA'],
            '6109918 LP' => ['item' => 'P-06', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
            '9888959 LP' => ['item' => 'P-07', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
            '9096790 QR' => ['item' => 'P-08', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
            '9005700 QR' => ['item' => 'P-09', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
            '5978934 LP' => ['item' => 'P-10', 'unidad' => 'UNIDAD TÉCNICA Y OPERACIONES'],
        ];

        $resultado = [];
        $maxRow = $sheet->getHighestRow();

        for ($row = 13; $row <= min(35, $maxRow); $row++) {
            $num = $sheet->getCell("B{$row}")->getValue();
            $nombreRaw = trim((string) $sheet->getCell("C{$row}")->getValue());
            $ciRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
            $cargoRaw = trim((string) $sheet->getCell("E{$row}")->getValue());

            if (empty($nombreRaw) || empty($ciRaw) || str_contains(strtoupper($nombreRaw), 'TOTAL')) {
                continue;
            }

            $fechaIngresoVal = $sheet->getCell("F{$row}")->getValue();
            $fechaIngreso = $this->parseExcelDate($fechaIngresoVal) ?? '2026-01-01';

            $haberBasico = (float) ($sheet->getCell("G{$row}")->getValue() ?? 0.0);
            $diasTrabajados = (int) ($sheet->getCell("H{$row}")->getValue() ?? 30);
            $haberPorDias = (float) ($sheet->getCell("I{$row}")->getCalculatedValue() ?? $haberBasico);
            $totalGanado = (float) ($sheet->getCell("R{$row}")->getCalculatedValue() ?? $haberBasico);
            $gestora1271 = (float) ($sheet->getCell("W{$row}")->getCalculatedValue() ?? round($totalGanado * 0.1271, 2));
            $liquidoPagable = (float) ($sheet->getCell("Z{$row}")->getCalculatedValue() ?? round($totalGanado - $gestora1271, 2));

            $ciNormalizado = $this->limpiarCi($ciRaw);
            $nombresSeparados = $this->separarApellidosNombresPlanta($nombreRaw);

            $metaItem = $itemsMap[$ciRaw] ?? $itemsMap[$ciNormalizado] ?? [
                'item' => 'P-' . str_pad((string) (count($resultado) + 1), 2, '0', STR_PAD_LEFT),
                'unidad' => str_contains(strtoupper($cargoRaw), 'PLOMERO') ? 'UNIDAD TÉCNICA Y OPERACIONES' : 'UNIDAD ADMINISTRATIVA Y FINANCIERA',
            ];

            $resultado[] = [
                'numero' => $num,
                'nombre_completo' => $nombreRaw,
                'nombres' => $nombresSeparados['nombres'],
                'primer_apellido' => $nombresSeparados['primer_apellido'],
                'segundo_apellido' => $nombresSeparados['segundo_apellido'],
                'ci_original' => $ciRaw,
                'ci' => $ciNormalizado,
                'cargo' => $cargoRaw,
                'item' => $metaItem['item'],
                'unidad_nombre' => $metaItem['unidad'],
                'fecha_ingreso' => $fechaIngreso,
                'haber_basico' => $haberBasico,
                'dias_trabajados' => $diasTrabajados,
                'haber_por_dias' => $haberPorDias,
                'total_ganado' => $totalGanado,
                'gestora_12_71' => $gestora1271,
                'liquido_pagable' => $liquidoPagable,
            ];
        }

        return $resultado;
    }

    /**
     * Extrae personal eventual de la hoja 'PLANILLA EVENTUAL'.
     */
    private function extraerEventual($spreadsheet): array
    {
        $sheet = $spreadsheet->getSheetByName('PLANILLA EVENTUAL');
        if (!$sheet) {
            return [];
        }

        $resultado = [];
        $maxRow = $sheet->getHighestRow();

        for ($row = 12; $row <= min(25, $maxRow); $row++) {
            $num = $sheet->getCell("B{$row}")->getValue();
            $nombreRaw = trim((string) $sheet->getCell("C{$row}")->getValue());
            $ciRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
            $cargoRaw = trim((string) $sheet->getCell("E{$row}")->getValue());

            if (empty($nombreRaw) || empty($ciRaw) || str_contains(strtoupper($nombreRaw), 'TOTAL')) {
                continue;
            }

            $fechaIngresoVal = $sheet->getCell("F{$row}")->getValue();
            $fechaIngreso = $this->parseExcelDate($fechaIngresoVal) ?? '2026-01-01';

            $haberBasico = (float) ($sheet->getCell("G{$row}")->getValue() ?? 3300.0);
            $diasTrabajados = (int) ($sheet->getCell("H{$row}")->getValue() ?? 30);
            $totalGanado = (float) ($sheet->getCell("O{$row}")->getCalculatedValue() ?? $haberBasico);
            $liquidoPagable = (float) ($sheet->getCell("W{$row}")->getCalculatedValue() ?? $totalGanado);

            $ciNormalizado = $this->limpiarCi($ciRaw);
            $nombresSeparados = $this->separarApellidosNombresEventual($nombreRaw);

            $resultado[] = [
                'numero' => $num,
                'nombre_completo' => $nombreRaw,
                'nombres' => $nombresSeparados['nombres'],
                'primer_apellido' => $nombresSeparados['primer_apellido'],
                'segundo_apellido' => $nombresSeparados['segundo_apellido'],
                'ci_original' => $ciRaw,
                'ci' => $ciNormalizado,
                'cargo' => $cargoRaw,
                'item' => 'E-01',
                'unidad_nombre' => 'UNIDAD TÉCNICA Y OPERACIONES',
                'fecha_ingreso' => $fechaIngreso,
                'haber_basico' => $haberBasico,
                'dias_trabajados' => $diasTrabajados,
                'total_ganado' => $totalGanado,
                'gestora_12_71' => 0.0,
                'liquido_pagable' => $liquidoPagable,
            ];
        }

        return $resultado;
    }

    /**
     * Extrae directores de la hoja 'PLANILLA DIETAS DIRECTORIO'.
     */
    private function extraerDirectorio($spreadsheet): array
    {
        $sheet = $spreadsheet->getSheetByName('PLANILLA DIETAS DIRECTORIO');
        if (!$sheet) {
            return [];
        }

        $resultado = [];
        $itemsDirectorios = [
            '6019157' => 'D-01',
            '2731500 OR' => 'D-02',
            '2269353' => 'D-03',
        ];

        for ($row = 59; $row <= 63; $row++) {
            $nombreRaw = trim((string) $sheet->getCell("B{$row}")->getValue());
            $ciRaw = trim((string) $sheet->getCell("C{$row}")->getValue());
            $cargoRaw = trim((string) $sheet->getCell("D{$row}")->getValue());

            if (empty($nombreRaw)) {
                continue;
            }

            $dietaBase = (float) ($sheet->getCell("E{$row}")->getCalculatedValue() ?? 0.0);
            $sesiones = (int) ($sheet->getCell("F{$row}")->getValue() ?? 0);
            $totalDietas = (float) ($sheet->getCell("I{$row}")->getCalculatedValue() ?? 0.0);
            $liquidoPagable = (float) ($sheet->getCell("Q{$row}")->getCalculatedValue() ?? $totalDietas);

            $ciNormalizado = $this->limpiarCi($ciRaw);
            $nombresSeparados = $this->separarNombresDirectorio($nombreRaw);

            $item = $itemsDirectorios[$ciRaw] ?? $itemsDirectorios[$ciNormalizado] ?? ('D-' . str_pad((string) (count($resultado) + 1), 2, '0', STR_PAD_LEFT));

            $resultado[] = [
                'nombre_completo' => $nombreRaw,
                'nombres' => $nombresSeparados['nombres'],
                'primer_apellido' => $nombresSeparados['primer_apellido'],
                'segundo_apellido' => $nombresSeparados['segundo_apellido'],
                'ci_original' => $ciRaw,
                'ci' => $ciNormalizado,
                'cargo' => $cargoRaw ?: 'Miembro de Directorio',
                'item' => $item,
                'unidad_nombre' => 'DIRECTORIO INSTITUCIONAL',
                'fecha_ingreso' => '2026-01-01',
                'haber_basico' => $dietaBase,
                'sesiones_asistidas' => $sesiones,
                'total_dietas' => $totalDietas,
                'total_ganado' => $totalDietas,
                'liquido_pagable' => $liquidoPagable,
            ];
        }

        return $resultado;
    }

    /**
     * Procesa, crea o actualiza un funcionario con su escala, puesto y asignación.
     */
    private function procesarFuncionario(array $data, string $tipo, array $unidades, array $niveles, int $idGestion, int $idUsuario): array
    {
        $ci = $data['ci'];
        if (empty($ci)) {
            return ['nuevo' => false, 'asignado' => false];
        }

        $personaExistente = Persona::where('nro_documento', $ci)->first();
        $esNuevo = false;

        if (!$personaExistente) {
            $persona = Persona::create([
                'nombres' => strtoupper($data['nombres']),
                'primer_apellido' => $data['primer_apellido'] ? strtoupper($data['primer_apellido']) : null,
                'segundo_apellido' => $data['segundo_apellido'] ? strtoupper($data['segundo_apellido']) : null,
                'tipo_documento' => 'CI',
                'nro_documento' => $ci,
                'genero' => 'MASCULINO',
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]);
            $esNuevo = true;
        } else {
            $persona = $personaExistente;
            $persona->update([
                'nombres' => strtoupper($data['nombres']),
                'primer_apellido' => $data['primer_apellido'] ? strtoupper($data['primer_apellido']) : null,
                'segundo_apellido' => $data['segundo_apellido'] ? strtoupper($data['segundo_apellido']) : null,
                '_usuario_modificacion' => $idUsuario,
                '_fecha_modificacion' => now(),
            ]);
        }

        // Ficha Personal
        $ficha = FichaPersonal::firstOrCreate(
            ['id_persona' => $persona->id],
            ['_usuario_creacion' => $idUsuario, '_fecha_creacion' => now()]
        );

        // Escala Salarial Institucional
        $haberBasico = (float) $data['haber_basico'];
        $escala = $this->determinarEscalaSalarial($haberBasico, (string) ($data['cargo'] ?? ''), $niveles, $idGestion, $idUsuario);

        // Unidad Organizacional
        $nombreUnidad = $data['unidad_nombre'] ?? 'UNIDAD ADMINISTRATIVA Y FINANCIERA';
        $idUnidad = $unidades[$nombreUnidad] ?? $unidades['UNIDAD ADMINISTRATIVA Y FINANCIERA'];

        // Puesto
        $nombreCargo = trim($data['cargo']);
        $puesto = Puesto::firstOrCreate(
            [
                'nombre' => $nombreCargo,
                'id_unidad_organizacional' => $idUnidad,
            ],
            [
                'tipo_puesto' => $tipo,
                'id_escala_salarial' => $escala->id,
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]
        );

        if ($puesto->id_escala_salarial !== $escala->id) {
            $puesto->update(['id_escala_salarial' => $escala->id]);
        }

        // Asignación de Puesto (Activa)
        $itemCodigo = $data['item'] ?? '-';
        $nroItemInt = (int) (preg_replace('/\D/', '', (string) $itemCodigo) ?: 1);

        $asignacion = AsignacionPuesto::where('id_persona', $persona->id)
            ->where('_estado', 'ACTIVO')
            ->whereNull('fecha_fin')
            ->first();

        if (!$asignacion) {
            AsignacionPuesto::create([
                'id_persona' => $persona->id,
                'id_puesto' => $puesto->id,
                'nro_item' => $nroItemInt,
                'asignacion' => $itemCodigo,
                'tipo_asignacion' => $tipo,
                'fecha_inicio' => $data['fecha_ingreso'] ?? '2026-01-01',
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]);
        } else {
            $asignacion->update([
                'id_puesto' => $puesto->id,
                'nro_item' => $nroItemInt,
                'asignacion' => $itemCodigo,
                'tipo_asignacion' => $tipo,
                '_usuario_modificacion' => $idUsuario,
                '_fecha_modificacion' => now(),
            ]);
        }

        // Registrar Dato Laboral en Ficha Personal
        DatoLaboral::updateOrCreate(
            [
                'id_ficha_personal' => $ficha->id,
                'cargo' => $nombreCargo,
            ],
            [
                'unidad_organizacional' => $nombreUnidad,
                'tipo_funcionario' => $tipo,
                'nro_item' => $nroItemInt,
                'nro_contrato' => $itemCodigo,
                'fecha_ingreso' => $data['fecha_ingreso'] ?? '2026-01-01',
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]
        );

        return ['nuevo' => $esNuevo, 'asignado' => true];
    }

    /**
     * Asegura la existencia de la gestión 2026.
     */
    private function asegurarGestion(int $idUsuario): int
    {
        $gestion = DB::table('rrhh.gestiones')->where('anio', 2026)->first();
        if ($gestion) {
            return (int) $gestion->id;
        }

        return (int) DB::table('rrhh.gestiones')->insertGetId([
            'nombre' => 'Gestión 2026',
            'anio' => 2026,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => $idUsuario,
            '_fecha_creacion' => now(),
        ]);
    }

    /**
     * Asegura la existencia de la regional EMAPA Patacamaya.
     */
    private function asegurarRegional(int $idGestion, int $idUsuario): int
    {
        $reg = DB::table('rrhh.regionales')->where('sigla', 'PTC')->first();
        if ($reg) {
            return (int) $reg->id;
        }

        return (int) DB::table('rrhh.regionales')->insertGetId([
            'nombre' => 'OFICINA CENTRAL PATACAMAYA',
            'sigla' => 'PTC',
            'id_gestion' => $idGestion,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => $idUsuario,
            '_fecha_creacion' => now(),
        ]);
    }

    /**
     * Resuelve o crea la escala salarial institucional oficial con nivel jerárquico y denominación real.
     */
    private function determinarEscalaSalarial(float $haberBasico, string $cargo, array $niveles, int $idGestion, int $idUsuario): EscalaSalarial
    {
        [$nombreEscala, $idNivel, $codigo] = match (true) {
            $haberBasico >= 6500.0 => ['Nivel 1: Gerencia General', $niveles['EJECUTIVO'] ?? null, 'NIV-01'],
            $haberBasico >= 4000.0 => ['Nivel 2: Jefatura de Unidad / Profesional', $niveles['JEFATURA'] ?? null, 'NIV-02'],
            $haberBasico >= 3600.0 => ['Nivel 3: Administrativo / Técnico de Apoyo', $niveles['ADMINISTRATIVO'] ?? null, 'NIV-03'],
            $haberBasico >= 3400.0 => ['Nivel 4: Técnico I / Operativo Especializado', $niveles['OPERATIVO'] ?? null, 'NIV-04'],
            $haberBasico >= 3000.0 => ['Nivel 5: Técnico II / Auxiliar Operativo', $niveles['OPERATIVO'] ?? null, 'NIV-05'],
            $haberBasico <= 1000.0 => ['Dietas Directorio: Miembro de Directorio', $niveles['EJECUTIVO'] ?? null, 'DIR-01'],
            default => ["Escala Tabulador Bs. " . number_format($haberBasico, 2), $niveles['OPERATIVO'] ?? null, null],
        };

        $escala = EscalaSalarial::where('salario', $haberBasico)
            ->where('id_gestion', $idGestion)
            ->first();

        if (!$escala) {
            $escala = EscalaSalarial::create([
                'nombre' => $nombreEscala,
                'salario' => $haberBasico,
                'id_nivel' => $idNivel,
                'codigo' => $codigo,
                'id_gestion' => $idGestion,
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]);
        } else {
            // Actualizar si tenía el nombre anterior genérico o si no tenía nivel asociado
            if (str_starts_with($escala->nombre, 'Escala Oficial Bs.') || empty($escala->id_nivel)) {
                $escala->update([
                    'nombre' => $nombreEscala,
                    'id_nivel' => $idNivel,
                    'codigo' => $codigo,
                    '_usuario_modificacion' => $idUsuario,
                    '_fecha_modificacion' => now(),
                ]);
            }
        }

        return $escala;
    }

    /**
     * Asegura los niveles jerárquicos organizacionales.
     */
    private function asegurarNiveles(int $idGestion, int $idUsuario): array
    {
        $nivelesDefs = [
            'EJECUTIVO' => ['nombre' => 'NIVEL DIRECTIVO / EJECUTIVO', 'nivel' => 1],
            'JEFATURA' => ['nombre' => 'NIVEL JEFATURA', 'nivel' => 2],
            'ADMINISTRATIVO' => ['nombre' => 'NIVEL ADMINISTRATIVO', 'nivel' => 3],
            'OPERATIVO' => ['nombre' => 'NIVEL OPERATIVO', 'nivel' => 4],
        ];

        $res = [];
        foreach ($nivelesDefs as $key => $n) {
            $exist = DB::table('rrhh.niveles')->where('nombre', $n['nombre'])->first();
            if ($exist) {
                $res[$key] = (int) $exist->id;
            } else {
                $id = DB::table('rrhh.niveles')->insertGetId([
                    'nombre' => $n['nombre'],
                    'nivel' => $n['nivel'],
                    'id_gestion' => $idGestion,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => $idUsuario,
                    '_fecha_creacion' => now(),
                ]);
                $res[$key] = (int) $id;
            }
        }

        return $res;
    }

    /**
     * Asegura las unidades organizacionales oficiales de EMAPA Patacamaya.
     */
    private function asegurarUnidades(int $idGestion, int $idRegional, array $niveles, int $idUsuario): array
    {
        $unidadesDefs = [
            'GERENCIA GENERAL' => ['sigla' => 'GG', 'nivel' => $niveles['EJECUTIVO'], 'padre' => null],
            'UNIDAD ADMINISTRATIVA Y FINANCIERA' => ['sigla' => 'UAF', 'nivel' => $niveles['JEFATURA'], 'padre' => 'GERENCIA GENERAL'],
            'UNIDAD COMERCIAL' => ['sigla' => 'UCOM', 'nivel' => $niveles['JEFATURA'], 'padre' => 'GERENCIA GENERAL'],
            'UNIDAD TÉCNICA Y OPERACIONES' => ['sigla' => 'UTO', 'nivel' => $niveles['JEFATURA'], 'padre' => 'GERENCIA GENERAL'],
            'DIRECTORIO INSTITUCIONAL' => ['sigla' => 'DIR', 'nivel' => $niveles['EJECUTIVO'], 'padre' => null],
        ];

        $res = [];
        // Primero crear las unidades raíz
        foreach ($unidadesDefs as $nombre => $cfg) {
            $u = UnidadOrganizacional::where('nombre', $nombre)->first();
            if (!$u) {
                $u = UnidadOrganizacional::create([
                    'nombre' => $nombre,
                    'sigla' => $cfg['sigla'],
                    'id_regional' => $idRegional,
                    'id_gestion' => $idGestion,
                    'id_nivel' => $cfg['nivel'],
                    'es_unidad_recursos_humanos' => $nombre === 'UNIDAD ADMINISTRATIVA Y FINANCIERA',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => $idUsuario,
                    '_fecha_creacion' => now(),
                ]);
            }
            $res[$nombre] = (int) $u->id;
        }

        // Vincular padres
        foreach ($unidadesDefs as $nombre => $cfg) {
            if ($cfg['padre'] && isset($res[$cfg['padre']])) {
                UnidadOrganizacional::where('id', $res[$nombre])
                    ->update([
                        'id_organismo_padre' => $res[$cfg['padre']],
                        'padreId' => $res[$cfg['padre']],
                    ]);
            }
        }

        return $res;
    }

    /**
     * Limpia el C.I. quitando espacios repetidos pero preservando su valor canónico.
     */
    private function limpiarCi(string $ciRaw): string
    {
        $ci = preg_replace('/\s+/', ' ', trim($ciRaw));
        return trim((string) $ci);
    }

    /**
     * Separa "Apellido1 Apellido2 Nombre1 Nombre2" de la hoja PLANILLA PERSONAL.
     */
    private function separarApellidosNombresPlanta(string $nombreCompleto): array
    {
        $partes = array_values(array_filter(explode(' ', trim($nombreCompleto))));
        $total = count($partes);

        if ($total === 2) {
            return [
                'primer_apellido' => $partes[0],
                'segundo_apellido' => null,
                'nombres' => $partes[1],
            ];
        }

        if ($total === 3) {
            return [
                'primer_apellido' => $partes[0],
                'segundo_apellido' => $partes[1],
                'nombres' => $partes[2],
            ];
        }

        // 4 o más palabras: "Ramirez Capia David Fernando", "Huacara Ayala Ivan Benigno"
        return [
            'primer_apellido' => $partes[0],
            'segundo_apellido' => $partes[1],
            'nombres' => implode(' ', array_slice($partes, 2)),
        ];
    }

    /**
     * Separa "Honorio Cruz Alberto" de la hoja PLANILLA EVENTUAL.
     */
    private function separarApellidosNombresEventual(string $nombreCompleto): array
    {
        return $this->separarApellidosNombresPlanta($nombreCompleto);
    }

    /**
     * Separa "LILIANA ROJAS APAZA" (Nombre Apellido1 Apellido2) de DIRECTORIO.
     */
    private function separarNombresDirectorio(string $nombreCompleto): array
    {
        $partes = array_values(array_filter(explode(' ', trim($nombreCompleto))));
        $total = count($partes);

        if ($total <= 1) {
            return [
                'primer_apellido' => null,
                'segundo_apellido' => null,
                'nombres' => $nombreCompleto,
            ];
        }

        if ($total === 2) {
            return [
                'nombres' => $partes[0],
                'primer_apellido' => $partes[1],
                'segundo_apellido' => null,
            ];
        }

        // Caso "LILIANA ROJAS APAZA" -> Nombres: LILIANA, Primer Apellido: ROJAS, Segundo Apellido: APAZA
        return [
            'nombres' => $partes[0],
            'primer_apellido' => $partes[1],
            'segundo_apellido' => implode(' ', array_slice($partes, 2)),
        ];
    }

    /**
     * Convierte número serial de Excel a 'YYYY-MM-DD'.
     */
    private function parseExcelDate($excelValue): ?string
    {
        if (empty($excelValue)) {
            return null;
        }

        if (is_numeric($excelValue)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $excelValue);
                return $dt->format('Y-m-d');
            } catch (Exception) {
                return null;
            }
        }

        $timestamp = strtotime((string) $excelValue);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }
}
