<?php

declare(strict_types=1);

namespace Database\Seeders\Comercial;

use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\Zona;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Parametrica;
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

        // 2. CATÁLOGOS PARAMÉTRICOS DE GESTIÓN COMERCIAL
        $parametricasComercial = [
            [
                'tabla' => 'TABLA_COMERCIAL_ESTADOS_ABONADO',
                'nombre' => 'ESTADOS DE SERVICIO DEL ABONADO',
                'descripcion' => 'Estados operativos del suministro de agua potable: Activo, Corte, Suspendido, Permiso',
                'items' => [
                    ['codigo' => 'A', 'nombre' => 'ACTIVO', 'detalle' => 'Suministro normal, regular y habilitado'],
                    ['codigo' => 'C', 'nombre' => 'CORTE', 'detalle' => 'Servicio cortado físicamente en llave de paso o acometida por mora'],
                    ['codigo' => 'S', 'nombre' => 'SUSPENDIDO', 'detalle' => 'Suspensión temporal solicitada o administrativa'],
                    ['codigo' => 'P', 'nombre' => 'PERMISO', 'detalle' => 'Permiso especial de no consumo o remodelación'],
                ],
            ],
            [
                'tabla' => 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS',
                'nombre' => 'CONCEPTOS DE OTROS INGRESOS Y SERVICIOS',
                'descripcion' => 'Catálogo de cobros no tarifarios: Reconexión, Multas, Cambio de Medidor, etc.',
                'items' => [
                    ['codigo' => 'REC', 'nombre' => 'Reconexión de Servicio de Agua', 'detalle' => 'Cobro por rehabilitación de servicio tras corte'],
                    ['codigo' => 'CAM', 'nombre' => 'Cambio de Nombre o Titularidad', 'detalle' => 'Trámite administrativo de transferencia de póliza'],
                    ['codigo' => 'FRA', 'nombre' => 'Multa por Conexión Clandestina / Fraude', 'detalle' => 'Sanción legal por uso indebido o manipulación'],
                    ['codigo' => 'CIS', 'nombre' => 'Venta de Agua Potable por Cisterna', 'detalle' => 'Carga y venta de agua en bloque por cisterna'],
                    ['codigo' => 'MED', 'nombre' => 'Reposición o Cambio de Medidor', 'detalle' => 'Costo por suministro e instalación de nuevo medidor'],
                ],
            ],
            [
                'tabla' => 'TABLA_COMERCIAL_TIPOS_MEDIDOR',
                'nombre' => 'TIPOS Y DIÁMETROS DE MEDIDORES',
                'descripcion' => 'Especificaciones técnicas de los medidores de agua instalados (1/2", 3/4", 1", etc.)',
                'items' => [
                    ['codigo' => '1/2"', 'nombre' => 'Medidor Chorro Único 1/2" (15mm) - Domiciliario', 'detalle' => 'Diámetro estándar para conexiones domiciliarias'],
                    ['codigo' => '3/4"', 'nombre' => 'Medidor Chorro Múltiple 3/4" (20mm) - Comercial', 'detalle' => 'Diámetro para conexiones comerciales o alto consumo'],
                    ['codigo' => '1"', 'nombre' => 'Medidor Chorro Múltiple 1" (25mm) - Industrial', 'detalle' => 'Diámetro para industrias o instituciones'],
                    ['codigo' => '1 1/2"', 'nombre' => 'Medidor Gran Consumo 1 1/2" (40mm)', 'detalle' => 'Medidor para grandes consumidores o baterías'],
                    ['codigo' => '2"', 'nombre' => 'Medidor Woltman / Brida 2" (50mm) - Macromedición', 'detalle' => 'Macromedición de sectores hidráulicos o tanques'],
                ],
            ],
        ];

        foreach ($parametricasComercial as $grupo) {
            Parametrica::updateOrCreate(
                [
                    'param_tabla' => $grupo['tabla'],
                    'param_codigo' => 'ORIGEN',
                    'param_valor' => 0,
                ],
                [
                    'param_nombre' => $grupo['nombre'],
                    'param_descripcion' => $grupo['descripcion'],
                    'param_estado' => 'A',
                    'param_usr_registrado' => 1,
                ]
            );

            $orden = 1;
            foreach ($grupo['items'] as $item) {
                Parametrica::updateOrCreate(
                    [
                        'param_tabla' => $grupo['tabla'],
                        'param_codigo' => $item['codigo'],
                    ],
                    [
                        'param_nombre' => $item['nombre'],
                        'param_descripcion' => $item['detalle'],
                        'param_valor' => $orden++,
                        'param_estado' => 'A',
                        'param_usr_registrado' => 1,
                    ]
                );
            }
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
                'label' => 'Cierres y Arqueos de Caja',
                'route' => 'comercial_sesiones_caja',
                'icon' => 'mdi-lock-check',
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
            [
                'label' => 'Aportes e Instalaciones',
                'route' => 'comercial_aportes',
                'icon' => 'mdi-pipe-wrench',
                'order' => 10,
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

        // 5. Asignar visibilidad por defecto al Rol Administrador General
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
            ['name' => 'comercial.abonados.ver', 'module' => 'Padrón de Abonados', 'description' => 'Ver listado y ficha de abonados'],
            ['name' => 'comercial.abonados.crear', 'module' => 'Padrón de Abonados', 'description' => 'Registrar y modificar abonados'],
            ['name' => 'comercial.abonados.georreferenciar', 'module' => 'Padrón de Abonados', 'description' => 'Georreferenciar acometidas y asignar datos catastrales'],
            ['name' => 'comercial.periodos.administrar', 'module' => 'Ciclo de Períodos', 'description' => 'Aperturar y cerrar períodos mensuales de facturación'],
            ['name' => 'comercial.lecturas.registrar', 'module' => 'Toma de Lecturas', 'description' => 'Registrar lecturas de medidores y liquidar mes'],
            ['name' => 'comercial.sesiones_caja.aperturar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Aperturar turno de recaudación en caja'],
            ['name' => 'comercial.sesiones_caja.cerrar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Cierre de turno y arqueo ciego de recaudación'],
            ['name' => 'comercial.sesiones_caja.supervisar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Supervisar y auditar cierres de turnos de cajeros'],
            ['name' => 'comercial.convenios.administrar', 'module' => 'Convenios de Pago', 'description' => 'Suscripción y seguimiento de convenios de pago'],
            ['name' => 'comercial.cortes.administrar', 'module' => 'Cortes y Reconexiones', 'description' => 'Gestionar órdenes de corte y reconexión'],
            ['name' => 'comercial.zonas_calles.administrar', 'module' => 'Zonas y Calles', 'description' => 'Gestionar sectores, zonas, rutas de lectura y calles'],
            ['name' => 'comercial.tarifas.administrar', 'module' => 'Estructura Tarifaria', 'description' => 'Configurar categorías tarifarias y rangos de consumo AAPS'],
            ['name' => 'comercial.reportes.ver', 'module' => 'Reportes Comerciales', 'description' => 'Ver reportes comerciales, recaudaciones y morosidad'],
            ['name' => 'comercial.aportes.administrar', 'module' => 'Aportes e Instalaciones', 'description' => 'Gestionar derechos de acometida, inspecciones y materiales'],
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
