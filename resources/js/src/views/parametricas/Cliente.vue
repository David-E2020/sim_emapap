<template>
  <div>
    <v-card elevation="2" style="padding: 20px">
      <p class="text-2xl">Clientes</p>

      <v-row>
        <!-- basic -->
        <v-col cols="12">
          <v-spacer></v-spacer>
          <v-row justify="end">
            <v-col cols="12" style="width: 100%; text-align: end">
              <v-btn color="primary" small elevation="2" @click="btnNuevo()">Nuevo</v-btn>
            </v-col>
          </v-row>

          <br />
          <br />

          <template>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Cliente/Razón social</th>
                    <th class="text-left">Celular</th>
                    <th class="text-left">Correo</th>
                    <th class="text-left">NIT/CI/CEX</th>
                    <th class="text-center">Nro facturas emitidas</th>
                    <th class="text-left">Tipo Documento</th>
                    <th class="text-left">Dirección</th>
                    <th class="text-left">Opciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in clientes" :key="item.dni_ci">
                    <td>{{ item.cliente }}</td>
                    <td>{{ item.celular }}</td>
                    <td>{{ item.correo }}</td>
                    <td>
                      {{ item.nro_identificacion }} {{ item.complemento }}
                    </td>
                    <td class="text-center">
                      {{ item.facturas_count }}
                    </td>
                    <td>
                      {{ (item.tipo_documento_identidad != null) ? item.tipo_documento_identidad.param_nombre : ' - ' }}
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
          <v-dialog persistent scrollable v-model="dialog" max-width="990">

            <v-form v-model="valid" @submit.prevent="submit" lazy-validation ref="form">

              <v-card>
                <v-card-title class="text-h5">{{ isEdit ? 'Editar' : 'Nuevo' }}</v-card-title>
                <v-card-subtitle>
                  Los campos con <span style="color: red;">*</span> son obligatorios.
                </v-card-subtitle>

                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.nombre" outlined label="Nombre" hide-details="auto">
                        </v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.paterno" outlined label="Paterno" hide-details="auto">
                        </v-text-field>
                      </v-col>
                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.materno" outlined label="Materno" hide-details="auto">
                        </v-text-field>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.celular" outlined label="Celular" hide-details="auto">
                        </v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.correo" outlined label="Correo" hide-details="auto">
                        </v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field v-model="formCliente.cliente" required outlined label="Cliente/Razón social *"
                          hide-details="auto" :rules="rulesText">

                          <template #label>
                            <span class="red--text">* </span>Cliente/Razón social
                          </template>
                        </v-text-field>
                      </v-col>

                    </v-row>

                    <v-row>
                      <v-col cols="4" md="4">


                        <v-select v-model="formCliente.tipo_documento" :rules="rulesSelect" menu-props="auto" outlined
                          label="Tipo Documento *" :items="tipoDocumentoIdentidadList" item-text="param_nombre"
                          item-value="id" hide-details="auto" persistent-hint return-object
                          @change="changeDocumentoIdentidad">
                          <template #label>
                            <span class="red--text">* </span>Tipo Documento
                          </template>
                        </v-select>
                      </v-col>

                      <v-col :cols="isCI ? 4 : 8" :md="isCI ? 4 : 8">

                        <v-text-field :disabled="!formCliente.tipo_documento" v-model="formCliente.nro_identificacion"
                          outlined :label="labelIdentificacion" required hide-details="auto"
                          :rules="rulesTextNroIdentificacion">
                        </v-text-field>


                      </v-col>
                      <v-col cols="4" md="4" v-if="isCI">
                        <v-text-field @keyup="textUppercasePlaca(formCliente)" v-model="formCliente.complemento"
                          outlined label="Complemento" hide-details="auto">
                        </v-text-field>
                      </v-col>
                    </v-row>


                    <v-row>
                      <v-col cols="6" md="6">
                        <v-textarea :rules="rulesText" required v-model="formCliente.direccion" hide-details="auto"
                          rows="1" outlined label="Dirección *">
                          <template #label>
                            <span class="red--text">* </span>Dirección
                          </template>

                        </v-textarea>
                      </v-col>
                      <v-col cols="6" md="6">
                        <v-textarea v-model="formCliente.observaciones" hide-details="auto" rows="1" outlined
                          label="Observaciones">
                        </v-textarea>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>

                  <v-btn class="ma-2" elevation="10" small color="error" @click="dialog = false">
                    <v-icon small>mdi-close</v-icon>Cerrar
                  </v-btn>

                  <v-btn class="ma-2" elevation="10" small :loading="loading" :disabled="loading || !valid"
                    type="submit" color="primary">
                    {{ isEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}
                  </v-btn>

                </v-card-actions>

              </v-card>

            </v-form>

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
        <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
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
    tipoDocumentoIdentidadList: null,
    clientes: [],
    valid: false,
    dialog: false,
    labelIdentificacion: 'Seleccione',
    isCI: false,
    formCliente: {
      nombre: null,
      paterno: null,
      materno: null,
      celular: null,
      correo: null,
      cliente: null,
      nro_identificacion: null,
      tipo_documento: null,

      complemento: null,
      direccion: null,
      observaciones: null,
    },
  }),
  computed: {

    rulesNumber() {
      return [
        v => !!v || 'Es requerido',
        v => Number.isInteger(Number(v)) || 'No se admite letras',
        v => v > 0 || 'Es requerido ',
      ]
    },


    rulesSelect() {
      return [
        v => !!v || 'Es requerido'
      ]
    },


    rulesCelular() {
      return [
        v => !!v || 'Es requerido',
        v => Number.isInteger(Number(v)) || 'No se admite letras',
        v => v >= 0 && v.length >= 8 && v.length <= 8 || 'se requiere 8  digitos ',
        v => (v.substr(0, 1) == '6' || v.substr(0, 1) == '7') || 'Es requerido un numero de celular valido'

      ]
    },

    rulesCorreo() {
      return [
        (v) => !!v || "Es requerido",
        (v) => /.+@.+\..+/.test(v) || "El correo debe ser válido",
      ]
    },

    rulesNumberDecimal() {
      return [
        v => !!v || 'Es requerido',
        // v => Number.isInteger(Number(v)) || 'No se admite letras',
        v => v > 0 || 'Es requerido ',
      ]
    },
    rulesTextNroIdentificacion() {
      return [
        (v) => !!v || "Es requerido",

        (v) => (v && v.length >= 0) || "Es requerido",
      ];
    },
    rulesText() {
      return [
        v => !!v || 'Es requerido',
        v => !Number.isInteger(Number(v)) || 'No se admite números',
        v => (v && v.length > 0) || 'Es requerido',
      ]
    },
    rulesCantidad() {
      return [
        v => !!v || 'Es requerido',
        v => Number.isInteger(Number(v)) || 'No se admite letras',
        v => v <= this.formFacturaDetalle.stock || 'La cantidad tiene que ser menor al stock',
      ]
    },
  },
  mounted() {
    this.getDatos()
    this.listTipoDocumentoIdentidad();
  },
  watch: {

  },



  created() { },
  methods: {
    submit: function () {
      var validateForm = this.$refs.form.validate()
      if (!validateForm) {
        return false
      }
      this.loading = true

      if (this.isEdit) {


        this.formCliente.param_tipo_documento_identidad_id = this.formCliente.tipo_documento.id

        axios
          .put('api/cliente/' + this.formCliente.id, this.formCliente)
          .then(response => {
            this.getDatos()
            this.formCliente = {
              nombre: null,
              paterno: null,
              materno: null,
              celular: null,
              correo: null,
              cliente: null,
              nro_identificacion: null,
              tipo_documento: null,

              complemento: null,
              direccion: null,
              observaciones: null,
            },
              this.$refs.form.reset();

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

        this.formCliente.param_tipo_documento_identidad_id = this.formCliente.tipo_documento.id

        axios
          .post('api/cliente', this.formCliente)
          .then(response => {
            this.getDatos()
            this.formCliente = {
              nombre: null,
              paterno: null,
              materno: null,
              celular: null,
              correo: null,
              cliente: null,
              nro_identificacion: null,
              tipo_documento: null,

              complemento: null,
              direccion: null,
              observaciones: null,
            },
              this.dialog = false
            this.loading = false
            this.snackbar = {
              status: true,
              text: 'Nuevo Registro',
              color: 'primary',
            }
          })
          .catch(error => {
            this.snackbar = {
              status: true,
              text: error.response.data.message,
              color: "error",
            };
            this.loading = false
          })
      }
    },
    btnEditar(item) {
      this.formCliente = item;
      var tipoDocumentoIdentidad = item.tipo_documento_identidad;

      this.formCliente.tipo_documento = tipoDocumentoIdentidad;

      if (tipoDocumentoIdentidad) {
        this.changeDocumentoIdentidad(tipoDocumentoIdentidad);
      }
      this.dialog = true
      this.isEdit = true
    },

    listTipoDocumentoIdentidad() {
      this.tipoDocumentoIdentidadList = null;
      axios
        .get("api/datos-registro/tabla/tipo_documento_identidad")
        .then((response) => {
          this.tipoDocumentoIdentidadList = response.data;
        });
    },

    textUppercasePlaca(formCliente) {
      this.formCliente.complemento = formCliente.complemento.toUpperCase();

    },

    changeDocumentoIdentidad(item) {
      this.labelIdentificacion = item.param_nombre;
      if (item.param_codigo == 1) {
        this.isCI = true;
      } else {
        this.isCI = false;
      }
    },

    btnDialogConfirm() {
      var url_ = 'api/cliente/' + this.dataDialogConfirm.id
      axios.delete(url_).then(response => {
        this.dialogConfirm = false
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

      this.formCliente = {
        nombre: '',
        paterno: '',
        materno: '',
        celular: '',
        correo: '',
        cliente: '',
        nit: '',
        direccion: '',
        observaciones: '',
      };

      if (this.$refs.form) {
        this.$refs.form.reset();
      }


      this.isEdit = false;
      this.dialog = true;

    },

    getDatos() {
      axios.get('api/cliente').then(response => {
        this.clientes = response.data
      })
    },
  },
  components: {},
}
</script>
