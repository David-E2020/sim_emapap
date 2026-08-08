<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PlantaSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		DB::table('plantas')->insert([
			"nombre" => "OFICINA CENTRAL",
			"descripcion" => "OC",
			"codigo" => "DIR",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO Y ALMACENAMINETO DE GRANOS - CUATRO CAÑADAS",
			"descripcion" => "PL4C",
			"codigo" => "SIL003",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO Y ALMACENAMIENTO DE GRANOS - SAN JULIAN",
			"descripcion" => "PLAGS",
			"codigo" => "SIL134",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO Y ALMACENAMIENTO - CABEZAS",
			"descripcion" => "PLAAC",
			"codigo" => "SIL102",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO Y ALMACENAMIENTO DE GRANOS - SAN PEDRO",
			"descripcion" => "PLAAGS",
			"codigo" => "SIL004",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "INGENIO ARROCERO - YAPACANI",
			"descripcion" => "PLIAY",
			"codigo" => "PLIAY",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO Y TRANSFORMACION GRANO DE TRIGO - CARACOLLO",
			"descripcion" => "PLATGTC",
			"codigo" => "SIL127",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "INGENIO ARROCERO - SAN ANDRES",
			"descripcion" => "PLIAS",
			"codigo" => "SIL213",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA DE ACOPIO DE GRANOS - IVIRGANZAMA",
			"descripcion" => "PLAGI",
			"codigo" => "SIL222",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMPLEJO PISCICOLA - CHIMORE",
			"descripcion" => "CPC",
			"codigo" => "CPC",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA AGRO INDUSTRIAL DF&R",
			"descripcion" => "SIL",
			"codigo" => "SIL013",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  AGROINDUSTRIA CAICO S.A.",
			"descripcion" => "SIL",
			"codigo" => "SIL011"
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION EL CHACO",
			"descripcion" => "GAL",
			"codigo" => "GAL311",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION RIOS",
			"descripcion" => "GAL",
			"codigo" => "GAL313",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA AUDAX AQUACULTURE & FHISING CONPANY S.A.",
			"descripcion" => "PPZ",
			"codigo" => "PPZ001",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CAL VIRTUAL POTOSI",
			"descripcion" => "GAL",
			"codigo" => "GAL196",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CAL VIRTUAL SANTA CRUZ EMAPA",
			"descripcion" => "GAL",
			"codigo" => "GAL032",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CAL VIRTUAL TARIJA EMAPA",
			"descripcion" => "GAL",
			"codigo" => "GAL029",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CANELAS",
			"descripcion" => "GAL",
			"codigo" => "GAL228",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CENTRO DE ACOPIO AYO AYO",
			"descripcion" => "SIL",
			"codigo" => "ACO123",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  CENTRO DE ACOPIO CORO CORO",
			"descripcion" => "SIL",
			"codigo" => "SIL265"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  CENTRO DE ACOPIO PAPEL PAMPA",
			"descripcion" => "SIL",
			"codigo" => "SIL263"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  CENTRO DE ACOPIO TARIJA",
			"descripcion" => "SIL",
			"codigo" => "SIL112"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA CERCADO S.R.L. (AURORA)",
			"descripcion" => "ING",
			"codigo" => "ING039",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CHARAPAQUI",
			"descripcion" => "GAL",
			"codigo" => "GAL141",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA CIA. MOLINERA BOLIVIANA",
			"descripcion" => "MOL",
			"codigo" => "MOL002",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CIAL",
			"descripcion" => "GAL",
			"codigo" => "GAL020",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION CIRCUNVALACION (UÑO) ",
			"descripcion" => "GAL",
			"codigo" => "GAL033",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  COLISEO AYO AYO",
			"descripcion" => "GAL",
			"codigo" => "GAL318"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA DOLLYALIMENTOS S.R.L.",
			"descripcion" => "SIL",
			"codigo" => "SIL182",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA  EMAPA",
			"descripcion" => "ING",
			"codigo" => "ING004",
			"tipo_acopio_id" => 1
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  HERMANOS ROJAS",
			"descripcion" => "ING",
			"codigo" => "ING035"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA IMNORTE",
			"descripcion" => "MOL",
			"codigo" => "SIL006",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA INGENIO ARROCERO MONTAÑO",
			"descripcion" => "ING",
			"codigo" => "ING040",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  INGENIO GRABOLC LTDA",
			"descripcion" => "ING",
			"codigo" => "ING133"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  JIHUSSA",
			"descripcion" => "SIL",
			"codigo" => "SIL016"
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION LEDEZMA",
			"descripcion" => "GAL",
			"codigo" => "GAL089",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  DEL ORIENTE SA",
			"descripcion" => "MOL",
			"codigo" => "MOL016"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOLINERA OKI S.R.L.",
			"descripcion" => "MOL",
			"codigo" => "MOL027",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOLINO ANDINO S.A.",
			"descripcion" => "MOL",
			"codigo" => "MOL005",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  AURORA S.R.L.",
			"descripcion" => "MOL",
			"codigo" => "MOL001"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOLINO CONCORDIA SRL (COCHABAMBA)",
			"descripcion" => "MOL",
			"codigo" => "MOL013",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOLINO INDUSTRIAS ALIMENTOS DEL SUR",
			"descripcion" => "MOL",
			"codigo" => "MOL044",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOLINO SIMSA",
			"descripcion" => "MOL",
			"codigo" => "MOL003",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA MOMELSA",
			"descripcion" => "SIL",
			"codigo" => "SIL017",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  OVANDO",
			"descripcion" => "ING",
			"codigo" => "ING050"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA PANAMERICANA",
			"descripcion" => "ING",
			"codigo" => "ING046",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  PROLEGA SA INTAGRO Silo",
			"descripcion" => "SIL",
			"codigo" => "SIL002"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  SAITE",
			"descripcion" => "MOL",
			"codigo" => "MOL037"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  SANTA ANA (EN PROCESO LEGAL)",
			"descripcion" => "ING",
			"codigo" => "ING017"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SANTA FE LTDA.",
			"descripcion" => "ING",
			"codigo" => "ING104",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SEÑOR DE MAYO",
			"descripcion" => "ING",
			"codigo" => "ING056",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  EEPS - SEDEM",
			"descripcion" => "SIL",
			"codigo" => "SIL302"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  GRANO DE ORO",
			"descripcion" => "SIL",
			"codigo" => "SIL256"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTAS  OKISERV SRL",
			"descripcion" => "SIL",
			"codigo" => "SIL281"
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SILOS GRANORTE",
			"descripcion" => "SIL",
			"codigo" => "SIL001",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SILOS INDUSTRIAS REYNALES",
			"descripcion" => "SIL",
			"codigo" => "SIL241",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SOCIEDAD AGRO INDUSTRIAL ITIKA S.A.",
			"descripcion" => "SIL",
			"codigo" => "SIL064",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION SOCOIN",
			"descripcion" => "GAL",
			"codigo" => "GAL014",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA SOFIA",
			"descripcion" => "ING",
			"codigo" => "ING043",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA TARIFA",
			"descripcion" => "ING",
			"codigo" => "ING016",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA TORREMOLINOS",
			"descripcion" => "MOL",
			"codigo" => "MOL004",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA URUPE SRL",
			"descripcion" => "ING",
			"codigo" => "ING038",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIBADA VIRGEN DE COTOCA (EN PROCESO LEGAL)",
			"descripcion" => "ING",
			"codigo" => "ING028",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION COÑA COÑA",
			"descripcion" => "GAL",
			"codigo" => "GAL022",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMERCIALIZACION INSTITUCIONAL VIRTUAL COBIJA",
			"descripcion" => "GAL",
			"codigo" => "GAL211",
			"tipo_acopio_id" => 3
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA PRIVADA VIDA SANA",
			"descripcion" => "ING",
			"codigo" => "ING126",
			"tipo_acopio_id" => 2
		]);
		DB::table('plantas')->insert([
			"nombre" => "PLANTA MOLINERA DEL ORIENTE SA",
			"descripcion" => "MOL",
			"codigo" => "MOL016"
		]);
		DB::table('plantas')->insert([
			"nombre" => "SILOS NORTE DE LA PAZ ",
			"descripcion" => "SIL",
			"codigo" => "SIL180"
		]);
		DB::table('plantas')->insert([
			"nombre" => "GRUPO NAPAL S.R.L.",
			"descripcion" => "SIL",
			"codigo" => "SIL239"
		]);
		DB::table('plantas')->insert([
			"nombre" => "TUCANES",
			"descripcion" => "SIL",
			"codigo" => "SIL288"
		]);
		DB::table('plantas')->insert([
			"nombre" => "INDUSTRIAS Y SERVICIOS TRINIAGRO",
			"descripcion" => "ING",
			"codigo" => "ING097"
		]);
		DB::table('plantas')->insert([
			"nombre" => "INOLSA S.A. (PROCESO LEGAL)",
			"descripcion" => "SIL",
			"codigo" => "SIL047"
		]);
		DB::table('plantas')->insert([
			"nombre" => "CENTRO DE TRANSFORMACIÓN HONESTY FOODS",
			"descripcion" => "SIL",
			"codigo" => "SIL007"
		]);
		DB::table('plantas')->insert([
			"nombre" => "COMPLEJO INDUSTRIAL SILOS DEL VALLE S.R.L.",
			"descripcion" => "SIL",
			"codigo" => "SIL008"
		]);
	}
}
