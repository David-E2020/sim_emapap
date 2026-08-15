<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// Limpiar las tablas de menús antes de poblar la estructura limpia
		DB::statement('TRUNCATE TABLE acopio.menu_roles RESTART IDENTITY CASCADE');
		DB::statement('TRUNCATE TABLE acopio.menus RESTART IDENTITY CASCADE');

		// 1. ADMINISTRAR
		$menuAdmin = new Menu();
		$menuAdmin->icon = 'mdiCog';
		$menuAdmin->label = 'Administrar';
		$menuAdmin->order = 0;
		$menuAdmin->save();

		$sub_menu_usuarios = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiAccountMultiple',
			'menu_id' => $menuAdmin->id,
			'level' => 1,
			'label' => 'Usuarios',
			'route' => 'usuarios',
			'order' => 1,
		]);
		$sub_menu_asignacion = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCog',
			'menu_id' => $menuAdmin->id,
			'level' => 1,
			'label' => 'Administrar Menu',
			'route' => 'admin_menu',
			'order' => 2,
		]);
		$sub_menu_control = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiAccountCogOutline',
			'menu_id' => $menuAdmin->id,
			'level' => 1,
			'label' => 'Control de Acceso',
			'route' => 'control_acceso',
			'order' => 3,
		]);

		// 2. DATOS / PARAMETRICAS
		$menuDatos = new Menu();
		$menuDatos->icon = 'mdiDatabase';
		$menuDatos->label = 'Datos';
		$menuDatos->order = 1;
		$menuDatos->save();

		$sub_menu_parametrica = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCog',
			'menu_id' => $menuDatos->id,
			'level' => 1,
			'label' => 'Parametrica',
			'route' => 'parametrica',
			'order' => 1,
		]);

		// ASIGNACIONES DE ROLES PARA MENU (Para todos los roles activos o Rol 1)
		$roles = DB::table('acopio.roles')->pluck('id');
		foreach ([$sub_menu_usuarios, $sub_menu_asignacion, $sub_menu_control, $sub_menu_parametrica] as $menuId) {
			foreach ($roles as $rolId) {
				DB::table('acopio.menu_roles')->insert([
					'menu_id' => $menuId,
					'check' => true,
					'rol_id' => $rolId,
				]);
			}
		}
	}
}
