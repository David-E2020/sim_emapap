<template>
        <v-container fluid class="grey lighten-5">
        <loading :active.sync="isLoading"
            :is-full-page="fullPage">
        </loading>
        <div class="row">
            <div class="col-md-6">
                <v-card>
                <v-card-title>
                    <h3>Biometricos</h3>
                <v-spacer></v-spacer>
                <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn>
                </v-card-title>
                <div class="container">
                    <div class="alert alert-success" role="alert">
                    Sincronizacion automatica <strong> 12:30 pm</strong> Lunes  a Viernes
                </div>
                <div class="alert alert-warning" role="alert">
                    Sincronizacion automatica <strong> 18:30 pm</strong> Lunes  a Viernes
                </div>
                <div class="alert alert-danger" role="alert">
                    Sincronizacion automatica <strong> 23:30 pm</strong> Lunes  a Viernes
                </div>
                </div>
                
                <v-card-text>
                    <vue-bootstrap4-table :rows="biometrics" :columns="columns" :config="config" >
                        <template slot="sort-asc-icon">
                            <i class="fa fa-sort-asc"></i>
                        </template>
                        <template slot="sort-desc-icon">
                            <i class="fa fa-sort-desc"></i>
                        </template>
                        <template slot="no-sort-icon">
                            <i class="fa fa-sort"></i>
                        </template>
                        <template slot="pagination-info" slot-scope="props">
                            De {{props.currentPageRowsLength}}
                            a {{props.filteredRowsLength}}
                            ({{props.originalRowsLength}} Total Registros)
                        </template>

                        <template slot="option" slot-scope="props">
                            <v-layout justify-space-around>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                        <v-icon @click="sync(props.row)" v-on="on">
                                            sync
                                        </v-icon>
                                </template>
                                <span>Sincronizar Asistencias</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="edit(props.row)" v-on="on">
                                        edit
                                    </v-icon>
                                </template>
                                <span>Editar Biometrico </span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="user_list(props.row)" v-on="on">
                                        person
                                    </v-icon>
                                </template>
                                <span>Ver usuarios</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="user_reset(props.row)" v-on="on">
                                        lock
                                    </v-icon>
                                </template>
                                <span>Resetear Biometrico</span>
                                </v-tooltip>
                                 <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="user_desactivar(props.row)" v-on="on" class="fa fa-stop">
                                    </v-icon>&nbsp;
                                </template>
                                <span>Desactivar</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="user_desbloquear(props.row)" v-on="on" class="fa fa-unlock">

                                    </v-icon>&nbsp;
                                </template>
                                <span>Desbloquear BIometrico</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="user_activar(props.row)" v-on="on" class="fa fa-bolt">

                                    </v-icon>
                                </template>
                                <span>Activar Biometrico</span>
                                </v-tooltip>

                            </v-layout>

                        </template>
                    </vue-bootstrap4-table>
                </v-card-text>
                <edit-biometric :dialog="dialog" :biometric="biometric" @close="close"  @biometric="update"></edit-biometric>
                <sync-biometric :dialog="dialog_sync" :biometric="biometric" @close="close_sync" ></sync-biometric>
            </v-card>
            </div>
            <div class="col-md-6">
                 <v-card>
                <v-card-title>
                    <h3>Usuarios Biometrico <span class="badge badge-pill badge-info">{{biometrico_data.ip}}</span></h3>
                <v-spacer></v-spacer>
                <v-btn @click="createUser();" color="primary" dark class="mb-2" data-toggle="modal" data-target="#myModal">Nuevo</v-btn>
                </v-card-title>
                <v-card-text>
                    <vue-bootstrap4-table :rows="users_biometric" :columns="columns_user" :config="config_user" >
                        <template slot="sort-asc-icon">
                            <i class="fa fa-sort-asc"></i>
                        </template>
                        <template slot="sort-desc-icon">
                            <i class="fa fa-sort-desc"></i>
                        </template>
                        <template slot="no-sort-icon">
                            <i class="fa fa-sort"></i>
                        </template>
                        <template slot="pagination-info" slot-scope="props">
                            De {{props.currentPageRowsLength}}
                            a {{props.filteredRowsLength}}
                            ({{props.originalRowsLength}} Total Registros)
                        </template>

                        <template slot="option" slot-scope="props">
                            <v-layout justify-space-around>
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon @click="delete_user(props.row)" v-on="on">
                                        delete
                                    </v-icon>
                                </template>
                                <span>Editar Biometrico </span>
                                </v-tooltip>


                            </v-layout>

                        </template>
                    </vue-bootstrap4-table>
                </v-card-text>
                <edit-biometric :dialog="dialog" :biometric="biometric" @close="close"  @biometric="update"></edit-biometric>
                <sync-biometric :dialog="dialog_sync" :biometric="biometric" @close="close_sync" ></sync-biometric>
            </v-card>
            </div>
                        <!-- The Modal -->
            <div class="modal inmodal fade" id="myModal" tabindex="-5" role="dialog"  aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">

                  <!-- Modal Header -->
                  <div class="modal-header">
                    <h4 class="modal-title">Funcionarios Publicos Sincronizacion a Biometricos</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>

                  <!-- Modal body -->
                  <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="staticEmail" class="col-sm-2 col-form-label">Biometrico</label>
                                <div class="col-sm-10">
                                    <select class="form-control" v-model="data_biometrico" @change="getInfo()">
                                        <option selected>Seleccione el biometrico</option>
                                        <option :value="item.id" v-for="item in biometrics">{{ item.name }} ({{item.ip}})</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="inputPassword" class="col-sm-2 col-form-label">Sincronizar Funcionarios</label>
                                <div class="col-sm-10">
                                    <v-icon @click="sync_users()">
                                            sync
                                    </v-icon>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="inputPassword" class="col-sm-2 col-form-label">Conexion Datos</label>
                                <div class="col-sm-10">
                                    <span class="badge badge-pill badge-info">{{name}}</span>
                                    <span class="badge badge-pill badge-info">{{time}}</span>
                                </div>
                              </div>
                              <div class="form-group row">
                                  <vue-bootstrap4-table :rows="funcionario" :columns="columns_funcionario" :config="config_funcionario" >
                                    <template slot="sort-asc-icon">
                                        <i class="fa fa-sort-asc"></i>
                                    </template>
                                    <template slot="sort-desc-icon">
                                        <i class="fa fa-sort-desc"></i>
                                    </template>
                                    <template slot="no-sort-icon">
                                        <i class="fa fa-sort"></i>
                                    </template>
                                    <template slot="pagination-info" slot-scope="props">
                                        De {{props.currentPageRowsLength}}
                                        a {{props.filteredRowsLength}}
                                        ({{props.originalRowsLength}} Total Registros)
                                    </template>

                                    <template slot="option" slot-scope="props">
                                        <v-layout justify-space-around>
                                            <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-icon @click="edit(props.row)" v-on="on">
                                                    edit
                                                </v-icon>
                                            </template>
                                            <span>Editar Biometrico </span>
                                            </v-tooltip>
                                        </v-layout>
                                    </template>
                                </vue-bootstrap4-table>
                              </div>                        
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header card-calendar">
                                    <h4 class="card-title ">
                                      Registrar Funcionario al Biometrico
                                    </h4>
                                    <div class="card-tools">
                                    </div>
                                </div>
                            <div class="card-body">
                                <form>
                                  <div class="form-group row">
                                    <label for="staticEmail" class="col-sm-3 col-form-label">Nombre Completo</label>
                                    <div class="col-sm-9">
                                       <!--model-select :options="funcionarios"
                                            v-model="select"    
                                            placeholder="seleccionar funcionario"
                                            :selectedItem="onselect"
                                            >
                                        </model-select-->
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <label for="inputPassword" class="col-sm-3 col-form-label">Gerencia</label>
                                    <div class="col-sm-9">
                                      <input type="text" class="form-control" id="gerencia" placeholder="Ingrese Codigo"  v-model="select.gerencia">
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <label for="inputPassword" class="col-sm-3 col-form-label">Cargo</label>
                                    <div class="col-sm-9">
                                      <input type="text" class="form-control" id="cargo" placeholder="Ingrese Codigo"  v-model="select.cargo">
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <label for="inputPassword" class="col-sm-3 col-form-label">Codigo Biometrico</label>
                                    <div class="col-sm-9">
                                      <input type="number" class="form-control" id="codigo_biometrico" placeholder="Ingrese Codigo"  v-model="select.biometrico">
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <label for="inputPassword" class="col-sm-3 col-form-label">Password</label>
                                    <div class="col-sm-9">
                                      <input type="text" class="form-control" id="password" placeholder="Ingrese Password"  v-model="select.ci">
                                    </div>
                                  </div>
                                </form>
                            </div>
                            <div class="card-footer text-right">
                                <v-spacer></v-spacer>
                                  <v-btn color="blue darken-1" flat @click="RegistrarUsuario()">Registrar usuario</v-btn>
                            </div>
                            </div>
                        </div>
                        </div>
                  </div>

                  <!-- Modal footer -->
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                  </div>

                </div>
              </div>
            </div>
            <div class="modal inmodal fade" id="myUpdate" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">

                  <!-- Modal Header -->
                  <div class="modal-header">
                    <h4 class="modal-title">Funcionarios Publicos</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>

                  <!-- Modal body -->
                  <div class="modal-body">
                    Modal body..
                  </div>

                  <!-- Modal footer -->
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                  </div>

                </div>
              </div>
            </div>
        </div>
            
    </v-container>


</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditBiometric from './Edit.vue';
import SyncBiometric from './Sync.vue';

export default {
    data:()=>({
        biometrics:[],
        biometrico_data:{},
        users_biometric:[],
        biometric:{},
        user_registro:{},
        dialog:false,
        dialog_sync:false,
        valid: true,
        isLoading: false,
        fullPage: true,
        data_biometrico:'',
        name:'',
        time:'',
        funcionario:[],
        data_funcionario:{},
         select: {},
        funcionarios:[],
        items: [
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
        ],
        columns: [
            {
                label: "Codigo",
                name: "id",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Codigo"
                },
                sort: true,
            },
            {
                label: "Nombre",
                name: "name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "Direccion",
                name: "address",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Direccion"
                },
                sort: true,
            },
            {
                label: "IP",
                name: "ip",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese ip"
                },
                sort: true,
            },
            {
                label: "Puerto",
                name: "port",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese port"
                },
                sort: true,
            },

            {
                label: "Opciones",
                name: "option",
                sort: false,
            }],

        config: {
            checkbox_rows: false,
            rows_selectable: false,
            pagination: true,
            card_mode: false,
            show_refresh_button:  false,
            show_reset_button:  false,
            global_search:  {
                placeholder:  "Enter custom Search text",
                visibility:  false,
                case_sensitive:  false
            },
            per_page_options:  [5,  10,  20,  30],
            server_mode:  false,
        },

        columns_user: [
            {
                label: "ID Biometrico",
                name: "id_biometrico",
                filter: {
                    type: "simple",
                    placeholder: "ID"
                },
                sort: true,
            },
            {
                label: "Nombre Completo",
                name: "nombre_completo",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "RFID",
                name: "rf_id",
                filter: {
                    type: "simple",
                    placeholder: "RFID"
                },
                sort: true,
            },
            {
                label: "Rol",
                name: "rol",
                filter: {
                    type: "simple",
                    placeholder: "Rol"
                },
                sort: true,
            },
            {
                label: "Passowrd",
                name: "password",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Password"
                },
                sort: true,
            },

            {
                label: "Opciones",
                name: "option",
                sort: false,
            }],

        config_user: {
            checkbox_rows: false,
            rows_selectable: false,
            pagination: true,
            card_mode: false,
            show_refresh_button:  false,
            show_reset_button:  false,
            global_search:  {
                placeholder:  "Enter custom Search text",
                visibility:  false,
                case_sensitive:  false
            },
            per_page_options:  [5,  10,  20,  30],
            server_mode:  false,
        },


        columns_funcionario: [
            {
                label: "Biometrico",
                name: "biometric.name",
                filter: {
                    type: "simple",
                    placeholder: "ID"
                },
                sort: true,
            },
            {
                label: "Nombre Completo",
                name: "full_name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "Codigo Biometrico",
                name: "code_biometric",
                filter: {
                    type: "simple",
                    placeholder: "RFID"
                },
                sort: true,
            },
            {
                label: "Estado",
                name: "state",
                filter: {
                    type: "simple",
                    placeholder: "Rol"
                },
                sort: true,
            },

            {
                label: "Password",
                name: "password",
                sort: false,
            }],

        config_funcionario: {
            checkbox_rows: false,
            rows_selectable: false,
            pagination: true,
            card_mode: false,
            show_refresh_button:  false,
            show_reset_button:  false,
            global_search:  {
                placeholder:  "Enter custom Search text",
                visibility:  false,
                case_sensitive:  false
            },
            per_page_options:  [5,  10,  20,  30],
            server_mode:  false,
        },


    }),
    mounted(){
        this.search();
    },
    methods:{
        search(){
            axios.get('/api/biometric')
                 .then((response)=>{
                    // this.employees = response.data;
                    this.biometrics = response.data.biometrics;
                });
        },
        create() {
            this.biometric ={};
            this.dialog = true;
        },
        createUser(){
            this.user_registro={};
            this.listEmploye();
        },
        sync(item){
            this.biometric = item;
            this.dialog_sync = true;
        },
        sync_users(){
            this.$swal({
             title: '¿Esta seguro de sincronizar los datos del biometrico a la base de datos de RRHH?',
              text: 'Verifique los datos de los usuarios del biometrico para realizar la sincronizacion',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Si, Registrar!',
              cancelButtonText: 'No, cancelar',
              showCloseButton: true,
              showLoaderOnConfirm: true
              }).then((result) => {
              if(result.value) {
                this.isLoading = true;
                var data={"biometrico":this.data_biometrico};
                 axios.post('/api/sincronizarUsers',data)
                .then(response=>{
                    this.isLoading = false;
                    iziToast.success({
                        position: 'topRight',
                        title: 'Se sincronizo los datos exitosamente',
                        message: 'Refresque la pagina en caso de carga lenta'
                    });
                   this.funcionario_list();
                });
              } else {
                this.isLoading = false;
                this.$swal('Cancelado', 'No se registro sus datos', 'info')
              }
            })

        },
        edit (item) {

            axios.get(`/api/biometric/${item.id}/edit`)
            .then(response => {
                this.biometric = response.data.biometric;
            })
            .catch(error => {
            });

            this.dialog = true
        },
        funcionario_list(item) {
            this.isLoading = true;
            axios.get('/api/listUserBiometrico/'+this.data_biometrico)
            .then(response => {
                this.isLoading = false;
                if (response.data.success=="true") {
                    this.funcionario=response.data.data; 
                }else{

                }              
            })
            .catch(error => {
                this.isLoading = false;
                iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede listar los datos no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },
        user_list(item) {
            this.isLoading = true;
            axios.get(`/api/list_user_biometric/${item.id}`)
            .then(response => {
                this.isLoading = false;
                if (response.data.success=="true") {
                    this.users_biometric = response.data.data;
                    this.biometrico_data=response.data.biometrico_data;
                    iziToast.success({
                            position: 'topRight',
                            title: 'Listado de usuarios Biometrico '+response.data.biometrico_data.name,
                            message: response.data.biometrico_data.ip,
                            theme: 'light', // dark
                            color: 'green', // blue, red, green, yellow
                    });
                }else{

                }
              
            })
            .catch(error => {
                this.isLoading = false;
                iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede listar los datos no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },
        user_reset(item) {
            this.isLoading = true;
            axios.get(`/api/ReiniciarBiometrico/${item.id}`)
            .then(response => {
                this.isLoading = false;
                if (response.data.success=="true") {
                    iziToast.success({
                            position: 'topRight',
                            title: 'Reseteo exitoso '+response.data.biometrico_data.name,
                            message: response.data.biometrico_data.ip,
                            theme: 'light', // dark
                            color: 'green', // blue, red, green, yellow
                    });
                }else{

                }
            })
            .catch(error => {
                this.isLoading = false;
                 iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede resetear el biometrico no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },
         user_desactivar(item) {
            this.isLoading = true;
            axios.get(`/api/DesactivarBiometrico/${item.id}`)
            .then(response => {
                    this.isLoading = false;
                if (response.data.success=="true") {
                     iziToast.success({
                            position: 'topRight',
                            title: 'Desactivacion exitosa '+response.data.biometrico_data.name,
                            message: response.data.biometrico_data.ip,
                            theme: 'light', // dark
                            color: 'green', // blue, red, green, yellow
                    });
                }else{

                }
            })
            .catch(error => {
                this.isLoading = false;
                iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede desactivar el biometrico no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },
        user_desbloquear(item){
            this.isLoading = true;
            axios.get(`/api/DesbloquearBiometrico/${item.id}`)
            .then(response => {
                this.isLoading = false;
                if (response.data.success=="true") {
                     iziToast.success({
                            position: 'topRight',
                            title: 'Desbloqueo exitoso '+response.data.biometrico_data.name,
                            message: response.data.biometrico_data.ip,
                            theme: 'light', // dark
                            color: 'green', // blue, red, green, yellow
                    });
                }else{

                }
            })
            .catch(error => {
                this.isLoading = false;
                iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede desbloquear el biometrico  no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },  
        user_activar(item){
            this.isLoading = true;
            axios.get(`/api/ActivarBiometrico/${item.id}`)
            .then(response => {
            this.isLoading = false;
                if (response.data.success=="true") {
                     iziToast.success({
                            position: 'topRight',
                            title: 'Activacion exitosa '+response.data.biometrico_data.name,
                            message: response.data.biometrico_data.ip,
                            theme: 'light', // dark
                            color: 'green', // blue, red, green, yellow
                    });
                }else{

                }
            })
            .catch(error => {
                this.isLoading = false;
                iziToast.success({
                            position: 'topRight',
                            title: 'Error de conexion',
                            message: 'No se puede activar el biometrico  no tiene conexion',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                    });
            });
        },  
        update (item) {
            axios.post('/api/biometric', item)
                  .then(response => {
                        iziToast.success({
                            title: '',
                            message: 'Registro Satisfactorio',
                        });
                        this.search();
                    })
                    .catch(function (error) {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog =false;

        },
        destroy (item) {

            axios.delete(`/api/biometric/${item.id}`)
            .then((response)=>{

                this.search();
                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },

        close() {
            this.dialog = false;
        },
        close_sync()
        {
            this.dialog_sync = false;
        },
        getInfo()
        {
            this.isLoading = true;
            axios.get(`/api/info_biometric/${this.data_biometrico}`)
                 .then(response=>{
                     this.name = response.data.name;
                     this.time = response.data.time;
                     this.isLoading = false;
                        if (response.data.success=="true") {
                             iziToast.success({
                                    position: 'topRight',
                                    title: 'Conexion exitosa '+response.data.biometrico_data.name,
                                    message: response.data.biometrico_data.ip,
                                    theme: 'light', // dark
                                    color: 'green', // blue, red, green, yellow
                            });
                             this.funcionario_list();
                        }else{

                        }
                 })
                 .catch( (error) => {
                     this.isLoading = false;
                    this.name = "SIN CONEXON";
                     this.time = "SIN CONEXION";
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo establecer conexion con el biometrico',
                    });
                  });
        },
        listEmploye(){
            axios.get('/api/listEmployeBio',{})
            .then((response)=>{
                var obj=[];
               for (var i = 0; i < response.data.empleados.length; i++) {
                    obj.push({value:response.data.empleados[i].id,
                    text:response.data.empleados[i].full_name,
                    cargo:response.data.empleados[i].cargo,
                    ci:response.data.empleados[i].identity_card,
                    gerencia:response.data.empleados[i].gerencia,
                    biometrico:response.data.empleados[i].biometric_code,
                    nro_cuenta:response.data.empleados[i].account_number});

                }
                this.funcionarios=obj;
                //this.days_work_month=response.data.dias_mes;
            }).catch((error)=> {
            });
        },
        onselect(item,data){

        },
        RegistrarUsuario(){
        this.$swal({
             title: '¿Esta seguro de sincronizar los datos del biometrico a la base de datos de RRHH?',
              text: 'Verifique los datos de los usuarios del biometrico para realizar la sincronizacion',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Si, Registrar!',
              cancelButtonText: 'No, cancelar',
              showCloseButton: true,
              showLoaderOnConfirm: true
              }).then((result) => {
              if(result.value) {
                for (var i = 0; i < this.biometrics.length; i++) {
                    this.isLoading = true;
                    this.select.biometrico_data=this.biometrics[i];
                     axios.post('/api/registrarbiouser/'+this.biometrics[i].id,this.select)
                    .then(response=>{
                        this.isLoading = false;
                        iziToast.success({
                            position: 'topRight',
                            title: 'Se sincronizo los registros los datos exitosamente',
                            message: 'Refresque la pagina en caso de carga lenta'
                        });
                    })
                    .catch((error)=> {
                        this.isLoading = false;
                        iziToast.success({
                                position: 'topRight',
                                title: 'Error de conexion',
                                message: 'No se puede activar el biometrico  no tiene conexion',
                                theme: 'light', // dark
                                color: 'red', // blue, red, green, yellow
                        });
                    });
                }
                
              } else {
                this.isLoading = false;
                this.$swal('Cancelado', 'No se registro sus datos', 'info')
              }
            })            
        },
        delete_user(param){
            this.$swal({
             title: '¿Esta seguro de eliminar al usuario del biometrico?',
              text: 'Verifique los datos antes de realizar la eliminación',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Si, Eliminar!',
              cancelButtonText: 'No, cancelar',
              showCloseButton: true,
              showLoaderOnConfirm: true
              }).then((result) => {
              if(result.value) {
                    this.isLoading = true;
                     axios.get('/api/DeletUserBiometric/'+this.biometrico_data.id+'/'+param.id_biometrico,{})
                    .then(response=>{
                        this.isLoading = false;
                        iziToast.success({
                            position: 'topRight',
                            title: 'Se realizo la eliminacion con exito',
                            message: 'datos a refrescar'
                        });
                        this.user_list(this.biometrico_data);
                    })
                    .catch((error)=> {
                        this.isLoading = false;
                        iziToast.success({
                                position: 'topRight',
                                title: 'Error de conexion',
                                message: 'No se puede activar el biometrico  no tiene conexion',
                                theme: 'light', // dark
                                color: 'red', // blue, red, green, yellow
                        });
                    });
              } else {
                this.isLoading = false;
                this.$swal('Cancelado', 'No se registro sus datos', 'info')
              }
            })  
        }
    },
    components: {
        VueBootstrap4Table,
        EditBiometric,
        SyncBiometric,
    }
}
</script>
