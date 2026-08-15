<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-2 px-4" elevation="1">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded class="mr-3 text-white" size="44">
            <v-icon color="white">mdi-shield-lock-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Control de Acceso a Módulos</h2>
            <span class="text-caption text-secondary">Asignación de permisos por módulos del sistema y supervisión de accesos</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- FORMULARIO DE ACCESOS -->
    <v-card elevation="2" class="mb-5">
      <v-card-title class="py-3">
        <v-icon color="primary" left>mdi-account-cog-outline</v-icon>
        <span class="text-subtitle-1 font-weight-bold">Selección de Usuario y Permisos</span>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text class="pt-4">
        <v-form v-model="valid" ref="form" @submit.prevent="registrarSolicitud">
          <v-row>
            <!-- SELECTOR DE USUARIO -->
            <v-col cols="12" md="6">
              <v-autocomplete
                label="Seleccionar Usuario"
                outlined
                dense
                :items="usuarios"
                item-text="usr_usuario"
                item-value="id"
                v-model="selectedUserId"
                @change="handleUserChange"
                prepend-inner-icon="mdi-account-search"
                clearable
                placeholder="Busca por usuario o nombre..."
              >
                <template v-slot:item="{ item }">
                  <v-list-item-avatar color="primary lighten-5" size="32" class="my-0">
                    <v-icon small color="primary">mdi-account-outline</v-icon>
                  </v-list-item-avatar>
                  <v-list-item-content>
                    <v-list-item-title class="font-weight-medium text-body-2">{{ item.usr_usuario }}</v-list-item-title>
                    <v-list-item-subtitle class="text-caption text-secondary">{{ item.name || '-' }}</v-list-item-subtitle>
                  </v-list-item-content>
                </template>
              </v-autocomplete>
            </v-col>

            <v-col cols="12" md="6" class="d-flex align-center">
              <v-chip color="primary" label outlined class="font-weight-bold" v-if="usuarios.length > 0">
                <v-icon small left>mdi-account-group</v-icon>
                {{ usuarios.length }} Usuarios Disponibles
              </v-chip>
            </v-col>
          </v-row>

          <v-divider class="my-3" v-if="fromUser"></v-divider>

          <!-- DETALLES DEL USUARIO Y MÓDULOS -->
          <v-row v-if="fromUser">
            <!-- TARJETA INFORMACIÓN DEL USUARIO -->
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-4 fill-height">
                <div class="d-flex align-center mb-3">
                  <v-avatar color="primary" size="48" class="mr-3 text-white">
                    <v-icon color="white">mdi-account-circle-outline</v-icon>
                  </v-avatar>
                  <div>
                    <h3 class="text-subtitle-1 font-weight-bold primary--text mb-0">{{ fromUser.name || fromUser.usr_usuario }}</h3>
                    <span class="text-caption text-secondary">@{{ fromUser.usr_usuario }}</span>
                  </div>
                </div>

                <v-divider class="mb-3"></v-divider>

                <div class="d-flex flex-column gap-2 text-body-2">
                  <div class="d-flex justify-space-between py-1 border-bottom">
                    <span class="text-secondary">Correo Electrónico:</span>
                    <span class="font-weight-medium">{{ fromUser.email || '-' }}</span>
                  </div>
                  <div class="d-flex justify-space-between py-1 border-bottom">
                    <span class="text-secondary">Estado en Sistema:</span>
                    <v-chip small color="success" class="font-weight-bold" label>
                      {{ fromUser.usr_estado === 'A' ? 'Activo' : 'Inactivo' }}
                    </v-chip>
                  </div>
                  <div class="d-flex justify-space-between py-1">
                    <span class="text-secondary">Roles Asignados:</span>
                    <span class="font-weight-bold primary--text">
                      {{ fromUser.roles && fromUser.roles.length > 0 ? fromUser.roles.map(r => r.name).join(', ') : 'Sin Rol' }}
                    </span>
                  </div>
                </div>
              </v-card>
            </v-col>

            <!-- MÓDULOS DEL SISTEMA -->
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-4 fill-height">
                <div class="text-subtitle-2 font-weight-bold color-primary mb-3 d-flex align-center">
                  <v-icon small color="primary" class="mr-2">mdi-view-dashboard-outline</v-icon>
                  Módulos de Sistema Habilitados
                </div>

                <v-divider class="mb-3"></v-divider>

                <v-list dense class="pa-0">
                  <v-list-item class="px-0">
                    <v-list-item-avatar size="32" color="primary lighten-5" class="mr-3">
                      <v-icon small color="primary">mdi-water-pump</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-bold text-body-2">Cobranzas de Agua Potable</v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-secondary">Módulo de lecturas, cortes y cobranza</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-switch v-model="sieCobranzas" color="primary" hide-details></v-switch>
                    </v-list-item-action>
                  </v-list-item>

                  <v-list-item class="px-0">
                    <v-list-item-avatar size="32" color="primary lighten-5" class="mr-3">
                      <v-icon small color="primary">mdi-receipt-text-outline</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-bold text-body-2">Sistema de Facturación</v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-secondary">Emisión de facturas y notas fiscales</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-switch v-model="sieFacturacion" color="primary" hide-details></v-switch>
                    </v-list-item-action>
                  </v-list-item>

                  <v-list-item class="px-0">
                    <v-list-item-avatar size="32" color="primary lighten-5" class="mr-3">
                      <v-icon small color="primary">mdi-database-cog-outline</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-bold text-body-2">Paramétricas y Catálogos</v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-secondary">Listas maestras y configuración</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-switch v-model="sieParametricas" color="primary" hide-details></v-switch>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>

                <div class="mt-4 text-right">
                  <v-btn
                    color="primary"
                    elevation="1"
                    :loading="loadingSave"
                    :disabled="loadingSave"
                    @click="registrarSolicitud"
                    class="text-capitalize px-4"
                  >
                    <v-icon left small>mdi-content-save-outline</v-icon> Guardar Accesos
                  </v-btn>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- ESTADO VACÍO CUANDO NO HAY USUARIO SELECCIONADO -->
          <div v-else class="py-12 text-center">
            <v-avatar color="primary lighten-5" size="70" class="mb-3">
              <v-icon size="36" color="primary">mdi-account-search-outline</v-icon>
            </v-avatar>
            <h3 class="text-h6 font-weight-bold color-primary">Selecciona un Usuario</h3>
            <p class="text-caption text-secondary max-w-sm mx-auto px-4">
              Elige un usuario en la lista desplegable superior para visualizar sus datos y gestionar los accesos a los módulos.
            </p>
          </div>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- NOTIFICACIONES SNACKBAR -->
    <v-snackbar v-model="snackbar.status" bottom right :color="snackbar.color" :timeout="2200" rounded="pill">
      <div class="d-flex align-center">
        <v-icon left color="white">mdi-check-circle-outline</v-icon>
        <span>{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn icon dark v-bind="attrs" @click="snackbar.status = false">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  data: () => ({
    usuarios: [],
    selectedUserId: null,
    fromUser: null,
    sieCobranzas: true,
    sieFacturacion: true,
    sieParametricas: true,
    valid: true,
    loadingSave: false,
    snackbar: {
      status: false,
      text: '',
      color: 'success',
    },
  }),

  mounted() {
    this.getUsuarios();
  },

  methods: {
    getUsuarios() {
      axios.get('api/listar_usuario_acceso')
        .then((response) => {
          this.usuarios = response.data;
          if (this.usuarios.length > 0) {
            this.selectedUserId = this.usuarios[0].id;
            this.handleUserChange(this.selectedUserId);
          }
        })
        .catch((error) => {
          this.showSnackbar('Error al cargar la lista de usuarios', 'error');
        });
    },

    handleUserChange(selectedUserId) {
      this.fromUser = this.usuarios.find(user => user.id === selectedUserId) || null;
    },

    registrarSolicitud() {
      if (!this.fromUser) return;
      this.loadingSave = true;

      const solicitudData = {
        user_id: this.fromUser.id,
        sieCobranzas: this.sieCobranzas,
        sieFacturacion: this.sieFacturacion,
        sieParametricas: this.sieParametricas,
      };

      axios.post('api/guardar_acceso_usuario', solicitudData)
        .then((response) => {
          this.loadingSave = false;
          this.showSnackbar(response.data.mensaje || 'Accesos actualizados correctamente', 'success');
        })
        .catch((error) => {
          this.loadingSave = false;
          this.showSnackbar('Error al guardar accesos del usuario', 'error');
        });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    }
  }
}
</script>
