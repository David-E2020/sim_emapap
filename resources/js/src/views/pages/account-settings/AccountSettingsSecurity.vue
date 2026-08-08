<template>
  <v-card flat class="mt-5">
    <v-form ref="form" v-model="valid" lazy-validation>
      <div class="px-3">
        <v-card-text class="pt-5">
          <v-row>
            <v-col cols="12" sm="8" md="6">
              <!-- current password -->
              <v-text-field v-model="accountSettingData.account.password"
                :type="isCurrentPasswordVisible ? 'text' : 'password'"
                :append-icon="isCurrentPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline"
                label="Current Password" outlined dense
                @click:append="isCurrentPasswordVisible = !isCurrentPasswordVisible"></v-text-field>

              <!-- new password -->
              <v-text-field v-model="newPassword" :type="isNewPasswordVisible ? 'text' : 'password'"
                :append-icon="isNewPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline" label="New Password"
                outlined dense hint="Make sure it's at least 8 characters." persistent-hint
                @click:append="isNewPasswordVisible = !isNewPasswordVisible"
                :rules="passwordValidation"></v-text-field>

              <!-- confirm password -->
              <v-text-field v-model="cPassword" :type="isCPasswordVisible ? 'text' : 'password'"
                :append-icon="isCPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline"
                label="Confirm New Password" outlined dense class="mt-3"
                @click:append="isCPasswordVisible = !isCPasswordVisible"
                :rules="passwordValidation"></v-text-field>
            </v-col>

            <v-col cols="12" sm="4" md="6" class="d-none d-sm-flex justify-center position-relative">
              <v-img contain max-width="170" :src="require('@/assets/images/3d-characters/pose-m-1.png').default"
                class="security-character"></v-img>
            </v-col>
          </v-row>
        </v-card-text>
      </div>

      <!-- divider -->
      <v-divider></v-divider>
      <!-- action buttons -->
      <v-card-text>
        <v-btn color="primary" class="me-3 mt-3" @click="openConfirmationDialog">
          GUARDAR CAMBIOS </v-btn>
        <v-btn color="secondary" outlined class="mt-3" onclick="resetForm()">
          CANCELAR </v-btn>
      </v-card-text>

      <div class="pa-3">
        <v-card-title class="flex-nowrap">
          <v-icon class="text--primary me-3">
            {{ icons.mdiKeyOutline }}
          </v-icon>
          <span class="text-break">Two-factor authentication</span>
        </v-card-title>

        <v-card-text class="two-factor-auth text-center mx-auto">
          <v-avatar color="primary" class="primary mb-4" rounded>
            <v-icon size="25" color="white">
              {{ icons.mdiLockOpenOutline }}
            </v-icon>
          </v-avatar>
          <p class="text-base text--primary font-weight-semibold">Two factor authentication is not enabled yet.</p>
          <p class="text-sm text--primary">
            Two-factor authentication adds an additional layer of security to your account by requiring more than just a
            password to log in. Learn more.
          </p>
        </v-card-text>

      </div>
      <div>
        <template>
          <v-dialog v-model="dialogConfirm" max-width="50%">
            <v-card>
              <v-card-title class="headline color-primary text-center">GUARDAR CAMBIOS</v-card-title>
              <v-card-text class="text-h6">¿Estás seguro de que deseas cambiar tu contraseña? si presiona <strong style="color: green;">ACEPTAR</strong> 
                se cerrará la sesión y deberás iniciar sesión nuevamente. <p>Recargue la página para ver los cambios.</p></v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red darken-1" text @click="dialogConfirm = false" rounded>Cancelar</v-btn>
                <v-btn color="green darken-1" text @click="updatePassword" rounded >Aceptar</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
              <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
            </template>
          </v-snackbar>
        </template>
      </div>
    </v-form>
  </v-card>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiKeyOutline, mdiLockOpenOutline, mdiEyeOffOutline, mdiEyeOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'
import axios from 'axios'
import { rule } from 'postcss';

export default {
  setup() {
    const valid = ref(false);
    const isCurrentPasswordVisible = ref(false)
    const isNewPasswordVisible = ref(false)
    const isCPasswordVisible = ref(false)
    const currentPassword = ref('12345678')
    const newPassword = ref('')
    const cPassword = ref('')
    const dialogConfirm = ref(false)
    const snackbar = ref({
      color: '',
      text: '',
      status: false,
    });

    return {
      valid,
      isCurrentPasswordVisible,
      isNewPasswordVisible,
      currentPassword,
      isCPasswordVisible,
      newPassword,
      cPassword,
      dialogConfirm,
      snackbar,
      icons: {
        mdiKeyOutline,
        mdiLockOpenOutline,
        mdiEyeOffOutline,
        mdiEyeOutline,
      },
    }
  },
  data: () => ({
    rules_password: [
      { required: true, message: 'Por favor, introduzca su contraseña', trigger: 'blur' },
      { min: 8, message: 'La contraseña debe tener al menos 8 caracteres', trigger: 'blur' }
    ],
    confirmPassword: [
      { required: true, message: 'Por favor, confirme su contraseña', trigger: 'blur' },
      {
        validator: (rule, value, callback) => {
          if (value === '') {
            callback(new Error('Por favor, confirme su contraseña'));
          } else if (value !== this.newPassword) {
            callback(new Error('Las contraseñas no coinciden'));
          } else {
            callback();
          }
        },
        trigger: 'blur'
      }
    ],
    user: null,
    accountSettingData: {
      account: {
        avatarImg: require('@/assets/images/avatars/1.png').default,
        username: '',
        name: '',
        email: '',
        email2: '',
        ci: '',
        status: 'Active',
        company: 'EMAPA',
      },
      information: {
        bio: 'The name’s John Deo. I am a tireless seeker of knowledge, occasional purveyor of wisdom and also, coincidentally, a graphic designer. Algolia helps businesses across industries quickly create relevant 😎, scaLabel 😀, and lightning 😍 fast search and discovery experiences.',
        birthday: 'February 22, 1995',
        address: '',
        phone: '',
        website: '',
        country: 'USA',
        languages: ['English', 'Spanish'],
        sistemas: [],
        gender: 'male',
      },
    },
  }),
  methods: {
    openConfirmationDialog() {
      this.dialogConfirm = true;
    },
    updatePassword() {
      if (this.newPassword === '') {
        this.snackbar = {
          color: 'error',
          text: 'Por favor, introduzca su nueva contraseña',
          status: true,
        }
        return;
      }
      if (this.newPassword !== this.cPassword) {
        this.snackbar = {
          color: 'error',
          text: 'Las contraseñas no coinciden',
          status: true,
        }
        return;
      }
      const data = {
        id: this.user.id,
        name: this.user.name,
        usr_usuario: this.user.usr_usuario,
        email: this.user.email,
        password: this.newPassword,
      };
      axios.post('/api/update_user_password', data)
        .then(response => {
          if (response.data.success) {
            // this.dialogConfirm = false;
            this.updating = false;
            // this.getUser();
            this.resetForm();
            this.snackbar = {
              color: 'success',
              text: 'Contraseña actualizada correctamente',
              status: true,
            }
            this.dialogConfirm = false;
            setTimeout(() => {
              this.$store.dispatch('auth/logout')
              .then(() => {
                this.$router.push('/pages/login');
              })
              .catch(err => console.error(err));
            }, 2500);
          } else {
            // this.dialogConfirm = false;
            this.snackbar = {
              color: 'error',
              text: 'Error al actualizar la contraseña 0',
              status: true,
            }
          }
        })
        .catch(_error => {
          console.error('Error:', error);
          this.dialogConfirm = false;
          this.updating = false;
          this.snackbar = {
            color: 'error',
            text: 'Error al actualizar la contraseña',
            status: true,
          }
        });
      this.getUser();
    },

    resetForm() {
      this.password = ''
      this.newPassword = ''
    },
    getUser() {
      this.user = JSON.parse(localStorage.getItem('user'))
      this.accountSettingData.account.username = this.user.usr_usuario
      this.accountSettingData.account.name = this.user.name
      this.accountSettingData.account.email = this.user.email
      this.accountSettingData.account.email2 = this.user.email_verified_at
      this.accountSettingData.account.password = this.user.usr_new_password
    }
  },
  mounted() {
    this.getUser()
  },
  computed: {
    passwordValidation() {
    const errors = [];
    if (this.newPassword.length < 8) {
      errors.push('La contraseña debe tener al menos 8 caracteres');
    }
    if (this.newPassword !== this.cPassword) {
      errors.push('Las contraseñas no coinciden');
    }

    return errors;
  },
  validate() {
    return this.passwordValidation.length === 0;
  }

  }
}
</script>

<style lang="scss" scoped>
.two-factor-auth {
  max-width: 25rem;
}
.security-character {
  position: absolute;
  bottom: -0.5rem;
}
</style>
