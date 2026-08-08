<template>
    <v-card>
        <v-card-title>
            <h3>Solicitudes Permiso de Viaje y Baja Medica</h3>
        <v-spacer></v-spacer>
        <v-btn @click="create()" color="primary" dark class="mb-2">Nuevo</v-btn>
        </v-card-title>
        <v-card-text>
        	 <v-flex xs6 sm6 md6>
		                <label for="validationServer013">Buscar Funcionario</label>
		                <model-select :options="funcionarios"
		                    v-model="select"
		                    placeholder="seleccionar funcionario"
		                    @input="onSelect"
		                    >
		                </model-select>
             </v-flex><hr>
             <vue-bootstrap4-table :rows="employee_requests" :columns="columns" :config="config" >
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
                <template slot="approves" slot-scope="props">
                    <v-progress-linear color="green" height="18"
                        :value="Math.ceil(porcentajeApprove(props.row.approves))"
                    >
                        <h6>{{ Math.ceil(porcentajeApprove(props.row.approves)) }}%</h6>
                    </v-progress-linear>

                    <!-- <div class="text-xs-center"> -->
                    <!-- <div class="progress">
                        <div class="progress-bar" role="progressbar" :style="'width: '+porcentajeApprove(props.row.approves)+'%'" :aria-valuenow="porcentajeApprove(props.row.approves)" aria-valuemin="0" aria-valuemax="100">{{Math.round( * 10) / 10}} %</div>
                    </div> -->
                    <!-- <v-chip :color="props.row.active?'success':'danger'" :text-color="props.row.active?'white':'danger'" small>{{props.row.active?'Activo':'Inactivo'}}</v-chip> -->
                    <!-- </div> -->
                </template>
                <template slot="option" slot-scope="props">
                    <v-btn-toggle>
                        <v-btn  @click="show(props.row)">
                            <v-icon >
                                remove_red_eye
                            </v-icon>
                        </v-btn>
                        <v-btn @click="edit(props.row)" v-if="porcentajeApprove(props.row.approves) != 100" >
                            <v-icon  >
                                thumbs_up_down
                            </v-icon>
                        </v-btn>

                        <!--v-btn @click="sendRequest(props.row)" v-if="employee.position_id!=58">
                                <v-icon>send</v-icon>
                        </v-btn-->
                        <v-btn @click="show_upload(props.row)" v-if="!props.row.image_path">
                                <v-icon>file_upload</v-icon>
                        </v-btn>
                        <v-btn @click="show_image(props.row)" v-else>
                                <v-icon>insert_photo</v-icon>
                        </v-btn>
                        <v-btn @click="archive(props.row)">
                                <v-icon>archive</v-icon>
                        </v-btn>




                    </v-btn-toggle>

                </template>
            </vue-bootstrap4-table>
        </v-card-text>

        <upload-image :dialog="dialog_upload" :employee_request="employee_request" @close="close_upload" @update_image="update_image"></upload-image>
        <edit-fucov :dialog_render="dialog_render" :employee_request="employee_request" @close="close"  @employee_request="update"></edit-fucov>
        <edit-render :dialog="dialog" :employee_request="employee_request" @close="close_render" @employee_request="update_aprove"></edit-render>
        <!--show-request :dialog_show="dialog_show" :employee_request="employee_request" @close_show="close_show"  @employee_request_show="update_show"></show-request-->

        <v-dialog
            v-model="dialog_view"
            max-width="800"
        >
        <v-card>
            <v-toolbar dark color="rrhh-primary">
            <v-btn icon dark @click="dialog_view = false">
                <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Boleta Escaneada</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
                <v-btn dark flat @click="dialog_view = false">Salir</v-btn>
            </v-toolbar-items>
            </v-toolbar>
                <iframe :src="getUrl" frameborder="0" style="height:600px;width:100%;" ></iframe>
            <!-- <v-card-text>
            </v-card-text> -->
        </v-card>
        </v-dialog>
    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditFucov from './EditFucov.vue';
import ShowRequest from './Show.vue';
import EditRender from './EditRender.vue';
import UploadImage from './Upload.vue';

export default {
    data:()=>({
        employee_requests:[],
        employee_request:{},
        employee:{},
        dialog:false,
        dialog_render:false,
        dialog_show:false,
        dialog_upload:false,
        dialog_view:false,
    	select: {},
        funcionarios:[],
        // knowledge:25,
        toggle_exclusive: undefined,
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
                label: "Nombre ",
                name: "employee.first_name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre Completo"
                },
                sort: true,
            },
            {
                label: "Segundo N.",
                name: "employee.second_name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre Completo"
                },
                sort: true,
            },
            {
                label: "Paterno",
                name: "employee.last_name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre Completo"
                },
                sort: true,
            },
            {
                label: "Materno",
                name: "employee.mother_last_name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre Completo"
                },
                sort: true,
            },
            {
                label: "Tipo de Solicitud",
                name: "request_type.name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Tipo de Solicitud"
                },
                sort: true,
            },
            {
                label: "Fecha",
                name: "date",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Fecha"
                },
                sort: true,
            },
            {
                label: "A Fecha",
                name: "todate",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Fecha"
                },
                sort: true,
            },
            {
                label: "De Hora",
                name: "hour_in",
                filter: {
                    type: "simple",
                    placeholder: "Hora"
                },
                sort: true,
            },
            {
                label: "A Hora",
                name: "hour_out",
                filter: {
                    type: "simple",
                    placeholder: "Hora"
                },
                sort: true,
            },
            {
                label: "Aprobacion",
                name: "approves",

            },

            {
                label: "Opciones",
                name: "option",
                sort: false,
            }],

        config:
        {
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
        this.listEmploye();
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
    	onSelect (items) {
    	
      		this.search(items.value);
      	},
        search(params){
            axios.get('/api/employee_request_fucov/'+params)
                 .then((response)=>{
                    // this.employees = response.data;
                    this.employee_requests = response.data.employee_requests;
                    this.employee = response.data.employee;
                });
        },
         create() {
            this.dialog_render = true;
            this.employee_request ={};
            this.employee_request.date = new Date().toISOString().substr(0, 10);
            this.employee_request.employee_id = this.employee.id;
        },
         listEmploye(){
            axios.get('/api/listEmployeBio',{})
            .then((response)=>{
                this.fecha_actual=response.data.fecha;
                var obj=[];
               for (var i = 0; i < response.data.empleados.length; i++) {
                    obj.push({value:response.data.empleados[i].id,
                    text:response.data.empleados[i].full_name,
                    cargo:response.data.empleados[i].cargo,
                    ci:response.data.empleados[i].identity_card,
                    gerencia:response.data.empleados[i].gerencia,
                    nro_cuenta:response.data.empleados[i].account_number});
                }
                this.funcionarios=obj;
                //this.days_work_month=response.data.dias_mes;
            }).catch((error)=> {
            });
        },
        show(item){
            axios.get(`/api/employee_request/${item.id}`)
            .then(response => {
                this.employee_request = response.data.employee_request
            })
            .catch(error => {
            });
            this.dialog_show = true
        },
        edit (item) {

            axios.get(`/api/employee_request/${item.id}/edit`)
            .then(response => {
                this.employee_request = response.data.employee_request
                 this.dialog = true;
            })
            .catch(error => {
            });
        },
        update (item) {
            axios.post('/api/employee_request', item)
                  .then(response => {
                        iziToast.success({
                            title: 'Registro Satisfactorio',
                            message: 'Se registro '+response.data.name,
                        });
                        this.search(response.data.request.employee_id);
                    })
                    .catch(function (error) {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog_render =false;

        },
        update_aprove(item){
            axios.post('/api/approve_request', item)
                  .then(response => {

                        iziToast.success({
                            title: 'Solicitud procesada como '+item.approve_state,
                            message: '',
                        });

                        this.search(response.data.employee_request.employee_id);
                    })
                    .catch( (error)=> {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog =false;
        },
        update_image(item){
            let formData = new FormData();
            formData.set('id',item.id);
            formData.append("image_file", item.image_file || '');
            axios.post('/api/request_upload_image', formData,{
                        headers: {
                                'Content-Type': 'multipart/form-data'
                                }
                    })
                .then(response => {
                    //this.$store.dispatch('template/showMessage',{message:'Se Actualizó la lista de productos',color:'success'});
                    iziToast.success({
                        title: 'OK',
                        message: 'Se Subio la imagen con exito!',
                    });
                    this.search();
                    // this.search();
                })
                .catch(function (error) {
                    //this.$store.dispatch('template/showMessage',{message:error,color:'danger'});

                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo registrar la imagen, error contactase con el administrador del sistema',
                    });

                });


            this.dialog_upload = false;

        },
        show_upload(item)
        {
            this.employee_request = item;
            this.dialog_upload = true;
        },
        update_show (item) {
            // axios.post('/api/employee_request', item)
            //       .then(response => {
            //             iziToast.success({
            //                 title: 'Registro Satisfactorio',
            //                 message: 'Se registro '+response.data.name,
            //             });
            //             this.search();
            //         })
            //         .catch(function (error) {

            //             iziToast.error({
            //                 title: 'Error',
            //                 message: 'Contactese con el Administrador de la Pagina: '+error,
            //             });
            //         });
            this.dialog =false;

        },
        show_image(item)
        {
            this.employee_request = item;
            this.dialog_view = true;
        },
        destroy (item) {

            axios.delete(`/api/employee_request/${item.id}`)
            .then((response)=>{

                this.search();
                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch((error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        porcentajeApprove(approves){
            let porcent = 100/approves.length;
            let total_porcent=0;
            approves.forEach(approve => {
                if(approve.state=='Aprobado'){
                    total_porcent +=porcent;
                }
            });
            return total_porcent;
        },
        close() {
            this.dialog_render = false;
        },
        close_show() {
            this.dialog_show = false;
        },
        close_upload() {
            this.dialog_upload = false;
        },
        close_render(){
            this.dialog = false;            
        },
        archive(item)
        {
            Swal.fire({
            title: 'Archivar Solicitud ?',
            text: "Archivar solicitud de permiso",
            type: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Archivar',
            cancelButtonText: 'Cancelar'
            }).then((result) => {
            if (result.value) {

            axios.get(`/api/archive_request/${item.id}`)
                .then(response => {
                    // this.employee_request = response.data.employee_request
                    iziToast.success({
                        title: 'Se archivo la solicitud',
                        message: '',
                    });
                    this.search();
                })
                .catch(error => {
                    iziToast.error({
                            title: 'Error',
                            message: error,
                        });
            });

            }
            })


        },
        sendRequest(item)
        {
            Swal.fire({
            title: 'Enviar Solicitud ?',
            text: "Enviar solicitud de permiso",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Enviar',
            cancelButtonText: 'Cancelar'
            }).then((result) => {
            if (result.value) {

            axios.get(`/api/send_request/${item.id}`)
                .then(response => {
                    // this.employee_request = response.data.employee_request
                    if(response.data.status =='success'){

                        iziToast.success({
                            title: response.data.message,
                            message: '',
                        });

                    }else{
                        iziToast.error({
                            title: response.data.message,
                            message: '',
                        });
                    }

                    this.search();
                })
                .catch(error => {
                    iziToast.error({
                            title: 'Error',
                            message: error,
                        });
            });

            }
            })
        }
    },
    components: {
        VueBootstrap4Table,
        EditRender,
        EditFucov,
        ShowRequest,
        UploadImage,
    }
}
</script>
