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
      requiresAuth: true
    },
  },
  {
    path: '/typography',
    name: 'typography',
    component: () => import('@/views/typography/Typography.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/icons',
    name: 'icons',
    component: () => import('@/views/icons/Icons.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/cards',
    name: 'cards',
    component: () => import('@/views/cards/Card.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/simple-table',
    name: 'simple-table',
    component: () => import('@/views/simple-table/SimpleTable.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/form-layouts',
    name: 'form-layouts',
    component: () => import('@/views/form-layouts/FormLayouts.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    component: () => import('@/views/pages/account-settings/AccountSettings.vue'),
    meta: {
      requiresAuth: true
    },
  },

  // MENU DATOS / PARAMETRICAS
  {
    path: '/parametrica',
    name: 'parametrica',
    component: () => import('@/views/parametricas/Parametrica.vue'),
    meta: {
      requiresAuth: true
    },
  },

  // MENU ADMINISTRACION
  {
    path: '/usuarios',
    name: 'usuarios',
    component: () => import('@/views/administrar/Usuario.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/control_acceso',
    name: 'control_acceso',
    component: () => import('@/views/administrar/ControlAcceso.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/admin-menu',
    name: 'admin_menu',
    component: () => import('@/views/administrar/AdminMenu.vue'),
    meta: {
      requiresAuth: true
    },
  },

  // FUTUROS MÓDULOS DE COBRANZAS Y FACTURACIÓN
  // { path: '/cobranzas/lecturas', name: 'cobranzas-lecturas', ... }
  // { path: '/facturacion/emision', name: 'facturacion-emision', ... }

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
