<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for PRODUCTION.
     * Carga menús completos, roles, permisos granulares, paramétricas y catálogos base.
     * Excluye expresamente cualquier dato demo/ficticio.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Usuarios y Roles Base
        $this->call([
            UserSeeder::class,
            RolSeeder::class,
            MenuSeeder::class,
            ParametricaSeeder::class,
        ]);

        // 2. Módulo Datos / Empresa SIAT
        $this->call([
            \Database\Seeders\Datos\DatosMenuSeeder::class,
        ]);

        // 3. Módulo Recursos Humanos (Estructural y Catálogos)
        $this->call([
            \Database\Seeders\Rrhh\DepartamentoSeeder::class,
            \Database\Seeders\Rrhh\RrhhParametricasSeeder::class,
            \Database\Seeders\Rrhh\FeriadoSeeder::class,
            \Database\Seeders\Rrhh\PermisoJustificacionSeeder::class,
            \Database\Seeders\Rrhh\RrhhMenuAndPermissionsSeeder::class,
        ]);

        // 4. Módulo Correspondencia y Trámites (Estructural y Plantillas Oficiales)
        $this->call([
            \Database\Seeders\Correspondencia\CorrespondenciaParametricasSeeder::class,
            \Database\Seeders\Correspondencia\PlantillasDocumentosSeeder::class,
            \Database\Seeders\Correspondencia\CorrespondenciaMenuAndPermissionsSeeder::class,
        ]);

        // 5. Módulo Facturación SIAT (Sucursal 0, Punto Venta 0, Catálogo Servicios SIN, Menús y Permisos)
        $this->call([
            \Database\Seeders\Facturacion\EmapapServiciosSeeder::class,
            \Database\Seeders\Facturacion\FacturacionMenuAndPermissionsSeeder::class,
        ]);

        // 6. Módulo Gestión Comercial (Categorías, Zonas, Menús y Permisos)
        $this->call([
            \Database\Seeders\Comercial\ComercialParametricasSeeder::class,
        ]);

        // 7. Módulo Contabilidad y Finanzas (Plan Único de Cuentas, Menús y Permisos)
        $this->call([
            \Database\Seeders\Contabilidad\PlanCuentasSeeder::class,
            \Database\Seeders\Contabilidad\ContabilidadMenuSeeder::class,
        ]);

        // 8. Sincronización Final de Permisos Spatie
        // Asegurar que el rol Administrador General y el usuario 'admin' tengan el 100% de los permisos creados
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = Permission::all();
        $adminRole = Role::where('name', 'Administrador General')->where('guard_name', 'api')->first()
            ?: Role::where('name', 'Administrador General')->first();

        if ($adminRole) {
            $adminRole->syncPermissions($allPermissions);
        }

        $adminUser = User::where('usr_usuario', 'admin')->first();
        if ($adminUser && $adminRole) {
            if (!$adminUser->hasRole($adminRole->name)) {
                $adminUser->assignRole($adminRole);
            }
            $adminUser->syncPermissions($allPermissions);
        }
    }
}
