<?php

namespace Database\Seeders\Contabilidad;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class ContabilidadMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. MENÚ PRINCIPAL: CONTABILIDAD Y FINANZAS (Nivel 0)
        $menuContabilidad = Menu::updateOrCreate(
            ['label' => 'Contabilidad y Finanzas', 'level' => 0],
            [
                'icon' => 'mdi-calculator-variant-outline',
                'route' => null,
                'order' => 6,
                'estado' => true,
            ]
        );

        // 2. SUBMENÚS (Nivel 1)
        $subPlanCuentas = Menu::updateOrCreate(
            ['route' => 'contabilidad_plan_cuentas'],
            [
                'icon' => 'mdi-file-tree-outline',
                'menu_id' => $menuContabilidad->id,
                'level' => 1,
                'label' => 'Plan de Cuentas',
                'order' => 1,
                'estado' => true,
            ]
        );

        $subComprobantes = Menu::updateOrCreate(
            ['route' => 'contabilidad_comprobantes'],
            [
                'icon' => 'mdi-book-multiple-outline',
                'menu_id' => $menuContabilidad->id,
                'level' => 1,
                'label' => 'Comprobantes (CI/CE/CD)',
                'order' => 2,
                'estado' => true,
            ]
        );

        $subInterfases = Menu::updateOrCreate(
            ['route' => 'contabilidad_interfases'],
            [
                'icon' => 'mdi-sync',
                'menu_id' => $menuContabilidad->id,
                'level' => 1,
                'label' => 'Interfaces Automáticas',
                'order' => 3,
                'estado' => true,
            ]
        );

        $subLibros = Menu::updateOrCreate(
            ['route' => 'contabilidad_libros'],
            [
                'icon' => 'mdi-book-open-page-variant-outline',
                'menu_id' => $menuContabilidad->id,
                'level' => 1,
                'label' => 'Libros Diario y Mayor',
                'order' => 4,
                'estado' => true,
            ]
        );

        $subEstadosFinancieros = Menu::updateOrCreate(
            ['route' => 'contabilidad_estados_financieros'],
            [
                'icon' => 'mdi-chart-box-outline',
                'menu_id' => $menuContabilidad->id,
                'level' => 1,
                'label' => 'Estados Financieros SAFCO',
                'order' => 5,
                'estado' => true,
            ]
        );

        // 3. ASIGNAR POR DEFECTO A ADMINISTRADOR GENERAL
        $subMenusIds = [
            $subPlanCuentas->id,
            $subComprobantes->id,
            $subInterfases->id,
            $subLibros->id,
            $subEstadosFinancieros->id,
        ];

        $adminRole = Rol::where('guard_name', 'api')->first() ?: Rol::find(1);

        if ($adminRole) {
            MenuRol::updateOrCreate(
                ['menu_id' => $menuContabilidad->id, 'rol_id' => $adminRole->id],
                ['check' => true]
            );
            foreach ($subMenusIds as $menuId) {
                MenuRol::updateOrCreate(
                    [
                        'menu_id' => $menuId,
                        'rol_id' => $adminRole->id,
                    ],
                    [
                        'check' => true,
                    ]
                );
            }
        }
    }
}
