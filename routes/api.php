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

    // ==========================================
    // MÓDULO RECURSOS HUMANOS (CAPIBARA INTEGRADO)
    // ==========================================
    Route::get('rrhh/personal', [\App\Http\Controllers\Rrhh\PersonalController::class, 'index']);
    Route::post('rrhh/personal', [\App\Http\Controllers\Rrhh\PersonalController::class, 'store']);
    Route::get('rrhh/personal/mi-ficha', [\App\Http\Controllers\Rrhh\PersonalController::class, 'miFichaPersonal']);
    Route::get('rrhh/personal/{id}', [\App\Http\Controllers\Rrhh\PersonalController::class, 'show']);
    Route::post('rrhh/personal/{id}/estudios', [\App\Http\Controllers\Rrhh\PersonalController::class, 'storeEstudio']);
    Route::post('rrhh/personal/{id}/experiencia', [\App\Http\Controllers\Rrhh\PersonalController::class, 'storeExperiencia']);
    Route::post('rrhh/personal/{id}/cas', [\App\Http\Controllers\Rrhh\PersonalController::class, 'storeCas']);

    Route::get('rrhh/organigrama', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'organigrama']);
    Route::post('rrhh/unidades-organizacionales', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'storeUnidad']);
    Route::post('rrhh/puestos', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'storePuesto']);
    Route::post('rrhh/asignar-puesto', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'asignarPuesto']);
    Route::get('rrhh/escalas-salariales', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'listarEscalasSalariales']);
    Route::post('rrhh/escalas-salariales', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'storeEscalaSalarial']);
    Route::get('rrhh/regionales', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'listarRegionales']);
    Route::post('rrhh/regionales', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'storeRegional']);
    Route::get('rrhh/gestiones', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'listarGestiones']);
    Route::post('rrhh/gestiones', [\App\Http\Controllers\Rrhh\EstructuraOrganizacionalController::class, 'storeGestion']);

    Route::get('rrhh/biometricos', [\App\Http\Controllers\Rrhh\AsistenciaController::class, 'listarBiometricos']);
    Route::post('rrhh/biometricos/{id}/probar-conexion', [\App\Http\Controllers\Rrhh\AsistenciaController::class, 'probarConexion']);
    Route::post('rrhh/biometricos/{id}/sincronizar', [\App\Http\Controllers\Rrhh\AsistenciaController::class, 'sincronizar']);
    Route::get('rrhh/asistencias', [\App\Http\Controllers\Rrhh\AsistenciaController::class, 'listarAsistencias']);
    Route::post('rrhh/asistencias/calcular', [\App\Http\Controllers\Rrhh\AsistenciaController::class, 'calcularAsistencia']);

    Route::get('rrhh/permisos/catalogo', [\App\Http\Controllers\Rrhh\SolicitudSalidaController::class, 'catalogoPermisos']);
    Route::get('rrhh/solicitudes', [\App\Http\Controllers\Rrhh\SolicitudSalidaController::class, 'index']);
    Route::post('rrhh/solicitudes', [\App\Http\Controllers\Rrhh\SolicitudSalidaController::class, 'store']);

    // Horarios y Turnos
    Route::get('rrhh/horarios', [\App\Http\Controllers\Rrhh\HorarioController::class, 'index']);
    Route::post('rrhh/horarios', [\App\Http\Controllers\Rrhh\HorarioController::class, 'store']);
    Route::get('rrhh/asignaciones-horarios', [\App\Http\Controllers\Rrhh\HorarioController::class, 'listarAsignaciones']);
    Route::post('rrhh/asignaciones-horarios', [\App\Http\Controllers\Rrhh\HorarioController::class, 'asignarHorario']);

    // Comisiones, Omisiones y Aprobaciones
    Route::get('rrhh/comisiones', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'listarComisiones']);
    Route::post('rrhh/comisiones', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'storeComision']);
    Route::get('rrhh/omisiones', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'listarOmisiones']);
    Route::post('rrhh/omisiones', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'storeOmision']);
    Route::get('rrhh/bandeja-aprobaciones', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'bandejaAprobaciones']);
    Route::put('rrhh/bandeja-aprobaciones/{id}/resolver', [\App\Http\Controllers\Rrhh\ComisionesOmisionesController::class, 'resolverSolicitud']);

    // Feriados y Fechas de Corte
    Route::get('rrhh/feriados', [\App\Http\Controllers\Rrhh\FeriadoCorteController::class, 'listarFeriados']);
    Route::post('rrhh/feriados', [\App\Http\Controllers\Rrhh\FeriadoCorteController::class, 'storeFeriado']);
    Route::get('rrhh/fechas-corte', [\App\Http\Controllers\Rrhh\FeriadoCorteController::class, 'listarFechasCorte']);
    Route::post('rrhh/fechas-corte', [\App\Http\Controllers\Rrhh\FeriadoCorteController::class, 'storeFechaCorte']);

    // Reportes Oficiales y Planillas
    Route::get('rrhh/reportes/boleta-salida/{id}/html', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'boletaSalidaHtml']);
    Route::get('rrhh/reportes/asistencia-mensual', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'asistenciaMensual']);
    Route::get('rrhh/reportes/refrigerio-mensual', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'refrigerioMensual']);
    Route::get('rrhh/reportes/saldo-vacaciones', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'saldoVacaciones']);
    Route::get('rrhh/reportes/planilla-sueldos', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'planillaSueldosMensual']);
    Route::post('rrhh/reportes/cerrar-declarar-planilla', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'cerrarYDeclararPlanilla']);
    Route::get('rrhh/reportes/boleta-pago/{personaId}/html', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'boletaPagoHtml']);
    Route::get('rrhh/reportes/padron-personal', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'padronPersonal']);
    Route::get('rrhh/reportes/kardex-funcionario/{personaId}/html', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'kardexFuncionarioHtml']);
    Route::post('rrhh/reportes/generar-personalizado', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'generarReportePersonalizado']);
    Route::get('rrhh/reportes/certificado-trabajo/{personaId}/html', [\App\Http\Controllers\Rrhh\ReporteRrhhController::class, 'certificadoTrabajoHtml']);

    // Rutas para Obtener Estructura de Menú (Según Usuario/Rol Autenticado)
    Route::get('usuario/menu-rol/{rolId}', [UsuarioController::class, 'menuRol']);
    Route::get('usuario/menu-usuario/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-navegacion/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-acopio/{usuarioId}', [UsuarioController::class, 'menuUsuario']); // Compatibilidad

    // Paramétricas y Datos Maestros
    Route::apiResource('parametrica-api', ParametricaController::class);
    Route::post('registrar_campo', [ParametricaController::class, 'registrar_campo']);

    // ==========================================
    // MÓDULO DE CORRESPONDENCIA Y HOJAS DE RUTA (LONDRA)
    // ==========================================
    // 1. Hojas de Ruta
    Route::get('correspondencia/hojas-ruta', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'index']);
    Route::post('correspondencia/hojas-ruta', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'store']);
    Route::get('correspondencia/hojas-ruta/{id}', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'show']);
    Route::get('correspondencia/hojas-ruta/{id}/caratula', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'caratula']);
    Route::post('correspondencia/hojas-ruta/{id}/cerrar', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'cerrar']);
    Route::post('correspondencia/hojas-ruta/{id}/reabrir', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'reabrir']);
    Route::post('correspondencia/hojas-ruta/agrupar', [\App\Http\Controllers\Correspondencia\HojaRutaController::class, 'agrupar']);

    // 2. Derivaciones y Workflow
    Route::post('correspondencia/derivaciones', [\App\Http\Controllers\Correspondencia\DerivacionController::class, 'derivar']);
    Route::post('correspondencia/derivaciones/{id}/recibir', [\App\Http\Controllers\Correspondencia\DerivacionController::class, 'recibir']);
    Route::post('correspondencia/derivaciones/{id}/devolver', [\App\Http\Controllers\Correspondencia\DerivacionController::class, 'devolver']);

    // 3. Documentos Oficiales
    Route::get('correspondencia/documentos', [\App\Http\Controllers\Correspondencia\DocumentoController::class, 'index']);
    Route::post('correspondencia/documentos', [\App\Http\Controllers\Correspondencia\DocumentoController::class, 'store']);
    Route::get('correspondencia/documentos/{id}', [\App\Http\Controllers\Correspondencia\DocumentoController::class, 'show']);
    Route::get('correspondencia/documentos/{id}/preview', [\App\Http\Controllers\Correspondencia\DocumentoController::class, 'previewHtml']);
    Route::post('correspondencia/documentos/{id}/adjuntos', [\App\Http\Controllers\Correspondencia\DocumentoController::class, 'adjuntarArchivo']);

    // 4. Firmas y Aprobaciones
    Route::get('correspondencia/firmas/pendientes', [\App\Http\Controllers\Correspondencia\FirmaAprobacionController::class, 'pendientes']);
    Route::post('correspondencia/firmas/firmar', [\App\Http\Controllers\Correspondencia\FirmaAprobacionController::class, 'firmar']);
    Route::post('correspondencia/firmas/rechazar', [\App\Http\Controllers\Correspondencia\FirmaAprobacionController::class, 'rechazar']);

    // 5. Seguimiento y Trazabilidad
    Route::get('correspondencia/seguimiento/{id}/timeline', [\App\Http\Controllers\Correspondencia\SeguimientoController::class, 'timeline']);

    // 6. Ventanilla Única
    Route::get('correspondencia/ventanillas', [\App\Http\Controllers\Correspondencia\VentanillaController::class, 'index']);
    Route::post('correspondencia/ventanillas/entrada', [\App\Http\Controllers\Correspondencia\VentanillaController::class, 'registrarEntrada']);

    // 7. Configuración
    Route::get('correspondencia/configuracion/plantillas', [\App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController::class, 'plantillas']);
    Route::get('correspondencia/configuracion/correlativos', [\App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController::class, 'correlativos']);
    Route::get('correspondencia/configuracion/proveidos', [\App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController::class, 'proveidos']);
    Route::get('correspondencia/configuracion/secretarios', [\App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController::class, 'secretarios']);
    Route::post('correspondencia/configuracion/secretarios', [\App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController::class, 'storeSecretario']);

    // 8. Despacho y Bandeja de Salida Externa (Gestor de Salida)
    Route::get('correspondencia/despachos', [\App\Http\Controllers\Correspondencia\DespachoSalidaController::class, 'index']);
    Route::post('correspondencia/despachos', [\App\Http\Controllers\Correspondencia\DespachoSalidaController::class, 'store']);
    Route::post('correspondencia/despachos/{id}/entregar', [\App\Http\Controllers\Correspondencia\DespachoSalidaController::class, 'entregar']);

    // 9. Etiquetas y Carpetas Virtuales
    Route::get('correspondencia/etiquetas', [\App\Http\Controllers\Correspondencia\EtiquetaController::class, 'index']);
    Route::post('correspondencia/etiquetas', [\App\Http\Controllers\Correspondencia\EtiquetaController::class, 'store']);
    Route::post('correspondencia/etiquetas/asignar', [\App\Http\Controllers\Correspondencia\EtiquetaController::class, 'asignar']);
    Route::post('correspondencia/etiquetas/desasignar', [\App\Http\Controllers\Correspondencia\EtiquetaController::class, 'desasignar']);

    // 10. Accesos Compartidos
    Route::get('correspondencia/compartidos', [\App\Http\Controllers\Correspondencia\AccesoCompartidoController::class, 'index']);
    Route::post('correspondencia/compartidos/compartir', [\App\Http\Controllers\Correspondencia\AccesoCompartidoController::class, 'compartir']);

    // 11. Solicitudes Ciudadanas y Trámites Digitales
    Route::get('correspondencia/solicitudes-ciudadanas', [\App\Http\Controllers\Correspondencia\SolicitudCiudadanaController::class, 'index']);
    Route::post('correspondencia/solicitudes-ciudadanas/{id}/convertir-hoja-ruta', [\App\Http\Controllers\Correspondencia\SolicitudCiudadanaController::class, 'convertirEnHojaRuta']);
});

// Rutas Públicas (Sin Autenticación Requerida)
Route::get('correspondencia/publico/verificar-documento/{codigo}', [\App\Http\Controllers\Correspondencia\VerificacionPublicaController::class, 'verificarDocumento']);
Route::get('correspondencia/publico/verificar-hoja-ruta', [\App\Http\Controllers\Correspondencia\VerificacionPublicaController::class, 'verificarHojaRuta']);
Route::post('correspondencia/publico/solicitud-ciudadana', [\App\Http\Controllers\Correspondencia\SolicitudCiudadanaController::class, 'registrarPublico']);


