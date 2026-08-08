<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitudInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //lista de solicitudes de insumos
        DB::table('insumos.solicitudes_insumos')->insert([
            [
                'nombre_solicitante' => 'Jessika galindo',
                'cargo_solicitante' => 'Jefe de planta',
                'numero_acta' => null,
                'observaciones' => 'Ninguna',
                'entrada_salida' => 0,
                'numero_referencia' => 'proceso Compra CUCE',
                'total_items' => '2',
                'total_costo' => '100.00',
                'user_id' => 1,
                'estado_id' => 1,                
                'planta_id_origen' => 1,
                'planta_id_destino' => 1,
                'tipo_solicitud_id' => 7,
                'fecha_registro' => '2021-09-29 10:00:00',
                'fecha_atencion' => '2021-09-29 10:00:00',
                'fecha_documento' => '2021-09-29 10:00:00',
            ],
            [
                'nombre_solicitante' => 'Lindomar Leon',
                'cargo_solicitante' => 'Jefe de planta',
                'numero_acta' => 23,
                'observaciones' => 'Ninguna',
                'entrada_salida' => 1,
                'numero_referencia' => '123',
                'total_items' => '2',
                'total_costo' => '100.00',
                'user_id' => 1,
                'estado_id' => 1,                                
                'planta_id_origen' => 1,
                'planta_id_destino' => 1,
                'tipo_solicitud_id' => 5,
                'fecha_registro' => '2021-09-30 10:00:00',
                'fecha_atencion' => '2021-09-30 10:00:00',
                'fecha_documento' => '2021-09-30 10:00:00',
            ],
            [
                'nombre_solicitante' => 'Planta Andres Mendez',
                'cargo_solicitante' => 'Jefe de planta',
                'numero_acta' => null,
                'observaciones' => 'Ninguna',
                'entrada_salida' => 1,
                'numero_referencia' => '123',
                'total_items' => '21',
                'total_costo' => '1000.00',
                'user_id' => 1,
                'estado_id' => 1,                                
                'planta_id_origen' => 1,
                'planta_id_destino' => 1,
                'tipo_solicitud_id' => 6,
                'fecha_registro' => '2021-09-30 10:00:00',
                'fecha_atencion' => '2021-09-30 10:00:00',
                'fecha_documento' => '2021-09-30 10:00:00',
            ],
            [
                'nombre_solicitante' => 'Rosa Espinoza',
                'cargo_solicitante' => 'Jefe de planta Central',
                'numero_acta' => null,
                'observaciones' => 'Ninguna',
                'entrada_salida' => 1,
                'numero_referencia' => '123',
                'total_items' => '10',
                'total_costo' => '5000.00',
                'user_id' => 1,
                'estado_id' => 1,                                
                'planta_id_origen' => 1,
                'planta_id_destino' => 1,
                'tipo_solicitud_id' => 6,
                'fecha_registro' => '2021-09-30 10:00:00',
                'fecha_atencion' => '2021-09-30 10:00:00',
                'fecha_documento' => '2021-09-30 10:00:00',
            ],
            [
                'nombre_solicitante' => 'Carlos Perez',
                'cargo_solicitante' => 'Jefe de planta',
                'numero_acta' => null,
                'observaciones' => 'Ninguna',
                'entrada_salida' => 1,
                'numero_referencia' => '123',
                'total_items' => '2',
                'total_costo' => '100.00',
                'user_id' => 1,
                'estado_id' => 1,                                
                'planta_id_origen' => 1,
                'planta_id_destino' => 1,
                'tipo_solicitud_id' => 1,
                'fecha_registro' => '2021-10-01 10:00:00',
                'fecha_atencion' => '2021-10-01 10:00:00',
                'fecha_documento' => '2021-10-01 10:00:00',
            ]
        ]);
    }
}
