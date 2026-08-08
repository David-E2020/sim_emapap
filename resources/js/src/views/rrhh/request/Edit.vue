<template>
<v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
            <v-card-title class="rrhh-primary" >
                <span >{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">

                 <v-layout wrap>
                    <v-flex xs12 sm6 md6>
                        <v-menu
                            ref="menu"
                            v-model="menu"
                            :close-on-content-click="false"
                            :return-value.sync="date"
                            transition="scale-transition"
                            offset-y
                            full-width
                            min-width="290px"
                        >
                            <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="item.date"
                                label="Picker in menu"
                                prepend-icon="event"
                                readonly
                                v-on="on"
                            ></v-text-field>
                            </template>
                            <v-date-picker v-model="item.date" no-title scrollable>
                            <v-spacer></v-spacer>
                            <v-btn text color="primary" @click="menu = false">Cancel</v-btn>
                            <v-btn text color="primary" @click="$refs.menu.save(date)">OK</v-btn>
                            </v-date-picker>
                        </v-menu>
                    </v-flex>

                    <v-flex xs6 sm6 md6>
                        <v-combobox
                        v-model="item.request_type"
                        :items="request_types"
                        label="Tipo de Solicitud"
                        item-text="name"
                        ></v-combobox>

                    </v-flex>

                    <v-flex xs11 sm3>
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
                            full-width
                            @click:minute="$refs.menu2.save(item.hour_in)"
                            ></v-time-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs11 sm3>
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
                            full-width
                            @click:minute="$refs.menu3.save(item.hour_out)"
                            ></v-time-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs6 sm6 md6 >
                        <v-text-field label="Lugar" hint="Ingrese Lugar" required v-model="item.destiny_place" v-if="item.request_type.name != 'LICENCIA DE 2 DÍAS AL AÑO'"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md12 v-if="item.request_type" >
                        <v-text-field label="Motivo" hint="Ingrese Motivo" required v-model="item.reason" v-if="item.request_type.name != 'LICENCIA DE 2 DÍAS AL AÑO'"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm12 md12 v-if="item.request_type">
                        <!-- {{item.request_type}} -->
                        <div v-if="item.request_type.name=='Permiso por Asueto'" >
                            <v-text-field :label="item.request_type.label1" v-if="item.request_type.label1" v-model="item.value1" ></v-text-field>
                        </div>
                        <div v-if="item.request_type.name=='Permiso por Tolerancia'">
                            <label for="">Tolerancia:</label>
                        </div>
                        <div v-if="item.request_type.name=='Permiso con Goce de Haberes'">
                            <label for="">Compensación:</label>
                        </div>
                         <div v-if="item.request_type.name=='Permiso sin Goce de Haberes'">
                            <label for="">Motivo:</label>
                        </div>
                            <v-switch
                            v-model="item.value1"
                            :label="`${item.request_type.label1}`"
                            v-if="item.request_type.label1 && item.request_type.name !='Permiso por Asueto'"
                            ></v-switch>
                            <v-switch
                            v-model="item.value2"
                            :label="`${item.request_type.label2}`"
                            v-if="item.request_type.label2"
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
                    <!-- {{JSON.stringify(item)}} -->
                    <!-- <v-flex xs12 sm12 md12>
                        <v-text-field label="Nombre Corto" hint="Ingrese Nombre Corto " v-model="item.shortened"></v-text-field>
                    </v-flex> -->

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
	}
}
</script>
