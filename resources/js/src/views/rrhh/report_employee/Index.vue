<template>
    <v-card>
        <v-card-title>
            <h3>REPORTES FUNCIONARIOS</h3>
            <v-dialog
            v-model="dialog_2"
            hide-overlay
            persistent
            width="300"
            >
            <v-card
                color="primary"
                dark
            >
                <v-card-text>
                Por favor espere
                <v-progress-linear
                    indeterminate
                    color="white"
                    class="mb-0"
                ></v-progress-linear>
                </v-card-text>
            </v-card>
            </v-dialog>
        </v-card-title>
        <v-tabs
        v-model="tab"
        color="#AED6F1"
        light
        slider-color="#212121"
        >
            <v-tab href="#tab-1" @click="search_funcionario_activo()">
                Activo
            </v-tab>
            <v-tab href="#tab-2" @click="search_funcionario_inactivo()">
                Inactivo
            </v-tab>
            <v-tab href="#tab-3" @click="search_funcionario_todos()">
                Todos
            </v-tab>

            <v-tab-item
                value="tab-1"
            >
                <v-card>
                 <loading :active.sync="isLoading"
                        :is-full-page="fullPage">
                    </loading>
                    <v-card-title>
                        <h3>Reporte de Funcionarios Activos</h3>
                    <v-dialog
                    v-model="dialog_2"
                    hide-overlay
                    persistent
                    width="300"
                    >
                    <v-card
                        color="primary"
                        dark
                    >
                        <v-card-text>
                        Por favor espere
                        <v-progress-linear
                            indeterminate
                            color="white"
                            class="mb-0"
                        ></v-progress-linear>
                        </v-card-text>
                    </v-card>
                    </v-dialog>
                    </v-card-title>                    

                    <div class="container-fluid h-100"> 
                        <div class="row w-100 align-items-center">
                            <div class="col-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement(item)" :key="index">{{item.name}}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-9 text-center">
                                <v-btn @click="exportarExcelActivo();" color="primary" dark class="mb-2 m-5">Exportar Excel</v-btn>
                            </div>	
                        </div>
                    </div> 
                    <v-card-text>

                        <vue-bootstrap4-table :rows="employees" :columns="columns" :config="config" style="font-size: 12px;">
                            <template slot="sort-asc-icon">
                                <i class="fa fa-sort-asc"></i>
                            </template>
                            <template slot="sort-desc-icon">
                                <i class="fa fa-sort-desc"></i>
                            </template>
                            <template slot="no-sort-icon">
                                <i class="fa fa-sort"></i>
                            </template>
                            <template slot="active" slot-scope="props">
                                <div class="text-xs-center">
                                <v-chip :color="props.row.active?'success':'danger'" :text-color="props.row.active?'white':'danger'" small>{{props.row.active?'Activo':'Inactivo'}}</v-chip>
                                </div>
                            </template>
                            <template slot="pagination-info" slot-scope="props">
                                De {{props.currentPageRowsLength}}
                                a {{props.filteredRowsLength}}
                                ({{props.originalRowsLength}} Total Registros)
                            </template>
                        </vue-bootstrap4-table>
                    </v-card-text>
                    <v-dialog
                    v-model="dialog_report"
                    width="1000"
                    >
                    <v-card>
                        <!-- <v-card-title
                        class="headline grey lighten-2"
                        primary-title
                        >
                        Reporte
                        </v-card-title> -->

                        <v-card-text>
                            <!--iframe id='ireport' :src="'/api/attendance_employee/'+this.employee.id" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe-->
                        </v-card-text>

                        <v-divider></v-divider>

                        <v-card-actions>
                        <div class="flex-grow-1"></div>
                        <v-btn
                            color="primary"
                            text
                            @click="dialog_report = false"
                        >
                        Cerrar
                        </v-btn>
                        </v-card-actions>
                    </v-card>
                    </v-dialog>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-2"
            >
                <v-card>
                 <loading :active.sync="isLoading"
                        :is-full-page="fullPage">
                    </loading>
                    <v-card-title>
                        <h3>Reporte de Funcionarios Inactivos</h3>
                    <v-spacer></v-spacer>

                    <br>
                    <v-dialog
                    v-model="dialog_2"
                    hide-overlay
                    persistent
                    width="300"
                    >
                    <v-card
                        color="primary"
                        dark
                    >
                        <v-card-text>
                        Por favor espere
                        <v-progress-linear
                            indeterminate
                            color="white"
                            class="mb-0"
                        ></v-progress-linear>
                        </v-card-text>
                    </v-card>
                    </v-dialog>
                    </v-card-title>                  

                    <div class="container-fluid h-100"> 
                        <div class="row w-100 align-items-center">
                            <div class="col-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement2(item)" :key="index">{{item.name}}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-9 text-center">
                                <v-btn @click="exportarExcelInActivo();" color="primary" dark class="mb-2 m-5">Exportar Excel</v-btn>
                            </div>	
                        </div>
                    </div> 

                    

                    <v-card-text>

                        <vue-bootstrap4-table :rows="employees2" :columns="columns2" :config="config" style="font-size: 12px;">
                            <template slot="sort-asc-icon">
                                <i class="fa fa-sort-asc"></i>
                            </template>
                            <template slot="sort-desc-icon">
                                <i class="fa fa-sort-desc"></i>
                            </template>
                            <template slot="no-sort-icon">
                                <i class="fa fa-sort"></i>
                            </template>
                            <template slot="active" slot-scope="props">
                                <div class="text-xs-center">
                                <v-chip :color="props.row.active?'success':'danger'" :text-color="props.row.active?'white':'danger'" small>{{props.row.active?'Activo':'Inactivo'}}</v-chip>
                                </div>
                            </template>
                            <template slot="pagination-info" slot-scope="props">
                                De {{props.currentPageRowsLength}}
                                a {{props.filteredRowsLength}}
                                ({{props.originalRowsLength}} Total Registros)
                            </template>
                        </vue-bootstrap4-table>
                    </v-card-text>
                    <v-dialog
                    v-model="dialog_report"
                    width="1000"
                    >
                    <v-card>
                        <!-- <v-card-title
                        class="headline grey lighten-2"
                        primary-title
                        >
                        Reporte
                        </v-card-title> -->

                        <v-card-text>
                            <!--iframe id='ireport' :src="'/api/attendance_employee/'+this.employee.id" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe-->
                        </v-card-text>

                        <v-divider></v-divider>

                        <v-card-actions>
                        <div class="flex-grow-1"></div>
                        <v-btn
                            color="primary"
                            text
                            @click="dialog_report = false"
                        >
                        Cerrar
                        </v-btn>
                        </v-card-actions>
                    </v-card>
                    </v-dialog>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-3"
            >                
                <v-card>
                 <loading :active.sync="isLoading"
                        :is-full-page="fullPage">
                    </loading>
                    <v-card-title>
                        <h3>Reporte de Todos los Funcionarios</h3>
                    <v-spacer></v-spacer>

                    <br>
                    <v-dialog
                    v-model="dialog_2"
                    hide-overlay
                    persistent
                    width="300"
                    >
                    <v-card
                        color="primary"
                        dark
                    >
                        <v-card-text>
                        Por favor espere
                        <v-progress-linear
                            indeterminate
                            color="white"
                            class="mb-0"
                        ></v-progress-linear>
                        </v-card-text>
                    </v-card>
                    </v-dialog>
                    </v-card-title>           

                    <div class="container-fluid h-100"> 
                        <div class="row w-100 align-items-center">
                            <div class="col-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-building"></i>  {{management?management.name:'Seleccione una Gerencia'}}
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" v-for="(item,index) in managements" @click="selectManagement3(item)" :key="index">{{item.name}}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-9 text-center">
                                <v-btn @click="exportarExcelTodos();" color="primary" dark class="mb-2 m-5">Exportar Excel</v-btn>
                            </div>	
                        </div>
                    </div>                     

                    <v-card-text>

                        <vue-bootstrap4-table :rows="employees3" :columns="columns3" :config="config" style="font-size: 12px;">
                            <template slot="sort-asc-icon">
                                <i class="fa fa-sort-asc"></i>
                            </template>
                            <template slot="sort-desc-icon">
                                <i class="fa fa-sort-desc"></i>
                            </template>
                            <template slot="no-sort-icon">
                                <i class="fa fa-sort"></i>
                            </template>
                            <template slot="active" slot-scope="props">
                                <div class="text-xs-center">
                                <v-chip :color="props.row.active?'success':'danger'" :text-color="props.row.active?'white':'danger'" small>{{props.row.active?'Activo':'Inactivo'}}</v-chip>
                                </div>
                            </template>
                            <template slot="pagination-info" slot-scope="props">
                                De {{props.currentPageRowsLength}}
                                a {{props.filteredRowsLength}}
                                ({{props.originalRowsLength}} Total Registros)
                            </template>
                        </vue-bootstrap4-table>
                    </v-card-text>
                    <v-dialog
                    v-model="dialog_report"
                    width="1000"
                    >
                    <v-card>
                        <!-- <v-card-title
                        class="headline grey lighten-2"
                        primary-title
                        >
                        Reporte
                        </v-card-title> -->

                        <v-card-text>
                            <!--iframe id='ireport' :src="'/api/attendance_employee/'+this.employee.id" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe-->
                        </v-card-text>

                        <v-divider></v-divider>

                        <v-card-actions>
                        <div class="flex-grow-1"></div>
                        <v-btn
                            color="primary"
                            text
                            @click="dialog_report = false"
                        >
                        Cerrar
                        </v-btn>
                        </v-card-actions>
                    </v-card>
                    </v-dialog>
                </v-card>
            </v-tab-item>
            
        </v-tabs>

        <div class="text-xs-center mt-3" v-if="employee">
        <!-- {{url}} -->
        <!-- <v-btn @click="next">Siguiente</v-btn> -->

        <!-- <v-btn  @click="showDialog(`/api/ficha_personal/${employee.id}`)"   >  Ver Reporte <v-icon right dark >printer</v-icon> </v-btn> -->
       
        </div>
        <v-dialog
        v-model="dialog_report"
        width="1000"
        >
        <v-card>
            <!-- <v-card-title
            class="headline grey lighten-2"
            primary-title
            >
            Reporte
            </v-card-title> -->

            <v-card-text>
                <!--iframe id='ireport' :src="url" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe-->
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
            <div class="flex-grow-1"></div>
            <v-btn
                color="primary"
                text
                @click="dialog_report = false"
            >
                Cerrar
            </v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>



    </v-card>
        
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
export default {
    data:()=>({
        isLoading: false,
        idManangementActivo: 0,
        idManangementInActivo: 0,
        idManangementTodos: 0,
        fullPage: true,
        managements:[],
        management:{},
        tab:'',
        columns: [
            {
                label: "Nro.",
                name: "id",
                filter: {
                    type: "simple",
                    placeholder: "Código"
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
                label: "CI",
                name: "identity_card",
                filter: {
                    type: "simple",
                    placeholder: "ci"
                },
                sort: true,
            },
            {
                label: "Gerencia",
                name: "management.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Unidad",
                name: "unity.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Cargo",
                name: "position.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Fecha de Ingreso",
                name: "entry_date",
                filter: {
                    type: "simple",
                    placeholder: "Fecha de Ingreso"
                },
                sort: true,
            },
            ],
        columns2: [
            {
                label: "Nro.",
                name: "id",
                filter: {
                    type: "simple",
                    placeholder: "Código"
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
                label: "CI",
                name: "identity_card",
                filter: {
                    type: "simple",
                    placeholder: "ci"
                },
                sort: true,
            },
            {
                label: "Gerencia",
                name: "management.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Unidad",
                name: "unity.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Cargo",
                name: "position.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Fecha de Ingreso",
                name: "entry_date",
                filter: {
                    type: "simple",
                    placeholder: "Fecha de Ingreso"
                },
                sort: true,
            },
            ],
        columns3: [
            {
                label: "Nro.",
                name: "id",
                filter: {
                    type: "simple",
                    placeholder: "Código"
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
                label: "CI",
                name: "identity_card",
                filter: {
                    type: "simple",
                    placeholder: "ci"
                },
                sort: true,
            },
            {
                label: "Gerencia",
                name: "management.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Unidad",
                name: "unity.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Cargo",
                name: "position.name",
                filter: {
                    type: "simple",
                    placeholder: "Cargo"
                },
                sort: true,
            },
            {
                label: "Fecha de Ingreso",
                name: "entry_date",
                filter: {
                    type: "simple",
                    placeholder: "Fecha de Ingreso"
                },
                sort: true,
            },
            ],                    
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
        employees: [],
        employee: {},
        employees2: [],
        employee2: {},
        employees3: [],
        employee3: {},
        dialog: false,
        dialog_assign:false,
        dialog_report:false,
        dialog_2:false,
        dialog_history:false,

    }),
    computed: {
        formTitle () {
                return this.editedIndex === -1 ? 'Nuevo' : 'Editar'
            }
    },
    mounted()
    {
        this.getManagements();
        this.search_funcionario_activo();
    },
    methods:{
        getManagements()
        {
            axios.get(`api/auth/management`)
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
        search_funcionario_activo(){
            this.isLoading=true;
            axios.post('api/auth/reporte_funcionario_activo')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees = response.data;
                });
        },
        selectManagement(management)
        {
            this.idManangementActivo = management.id;
            axios.post('api/auth/reporte_funcionario_activo',{id:management.id})
                 .then(response=>{
                     this.employees = response.data;
                 });
        },
        search_funcionario_inactivo(){
            this.isLoading=true;
            axios.post('api/auth/reporte_funcionario_inactivo')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees2 = response.data;
                });
        },
        selectManagement2(management)
        {
            this.idManangementInActivo = management.id;
            axios.post('api/auth/reporte_funcionario_inactivo',{id:management.id})
                .then((response)=>{
                    this.employees2 = response.data;
                });
        },
        search_funcionario_todos(){
            this.isLoading=true;
            axios.post('api/auth/reporte_funcionario_todos')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees3 = response.data;
                });
        },
        selectManagement3(management)
        {
            this.idManangementTodos = management.id;
            axios.post('api/auth/reporte_funcionario_todos',{id:management.id})
                .then((response)=>{
                    this.employees3 = response.data;
                });
        },
        exportarExcelActivo()
        {
            this.dialog_2 = true;
            axios({
                url: '/api/reporteExcelFuncionarioActivo/'+this.idManangementActivo,
                method: 'GET',
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'FuncionariosActivos'+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                this.dialog_2 = false;
            });
        },
        exportarExcelInActivo()
        {
            this.dialog_2 = true;
            axios({
                url: '/api/reporteExcelFuncionarioInActivo/'+this.idManangementInActivo,
                method: 'GET',
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'FuncionariosInActivos'+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                this.dialog_2 = false;
            });
        },
        exportarExcelTodos()
        {
            this.dialog_2 = true;
            axios({
                url: '/api/reporteExcelFuncionarioTodos/'+this.idManangementTodos,
                method: 'GET',
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'FuncionariosTodos'+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                this.dialog_2 = false;
            });
        },
    },
    components: {
        VueBootstrap4Table,
    }
}
</script>
// axios.get('api/auth/employee')
            axios.get('api/auth/employee_active')
                .then((response)=>{
                    this.employees = response.data;
                });