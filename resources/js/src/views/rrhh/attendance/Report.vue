<template>
 <v-card>
        <v-card-title>
            <h3>Reporte de Asistencias</h3>
        <v-spacer></v-spacer>

        <!-- <v-btn @click="create();" color="primary" dark class="mb-2">Asignar</v-btn> -->
        <!-- {{JSON.stringify(items)}} -->
        </v-card-title>
        <v-card-text>

        <v-layout justify-space-between row fill-height>
            <v-flex xs5 sm5 md5>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement(item)" :key="index">{{item.name}}</a>
                    </div>
                </div>
            </v-flex>
         <v-flex xs3 sm3 md3 >
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
                    v-model="date"
                    label="De Fecha"
                    hint="YYYY-MM-DD"
                    persistent-hint
                    prepend-icon="event"
                    v-on="on"
                    ></v-text-field>
                </template>
                <v-date-picker v-model="date" no-title @input="menu = false"></v-date-picker>
                </v-menu>
        </v-flex>
         <v-flex xs3 sm3 md3 >
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
                    v-model="to_date"
                    label="Hasta Fecha"
                    hint="YYYY-MM-DD"
                    persistent-hint
                    prepend-icon="event"
                    v-on="on"
                    ></v-text-field>
                </template>
                <v-date-picker v-model="to_date" no-title @input="menu1 = false"></v-date-picker>
                </v-menu>
        </v-flex>
        <v-flex xs1 sm1 md1>
            <!-- <v-btn icon> <i class="fa fa-file-excel-o text-success"></i> <i class="fa fa-download text-success"></i></v-btn> -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                <a :href="`/api/payroll/${management.id}/${date}/${to_date}`">
                     <v-icon color='green'>cloud_download</v-icon>
                </a>
                </template>
                <span>Descargar Planilla</span>
            </v-tooltip>

        </v-flex>
        <v-flex xs12 md12 sm12 style="padding-left: 5px">
            <!-- <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement(item)" :key="index">{{item.name}}</a>
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
                    <v-icon @click="showAttendance(props.row,2)" >
                        history
                    </v-icon>
                    <v-icon @click="showAttendanceComplet(props.row,1)" >
                        compare
                    </v-icon>
                    <v-icon @click="showAttendance2(props.row,3)" >
                        attach_money
                    </v-icon>
                </template>
            </vue-bootstrap4-table>
        </v-flex>

        </v-layout>


        </v-card-text>
         <v-dialog
            v-model="dialog_printer"
            max-width="800"
        >
        <v-card>
            <v-toolbar dark color="rrhh-primary">
            <v-btn icon dark @click="dialog_printer = false">
                <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Tarjeta de Asistencia</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
                <v-btn dark flat @click="dialog_printer = false">Salir</v-btn>
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
        date:null,
        to_date:null,
        menu:false,
        menu1:false,
        dialog_printer:false,
        employee:{},
        sw:false,
        switcher:0,
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
                name: "first_name",
                filter: {
                    type: "simple",
                    placeholder: "Primer Nombre"
                },
                sort: true,
            },
            {
                label: "Segundo Nombre",
                name: "second_name",
                filter: {
                    type: "simple",
                    placeholder: "Segundo Nombre"
                },
                sort: true,
            },
            {
                label: "Apellido Paterno",
                name: "last_name",
                filter: {
                    type: "simple",
                    placeholder: "Apellido Paterno"
                },
                sort: true,
            },
            {
                label: "Apellido Materno",
                name: "mother_last_name",
                filter: {
                    type: "simple",
                    placeholder: "Apellido Materno"
                },
                sort: true,
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
        // this.columns[5].filter.options = [
        //           {
        //             "name" : "Irwin",
        //             "value" : "Irwin"
        //           },
        //           {
        //             "name" : "Don",
        //             "value" : "Don"
        //           },
        //           {
        //             "name" : "Lolita Paris",
        //             "value" : "Lolita"
        //           }
        //         ];
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

                        // let m =[];
                        // this.managements.forEach(item=>{
                        //     m.push({name:item.name,value:item.name})
                        // });
                        // this.columns[5].filter.options =m;
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
        showAttendance(employee,sw)
        {
             if (this.date==null && this.to_date==null) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error...',
                  text: 'Debe seleccionar el rango de fechas!',
                })
            }else{
            this.employee = employee;
            this.switcher = sw;
            this.dialog_printer = true;
            }
        },
        showAttendance2(employee,sw)
        {
             if (this.date==null && this.to_date==null) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error...',
                  text: 'Debe seleccionar el rango de fechas!',
                })
            }else{
            this.employee = employee;
            this.switcher = sw;
            this.dialog_printer = true;
            }
        },
        showAttendanceComplet(employee,sw){
             if (this.date==null && this.to_date==null) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error...',
                  text: 'Debe seleccionar el rango de fechas!',
                })
            }else{
            this.employee = employee;
            this.switcher = sw;
            this.dialog_printer = true;
            }
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
                                date:this.date,
                                type_hour_id: this.type_hour_id,
                                employees: this.items
                                };
                    axios.post('api/eventual_schedule',params)
                        .then(response=>{
                             iziToast.success({
                                title: 'Se asigno horario a los empleados',
                                message: 'a los empleados',
                            });
                            this.$router.push('/attendance')

                        });
                }
            })


        }

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
        getUrl()
        {
            let url=''
            if(this.employee.id)
            {

                if(this.switcher==1)
                {
                    url= `/api/attendance_employee/${this.employee.id}/${this.date}/${this.to_date}`; //cambiar por la boleta de pago XD
                }else if(this.switcher==2){
                    url= `/api/attendance_employee_date/${this.employee.id}/${this.date}/${this.to_date}`;
                }else if(this.switcher==3){
                    url= `/api/attendance_employee_complet/${this.employee.id}/${this.date}/${this.to_date}`;

                }
            }
            return url;
        }
    },
    components: {
        VueBootstrap4Table,
    }
}
</script>
