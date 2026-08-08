<template>
  <div>
    <v-card elevation="2" style="padding: 20px">
      <p class="text-2xl">Comprador</p>

      <v-row>
        <!-- basic -->
        <v-col cols="12">
          <v-spacer></v-spacer>
          <v-row justify="end">
            <v-col cols="12" style="width: 100%; text-align: end">
              <v-btn color="primary" elevation="2" @click="btnNuevo()">Nuevo</v-btn>
            </v-col>
          </v-row>

          <br />
          <br />

          <template>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">DNI/CI.</th>
                    <th class="text-left">Comprador</th>
                    <th class="text-left">Representante</th>
                    <th class="text-left">dirección</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in compradores" :key="item.dni_ci">
                    <td>{{ item.id }}</td>
                    <td>{{ item.dni_ci }}</td>
                    <td>{{ item.comprador }}</td>
                    <td>
                      {{ item.representante_nombre }} {{ item.representante_paterno }} {{ item.representante_materno }}
                    </td>
                    <td>{{ item.direccion }}</td>
                    <td>
                      <v-btn icon color="error" @click="btncConfirmDelete(item, 'n1')">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>

                      <v-btn icon color="success" @click="btnEditar(item)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </template>
        </v-col>
      </v-row>

      <template>
        <v-row justify="center">
          <v-dialog v-model="dialog" max-width="990">
            <v-card>
              <v-card-title class="text-h5">{{ isEdit ? 'Editar' : 'Nuevo' }}</v-card-title>

              <v-card-text>
                <v-form v-model="valid" @submit.prevent="submit" lazy-validation ref="form">
                  <v-container>
                    <v-row>
                      <v-col cols="6" md="6">
                        <v-text-field
                          v-model="formComprador.dni_ci"
                          filled
                          label="DNI/CI"
                          required
                          :rules="[v => !!v || 'El DNI CI es requerido']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="6" md="6">
                        <v-text-field
                          v-model="formComprador.comprador"
                          filled
                          label="Comprador"
                          required
                          :rules="[v => !!v || 'El Compradores requerido']"
                        ></v-text-field>
                      </v-col>
                    </v-row>

                    <p>REPRESENTANTE</p>

                    <v-row>
                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formComprador.representante_nombre"
                          filled
                          label="Nombre"
                          required
                          :rules="[v => !!v || 'El Nombre requerido']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formComprador.representante_paterno"
                          filled
                          label="Paterno"
                          required
                          :rules="[v => !!v || 'El Paterno requerido']"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formComprador.representante_materno"
                          filled
                          label="Paterno"
                          required
                          :rules="[v => !!v || 'El Materno requerido']"
                        ></v-text-field>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="12" md="12">
                        <v-textarea v-model="formComprador.direccion" filled label="Dirección"></v-textarea>
                      </v-col>
                    </v-row>

                    <br />
                    <br />
                    <v-row align="end">
                      <v-spacer></v-spacer>

                      <v-btn color="green darken-1" text @click="dialog = false">Cerrar</v-btn>

                      <v-btn depressed color="primary" :loading="loading" :disabled="loading || !valid" type="submit">{{
                        isEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR'
                      }}</v-btn>
                    </v-row>
                  </v-container>
                </v-form>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-row>

        <!---- confirmacion-->
        <template>
          <v-row justify="center">
            <v-dialog v-model="dialogConfirm" persistent max-width="360">
              <v-card>
                <v-card-title class="text-h5">¿Esta seguro de eliminar?</v-card-title>
                <v-card-text></v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="green darken-1" text @click="dialogConfirm = false">Cancelar</v-btn>
                  <v-btn color="error darken-1" text @click="btnDialogConfirm()">SI</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-row>
        </template>
        <!---- snackbar-->
        <v-snackbar v-model="snackbar.status"  bottom :color="snackbar.color" :timeout="1500">
          {{ snackbar.text }}
          <template v-slot:action="{ attrs }">
            <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
          </template>
        </v-snackbar>
      </template>
    </v-card>
  </div>
</template>

<script>
import { mdiPencilOutline } from '@mdi/js'
export default {
  setup() {
    return {
      icons: {
        mdiPencilOutline,
      },
    }
  },
  data: () => ({
    isEdit: false,
    formEdit: {},
    loading: false,
    dialogConfirm: false,
    dataDialogConfirm: {},
    snackbar: {
      status: false,
      text: '',
    },
    compradores: [],
    valid: false,
    dialog: false,
    formComprador: {
      dni_ci: '',
      comprador: '',
      representante_nombre: '',
      representante_paterno: '',
      representante_materno: '',
      direccion: '',
    },
  }),
  computed: {},
  mounted() {
    this.getDatos()
  },
  watch: {},
  created() {},
  methods: {
    submit: function () {
      var validateForm = this.$refs.form.validate()
      if (!validateForm) {
        return false
      }
      this.loading = true

      if (this.isEdit) {
        axios
          .put('api/comprador/' + this.formComprador.id, this.formComprador)
          .then(response => {
            this.getDatos()
            this.formComprador = {
              dni_ci: '',
              comprador: '',
              representante_nombre: '',
              representante_paterno: '',
              representante_materno: '',
              direccion: '',
            }
            this.dialog = false
            this.loading = false
            this.snackbar = {
              status: true,
              text: 'Registro Editado',
              color: 'primary',
            }
          })
          .catch(error => {
            this.loading = false
          })
      } else {
        axios
          .post('api/comprador', this.formComprador)
          .then(response => {
            this.getDatos()
            this.formComprador = {
              dni_ci: '',
              comprador: '',
              representante_nombre: '',
              representante_paterno: '',
              representante_materno: '',
              direccion: '',
            }
            this.dialog = false
            this.loading = false
            this.snackbar = {
              status: true,
              text: 'Nuevo Registro',
              color: 'primary',
            }
          })
          .catch(error => {
            this.loading = false
          })
      }
    },
    btnEditar(item) {
      this.formComprador = item
      this.dialog = true
      this.isEdit = true
    },
    btnDialogConfirm() {
      var url_ = 'api/comprador/' + this.dataDialogConfirm.id
      axios.delete(url_).then(response => {
        this.dialogConfirm = false;
        this.snackbar = {
          status: true,
          text: 'Eliminado',
          color: 'primary',
        }
        this.getDatos()
      })
    },
    btncConfirmDelete(item) {
      this.dataDialogConfirm = item
      this.dialogConfirm = true
    },

    btnNuevo() {
      this.dialog = true
    },

    getDatos() {
      axios.get('api/comprador').then(response => {
        this.compradores = response.data
      })
    },
  },
  components: {},
}
</script>
