<style scoped>
.pad-td {
    padding-top: 9px !important;
    padding-bottom: 9px !important;
}

td,
th {
    border: 1px solid #e4e3e6;
}
</style>
<template>
    <div>
        <v-card class="mx-auto" outlined>
            <v-card-title>
                ADMINISTRAR PLANTAS - ALMACEN/SILOS/GALPONES
            </v-card-title>
        </v-card>
        <br>
        <v-dialog v-model="dialogSucursal" persistent max-width="390">
            <v-form v-model="validSucursal" lazy-validation @submit.prevent="submitSucursal" ref="formSucursal">
                <v-card>
                    <v-card-title class="text-h5">
                        {{ isFormSucursalEdit ? 'Editar ' : 'Nuevo ' }}
                    </v-card-title>
                    <v-card-text>
                        <v-text-field v-model="formularioSucursal.label" filled label="Nombre" required dense
                            :rules="[v => !!v || 'El Nombre es requerido']"></v-text-field>

                        <v-text-field v-model="formularioSucursal.route" filled label="Ruta" dense></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn class="ma-2" elevation="10" small color="error" @click="dialogSucursal = false">
                            <v-icon small>mdi-close</v-icon>Cerrar
                        </v-btn>
                        <v-btn class="ma-2" elevation="10" small :loading="btnLoadingSucursal"
                            :disabled="btnLoadingSucursal || !validSucursal" type="submit" color="primary">
                            {{ isFormSucursalEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>
        <v-dialog v-model="dialogPunto" persistent scrollable max-width="600">
            <v-form v-model="validSucursal" lazy-validation @submit.prevent="registroPunto" ref="formPunto">
                <v-card>
                    <v-card-title class="text-h5">
                        {{ isFormPuntoVentaEdit ? 'Editar ' : 'Nuevo ' }}
                    </v-card-title>
                    <v-card-subtitle>- </v-card-subtitle>
                    <!-- Revisar la parte de Editar :rules="rulesSelect"  -->
                    <v-card-text>
                        <div class="row"><div class="col">
                        <v-select dense v-model="formularioPunto.tipo_jerarquia_id" :items="jerarquiaTipos" label="Seleccionar Tipo Jerarquie" item-text="nombre" item-value="id" outlined hide-details="auto" required :disabled="isDisabled">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                        </div>
                        <div class="col">  
                        <v-select dense v-model="formularioPunto.tipo_punto" :items="TipoPuntos" label="Seleccionar Tipo Puntos" item-text="nombre" item-value="nombre" outlined hide-details="auto" required :disabled="isDisabled">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                        </div></div>    
                        <div class="row"><div class="col-8">                         
                        <v-text-field dense v-model="formularioPunto.nombre" outlined  label="Nombre Ingenio / Almacen / Silo / Galpon" required >
                            <!-- :rules="rulesText" -->
                        </v-text-field>
                        </div><div class="col-4"> 
                        <v-text-field dense v-model="formularioPunto.codigo" outlined  label="Codigo" :counter="10" required>
                            <!-- :rules="rulesTextLimit" -->
                        </v-text-field>
                        </div></div>    
                        <div class="row"><div class="col"> 
                        <v-select dense v-model="formularioPunto.departamento_id" :items="departamentos" item-text="dep_nombre" item-value="id" menu-props="auto" label="Seleccionar Departamento" hide-details prepend-icon="mdi-map-marker" single-line @change="getProvincias">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                        </div>    
                        <div class="col"> 
                        <v-autocomplete dense v-model="formularioPunto.provincia_id" :items="provincias" item-text="prv_provincia" item-value="id" menu-props="auto" label="Seleccionar Provincia" hide-details prepend-icon="mdi-map-marker" single-line @change="getMinicipios">
                            <!-- :rules="rulesSelect" -->
                        </v-autocomplete>
                        </div></div>    
                        <div class="row"><div class="col"> 
                        <v-select dense v-model="formularioPunto.municipio_id" :items="municipios" item-text="municipio" item-value="id_municipio" menu-props="auto" label="Seleccionar Municipio" hide-details prepend-icon="mdi-map-marker" single-line @change="getLocalidades">
                            <!-- :rules="rulesSelect" -->
                        </v-select>
                        </div>
                        <div class="col"> 
                        <v-autocomplete dense v-model="formularioPunto.localidad_id" :items="localidades" item-text="localidad" item-value="id_localidad" menu-props="auto" label="Seleccionar Localidad" hide-details prepend-icon="mdi-map-marker" single-line>
                            <!-- :rules="rulesSelect" -->
                        </v-autocomplete>
                        </div></div>
                        <div class="row"><div class="col">
                            <v-autocomplete dense prepend-icon="mdi mdi-smoke-detector" v-model="formularioPunto.programa_id" :items="programas" item-text="prog_nombre" item-value="programa_id" menu-props="auto" label="Seleccionar Programa" hide-details single-line> 
                            </v-autocomplete>
                        </div></div>  
                        <div class="row"><div class="col"> 
                        <v-textarea dense prepend-icon="mdi-comment" rows="1" v-model="formularioPunto.direccion" label="Direccion" outlined  required >
                            <!-- :rules="rulesText" -->
                        </v-textarea>
                        </div></div>    
                        <div class="row"><div class="col">                         
                        <v-textarea dense prepend-icon="mdi-comment" rows="1" v-model="formularioPunto.descripcion" label="Descripcion" outlined  required >
                            <!-- :rules="rulesText" -->
                        </v-textarea>
                    </div></div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn class="ma-2" elevation="10" small color="error" @click="dialogPunto = false">
                            <v-icon small>mdi-close</v-icon>Cerrar
                        </v-btn>
                        <v-btn class="ma-2" elevation="10" small :loading="btnLoadingSucursal"
                            :disabled="btnLoadingSucursal || !validSucursal" type="submit" color="primary">
                            {{ isFormPuntoVentaEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>
        <div v-if="sucursales">
            <div v-if="sucursales.length == 0">
                <div class="text-center">
                    Sin Datos
                </div>
            </div>
            <div v-if="sucursales.length != 0">
                <v-row v-for="item in sucursales" :key="item.id">
                    <v-col cols="12">
                        <v-card outlined>
                            <v-card-title>
                                <strong>PLANTAS:  </strong> {{ item.nombre }} - {{ item.codigo }}
                                <v-btn icon color="green" @click="detallePuntos(item)">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                            </v-card-title>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="6">
                                        <v-btn small color="indigo" outlined @click="btnListarPuntos(item)">Jerarquia
                                            <v-icon right dark>mdi-store-search-outline</v-icon>
                                        </v-btn>
                                    </v-col>
                                    <v-col cols="6">
                                        <div class="d-flex justify-end">
                                            <v-btn small color="primary" dark @click="btnNuevoPunto(item)">
                                                Almacen / Silo
                                            </v-btn>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </div>
        </div>
        <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
            </template>
        </v-snackbar>


        <!--DETALLE DE LOS PUNTOS (SILOS/ALAMCENES)-->
        <template>
            <v-row justify="center">
                <!---- ventana emergente-->
                <v-dialog persistent scrollable v-model="dialogPuntos" max-width="60%" height="80%">
                <v-form v-model="valid" lazy-validation ref="form1">
                    <v-card>
                        <v-card-title class="title">
                        <v-icon large left>mdi-home-assistant</v-icon>
                        <span class="text-h6 font-weight-light">PUNTOS (SILOS/ALMACENES)</span>
                        </v-card-title>
                        <v-card-subtitle class="text-h6 d-flex justify-center">{{ this.planta }}</v-card-subtitle>
                        <v-card-text>
                        <v-container>
                            <v-row>
                                <v-expansion-panels v-model="panel" multiple>
                                            <v-expansion-panel>
                                                <v-expansion-panel-header>
                                                </v-expansion-panel-header>
                                                <v-expansion-panel-content>
                                                    <v-data-table :headers="headersPuntos" :items="puntos" :search="search">
                                                    <template v-slot:item.acciones="{ item }">
                                                        <v-tooltip bottom>
                                                            <template v-slot:activator="{ on: tooltip }">
                                                                <v-btn v-on="{ ...tooltip }" @click="detallePuntos(item)" icon color="success" title="Detalle Puntos">
                                                                <v-icon>mdi-home-circle</v-icon>
                                                            </v-btn>
                                                            </template>
                                                        </v-tooltip>
                                                    </template>
                                                    </v-data-table>
                                                </v-expansion-panel-content>
                                            </v-expansion-panel>
                                        </v-expansion-panels>
                            </v-row>
                        </v-container>
                        </v-card-text>
                        <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn class="ma-2" elevation="10" small color="error" @click="dialogPuntos = false">
                        <v-icon small>mdi-close</v-icon>Cerrar
                        </v-btn>
                    </v-card-actions>
                    </v-card>
                </v-form>
                </v-dialog>
                <!---- final ventana emergente-->
            </v-row>
        </template>
    </div>

    
</template>

<script>
export default {
    data: () => ({

        dialogSucursal: false,
        validSucursal: false,
        btnLoadingSucursal: false,
        isFormSucursalEdit: false,

        isFormPuntoVentaEdit: false,
        tipoPuntoVentaList: null,
        formularioSucursal: {},
        sucursales: null,
        puntoVentas: null,
        loaderCufd: false,
        snackbar: {
            status: false,
            text: "",
        },

        dialogPunto: false,
        formularioPunto: {},

        departamentos: [],
        provincias: [],
        municipios: [],
        localidades: [],
        TipoPuntos: [
            { id: "1", nombre: "Silo" },
            { id: "2", nombre: "Almacen" },
            { id: "3", nombre: "Ingenio" },
        ],
        jerarquiaTipos: [
            { id: "0", nombre: "Punto Origen" },
            { id: "1", nombre: "Punto" },
        ],
        headersPuntos: [
        { text: 'Acciones', value: 'acciones', sortable: false },
        { text: 'Tipo Punto', value: 'tipo', sortable: true },
        { text: 'Arbol', value: 'padre', sortable: true },
        { text: 'Nombre', value: 'nombre', sortable: true },
        { text: 'Codigo', value: 'codigo', sortable: true },
        { text: 'Direccion', value: 'direccion', sortable: true },
        ],
        isDisabled: false,
        puntos: [],
        panel: [0],
        search: '',
        dialogPuntos: false,
        valid: false,
        planta: '',
        programas: [],
    }),

    computed: {
        rulesNumber() {
        return [
            (v) => !!v || "Es requerido",
            (v) => Number.isInteger(Number(v)) || "No se admite letras y decimales",
            (v) => v > 0 || "Es requerido ",
        ];
        },
        rulesBigNumber() {
        return [
            (v) => !!v || "Es requerido",
            //(v) => Number.isInteger(Number(v)) || "No se admite letras y decimales",
            (v) => v > 0 || "Es requerido ",
        ];
        },
        rulesSelect() {
        return [(v) => !!v || "Es requerido"];
        },

        rulesCelular() {
        return [
            (v) => !!v || "Es requerido",
            (v) => Number.isInteger(Number(v)) || "No se admite letras",
            (v) =>
            (v >= 0 && v.length >= 8 && v.length <= 8) ||
            "se requiere 8  digitos ",
            (v) =>
            v.substr(0, 1) == "6" ||
            v.substr(0, 1) == "7" ||
            "Es requerido un numero de celular valido",
        ];
        },

        rulesCorreo() {
        return [
            (v) => !!v || "Es requerido",
            (v) => /.+@.+\..+/.test(v) || "El correo debe ser válido",
        ];
        },

        rulesNumberDecimal() {
        return [
            (v) => !!v || "Es requerido",
            // v => Number.isInteger(Number(v)) || 'No se admite letras',
            (v) => v > 0 || "La cantidad debe ser mayor a 0",
        ];
        },
        
        rulesNumberDecimalParametro() {
        return [
            (v) => !!v || "Es requerido",
            // v => Number.isInteger(Number(v)) || 'No se admite letras',
            (v) => v >= 0 || "La cantidad debe ser mayor o igual a 0",
        ];
        },
        rulesNumberDecimal_Peso() {
        return [
            (v) => !!v || "Es requerido",
            // v => Number.isInteger(Number(v)) || 'No se admite letras',
            (v) => v >= 0 || "La cantidad debe ser mayor a 0",
        ];
        },
        rulesText() {
        return [
            (v) => !!v || "Es requerido",
            (v) => !Number.isInteger(Number(v)) || "No se admite números",
            (v) => (v && v.length > 0) || "Es requerido",
        ];
        },
        rulesTextLimit() {
        return [
            (v) => !!v || "Es requerido",
            (v) => !Number.isInteger(Number(v)) || "No se admite números",
            (v) => (v && v.length <= 10) || "Es requerido",
        ];
        },
        rulesTextNroIdentificacion() {
        return [
            (v) => !!v || "Es requerido",
            (v) => (v && v.length >= 0) || "Es requerido",
        ];
        },
        rulesCantidad() {
        return [
            (v) => !!v || "Es requerido",
            (v) => Number.isInteger(Number(v)) || "No se admite letras",
            (v) =>
            v <= this.formFacturaDetalle.stock ||
            "La cantidad tiene que ser menor al stock",
            (v) => v > 0 || "La cantidad debe ser mayor a 0",
        ];
        },
    },
    mounted() {
        this.getSucursales();
        this.getDepartamentos();
        this.getProgramas();
    },
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
        getPuntos() {
            axios
                .get('api/silo')
                .then(response => {
                    this.puntos = response.data.data;
                })
                .catch(error => { })
        },
        getProgramas() {
            axios
                .get('api/listar_programas')
                .then(response => {
                    this.programas = response.data;
                })
                .catch(error => { })
        },
        registroPunto: function () {
            if(this.isFromPuntoVentaEdit){
                this.guardarCambiosPunto();
            }else{
            var validateForm = this.$refs.formPunto.validate()
            if (!validateForm) {
                return false
            }
            this.cargando = true;
            axios
                .post("api/registro_punto", this.formularioPunto)
                .then((response) => {
                    if (response.data.success == 'true'){
                        if (this.$refs.formPunto) {
                        this.$refs.formPunto.reset();
                        }
                        this.cargando = false;
                        this.dialogPunto = false;
                        this.getPuntos();
                        this.snackbar = {
                            status: true,
                            text: 'Registro del Punto correctamente',
                            color: 'primary',
                        };
                    }
                    else {
                        this.snackbar = {
                        status: true,
                        text: response.data.mensaje,
                        color: "error",
                        };
                    }
                })
                .catch(error => {
                    this.snackbar = {
                    status: true,
                    text: error.response.data.mensaje,
                    color: "error",
                    };
                    this.cargando = false;
                });
            }
        },

        btnListarPuntos(planta) {
            this.puntos= [],
            this.cargando = true;
            axios
                .get("api/obtener_punto/" + planta.id)
                .then(response => {
                    this.dialogPuntos = true;
                    this.puntos = response.data.data;
                    this.planta = planta.nombre;
                    this.cargando = false;
                    this.snackbar = {
                            status: true,
                            text: 'Puntos Listados',
                            color: 'primary',
                        };
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
        obtenerPlanta(planta_id){
            this.planta= '',
            this.cargando = true;
            axios
                .get("api/obtener_planta/" + planta_id)
                .then(response => {
                    this.dialogPuntos = true;
                    this.puntos = response.data.data;
                    this.cargando = false;
                    this.snackbar = {
                            status: true,
                            text: 'Puntos Listados',
                            color: 'primary',
                        };
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
        getSucursales() {
            axios
                .get('api/planta')
                .then(response => {
                    this.sucursales = response.data.data;
                })
                .catch(error => { })
        },
        btnNuevoPunto(planta) {
            this.dialogPunto = true;
            this.formularioPunto.planta_id = planta.id;

        },

        submitSucursal: function () {
            var validateForm = this.$refs.formSucursal.validate()
            if (!validateForm) {
                return false
            }
        },

        btnNuevoPuntoVenta(sucursal) {
            this.dialogPunto = true;
            if (this.$refs.formPunto) {
                this.$refs.formPunto.reset();
            }
            this.formularioPunto.sucursal_id = sucursal.id;
        },

        btnNuevaSucursal() {
            this.dialogSucursal = true;
        },

        btnItemSucursal(sucursal) {
            this.puntoVentas = sucursal.punto_ventas;
        },

        btnAsignarUsuario(item) {
            
        },

        btnGetCuis(item) {
            item.loader.cuis = true;
            axios.get('api/siat/comex/cuis-v2/' + item.id).then(response => {
                var resp = response.data;
                item.loader.cuis = false;
                this.getSucursales();
                this.snackbar = {
                    status: true,
                    text: 'CUIS Solicitado',
                    color: 'primary',
                }
            }).catch((error) => {
                this.snackbar = {
                    status: true,
                    text: error.response.data.message,
                    color: "error",
                };
                item.loader.cuis = false;
            });
        },

        btnGetCuft(item) {
            item.loader.cufd = true;
            axios.get('api/siat/comex/cufd-v2/' + item.id).then(response => {
                var resp = response.data;
                item.loader.cufd = false;
                this.getSucursales();
                this.snackbar = {
                    status: true,
                    text: 'CUFD Solicitado',
                    color: 'primary',
                }

            }).catch((error) => {
                this.snackbar = {
                    status: true,
                    text: error.response.data.message,
                    color: "error",
                };
                item.loader.cufd = false;
            });
        },
        detallePuntos(item) {
            this.idFromPuntoVentaEdit = true;
            this.dialogPunto = true;
            this.formularioPunto = {
                id: item.id,
                tipo_jerarquia_id: item.tipo_jerarquia_id,
                tipo_punto: item.tipo_punto,
                nombre: item.nombre,
                codigo: item.codigo,
                departamento_id: item.departamento_id,
                provincia_id: item.provincia_id,
                municipio_id: item.municipio_id,
                localidad_id: item.localidad_id,
                programa_id: item.programa_id,
                direccion: item.direccion,
                descripcion: item.descripcion,
            }
        },

    },



}
</script>