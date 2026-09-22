<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametricaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Solo conserva las paramétricas esenciales y oficiales para EMAPAP (Agua Potable).
     * Se han purgado catálogos obsoletos ajenos al servicio (acopio de granos, silos, plantas, etc.).
     *
     * @return void
     */
    public function run()
    {
        // 1. TIPO MONEDA
        DB::table('parametricas')->insert([
            [
                'param_nombre' => 'TABLA TIPO MONEDA',
                'param_codigo' => 'ORIGEN',
                'param_valor' => 0,
                'param_tabla' => 'TABLA_TIPO_MONEDA',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'BOLIVIANOS (Bs)',
                'param_codigo' => 'BS',
                'param_valor' => 1,
                'param_tabla' => 'TABLA_TIPO_MONEDA',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'DÓLARES ($us)',
                'param_codigo' => 'SUS',
                'param_valor' => 2,
                'param_tabla' => 'TABLA_TIPO_MONEDA',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
        ]);

        // 2. TIPO DOCUMENTO DE IDENTIDAD
        DB::table('parametricas')->insert([
            [
                'param_nombre' => 'TABLA TIPO DOCUMENTO',
                'param_codigo' => 'ORIGEN',
                'param_valor' => 0,
                'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'CÉDULA DE IDENTIDAD',
                'param_codigo' => 'CI',
                'param_valor' => 1,
                'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'NÚMERO DE IDENTIFICACIÓN TRIBUTARIA',
                'param_codigo' => 'NIT',
                'param_valor' => 2,
                'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'PASAPORTE',
                'param_codigo' => 'PAS',
                'param_valor' => 3,
                'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
            [
                'param_nombre' => 'CÉDULA DE EXTRANJERÍA',
                'param_codigo' => 'CEX',
                'param_valor' => 4,
                'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
                'param_usr_registrado' => 1,
                'param_estado' => 'A',
            ],
        ]);
    }
}
