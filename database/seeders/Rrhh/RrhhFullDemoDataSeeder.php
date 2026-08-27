<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use App\Models\Rrhh\Persona;
use App\Models\Rrhh\FichaPersonal;
use App\Models\Rrhh\DatoLaboral;
use App\Models\Rrhh\EstudioAcademico;
use App\Models\Rrhh\Cas;
use App\Models\Rrhh\Horario;
use App\Models\Rrhh\Periodo;
use App\Models\Rrhh\Marcacion;
use App\Models\Rrhh\Asistencia;
use App\Models\Rrhh\SolicitudSalida;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RrhhFullDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Iniciando población completa del módulo de Recursos Humanos...');

        // 1. GESTIONES ANUALES
        $gestiones = [
            ['id' => 1, 'nombre' => 'Gestión 2024', 'anio' => 2024, 'fecha_inicio' => '2024-01-01', 'fecha_fin' => '2024-12-31'],
            ['id' => 2, 'nombre' => 'Gestión 2025', 'anio' => 2025, 'fecha_inicio' => '2025-01-01', 'fecha_fin' => '2025-12-31'],
            ['id' => 3, 'nombre' => 'Gestión 2026', 'anio' => 2026, 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31'],
        ];
        foreach ($gestiones as $g) {
            DB::table('rrhh.gestiones')->updateOrInsert(
                ['id' => $g['id']],
                [
                    'nombre' => $g['nombre'],
                    'anio' => $g['anio'],
                    'fecha_inicio' => $g['fecha_inicio'],
                    'fecha_fin' => $g['fecha_fin'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.gestiones', 'id'), coalesce(max(id), 1)) FROM rrhh.gestiones");

        // 2. REGIONALES
        $regionales = [
            ['id' => 1, 'nombre' => 'OFICINA CENTRAL LA PAZ', 'sigla' => 'LPZ', 'id_gestion' => 3],
            ['id' => 2, 'nombre' => 'REGIONAL SANTA CRUZ', 'sigla' => 'SCZ', 'id_gestion' => 3],
            ['id' => 3, 'nombre' => 'REGIONAL COCHABAMBA', 'sigla' => 'CBB', 'id_gestion' => 3],
            ['id' => 4, 'nombre' => 'REGIONAL TARIJA', 'sigla' => 'TJA', 'id_gestion' => 3],
            ['id' => 5, 'nombre' => 'REGIONAL ORURO', 'sigla' => 'ORU', 'id_gestion' => 3],
        ];
        foreach ($regionales as $r) {
            DB::table('rrhh.regionales')->updateOrInsert(
                ['id' => $r['id']],
                [
                    'nombre' => $r['nombre'],
                    'sigla' => $r['sigla'],
                    'id_gestion' => $r['id_gestion'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.regionales', 'id'), coalesce(max(id), 1)) FROM rrhh.regionales");

        // 3. NIVELES JERÁRQUICOS
        $niveles = [
            ['id' => 1, 'nombre' => 'NIVEL DIRECTIVO', 'nivel' => 1, 'id_gestion' => 3],
            ['id' => 2, 'nombre' => 'NIVEL EJECUTIVO', 'nivel' => 2, 'id_gestion' => 3],
            ['id' => 3, 'nombre' => 'NIVEL JEFATURA', 'nivel' => 3, 'id_gestion' => 3],
            ['id' => 4, 'nombre' => 'NIVEL PROFESIONAL', 'nivel' => 4, 'id_gestion' => 3],
            ['id' => 5, 'nombre' => 'NIVEL TÉCNICO', 'nivel' => 5, 'id_gestion' => 3],
            ['id' => 6, 'nombre' => 'NIVEL ADMINISTRATIVO', 'nivel' => 6, 'id_gestion' => 3],
            ['id' => 7, 'nombre' => 'NIVEL OPERATIVO', 'nivel' => 7, 'id_gestion' => 3],
        ];
        foreach ($niveles as $n) {
            DB::table('rrhh.niveles')->updateOrInsert(
                ['id' => $n['id']],
                [
                    'nombre' => $n['nombre'],
                    'nivel' => $n['nivel'],
                    'id_gestion' => $n['id_gestion'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.niveles', 'id'), coalesce(max(id), 1)) FROM rrhh.niveles");

        // 4. ESCALAS SALARIALES
        $escalas = [
            ['id' => 1, 'nombre' => 'Gerente General', 'salario' => 18500.00, 'id_gestion' => 3],
            ['id' => 2, 'nombre' => 'Gerente de Área', 'salario' => 14200.00, 'id_gestion' => 3],
            ['id' => 3, 'nombre' => 'Jefe de Unidad', 'salario' => 11500.00, 'id_gestion' => 3],
            ['id' => 4, 'nombre' => 'Profesional Especialista I', 'salario' => 9800.00, 'id_gestion' => 3],
            ['id' => 5, 'nombre' => 'Profesional II / Analista', 'salario' => 7600.00, 'id_gestion' => 3],
            ['id' => 6, 'nombre' => 'Técnico Operativo I', 'salario' => 5400.00, 'id_gestion' => 3],
            ['id' => 7, 'nombre' => 'Administrativo / Auxiliar', 'salario' => 4200.00, 'id_gestion' => 3],
            ['id' => 8, 'nombre' => 'Operador de Planta / Silos', 'salario' => 3800.00, 'id_gestion' => 3],
        ];
        foreach ($escalas as $e) {
            DB::table('rrhh.escalas_salariales')->updateOrInsert(
                ['id' => $e['id']],
                [
                    'nombre' => $e['nombre'],
                    'salario' => $e['salario'],
                    'id_gestion' => $e['id_gestion'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.escalas_salariales', 'id'), coalesce(max(id), 1)) FROM rrhh.escalas_salariales");

        // 5. UNIDADES ORGANIZACIONALES
        $unidades = [
            ['id' => 1, 'nombre' => 'GERENCIA GENERAL', 'sigla' => 'GG', 'id_padre' => null, 'regional' => 1, 'nivel' => 1],
            ['id' => 2, 'nombre' => 'GERENCIA DE ADMINISTRACIÓN Y FINANZAS', 'sigla' => 'GAF', 'id_padre' => 1, 'regional' => 1, 'nivel' => 2],
            ['id' => 3, 'nombre' => 'GERENCIA DE COMERCIALIZACIÓN', 'sigla' => 'GCOM', 'id_padre' => 1, 'regional' => 1, 'nivel' => 2],
            ['id' => 4, 'nombre' => 'GERENCIA DE PRODUCCIÓN AGROPECUARIA', 'sigla' => 'GPA', 'id_padre' => 1, 'regional' => 1, 'nivel' => 2],
            ['id' => 5, 'nombre' => 'UNIDAD DE RECURSOS HUMANOS', 'sigla' => 'URRH', 'id_padre' => 2, 'regional' => 1, 'nivel' => 3],
            ['id' => 6, 'nombre' => 'UNIDAD DE TECNOLOGÍAS Y SISTEMAS', 'sigla' => 'UTIC', 'id_padre' => 1, 'regional' => 1, 'nivel' => 3],
            ['id' => 7, 'nombre' => 'UNIDAD DE ALMACENES Y LOGÍSTICA', 'sigla' => 'UAL', 'id_padre' => 3, 'regional' => 1, 'nivel' => 3],
            ['id' => 8, 'nombre' => 'PLANTA INDUSTRIAL Y SILOS SAN PEDRO', 'sigla' => 'PSSP', 'id_padre' => 4, 'regional' => 2, 'nivel' => 3],
        ];
        foreach ($unidades as $u) {
            DB::table('rrhh.unidades_organizacionales')->updateOrInsert(
                ['id' => $u['id']],
                [
                    'nombre' => $u['nombre'],
                    'sigla' => $u['sigla'],
                    'id_organismo_padre' => $u['id_padre'],
                    'padreId' => $u['id_padre'],
                    'id_regional' => $u['regional'],
                    'id_gestion' => 3,
                    'id_nivel' => $u['nivel'],
                    'es_unidad_recursos_humanos' => $u['id'] === 5,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.unidades_organizacionales', 'id'), coalesce(max(id), 1)) FROM rrhh.unidades_organizacionales");

        // 6. PUESTOS DE TRABAJO
        $puestos = [
            ['id' => 1, 'nombre' => 'Gerente General Ejecutivo', 'id_unidad' => 1, 'id_escala' => 1],
            ['id' => 2, 'nombre' => 'Gerente de Administración y Finanzas', 'id_unidad' => 2, 'id_escala' => 2],
            ['id' => 3, 'nombre' => 'Gerente de Comercialización', 'id_unidad' => 3, 'id_escala' => 2],
            ['id' => 4, 'nombre' => 'Jefe de Recursos Humanos', 'id_unidad' => 5, 'id_escala' => 3],
            ['id' => 5, 'nombre' => 'Jefe de Tecnologías y Sistemas', 'id_unidad' => 6, 'id_escala' => 3],
            ['id' => 6, 'nombre' => 'Responsable de Planillas y Asistencia', 'id_unidad' => 5, 'id_escala' => 4],
            ['id' => 7, 'nombre' => 'Desarrollador de Software Senior', 'id_unidad' => 6, 'id_escala' => 4],
            ['id' => 8, 'nombre' => 'Administrador de Base de Datos y Redes', 'id_unidad' => 6, 'id_escala' => 5],
            ['id' => 9, 'nombre' => 'Analista Contable y Financiero', 'id_unidad' => 2, 'id_escala' => 5],
            ['id' => 10, 'nombre' => 'Responsable de Almacenes y Logística', 'id_unidad' => 7, 'id_escala' => 6],
            ['id' => 11, 'nombre' => 'Encargado de Silos y Acopio', 'id_unidad' => 8, 'id_escala' => 6],
            ['id' => 12, 'nombre' => 'Auxiliar Administrativo de Personal', 'id_unidad' => 5, 'id_escala' => 7],
            ['id' => 13, 'nombre' => 'Asistente de Soporte Técnico', 'id_unidad' => 6, 'id_escala' => 7],
            ['id' => 14, 'nombre' => 'Operador de Balanza y Báscula', 'id_unidad' => 8, 'id_escala' => 8],
            ['id' => 15, 'nombre' => 'Técnico de Mantenimiento de Silos', 'id_unidad' => 8, 'id_escala' => 8],
        ];
        foreach ($puestos as $p) {
            DB::table('rrhh.puestos')->updateOrInsert(
                ['id' => $p['id']],
                [
                    'nombre' => $p['nombre'],
                    'id_unidad_organizacional' => $p['id_unidad'],
                    'id_escala_salarial' => $p['id_escala'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.puestos', 'id'), coalesce(max(id), 1)) FROM rrhh.puestos");

        // 7. FUNCIONARIOS REPRESENTATIVOS (15 PERSONAS CON LEGAJOS Y CAS)
        $funcionariosData = [
            [
                'id' => 1, 'nombres' => 'Carlos Franklin', 'primer_apellido' => 'Mamani', 'segundo_apellido' => 'Quispe',
                'ci' => '4892104', 'genero' => 'MASCULINO', 'celular' => '71548920', 'correo' => 'carlos.mamani@emapa.gob.bo',
                'puesto_id' => 1, 'item' => 1, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2016-02-01',
                'nivel_inst' => 'MAESTRIA', 'carrera' => 'Economía y Administración Pública', 'inst' => 'UMSA',
                'cas_anios' => 16, 'cas_meses' => 4, 'usuario' => 'gerente.general',
            ],
            [
                'id' => 2, 'nombres' => 'Patricia Elena', 'primer_apellido' => 'Vargas', 'segundo_apellido' => 'Morales',
                'ci' => '3928105', 'genero' => 'FEMENINO', 'celular' => '72019482', 'correo' => 'patricia.vargas@emapa.gob.bo',
                'puesto_id' => 2, 'item' => 2, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2018-04-15',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Auditoría Financiera', 'inst' => 'UMSA',
                'cas_anios' => 11, 'cas_meses' => 2, 'usuario' => 'gerente.gaf',
            ],
            [
                'id' => 3, 'nombres' => 'Marcelo Javier', 'primer_apellido' => 'Rojas', 'segundo_apellido' => 'Guzmán',
                'ci' => '5401928', 'genero' => 'MASCULINO', 'celular' => '77291048', 'correo' => 'marcelo.rojas@emapa.gob.bo',
                'puesto_id' => 3, 'item' => 3, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2019-01-10',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Ingeniería Comercial', 'inst' => 'UCB',
                'cas_anios' => 8, 'cas_meses' => 6, 'usuario' => 'gerente.comercial',
            ],
            [
                'id' => 4, 'nombres' => 'Silvia Carola', 'primer_apellido' => 'Flores', 'segundo_apellido' => 'Condori',
                'ci' => '6192840', 'genero' => 'FEMENINO', 'celular' => '76501928', 'correo' => 'silvia.flores@emapa.gob.bo',
                'puesto_id' => 4, 'item' => 4, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2017-06-01',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Administración de Empresas', 'inst' => 'UMSA',
                'cas_anios' => 12, 'cas_meses' => 0, 'usuario' => 'jefe.rrhh',
            ],
            [
                'id' => 5, 'nombres' => 'David Ramiro', 'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Choque',
                'ci' => '6819402', 'genero' => 'MASCULINO', 'celular' => '73049182', 'correo' => 'david.mendoza@emapa.gob.bo',
                'puesto_id' => 5, 'item' => 5, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2020-03-01',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Ingeniería de Sistemas', 'inst' => 'UMSA',
                'cas_anios' => 6, 'cas_meses' => 8, 'usuario' => 'jefe.sistemas',
            ],
            [
                'id' => 6, 'nombres' => 'Andrea Ximena', 'primer_apellido' => 'Torres', 'segundo_apellido' => 'Rios',
                'ci' => '7019283', 'genero' => 'FEMENINO', 'celular' => '71928401', 'correo' => 'andrea.torres@emapa.gob.bo',
                'puesto_id' => 6, 'item' => 6, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2021-02-15',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Contaduría Pública', 'inst' => 'UMSA',
                'cas_anios' => 5, 'cas_meses' => 3, 'usuario' => 'planillas.rrhh',
            ],
            [
                'id' => 7, 'nombres' => 'Rodrigo Iván', 'primer_apellido' => 'Aguilar', 'segundo_apellido' => 'Pérez',
                'ci' => '7829104', 'genero' => 'MASCULINO', 'celular' => '78201948', 'correo' => 'rodrigo.aguilar@emapa.gob.bo',
                'puesto_id' => 7, 'item' => 7, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2022-01-10',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Ingeniería de Sistemas', 'inst' => 'UMSS',
                'cas_anios' => 4, 'cas_meses' => 2, 'usuario' => 'dev.senior',
            ],
            [
                'id' => 8, 'nombres' => 'Gonzalo Sergio', 'primer_apellido' => 'Castro', 'segundo_apellido' => 'Chávez',
                'ci' => '8192049', 'genero' => 'MASCULINO', 'celular' => '74019284', 'correo' => 'gonzalo.castro@emapa.gob.bo',
                'puesto_id' => 8, 'item' => 8, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2022-05-01',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Ingeniería en Redes', 'inst' => 'UAGRM',
                'cas_anios' => 3, 'cas_meses' => 5, 'usuario' => 'dba.redes',
            ],
            [
                'id' => 9, 'nombres' => 'Claudia Paola', 'primer_apellido' => 'Navarro', 'segundo_apellido' => 'Suárez',
                'ci' => '8920194', 'genero' => 'FEMENINO', 'celular' => '79019284', 'correo' => 'claudia.navarro@emapa.gob.bo',
                'puesto_id' => 9, 'item' => 9, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2023-01-15',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Finanzas Corporativas', 'inst' => 'UCB',
                'cas_anios' => 2, 'cas_meses' => 0, 'usuario' => 'analista.contable',
            ],
            [
                'id' => 10, 'nombres' => 'Jorge Luis', 'primer_apellido' => 'Morales', 'segundo_apellido' => 'Calle',
                'ci' => '9019284', 'genero' => 'MASCULINO', 'celular' => '72910482', 'correo' => 'jorge.morales@emapa.gob.bo',
                'puesto_id' => 10, 'item' => 10, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2023-03-01',
                'nivel_inst' => 'TECNICO_SUPERIOR', 'carrera' => 'Gestión Logística', 'inst' => 'INCOS',
                'cas_anios' => 2, 'cas_meses' => 4, 'usuario' => 'almacenes.logistica',
            ],
            [
                'id' => 11, 'nombres' => 'Rubén Darío', 'primer_apellido' => 'Gutiérrez', 'segundo_apellido' => 'Ortiz',
                'ci' => '4091820', 'genero' => 'MASCULINO', 'celular' => '75019284', 'correo' => 'ruben.gutierrez@emapa.gob.bo',
                'puesto_id' => 11, 'item' => 11, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2015-08-01',
                'nivel_inst' => 'LICENCIATURA', 'carrera' => 'Ingeniería Agronómica', 'inst' => 'UAGRM',
                'cas_anios' => 20, 'cas_meses' => 1, 'usuario' => 'encargado.silos',
            ],
            [
                'id' => 12, 'nombres' => 'Gabriela Lizeth', 'primer_apellido' => 'Paredes', 'segundo_apellido' => 'Soto',
                'ci' => '9401928', 'genero' => 'FEMENINO', 'celular' => '76910482', 'correo' => 'gabriela.paredes@emapa.gob.bo',
                'puesto_id' => 12, 'item' => 12, 'tipo_contrato' => 'EVENTUAL', 'fecha_ingreso' => '2024-01-02',
                'nivel_inst' => 'TECNICO_SUPERIOR', 'carrera' => 'Secretariado Ejecutivo', 'inst' => 'INCOS',
                'cas_anios' => 0, 'cas_meses' => 0, 'usuario' => null,
            ],
            [
                'id' => 13, 'nombres' => 'Kevin Alejandro', 'primer_apellido' => 'Salazar', 'segundo_apellido' => 'Mendoza',
                'ci' => '10291048', 'genero' => 'MASCULINO', 'celular' => '71049281', 'correo' => 'kevin.salazar@emapa.gob.bo',
                'puesto_id' => 13, 'item' => 13, 'tipo_contrato' => 'EVENTUAL', 'fecha_ingreso' => '2024-02-01',
                'nivel_inst' => 'TECNICO_MEDIO', 'carrera' => 'Sistemas Informáticos', 'inst' => 'Instituto Tecnológico',
                'cas_anios' => 0, 'cas_meses' => 0, 'usuario' => null,
            ],
            [
                'id' => 14, 'nombres' => 'Víctor Hugo', 'primer_apellido' => 'Miranda', 'segundo_apellido' => 'Aruquipa',
                'ci' => '6201948', 'genero' => 'MASCULINO', 'celular' => '77019284', 'correo' => 'victor.miranda@emapa.gob.bo',
                'puesto_id' => 14, 'item' => 14, 'tipo_contrato' => 'EVENTUAL', 'fecha_ingreso' => '2023-08-15',
                'nivel_inst' => 'SECUNDARIA', 'carrera' => 'Bachiller en Humanidades', 'inst' => 'Colegio San Simón',
                'cas_anios' => 0, 'cas_meses' => 0, 'usuario' => null,
            ],
            [
                'id' => 15, 'nombres' => 'Jaime Alberto', 'primer_apellido' => 'Velasco', 'segundo_apellido' => 'Duran',
                'ci' => '5192049', 'genero' => 'MASCULINO', 'celular' => '78019482', 'correo' => 'jaime.velasco@emapa.gob.bo',
                'puesto_id' => 15, 'item' => 15, 'tipo_contrato' => 'PLANTA', 'fecha_ingreso' => '2016-10-01',
                'nivel_inst' => 'TECNICO_SUPERIOR', 'carrera' => 'Electromecánica Industrial', 'inst' => 'Instituto Técnico',
                'cas_anios' => 18, 'cas_meses' => 7, 'usuario' => null,
            ],
        ];

        foreach ($funcionariosData as $f) {
            // 7.1 Persona
            $persona = Persona::updateOrCreate(
                ['id' => $f['id']],
                [
                    'nombres' => $f['nombres'],
                    'primer_apellido' => $f['primer_apellido'],
                    'segundo_apellido' => $f['segundo_apellido'],
                    'nro_documento' => $f['ci'],
                    'genero' => $f['genero'],
                    'telefono_celular' => $f['celular'],
                    'correo_electronico_personal' => $f['correo'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // 7.2 Asignación de Puesto
            DB::table('rrhh.asignaciones_puestos')->updateOrInsert(
                [
                    'id_persona' => $persona->id,
                    'id_puesto' => $f['puesto_id'],
                ],
                [
                    'tipo_asignacion' => 'ITEM',
                    'nro_item' => $f['item'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // 7.3 Ficha Personal
            $ficha = FichaPersonal::updateOrCreate(
                ['id_persona' => $persona->id],
                [
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // 7.4 Dato Laboral
            DatoLaboral::updateOrCreate(
                ['id_ficha_personal' => $ficha->id],
                [
                    'tipo_funcionario' => $f['tipo_contrato'],
                    'fecha_ingreso' => $f['fecha_ingreso'],
                    'cargo' => 'Funcionario',
                    'nro_item' => $f['item'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // 7.5 Estudio Académico
            EstudioAcademico::updateOrCreate(
                [
                    'id_ficha_personal' => $ficha->id,
                    'carrera' => $f['carrera'],
                ],
                [
                    'nivel_instruccion' => $f['nivel_inst'],
                    'institucion' => $f['inst'],
                    'grado_obtenido' => 'TITULADO',
                    'fecha_emision' => '2015-11-30',
                    'tiene_titulo_provision_nacional' => true,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // 7.6 CAS (Calificación de Años de Servicio)
            if ($f['cas_anios'] > 0) {
                Cas::updateOrCreate(
                    ['id_ficha_personal' => $ficha->id],
                    [
                        'nro_resolucion' => 'RA-RRHH-' . str_pad((string)$f['id'], 3, '0', STR_PAD_LEFT) . '/2024',
                        'anios' => $f['cas_anios'],
                        'meses' => $f['cas_meses'],
                        'dias' => 15,
                        'fecha_calificacion' => '2024-01-15',
                        'fecha_resolucion' => '2024-01-15',
                        '_estado' => 'ACTIVO',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );
            }

            // 7.7 Usuario ERP
            if (!empty($f['usuario'])) {
                User::updateOrCreate(
                    ['usr_usuario' => $f['usuario']],
                    [
                        'name' => $persona->nombre_completo,
                        'email' => $f['correo'],
                        'password' => Hash::make('password123'),
                        'usr_estado' => 'A',
                        'usr_externo_id' => $persona->id,
                        'usr_cargo_add' => $f['carrera'],
                    ]
                );
            }
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.personas', 'id'), coalesce(max(id), 1)) FROM rrhh.personas");
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.fichas_personales', 'id'), coalesce(max(id), 1)) FROM rrhh.fichas_personales");

        // 8. RELOJES BIOMÉTRICOS
        $biometricos = [
            ['id' => 1, 'nombre' => 'ZK-CENTRAL-01', 'url' => '192.168.1.201', 'puerto' => 4370, 'ubicacion' => 'Sede Central La Paz - PB', 'modelo' => 'MB360'],
            ['id' => 2, 'nombre' => 'ZK-CENTRAL-02', 'url' => '192.168.1.202', 'puerto' => 4370, 'ubicacion' => 'Sede Central La Paz - Piso 2', 'modelo' => 'IN01-A'],
            ['id' => 3, 'nombre' => 'ZK-SANPEDRO-01', 'url' => '192.168.2.201', 'puerto' => 4370, 'ubicacion' => 'Planta Industrial San Pedro', 'modelo' => 'K40'],
            ['id' => 4, 'nombre' => 'ZK-MONTERO-01', 'url' => '192.168.2.202', 'puerto' => 4370, 'ubicacion' => 'Silos Montero', 'modelo' => 'G3'],
        ];
        foreach ($biometricos as $b) {
            DB::table('rrhh.biometricos')->updateOrInsert(
                ['id' => $b['id']],
                [
                    'nombre' => $b['nombre'],
                    'url' => $b['url'],
                    'puerto' => $b['puerto'],
                    'ubicacion' => $b['ubicacion'],
                    'modelo' => $b['modelo'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.biometricos', 'id'), coalesce(max(id), 1)) FROM rrhh.biometricos");

        // 9. HORARIOS INSTITUCIONALES
        $horarioContinuo = Horario::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'HORARIO CONTINUO SEDE CENTRAL (8:30 - 16:30)',
                'tipo' => 'CONTINUO',
                'dias_laborales' => '1,2,3,4,5',
                'tolerancia_minutos' => 15,
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );
        Periodo::updateOrCreate(
            ['id_horario' => $horarioContinuo->id, 'orden' => 1],
            [
                'hora_inicio' => '08:30',
                'hora_fin' => '16:30',
                'hora_inicio_tolerancia' => '08:45',
                'hora_fin_tolerancia' => '16:15',
                '_estado' => 'ACTIVO',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );

        // Asignar horario a todos los funcionarios
        foreach (Persona::all() as $p) {
            DB::table('rrhh.asignaciones_horarios')->updateOrInsert(
                [
                    'id_persona' => $p->id,
                    'id_horarios' => $horarioContinuo->id,
                ],
                [
                    'fecha_inicio' => '2026-01-01',
                    'fecha_fin' => '2026-12-31',
                    'permanente' => true,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        // 10. GENERAR ASISTENCIAS Y MARCACIONES REALES DEL MES ACTUAL
        $diasLaborables = 20;
        $anio = (int)date('Y');
        $mes = (int)date('m');

        foreach (Persona::all() as $p) {
            for ($dia = 1; $dia <= $diasLaborables; $dia++) {
                $fechaStr = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                $minutosAtraso = ($p->id % 3 === 0 && $dia % 5 === 0) ? 12 : 0;
                $horaEntrada = sprintf('08:%02d:15', 30 + $minutosAtraso);

                // Marcación Entrada
                Marcacion::updateOrCreate(
                    [
                        'id_usuario_marcacion' => (string)$p->id,
                        'fecha' => $fechaStr,
                        'hora' => $horaEntrada,
                    ],
                    [
                        'id_biometrico' => 1,
                        '_estado' => 'ACTIVO',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );

                // Marcación Salida
                Marcacion::updateOrCreate(
                    [
                        'id_usuario_marcacion' => (string)$p->id,
                        'fecha' => $fechaStr,
                        'hora' => '16:32:00',
                    ],
                    [
                        'id_biometrico' => 1,
                        '_estado' => 'ACTIVO',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );

                // Asistencia Consolidada
                Asistencia::updateOrCreate(
                    [
                        'id_persona' => $p->id,
                        'fecha' => $fechaStr,
                    ],
                    [
                        'entrada_primer_periodo' => $horaEntrada,
                        'salida_primer_periodo' => '16:32:00',
                        'minutos_de_atraso_primer_periodo' => $minutosAtraso,
                        'minutos_de_atraso_segundo_periodo' => 0,
                        'minutos_de_salida_temprana_primer_periodo' => 0,
                        'minutos_de_salida_temprana_segundo_periodo' => 0,
                        'merece_refrigerio' => true,
                        'dia' => 'DIA_HABIL',
                        'id_horario' => $horarioContinuo->id,
                        '_estado' => 'ACTIVO',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );
            }
        }

        // 11. BOLETAS DE SALIDA Y PERMISOS DE EJEMPLO
        $solicitudes = [
            [
                'id_persona' => 6,
                'cite' => 'BOL-RRHH-001/2026',
                'id_permiso' => 1,
                'fecha_salida' => sprintf('%04d-%02d-10', $anio, $mes),
                'hora_salida' => '10:00:00',
                'hora_retorno' => '11:30:00',
                'justificacion' => 'Trámites personales ante entidad bancaria',
                'estado' => 'APROBADO',
            ],
            [
                'id_persona' => 7,
                'cite' => 'BOL-RRHH-002/2026',
                'id_permiso' => 2,
                'fecha_salida' => sprintf('%04d-%02d-15', $anio, $mes),
                'hora_salida' => '14:00:00',
                'hora_retorno' => '16:30:00',
                'justificacion' => 'Consulta médica en Caja Nacional de Salud (CNS)',
                'estado' => 'APROBADO',
            ],
            [
                'id_persona' => 8,
                'cite' => 'BOL-RRHH-003/2026',
                'id_permiso' => 3,
                'fecha_salida' => sprintf('%04d-%02d-20', $anio, $mes),
                'hora_salida' => '09:00:00',
                'hora_retorno' => '13:00:00',
                'justificacion' => 'Revisión técnica de enlaces de fibra óptica en Sede El Alto',
                'estado' => 'PENDIENTE',
            ],
        ];

        foreach ($solicitudes as $s) {
            $sol = SolicitudSalida::updateOrCreate(
                ['cite' => $s['cite']],
                [
                    'id_permiso' => $s['id_permiso'],
                    'fecha_inicio' => $s['fecha_salida'],
                    'fecha_fin' => $s['fecha_salida'],
                    'hora_inicio' => $s['hora_salida'],
                    'hora_fin' => $s['hora_retorno'],
                    'motivo' => $s['justificacion'],
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            DB::table('rrhh.usuarios_solicitudes_salidas')->updateOrInsert(
                [
                    'id_solicitud_salida' => $sol->id,
                    'id_persona' => $s['id_persona'],
                ],
                [
                    'estado_aprobacion' => $s['estado'],
                    'fecha_revision' => now(),
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        // 12. FECHAS DE CORTE MENSUAL (12 MESES)
        for ($m = 1; $m <= 12; $m++) {
            DB::table('rrhh.fechas_cortes')->updateOrInsert(
                [
                    'gestion' => $anio,
                    'mes' => $m,
                ],
                [
                    'dia_inicio' => 21,
                    'dia_fin' => 20,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        $this->command?->info('✅ ¡Población de Recursos Humanos completada exitosamente con 15 funcionarios y legajos completos!');
    }
}
