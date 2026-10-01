<template>
  <v-card flat class="pa-4 mt-2">
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="openConfirmationDialog">
      <v-card-text class="pt-2">
        <v-row>
          <v-col cols="12" md="7">
            <div class="text-subtitle-1 font-weight-bold color-primary mb-3 d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-lock-reset</v-icon>
              Cambio de Contraseña
            </div>

            <!-- CONTRASEÑA ACTUAL -->
            <v-text-field
              v-model="currentPassword"
              :type="isCurrentPasswordVisible ? 'text' : 'password'"
              :append-icon="isCurrentPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
              label="Contraseña Actual"
              outlined
              dense
              class="mb-3"
              @click:append="isCurrentPasswordVisible = !isCurrentPasswordVisible"
              :rules="[v => !!v || 'La contraseña actual es requerida']"
            ></v-text-field>

            <!-- NUEVA CONTRASEÑA -->
            <v-text-field
              v-model="newPassword"
              :type="isNewPasswordVisible ? 'text' : 'password'"
              :append-icon="isNewPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
              label="Nueva Contraseña"
              outlined
              dense
              hint="Debe contener al menos 6 caracteres."
              persistent-hint
              class="mb-3"
              @click:append="isNewPasswordVisible = !isNewPasswordVisible"
              :rules="rulesNewPassword"
            ></v-text-field>

            <!-- CONFIRMAR NUEVA CONTRASEÑA -->
            <v-text-field
              v-model="cPassword"
              :type="isCPasswordVisible ? 'text' : 'password'"
              :append-icon="isCPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
              label="Confirmar Nueva Contraseña"
              outlined
              dense
              class="mt-3 mb-4"
              @click:append="isCPasswordVisible = !isCPasswordVisible"
              :rules="rulesConfirmPassword"
            ></v-text-field>

            <div class="d-flex align-center gap-2">
              <v-btn color="primary" elevation="1" class="text-capitalize px-5" @click="openConfirmationDialog">
                <v-icon left small>mdi-content-save-outline</v-icon> Guardar Cambios
              </v-btn>
              <v-btn color="secondary" outlined class="text-capitalize" @click="resetForm">
                Cancelar
              </v-btn>
            </div>
          </v-col>

          <v-col cols="12" md="5" class="d-none d-md-flex flex-column align-center justify-center text-center">
            <v-avatar color="primary lighten-5" size="100" class="mb-3">
              <v-icon size="48" color="primary">mdi-shield-check-outline</v-icon>
            </v-avatar>
            <h4 class="text-subtitle-1 font-weight-bold color-primary">Recomendación de Seguridad</h4>
            <p class="text-caption text-secondary px-4">
              Asegúrate de que tu nueva contraseña tenga al menos 6 caracteres y no coincida con claves utilizadas previamente.
            </p>
          </v-col>
        </v-row>
      </v-card-text>

      <!-- DIÁLOGO CONFIRMACIÓN -->
      <v-dialog v-model="dialogConfirm" max-width="450" persistent>
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-alert-circle-outline</v-icon>
            Confirmar Cambio de Contraseña
          </v-card-title>
          <v-card-text class="pt-4 text-body-1">
            ¿Estás seguro de que deseas cambiar tu contraseña? Al confirmar, tu sesión finalizará y deberás iniciar sesión nuevamente con tu nueva clave.
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="dialogConfirm = false" class="text-capitalize">
              Cancelar
            </v-btn>
            <v-btn color="primary" elevation="1" :loading="updating" @click="updatePassword" class="text-capitalize">
              Aceptar y Cerrar Sesión
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- NOTIFICACIONES SNACKBAR -->
      <v-snackbar v-model="snackbar.status" bottom right :color="snackbar.color" :timeout="2000" rounded="pill">
        <div class="d-flex align-center">
          <v-icon left color="white">mdi-information-outline</v-icon>
          <span>{{ snackbar.text }}</span>
        </div>
      </v-snackbar>
    </v-form>
  </v-card>
</template>

<script>
import axios from 'axios';

export default {
  data: () => ({
    valid: false,
    updating: false,
    isCurrentPasswordVisible: false,
    isNewPasswordVisible: false,
    isCPasswordVisible: false,
    currentPassword: '',
    newPassword: '',
    cPassword: '',
    dialogConfirm: false,
    snackbar: {
      status: false,
      text: '',
      color: 'success',
    },
    user: null,
  }),

  computed: {
    rulesNewPassword() {
      return [
        v => !!v || 'Por favor, introduzca su nueva contraseña',
        v => (v && v.length >= 6) || 'La contraseña debe tener al menos 6 caracteres'
      ];
    },
    rulesConfirmPassword() {
      return [
        v => !!v || 'Por favor, confirme su nueva contraseña',
        v => v === this.newPassword || 'Las contraseñas no coinciden'
      ];
    }
  },

  mounted() {
    this.getUser();
  },

  methods: {
    getUser() {
      const stored = localStorage.getItem('user');
      if (stored) {
        this.user = JSON.parse(stored);
      }
    },

    openConfirmationDialog() {
      if (this.$refs.form.validate()) {
        this.dialogConfirm = true;
      }
    },

    resetForm() {
      this.currentPassword = '';
      this.newPassword = '';
      this.cPassword = '';
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
    },

    updatePassword() {
      if (!this.user) return;
      this.updating = true;

      const data = {
        id: this.user.id,
        current_password: this.currentPassword,
        password: this.newPassword,
      };

      axios.post('api/update_user_password', data)
        .then(response => {
          this.updating = false;
          if (response.data.success) {
            this.dialogConfirm = false;
            this.showSnackbar('Contraseña actualizada correctamente', 'success');
            setTimeout(() => {
              this.$store.dispatch('auth/logout')
                .finally(() => {
                  this.$router.push('/login');
                });
            }, 1800);
          } else {
            this.dialogConfirm = false;
            this.showSnackbar(response.data.mensaje || 'Error al actualizar la contraseña', 'error');
          }
        })
        .catch(error => {
          this.updating = false;
          this.dialogConfirm = false;
          const msg = error.response && error.response.data && error.response.data.mensaje 
            ? error.response.data.mensaje 
            : 'Error al cambiar la contraseña';
          this.showSnackbar(msg, 'error');
        });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    }
  }
}
</script>
