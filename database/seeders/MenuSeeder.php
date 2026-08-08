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
		// 1. ADMINISTRAR
		$menu = new Menu();
		$menu->icon = 'mdiCog';
		$menu->label = 'Administrar';
		$menu->order = 0;
		$menu->save();

		$sub_menu_usuarios = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiAccountMultiple',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Usuarios',
			'route' => 'usuarios',
			'order' => 1,
		]);
		$sub_menu_asignacion = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCog',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Administrar Menu',
			'route' => 'admin_menu',
			'order' => 2,
		]);
		$sub_menu_control = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiAccountCogOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Control de Acceso',
			'route' => 'control_acceso',
			'order' => 3,
		]);
		$sub_menu_almacen = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiMapMarker',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Asignacion Regional',
			'route' => 'asignacion_regional',
			'order' => 4,
		]);

		// 2. DATOS
		$menu = new Menu();
		$menu->icon = 'mdiDatabase';
		$menu->label = 'Datos';
		$menu->order = 1;
		$menu->save();

		$sub_menu_parametrica = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCog',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Parametrica',
			'route' => 'parametrica',
			'order' => 1,
		]);

		$sub_menu_planta = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFactory',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Plantas',
			'route' => 'parametrica_planta',
			'order' => 2,
		]);

		// 3. RECURSOS HUMANOS
		$menu = new Menu();
		$menu->icon = 'mdiFileDocumentCheckOutline';
		$menu->label = 'RRHH';
		$menu->order = 2;
		$menu->save();

		$sub_menu_info_personal = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCloudSyncOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Inf. Persona',
			'route' => 'employee_info',
			'order' => 1,
		]);

		$sub_menu_boleta_permiso = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFileDocumentOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Boleta Permiso',
			'route' => 'my_request',
			'order' => 2,
		]);
		$sub_menu_sincronizacion = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFileDocumentOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Sincronizacion',
			'route' => 'biometric',
			'order' => 3,
		]);

		$sub_menu_funcionario = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiCloudSyncOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Funcionarios',
			'route' => 'employee',
			'order' => 4,
		]);

		$sub_menu_datos_rrhh = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFileDocumentOutline',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Datos',
			'route' => 'datos_rrhh',
			'order' => 5,
		]);

		$sub_menu_reporte_permiso = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFolderAccount',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Reporte Permisos',
			'route' => 'employee_request',
			'order' => 6,
		]);
		$sub_menu_kardex_asistencia = DB::table('acopio.menus')->insertGetId([
			'icon' => 'mdiFolderAccount',
			'menu_id' => $menu->id,
			'level' => 1,
			'label' => 'Kardex Asistencia',
			'route' => 'report',
			'order' => 7,
		]);

		// ASIGNACIONES DE ROLES PARA MENU
		foreach ([$sub_menu_usuarios, $sub_menu_asignacion, $sub_menu_control, $sub_menu_almacen, $sub_menu_parametrica, $sub_menu_planta, $sub_menu_info_personal, $sub_menu_boleta_permiso, $sub_menu_sincronizacion, $sub_menu_funcionario, $sub_menu_datos_rrhh, $sub_menu_reporte_permiso, $sub_menu_kardex_asistencia] as $menuId) {
			DB::table('acopio.menu_roles')->insert([
				'menu_id' => $menuId,
				'check' => true,
				'rol_id' => 1,
			]);
		}
	}
}
