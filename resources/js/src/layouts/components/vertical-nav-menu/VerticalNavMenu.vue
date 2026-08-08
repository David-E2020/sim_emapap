<template>
  <v-navigation-drawer
    :value="isDrawerOpen"
    app
    class="app-navigation-menu"
    :right="$vuetify.rtl"
    @input="val => $emit('update:is-drawer-open', val)"
  >
    <!-- Navigation Header -->
    <div class="vertical-nav-header d-flex items-center pt-5 justify-center">
      <router-link :to="{ name: routeHome }" class="d-flex flex-column align-center text-decoration-none m-0 pb-0" style="max-height: 100px;">
        <v-img
          :src="require('@/assets/images/logos/logoEmapa2.png').default"
          max-height="40%"
          max-width="30%"
          alt="logo"
          contain
          eager
          class="app-logo"
        ></v-img>
        <v-slide-x-transition>
          <h2 class="app-title text--primary">AGUA POTABLE</h2>
        </v-slide-x-transition>
      </router-link>
    </div>
    <!-- Navigation Items -->
    <v-list expand shaped class="vertical-nav-menu-items" v-if="menus">
      <v-list-group :value="false" :prepend-icon="item.icon_mdi" v-for="item in menus" :key="item.order">
        <template v-slot:activator>
          <v-list-item-title>{{ item.label }} </v-list-item-title>
        </template>
        <v-list-item v-for="itemN2 in item.sub_menu" :key="itemN2.order" :to="{ name: itemN2.route }">
          <v-list-item-icon>
            <v-icon v-text="'mdi-minius'"></v-icon>
          </v-list-item-icon>
          <v-list-item-title v-text="itemN2.label" style="font-size: 14px; font-weight: bold;"></v-list-item-title>
          <v-list-item-icon>
            <!-- tamaño igual al texto -->
            <v-icon v-text="itemN2.icon_mdi" style="font-size: 16px; font-weight: bold;"
            ></v-icon>
          </v-list-item-icon>
        </v-list-item>
      </v-list-group>
    </v-list>
    <v-list expand shaped class="vertical-nav-menu-items" v-if="!menus">
      <div class="text-center">
        <v-progress-circular :size="30" color="primary" indeterminate></v-progress-circular>
      </div>
    </v-list>
  </v-navigation-drawer>
</template>
<script>
// eslint-disable-next-line object-curly-newline
import {
  mdiHomeOutline,
  mdiAlphaTBoxOutline,
  mdiEyeOutline,
  mdiCreditCardOutline,
  mdiTable,
  mdiFolderCogOutline,
  mdiFileOutline,
  mdiFormSelect,
  mdiAccountCogOutline,
  mdiAccountCog,
  mdiCart,
  mdiCloudSyncOutline,
  mdiAccountMultiple,
  mdiHomeCity,
  mdiAccountBoxMultiple,
  mdiCrosshairsGps,
  mdiFerry,
  mdiCurrencyUsd,
  mdiChartAreaspline,
  mdiCloudPrintOutline,
  mdiFileDocumentOutline,
} from '@mdi/js'
import NavMenuSectionTitle from './components/NavMenuSectionTitle.vue'
import NavMenuGroup from './components/NavMenuGroup.vue'
import NavMenuLink from './components/NavMenuLink.vue'

export default {
  components: {
    NavMenuSectionTitle,
    NavMenuGroup,
    NavMenuLink,
  },
  props: {
    isDrawerOpen: {
      type: Boolean,
      default: null,
    },
  },
  setup() {
    return {
      icons: {
        mdiHomeOutline,
        mdiAlphaTBoxOutline,
        mdiEyeOutline,
        mdiCreditCardOutline,
        mdiTable,

        mdiFolderCogOutline,
        mdiFileOutline,
        mdiFormSelect,
        mdiAccountCogOutline,
        mdiCart,
        mdiCloudSyncOutline,
        mdiAccountCog,
        mdiAccountMultiple,
        mdiHomeCity,
        mdiAccountBoxMultiple,
        mdiCrosshairsGps,
        mdiFerry,
        mdiCurrencyUsd,
        mdiChartAreaspline,
        mdiCloudPrintOutline,
        mdiFileDocumentOutline,
      },
    }
  },

  data: () => ({
    menus: null,
    user: null,
    routeHome: null,
  }),
  mounted() {
    this.getMenu()
  },

  methods: {
    getMenu() {
      this.routeHome = localStorage.getItem('rute_home')
      this.menus = null
      var user_ = JSON.parse(localStorage.getItem('user'))
      var userId_ = user_.id
      var urlMenu = '/api/usuario/menu-acopio/' + userId_
      axios
        .get(urlMenu)
        .then(response => {
          this.menus = response.data.menus
        })
        .catch(_error => {})
    },

    getUser() {
      this.user = JSON.parse(localStorage.getItem('user'))
    },

 collapseSubItems() {
      this.nav.map((item)=>item.active=false)
 },

  },
}
</script>

<style lang="scss" scoped>
@import '@resources/sass/preset/mixins.scss';

.app-title {
  font-size: 1.25rem;
  font-weight: 700;
  font-stretch: normal;
  font-style: normal;
  line-height: normal;
  letter-spacing: 0.3px;
}

// ? Adjust this `translateX` value to keep logo in center when vertical nav menu is collapsed (Value depends on your logo)
.app-logo {
  transition: all 0.18s ease-in-out;
  .v-navigation-drawer--mini-variant & {
    transform: translateX(-4px);
  }
}

@include theme(app-navigation-menu) using ($material) {
  background-color: map-deep-get($material, 'background');
}

.app-navigation-menu {
  .v-list-item {
    &.vertical-nav-menu-link {
      ::v-deep .v-list-item__icon {
        .v-icon {
          transition: none !important;
        }
      }
    }
  }
}

// You can remove below style
// Upgrade Banner
.app-navigation-menu {
  .upgrade-banner {
    position: absolute;
    bottom: 13px;
    left: 50%;
    transform: translateX(-50%);
  }
}
</style>
