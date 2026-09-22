<?php

declare(strict_types=1);

namespace Database\Seeders\Comercial;

use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\Zona;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ComercialParametricasSeeder extends Seeder
{
    public function run(): void
    {
        // 1. OBTENER O CREAR PLIEGO TARIFARIO OFICIAL (Las categorías se poblarán desde la migración)
        \App\Models\Comercial\PaqueteTarifario::firstOrCreate(
            ['codigo' => 'PLIEGO_EMAPAP_VIGENTE'],
            [
                'nombre' => 'Pliego Tarifario Oficial EMAPAP (Vigente)',
                'resolucion_legal' => 'Resolución Administrativa Regulatoria AAPS / EMAPAP',
                'fecha_inicio_vigencia' => '2024-01-01',
                'es_vigente' => true,
                'descripcion' => 'Estructura tarifaria con categorías y escalas variables de consumo en m³ según FoxPro',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'MIGRACION',
                '_usuario_creacion' => 1,
            ]
        );

        // 3. MENÚ PRINCIPAL "Gestión Comercial"
        $menuComercial = Menu::updateOrCreate(
            ['label' => 'Gestión Comercial', 'level' => 0],
            [
                'icon' => 'mdi-water-pump',
                'route' => null,
                'order' => 5,
                'estado' => true,
            ]
        );

        // 4. SUBMÓDULOS DE COMERCIAL
        $submodulos = [
            [
                'label' => 'Padrón de Abonados',
                'route' => 'comercial_abonados',
                'icon' => 'mdi-account-group',
                'order' => 1,
            ],
            [
                'label' => 'Ciclo de Períodos',
                'route' => 'comercial_periodos',
                'icon' => 'mdi-calendar-sync',
                'order' => 2,
            ],
            [
                'label' => 'Toma de Lecturas',
                'route' => 'comercial_lecturas',
                'icon' => 'mdi-counter',
                'order' => 3,
            ],
            [
                'label' => 'Caja y Cobranzas',
                'route' => 'comercial_caja',
                'icon' => 'mdi-cash-register',
                'order' => 4,
            ],
            [
                'label' => 'Convenios de Pago',
                'route' => 'comercial_convenios',
                'icon' => 'mdi-handshake-outline',
                'order' => 5,
            ],
            [
                'label' => 'Cortes y Reconexiones',
                'route' => 'comercial_cortes',
                'icon' => 'mdi-pipe-disconnected',
                'order' => 6,
            ],
            [
                'label' => 'Zonas y Calles',
                'route' => 'comercial_zonas_calles',
                'icon' => 'mdi-map-marker-multiple',
                'order' => 7,
            ],
            [
                'label' => 'Estructura Tarifaria',
                'route' => 'comercial_tarifas',
                'icon' => 'mdi-currency-usd',
                'order' => 8,
            ],
            [
                'label' => 'Reportes Comerciales',
                'route' => 'comercial_reportes',
                'icon' => 'mdi-chart-box-outline',
                'order' => 9,
            ],
        ];

        $submenusCreated = [];
        foreach ($submodulos as $sub) {
            $submenusCreated[] = Menu::updateOrCreate(
                ['route' => $sub['route'], 'menu_id' => $menuComercial->id],
                [
                    'label' => $sub['label'],
                    'icon' => $sub['icon'],
                    'level' => 1,
                    'order' => $sub['order'],
                    'estado' => true,
                ]
            );
        }

        // 5. Asignar visibilidad a todos los Roles (incluyendo Administrador)
        $roles = Rol::all();
        $adminRole = Rol::where('guard_name', 'api')->first() ?: Rol::find(1);
        foreach ($roles as $role) {
            MenuRol::updateOrCreate(
                ['rol_id' => $role->id, 'menu_id' => $menuComercial->id],
                ['check' => true]
            );
            foreach ($submenusCreated as $sm) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $role->id, 'menu_id' => $sm->id],
                    ['check' => true]
                );
            }
        }

        // 6. Permisos Spatie Granulares para Comercial
        $permisosData = [
            ['name' => 'comercial.abonados.ver', 'module' => 'Comercial', 'description' => 'Ver listado y ficha de abonados'],
            ['name' => 'comercial.abonados.crear', 'module' => 'Comercial', 'description' => 'Registrar y modificar abonados'],
            ['name' => 'comercial.lecturas.registrar', 'module' => 'Comercial', 'description' => 'Registrar lecturas de medidores y liquidar mes'],
            ['name' => 'comercial.caja.cobrar', 'module' => 'Comercial', 'description' => 'Cobrar en ventanilla y emitir facturas/recibos'],
            ['name' => 'comercial.convenios.administrar', 'module' => 'Comercial', 'description' => 'Suscripción y seguimiento de convenios de pago'],
            ['name' => 'comercial.cortes.administrar', 'module' => 'Comercial', 'description' => 'Gestionar órdenes de corte y reconexión'],
            ['name' => 'comercial.tarifas.administrar', 'module' => 'Comercial', 'description' => 'Configurar categorías tarifarias y zonas'],
            ['name' => 'comercial.reportes.ver', 'module' => 'Comercial', 'description' => 'Ver reportes comerciales y recaudaciones'],
        ];

        foreach ($permisosData as $p) {
            $perm = Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => 'api'],
                [
                    'module' => $p['module'],
                    'description' => $p['description'],
                ]
            );

            if ($adminRole) {
                $adminRole->givePermissionTo($perm);
            }
        }
    }
}
