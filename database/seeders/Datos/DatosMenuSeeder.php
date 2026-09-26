<?php

declare(strict_types=1);

namespace Database\Seeders\Datos;

use App\Models\Menu;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DatosMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menuDatos = Menu::where('label', 'Datos')->first();

        if (!$menuDatos) {
            $menuDatos = Menu::create([
                'label' => 'Datos',
                'route' => null,
                'icon' => 'mdi-database',
                'level' => 0,
                'order' => 2,
                'estado' => true,
            ]);
        }

        // 1. Submenú Configuración Empresa y SIAT
        $submenu = Menu::updateOrCreate(
            [
                'menu_id' => $menuDatos->id,
                'route' => 'datos_empresa',
            ],
            [
                'label' => 'Configuración Empresa y SIAT',
                'icon' => 'mdi-office-building-cog',
                'level' => 1,
                'order' => 2,
                'estado' => true,
            ]
        );

        // 2. Permiso asociado
        $permiso = Permission::firstOrCreate(
            ['name' => 'datos.empresa.gestionar'],
            [
                'guard_name' => 'api',
                'description' => 'Configurar datos de la empresa, credenciales fiscales y parámetros SIAT',
                'module' => 'Configuración Empresa y SIAT',
            ]
        );

        // 3. Asignar permiso a rol Administrador
        $rolAdmin = Rol::where('name', 'Administrador General')
            ->orWhere('name', 'Administrador')
            ->orWhere('name', 'admin')
            ->first();

        if ($rolAdmin) {
            $rolAdmin->givePermissionTo($permiso);

            // Asignar en tabla menu_roles
            \App\Models\MenuRol::updateOrCreate(
                [
                    'rol_id' => $rolAdmin->id,
                    'menu_id' => $submenu->id,
                ],
                [
                    'check' => true,
                ]
            );

            \App\Models\MenuRol::updateOrCreate(
                [
                    'rol_id' => $rolAdmin->id,
                    'menu_id' => $menuDatos->id,
                ],
                [
                    'check' => true,
                ]
            );
        }
    }
}
