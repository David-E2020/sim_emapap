<template>
 <v-card>
        <v-card-title>
            <h3>Asignacion de Horario Eventual</h3>
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
                                v-model="date"
                                label="Fecha"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="date" no-title @input="menu = false"></v-date-picker>
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
                <v-flex xs12 sm12 md12>
                        <v-btn color="primary" small @click="store()"> Asignar Horario</v-btn>
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
                        <v-list-tile-title>{{ item.first_name }}</v-list-tile-title>
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
        menu:false,
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
    },
    components: {
        VueBootstrap4Table,
    }
}
</script>
