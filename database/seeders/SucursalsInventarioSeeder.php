<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalsInventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('public.sucursals')->insert([
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 21,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 22,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 28,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 29,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 34,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 48,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 49,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 50,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 53,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 64,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 65,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
            [
                'tipo_documento_sector_id' => '1',
                'nombre' => 'ALMACEN GENERAL',
                'padre_id' => 0,
                'planta_id' => 68,
                'estado' => 'A',
                'estado_baja' => 'A',
                'user_id' => 1,
                'tipo' => 'Almacen',
            ],
        ]);
    }
}
