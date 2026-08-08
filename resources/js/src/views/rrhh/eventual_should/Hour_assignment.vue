<template>
 <v-card>
        <v-card-title>
            <h3>Asignacion de Horarios</h3>
        <v-spacer></v-spacer>

        <!-- <v-btn @click="create();" color="primary" dark class="mb-2">Asignar</v-btn> -->
        <!-- {{JSON.stringify(items)}} -->
        </v-card-title>
        <v-card-text>

        <v-layout justify-space-between row fill-height>

        <v-flex xs8 style="padding-left: 5px">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement(item)" :key="index">{{item.name}}</a>
                </div>
            </div>
            <!-- <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-map-marker"></i> {{location?location.name:'Seleccione Ubicacion'}}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" v-for="(item,index) in locations" @click="selectLocation(item)" :key="index">{{item.name}}</a>
                </div>
            </div> -->
            <vue-bootstrap4-table :rows="employees" :columns="columns" :config="config" >
                 <!-- <template slot="selected-rows-info" slot-scope="props">
                    Total Numero de Empleados Seleccionados : {{props.selectedItemsCount}}
                </template> -->
                <template slot="sort-asc-icon">
                    <i class="fa fa-sort-asc"></i>
                </template>
                <template slot="sort-desc-icon">
                    <i class="fa fa-sort-desc"></i>
                </template>
                <template slot="no-sort-icon">
                    <i class="fa fa-sort"></i>
                </template>
                <template slot="option" slot-scope="props">
                    <v-icon @click="addItem(props.row)" >
                        forward
                    </v-icon>
                </template>
                <template slot="type_hours_employee" slot-scope="props">
                    <table>
                        <tbody>
                            <tr v-for="item in props.row.type_hours_employee">
                                <td>{{item.type_date.name}}</td>
                                <td>{{item.date_start}}</td>
                                <td>{{item.date_finish}}</td>
                                <td>
                                     <v-btn icon ripple @click="editHour(item)" lass="mb-2" data-toggle="modal" data-target="#myModal">
                                        <v-icon color="warning">edit</v-icon>
                                    </v-btn>
                                     <v-icon @click="deleteHour(item)" class="error">
                                        delete
                                    </v-icon>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </vue-bootstrap4-table>
        </v-flex>
        <v-flex xs4 style="padding-left: 5px">
            <v-card>
                <v-layout>

                    <v-flex xs12 sm4 md4 >
                            <v-menu
                            ref="menu"
                            v-model="menu"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                                <v-text-field
                                v-model="date_inicio"
                                label="Fecha Inicio"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="date_inicio" no-title @input="menu = false"></v-date-picker>
                            </v-menu>

                    </v-flex>
                    <v-flex xs12 sm8 md8>
                            <v-select
                            :items="type_hours"
                            v-model="type_hour_id"
                            item-text="name"
                            item-value="id"
                            prepend-icon="access_time"
                            ></v-select>
                            <!-- {{type_hour}} -->
                    </v-flex>

                </v-layout>
                <v-layout>

                    <v-flex xs12 sm4 md4 >
                            <v-menu
                            ref="menu1"
                            v-model="menu1"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                                <v-text-field
                                v-model="date_fin"
                                label="Fecha Fin"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="date_fin" no-title @input="menu1 = false"></v-date-picker>
                            </v-menu>

                    </v-flex>
                </v-layout>
                <v-flex xs12 sm12 md12>
                        <v-btn color="primary" small @click="store()"> Asignar Horario Individual</v-btn>
                        <v-btn color="warning" small @click="storeFull()"> Asignar Horario Masivo</v-btn>
                </v-flex>
                <v-list >
                    <v-subheader inset>Funcionario Seleccionados: {{items.length}}</v-subheader>

                    <v-list-tile
                        v-for="(item,index ) in items"
                        :key="index"
                        avatar
                    >
                        <v-list-tile-avatar>
                        <v-icon color='blue' >person</v-icon>
                        </v-list-tile-avatar>

                        <v-list-tile-content>
                        <v-list-tile-title>{{ item.full }}</v-list-tile-title>
                        <v-list-tile-sub-title class="caption   "> {{ item.management.name }}</v-list-tile-sub-title>
                        </v-list-tile-content>
                        <v-list-tile-action>
                        <v-btn icon ripple @click="removeItem(index)">
                            <v-icon color="red">delete</v-icon>
                        </v-btn>
                        </v-list-tile-action>
                    </v-list-tile>
                </v-list>
            </v-card>
        </v-flex>
        </v-layout>
        </v-card-text>
        <div class="modal inmodal fade" id="myModal" tabindex="-5" role="dialog"  aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title">EDITAR HORARIO</h4>
                    </div>
                    <div class="modal-body">
                        <p><b>{{hour_edit.type_date.name}}</b></p>
                        <p><b>Fecha Inicio: </b>{{hour_edit.date_start}}</p>
                        <p><b>Fecha Fin: </b>{{hour_edit.date_finish}}</p>

                        <v-flex xs12 sm4 md4 >
                            <v-menu
                            ref="menu2"
                            v-model="menu2"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                                <v-text-field
                                v-model="hour_edit.date_start"
                                label="Fecha Inicio"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="hour_edit.date_start" no-title @input="menu2 = false"></v-date-picker>
                            </v-menu>

                    </v-flex>
                     <v-flex xs12 sm4 md4 >
                            <v-menu
                            ref="menu3"
                            v-model="menu3"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                                <v-text-field
                                v-model="hour_edit.date_finish"
                                label="Fecha Inicio"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="hour_edit.date_finish" no-title @input="menu3 = false"></v-date-picker>
                            </v-menu>

                    </v-flex>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" @click="updateHour(hour_edit);">Actualizar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
 </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import 'vue-loading-overlay/dist/vue-loading.css';
export default
{
    data:()=>({
        employees:[],
        type_hours:[],
        managements:[],
        locations:[],
        location:null,
        management:null,
        type_hour_id:{},
        items:[],
        date_inicio:null,
        date_fin:null,
        date_inicio_modal:null,
        date_fin_modal:null,
        menu:false,
        menu1:false,
        menu2:false,
        menu3:false,
        isLoading: false,
        fullPage: true,
        hour_edit:{type_date:{name:''}},
        columns: [

            {
                label: "C.I.",
                name: "identity_card",
                filter: {
                    type: "simple",
                    placeholder: "C.I."
                },
                sort: true,
            },
            {
                label: "Primer Nombre",
                name: "full",
                filter: {
                    type: "simple",
                    placeholder: "Primer Nombre"
                },
                sort: true,
            },
            {
                label: "Asignaciones",
                name: "type_hours_employee",
                filter: {
                    type: "simple",
                    placeholder: "Horarios"
                },
                sort: false,
            },
            {
                label: "",
                name: "option",
                sort: false,
            }
        ],
        config: {
            checkbox_rows: false,
            highlight_row_hover: true,
            rows_selectable: true,
            multi_column_sort: false,
            // selected_rows_info:  true,
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
        this.getTypeHours();
        this.search();
        this.getManagements();
        this.getLocation();
    },
    methods:{
        getTypeHours()
        {
            axios.get(`api/type_hour`)
                 .then(response=>{
                     this.type_hours = response.data.type_hours;
                 });
        },
        search(){
            axios.get(`api/employee`)
                 .then((response)=>{
                    this.employees = response.data;
                });
        },
        getLocation(){
            axios.get(`api/location`)
                 .then(response=>{
                     this.locations = response.data.locations;
                 });
        },
        getManagements()
        {
            axios.get(`api/management`)
                 .then((response)=>{
                        this.managements = response.data;
                        this.managements.push({id:0,name:"Todos"});
                 })
        },
        selected_items(selected_items)
        {
        },
        selectManagement(management)
        {
            this.management = management;
            axios.get(`api/employees_management/${this.management.id}`)
                 .then(response=>{
                     this.employees = response.data.employees;

                 });
        },
        selectLocation(location)
        {
            this.location = location;
        },
        addItem(item)
        {
            this.items.push(item)
        },
        removeItem(index)
        {
            this.items.splice(index,1);
        },
        store()
        {

            Swal.fire({
                title: `Asignar Horario a los ${this.items.length} Funcionarios `,
                text: "Una ver realizado el proceso no se podra revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, Asignar!',
                cancelButtonText: 'No'
                }).then((result) => {
                if (result.value) {
                    let params={
                                date_inicio:this.date_inicio,
                                date_fin:this.date_fin,
                                type_hour_id: this.type_hour_id,
                                employees: this.items
                                };
                    axios.post('api/hour_register_select',params)
                        .then(response=>{
                             iziToast.success({
                                title: 'Se asigno horario a los empleados',
                                message: 'a los empleados',
                            });
                                this.date_inicio=null;
                                this.date_fin=null;
                                this.this.type_hour_id={};
                                this.items=[];
                             this.selectManagement(this.management.id);
                           // this.$router.push('/attendance')
                        });
                }
            })


        },
        storeFull()
        {
            Swal.fire({
                title: 'Asignar Horario a los Funcionarios gerencia '+this.management.name,
                text: "Una ver realizado el proceso no se podra revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, Asignar!',
                cancelButtonText: 'No'
                }).then((result) => {
                if (result.value) {
                    this.isLoading=true;
                    let params={
                                date_inicio:this.date_inicio,
                                date_fin:this.date_fin,
                                type_hour_id: this.type_hour_id,
                                employees: this.items,
                                management:this.management.id,
                                };
                    axios.post('api/hour_register',params)
                        .then(response=>{
                            this.isLoading=false;
                            if (response.data.success="true") {
                                iziToast.success({
                                title: 'Se asigno horarios a los funcionarios',
                                message: 'Funcionarios asignados',
                                });
                                this.$router.push('/attendance')
                            }else{
                                iziToast.success({
                                    position: 'topRight',
                                    title: 'Error de consulta',
                                    message: 'Error de consulta intente nuevamente',
                                    theme: 'light', // dark
                                    color: 'red', // blue, red, green, yellow
                                });
                            }                           
                    }) .catch(error => {
                        this.isLoading=false;
                        iziToast.success({
                                position: 'topRight',
                                title: 'Error de consulta',
                                message: 'Error de consulta intente nuevamente',
                                theme: 'light', // dark
                                color: 'red', // blue, red, green, yellow
                        });
                    });
                }
            })
        },
        editHour(params){
            this.hour_edit=params;
        },
        updateHour(params){
            axios.post('api/hour_update',params)
                .then(response=>{
                    $('#myModal').modal('hide');
                    this.isLoading=false;
                    if (response.data.success="true") {
                        iziToast.success({
                        title: 'Se actualizo el horario al funcionario',
                        message: 'actualizacion',
                        });
                        this.search();
                    }else{
                        iziToast.success({
                            position: 'topRight',
                            title: 'Error de consulta',
                            message: 'Error de consulta intente nuevamente',
                            theme: 'light', // dark
                            color: 'red', // blue, red, green, yellow
                        });
                    }                           
            }) .catch(error => {
                this.isLoading=false;
                iziToast.success({
                        position: 'topRight',
                        title: 'Error de consulta',
                        message: 'Error de consulta intente nuevamente',
                        theme: 'light', // dark
                        color: 'red', // blue, red, green, yellow
                });
            });
        },
        deleteHour(params)
        {
            Swal.fire({
                title: 'Desea eliminar este horario del funcionario',
                text: "Una ver realizado el proceso no se podra revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, Asignar!',
                cancelButtonText: 'No'
                }).then((result) => {
                if (result.value) {
                    this.isLoading=true;
                    axios.post('api/hour_delete',params)
                        .then(response=>{
                            this.isLoading=false;
                            if (response.data.success="true") {
                                iziToast.success({
                                title: 'Se elimino el horario al funcionario',
                                message: 'eliminacion',
                                });
                                this.search();
                            }else{
                                iziToast.success({
                                    position: 'topRight',
                                    title: 'Error de consulta',
                                    message: 'Error de consulta intente nuevamente',
                                    theme: 'light', // dark
                                    color: 'red', // blue, red, green, yellow
                                });
                            }                           
                    }) .catch(error => {
                        this.isLoading=false;
                        iziToast.success({
                                position: 'topRight',
                                title: 'Error de consulta',
                                message: 'Error de consulta intente nuevamente',
                                theme: 'light', // dark
                                color: 'red', // blue, red, green, yellow
                        });
                    });
                }
            })
        },
    },
    computed:{
        // management_list()
        // {
        //     let managements = [];
        //     this.managements.forEach( item => {
        //         managements.push({name:item.name,value:item.name})
        //     });
        //     return managements;
        // }
    },
    components: {
        VueBootstrap4Table,
    }
}
</script>
