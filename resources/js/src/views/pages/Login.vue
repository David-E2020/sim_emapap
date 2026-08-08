<template>
  <div class="auth-wrapper auth-v1">
    <div class="auth-inner">
      <v-card class="auth-card">
        <v-form @submit.prevent="login">
          <v-window>
            <v-window-item :value="1">
              <v-row>
                <v-col cols="12" md="6">
                  <v-card-text class="mt-12">
                    <h2 class="text-2xl font-weight-semibold">
                      SIE - PLANTAS INDUSTRIALES</h2>
                    <v-row align="center" justify="center">
                      <v-col cols="12" sm="8">
                        <v-text-field v-model="usr_usuario" outlined label="Usuario" placeholder="Ingrese el usuario"
                          hide-details class="mt-6"
                          :append-icon="icons.mdiAccount"
                          ></v-text-field>
                        <v-text-field v-model="password" outlined :type="isPasswordVisible ? 'text' : 'password'"
                          label="Contraseña" placeholder="············"
                          :append-icon="isPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline" hide-details
                          @click:append="isPasswordVisible = !isPasswordVisible" class="mt-6"></v-text-field>
                        <v-btn block color="primary" :disabled="loaderLogin" :loading="loaderLogin" class="mt-6"
                          type="submit">
                          Ingresar
                        </v-btn>
                        <br>
                        <h6 class="text-center  grey--text ">Para poder ingresar al sistema de gestion de plantas <br>debe
                          estar registrado en el sistema de facturacion SIE - COMERCIALIZACION</h6>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="6" class="rounded-bl-xl" style="background:#dbdadb">
                  <div style="  text-align: center; padding: 180px 0;">
                    <v-img :src="require('@/assets/images/logos/logoEmapa2.png').default" width="430" height="100%"
                      alt="logo" contain class="me-3"></v-img>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>
          </v-window>
        </v-form>
      </v-card>
    </div>
    <!-- background triangle shape  -->

    <img class="auth-mask-bg" height="173"
      :src="require(`@/assets/images/misc/mask-${$vuetify.theme.dark ? 'dark' : 'light'}.png`).default" />

    <!-- tree -->
    <v-img class="auth-tree" width="247" height="185" :src="require('@/assets/images/misc/tree.png').default"></v-img>

    <!-- tree  -->
    <v-img class="auth-tree-3" width="377" height="289" :src="require('@/assets/images/misc/tree-3.png').default"></v-img>



    <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1800">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiFacebook, mdiTwitter, mdiGithub, mdiGoogle, mdiEyeOutline, mdiEyeOffOutline, mdiAccount } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {
  data: () => ({
    owl_hide: false,
    usr_usuario: '',
    password: '',
    loaderLogin: false,
    snackbar: {
      status: false,
      text: '',
      color: '',
    },
  }),
  mounted() { },
  setup() {
    const isPasswordVisible = ref(false)
    const email = ref('')
    const password = ref('')
    const socialLink = [
      {
        icon: mdiFacebook,
        color: '#4267b2',
        colorInDark: '#4267b2',
      },
      {
        icon: mdiTwitter,
        color: '#1da1f2',
        colorInDark: '#1da1f2',
      },
      {
        icon: mdiGithub,
        color: '#272727',
        colorInDark: '#fff',
      },
      {
        icon: mdiGoogle,
        color: '#db4437',
        colorInDark: '#db4437',
      },
    ]

    return {
      isPasswordVisible,
      email,
      password,
      socialLink,
      icons: {
        mdiEyeOutline,
        mdiEyeOffOutline,
        mdiAccount,
      },
    }
  },
  methods: {
    login() {
      this.loaderLogin = true
      let usr_usuario = this.usr_usuario
      let password = this.password
      this.$store
        .dispatch('auth/login', { usr_usuario, password })
        .then(res => {
          this.loaderLogin = false;
          var route_ = res.data.rute_home;
          this.$router.push({ name: route_ });
        })
        .catch(err => {
          this.loaderLogin = false;
          if (err.response) {
            this.snackbar = {
              status: true,
              text: err.response.data.message,
              color: 'blue',
            }
          } else {
            this.snackbar = {
              status: true,
              text: "Error Login",
              color: 'blue',
            }
          }

          /*
          iziToast.success({
            position: 'topRight',
            title: 'Credenciales invalidas',
            message: err,
            theme: 'light', // dark
            color: 'red', // blue, red, green, yellow
          })
*/
        })
    },
  },
}
</script>

<style lang="scss">
@import '~@resources/sass/preset/pages/auth.scss';

.v-application .rounded-bl-xl {
  border-bottom-left-radius: 300px !important;
}

.v-application .rounded-br-xl {
  border-bottom-right-radius: 300px !important;
}
</style>
