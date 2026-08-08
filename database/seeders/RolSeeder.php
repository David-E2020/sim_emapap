<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {

		$permission = Permission::create(['name' => 'SIGP']);
		//$permission = Permission::create(['name' => 'SIMON_PRUEBAS']);
		$permission = Permission::where('name', 'SIGP')->first();
		$role = Role::create(['name' => 'Administrador General']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Administrador Produccion']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Administrador Acopio']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Administrador Insumos']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Administrador Producto Terminado']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Encargado Almacen Produccion']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Encargado Almacen Acopio']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Encargado Almacen Insumos']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Encargado Almacen Producto Terminado']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Tecnico Produccion']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Tecnico Acopio']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Tecnico Insumos']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Tecnico Producto Terminado']);
		$role->givePermissionTo($permission);
		$role = Role::create(['name' => 'Responsable']);
		$role->givePermissionTo($permission);
		#Roles usuarios

		DB::table('acopio.rol_users')->insert([
			[
				'rol_id' => 1,
				'usuario_id' => 1,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 131,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 407,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 405,
			],
			// [
			// 	'rol_id' => 1,
			// 	'usuario_id' => 527,
			// ],
			[
				'rol_id' => 1,
				'usuario_id' => 376,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 157,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 351,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 470,
			],
			[
				'rol_id' => 1,
				'usuario_id' => 371,
			],
		]);
	}
}
