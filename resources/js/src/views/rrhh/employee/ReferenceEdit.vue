<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }} </span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    {{item.employee}}
                 <v-layout wrap>



                     <v-flex xs12 sm12 md3>
                        <v-select
                        label="AFP"
                        v-model="item.contribution_id"
                        :items="contributions"
                        item-text="afp_name"
                        item-value="id"
                        hint="AFP"
                        >
                        </v-select>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Nua/Cua" hint="Ingrese Nua Cua" v-model="item.cua_nua"></v-text-field>
                    </v-flex>

                    <!-- <v-flex xs6 sm6 md3>
                        <v-text-field label="Banco" hint="Ingrese Banco" v-model="item.bank"></v-text-field>
                    </v-flex> -->
                      <v-flex xs12 sm12 md3>
                        <v-select
                        label="Banco"
                        v-model="item.bank"
                        :items="banks"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Nro Cuenta" hint="Ingrese Cuenta" v-model="item.account_number"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Caja de Seguro"
                        v-model="item.healh_box_id"
                        :items="health_boxes"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                     <v-flex xs6 sm6 md3>
                        <v-text-field label="Nro Matrícula" hint="Ingrese Matrícula" v-model="item.registration_number_medical"></v-text-field>
                    </v-flex>
                     <v-flex xs6 sm6 md3>
                        <v-text-field label="Tipo de Sangre" hint="Ingrese Tipo de Sangre" v-model="item.blood_type"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Médico" hint="Ingrese Médico" v-model="item.doctor_name"></v-text-field>
                    </v-flex>

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Numero de Dependiente" hint="Ingrese Nro Dependiente" v-model="item.number_dependency"></v-text-field>
                    </v-flex>
<!--
                    <v-flex xs6 sm6 md3>
                        <v-switch v-model="item.sworn_declaration" :label="`Declaracion Juarada:${item.sworn_declaration?'Si':'No'}`"></v-switch>
                    </v-flex> -->
                    <!-- <v-flex xs6 sm6 md3>
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
                            v-model="item.date_sworn_declaration"
                            label="Fecha de Declaracion"
                            hint="Ingrese fecha"
                            prepend-icon="event"
                            v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="item.date_sworn_declaration" no-title @input="menu1 = false"></v-date-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
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
                            v-model="item.date_reception"
                            label="Fecha de Recepcion"
                            hint="Recepcion en Recursos humanos"
                            prepend-icon="event"
                            v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="item.date_reception" no-title @input="menu2 = false"></v-date-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Nro de Declaracion" hint="Ingrese Nro Declaracion" v-model="item.number_declaration"></v-text-field>
                    </v-flex> -->


                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendEmployee()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        employee: Object
	},
    data:()=>({

        civil_statuses:[{id:'C',name:'Casado(a)'},{id:'S',name:'Soltero(a)'},{id:'V',name:'Viudo(a)'},{id:'D',name:'Divorciado(a)'}],
        banks:['Banco Union','Banco Bisa','BNB','Banco FIE','Banco Sol'],
        curriculum_name:'',
        curriculum_file:'',
        curriculum_url:'',
        genders:[{id:'M',name:'Masculino'} ,{id:'F',name:'Femenino'}],
        date: new Date().toISOString().substr(0, 10),
        date2: new Date().toISOString().substr(0, 10),
        menu: false,
        menu_birth_date:false,
        modal: false,
        menu2: false,
        imageData: "",
        health_boxes:[],
        contributions: [],
        menu1:false,

    }),
    mounted(){
        this.getHealthBoxes();
        this.getContributions();

    },
    methods:{
        getHealthBoxes (){
            axios.get('/api/health_box')
            .then(response => {
                this.health_boxes = response.data.health_boxes;
            })
            .catch(error => {
            });
        },
        getContributions (){
            axios.get('/api/contribution')
            .then(response => {
                this.contributions = response.data;
            })
            .catch(error => {
            });
        },
        sendEmployee()
        {
             this.item.health_box = _.find(this.health_boxes, (o) => { return o.id == this.item.healh_box_id; });
             this.item.contribution = _.find(this.contributions, (o) => { return o.id == this.item.contribution_id; });
            this.$emit('employee',this.item)
            this.GuardarDatosReferenciales(this.item);            
        },
        sendClose() {
            this.$emit('close',false)
        },
        save (date) {
            this.item.birth_date = date;
            this.$refs.menu_birth_date.save(date)
        },
        GuardarDatosReferenciales(data){
            axios.post('/api/actualizar_empleado',data)
                .then(response=>{
                    iziToast.success({
                                position: 'topRight',
                                title: 'Datos del referenciales',
                                message: 'Se actualizado los datos referenciales'
                    });
                     this.$emit('employee',response.data.employee)
                });
        }
    },
    computed:{
        item(){
           let item = this.employee
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Datos Referenciales'
            if(this.item.id) {

                title = 'Editar Datos Referenciales'
            }
            return title
        },
    },
    watch: {
      menu_birth_date (val) {
        val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
      },
    },

}
</script>
