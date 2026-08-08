<?php
use App\Http\Controllers\Administracion\AccesoUsuarioController;
use App\Http\Controllers\Administracion\Parametricas\CampaniaController;
use App\Http\Controllers\Administracion\Parametricas\ContratoController;
use App\Http\Controllers\Administracion\Parametricas\LoteController;
use App\Http\Controllers\Administracion\Parametricas\ParametricaController;
use App\Http\Controllers\Administracion\Parametricas\ProgramaController;
use App\Http\Controllers\Administracion\UsuarioController;
use App\Http\Controllers\Inventario\ContratoController as InventarioContratoController;
use App\Http\Controllers\Inventario\IngresoController as InventarioIngresoController;
use App\Http\Controllers\Inventario\SolicitudController;
use App\Http\Controllers\Inventario\TransporteController;
use App\Http\Controllers\Inventario\UsuarioPuntoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRolController;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\ReporteExcelController;
use App\Http\Controllers\ReportesBase\ReportesController;
use App\Http\Controllers\Reporte\ReporteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolUserController;
use App\Http\Controllers\RRHH\BiometricController;
use App\Http\Controllers\RRHH\CityController;
use App\Http\Controllers\RRHH\ContractModalityController;
use App\Http\Controllers\RRHH\ContractTypeController;
use App\Http\Controllers\RRHH\ContributionController;
use App\Http\Controllers\RRHH\CountryController;
use App\Http\Controllers\RRHH\DocumentTypeController;
use App\Http\Controllers\RRHH\EmployeeController;
use App\Http\Controllers\RRHH\EmployeeRequestController;
use App\Http\Controllers\RRHH\HealthBoxController;
use App\Http\Controllers\RRHH\HolydayController;
use App\Http\Controllers\RRHH\KinshipController;
use App\Http\Controllers\RRHH\LocationController;
use App\Http\Controllers\RRHH\ManagementController;
use App\Http\Controllers\RRHH\PositionController;
use App\Http\Controllers\RRHH\ReportController;
use App\Http\Controllers\RRHH\ReportExcelController;
use App\Http\Controllers\RRHH\ReportsMixController;
use App\Http\Controllers\RRHH\RequestTypeController;
use App\Http\Controllers\RRHH\TypeHourController;
use App\Http\Controllers\RRHH\UnitController;
use App\Http\Controllers\sig\AgricultorController;
use App\Http\Controllers\SiloController;
use App\Http\Controllers\Subsidio\SedemController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('login_aplicativo_movil', [App\Http\Controllers\AuthController::class, 'login_aplicativo_movil']);
Route::get('hora_servidor', [SedemController::class, 'hora_servidor']);

Route::group([
	'prefix'     => 'auth',
	'middleware' => 'jwt.auth',
], function ($router) {

});
/*******************************TODO REPORTE PARA RECURSOS HUMANOS**********************************************************************/
Route::get('ficha_personal/{id}', [ReportController::class, 'ficha_personal']);
Route::get('employee_request_print/{id}', [ReportController::class, 'boleta']);
Route::get('attendance_employee/{employee_id}/{from_date}/{to_date}', [ReportController::class, 'attendance_employee']);
Route::get('attendance_employee_date/{employee_id}/{from_date}/{to_date}', [ReportController::class, 'attendance_employee_date']);
Route::get('attendance_employee_complet/{employee_id}/{from_date}/{to_date}', [ReportController::class, 'attendance_employee_complet']);
Route::get('payroll/{management_id}/{from_date}/{to_date}', [ReportExcelController::class, 'payroll']);
Route::get('roe', [ReportExcelController::class, 'roe']);
Route::get('reporteExcelDescuentos/{id}', [ReportsMixController::class, 'reporteExcelDescuentos']);
Route::get('reporteExcelSalarios/{id}', [ReportsMixController::class, 'reporteExcelSalarios']);
Route::get('reporteExcelROE/{id}', [ReportsMixController::class, 'reporteExcelROE']);
Route::get('reporteExcelFuncionarioActivo/{id}', [ReportsMixController::class, 'reporteExcelFuncionarioActivo']);
Route::get('reporteExcelFuncionarioInActivo/{id}', [ReportsMixController::class, 'reporteExcelFuncionarioInActivo']);
Route::get('reporteExcelFuncionarioTodos/{id}', [ReportsMixController::class, 'reporteExcelFuncionarioTodos']);
Route::get('reporteExcelGeneralPorCargos', [ReportsMixController::class, 'reporteExcelGeneralPorCargos']);
Route::get('reporteExcelPersonalNuevo/{mes}/{anio}', [ReportsMixController::class, 'reporteExcelPersonalNuevo']);
Route::get('reporteExcelPersonalRetirado/{mes}/{anio}', [ReportsMixController::class, 'reporteExcelPersonalRetirado']);
Route::get('reporteExcelPersonalConVacaciones/{mes}/{anio}', [ReportsMixController::class, 'reporteExcelPersonalConVacaciones']);
Route::post('management', [ManagementController::class, 'reporteDPFs']);
Route::get('ReportMonth/{fecha}/{id_user}/{planta}', [ReportController::class, 'ReportMonth']);
Route::get('ReportYear/{fecha}/{id_user}/{planta}', [ReportController::class, 'ReportYear']);
Route::get('reportMonthExcel/{fecha}/{id_user}/{tipo_doc}/{planta}', [ReportController::class, 'reportMonthExcel']);
Route::post('updateFile', [EmployeeController::class, 'updateFile']);
/***************************************************************************************************************************************/

Route::group(array('middleware' => 'jwt.auth'), function () {
	Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout']);
	//administracion usuario
	Route::get('usuario', [UsuarioController::class, 'index']);
	Route::get('usuario/agregar-sistema/{id}', [UsuarioController::class, 'agregarSistema']);
	Route::get('usuario/quitar-sistema/{id}', [UsuarioController::class, 'quitarSistema']);
	Route::get('usuario/rol-user/{id}', [UsuarioController::class, 'rolUser']);
	Route::get('usuario/menu-rol/{rolId}', [UsuarioController::class, 'menuRol']);
	Route::get('usuario/menu-acopio/{usuarioId}', [UsuarioController::class, 'menuAcopio']);
	Route::get('usuario/asignar-puntoventa/{usarioId}/{puntoVentaId}', [UsuarioController::class, 'asignarPuntoVenta']);
	Route::apiResource('rol', RolController::class);
	Route::apiResource('menu', MenuController::class);
	Route::post('menu/change/cambiar-orden', [MenuController::class, 'cambiarOrden']);
	Route::apiResource('rol-user', RolUserController::class);
	Route::apiResource('menu-rol', MenuRolController::class);

	Route::post('update_user_password', [RolUserController::class, 'update_user_password']);

	//finalizacion de administracion
	//Abm parametricas
	Route::apiResource('parametrica-api', ParametricaController::class);
	Route::post('registrar_campo', [ParametricaController::class, 'registrar_campo']);
	Route::apiResource('programa', ProgramaController::class);
	Route::apiResource('campania', CampaniaController::class);
	#Plantas
	Route::apiResource('planta', PlantaController::class);
	Route::get('listar_planta_gat', [PlantaController::class, 'listar_planta_gat']);
	Route::get('listar_planta_gat_destino/{origen_planta_id}', [PlantaController::class, 'listar_planta_gat_destino']);
	Route::get('listar_tipo_plantas/{tipo_solicitud_id}', [PlantaController::class, 'listar_tipo_plantas']);
	Route::get('listar_planta/{tipo_planta_id}', [PlantaController::class, 'listar_planta']);
	Route::get('listar_tipo_plantas_gat', [PlantaController::class, 'listar_tipo_plantas_gat']);
	Route::get('verificar_planta_asignada', [PlantaController::class, 'verificar_planta_asignada']);
	Route::get('usuario_rol_planta/{planta_id}', [PlantaController::class, 'usuario_rol_planta']);
	Route::get('verificar_planta_asignada_rol', [PlantaController::class, 'verificar_planta_asignada_rol']);
	Route::get('verificar_planta_asignada_rol_tipo/{tipo_planta_id}', [PlantaController::class, 'verificar_planta_asignada_rol_tipo']);
	Route::get('listar_tipo_plantas_gc', [PlantaController::class, 'listar_tipo_plantas_gc']);

	//PUNTO = (SILOS, ALAMACENES. GALPONES)
	Route::get('listar_departamento', [SiloController::class, 'listar_departamento']);
	Route::get('listar_provincia/{id}', [SiloController::class, 'listar_provincia']);
	Route::get('listar_municipio/{id}', [SiloController::class, 'listar_municipio']);
	Route::get('listar_localidad/{id}', [SiloController::class, 'listar_localidad']);
	Route::post('registro_punto', [SiloController::class, 'registro_punto']);
	Route::get('obtener_punto/{id}', [SiloController::class, 'obtener_punto']);

	//LOTES
	Route::post('obtener_producto', [LoteController::class, 'obtener_producto']);
	Route::apiResource('lote', LoteController::class);
	Route::get('listar_lote_producto/{id_articulo}/{id_punto}', [LoteController::class, 'listar_lote_producto']); //nuevo
	Route::post('obtener_lote', [LoteController::class, 'obtener_lote']);
	Route::post('obtener_lote_alimento_balanceado', [LoteController::class, 'obtener_lote_alimento_balanceado']);
	Route::get('obtener_detalle_lote/{id_articulo}', [LoteController::class, 'obtener_detalle_lote']);
	Route::get('buscar_lote/{id_articulo}', [LoteController::class, 'buscar_lote']);
	Route::post('obtener_lote_sub_producto', [LoteController::class, 'obtener_lote_sub_producto']);
	
	//ALMACEN DE COMERCIALIZACION
	Route::get('obtener_almacen_comercializacion', [SiloController::class, 'obtener_almacen_comercializacion']);

	//ARTICULO
	Route::get('linea_producto_aba', [ArticulosController::class, 'linea_producto_aba']);
	Route::get('linea_producto_materia_prima', [ArticulosController::class, 'linea_producto_materia_prima']);
	Route::get('sub_linea_producto_materia_prima/{linea_id}', [ArticulosController::class, 'sub_linea_producto_materia_prima']);
	Route::get('linea_producto_insumos', [ArticulosController::class, 'linea_producto_insumos']);
	Route::get('sub_linea_producto_insumos/{linea_id}', [ArticulosController::class, 'sub_linea_producto_insumos']);
	Route::get('sub_linea_producto_sub_producto/{linea_id}', [ArticulosController::class, 'sub_linea_producto_sub_producto']);

	//asignacion planta
	Route::apiResource('punto-venta', PuntoVentaController::class);
	Route::get('punto-venta/user/{userId}', [PuntoVentaController::class, 'puntoVentaUser']);
	Route::get('punto-venta/evento/pv', [PuntoVentaController::class, 'puntoVenta']);
	Route::get('planta_asignada', [PuntoVentaController::class, 'planta_asignada']);
	Route::get('crear_punto', [PuntoVentaController::class, 'crear_punto']);

	Route::post('listar_plantas', [SiloController::class, 'listar_plantas_capacidad']);
	Route::get('listar_cuentas/{id}', [OperacionesController::class, 'listar_cuenta']); /*norberto reportes*/
	Route::post('registrar_operacion', [OperacionesController::class, 'Registrar_operacion']); 
	Route::get('listar_operaciones/{id}', [OperacionesController::class, 'listar_operaciones']); 
	Route::get('listar_bancos', [OperacionesController::class, 'listar_bancos']);
	Route::post('registrar_cuentas', [OperacionesController::class, 'Registrar_cuentas']);	/*norberto reportes*/
	Route::get('listar_rau/{id}', [OperacionesController::class, 'listar_rau']); 
	Route::post('registrar_rau', [OperacionesController::class, 'registrar_rau']);	/*norberto reportes*/
	Route::get('listar_rauasigna/{id}/{idcamp}', [OperacionesController::class, 'listar_rau']); 
	Route::post('registrar_rauasigna', [OperacionesController::class, 'registrar_rauasigna']);	/*norberto reportes*/
	Route::post('listar_numeropago', [OperacionesController::class, 'listar_numeropago']); 
	Route::post('registrar_nropago_asignado', [OperacionesController::class, 'registrar_nropago_asignado']);	/*norberto reportes*/ 
	Route::post('registrar_pagodeuda_automatico', [OperacionesController::class, 'registrar_pagodeuda_automatico']);	/*norberto reportes*/
	Route::get('parametricas/{tabla}', [InsumoIngresoController::class, 'parametricas']);
	//CUADRE ACOPIO
	Route::get('listar_lote_movimientos_acopio', [IngresoController::class, 'listar_lote_movimientos_acopio']);
	Route::get('listar_movimientos_acopio/{lote}', [IngresoController::class, 'listar_movimientos_acopio']);
	Route::get('listar_boletas_acopio_pendientes_diario', [IngresoController::class, 'listar_boletas_acopio_pendientes_diario']);
	Route::post('registro_boletas_ingreso_inventario', [IngresoController::class, 'registro_boletas_ingreso_inventario']);
	Route::post('registro_boletas_salida', [IngresoController::class, 'registro_boletas_salida']);

	//SOLICITUD PAGO
	Route::post('registrar_solicitud_pago', [AcopioController::class, 'registrar_solicitud_pago']);
	Route::get('listar_tipo_documento/{tipo_solicitud_id}', [AcopioController::class, 'listar_tipo_documento']);
	Route::get('reporte_solicitud_pago/{id_solicitud}', [ReporteAcopioController::class, 'reporte_solicitud_pago']);
	Route::get('listar_solicitudes_acopio/{tipo_solicitud_id}', [AcopioController::class, 'listar_solicitudes_acopio']);

	//KARDEX ACOPIO
	Route::post('kardex_individual_articulo_punto', [ReporteAcopioController::class, 'kardex_individual_articulo_punto']);
	Route::get('kardex_valorado_articulo/{id}/{punto_id}', [ReporteAcopioController::class, 'kardex_valorado_articulo']);

	//CAPACIDAD ACOPIO
	Route::post('registro_capacidad_acopio', [AcopioController::class, 'registro_capacidad_acopio']);
	Route::get('obtener_punto_princicpal/{planta_id}', [AcopioController::class, 'obtener_punto_princicpal']);
	Route::get('listar_capacidad_acopio', [AcopioController::class, 'listar_capacidad_acopio']);
	//ACOPIO REPORTES
	Route::post('buscar_productor_acopio', [AcopioController::class, 'buscar_productor_acopio']);


	//INVENTARIO
	Route::get('listar_contratos', [InventarioContratoController::class, 'listar_contratos']);
	Route::post('registrar_conductor', [TransporteController::class, 'registrar_conductor']);
	Route::post('registrar_transporte', [TransporteController::class, 'registrar_transporte']);
	Route::get('buscar_conductor', [TransporteController::class, 'buscar_conductor']);
	Route::get('buscar_conductor_transporte/{documento}', [TransporteController::class, 'buscar_conductor_transporte']);
	Route::get('buscar_transporte', [TransporteController::class, 'buscar_transporte']);
	Route::get('listar_vehiculo_conductor/{conductor_id}', [TransporteController::class, 'listar_vehiculo_conductor']);
	Route::get('listar_conductor_transporte/{id}', [TransporteController::class, 'listar_conductor_transporte']);


	//KARDEX INVENTARIO
	Route::get('kardex_valorado_articulo_inventario_excel_lote/{id}/{punto_id}/{campania}/{lote}', [ReporteExcelController::class, 'kardex_valorado_articulo_inventario_excel']);
	Route::get('kardex_valorado_lote_articulo_inventario_excel/{id}/{punto_id}/{lote_id}', [ReporteExcelController::class, 'kardex_valorado_lote_articulo_inventario_excel']);
	Route::post('kardex_valorado_lote_articulo_inventario_excel_detallado', [ReporteExcelController::class, 'kardex_valorado_lote_articulo_inventario_excel_detallado']);

	//CONTRATO
	Route::get('listar_distribuidora_logistica', [ContratoController::class, 'listar_distribuidora_logistica']);
	Route::get('listar_unidad_medida_logistica', [ContratoController::class, 'listar_unidad_medida_logistica']);
    Route::get('listar_estibalaje', [ContratoController::class, 'listar_estibalaje']);
    Route::get('listar_contrato', [ContratoController::class, 'listar_contrato']);
    Route::get('listar_tipo_contrato', [ContratoController::class, 'listar_tipo_contrato']);
    Route::get('listar_tipo_contrato_servicio', [ContratoController::class, 'listar_tipo_contrato_servicio']);
    Route::get('listar_tipo_contrato_porservicios', [ContratoController::class, 'listar_tipo_contrato_porservicios']);
    Route::get('baja_contrato/{id}', [ContratoController::class, 'baja_contrato']);
    Route::get('listar_tipo_solicitudes_productos', [ContratoController::class, 'listar_tipo_solicitudes_productos']);
    Route::get('listar_productos_solicitud/{tipo}', [ContratoController::class, 'listar_productos_solicitud']);
    Route::post('editar_contrato/{id}', [ContratoController::class, 'editar_contrato']);
    Route::get('edicion_contrato/{numero_contrato}', [ContratoController::class, 'edicion_contrato']);

	//SOLICTUDES
	Route::apiResource('solicitud', SolicitudController::class); // solicitudes
	Route::get('listar_solicitudes_inventario/{tipo_solicitud_id}', [SolicitudController::class, 'listar_solicitudes_inventario']);
	Route::get('kardex_valorado_articulo_inventario_excel/{id}/{punto_id}', [ReporteExcelController::class, 'kardex_valorado_articulo_inventario_excel']);

	//ORDEN DESPACHO
	Route::get('listar_solicitud_orden_despacho/{id}', [SolicitudController::class, 'listar_solicitud_orden_despacho']);
	Route::get('listar_solicitud_despacho', [SolicitudController::class, 'listar_solicitud_despacho']);
	Route::post('obtener_detalle_items_despacho', [SolicitudController::class, 'obtener_detalle_items_despacho']);
	Route::post('movimiento_orden_despacho', [SolicitudController::class, 'movimiento_orden_despacho']);
	Route::post('adicionar_items', [SolicitudController::class, 'adicionar_items']);
	Route::post('verificar_stock_origen_planta', [SolicitudController::class, 'verificar_stock_origen_planta']);
	Route::post('verificar_cantidad_solicitada', [SolicitudController::class, 'verificar_cantidad_solicitada']);
	Route::post('actualizar_conductor_despacho/{solicitud_id}', [SolicitudController::class, 'actualizar_conductor_despacho']);
	Route::post('actualizar_vehiculo_despacho/{solicitud_id}', [SolicitudController::class, 'actualizar_vehiculo_despacho']);

	Route::get('obtener_detalle_logistica/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_logistica']);
	Route::get('obtener_detalle_logistica_ingreso/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_logistica_ingreso']);
	Route::get('obtener_detalle_solicitud_inventario/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_solicitud_inventario']);
	Route::get('obtener_detalle_solicitud_inventario_devolucion/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_solicitud_inventario_devolucion']);

	//ORDEN DE TRASLADO
	Route::get('listar_solicitud_orden_traslado/{id}', [SolicitudController::class, 'listar_solicitud_orden_traslado']);
	Route::get('listar_orden_traslado', [InventarioIngresoController::class, 'listar_orden_traslado']);
	Route::get('listar_movimiento_traslado', [SolicitudController::class, 'listar_movimiento_traslado']);
	Route::post('movimiento_orden_traslado_logistica', [SolicitudController::class, 'movimiento_orden_traslado_logistica']);
	Route::post('movimiento_orden_traslado', [SolicitudController::class, 'movimiento_orden_traslado']);
	Route::post('confirmar_solicitud_orden_traslado/{id}', [SolicitudController::class, 'confirmar_solicitud_orden_traslado']);
	//ORDEN DE CARGA
	Route::get('listar_solicitud_orden_carga/{id}', [SolicitudController::class, 'listar_solicitud_orden_carga']);
	// Route::get('listar_orden_carga', [InventarioIngresoController::class, 'listar_orden_carga']);
	Route::get('listar_movimiento_carga', [SolicitudController::class, 'listar_movimiento_carga']);
	Route::post('movimiento_orden_carga', [SolicitudController::class, 'movimiento_orden_carga']);
	Route::post('confirmar_solicitud_orden_carga/{id}', [SolicitudController::class, 'confirmar_solicitud_orden_carga']);

	//SOLICITUDES MOVIMIENTOS
	Route::get('listar_solicitud_inventario_dia/{tipo_solicitud_id}', [SolicitudController::class, 'listar_solicitud_inventario_dia']);
	Route::post('listar_solicitud_inventario_intervalo', [SolicitudController::class, 'listar_solicitud_inventario_intervalo']);
	Route::get('obtener_seguimiento/{solicitud_id}', [SolicitudController::class, 'obtener_seguimiento']);
	Route::get('obtener_movimiento_salida/{solicitud_id}', [SolicitudController::class, 'obtener_movimiento_salida']);

	Route::get('obtener_detalle_salida_logistica/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_salida_logistica']);
	Route::get('obtener_detalle_ingreso_logistica/{solicitud_id}', [SolicitudController::class, 'obtener_detalle_ingreso_logistica']);

	Route::get('listar_codigo_solicitud/{tipo_solicitud_id}', [SolicitudController::class, 'listar_codigo_solicitud']);
	Route::post('listar_codigo_solicitud_punto', [SolicitudController::class, 'listar_codigo_solicitud_punto']);
	Route::get('buscar_solicitud/{id_solicitud}', [SolicitudController::class, 'buscar_solicitud']);
	Route::get('buscar_solicitud_devolucion/{id_solicitud}', [SolicitudController::class, 'buscar_solicitud_devolucion']);

	//MOVIMIENTOS BOLETAS
	Route::post('movimiento_boleta_ingreso_inventario', [InventarioIngresoController::class, 'movimiento_boleta_ingreso_inventario']);
	Route::post('movimiento_boleta_salida_inventario', [InventarioIngresoController::class, 'movimiento_boleta_salida_inventario']);

	Route::get('listar_almacenes_plantas/{planta_id}', [SolicitudController::class, 'listar_almacenes_plantas']);
	
	Route::get('listar_tipo_producto/{tipo_solicitud_id}', [SolicitudController::class, 'listar_tipo_producto']);
	Route::get('listar_tipo_producto_limpieza/{tipo_movimiento_id}', [SolicitudController::class, 'listar_tipo_producto_limpieza']);

	Route::post('obtener_productos_solicitud', [SolicitudController::class, 'obtener_productos_solicitud']);
	Route::post('registro_solicitud_emapa', [SolicitudController::class, 'registro_solicitud_emapa']);
	Route::post('aprobar_solicitud_emapa', [SolicitudController::class, 'aprobar_solicitud_emapa']);

	//FRACCIONAMIENTO
	Route::post('obtener_productos_solicitud_fraccionamiento', [SolicitudController::class, 'obtener_productos_solicitud_fraccionamiento']);

	//MERMA
	Route::post('obtener_productos_solicitud_merma', [SolicitudController::class, 'obtener_productos_solicitud_merma']);

	//SOLICITUD DE ORDEN LIMPIEZA
	Route::post('obtener_productos_solicitud_limpieza', [SolicitudController::class, 'obtener_productos_solicitud_limpieza']);

	//SOLICITUD DE MERMA
	Route::get('listar_programas_materia_prima', [ArticulosController::class, 'listar_programas_materia_prima']);
	Route::get('obtener_campaña/{programa_id}', [ArticulosController::class, 'obtener_campaña']);
	//SOLICITUD DE ORDEN SOBRANTE
	//AJUSTE
	Route::get('listar_tipo_movimiento/{tipo_solicitud_id}', [SolicitudController::class, 'listar_tipo_movimiento']);
	Route::post('obtener_productos_solicitud_ajuste', [SolicitudController::class, 'obtener_productos_solicitud_ajuste']);
	Route::post('movimiento_boleta_ingreso_inventario_ajuste', [InventarioIngresoController::class, 'movimiento_boleta_ingreso_inventario_ajuste']);

	//SOLICTUD TRANSFERENCIA
	Route::get('/listar_tipo_transferencia', [SolicitudController::class, 'listar_tipo_transferencia']);
	Route::post('movimiento_orden_salida_ingreso_inventario', [InventarioIngresoController::class, 'movimiento_orden_salida_ingreso_inventario']);
	Route::post('obtener_lotes', [SolicitudController::class, 'obtener_lotes']);
	Route::post('obtener_lotes_destino', [SolicitudController::class, 'obtener_lotes_destino']);
	Route::post('obtener_producto_detalle_solicitud', [SolicitudController::class, 'obtener_producto_detalle_solicitud']);
	Route::post('registro_solicitud_emapa_producto', [SolicitudController::class, 'registro_solicitud_emapa_producto']);
	Route::post('verificar_stock', [SolicitudController::class, 'verificar_stock']);
	Route::get('obtener_registro_producto_detalle_solicitud/{solicitud_id}', [SolicitudController::class, 'obtener_registro_producto_detalle_solicitud']);

	//REPORTES
	Route::get('listar_tipo_solicitud', [SolicitudController::class, 'listar_tipo_solicitud']);
	Route::get('listar_tipo_solicitud_principal/{tipo_solicitud_id}', [SolicitudController::class, 'listar_tipo_solicitud_principal']);
	Route::get('listar_tipo_solicitudes', [SolicitudController::class, 'listar_tipo_solicitudes']);

	//CARTERA



	//SUB SISTEMA DE RECURSOS HUMANOS
	//para empleados
	Route::resource('employee', EmployeeController::class);
	Route::post('actualizar_empleado', [EmployeeController::class, 'actualizar_empleado']);
	Route::get('employee_curriculum', [EmployeeController::class, 'curriculum']);
	Route::get('employee_active', [EmployeeController::class, 'active']);
	Route::get('employee_inactive', [EmployeeController::class, 'inactive']);
	Route::post('employee_delete', [EmployeeController::class, 'desactivar']);
	Route::post('employee_create', [EmployeeController::class, 'activar']);
	Route::post('employee_info3', [EmployeeController::class, 'info_to_rrhh']);
	Route::get('employee_contract', [EmployeeController::class, 'employees_contrac_include']);
	Route::post('assign_type_hour', [EmployeeController::class, 'assign_type_hour']);
	Route::post('save_employee', [EmployeeController::class, 'save_employee']);
	Route::post('enabled_employee', [EmployeeController::class, 'enabled']);
	Route::get('employee_info', [EmployeeController::class, 'info']);
	Route::get('dashboard', [EmployeeController::class, 'dashboard']);
	Route::get('user_check', [EmployeeController::class, 'check']);
	//para boletas de empleados
	Route::get('my_request', [EmployeeRequestController::class, 'index_employee']);
	Route::get('send_request/{employee_request_id}', [EmployeeRequestController::class, 'send']);
	Route::post('approve_request', [EmployeeRequestController::class, 'approve']);
	Route::post('request_upload_image', [EmployeeRequestController::class, 'upload_image']);
	Route::get('archive_request/{employee_request_id}', [EmployeeRequestController::class, 'archived']);
	Route::get('archives', [EmployeeRequestController::class, 'index_archived']);
	Route::get('rechazos', [EmployeeRequestController::class, 'index_rechazos']);
	Route::resource('employee_request', EmployeeRequestController::class);
	//para cruds parametros tablas recursos humanos
	Route::resource('position', PositionController::class);
	Route::resource('city', CityController::class);
	Route::resource('document_type', DocumentTypeController::class);
	Route::resource('country', CountryController::class);
	Route::resource('management', ManagementController::class);
	Route::resource('unity', UnitController::class);
	Route::resource('contribution', ContributionController::class);
	Route::resource('kinship', KinshipController::class);
	Route::resource('health_box', HealthBoxController::class);
	Route::resource('type_hour', TypeHourController::class);
	Route::resource('location', LocationController::class);
	Route::resource('holyday', HolydayController::class);
	Route::resource('request_type', RequestTypeController::class);
	//especificamente para contratos
	Route::resource('contract_type', ContractTypeController::class);
	Route::resource('contract_modality', ContractModalityController::class);
	Route::get('contract_type1', [ContractTypeController::class, 'index2']);
	Route::post('contract_type2', [ContractTypeController::class, 'guarda_con_imagen']);
	Route::get('contract_type3', [ContractTypeController::class, 'selectContrPlantillas']);
	Route::post('contract_type_plantilla', [ContractTypeController::class, 'upload_plantilla']);
	//todo para conexion con el biometrico
	Route::resource('biometric', BiometricController::class);
	Route::post('sync_biometric', [BiometricController::class, 'sync']);
	Route::get('info_biometric/{biometric_id}', [BiometricController::class, 'getInfo']);
	Route::post('sync_biometric_diary', [BiometricController::class, 'syncDiario']);
	Route::post('sync_biometric_diary_fecha', [BiometricController::class, 'syncDiarioFecha']);
	Route::get('list_user_biometric/{list_user_biometric}', [BiometricController::class, 'list_user_biometric']);
	Route::get('ReiniciarBiometrico/{list_user_biometric}', [BiometricController::class, 'ReiniciarBiometrico']);
	Route::get('DesactivarBiometrico/{list_user_biometric}', [BiometricController::class, 'DesactivarBiometrico']);
	Route::get('ActivarBiometrico/{list_user_biometric}', [BiometricController::class, 'ActivarBiometrico']);
	Route::get('DesbloquearBiometrico/{list_user_biometric}', [BiometricController::class, 'DesbloquearBiometrico']);
	Route::get('ActivarBiometrico/{list_user_biometric}', [BiometricController::class, 'ActivarBiometrico']);
	Route::post('sincronizarUsers', [BiometricController::class, 'sincronizarUsers']);
	Route::get('listUserBiometrico/{list_user_biometric}', [BiometricController::class, 'listUserBiometrico']);
	Route::post('registrarbiouser/{id_biometrico}', [BiometricController::class, 'registrarbiouser']);
	Route::get('listEmployeBio', [BiometricController::class, 'listEmployeBio']);
	Route::get('DeletUserBiometric/{id_biometrico}/{id_user}', [BiometricController::class, 'DeletUserBiometric']);
	//ADMINISTRACION DE USUARIOS Y ACCESOS
    Route::get('usuario_rol', [UsuarioController::class, 'usuario_rol']);
    Route::get('listar_usuario_acceso', [AccesoUsuarioController::class, 'listar_usuario_acceso']);
    Route::post('acceso_usuario', [UsuarioController::class, 'acceso_usuario']);
    Route::get('usuario_punto', [UsuarioPuntoController::class, 'usuario_punto']);
    Route::get('listar_puntos', [UsuarioPuntoController::class, 'listar_puntos']);
    Route::post('asignar_puntos', [UsuarioPuntoController::class, 'asignar_puntos']);
    Route::get('listar_usuario_punto', [UsuarioPuntoController::class, 'listar_usuario_punto']);
	Route::get('get_all_contratos', [ContratoController::class, 'get_all_contratos']);
	


});
