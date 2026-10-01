<?php

use App\Http\Controllers\Administracion\AccesoUsuarioController;
use App\Http\Controllers\Administracion\AuditLogController;
use App\Http\Controllers\Administracion\Parametricas\ParametricaController;
use App\Http\Controllers\Administracion\RolesPermisosController;
use App\Http\Controllers\Administracion\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Correspondencia\AccesoCompartidoController;
use App\Http\Controllers\Correspondencia\ConfiguracionCorrespondenciaController;
use App\Http\Controllers\Correspondencia\DashboardCorrespondenciaController;
use App\Http\Controllers\Correspondencia\DerivacionController;
use App\Http\Controllers\Correspondencia\DespachoSalidaController;
use App\Http\Controllers\Correspondencia\DocumentoController;
use App\Http\Controllers\Correspondencia\EtiquetaController;
use App\Http\Controllers\Correspondencia\FirmaAprobacionController;
use App\Http\Controllers\Correspondencia\HojaRutaController;
use App\Http\Controllers\Correspondencia\PermisosCorrespondenciaController;
use App\Http\Controllers\Correspondencia\RevisionDocumentoController;
use App\Http\Controllers\Correspondencia\SeguimientoController;
use App\Http\Controllers\Correspondencia\SolicitudCiudadanaController;
use App\Http\Controllers\Correspondencia\TransferenciaController;
use App\Http\Controllers\Correspondencia\VentanillaController;
use App\Http\Controllers\Correspondencia\VerificacionPublicaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRolController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolUserController;
use App\Http\Controllers\Rrhh\AsistenciaController;
use App\Http\Controllers\Rrhh\ComisionesOmisionesController;
use App\Http\Controllers\Rrhh\EstructuraOrganizacionalController;
use App\Http\Controllers\Rrhh\FeriadoCorteController;
use App\Http\Controllers\Rrhh\HorarioController;
use App\Http\Controllers\Rrhh\PersonalController;
use App\Http\Controllers\Rrhh\ReporteRrhhController;
use App\Http\Controllers\Facturacion\ClienteFacturaController;
use App\Http\Controllers\Facturacion\EventoSignificativoController;
use App\Http\Controllers\Facturacion\FacturaController;
use App\Http\Controllers\Facturacion\FacturacionCucuGatewayController;
use App\Http\Controllers\Facturacion\ReporteFacturacionController;
use App\Http\Controllers\Facturacion\SiatCodigoController;
use App\Http\Controllers\Comercial\AbonadoController;
use App\Http\Controllers\Comercial\LecturaController;
use App\Http\Controllers\Comercial\CobranzaCajaController;
use App\Http\Controllers\Comercial\CajaSesionController;
use App\Http\Controllers\Comercial\ConvenioController;
use App\Http\Controllers\Comercial\CorteReconexionController;
use App\Http\Controllers\Comercial\TarifaZonaController;
use App\Http\Controllers\Comercial\ReporteComercialController;
use App\Http\Controllers\Comercial\AporteConexionController;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Rrhh\SolicitudSalidaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema Base Independiente
|--------------------------------------------------------------------------
*/

// Rutas Públicas (Protegidas con Throttling: 60 intentos por minuto anti-fuerza bruta)
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:60,1');
Route::post('logout', [AuthController::class, 'logout']);

// Rutas Autenticadas (JWT)
Route::group(['middleware' => ['jwt.auth']], function () {

    Route::post('update_user_password', [RolUserController::class, 'update_user_password']);

    // Dashboard Operativo y Métricas Globales (Requiere rol activo o Super Admin)
    Route::get('dashboard/metricas', [DashboardController::class, 'metricas'])->middleware('check.permission');

    // Rutas protegidas para administración de usuarios, accesos y menús
    Route::group(['middleware' => ['admin.access']], function () {
        Route::get('usuario', [UsuarioController::class, 'index']);
        Route::post('usuario', [UsuarioController::class, 'store']);
        Route::get('usuario/{id}', [UsuarioController::class, 'show']);
        Route::put('usuario/{id}', [UsuarioController::class, 'update']);
        Route::get('usuario/agregar-sistema/{id}', [UsuarioController::class, 'agregarSistema']);
        Route::get('usuario/quitar-sistema/{id}', [UsuarioController::class, 'quitarSistema']);
        Route::post('usuario/{id}/reset-password', [UsuarioController::class, 'resetPasswordInstitucional']);
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
    // RUTAS BASE DE USUARIO Y NAVEGACIÓN (Requieren rol activo en el sistema)
    // ==========================================
    Route::group(['middleware' => ['check.permission']], function () {
        Route::get('menu_usuario', function () {
            $user = auth()->user() ?: request()->user('api');
            $userId = $user ? $user->id : 1;
            return app(UsuarioController::class)->menuUsuario($userId);
        });
        Route::get('usuario/menu-acopio', function () {
            $user = auth()->user() ?: request()->user('api');
            $userId = $user ? $user->id : 1;
            return app(UsuarioController::class)->menuUsuario($userId);
        });
        Route::get('usuario/menu-usuario', function () {
            $user = auth()->user() ?: request()->user('api');
            $userId = $user ? $user->id : 1;
            return app(UsuarioController::class)->menuUsuario($userId);
        });
        Route::get('usuario/menu-rol/{rolId}', [UsuarioController::class, 'menuRol']);
        Route::get('usuario/menu-usuario/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
        Route::get('usuario/menu-navegacion/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
        Route::get('usuario/menu-acopio/{usuarioId}', [UsuarioController::class, 'menuUsuario']); // Compatibilidad
        Route::get('usuario/rutas-permitidas', function () {
            $user = auth()->user() ?: request()->user('api');
            if (! $user) {
                return response()->json(['success' => false, 'allowed_routes' => []], 401);
            }
            $menuData = app(UsuarioController::class)->menuUsuario($user->id);
            $allowedRoutes = [];
            if (! empty($menuData['menus'])) {
                foreach ($menuData['menus'] as $parentMenu) {
                    if (! empty($parentMenu->sub_menu)) {
                        foreach ($parentMenu->sub_menu as $sub) {
                            if (! empty($sub->route)) {
                                $allowedRoutes[] = $sub->route;
                            }
                        }
                    }
                }
            }
            return response()->json([
                'success' => true,
                'allowed_routes' => $allowedRoutes,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]);
        });
    });

    // ==========================================
    // MÓDULO PARAMÉTRICAS Y DATOS MAESTROS
    // ==========================================
    Route::group(['middleware' => ['check.permission:parametricas.ver|parametricas.crear|parametricas.editar']], function () {
        Route::apiResource('parametrica-api', ParametricaController::class);
        Route::post('registrar_campo', [ParametricaController::class, 'registrar_campo']);
    });

    // Configuración Institucional y Parámetros SIAT (Módulo Datos)
    Route::group(['middleware' => ['check.permission:datos.empresa.gestionar']], function () {
        Route::get('datos/empresa', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'obtener']);
        Route::post('datos/empresa', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'guardar']);
        Route::post('datos/empresa/probar-conexion', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'probarConexion']);
        Route::post('datos/empresa/restablecer-endpoints', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'restablecerEndpoints']);
    });

    // Migrador y Comparador de Respaldos FoxPro (Módulo Datos)
    Route::group(['middleware' => ['check.permission:datos.migrador.ejecutar']], function () {
        Route::get('datos/migracion/rutas-predefinidas', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'rutasPredefinidas']);
        Route::post('datos/migracion/explorar-servidor', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'explorarServidor']);
        Route::post('datos/migracion/escanear', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'escanear']);
        Route::post('datos/migracion/subir-respaldo', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'subirRespaldo']);
        Route::post('datos/migracion/subir-chunk', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'subirChunk']);
        Route::post('datos/migracion/iniciar-fondo', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'iniciarFondo']);
        Route::get('datos/migracion/estado-job/{jobId}', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'estadoJob']);
        Route::post('datos/migracion/cancelar-job/{jobId}', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'cancelarJob']);
        Route::post('datos/migracion/ejecutar', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'ejecutar']);
        Route::get('datos/migracion/historial', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'historial']);
        Route::post('datos/migracion/revertir', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'revertir']);
        Route::post('datos/migracion/vincular-facturas-lecturas', [\App\Http\Controllers\Datos\MigracionFoxProController::class, 'vincularFacturasLecturas']);
    });

    // ==========================================
    // MÓDULO RECURSOS HUMANOS (CAPIBARA INTEGRADO)
    // ==========================================
    Route::group(['prefix' => 'rrhh'], function () {
        // Mi ficha personal (accesible para cualquier funcionario con sesión iniciada)
        Route::get('personal/mi-ficha', [PersonalController::class, 'miFichaPersonal']);

        // Personal y Legajos
        Route::group(['middleware' => ['check.permission:rrhh.personal.ver|rrhh.personal.crear|rrhh.personal.editar|rrhh.personal.legajo']], function () {
            Route::get('personal', [PersonalController::class, 'index']);
            Route::post('personal', [PersonalController::class, 'store']);
            Route::put('personal/{id}', [PersonalController::class, 'update']);
            Route::get('personal/{id}', [PersonalController::class, 'show']);
            Route::post('personal/{id}/estudios', [PersonalController::class, 'storeEstudio']);
            Route::post('personal/{id}/experiencia', [PersonalController::class, 'storeExperiencia']);
            Route::post('personal/{id}/cas', [PersonalController::class, 'storeCas']);
            Route::post('personal/{id}/crear-usuario', [PersonalController::class, 'crearUsuarioErp']);
            Route::post('personal/{id}/reset-password', [PersonalController::class, 'resetPasswordErp']);
        });

        // Estructura Organizacional
        Route::group(['middleware' => ['check.permission:rrhh.organigrama.ver|rrhh.organigrama.crear_unidad|rrhh.organigrama.crear_puesto|rrhh.organigrama.asignar_puesto']], function () {
            Route::get('organigrama', [EstructuraOrganizacionalController::class, 'organigrama']);
            Route::post('unidades-organizacionales', [EstructuraOrganizacionalController::class, 'storeUnidad']);
            Route::put('unidades-organizacionales/{id}', [EstructuraOrganizacionalController::class, 'updateUnidad']);
            Route::delete('unidades-organizacionales/{id}', [EstructuraOrganizacionalController::class, 'destroyUnidad']);
            Route::post('puestos', [EstructuraOrganizacionalController::class, 'storePuesto']);
            Route::put('puestos/{id}', [EstructuraOrganizacionalController::class, 'updatePuesto']);
            Route::delete('puestos/{id}', [EstructuraOrganizacionalController::class, 'destroyPuesto']);
            Route::post('asignar-puesto', [EstructuraOrganizacionalController::class, 'asignarPuesto']);
            Route::post('asignaciones-puestos/{id}/desvincular', [EstructuraOrganizacionalController::class, 'desasignarPuesto']);
            Route::delete('asignaciones-puestos/{id}', [EstructuraOrganizacionalController::class, 'desasignarPuesto']);
            Route::get('puestos/{id}/historial', [EstructuraOrganizacionalController::class, 'historialPuesto']);
            Route::get('escalas-salariales', [EstructuraOrganizacionalController::class, 'listarEscalasSalariales']);
            Route::post('escalas-salariales', [EstructuraOrganizacionalController::class, 'storeEscalaSalarial']);
            Route::get('regionales', [EstructuraOrganizacionalController::class, 'listarRegionales']);
            Route::post('regionales', [EstructuraOrganizacionalController::class, 'storeRegional']);
            Route::get('gestiones', [EstructuraOrganizacionalController::class, 'listarGestiones']);
            Route::post('gestiones', [EstructuraOrganizacionalController::class, 'storeGestion']);
        });

        // Control de Asistencia y Biométricos
        Route::group(['middleware' => ['check.permission:rrhh.asistencias.ver|rrhh.asistencias.sincronizar_biometrico|rrhh.asistencias.calcular_asistencia|rrhh.biometricos.administrar']], function () {
            Route::get('biometricos', [AsistenciaController::class, 'listarBiometricos']);
            Route::post('biometricos/{id}/probar-conexion', [AsistenciaController::class, 'probarConexion']);
            Route::post('biometricos/{id}/sincronizar', [AsistenciaController::class, 'sincronizar']);
            Route::get('asistencias', [AsistenciaController::class, 'listarAsistencias']);
            Route::post('asistencias/calcular', [AsistenciaController::class, 'calcularAsistencia']);
        });

        // Solicitudes y Permisos
        Route::group(['middleware' => ['check.permission:rrhh.solicitudes.ver|rrhh.solicitudes.crear|rrhh.solicitudes.aprobar']], function () {
            Route::get('permisos/catalogo', [SolicitudSalidaController::class, 'catalogoPermisos']);
            Route::get('solicitudes', [SolicitudSalidaController::class, 'index']);
            Route::post('solicitudes', [SolicitudSalidaController::class, 'store']);
        });

        // Horarios y Turnos
        Route::group(['middleware' => ['check.permission:rrhh.horarios.ver|rrhh.horarios.crear|rrhh.horarios.asignar']], function () {
            Route::get('horarios', [HorarioController::class, 'index']);
            Route::post('horarios', [HorarioController::class, 'store']);
            Route::get('asignaciones-horarios', [HorarioController::class, 'listarAsignaciones']);
            Route::post('asignaciones-horarios', [HorarioController::class, 'asignarHorario']);
        });

        // Comisiones, Omisiones y Aprobaciones
        Route::group(['middleware' => ['check.permission:rrhh.comisiones.ver|rrhh.comisiones.crear|rrhh.omisiones.crear|rrhh.omisiones.aprobar']], function () {
            Route::get('comisiones', [ComisionesOmisionesController::class, 'listarComisiones']);
            Route::post('comisiones', [ComisionesOmisionesController::class, 'storeComision']);
            Route::get('omisiones', [ComisionesOmisionesController::class, 'listarOmisiones']);
            Route::post('omisiones', [ComisionesOmisionesController::class, 'storeOmision']);
            Route::get('bandeja-aprobaciones', [ComisionesOmisionesController::class, 'bandejaAprobaciones']);
            Route::put('bandeja-aprobaciones/{id}/resolver', [ComisionesOmisionesController::class, 'resolverSolicitud']);
        });

        // Feriados y Fechas de Corte
        Route::group(['middleware' => ['check.permission:rrhh.feriados.ver|rrhh.feriados.crear']], function () {
            Route::get('feriados', [FeriadoCorteController::class, 'listarFeriados']);
            Route::post('feriados', [FeriadoCorteController::class, 'storeFeriado']);
            Route::get('fechas-corte', [FeriadoCorteController::class, 'listarFechasCorte']);
            Route::post('fechas-corte', [FeriadoCorteController::class, 'storeFechaCorte']);
        });

        // Reportes Oficiales y Planillas
        Route::group(['middleware' => ['check.permission:rrhh.reportes.asistencia|rrhh.reportes.refrigerio|rrhh.reportes.vacaciones|rrhh.reportes.boleta_imprimir']], function () {
            Route::get('reportes/boleta-salida/{id}/html', [ReporteRrhhController::class, 'boletaSalidaHtml']);
            Route::get('reportes/asistencia-mensual', [ReporteRrhhController::class, 'asistenciaMensual']);
            Route::get('reportes/refrigerio-mensual', [ReporteRrhhController::class, 'refrigerioMensual']);
            Route::get('reportes/saldo-vacaciones', [ReporteRrhhController::class, 'saldoVacaciones']);
            Route::get('reportes/planilla-sueldos', [ReporteRrhhController::class, 'planillaSueldosMensual']);
            Route::post('reportes/cerrar-declarar-planilla', [ReporteRrhhController::class, 'cerrarYDeclararPlanilla']);
            Route::get('reportes/boleta-pago/{personaId}/html', [ReporteRrhhController::class, 'boletaPagoHtml']);
            Route::get('reportes/padron-personal', [ReporteRrhhController::class, 'padronPersonal']);
            Route::get('reportes/kardex-funcionario/{personaId}/html', [ReporteRrhhController::class, 'kardexFuncionarioHtml']);
            Route::post('reportes/generar-personalizado', [ReporteRrhhController::class, 'generarReportePersonalizado']);
            Route::get('reportes/certificado-trabajo/{personaId}/html', [ReporteRrhhController::class, 'certificadoTrabajoHtml']);
        });
    });

    // ==========================================
    // MÓDULO DE CORRESPONDENCIA Y HOJAS DE RUTA (LONDRA)
    // ==========================================
    Route::group(['prefix' => 'correspondencia'], function () {
        // Hojas de Ruta y Bandeja
        Route::group(['middleware' => ['check.permission:correspondencia.hojas_ruta.ver|correspondencia.hojas_ruta.crear|correspondencia.hojas_ruta.derivar|correspondencia.hojas_ruta.recibir']], function () {
            Route::get('hojas-ruta/bandeja', [HojaRutaController::class, 'bandeja']);
            Route::get('hojas-ruta', [HojaRutaController::class, 'index']);
            Route::post('hojas-ruta', [HojaRutaController::class, 'store']);
            Route::post('hojas-ruta/agrupar', [HojaRutaController::class, 'agrupar']);
            Route::get('hojas-ruta/{id}/acciones-permitidas', [HojaRutaController::class, 'accionesPermitidas']);
            Route::get('hojas-ruta/{id}', [HojaRutaController::class, 'show']);
            Route::get('hojas-ruta/{id}/caratula', [HojaRutaController::class, 'caratula']);
            Route::post('hojas-ruta/{id}/cerrar', [HojaRutaController::class, 'cerrar']);
            Route::post('hojas-ruta/{id}/reabrir', [HojaRutaController::class, 'reabrir']);
            Route::post('hojas-ruta/{id}/desagrupar', [HojaRutaController::class, 'desagrupar']);

            // Derivaciones
            Route::post('derivaciones', [DerivacionController::class, 'derivar']);
            Route::post('derivaciones/{id}/recibir', [DerivacionController::class, 'recibir']);
            Route::post('derivaciones/{id}/devolver', [DerivacionController::class, 'devolver']);
            Route::post('derivaciones/{id}/anular', [DerivacionController::class, 'anular']);

            // Accesos Compartidos
            Route::get('compartidos', [AccesoCompartidoController::class, 'index']);
            Route::post('compartidos/compartir', [AccesoCompartidoController::class, 'compartir']);
        });

        // Documentos Oficiales
        Route::group(['middleware' => ['check.permission:correspondencia.documentos.ver|correspondencia.documentos.crear']], function () {
            Route::get('documentos/bandeja', [DocumentoController::class, 'bandeja']);
            Route::get('documentos', [DocumentoController::class, 'index']);
            Route::post('documentos', [DocumentoController::class, 'store']);
            Route::get('documentos/{id}', [DocumentoController::class, 'show']);
            Route::put('documentos/{id}', [DocumentoController::class, 'update']);
            Route::delete('documentos/{id}', [DocumentoController::class, 'destroy']);
            Route::post('documentos/{id}/anular', [DocumentoController::class, 'anular']);
            Route::post('documentos/{id}/enviar-revision', [DocumentoController::class, 'enviarRevision']);
            Route::get('documentos/{id}/preview', [DocumentoController::class, 'previewHtml']);
            Route::post('documentos/{id}/adjuntos', [DocumentoController::class, 'adjuntarArchivo']);
            Route::get('documentos/{id}/revisiones', [RevisionDocumentoController::class, 'index']);
            Route::post('documentos/{id}/revisiones', [RevisionDocumentoController::class, 'store']);
        });

        // Firmas y Aprobaciones
        Route::group(['middleware' => ['check.permission:correspondencia.documentos.firmar']], function () {
            Route::get('firmas/pendientes', [FirmaAprobacionController::class, 'pendientes']);
            Route::post('firmas/firmar', [FirmaAprobacionController::class, 'firmar']);
            Route::post('firmas/rechazar', [FirmaAprobacionController::class, 'rechazar']);
        });

        // Seguimiento y Trazabilidad
        Route::group(['middleware' => ['check.permission:correspondencia.seguimiento.ver|correspondencia.hojas_ruta.ver']], function () {
            Route::get('seguimiento/{id}/timeline', [SeguimientoController::class, 'timeline'])->where('id', '.*');
        });

        // Ventanilla Única
        Route::group(['middleware' => ['check.permission:correspondencia.ventanilla.recibir|correspondencia.ventanilla.despachar']], function () {
            Route::get('ventanillas', [VentanillaController::class, 'index']);
            Route::post('ventanillas/entrada', [VentanillaController::class, 'registrarEntrada']);
        });

        // Configuración y Diseñador de Plantillas
        Route::group(['middleware' => ['check.permission:correspondencia.configuracion.administrar']], function () {
            Route::get('configuracion/plantillas', [ConfiguracionCorrespondenciaController::class, 'plantillas']);
            Route::get('configuracion/plantillas/{id}', [ConfiguracionCorrespondenciaController::class, 'showPlantilla']);
            Route::post('configuracion/plantillas', [ConfiguracionCorrespondenciaController::class, 'storePlantilla']);
            Route::get('configuracion/correlativos', [ConfiguracionCorrespondenciaController::class, 'correlativos']);
            Route::get('configuracion/proveidos', [ConfiguracionCorrespondenciaController::class, 'proveidos']);
            Route::get('configuracion/secretarios', [ConfiguracionCorrespondenciaController::class, 'secretarios']);
            Route::post('configuracion/secretarios', [ConfiguracionCorrespondenciaController::class, 'storeSecretario']);
        });

        // Despacho y Bandeja de Salida Externa
        Route::group(['middleware' => ['check.permission:correspondencia.despachos.administrar']], function () {
            Route::get('despachos', [DespachoSalidaController::class, 'index']);
            Route::post('despachos', [DespachoSalidaController::class, 'store']);
            Route::post('despachos/{id}/entregar', [DespachoSalidaController::class, 'entregar']);
        });

        // Etiquetas y Carpetas Virtuales
        Route::group(['middleware' => ['check.permission:correspondencia.etiquetas.administrar']], function () {
            Route::get('etiquetas', [EtiquetaController::class, 'index']);
            Route::post('etiquetas', [EtiquetaController::class, 'store']);
            Route::post('etiquetas/asignar', [EtiquetaController::class, 'asignar']);
            Route::post('etiquetas/desasignar', [EtiquetaController::class, 'desasignar']);
        });

        // Solicitudes Ciudadanas
        Route::group(['middleware' => ['check.permission:correspondencia.solicitudes.administrar']], function () {
            Route::get('solicitudes-ciudadanas', [SolicitudCiudadanaController::class, 'index']);
            Route::post('solicitudes-ciudadanas/{id}/convertir-hoja-ruta', [SolicitudCiudadanaController::class, 'convertirEnHojaRuta']);
        });

        // Dashboard y Métricas
        Route::group(['middleware' => ['check.permission:correspondencia.dashboard.ver']], function () {
            Route::get('dashboard/cards', [DashboardCorrespondenciaController::class, 'cards']);
            Route::get('dashboard/charts', [DashboardCorrespondenciaController::class, 'charts']);
        });

        // Matriz de Permisos de Derivación
        Route::group(['middleware' => ['check.permission:correspondencia.permisos.administrar']], function () {
            Route::get('permisos/derivaciones', [PermisosCorrespondenciaController::class, 'derivaciones']);
            Route::post('permisos/derivaciones', [PermisosCorrespondenciaController::class, 'storePermisoDerivacion']);
            Route::get('permisos/arbol-jerarquico', [PermisosCorrespondenciaController::class, 'arbolJerarquico']);
            Route::get('permisos/lista-grupos/{tipo}', [PermisosCorrespondenciaController::class, 'listaGrupos']);
            Route::get('permisos/destinos-asignados', [PermisosCorrespondenciaController::class, 'destinosAsignados']);
            Route::post('permisos/guardar-lote', [PermisosCorrespondenciaController::class, 'guardarLote']);
            Route::post('permisos/restablecer', [PermisosCorrespondenciaController::class, 'restablecer']);
            Route::get('permisos/resumen', [PermisosCorrespondenciaController::class, 'resumen']);
        });

        // Transferencias Masivas de Bandejas
        Route::group(['middleware' => ['check.permission:correspondencia.transferencias.ejecutar']], function () {
            Route::post('transferencias', [TransferenciaController::class, 'transferir']);
        });
    });

    // ==========================================
    // MÓDULO DE FACTURACIÓN ELECTRÓNICA EN LÍNEA (SIAT - SIN)
    // ==========================================
    Route::group(['prefix' => 'facturacion'], function () {
        // Facturas y Cobro en Ventanilla
        Route::group(['middleware' => ['check.permission:facturacion.caja.cobrar|facturacion.facturas.ver|facturacion.facturas.crear|facturacion.facturas.anular']], function () {
            Route::get('facturas', [FacturaController::class, 'index']);
            Route::get('facturas/exportar-pdf', [FacturaController::class, 'exportarPdf']);
            Route::get('facturas/exportar-excel', [FacturaController::class, 'exportarExcel']);
            Route::post('facturas', [FacturaController::class, 'store']);
            Route::post('facturas/emision-masiva', [FacturaController::class, 'emisionMasiva']);
            Route::post('facturas/{id}/anular', [FacturaController::class, 'anular']);
            Route::post('facturas/{id}/anular-administrativa', [FacturacionCucuGatewayController::class, 'anularAdministrativa']);
            Route::post('facturas/{id}/revertir-anulacion', [FacturacionCucuGatewayController::class, 'revertirAnulacion']);
            Route::post('facturas/{id}/enviar-correo', [FacturaController::class, 'enviarPorCorreo']);
            Route::get('facturas/{id}/verificar-estado-sin', [FacturaController::class, 'verificarEstadoSin']);
            Route::post('facturas/{id}/enviar-siat', [FacturaController::class, 'enviarSiat']);
            Route::get('facturas/{id}/pdf', [FacturaController::class, 'descargarPdf']);
            Route::get('facturas/{id}/preview', [FacturaController::class, 'previsualizarHtml']);
            Route::get('facturas/{id}/xml', [FacturaController::class, 'descargarXml']);

            // Cobros QR Simple
            Route::post('cobros-qr/generar', [FacturacionCucuGatewayController::class, 'generarQr']);
            Route::get('cobros-qr/{uuid}/estado', [FacturacionCucuGatewayController::class, 'consultarEstadoQr']);
            Route::post('cobros-qr/{uuid}/confirmar', [FacturacionCucuGatewayController::class, 'confirmarPagoQr']);
            Route::get('dashboard/metricas', [FacturacionCucuGatewayController::class, 'metricasDashboard']);
        });

        // Clientes Facturación Libre
        Route::group(['middleware' => ['check.permission:facturacion.clientes.administrar|facturacion.facturas.crear|facturacion.caja.cobrar']], function () {
            Route::get('clientes', [ClienteFacturaController::class, 'index']);
            Route::post('clientes', [ClienteFacturaController::class, 'store']);
            Route::get('clientes/{id}', [ClienteFacturaController::class, 'show']);
            Route::delete('clientes/{id}', [ClienteFacturaController::class, 'destroy']);
        });

        // Configuración y Parámetros SIAT
        Route::group(['middleware' => ['check.permission:facturacion.puntos_venta.administrar|facturacion.catalogos.administrar|facturacion.caja.cobrar|facturacion.facturas.ver']], function () {
            Route::get('siat/estado-conexion', [SiatCodigoController::class, 'estadoConexion']);
            Route::get('siat/sincronizar-hora', [SiatCodigoController::class, 'sincronizarHora']);
            Route::get('siat/verificar-nit/{nit}', [SiatCodigoController::class, 'verificarNit']);
            Route::get('siat/catalogos', [SiatCodigoController::class, 'catalogos']);
            Route::get('siat/sucursales', [SiatCodigoController::class, 'sucursales']);
            Route::get('siat/productos', [SiatCodigoController::class, 'productos']);
            Route::post('siat/productos', [SiatCodigoController::class, 'guardarProducto']);
            Route::post('siat/puntos-venta', [SiatCodigoController::class, 'registrarPuntoVenta']);
            Route::post('siat/puntos-venta/{id}/cierre', [SiatCodigoController::class, 'cerrarPuntoVenta']);
            Route::post('siat/puntos-venta/{id}/cuis', [SiatCodigoController::class, 'solicitarCuisPuntoVenta']);
        });

        // Eventos Significativos y Contingencias
        Route::group(['middleware' => ['check.permission:facturacion.eventos.administrar|facturacion.contingencias.administrar']], function () {
            Route::get('eventos-significativos', [EventoSignificativoController::class, 'index']);
            Route::post('eventos-significativos', [EventoSignificativoController::class, 'store']);
            Route::post('eventos-significativos/{id}/cerrar', [EventoSignificativoController::class, 'cerrar']);
            Route::post('eventos-significativos/paquetes/{id}/validar', [EventoSignificativoController::class, 'validarPaquete']);
        });

        // Reportes y Libro de Ventas IVA
        Route::group(['middleware' => ['check.permission:facturacion.libro_ventas.ver']], function () {
            Route::get('reportes/libro-ventas', [ReporteFacturacionController::class, 'libroVentas']);
            Route::get('reportes/libro-ventas/csv', [ReporteFacturacionController::class, 'exportarCsvLibroVentas']);
            Route::get('reportes/ventas-mensuales', [ReporteFacturacionController::class, 'ventasMensuales']);
        });
    });

    // ==========================================
    // MÓDULO COMERCIAL Y OPERATIVO DE AGUA POTABLE
    // ==========================================
    Route::group(['prefix' => 'comercial'], function () {
        // Padrón de Abonados
        Route::group(['middleware' => ['check.permission:comercial.abonados.ver|comercial.abonados.crear|comercial.abonados.georreferenciar|gis.georreferenciacion.editar']], function () {
            Route::get('abonados', [AbonadoController::class, 'index']);
            Route::post('abonados', [AbonadoController::class, 'store']);
            Route::get('abonados/{id}', [AbonadoController::class, 'show']);
            Route::put('abonados/{id}', [AbonadoController::class, 'update']);
            Route::post('abonados/{id}/cambiar-medidor', [AbonadoController::class, 'cambiarMedidor']);
            Route::post('abonados/{id}/dar-baja', [AbonadoController::class, 'darDeBaja']);
            Route::get('abonados/{id}/extracto/pdf', [AbonadoController::class, 'descargarExtractoPdf']);
        });

        // Ciclos y Lecturas
        Route::group(['middleware' => ['check.permission:comercial.periodos.administrar|comercial.lecturas.registrar']], function () {
            Route::get('periodos', [LecturaController::class, 'indexPeriodos']);
            Route::post('periodos/abrir', [LecturaController::class, 'abrirPeriodo']);
            Route::put('periodos/{id}', [LecturaController::class, 'actualizarPeriodo']);
            Route::post('periodos/{id}/cambiar-estado', [LecturaController::class, 'cambiarEstadoPeriodo']);
            Route::delete('periodos/{id}', [LecturaController::class, 'eliminarPeriodo']);
            Route::get('periodos/{id}/planilla', [LecturaController::class, 'obtenerPlanilla']);
            Route::get('periodos/{id}/avisos-cobranza/pdf', [LecturaController::class, 'descargarAvisosLote']);
            Route::post('lecturas/{id}', [LecturaController::class, 'guardarLectura']);
            Route::post('lecturas/lote', [LecturaController::class, 'guardarLote']);
            Route::post('periodos/{id}/liquidar', [LecturaController::class, 'liquidarPeriodo']);
        });

        // Ventanilla de Cobranzas y Recaudación (Caja)
        Route::group(['middleware' => ['check.permission:facturacion.caja.cobrar|comercial.sesiones_caja.aperturar|comercial.sesiones_caja.cerrar|comercial.sesiones_caja.supervisar']], function () {
            Route::get('caja/buscar-abonados', [CobranzaCajaController::class, 'buscarAbonados']);
            Route::get('caja/estado-cuenta/{codigo}', [CobranzaCajaController::class, 'estadoCuenta']);
            Route::post('caja/cobrar', [CobranzaCajaController::class, 'cobrar']);
            Route::post('caja/recibos', [CobranzaCajaController::class, 'emitirReciboCaja']);
            Route::get('caja/recibos/{id}/pdf', [CobranzaCajaController::class, 'descargarReciboCaja']);
            Route::get('caja/aviso-cobranza/{id}/pdf', [CobranzaCajaController::class, 'descargarAvisoCobranza']);

            // Sesiones de Caja, Apertura, Cierre, Arqueo
            Route::get('caja-sesiones/estado-actual', [CajaSesionController::class, 'estadoActual']);
            Route::get('caja-sesiones/cajas-disponibles', [CajaSesionController::class, 'cajasDisponibles']);
            Route::post('caja-sesiones/abrir', [CajaSesionController::class, 'abrir']);
            Route::get('caja-sesiones/resumen-arqueo', [CajaSesionController::class, 'resumenArqueo']);
            Route::post('caja-sesiones/cerrar', [CajaSesionController::class, 'cerrar']);
            Route::post('caja-sesiones/movimiento', [CajaSesionController::class, 'registrarMovimiento']);
            Route::get('caja-sesiones/{id}/reporte-pdf', [CajaSesionController::class, 'descargarReportePdf']);
            Route::get('caja-sesiones/historial', [CajaSesionController::class, 'historial']);
            Route::get('caja-sesiones/cajeros', [CajaSesionController::class, 'listarCajeros']);
            Route::post('cajas/{idPuntoVenta}/asignar-cajero', [CajaSesionController::class, 'asignarCajeroDefecto']);
        });

        // Convenios de Pago
        Route::group(['middleware' => ['check.permission:comercial.convenios.administrar']], function () {
            Route::get('convenios', [ConvenioController::class, 'index']);
            Route::post('convenios/simular', [ConvenioController::class, 'simular']);
            Route::post('convenios', [ConvenioController::class, 'store']);
        });

        // Cortes y Reconexiones
        Route::group(['middleware' => ['check.permission:comercial.cortes.administrar']], function () {
            Route::get('cortes/ordenes', [CorteReconexionController::class, 'indexOrdenes']);
            Route::get('cortes/ordenes/{id}/pdf', [CorteReconexionController::class, 'descargarOrdenTrabajo']);
            Route::get('cortes/candidatos', [CorteReconexionController::class, 'candidatosCorte']);
            Route::post('cortes/generar', [CorteReconexionController::class, 'generarCortes']);
            Route::post('cortes/{id}/ejecutar', [CorteReconexionController::class, 'ejecutarCorte']);
            Route::post('reconexiones/{id}/ejecutar', [CorteReconexionController::class, 'ejecutarReconexion']);
        });

        // Estructura Tarifaria, Zonas y Calles
        Route::group(['middleware' => ['check.permission:comercial.tarifas.administrar|comercial.zonas_calles.administrar|gis.mapa.ver|gis.capas.administrar']], function () {
            Route::get('paquetes-tarifarios', [TarifaZonaController::class, 'indexPaquetes']);
            Route::post('paquetes-tarifarios', [TarifaZonaController::class, 'storePaquete']);
            Route::post('paquetes-tarifarios/{id}/clonar', [TarifaZonaController::class, 'clonarPaquete']);
            Route::post('paquetes-tarifarios/{id}/activar', [TarifaZonaController::class, 'activarPaquete']);
            Route::get('paquetes-tarifarios/{id}/matriz', [TarifaZonaController::class, 'getMatriz']);
            Route::put('paquetes-tarifarios/{id}/matriz', [TarifaZonaController::class, 'updateMatriz']);

            Route::get('tarifas', [TarifaZonaController::class, 'indexCategorias']);
            Route::put('tarifas/{id}', [TarifaZonaController::class, 'updateCategoria']);
            Route::get('zonas', [TarifaZonaController::class, 'indexZonas']);
            Route::post('zonas', [TarifaZonaController::class, 'storeZona']);
            Route::put('zonas/{id}', [TarifaZonaController::class, 'updateZona']);
            Route::get('calles', [TarifaZonaController::class, 'indexCalles']);
            Route::post('calles', [TarifaZonaController::class, 'storeCalle']);
            Route::put('calles/{id}', [TarifaZonaController::class, 'updateCalle']);
        });

        // Reportes Comerciales y Cuadre de Caja
        Route::group(['middleware' => ['check.permission:comercial.reportes.ver']], function () {
            Route::get('reportes/recaudacion-diaria', [ReporteComercialController::class, 'recaudacionDiaria']);
            Route::get('reportes/recaudacion-consolidada', [ReporteComercialController::class, 'recaudacionConsolidada']);
            Route::get('reportes/recaudacion-consolidada/pdf', [ReporteComercialController::class, 'descargarPdfConsolidado']);
            Route::get('reportes/recaudacion-consolidada/csv', [ReporteComercialController::class, 'exportarCsvConsolidado']);
            Route::get('reportes/morosidad', [ReporteComercialController::class, 'morosidad']);
            Route::get('reportes/balance-consumo', [ReporteComercialController::class, 'balanceConsumo']);

            Route::get('reportes/planilla-lecturas/pdf', [ReporteComercialController::class, 'descargarPlanillaLecturasPdf']);
            Route::get('reportes/planilla-lecturas/excel', [ReporteComercialController::class, 'exportarPlanillaLecturasExcel']);
            Route::get('reportes/resumen-operaciones-zonas', [ReporteComercialController::class, 'resumenOperacionesZonas']);
            Route::get('reportes/resumen-operaciones-zonas/pdf', [ReporteComercialController::class, 'descargarResumenOperacionesZonasPdf']);
            Route::get('reportes/resumen-operaciones-zonas/excel', [ReporteComercialController::class, 'exportarResumenOperacionesZonasExcel']);
            Route::get('reportes/nomina-cortes', [ReporteComercialController::class, 'nominaCortes']);
            Route::get('reportes/nomina-cortes/pdf', [ReporteComercialController::class, 'descargarNominaCortesPdf']);
            Route::get('reportes/nomina-cortes/excel', [ReporteComercialController::class, 'exportarNominaCortesExcel']);
        });

        // Aportes e Instalaciones
        Route::group(['middleware' => ['check.permission:comercial.aportes.administrar']], function () {
            Route::get('aportes', [AporteConexionController::class, 'index']);
            Route::post('aportes', [AporteConexionController::class, 'store']);
            Route::get('aportes/{id}/contrato-pdf', [AporteConexionController::class, 'contratoPdf']);
            Route::get('aportes/{id}/factura-pdf', [AporteConexionController::class, 'facturaPdf']);
            Route::get('abonados/{id}/aportes', [AporteConexionController::class, 'porAbonado']);
        });
    });

    // ==========================================
    // MÓDULO DE CONTABILIDAD GUBERNAMENTAL E INTEGRADA (LEY 1178 SAFCO)
    // ==========================================
    Route::group(['prefix' => 'contabilidad'], function () {
        // Plan de Cuentas y Parámetros
        Route::group(['middleware' => ['check.permission:contabilidad.plan_cuentas.ver|contabilidad.plan_cuentas.administrar']], function () {
            Route::get('plan-cuentas', [ContabilidadController::class, 'planCuentas']);
            Route::get('plan-cuentas/imputables', [ContabilidadController::class, 'cuentasImputables']);
            Route::post('plan-cuentas', [ContabilidadController::class, 'guardarCuenta']);
            Route::get('centros-costo', [ContabilidadController::class, 'centrosCosto']);
            Route::get('gestiones', [ContabilidadController::class, 'gestiones']);
            Route::get('mapeos', [ContabilidadController::class, 'mapeos']);
        });

        // Comprobantes Contables
        Route::group(['middleware' => ['check.permission:contabilidad.comprobantes.ver|contabilidad.comprobantes.crear|contabilidad.comprobantes.aprobar']], function () {
            Route::get('comprobantes', [ContabilidadController::class, 'comprobantes']);
            Route::post('comprobantes', [ContabilidadController::class, 'guardarComprobante']);
            Route::get('comprobantes/{id}', [ContabilidadController::class, 'showComprobante']);
            Route::get('comprobantes/{id}/pdf', [ContabilidadController::class, 'descargarComprobantePdf']);
            Route::put('comprobantes/{id}/anular', [ContabilidadController::class, 'anularComprobante']);
        });

        // Interfaces Automáticas
        Route::group(['middleware' => ['check.permission:contabilidad.interfases.procesar']], function () {
            Route::get('interfases/pendientes', [ContabilidadController::class, 'pendientesInterfases']);
            Route::post('interfases/contabilizar-caja', [ContabilidadController::class, 'contabilizarCaja']);
            Route::post('interfases/contabilizar-lecturas', [ContabilidadController::class, 'contabilizarLecturasAgua']);
            Route::post('interfases/contabilizar-planilla-sueldos', [ContabilidadController::class, 'contabilizarPlanillaRrhh']);
        });

        // Libros y Estados Financieros
        Route::group(['middleware' => ['check.permission:contabilidad.libros.ver|contabilidad.estados_financieros.ver']], function () {
            Route::get('reportes/libro-diario', [ContabilidadController::class, 'libroDiario']);
            Route::get('reportes/libro-mayor', [ContabilidadController::class, 'libroMayor']);
            Route::get('reportes/balance-comprobacion', [ContabilidadController::class, 'balanceComprobacion']);
            Route::get('reportes/balance-general', [ContabilidadController::class, 'balanceGeneral']);
            Route::get('reportes/estado-resultados', [ContabilidadController::class, 'estadoResultados']);
        });
    });
});

// Rutas Públicas (Sin Autenticación Requerida - Protegidas contra Scraping y Abuso con Throttling)
Route::group(['middleware' => ['throttle:60,1']], function () {
    Route::get('correspondencia/publico/verificar-documento/{codigo}', [VerificacionPublicaController::class, 'verificarDocumento'])->where('codigo', '.*');
    Route::get('correspondencia/publico/verificar-hoja-ruta', [VerificacionPublicaController::class, 'verificarHojaRuta']);
    Route::get('correspondencia/publico/seguimiento/{codigo}', [SeguimientoController::class, 'publico'])->where('codigo', '.*');
    Route::post('correspondencia/publico/solicitud-ciudadana', [SolicitudCiudadanaController::class, 'registrarPublico']);

    // Facturas Públicas (Descarga oficial por QR o Correo)
    Route::get('facturacion/publico/facturas/{id}/pdf', [FacturaController::class, 'descargarPdf']);
    Route::get('facturacion/publico/facturas/{id}/preview', [FacturaController::class, 'previsualizarHtml']);
    Route::get('facturacion/publico/facturas/{id}/xml', [FacturaController::class, 'descargarXml']);
    Route::post('facturacion/publico/cobros-qr/webhook', [FacturacionCucuGatewayController::class, 'webhookBancoQr']);
});
