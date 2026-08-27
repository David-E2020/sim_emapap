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
    path: '*',
    redirect: 'error-404',
  },
];
