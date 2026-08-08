<template>
  <div>
    <!--VISTA PARA REPORTES-->
     <v-dialog persistent v-model="dialog_report" width="990">
      <v-card>
        <v-card-title class="grey lighten-2">
          PRE VISUALIZAR
          <v-spacer></v-spacer>
          <v-btn icon @click="dialog_report = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-expand-transition>
          <div v-if="urlPreVisualizar">
            <iframe id="ireport" :src="urlPreVisualizar" frameborder="0" allowtransparency="true"
              style="width: 100%; height: 500px"></iframe>
          </div>
        </v-expand-transition>
        <div v-if="!urlPreVisualizar">
          <v-card-text style="width: 100%; height: 500px; padding-top: 200px; padding-bottom: 200px;">
            <div class="text-center">
              <v-progress-circular :size="50" color="primary" indeterminate></v-progress-circular>
            </div>
          </v-card-text>
        </div>
      </v-card>
    </v-dialog>
    <!--FINALIZACION DE VISTA PARA REPORTE-->
    <v-card elevation="2" style="padding: 10px">
      <v-row>
        <!-- basic -->
        <v-col cols="5">
          <v-row>
            <v-col cols="6">
              <p class="text-2xl">Datos Registro</p>
            </v-col>
            <v-col cols="6 text-end">
              <v-spacer></v-spacer>
              <v-btn color="primary" small elevation="24" @click="nuevoRegistro()">Nuevo</v-btn>
                <v-btn icon color="primary" @click="verDocumentoIngreso()">
                  <v-icon>mdi-file-document-outline</v-icon>
                </v-btn>
            </v-col>
          </v-row>
          <template>
            <v-simple-table fixed-header>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Acciones</th>
                    <th class="text-left">Nro.</th>
                    <th class="text-left">Nombre</th>
                    <th class="text-left">Valor</th>
                    <th class="text-left">Codigo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in registros" :key="item.id">
                    <td>
                      <v-btn icon color="error" @click="confirmDelete(item, 'n1')">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>

                      <v-btn icon color="success" @click="editarRegistro(item)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>

                      <v-btn icon color="primary" @click="detalleRegistro(item)">
                        <v-icon>mdi-eye</v-icon>
                      </v-btn>
                    </td>
                    <td>{{ item.id }}</td>
                    <td>{{ item.param_tabla }}</td>
                    <td>{{ item.param_valor }}</td>
                    <td>{{ item.param_codigo }}</td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </template>
        </v-col>
        <v-col cols="7">
          <div v-if="detalle != null">
            <v-row>
              <v-col cols="6">
                <div class="text-2xl">{{ tabla_seleccionada.param_tabla }}</div>
              </v-col>
              <v-col cols="6 text-end">
                <v-spacer></v-spacer>
                <v-btn color="primary" small elevation="24" @click="nuevoRegistroN2()">Nuevo</v-btn>
              </v-col>
            </v-row>
            <br>
            <template>
              <v-simple-table fixed-header height="450px">
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th class="text-left">Acciones</th>
                      <th class="text-left">Id</th>
                      <th class="text-left">Valor</th>
                      <th class="text-left">Nombre</th>
                      <th class="text-left">Codigo</th>
                      <th class="text-left">Descripcion</th>
                    </tr>                  </thead>
                  <tbody>
                    <tr v-for="item in registrosN2" :key="item.param_valor">
                      <td>
                        <v-btn icon color="error" @click="confirmDelete(item, 'n2')">
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>

                        <v-btn icon color="success" @click="editarRegistroN2(item)">
                          <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                      </td>
                      <td>{{ item.id }}</td>
                      <td>{{ item.param_valor }}</td>
                      <td>{{ item.param_nombre }}</td>
                      <td>{{ item.param_codigo }}</td>
                      <td>{{ item.param_descripcion }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </template>
          </div>
        </v-col>
      </v-row>

      <template>
        <v-row justify="center">
          <v-dialog v-model="dialog" max-width="490">
            <v-card>
              <v-card-title class="text-h5">{{ isEdit ? 'Editar' : 'Nuevo' }}</v-card-title>

              <v-card-text>
                <v-form v-model="valid" @submit.prevent="submit" lazy-validation ref="form">
                  <v-container>
                    <v-row>
                      <v-col cols="12" md="12">
                        <v-text-field v-model="formRegistro.param_tabla" filled label="Nombres" required
                          :rules="[(v) => !!v || 'El nombre es requerido']"></v-text-field>
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
        <v-row justify="center">
          <v-dialog v-model="dialogN2" max-width="490">
            <v-card>
              <v-card-title class="text-h5">{{ isEditN2 ? 'Editar' : 'Nuevo' }}</v-card-title>
              <v-card-text>
                <v-form v-model="validN2" @submit.prevent="submitN2" lazy-validation ref="formn2">
                  <v-container>
                    <v-row>
                      <v-col cols="12" md="12">
                        <v-text-field v-model="formRegistroN2.param_codigo" filled label="Código" required
                          :rules="[(v) => !!v || 'El código es requerido']"></v-text-field>
                        <br />
                        <v-text-field v-model="formRegistroN2.param_nombre" filled label="Nombre" required
                          :rules="[(v) => !!v || 'El nombre es requerido']"></v-text-field>
                        <br />

                        <v-textarea v-model="formRegistroN2.param_detalle" filled label="Detalle"></v-textarea>
                      </v-col>
                    </v-row>
                    <br />
                    <br />
                    <v-row align="end">
                      <v-spacer></v-spacer>
                      <v-btn color="green darken-1" text @click="dialogN2 = false">Cerrar</v-btn>
                      <v-btn depressed color="primary" :loading="loadingN2" :disabled="loadingN2 || !validN2"
                        type="submit">{{ isEditN2 ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}</v-btn>
                    </v-row>
                  </v-container>
                </v-form>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-row>
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

import {
  mdiPencilOutline,

} from '@mdi/js'
export default {
  setup() {
    return {
      icons: {
        mdiPencilOutline,
      },
    }
  },
  data: () => ({
    dataDialogConfirm: {
      item: {},
      tipo: ''
    },
    snackbar: {
      status: false,
      text: ""
    },
    registros: [],
    registrosN2: [],
    isEditN2: false,
    dialog_report: false,
    itemEdit: null,
    detalle: null,
    valid: false,
    validN2: false,
    isEdit: false,
    dialog: false,
    dialogN2: false,
    dialogConfirm: false,
    urlPreVisualizar: null,
    formRegistro: {
      param_tabla: ''
    },

    formRegistroN2: {
      param_foranea: '',
      param_codigo: '',
      param_nombre: '',
      param_detalle: ''
    },

    loading: false,
    loadingN2: false,
    tabla_seleccionada:{},
  }),
  computed: {

  },
  mounted() {
    this.getParametrica();
  },
  watch: {

  },

  created() {

  },
  methods: {
    getParametrica() {
      this.userCreating = true;
      this.registros = [];
      axios.get(`api/parametrica-api`)
        .then((response) => {
          this.registros = response.data;
          this.userCreating = false;
        });
    },
    confirmDelete(item, tipo) {
      this.dataDialogConfirm = {
        item,
        tipo
      };
      this.dialogConfirm = true;

    },
    btnDialogConfirm() {
      if (this.dataDialogConfirm.tipo == 'n1') {
        this.eliminarRegistro(this.dataDialogConfirm.item);
      }
      if (this.dataDialogConfirm.tipo == 'n2') {
        this.eliminarRegistroN2(this.dataDialogConfirm.item);
      }
      this.dialogConfirm = false;
    },

    eliminarRegistroN2(item) {
      var url_ = "api/parametrica-api/" + item.id;
      axios.delete(url_).then((response) => {
        this.detalleRegistro(this.detalle);
        if (response.success=='true') {
          this.snackbar = {
            status: true,
            text: response.data.mensaje,
            color: "success"
          };
        }else{
          this.snackbar = {
            status: true,
            text: response.data.mensaje,
            color: "error"
          };
        }
      })
    },

    eliminarRegistro(item) {
      var url_ = "api/parametrica-api/" + item.id;
      axios.delete(url_).then((response) => {
        if (response.data.success=='true') {
          this.snackbar = {
            status: true,
            text: response.data.mensaje,
            color: "primary"
          };
        }else{
          this.snackbar = {
            status: true,
            text: response.data.mensaje,
            color: "error"
          };
        }
        this.getParametrica();
      })
      .catch((error) => {
        this.snackbar = {
          status: true,
          text: error,
          color: 'error',
        }
      });
    },
    detalleRegistro(item) { 
      this.tabla_seleccionada=item;
      axios.get('api/parametrica-api/' + item.param_tabla)
        .then((response) => {
          var data = response.data;
          this.detalle = data;
          this.registrosN2 = data;
        });
    },

    editarRegistro(item) {
      this.isEdit = true;
      this.dialog = true;
      this.formRegistro = {
        id: item.id,
        param_tabla: item.param_tabla
      };
      
    },

    editarRegistroN2(item) {
      this.isEditN2 = true;
      this.dialogN2 = true;
      this.formRegistroN2 = item;

    },

    nuevoRegistroN2() {
      this.isEditN2 = false;
      this.dialogN2 = true;
      this.formRegistroN2 = {
        id: "",
        param_foranea: "",
        param_codigo: "",
        param_nombre: "",
        param_detalle: ""
      };
      
    },

    nuevoRegistro() {
      this.isEdit = false;
      this.dialog = true;
      this.formRegistro = {
        param_tabla: ''
      };
    },

    submitN2: function () {
      var validateForm = this.$refs.formn2.validate();
      if (!validateForm) {
        return false;
      }
      this.loadingN2 = true;
      if (this.isEditN2) {
        axios
          .post("api/registrar_campo", this.formRegistroN2)
          .then((response) => {
            this.formRegistroN2 = {
              id: "",
              param_foranea: "",
              param_codigo: "",
              param_nombre: "",
              param_detalle: ""
            };
            this.dialogN2 = false;
            this.loadingN2 = false;
            this.snackbar = {
              status: true,
              text: "Registro Editado",
              color: "primary"
            };
            this.detalleRegistro(this.tabla_seleccionada);
          })
          .catch((error) => {
            this.loadingN2 = false;
          });
      } else {
        this.formRegistroN2.param_tabla = this.tabla_seleccionada.param_tabla;
          axios
            .post("api/registrar_campo", this.formRegistroN2)
            .then((response) => {
              this.detalleRegistro(this.tabla_seleccionada);
              this.dialogN2 = false;
              this.loadingN2 = false;
              this.snackbar = {
                status: true,
                text: "Nuevo Registro",
                color: "primary"
              };
            })
            .catch((error) => {
              this.loadingN2 = false;
            });

      }
    },

    submit: function () {
      var validateForm = this.$refs.form.validate();
      if (!validateForm) {
        return false;
      }
      this.loading = true;
      if (this.isEdit) {
        axios
          .post("api/parametrica-api", this.formRegistro)
          .then((response) => {
            this.getParametrica();
            this.formRegistro = {
              param_tabla: ''
            };
            this.dialog = false;
            this.loading = false;
            this.snackbar = {
              status: true,
              text: "Registro Actualizado",
              color: "warning"
            }
          })
          .catch((error) => {
            this.loading = false;

          });
      } else {
        axios
          .post("api/parametrica-api", this.formRegistro)
          .then((response) => {
            this.getParametrica();
            this.formRegistro = {
              param_tabla: ''
            };
            this.dialog = false;
            this.loading = false;
            this.snackbar = {
              status: true,
              text: "Nuevo Registro",
              color: "primary"
            }
          })
          .catch((error) => {
            this.loading = false;

          });
      }
    },
    verDocumentoIngreso() {
      this.urlPreVisualizar = null;
      this.dialog_report = true;
      var url_ = '/api/nota_ingreso_almacen';
      axios
        .get(url_)
        .then(response => {
          var dataBase64 = response.data.data;
          this.urlPreVisualizar = 'data:application/pdf;base64,' + dataBase64;

        })
        .catch(error => {

        })

    },
  },
  components: {

  },
}
</script>
