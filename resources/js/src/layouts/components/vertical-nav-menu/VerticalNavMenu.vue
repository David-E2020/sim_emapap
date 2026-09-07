<template>
  <v-navigation-drawer
    :value="isDrawerOpen"
    app
    width="295"
    class="app-navigation-menu"
    :right="$vuetify.rtl"
    @input="val => $emit('update:is-drawer-open', val)"
  >
    <!-- Navigation Header -->
    <div class="vertical-nav-header d-flex items-center pt-4 pb-2 justify-center">
      <router-link :to="{ name: routeHome }" class="d-flex flex-column align-center text-decoration-none m-0 pb-0" style="max-height: 100px;">
        <v-img
          :src="require('@/assets/images/logos/logoEmapa2.png').default"
          max-height="40%"
          max-width="32%"
          alt="logo"
          contain
          eager
          class="app-logo"
        ></v-img>
        <v-slide-x-transition>
          <h2 class="app-title text--primary mt-1">AGUA POTABLE</h2>
        </v-slide-x-transition>
      </router-link>
    </div>

    <!-- Subtle divider below header -->
    <div class="nav-header-divider mx-4 my-2"></div>

    <!-- Navigation Items -->
    <v-list expand shaped class="vertical-nav-menu-items px-2" v-if="menus">
      <v-list-group
        v-model="item.isOpen"
        :prepend-icon="item.icon_mdi"
        v-for="(item, index) in menus"
        :key="'group-' + (item.id || item.label || index)"
        class="nav-group-item mx-1 my-1"
        :class="{ 'group-has-active-child': isGroupActive(item) }"
      >
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
    <v-list expand shaped class="vertical-nav-menu-items px-2" v-if="!menus">
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
  watch: {
    $route() {
      this.syncActiveGroup()
    },
  },
  mounted() {
    this.getMenu()
  },
  methods: {
    isGroupActive(item) {
      if (!item || !item.sub_menu || !Array.isArray(item.sub_menu)) return false
      const currentRouteName = this.$route.name
      const currentPath = this.$route.path
      return item.sub_menu.some(sub => {
        if (!sub || !sub.route) return false
        if (sub.route === currentRouteName) return true
        if (currentPath && (sub.route === currentPath || currentPath.includes(sub.route.replace(/_/g, '/')))) return true
        return false
      })
    },
    syncActiveGroup() {
      if (!this.menus) return
      this.menus.forEach(item => {
        if (this.isGroupActive(item)) {
          this.$set(item, 'isOpen', true)
        }
      })
    },
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
          const rawMenus = response.data.menus || []
          this.menus = rawMenus.map(item => ({
            ...item,
            isOpen: this.isGroupActive(item),
          }))
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
  background: linear-gradient(90deg, rgba(167, 139, 250, 0.05) 0%, rgba(167, 139, 250, 0.22) 50%, rgba(167, 139, 250, 0.05) 100%);
  margin-bottom: 8px;
}

.nav-group-item {
  margin-bottom: 4px;
  border-radius: 10px;

  &.group-has-active-child:not(.v-list-group--active) {
    ::v-deep .v-list-group__header {
      background-color: rgba(167, 139, 250, 0.08);

      .group-title {
        color: #9E77ED !important;
        font-weight: 600;
      }

      .v-list-item__icon .v-icon {
        color: #9E77ED !important;
      }
    }
  }

  ::v-deep .v-list-group__header {
    border-radius: 10px;
    min-height: 42px;
    padding: 0 10px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);

    &:hover {
      background-color: rgba(167, 139, 250, 0.08) !important;
    }

    .v-list-item__icon {
      margin-top: auto;
      margin-bottom: auto;
      margin-right: 10px !important;

      .v-icon {
        font-size: 1.25rem;
        transition: color 0.2s ease;
      }
    }

    .v-list-group__header__append-icon {
      margin-left: 4px !important;
      min-width: 20px !important;
    }
  }

  &.v-list-group--active {
    ::v-deep .v-list-group__header {
      background-color: rgba(167, 139, 250, 0.1) !important;

      .group-title {
        color: #9E77ED !important;
        font-weight: 600 !important;
      }

      .v-list-item__icon .v-icon {
        color: #9E77ED !important;
      }
    }
  }
}

.group-title {
  font-size: 0.92rem;
  letter-spacing: 0.2px;
}

.submenu-container {
  position: relative;
  border-left: 1.5px dashed rgba(167, 139, 250, 0.22);
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
    background-color: rgba(167, 139, 250, 0.35);
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
    background-color: rgba(167, 139, 250, 0.06) !important;
    
    .submenu-bullet {
      background-color: #9E77ED;
      transform: scale(1.3);
    }

    .submenu-right-icon {
      opacity: 1;
      transform: translateX(2px);
    }
  }

  &--active {
    background: linear-gradient(98deg, rgba(167, 139, 250, 0.14), rgba(167, 139, 250, 0.04) 94%) !important;

    .submenu-bullet {
      background-color: #9E77ED;
      box-shadow: 0 0 6px rgba(167, 139, 250, 0.45);
    }

    .submenu-title {
      color: #9E77ED !important;
      font-weight: 600 !important;
    }

    .submenu-right-icon {
      opacity: 1;
      .submenu-icon-inner {
        color: #9E77ED !important;
      }
    }
  }
}

@include theme(app-navigation-menu) using ($material) {
  background-color: map-deep-get($material, 'background');
  box-shadow: 2px 0 16px rgba(0, 0, 0, 0.035) !important;
  border-right: 1px solid rgba(94, 86, 105, 0.08) !important;
}
</style>
