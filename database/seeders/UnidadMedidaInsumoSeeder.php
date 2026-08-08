<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadMedidaInsumoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('insumos.unidad_medida')->insert(
			[
				[
					"nombre" => "Quintal",
					"abreviatura" => "46 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 46,
					"id_unidad" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "45 Kilogramos",
					"abreviatura" => "45 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 45,
					"id_unidad" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Arroba",
					"abreviatura" => "@",
					"magnitud" => "MASA",
					"equivalencia_kg" => 11.5,
					"id_unidad" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "1 Kilogramo",
					"abreviatura" => "1 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "50 Kilogramos",
					"abreviatura" => "50 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 50,
					"id_unidad" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "5 Kilogramos",
					"abreviatura" => "5 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 5,
					"id_unidad" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Quintal",
					"abreviatura" => "q (FR)",
					"magnitud" => "MASA",
					"equivalencia_kg" => 46,
					"id_unidad" => 7,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "23 Kilogramos",
					"abreviatura" => "23 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 23,
					"id_unidad" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Tonelada",
					"abreviatura" => "t",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1000,
					"id_unidad" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "2 Kg",
					"abreviatura" => "2 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 2,
					"id_unidad" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "20 Kilos",
					"abreviatura" => "20 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 20000,
					"id_unidad" => 11,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa 500 gramos",
					"abreviatura" => "Bol 500 g",
					"magnitud" => "MASA",
					"equivalencia_kg" => 0.5,
					"id_unidad" => 12,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Unidad",
					"abreviatura" => "u",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 13,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa 1\/2 Kilogramos",
					"abreviatura" => "Bol 1\/2 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 0.5,
					"id_unidad" => 14,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa 453 Gramos",
					"abreviatura" => "Bol 453 g",
					"magnitud" => "MASA",
					"equivalencia_kg" => 0.45,
					"id_unidad" => 15,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa  Unidad",
					"abreviatura" => "Bol u",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 16,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa 1 Kilogramo",
					"abreviatura" => "Bol 1 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 17,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bolsa 46 Kilogramos",
					"abreviatura" => "Bolsa 46 Kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 46,
					"id_unidad" => 18,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "KILOGRAMO",
					"abreviatura" => "KILOGRAMO",
					"magnitud" => "MASA",
					"equivalencia_kg" => 0,
					"id_unidad" => 19,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "kg",
					"abreviatura" => "kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 20,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "Bol 25 kg",
					"abreviatura" => "Bol 25 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 0,
					"id_unidad" => 21,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "BOL 46 KG",
					"abreviatura" => "BOL 46 KG",
					"magnitud" => "MASA",
					"equivalencia_kg" => 46,
					"id_unidad" => 22,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"nombre" => "bol 25 kg",
					"abreviatura" => "bol 25 kg",
					"magnitud" => "MASA",
					"equivalencia_kg" => 1,
					"id_unidad" => 23,
					"usr_registrado" => 1,
					"estado" => "A"
				]
			]
		);
	}
}
