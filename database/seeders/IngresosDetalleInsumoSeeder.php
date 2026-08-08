<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngresosDetalleInsumoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('insumos.ingresos_detalle_insumos')->insert([
        // [
        //   'articulo_id' => 580,
        //   'cantidad' => 1,
        //   'costo' => 1080,
        //   'fecha_vencimiento' => '2023-10-20 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],
        // [
        //   'articulo_id' => 132,
        //   'cantidad' => 1,
        //   'costo' => 750,
        //   'fecha_vencimiento' => '2023-10-25 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],
        // [
        //   'articulo_id' => 417,
        //   'cantidad' => 4,
        //   'costo' => 880,
        //   'fecha_vencimiento' => '2023-10-25 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],

        // [
        //   'articulo_id' => 589,
        //   'cantidad' => 1,
        //   'costo' => 970,
        //   'fecha_vencimiento' => '2023-10-25 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],

        // [
        //   'articulo_id' => 1340,
        //   'cantidad' => 1,
        //   'costo' => 1395,
        //   'fecha_vencimiento' => '2023-10-25 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],

        // [
        //   'articulo_id' => 359,
        //   'cantidad' => 2,
        //   'costo' => 1780,
        //   'fecha_vencimiento' => '2023-10-25 09:42:34',
        //   'ingreso_insumo_id' => 1,
        // ],

        // [
        //   'articulo_id' => 1475,
        //   'cantidad' => 10,
        //   'costo' => 720,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1476,
        //   'cantidad' => 20,
        //   'costo' => 840,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1477,
        //   'cantidad' => 4,
        //   'costo' => 900,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1478,
        //   'cantidad' => 10,
        //   'costo' => 19.5,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16,',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1479,
        //   'cantidad' => 10,
        //   'costo' => 28.5,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1470,
        //   'cantidad' => 10,
        //   'costo' => 23.55,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1471,
        //   'cantidad' => 10,
        //   'costo' => 41.25,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1472,
        //   'cantidad' => 10,
        //   'costo' => 46.28,
        //   'ingreso_insumo_id' => 2,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16'
        // ],

        // [
        //   'articulo_id' => 1473,
        //   'cantidad' => 10,
        //   'costo' => 23.94,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1474,
        //   'cantidad' => 10,
        //   'costo' => 19.58,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1475,
        //   'cantidad' => 10,
        //   'costo' => 19.5,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1476,
        //   'cantidad' => 10,
        //   'costo' => 9.75,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1477,
        //   'cantidad' => 10,
        //   'costo' => 12.15,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1478,
        //   'cantidad' => 10,
        //   'costo' => 18,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1479,
        //   'cantidad' => 10,
        //   'costo' => 16.5,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1470,
        //   'cantidad' => 10,
        //   'costo' => 21.3,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1461,
        //   'cantidad' => 10,
        //   'costo' => 16.5,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1462,
        //   'cantidad' => 10,
        //   'costo' => 28.8,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1463,
        //   'cantidad' => 10,
        //   'costo' => 9,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1464,
        //   'cantidad' => 10,
        //   'costo' => 9.75,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1465,
        //   'cantidad' => 10,
        //   'costo' => 15,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1466,
        //   'cantidad' => 10,
        //   'costo' => 32.4,
        //   'fecha_vencimiento' => '2023-10-25 10:02:16',
        //   'ingreso_insumo_id' => 2,
        // ],

        // [
        //   'articulo_id' => 1356,
        //   'cantidad' => 1,
        //   'costo' => 6408.46,
        //   'fecha_vencimiento' => '2023-10-25 10:17:57',
        //   'ingreso_insumo_id' => 3,
        // ],

        // [
        //   'articulo_id' => 1358,
        //   'cantidad' => 1,
        //   'costo' => 10253.54,
        //   'fecha_vencimiento' => '2023-10-25 10:17:57',
        //   'ingreso_insumo_id' => 3,
        // ],

        // [
        //   'articulo_id' => 1359,
        //   'cantidad' => 1,
        //   'costo' => 33324,
        //   'fecha_vencimiento' => '2023-10-25 10:17:57',
        //   'ingreso_insumo_id' => 3,
        // ],

        // [
        //   'articulo_id' => 1365,
        //   'cantidad' => 2,
        //   'costo' => 850,
        //   'fecha_vencimiento' => '2023-10-25 10:24:32',
        //   'ingreso_insumo_id' => 4,
        // ],

        // [
        //   'articulo_id' => 1369,
        //   'cantidad' => 2,
        //   'costo' => 900,
        //   'fecha_vencimiento' => '2023-10-25 10:24:32',
        //   'ingreso_insumo_id' => 4,
        // ],

        // [
        //   'articulo_id' => 1372,
        //   'cantidad' => 1,
        //   'costo' => 850,
        //   'fecha_vencimiento' => '2023-10-25 10:24:32',
        //   'ingreso_insumo_id' => 4,
        // ],

        // [
        //   'articulo_id' => 1375,
        //   'cantidad' => 1,
        //   'costo' => 850,
        //   'fecha_vencimiento' => '2023-10-25 10:24:32',
        //   'ingreso_insumo_id' => 4,
        // ],

        // [
        //   'articulo_id' => 1376,
        //   'cantidad' => 4,
        //   'costo' => 193,
        //   'fecha_vencimiento' => '2023-10-25 10:24:32',
        //   'ingreso_insumo_id' => 4,
        // ],

        // [
        //   'articulo_id' => 1350,
        //   'cantidad' => 80,
        //   'costo' => 18,
        //   'fecha_vencimiento' => '2023-10-25 15:02:52',
        //   'ingreso_insumo_id' => 6,
        // ],

        // [
        //   'articulo_id' => 1251,
        //   'cantidad' => 30,
        //   'costo' => 18,
        //   'fecha_vencimiento' => '2023-10-25 15:02:52',
        //   'ingreso_insumo_id' => 6,
        // ],

        // [
        //   'articulo_id' => 1252,
        //   'cantidad' => 8,
        //   'costo' => 135,
        //   'fecha_vencimiento' => '2023-10-25 15:02:52',
        //   'ingreso_insumo_id' => 6,
        // ],

        // [
        //   'articulo_id' => 1253,
        //   'cantidad' => 9,
        //   'costo' => 750,
        //   'fecha_vencimiento' => '2023-10-25 15:02:52',
        //   'ingreso_insumo_id' => 6,
        // ],

        // [
        //   'articulo_id' => 1254,
        //   'cantidad' => 48,
        //   'costo' => 1.5,
        //   'fecha_vencimiento' => '2023-10-25 15:02:52',
        //   'ingreso_insumo_id' => 6,
        // ],
        /*
        [
          'articulo_id' => 1255,
          'cantidad' => 48,
          'costo' => 2, 5,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1256,
          'cantidad' => 100,
          'costo' => 1, 5,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1257,
          'cantidad' => 36,
          'costo' => 3,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1258,
          'cantidad' => 20,
          'costo' => 24,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1259,
          'cantidad' => 60,
          'costo' => 10,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1260,
          'cantidad' => 40,
          'costo' => 4, 5,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1261,
          'cantidad' => 18,
          'costo' => 8,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1262,
          'cantidad' => 18,
          'costo' => 8,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 1263,
          'cantidad' => 10,
          'costo' => 24,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 6,
        ],

        [
          'articulo_id' => 27,
          'cantidad' => 40,
          'costo' => 30,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 66,
          'cantidad' => 130,
          'costo' => 22,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 73,
          'cantidad' => 1300,
          'costo' => 0, 8,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 74,
          'cantidad' => 1000,
          'costo' => 0, 8,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 91,
          'cantidad' => 1000,
          'costo' => 1,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 92,
          'cantidad' => 250,
          'costo' => 0, 7,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 96,
          'cantidad' => 300,
          'costo' => 3,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 110,
          'cantidad' => 250,
          'costo' => 4,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 133,
          'cantidad' => 250,
          'costo' => 3,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 150,
          'cantidad' => 80,
          'costo' => 1, 86,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 151,
          'cantidad' => 80,
          'costo' => 1, 85,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 152,
          'cantidad' => 80,
          'costo' => 1, 85,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 162,
          'cantidad' => 60,
          'costo' => 60,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 164,
          'cantidad' => 80,
          'costo' => 12, 9,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 170,
          'cantidad' => 50,
          'costo' => 3, 5,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 171,
          'cantidad' => 20,
          'costo' => 2, 8,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 9,
        ],

        [
          'articulo_id' => 1257,
          'cantidad' => 2,
          'costo' => 9179,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1258,
          'cantidad' => 2,
          'costo' => 8147,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1259,
          'cantidad' => 2,
          'costo' => 8391,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1260,
          'cantidad' => 1,
          'costo' => 47053,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1261,
          'cantidad' => 6,
          'costo' => 585,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1262,
          'cantidad' => 6,
          'costo' => 877,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1263,
          'cantidad' => 6,
          'costo' => 1554,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1264,
          'cantidad' => 2,
          'costo' => 85,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1265,
          'cantidad' => 12,
          'costo' => 330,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1266,
          'cantidad' => 12,
          'costo' => 54,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1267,
          'cantidad' => 2,
          'costo' => 3111,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 271,
          'cantidad' => 6,
          'costo' => 1207,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 269,
          'cantidad' => 4,
          'costo' => 1472,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 268,
          'cantidad' => 6,
          'costo' => 988,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 270,
          'cantidad' => 6,
          'costo' => 968,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 272,
          'cantidad' => 30,
          'costo' => 12,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 273,
          'cantidad' => 12,
          'costo' => 59,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 274,
          'cantidad' => 50,
          'costo' => 51,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 275,
          'cantidad' => 100,
          'costo' => 11,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 276,
          'cantidad' => 100,
          'costo' => 23,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1277,
          'cantidad' => 30,
          'costo' => 19,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1278,
          'cantidad' => 30,
          'costo' => 44,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1279,
          'cantidad' => 30,
          'costo' => 36,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1280,
          'cantidad' => 30,
          'costo' => 18,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1281,
          'cantidad' => 30,
          'costo' => 55,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1284,
          'cantidad' => 2,
          'costo' => 2556,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1287,
          'cantidad' => 2,
          'costo' => 269,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1290,
          'cantidad' => 2,
          'costo' => 1387,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1291,
          'cantidad' => 2,
          'costo' => 166,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1288,
          'cantidad' => 2,
          'costo' => 1342,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 1289,
          'cantidad' => 2,
          'costo' => 146,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 7,
        ],

        [
          'articulo_id' => 94,
          'cantidad' => 13,
          'costo' => 310,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 10,
        ],

        [
          'articulo_id' => 95,
          'cantidad' => 15,
          'costo' => 398,
          'fecha_vencimiento' => '25/10/2023 15:02:52',
          'ingreso_insumo_id' => 10,
        ]*/
      ]
    );
  }
}
