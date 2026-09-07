<?php

namespace Database\Seeders;

use App\Models\RolUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // LISTA DE PERMISOS GRANULARES DEL SISTEMA BASE
        $permissions = [
            // Módulo Administración de Usuarios
            'admin.usuarios.ver',
            'admin.usuarios.crear',
            'admin.usuarios.editar',
            'admin.usuarios.eliminar',
            'admin.usuarios.acceso',

            // Módulo Administración de Menús
            'admin.menus.ver',
            'admin.menus.crear',
            'admin.menus.editar',
            'admin.menus.eliminar',
            'admin.menus.reordenar',

            // Módulo Control de Acceso
            'admin.control_acceso.ver',
            'admin.control_acceso.guardar',

            // Módulo Paramétricas y Catálogos
            'parametricas.ver',
            'parametricas.crear',
            'parametricas.editar',

            // Permiso legado de compatibilidad
            'SIGP',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'api']);
        }

        // ROLES DEL SISTEMA BASE
        $adminRole = Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'api']);
        $operadorRole = Role::firstOrCreate(['name' => 'Operador del Sistema', 'guard_name' => 'api']);
        $consultorRole = Role::firstOrCreate(['name' => 'Consultor', 'guard_name' => 'api']);

        // ASIGNAR TODOS LOS PERMISOS AL ADMINISTRADOR GENERAL
        $adminRole->syncPermissions(Permission::all());

        // PERMISOS PARA OPERADOR
        $operadorRole->syncPermissions([
            'admin.usuarios.ver',
            'admin.control_acceso.ver',
            'parametricas.ver',
            'parametricas.crear',
            'SIGP',
        ]);

        // PERMISOS PARA CONSULTOR
        $consultorRole->syncPermissions([
            'admin.usuarios.ver',
            'parametricas.ver',
            'SIGP',
        ]);

        // ASIGNAR ROL AL PRIMER USUARIO
        $userAdmin = User::first();
        if ($userAdmin) {
            $userAdmin->assignRole($adminRole);
            $userAdmin->givePermissionTo(Permission::all());

            RolUser::updateOrCreate(
                ['usuario_id' => $userAdmin->id],
                [
                    'rol_id' => $adminRole->id,
                    'estado' => true,
                ]
            );
        }
    }
}
