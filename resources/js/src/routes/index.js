import Vue from 'vue';
import Router from 'vue-router';

export const routes = [
  {
    path: '/',
    redirect: 'dashboard',
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/dashboard/Dashboard.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    component: () => import('@/views/pages/account-settings/AccountSettings.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MENU DATOS / PARAMETRICAS
  {
    path: '/parametrica',
    name: 'parametrica',
    component: () => import('@/views/parametricas/Parametrica.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/datos/empresa',
    name: 'datos_empresa',
    component: () => import('@/views/datos/ConfiguracionEmpresaSiat.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MENU ADMINISTRACION
  {
    path: '/usuarios',
    name: 'usuarios',
    component: () => import('@/views/administrar/Usuario.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/roles-permisos',
    name: 'roles_permisos',
    component: () => import('@/views/administrar/RolesPermisos.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/control_acceso',
    redirect: '/roles-permisos',
  },
  {
    path: '/admin-menu',
    redirect: '/roles-permisos',
  },
  {
    path: '/admin_menu',
    redirect: '/roles-permisos',
  },
  {
    path: '/auditoria',
    name: 'auditoria',
    component: () => import('@/views/administrar/Auditoria.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MODULO RECURSOS HUMANOS (CAPIBARA)
  {
    path: '/rrhh/personal',
    name: 'rrhh_personal',
    component: () => import('@/views/rrhh/Personal.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/organigrama',
    name: 'rrhh_organigrama',
    component: () => import('@/views/rrhh/Organigrama.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/asistencias',
    name: 'rrhh_asistencias',
    component: () => import('@/views/rrhh/ControlAsistencia.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/solicitudes',
    name: 'rrhh_solicitudes',
    component: () => import('@/views/rrhh/BoletasPermisos.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/horarios',
    name: 'rrhh_horarios',
    component: () => import('@/views/rrhh/Horarios.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/comisiones-omisiones',
    name: 'rrhh_comisiones_omisiones',
    component: () => import('@/views/rrhh/ComisionesOmisiones.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/feriados-cortes',
    name: 'rrhh_feriados_cortes',
    component: () => import('@/views/rrhh/FeriadosCortes.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/rrhh/reportes',
    name: 'rrhh_reportes',
    component: () => import('@/views/rrhh/Reportes.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MODULO CORRESPONDENCIA Y HOJAS DE RUTA (LONDRA)
  {
    path: '/correspondencia/hojas-ruta',
    name: 'correspondencia_hojas_ruta',
    component: () => import('@/views/correspondencia/BandejaHojasRuta.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/documentos',
    name: 'correspondencia_documentos',
    component: () => import('@/views/correspondencia/GestionDocumentos.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/firmas',
    name: 'correspondencia_firmas',
    component: () => import('@/views/correspondencia/BandejaFirmas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/seguimiento',
    name: 'correspondencia_seguimiento',
    component: () => import('@/views/correspondencia/SeguimientoHojaRuta.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/ventanilla',
    name: 'correspondencia_ventanilla',
    component: () => import('@/views/correspondencia/VentanillaExterna.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/despacho-salida',
    name: 'correspondencia_despacho_salida',
    component: () => import('@/views/correspondencia/BandejaSalidaExterna.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/solicitudes-ciudadanas',
    name: 'correspondencia_solicitudes_ciudadanas',
    component: () => import('@/views/correspondencia/SolicitudesCiudadanas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/visor-expediente',
    name: 'correspondencia_visor_expediente',
    component: () => import('@/views/correspondencia/VisorExpedienteDigital.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/etiquetas',
    name: 'correspondencia_etiquetas',
    component: () => import('@/views/correspondencia/GestionEtiquetas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/plantillas',
    name: 'correspondencia_plantillas',
    component: () => import('@/views/correspondencia/DisenadorPlantillas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/configuracion',
    name: 'correspondencia_configuracion',
    component: () => import('@/views/correspondencia/ConfiguracionCorrespondencia.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/dashboard',
    name: 'correspondencia_dashboard',
    component: () => import('@/views/correspondencia/DashboardCorrespondencia.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/permisos',
    name: 'correspondencia_permisos',
    component: () => import('@/views/correspondencia/MatrizPermisosCorrespondencia.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/correspondencia/transferencias',
    name: 'correspondencia_transferencias',
    component: () => import('@/views/correspondencia/TransferenciaExpedientes.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MODULO DE FACTURACIÓN ELECTRÓNICA SIAT
  {
    path: '/facturacion/caja',
    name: 'facturacion_caja',
    component: () => import('@/views/comercial/CajaCobranzas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/crear',
    name: 'facturacion_crear',
    component: () => import('@/views/facturacion/CrearFactura.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/bandeja',
    name: 'facturacion_bandeja',
    component: () => import('@/views/facturacion/BandejaFacturas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/contingencias',
    name: 'facturacion_contingencias',
    component: () => import('@/views/facturacion/FacturaContingencia.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/eventos',
    name: 'facturacion_eventos',
    component: () => import('@/views/facturacion/EventosSignificativos.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/puntos-venta',
    name: 'facturacion_puntos_venta',
    component: () => import('@/views/facturacion/PuntosVentaSucursales.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/clientes',
    name: 'facturacion_clientes',
    component: () => import('@/views/facturacion/ClientesFacturacion.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/productos',
    name: 'facturacion_productos',
    component: () => import('@/views/facturacion/ProductosServicios.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/facturacion/libro-ventas',
    name: 'facturacion_libro_ventas',
    component: () => import('@/views/facturacion/ReporteLibroVentas.vue'),
    meta: {
      requiresAuth: true,
    },
  },

  // MODULO GESTIÓN COMERCIAL Y AGUA POTABLE
  {
    path: '/comercial/abonados',
    name: 'comercial_abonados',
    component: () => import('@/views/comercial/PadronAbonados.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/lecturas',
    name: 'comercial_lecturas',
    component: () => import('@/views/comercial/TomaLecturas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/caja',
    name: 'comercial_caja',
    component: () => import('@/views/comercial/CajaCobranzas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/convenios',
    name: 'comercial_convenios',
    component: () => import('@/views/comercial/ConveniosPago.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/cortes',
    name: 'comercial_cortes',
    component: () => import('@/views/comercial/CortesReconexiones.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/tarifas',
    name: 'comercial_tarifas',
    component: () => import('@/views/comercial/ConfiguracionTarifasZonas.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/reportes',
    name: 'comercial_reportes',
    component: () => import('@/views/comercial/ReportesComerciales.vue'),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/comercial/sesiones-caja',
    name: 'comercial_sesiones_caja',
    component: () => import('@/views/comercial/ReportesComerciales.vue'),
    meta: {
      requiresAuth: true,
      title: 'Historial de Sesiones y Arqueos de Caja',
    },
  },

  // MÓDULO DE CONTABILIDAD GUBERNAMENTAL E INTEGRADA (LEY 1178 SAFCO)
  {
    path: '/contabilidad/plan-cuentas',
    name: 'contabilidad_plan_cuentas',
    component: () => import('@/views/contabilidad/PlanCuentas.vue'),
    meta: {
      requiresAuth: true,
      title: 'Plan Único de Cuentas',
    },
  },
  {
    path: '/contabilidad/comprobantes',
    name: 'contabilidad_comprobantes',
    component: () => import('@/views/contabilidad/ComprobantesContables.vue'),
    meta: {
      requiresAuth: true,
      title: 'Comprobantes Contables (CI/CE/CD)',
    },
  },
  {
    path: '/contabilidad/interfases',
    name: 'contabilidad_interfases',
    component: () => import('@/views/contabilidad/InterfasesAutomaticas.vue'),
    meta: {
      requiresAuth: true,
      title: 'Consola de Integración Contable',
    },
  },
  {
    path: '/contabilidad/libros',
    name: 'contabilidad_libros',
    component: () => import('@/views/contabilidad/LibroMayorDiario.vue'),
    meta: {
      requiresAuth: true,
      title: 'Libros Diario y Mayor Analítico',
    },
  },
  {
    path: '/contabilidad/estados-financieros',
    name: 'contabilidad_estados_financieros',
    component: () => import('@/views/contabilidad/EstadosFinancieros.vue'),
    meta: {
      requiresAuth: true,
      title: 'Estados Financieros SAFCO',
    },
  },

  // VALIDACIÓN PÚBLICA QR
  {
    path: '/verificar-documento/:codigo?',
    name: 'verificar_documento_publico',
    component: () => import('@/views/correspondencia/VerificarDocumentoPublico.vue'),
    meta: {
      layout: 'blank',
    },
  },
  {
    path: '/verificar-hoja-ruta',
    name: 'verificar_hoja_ruta_publico',
    component: () => import('@/views/correspondencia/VerificarDocumentoPublico.vue'),
    meta: {
      layout: 'blank',
    },
  },

  // PAGES
  {
    path: '/pages/login',
    name: 'pages-login',
    component: () => import('@/views/pages/Login.vue'),
    meta: {
      layout: 'blank',
    },
  },
  {
    path: '/pages/register',
    name: 'pages-register',
    component: () => import('@/views/pages/Register.vue'),
    meta: {
      layout: 'blank',
    },
  },
  {
    path: '/error-404',
    name: 'error-404',
    component: () => import('@/views/pages/Error404.vue'),
    meta: {
      layout: 'blank',
    },
  },
  {
    path: '*',
    redirect: '/error-404',
  },
];

