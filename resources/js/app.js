require("./bootstrap");
import '@fontsource/inter';
import '@/plugins/vue-composition-api'
import '@resources/sass/styles/styles.scss'
import vuetify from './src/plugins/vuetify'
import Vue from 'vue'
import App from './src/App.vue'
import Vuex from 'vuex';
import {storage} from './src/store'
import {routes} from './src/routes'
import {autentication} from './src/store_modules/autentication';
import VueRouter from 'vue-router'
Vue.prototype.jQuery = jQuery;

window.moment = require('moment');

Vue.use(Vuex)

Vue.use(VueRouter);
window.iziToast = require('izitoast');//notificaciones
window.Inputmask = require('inputmask');

const router = new VueRouter({
    mode: 'history',
    routes:routes
});

// Autorecuperación inteligente: Si un chunk falla por despliegue nuevo o caché vieja, recargar limpiamente
router.onError(error => {
  if (/Loading( chunk)? (\d+ )?failed/i.test(error.message) || error.name === 'ChunkLoadError') {
    window.location.reload();
  }
});

const store = new Vuex.Store({
  state:{
     siatEnLinea:false,
     loadingProgressLinear:false
  },
    modules:{
        template: storage,
        auth: autentication,
        // dconfirm: confirm,
    }
});
Vue.prototype.$http = axios;

// Función para determinar si el usuario en sesión es Administrador General
function isUserAdmin() {
  try {
    const rol = localStorage.getItem('rol');
    if (['Administrador General', 'Administrador', 'Super Admin'].includes(rol)) return true;

    const rolesStr = localStorage.getItem('roles');
    if (rolesStr) {
      const roles = JSON.parse(rolesStr);
      if (Array.isArray(roles) && roles.some(r => {
        const name = typeof r === 'string' ? r : (r && r.name ? r.name : '');
        return ['Administrador General', 'Administrador', 'Super Admin'].includes(name);
      })) {
        return true;
      }
    }
  } catch (_) {}
  return false;
}

// Evaluación estricta y segura de permisos (Fail-closed)
Vue.prototype.$can = function (permission) {
  if (!permission) return true;
  try {
    if (isUserAdmin()) return true;

    const permissionsStr = localStorage.getItem('permissions');
    if (!permissionsStr) return false;
    const userPermissions = JSON.parse(permissionsStr);
    if (!Array.isArray(userPermissions)) return false;

    if (Array.isArray(permission)) {
      return permission.some(p => userPermissions.includes(p));
    }
    if (typeof permission === 'string') {
      if (permission.includes('|')) {
        return permission.split('|').some(p => userPermissions.includes(p.trim()));
      }
      return userPermissions.includes(permission);
    }
    return false;
  } catch (e) {
    return false;
  }
};

const tokenJWT = localStorage.getItem('token');
if (tokenJWT) {
  Vue.prototype.$http.defaults.headers.common['Authorization'] = 'Bearer ' + tokenJWT;
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + tokenJWT;
}

// Interceptor global para capturar 401 (Sesión vencida) y 403 (Acceso denegado / Cuenta revocada)
axios.interceptors.response.use(
  response => response,
  error => {
    if (error && error.response) {
      if (error.response.status === 401) {
        store.dispatch('auth/logout').finally(() => {
          if (router.currentRoute.path !== '/login') {
            router.push('/login');
          }
        });
      } else if (error.response.status === 403) {
        const msg = (error.response.data && error.response.data.message) || 'No dispone de los privilegios o rol necesarios para realizar esta acción.';
        const isAccessRevoked = msg.includes('inactiva') || msg.includes('sin acceso') || msg.includes('no cuenta con roles') || msg.includes('revocado') || msg.includes('no tiene ningún rol');

        if (isAccessRevoked) {
          if (window.iziToast && typeof window.iziToast.warning === 'function') {
            window.iziToast.warning({
              title: 'Acceso Revocado',
              message: msg,
              position: 'topRight',
              timeout: 6000,
            });
          }
          store.dispatch('auth/logout').finally(() => {
            if (router.currentRoute.path !== '/login') {
              router.push('/login');
            }
          });
        } else {
          if (window.iziToast && typeof window.iziToast.warning === 'function') {
            window.iziToast.warning({
              title: 'Acceso Denegado (403)',
              message: msg,
              position: 'topRight',
              timeout: 5000,
            });
          }
        }
      }
    }
    return Promise.reject(error);
  }
);

// Mapeo exhaustivo de nombres de rutas a permisos Spatie
const ROUTE_PERMISSION_MAP = {
  // 1. Administración
  usuarios: ['admin.usuarios.ver'],
  roles_permisos: ['admin.roles.ver', 'admin.control_acceso.ver'],
  auditoria: ['admin.auditoria.ver'],

  // 2. Datos
  parametrica: ['parametricas.ver', 'parametricas.crear'],
  datos_empresa: ['datos.empresa.gestionar'],
  datos_migrador_respaldos: ['datos.migrador.ejecutar'],

  // 3. Recursos Humanos
  rrhh_personal: ['rrhh.personal.ver'],
  rrhh_organigrama: ['rrhh.organigrama.ver', 'rrhh.organigrama.crear_unidad'],
  rrhh_asistencias: ['rrhh.asistencias.ver'],
  rrhh_solicitudes: ['rrhh.solicitudes.ver', 'rrhh.solicitudes.crear'],
  rrhh_horarios: ['rrhh.horarios.ver', 'rrhh.horarios.crear'],
  rrhh_comisiones_omisiones: ['rrhh.comisiones.ver', 'rrhh.omisiones.crear'],
  rrhh_feriados_cortes: ['rrhh.feriados.ver'],
  rrhh_reportes: ['rrhh.reportes.asistencia', 'rrhh.reportes.refrigerio'],

  // 4. Correspondencia y Trámites
  correspondencia_dashboard: ['correspondencia.dashboard.ver'],
  correspondencia_hojas_ruta: ['correspondencia.hojas_ruta.ver'],
  correspondencia_documentos: ['correspondencia.documentos.ver'],
  correspondencia_firmas: ['correspondencia.documentos.firmar'],
  correspondencia_seguimiento: ['correspondencia.seguimiento.ver'],
  correspondencia_ventanilla: ['correspondencia.ventanilla.recibir'],
  correspondencia_despacho_salida: ['correspondencia.despachos.administrar'],
  correspondencia_solicitudes_ciudadanas: ['correspondencia.solicitudes.administrar'],
  correspondencia_visor_expediente: ['correspondencia.visor.ver'],
  correspondencia_etiquetas: ['correspondencia.etiquetas.administrar'],
  correspondencia_plantillas: ['correspondencia.configuracion.administrar'],
  correspondencia_configuracion: ['correspondencia.configuracion.administrar'],
  correspondencia_permisos: ['correspondencia.permisos.administrar'],
  correspondencia_transferencias: ['correspondencia.transferencias.ejecutar'],

  // 5. Facturación SIAT
  facturacion_caja: ['facturacion.caja.cobrar'],
  facturacion_crear: ['facturacion.facturas.crear'],
  facturacion_bandeja: ['facturacion.facturas.ver'],
  facturacion_contingencias: ['facturacion.contingencias.administrar'],
  facturacion_eventos: ['facturacion.eventos.administrar'],
  facturacion_puntos_venta: ['facturacion.puntos_venta.administrar'],
  facturacion_clientes: ['facturacion.clientes.administrar'],
  facturacion_productos: ['facturacion.catalogos.administrar'],
  facturacion_libro_ventas: ['facturacion.libro_ventas.ver'],

  // 6. Gestión Comercial
  comercial_abonados: ['comercial.abonados.ver'],
  comercial_periodos: ['comercial.periodos.administrar'],
  comercial_lecturas: ['comercial.lecturas.registrar'],
  comercial_sesiones_caja: ['comercial.sesiones_caja.aperturar', 'comercial.sesiones_caja.cerrar', 'comercial.sesiones_caja.supervisar'],
  comercial_convenios: ['comercial.convenios.administrar'],
  comercial_cortes: ['comercial.cortes.administrar'],
  comercial_zonas_calles: ['comercial.zonas_calles.administrar', 'gis.mapa.ver'],
  comercial_tarifas: ['comercial.tarifas.administrar'],
  comercial_reportes: ['comercial.reportes.ver'],
  comercial_aportes: ['comercial.aportes.administrar'],

  // 7. Contabilidad y Finanzas
  contabilidad_plan_cuentas: ['contabilidad.plan_cuentas.ver'],
  contabilidad_comprobantes: ['contabilidad.comprobantes.ver'],
  contabilidad_interfases: ['contabilidad.interfases.procesar'],
  contabilidad_libros: ['contabilidad.libros.ver'],
  contabilidad_estados_financieros: ['contabilidad.estados_financieros.ver'],
};

const PUBLIC_ROUTE_NAMES = [
  'pages-login',
  'pages-register',
  'error-403',
  'error-404',
  'verificar_documento_publico',
  'verificar_hoja_ruta_publico'
];

router.beforeEach((to, from, next) => {
  const isPublic = PUBLIC_ROUTE_NAMES.includes(to.name) || (to.meta && to.meta.requiresAuth === false);
  const isLoggedIn = store.getters['auth/isLoggedIn'] || !!localStorage.getItem('token');

  // 1. Si no está autenticado y la ruta requiere autenticación -> Redirigir al Login limpio (sin ?redirect=%2Fdashboard innecesario)
  if (!isLoggedIn && !isPublic) {
    const isGeneric = to.fullPath === '/' || to.fullPath === '/dashboard' || to.name === 'dashboard';
    const query = (!isGeneric && to.fullPath && to.fullPath !== '/login') ? { redirect: to.fullPath } : {};
    next({ path: '/login', query });
    return;
  }

  // 2. Si ya está logueado e intenta ir a la página de login -> Redirigir al Dashboard
  if (isLoggedIn && (to.path === '/login' || to.name === 'pages-login')) {
    next({ name: 'dashboard' });
    return;
  }

  // 3. Rutas públicas no requieren chequeo de roles/permisos
  if (isPublic) {
    next();
    return;
  }

  // 4. Administrador General tiene acceso total irrestricto
  if (isUserAdmin()) {
    next();
    return;
  }

  // 5. Verificar si el usuario cuenta con roles asignados o rutas permitidas
  let allowedRoutes = [];
  try {
    const raw = localStorage.getItem('allowed_routes');
    if (raw) allowedRoutes = JSON.parse(raw);
  } catch (_) {}

  const userRol = localStorage.getItem('rol');
  let userRolesList = [];
  try {
    const rawRoles = localStorage.getItem('roles');
    if (rawRoles) userRolesList = JSON.parse(rawRoles);
  } catch (_) {}

  const hasNoRoles = (!userRol || userRol === 'Sin Rol' || userRol === 'null' || userRol === 'Usuario') &&
                     (!Array.isArray(userRolesList) || userRolesList.length === 0) &&
                     (!Array.isArray(allowedRoutes) || allowedRoutes.length === 0);

  // Si no tiene ningún rol ni rutas permitidas -> Bloquear acceso completamente, incluso al dashboard
  if (hasNoRoles) {
    if (window.iziToast && typeof window.iziToast.warning === 'function') {
      window.iziToast.warning({
        title: 'Acceso Revocado',
        message: 'Su usuario no cuenta con roles ni permisos asignados en el sistema.',
        position: 'topRight',
        timeout: 4000,
      });
    }
    next({ name: 'error-403', query: { from: to.fullPath } });
    return;
  }

  // 6. Rutas comunes para funcionarios autenticados con rol activo
  if (to.name === 'dashboard' || to.name === 'pages-account-settings') {
    next();
    return;
  }

  // 7. Verificar si el submódulo está expresamente habilitado en los submenús del rol (allowed_routes)
  if (Array.isArray(allowedRoutes) && allowedRoutes.includes(to.name)) {
    next();
    return;
  }

  // 7. Si no está en allowed_routes, verificar si cuenta con permisos Spatie mapeados a la ruta
  const requiredPermissions = ROUTE_PERMISSION_MAP[to.name];
  if (requiredPermissions && Vue.prototype.$can(requiredPermissions)) {
    next();
    return;
  }

  // 8. Si la ruta pertenece a un módulo restringido y no se acreditó autorización -> 403 Forbidden
  if (requiredPermissions || (Array.isArray(allowedRoutes) && allowedRoutes.length > 0)) {
    if (window.iziToast && typeof window.iziToast.warning === 'function') {
      window.iziToast.warning({
        title: 'Acceso Restringido',
        message: 'No cuenta con los privilegios requeridos para ingresar a este módulo institucional.',
        position: 'topRight',
        timeout: 4000,
      });
    }
    next({ name: 'error-403', query: { from: to.fullPath } });
    return;
  }

  next();
});

Vue.config.productionTip = false;
Vue.config.devtools = false;
Vue.directive('decimal', {
    inserted: function (el) {
        Inputmask({
            alias: "decimal",
            groupSeparator: "",
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            placeholder: "0",
            max: 10000000000
        }).mask(el);
    }
});

const app = new Vue({
    store,
    vuetify,
    data: {
        themeColor: '#000',
    },
    router,
    render: h => h(App),
}).$mount('#app')