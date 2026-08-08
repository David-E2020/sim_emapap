<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConductorAcopioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('acopio.conductors')->insert([
            [
                'con_transporte_id' => 1,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 123456,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 1,
                'con_usr_modificado' => 1,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 2,
                'con_nombre_completo' => 'Marcos Merez',
                'con_numero_identificacion' => 654123,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 2,
                'con_usr_modificado' => 2,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 3,
                'con_nombre_completo' => 'Juan Perez',
                'con_numero_identificacion' => 758962,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 3,
                'con_usr_modificado' => 3,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 4,
                'con_nombre_completo' => 'Pedro Perez',
                'con_numero_identificacion' => 147258369,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 4,
                'con_usr_modificado' => 4,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 5,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 963852,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 5,
                'con_usr_modificado' => 5,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 6,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 456789,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 6,
                'con_usr_modificado' => 6,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 7,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 12378945,
                'con_direccion' => 'Av. 2422',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 7,
                'con_usr_modificado' => 7,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 8,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 123456789,
                'con_direccion' => 'Av. 32',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 8,
                'con_usr_modificado' => 8,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 9,
                'con_nombre_completo' => 'Antonio Suarez',
                'con_numero_identificacion' => 123456789,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 9,
                'con_usr_modificado' => 9,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ],
            [
                'con_transporte_id' => 10,
                'con_nombre_completo' => 'Jose Suarez',
                'con_numero_identificacion' => 123456789,
                'con_direccion' => 'Av. 1',
                'con_telefono' => '123456789',
                'con_categoria' => 'A1',
                'con_numero_licencia' => '123456789',
                'con_estado_registro' => 'A',
                'con_usr_registrado' => 10,
                'con_usr_modificado' => 10,
                'con_usr_eliminado' => null,
                'created_at' => 'now()',
            ]
        ]);
    }
}