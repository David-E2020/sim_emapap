<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitudDetalleInsumoSeeder extends Seeder
{

    public function run()
    {
        DB::table('insumos.solicitudes_detalle_insumos')->insert([
            [
                'num_secuencia' => 3,
                'cantidad_solicitada' => 3,
                'cantidad_entregada' => null,
                'solicitud_id' => 1,
                'articulo_id' => 3
            ],
            [
                'num_secuencia' => 2,
                'cantidad_solicitada' => 3,
                'cantidad_entregada' => null,
                'solicitud_id' => 1,
                'articulo_id' => 2
            ],

            [
                'num_secuencia' => 1,
                'cantidad_solicitada' => 1,
                'cantidad_entregada' => Null,
                'solicitud_id' => 1,
                'articulo_id' => 1
            ],

            [
                'num_secuencia' => 4,
                'cantidad_solicitada' => 4,
                'cantidad_entregada' => Null,
                'solicitud_id' => 1,
                'articulo_id' => 4
            ],

            [
                'num_secuencia' => 1,
                'cantidad_solicitada' => 5,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 5
            ],

            [
                'num_secuencia' => 2,
                'cantidad_solicitada' => 6,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 7
            ],

            [
                'num_secuencia' => 3,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 9
            ],

            [
                'num_secuencia' => 4,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 91
            ],

            [
                'num_secuencia' => 1,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 5,
                'articulo_id' => 71
            ],

            [
                'num_secuencia' => 5,
                'cantidad_solicitada' => 41,
                'cantidad_entregada' => Null,
                'solicitud_id' => 1,
                'articulo_id' => 41
            ],

            [
                'num_secuencia' => 1,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 4,
                'articulo_id' => 19
            ],

            [
                'num_secuencia' => 1,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 99
            ],

            [
                'num_secuencia' => 5,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 92
            ],

            [
                'num_secuencia' => 6,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 93
            ],

            [
                'num_secuencia' => 7,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 94
            ],

            [
                'num_secuencia' => 8,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 95
            ],

            [
                'num_secuencia' => '9',
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 96
            ],

            [
                'num_secuencia' => 10,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 97
            ],

            [
                'num_secuencia' => 11,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 98
            ],

            [
                'num_secuencia' => 12,
                'cantidad_solicitada' => 8,
                'cantidad_entregada' => Null,
                'solicitud_id' => 2,
                'articulo_id' => 100
            ],

            [
                'num_secuencia' => 10,
                'cantidad_solicitada' => 12,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 119
            ],

            [
                'num_secuencia' => '9',
                'cantidad_solicitada' => 2,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 118
            ],

            [
                'num_secuencia' => 8,
                'cantidad_solicitada' => 17,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 117
            ],

            [
                'num_secuencia' => 7,
                'cantidad_solicitada' => 14,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 116
            ],

            [
                'num_secuencia' => 6,
                'cantidad_solicitada' => 12,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 115
            ],

            [
                'num_secuencia' => 5,
                'cantidad_solicitada' => 18,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 114
            ],

            [
                'num_secuencia' => 4,
                'cantidad_solicitada' => 16,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 113
            ],

            [
                'num_secuencia' => 3,
                'cantidad_solicitada' => 13,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 112
            ],

            [
                'num_secuencia' => 2,
                'cantidad_solicitada' => 10,
                'cantidad_entregada' => Null,
                'solicitud_id' => 3,
                'articulo_id' => 111
            ]
        ]);
    }
}
