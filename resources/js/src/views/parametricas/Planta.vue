
<style scoped>
.scroll-submenu {
  height: 370px;
  overflow-y: auto;
}
</style>
<template>
  <div>
    <v-card v-if="plantas">
      <v-toolbar flat>
        <v-toolbar-title></v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
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
      
      <v-data-table :headers="headers" :items="plantas" :search="search">
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
    <v-card v-if="!plantas">
      <v-card-text>
        <div class="text-center">
          <v-progress-circular :size="50" color="primary" indeterminate></v-progress-circular>
        </div>
      </v-card-text>
    </v-card>
    <template>
        <v-row justify="center">
          <!---- ventana emergente-->
          <v-dialog persistent scrollable v-model="dialog" max-width="990">
            <v-form v-model="valid" @submit.prevent="submit" lazy-validation ref="form" >
              <v-card>
                <v-card-title class="text-h5">{{ isEdit ? 'Editar' : 'Nuevo' }}</v-card-title>
                <v-card-subtitle>
                  Los campos con <span style="color: red;">*</span> son obligatorios.
                </v-card-subtitle>
                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="6" md="6">
                        <v-text-field v-model="formPlanta.codigo" outlined label="Codigo" hide-details="auto" :rules="rulesText">
                        </v-text-field>
                      </v-col>
                      <v-col cols="12" md="12">
                        <v-text-field v-model="formPlanta.nombre" outlined label="Nombre" hide-details="auto" :rules="rulesText">
                        </v-text-field>
                      </v-col>
                      <v-col cols="12" md="12">
                        <v-text-field v-model="formPlanta.descripcion" outlined label="Descripcion" hide-details="auto" :rules="rulesText">
                        </v-text-field>
                      </v-col>
                      <v-col cols="12" md="12">
                        <v-select v-model="formPlanta.tipo_acopio_id" :items="tipo_acopios" label="Seleccionar Tipo Acopio" item-text="param_nombre" item-value="param_valor"
                          outlined hide-details="auto" :rules="rulesSelect">
                        </v-select>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="formPlanta.latitud" outlined label="Latitud" hide-details="auto" v-show="false" :rules="rulesText">
                        </v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="formPlanta.longitud" outlined label="Longitud" hide-details="auto"  v-show="false" :rules="rulesText">
                        </v-text-field>
                      </v-col>


                      <v-col cols="12" md="6"> 
                        <v-select dense v-model="formPlanta.departamento_id" :items="departamentos" item-text="dep_nombre" item-value="id" menu-props="auto" label="Seleccionar Departamento" hide-details :rules="rulesText" prepend-icon="mdi-map-marker" single-line @change="getProvincias">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                      </v-col>    
                      <v-col cols="12" md="6"> 
                        <v-select dense v-model="formPlanta.provincia_id" :items="provincias" item-text="prv_provincia" item-value="id" menu-props="auto" label="Seleccionar Provincia" hide-details :rules="rulesText" prepend-icon="mdi-map-marker" single-line @change="getMinicipios">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                      </v-col>
                      <v-col cols="12" md="6"> 
                        <v-select dense v-model="formPlanta.municipio_id" :items="municipios" item-text="municipio" item-value="id_municipio" menu-props="auto" label="Seleccionar Municipio" hide-details :rules="rulesText" prepend-icon="mdi-map-marker" single-line @change="getLocalidades">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                      </v-col>
                      <v-col cols="12" md="6"> 
                        <v-select dense v-model="formPlanta.localidad_id" :items="localidades" item-text="localidad" item-value="id_localidad" menu-props="auto" label="Seleccionar Localidad" hide-details :rules="rulesText" prepend-icon="mdi-map-marker" single-line>
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                      </v-col>
                    </v-row>
                    <br>
                    <MapComponent @valueSent="recupera_data" :formPlanta_data="formPlanta" :controlEdit="isEdit" :data_polygono="data_polygono"  />
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
          <!---- final ventana emergente-->
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
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import { mdiPencilOutline } from '@mdi/js'
import MapComponent from './MapPlanta.vue'

export default {
  
  setup() {
    return {
      icons: {
        mdiPencilOutline,
      },
    }
  },
  data: () => ({
    dialogVisible: false,
    data_polygono: [],
    departamentos: [],
    provincias: [],
    municipios: [],
    localidades: [],

    plantas: null,
    formPlanta:{
      latitud: '',
      longitud: '',
    },
    roles: null,
    menus: [],
    subMenus: null,
    dialog:false,
    valid: false,
    loading: false,
    isEdit:false,
    dialogConfirm:false,
    selected: [],
    tipo_acopios: [],
    snackbar: {
      status: false,
      text: '',
    },
    search: '',
    headers: [
      { text: 'Codigo', value: 'codigo', sortable: true, align: 'center'},
      { text: 'Nombre', value: 'nombre', sortable: true, align: 'center'},
      { text: 'Descripcion', value: 'descripcion', sortable: true, align: 'center'},
      { text: 'Acciones', value: 'acciones', sortable: false, align: 'center'},
    ],
    dataDialogConfirm: {},
  }),
  
  computed: {
    rulesText() {
      return [
        v => !!v || 'Es requerido',
        //v => !Number.isInteger(Number(v)) || 'No se admite números',
        //v => (v && v.length > 0) || 'Es requerido',
      ]
    },
    rulesSelect() {
      return [
        v => !!v || 'Es requerido',
      ]
    },
  },
  mounted() {
    this.getPlantas();
    this.getTipoAcopio();
    this.getDepartamentos();
    
  },
  watch:{
    dialog(newValue){
      if(!newValue){
        window.location.reload();
      }
    }
  },
  created() { },
  methods: {
    getDepartamentos() {
        this.departamentos= [],
        this.provincias= [],
        this.municipios= [],
        this.localidades= [],
        this.cargando = true;
        axios
            .get('api/listar_departamento')
            .then(response => {
                this.departamentos = response.data.data;
                this.cargando = false;
            })
            .catch(error => { 
                this.snackbar = {
                status: true,
                text: error.response.data.message,
                color: "error",
            };
            this.cargando = false;
            })
    },
    getProvincias(params) {
        this.provincias= [],
        this.municipios= [],
        this.localidades= [],
        this.cargando = true;
        axios
            .get('api/listar_provincia/' + params)
            .then(response => {
                this.provincias = response.data;
                this.cargando = false;
            })
            .catch(error => { 
                this.snackbar = {
                status: true,
                text: error.response.data.message,
                color: "error",
            };
            this.cargando = false;
            })
    },
    getMinicipios(params) {
        this.municipios= [],
        this.localidades= [],
        this.cargando = true;
        axios
            .get('api/listar_municipio/' + params)
            .then(response => {
                this.municipios = response.data;
                this.cargando = false;
            })
            .catch(error => { 
                this.snackbar = {
                status: true,
                text: error.response.data.message,
                color: "error",
            };
            this.cargando = false;
            })
    },
    getLocalidades(params) {
        this.localidades= [],
        this.cargando = true;
        axios
            .get('api/listar_localidad/' + params)
            .then(response => {
                this.localidades = response.data;
                this.cargando = false;
            })
            .catch(error => { 
                this.snackbar = {
                status: true,
                text: error.response.data.message,
                color: "error",
            };
            this.cargando = false;
            })
    },
    recupera_data(latlong){
      this.formPlanta.latitud  = latlong[1];
      this.formPlanta.longitud = latlong[0];
      
      var validateForm = this.$refs.form.validate();
      if (!validateForm) {
        return false
      }
      
    },
    getTipoAcopio(){
      this.cargando = true;
      axios
        .get('api/listar_tipos_data_acopio')
        .then(response => {
          this.tipo_acopios = response.data.data;
          this.cargando = false;
        })
        .catch(error => { })
    },
    getPlantas() {
      axios
        .get('api/planta')
        .then(response => {
          this.plantas = response.data.data;
        })
        .catch(error => { })
    },
    btnEditar(item) {
      this.formPlanta = item;
      this.dialog = true;
      this.isEdit = true;
      this.getProvincias(item.departamento_id);
      this.getMinicipios(item.provincia_id);
      this.getLocalidades(item.municipio_id);
    },
     btnNuevo() {
      this.formPlanta = {};
      if (this.$refs.form) {
        this.$refs.form.reset();
      }
      this.isEdit = false;
      this.dialog = true;
    },
    submit: function () {

      var validateForm = this.$refs.form.validate()
      if (!validateForm) {
        return false
      }
      
      
      this.loading = true
      if (this.isEdit) {
        axios
          .post('api/planta', this.formPlanta)
          .then(response => {
            this.getPlantas();
            this.formPlanta = {},
            this.$refs.form.reset();
            this.dialog = false;
            this.loading = false;
            this.snackbar = {
              status: true,
              text: 'Registro Actualizado',
              color: 'primary',
            };
            window.location.reload();
          })
          .catch(error => {
            this.loading = false
          })
      } else {
        axios
          .post('api/planta', this.formPlanta)
          .then(response => {
            this.getPlantas();
            this.formCliente = {},
            this.dialog = false;
            this.loading = false;
            this.snackbar = {
              status: true,
              text: 'Nuevo Registro',
              color: 'primary',
            };
            window.location.reload();
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
    btnDialogConfirm() {
      var url_ = 'api/planta/' + this.dataDialogConfirm.id
      axios.delete(url_).then(response => {
        this.dialogConfirm = false
        this.snackbar = {
          status: true,
          text: 'Eliminado',
          color: 'primary',
        }
        this.getPlantas()
      })
    },
    btncConfirmEliminar(item) {
      this.dataDialogConfirm = item
      this.dialogConfirm = true
    },
  },
  components: {
    Multiselect,
    MapComponent
  },
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css">
</style>
