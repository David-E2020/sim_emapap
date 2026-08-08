<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LineaInsumoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('insumos.lineas')->insert([
			[
				"codigo" => "ABA",
				"nombre" => "ALIMENTO BALANCEADO",
				"descripcion" => "ALIMENTO BALANCEADO",
				"tipo_prod_id" => 6,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "ARR",
				"nombre" => "ARROZ",
				"descripcion" => "ARROZ",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "NCL",
				"nombre" => "NUCLEO",
				"descripcion" => "NUCLEO",
				"tipo_prod_id" => 6,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "PCZ",
				"nombre" => "PEZ ALEVIN",
				"descripcion" => "PEZ ALEVIN",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "PZ2",
				"nombre" => "PEZ",
				"descripcion" => "PEZ",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "QUI",
				"nombre" => "QUINUA",
				"descripcion" => "QUINUA",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "SOY",
				"nombre" => "SOYA",
				"descripcion" => "SOYA",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "TRI",
				"nombre" => "TRIGO",
				"descripcion" => "TRIGO",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "MAI",
				"nombre" => "MAIZ",
				"descripcion" => "MAIZ",
				"tipo_prod_id" => 1,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "HAR",
				"nombre" => "HARINA",
				"descripcion" => "HARINA",
				"tipo_prod_id" => 2,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "MTE",
				"nombre" => "MATERIAL ESCRITORIO",
				"descripcion" => "Material de Escritorio",
				"tipo_prod_id" => 4,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			],
			[
				"codigo" => "INS",
				"nombre" => "INSUMOS",
				"descripcion" => "INSUMOS DE PRODUCCION",
				"tipo_prod_id" => 5,
				"usr_registrado" => 1,
				"usr_modificado" => 1,
				"estado" => "A"
			]
		]);
	}
}
