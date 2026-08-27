<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeriadoSeeder extends Seeder
{
    public function run(): void
    {
        $anio = (int)date('Y');

        $feriados = [
            // Feriados Nacionales
            ['nombre' => 'Año Nuevo', 'dia' => 1, 'mes' => 1, 'dia_feriado' => 1, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Día del Estado Plurinacional', 'dia' => 22, 'mes' => 1, 'dia_feriado' => 22, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Día del Trabajo', 'dia' => 1, 'mes' => 5, 'dia_feriado' => 1, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Año Nuevo Aymara Amazónico', 'dia' => 21, 'mes' => 6, 'dia_feriado' => 21, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Día de la Independencia de Bolivia', 'dia' => 6, 'mes' => 8, 'dia_feriado' => 6, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Día de Todos los Santos', 'dia' => 2, 'mes' => 11, 'dia_feriado' => 2, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],
            ['nombre' => 'Navidad', 'dia' => 25, 'mes' => 12, 'dia_feriado' => 25, 'anio' => $anio, 'es_feriado_nacional' => true, 'id_departamento' => null],

            // Feriados Departamentales
            ['nombre' => 'Aniversario de Oruro', 'dia' => 10, 'mes' => 2, 'dia_feriado' => 10, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 4],
            ['nombre' => 'Aniversario de Tarija', 'dia' => 15, 'mes' => 4, 'dia_feriado' => 15, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 6],
            ['nombre' => 'Aniversario de Chuquisaca', 'dia' => 25, 'mes' => 5, 'dia_feriado' => 25, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 1],
            ['nombre' => 'Aniversario de La Paz', 'dia' => 16, 'mes' => 7, 'dia_feriado' => 16, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 2],
            ['nombre' => 'Aniversario de Cochabamba', 'dia' => 14, 'mes' => 9, 'dia_feriado' => 14, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 3],
            ['nombre' => 'Aniversario de Santa Cruz', 'dia' => 24, 'mes' => 9, 'dia_feriado' => 24, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 7],
            ['nombre' => 'Aniversario de Pando', 'dia' => 1, 'mes' => 10, 'dia_feriado' => 1, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 9],
            ['nombre' => 'Aniversario de Potosí', 'dia' => 10, 'mes' => 11, 'dia_feriado' => 10, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 5],
            ['nombre' => 'Aniversario de Beni', 'dia' => 18, 'mes' => 11, 'dia_feriado' => 18, 'anio' => $anio, 'es_feriado_nacional' => false, 'id_departamento' => 8],
        ];

        foreach ($feriados as $f) {
            DB::table('rrhh.feriados')->updateOrInsert(
                [
                    'nombre' => $f['nombre'],
                    'anio' => $f['anio'],
                ],
                [
                    'dia' => $f['dia'],
                    'mes' => $f['mes'],
                    'dia_feriado' => $f['dia_feriado'],
                    'es_feriado_nacional' => $f['es_feriado_nacional'],
                    'id_departamento' => $f['id_departamento'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }
    }
}
