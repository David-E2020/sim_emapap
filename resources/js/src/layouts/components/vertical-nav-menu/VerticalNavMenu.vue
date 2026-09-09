<template>
  <v-navigation-drawer
    :value="isDrawerOpen"
    app
    width="290"
    class="app-navigation-menu"
    :right="$vuetify.rtl"
    @input="val => $emit('update:is-drawer-open', val)"
  >
    <!-- Navigation Header -->
    <div class="vertical-nav-header d-flex flex-column align-center pt-5 pb-3 px-4 justify-center">
      <router-link :to="{ name: routeHome || 'dashboard' }" class="d-flex flex-column align-center text-decoration-none w-full">
        <div class="brand-logo-container">
          <img
            :src="require('@/assets/images/logos/logoEmapa2.png').default"
            alt="EMAPA"
            class="brand-logo-img"
          />
        </div>
        <div class="brand-title-wrap mt-2 text-center">
          <h2 class="brand-name">EMAPA</h2>
          <span class="brand-badge-system">AGUA POTABLE · SIE</span>
        </div>
      </router-link>
    </div>

    <!-- Subtle divider below header -->
    <div class="nav-header-divider mx-4 my-2"></div>

    <!-- Navigation Items -->
    <v-list expand shaped class="vertical-nav-menu-items px-3" v-if="menus && menus.length">
      <v-list-group
        v-model="item.isOpen"
        :prepend-icon="item.icon_mdi"
        v-for="(item, index) in menus"
        :key="'group-' + (item.id || item.label || index)"
        class="nav-group-item my-1"
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
            <!-- Left icon aligned with parent navigation -->
            <v-list-item-icon class="me-3 my-auto submenu-icon-wrap">
              <v-icon small class="submenu-icon-inner">{{ itemN2.icon_mdi || 'mdi-circle-small' }}</v-icon>
            </v-list-item-icon>

            <v-list-item-title class="submenu-title text-body-2 font-weight-medium">{{ itemN2.label }}</v-list-item-title>
          </v-list-item>
        </div>
      </v-list-group>
    </v-list>
    <div class="text-center py-6" v-else-if="!menus">
      <v-progress-circular :size="28" width="3" color="primary" indeterminate></v-progress-circular>
    </div>
    <div class="text-center py-6 px-4" v-else>
      <span class="text-caption text-secondary d-block mb-2">No se encontraron menús</span>
      <v-btn small text color="primary" @click="getMenu()">Reintentar</v-btn>
    </div>
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
      if (!this.menus || !Array.isArray(this.menus)) return
      this.menus.forEach(group => {
        if (this.isGroupActive(group)) {
          this.$set(group, 'isOpen', true)
        }
      })
    },
    setMenuList(rawMenus) {
      this.menus = (rawMenus || []).map(item => ({
        ...item,
        isOpen: this.isGroupActive(item),
      }))
      this.$nextTick(() => {
        this.syncActiveGroup()
      })
    },
    getMenu() {
      this.routeHome = localStorage.getItem('rute_home') || 'dashboard'
      this.menus = null
      let user_ = null
      try {
        user_ = JSON.parse(localStorage.getItem('user'))
      } catch (e) {
        user_ = null
      }
      const userId_ = user_ && user_.id ? user_.id : 1
      const urlMenu = '/api/usuario/menu-acopio/' + userId_
      axios
        .get(urlMenu)
        .then(response => {
          this.setMenuList(response.data && response.data.menus)
        })
        .catch(err => {
          console.warn('Fallback a /api/menu_usuario por:', err)
          axios
            .get('/api/menu_usuario')
            .then(res => {
              this.setMenuList(res.data && res.data.menus)
            })
            .catch(err2 => {
              console.error('Error al cargar menús:', err2)
              this.menus = []
            })
        })
    },
  },
}
</script>

<style lang="scss" scoped>
@import '@resources/sass/preset/mixins.scss';

/* Brand Header */
.brand-logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.brand-logo-img {
  height: 42px;
  width: auto;
  max-width: 170px;
  object-fit: contain;
  transition: transform 0.2s ease;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
}

.brand-title-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.brand-name {
  font-size: 1.125rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  margin: 0;
  line-height: 1.2;
}

.brand-badge-system {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-top: 3px;
  padding: 2px 8px;
  border-radius: 12px;
}

/* Nav Divider */
.nav-header-divider {
  height: 1px;
  margin-bottom: 10px;
}

/* Menu Groups */
.nav-group-item {
  margin-bottom: 4px;
  border-radius: 10px;

  ::v-deep .v-list-group__header {
    border-radius: 10px;
    min-height: 42px;
    padding: 0 12px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);

    .v-list-item__icon {
      margin-top: auto;
      margin-bottom: auto;
      margin-right: 12px !important;

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
}

.group-title {
  font-size: 0.875rem;
  letter-spacing: 0.01em;
}

/* Submenu container */
.submenu-container {
  position: relative;
  margin-left: 28px;
  padding-left: 6px !important;
}

.submenu-item {
  min-height: 38px !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 0 12px !important;

  .submenu-icon-wrap {
    min-width: 22px !important;
    margin-right: 10px !important;
    opacity: 0.8;
    transition: all 0.2s ease;

    .submenu-icon-inner {
      font-size: 1.15rem !important;
      transition: color 0.2s ease, transform 0.2s ease;
    }
  }

  .submenu-title {
    font-size: 0.84rem !important;
    transition: color 0.2s ease;
    white-space: normal !important;
    line-height: 1.25 !important;
  }

  &:hover {
    .submenu-icon-wrap {
      opacity: 1;
      transform: translateX(2px);
    }
  }

  &--active {
    .submenu-icon-wrap {
      opacity: 1;
    }
  }
}

.app-navigation-menu {
  top: 14px !important;
  left: 14px !important;
  height: calc(100vh - 28px) !important;
  max-height: calc(100vh - 28px) !important;
  border-radius: 14px !important;
  overflow: hidden !important;
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), width 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;

  @media (max-width: 1263.98px) {
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border-radius: 0 14px 14px 0 !important;
  }
}

/* Light Theme Specific Styling for Sidebar */
.theme--light .app-navigation-menu,
.theme--light.app-navigation-menu {
  background-color: #ffffff !important;
  border: 1px solid rgba(15, 23, 42, 0.08) !important;
  box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.02) !important;

  .brand-name {
    color: #0f172a;
  }

  .brand-badge-system {
    color: #475569;
    background-color: #f1f5f9;
    border: 1px solid rgba(15, 23, 42, 0.06);
  }

  .nav-header-divider {
    background: linear-gradient(90deg, transparent 0%, rgba(15, 23, 42, 0.08) 50%, transparent 100%);
  }

  .nav-group-item {
    ::v-deep .v-list-group__header {
      &:hover {
        background-color: rgba(37, 99, 235, 0.05) !important;
      }
    }

    &.group-has-active-child:not(.v-list-group--active) {
      ::v-deep .v-list-group__header {
        background-color: rgba(37, 99, 235, 0.06);

        .group-title {
          color: #2563eb !important;
          font-weight: 600;
        }

        .v-list-item__icon .v-icon {
          color: #2563eb !important;
        }
      }
    }

    &.v-list-group--active {
      ::v-deep .v-list-group__header {
        background-color: rgba(37, 99, 235, 0.08) !important;

        .group-title {
          color: #2563eb !important;
          font-weight: 600 !important;
        }

        .v-list-item__icon .v-icon {
          color: #2563eb !important;
        }
      }
    }
  }

  .submenu-container {
    border-left: 1.5px dashed rgba(37, 99, 235, 0.22);
  }

  .submenu-item {
    .submenu-icon-wrap .submenu-icon-inner {
      color: #64748b;
    }

    .submenu-title {
      color: #334155;
    }

    &:hover {
      background-color: rgba(37, 99, 235, 0.05) !important;

      .submenu-icon-wrap .submenu-icon-inner,
      .submenu-title {
        color: #1d4ed8;
      }
    }

    &--active {
      background: linear-gradient(98deg, rgba(37, 99, 235, 0.12), rgba(37, 99, 235, 0.03) 94%) !important;

      .submenu-icon-wrap .submenu-icon-inner {
        color: #2563eb !important;
      }

      .submenu-title {
        color: #1d4ed8 !important;
        font-weight: 600 !important;
      }
    }
  }
}

/* Dark Theme Specific Styling for Sidebar */
.theme--dark .app-navigation-menu,
.theme--dark.app-navigation-menu {
  background-color: #16171d !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35) !important;

  .brand-name {
    color: #f8fafc;
  }

  .brand-badge-system {
    color: #94a3b8;
    background-color: #20222a;
    border: 1px solid rgba(255, 255, 255, 0.07);
  }

  .nav-header-divider {
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.08) 50%, transparent 100%);
  }

  .nav-group-item {
    ::v-deep .v-list-group__header {
      &:hover {
        background-color: rgba(59, 130, 246, 0.08) !important;
      }
    }

    &.group-has-active-child:not(.v-list-group--active) {
      ::v-deep .v-list-group__header {
        background-color: rgba(59, 130, 246, 0.1);

        .group-title {
          color: #60a5fa !important;
          font-weight: 600;
        }

        .v-list-item__icon .v-icon {
          color: #60a5fa !important;
        }
      }
    }

    &.v-list-group--active {
      ::v-deep .v-list-group__header {
        background-color: rgba(59, 130, 246, 0.14) !important;

        .group-title {
          color: #60a5fa !important;
          font-weight: 600 !important;
        }

        .v-list-item__icon .v-icon {
          color: #60a5fa !important;
        }
      }
    }
  }

  .submenu-container {
    border-left: 1.5px dashed rgba(59, 130, 246, 0.25);
  }

  .submenu-item {
    .submenu-icon-wrap .submenu-icon-inner {
      color: #94a3b8;
    }

    .submenu-title {
      color: #cbd5e1;
    }

    &:hover {
      background-color: rgba(59, 130, 246, 0.08) !important;

      .submenu-icon-wrap .submenu-icon-inner,
      .submenu-title {
        color: #93c5fd;
      }
    }

    &--active {
      background: linear-gradient(98deg, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0.05) 94%) !important;

      .submenu-icon-wrap .submenu-icon-inner {
        color: #60a5fa !important;
      }

      .submenu-title {
        color: #60a5fa !important;
        font-weight: 600 !important;
      }
    }
  }
}
</style>
