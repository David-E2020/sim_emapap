<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class RolesPermisosController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Mapeo completo de rutas de submenú a nombres de módulo de permisos Spatie.
     */
    public const MODULE_MAP = [
        // 1. Administrar
        'usuarios' => 'Usuarios',
        'roles_permisos' => 'Roles y Permisos',
        'control_acceso' => 'Roles y Permisos',
        'admin_menu' => 'Roles y Permisos',
        'auditoria' => 'Auditoría y Seguridad',

        // 2. Datos
        'parametrica' => 'Parametrica',
        'datos_empresa' => 'Configuración Empresa y SIAT',
        'datos_migrador_respaldos' => 'Migrador de Respaldos (FoxPro)',

        // 3. Recursos Humanos
        'rrhh_personal' => 'Personal y Legajos',
        'rrhh_organigrama' => 'Estructura Organizacional',
        'rrhh_asistencias' => 'Control de Asistencia',
        'rrhh_horarios' => 'Gestión de Horarios',
        'rrhh_solicitudes' => 'Boletas y Permisos',
        'rrhh_comisiones_omisiones' => 'Comisiones y Omisiones',
        'rrhh_feriados_cortes' => 'Feriados y Cortes',
        'rrhh_reportes' => 'Reportes y Planillas',

        // 4. Correspondencia y Trámites
        'correspondencia_dashboard' => 'Dashboard y Métricas',
        'correspondencia_hojas_ruta' => 'Bandeja de Hojas de Ruta',
        'correspondencia_documentos' => 'Redacción de Documentos',
        'correspondencia_firmas' => 'Firmas y Aprobaciones',
        'correspondencia_seguimiento' => 'Seguimiento y Trazabilidad',
        'correspondencia_visor_expediente' => 'Visor de Expediente 360°',
        'correspondencia_ventanilla' => 'Ventanilla Única',
        'correspondencia_despacho_salida' => 'Despacho y Salida Externa',
        'correspondencia_solicitudes_ciudadanas' => 'Solicitudes Ciudadanas',
        'correspondencia_etiquetas' => 'Etiquetas y Carpetas',
        'correspondencia_permisos' => 'Matriz de Derivaciones',
        'correspondencia_plantillas' => 'Plantillas y Diseñador PDF',
        'correspondencia_transferencias' => 'Transferencias de Bandeja',

        // 5. Facturación SIAT
        'facturacion_caja' => 'Caja y Facturación en Ventanilla',
        'facturacion_crear' => 'Facturación Libre / Especial',
        'facturacion_bandeja' => 'Bandeja de Facturas',
        'facturacion_contingencias' => 'Facturas de Contingencia',
        'facturacion_eventos' => 'Eventos Significativos',
        'facturacion_puntos_venta' => 'Sucursales y Puntos de Venta',
        'facturacion_clientes' => 'Clientes Facturación Libre',
        'facturacion_productos' => 'Productos y Servicios SIN',
        'facturacion_libro_ventas' => 'Libro de Ventas IVA',

        // 6. Gestión Comercial
        'comercial_abonados' => 'Padrón de Abonados',
        'comercial_periodos' => 'Ciclo de Períodos',
        'comercial_lecturas' => 'Toma de Lecturas',
        'comercial_sesiones_caja' => 'Cierres y Arqueos de Caja',
        'comercial_convenios' => 'Convenios de Pago',
        'comercial_cortes' => 'Cortes y Reconexiones',
        'comercial_zonas_calles' => 'Zonas y Calles',
        'comercial_tarifas' => 'Estructura Tarifaria',
        'comercial_reportes' => 'Reportes Comerciales',
        'comercial_aportes' => 'Aportes e Instalaciones',

        // 7. Contabilidad y Finanzas
        'contabilidad_plan_cuentas' => 'Plan de Cuentas',
        'contabilidad_comprobantes' => 'Comprobantes (CI/CE/CD)',
        'contabilidad_interfases' => 'Interfaces Automáticas',
        'contabilidad_libros' => 'Libros Diario y Mayor',
        'contabilidad_estados_financieros' => 'Estados Financieros SAFCO',
    ];

    /**
     * Obtener la estructura unificada de menús y permisos granulares contextuales para un rol.
     */
    public function matriz(int|string $rolId): JsonResponse
    {
        try {
            $rol = Rol::with('permissions')->findOrFail((int) $rolId);
            $rolePermissionNames = $rol->permissions->pluck('name')->toArray();

            // 1. Obtener todos los permisos Spatie
            $allPermissions = Permission::orderBy('id', 'asc')->get();
            $permissionsByModule = $allPermissions->groupBy('module');

            // 2. Obtener menús y submenús
            $menuRolIds = MenuRol::where('rol_id', $rol->id)->where('check', true)->pluck('menu_id')->toArray();
            $menus = Menu::with(['subMenuN1' => function ($q) {
                $q->orderBy('order', 'asc');
            }])->whereNull('menu_id')->orderBy('order', 'asc')->get();

            $menusResp = [];
            foreach ($menus as $m) {
                $subMenus = $m->subMenuN1 ?? [];
                $subResp = [];
                $countActiveSubmenus = 0;

                foreach ($subMenus as $sub) {
                    $moduleKey = self::MODULE_MAP[$sub->route] ?? ($sub->label ?: 'General');
                    $modulePerms = $permissionsByModule->get($moduleKey, collect([]));

                    $granularPerms = [];
                    $activePermsCount = 0;

                    foreach ($modulePerms as $p) {
                        $granted = in_array($p->name, $rolePermissionNames, true);
                        if ($granted) {
                            $activePermsCount++;
                        }

                        $granularPerms[] = [
                            'id' => $p->id,
                            'name' => $p->name,
                            'description' => $p->description ?: 'Acción del sistema',
                            'module' => $p->module,
                            'active' => $granted,
                        ];
                    }

                    // INTELIGENCIA AUTOMÁTICA: Un submódulo es visible si está en menu_rol O si tiene al menos 1 permiso activo
                    $isMenuActive = in_array($sub->id, $menuRolIds, true) || $activePermsCount > 0;
                    if ($isMenuActive) {
                        $countActiveSubmenus++;
                        // Sincronizar en base de datos si no estaba
                        if (! in_array($sub->id, $menuRolIds, true) && $activePermsCount > 0) {
                            MenuRol::updateOrCreate(
                                ['rol_id' => $rol->id, 'menu_id' => $sub->id],
                                ['check' => true]
                            );
                            if ($m->id) {
                                MenuRol::updateOrCreate(
                                    ['rol_id' => $rol->id, 'menu_id' => $m->id],
                                    ['check' => true]
                                );
                            }
                        }
                    }

                    $sub->active = $isMenuActive;
                    $sub->module_name = $moduleKey;
                    $sub->granular_permissions = $granularPerms;
                    $sub->perm_stats = [
                        'active' => $activePermsCount,
                        'total' => count($granularPerms),
                    ];

                    $subResp[] = $sub;
                }

                $totalSub = count($subResp);
                $m->sub_menu = $subResp;
                $m->progreso = [
                    'porcentaje' => $totalSub > 0 ? round(($countActiveSubmenus / $totalSub) * 100) : 0,
                    'countActive' => $countActiveSubmenus,
                    'total' => $totalSub,
                ];
                $menusResp[] = $m;
            }

            return response()->json([
                'success' => true,
                'role' => $rol,
                'menus' => $menusResp,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al obtener matriz unificada', ['rol_id' => $rolId, 'exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al cargar la matriz de roles y permisos.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Conmutar (activar/desactivar) un permiso granular específico de Spatie para un rol con sincronización automática de menú.
     */
    public function togglePermiso(Request $request): JsonResponse
    {
        $request->validate([
            'rol_id' => 'required|integer',
            'permission_name' => 'required|string',
        ]);

        try {
            $rol = Rol::findOrFail((int) $request->input('rol_id'));

            // Garantizar guard_name compatible
            if ($rol->guard_name !== 'api') {
                $rol->guard_name = 'api';
                $rol->save();
            }

            $permName = trim($request->input('permission_name'));
            $permission = Permission::where('name', $permName)->firstOrFail();

            $hasPermission = $rol->hasPermissionTo($permName);

            if ($hasPermission) {
                $rol->revokePermissionTo($permName);
                $newState = false;
                $actionText = 'revocado';
            } else {
                $rol->givePermissionTo($permName);
                $newState = true;
                $actionText = 'concedido';
            }

            // AUTO-SINCRONIZACIÓN DE MENÚ:
            // Al conceder cualquier permiso, activar automáticamente el menú del módulo y su menú padre
            $moduleName = $permission->module;
            if ($moduleName) {
                $subMenus = Menu::where(function ($q) use ($moduleName) {
                    $q->where('label', $moduleName)
                        ->orWhere('route', array_search($moduleName, self::MODULE_MAP, true) ?: '__none__');
                })->get();

                foreach ($subMenus as $subMenu) {
                    if ($newState) {
                        // Activar submenú
                        MenuRol::updateOrCreate(
                            ['rol_id' => $rol->id, 'menu_id' => $subMenu->id],
                            ['check' => true]
                        );
                        // Activar menú padre
                        if ($subMenu->menu_id) {
                            MenuRol::updateOrCreate(
                                ['rol_id' => $rol->id, 'menu_id' => $subMenu->menu_id],
                                ['check' => true]
                            );
                        }
                    }
                }
            }

            $this->auditService->log(
                event: 'user_role_permission_toggled',
                model: $rol,
                newValues: [
                    'permission' => $permName,
                    'active' => $newState,
                    'role_name' => $rol->name,
                ]
            );

            return response()->json([
                'success' => true,
                'active' => $newState,
                'message' => "Permiso \"{$permName}\" {$actionText} correctamente para {$rol->name}.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al conmutar permiso Spatie para rol', [
                'request' => $request->all(),
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar el permiso del rol: '.$ex->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Conmutar la visibilidad de un submenú para un rol con sincronización automática de permisos.
     */
    public function toggleMenuRol(Request $request): JsonResponse
    {
        $request->validate([
            'rol_id' => 'required|integer',
            'menu_id' => 'required|integer',
        ]);

        try {
            $rol = Rol::findOrFail((int) $request->input('rol_id'));
            $menu = Menu::findOrFail((int) $request->input('menu_id'));

            if ($rol->guard_name !== 'api') {
                $rol->guard_name = 'api';
                $rol->save();
            }

            $menuRol = MenuRol::where('rol_id', $rol->id)->where('menu_id', $menu->id)->first();
            $isCurrentlyActive = $menuRol ? (bool) $menuRol->check : false;
            $newActiveState = ! $isCurrentlyActive;

            // Actualizar estado del submenú
            MenuRol::updateOrCreate(
                ['rol_id' => $rol->id, 'menu_id' => $menu->id],
                ['check' => $newActiveState]
            );

            // Sincronizar Menú Padre
            if ($menu->menu_id) {
                if ($newActiveState) {
                    // Si se activa un hijo, el padre DEBE estar activo
                    MenuRol::updateOrCreate(
                        ['rol_id' => $rol->id, 'menu_id' => $menu->menu_id],
                        ['check' => true]
                    );
                } else {
                    // Si se desactiva, verificar si quedan otros hijos activos
                    $otherActiveChildren = MenuRol::where('rol_id', $rol->id)
                        ->where('check', true)
                        ->whereIn('menu_id', Menu::where('menu_id', $menu->menu_id)->pluck('id'))
                        ->exists();

                    if (! $otherActiveChildren) {
                        MenuRol::where('rol_id', $rol->id)->where('menu_id', $menu->menu_id)->update(['check' => false]);
                    }
                }
            }

            // AUTO-SINCRONIZACIÓN DE PERMISOS:
            $moduleName = self::MODULE_MAP[$menu->route] ?? $menu->label;
            $permissions = Permission::where('module', $moduleName)->get();

            if ($newActiveState) {
                // Al activar la navegación, conceder el permiso de "ver" si existe
                $verPerm = $permissions->first(fn ($p) => str_ends_with($p->name, '.ver'));
                if ($verPerm) {
                    $rol->givePermissionTo($verPerm->name);
                }
            } else {
                // Al desactivar la navegación completa, revocar todas las acciones del módulo para ese rol
                foreach ($permissions as $p) {
                    $rol->revokePermissionTo($p->name);
                }
            }

            return response()->json([
                'success' => true,
                'active' => $newActiveState,
                'message' => $newActiveState
                    ? "Submódulo \"{$menu->label}\" activado y visible en barra lateral."
                    : "Submódulo \"{$menu->label}\" desactivado y oculto.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al toggle menu rol', ['request' => $request->all(), 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al actualizar visibilidad del menú.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtener permisos huérfanos (sin módulo o pendientes de asignación).
     */
    public function permisosHuerfanos(): JsonResponse
    {
        try {
            $orphanPerms = Permission::where(function ($q) {
                $q->whereNull('module')
                    ->orWhere('module', '')
                    ->orWhere('module', 'General');
            })->where('name', '!=', 'SIGP')->get();

            return response()->json([
                'success' => true,
                'count' => $orphanPerms->count(),
                'permissions' => $orphanPerms,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al consultar permisos huerfanos', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al consultar permisos huérfanos.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crear un nuevo Menú Principal (Nivel 0) o Submódulo (Nivel 1) con asignación de roles y permisos.
     */
    public function crearMenu(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:100',
            'menu_id' => 'nullable|integer',
            'role_ids' => 'nullable|array',
            'orphan_permission_ids' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $menuId = $request->input('menu_id');
                $level = $menuId ? 1 : 0;

                $maxOrder = Menu::where('menu_id', $menuId)->max('order') ?? 0;

                $menu = Menu::create([
                    'label' => trim($request->input('label')),
                    'icon' => $request->input('icon') ?: ($level === 0 ? 'mdiFolder' : 'mdiFile'),
                    'route' => $request->input('route') ? trim($request->input('route')) : null,
                    'menu_id' => $menuId,
                    'level' => $level,
                    'order' => $maxOrder + 1,
                    'estado' => true,
                ]);

                // Asignar visibilidad a roles seleccionados
                $roleIds = $request->input('role_ids', []);
                foreach ($roleIds as $rolId) {
                    MenuRol::updateOrCreate(
                        ['rol_id' => (int) $rolId, 'menu_id' => $menu->id],
                        ['check' => true]
                    );
                    if ($menuId) {
                        MenuRol::updateOrCreate(
                            ['rol_id' => (int) $rolId, 'menu_id' => (int) $menuId],
                            ['check' => true]
                        );
                    }
                }

                // Vincular permisos huérfanos seleccionados a este módulo
                $orphanIds = $request->input('orphan_permission_ids', []);
                if (! empty($orphanIds)) {
                    Permission::whereIn('id', $orphanIds)->update([
                        'module' => $menu->label,
                    ]);
                }

                $this->auditService->log(
                    event: 'menu_item_created',
                    model: $menu,
                    newValues: [
                        'label' => $menu->label,
                        'route' => $menu->route,
                        'level' => $menu->level,
                        'assigned_roles' => count($roleIds),
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => "Menú \"{$menu->label}\" creado exitosamente.",
                    'menu' => $menu,
                ], Response::HTTP_CREATED);
            });
        } catch (\Throwable $ex) {
            Log::error('Error al crear menu', ['request' => $request->all(), 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al crear el menú.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualizar propiedades de un Menú o Submódulo.
     */
    public function actualizarMenu(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:100',
        ]);

        try {
            $menu = Menu::findOrFail($id);
            $oldValues = $menu->toArray();

            $menu->update([
                'label' => trim($request->input('label')),
                'icon' => $request->input('icon') ?: $menu->icon,
                'route' => $request->input('route') ? trim($request->input('route')) : null,
            ]);

            $this->auditService->log(
                event: 'menu_item_updated',
                model: $menu,
                oldValues: $oldValues,
                newValues: $menu->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => "Menú \"{$menu->label}\" actualizado correctamente.",
                'menu' => $menu,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar menu', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al actualizar menú.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un Menú o Submódulo.
     */
    public function eliminarMenu(int $id): JsonResponse
    {
        try {
            $menu = Menu::with('subMenuN1')->findOrFail($id);

            if ($menu->subMenuN1 && $menu->subMenuN1->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar este menú porque contiene submódulos. Elimina primero sus submódulos.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            MenuRol::where('menu_id', $menu->id)->delete();
            $menuLabel = $menu->label;
            $menu->delete();

            $this->auditService->log(
                event: 'menu_item_deleted',
                model: $menu,
                oldValues: ['label' => $menuLabel, 'id' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => "Menú \"{$menuLabel}\" eliminado correctamente.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al eliminar menu', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al eliminar menú.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reordenar menús o submódulos.
     */
    public function reordenarMenus(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order' => 'required|integer',
            'items.*.menu_id' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->input('items') as $item) {
                    $updateData = ['order' => (int) $item['order']];
                    if (array_key_exists('menu_id', $item)) {
                        $updateData['menu_id'] = $item['menu_id'] ? (int) $item['menu_id'] : null;
                        $updateData['level'] = $updateData['menu_id'] ? 1 : 0;
                    }
                    Menu::where('id', $item['id'])->update($updateData);
                }
            });

            return response()->json(['success' => true, 'message' => 'Orden y jerarquía de navegación actualizados correctamente.'], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al reordenar menus', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al reordenar menús.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Conceder o revocar masivamente todos los permisos de un módulo para un rol.
     */
    public function batchModulePermissions(Request $request): JsonResponse
    {
        $request->validate([
            'rol_id' => 'required|integer',
            'module' => 'required|string',
            'grant_all' => 'required|boolean',
        ]);

        try {
            $rol = Rol::findOrFail((int) $request->input('rol_id'));
            if ($rol->guard_name !== 'api') {
                $rol->guard_name = 'api';
                $rol->save();
            }

            $moduleName = $request->input('module');
            $grantAll = (bool) $request->input('grant_all');

            $permissions = Permission::where('module', $moduleName)->get();

            foreach ($permissions as $perm) {
                if ($grantAll) {
                    $rol->givePermissionTo($perm->name);
                } else {
                    $rol->revokePermissionTo($perm->name);
                }
            }

            // AUTO-SINCRONIZAR VISIBILIDAD DE MENÚ
            $subMenus = Menu::where(function ($q) use ($moduleName) {
                $q->where('label', $moduleName)
                    ->orWhere('route', array_search($moduleName, self::MODULE_MAP, true) ?: '__none__');
            })->get();

            foreach ($subMenus as $subMenu) {
                MenuRol::updateOrCreate(
                    ['rol_id' => $rol->id, 'menu_id' => $subMenu->id],
                    ['check' => $grantAll]
                );
                if ($subMenu->menu_id) {
                    MenuRol::updateOrCreate(
                        ['rol_id' => $rol->id, 'menu_id' => $subMenu->menu_id],
                        ['check' => $grantAll]
                    );
                }
            }

            $this->auditService->log(
                event: 'role_module_permissions_batched',
                model: $rol,
                newValues: [
                    'module' => $moduleName,
                    'granted_all' => $grantAll,
                    'total_affected' => $permissions->count(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $grantAll
                    ? "Se concedieron todas las acciones de \"{$moduleName}\" al rol {$rol->name}."
                    : "Se revocaron todas las acciones de \"{$moduleName}\" al rol {$rol->name}.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error en batch de permisos de módulo', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al procesar permisos por lote.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
