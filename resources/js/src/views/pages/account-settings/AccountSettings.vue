<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-2 px-4" elevation="1">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded class="mr-3 text-white" size="44">
            <v-icon color="white">mdi-account-cog-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Configuración de Cuenta</h2>
            <span class="text-caption text-secondary">Ajustes del perfil de usuario y seguridad de contraseña</span>
          </div>
        </div>
      </div>
    </v-card>

    <v-card elevation="2" id="account-setting-card">
      <!-- PESTAÑAS DE NAVEGACIÓN -->
      <v-tabs v-model="tab" show-arrows color="primary" class="border-bottom">
        <v-tab v-for="t in tabs" :key="t.title" class="font-weight-bold text-capitalize">
          <v-icon size="20" class="mr-2">
            {{ t.icon }}
          </v-icon>
          <span>{{ t.title }}</span>
        </v-tab>
      </v-tabs>

      <!-- CONTENIDO DE PESTAÑAS -->
      <v-tabs-items v-model="tab">
        <v-tab-item>
          <account-settings-account :account-data="accountData"></account-settings-account>
        </v-tab-item>
        
        <v-tab-item>
          <account-settings-security></account-settings-security>
        </v-tab-item>
      </v-tabs-items>
    </v-card>
  </div>
</template>

<script>
import AccountSettingsAccount from './AccountSettingsAccount.vue'
import AccountSettingsSecurity from './AccountSettingsSecurity.vue'

export default {
  components: {
    AccountSettingsAccount,
    AccountSettingsSecurity,
  },

  data: () => ({
    tab: 0,
    tabs: [
      { title: 'Mi Perfil', icon: 'mdi-account-outline' },
      { title: 'Seguridad y Contraseña', icon: 'mdi-lock-outline' },
    ],
    accountData: {
      username: '',
      name: '',
      email: '',
      status: 'Activo',
      role: 'Usuario',
    },
  }),

  mounted() {
    this.getUserData();
  },

  methods: {
    getUserData() {
      const userStr = localStorage.getItem('user');
      const roleStr = localStorage.getItem('role') || 'Administrador General';
      
      if (userStr) {
        const user = JSON.parse(userStr);
        this.accountData = {
          username: user.usr_usuario || '',
          name: user.name || '',
          email: user.email || '',
          status: user.usr_estado === 'A' ? 'Activo' : 'Inactivo',
          role: user.roles && user.roles.length > 0 ? user.roles[0].name : roleStr,
        };
      }
    },
  },
}
</script>
