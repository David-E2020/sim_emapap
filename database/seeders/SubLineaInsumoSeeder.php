<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubLineaInsumoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('insumos.sub_lineas')->insert(
			[
				[
					"codigo" => "AB1",
					"nombre" => "A.B. VACUNO MANTENIMIENTO PELETIZADO",
					"descripcion" => "AB1",
					"codigo_alternativo" => "ABA-AB1",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ABC",
					"nombre" => "A. BALANCEADO VACUNO EN MANTENIMIENTO",
					"descripcion" => "ABC",
					"codigo_alternativo" => "ABA-ABC",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ABEM",
					"nombre" => "A.B. VACUNO EN MANTENIMIENTO",
					"descripcion" => "ABEM",
					"codigo_alternativo" => "ABA-ABEM",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ABP",
					"nombre" => "A.B. VACUNO LECHERO PELETIZADO",
					"descripcion" => "ABP",
					"codigo_alternativo" => "ABA-ABP",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ABV",
					"nombre" => "A.B. VACUNO MANTENIMIENTO 45 KG",
					"descripcion" => "ABV",
					"codigo_alternativo" => "ABA-ABV",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API001",
					"nombre" => "A.B. AVICOLA PARRILLERO INICIO",
					"descripcion" => "API001",
					"codigo_alternativo" => "ABA-API001",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API002",
					"nombre" => "A.B. AVICOLA PARRILLERO CRECIMIENTO ",
					"descripcion" => "API002",
					"codigo_alternativo" => "ABA-API002",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API003",
					"nombre" => "A.B. AVICOLA PARRILLERO ACABADO ",
					"descripcion" => "API003",
					"codigo_alternativo" => "ABA-API003",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API004",
					"nombre" => "A.B. CERDO CRECIMIENTO ",
					"descripcion" => "API004",
					"codigo_alternativo" => "ABA-API004",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API005",
					"nombre" => "A.B. CERDO ACABADO",
					"descripcion" => "API005",
					"codigo_alternativo" => "ABA-API005",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API006",
					"nombre" => "A.B. CERDO LACTANTE",
					"descripcion" => "API006",
					"codigo_alternativo" => "ABA-API006",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API007",
					"nombre" => "A.B. CERDO GESTANTE",
					"descripcion" => "API007",
					"codigo_alternativo" => "ABA-API007",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "API008",
					"nombre" => "AB. AVICOLA PARRILLERO INICIO",
					"descripcion" => "API008",
					"codigo_alternativo" => "ABA-API008",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AVL",
					"nombre" => "A.B. VACUNO LECHERO",
					"descripcion" => "AVL",
					"codigo_alternativo" => "ABA-AVL",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PFCR",
					"nombre" => "A.B. PISCICOLA CRECIMIENTO (F2)",
					"descripcion" => "PFCR",
					"codigo_alternativo" => "ABA-PFCR",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PFEN",
					"nombre" => "A.B. PISCICOLA ENGORDE (F3)",
					"descripcion" => "PFEN",
					"codigo_alternativo" => "ABA-PFEN",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PFIN",
					"nombre" => "A.B. PISCICOLA INICIO (F1)",
					"descripcion" => "PFIN",
					"codigo_alternativo" => "ABA-PFIN",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R4U",
					"nombre" => "ALIMENTO BALANCEADO MANTENIMIENTO VACUNO-PTS",
					"descripcion" => "R4U",
					"codigo_alternativo" => "ABA-R4U",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R5U",
					"nombre" => "ALIMENTO BALANCEADO VACUNO LECHERO-PTS",
					"descripcion" => "R5U",
					"codigo_alternativo" => "ABA-R5U",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "VGC23",
					"nombre" => "VETERMIX GANADO DE CONFINAMIENTO",
					"descripcion" => "VGC23",
					"codigo_alternativo" => "ABA-VGC23",
					"linea_id" => 1,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R3U",
					"nombre" => "AFRECHO DE ARROZ -PTS",
					"descripcion" => "R3U",
					"codigo_alternativo" => "ARR-R3U",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R0T",
					"nombre" => "ARROZ ENTERO AD-PTS",
					"descripcion" => "R0T",
					"codigo_alternativo" => "ARR-R0T",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R1U",
					"nombre" => "ARROZ TRES CUARTOS BB-PTS",
					"descripcion" => "R1U",
					"codigo_alternativo" => "ARR-R1U",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R2U",
					"nombre" => "ARROCILLO CB-PTS",
					"descripcion" => "R2U",
					"codigo_alternativo" => "ARR-R2U",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R6U",
					"nombre" => "COLILLA DA-PTS",
					"descripcion" => "R6U",
					"codigo_alternativo" => "ARR-R6U",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "MER",
					"nombre" => "MERMA",
					"descripcion" => "MER",
					"codigo_alternativo" => "ARR-MER",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "B",
					"nombre" => "ARROZ TRES CUARTOS B",
					"descripcion" => "B",
					"codigo_alternativo" => "ARR-B",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "BA",
					"nombre" => "ARROZ TRES CUARTOS BA",
					"descripcion" => "BA",
					"codigo_alternativo" => "ARR-BA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "BB",
					"nombre" => "ARROZ TRES CUARTOS BB",
					"descripcion" => "BB",
					"codigo_alternativo" => "ARR-BB",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "C",
					"nombre" => "ARROCILLO C",
					"descripcion" => "C",
					"codigo_alternativo" => "ARR-C",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CA",
					"nombre" => "ARROCILLO CA",
					"descripcion" => "CA",
					"codigo_alternativo" => "ARR-CA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CAS",
					"nombre" => "CASCARA",
					"descripcion" => "CAS",
					"codigo_alternativo" => "ARR-CAS",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CB",
					"nombre" => "ARROCILLO CB",
					"descripcion" => "CB",
					"codigo_alternativo" => "ARR-CB",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CHA",
					"nombre" => "ARROZ EN CHALA",
					"descripcion" => "CHA",
					"codigo_alternativo" => "ARR-CHA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CO2",
					"nombre" => "COLILLA TIPO 2",
					"descripcion" => "CO2",
					"codigo_alternativo" => "ARR-CO2",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CO3",
					"nombre" => "COLILLA TIPO 3",
					"descripcion" => "CO3",
					"codigo_alternativo" => "ARR-CO3",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "COL",
					"nombre" => "COLILLA",
					"descripcion" => "COL",
					"codigo_alternativo" => "ARR-COL",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "D",
					"nombre" => "COLILLA D",
					"descripcion" => "D",
					"codigo_alternativo" => "ARR-D",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "DA",
					"nombre" => "COLILLA DA",
					"descripcion" => "DA",
					"codigo_alternativo" => "ARR-DA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "DB",
					"nombre" => "COLILLA DB",
					"descripcion" => "DB",
					"codigo_alternativo" => "ARR-DB",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "EN2",
					"nombre" => "ARROZ ENTERO TIPO 2",
					"descripcion" => "EN2",
					"codigo_alternativo" => "ARR-EN2",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "EN3",
					"nombre" => "ARROZ ENTERO TIPO 3",
					"descripcion" => "EN3",
					"codigo_alternativo" => "ARR-EN3",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AR2",
					"nombre" => "ARROCILLO TIPO 2",
					"descripcion" => "AR2",
					"codigo_alternativo" => "ARR-AR2",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AR3",
					"nombre" => "ARROCILLO TIPO 3",
					"descripcion" => "AR3",
					"codigo_alternativo" => "ARR-AR3",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AS2",
					"nombre" => "AFRECHO DE ARROZ TIPO 2",
					"descripcion" => "AS2",
					"codigo_alternativo" => "ARR-AS2",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AS3",
					"nombre" => "AFRECHO DE ARROZ TIPO 3",
					"descripcion" => "AS3",
					"codigo_alternativo" => "ARR-AS3",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ASA",
					"nombre" => "AFRECHO DE ARROZ",
					"descripcion" => "ASA",
					"codigo_alternativo" => "ARR-ASA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AC",
					"nombre" => "ARROZ ENTERO AC",
					"descripcion" => "AC",
					"codigo_alternativo" => "ARR-AC",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ACO",
					"nombre" => "ARROCILLO MAS COLILLA",
					"descripcion" => "ACO",
					"codigo_alternativo" => "ARR-ACO",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AD",
					"nombre" => "ARROZ ENTERO AD",
					"descripcion" => "AD",
					"codigo_alternativo" => "ARR-AD",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AFR",
					"nombre" => "AFRECHO",
					"descripcion" => "AFR",
					"codigo_alternativo" => "ARR-AFR",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ABI",
					"nombre" => "ARROZ ENTERO ABI",
					"descripcion" => "ABI",
					"codigo_alternativo" => "ARR-ABI",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "A",
					"nombre" => "ARROZ ENTERO A",
					"descripcion" => "A",
					"codigo_alternativo" => "ARR-A",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AA",
					"nombre" => "ARROZ ENTERO AA",
					"descripcion" => "AA",
					"codigo_alternativo" => "ARR-AA",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AA+",
					"nombre" => "ARROZ ENTERO AA+",
					"descripcion" => "AA+",
					"codigo_alternativo" => "ARR-AA+",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AB",
					"nombre" => "ARROZ ENTERO AB",
					"descripcion" => "AB",
					"codigo_alternativo" => "ARR-AB",
					"linea_id" => 2,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => 0,
					"nombre" => "HARINA 000",
					"descripcion" => 0,
					"codigo_alternativo" => "HAR-0",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "00S",
					"nombre" => "HARINA 000 Sub",
					"descripcion" => "00S",
					"codigo_alternativo" => "HAR-00S",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "HPO",
					"nombre" => "HARINA POL18",
					"descripcion" => "HPO",
					"codigo_alternativo" => "HAR-HPO",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "HPS",
					"nombre" => "HARINA PRINCESA",
					"descripcion" => "HPS",
					"codigo_alternativo" => "HAR-HPS",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "HTI",
					"nombre" => "HARINA DE TRIGO IMP",
					"descripcion" => "HTI",
					"codigo_alternativo" => "HAR-HTI",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PM5",
					"nombre" => "HARINA PMA",
					"descripcion" => "PM5",
					"codigo_alternativo" => "HAR-PM5",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "SF0",
					"nombre" => "HARINA PANADERA 000 CONCORDIA\/2023",
					"descripcion" => "SF0",
					"codigo_alternativo" => "HAR-SF0",
					"linea_id" => 10,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R6T",
					"nombre" => "GRANILLO DE MAIZ TIPO 2-PTS",
					"descripcion" => "R6T",
					"codigo_alternativo" => "MAI-R6T",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R7T",
					"nombre" => "RESIDUO DE MAIZ-PTS",
					"descripcion" => "R7T",
					"codigo_alternativo" => "MAI-R7T",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R8T",
					"nombre" => "HARINA DE MAIZ (COSUMO ANIMAL)-PTS",
					"descripcion" => "R8T",
					"codigo_alternativo" => "MAI-R8T",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "RM2",
					"nombre" => "RESIDUO DE MAIZ TIPO 2",
					"descripcion" => "RM2",
					"codigo_alternativo" => "MAI-RM2",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "RMA",
					"nombre" => "RESIDUO DE MAIZ",
					"descripcion" => "RMA",
					"codigo_alternativo" => "MAI-RMA",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R5T",
					"nombre" => "GRANILLO DE MAIZ -PTS",
					"descripcion" => "R5T",
					"codigo_alternativo" => "MAI-R5T",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "MZ2",
					"nombre" => "GRANILLO DE MAIZ TIPO 2TN ",
					"descripcion" => "MZ2",
					"codigo_alternativo" => "MAI-MZ2",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "GEM",
					"nombre" => "GERMEN DE MAIZ",
					"descripcion" => "GEM",
					"codigo_alternativo" => "MAI-GEM",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "GM1",
					"nombre" => "GRANO DE MAIZ",
					"descripcion" => "GM1",
					"codigo_alternativo" => "MAI-GM1",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "GT2",
					"nombre" => "GRANILLO DE MAIZ TIPO 2",
					"descripcion" => "GT2",
					"codigo_alternativo" => "MAI-GT2",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "GT3",
					"nombre" => "GRANILLO DE MAIZ TIPO 3",
					"descripcion" => "GT3",
					"codigo_alternativo" => "MAI-GT3",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "HM1",
					"nombre" => "HARINILLA DE MAIZ",
					"descripcion" => "HM1",
					"codigo_alternativo" => "MAI-HM1",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "HMZ",
					"nombre" => "HARINA DE MAIZ",
					"descripcion" => "HMZ",
					"codigo_alternativo" => "MAI-HMZ",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "LS1",
					"nombre" => "MAIZ LIMPIO Y SECO",
					"descripcion" => "LS1",
					"codigo_alternativo" => "MAI-LS1",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "M16",
					"nombre" => "MAIZ VERANO 16-17- NO VALIDO",
					"descripcion" => "M16",
					"codigo_alternativo" => "MAI-M16",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "M42",
					"nombre" => "MAIZ TIPO 2",
					"descripcion" => "M42",
					"codigo_alternativo" => "MAI-M42",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "M67",
					"nombre" => "MAIZ VERANO 16-17",
					"descripcion" => "M67",
					"codigo_alternativo" => "MAI-M67",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "M78",
					"nombre" => "MAIZ VERANO 17-18",
					"descripcion" => "M78",
					"codigo_alternativo" => "MAI-M78",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "M89",
					"nombre" => "MAIZ VERANO 18-19",
					"descripcion" => "M89",
					"codigo_alternativo" => "MAI-M89",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "MAI",
					"nombre" => "MAIZ AGRANEL",
					"descripcion" => "MAI",
					"codigo_alternativo" => "MAI-MAI",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "DMZ",
					"nombre" => "GRANILLO DE MAIZ",
					"descripcion" => "DMZ",
					"codigo_alternativo" => "MAI-DMZ",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "CM1",
					"nombre" => "CASCARA DE MAIZ",
					"descripcion" => "CM1",
					"codigo_alternativo" => "MAI-CM1",
					"linea_id" => 9,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "FEC",
					"nombre" => "FERTIPHOS CONFINAMIENTO",
					"descripcion" => "FEC",
					"codigo_alternativo" => "NCL-FEC",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AFL",
					"nombre" => "AFLAMIX",
					"descripcion" => "AFL",
					"codigo_alternativo" => "NCL-AFL",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "NLG",
					"nombre" => "NUCLEO LECHERAS GOLD",
					"descripcion" => "NLG",
					"codigo_alternativo" => "NCL-NLG",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM",
					"nombre" => "PREMIX",
					"descripcion" => "PRM",
					"codigo_alternativo" => "NCL-PRM",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM 004",
					"nombre" => "PREMIX CERDO GESTANTE",
					"descripcion" => "PRM 004",
					"codigo_alternativo" => "NCL-PRM 004",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM001",
					"nombre" => "PREMIX CERDO CRECIMIENTO",
					"descripcion" => "PRM001",
					"codigo_alternativo" => "NCL-PRM001",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM002",
					"nombre" => "PREMIX CERDO ACABADO",
					"descripcion" => "PRM002",
					"codigo_alternativo" => "NCL-PRM002",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM003",
					"nombre" => "PREMIX CERDO LACTANTE",
					"descripcion" => "PRM003",
					"codigo_alternativo" => "NCL-PRM003",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM005",
					"nombre" => "PREMIX AVICOLA PARRILLERO INICIAL",
					"descripcion" => "PRM005",
					"codigo_alternativo" => "NCL-PRM005",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM006",
					"nombre" => "PREMIX AVICOLA PARRILLERO CRECIMIENTO",
					"descripcion" => "PRM006",
					"codigo_alternativo" => "NCL-PRM006",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM007",
					"nombre" => "PREMIX PARRILLERO TERMINADO",
					"descripcion" => "PRM007",
					"codigo_alternativo" => "NCL-PRM007",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM008",
					"nombre" => "PREMIX PARRILLERO INICIO",
					"descripcion" => "PRM008",
					"codigo_alternativo" => "NCL-PRM008",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PRM009",
					"nombre" => "PREMIX PARRILLERO CRECIMIENTO",
					"descripcion" => "PRM009",
					"codigo_alternativo" => "NCL-PRM009",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "VGL",
					"nombre" => "VETERMIX GANADO LECHERO",
					"descripcion" => "VGL",
					"codigo_alternativo" => "NCL-VGL",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "VGC",
					"nombre" => "VETERMIX GANADO DE CONFINAMIENTO",
					"descripcion" => "VGC",
					"codigo_alternativo" => "NCL-VGC",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "SIN",
					"nombre" => "SINTOX",
					"descripcion" => "SIN",
					"codigo_alternativo" => "NCL-SIN",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "SIN23",
					"nombre" => "SINTOX",
					"descripcion" => "SIN23",
					"codigo_alternativo" => "NCL-SIN23",
					"linea_id" => 3,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PES",
					"nombre" => "ALEVIN-T",
					"descripcion" => "PES",
					"codigo_alternativo" => "PCZ-PES",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PSP",
					"nombre" => "ALEVIN-P",
					"descripcion" => "PSP",
					"codigo_alternativo" => "PCZ-PSP",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE1",
					"nombre" => "ALEVIN TAMBAQUI-T",
					"descripcion" => "PE1",
					"codigo_alternativo" => "PCZ-PE1",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE2",
					"nombre" => "ALEVIN PACU-P",
					"descripcion" => "PE2",
					"codigo_alternativo" => "PCZ-PE2",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE3",
					"nombre" => "ALEVIN PACU-T",
					"descripcion" => "PE3",
					"codigo_alternativo" => "PCZ-PE3",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE4",
					"nombre" => "ALEVIN TAMBAQUI-P",
					"descripcion" => "PE4",
					"codigo_alternativo" => "PCZ-PE4",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PJP",
					"nombre" => "ALEVIN PRE JUVENIL TAMBAQUI-P",
					"descripcion" => "PJP",
					"codigo_alternativo" => "PCZ-PJP",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PJT",
					"nombre" => "ALEVIN PRE JUVENIL TAMBAQUI-L",
					"descripcion" => "PJT",
					"codigo_alternativo" => "PCZ-PJT",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "JTB",
					"nombre" => "ALEVIN JUVENIL TAMBAQUI-L",
					"descripcion" => "JTB",
					"codigo_alternativo" => "PCZ-JTB",
					"linea_id" => 4,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "LTE",
					"nombre" => "LOMITO DE TAMBAQUI ENLATADO AL AGUA",
					"descripcion" => "LTE",
					"codigo_alternativo" => "PZ2-LTE",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "LTL",
					"nombre" => "LOMITO DE TAMBAQUI ENLATADO AL LIMON",
					"descripcion" => "LTL",
					"codigo_alternativo" => "PZ2-LTL",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "FEP",
					"nombre" => "FILETE DE PACU SIN ESPINAS CON PIEL SIN ESCAMAS AL VACIO ",
					"descripcion" => "FEP",
					"codigo_alternativo" => "PZ2-FEP",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "FPE",
					"nombre" => "FILETE DE PACU SIN ESPINA ENVASADO AL VACIO Kg-EMAPA",
					"descripcion" => "FPE",
					"codigo_alternativo" => "PZ2-FPE",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "FTB",
					"nombre" => "FILETE DE TAMBAQUI SIN ESPINA ENVASADO AL VACIO",
					"descripcion" => "FTB",
					"codigo_alternativo" => "PZ2-FTB",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "FTP",
					"nombre" => "FILETE DE TAMBAQUI SIN ESPINAS CON PIEL SIN ESCAMAS AL VACIO",
					"descripcion" => "FTP",
					"codigo_alternativo" => "PZ2-FTP",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AP5",
					"nombre" => "ALEVIN PACU-P UNIDAD",
					"descripcion" => "AP5",
					"codigo_alternativo" => "PZ2-AP5",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PPV",
					"nombre" => "POSTAS DE PACU AL VACIO",
					"descripcion" => "PPV",
					"codigo_alternativo" => "PZ2-PPV",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE5",
					"nombre" => "ALEVIN TAMBAQUI-L ",
					"descripcion" => "PE5",
					"codigo_alternativo" => "PZ2-PE5",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE6",
					"nombre" => "ALEVIN PACU-L",
					"descripcion" => "PE6",
					"codigo_alternativo" => "PZ2-PE6",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE8",
					"nombre" => "PACU FRESCO",
					"descripcion" => "PE8",
					"codigo_alternativo" => "PZ2-PE8",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PE9",
					"nombre" => "TAMBAQUI FRESCO ",
					"descripcion" => "PE9",
					"codigo_alternativo" => "PZ2-PE9",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PEA",
					"nombre" => "LOMITO DE PACU ENLATADO AL AGUA",
					"descripcion" => "PEA",
					"codigo_alternativo" => "PZ2-PEA",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PEL",
					"nombre" => "LOMITO DE PACU ENLATADO AL LIMON",
					"descripcion" => "PEL",
					"codigo_alternativo" => "PZ2-PEL",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PT10",
					"nombre" => "TAMBAQUI FRESCO EVISCERADO",
					"descripcion" => "PT10",
					"codigo_alternativo" => "PZ2-PT10",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PT8",
					"nombre" => "CORTE LONGITUDINAL DE TAMBAQUI CON ESCAMA AL VACIO",
					"descripcion" => "PT8",
					"codigo_alternativo" => "PZ2-PT8",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PT9",
					"nombre" => "CORTE LONGITUDINAL DE PACU CON ESCAMA AL VACIO",
					"descripcion" => "PT9",
					"codigo_alternativo" => "PZ2-PT9",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PTV",
					"nombre" => "POSTAS DE TAMBAQUI AL VACIO",
					"descripcion" => "PTV",
					"codigo_alternativo" => "PZ2-PTV",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PZ2",
					"nombre" => "ALEVIN TAMBAQUI-P ",
					"descripcion" => "PZ2",
					"codigo_alternativo" => "PZ2-PZ2",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "PZ7",
					"nombre" => "PACU FRESCO EVISCERADO",
					"descripcion" => "PZ7",
					"codigo_alternativo" => "PZ2-PZ7",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TPP",
					"nombre" => "TAMBAQUI FRESCO PRODUCTOR",
					"descripcion" => "TPP",
					"codigo_alternativo" => "PZ2-TPP",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TBE",
					"nombre" => "TAMBAQUI EN MITADES CON ESCAMAS SELLADO AL VACIO EMAPA",
					"descripcion" => "TBE",
					"codigo_alternativo" => "PZ2-TBE",
					"linea_id" => 5,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "QCG",
					"nombre" => "QUINUA AGRANEL CG",
					"descripcion" => "QCG",
					"codigo_alternativo" => "QUI-QCG",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "QCM",
					"nombre" => "QUINUA AGRANEL CM",
					"descripcion" => "QCM",
					"codigo_alternativo" => "QUI-QCM",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "QOG",
					"nombre" => "QUINUA AGRANEL OG",
					"descripcion" => "QOG",
					"codigo_alternativo" => "QUI-QOG",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "QOM",
					"nombre" => "QUINUA AGRANEL OM",
					"descripcion" => "QOM",
					"codigo_alternativo" => "QUI-QOM",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "QUI",
					"nombre" => "QUINUA AGRANEL",
					"descripcion" => "QUI",
					"codigo_alternativo" => "QUI-QUI",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "1QP",
					"nombre" => "QUINUA PERLADA",
					"descripcion" => "1QP",
					"codigo_alternativo" => "QUI-1QP",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "2HQ",
					"nombre" => "HARINA DE QUINUA",
					"descripcion" => "2HQ",
					"codigo_alternativo" => "QUI-2HQ",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "3HQ",
					"nombre" => "HOJUELA DE QUINUA",
					"descripcion" => "3HQ",
					"codigo_alternativo" => "QUI-3HQ",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "4QC",
					"nombre" => "QUINUA DE CUARTA",
					"descripcion" => "4QC",
					"codigo_alternativo" => "QUI-4QC",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "5QM",
					"nombre" => "QUINUA MULTICOLOR",
					"descripcion" => "5QM",
					"codigo_alternativo" => "QUI-5QM",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "6QS",
					"nombre" => "SAPONINA",
					"descripcion" => "6QS",
					"codigo_alternativo" => "QUI-6QS",
					"linea_id" => 6,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "SOYA",
					"nombre" => "SOYA AGRANEL",
					"descripcion" => "SOYA",
					"codigo_alternativo" => "SOY-SOYA",
					"linea_id" => 7,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TG2",
					"nombre" => "GRANILLO DE TRIGO TIPO 2",
					"descripcion" => "TG2",
					"codigo_alternativo" => "TRI-TG2",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TG3",
					"nombre" => "GRANILLO DE TRIGO TIPO 3",
					"descripcion" => "TG3",
					"codigo_alternativo" => "TRI-TG3",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TGT",
					"nombre" => "GRANILLO DE TRIGO",
					"descripcion" => "TGT",
					"codigo_alternativo" => "TRI-TGT",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TRI",
					"nombre" => "TRIGO AGRANEL",
					"descripcion" => "TRI",
					"codigo_alternativo" => "TRI-TRI",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "TRS",
					"nombre" => "RESIDUO DE TRIGO",
					"descripcion" => "TRS",
					"codigo_alternativo" => "TRI-TRS",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R4T",
					"nombre" => "RESIDUO DE TRIGO TIPO 3-PTS",
					"descripcion" => "R4T",
					"codigo_alternativo" => "TRI-R4T",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R3T",
					"nombre" => "RESIDUO DE TRIGO TIPO 2-PTS",
					"descripcion" => "R3T",
					"codigo_alternativo" => "TRI-R3T",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R2T",
					"nombre" => "GRANILLO DE TRIGO-PTS",
					"descripcion" => "R2T",
					"codigo_alternativo" => "TRI-R2T",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "RT2",
					"nombre" => "RESIDUO DE TRIGO TIPO 2",
					"descripcion" => "RT2",
					"codigo_alternativo" => "TRI-RT2",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "RT3",
					"nombre" => "RESIDUO DE TRIGO TIPO 3",
					"descripcion" => "RT3",
					"codigo_alternativo" => "TRI-RT3",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "R9T",
					"nombre" => "AFRECHO DE TRIGO-PTS",
					"descripcion" => "R9T",
					"codigo_alternativo" => "TRI-R9T",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "MRM",
					"nombre" => "MERMA TRIGO",
					"descripcion" => "MRM",
					"codigo_alternativo" => "TRI-MRM",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AFT",
					"nombre" => "AFRECHO",
					"descripcion" => "AFT",
					"codigo_alternativo" => "TRI-AFT",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "AF0",
					"nombre" => "AFRECHO DE TRIGO 1 KG",
					"descripcion" => "AF0",
					"codigo_alternativo" => "TRI-AF0",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "ATI",
					"nombre" => "AFRECHO DE TRIGO IMP",
					"descripcion" => "ATI",
					"codigo_alternativo" => "TRI-ATI",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				],
				[
					"codigo" => "LST1",
					"nombre" => "Trigo limpio y seco ",
					"descripcion" => "LST1",
					"codigo_alternativo" => "TRI-LST1",
					"linea_id" => 8,
					"usr_registrado" => 1,
					"estado" => "A"
				]
			]
		);
	}
}
