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
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Rrhh\SolicitudSalidaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema Base Independiente
|--------------------------------------------------------------------------
*/

// Rutas Públicas (Protegidas con Rate Limiting estricto anti-fuerza bruta)
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Rutas Autenticadas (JWT)
Route::group(['middleware' => ['jwt.auth']], function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('update_user_password', [RolUserController::class, 'update_user_password']);

    // Dashboard Operativo y Métricas Globales
    Route::get('dashboard/metricas', [DashboardController::class, 'metricas']);

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
    Route::get('rrhh/personal', [PersonalController::class, 'index']);
    Route::post('rrhh/personal', [PersonalController::class, 'store']);
    Route::get('rrhh/personal/mi-ficha', [PersonalController::class, 'miFichaPersonal']);
    Route::get('rrhh/personal/{id}', [PersonalController::class, 'show']);
    Route::post('rrhh/personal/{id}/estudios', [PersonalController::class, 'storeEstudio']);
    Route::post('rrhh/personal/{id}/experiencia', [PersonalController::class, 'storeExperiencia']);
    Route::post('rrhh/personal/{id}/cas', [PersonalController::class, 'storeCas']);

    Route::get('rrhh/organigrama', [EstructuraOrganizacionalController::class, 'organigrama']);
    Route::post('rrhh/unidades-organizacionales', [EstructuraOrganizacionalController::class, 'storeUnidad']);
    Route::post('rrhh/puestos', [EstructuraOrganizacionalController::class, 'storePuesto']);
    Route::post('rrhh/asignar-puesto', [EstructuraOrganizacionalController::class, 'asignarPuesto']);
    Route::get('rrhh/escalas-salariales', [EstructuraOrganizacionalController::class, 'listarEscalasSalariales']);
    Route::post('rrhh/escalas-salariales', [EstructuraOrganizacionalController::class, 'storeEscalaSalarial']);
    Route::get('rrhh/regionales', [EstructuraOrganizacionalController::class, 'listarRegionales']);
    Route::post('rrhh/regionales', [EstructuraOrganizacionalController::class, 'storeRegional']);
    Route::get('rrhh/gestiones', [EstructuraOrganizacionalController::class, 'listarGestiones']);
    Route::post('rrhh/gestiones', [EstructuraOrganizacionalController::class, 'storeGestion']);

    Route::get('rrhh/biometricos', [AsistenciaController::class, 'listarBiometricos']);
    Route::post('rrhh/biometricos/{id}/probar-conexion', [AsistenciaController::class, 'probarConexion']);
    Route::post('rrhh/biometricos/{id}/sincronizar', [AsistenciaController::class, 'sincronizar']);
    Route::get('rrhh/asistencias', [AsistenciaController::class, 'listarAsistencias']);
    Route::post('rrhh/asistencias/calcular', [AsistenciaController::class, 'calcularAsistencia']);

    Route::get('rrhh/permisos/catalogo', [SolicitudSalidaController::class, 'catalogoPermisos']);
    Route::get('rrhh/solicitudes', [SolicitudSalidaController::class, 'index']);
    Route::post('rrhh/solicitudes', [SolicitudSalidaController::class, 'store']);

    // Horarios y Turnos
    Route::get('rrhh/horarios', [HorarioController::class, 'index']);
    Route::post('rrhh/horarios', [HorarioController::class, 'store']);
    Route::get('rrhh/asignaciones-horarios', [HorarioController::class, 'listarAsignaciones']);
    Route::post('rrhh/asignaciones-horarios', [HorarioController::class, 'asignarHorario']);

    // Comisiones, Omisiones y Aprobaciones
    Route::get('rrhh/comisiones', [ComisionesOmisionesController::class, 'listarComisiones']);
    Route::post('rrhh/comisiones', [ComisionesOmisionesController::class, 'storeComision']);
    Route::get('rrhh/omisiones', [ComisionesOmisionesController::class, 'listarOmisiones']);
    Route::post('rrhh/omisiones', [ComisionesOmisionesController::class, 'storeOmision']);
    Route::get('rrhh/bandeja-aprobaciones', [ComisionesOmisionesController::class, 'bandejaAprobaciones']);
    Route::put('rrhh/bandeja-aprobaciones/{id}/resolver', [ComisionesOmisionesController::class, 'resolverSolicitud']);

    // Feriados y Fechas de Corte
    Route::get('rrhh/feriados', [FeriadoCorteController::class, 'listarFeriados']);
    Route::post('rrhh/feriados', [FeriadoCorteController::class, 'storeFeriado']);
    Route::get('rrhh/fechas-corte', [FeriadoCorteController::class, 'listarFechasCorte']);
    Route::post('rrhh/fechas-corte', [FeriadoCorteController::class, 'storeFechaCorte']);

    // Reportes Oficiales y Planillas
    Route::get('rrhh/reportes/boleta-salida/{id}/html', [ReporteRrhhController::class, 'boletaSalidaHtml']);
    Route::get('rrhh/reportes/asistencia-mensual', [ReporteRrhhController::class, 'asistenciaMensual']);
    Route::get('rrhh/reportes/refrigerio-mensual', [ReporteRrhhController::class, 'refrigerioMensual']);
    Route::get('rrhh/reportes/saldo-vacaciones', [ReporteRrhhController::class, 'saldoVacaciones']);
    Route::get('rrhh/reportes/planilla-sueldos', [ReporteRrhhController::class, 'planillaSueldosMensual']);
    Route::post('rrhh/reportes/cerrar-declarar-planilla', [ReporteRrhhController::class, 'cerrarYDeclararPlanilla']);
    Route::get('rrhh/reportes/boleta-pago/{personaId}/html', [ReporteRrhhController::class, 'boletaPagoHtml']);
    Route::get('rrhh/reportes/padron-personal', [ReporteRrhhController::class, 'padronPersonal']);
    Route::get('rrhh/reportes/kardex-funcionario/{personaId}/html', [ReporteRrhhController::class, 'kardexFuncionarioHtml']);
    Route::post('rrhh/reportes/generar-personalizado', [ReporteRrhhController::class, 'generarReportePersonalizado']);
    Route::get('rrhh/reportes/certificado-trabajo/{personaId}/html', [ReporteRrhhController::class, 'certificadoTrabajoHtml']);

    // Rutas para Obtener Estructura de Menú (Según Usuario/Rol Autenticado)
    Route::get('menu_usuario', function () {
        $user = auth()->user();
        $userId = $user ? $user->id : (request()->user('api') ? request()->user('api')->id : 1);
        return app(UsuarioController::class)->menuUsuario($userId);
    });
    Route::get('usuario/menu-acopio', function () {
        $user = auth()->user();
        $userId = $user ? $user->id : (request()->user('api') ? request()->user('api')->id : 1);
        return app(UsuarioController::class)->menuUsuario($userId);
    });
    Route::get('usuario/menu-usuario', function () {
        $user = auth()->user();
        $userId = $user ? $user->id : (request()->user('api') ? request()->user('api')->id : 1);
        return app(UsuarioController::class)->menuUsuario($userId);
    });
    Route::get('usuario/menu-rol/{rolId}', [UsuarioController::class, 'menuRol']);
    Route::get('usuario/menu-usuario/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-navegacion/{usuarioId}', [UsuarioController::class, 'menuUsuario']);
    Route::get('usuario/menu-acopio/{usuarioId}', [UsuarioController::class, 'menuUsuario']); // Compatibilidad

    // Paramétricas y Datos Maestros
    Route::apiResource('parametrica-api', ParametricaController::class);
    Route::post('registrar_campo', [ParametricaController::class, 'registrar_campo']);

    // Configuración Institucional y Parámetros SIAT (Módulo Datos)
    Route::get('datos/empresa', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'obtener']);
    Route::post('datos/empresa', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'guardar']);
    Route::post('datos/empresa/probar-conexion', [\App\Http\Controllers\Datos\ConfiguracionEmpresaController::class, 'probarConexion']);

    // ==========================================
    // MÓDULO DE CORRESPONDENCIA Y HOJAS DE RUTA (LONDRA)
    // ==========================================
    // 1. Hojas de Ruta
    Route::get('correspondencia/hojas-ruta/bandeja', [HojaRutaController::class, 'bandeja']);
    Route::get('correspondencia/hojas-ruta', [HojaRutaController::class, 'index']);
    Route::post('correspondencia/hojas-ruta', [HojaRutaController::class, 'store']);
    Route::post('correspondencia/hojas-ruta/agrupar', [HojaRutaController::class, 'agrupar']);
    Route::get('correspondencia/hojas-ruta/{id}/acciones-permitidas', [HojaRutaController::class, 'accionesPermitidas']);
    Route::get('correspondencia/hojas-ruta/{id}', [HojaRutaController::class, 'show']);
    Route::get('correspondencia/hojas-ruta/{id}/caratula', [HojaRutaController::class, 'caratula']);
    Route::post('correspondencia/hojas-ruta/{id}/cerrar', [HojaRutaController::class, 'cerrar']);
    Route::post('correspondencia/hojas-ruta/{id}/reabrir', [HojaRutaController::class, 'reabrir']);
    Route::post('correspondencia/hojas-ruta/{id}/desagrupar', [HojaRutaController::class, 'desagrupar']);

    // 2. Derivaciones y Workflow
    Route::post('correspondencia/derivaciones', [DerivacionController::class, 'derivar']);
    Route::post('correspondencia/derivaciones/{id}/recibir', [DerivacionController::class, 'recibir']);
    Route::post('correspondencia/derivaciones/{id}/devolver', [DerivacionController::class, 'devolver']);
    Route::post('correspondencia/derivaciones/{id}/anular', [DerivacionController::class, 'anular']);

    // 3. Documentos Oficiales
    Route::get('correspondencia/documentos/bandeja', [DocumentoController::class, 'bandeja']);
    Route::get('correspondencia/documentos', [DocumentoController::class, 'index']);
    Route::post('correspondencia/documentos', [DocumentoController::class, 'store']);
    Route::get('correspondencia/documentos/{id}', [DocumentoController::class, 'show']);
    Route::put('correspondencia/documentos/{id}', [DocumentoController::class, 'update']);
    Route::delete('correspondencia/documentos/{id}', [DocumentoController::class, 'destroy']);
    Route::post('correspondencia/documentos/{id}/anular', [DocumentoController::class, 'anular']);
    Route::post('correspondencia/documentos/{id}/enviar-revision', [DocumentoController::class, 'enviarRevision']);
    Route::get('correspondencia/documentos/{id}/preview', [DocumentoController::class, 'previewHtml']);
    Route::post('correspondencia/documentos/{id}/adjuntos', [DocumentoController::class, 'adjuntarArchivo']);

    // 4. Firmas y Aprobaciones
    Route::get('correspondencia/firmas/pendientes', [FirmaAprobacionController::class, 'pendientes']);
    Route::post('correspondencia/firmas/firmar', [FirmaAprobacionController::class, 'firmar']);
    Route::post('correspondencia/firmas/rechazar', [FirmaAprobacionController::class, 'rechazar']);

    // 5. Seguimiento y Trazabilidad
    Route::get('correspondencia/seguimiento/{id}/timeline', [SeguimientoController::class, 'timeline'])->where('id', '.*');

    // 6. Ventanilla Única
    Route::get('correspondencia/ventanillas', [VentanillaController::class, 'index']);
    Route::post('correspondencia/ventanillas/entrada', [VentanillaController::class, 'registrarEntrada']);

    // 7. Configuración y Diseñador de Plantillas
    Route::get('correspondencia/configuracion/plantillas', [ConfiguracionCorrespondenciaController::class, 'plantillas']);
    Route::get('correspondencia/configuracion/plantillas/{id}', [ConfiguracionCorrespondenciaController::class, 'showPlantilla']);
    Route::post('correspondencia/configuracion/plantillas', [ConfiguracionCorrespondenciaController::class, 'storePlantilla']);
    Route::get('correspondencia/configuracion/correlativos', [ConfiguracionCorrespondenciaController::class, 'correlativos']);
    Route::get('correspondencia/configuracion/proveidos', [ConfiguracionCorrespondenciaController::class, 'proveidos']);
    Route::get('correspondencia/configuracion/secretarios', [ConfiguracionCorrespondenciaController::class, 'secretarios']);
    Route::post('correspondencia/configuracion/secretarios', [ConfiguracionCorrespondenciaController::class, 'storeSecretario']);

    // 8. Despacho y Bandeja de Salida Externa (Gestor de Salida)
    Route::get('correspondencia/despachos', [DespachoSalidaController::class, 'index']);
    Route::post('correspondencia/despachos', [DespachoSalidaController::class, 'store']);
    Route::post('correspondencia/despachos/{id}/entregar', [DespachoSalidaController::class, 'entregar']);

    // 9. Etiquetas y Carpetas Virtuales
    Route::get('correspondencia/etiquetas', [EtiquetaController::class, 'index']);
    Route::post('correspondencia/etiquetas', [EtiquetaController::class, 'store']);
    Route::post('correspondencia/etiquetas/asignar', [EtiquetaController::class, 'asignar']);
    Route::post('correspondencia/etiquetas/desasignar', [EtiquetaController::class, 'desasignar']);

    // 10. Accesos Compartidos
    Route::get('correspondencia/compartidos', [AccesoCompartidoController::class, 'index']);
    Route::post('correspondencia/compartidos/compartir', [AccesoCompartidoController::class, 'compartir']);

    // 11. Solicitudes Ciudadanas y Trámites Digitales
    Route::get('correspondencia/solicitudes-ciudadanas', [SolicitudCiudadanaController::class, 'index']);
    Route::post('correspondencia/solicitudes-ciudadanas/{id}/convertir-hoja-ruta', [SolicitudCiudadanaController::class, 'convertirEnHojaRuta']);

    // 12. Dashboard de Correspondencia y KPIs
    Route::get('correspondencia/dashboard/cards', [DashboardCorrespondenciaController::class, 'cards']);
    Route::get('correspondencia/dashboard/charts', [DashboardCorrespondenciaController::class, 'charts']);

    // 13. Matriz de Permisos de Derivación (Copia Fiel Londres/SIM-EMAPA)
    Route::get('correspondencia/permisos/derivaciones', [PermisosCorrespondenciaController::class, 'derivaciones']);
    Route::post('correspondencia/permisos/derivaciones', [PermisosCorrespondenciaController::class, 'storePermisoDerivacion']);
    Route::get('correspondencia/permisos/arbol-jerarquico', [PermisosCorrespondenciaController::class, 'arbolJerarquico']);
    Route::get('correspondencia/permisos/lista-grupos/{tipo}', [PermisosCorrespondenciaController::class, 'listaGrupos']);
    Route::get('correspondencia/permisos/destinos-asignados', [PermisosCorrespondenciaController::class, 'destinosAsignados']);
    Route::post('correspondencia/permisos/guardar-lote', [PermisosCorrespondenciaController::class, 'guardarLote']);
    Route::post('correspondencia/permisos/restablecer', [PermisosCorrespondenciaController::class, 'restablecer']);
    Route::get('correspondencia/permisos/resumen', [PermisosCorrespondenciaController::class, 'resumen']);

    // 14. Transferencias Masivas de Bandejas
    Route::post('correspondencia/transferencias', [TransferenciaController::class, 'transferir']);

    // 15. Revisiones y Versiones de Documentos
    Route::get('correspondencia/documentos/{id}/revisiones', [RevisionDocumentoController::class, 'index']);
    Route::post('correspondencia/documentos/{id}/revisiones', [RevisionDocumentoController::class, 'store']);

    // ==========================================
    // MÓDULO DE FACTURACIÓN ELECTRÓNICA EN LÍNEA (SIAT - SIN)
    // ==========================================
    Route::get('facturacion/facturas', [FacturaController::class, 'index']);
    Route::post('facturacion/facturas', [FacturaController::class, 'store']);
    Route::post('facturacion/facturas/emision-masiva', [FacturaController::class, 'emisionMasiva']);
    Route::post('facturacion/facturas/{id}/anular', [FacturaController::class, 'anular']);
    Route::post('facturacion/facturas/{id}/enviar-correo', [FacturaController::class, 'enviarPorCorreo']);
    Route::get('facturacion/facturas/{id}/verificar-estado-sin', [FacturaController::class, 'verificarEstadoSin']);
    Route::get('facturacion/facturas/{id}/pdf', [FacturaController::class, 'descargarPdf']);
    Route::get('facturacion/facturas/{id}/preview', [FacturaController::class, 'previsualizarHtml']);
    Route::get('facturacion/facturas/{id}/xml', [FacturaController::class, 'descargarXml']);

    Route::get('facturacion/clientes', [ClienteFacturaController::class, 'index']);
    Route::post('facturacion/clientes', [ClienteFacturaController::class, 'store']);
    Route::get('facturacion/clientes/{id}', [ClienteFacturaController::class, 'show']);
    Route::delete('facturacion/clientes/{id}', [ClienteFacturaController::class, 'destroy']);

    Route::get('facturacion/siat/estado-conexion', [SiatCodigoController::class, 'estadoConexion']);
    Route::get('facturacion/siat/sincronizar-hora', [SiatCodigoController::class, 'sincronizarHora']);
    Route::get('facturacion/siat/verificar-nit/{nit}', [SiatCodigoController::class, 'verificarNit']);
    Route::get('facturacion/siat/catalogos', [SiatCodigoController::class, 'catalogos']);
    Route::get('facturacion/siat/sucursales', [SiatCodigoController::class, 'sucursales']);
    Route::get('facturacion/siat/productos', [SiatCodigoController::class, 'productos']);
    Route::post('facturacion/siat/productos', [SiatCodigoController::class, 'guardarProducto']);
    Route::post('facturacion/siat/puntos-venta', [SiatCodigoController::class, 'registrarPuntoVenta']);
    Route::post('facturacion/siat/puntos-venta/{id}/cierre', [SiatCodigoController::class, 'cerrarPuntoVenta']);
    Route::post('facturacion/siat/puntos-venta/{id}/cuis', [SiatCodigoController::class, 'solicitarCuisPuntoVenta']);

    Route::get('facturacion/eventos-significativos', [EventoSignificativoController::class, 'index']);
    Route::post('facturacion/eventos-significativos', [EventoSignificativoController::class, 'store']);
    Route::post('facturacion/eventos-significativos/{id}/cerrar', [EventoSignificativoController::class, 'cerrar']);
    Route::post('facturacion/eventos-significativos/paquetes/{id}/validar', [EventoSignificativoController::class, 'validarPaquete']);

    Route::get('facturacion/reportes/libro-ventas', [ReporteFacturacionController::class, 'libroVentas']);
    Route::get('facturacion/reportes/libro-ventas/csv', [ReporteFacturacionController::class, 'exportarCsvLibroVentas']);
    Route::get('facturacion/reportes/ventas-mensuales', [ReporteFacturacionController::class, 'ventasMensuales']);

    // ==========================================
    // MÓDULO COMERCIAL Y OPERATIVO DE AGUA POTABLE
    // ==========================================
    // 1. Abonados
    Route::get('comercial/abonados', [AbonadoController::class, 'index']);
    Route::post('comercial/abonados', [AbonadoController::class, 'store']);
    Route::get('comercial/abonados/{id}', [AbonadoController::class, 'show']);
    Route::put('comercial/abonados/{id}', [AbonadoController::class, 'update']);
    Route::post('comercial/abonados/{id}/cambiar-medidor', [AbonadoController::class, 'cambiarMedidor']);
    Route::post('comercial/abonados/{id}/dar-baja', [AbonadoController::class, 'darDeBaja']);
    Route::get('comercial/abonados/{id}/extracto/pdf', [AbonadoController::class, 'descargarExtractoPdf']);

    // 2. Ciclos y Lecturas
    Route::get('comercial/periodos', [LecturaController::class, 'indexPeriodos']);
    Route::post('comercial/periodos/abrir', [LecturaController::class, 'abrirPeriodo']);
    Route::get('comercial/periodos/{id}/planilla', [LecturaController::class, 'obtenerPlanilla']);
    Route::get('comercial/periodos/{id}/avisos-cobranza/pdf', [LecturaController::class, 'descargarAvisosLote']);
    Route::post('comercial/lecturas/{id}', [LecturaController::class, 'guardarLectura']);
    Route::post('comercial/lecturas/lote', [LecturaController::class, 'guardarLote']);
    Route::post('comercial/periodos/{id}/liquidar', [LecturaController::class, 'liquidarPeriodo']);

    // 3. Ventanilla de Cobranzas y Recaudación (con emisión de Factura SIAT)
    Route::get('comercial/caja/buscar-abonados', [CobranzaCajaController::class, 'buscarAbonados']);
    Route::get('comercial/caja/estado-cuenta/{codigo}', [CobranzaCajaController::class, 'estadoCuenta']);
    Route::post('comercial/caja/cobrar', [CobranzaCajaController::class, 'cobrar']);
    Route::post('comercial/caja/recibos', [CobranzaCajaController::class, 'emitirReciboCaja']);
    Route::get('comercial/caja/recibos/{id}/pdf', [CobranzaCajaController::class, 'descargarReciboCaja']);
    Route::get('comercial/caja/aviso-cobranza/{id}/pdf', [CobranzaCajaController::class, 'descargarAvisoCobranza']);

    // 3.1 Sesiones de Caja, Apertura, Cierre, Arqueo y Asignación
    Route::get('comercial/caja-sesiones/estado-actual', [CajaSesionController::class, 'estadoActual']);
    Route::get('comercial/caja-sesiones/cajas-disponibles', [CajaSesionController::class, 'cajasDisponibles']);
    Route::post('comercial/caja-sesiones/abrir', [CajaSesionController::class, 'abrir']);
    Route::get('comercial/caja-sesiones/resumen-arqueo', [CajaSesionController::class, 'resumenArqueo']);
    Route::post('comercial/caja-sesiones/cerrar', [CajaSesionController::class, 'cerrar']);
    Route::post('comercial/caja-sesiones/movimiento', [CajaSesionController::class, 'registrarMovimiento']);
    Route::get('comercial/caja-sesiones/{id}/reporte-pdf', [CajaSesionController::class, 'descargarReportePdf']);
    Route::get('comercial/caja-sesiones/historial', [CajaSesionController::class, 'historial']);
    Route::get('comercial/caja-sesiones/cajeros', [CajaSesionController::class, 'listarCajeros']);
    Route::post('comercial/cajas/{idPuntoVenta}/asignar-cajero', [CajaSesionController::class, 'asignarCajeroDefecto']);

    // 4. Convenios de Pago
    Route::get('comercial/convenios', [ConvenioController::class, 'index']);
    Route::post('comercial/convenios/simular', [ConvenioController::class, 'simular']);
    Route::post('comercial/convenios', [ConvenioController::class, 'store']);

    // 5. Cuadrillas Operativas: Cortes y Reconexiones
    Route::get('comercial/cortes/ordenes', [CorteReconexionController::class, 'indexOrdenes']);
    Route::get('comercial/cortes/ordenes/{id}/pdf', [CorteReconexionController::class, 'descargarOrdenTrabajo']);
    Route::get('comercial/cortes/candidatos', [CorteReconexionController::class, 'candidatosCorte']);
    Route::post('comercial/cortes/generar', [CorteReconexionController::class, 'generarCortes']);
    Route::post('comercial/cortes/{id}/ejecutar', [CorteReconexionController::class, 'ejecutarCorte']);
    Route::post('comercial/reconexiones/{id}/ejecutar', [CorteReconexionController::class, 'ejecutarReconexion']);

    // 6. Catastro: Tarifas, Zonas y Calles
    Route::get('comercial/tarifas', [TarifaZonaController::class, 'indexCategorias']);
    Route::put('comercial/tarifas/{id}', [TarifaZonaController::class, 'updateCategoria']);
    Route::get('comercial/zonas', [TarifaZonaController::class, 'indexZonas']);
    Route::get('comercial/calles', [TarifaZonaController::class, 'indexCalles']);
    Route::post('comercial/calles', [TarifaZonaController::class, 'storeCalle']);

    // 7. Reportes Comerciales y Cuadre de Caja
    Route::get('comercial/reportes/recaudacion-diaria', [ReporteComercialController::class, 'recaudacionDiaria']);
    Route::get('comercial/reportes/recaudacion-consolidada', [ReporteComercialController::class, 'recaudacionConsolidada']);
    Route::get('comercial/reportes/recaudacion-consolidada/pdf', [ReporteComercialController::class, 'descargarPdfConsolidado']);
    Route::get('comercial/reportes/recaudacion-consolidada/csv', [ReporteComercialController::class, 'exportarCsvConsolidado']);
    Route::get('comercial/reportes/morosidad', [ReporteComercialController::class, 'morosidad']);
    Route::get('comercial/reportes/balance-consumo', [ReporteComercialController::class, 'balanceConsumo']);

    // ==========================================
    // MÓDULO DE CONTABILIDAD GUBERNAMENTAL E INTEGRADA (LEY 1178 SAFCO)
    // ==========================================
    // 1. Plan de Cuentas
    Route::get('contabilidad/plan-cuentas', [ContabilidadController::class, 'planCuentas']);
    Route::get('contabilidad/plan-cuentas/imputables', [ContabilidadController::class, 'cuentasImputables']);
    Route::post('contabilidad/plan-cuentas', [ContabilidadController::class, 'guardarCuenta']);

    // 2. Comprobantes Contables (CI, CE, CD)
    Route::get('contabilidad/comprobantes', [ContabilidadController::class, 'comprobantes']);
    Route::post('contabilidad/comprobantes', [ContabilidadController::class, 'guardarComprobante']);
    Route::get('contabilidad/comprobantes/{id}', [ContabilidadController::class, 'showComprobante']);
    Route::get('contabilidad/comprobantes/{id}/pdf', [ContabilidadController::class, 'descargarComprobantePdf']);
    Route::put('contabilidad/comprobantes/{id}/anular', [ContabilidadController::class, 'anularComprobante']);

    // 3. Consola de Interfases Automáticas (Comercial, Cajas, RRHH)
    Route::get('contabilidad/interfases/pendientes', [ContabilidadController::class, 'pendientesInterfases']);
    Route::post('contabilidad/interfases/contabilizar-caja', [ContabilidadController::class, 'contabilizarCaja']);
    Route::post('contabilidad/interfases/contabilizar-lecturas', [ContabilidadController::class, 'contabilizarLecturasAgua']);
    Route::post('contabilidad/interfases/contabilizar-planilla-sueldos', [ContabilidadController::class, 'contabilizarPlanillaRrhh']);

    // 4. Libros y Estados Financieros
    Route::get('contabilidad/reportes/libro-diario', [ContabilidadController::class, 'libroDiario']);
    Route::get('contabilidad/reportes/libro-mayor', [ContabilidadController::class, 'libroMayor']);
    Route::get('contabilidad/reportes/balance-comprobacion', [ContabilidadController::class, 'balanceComprobacion']);
    Route::get('contabilidad/reportes/balance-general', [ContabilidadController::class, 'balanceGeneral']);
    Route::get('contabilidad/reportes/estado-resultados', [ContabilidadController::class, 'estadoResultados']);

    // 5. Parámetros
    Route::get('contabilidad/centros-costo', [ContabilidadController::class, 'centrosCosto']);
    Route::get('contabilidad/gestiones', [ContabilidadController::class, 'gestiones']);
    Route::get('contabilidad/mapeos', [ContabilidadController::class, 'mapeos']);
});

// Rutas Públicas (Sin Autenticación Requerida)
Route::get('correspondencia/publico/verificar-documento/{codigo}', [VerificacionPublicaController::class, 'verificarDocumento'])->where('codigo', '.*');
Route::get('correspondencia/publico/verificar-hoja-ruta', [VerificacionPublicaController::class, 'verificarHojaRuta']);
Route::get('correspondencia/publico/seguimiento/{codigo}', [SeguimientoController::class, 'publico'])->where('codigo', '.*');
Route::post('correspondencia/publico/solicitud-ciudadana', [SolicitudCiudadanaController::class, 'registrarPublico']);

// Facturas Públicas (Descarga directa por QR o Correo)
Route::get('facturacion/publico/facturas/{id}/pdf', [FacturaController::class, 'descargarPdf']);
Route::get('facturacion/publico/facturas/{id}/preview', [FacturaController::class, 'previsualizarHtml']);
Route::get('facturacion/publico/facturas/{id}/xml', [FacturaController::class, 'descargarXml']);
