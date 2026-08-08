<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovimientoExistenciaDetalleInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inventario.movimiento_detalle_inventarios')->insert([
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1150,
            //     "mvd_lote_id" => 1,
            //     "mvd_codigo_lote" => "77908-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 947,
            //     "mvd_lote_id" => 2,
            //     "mvd_codigo_lote" => "78119-310124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 762,
            //     "mvd_lote_id" => 3,
            //     "mvd_codigo_lote" => "78119-310124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 146,
            //     "mvd_cantidad" => 1436.97,
            //     "mvd_lote_id" => 4,
            //     "mvd_codigo_lote" => 78168,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 147,
            //     "mvd_cantidad" => 3374.05,
            //     "mvd_lote_id" => 5,
            //     "mvd_codigo_lote" => 78169,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 148,
            //     "mvd_cantidad" => 3930.58,
            //     "mvd_lote_id" => 6,
            //     "mvd_codigo_lote" => 78170,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 149,
            //     "mvd_cantidad" => 1597.83,
            //     "mvd_lote_id" => 7,
            //     "mvd_codigo_lote" => 78171,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 152,
            //     "mvd_cantidad" => 1449.23,
            //     "mvd_lote_id" => 8,
            //     "mvd_codigo_lote" => 78172,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 153,
            //     "mvd_cantidad" => 2338.73,
            //     "mvd_lote_id" => 9,
            //     "mvd_codigo_lote" => 78175,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 151,
            //     "mvd_cantidad" => 3686.15,
            //     "mvd_lote_id" => 10,
            //     "mvd_codigo_lote" => 78176,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 3978,
            //     "mvd_lote_id" => 11,
            //     "mvd_codigo_lote" => "59788-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 49230953.76,
            //     "mvd_lote_id" => 12,
            //     "mvd_codigo_lote" => 75681,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 24475036.51,
            //     "mvd_lote_id" => 13,
            //     "mvd_codigo_lote" => 78119,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 44,
            //     "mvd_lote_id" => 14,
            //     "mvd_codigo_lote" => 11,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 5,
            //     "mvd_cantidad" => 66,
            //     "mvd_lote_id" => 15,
            //     "mvd_codigo_lote" => "23-271223=>10",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 6,
            //     "mvd_cantidad" => 570,
            //     "mvd_lote_id" => 16,
            //     "mvd_codigo_lote" => "24-260124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 8,
            //     "mvd_cantidad" => 502,
            //     "mvd_lote_id" => 17,
            //     "mvd_codigo_lote" => "24-300124=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 7,
            //     "mvd_cantidad" => 435,
            //     "mvd_lote_id" => 18,
            //     "mvd_codigo_lote" => "24-300124=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 9,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 19,
            //     "mvd_codigo_lote" => "24-300124=>4",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 10,
            //     "mvd_cantidad" => 154,
            //     "mvd_lote_id" => 20,
            //     "mvd_codigo_lote" => "24-300124=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 12,
            //     "mvd_cantidad" => 102,
            //     "mvd_lote_id" => 21,
            //     "mvd_codigo_lote" => "24-300124=>6",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 1,
            //     "mvd_articulo_id" => 11,
            //     "mvd_cantidad" => 101,
            //     "mvd_lote_id" => 22,
            //     "mvd_codigo_lote" => "24-300124=>7",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 2,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 22728355.03,
            //     "mvd_lote_id" => 23,
            //     "mvd_codigo_lote" => 73796,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 2,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 7382234.97,
            //     "mvd_lote_id" => 24,
            //     "mvd_codigo_lote" => 76148,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 2,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 5585.51,
            //     "mvd_lote_id" => 25,
            //     "mvd_codigo_lote" => 78154,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 2,
            //     "mvd_articulo_id" => 202,
            //     "mvd_cantidad" => 74321.22,
            //     "mvd_lote_id" => 26,
            //     "mvd_codigo_lote" => 78253,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 2,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 232571.23,
            //     "mvd_lote_id" => 27,
            //     "mvd_codigo_lote" => 78334,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 3,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 24962986.87,
            //     "mvd_lote_id" => 28,
            //     "mvd_codigo_lote" => 73531,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 4,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 1434326.82,
            //     "mvd_lote_id" => 29,
            //     "mvd_codigo_lote" => 78145,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 4,
            //     "mvd_articulo_id" => 225,
            //     "mvd_cantidad" => 5000,
            //     "mvd_lote_id" => 30,
            //     "mvd_codigo_lote" => 78294,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 4,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 50013.24,
            //     "mvd_lote_id" => 31,
            //     "mvd_codigo_lote" => 78336,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 4,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 1269352.3,
            //     "mvd_lote_id" => 32,
            //     "mvd_codigo_lote" => 1596,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 4,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 67930217.99,
            //     "mvd_lote_id" => 33,
            //     "mvd_codigo_lote" => 74655,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 8240149.27,
            //     "mvd_lote_id" => 34,
            //     "mvd_codigo_lote" => 78043,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 219,
            //     "mvd_cantidad" => 25000,
            //     "mvd_lote_id" => 35,
            //     "mvd_codigo_lote" => 78216,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 221,
            //     "mvd_cantidad" => 18020,
            //     "mvd_lote_id" => 36,
            //     "mvd_codigo_lote" => 78217,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 219,
            //     "mvd_cantidad" => 20000,
            //     "mvd_lote_id" => 37,
            //     "mvd_codigo_lote" => 78218,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 113713.67,
            //     "mvd_lote_id" => 38,
            //     "mvd_codigo_lote" => 78278,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1389,
            //     "mvd_lote_id" => 39,
            //     "mvd_codigo_lote" => "53619-041123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1117,
            //     "mvd_lote_id" => 40,
            //     "mvd_codigo_lote" => "53619-041123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 113,
            //     "mvd_cantidad" => 6000,
            //     "mvd_lote_id" => 41,
            //     "mvd_codigo_lote" => 78123,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1762,
            //     "mvd_lote_id" => 42,
            //     "mvd_codigo_lote" => "70748-301023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1417,
            //     "mvd_lote_id" => 43,
            //     "mvd_codigo_lote" => "70748-301023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 578,
            //     "mvd_lote_id" => 44,
            //     "mvd_codigo_lote" => "78146-231123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 463,
            //     "mvd_lote_id" => 45,
            //     "mvd_codigo_lote" => "78146-231123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 296918.55,
            //     "mvd_lote_id" => 46,
            //     "mvd_codigo_lote" => 78215,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1031,
            //     "mvd_lote_id" => 47,
            //     "mvd_codigo_lote" => "78215-190124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 5,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 829,
            //     "mvd_lote_id" => 48,
            //     "mvd_codigo_lote" => "78215-190124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 125,
            //     "mvd_lote_id" => 49,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 333,
            //     "mvd_lote_id" => 50,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 202,
            //     "mvd_lote_id" => 51,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 950,
            //     "mvd_lote_id" => 52,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 280,
            //     "mvd_lote_id" => 53,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 54,
            //     "mvd_cantidad" => 180,
            //     "mvd_lote_id" => 54,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 180,
            //     "mvd_lote_id" => 55,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 60,
            //     "mvd_cantidad" => 250,
            //     "mvd_lote_id" => 56,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 22030,
            //     "mvd_lote_id" => 57,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 250,
            //     "mvd_lote_id" => 58,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 71,
            //     "mvd_cantidad" => 35,
            //     "mvd_lote_id" => 59,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 35,
            //     "mvd_lote_id" => 60,
            //     "mvd_codigo_lote" => "66457-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 22842353.54,
            //     "mvd_lote_id" => 61,
            //     "mvd_codigo_lote" => 78065,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 270,
            //     "mvd_lote_id" => 62,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 34,
            //     "mvd_lote_id" => 63,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 304,
            //     "mvd_lote_id" => 64,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 99,
            //     "mvd_lote_id" => 65,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 54,
            //     "mvd_cantidad" => 102,
            //     "mvd_lote_id" => 66,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 102,
            //     "mvd_lote_id" => 67,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 60,
            //     "mvd_cantidad" => 96,
            //     "mvd_lote_id" => 68,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 6965,
            //     "mvd_lote_id" => 69,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 27,
            //     "mvd_lote_id" => 70,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 123,
            //     "mvd_lote_id" => 71,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 71,
            //     "mvd_cantidad" => 22,
            //     "mvd_lote_id" => 72,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 22,
            //     "mvd_lote_id" => 73,
            //     "mvd_codigo_lote" => "78065-080224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 292,
            //     "mvd_lote_id" => 74,
            //     "mvd_codigo_lote" => "78065-190823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 292,
            //     "mvd_lote_id" => 75,
            //     "mvd_codigo_lote" => "78065-190823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 6,
            //     "mvd_lote_id" => 76,
            //     "mvd_codigo_lote" => "78065-190823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 1756,
            //     "mvd_lote_id" => 77,
            //     "mvd_codigo_lote" => "66457-021023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 6,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 1456,
            //     "mvd_lote_id" => 78,
            //     "mvd_codigo_lote" => "66457-021023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 7,
            //     "mvd_articulo_id" => 202,
            //     "mvd_cantidad" => 495967.7,
            //     "mvd_lote_id" => 79,
            //     "mvd_codigo_lote" => 78254,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 7,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 40224731.36,
            //     "mvd_lote_id" => 80,
            //     "mvd_codigo_lote" => 76719,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 189,
            //     "mvd_cantidad" => 2740,
            //     "mvd_lote_id" => 81,
            //     "mvd_codigo_lote" => 78303,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 181,
            //     "mvd_cantidad" => 721,
            //     "mvd_lote_id" => 82,
            //     "mvd_codigo_lote" => 78324,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 171,
            //     "mvd_cantidad" => 500,
            //     "mvd_lote_id" => 83,
            //     "mvd_codigo_lote" => 78325,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 120,
            //     "mvd_cantidad" => 25,
            //     "mvd_lote_id" => 84,
            //     "mvd_codigo_lote" => 78192,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 170,
            //     "mvd_cantidad" => 1204,
            //     "mvd_lote_id" => 85,
            //     "mvd_codigo_lote" => 78088,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 8,
            //     "mvd_articulo_id" => 169,
            //     "mvd_cantidad" => 352,
            //     "mvd_lote_id" => 86,
            //     "mvd_codigo_lote" => 78089,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 9,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 17102695.22,
            //     "mvd_lote_id" => 87,
            //     "mvd_codigo_lote" => 78121,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 10,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 13940188.75,
            //     "mvd_lote_id" => 88,
            //     "mvd_codigo_lote" => 78139,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 800,
            //     "mvd_lote_id" => 89,
            //     "mvd_codigo_lote" => "M5¬78110-061023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 222,
            //     "mvd_lote_id" => 90,
            //     "mvd_codigo_lote" => "M8¬78092-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 710,
            //     "mvd_lote_id" => 91,
            //     "mvd_codigo_lote" => "M27¬78223-290923=>7",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 675,
            //     "mvd_lote_id" => 92,
            //     "mvd_codigo_lote" => "M3¬69902-260623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1046,
            //     "mvd_lote_id" => 93,
            //     "mvd_codigo_lote" => "M3¬78189-070923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 275,
            //     "mvd_lote_id" => 94,
            //     "mvd_codigo_lote" => "M1¬69905-100723=>15",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1534,
            //     "mvd_lote_id" => 95,
            //     "mvd_codigo_lote" => "M13¬77977-301123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 5,
            //     "mvd_cantidad" => 14295,
            //     "mvd_lote_id" => 96,
            //     "mvd_codigo_lote" => "S3¬23-271223=>10",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 11,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1200,
            //     "mvd_lote_id" => 97,
            //     "mvd_codigo_lote" => "S127¬78146-231023=>4",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 121,
            //     "mvd_lote_id" => 98,
            //     "mvd_codigo_lote" => "I4¬57390-080923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 99,
            //     "mvd_codigo_lote" => "I39¬76948-200723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 74,
            //     "mvd_lote_id" => 100,
            //     "mvd_codigo_lote" => "I39¬76948-181023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 101,
            //     "mvd_codigo_lote" => "I39¬76948-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 99,
            //     "mvd_lote_id" => 102,
            //     "mvd_codigo_lote" => "I38¬75754-290823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 91,
            //     "mvd_lote_id" => 103,
            //     "mvd_codigo_lote" => "I38¬75754-291123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 66,
            //     "mvd_lote_id" => 104,
            //     "mvd_codigo_lote" => "I38¬75754-280823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 56,
            //     "mvd_lote_id" => 105,
            //     "mvd_codigo_lote" => "I16¬76149-280723=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 66,
            //     "mvd_lote_id" => 106,
            //     "mvd_codigo_lote" => "I16¬76149-271123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 185,
            //     "mvd_lote_id" => 107,
            //     "mvd_codigo_lote" => "I16¬76149-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 142,
            //     "mvd_lote_id" => 108,
            //     "mvd_codigo_lote" => "S1¬57470-190723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 8,
            //     "mvd_lote_id" => 109,
            //     "mvd_codigo_lote" => "S1¬57470-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 120,
            //     "mvd_lote_id" => 110,
            //     "mvd_codigo_lote" => "S1¬57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 20031,
            //     "mvd_lote_id" => 111,
            //     "mvd_codigo_lote" => "M4¬69901-160223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1028,
            //     "mvd_lote_id" => 112,
            //     "mvd_codigo_lote" => "M27¬78223-280923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 2400,
            //     "mvd_lote_id" => 113,
            //     "mvd_codigo_lote" => "M2¬78207-301123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1296,
            //     "mvd_lote_id" => 114,
            //     "mvd_codigo_lote" => "M8¬78092-140923=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 883,
            //     "mvd_lote_id" => 115,
            //     "mvd_codigo_lote" => "M3¬78189-140923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 2016,
            //     "mvd_lote_id" => 116,
            //     "mvd_codigo_lote" => "M13¬77977-301123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 147,
            //     "mvd_lote_id" => 117,
            //     "mvd_codigo_lote" => "I56¬78134-030823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 300,
            //     "mvd_lote_id" => 118,
            //     "mvd_codigo_lote" => "I56¬78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 20306,
            //     "mvd_lote_id" => 119,
            //     "mvd_codigo_lote" => "S3¬59788-080223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 71,
            //     "mvd_lote_id" => 120,
            //     "mvd_codigo_lote" => "S182¬76915-270723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 121,
            //     "mvd_codigo_lote" => "S182¬76915-270723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 21231,
            //     "mvd_lote_id" => 122,
            //     "mvd_codigo_lote" => "S127¬78146-180823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 717,
            //     "mvd_lote_id" => 123,
            //     "mvd_codigo_lote" => "I50¬76720-190923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 192,
            //     "mvd_lote_id" => 124,
            //     "mvd_codigo_lote" => "S182¬76915-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 42,
            //     "mvd_lote_id" => 125,
            //     "mvd_codigo_lote" => "S1¬77857-271223=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1223,
            //     "mvd_lote_id" => 126,
            //     "mvd_codigo_lote" => "S127¬70748-040523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 446,
            //     "mvd_lote_id" => 127,
            //     "mvd_codigo_lote" => "S127¬78055-260623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 90,
            //     "mvd_lote_id" => 128,
            //     "mvd_codigo_lote" => "I50¬76720-170723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 129,
            //     "mvd_codigo_lote" => "I46¬59180-140623=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 124,
            //     "mvd_lote_id" => 130,
            //     "mvd_codigo_lote" => "I50¬76720-150923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 258,
            //     "mvd_lote_id" => 131,
            //     "mvd_codigo_lote" => "I43¬76748-280723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 34,
            //     "mvd_lote_id" => 132,
            //     "mvd_codigo_lote" => "I43¬76748-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 35,
            //     "mvd_lote_id" => 133,
            //     "mvd_codigo_lote" => "I43¬76748-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 45,
            //     "mvd_lote_id" => 134,
            //     "mvd_codigo_lote" => "I40¬77136-301023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 127,
            //     "mvd_lote_id" => 135,
            //     "mvd_codigo_lote" => "I40¬77136-221223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 14,
            //     "mvd_lote_id" => 136,
            //     "mvd_codigo_lote" => "I40¬77136-261023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 277,
            //     "mvd_lote_id" => 137,
            //     "mvd_codigo_lote" => "I40¬77136-270723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 12,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 553,
            //     "mvd_lote_id" => 138,
            //     "mvd_codigo_lote" => "I4¬57390-260723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 13,
            //     "mvd_articulo_id" => 170,
            //     "mvd_cantidad" => 500,
            //     "mvd_lote_id" => 139,
            //     "mvd_codigo_lote" => 78136,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 13,
            //     "mvd_articulo_id" => 172,
            //     "mvd_cantidad" => 1509,
            //     "mvd_lote_id" => 140,
            //     "mvd_codigo_lote" => 78166,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 13,
            //     "mvd_articulo_id" => 173,
            //     "mvd_cantidad" => 1451,
            //     "mvd_lote_id" => 141,
            //     "mvd_codigo_lote" => 78174,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 13,
            //     "mvd_articulo_id" => 169,
            //     "mvd_cantidad" => 511,
            //     "mvd_lote_id" => 142,
            //     "mvd_codigo_lote" => 78190,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 2170,
            //     "mvd_lote_id" => 143,
            //     "mvd_codigo_lote" => "S127¬78146-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 125,
            //     "mvd_lote_id" => 144,
            //     "mvd_codigo_lote" => "S127¬78146-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 371,
            //     "mvd_lote_id" => 145,
            //     "mvd_codigo_lote" => "S182¬57836-290822=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 165,
            //     "mvd_lote_id" => 146,
            //     "mvd_codigo_lote" => "S182¬76915-170823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 191,
            //     "mvd_cantidad" => 2350,
            //     "mvd_lote_id" => 147,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 193,
            //     "mvd_cantidad" => 4700,
            //     "mvd_lote_id" => 148,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 630,
            //     "mvd_lote_id" => 149,
            //     "mvd_codigo_lote" => "I50¬76720-150823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 259,
            //     "mvd_lote_id" => 150,
            //     "mvd_codigo_lote" => "I56¬57854-271023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 574,
            //     "mvd_lote_id" => 151,
            //     "mvd_codigo_lote" => "I4¬57390-170823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 105,
            //     "mvd_lote_id" => 152,
            //     "mvd_codigo_lote" => "I43¬76748-180923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 487,
            //     "mvd_lote_id" => 153,
            //     "mvd_codigo_lote" => "M44¬72343-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 10780,
            //     "mvd_lote_id" => 154,
            //     "mvd_codigo_lote" => "M44¬72343-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 500,
            //     "mvd_lote_id" => 155,
            //     "mvd_codigo_lote" => "M8¬78092-270923=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 56,
            //     "mvd_lote_id" => 156,
            //     "mvd_codigo_lote" => "I16¬76149-270723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 228,
            //     "mvd_lote_id" => 157,
            //     "mvd_codigo_lote" => "I38¬75754-300823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 50,
            //     "mvd_lote_id" => 158,
            //     "mvd_codigo_lote" => "I39¬76948-140723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 83,
            //     "mvd_lote_id" => 159,
            //     "mvd_codigo_lote" => "I4¬44963-060723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 14,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 13,
            //     "mvd_lote_id" => 160,
            //     "mvd_codigo_lote" => "S1¬57470-220823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 15,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 170,
            //     "mvd_lote_id" => 161,
            //     "mvd_codigo_lote" => "S182¬76915-170823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 15,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 43,
            //     "mvd_lote_id" => 162,
            //     "mvd_codigo_lote" => "I4¬57390-300123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 15,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 163,
            //     "mvd_codigo_lote" => "I4¬57390-300123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 15,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 771,
            //     "mvd_lote_id" => 164,
            //     "mvd_codigo_lote" => "S1¬57470-080823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 15,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 182,
            //     "mvd_lote_id" => 165,
            //     "mvd_codigo_lote" => "I38¬75754-090623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 400,
            //     "mvd_lote_id" => 166,
            //     "mvd_codigo_lote" => "S182¬76915-170823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 115,
            //     "mvd_lote_id" => 167,
            //     "mvd_codigo_lote" => "I104¬76760-280723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 169,
            //     "mvd_lote_id" => 168,
            //     "mvd_codigo_lote" => "I39¬76948-140923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 700,
            //     "mvd_lote_id" => 169,
            //     "mvd_codigo_lote" => "I4¬57390-080923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 200,
            //     "mvd_lote_id" => 170,
            //     "mvd_codigo_lote" => "I40¬77136-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 1196,
            //     "mvd_lote_id" => 171,
            //     "mvd_codigo_lote" => "I50¬76720-170623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 22,
            //     "mvd_lote_id" => 172,
            //     "mvd_codigo_lote" => "I50¬76720-251123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 355,
            //     "mvd_lote_id" => 173,
            //     "mvd_codigo_lote" => "I56¬57854-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 2166,
            //     "mvd_lote_id" => 174,
            //     "mvd_codigo_lote" => "I56¬57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 191,
            //     "mvd_cantidad" => 3700,
            //     "mvd_lote_id" => 175,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 193,
            //     "mvd_cantidad" => 12175,
            //     "mvd_lote_id" => 176,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 295,
            //     "mvd_lote_id" => 177,
            //     "mvd_codigo_lote" => "M8¬78092-200923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 16,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 153,
            //     "mvd_lote_id" => 178,
            //     "mvd_codigo_lote" => "S1¬57470-150823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1200,
            //     "mvd_lote_id" => 179,
            //     "mvd_codigo_lote" => "S127¬78146-210823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 120,
            //     "mvd_lote_id" => 180,
            //     "mvd_codigo_lote" => "S182¬76915-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 37,
            //     "mvd_cantidad" => 1610,
            //     "mvd_lote_id" => 181,
            //     "mvd_codigo_lote" => "I4¬57390-200623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 253,
            //     "mvd_lote_id" => 182,
            //     "mvd_codigo_lote" => "I40¬77136-261023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 273,
            //     "mvd_lote_id" => 183,
            //     "mvd_codigo_lote" => "I43¬76748-280623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 630,
            //     "mvd_lote_id" => 184,
            //     "mvd_codigo_lote" => "I50¬76720-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 388,
            //     "mvd_lote_id" => 185,
            //     "mvd_codigo_lote" => "I56¬57854-280223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1054,
            //     "mvd_lote_id" => 186,
            //     "mvd_codigo_lote" => "M27¬78223-211023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 4782,
            //     "mvd_lote_id" => 187,
            //     "mvd_codigo_lote" => "M44¬72343-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 465,
            //     "mvd_lote_id" => 188,
            //     "mvd_codigo_lote" => "M8¬53347-060323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 163,
            //     "mvd_lote_id" => 189,
            //     "mvd_codigo_lote" => "M8¬78092-280623=>9",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 1152,
            //     "mvd_lote_id" => 190,
            //     "mvd_codigo_lote" => "S1¬57470-220523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 98,
            //     "mvd_lote_id" => 191,
            //     "mvd_codigo_lote" => "S1¬57470-260923=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 609,
            //     "mvd_lote_id" => 192,
            //     "mvd_codigo_lote" => "I104¬76760-160623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 279,
            //     "mvd_lote_id" => 193,
            //     "mvd_codigo_lote" => "I104¬76760-200723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 258,
            //     "mvd_lote_id" => 194,
            //     "mvd_codigo_lote" => "I16¬76149-270723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 17,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 308,
            //     "mvd_lote_id" => 195,
            //     "mvd_codigo_lote" => "I39¬76948-181023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 18,
            //     "mvd_articulo_id" => 198,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 196,
            //     "mvd_codigo_lote" => 78164,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 18,
            //     "mvd_articulo_id" => 199,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 197,
            //     "mvd_codigo_lote" => 78165,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 19,
            //     "mvd_articulo_id" => 200,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 198,
            //     "mvd_codigo_lote" => 78142,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 19,
            //     "mvd_articulo_id" => 198,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 199,
            //     "mvd_codigo_lote" => 78163,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 20,
            //     "mvd_articulo_id" => 199,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 200,
            //     "mvd_codigo_lote" => 78152,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 21,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 462359.39,
            //     "mvd_lote_id" => 201,
            //     "mvd_codigo_lote" => 78140,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 408,
            //     "mvd_lote_id" => 202,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 408,
            //     "mvd_lote_id" => 203,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 126,
            //     "mvd_lote_id" => 204,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 89,
            //     "mvd_lote_id" => 205,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 11568.3,
            //     "mvd_lote_id" => 206,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 116,
            //     "mvd_lote_id" => 207,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 116,
            //     "mvd_lote_id" => 208,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 209,
            //     "mvd_codigo_lote" => "76948-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 223,
            //     "mvd_lote_id" => 210,
            //     "mvd_codigo_lote" => "76948-150623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 608,
            //     "mvd_lote_id" => 211,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 878,
            //     "mvd_lote_id" => 212,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 103,
            //     "mvd_lote_id" => 213,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 134,
            //     "mvd_lote_id" => 214,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 285,
            //     "mvd_lote_id" => 215,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 15849.84,
            //     "mvd_lote_id" => 216,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 436,
            //     "mvd_lote_id" => 217,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 218,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 22,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 9,
            //     "mvd_lote_id" => 219,
            //     "mvd_codigo_lote" => "45201-240323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 23,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1200,
            //     "mvd_lote_id" => 220,
            //     "mvd_codigo_lote" => "S127¬78146-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 221,
            //     "mvd_codigo_lote" => "69903-220723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 14559,
            //     "mvd_lote_id" => 222,
            //     "mvd_codigo_lote" => "69903-250723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 263,
            //     "mvd_lote_id" => 223,
            //     "mvd_codigo_lote" => "69903-260723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 410,
            //     "mvd_lote_id" => 224,
            //     "mvd_codigo_lote" => "78148-260923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 22,
            //     "mvd_lote_id" => 225,
            //     "mvd_codigo_lote" => 78207,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 24,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 4847,
            //     "mvd_lote_id" => 226,
            //     "mvd_codigo_lote" => "78207-271223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 661,
            //     "mvd_lote_id" => 227,
            //     "mvd_codigo_lote" => "I104¬76760-160823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 78,
            //     "mvd_lote_id" => 228,
            //     "mvd_codigo_lote" => "I4¬57390-270923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 229,
            //     "mvd_codigo_lote" => "I40¬77136-231023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 106,
            //     "mvd_lote_id" => 230,
            //     "mvd_codigo_lote" => "I43¬76748-261023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 100,
            //     "mvd_lote_id" => 231,
            //     "mvd_codigo_lote" => "I50¬76720-211223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 300,
            //     "mvd_lote_id" => 232,
            //     "mvd_codigo_lote" => "I50¬76720-260723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 268,
            //     "mvd_lote_id" => 233,
            //     "mvd_codigo_lote" => "I56¬57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 157,
            //     "mvd_lote_id" => 234,
            //     "mvd_codigo_lote" => "I56¬78134-100823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 33,
            //     "mvd_cantidad" => 805,
            //     "mvd_lote_id" => 235,
            //     "mvd_codigo_lote" => "S1¬48313-160223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 187,
            //     "mvd_lote_id" => 236,
            //     "mvd_codigo_lote" => "S182¬76915-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 200,
            //     "mvd_lote_id" => 237,
            //     "mvd_codigo_lote" => "I16¬76149-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 151,
            //     "mvd_lote_id" => 238,
            //     "mvd_codigo_lote" => "I38¬75754-061223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 25,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 37,
            //     "mvd_lote_id" => 239,
            //     "mvd_codigo_lote" => "I4¬57390-100523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 180,
            //     "mvd_lote_id" => 240,
            //     "mvd_codigo_lote" => "S127¬70748-220623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1020,
            //     "mvd_lote_id" => 241,
            //     "mvd_codigo_lote" => "S127¬78055-260623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 452,
            //     "mvd_lote_id" => 242,
            //     "mvd_codigo_lote" => "S127¬78146-261023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 41,
            //     "mvd_lote_id" => 243,
            //     "mvd_codigo_lote" => "S182¬76915-170823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 521,
            //     "mvd_lote_id" => 244,
            //     "mvd_codigo_lote" => "S256¬78039-111223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 76,
            //     "mvd_lote_id" => 245,
            //     "mvd_codigo_lote" => "I40¬77136-041223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 34,
            //     "mvd_lote_id" => 246,
            //     "mvd_codigo_lote" => "I40¬77136-271123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 118,
            //     "mvd_lote_id" => 247,
            //     "mvd_codigo_lote" => "I43¬76748-180923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 373,
            //     "mvd_lote_id" => 248,
            //     "mvd_codigo_lote" => "I50¬76720-140623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 620,
            //     "mvd_lote_id" => 249,
            //     "mvd_codigo_lote" => "I50¬76720-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 569,
            //     "mvd_lote_id" => 250,
            //     "mvd_codigo_lote" => "I56¬57854-300623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 580,
            //     "mvd_lote_id" => 251,
            //     "mvd_codigo_lote" => "M13¬78213-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 3436,
            //     "mvd_lote_id" => 252,
            //     "mvd_codigo_lote" => "M44¬72343-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 120,
            //     "mvd_lote_id" => 253,
            //     "mvd_codigo_lote" => "I16¬76149-190723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 254,
            //     "mvd_codigo_lote" => "I38¬75754-180823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 60,
            //     "mvd_lote_id" => 255,
            //     "mvd_codigo_lote" => "I39¬76948-270923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 69,
            //     "mvd_lote_id" => 256,
            //     "mvd_codigo_lote" => "I4¬57390-080923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 37,
            //     "mvd_cantidad" => 961,
            //     "mvd_lote_id" => 257,
            //     "mvd_codigo_lote" => "I4¬57390-090523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 26,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 407,
            //     "mvd_lote_id" => 258,
            //     "mvd_codigo_lote" => "S1¬57470-110923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 673,
            //     "mvd_lote_id" => 259,
            //     "mvd_codigo_lote" => "M44¬72343-220623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1000,
            //     "mvd_lote_id" => 260,
            //     "mvd_codigo_lote" => "S127¬78146-210823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 588,
            //     "mvd_lote_id" => 261,
            //     "mvd_codigo_lote" => "M5¬70416-290623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 3042,
            //     "mvd_lote_id" => 262,
            //     "mvd_codigo_lote" => "M5¬78069-280623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 410,
            //     "mvd_lote_id" => 263,
            //     "mvd_codigo_lote" => "M4¬69901-310523=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 410,
            //     "mvd_lote_id" => 264,
            //     "mvd_codigo_lote" => "M2¬69903-260723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 27,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 265,
            //     "mvd_codigo_lote" => "M3¬69902-250423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 12160,
            //     "mvd_lote_id" => 266,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 65,
            //     "mvd_lote_id" => 267,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 65,
            //     "mvd_lote_id" => 268,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 2419132.13,
            //     "mvd_lote_id" => 269,
            //     "mvd_codigo_lote" => 76915,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 270,
            //     "mvd_codigo_lote" => "76915-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 271,
            //     "mvd_codigo_lote" => "76915-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 54,
            //     "mvd_cantidad" => 56,
            //     "mvd_lote_id" => 272,
            //     "mvd_codigo_lote" => "76915-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 300,
            //     "mvd_lote_id" => 273,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 300,
            //     "mvd_lote_id" => 274,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 600,
            //     "mvd_lote_id" => 275,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 76,
            //     "mvd_lote_id" => 276,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 49,
            //     "mvd_lote_id" => 277,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 28,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 49,
            //     "mvd_lote_id" => 278,
            //     "mvd_codigo_lote" => "76915-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 1274,
            //     "mvd_lote_id" => 279,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 1274,
            //     "mvd_lote_id" => 280,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 271,
            //     "mvd_lote_id" => 281,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 168,
            //     "mvd_lote_id" => 282,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 27429.11,
            //     "mvd_lote_id" => 283,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 291,
            //     "mvd_lote_id" => 284,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 291,
            //     "mvd_lote_id" => 285,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 15,
            //     "mvd_lote_id" => 286,
            //     "mvd_codigo_lote" => "57390-011223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 68,
            //     "mvd_lote_id" => 287,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 466,
            //     "mvd_lote_id" => 288,
            //     "mvd_codigo_lote" => "57390-141123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 466,
            //     "mvd_lote_id" => 289,
            //     "mvd_codigo_lote" => "57390-141123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 44,
            //     "mvd_cantidad" => 1032,
            //     "mvd_lote_id" => 290,
            //     "mvd_codigo_lote" => "57390-220623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 35,
            //     "mvd_cantidad" => 620,
            //     "mvd_lote_id" => 291,
            //     "mvd_codigo_lote" => "57390-291123=>4",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 220,
            //     "mvd_lote_id" => 292,
            //     "mvd_codigo_lote" => "57390-311023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 7,
            //     "mvd_lote_id" => 293,
            //     "mvd_codigo_lote" => "57390-311023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 3429445.64,
            //     "mvd_lote_id" => 294,
            //     "mvd_codigo_lote" => 76161,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 517,
            //     "mvd_lote_id" => 295,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 517,
            //     "mvd_lote_id" => 296,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 117,
            //     "mvd_lote_id" => 297,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 39,
            //     "mvd_lote_id" => 298,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 39,
            //     "mvd_lote_id" => 299,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 11235.35,
            //     "mvd_lote_id" => 300,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 99,
            //     "mvd_lote_id" => 301,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 99,
            //     "mvd_lote_id" => 302,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 10,
            //     "mvd_lote_id" => 303,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 10,
            //     "mvd_lote_id" => 304,
            //     "mvd_codigo_lote" => "76161-090224=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 15,
            //     "mvd_lote_id" => 305,
            //     "mvd_codigo_lote" => "76161-161223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 196,
            //     "mvd_lote_id" => 306,
            //     "mvd_codigo_lote" => "76161-260124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 68,
            //     "mvd_lote_id" => 307,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 11,
            //     "mvd_lote_id" => 308,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 1820.29,
            //     "mvd_lote_id" => 309,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 16,
            //     "mvd_lote_id" => 310,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 29,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 311,
            //     "mvd_codigo_lote" => "44963-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 5003.51,
            //     "mvd_lote_id" => 312,
            //     "mvd_codigo_lote" => 77904,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 1647,
            //     "mvd_lote_id" => 313,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 1647,
            //     "mvd_lote_id" => 314,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 453,
            //     "mvd_lote_id" => 315,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 134,
            //     "mvd_lote_id" => 316,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 134,
            //     "mvd_lote_id" => 317,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 50989.6,
            //     "mvd_lote_id" => 318,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 384,
            //     "mvd_lote_id" => 319,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 30,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 384,
            //     "mvd_lote_id" => 320,
            //     "mvd_codigo_lote" => "45195-121223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 31,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 2310426.17,
            //     "mvd_lote_id" => 321,
            //     "mvd_codigo_lote" => 78281,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 31,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 3301380.68,
            //     "mvd_lote_id" => 322,
            //     "mvd_codigo_lote" => 78322,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 125,
            //     "mvd_lote_id" => 323,
            //     "mvd_codigo_lote" => "77136-201223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 324,
            //     "mvd_codigo_lote" => "77136-221223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 231,
            //     "mvd_lote_id" => 325,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 231,
            //     "mvd_lote_id" => 326,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 130,
            //     "mvd_lote_id" => 327,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 70,
            //     "mvd_lote_id" => 328,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 70,
            //     "mvd_lote_id" => 329,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 6388,
            //     "mvd_lote_id" => 330,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 90,
            //     "mvd_lote_id" => 331,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 90,
            //     "mvd_lote_id" => 332,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 1,
            //     "mvd_lote_id" => 333,
            //     "mvd_codigo_lote" => "77136-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 905,
            //     "mvd_lote_id" => 334,
            //     "mvd_codigo_lote" => "77136-291123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 32,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 905,
            //     "mvd_lote_id" => 335,
            //     "mvd_codigo_lote" => "77136-291123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 1171769.12,
            //     "mvd_lote_id" => 336,
            //     "mvd_codigo_lote" => 78304,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 2476,
            //     "mvd_lote_id" => 337,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 338,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 2626,
            //     "mvd_lote_id" => 339,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 559,
            //     "mvd_lote_id" => 340,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 112,
            //     "mvd_lote_id" => 341,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 112,
            //     "mvd_lote_id" => 342,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 55750,
            //     "mvd_lote_id" => 343,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 320,
            //     "mvd_lote_id" => 344,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 320,
            //     "mvd_lote_id" => 345,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 346,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 33,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 8,
            //     "mvd_lote_id" => 347,
            //     "mvd_codigo_lote" => "78304-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 34,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 9615456.01,
            //     "mvd_lote_id" => 348,
            //     "mvd_codigo_lote" => 78144,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 34,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 327165.72,
            //     "mvd_lote_id" => 349,
            //     "mvd_codigo_lote" => 78162,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 316,
            //     "mvd_lote_id" => 350,
            //     "mvd_codigo_lote" => "S1¬57470-230823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 159,
            //     "mvd_lote_id" => 351,
            //     "mvd_codigo_lote" => "S3¬59788-220523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 429,
            //     "mvd_lote_id" => 352,
            //     "mvd_codigo_lote" => "S3¬77908-120723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 990,
            //     "mvd_lote_id" => 353,
            //     "mvd_codigo_lote" => "S3¬78119-140923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 54,
            //     "mvd_lote_id" => 354,
            //     "mvd_codigo_lote" => "I46¬59180-200723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 35,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 400,
            //     "mvd_lote_id" => 355,
            //     "mvd_codigo_lote" => "I56¬78134-030823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 36,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 8,
            //     "mvd_lote_id" => 356,
            //     "mvd_codigo_lote" => "56800-290323=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 36,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 5400,
            //     "mvd_lote_id" => 357,
            //     "mvd_codigo_lote" => "78223-310124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 36,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 4565,
            //     "mvd_lote_id" => 358,
            //     "mvd_codigo_lote" => "78223-310124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 36,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 359,
            //     "mvd_codigo_lote" => 78223,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 36,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 6278,
            //     "mvd_lote_id" => 360,
            //     "mvd_codigo_lote" => "74231-300523=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 8302,
            //     "mvd_lote_id" => 361,
            //     "mvd_codigo_lote" => 78206,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 43306,
            //     "mvd_lote_id" => 362,
            //     "mvd_codigo_lote" => "78069-280623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 17274,
            //     "mvd_lote_id" => 363,
            //     "mvd_codigo_lote" => "78110-060723=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 7140,
            //     "mvd_lote_id" => 364,
            //     "mvd_codigo_lote" => "78206-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 9411,
            //     "mvd_lote_id" => 365,
            //     "mvd_codigo_lote" => "78206-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 37,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 41168,
            //     "mvd_lote_id" => 366,
            //     "mvd_codigo_lote" => "70416-300623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 38,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 587,
            //     "mvd_lote_id" => 367,
            //     "mvd_codigo_lote" => "69905-180723=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 38,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 1324617,
            //     "mvd_lote_id" => 368,
            //     "mvd_codigo_lote" => 78211,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 38,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 2730,
            //     "mvd_lote_id" => 369,
            //     "mvd_codigo_lote" => "78211-290923=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 235853,
            //     "mvd_lote_id" => 370,
            //     "mvd_codigo_lote" => 78213,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 26261,
            //     "mvd_lote_id" => 371,
            //     "mvd_codigo_lote" => "78213-271223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 22200,
            //     "mvd_lote_id" => 372,
            //     "mvd_codigo_lote" => "78213-271223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 34,
            //     "mvd_lote_id" => 373,
            //     "mvd_codigo_lote" => 78228,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 24,
            //     "mvd_lote_id" => 374,
            //     "mvd_codigo_lote" => "78228-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 3796,
            //     "mvd_lote_id" => 375,
            //     "mvd_codigo_lote" => "78228-151123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 1668,
            //     "mvd_lote_id" => 376,
            //     "mvd_codigo_lote" => 77977,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 365,
            //     "mvd_lote_id" => 377,
            //     "mvd_codigo_lote" => "77977-241123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 515,
            //     "mvd_lote_id" => 378,
            //     "mvd_codigo_lote" => "77977-301123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1930,
            //     "mvd_lote_id" => 379,
            //     "mvd_codigo_lote" => "69906-050723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1071,
            //     "mvd_lote_id" => 380,
            //     "mvd_codigo_lote" => "69906-050723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 39,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 4894,
            //     "mvd_lote_id" => 381,
            //     "mvd_codigo_lote" => "69906-240623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 2641,
            //     "mvd_lote_id" => 382,
            //     "mvd_codigo_lote" => "72343-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 670,
            //     "mvd_lote_id" => 383,
            //     "mvd_codigo_lote" => "72343-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 6801,
            //     "mvd_lote_id" => 384,
            //     "mvd_codigo_lote" => "72343-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 19,
            //     "mvd_lote_id" => 385,
            //     "mvd_codigo_lote" => 78293,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 12960,
            //     "mvd_lote_id" => 386,
            //     "mvd_codigo_lote" => "78293-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 40,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 10956,
            //     "mvd_lote_id" => 387,
            //     "mvd_codigo_lote" => "78293-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 977,
            //     "mvd_lote_id" => 388,
            //     "mvd_codigo_lote" => "69902-300623=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 578,
            //     "mvd_lote_id" => 389,
            //     "mvd_codigo_lote" => "69902-300623=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 623527,
            //     "mvd_lote_id" => 390,
            //     "mvd_codigo_lote" => 78209,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 400,
            //     "mvd_lote_id" => 391,
            //     "mvd_codigo_lote" => "78209-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 338,
            //     "mvd_lote_id" => 392,
            //     "mvd_codigo_lote" => "78209-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 41,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 46,
            //     "mvd_lote_id" => 393,
            //     "mvd_codigo_lote" => "78094-300623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 42,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 18619101.79,
            //     "mvd_lote_id" => 394,
            //     "mvd_codigo_lote" => 78105,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 1288,
            //     "mvd_lote_id" => 395,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 178,
            //     "mvd_lote_id" => 396,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 108,
            //     "mvd_lote_id" => 397,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 14686,
            //     "mvd_lote_id" => 398,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 143,
            //     "mvd_lote_id" => 399,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 400,
            //     "mvd_codigo_lote" => "57364-100223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 512,
            //     "mvd_lote_id" => 401,
            //     "mvd_codigo_lote" => "76720-210923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 512,
            //     "mvd_lote_id" => 402,
            //     "mvd_codigo_lote" => "76720-210923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 330,
            //     "mvd_lote_id" => 403,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 30,
            //     "mvd_lote_id" => 404,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 360,
            //     "mvd_lote_id" => 405,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 97,
            //     "mvd_lote_id" => 406,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 60,
            //     "mvd_lote_id" => 407,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 60,
            //     "mvd_lote_id" => 408,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 6902,
            //     "mvd_lote_id" => 409,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 144,
            //     "mvd_lote_id" => 410,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 144,
            //     "mvd_lote_id" => 411,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 412,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 43,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 413,
            //     "mvd_codigo_lote" => "76720-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 47,
            //     "mvd_lote_id" => 414,
            //     "mvd_codigo_lote" => "59180-151223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 415,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 416,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 417,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 418,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 419,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 420,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 421,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 422,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 423,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 19385,
            //     "mvd_lote_id" => 424,
            //     "mvd_codigo_lote" => "59180-261223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 896,
            //     "mvd_lote_id" => 425,
            //     "mvd_codigo_lote" => "59180-310723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 44,
            //     "mvd_articulo_id" => 71,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 426,
            //     "mvd_codigo_lote" => "59180-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 45,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 338420,
            //     "mvd_lote_id" => 427,
            //     "mvd_codigo_lote" => 12,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 46,
            //     "mvd_articulo_id" => 193,
            //     "mvd_cantidad" => 500,
            //     "mvd_lote_id" => 428,
            //     "mvd_codigo_lote" => "71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 47,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 157386.24,
            //     "mvd_lote_id" => 429,
            //     "mvd_codigo_lote" => 6675,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 1072,
            //     "mvd_lote_id" => 430,
            //     "mvd_codigo_lote" => "58253-080223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 431,
            //     "mvd_codigo_lote" => "58253-160223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 37,
            //     "mvd_lote_id" => 432,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 170,
            //     "mvd_lote_id" => 433,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 65,
            //     "mvd_lote_id" => 434,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 41,
            //     "mvd_lote_id" => 435,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 5836,
            //     "mvd_lote_id" => 436,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 63,
            //     "mvd_lote_id" => 437,
            //     "mvd_codigo_lote" => "58253-230223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 89,
            //     "mvd_lote_id" => 438,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 429,
            //     "mvd_lote_id" => 439,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 518,
            //     "mvd_lote_id" => 440,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 121,
            //     "mvd_lote_id" => 441,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 67,
            //     "mvd_lote_id" => 442,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 9966,
            //     "mvd_lote_id" => 443,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 139,
            //     "mvd_lote_id" => 444,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 139,
            //     "mvd_lote_id" => 445,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 446,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 447,
            //     "mvd_codigo_lote" => "78187-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 30,
            //     "mvd_lote_id" => 448,
            //     "mvd_codigo_lote" => "78187-271023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 30,
            //     "mvd_lote_id" => 449,
            //     "mvd_codigo_lote" => "78187-271023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 327895.16,
            //     "mvd_lote_id" => 450,
            //     "mvd_codigo_lote" => 76760,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 2030,
            //     "mvd_lote_id" => 451,
            //     "mvd_codigo_lote" => "76760-220623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 699,
            //     "mvd_lote_id" => 452,
            //     "mvd_codigo_lote" => "76760-220623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 209,
            //     "mvd_lote_id" => 453,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 12,
            //     "mvd_lote_id" => 454,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 221,
            //     "mvd_lote_id" => 455,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 64,
            //     "mvd_lote_id" => 456,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 46,
            //     "mvd_lote_id" => 457,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 46,
            //     "mvd_lote_id" => 458,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 4592,
            //     "mvd_lote_id" => 459,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 77,
            //     "mvd_lote_id" => 460,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 77,
            //     "mvd_lote_id" => 461,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 1,
            //     "mvd_lote_id" => 462,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 48,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 5,
            //     "mvd_lote_id" => 463,
            //     "mvd_codigo_lote" => "76760-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 4540,
            //     "mvd_lote_id" => 464,
            //     "mvd_codigo_lote" => "78134-100823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 5955,
            //     "mvd_lote_id" => 465,
            //     "mvd_codigo_lote" => "78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 333,
            //     "mvd_lote_id" => 466,
            //     "mvd_codigo_lote" => "78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 164404,
            //     "mvd_lote_id" => 467,
            //     "mvd_codigo_lote" => "78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 975,
            //     "mvd_lote_id" => 468,
            //     "mvd_codigo_lote" => "78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 74,
            //     "mvd_lote_id" => 469,
            //     "mvd_codigo_lote" => "78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 5320,
            //     "mvd_lote_id" => 470,
            //     "mvd_codigo_lote" => "57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 1722,
            //     "mvd_lote_id" => 471,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 1722,
            //     "mvd_lote_id" => 472,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 445,
            //     "mvd_lote_id" => 473,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 232,
            //     "mvd_lote_id" => 474,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 232,
            //     "mvd_lote_id" => 475,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 48434,
            //     "mvd_lote_id" => 476,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 436,
            //     "mvd_lote_id" => 477,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 436,
            //     "mvd_lote_id" => 478,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 36,
            //     "mvd_lote_id" => 479,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 36,
            //     "mvd_lote_id" => 480,
            //     "mvd_codigo_lote" => "57854-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 1860,
            //     "mvd_lote_id" => 481,
            //     "mvd_codigo_lote" => "57854-301123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 49,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 296,
            //     "mvd_lote_id" => 482,
            //     "mvd_codigo_lote" => "57854-301123=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 50,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 470000,
            //     "mvd_lote_id" => 483,
            //     "mvd_codigo_lote" => 78316,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 2625001.63,
            //     "mvd_lote_id" => 484,
            //     "mvd_codigo_lote" => 78039,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 1565,
            //     "mvd_lote_id" => 485,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 59,
            //     "mvd_lote_id" => 486,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 1624,
            //     "mvd_lote_id" => 487,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 312,
            //     "mvd_lote_id" => 488,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 221,
            //     "mvd_lote_id" => 489,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 221,
            //     "mvd_lote_id" => 490,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 31151,
            //     "mvd_lote_id" => 491,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 272,
            //     "mvd_lote_id" => 492,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 51,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 272,
            //     "mvd_lote_id" => 493,
            //     "mvd_codigo_lote" => "78039-291223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 52,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 1765000,
            //     "mvd_lote_id" => 494,
            //     "mvd_codigo_lote" => 78214,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 52,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 900000,
            //     "mvd_lote_id" => 495,
            //     "mvd_codigo_lote" => 78267,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 24,
            //     "mvd_cantidad" => 3243,
            //     "mvd_lote_id" => 496,
            //     "mvd_codigo_lote" => "57470-050723=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 37,
            //     "mvd_cantidad" => 2484,
            //     "mvd_lote_id" => 497,
            //     "mvd_codigo_lote" => "57470-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 44,
            //     "mvd_cantidad" => 7590,
            //     "mvd_lote_id" => 498,
            //     "mvd_codigo_lote" => "57470-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 1452,
            //     "mvd_lote_id" => 499,
            //     "mvd_codigo_lote" => "57470-250523=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 364,
            //     "mvd_lote_id" => 500,
            //     "mvd_codigo_lote" => "57470-250523=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 3883592.69,
            //     "mvd_lote_id" => 501,
            //     "mvd_codigo_lote" => 77857,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 1528,
            //     "mvd_lote_id" => 502,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 20,
            //     "mvd_lote_id" => 503,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 1548,
            //     "mvd_lote_id" => 504,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 238,
            //     "mvd_lote_id" => 505,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 17,
            //     "mvd_lote_id" => 506,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 180,
            //     "mvd_lote_id" => 507,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 28472,
            //     "mvd_lote_id" => 508,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 151,
            //     "mvd_lote_id" => 509,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 151,
            //     "mvd_lote_id" => 510,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 52,
            //     "mvd_lote_id" => 511,
            //     "mvd_codigo_lote" => "57470-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 339,
            //     "mvd_lote_id" => 512,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 339,
            //     "mvd_lote_id" => 513,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 55,
            //     "mvd_lote_id" => 514,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 24,
            //     "mvd_lote_id" => 515,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 64,
            //     "mvd_lote_id" => 516,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 6510,
            //     "mvd_lote_id" => 517,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 48,
            //     "mvd_lote_id" => 518,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 48,
            //     "mvd_lote_id" => 519,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 15,
            //     "mvd_lote_id" => 520,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 15,
            //     "mvd_lote_id" => 521,
            //     "mvd_codigo_lote" => "77857-271223=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 44,
            //     "mvd_cantidad" => 598,
            //     "mvd_lote_id" => 522,
            //     "mvd_codigo_lote" => "48313-050423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 168,
            //     "mvd_lote_id" => 523,
            //     "mvd_codigo_lote" => "48313-130423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 23,
            //     "mvd_lote_id" => 524,
            //     "mvd_codigo_lote" => "48313-130423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 3210,
            //     "mvd_lote_id" => 525,
            //     "mvd_codigo_lote" => "48313-130423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 17,
            //     "mvd_lote_id" => 526,
            //     "mvd_codigo_lote" => "48313-130423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 10,
            //     "mvd_lote_id" => 527,
            //     "mvd_codigo_lote" => "48313-130423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 25,
            //     "mvd_cantidad" => 2124,
            //     "mvd_lote_id" => 528,
            //     "mvd_codigo_lote" => "48313-220223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 33,
            //     "mvd_cantidad" => 2124,
            //     "mvd_lote_id" => 529,
            //     "mvd_codigo_lote" => "48313-220223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 24,
            //     "mvd_cantidad" => 1250,
            //     "mvd_lote_id" => 530,
            //     "mvd_codigo_lote" => "48313-270223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 1424,
            //     "mvd_lote_id" => 531,
            //     "mvd_codigo_lote" => "48313-280323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 53,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 223484.95,
            //     "mvd_lote_id" => 532,
            //     "mvd_codigo_lote" => 5,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 54,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 1623192.34,
            //     "mvd_lote_id" => 533,
            //     "mvd_codigo_lote" => 78197,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 55,
            //     "mvd_articulo_id" => 131,
            //     "mvd_cantidad" => 17051175.95,
            //     "mvd_lote_id" => 534,
            //     "mvd_codigo_lote" => 74349,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 55,
            //     "mvd_articulo_id" => 116,
            //     "mvd_cantidad" => 4120,
            //     "mvd_lote_id" => 535,
            //     "mvd_codigo_lote" => 78097,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 37,
            //     "mvd_cantidad" => 1300,
            //     "mvd_lote_id" => 536,
            //     "mvd_codigo_lote" => "I4¬57390-200623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 219,
            //     "mvd_lote_id" => 537,
            //     "mvd_codigo_lote" => "I4¬57390-210923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 40,
            //     "mvd_lote_id" => 538,
            //     "mvd_codigo_lote" => "I40¬77136-150923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 35,
            //     "mvd_lote_id" => 539,
            //     "mvd_codigo_lote" => "I43¬76748-050723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 540,
            //     "mvd_codigo_lote" => "I46¬59180-310723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 30,
            //     "mvd_lote_id" => 541,
            //     "mvd_codigo_lote" => "I46¬59180-310723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 56,
            //     "mvd_lote_id" => 542,
            //     "mvd_codigo_lote" => "I50¬76720-150623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 89,
            //     "mvd_lote_id" => 543,
            //     "mvd_codigo_lote" => "I50¬76720-240723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 77,
            //     "mvd_lote_id" => 544,
            //     "mvd_codigo_lote" => "S1¬57470-301023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 62,
            //     "mvd_lote_id" => 545,
            //     "mvd_codigo_lote" => "S1¬57470-311023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 240,
            //     "mvd_lote_id" => 546,
            //     "mvd_codigo_lote" => "S127¬78146-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 580,
            //     "mvd_lote_id" => 547,
            //     "mvd_codigo_lote" => "S127¬78146-251023=>5",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 130,
            //     "mvd_lote_id" => 548,
            //     "mvd_codigo_lote" => "S182¬76915-171123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 54,
            //     "mvd_cantidad" => 98,
            //     "mvd_lote_id" => 549,
            //     "mvd_codigo_lote" => "S182¬76915-190923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 445,
            //     "mvd_lote_id" => 550,
            //     "mvd_codigo_lote" => "S182¬76915-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 6,
            //     "mvd_lote_id" => 551,
            //     "mvd_codigo_lote" => "S182¬76915-260923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 11,
            //     "mvd_lote_id" => 552,
            //     "mvd_codigo_lote" => "S3¬59788-180423=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 1740,
            //     "mvd_lote_id" => 553,
            //     "mvd_codigo_lote" => "S3¬59788-290923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 2060,
            //     "mvd_lote_id" => 554,
            //     "mvd_codigo_lote" => "I56¬78134-100823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 431,
            //     "mvd_lote_id" => 555,
            //     "mvd_codigo_lote" => "S3¬77908-180723=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 570,
            //     "mvd_lote_id" => 556,
            //     "mvd_codigo_lote" => "S3¬78119-121023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 1239,
            //     "mvd_lote_id" => 557,
            //     "mvd_codigo_lote" => "I56¬78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 1461,
            //     "mvd_lote_id" => 558,
            //     "mvd_codigo_lote" => "I56¬78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 1240,
            //     "mvd_lote_id" => 559,
            //     "mvd_codigo_lote" => "I56¬57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 351,
            //     "mvd_lote_id" => 560,
            //     "mvd_codigo_lote" => "I56¬57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 221,
            //     "mvd_lote_id" => 561,
            //     "mvd_codigo_lote" => "I56¬57854-241023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 519,
            //     "mvd_lote_id" => 562,
            //     "mvd_codigo_lote" => "I56¬57854-271023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 600,
            //     "mvd_lote_id" => 563,
            //     "mvd_codigo_lote" => "M13¬69906-240623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1084,
            //     "mvd_lote_id" => 564,
            //     "mvd_codigo_lote" => "M13¬69906-260623=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 527,
            //     "mvd_lote_id" => 565,
            //     "mvd_codigo_lote" => "M27¬78223-081123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 16290,
            //     "mvd_lote_id" => 566,
            //     "mvd_codigo_lote" => "M44¬72343-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 89,
            //     "mvd_lote_id" => 567,
            //     "mvd_codigo_lote" => "M8¬78092-101123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 4420,
            //     "mvd_lote_id" => 568,
            //     "mvd_codigo_lote" => "M13¬78213-141223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 2000,
            //     "mvd_lote_id" => 569,
            //     "mvd_codigo_lote" => "M13¬78228-171023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 193,
            //     "mvd_cantidad" => 11550,
            //     "mvd_lote_id" => 570,
            //     "mvd_codigo_lote" => "M37¬71384-030323=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 191,
            //     "mvd_cantidad" => 12029,
            //     "mvd_lote_id" => 571,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 192,
            //     "mvd_cantidad" => 37422,
            //     "mvd_lote_id" => 572,
            //     "mvd_codigo_lote" => "M37¬71384-050123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 31,
            //     "mvd_cantidad" => 639,
            //     "mvd_lote_id" => 573,
            //     "mvd_codigo_lote" => "S1¬57470-050723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 13,
            //     "mvd_lote_id" => 574,
            //     "mvd_codigo_lote" => "S1¬57470-160823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 408,
            //     "mvd_lote_id" => 575,
            //     "mvd_codigo_lote" => "S1¬57470-250523=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 375,
            //     "mvd_lote_id" => 576,
            //     "mvd_codigo_lote" => "I104¬58253-070223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 14407,
            //     "mvd_lote_id" => 577,
            //     "mvd_codigo_lote" => "I104¬58253-150223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 19,
            //     "mvd_lote_id" => 578,
            //     "mvd_codigo_lote" => "I104¬76760-090823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 2520,
            //     "mvd_lote_id" => 579,
            //     "mvd_codigo_lote" => "I104¬76760-160623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 17,
            //     "mvd_lote_id" => 580,
            //     "mvd_codigo_lote" => "I16¬76149-190923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 908,
            //     "mvd_lote_id" => 581,
            //     "mvd_codigo_lote" => "I16¬76149-211123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 589,
            //     "mvd_lote_id" => 582,
            //     "mvd_codigo_lote" => "I38¬75754-280623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 223,
            //     "mvd_lote_id" => 583,
            //     "mvd_codigo_lote" => "I39¬76948-150623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 149,
            //     "mvd_lote_id" => 584,
            //     "mvd_codigo_lote" => "I39¬76948-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 56,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 330,
            //     "mvd_lote_id" => 585,
            //     "mvd_codigo_lote" => "I4¬44963-130723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 9490,
            //     "mvd_lote_id" => 586,
            //     "mvd_codigo_lote" => "76748-271223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 256,
            //     "mvd_lote_id" => 587,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 256,
            //     "mvd_lote_id" => 588,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 63,
            //     "mvd_lote_id" => 589,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 42,
            //     "mvd_lote_id" => 590,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 42,
            //     "mvd_lote_id" => 591,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 60,
            //     "mvd_cantidad" => 6106,
            //     "mvd_lote_id" => 592,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 52,
            //     "mvd_lote_id" => 593,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 6158,
            //     "mvd_lote_id" => 594,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 1,
            //     "mvd_lote_id" => 595,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 1,
            //     "mvd_lote_id" => 596,
            //     "mvd_codigo_lote" => "76748-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 57,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 548595.8,
            //     "mvd_lote_id" => 597,
            //     "mvd_codigo_lote" => 76748,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 23,
            //     "mvd_cantidad" => 2118,
            //     "mvd_lote_id" => 598,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 2118,
            //     "mvd_lote_id" => 599,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 6,
            //     "mvd_lote_id" => 600,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 6,
            //     "mvd_lote_id" => 601,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 116,
            //     "mvd_lote_id" => 602,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 110,
            //     "mvd_lote_id" => 603,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 110,
            //     "mvd_lote_id" => 604,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 14969,
            //     "mvd_lote_id" => 605,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 84,
            //     "mvd_lote_id" => 606,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 84,
            //     "mvd_lote_id" => 607,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 3,
            //     "mvd_lote_id" => 608,
            //     "mvd_codigo_lote" => "76149-291223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 455158.02,
            //     "mvd_lote_id" => 609,
            //     "mvd_codigo_lote" => 76149,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 39,
            //     "mvd_cantidad" => 2426,
            //     "mvd_lote_id" => 610,
            //     "mvd_codigo_lote" => "76149-081223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 58,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 361,
            //     "mvd_lote_id" => 611,
            //     "mvd_codigo_lote" => "76149-131123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 2632,
            //     "mvd_lote_id" => 612,
            //     "mvd_codigo_lote" => "78071-280623=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 719,
            //     "mvd_lote_id" => 613,
            //     "mvd_codigo_lote" => "69901-260723=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 150,
            //     "mvd_lote_id" => 614,
            //     "mvd_codigo_lote" => "69901-280723=>3",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 206,
            //     "mvd_cantidad" => 114,
            //     "mvd_lote_id" => 615,
            //     "mvd_codigo_lote" => "69901-280723=>4",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 227,
            //     "mvd_cantidad" => 336795,
            //     "mvd_lote_id" => 616,
            //     "mvd_codigo_lote" => 78210,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 12535,
            //     "mvd_lote_id" => 617,
            //     "mvd_codigo_lote" => "78210-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 59,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 8706,
            //     "mvd_lote_id" => 618,
            //     "mvd_codigo_lote" => "78210-300124=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 599929,
            //     "mvd_lote_id" => 619,
            //     "mvd_codigo_lote" => 75754,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 508,
            //     "mvd_lote_id" => 620,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 43,
            //     "mvd_cantidad" => 4,
            //     "mvd_lote_id" => 621,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 45,
            //     "mvd_cantidad" => 512,
            //     "mvd_lote_id" => 622,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 105,
            //     "mvd_lote_id" => 623,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 56,
            //     "mvd_cantidad" => 96,
            //     "mvd_lote_id" => 624,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 57,
            //     "mvd_cantidad" => 96,
            //     "mvd_lote_id" => 625,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 61,
            //     "mvd_cantidad" => 8412,
            //     "mvd_lote_id" => 626,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 92,
            //     "mvd_lote_id" => 627,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 70,
            //     "mvd_cantidad" => 92,
            //     "mvd_lote_id" => 628,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 73,
            //     "mvd_cantidad" => 23,
            //     "mvd_lote_id" => 629,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 60,
            //     "mvd_articulo_id" => 74,
            //     "mvd_cantidad" => 23,
            //     "mvd_lote_id" => 630,
            //     "mvd_codigo_lote" => "75754-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 61,
            //     "mvd_articulo_id" => 65,
            //     "mvd_cantidad" => 20175.83,
            //     "mvd_lote_id" => 631,
            //     "mvd_codigo_lote" => 112,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 437,
            //     "mvd_lote_id" => 632,
            //     "mvd_codigo_lote" => "S182¬76915-171123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 776,
            //     "mvd_lote_id" => 633,
            //     "mvd_codigo_lote" => "S256¬78039-111223=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 409,
            //     "mvd_lote_id" => 634,
            //     "mvd_codigo_lote" => "S3¬77908-070723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 620,
            //     "mvd_lote_id" => 635,
            //     "mvd_codigo_lote" => "I50¬76720-221123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 253,
            //     "mvd_lote_id" => 636,
            //     "mvd_codigo_lote" => "I56¬57854-130623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 446,
            //     "mvd_lote_id" => 637,
            //     "mvd_codigo_lote" => "I56¬57854-210723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 624,
            //     "mvd_lote_id" => 638,
            //     "mvd_codigo_lote" => "I56¬57854-281223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 1200,
            //     "mvd_lote_id" => 639,
            //     "mvd_codigo_lote" => "I56¬78134-030823=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 310,
            //     "mvd_lote_id" => 640,
            //     "mvd_codigo_lote" => "I56¬78134-310823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 2729,
            //     "mvd_lote_id" => 641,
            //     "mvd_codigo_lote" => "M13¬77977-241123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1000,
            //     "mvd_lote_id" => 642,
            //     "mvd_codigo_lote" => "M13¬78228-171023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 1087,
            //     "mvd_lote_id" => 643,
            //     "mvd_codigo_lote" => "M27¬78223-280923=>2",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 874,
            //     "mvd_lote_id" => 644,
            //     "mvd_codigo_lote" => "M27¬78223-300923=>4",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 2,
            //     "mvd_lote_id" => 645,
            //     "mvd_codigo_lote" => "M44¬72343-270623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 205,
            //     "mvd_cantidad" => 181,
            //     "mvd_lote_id" => 646,
            //     "mvd_codigo_lote" => "M8¬78092-150923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 95,
            //     "mvd_cantidad" => 870,
            //     "mvd_lote_id" => 647,
            //     "mvd_codigo_lote" => "M8¬78092-191023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 172,
            //     "mvd_lote_id" => 648,
            //     "mvd_codigo_lote" => "S1¬57470-120923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 267,
            //     "mvd_lote_id" => 649,
            //     "mvd_codigo_lote" => "I4¬57390-220923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 41,
            //     "mvd_cantidad" => 158,
            //     "mvd_lote_id" => 650,
            //     "mvd_codigo_lote" => "I40¬77136-041223=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 63,
            //     "mvd_cantidad" => 116,
            //     "mvd_lote_id" => 651,
            //     "mvd_codigo_lote" => "I40¬77136-201023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 107,
            //     "mvd_lote_id" => 652,
            //     "mvd_codigo_lote" => "I40¬77136-260723=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 240,
            //     "mvd_lote_id" => 653,
            //     "mvd_codigo_lote" => "I43¬76748-201023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 427,
            //     "mvd_lote_id" => 654,
            //     "mvd_codigo_lote" => "I104¬76760-110823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 149,
            //     "mvd_lote_id" => 655,
            //     "mvd_codigo_lote" => "I16¬76149-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 30,
            //     "mvd_cantidad" => 468,
            //     "mvd_lote_id" => 656,
            //     "mvd_codigo_lote" => "I16¬76149-161123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 848,
            //     "mvd_lote_id" => 657,
            //     "mvd_codigo_lote" => "I38¬75754-231123=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 51,
            //     "mvd_cantidad" => 35,
            //     "mvd_lote_id" => 658,
            //     "mvd_codigo_lote" => "I38¬75754-280623=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 62,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 203,
            //     "mvd_lote_id" => 659,
            //     "mvd_codigo_lote" => "I39¬76948-280923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 63,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 218,
            //     "mvd_lote_id" => 660,
            //     "mvd_codigo_lote" => "I4¬57390-040823=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 63,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 273,
            //     "mvd_lote_id" => 661,
            //     "mvd_codigo_lote" => "S182¬76915-130923=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 64,
            //     "mvd_articulo_id" => 36,
            //     "mvd_cantidad" => 106599,
            //     "mvd_lote_id" => 662,
            //     "mvd_codigo_lote" => 78279,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 64,
            //     "mvd_articulo_id" => 34,
            //     "mvd_cantidad" => 128786,
            //     "mvd_lote_id" => 663,
            //     "mvd_codigo_lote" => 78280,
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ],
            // [
            //     "mvd_mv_id" => 64,
            //     "mvd_articulo_id" => 38,
            //     "mvd_cantidad" => 155,
            //     "mvd_lote_id" => 664,
            //     "mvd_codigo_lote" => "I4¬57390-121023=>1",
            //     "mvd_estado" => "A",
            //     "usr_registrado" => 1
            // ]
        ]);
    }
}
