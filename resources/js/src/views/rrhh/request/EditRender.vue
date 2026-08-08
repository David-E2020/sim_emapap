<template>
<v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
            <v-card-title class="rrhh-primary" >
                <span >{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
            	 <v-flex xs12 sm12 md12>
                        <v-combobox
                        v-model="item.request_type"
                        :items="request_types"
                        label="Tipo de Solicitud"
                        item-text="name"
                        @change="ValidacionBoleta(item.request_type)"
                        ></v-combobox>
                    </v-flex>

                    <v-flex xs12 sm6 md6 >
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
                            v-model="item.date"
                            label="Fecha"
                            hint="YYYY-MM-DD"
                            persistent-hint
                            prepend-icon="event"
                            v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="item.date" no-title @input="menu = false"></v-date-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs12 sm6 md6 v-if="hasToCombo">
                        <v-combobox
                        v-model="item.request_day"
                        :items="request_day"
                        label="Tipo de licencia"
                        item-text="name"
                        @change="ValidacionLicencia(item)"
                        ></v-combobox>
                    </v-flex>
                    <v-flex xs12 sm6 md6 v-if="hasToDate">
                         <v-menu
                        ref="menu4"
                        v-model="menu4"
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
                            v-model="item.todate"
                            label="A Fecha"
                            hint="YYYY-MM-DD"
                            persistent-hint
                            prepend-icon="event"
                            v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="item.todate" no-title @input="menu4 = false"></v-date-picker>
                        </v-menu>
                    </v-flex>


                    <v-flex xs11 sm3 v-if="hasHours">
                        <v-menu
                            ref="menu2"
                            v-model="menu2"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            :return-value.sync="item.hour_in"
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"

                        >
                            <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="item.hour_in"
                                label="De horas"
                                prepend-icon="access_time"
                                readonly
                                v-on="on"
                            ></v-text-field>
                            </template>
                            <v-time-picker
                            v-if="menu2"
                            v-model="item.hour_in"
                            format="24hr"
                            full-width
                            @click:minute="$refs.menu2.save(item.hour_in)"
                            ></v-time-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs11 sm3 v-if="hasHours">
                        <v-menu
                            ref="menu3"
                            v-model="menu3"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            :return-value.sync="item.hour_out"
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px"
                        >
                            <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="item.hour_out"
                                label="A horas"
                                prepend-icon="access_time"
                                readonly
                                v-on="on"
                            ></v-text-field>
                            </template>
                            <v-time-picker
                            v-if="menu3"
                            v-model="item.hour_out"
                            format="24hr"
                            full-width
                            @click:minute="$refs.menu3.save(item.hour_out)"
                            ></v-time-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs6 sm6 md6 v-if="hasPlace">
                        <v-text-field label="Lugar" hint="Ingrese Lugar" required v-model="item.destiny_place"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md12 v-if="hasReason" >
                        <v-text-field label="Motivo" hint="Ingrese Motivo" required v-model="item.reason" ></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md12 >
                        <v-text-field label="Autorizado por:" hint="Ingrese Nombre" required v-model="item.authorized_name" ></v-text-field>
                    </v-flex>

                    <v-flex xs6 sm6 md6 v-if="isBreak">
                        <v-text-field :label="`${item.request_type.label1}`" required v-model="item.value1"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md6 v-if="isBreak">
                         <v-menu
                        ref="menu5"
                        v-model="menu5"
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
                            v-model="item.value2"
                            :label="`${item.request_type.label2}`"
                            hint="YYYY-MM-DD"
                            persistent-hint
                            prepend-icon="event"
                            v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="item.value2" no-title @input="menu5 = false"></v-date-picker>
                        </v-menu>
                        <!-- <v-text-field :label="`${item.request_type.label2}`" required v-model="item.value2"></v-text-field> -->
                    </v-flex>

                    <v-flex xs12 sm12 md12 v-if="item.request_type">

                        <div v-if="item.request_type.name=='DESCANSO ANUAL' || item.request_type.name=='LICENCIA DE 2 DÍAS AL AÑO'">
                            <label for="">toma solo medio dia?:</label>
                        </div>
                        <div v-if="item.request_type.name=='PERMISO PERSONAL CON GOCE DE HABERES'">
                            <label for="">Compensación:</label>
                        </div>
                         <div v-if="item.request_type.name=='TOLERANCIA' || item.request_type.name=='PERMISO PERSONAL SIN GOCE DE HABERES' ">
                            <label for="">Motivo:</label>
                        </div>
                            <v-switch
                            v-model="item.value1"
                            :label="`${item.request_type.label1} : ${item.value1?'Si':'No'}`"
                            v-if="item.request_type.label1 &&  item.request_type.id == 1"
                            ></v-switch>
                            <v-switch
                            v-model="item.value1"
                            :label="`${item.request_type.label1} `"
                            v-if="item.request_type.label1 && isSwith"
                            ></v-switch>
                            <v-switch
                            v-model="item.value2"
                            :label="`${item.request_type.label2}`"
                            v-if="item.request_type.label2 && isSwith"
                            ></v-switch>
                            <v-switch
                            v-model="item.value3"
                            :label="`${item.request_type.label3}`"
                            v-if="item.request_type.label3"
                            ></v-switch>
                            <v-switch
                            v-model="item.value4"
                            :label="`${item.request_type.label4}`"
                            v-if="item.request_type.label4"
                            ></v-switch>
                            <v-switch
                            v-model="item.value5"
                            :label="`${item.request_type.label5}`"
                            v-if="item.request_type.label5"
                            ></v-switch>
                    </v-flex>

                </v-layout>
     
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cerrar</v-btn>

                <v-btn color="red darken-1" flat @click="sendRequest('Rechazado')">Rechazar</v-btn>
                <v-btn color="green darken-1" flat @click="sendRequest('Aprobado')">Aprobar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        employee_request: Object
	},
    data:()=>({
        date: new Date().toISOString().substr(0, 10),
        menu: false,
        request_types: [],
        time: null,
        menu2: false,
        time2: null,
        menu3:false,
        menu4:false,
    }),
    mounted(){
        this.getRequestTypes();
    },
    methods:{
        sendRequest(approve_state) {
            this.item.approve_state = approve_state
            this.$emit('employee_request',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        getRequestTypes (){
            axios.get('/api/request_type')
            .then(response => {
                this.request_types = response.data.request_types;
            })
            .catch(error => {
            });
        },
    },
    computed:{
        item(){
           let item = this.employee_request
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Boleta'
            if(this.item.employee) {

                title = this.item.employee.first_name +' '+this.item.employee.second_name+' '+this.item.employee.last_name+ ' '+ this.item.employee.mother_last_name;
            }
            return title
        },
          hasToDate(){
            let has = false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 3:
                    case 5:
                    case 6:
                    case 7:
                        has=true;
                        break;
                    case 8:
                        has=false;
                        break;
                    case 9:
                        has=true;
                        break;
                    case 10:
                        has=true;
                        break;
                     default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        hasToCombo(){
            let has = false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 3:
                    case 5:
                    case 6:
                    case 7:
                        has=false;
                        break;
                    case 8:
                        has=true;
                          this.request_day=[{id:1,name:"1/2 Media Jornada"},{id:2,name:"1 Dia Laboral"}]
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        hasPlace()
        {
            let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 2:
                        has=true;
                        break;
                    case 9:
                        has=true;
                        break;
                    case 10:
                        has=false;
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        hasHours()
        {
            let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                        has=true;
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        hasReason()
        {
            let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        has=true;
                        break;
                    case 10:
                        has=false;
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        hasInterval(){
        	 let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        has=false;
                        break;
                    case 10:
                        has=true;
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        },
        isSwith(){
            let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 1:
                    case 7:
                        has=true;
                        break;
                    case 8:
                       if(this.item.request_day){
                            if (this.item.request_day.id==1) {
                                has=false;
                            }else{                                
                                has=true;
                            }
                        }else{
                            has=true;
                        }
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return !has;
        },
        isBreak(){
            let has= false;
            if(this.item.request_type)
            {
                switch (this.item.request_type.id) {
                    case 7:
                        has=true;
                        break;
                    default:
                        has=false;
                        break;
                }
            }
            return has;
        }
	}
}
</script>
