<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoJustificacionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['id' => 1, 'nombre' => 'PERMISO PARTICULAR', 'sigla' => 'P.P.', 'tiempo_maximo' => 2, 'tipo_tiempo' => 'HORAS', 'es_con_goce_haberes' => false, 'descripcion' => 'Permiso por asuntos particulares (hasta 2 horas)'],
            ['id' => 2, 'nombre' => 'COMISION OFICIAL', 'sigla' => 'C.O.', 'tiempo_maximo' => 8, 'tipo_tiempo' => 'HORAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Salidas para gestiones institucionales oficiales'],
            ['id' => 3, 'nombre' => 'CONSULTA MEDICA / SEGURO', 'sigla' => 'MED', 'tiempo_maximo' => 4, 'tipo_tiempo' => 'HORAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Atención médica en la Caja Nacional de Salud u hospital'],
            ['id' => 4, 'nombre' => 'BAJA MEDICA', 'sigla' => 'B.M.', 'tiempo_maximo' => 30, 'tipo_tiempo' => 'DIAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Incapacidad temporal emitida por médico tratante'],
            ['id' => 5, 'nombre' => 'LICENCIA POR DUELO', 'sigla' => 'DUELO', 'tiempo_maximo' => 3, 'tipo_tiempo' => 'DIAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Fallecimiento de familiares de primer y segundo grado'],
            ['id' => 6, 'nombre' => 'LICENCIA POR MATRIMONIO', 'sigla' => 'MATR', 'tiempo_maximo' => 3, 'tipo_tiempo' => 'DIAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Licencia especial por matrimonio civil'],
            ['id' => 7, 'nombre' => 'LICENCIA POR PATERNIDAD / MATERNIDAD', 'sigla' => 'PAT', 'tiempo_maximo' => 3, 'tipo_tiempo' => 'DIAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Nacimiento de hijos'],
            ['id' => 8, 'nombre' => 'TOLERANCIA DE LACTANCIA', 'sigla' => 'LACT', 'tiempo_maximo' => 1, 'tipo_tiempo' => 'HORAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Periodo de lactancia materna (1 hora diaria)'],
            ['id' => 9, 'nombre' => 'VACACION ANUAL', 'sigla' => 'VAC', 'tiempo_maximo' => 30, 'tipo_tiempo' => 'DIAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Uso de vacación según escala de años de servicio'],
            ['id' => 10, 'nombre' => 'OMISION DE MARCADO', 'sigla' => 'OM', 'tiempo_maximo' => 1, 'tipo_tiempo' => 'HORAS', 'es_con_goce_haberes' => true, 'descripcion' => 'Regularización de olvido o fallo de reloj biométrico'],
        ];

        foreach ($permisos as $p) {
            DB::table('rrhh.permisos')->updateOrInsert(
                ['id' => $p['id']],
                [
                    'nombre' => $p['nombre'],
                    'sigla' => $p['sigla'],
                    'tiempo_maximo' => $p['tiempo_maximo'],
                    'tipo_tiempo' => $p['tipo_tiempo'],
                    'es_con_goce_haberes' => $p['es_con_goce_haberes'],
                    'descripcion' => $p['descripcion'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        $justificaciones = [
            ['id' => 1, 'nombre' => 'Trámite Institucional', 'sigla' => 'TI', 'descripcion' => 'Gestiones administrativas ante entidades públicas'],
            ['id' => 2, 'nombre' => 'Reunión de Coordinación Externa', 'sigla' => 'RCE', 'descripcion' => 'Reunión de trabajo fuera de instalaciones'],
            ['id' => 3, 'nombre' => 'Cita Médica Programada', 'sigla' => 'CMP', 'descripcion' => 'Atención de salud comprobable con ficha/boleta'],
            ['id' => 4, 'nombre' => 'Urgencia Familiar o Personal', 'sigla' => 'UFP', 'descripcion' => 'Situaciones imprevistas de fuerza mayor'],
            ['id' => 5, 'nombre' => 'Falla Técnica en Reloj Biométrico', 'sigla' => 'FTB', 'descripcion' => 'Reloj sin energía, desconectado o sin lector'],
        ];

        foreach ($justificaciones as $j) {
            DB::table('rrhh.justificaciones')->updateOrInsert(
                ['id' => $j['id']],
                [
                    'nombre' => $j['nombre'],
                    'sigla' => $j['sigla'],
                    'descripcion' => $j['descripcion'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.permisos', 'id'), coalesce(max(id), 1)) FROM rrhh.permisos");
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.justificaciones', 'id'), coalesce(max(id), 1)) FROM rrhh.justificaciones");
    }
}
