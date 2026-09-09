<template>
  <div>
    <v-app> 
      <vertical-nav-menu :is-drawer-open.sync="isDrawerOpen"></vertical-nav-menu>

      <v-app-bar
        app
        elevation="0"
        height="62"
        class="app-top-bar"
        :class="{ 'app-top-bar--full': isDrawerOpen === false }"
      >

        <v-progress-linear :active="$store.state.loadingProgressLinear"
          :indeterminate="$store.state.loadingProgressLinear" absolute top color="primary"></v-progress-linear>

        <div class="boxed-container w-full">
          <div class="d-flex align-center mx-3">
            <!-- Left Content -->
            <v-app-bar-nav-icon class="d-block me-2 topbar-icon-btn rounded-lg" @click="isDrawerOpen = !isDrawerOpen"></v-app-bar-nav-icon>
            <v-spacer></v-spacer>
            <v-btn icon small class="mx-1 topbar-icon-btn" v-fullscreen title="Pantalla completa">
              <v-icon small>mdi-monitor-screenshot</v-icon>
            </v-btn>
            <theme-switcher class="ma-1"></theme-switcher>
            <v-tooltip v-if="puntoVentaUser">
              <template v-slot:activator="{ on: tooltip }">
                <v-chip class="mx-2 top-bar-chip font-weight-medium" :loading="!comex" outlined color="primary" small v-on="{ ...tooltip }">
                  <v-icon left small>
                    mdi-storefront-outline
                  </v-icon>
                  PLANTA {{puntoVentaUser.planta[0].codigo}}
                </v-chip>
              </template>
              <span>
                {{puntoVentaUser.planta[0].nombre}}
              </span>
            </v-tooltip>
            <v-chip class="mx-2 top-bar-chip font-weight-medium" :loading="!rol" outlined color="primary" small>
              <v-icon left small>
                mdi-shield-check
              </v-icon>
              {{ rol }}
            </v-chip>
            <app-bar-user-menu></app-bar-user-menu>
          </div>
        </div>
      </v-app-bar>
      <v-main :class="{ 'v-main--full': isDrawerOpen === false }">
        <div class="app-content-container pt-1 pb-4">
          <slot></slot>
        </div>
        <br />
      </v-main>
      <v-footer app inset absolute height="48" class="px-0 app-footer">
        <div class="boxed-container w-full">
          <div class="mx-4 d-flex justify-space-between align-center">
            <span class="text-caption">
              &copy; 2024
              <a class="font-weight-medium text-decoration-none footer-link" target="_blank">SIE - GPI</a></span>
            <span class="d-sm-inline d-none text-caption">
              <a class="me-6 text-decoration-none footer-link">Unidad de Tecnologías</a>
              <a class="text-decoration-none footer-link">Software Libre L11.0 hasta 2027</a>
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
    };
  },

  data: () => ({
    comex: null,
    puntoVentaUser: null,
    loadingVerificaCufd: false,
    rol: "",
    snackbar: {
      status: false,
      text: "",
    },
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
/* Top Bar Styling - Modern Floating Island Card */
.app-top-bar {
  top: 14px !important;
  height: 62px !important;
  border-radius: 14px !important;
  left: 14px !important;
  right: 14px !important;
  width: calc(100% - 28px) !important;
  max-width: calc(100% - 28px) !important;
  z-index: 6 !important;
  transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1), width 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;

  @media (min-width: 1264px) {
    &:not(.app-top-bar--full) {
      left: calc(290px + 28px) !important;
      width: calc(100% - 290px - 42px) !important;
      max-width: calc(100% - 290px - 42px) !important;
    }
  }
}

.theme--light .app-top-bar,
.theme--light.app-top-bar {
  background-color: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(15, 23, 42, 0.08) !important;
  box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.02) !important;

  .topbar-icon-btn {
    color: #475569 !important;
    &:hover {
      background-color: rgba(15, 23, 42, 0.05) !important;
      color: #0f172a !important;
    }
  }

  .top-bar-chip {
    background-color: rgba(37, 99, 235, 0.07) !important;
    border: 1px solid rgba(37, 99, 235, 0.2) !important;
    color: #1d4ed8 !important;
    border-radius: 20px !important;

    ::v-deep .v-icon {
      color: #2563eb !important;
    }
  }
}

.theme--dark .app-top-bar,
.theme--dark.app-top-bar {
  background-color: rgba(22, 23, 29, 0.95) !important;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35) !important;

  .topbar-icon-btn {
    color: #94a3b8 !important;
    &:hover {
      background-color: rgba(255, 255, 255, 0.08) !important;
      color: #f8fafc !important;
    }
  }

  .top-bar-chip {
    background-color: rgba(59, 130, 246, 0.12) !important;
    border: 1px solid rgba(59, 130, 246, 0.28) !important;
    color: #93c5fd !important;
    border-radius: 20px !important;

    ::v-deep .v-icon {
      color: #60a5fa !important;
    }
  }
}

/* Main Content Area Spacing & Alignment with Floating Island Topbar */
.v-main {
  padding-top: calc(62px + 24px) !important;
  transition: padding-left 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;

  @media (min-width: 1264px) {
    &:not(.v-main--full) {
      padding-left: calc(290px + 14px) !important;
    }
  }
}

.app-content-container {
  max-width: 100%;
  padding-left: 14px !important;
  padding-right: 14px !important;
}

/* Footer Styling - Minimal Canvas Style */
.app-footer {
  background-color: transparent !important;
  border-top: none !important;

  .footer-link {
    transition: color 0.2s ease;
  }
}

.theme--light .app-footer,
.theme--light.app-footer {
  color: #64748b !important;

  .footer-link {
    color: #2563eb !important;
    &:hover {
      color: #1d4ed8 !important;
    }
  }
}

.theme--dark .app-footer,
.theme--dark.app-footer {
  color: #94a3b8 !important;

  .footer-link {
    color: #60a5fa !important;
    &:hover {
      color: #93c5fd !important;
    }
  }
}

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
