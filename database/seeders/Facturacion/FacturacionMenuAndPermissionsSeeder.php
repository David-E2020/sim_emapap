<?php

declare(strict_types=1);

namespace Database\Seeders\Facturacion;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FacturacionMenuAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Menú Padre "Facturación Electrónica SIAT"
        $menuFacturacion = Menu::updateOrCreate(
            ['label' => 'Facturación SIAT', 'level' => 0],
            [
                'icon' => 'mdi-receipt-text-check',
                'route' => null,
                'order' => 4,
                'estado' => true,
            ]
        );

        // 2. Submódulos de Facturación
        $submodulos = [
            [
                'label' => 'Caja y Facturación en Ventanilla',
                'route' => 'facturacion_caja',
                'icon' => 'mdi-cash-register',
                'order' => 1,
            ],
            [
                'label' => 'Facturación Libre / Especial',
                'route' => 'facturacion_crear',
                'icon' => 'mdi-receipt-text-plus',
                'order' => 2,
            ],
            [
                'label' => 'Bandeja de Facturas',
                'route' => 'facturacion_bandeja',
                'icon' => 'mdi-file-table-box-outline',
                'order' => 3,
            ],
            [
                'label' => 'Facturas de Contingencia',
                'route' => 'facturacion_contingencias',
                'icon' => 'mdi-file-document-alert-outline',
                'order' => 4,
            ],
            [
                'label' => 'Eventos Significativos',
                'route' => 'facturacion_eventos',
                'icon' => 'mdi-alert-octagon-outline',
                'order' => 5,
            ],
            [
                'label' => 'Sucursales y Puntos de Venta',
                'route' => 'facturacion_puntos_venta',
                'icon' => 'mdi-store-cog-outline',
                'order' => 6,
            ],
            [
                'label' => 'Clientes Facturación Libre',
                'route' => 'facturacion_clientes',
                'icon' => 'mdi-account-group-outline',
                'order' => 7,
            ],
            [
                'label' => 'Productos y Servicios SIN',
                'route' => 'facturacion_productos',
                'icon' => 'mdi-package-variant-closed',
                'order' => 8,
            ],
            [
                'label' => 'Libro de Ventas IVA',
                'route' => 'facturacion_libro_ventas',
                'icon' => 'mdi-book-open-page-variant',
                'order' => 9,
            ],
        ];

        $submenusCreated = [];
        foreach ($submodulos as $sub) {
            $submenusCreated[] = Menu::updateOrCreate(
                ['route' => $sub['route'], 'menu_id' => $menuFacturacion->id],
                [
                    'label' => $sub['label'],
                    'icon' => $sub['icon'],
                    'level' => 1,
                    'order' => $sub['order'],
                    'estado' => true,
                ]
            );
        }

        // 3. Activar visibilidad por defecto para Rol Administrador
        $adminRole = Rol::where('guard_name', 'api')->first() ?: Rol::find(1);
        if ($adminRole) {
            MenuRol::updateOrCreate(
                ['rol_id' => $adminRole->id, 'menu_id' => $menuFacturacion->id],
                ['check' => true]
            );
            foreach ($submenusCreated as $sm) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $adminRole->id, 'menu_id' => $sm->id],
                    ['check' => true]
                );
            }
        }

        // 4. Permisos Spatie Granulares para Facturación
        $permisosData = [
            ['name' => 'facturacion.caja.cobrar', 'module' => 'Caja y Facturación en Ventanilla', 'description' => 'Cobro en ventanilla y emisión de factura con código QR SIAT'],
            ['name' => 'facturacion.facturas.crear', 'module' => 'Facturación Libre / Especial', 'description' => 'Emisión manual de facturas electrónicas por servicios especiales'],
            ['name' => 'facturacion.facturas.ver', 'module' => 'Bandeja de Facturas', 'description' => 'Ver listado y detalles de facturas emitidas'],
            ['name' => 'facturacion.facturas.anular', 'module' => 'Bandeja de Facturas', 'description' => 'Anular facturas ante el SIN'],
            ['name' => 'facturacion.contingencias.administrar', 'module' => 'Facturas de Contingencia', 'description' => 'Registrar y empaquetar facturas de contingencia'],
            ['name' => 'facturacion.eventos.administrar', 'module' => 'Eventos Significativos', 'description' => 'Iniciar y cerrar eventos significativos'],
            ['name' => 'facturacion.puntos_venta.administrar', 'module' => 'Sucursales y Puntos de Venta', 'description' => 'Administrar sucursales y puntos de venta'],
            ['name' => 'facturacion.clientes.administrar', 'module' => 'Clientes Facturación Libre', 'description' => 'Administrar catálogo de clientes para facturación libre'],
            ['name' => 'facturacion.catalogos.administrar', 'module' => 'Productos y Servicios SIN', 'description' => 'Sincronizar catálogos y productos homologados del SIN'],
            ['name' => 'facturacion.libro_ventas.ver', 'module' => 'Libro de Ventas IVA', 'description' => 'Consultar y exportar el Registro de Ventas IVA (RCV)'],
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
