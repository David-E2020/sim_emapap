<template>
 <v-card>
        <v-card-title>
            <h3>Generación de Contratos</h3>
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
                    <v-icon @click="printContrato(props.row)" >
                        print
                    </v-icon>
                </template>
            </vue-bootstrap4-table>
        </v-flex>
        <v-flex xs4 style="padding-left: 5px">
            <v-card>
                <v-layout>
                    <!-- <v-flex xs6 sm6 md6 >
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
                    <v-flex xs6 sm6 md6 >
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
                    </v-flex>                     -->
                    <!-- <v-flex xs12 sm8 md8>
                            <v-select
                            :items="type_hours"
                            v-model="type_hour_id"
                            item-text="name"
                            item-value="id"
                            prepend-icon="access_time"
                            ></v-select> -->
                            <!-- {{type_hour}} -->
                    <!-- </v-flex> -->

                </v-layout>
                <br>
                <v-flex xs12 sm12 md12>
                        <v-btn color="primary" small @click="printContratos()"> Imprimir Contratos</v-btn>
                        <v-btn color="primary" small @click="printContratosZip()"> Imprimir Contratos ZIP</v-btn>
                        <!-- <a href="/api/reporte_contrato/1/2020-06-23/2020-06-23/uno" target="_blank" rel="noopener noreferrer">fer</a> -->
                        
                        <v-flex xs12 sm12 md12>
                            <v-select
                            label="Tipo de Contrato"
                            v-model="items.type_contract_id"
                            :items="this.type_contrac"
                            item-text="name"
                            item-value="id"
                            hint=""
                            persistent-hint>
                            </v-select>
                        </v-flex>
                </v-flex>
                <v-list >
                    <v-subheader inset>Funcionarios Seleccionados: {{items.length}}</v-subheader>

                    <v-list-tile
                        v-for="(item,index ) in items"
                        :key="index"
                        avatar
                    >
                        <v-list-tile-avatar>
                        <v-icon color='blue' >person</v-icon>
                        </v-list-tile-avatar>

                        <v-list-tile-content>
                        <v-list-tile-title>{{ item.first_name + " " + item.second_name  + " " + item.last_name  + " " + item.mother_last_name }}</v-list-tile-title>
                        <v-list-tile-sub-title class="caption   "> {{ item.management.name }}</v-list-tile-sub-title>
                        <v-list-tile-sub-title class="caption   "> {{ item.contract_type.name }}</v-list-tile-sub-title>
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

        <v-dialog
            v-model="dialog_printer"
            max-width="800"
        >
            <v-card>
                <v-toolbar dark color="rrhh-primary">
                <v-btn icon dark @click="dialog_printer = false">
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>Reporte Contrato</v-toolbar-title>
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
        <v-dialog
            v-model="dialog_printers"
            max-width="800"
        >
            <v-card>
                <v-toolbar dark color="rrhh-primary">
                <v-btn icon dark @click="dialog_printers = false">
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>Reporte Contrato</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    <v-btn dark flat @click="dialog_printers = false">Salir</v-btn>
                </v-toolbar-items>
                </v-toolbar>
                    <iframe :src="getUrls" frameborder="0" style="height:600px;width:100%;" ></iframe>
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
        dialog_printer:false,
        dialog_printers:false,
        employees:[],
        type_hours:[],
        managements:[],
        locations:[],
        location:null,
        management:null,
        type_hour_id:{},
        employee:{},
        items:[],
        // itemsContratos:[],
        date:null,
        menu:false,
        menu1:false,
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
            axios.get(`api/auth/type_hour`)
                 .then(response=>{
                     this.type_hours = response.data.type_hours;
                 });
        },
        search(){
            axios.get(`api/auth/employee_contract`)
                 .then((response)=>{
                    this.employees = response.data;

                });
            
            axios.get('/api/auth/contract_type3')
                .then((response)=>{
                this.type_contrac = response.data;
            });
        },
        getLocation(){
            axios.get(`api/auth/location`)
                 .then(response=>{
                     this.locations = response.data.locations;
                 });
        },
        getManagements()
        {
            axios.get(`api/auth/management`)
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
            axios.get(`api/auth/employees_management_contract/${this.management.id}`)
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
            // alert(item.id)
            var permiso=true;
            this.items.forEach( function(valor, indice, array){
                if(item.id == valor.id){
                    permiso=false;
                }         
            });
            if(permiso==true){
                var aux_contrato_name = "";
                var aux_gerencia_name = "";
                try {
                    var aux_test = item.contract_type.name;
                } catch (error) {
                    aux_contrato_name="NO SE LE ASIGNO TIPO DE CONTRATO";

                    iziToast.warning({
                        title: 'Alerta del sistema',
                        message: 'NO SE LE ASIGNO TIPO DE CONTRATO!',
                        position: 'center'
                    });
                }
                if(aux_contrato_name!=""){
                    item.contract_type = {"name":aux_contrato_name+""};
                }
                try {
                    var aux_test2 = item.management.name;
                } catch (error) {
                    aux_gerencia_name="NO SE LE ASIGNO GERENCIA";

                    iziToast.warning({
                        title: 'Alerta del sistema',
                        message: 'NO SE LE ASIGNO GERENCIA!',
                        position: 'center'
                    });
                }
                if(aux_gerencia_name!=""){
                    item.management = {"name":aux_gerencia_name+""};
                }
                this.items.push(item)
            }else{
                // alert("El funcionario ya fue seleccionado")
                iziToast.warning({
                    title: 'Alerta del sistema',
                    message: 'El funcionario ya fue seleccionado!',
                });
            }
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
                    axios.post('api/auth/eventual_schedule',params)
                        .then(response=>{
                             iziToast.success({
                                title: 'Se asigno horario a los empleados',
                                message: 'a los empleados',
                            });
                            this.$router.push('/attendance')

                        });
                }
            })


        },
        printContrato(employee)//(employee,sw)
        {
            if(this.items.type_contract_id){

                this.employee = employee;
                var link = document.createElement("a");
                link.href =  ""+`/api/reporte_contrato/${employee.id}/${this.items.type_contract_id}/1/2020-06-23`;
                link.type="application/octet-stream .docx";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }else{

                iziToast.warning({
                    title: 'Alerta del sistema',
                    message: 'Escoja el tipo de contrato por favor!',
                    position: 'center'
                });
            }
        },
        printContratos()//(employee,sw)
        {
            if(this.items.type_contract_id){
                // alert(this.items);
                this.employees = this.items;
                //this.dialog_printers = true;
                var tipo_contrato = this.items.type_contract_id
                this.items.forEach( function(valor, indice, array){
                    // alert(valor.id)
                    var link = document.createElement("a");
                    // link.download.useDownloadDir = "fer.pdf";
                    link.download = "error.pdf";
                    link.href = `/api/reporte_contrato/${valor.id}/${tipo_contrato}/1/2020-06-23`;
                    document.body.appendChild(link);
                    // alert(valor.id)
                    link.click();
                    document.body.removeChild(link);
                });
            }else{
                // alert("escoja el tipo de contrato por favor")
                iziToast.warning({
                    title: 'Alerta del sistema',
                    message: 'Escoja el tipo de contrato por favor!',
                });
            }
        },
        printContratosZip()//(employee,sw)
        {
            if(this.items.type_contract_id){
                // alert(this.items);
                this.employees = this.items;
                //this.dialog_printers = true;
                var tipo_contrato = this.items.type_contract_id
                var id_imprimir = "";
                var cont = 0;
                this.items.forEach( function(valor, indice, array){
                    if(cont==0){
                        id_imprimir = "" + valor.id;    
                        //alert(id_imprimir)                    
                    }else{
                        id_imprimir = id_imprimir + "," + valor.id;
                        //alert(id_imprimir)
                    }
                    cont++;
                    //alert(id_imprimir)
                });
                if(cont>0){
                    // alert(valor.id)
                    var link = document.createElement("a");
                    link.href = `/api/reporte_contrato/${id_imprimir}/${tipo_contrato}/2/2020-06-23`;
                    document.body.appendChild(link);
                    // alert(valor.id)
                    link.click();
                    document.body.removeChild(link);
                }
            }else{
                // alert("escoja el tipo de contrato por favor")
                iziToast.warning({
                    title: 'Alerta del sistema',
                    message: 'Escoja el tipo de contrato por favor!',
                });
            }
        },
    },
    computed:{
        getUrl()
        {
            let url=''
            if(this.employee.id)
            {
                url= `/api/reporte_contrato/${this.employee.id}/2020-06-23/2020-06-23/uno`;
            }
            return url;
        },
        getUrls()
        {
            let url=''
            if(this.employees)
            {
                if(this.dialog_printers){
                    // array = new Array();
                    var itemsContratos = "";
                    var cont = 0;
                    this.items.forEach( function(valor, indice, array){
                        if(cont==0){
                            itemsContratos = itemsContratos + "" + valor.id;
                        }else{
                            itemsContratos = itemsContratos + "," + valor.id;
                        }                                                
                        cont++;

                        var link = document.createElement("a");
                        link.download = "fer.pdf";
                        link.href = `/api/reporte_contrato/${valor.id}/2020-06-23/2020-06-23/uno`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                    // return;
                    // alert(itemsContratos);
                    url= `/api/reporte_contrato/${itemsContratos}/2020-06-23/2020-06-23/mas`;
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