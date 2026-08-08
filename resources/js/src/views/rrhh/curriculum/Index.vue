<template>
    <v-card>
        <loading :active.sync="isLoading"
            :is-full-page="fullPage">
        </loading>
        <v-card-title>
            <h3>Curriculum</h3>
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
                <template slot="option" slot-scope="props">

                    <v-icon @click="disabled(props.row)" v-if="props.row.user_edit==true" >
                        check_box
                    </v-icon>
                    <v-icon @click="enabled(props.row)" v-if="props.row.user_edit==false" >
                        crop_square
                    </v-icon>

                     <v-icon @click="curriculum(props.row)">
                        history_edu
                    </v-icon>
                    <v-icon color='blue' @click="showDialog(`/api/ficha_personal/${props.row.id}`)" v-if="!props.row.user_edit">
                        description
                    </v-icon>
                    <v-icon @click="showDialog(`/api/ficha_personal/${props.row.id}`)" v-if="props.row.user_edit">
                        description
                    </v-icon>
                    <v-icon @click="show_photo(props.row)">
                        image
                    </v-icon>
                </template>
            </vue-bootstrap4-table>
        </v-card-text>
        <edit-employee :dialog="dialog" :employee="employee" @close="close"  @employee="update"></edit-employee>
        <!-- <assign-edit :dialog="dialog_assign" :employee="employee" @close="close_assign" @employee="update_assign">  </assign-edit> -->
        <!-- <history-employee :dialog="dialog_history" :employee="employee" @close="close_history"></history-employee> -->

        
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
                <iframe id='ireport' :src="url" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe>
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
import EditEmployee from './Edit.vue';
export default {
    data:()=>({
        isLoading: false,
        fullPage: true,
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
                label: "Gerencia",
                name: "management.name",
                filter: {
                    type: "simple",
                    placeholder: "Gerencia"
                },
                sort: true,
            },
            {
                label: "Cargo",
                name: "position.name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Cargo"
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

        employees: [],
        employee: {},
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
        this.search();
    },
    methods:{

        search(){
            axios.get('api/employee')
                .then((response)=>{
                    this.employees = response.data;
                });
        },
        curriculum(item){
            // alert(item.id);
            sessionStorage.setItem('curriculum_individual', item.id);
            var link = document.createElement("a");
            link.href = `/employee_info2`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        showDialog(url){
            var data_url=url.split('/');
            var cadena="/"+data_url[1]+"/"+data_url[2];
            if (cadena=='/api/ficha_personal') {
            this.url =url;
            this.dialog_report = true; 
            }else{
               this.url ="storage"+url;
                this.dialog_report = true; 
            }
            
        },
        show_photo(item){
            this.editedIndex = this.employees.indexOf(item)
            axios.get(`/api/employee/${item.id}/edit`)
            .then(response => {
                this.employee = response.data.employee
            })
            .catch(error => {
                
            });

            this.dialog = true
        },
        enabled(item)
        {
            
            axios.post('api/enabled_employee',{id:item.id,user_edit:true})
                .then(response=>{
                    this.search();
                })
        },
        disabled(item)
        {
            axios.post('api/enabled_employee',{id:item.id,user_edit:false})
                .then(response=>{
                    this.search();
                })
        },

      

        close() {
            this.dialog = false;
        },

    },
    components: {
        VueBootstrap4Table,
        EditEmployee,
    }
}
</script>
// axios.get('api/employee')
            axios.get('api/employee_active')
                .then((response)=>{
                    this.employees = response.data;
                });