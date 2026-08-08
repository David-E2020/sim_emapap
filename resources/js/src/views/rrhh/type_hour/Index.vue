<template>
    <v-card>
        <v-card-title>
            <h3>Tipos de Horarios</h3>
        <v-spacer></v-spacer>
        <v-btn @click="createvirtual()" color="primary" primary class="mb-2">Habilitar Usuarios</v-btn>
        <v-btn @click="create()" color="primary" dark class="mb-2">Nuevo</v-btn>
        </v-card-title>
        <v-card-text>
             <vue-bootstrap4-table :rows="type_hours" :columns="columns" :config="config" >
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

                    <v-icon @click="edit(props.row)">
                        edit
                    </v-icon>
                    <v-icon  @click="destroy(props.row)">
                        delete
                    </v-icon>
                    <!-- <v-btn-toggle>
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
                    </v-btn-toggle> -->


                </template>
            </vue-bootstrap4-table>
        </v-card-text>
        <edit-type :dialog="dialog" :type_hour="type_hour" @close="close"  @type_hour="update"></edit-type>

    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditType from './Edit.vue';
export default {
    data:()=>({
        type_hours:[],
        type_hour:{},
        dialog:false,
        isLoading: false,
        fullPage: true,
         columns: [
            {
                label: "Codigo",
                name: "code",
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
            // {
            //     label: "Dias",
            //     name: "",

            // },
            {
                label: "Entrada",
                name: "entry",
                filter: {
                    type: "simple",
                    placeholder: "Entrada"
                },
                sort: true,
            },
            {
                label: "Salida",
                name: "output",
                filter: {
                    type: "simple",
                    placeholder: "Salida"
                },
                sort: true,
            },
            {
                label: "Tolerancia",
                name: "tolerance",

            },
            {
                label: "Turno",
                name: "work_shift",

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

    }),
    mounted(){
        this.search();
    },
    methods:{
        search()
        {
            axios.get(`api/type_hour`)
                 .then(response =>{
                     this.type_hours = response.data.type_hours;
                 });
        },
        create()
        {
            this.type_hour = {};
            this.dialog=true;
        },
        createvirtual(){
            Swal.fire({
            title: 'Deseea asignar horario normal?',
            text: "Se habilitaran a todos los usuarios el horario normal",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Asignar!',
            cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    this.isLoading = true;
                    axios.post(`api/habilitarVirtual`)
                        .then(response => {
                            this.isLoading = false
                            iziToast.success({
                                title: 'Asignacion de Registros',
                                message: 'Se asigno el horario normal a todos los usuarios',
                            });
                            this.search();
                            this.dialog = false;
                        })
                        .catch( (error)=> {
                            this.isLoading = false
                            iziToast.error({
                                title: 'Error',
                                message: 'Contactese con el Administrador de la Pagina: '+error,
                            });
                        });
                }
            })
        },
        destroy(item)
        {
            Swal.fire({
            title: 'Eliminar Horario?',
            text: "Una vez eliminado no se podra revertir el proceso!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Eliminar!',
            cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`api/type_hour/${item.id}`)
                        .then(response => {
                            iziToast.success({
                                title: 'Eliminacion de Registro',
                                message: 'Se elimino el horario '+response.data.name,
                            });
                            this.search();
                            this.dialog = false;
                        })
                        .catch( (error)=> {

                            iziToast.error({
                                title: 'Error',
                                message: 'Contactese con el Administrador de la Pagina: '+error,
                            });
                        });
                }
            })

        },
        close()
        {
            this.dialog=false;
        },
        show(item)
        {
        },
        edit(item){
            axios.get(`api/type_hour/${item.id}/edit`)
                 .then(response=>{
                     this.type_hour = response.data.type_hour;
                     this.dialog = true;
                 });

        },
        update(item)
        {
               axios.post(`api/type_hour`,item)
                    .then(response => {
                        iziToast.success({
                            title: 'Registro Satisfactorio',
                            message: 'Se registro '+response.data.name,
                        });
                        this.search();
                        this.dialog = false;
                    })
                    .catch( (error)=> {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });

        }

    },
    components: {
        VueBootstrap4Table,
        EditType,
    }
}
</script>
