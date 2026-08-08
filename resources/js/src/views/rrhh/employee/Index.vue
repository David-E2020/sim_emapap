<template>
    <v-card>
        <v-card-title>
        <v-toolbar flat>
            <v-toolbar-title>FUNCIONARIOS</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <div style="width: 300px;">
              <v-text-field v-model="search" solo append-icon="mdi-magnify" label="Buscar" dense single-line hide-details>
              </v-text-field>
            </div>

            <!-- Mas informacion del boton -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn v-bind="attrs" v-on="on" @click="create();" color="primary" dark class="mb-2"><v-icon>mdi-account-plus</v-icon></v-btn>
                </template>
                <span>Crear una nueva cuenta de usuario</span>
            </v-tooltip>

            <v-btn @click="download();" color="success" dark class="mb-2"><v-icon>mdi-cloud-download-outline</v-icon></v-btn>
        </v-toolbar>
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
          <v-row>
            <v-col cols="12">
                <v-btn @click="employee_active();" color="success" dark class="mb-2"><v-icon>mdi-account-voice</v-icon></v-btn>
                <v-btn @click="employee_inactive();" color="red" dark class="mb-2"><v-icon>mdi-account-voice-off</v-icon></v-btn>
                <v-btn @click="employee_full();" color="primary" dark class="mb-2"><v-icon>mdi-account-multiple</v-icon></v-btn>
            </v-col>
          </v-row>            
        </v-card-text>
        <v-card-text>
        <v-data-table :headers="headers" :items="employees" :search="search">
            <template v-slot:item.first_name="{ item }">
                {{ item.first_name }} {{ item.second_name }}
            </template>
            <template v-slot:item.acciones="{ item }">
                <v-icon @click="disabled(props.row)" v-if="item.user_edit==true" >
                    mdi-check-circle
                </v-icon> 
                <v-icon @click="enabled(item)" v-if="item.user_edit==false" >
                    mdi-checkbox-blank-circle-outline
                </v-icon>
                <v-icon @click="edit(item)">
                    mdi-lead-pencil
                </v-icon>
                <v-icon @click="destroy(item)" v-if="item.status_employee=='A'">
                    mdi-delete
                </v-icon>
                <v-icon @click="construct(item)" v-if="item.status_employee=='D'">
                    mdi-person
                </v-icon>
                <v-icon @click="construct(item)" v-if="item.status_employee=='' || item.status_employee==null">
                    mdi-person
                </v-icon>
                 <v-icon @click="history_assing(item)">
                    mdi-clipboard-account
                </v-icon>
            </template>
        </v-data-table>
        </v-card-text>
        <edit-employee :dialog="dialog" :employee="employee" @close="close"  @employee="update"></edit-employee>
        <assign-edit :dialog="dialog_assign" :employee="employee" @close="close_assign" @employee="update_assign">  </assign-edit>
        <history-employee :dialog="dialog_history" :employee="employee" @close="close_history"></history-employee>

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
        <template>
            <v-row justify="center">
                <v-dialog v-model="dialogConfirmacion" persistent max-width="344">
                    <v-card>
                        <v-system-bar small>
                          <v-spacer></v-spacer>
                          <v-btn icon  color="error" small @click="dialogConfirmacion=false"><v-icon>mdi-close-box</v-icon></v-btn>
                        </v-system-bar>
                        <v-card-title class="text-h6">Desea dar de baja al Empleado?</v-card-title>
                        <v-card-text>Verifique los datos antes de ejecutar la acción</v-card-text>
                        <v-card-actions>
                          <v-spacer></v-spacer>
                          <v-btn color="danger" small @click="back()">Cancelar</v-btn>
                          <v-btn color="primary" small @click="destroy(form_delete)">Aceptar</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-row>
        </template>
    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditEmployee from './Edit.vue';
import AssignEdit from './AssignEdit';
import HistoryEmployee from './History.vue';
export default {
    data:()=>({
        isLoading: false,
        fullPage: true,
        snackbar: {
            status: false,
            text: '',
        },
        search: '',
        headers: [
            { text: 'Acciones', value: 'acciones', sortable: true },
            { text: 'C.I', value: 'identity_card', sortable: true },
            { text: 'Nombres', value: 'first_name', sortable: true },
            { text: 'Ap. Paterno', value: 'last_name', sortable: true },
            { text: 'Ap. Materno', value: 'mother_last_name', sortable: true },
            { text: 'Cel/Tel', value: 'cellphone', sortable: true },
            { text: 'Correo', value: 'email', sortable: false },
            { text: 'Ap. Gerencia', value: 'management.name', sortable: true },
            { text: 'Ap. Cargo', value: 'position.name', sortable: true },
            { text: 'Biometrico', value: 'biometric_code', sortable: true },
        ],
        employees: [],
        employee: {},
        dialog: false,
        dialog_assign:false,
        dialog_report:false,
        dialog_2:false,
        dialog_history:false,
        dialogConfirmacion: false,
        form_delete:{},

    }),
    computed: {
        formTitle () {
                return this.editedIndex === -1 ? 'Nuevo' : 'Editar'
            }
    },
    mounted()
    {
        this.search_employee();
    },
    methods:{

        enabled(item)
        {
            
            axios.post('api/enabled_employee',{id:item.id,user_edit:true})
                .then(response=>{
                    this.search_employee();
                })
        },
        disabled(item)
        {
            axios.post('api/enabled_employee',{id:item.id,user_edit:false})
                .then(response=>{
                    this.search_employee();
                })
        },

        search_employee(){
            // axios.get('api/employee')
            this.isLoading=true;
            axios.get('api/employee_active')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees = response.data;
                });
        },
        edit_assing(item)
        {
            axios.get(`api/employee/${item.id}`)
                .then(response=>{
                    this.employee= response.data.employee;
                    this.dialog_assign = true;
                });
        },

        history_assing(item)
        {
            axios.get(`api/employee_history/${item.id}`)
                .then(response=>{
                    this.employee= response.data.employee;
                    this.dialog_history = true;
                });
        },
        close_history()
        {
            this.dialog_history= false;
        },
        update_assign(item)
        {
            axios.post(`api/assign_type_hour`,item)
                .then(response=>{
                    iziToast.success({
                                title: 'Se asigo horario a ',
                                message: `${response.data.name}`,
                            })
                        this.dialog_assign = false;
                });
        },
        close_assign()
        {
            this.dialog_assign = false;
        },
        create() {
            this.employee ={};
            this.dialog = true;
        },

        edit (item) {
            this.editedIndex = this.employees.indexOf(item)
            axios.get(`/api/employee/${item.id}/edit`)
            .then(response => {
                this.employee = response.data.employee
            })
            .catch(error => {

            });

            this.dialog = true
        },
        update (item) {
            if (item.first_name==undefined ||item.first_name==null  && item.document_type_id==null && item.city_identity_card_id==null && item.position_id==undefined || item.position_id==null) {
               iziToast.success({
                            position: 'topRight',
                            title: 'Campos requeridos',
                            message: 'Campos obligatorios primer nombre, paterno, cargo, ci y tipo documento',
                            theme: 'question', // dark
                            color: 'red', // blue, red, green, yellow
                        });
            }else{
            this.isLoading = true;
            let formData = new FormData();
            formData.set('first_name',item.first_name|| '');
            formData.set('second_name',item.second_name|| '');
            formData.set('last_name',item.last_name|| '') ;
            formData.set('mother_last_name',item.mother_last_name|| '');
            formData.set('biometric_code',item.biometric_code || '' );
            formData.set('identity_card',item.identity_card || '');
            // // formData.set('identity_card_id',item.identity_card_id);
            formData.set('birth_date',item.birth_date || '');
            formData.set('cellphone',item.cellphone || '');
            formData.set('city_identity_card_id',item.city_identity_card_id || ''); 
            formData.set('contract_type_id',item.contract_type_id || '');

            formData.set('military_serial_number',item.military_serial_number || '');

            formData.set('civil_status',item.civil_status || '');
            formData.set('contract_modality_id',item.contract_modality_id || '');
            formData.set('contribution_id',item.contribution_id || '');
            formData.set('country_id',item.country_id || '' );
            formData.set('cua_nua',item.cua_nua || '');
            formData.set('disability',item.disability || '');
            formData.set('document_type_id',item.document_type_id || '');
            formData.set('entry_date',item.entry_date || '');
            formData.set('gender',item.gender || '');
            formData.set('management_id',item.management_id || '');
            formData.set('unit_id',item.unit_id || '');
            formData.set('phone',item.phone || '');
            formData.set('position_id',item.position_id || '');
            formData.set('profession',item.profession || ''); 
            formData.set('reason',item.reason || '');
            formData.set('salary',item.salary || '');
            formData.set('tutor',item.tutor || '');
            formData.set('retirement_date',item.retirement_date || '');
            formData.set('planta_id',item.planta_id || '');
            formData.set('address',item.address || '');
            if(item.id)
            {
                formData.set('id',item.id);
            }

            formData.append("curriculum_file", item.curriculum_file || '');
            // formData.append("curriculum_file", null);
            formData.append("image_file", item.image_file || '');
            axios.post('/api/employee', formData,{
                            headers: {
                                    'Content-Type': 'multipart/form-data'
                                    }
                        })
                    .then(response => {
                        this.isLoading = false;
                        if (response.data.success=="true") {
                            iziToast.success({
                                title: 'Exitoso',
                                message: 'Datos Guardados del empleado exitosoo!',
                            });
                            this.search_employee();
                        }else if(response.data.success=="false"){
                            iziToast.error({
                                title: 'Error',
                                message: 'No se puede guardar los datos llene los campos vacios faltantes!',
                            });
                        }else if(response.data.success=="ci"){
                            iziToast.error({
                                title: 'Error',
                                message: response.data.mensaje,
                            });
                        }
                        //this.$store.dispatch('template/showMessage',{message:'Se Actualizó la lista de productos',color:'success'});
                        
                        // this.search_employee();
                    })
                    .catch(function (error) {
                        //this.$store.dispatch('template/showMessage',{message:error,color:'danger'});
                        this.isLoading = true;
                        iziToast.error({
                            title: 'Error',
                            message: 'No se pudo registrar, error contactase con el ',
                        });

                    });
                this.dialog =false;
            }
        },
        destroy (item) {

            axios.post('api/employee_delete',{id:item.id,user_edit:false})
                .then(response=>{
                this.search_employee();
            })
        },
        construct (item) {
            Swal.fire({
            title: '¿ Decea activar este Funcionario ?',
            text: "Presione ACEPTAR para confirmar",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.post('api/employee_create',{id:item.id,user_edit:false})
                    .then(response=>{
                        this.employee_inactive();
                    })
                }
            });
        },

        close() {
            this.dialog = false;
        },
        show_kardex(employee)
        {
            this.employee = employee;
            this.dialog_report = true;
        },
        download(){
            // `this` inside methods point to the Vue instance
            this.dialog_2 = true;
            //  self.dialog = true;
            //let parameters = this.getParams();
            //parameters.excel =true;
            axios({
                url: '/api/roe',
                method: 'GET',
              //  params: parameters,
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Roe'+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                this.dialog_2 = false;
            });
        },
        employee_active() {
            // axios.get('api/employee')
            this.isLoading=true;
            axios.get('api/employee_active')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees = response.data;
                });
        },
        employee_inactive() {
            // axios.get('api/employee')
            this.isLoading=true;
            axios.get('api/employee_inactive')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees = response.data;
                });
        },
        employee_full() {
            this.isLoading=true;
            axios.get('api/employee')
            // axios.get('api/employee_inactive')
                .then((response)=>{
                    this.isLoading=false;
                    this.employees = response.data;
                });
        },

    },
    components: {
        VueBootstrap4Table,
        EditEmployee,
        AssignEdit,
        HistoryEmployee,
    }
}
</script>