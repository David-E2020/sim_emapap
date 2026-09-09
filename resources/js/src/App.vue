<template>
  <component :is="resolveLayout">
    <router-view></router-view>
    <upgrade-to-pro></upgrade-to-pro>
  </component>
</template>

<script>
import { computed } from '@vue/composition-api'
import { useRouter } from '@/utils'
import LayoutBlank from '@/layouts/Blank.vue'
import LayoutContent from '@/layouts/Content.vue'
import UpgradeToPro from './components/UpgradeToPro.vue'
import Multiselect from 'vue-multiselect'
import Vue from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

Vue.use(VueSweetalert2);

Vue.filter('capitalize', function (value) {
            if (! value) return ''; // Cuando el valor pasado es nulo, se devuelve una cadena vacía
            value = value.toString (); // Convertir el valor pasado a tipo String
            // Convierta la primera y segunda letras de la cadena a mayúsculas y concatene las siguientes cadenas
            return  PrimeraLetraMayuscula(value.toLowerCase());// .toUpperCase () + value.charAt (1) .toUpperCase () + value.slice (2) + 'filtro global'
});

Vue.filter('totalStock', function (existencias) {
    var sum = 0;
    existencias.forEach(element => {
      sum = sum + element.stock;
    });
    return sum;
});

Vue.filter('totalCajas', function (detalles) {

    var sum = 0;
    detalles.forEach(element => {
      sum = sum + element.existencia.stock;
    });
    return sum;
});

Vue.filter('roundDecimal', function (val) {
    
  var resp = 0;

    try {
      resp = parseFloat(val).toFixed(2);
    } catch (error) {
      
    }


    return resp;
});

function PrimeraLetraMayuscula(string){
  return string.charAt(0).toUpperCase() + string.slice(1);
}

export default {
  name: 'App',

  data:()=>({
        drawer: true
  }),


  components: {
    LayoutBlank,
    LayoutContent,
    UpgradeToPro,
  },
  setup() {
    const { route } = useRouter()
    const resolveLayout = computed(() => {
      // Handles initial route
      if (route.value.name === null) return 'layout-blank'

      if (route.value.meta.layout === 'blank') return 'layout-blank'

      return 'layout-content'
    })

    return {
      resolveLayout,
    }
  },
  computed:{
      
      getToken(){
        return this.$store.state.auth.token;
      }
  },
  watch: {
    '$vuetify.theme.dark'(val) {
      localStorage.setItem('theme_dark', val);
    },
  },
  created(){
    const savedDark = localStorage.getItem('theme_dark');
    if (savedDark !== null) {
      this.$vuetify.theme.dark = savedDark === 'true';
    }
    axios.defaults.headers.common['Authorization'] = 'Bearer '+this.getToken;
    axios.interceptors.response.use(undefined,(err) => {
    return new Promise( (resolve, reject) => {
        if (err.response.status === 401) {
        // if you ever get an unauthorized, logout the user
        this.$store.dispatch('auth/logout')
        .then(() => this.$router.push('/pages/login'))
        // you can also redirect to /login if needed !
        }
        throw err;
    });
    });
  }
  
}
</script>
