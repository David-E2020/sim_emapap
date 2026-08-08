<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovimientoExistenciaInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inventario.movimiento_inventarios')->insert([
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 2,
            //     "mv_origen_id" => 34,
            //     "mv_destino_id" => 34,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 3,
            //     "mv_origen_id" => 60,
            //     "mv_destino_id" => 60,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 4,
            //     "mv_origen_id" => 58,
            //     "mv_destino_id" => 58,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 5,
            //     "mv_origen_id" => 57,
            //     "mv_destino_id" => 57,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 7,
            //     "mv_origen_id" => 61,
            //     "mv_destino_id" => 61,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 8,
            //     "mv_origen_id" => 42,
            //     "mv_destino_id" => 42,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 9,
            //     "mv_origen_id" => 65,
            //     "mv_destino_id" => 65,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 10,
            //     "mv_origen_id" => 66,
            //     "mv_destino_id" => 66,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 11,
            //     "mv_origen_id" => 170,
            //     "mv_destino_id" => 170,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 12,
            //     "mv_origen_id" => 67,
            //     "mv_destino_id" => 67,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 13,
            //     "mv_origen_id" => 123,
            //     "mv_destino_id" => 123,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 14,
            //     "mv_origen_id" => 124,
            //     "mv_destino_id" => 124,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 15,
            //     "mv_origen_id" => 162,
            //     "mv_destino_id" => 162,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 16,
            //     "mv_origen_id" => 131,
            //     "mv_destino_id" => 131,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 17,
            //     "mv_origen_id" => 144,
            //     "mv_destino_id" => 144,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 18,
            //     "mv_origen_id" => 130,
            //     "mv_destino_id" => 130,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 19,
            //     "mv_origen_id" => 127,
            //     "mv_destino_id" => 127,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 20,
            //     "mv_origen_id" => 125,
            //     "mv_destino_id" => 125,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 21,
            //     "mv_origen_id" => 190,
            //     "mv_destino_id" => 190,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 22,
            //     "mv_origen_id" => 191,
            //     "mv_destino_id" => 191,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 23,
            //     "mv_origen_id" => 180,
            //     "mv_destino_id" => 180,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 24,
            //     "mv_origen_id" => 136,
            //     "mv_destino_id" => 136,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 25,
            //     "mv_origen_id" => 155,
            //     "mv_destino_id" => 155,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 26,
            //     "mv_origen_id" => 68,
            //     "mv_destino_id" => 68,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 27,
            //     "mv_origen_id" => 132,
            //     "mv_destino_id" => 132,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 28,
            //     "mv_origen_id" => 192,
            //     "mv_destino_id" => 192,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 29,
            //     "mv_origen_id" => 193,
            //     "mv_destino_id" => 193,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 30,
            //     "mv_origen_id" => 36,
            //     "mv_destino_id" => 36,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 31,
            //     "mv_origen_id" => 163,
            //     "mv_destino_id" => 163,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 32,
            //     "mv_origen_id" => 165,
            //     "mv_destino_id" => 165,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 33,
            //     "mv_origen_id" => 69,
            //     "mv_destino_id" => 69,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 34,
            //     "mv_origen_id" => 194,
            //     "mv_destino_id" => 194,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 35,
            //     "mv_origen_id" => 188,
            //     "mv_destino_id" => 188,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 36,
            //     "mv_origen_id" => 62,
            //     "mv_destino_id" => 62,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 37,
            //     "mv_origen_id" => 128,
            //     "mv_destino_id" => 128,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 39,
            //     "mv_origen_id" => 70,
            //     "mv_destino_id" => 70,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 40,
            //     "mv_origen_id" => 71,
            //     "mv_destino_id" => 71,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 41,
            //     "mv_origen_id" => 72,
            //     "mv_destino_id" => 72,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 42,
            //     "mv_origen_id" => 73,
            //     "mv_destino_id" => 73,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 43,
            //     "mv_origen_id" => 74,
            //     "mv_destino_id" => 74,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 44,
            //     "mv_origen_id" => 75,
            //     "mv_destino_id" => 75,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 45,
            //     "mv_origen_id" => 55,
            //     "mv_destino_id" => 55,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 46,
            //     "mv_origen_id" => 41,
            //     "mv_destino_id" => 41,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 47,
            //     "mv_origen_id" => 53,
            //     "mv_destino_id" => 53,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 48,
            //     "mv_origen_id" => 195,
            //     "mv_destino_id" => 195,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 49,
            //     "mv_origen_id" => 196,
            //     "mv_destino_id" => 196,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 50,
            //     "mv_origen_id" => 197,
            //     "mv_destino_id" => 197,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 51,
            //     "mv_origen_id" => 44,
            //     "mv_destino_id" => 44,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 52,
            //     "mv_origen_id" => 45,
            //     "mv_destino_id" => 45,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 53,
            //     "mv_origen_id" => 198,
            //     "mv_destino_id" => 198,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 54,
            //     "mv_origen_id" => 164,
            //     "mv_destino_id" => 164,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 55,
            //     "mv_origen_id" => 189,
            //     "mv_destino_id" => 189,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 56,
            //     "mv_origen_id" => 46,
            //     "mv_destino_id" => 46,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 57,
            //     "mv_origen_id" => 59,
            //     "mv_destino_id" => 59,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 58,
            //     "mv_origen_id" => 63,
            //     "mv_destino_id" => 63,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 59,
            //     "mv_origen_id" => 122,
            //     "mv_destino_id" => 122,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 60,
            //     "mv_origen_id" => 48,
            //     "mv_destino_id" => 48,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 61,
            //     "mv_origen_id" => 50,
            //     "mv_destino_id" => 50,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 62,
            //     "mv_origen_id" => 76,
            //     "mv_destino_id" => 76,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 63,
            //     "mv_origen_id" => 109,
            //     "mv_destino_id" => 109,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 64,
            //     "mv_origen_id" => 199,
            //     "mv_destino_id" => 199,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 65,
            //     "mv_origen_id" => 200,
            //     "mv_destino_id" => 200,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 66,
            //     "mv_origen_id" => 156,
            //     "mv_destino_id" => 156,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ],
            // [
            //     "mv_nro_correlativo" => 0,
            //     "mv_tipo_movimiento_id" => 14,
            //     "mv_planta_id" => 67,
            //     "mv_origen_id" => 161,
            //     "mv_destino_id" => 161,
            //     "mv_tipo" => "INGRESO",
            //     "mv_estado_id" => 10,
            //     "mv_estado" => "A",
            //     "mv_usr_registrado" => 1
            // ]
        ]);
    }
}
