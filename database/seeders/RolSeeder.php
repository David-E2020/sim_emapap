<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para registrar todos los permisos granulares del sistema,
     * crear los roles especializados de la Empresa Municipal de Agua Potable y Alcantarillado (EMAPAP),
     * sincronizar la matriz de visibilidad de menús (menu_rol) y asignar roles a los usuarios clave.
     */
    public function run(): void
    {
        // 1. Resetear cache de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. DEFINICIÓN EXHAUSTIVA DE PERMISOS GRANULARES POR SUBMÓDULO
        // Cada permiso cuenta con un 'module' explícito que coincide con MODULE_MAP para evitar 'Permisos Huérfanos'
        $allPermissionsData = [
            // ==================== 1. ADMINISTRAR ====================
            // Submódulo: Usuarios
            ['name' => 'admin.usuarios.ver', 'module' => 'Usuarios', 'description' => 'Consultar y listar usuarios del sistema'],
            ['name' => 'admin.usuarios.crear', 'module' => 'Usuarios', 'description' => 'Registrar nuevos usuarios en el sistema'],
            ['name' => 'admin.usuarios.editar', 'module' => 'Usuarios', 'description' => 'Modificar datos de usuarios existentes'],
            ['name' => 'admin.usuarios.eliminar', 'module' => 'Usuarios', 'description' => 'Desactivar o suspender cuentas de usuario'],
            ['name' => 'admin.usuarios.acceso', 'module' => 'Usuarios', 'description' => 'Habilitar o revocar acceso directo al sistema'],
            ['name' => 'admin.usuarios.reset_password', 'module' => 'Usuarios', 'description' => 'Restablecer contraseña institucional de usuario'],

            // Submódulo: Roles y Permisos
            ['name' => 'admin.menus.ver', 'module' => 'Roles y Permisos', 'description' => 'Visualizar estructura y jerarquía de menús'],
            ['name' => 'admin.menus.crear', 'module' => 'Roles y Permisos', 'description' => 'Crear nuevos menús y submódulos'],
            ['name' => 'admin.menus.editar', 'module' => 'Roles y Permisos', 'description' => 'Editar propiedades y rutas de menús'],
            ['name' => 'admin.menus.eliminar', 'module' => 'Roles y Permisos', 'description' => 'Eliminar menús o submódulos'],
            ['name' => 'admin.menus.reordenar', 'module' => 'Roles y Permisos', 'description' => 'Reordenar menús en la barra lateral'],
            ['name' => 'admin.control_acceso.ver', 'module' => 'Roles y Permisos', 'description' => 'Ver matriz unificada de roles y privilegios'],
            ['name' => 'admin.control_acceso.guardar', 'module' => 'Roles y Permisos', 'description' => 'Asignar y modificar permisos de roles'],

            // ==================== 2. DATOS ====================
            // Submódulo: Paramétrica
            ['name' => 'parametricas.ver', 'module' => 'Parametrica', 'description' => 'Consultar catálogos y tablas paramétricas'],
            ['name' => 'parametricas.crear', 'module' => 'Parametrica', 'description' => 'Agregar nuevos registros a tablas paramétricas'],
            ['name' => 'parametricas.editar', 'module' => 'Parametrica', 'description' => 'Modificar valores de catálogos paramétricos'],

            // Submódulo: Configuración Empresa y SIAT
            ['name' => 'datos.empresa.gestionar', 'module' => 'Configuración Empresa y SIAT', 'description' => 'Configurar datos de la empresa, credenciales fiscales y parámetros SIAT'],

            // Submódulo: Migrador FoxPro
            ['name' => 'datos.migrador.ejecutar', 'module' => 'Migrador de Respaldos (FoxPro)', 'description' => 'Ejecutar procesos de migración y sincronización FoxPro'],

            // ==================== 3. RECURSOS HUMANOS ====================
            // Personal y Legajos
            ['name' => 'rrhh.personal.ver', 'module' => 'Personal y Legajos', 'description' => 'Consultar listado de funcionarios y datos básicos'],
            ['name' => 'rrhh.personal.crear', 'module' => 'Personal y Legajos', 'description' => 'Registrar nuevos funcionarios y cuentas de acceso'],
            ['name' => 'rrhh.personal.editar', 'module' => 'Personal y Legajos', 'description' => 'Modificar datos personales y demográficos'],
            ['name' => 'rrhh.personal.legajo', 'module' => 'Personal y Legajos', 'description' => 'Ver y actualizar legajo digital (estudios, experiencia, CAS)'],

            // Estructura Organizacional
            ['name' => 'rrhh.organigrama.ver', 'module' => 'Estructura Organizacional', 'description' => 'Ver árbol de organigrama y puestos institucionales'],
            ['name' => 'rrhh.organigrama.crear_unidad', 'module' => 'Estructura Organizacional', 'description' => 'Crear y reorganizar unidades organizacionales'],
            ['name' => 'rrhh.organigrama.crear_puesto', 'module' => 'Estructura Organizacional', 'description' => 'Definir puestos y escalas salariales'],
            ['name' => 'rrhh.organigrama.asignar_puesto', 'module' => 'Estructura Organizacional', 'description' => 'Asignar ítems y cargos a funcionarios'],

            // Control de Asistencia
            ['name' => 'rrhh.asistencias.ver', 'module' => 'Control de Asistencia', 'description' => 'Ver marcaciones y registros diarios de asistencia'],
            ['name' => 'rrhh.asistencias.sincronizar_biometrico', 'module' => 'Control de Asistencia', 'description' => 'Descargar marcaciones desde relojes biométricos'],
            ['name' => 'rrhh.asistencias.calcular_asistencia', 'module' => 'Control de Asistencia', 'description' => 'Procesar cálculo de atrasos, tolerancias y refrigerios'],
            ['name' => 'rrhh.biometricos.administrar', 'module' => 'Control de Asistencia', 'description' => 'Registrar y configurar relojes biométricos en red'],

            // Gestión de Horarios
            ['name' => 'rrhh.horarios.ver', 'module' => 'Gestión de Horarios', 'description' => 'Ver tipos de jornada y asignaciones de horario'],
            ['name' => 'rrhh.horarios.crear', 'module' => 'Gestión de Horarios', 'description' => 'Crear nuevos horarios y turnos de entrada/salida'],
            ['name' => 'rrhh.horarios.asignar', 'module' => 'Gestión de Horarios', 'description' => 'Asignar turnos a funcionarios de forma individual o masiva'],

            // Boletas y Permisos
            ['name' => 'rrhh.solicitudes.ver', 'module' => 'Boletas y Permisos', 'description' => 'Listar boletas de salida y solicitudes de permiso'],
            ['name' => 'rrhh.solicitudes.crear', 'module' => 'Boletas y Permisos', 'description' => 'Solicitar permisos, licencias y comisiones oficiales'],
            ['name' => 'rrhh.solicitudes.aprobar', 'module' => 'Boletas y Permisos', 'description' => 'Aprobar o rechazar boletas de salida de personal'],

            // Comisiones y Omisiones
            ['name' => 'rrhh.comisiones.ver', 'module' => 'Comisiones y Omisiones', 'description' => 'Ver viajes oficiales en comisión y descargos'],
            ['name' => 'rrhh.comisiones.crear', 'module' => 'Comisiones y Omisiones', 'description' => 'Registrar comisiones de viaje institucional con viáticos'],
            ['name' => 'rrhh.omisiones.crear', 'module' => 'Comisiones y Omisiones', 'description' => 'Solicitar regularización por omisión o fallo de marcado'],
            ['name' => 'rrhh.omisiones.aprobar', 'module' => 'Comisiones y Omisiones', 'description' => 'Bandeja de firma y resolución de solicitudes'],

            // Feriados y Cortes
            ['name' => 'rrhh.feriados.ver', 'module' => 'Feriados y Cortes', 'description' => 'Consultar calendario oficial de feriados y cortes'],
            ['name' => 'rrhh.feriados.crear', 'module' => 'Feriados y Cortes', 'description' => 'Registrar feriados nacionales y fechas de corte mensual'],

            // Reportes y Planillas
            ['name' => 'rrhh.reportes.asistencia', 'module' => 'Reportes y Planillas', 'description' => 'Generar consolidado mensual de asistencia y atrasos'],
            ['name' => 'rrhh.reportes.refrigerio', 'module' => 'Reportes y Planillas', 'description' => 'Generar planilla mensual de refrigerios para pago'],
            ['name' => 'rrhh.reportes.vacaciones', 'module' => 'Reportes y Planillas', 'description' => 'Consultar kardex y saldo de vacaciones por ley'],
            ['name' => 'rrhh.reportes.boleta_imprimir', 'module' => 'Reportes y Planillas', 'description' => 'Imprimir boleta de salida con membrete oficial EMAPA'],

            // ==================== 4. CORRESPONDENCIA Y TRÁMITES ====================
            ['name' => 'correspondencia.dashboard.ver', 'module' => 'Dashboard y Métricas', 'description' => 'Ver métricas e indicadores de gestión documental'],
            ['name' => 'correspondencia.hojas_ruta.ver', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Ver bandejas de entrada, salida y archivados'],
            ['name' => 'correspondencia.hojas_ruta.crear', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Generar nuevas hojas de ruta y trámites'],
            ['name' => 'correspondencia.hojas_ruta.derivar', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Derivar expedientes con proveídos oficiales'],
            ['name' => 'correspondencia.hojas_ruta.recibir', 'module' => 'Bandeja de Hojas de Ruta', 'description' => 'Recepcionar correspondencia en bandeja'],
            ['name' => 'correspondencia.documentos.ver', 'module' => 'Redacción de Documentos', 'description' => 'Consultar repositorio de documentos internos'],
            ['name' => 'correspondencia.documentos.crear', 'module' => 'Redacción de Documentos', 'description' => 'Redactar memorándums, informes y notas internas'],
            ['name' => 'correspondencia.documentos.firmar', 'module' => 'Firmas y Aprobaciones', 'description' => 'Firmar electrónicamente documentos con PIN oficial'],
            ['name' => 'correspondencia.seguimiento.ver', 'module' => 'Seguimiento y Trazabilidad', 'description' => 'Ver timeline y árbol de trazabilidad de trámites'],
            ['name' => 'correspondencia.visor.ver', 'module' => 'Visor de Expediente 360°', 'description' => 'Consultar historial completo, anexos y hojas de ruta 360°'],
            ['name' => 'correspondencia.ventanilla.recibir', 'module' => 'Ventanilla Única', 'description' => 'Registrar cartas externas y emitir comprobantes de trámite'],
            ['name' => 'correspondencia.ventanilla.despachar', 'module' => 'Ventanilla Única', 'description' => 'Despachar correspondencia externa y citaciones'],
            ['name' => 'correspondencia.despachos.administrar', 'module' => 'Despacho y Salida Externa', 'description' => 'Gestionar envíos físicos y acuses de recibo'],
            ['name' => 'correspondencia.solicitudes.administrar', 'module' => 'Solicitudes Ciudadanas', 'description' => 'Admitir, derivar o rechazar trámites ciudadanos web'],
            ['name' => 'correspondencia.etiquetas.administrar', 'module' => 'Etiquetas y Carpetas', 'description' => 'Crear y organizar etiquetas y carpetas de expedientes'],
            ['name' => 'correspondencia.permisos.administrar', 'module' => 'Matriz de Derivaciones', 'description' => 'Configurar flujos de derivación permitidos entre áreas'],
            ['name' => 'correspondencia.transferencias.ejecutar', 'module' => 'Transferencias de Bandeja', 'description' => 'Reasignar bandejas y expedientes entre funcionarios'],
            ['name' => 'correspondencia.configuracion.administrar', 'module' => 'Plantillas y Diseñador PDF', 'description' => 'Gestionar plantillas oficiales, correlativos y membretes'],

            // ==================== 5. FACTURACIÓN SIAT ====================
            ['name' => 'facturacion.caja.cobrar', 'module' => 'Caja y Facturación en Ventanilla', 'description' => 'Cobro en ventanilla y emisión de factura computarizada SIAT'],
            ['name' => 'facturacion.facturas.crear', 'module' => 'Facturación Libre / Especial', 'description' => 'Emisión manual de facturas electrónicas por servicios especiales'],
            ['name' => 'facturacion.facturas.ver', 'module' => 'Bandeja de Facturas', 'description' => 'Consultar y reimprimir facturas emitidas'],
            ['name' => 'facturacion.facturas.anular', 'module' => 'Bandeja de Facturas', 'description' => 'Solicitar anulación de facturas ante el SIN'],
            ['name' => 'facturacion.contingencias.administrar', 'module' => 'Facturas de Contingencia', 'description' => 'Registrar y empaquetar facturas de contingencia'],
            ['name' => 'facturacion.eventos.administrar', 'module' => 'Eventos Significativos', 'description' => 'Iniciar y cerrar eventos significativos ante el SIN'],
            ['name' => 'facturacion.puntos_venta.administrar', 'module' => 'Sucursales y Puntos de Venta', 'description' => 'Administrar sucursales y puntos de venta autorizados'],
            ['name' => 'facturacion.clientes.administrar', 'module' => 'Clientes Facturación Libre', 'description' => 'Administrar catálogo de clientes para facturas libres'],
            ['name' => 'facturacion.catalogos.administrar', 'module' => 'Productos y Servicios SIN', 'description' => 'Sincronizar y homologar servicios con catálogo SIN'],
            ['name' => 'facturacion.libro_ventas.ver', 'module' => 'Libro de Ventas IVA', 'description' => 'Consultar y exportar Registro de Ventas IVA (RCV)'],

            // ==================== 6. GESTIÓN COMERCIAL ====================
            ['name' => 'comercial.abonados.ver', 'module' => 'Padrón de Abonados', 'description' => 'Ver listado, kardex y ficha de abonados'],
            ['name' => 'comercial.abonados.crear', 'module' => 'Padrón de Abonados', 'description' => 'Registrar altas, bajas y transferencias de abonados'],
            ['name' => 'comercial.abonados.georreferenciar', 'module' => 'Padrón de Abonados', 'description' => 'Georreferenciar acometidas y asignar datos catastrales'],
            ['name' => 'comercial.periodos.administrar', 'module' => 'Ciclo de Períodos', 'description' => 'Aperturar, precerrar y liquidar períodos mensuales'],
            ['name' => 'comercial.lecturas.registrar', 'module' => 'Toma de Lecturas', 'description' => 'Registrar lecturas de medidores, consumos y críticas'],
            ['name' => 'comercial.sesiones_caja.aperturar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Aperturar turno de recaudación en caja'],
            ['name' => 'comercial.sesiones_caja.cerrar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Cierre de turno y arqueo ciego de recaudación'],
            ['name' => 'comercial.sesiones_caja.supervisar', 'module' => 'Cierres y Arqueos de Caja', 'description' => 'Supervisar y validar arqueos de todos los cajeros'],
            ['name' => 'comercial.convenios.administrar', 'module' => 'Convenios de Pago', 'description' => 'Suscripción, reprogramación y seguimiento de convenios de pago'],
            ['name' => 'comercial.cortes.administrar', 'module' => 'Cortes y Reconexiones', 'description' => 'Gestionar órdenes de corte por mora y reconexiones'],
            ['name' => 'comercial.zonas_calles.administrar', 'module' => 'Zonas y Calles', 'description' => 'Gestionar sectores, zonas, rutas de lectura y calles'],
            ['name' => 'comercial.tarifas.administrar', 'module' => 'Estructura Tarifaria', 'description' => 'Configurar categorías tarifarias y rangos de consumo AAPS'],
            ['name' => 'comercial.reportes.ver', 'module' => 'Reportes Comerciales', 'description' => 'Ver reportes de facturación, recaudación y morosidad'],
            ['name' => 'comercial.aportes.administrar', 'module' => 'Aportes e Instalaciones', 'description' => 'Gestionar derechos de conexión, inspecciones y materiales'],

            // ==================== 7. CONTABILIDAD Y FINANZAS ====================
            ['name' => 'contabilidad.plan_cuentas.ver', 'module' => 'Plan de Cuentas', 'description' => 'Consultar árbol y catálogo del Plan de Cuentas'],
            ['name' => 'contabilidad.plan_cuentas.administrar', 'module' => 'Plan de Cuentas', 'description' => 'Crear y modificar cuentas contables analíticas y de nivel superior'],
            ['name' => 'contabilidad.comprobantes.ver', 'module' => 'Comprobantes (CI/CE/CD)', 'description' => 'Consultar comprobantes de Ingreso, Egreso y Diario'],
            ['name' => 'contabilidad.comprobantes.crear', 'module' => 'Comprobantes (CI/CE/CD)', 'description' => 'Registrar y editar comprobantes y asientos contables'],
            ['name' => 'contabilidad.comprobantes.aprobar', 'module' => 'Comprobantes (CI/CE/CD)', 'description' => 'Aprobar, validar y mayorizar comprobantes contables'],
            ['name' => 'contabilidad.interfases.procesar', 'module' => 'Interfaces Automáticas', 'description' => 'Generar asientos contables automáticos desde recaudación y planillas'],
            ['name' => 'contabilidad.libros.ver', 'module' => 'Libros Diario y Mayor', 'description' => 'Consultar libros diario y mayor analítico'],
            ['name' => 'contabilidad.libros.exportar', 'module' => 'Libros Diario y Mayor', 'description' => 'Exportar libros contables a PDF y Excel'],
            ['name' => 'contabilidad.estados_financieros.ver', 'module' => 'Estados Financieros SAFCO', 'description' => 'Visualizar balance general y estado de resultados'],
            ['name' => 'contabilidad.estados_financieros.generar', 'module' => 'Estados Financieros SAFCO', 'description' => 'Generar estados financieros oficiales según Ley 1178'],

            // ==================== 8. CATASTRO Y MÓDULO GIS TÉCNICO ====================
            ['name' => 'gis.mapa.ver', 'module' => 'Zonas y Calles', 'description' => 'Visualizar mapa cartográfico y capas de red de agua'],
            ['name' => 'gis.capas.administrar', 'module' => 'Zonas y Calles', 'description' => 'Administrar capas GIS de tuberías, válvulas e hidrantes'],
            ['name' => 'gis.georreferenciacion.editar', 'module' => 'Padrón de Abonados', 'description' => 'Geolocalizar medidores y acometidas domiciliarias'],
            ['name' => 'gis.redes.ver', 'module' => 'Zonas y Calles', 'description' => 'Consultar topología de red de agua potable y alcantarillado'],

            // ==================== 9. PERMISOS TRANSVERSALES / LEGADO ====================
            ['name' => 'SIGP', 'module' => null, 'description' => 'Acceso general al entorno institucional EMAPAP'],
        ];

        foreach ($allPermissionsData as $pData) {
            Permission::updateOrCreate(
                ['name' => $pData['name'], 'guard_name' => 'api'],
                [
                    'module' => $pData['module'],
                    'description' => $pData['description'],
                ]
            );
        }

        // 3. ROLES DE LA EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO
        $rolesDefinition = [
            'Administrador General' => [
                'description' => 'Superadministrador con acceso irrestricto al 100% de módulos, usuarios y configuraciones.',
                'all_submenus' => true,
                'allowed_submenus' => [],
                'permissions' => '__ALL__',
            ],
            'Cajero(a) / Recaudador(a)' => [
                'description' => 'Operador de caja para recaudación de agua potable, alcantarillado, servicios técnicos y convenios.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'facturacion_caja',
                    'facturacion_bandeja',
                    'facturacion_contingencias',
                    'comercial_abonados',
                    'comercial_sesiones_caja',
                    'comercial_convenios',
                    'correspondencia_hojas_ruta',
                ],
                'permissions' => [
                    'SIGP',
                    'facturacion.caja.cobrar',
                    'facturacion.facturas.ver',
                    'facturacion.contingencias.administrar',
                    'comercial.abonados.ver',
                    'comercial.sesiones_caja.aperturar',
                    'comercial.sesiones_caja.cerrar',
                    'comercial.convenios.administrar',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.hojas_ruta.derivar',
                ],
            ],
            'Contador(a) General' => [
                'description' => 'Responsable contable y financiero: libros SAFCO Ley 1178, comprobantes, arqueos y devengamientos.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'contabilidad_plan_cuentas',
                    'contabilidad_comprobantes',
                    'contabilidad_interfases',
                    'contabilidad_libros',
                    'contabilidad_estados_financieros',
                    'facturacion_bandeja',
                    'facturacion_libro_ventas',
                    'comercial_sesiones_caja',
                    'comercial_reportes',
                    'rrhh_reportes',
                    'correspondencia_hojas_ruta',
                    'correspondencia_documentos',
                    'correspondencia_firmas',
                ],
                'permissions' => [
                    'SIGP',
                    'contabilidad.plan_cuentas.ver',
                    'contabilidad.plan_cuentas.administrar',
                    'contabilidad.comprobantes.ver',
                    'contabilidad.comprobantes.crear',
                    'contabilidad.comprobantes.aprobar',
                    'contabilidad.interfases.procesar',
                    'contabilidad.libros.ver',
                    'contabilidad.libros.exportar',
                    'contabilidad.estados_financieros.ver',
                    'contabilidad.estados_financieros.generar',
                    'facturacion.facturas.ver',
                    'facturacion.libro_ventas.ver',
                    'comercial.sesiones_caja.supervisar',
                    'comercial.reportes.ver',
                    'rrhh.reportes.asistencia',
                    'rrhh.reportes.refrigerio',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.hojas_ruta.derivar',
                    'correspondencia.documentos.ver',
                    'correspondencia.documentos.crear',
                    'correspondencia.documentos.firmar',
                ],
            ],
            'Jefe(a) Comercial' => [
                'description' => 'Dirección de gestión comercial, medición, facturación mensual, convenios, cortes y tarifas AAPS.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'comercial_abonados',
                    'comercial_periodos',
                    'comercial_lecturas',
                    'comercial_sesiones_caja',
                    'comercial_convenios',
                    'comercial_cortes',
                    'comercial_zonas_calles',
                    'comercial_tarifas',
                    'comercial_reportes',
                    'comercial_aportes',
                    'facturacion_caja',
                    'facturacion_bandeja',
                    'facturacion_contingencias',
                    'facturacion_eventos',
                    'facturacion_puntos_venta',
                    'facturacion_libro_ventas',
                    'correspondencia_dashboard',
                    'correspondencia_hojas_ruta',
                    'correspondencia_documentos',
                    'correspondencia_firmas',
                    'correspondencia_solicitudes_ciudadanas',
                ],
                'permissions' => [
                    'SIGP',
                    'comercial.abonados.ver',
                    'comercial.abonados.crear',
                    'comercial.abonados.georreferenciar',
                    'comercial.periodos.administrar',
                    'comercial.lecturas.registrar',
                    'comercial.sesiones_caja.aperturar',
                    'comercial.sesiones_caja.cerrar',
                    'comercial.sesiones_caja.supervisar',
                    'comercial.convenios.administrar',
                    'comercial.cortes.administrar',
                    'comercial.zonas_calles.administrar',
                    'comercial.tarifas.administrar',
                    'comercial.reportes.ver',
                    'comercial.aportes.administrar',
                    'facturacion.caja.cobrar',
                    'facturacion.facturas.ver',
                    'facturacion.facturas.anular',
                    'facturacion.contingencias.administrar',
                    'facturacion.eventos.administrar',
                    'facturacion.puntos_venta.administrar',
                    'facturacion.libro_ventas.ver',
                    'correspondencia.dashboard.ver',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.crear',
                    'correspondencia.hojas_ruta.derivar',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.documentos.ver',
                    'correspondencia.documentos.crear',
                    'correspondencia.documentos.firmar',
                    'correspondencia.solicitudes.administrar',
                ],
            ],
            'Técnico(a) de Catastro y GIS' => [
                'description' => 'Georreferenciación de redes de agua, catastro de acometidas, inspección técnica y cuadrillas.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'comercial_abonados',
                    'comercial_zonas_calles',
                    'comercial_aportes',
                    'comercial_cortes',
                    'comercial_lecturas',
                    'correspondencia_hojas_ruta',
                    'correspondencia_visor_expediente',
                ],
                'permissions' => [
                    'SIGP',
                    'comercial.abonados.ver',
                    'comercial.abonados.georreferenciar',
                    'comercial.zonas_calles.administrar',
                    'comercial.aportes.administrar',
                    'comercial.cortes.administrar',
                    'comercial.lecturas.registrar',
                    'gis.mapa.ver',
                    'gis.capas.administrar',
                    'gis.georreferenciacion.editar',
                    'gis.redes.ver',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.hojas_ruta.derivar',
                    'correspondencia.visor.ver',
                ],
            ],
            'Encargado(a) de Recursos Humanos' => [
                'description' => 'Gestión integral del personal, organigrama, biométricos, turnos, licencias, permisos y planillas.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'rrhh_personal',
                    'rrhh_organigrama',
                    'rrhh_asistencias',
                    'rrhh_horarios',
                    'rrhh_solicitudes',
                    'rrhh_comisiones_omisiones',
                    'rrhh_feriados_cortes',
                    'rrhh_reportes',
                    'correspondencia_hojas_ruta',
                    'correspondencia_documentos',
                    'correspondencia_firmas',
                ],
                'permissions' => [
                    'SIGP',
                    'rrhh.personal.ver',
                    'rrhh.personal.crear',
                    'rrhh.personal.editar',
                    'rrhh.personal.legajo',
                    'rrhh.organigrama.ver',
                    'rrhh.organigrama.crear_unidad',
                    'rrhh.organigrama.crear_puesto',
                    'rrhh.organigrama.asignar_puesto',
                    'rrhh.asistencias.ver',
                    'rrhh.asistencias.sincronizar_biometrico',
                    'rrhh.asistencias.calcular_asistencia',
                    'rrhh.biometricos.administrar',
                    'rrhh.horarios.ver',
                    'rrhh.horarios.crear',
                    'rrhh.horarios.asignar',
                    'rrhh.solicitudes.ver',
                    'rrhh.solicitudes.crear',
                    'rrhh.solicitudes.aprobar',
                    'rrhh.comisiones.ver',
                    'rrhh.comisiones.crear',
                    'rrhh.omisiones.crear',
                    'rrhh.omisiones.aprobar',
                    'rrhh.feriados.ver',
                    'rrhh.feriados.crear',
                    'rrhh.reportes.asistencia',
                    'rrhh.reportes.refrigerio',
                    'rrhh.reportes.vacaciones',
                    'rrhh.reportes.boleta_imprimir',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.crear',
                    'correspondencia.hojas_ruta.derivar',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.documentos.ver',
                    'correspondencia.documentos.crear',
                    'correspondencia.documentos.firmar',
                ],
            ],
            'Operador(a) de Ventanilla Única y Trámites' => [
                'description' => 'Atención al usuario, recepción de correspondencia externa, registro de solicitudes y trámites ODEBI.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'correspondencia_ventanilla',
                    'correspondencia_hojas_ruta',
                    'correspondencia_seguimiento',
                    'correspondencia_visor_expediente',
                    'correspondencia_solicitudes_ciudadanas',
                    'correspondencia_despacho_salida',
                    'comercial_abonados',
                ],
                'permissions' => [
                    'SIGP',
                    'correspondencia.ventanilla.recibir',
                    'correspondencia.ventanilla.despachar',
                    'correspondencia.hojas_ruta.ver',
                    'correspondencia.hojas_ruta.crear',
                    'correspondencia.hojas_ruta.derivar',
                    'correspondencia.hojas_ruta.recibir',
                    'correspondencia.seguimiento.ver',
                    'correspondencia.visor.ver',
                    'correspondencia.solicitudes.administrar',
                    'correspondencia.despachos.administrar',
                    'comercial.abonados.ver',
                ],
            ],
            'Operador del Sistema' => [
                'description' => 'Operador general con acceso básico de consulta institucional.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'usuarios',
                    'parametrica',
                ],
                'permissions' => [
                    'SIGP',
                    'admin.usuarios.ver',
                    'parametricas.ver',
                ],
            ],
            'Consultor' => [
                'description' => 'Rol de solo lectura y auditoría externa.',
                'all_submenus' => false,
                'allowed_submenus' => [
                    'parametrica',
                ],
                'permissions' => [
                    'SIGP',
                    'parametricas.ver',
                ],
            ],
        ];

        // Obtener todos los menús registrados en la BD
        $allMenus = Menu::all();
        $parentMenus = $allMenus->whereNull('menu_id');
        $childMenus = $allMenus->whereNotNull('menu_id');

        foreach ($rolesDefinition as $roleName => $roleConfig) {
            // A. Crear o actualizar rol Spatie
            $role = Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'api']
            );

            // B. Sincronizar Permisos Spatie
            if ($roleConfig['permissions'] === '__ALL__') {
                $role->syncPermissions(Permission::all());
            } else {
                $permissionsToSync = Permission::whereIn('name', $roleConfig['permissions'])->get();
                $role->syncPermissions($permissionsToSync);
            }

            // C. Configurar Matriz de Navegación (menu_rol)
            $isFullAdmin = (bool) $roleConfig['all_submenus'];
            $allowedRoutes = $roleConfig['allowed_submenus'] ?? [];

            // 1) Asignar submenús hijos (nivel 1)
            foreach ($childMenus as $child) {
                $shouldBeActive = $isFullAdmin || in_array($child->route, $allowedRoutes, true);

                MenuRol::updateOrCreate(
                    ['rol_id' => $role->id, 'menu_id' => $child->id],
                    ['check' => $shouldBeActive]
                );
            }

            // 2) Asignar menús padres (nivel 0) si tienen al menos un hijo activo
            foreach ($parentMenus as $parent) {
                $activeChildrenCount = MenuRol::where('rol_id', $role->id)
                    ->where('check', true)
                    ->whereIn('menu_id', $childMenus->where('menu_id', $parent->id)->pluck('id'))
                    ->count();

                $shouldParentBeActive = $isFullAdmin || ($activeChildrenCount > 0);

                MenuRol::updateOrCreate(
                    ['rol_id' => $role->id, 'menu_id' => $parent->id],
                    ['check' => $shouldParentBeActive]
                );
            }
        }

        // 4. ASIGNACIÓN ATÓMICA DE ROLES A USUARIOS EXISTENTES
        $userAssignments = [
            'admin' => 'Administrador General',
            'ysalvador' => 'Contador(a) General',
            'aisidro' => 'Jefe(a) Comercial',
            'ecachi' => 'Cajero(a) / Recaudador(a)',
        ];

        $sigpPermission = Permission::where('name', 'SIGP')->first();

        foreach ($userAssignments as $username => $roleToAssign) {
            $user = User::where('usr_usuario', $username)->first();
            $role = Role::where('name', $roleToAssign)->where('guard_name', 'api')->first();

            if ($user && $role) {
                // Sincronizar rol Spatie
                $user->syncRoles([$role]);

                // Asignar permiso base inmutable de acceso
                if ($sigpPermission) {
                    $user->givePermissionTo($sigpPermission);
                }

                // Sincronizar en tabla pivote de sesión rol_user
                RolUser::updateOrCreate(
                    ['usuario_id' => $user->id],
                    [
                        'rol_id' => $role->id,
                        'estado' => true,
                        'usr_registrado' => 1,
                        'usr_modificado' => 1,
                    ]
                );
            }
        }

        // Olvidar cache de permisos final
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
