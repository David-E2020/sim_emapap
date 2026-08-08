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
  {
    path: '/parametrica_planta',
    name: 'parametrica_planta',
    component: () => import('@/views/parametricas/Planta.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/parametrica_almacen',
    name: 'parametrica_almacen',
    component: () => import('@/views/parametricas/Almacen.vue'),
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
    path: '/asignacion_regional',
    name: 'asignacion_regional',
    component: () => import('@/views/administrar/AsignacionPlanta.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/asignacion_punto',
    name: 'asignacion_punto',
    component: () => import('@/views/administrar/AsignacionPunto.vue'),
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

  // RECURSOS HUMANOS
  {
    path: '/employee',
    name: 'employee',
    component: () => import('@/views/rrhh/employee/Index.vue'),
    meta: {
      requiresAuth: true
    },
  },
  {
    path: '/employee_info',
    name: 'employee_info',
    component: () => import('@/views/rrhh/employee/Show.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/datos_rrhh',
    name: 'datos_rrhh',
    component: () => import('@/views/rrhh/datos/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/biometric',
    name: 'biometric',
    component: () => import('@/views/rrhh/biometric/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/position',
    name: 'position',
    component: () => import('@/views/rrhh/position/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/city',
    name: 'city',
    component: () => import('@/views/rrhh/city/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/country',
    name: 'country',
    component: () => import('@/views/rrhh/country/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/document_type',
    name: 'document_type',
    component: () => import('@/views/rrhh/document_type/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/contract_type',
    name: 'contract_type',
    component: () => import('@/views/rrhh/contract_type/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/contract_modality',
    name: 'contract_modality',
    component: () => import('@/views/rrhh/contract_modality/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/management',
    name: 'management',
    component: () => import('@/views/rrhh/management/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/unity',
    name: 'unity',
    component: () => import('@/views/rrhh/unity/Index.vue'),
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/contribution',
    name: 'contribution',
    component: () => import('@/views/rrhh/contribution/Index.vue'),
    meta: {
      requiresAuth: true,
    }
  },
  {
    path: '/my_request',
    name: 'my_request',
    component: () => import('@/views/rrhh/my_request/Index.vue'),
    meta: {
      requiresAuth: true,
    }
  },
  {
    path: '/report',
    name: 'report',
    component: () => import('@/views/rrhh/attendance/Report.vue'),
    meta: {
      requiresAuth: true,
    }
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
