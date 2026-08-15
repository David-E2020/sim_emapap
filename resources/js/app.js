require("./bootstrap");
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
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import colors from 'vuetify/lib/util/colors'
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

Vue.prototype.$can = function (permission) {
  try {
    const permissionsStr = localStorage.getItem('permissions');
    if (!permissionsStr) return true;
    const userPermissions = JSON.parse(permissionsStr);
    if (!Array.isArray(userPermissions)) return true;
    return userPermissions.includes(permission) || userPermissions.includes('SIGP') || userPermissions.includes('admin.usuarios.ver');
  } catch (e) {
    return true;
  }
};

const tokenJWT = localStorage.getItem('token')
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
if (tokenJWT) {
  Vue.prototype.$http.defaults.headers.common['Authorization'] = tokenJWT
}
// Import Bootstrap and BootstrapVue CSS files (order is important)


Vue.use(BootstrapVue);

Vue.use(vuetify, {
    theme: {
        dark: true,
         dark: {
          primary: colors.blue.darken2,
          accent: colors.grey.darken3,
          secondary: colors.amber.darken3,
          background: '#34358e'
        },
        light: {
         primary: '#3f51b5',
         secondary: '#b0bec5',
         accent: '#8c9eff',
         error: '#b71c1c',
        }
    }
  })



router.beforeEach((to, from, next) => {
    if(to.matched.some(record => record.meta.requiresAuth)) {
      if (store.getters['auth/isLoggedIn']) {
        next()
        return
      }
      next('/pages/login')
    } else {
      next()
    }
  });

Vue.config.productionTip = false;
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
        userEmail: 'primo@gmail.com',
        userPassword: '123456'
    },
    router,
    render: h => h(App),				
    mounted()
    {
        axios.get('api/auth/user_check')
             .then(response=>{
                if (response.success=="true") {
                }else if(response.success=="false"){
                  location.href = 'login';
                }
             })
    },
}).$mount('#app')