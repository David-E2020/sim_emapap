<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Administracion\UsuarioController;
use App\Http\Controllers\Administracion\AccesoUsuarioController;
use App\Http\Controllers\Administracion\Parametricas\ParametricaController;
use App\Http\Controllers\Administracion\AuditLogController;
use App\Http\Controllers\Administracion\RolesPermisosController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRolController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema Base Independiente
|--------------------------------------------------------------------------
*/

// Rutas Públicas
Route::post('login', [AuthController::class, 'login']);

// Rutas Autenticadas (JWT)
Route::group(['middleware' => ['jwt.auth']], function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('update_user_password', [RolUserController::class, 'update_user_password']);

    // Rutas protegidas para administración de usuarios, accesos y menús
    Route::group(['middleware' => ['admin.access']], function () {
        Route::get('usuario', [UsuarioController::class, 'index']);
        Route::post('usuario', [UsuarioController::class, 'store']);
        Route::get('usuario/{id}', [UsuarioController::class, 'show']);
        Route::put('usuario/{id}', [UsuarioController::class, 'update']);
        Route::get('usuario/agregar-sistema/{id}', [UsuarioController::class, 'agregarSistema']);
        Route::get('usuario/quitar-sistema/{id}', [UsuarioController::class, 'quitarSistema']);
        Route::get('usuario/rol-user/{id}', [UsuarioController::class, 'rolUser']);
        Route::get('usuario_rol', [UsuarioController::class, 'usuario_rol']);
        Route::get('listar_usuario_acceso', [AccesoUsuarioController::class, 'listar_usuario_acceso']);
        Route::post('guardar_acceso_usuario', [AccesoUsuarioController::class, 'guardar_acceso_usuario']);

        // Matriz de Roles y Permisos Granulares (Spatie) y Gestión Unificada de Menús
        Route::get('roles-permisos/matriz/{rolId}', [RolesPermisosController::class, 'matriz']);
        Route::get('roles-permisos/permisos-huerfanos', [RolesPermisosController::class, 'permisosHuerfanos']);
        Route::post('roles-permisos/crear-menu', [RolesPermisosController::class, 'crearMenu']);
        Route::put('roles-permisos/actualizar-menu/{id}', [RolesPermisosController::class, 'actualizarMenu']);
        Route::delete('roles-permisos/eliminar-menu/{id}', [RolesPermisosController::class, 'eliminarMenu']);
        Route::post('roles-permisos/reordenar-menus', [RolesPermisosController::class, 'reordenarMenus']);
        Route::post('roles-permisos/toggle-permiso', [RolesPermisosController::class, 'togglePermiso']);
        Route::post('roles-permisos/toggle-menu-rol', [RolesPermisosController::class, 'toggleMenuRol']);
        Route::post('roles-permisos/batch-module-permissions', [RolesPermisosController::class, 'batchModulePermissions']);

        // Auditoría de Seguridad Inmutable
        Route::get('audit-logs', [AuditLogController::class, 'index']);

        Route::apiResource('rol', RolController::class);
        Route::apiResource('menu', MenuController::class);
        Route::get('menu/permisos-submenu/{id}', [MenuController::class, 'getPermisosSubmenu']);
        Route::post('menu/crear-permiso-submenu', [MenuController::class, 'crearPermisoSubmenu']);
        Route::post('menu/change/cambiar-orden', [MenuController::class, 'cambiarOrden']);
        Route::apiResource('rol-user', RolUserController::class);
        Route::apiResource('menu-rol', MenuRolController::class);
    });

    // Rutas para Obtener Estructura de Menú (Según Usuario/Rol Autenticado)
    Route::get('usuario/menu-rol/{rolId}', [UsuarioController::class, 'menuRol']);
    Route::get('usuario/menu-usuario/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-navegacion/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-acopio/{usuarioId}', [UsuarioController::class, 'menuUsuario']); // Compatibilidad

    // Paramétricas y Datos Maestros
    Route::apiResource('parametrica-api', ParametricaController::class);
    Route::post('registrar_campo', [ParametricaController::class, 'registrar_campo']);
});
