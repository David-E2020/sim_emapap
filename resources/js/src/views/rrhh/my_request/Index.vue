<template>
    <v-card>
        <v-card-title>
            <h3>Mis Boletas</h3>
        <v-spacer></v-spacer>
        <v-btn @click="create()" color="primary" dark class="mb-2">Nuevo</v-btn>
        </v-card-title>
        <v-card-text>
             <vue-bootstrap4-table :rows="employee_requests" :columns="columns" :config="config" :classes="classes">
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
                        :value="Math.ceil(porcentajeApprove(props.row.approves))">
                        <h6>{{ Math.ceil(porcentajeApprove(props.row.approves)) }}%</h6>
                    </v-progress-linear>

                </template>
                <template slot="option" slot-scope="props">
                    <v-btn-toggle>
                        <v-btn  @click="show(props.row)">
                            <v-icon >
                                remove_red_eye
                            </v-icon>
                        </v-btn>
                        <v-btn @click="edit(props.row)" v-if="props.row.employee_id==props.row.employee_approve_id" >
                            <v-icon  >
                                edit
                            </v-icon>
                        </v-btn>
                        <v-btn @click="destroy(props.row)" v-if="props.row.employee_id==props.row.employee_approve_id">
                            <v-icon  >
                                delete
                            </v-icon>
                        </v-btn>
                        <v-btn @click="sendRequest(props.row)" v-if="props.row.employee_id==props.row.employee_approve_id">
                                <v-icon>send</v-icon>
                        </v-btn>
                        <v-btn @click="showReport(props.row)">
                               <v-icon>print</v-icon>
                        </v-btn>
                    </v-btn-toggle>
                </template>
            </vue-bootstrap4-table>
        </v-card-text>
        <edit-request :dialog="dialog" :employee_request="employee_request" @close="close"  @employee_request="update"></edit-request>
        <show-request :dialog_show="dialog_show" :employee_request="employee_request" @close_show="close_show"  @employee_request_show="update_show"></show-request>
        <!-- dialog_printer -->
        <!-- <v-dialog v-model="dialog_printer" fullscreen hide-overlay transition="dialog-bottom-transition"> -->
        <v-dialog
            v-model="dialog_printer"
            max-width="800"
        >
        <v-card>
            <v-toolbar dark color="rrhh-primary">
            <v-btn icon dark @click="dialog_printer = false">
                <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Boleta</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
                <v-btn dark flat @click="dialog_printer = false">Salir</v-btn>
            </v-toolbar-items>
            </v-toolbar>
                <iframe id="frame_view" :src="url" frameborder="0" style="height:600px;width:100%;" ></iframe>
            <!-- <v-card-text>
            </v-card-text> -->
        </v-card>
        </v-dialog>

    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditRequest from './Edit.vue';
import ShowRequest from './Show.vue';

export default {
    data:()=>({
        employee_requests:[],
        employee_request:{},
        employee:{},
        dialog:false,
        dialog_show:false,
        dialog_printer:false,
        url:'',
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
        classes:{
                tableWrapper: "outer-table-div-class wrapper-class-two",
                table : {
                    "table-striped" : true,
                },
                thead: "rrhh-primary"
            }


    }),
    mounted(){
        this.search();
    },
    methods:{
        search(){
            axios.get('/api/my_request')
                 .then((response)=>{
                    // this.employees = response.data;
                    this.employee_requests = response.data.employee_requests;
                    this.employee = response.data.employee;
                });
        },
        create() {
            this.employee_request ={};
            this.employee_request.date = new Date().toISOString().substr(0, 10);
            this.employee_request.employee_id = this.employee.id;
            this.dialog = true;
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
            })
            .catch(error => {
               
            });

            this.dialog = true
        },
        update (item) {
            axios.post('/api/employee_request', item)
                  .then(response => {
                    if(response.data.success=="true"){
                        iziToast.success({
                            title: 'Registro Satisfactorio',
                            message: 'Se registro '+response.data.name,
                        });
                        this.search();
                    }else{
                         iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+response.data.mensaje,
                        });
                    }
                    })
                    .catch(function (error) {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog =false;

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
            this.dialog = false;
        },
        close_show() {
            this.dialog_show = false;
        },
        showReport(item){
            this.employee_request =_.cloneDeep(item);
            // this.url='/api/employee_request_print';
            this.setUrl(`/api/employee_request_print/${item.id}`);
            document.getElementById('frame_view').contentDocument.location.reload(true);
            this.dialog_printer = true;
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
                    //
                    iziToast.error({
                            title: 'Error',
                            message: error,
                        });
            });
            }
            })
        },
        setUrl(url)
        {
            this.url = url
        }
    },
    computed:
    {
    },
    components: {
        VueBootstrap4Table,
        EditRequest,
        ShowRequest
    }
}
</script>
