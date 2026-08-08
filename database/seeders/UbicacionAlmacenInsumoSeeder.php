<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UbicacionAlmacenInsumoSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('insumos.ubicaciones_almacen_insumos')->insert([
			[
				'planta_id' => '1',
				'ubicacion_id' => '1',
				'descripcion' => 'Zona de Alimentos',
				'sigla' => 'zal',
				'user_id' => '1',
			],

			[
				'planta_id' => '2',
				'ubicacion_id' => '2',
				'descripcion' => 'Zona A',
				'sigla' => 'ZoA',
				'user_id' => '1',
			],

			[
				'planta_id' => '3',
				'ubicacion_id' => '3',
				'descripcion' => 'Pasillo Principal',
				'sigla' => 'pasillo01',
				'user_id' => '1',
			],

			[
				'planta_id' => '4',
				'ubicacion_id' => '4',
				'descripcion' => 'Estante 001',
				'sigla' => 'Est01',
				'user_id' => '1',
			],

			[
				'planta_id' => '5',
				'ubicacion_id' => '5',
				'descripcion' => 'Estante 002',
				'sigla' => 'Est02',
				'user_id' => '1',
			],

			[
				'planta_id' => '6',
				'ubicacion_id' => '1',
				'descripcion' => 'Ambiente Laboratorio Alevines',
				'sigla' => 'lab001',
				'user_id' => '1',
			],

			[
				'planta_id' => '7',
				'ubicacion_id' => '2',
				'descripcion' => 'Planta Frigorifico',
				'sigla' => 'frig001',
				'user_id' => '1',
			],

			[
				'planta_id' => '8',
				'ubicacion_id' => '3',
				'descripcion' => 'Vitrina 001, Ambiente planta Racion',
				'sigla' => 'vitrina01',
				'user_id' => '1',
			],

			[
				'planta_id' => '10',
				'ubicacion_id' => '3',
				'descripcion' => 'Zona de Ropa',
				'sigla' => 'zr',
				'user_id' => '1',
			],

			[
				'planta_id' => '9',
				'ubicacion_id' => '3',
				'descripcion' => 'Zona de Electrónicos',
				'sigla' => 'ae',
				'user_id' => '1',
			],

			[
				'planta_id' => '1',
				'ubicacion_id' => '3',
				'descripcion' => 'Zona de Ropa',
				'sigla' => 'zr',
				'user_id' => '1',
			],

			[
				'planta_id' => '1',
				'ubicacion_id' => '2',
				'descripcion' => 'Ambiente Laboratorio Alevines',
				'sigla' => 'lab001',
				'user_id' => '1',
			],

			[
				'planta_id' => '1',
				'ubicacion_id' => '4',
				'descripcion' => 'Estante 001',
				'sigla' => 'Est01',
				'user_id' => '1',
			],
		]);
		$role = Role::where('name', 'Administrador General')->first();
		$usuario = User::find(1);
		$usuario->syncRoles($role);
		$usuario->sellingpoints()->sync([1]);
		$usuario->givePermissionTo('SIGP');
		$usuario = User::find(131);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(407);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(405);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(376);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(157);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(351);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
		$usuario = User::find(470);
		$usuario->syncRoles($role);
		$usuario->givePermissionTo('SIGP');
		$usuario->sellingpoints()->sync([1]);
	}
}
