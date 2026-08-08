<template>
  <div>


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
            </v-col>
          </v-row>


          <template>
            <v-simple-table fixed-header>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Nro.</th>
                    <th class="text-left">Tabla</th>
                    <th class="text-left">Opciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in registros" :key="item.param_valor">
                    <td>{{ item.id }}</td>
                    <td>{{ item.param_tabla }}</td>
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
                <div class="text-2xl">{{ detalle.param_tabla }}</div>
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
                      <th class="text-left">Código</th>
                      <th class="text-left">Nombre</th>
                      <th class="text-left">Detalle</th>
                      <th class="text-left">Opciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in registrosN2" :key="item.param_valor">
                      <td>{{ item.param_codigo }}</td>
                      <td>{{ item.param_nombre }}</td>
                      <td>{{ item.param_detalle }}</td>
                      <td>
                        <v-btn icon color="error" @click="confirmDelete(item, 'n2')">
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>

                        <v-btn icon color="success" @click="editarRegistroN2(item)">
                          <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                      </td>
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

        <!----Nivel 2-->
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
    itemEdit: null,
    detalle: null,
    valid: false,
    validN2: false,
    isEdit: false,
    dialog: false,
    dialogN2: false,
    dialogConfirm: false,
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
    loadingN2: false

  }),
  computed: {

  },
  mounted() {

    this.getDatos();
  },
  watch: {

  },

  created() {

  },
  methods: {
    btnPruebas() {
      this.snackbar = {
        status: true,
        text: "hola mundo",
        color: "primary"
      };

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
      var url_ = "api/datos-registro/" + item.id;
      axios.delete(url_).then((response) => {
        this.detalleRegistro(this.detalle);
        this.snackbar = {
          status: true,
          text: "Eliminado",
          color: "primary"
        };
      })
    },

    eliminarRegistro(item) {
      var url_ = "api/datos-registro/" + item.id;
      axios.delete(url_).then((response) => {
        this.snackbar = {
          status: true,
          text: "Eliminado",
          color: "primary"
        };
        this.getDatos();
      })
        .catch((error) => {
          this.snackbar = {
            status: true,
            text: error.response.data.message,
            color: 'error',
          }

        });
    },
    detalleRegistro(item) {
      
      axios.get('api/datos-registro/detail/' + item.id)
        .then((response) => {
          var data = response.data;
          this.detalle = data.item;
          this.registrosN2 = data.rows;
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

        ///mod
        axios
          .put("api/datos-registro/" + this.formRegistroN2.id, this.formRegistroN2)
          .then((response) => {
            this.detalleRegistro(this.detalle);
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

          })
          .catch((error) => {
            this.loadingN2 = false;

          });
      } else {
        this.formRegistroN2.param_foranea = this.detalle.id,
          axios
            .post("api/datos-registro", this.formRegistroN2)
            .then((response) => {
              this.detalleRegistro(this.detalle);
              
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
          .put("api/datos-registro/" + this.formRegistro.id, this.formRegistro)
          .then((response) => {
            this.getDatos();
            this.formRegistro = {
              param_tabla: ''
            };
            this.dialog = false;
            this.loading = false;
            this.snackbar = {
              status: true,
              text: "Registro Editado",
              color: "primary"
            }
          })
          .catch((error) => {
            this.loading = false;

          });
      } else {
        axios
          .post("api/datos-registro", this.formRegistro)
          .then((response) => {
            this.getDatos();
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

    getDatos() {
      axios.get('api/datos-registro')
        .then((response) => {
          this.registros = response.data
        });
    },


  },
  components: {

  },
}
</script>
