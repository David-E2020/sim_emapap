<template>
  <v-card flat class="pa-4 mt-2">
    <v-card-text>
      <!-- VISTA CABECERA USUARIO -->
      <div class="d-flex align-center flex-wrap mb-6">
        <v-avatar color="primary lighten-5" size="100" class="mr-6">
          <v-icon size="64" color="primary">mdi-account-circle-outline</v-icon>
        </v-avatar>

        <div>
          <h3 class="text-h5 font-weight-bold primary--text mb-1">{{ accountDataLocale.name || accountDataLocale.username }}</h3>
          <p class="text-subtitle-2 text-secondary mb-2">@{{ accountDataLocale.username }}</p>
          <div class="d-flex align-center gap-2">
            <v-chip small color="success" class="font-weight-bold" label>
              <v-icon x-small left>mdi-check-circle-outline</v-icon> {{ accountDataLocale.status }}
            </v-chip>
            <v-chip small color="primary" class="font-weight-bold" label outlined>
              <v-icon x-small left>mdi-shield-account</v-icon> {{ accountDataLocale.role || 'Usuario del Sistema' }}
            </v-chip>
          </div>
        </div>
      </div>

      <v-divider class="mb-6"></v-divider>

      <!-- FORMULARIO DATOS DE PERFIL -->
      <v-form class="multi-col-validation">
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="accountDataLocale.username"
              label="Nombre de Usuario"
              dense
              disabled
              outlined
              prepend-inner-icon="mdi-account"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field
              v-model="accountDataLocale.name"
              label="Nombre Completo"
              dense
              disabled
              outlined
              prepend-inner-icon="mdi-badge-account-horizontal-outline"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field
              v-model="accountDataLocale.email"
              label="Correo Electrónico"
              dense
              disabled
              outlined
              prepend-inner-icon="mdi-email-outline"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field
              v-model="accountDataLocale.role"
              label="Rol del Sistema"
              dense
              disabled
              outlined
              prepend-inner-icon="mdi-shield-account-outline"
            ></v-text-field>
          </v-col>
        </v-row>
      </v-form>

      <!-- ALERTA DE INFORMACIÓN -->
      <v-alert color="primary lighten-5" border="left" colored-border elevation="1" class="mt-4 mb-0">
        <div class="d-flex align-center">
          <v-icon color="primary" class="mr-3">mdi-information-outline</v-icon>
          <span class="text-caption text-secondary">
            Los datos personales y asignación de roles son administrados centralizadamente. Para cambios de datos, contacta con el administrador del sistema.
          </span>
        </div>
      </v-alert>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: {
    accountData: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      accountDataLocale: {
        username: '',
        name: '',
        email: '',
        status: 'Activo',
        role: 'Usuario',
      }
    };
  },
  watch: {
    accountData: {
      immediate: true,
      handler(val) {
        if (val) {
          this.accountDataLocale = {
            username: val.username || '',
            name: val.name || '',
            email: val.email || '',
            status: val.status || 'Activo',
            role: val.role || 'Usuario',
          };
        }
      }
    }
  }
}
</script>
