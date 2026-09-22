<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. ADMINISTRAR (Nivel 0)
        $menuAdmin = Menu::updateOrCreate(
            ['label' => 'Administrar', 'level' => 0],
            [
                'icon' => 'mdiCog',
                'route' => null,
                'order' => 0,
                'estado' => true,
            ]
        );

        $subUsuarios = Menu::updateOrCreate(
            ['route' => 'usuarios'],
            [
                'icon' => 'mdiAccountMultiple',
                'menu_id' => $menuAdmin->id,
                'level' => 1,
                'label' => 'Usuarios',
                'order' => 1,
                'estado' => true,
            ]
        );

        $subRolesPermisos = Menu::updateOrCreate(
            ['route' => 'roles_permisos'],
            [
                'icon' => 'mdiShieldKeyOutline',
                'menu_id' => $menuAdmin->id,
                'level' => 1,
                'label' => 'Roles y Permisos',
                'order' => 2,
                'estado' => true,
            ]
        );

        // Desactivar rutas redundantes en caso de existir previamente
        Menu::whereIn('route', ['admin_menu', 'control_acceso'])->update(['estado' => false]);

        // 2. DATOS / PARAMETRICAS (Nivel 0)
        $menuDatos = Menu::updateOrCreate(
            ['label' => 'Datos', 'level' => 0],
            [
                'icon' => 'mdiDatabase',
                'route' => null,
                'order' => 1,
                'estado' => true,
            ]
        );

        $subParametrica = Menu::updateOrCreate(
            ['route' => 'parametrica'],
            [
                'icon' => 'mdiCog',
                'menu_id' => $menuDatos->id,
                'level' => 1,
                'label' => 'Parametrica',
                'order' => 1,
                'estado' => true,
            ]
        );

        $subEmpresa = Menu::updateOrCreate(
            ['route' => 'datos_empresa'],
            [
                'icon' => 'mdiDomain',
                'menu_id' => $menuDatos->id,
                'level' => 1,
                'label' => 'Configuración Empresa / SIAT',
                'order' => 2,
                'estado' => true,
            ]
        );

        $subMigrador = Menu::updateOrCreate(
            ['route' => 'datos_migrador_respaldos'],
            [
                'icon' => 'mdiDatabaseSyncOutline',
                'menu_id' => $menuDatos->id,
                'level' => 1,
                'label' => 'Migrador de Respaldos (FoxPro)',
                'order' => 3,
                'estado' => true,
            ]
        );

        // ASIGNACIONES DE ROLES PARA TODOS LOS MENUS HIJOS
        $subMenus = [$subUsuarios->id, $subRolesPermisos->id, $subParametrica->id, $subEmpresa->id, $subMigrador->id];
        $roles = Rol::pluck('id');

        foreach ($subMenus as $menuId) {
            foreach ($roles as $rolId) {
                MenuRol::updateOrCreate(
                    [
                        'menu_id' => $menuId,
                        'rol_id' => $rolId,
                    ],
                    [
                        'check' => true,
                    ]
                );
            }
        }
    }
}
