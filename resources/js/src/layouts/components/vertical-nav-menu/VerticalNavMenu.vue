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

    <!-- Subtle divider below header -->
    <div class="nav-header-divider mx-4 my-2"></div>

    <!-- Navigation Items -->
    <v-list expand shaped class="vertical-nav-menu-items" v-if="menus">
      <v-list-group :value="false" :prepend-icon="item.icon_mdi" v-for="(item, index) in menus" :key="'group-' + (item.id || item.label || index)" class="nav-group-item">
        <template v-slot:activator>
          <v-list-item-title class="group-title font-weight-medium">{{ item.label }}</v-list-item-title>
        </template>
        
        <div class="submenu-container pl-2">
          <v-list-item
            v-for="(itemN2, index2) in item.sub_menu"
            :key="'sub-' + (itemN2.id || itemN2.route || itemN2.label || index2)"
            :to="{ name: itemN2.route }"
            class="submenu-item my-1 rounded-lg"
            active-class="submenu-item--active"
          >
            <!-- Left subtle guide bullet -->
            <v-list-item-icon class="me-2 my-auto submenu-bullet-icon">
              <span class="submenu-bullet"></span>
            </v-list-item-icon>

            <v-list-item-title class="submenu-title text-body-2 font-weight-medium">{{ itemN2.label }}</v-list-item-title>
            
            <!-- Right action icon -->
            <v-list-item-icon class="my-auto submenu-right-icon">
              <v-icon v-text="itemN2.icon_mdi" small class="submenu-icon-inner"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </div>
      </v-list-group>
    </v-list>
    <v-list expand shaped class="vertical-nav-menu-items" v-if="!menus">
      <div class="text-center py-6">
        <v-progress-circular :size="28" width="3" color="primary" indeterminate></v-progress-circular>
      </div>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  props: {
    isDrawerOpen: {
      type: Boolean,
      default: null,
    },
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
      this.routeHome = localStorage.getItem('rute_home') || 'dashboard'
      this.menus = null
      const userStored = localStorage.getItem('user')
      if (!userStored) return
      const user_ = JSON.parse(userStored)
      const userId_ = user_.id
      const urlMenu = '/api/usuario/menu-acopio/' + userId_
      axios
        .get(urlMenu)
        .then(response => {
          this.menus = response.data.menus
        })
        .catch(_error => {})
    },
    getUser() {
      try {
        this.user = JSON.parse(localStorage.getItem('user'))
      } catch (e) {
        this.user = null
      }
    },
  },
}
</script>

<style lang="scss" scoped>
@import '@resources/sass/preset/mixins.scss';

.app-title {
  font-size: 1.2rem;
  font-weight: 700;
  letter-spacing: 0.4px;
}

.app-logo {
  transition: all 0.2s ease-in-out;
  .v-navigation-drawer--mini-variant & {
    transform: translateX(-4px);
  }
}

.nav-header-divider {
  height: 1px;
  background: linear-gradient(90deg, rgba(145, 85, 253, 0.05) 0%, rgba(145, 85, 253, 0.25) 50%, rgba(145, 85, 253, 0.05) 100%);
  margin-bottom: 8px;
}

.nav-group-item {
  margin-bottom: 4px;
}

.group-title {
  font-size: 0.93rem;
  letter-spacing: 0.2px;
}

.submenu-container {
  position: relative;
  border-left: 1.5px dashed rgba(145, 85, 253, 0.2);
  margin-left: 28px;
  padding-left: 6px !important;
}

.submenu-item {
  min-height: 38px !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 0 12px !important;

  .submenu-bullet-icon {
    min-width: 14px !important;
    margin-right: 8px !important;
  }

  .submenu-bullet {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: rgba(145, 85, 253, 0.35);
    transition: all 0.2s ease;
  }

  .submenu-title {
    font-size: 0.875rem !important;
    color: rgba(94, 86, 105, 0.87);
    transition: color 0.2s ease;
  }

  .submenu-right-icon {
    min-width: 24px !important;
    opacity: 0.75;
    transition: transform 0.2s ease, opacity 0.2s ease;
  }

  &:hover {
    background-color: rgba(145, 85, 253, 0.06) !important;
    
    .submenu-bullet {
      background-color: var(--v-primary-base, #9155fd);
      transform: scale(1.3);
    }

    .submenu-right-icon {
      opacity: 1;
      transform: translateX(2px);
    }
  }

  &--active {
    background: linear-gradient(98deg, rgba(145, 85, 253, 0.16), rgba(145, 85, 253, 0.06) 94%) !important;

    .submenu-bullet {
      background-color: var(--v-primary-base, #9155fd);
      box-shadow: 0 0 6px rgba(145, 85, 253, 0.6);
    }

    .submenu-title {
      color: var(--v-primary-base, #9155fd) !important;
      font-weight: 600 !important;
    }

    .submenu-right-icon {
      opacity: 1;
      .submenu-icon-inner {
        color: var(--v-primary-base, #9155fd) !important;
      }
    }
  }
}

@include theme(app-navigation-menu) using ($material) {
  background-color: map-deep-get($material, 'background');
}
</style>
