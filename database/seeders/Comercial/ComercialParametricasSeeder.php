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
        // 1. REGISTRAR LAS 6 CATEGORIAS TARIFARIAS OFICIALES DE FOXPRO
        $categorias = [
            [
                'codigo' => 'D',
                'nombre' => 'DOMICILIARIA',
                'volumen_base' => 6.00,
                'tarifa_minima' => 12.60,
                'tarifa_excedente_base' => 2.10,
                'tarifa_alcantarillado' => 2.00,
                'aplica_ley_1886' => true, // 20% descuento 3ra edad
                'activo' => true,
            ],
            [
                'codigo' => 'A',
                'nombre' => 'COMERCIAL A',
                'volumen_base' => 6.00,
                'tarifa_minima' => 14.94,
                'tarifa_excedente_base' => 2.50,
                'tarifa_alcantarillado' => 2.00,
                'aplica_ley_1886' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'B',
                'nombre' => 'COMERCIAL B',
                'volumen_base' => 6.00,
                'tarifa_minima' => 21.12,
                'tarifa_excedente_base' => 3.00,
                'tarifa_alcantarillado' => 2.00,
                'aplica_ley_1886' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'E',
                'nombre' => 'ESPECIAL',
                'volumen_base' => 6.00,
                'tarifa_minima' => 21.48,
                'tarifa_excedente_base' => 3.58,
                'tarifa_alcantarillado' => 10.00,
                'aplica_ley_1886' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'P',
                'nombre' => 'ESTATAL O PUBLICA',
                'volumen_base' => 6.00,
                'tarifa_minima' => 21.12,
                'tarifa_excedente_base' => 2.49,
                'tarifa_alcantarillado' => 2.00,
                'aplica_ley_1886' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'L',
                'nombre' => 'LAVADO DE AUTOS',
                'volumen_base' => 6.00,
                'tarifa_minima' => 21.48,
                'tarifa_excedente_base' => 3.58,
                'tarifa_alcantarillado' => 10.00,
                'aplica_ley_1886' => false,
                'activo' => true,
            ],
        ];

        foreach ($categorias as $cat) {
            CategoriaTarifaria::updateOrCreate(
                ['codigo' => $cat['codigo']],
                $cat
            );
        }

        // 2. REGISTRAR LAS 15 ZONAS OFICIALES DE PATACAMAYA
        $zonas = [
            ['codigo' => 'ASUNCION', 'nombre' => 'Zona Asunción'],
            ['codigo' => 'CENTRAL', 'nombre' => 'Zona Central'],
            ['codigo' => 'CENTRAL NORTE', 'nombre' => 'Zona Central Norte'],
            ['codigo' => 'COMERCIAL', 'nombre' => 'Zona Comercial'],
            ['codigo' => 'ESPERANZA', 'nombre' => 'Zona Esperanza'],
            ['codigo' => 'ESTACION', 'nombre' => 'Zona Estación'],
            ['codigo' => 'JOCOPAMPA', 'nombre' => 'Zona Jocopampa'],
            ['codigo' => 'LITORAL', 'nombre' => 'Zona Litoral'],
            ['codigo' => 'MACHACAMARCA', 'nombre' => 'Zona Machacamarca'],
            ['codigo' => 'MODERNA', 'nombre' => 'Zona Moderna'],
            ['codigo' => 'N. TAYPILLANGA', 'nombre' => 'Zona Nueva Taypillanga'],
            ['codigo' => 'PORVENIR', 'nombre' => 'Zona Porvenir'],
            ['codigo' => 'PORVENIR NORTE', 'nombre' => 'Zona Porvenir Norte'],
            ['codigo' => 'LLOJLLA PARQUE', 'nombre' => 'Zona Llojlla Parque'],
            ['codigo' => 'MACHAK JAKAWI', 'nombre' => 'Zona Machak Jakawi'],
        ];

        foreach ($zonas as $z) {
            Zona::updateOrCreate(
                ['codigo' => $z['codigo']],
                ['nombre' => $z['nombre']]
            );
        }

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
                'label' => 'Toma de Lecturas',
                'route' => 'comercial_lecturas',
                'icon' => 'mdi-counter',
                'order' => 2,
            ],
            [
                'label' => 'Caja y Cobranzas',
                'route' => 'comercial_caja',
                'icon' => 'mdi-cash-register',
                'order' => 3,
            ],
            [
                'label' => 'Convenios de Pago',
                'route' => 'comercial_convenios',
                'icon' => 'mdi-handshake-outline',
                'order' => 4,
            ],
            [
                'label' => 'Cortes y Reconexiones',
                'route' => 'comercial_cortes',
                'icon' => 'mdi-pipe-disconnected',
                'order' => 5,
            ],
            [
                'label' => 'Tarifas y Zonas',
                'route' => 'comercial_tarifas',
                'icon' => 'mdi-map-marker-radius-outline',
                'order' => 6,
            ],
            [
                'label' => 'Reportes Comerciales',
                'route' => 'comercial_reportes',
                'icon' => 'mdi-chart-box-outline',
                'order' => 7,
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

        // 5. Asignar visibilidad al Rol Administrador
        $adminRole = Rol::where('guard_name', 'api')->first() ?: Rol::find(1);
        if ($adminRole) {
            MenuRol::updateOrCreate(
                ['rol_id' => $adminRole->id, 'menu_id' => $menuComercial->id],
                ['check' => true]
            );
            foreach ($submenusCreated as $sm) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $adminRole->id, 'menu_id' => $sm->id],
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
