<template>
  <div>
      <v-card>
      <v-toolbar flat>
        <v-toolbar-title>LISTADO DE CONTRATOSssss</v-toolbar-title>
        
        <v-spacer></v-spacer>
        <v-row justify="end">
            <v-col cols="10" style="text-align: end">
              <v-text-field v-model="search" solo append-icon="mdi-magnify" label="Buscar" dense single-line hide-details>
              </v-text-field>
            </v-col>
            <v-col cols="2" style="width: 100%; text-align: end">
              <v-btn color="primary" small elevation="2" @click="btnNuevo()">Nuevo</v-btn>
            </v-col>
        </v-row>
      </v-toolbar>
      <v-data-table :headers="headers" :items="contratos" :search="search">
        <template v-slot:item.nombre="{ item }">
          {{ item.nombre }}
        </template>
        <template v-slot:item.codigo="{ item }">
          {{ item.codigo }}
        </template>
        <template v-slot:item.descripcion="{ item }">
          {{ item?item.descripcion:'' }}
        </template>
         <template v-slot:item.acciones="{ item }">
            <v-tooltip bottom>
              <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip }" @click="btnEditar(item)" icon color="warning">
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
              </template>
              <span>Editar</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip }" @click="btncConfirmEliminar(item)" icon color="error">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
              <span>Eliminar</span>
            </v-tooltip>
        </template>
      </v-data-table>
    </v-card>
    <v-card>
      <v-card-text>
        <div class="text-center">
          <v-progress-circular :size="50" color="primary" indeterminate></v-progress-circular>
        </div>
      </v-card-text>
    </v-card>

      <template>
        <v-row justify="center">
          <v-dialog v-model="dialog" max-width="990">
            <v-card>
              <v-card-title class="text-h5">{{ isEdit ? 'Editar' : 'Nuevo' }}</v-card-title>

              <v-card-text>
                <v-form
                  v-model="valid"
                  @submit.prevent="submit"
                  enctype="multipart/form-data"
                  lazy-validation
                  ref="form"
                >
                  <v-container>
                    <v-row>
                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formContrato.nro_contrato"
                          filled
                          label="Nro Contrato"
                          required
                          :rules="[v => !!v || 'El Nro Contrato es requerido']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formContrato.cliente"
                          filled
                          label="Cliente"
                          required
                          :rules="[v => !!v || 'El Cliente requerido']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-menu
                          v-model="menu2"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              v-model="formContrato.fecha_salida"
                              label="Fecha de salida"
                              prepend-icon="mdi-calendar"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker v-model="formContrato.fecha_salida" @input="menu2 = false"></v-date-picker>
                        </v-menu>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="8" md="8">
                        <v-text-field
                          v-model="formContrato.incoterm"
                          filled
                          label="INCOTERM"
                          required
                          :rules="[v => !!v || 'El Campo requerido']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-file-input
                          v-model="formContrato.doc_salida"
                          v-on:change="onChange"
                          show-size
                          label="Doc. Salida"
                        ></v-file-input>
                        <small v-if="isEdit">
                          <a :href="'/upload/' + formContrato.doc_salida" target="_blank" rel="noopener noreferrer">
                            <v-icon small>mdi-tray-arrow-down</v-icon>

                            {{ formContrato.doc_salida }}
                          </a>
                        </small>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="8" md="8">
                        <v-textarea v-model="formContrato.direccion" filled label="Dirección"></v-textarea>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-select
                          v-model="formContrato.forma_pago_id"
                          :items="formasPago"
                          label="Formas de Pago"
                          item-text="param_codigo"
                          item-value="id"
                        ></v-select>
                      </v-col>
                    </v-row>
                     <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formContrato.nro_proceso_contrato"
                          filled
                          label="Nro Contrato"
                          required
                          :rules="[v => !!v || 'Nro Proceso Contrato']"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="4" md="4">
                        <v-text-field
                          v-model="formContrato.nro_proceso_interno"
                          filled
                          label="Cliente"
                          required
                          :rules="[v => !!v || 'Nro Proceso Interno']"
                        ></v-text-field>
                      </v-col>
                    <v-row>
                      <v-col cols="12">
                        <v-simple-table>
                          <template v-slot:default>
                            <thead>
                              <tr>
                                <th class="text-left">N° Tramo</th>
                                <th class="text-left">Unidad Medida</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino Detalle</th>
                                <th class="text-left">Vol. Estimado (qq/50/kg)</th>
                                <th class="text-left">Flete (Bs.)</th>
                                <th class="text-left">Monto Total</th>
                                <th class="text-left">Estibalaje</th>
                                <th class="text-left">Plazo (Dias)</th>
                                <th class="text-left">Acciones</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(det, index) in contrato_detalle" :key="index">
                                <td></td>
                                <td>
                                  <v-text-field :value="det.detalle" required disabled solo></v-text-field>
                                </td>
                                <td>
                                  <v-text-field
                                    :value="det.cantidad"
                                    required
                                    disabled
                                    type="number"
                                    solo
                                  ></v-text-field>
                                </td>
                                <td>
                                  <v-select
                                    solo
                                    :value="det.tipo_almendra_id"
                                    :items="formasPago"
                                    disabled
                                    label="--Seleccione--"
                                    item-text="param_codigo"
                                    item-value="id"
                                  ></v-select>
                                </td>
                                <td>
                                  <v-text-field
                                    :value="det.cantidad_almendra"
                                    required
                                    disabled
                                    type="number"
                                    solo
                                  ></v-text-field>
                                </td>
                                <td>
                                  <div class="my-3">
                                    <v-text-field :value="det.nro_lote" required disabled solo></v-text-field>
                                  </div>
                                </td>
                                <td>
                                  <v-btn depressed fab x-small color="error" @click="eliminarDetalle(det)">
                                    <v-icon>mdi-close </v-icon>
                                  </v-btn>
                                </td>
                              </tr>
                            </tbody>
                          </template>
                        </v-simple-table>
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
    date: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
    menu: false,
    modal: false,
    menu2: false,
    file: '',

    isEdit: false,
    loading: false,
    dialogConfirm: false,
    dataDialogConfirm: {},
    snackbar: {
      status: false,
      text: '',
    },
    formasPago: [],
    contratos: [],
    contrato_detalle: [],
    valid: false,
    validDetalle: false,
    dialog: false,

    formContratoDetalle: {
      contrato_id: '',
      detalle: '',
      cantidad: '',
      tipo_almendra_id: '',
      cantidad_almendra: '',
      nro_lote: '',
    },

    formContrato: {
      nro_contrato: '',
      cliente: '',
      fecha_salida: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
      incoterm: '',
      doc_salida: '',
      forma_pago_id: '1',
    },
    unidad_medida:[],
    estibalaje:[],
    distribuidora:[],
    tipo_contrato:[],
  }),
  computed: {},
  mounted() {
    this.getDatos()
    this.getFormasPago()
  },
  watch: {},
  created() {},
  methods: {
    onChange(event) {
      this.file = event;
    },

    save(date) {
      this.$refs.menu.save(date)
    },
    submitDetalle() {
      if (
        this.formContratoDetalle.detalle == '' ||
        this.formContratoDetalle.cantidad == '' ||
        this.formContratoDetalle.detalle == null
      ) {
        return false
      }

      this.contrato_detalle.push(JSON.parse(JSON.stringify(this.formContratoDetalle)))
      this.formContratoDetalle = {
        contrato_id: null,
        detalle: null,
        cantidad: null,
        tipo_almendra_id: null,
        cantidad_almendra: null,
        nro_lote: null,
      }
    },

    submit: function () {
      const config = {
        headers: {
          'content-type': 'multipart/form-data',
        },
      }

      var data = new FormData()
      if (this.file) {
        data.append('file', this.file);
      }
      data.append('nro_contrato', this.formContrato.nro_contrato);
      data.append('cliente', this.formContrato.cliente);
      data.append('fecha_salida', this.formContrato.fecha_salida);
      data.append('incoterm', this.formContrato.incoterm);
      data.append('doc_salida', this.formContrato.doc_salida);
      data.append('forma_pago_id', this.formContrato.forma_pago_id);
      data.append('detalles', JSON.stringify(this.contrato_detalle));

      var validateForm = this.$refs.form.validate()
      if (!validateForm) {
        return false
      }
      this.loading = true

      if (this.isEdit) {
        axios
          .put('api/contrato/' + this.formContrato.id,  data, config)
          .then(response => {
            this.getDatos()
            this.contrato_detalle = []
            this.formContrato = {
              nro_contrato: '',
              cliente: '',
              fecha_salida: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
              incoterm: '',
              doc_salida: '',
              forma_pago_id: '1',
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
          //.post('api/contrato', this.formContrato)
          .post('api/contrato', data, config)
          .then(response => {
            this.getDatos()
            this.contrato_detalle = []
            this.formContrato = {
              nro_contrato: '',
              cliente: '',
              fecha_salida: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
              incoterm: '',
              doc_salida: '',
              forma_pago_id: '1',
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
      this.formContrato = item
      this.contrato_detalle = item.detalles
      this.dialog = true
      this.isEdit = true
    },
    btnDialogConfirm() {
      var url_ = 'api/contrato/' + this.dataDialogConfirm.id
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
      this.isEdit = false
      this.dialog = true
    },
    getFormasPago() {
      axios.get('api/datos-registro/tabla/forma_pago').then(response => {
        this.formasPago = response.data
      })
    },
    getDatos() {
      axios.get('api/contrato').then(response => {
        this.contratos = response.data
      })
    },
    getTipoContrato() {
      axios.get('api/listar_tipo_contrato').then(response => {
        this.tipo_contrato = response.data;
      })
    },
    eliminarDetalle(item) {
      this.contrato_detalle.splice(item, 1)
    },


  },
  components: {},
}
</script>
