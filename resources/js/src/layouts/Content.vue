<template>
  <div>
    <v-app> 
      <vertical-nav-menu :is-drawer-open.sync="isDrawerOpen"></vertical-nav-menu>

      <v-app-bar app elevation="1">

        <v-progress-linear :active="$store.state.loadingProgressLinear"
          :indeterminate="$store.state.loadingProgressLinear" absolute top color="primary"></v-progress-linear>

        <div class="boxed-container w-full">
          <div class="d-flex align-center mx-6">
            <!-- Left Content -->
            <v-app-bar-nav-icon class="d-block me-2" @click="isDrawerOpen = !isDrawerOpen"></v-app-bar-nav-icon>
            <v-spacer></v-spacer>
            <v-icon left v-fullscreen > mdi mdi-monitor-screenshot </v-icon>
            <theme-switcher class="ma-2"></theme-switcher>
            <v-tooltip v-if="puntoVentaUser">
              <template v-slot:activator="{ on: tooltip }">
                <v-chip class="ma-2" :loading="!comex" label v-on="{ ...tooltip }">
                  <v-icon left>
                    mdi-storefront-outline
                  </v-icon>
                  PLANTA {{puntoVentaUser.planta[0].codigo}}
                </v-chip>
              </template>
              <span>
                {{puntoVentaUser.planta[0].nombre}}
              </span>
            </v-tooltip>
            <v-chip class="ma-2" :loading="!rol" label>
              <v-icon left>
                mdi-shield-check
              </v-icon>
              {{ rol }}
            </v-chip>
            <app-bar-user-menu></app-bar-user-menu>
          </div>
        </div>
      </v-app-bar>
      <v-main>
        <div class="app-content-container boxed-container">
          <slot></slot>
        </div>
        <br />
      </v-main>
      <v-footer app inset absolute height="56" class="px-0">
        <div class="boxed-container w-full">
          <div class="mx-6 d-flex justify-space-between">
            <span>
              &copy; 2024
              <a class="text-decoration-none" target="_blank">SIE - GPI</a></span>
            <span class="d-sm-inline d-none">
              <a class="me-6 text--secondary text-decoration-none">Unidad de Tecnologias</a>
              <a class="text--secondary text-decoration-none">Software Libre L11.0 hasta 2027</a>
            </span>
          </div>
        </div>
      </v-footer>
    </v-app>
    <template>
      <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
        {{ snackbar.text }}
        <template v-slot:action="{ attrs }">
          <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
        </template>
      </v-snackbar>
    </template>
  </div>
</template>

<script>
import VueFullscreen from 'vue-fullscreen';
import { ref } from "@vue/composition-api";
import { mdiMagnify, mdiBellOutline, mdiGithub } from "@mdi/js";
import VerticalNavMenu from "./components/vertical-nav-menu/VerticalNavMenu.vue";
import ThemeSwitcher from "./components/ThemeSwitcher.vue";
import AppBarUserMenu from "./components/AppBarUserMenu.vue";
import Vue from 'vue';
Vue.use(VueFullscreen);

export default {
  components: {
    VerticalNavMenu,
    ThemeSwitcher,
    AppBarUserMenu,
  },
  setup() {
    const isDrawerOpen = ref(null);

    return {
      isDrawerOpen,

      // Icons
      icons: {
        mdiMagnify,
        mdiBellOutline,
        mdiGithub,
      },
    };
  },

  data: () => ({
    loadingVerifica: false,
    comex: null,
    puntoVentaUser: null,
    loadingVerificaCufd: false,
    loadingSincronizar: false,
    rol: "",
    snackbar: {
      status: false,
      text: "",
    },
    siatEnLinea: false,
    siatSincronizar: false,
    siatcufdValido: false,
    puntVentaAsignado: true,

    dataPuntoVenta: null,
  }),

  mounted() {
    this.getData();
  },
  methods: {
    getData() {
      this.loadingVerificaCufd = true;
      this.rol = localStorage.getItem("rol");

      axios
        .get("api/planta_asignada")
        .then((response) => {
          var datResp = response.data;
          this.comex = datResp.comex;
          if (datResp.puntoventaUser) {
            this.puntoVentaUser = datResp.puntoventaUser;
            this.puntVentaAsignado = true;
            this.dataPuntoVenta = datResp.puntoventaUser.planta;
          }
        })
        .catch((error) => {
          this.loadingVerificaCufd = false;
          this.puntVentaAsignado = false;
          this.snackbar = {
            status: true,
            text: error.response.data.message,
            color: "primary",
          };
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.v-app-bar ::v-deep {
  .v-toolbar__content {
    padding: 0;
    .app-bar-search {
      .v-input__slot {
        padding-left: 18px;
      }
    }
  }
}

</style>
