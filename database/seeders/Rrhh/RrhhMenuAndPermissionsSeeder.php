<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RrhhMenuAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar menús de test si quedaron de pruebas
        DB::table('menus')->where('route', 'test-route')->delete();

        // 1. Menú Padre "Recursos Humanos"
        $menuRrhh = Menu::updateOrCreate(
            ['label' => 'Recursos Humanos', 'level' => 0],
            [
                'icon' => 'mdi-account-group-outline',
                'route' => null,
                'order' => 2,
                'estado' => true,
            ]
        );

        // 2. Todos los 8 Submódulos de Recursos Humanos (100% Capibara)
        $submodulos = [
            [
                'label' => 'Personal y Legajos',
                'route' => 'rrhh_personal',
                'icon' => 'mdi-account-badge-outline',
                'order' => 1,
            ],
            [
                'label' => 'Estructura Organizacional',
                'route' => 'rrhh_organigrama',
                'icon' => 'mdi-sitemap-outline',
                'order' => 2,
            ],
            [
                'label' => 'Control de Asistencia',
                'route' => 'rrhh_asistencias',
                'icon' => 'mdi-clock-check-outline',
                'order' => 3,
            ],
            [
                'label' => 'Gestión de Horarios',
                'route' => 'rrhh_horarios',
                'icon' => 'mdi-timetable',
                'order' => 4,
            ],
            [
                'label' => 'Permisos y Comisiones',
                'route' => 'rrhh_solicitudes',
                'icon' => 'mdi-folder-account-outline',
                'order' => 5,
            ],
            [
                'label' => 'Cálculos Laborales',
                'route' => 'rrhh_calculos_laborales',
                'icon' => 'mdi-calculator-variant-outline',
                'order' => 6,
            ],
            [
                'label' => 'Feriados y Cortes',
                'route' => 'rrhh_feriados_cortes',
                'icon' => 'mdi-calendar-range',
                'order' => 7,
            ],
            [
                'label' => 'Control de Vacaciones',
                'route' => 'rrhh_vacaciones',
                'icon' => 'mdi-calendar-account-outline',
                'order' => 8,
            ],
            [
                'label' => 'Reportes y Planillas',
                'route' => 'rrhh_reportes',
                'icon' => 'mdi-file-chart-outline',
                'order' => 9,
            ],
        ];

        $submenusCreated = [];
        foreach ($submodulos as $sub) {
            $submenusCreated[] = Menu::updateOrCreate(
                ['route' => $sub['route'], 'menu_id' => $menuRrhh->id],
                [
                    'label' => $sub['label'],
                    'icon' => $sub['icon'],
                    'level' => 1,
                    'order' => $sub['order'],
                    'estado' => true,
                ]
            );
        }

        // 3. Activar visibilidad por defecto para el Rol Administrador (Rol ID 1)
        $adminRole = Rol::where('guard_name', 'api')->first() ?: Rol::find(1);
        if ($adminRole) {
            MenuRol::updateOrCreate(
                ['rol_id' => $adminRole->id, 'menu_id' => $menuRrhh->id],
                ['check' => true]
            );
            foreach ($submenusCreated as $sm) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $adminRole->id, 'menu_id' => $sm->id],
                    ['check' => true]
                );
            }
        }

        // 4. Permisos Granulares Spatie con Descripciones en Español
        $permisosData = [
            // Personal
            ['name' => 'rrhh.personal.ver', 'module' => 'Personal y Legajos', 'description' => 'Consultar listado de funcionarios y datos básicos'],
            ['name' => 'rrhh.personal.crear', 'module' => 'Personal y Legajos', 'description' => 'Registrar nuevos funcionarios y cuentas de acceso'],
            ['name' => 'rrhh.personal.editar', 'module' => 'Personal y Legajos', 'description' => 'Modificar datos personales y demográficos'],
            ['name' => 'rrhh.personal.legajo', 'module' => 'Personal y Legajos', 'description' => 'Ver y actualizar legajo digital (estudios, experiencia, CAS)'],

            // Estructura
            ['name' => 'rrhh.organigrama.ver', 'module' => 'Estructura Organizacional', 'description' => 'Ver árbol de organigrama y puestos institucionales'],
            ['name' => 'rrhh.organigrama.crear_unidad', 'module' => 'Estructura Organizacional', 'description' => 'Crear y reorganizar unidades organizacionales'],
            ['name' => 'rrhh.organigrama.crear_puesto', 'module' => 'Estructura Organizacional', 'description' => 'Definir puestos y escalas salariales'],
            ['name' => 'rrhh.organigrama.asignar_puesto', 'module' => 'Estructura Organizacional', 'description' => 'Asignar ítems y cargos a funcionarios'],

            // Asistencias
            ['name' => 'rrhh.asistencias.ver', 'module' => 'Control de Asistencia', 'description' => 'Ver marcaciones y registros diarios de asistencia'],
            ['name' => 'rrhh.asistencias.sincronizar_biometrico', 'module' => 'Control de Asistencia', 'description' => 'Descargar marcaciones desde relojes biométricos'],
            ['name' => 'rrhh.asistencias.calcular_asistencia', 'module' => 'Control de Asistencia', 'description' => 'Procesar cálculo de atrasos, tolerancias y refrigerios'],
            ['name' => 'rrhh.biometricos.administrar', 'module' => 'Control de Asistencia', 'description' => 'Registrar y configurar relojes biométricos en red'],

            // Horarios
            ['name' => 'rrhh.horarios.ver', 'module' => 'Gestión de Horarios', 'description' => 'Ver tipos de jornada y asignaciones de horario'],
            ['name' => 'rrhh.horarios.crear', 'module' => 'Gestión de Horarios', 'description' => 'Crear nuevos horarios y turnos de entrada/salida'],
            ['name' => 'rrhh.horarios.asignar', 'module' => 'Gestión de Horarios', 'description' => 'Asignar turnos a funcionarios de forma individual o masiva'],

            // Solicitudes
            ['name' => 'rrhh.solicitudes.ver', 'module' => 'Boletas y Permisos', 'description' => 'Listar boletas de salida y solicitudes de permiso'],
            ['name' => 'rrhh.solicitudes.crear', 'module' => 'Boletas y Permisos', 'description' => 'Solicitar permisos, licencias y comisiones oficiales'],
            ['name' => 'rrhh.solicitudes.aprobar', 'module' => 'Boletas y Permisos', 'description' => 'Aprobar o rechazar boletas de salida de personal'],

            // Comisiones y Omisiones
            ['name' => 'rrhh.comisiones.ver', 'module' => 'Comisiones y Omisiones', 'description' => 'Ver viajes oficiales en comisión y descargos'],
            ['name' => 'rrhh.comisiones.crear', 'module' => 'Comisiones y Omisiones', 'description' => 'Registrar comisiones de viaje institucional con viáticos'],
            ['name' => 'rrhh.omisiones.crear', 'module' => 'Comisiones y Omisiones', 'description' => 'Solicitar regularización por omisión o fallo de marcado'],
            ['name' => 'rrhh.omisiones.aprobar', 'module' => 'Comisiones y Omisiones', 'description' => 'Bandeja de firma y resolución de solicitudes'],

            // Cálculos Laborales
            ['name' => 'rrhh.calculos_laborales.ver', 'module' => 'Cálculos Laborales', 'description' => 'Consultar parámetros laborales, SMN y aportes de ley'],
            ['name' => 'rrhh.calculos_laborales.guardar', 'module' => 'Cálculos Laborales', 'description' => 'Modificar parámetros salariales, SMN, Gestora y cargas patronales'],

            // Feriados y Cortes
            ['name' => 'rrhh.feriados.ver', 'module' => 'Feriados y Cortes', 'description' => 'Consultar calendario oficial de feriados y cortes'],
            ['name' => 'rrhh.feriados.crear', 'module' => 'Feriados y Cortes', 'description' => 'Registrar feriados nacionales y fechas de corte mensual'],

            // Vacaciones
            ['name' => 'rrhh.vacaciones.ver', 'module' => 'Control de Vacaciones', 'description' => 'Consultar récord y saldo de vacaciones de personal'],
            ['name' => 'rrhh.vacaciones.gestionar', 'module' => 'Control de Vacaciones', 'description' => 'Registrar solicitudes y programación de vacaciones'],

            // Reportes
            ['name' => 'rrhh.reportes.asistencia', 'module' => 'Reportes y Planillas', 'description' => 'Generar consolidado mensual de asistencia y atrasos'],
            ['name' => 'rrhh.reportes.refrigerio', 'module' => 'Reportes y Planillas', 'description' => 'Generar planilla mensual de refrigerios para pago'],
            ['name' => 'rrhh.reportes.vacaciones', 'module' => 'Reportes y Planillas', 'description' => 'Consultar kardex y saldo de vacaciones por ley'],
            ['name' => 'rrhh.reportes.boleta_imprimir', 'module' => 'Reportes y Planillas', 'description' => 'Imprimir boleta de salida con membrete oficial EMAPA'],
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
