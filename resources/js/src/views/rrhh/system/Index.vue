<template>
    <v-card>
        <v-card-title>
            <h3>Roles y Accesos</h3>
        </v-card-title>
        <v-card-text>
              <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header card-calendar">
                                <h4 class="card-title ">
                                    Roles
                                    <small class="float-sm-right">
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#RoleModal" @click="NuevoRol()">Nuevo<i class="fa fa-plus-circle"></i> </button>
                                    </small>
                                </h4>
                            </div>
                            <div class="card-body">
                                <table id="listaRole" class="table table-hover table-bordered" >
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in roles">
                                            <td>{{item.name}}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning"  data-toggle="modal" data-target="#RoleModal" @click="editarRol(item)">
                                                    <i class="material-icons pull-left">edit</i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" @click="eliminarRol(item)">
                                                    <i class="material-icons pull-left">delete</i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header card-calendar">

                                <h4 class="card-title ">
                                    Menus
                                    <small class="float-sm-right">
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#menuModal" data-json="null" > Nuevo  <i class="fa fa-plus-circle"></i> </button>
                                    </small>
                                </h4>
                            </div>
                            <div class="card-body">

                                <table id="lista" class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Icono</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <tr v-for="item in menus">
                                            <td>{{item.label}}</td>
                                            <td> <i v-bind:class="item.icon"></i> </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning" @click="editarMenu(item)">
                                                    <i class="material-icons pull-left">edit</i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" @click="eliminarMenu(item)">
                                                    <i class="material-icons pull-left">delete</i>
                                                </button>
                                            </td>
                                        </tr> 
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-calendar">

                                <h4 class="card-title ">
                                    Accesos
                                    <small class="float-sm-right">
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#permissionModal" data-json="null" > Nuevo  <i class="fa fa-plus-circle"></i> </button>
                                    </small>
                                </h4>
                            </div>
                            <div class="card-body">
                                <table id="listaProduct_demo" class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Icono</th>
                                            <th>Ruta</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in permissions">
                                            <td>{{item.name}}</td>
                                            <td> <i v-bind:class="item.sub_menu.icon" v-if="item.sub_menu"></i> </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning" @click="editarMenu(item)">
                                                    <i class="material-icons pull-left">edit</i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" @click="eliminarMenu(item)">
                                                    <i class="material-icons pull-left">delete</i>
                                                </button>
                                            </td>
                                        </tr> 
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5"></div>
                </div>  
        </v-card-text>
        <div class="modal fade" id="RoleModal" tabindex="-1" role="dialog" aria-labelledby="RoleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form id='formArticle'>
                    <div class="modal-content">
                        <div v-html='csrf'></div>
                        <input type="text" name="id" :value="form.id" v-if="form.id" hidden>
                        <div class="modal-header laravel-modal-bg">
                            <h5 class="modal-title" >{{title}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="lbname">Nombre</label>
                                    <input type="text" class="form-control" id="name" name="name" v-model="form.name" placeholder="Nombre">
                                </div>

                            </div>
                            <input type="text" name="permissions" :value="JSON.stringify(role_permissions)" hidden>
                            <div v-if="modulo=='modificar'">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-hover table-bordered" id="lista2">
                                        <thead>
                                            <tr>
                                                <th>Accesos para el Rol</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(permision,index) in role_permissions" :key="index">
                                                   <td>{{permision.sub_menu?permision.sub_menu.menu.label+' > ':''}}  {{permision.name}}</td>
                                                   <td>
                                                        <switches v-model="permision.enabled" theme="bootstrap" color="primary"></switches>
                                                   </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>                                
                            </div>
                            <div v-else>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" >Cancelar</button>
                            <div v-if="modulo=='modificar'">
                                <button type="button" class="btn btn-success" @click="darPermisos(role_permissions);">Dar Accesos</button>
                            </div>
                            <div v-else>
                                <button type="button" class="btn btn-success">Guardar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import Switches from 'vue-switches';

export default {
    props:['url','csrf'],
    data:()=>({
        contract_types:[],
        contract_type:{},
        employee_request:{},
        dialog:false,
        dialog_upload:false,
        roles:[],
        permissions:[],
        menus:[],
        title:{},
        role_permissions:[],
        form:{},
        modulo:'',
    }),
    mounted(){
        this.search();
    },
    computed:{
        getUrl()
        {
            let url=''
            if(this.employee_request.id)
            {
                url= `${this.employee_request.image_path?this.employee_request.image_path.toString().substring(7,(this.employee_request.image_path).length ):''}`;
            }
            return url;
        }
    },
    methods:{
        search(){
            axios.get('/api/system')
                 .then((response)=>{
                    this.roles = response.data.roles;
                    this.permissions = response.data.permissions;
                    this.menus = response.data.menus;
                });
        },
        create() {
            this.contract_type ={};
            this.dialog = true;
        },

        edit (item) {

            axios.get(`/api/location/${item.id}/edit`)
            .then(response => {
                this.contract_type = response.data
                for (var key in this.contract_type) {
                    this.contract_type = this.contract_type[key];
                }
            })
            .catch(error => {
            });

            this.dialog = true
        },
        update (item) {
            axios.post('/api/location', item)
                  .then(response => {
                        iziToast.success({
                            title: 'Registro Satisfactorio',
                            message: 'Se registro '+response.data.name,
                        });
                        this.search();
                    })
                    .catch( (error)=> {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog =false;
        },
        destroy (item) {

            axios.delete(`/api/location/${item.id}`)
            .then((response)=>{

                this.search();
                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error) =>{
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },

        close() {
            this.dialog = false;
        },
        NuevoRol(){
            this.form = {};
            this.modulo="";
            this.title='Nuevo Rol';                 
        },
        editarRol(params){
            this.title='Editar Rol';
            this.modulo="modificar";
            this.form = params;     
            axios.get(`/api/get_permissions_role/${params.id}`).then(response=>{
                        this.role_permissions = response.data.permissions;
                });

        },
        guardarRol(){

        },
        darPermisos(params){
            var data={id:this.form.id,name:this.form.name,permisions:JSON.stringify(params)};
             axios.post('/api/store_role', data)
                  .then(response => {
                        $("#RoleModal").modal('hide');
                        iziToast.success({
                            title: 'Accesos y edicion registradas',
                            message: 'Se registro y actualizo los datos correctamente',
                        });
                        this.search();
                    })
                    .catch( (error)=> {
                    iziToast.error({
                        title: 'Error',
                        message: 'Contactese con el Administrador de la Pagina: '+error,
                    });
            
            });
        },
        eliminarRol(params){

        },
    },
    components: {
        VueBootstrap4Table,
        Switches
    }
}
</script>
