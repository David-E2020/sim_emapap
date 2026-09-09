<template>
  <v-menu offset-y left nudge-bottom="14" min-width="230" content-class="user-profile-menu-content">
    <template v-slot:activator="{ on, attrs }">
      <v-badge bottom color="success" overlap offset-x="12" offset-y="12" class="ms-4" dot>
        <v-avatar size="38px" v-bind="attrs" v-on="on" class="user-avatar-trigger cursor-pointer">
          <v-img :src="require('@/assets/images/avatars/1.png').default"></v-img>
        </v-avatar>
      </v-badge>
    </template>
    <v-list>
      <div class="pb-3 pt-2">
        <v-badge bottom color="success" overlap offset-x="12" offset-y="12" class="ms-4" dot>
          <v-avatar size="40px">
            <v-img :src="require('@/assets/images/avatars/1.png').default"></v-img>
          </v-avatar>
        </v-badge>
        <div class="d-inline-flex flex-column justify-center ms-3" v-if="user" style="vertical-align: middle">
          <span class="text--primary font-weight-semibold mb-n1">
            {{ user.name }}
          </span>
          <small class="text--disabled text-capitalize">{{ user.usr_usuario }}</small>
        </div>
      </div>

      <!-- Profile -->
      <v-list-item link>
        <v-list-item-icon class="me-2">
          <v-icon size="22">
            {{ icons.mdiAccountOutline }}
          </v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title @click="btnPerfil()">Perfil</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-divider class="my-2"></v-divider>

      <!-- Logout -->
      <v-list-item link>
        <v-list-item-icon class="me-2">
          <v-icon size="22">
            {{ icons.mdiLogoutVariant }}
          </v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title @click="btnSalirSistema()">Salir</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script>
import {
  mdiAccountOutline,
  mdiLogoutVariant,
} from '@mdi/js'

export default {
  setup() {
    return {
      icons: {
        mdiAccountOutline,
        mdiLogoutVariant,
      },
    }
  },
  data: () => ({
    user: null,
    rol:''
  }),
  mounted() {
    this.getUser()
  },
  methods: {
    getUser() {
      this.user = JSON.parse(localStorage.getItem('user'));
      this.rol = localStorage.getItem('rol');
    },

    btnPerfil() {
      this.$router.push('/pages/account-settings');
    },

    btnSalirSistema() {
      
      var data = {};
      axios
        .post('api/logout', data)
        .then(response => {
          localStorage.clear();
          this.$router.push('/pages/login')
        })
        .catch(error => {})
    },
  },
}
</script>

<style lang="scss">
.user-avatar-trigger {
  border: 2px solid rgba(37, 99, 235, 0.25);
  transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;

  &:hover {
    transform: scale(1.05);
    border-color: #2563eb;
    box-shadow: 0 0 10px rgba(37, 99, 235, 0.3);
  }
}

.user-profile-menu-content {
  border-radius: 12px !important;
  overflow: hidden;

  .v-list {
    padding: 8px !important;
  }

  .v-list-item {
    min-height: 2.4rem !important;
    border-radius: 8px !important;
    margin-bottom: 2px;
    transition: background-color 0.15s ease, color 0.15s ease;
  }
}

.theme--light .user-profile-menu-content {
  background-color: #ffffff !important;
  border: 1px solid rgba(15, 23, 42, 0.08) !important;
  box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.1) !important;

  .v-list-item:hover {
    background-color: rgba(37, 99, 235, 0.06) !important;
    color: #2563eb !important;

    .v-icon {
      color: #2563eb !important;
    }
  }
}

.theme--dark .user-profile-menu-content {
  background-color: #18191f !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 14px 36px -4px rgba(0, 0, 0, 0.55) !important;

  .v-list-item:hover {
    background-color: rgba(59, 130, 246, 0.1) !important;
    color: #60a5fa !important;

    .v-icon {
      color: #60a5fa !important;
    }
  }
}
</style>
