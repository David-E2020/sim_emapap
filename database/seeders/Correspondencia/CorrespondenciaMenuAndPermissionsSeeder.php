<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CorrespondenciaMenuAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Menú Padre "Correspondencia y Trámites"
        $menuCorrespondencia = Menu::updateOrCreate(
            ['label' => 'Correspondencia y Trámites', 'level' => 0],
            [
                'icon' => 'mdi-email-sync-outline',
                'route' => null,
                'order' => 3,
                'estado' => true,
            ]
        );

        // 2. Submódulos de Correspondencia (100% Cobertura Londra)
        $submodulos = [
            [
                'label' => 'Dashboard y Métricas',
                'route' => 'correspondencia_dashboard',
                'icon' => 'mdi-view-dashboard-outline',
                'order' => 1,
            ],
            [
                'label' => 'Bandeja de Hojas de Ruta',
                'route' => 'correspondencia_hojas_ruta',
                'icon' => 'mdi-inbox-multiple',
                'order' => 2,
            ],
            [
                'label' => 'Redacción de Documentos',
                'route' => 'correspondencia_documentos',
                'icon' => 'mdi-file-document-edit-outline',
                'order' => 3,
            ],
            [
                'label' => 'Firmas y Aprobaciones',
                'route' => 'correspondencia_firmas',
                'icon' => 'mdi-draw-pen',
                'order' => 4,
            ],
            [
                'label' => 'Seguimiento y Trazabilidad',
                'route' => 'correspondencia_seguimiento',
                'icon' => 'mdi-timeline-text-outline',
                'order' => 5,
            ],
            [
                'label' => 'Visor de Expediente 360°',
                'route' => 'correspondencia_visor_expediente',
                'icon' => 'mdi-folder-open-outline',
                'order' => 6,
            ],
            [
                'label' => 'Ventanilla Única',
                'route' => 'correspondencia_ventanilla',
                'icon' => 'mdi-domain-plus',
                'order' => 7,
            ],
            [
                'label' => 'Despacho y Salida Externa',
                'route' => 'correspondencia_despacho_salida',
                'icon' => 'mdi-truck-delivery-outline',
                'order' => 8,
            ],
            [
                'label' => 'Solicitudes Ciudadanas',
                'route' => 'correspondencia_solicitudes_ciudadanas',
                'icon' => 'mdi-account-voice',
                'order' => 9,
            ],
            [
                'label' => 'Etiquetas y Carpetas',
                'route' => 'correspondencia_etiquetas',
                'icon' => 'mdi-tag-multiple-outline',
                'order' => 10,
            ],
            [
                'label' => 'Matriz de Derivaciones',
                'route' => 'correspondencia_permisos',
                'icon' => 'mdi-shield-account-outline',
                'order' => 11,
            ],
            [
                'label' => 'Plantillas y Diseñador PDF',
                'route' => 'correspondencia_plantillas',
                'icon' => 'mdi-file-document-edit-outline',
                'order' => 12,
            ],
            [
                'label' => 'Transferencias de Bandeja',
                'route' => 'correspondencia_transferencias',
                'icon' => 'mdi-account-switch-outline',
                'order' => 13,
            ],
        ];

        $submenusCreated = [];
        foreach ($submodulos as $sub) {
            $submenusCreated[] = Menu::updateOrCreate(
                ['route' => $sub['route'], 'menu_id' => $menuCorrespondencia->id],
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
                ['rol_id' => $adminRole->id, 'menu_id' => $menuCorrespondencia->id],
                ['check' => true]
            );
            foreach ($submenusCreated as $sm) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $adminRole->id, 'menu_id' => $sm->id],
                    ['check' => true]
                );
            }
        }

        // 4. Permisos Spatie Granulares
        $permisosData = [
            ['name' => 'correspondencia.dashboard.ver', 'module' => 'Dashboard y Métricas', 'description' => 'Ver métricas e indicadores de gestión'],
            ['name' => 'correspondencia.hojas_ruta.ver', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Ver bandejas de entrada, salida y archivados'],
            ['name' => 'correspondencia.hojas_ruta.crear', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Generar nuevas hojas de ruta y trámites'],
            ['name' => 'correspondencia.hojas_ruta.derivar', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Derivar expedientes con proveídos oficiales'],
            ['name' => 'correspondencia.hojas_ruta.recibir', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Recepcionar correspondencia en bandeja'],
            ['name' => 'correspondencia.documentos.ver', 'module' => 'Redacción de Documentos', 'description' => 'Consultar repositorio de documentos'],
            ['name' => 'correspondencia.documentos.crear', 'module' => 'Redacción de Documentos', 'description' => 'Redactar memorándums, informes y notas internas'],
            ['name' => 'correspondencia.documentos.firmar', 'module' => 'Firmas y Aprobaciones', 'description' => 'Firmar electrónicamente con PIN oficial'],
            ['name' => 'correspondencia.seguimiento.ver', 'module' => 'Seguimiento y Trazabilidad', 'description' => 'Ver timeline y árbol de trazabilidad'],
            ['name' => 'correspondencia.visor.ver', 'module' => 'Visor de Expediente 360°', 'description' => 'Consultar historial completo, anexos y hojas de ruta 360°'],
            ['name' => 'correspondencia.ventanilla.recibir', 'module' => 'Ventanilla Única', 'description' => 'Registrar cartas externas y emitir comprobantes'],
            ['name' => 'correspondencia.ventanilla.despachar', 'module' => 'Ventanilla Única', 'description' => 'Despachar correspondencia externa'],
            ['name' => 'correspondencia.despachos.administrar', 'module' => 'Despacho y Salida Externa', 'description' => 'Gestionar envíos físicos y acuses de recibo'],
            ['name' => 'correspondencia.solicitudes.administrar', 'module' => 'Solicitudes Ciudadanas', 'description' => 'Admitir o rechazar trámites ciudadanos web'],
            ['name' => 'correspondencia.etiquetas.administrar', 'module' => 'Etiquetas y Carpetas', 'description' => 'Crear y asignar etiquetas personales'],
            ['name' => 'correspondencia.permisos.administrar', 'module' => 'Matriz de Derivaciones', 'description' => 'Configurar flujos y derivaciones permitidas entre unidades'],
            ['name' => 'correspondencia.transferencias.ejecutar', 'module' => 'Transferencias de Bandeja', 'description' => 'Reasignar bandejas y expedientes entre funcionarios'],
            ['name' => 'correspondencia.configuracion.administrar', 'module' => 'Plantillas y Diseñador PDF', 'description' => 'Gestionar plantillas, correlativos y proveídos'],
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
