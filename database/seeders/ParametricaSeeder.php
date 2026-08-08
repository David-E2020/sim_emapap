<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametricaSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		//TIPO PLANTA
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO PLANTA',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_PLANTA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PROPIO',
			'param_codigo' => 'PA',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_PLANTA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PARTICULAR',
			'param_codigo' => 'PAR',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_PLANTA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'COMERCIALIZACION',
			'param_codigo' => 'COM',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_PLANTA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CONVENIO',
			'param_codigo' => 'CON',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_PLANTA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TIPO PUNTO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO PUNTO',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGENIO',
			'param_codigo' => 'ING',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'MOLINO',
			'param_codigo' => 'MOL',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ALMACEN',
			'param_codigo' => 'ALM',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SILO',
			'param_codigo' => 'SIL',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);


		DB::table('parametricas')->insert([
			'param_nombre' => 'CENTRO REGISTRO',
			'param_codigo' => 'CR',
			'param_valor' => 5,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'OCCIDENTE',
			'param_codigo' => 'OCC',
			'param_valor' =>  6,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'OFICINA',
			'param_codigo' => 'OFI',
			'param_valor' => 7,
			'param_tabla' => 'TABLA_TIPO_PUNTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);



		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO MONEDA',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_MONEDA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TIPO MONEDA
		DB::table('parametricas')->insert([
			'param_nombre' => 'BS',
			'param_codigo' => 'BS',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_MONEDA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'Sus',
			'param_codigo' => 'Sus',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_MONEDA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO INSCRIPCION',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_INSCRIPCION',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TIPO INSCRIPCION
		DB::table('parametricas')->insert([
			'param_nombre' => 'DEUDOR',
			'param_codigo' => 'DEU',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_INSCRIPCION',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'NO BENEFICIARIO(INDEPENDIENTE)',
			'param_codigo' => 'NBI',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_INSCRIPCION',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA VOLUMEN',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_VOLUMEN',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TIPO VOLUMEN
		DB::table('parametricas')->insert([
			'param_nombre' => 'POR TONELADA [TM]',
			'param_codigo' => 'TM',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_VOLUMEN',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => '',
			'param_codigo' => '',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_VOLUMEN',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO DOCUMENTO',
			'param_codigo' => 'ORIGIN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TIPO DOCUMENTO
		DB::table('parametricas')->insert([
			'param_nombre' => 'CEDULA IDENTIDAD',
			'param_codigo' => 'CI',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'LICENCIA CONDUCIR',
			'param_codigo' => 'LC',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CONTRATO',
			'param_codigo' => 'C',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CERTIFICACION POA',
			'param_codigo' => 'CP',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'HOJA DE RUTA',
			'param_codigo' => 'HR',
			'param_valor' => 5,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'NOTA INTERNA',
			'param_codigo' => 'NI',
			'param_valor' => 6,
			'param_tabla' => 'TABLA_TIPO_DOCUMENTO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA TIPO ANALISIS',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TABLA TIPO ANALISIS
		DB::table('parametricas')->insert([
			'param_nombre' => 'HUMEDAD',
			'param_codigo' => 'HUM',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'IMPUREZA',
			'param_codigo' => 'IMP',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO PARTIDO',
			'param_codigo' => 'PAR',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO DAÑADO y/o ATACADO POR INSECTOS',
			'param_codigo' => 'DAN',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO INFESTADO',
			'param_codigo' => 'DAN',
			'param_valor' => 5,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RENDIMIENTO',
			'param_codigo' => 'REN',
			'param_valor' => 6,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GERMINADO',
			'param_codigo' => 'GER',
			'param_valor' => 7,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PORCENTUAL PESO',
			'param_codigo' => 'GVE',
			'param_valor' => 8,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PUNTA NEGRA',
			'param_codigo' => 'GVE',
			'param_valor' => 9,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PORCENTUAL INSECTO',
			'param_codigo' => 'GVE',
			'param_valor' => 10,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO HECTOLITRICO',
			'param_codigo' => 'PH',
			'param_valor' => 11,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PANZA BLANCA',
			'param_codigo' => 'PBL',
			'param_valor' => 12,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ENTERO',
			'param_codigo' => 'REN',
			'param_valor' => 13,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TAMAÑO DE GRANO',
			'param_codigo' => 'TAM',
			'param_valor' => 14,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TRES CUARTOS',
			'param_codigo' => 'TCU',
			'param_valor' => 15,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ATACADO X INSECTO(Muerto)',
			'param_codigo' => 'AIN',
			'param_valor' => 16,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ARROCILLO',
			'param_codigo' => 'ARO',
			'param_valor' => 17,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CALIDAD DE GRANO',
			'param_codigo' => 'CAL',
			'param_valor' => 18,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO ROJO',
			'param_codigo' => 'GRO',
			'param_valor' => 19,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO VANO',
			'param_codigo' => 'GVA',
			'param_valor' => 20,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO VERDE',
			'param_codigo' => 'GVE',
			'param_valor' => 21,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'GRANO YESOSO',
			'param_codigo' => 'YPB',
			'param_valor' => 22,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'MERMA',
			'param_codigo' => 'MER',
			'param_valor' => 23,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TEMPERATURA INTERIOR',
			'param_codigo' => 'TI',
			'param_valor' => 24,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TEMPERATURA EXTERIOR',
			'param_codigo' => 'TE',
			'param_valor' => 25,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PRESENCIA DE OFF-FLAVOR ',
			'param_codigo' => '',
			'param_valor' => 26,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SABOR(CARACTERISTICO)',
			'param_codigo' => '',
			'param_valor' => 27,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'OLOR(CARACTERISTICO)',
			'param_codigo' => '',
			'param_valor' => 28,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'OJOS(CARACTERISTICO BRILLANTE)',
			'param_codigo' => '',
			'param_valor' => 29,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PIEL(TORNASOLADO CON MUCOSIDAD ACUOSA)',
			'param_codigo' => '',
			'param_valor' => 30,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'BRANQUIAS (COLOR BRILLANTE SIN MUCOSIDAD)',
			'param_codigo' => '',
			'param_valor' => 31,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CARNE (FIRME Y ELÁSTICA)',
			'param_codigo' => '',
			'param_valor' => 32,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO DE LA MUESTRA (Kg)',
			'param_codigo' => '',
			'param_valor' => 33,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'LONGITUD ESTÁNDAR (cm)',
			'param_codigo' => '',
			'param_valor' => 34,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'LONGITUD TOTAL (cm)',
			'param_codigo' => '',
			'param_valor' => 35,
			'param_tabla' => 'TABLA_TIPO_ANALISIS',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//DATOS DE BALANZA
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO BRUTO',
			'param_codigo' => 'LC',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO TARA',
			'param_codigo' => 'LC',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO NETO',
			'param_codigo' => 'LC',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO LIQUIDO',
			'param_codigo' => 'LC',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_DATOS_BALANZA',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TABLA TIPO ESTADO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_ESTADO',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PENDIENTE',
			'param_codigo' => 'PE',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'VERIFICADO',
			'param_codigo' => 'VE',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RECIBIDO',
			'param_codigo' => 'RE',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'APROBADO',
			'param_codigo' => 'AP',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ENVIADO',
			'param_codigo' => 'EN',
			'param_valor' => 5,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RECHAZADO',
			'param_codigo' => 'RE',
			'param_valor' => 6,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RECHAZO RECIBIDO',
			'param_codigo' => 'RR',
			'param_valor' => 7,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RECHAZO APROBACION',
			'param_codigo' => 'RA',
			'param_valor' => 8,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'RECHAZO ENVIO',
			'param_codigo' => 'RE',
			'param_valor' => 9,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESADO',
			'param_codigo' => 'IN',
			'param_valor' => 10,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ANULADO',
			'param_codigo' => 'A',
			'param_valor' => 11,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PAGADO',
			'param_codigo' => 'PA',
			'param_valor' => 12,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'VALIDADO',
			'param_codigo' => 'VA',
			'param_valor' => 13,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//MOVIMIENTOS PRODUCTOS EMAPA
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD GENERADA',
			'param_codigo' => 'SG',
			'param_valor' => 15,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD ENVIADO A LOGISTICA',
			'param_codigo' => 'SEL',
			'param_valor' => 17,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD RECIBIDA POR LOGISTICA',
			'param_codigo' => 'SEL',
			'param_valor' => 18,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ASIGNACION LOGISTICA',
			'param_codigo' => 'AL',
			'param_valor' => 14,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD ENVIADO A ORIGEN',
			'param_codigo' => 'SEAG',
			'param_valor' => 19,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ORIGEN',
			'param_codigo' => 'BS',
			'param_valor' => 20,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD ENVIADO A DESTINO',
			'param_codigo' => 'SEAG',
			'param_valor' => 21,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO DESTINO',
			'param_codigo' => 'BI',
			'param_valor' => 22,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'REGULARIZACION DE CONTRATO',
			'param_codigo' => 'RC',
			'param_valor' => 23,
			'param_tabla' => 'TABLA_TIPO_ESTADO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		//TABLA TIPO ESTADO ACOPIO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_codigo' => 'ORIGEN',
			'param_valor' => 0,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'DATOS PRODUCTOR',
			'param_codigo' => 'DP',
			'param_valor' => 1,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'DATOS TRANSPORTE',
			'param_codigo' => 'DT',
			'param_valor' => 2,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ANALISIS GRANO',
			'param_codigo' => 'AG',
			'param_valor' => 3,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO INGRESO',
			'param_codigo' => 'PI',
			'param_valor' => 4,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'PESO SALIDA',
			'param_codigo' => 'PS',
			'param_valor' => 5,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'FINALIZADO',
			'param_codigo' => 'FI',
			'param_valor' => 6,
			'param_tabla' => 'TABLA_TIPO_ESTADO_ACOPIO',
			'param_usr_registrado' => 1,
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'ELABORADO',
			'param_codigo' => 'EL',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'REVISION',
			'param_codigo' => 'RE',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'APROBADO',
			'param_codigo' => 'AP',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'ENTREGADO',
			'param_codigo' => 'EN',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FINALIZADO',
			'param_codigo' => 'FI',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'RECHAZADO',
			'param_codigo' => 'RE',
			'param_descripcion' => '',
			'param_valor' => '6',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'CADUCADO',
			'param_codigo' => 'CA',
			'param_descripcion' => '',
			'param_valor' => '7',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ANULADO',
			'param_codigo' => 'AN',
			'param_descripcion' => '',
			'param_valor' => '8',
			'param_tabla' => 'TABLA_TIPO_ESTADO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'MATERIA PRIMA',
			'param_codigo' => 'MP',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SUB PRODUCTOS EMAPA',
			'param_codigo' => 'SPE',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INSUMOS',
			'param_codigo' => 'SPT',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'MATERIAL DE ESCRITORIO ',
			'param_codigo' => 'ART',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ALIMENTO BALANCEADO',
			'param_codigo' => 'ABA',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_PRODUCTO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		//TIPO SERVICIO CONTRATO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_SERVICIO_CONTRATO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_SERVICIO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'RECEPCION SECADO LIMPIEZA ALMACENAMIENTO DESPACHO',
			'param_codigo' => 'RSLADM',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_SERVICIO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'RECEPCION LIMPIEZA ALMACENAMIENTO DESPACHO',
			'param_codigo' => 'RLADM',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_SERVICIO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'RTMTD',
			'param_codigo' => 'RTMTD',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_SERVICIO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		//TIPO MOVIMIENTO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_MOVIMIENTO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO ACOPIO',
			'param_codigo' => 'ACO-I',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ACOPIO',
			'param_codigo' => 'ACO-S',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO INVENTARIO ACOPIO',
			'param_codigo' => 'INV-I',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ORDEN PRODUCCION',
			'param_codigo' => 'OP-S',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO ORDEN PRODUCCION',
			'param_codigo' => 'OP-I',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ORDEN DE DESPACHO',
			'param_codigo' => 'OD-S',
			'param_descripcion' => '',
			'param_valor' => '6',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ORDEN DE TRASLADO',
			'param_codigo' => 'OT-S',
			'param_descripcion' => '',
			'param_valor' => '7',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO ORDEN DE TRASLADO',
			'param_codigo' => 'OT-I',
			'param_descripcion' => '',
			'param_valor' => '8',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO INSUMOS',
			'param_codigo' => 'OI-I',
			'param_descripcion' => '',
			'param_valor' => '9',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA INSUMOS ORDEN PRODUCCION',
			'param_codigo' => 'OP-S',
			'param_descripcion' => '',
			'param_valor' => '10',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SALIDA ORDEN DE CARGA',
			'param_codigo' => 'OC-S',
			'param_descripcion' => '',
			'param_valor' => '11',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO ORDEN DE DESPACHO',
			'param_codigo' => 'OD-I',
			'param_descripcion' => '',
			'param_valor' => '12',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO ORDEN DE CARGA',
			'param_codigo' => 'OC-I',
			'param_descripcion' => '',
			'param_valor' => '13',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO INICIAL MIGRACION',
			'param_codigo' => 'II-I',
			'param_descripcion' => '',
			'param_valor' => '13',
			'param_tabla' => 'TABLA_TIPO_MOVIMIENTO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		//TIPO MOVIMIENTO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_SOLICITUD',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN PRODUCCION MATERIA PRIMA',
			'param_codigo' => 'OP-MP',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN PRODUCCION ALIMENTO BALANCEADO',
			'param_codigo' => 'OP-ABA',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN DESPACHO',
			'param_codigo' => 'OD',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN TRASLADO',
			'param_codigo' => 'OT',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'SOLICITUD DE PAGO ACOPIO',
			'param_codigo' => 'SPA',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO MATERIA PRIMA ACOPIO',
			'param_codigo' => 'IMPA',
			'param_descripcion' => '',
			'param_valor' => '6',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN CARGA',
			'param_codigo' => 'OC',
			'param_descripcion' => '',
			'param_valor' => '7',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		//OBSERVADO
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN ENVIO',
			'param_codigo' => 'OE',
			'param_descripcion' => '',
			'param_valor' => '8',
			'param_tabla' => 'TABLA_TIPO_SOLICITUD',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		//TIPO CONTRATO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_CONTRATO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN PRODUCCION',
			'param_codigo' => 'ORP',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'LOGISTICA',
			'param_codigo' => 'LOG',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'B',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TRASLADO DE LOGISTICA',
			'param_codigo' => 'TL',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_CONTRATO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		//TIPO INGRESO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_INGRESO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO POR COMPRAS',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO POR DEVOLUCION',
			'param_codigo' => 'IPD',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO POR TRASLADO',
			'param_codigo' => 'IPT',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO POR RECEPCION',
			'param_codigo' => 'IPR',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INGRESO POR IMPORTACION',
			'param_codigo' => 'IPI',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		//TIPO INGRESO INSUMOS
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FONDOS EN AVANCE',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'ORDEN COMPRA',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'CONTRATO',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'REEMBOLSO',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'REPOCISIONES',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'INICIAL',
			'param_codigo' => 'IPC',
			'param_descripcion' => '',
			'param_valor' => '6',
			'param_tabla' => 'TABLA_TIPO_INGRESO_INSUMOS',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		//TIPO CONTRATO
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_CONTRATO_SERVICIO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_CONTRATO_SERVICIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'CON CONTRATO (C)',
			'param_codigo' => 'CCC',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_CONTRATO_SERVICIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'SIN CONTRATO (PRELIMINAR)',
			'param_codigo' => 'SCP',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_CONTRATO_SERVICIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_MODO_ACOPIO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_MODO_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'NORMAL',
			'param_codigo' => 'NRL',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_MODO_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'LIMPIO Y SECO',
			'param_codigo' => 'LYS',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_MODO_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'OTROS',
			'param_codigo' => 'OTR',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_MODO_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FORMULA NORMAL',
			'param_codigo' => 'FNL',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FORMULA DIRECTA',
			'param_codigo' => 'FDR',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FORMULA RANGO',
			'param_codigo' => 'FRG',
			'param_descripcion' => '',
			'param_valor' => '3',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FORMULA CLASE',
			'param_codigo' => 'FCL',
			'param_descripcion' => '',
			'param_valor' => '4',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'FORMULA TIPIFICADOR',
			'param_codigo' => 'FTD',
			'param_descripcion' => '',
			'param_valor' => '5',
			'param_tabla' => 'TABLA_TIPO_FORMULA_ACOPIO',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);


		//TIPO TRASNPORTE
		DB::table('parametricas')->insert([
			'param_nombre' => 'TABLA_TIPO_TRANSPORTE',
			'param_codigo' => 'ORIGEN',
			'param_descripcion' => '',
			'param_valor' => '0',
			'param_tabla' => 'TABLA_TIPO_TRANSPORTE',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);

		DB::table('parametricas')->insert([
			'param_nombre' => 'PRIVADO',
			'param_codigo' => 'OP-MP',
			'param_descripcion' => '',
			'param_valor' => '1',
			'param_tabla' => 'TABLA_TIPO_TRANSPORTE',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
		DB::table('parametricas')->insert([
			'param_nombre' => 'EMAPA',
			'param_codigo' => 'OP-ABA',
			'param_descripcion' => '',
			'param_valor' => '2',
			'param_tabla' => 'TABLA_TIPO_TRANSPORTE',
			'param_usr_registrado' => '1',
			'param_estado' => 'A',
		]);
	}
}
