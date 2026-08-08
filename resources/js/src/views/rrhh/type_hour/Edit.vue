<template>
    <v-dialog v-model="dialog" max-width="700px">
        <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md8>
                            <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md4>
                            <v-text-field label="Codigo" hint="Ingrese Codigo" required v-model="item.code">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md4>
                            <v-select
                            label="Turno de Trabajo"
                            v-model="item.work_shift"
                            :items="work_shifts"
                            hint="Seleccione Turno de Trabajo"
                            >
                            </v-select>
                        </v-flex>
                        <v-flex xs12 sm12 md4>
                            <v-select
                            label="Tipo Horario"
                            v-model="item.hours"
                            :items="hours"
                            hint="Seleccione tipo horario"
                            >
                            </v-select>
                        </v-flex>
                        <v-flex xs12 sm12 md4>
                            <v-combobox
                                v-model="item.location"
                                :items="locations"
                                item-text='name'
                                label="Ubicacion"
                            >
                            </v-combobox>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu1"
                                v-model="menu1"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.entry"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"

                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.entry"
                                    label="Entrada"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu1"
                                v-model="item.entry"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu1.save(item.entry)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu2"
                                v-model="menu2"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.start_of_entry"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"

                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.start_of_entry"
                                    label="Entrada Inicio"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu2"
                                v-model="item.start_of_entry"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu2.save(item.start_of_entry)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu3"
                                v-model="menu3"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.end_of_entry"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.end_of_entry"
                                    label="Entrada Fin"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu3"
                                v-model="item.end_of_entry"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu3.save(item.end_of_entry)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu4"
                                v-model="menu4"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.tolerance_entry"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.tolerance_entry"
                                    label="Tolerancia"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu4"
                                v-model="item.tolerance_entry"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu4.save(item.tolerance_entry)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <!-- hasta aqui lo de inicio  -->
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu5"
                                v-model="menu5"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.output"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"

                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.output"
                                    label="Salida"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu5"
                                v-model="item.output"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu5.save(item.output)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu6"
                                v-model="menu6"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.start_of_output"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"

                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.start_of_output"
                                    label="Salida Inicio"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu6"
                                v-model="item.start_of_output"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu6.save(item.start_of_output)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu7"
                                v-model="menu7"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.end_of_output"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.end_of_output"
                                    label="Salida Fin"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu7"
                                v-model="item.end_of_output"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu7.save(item.end_of_output)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs3 sm3 md3 >
                            <v-menu
                                ref="menu8"
                                v-model="menu8"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                :return-value.sync="item.tolerance_output"
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="item.tolerance_output"
                                    label="Tolerancia"
                                    prepend-icon="access_time"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-time-picker
                                v-if="menu8"
                                v-model="item.tolerance_output"
                                format="24hr"
                                full-width
                                @click:minute="$refs.menu8.save(item.tolerance_output)"
                                ></v-time-picker>
                            </v-menu>
                        </v-flex>

                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.monday"
                            label="Lunes" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.tuesday"
                            label="Martes" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.wednesday"
                            label="Miercoles" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.thursday"
                            label="Jueves" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                         <v-switch
                            v-model="item.friday"
                            label="Viernes" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.saturday"
                            label="Sabado" >
                        </v-switch>
                    </v-flex>
                    <v-flex xs3 sm3 md3>
                        <v-switch
                            v-model="item.sunday"
                            label="Domingo" >
                        </v-switch>
                    </v-flex>


                    </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendTypeHour()">Guardar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        type_hour: Object
	},
    data:()=>({
        menu1:false,//entrada
        menu2:false,//entrada
        menu3:false,//entrada
        menu4:false, //entrada
        menu5:false,//salida
        menu6:false,//salida
        menu7:false,//salida
        menu8:false, //salida
        work_shifts:['Mañana','Tarde','Noche'],
        hours:['NORMAL','CONTINUO'],
        locations:[],
    }),
    mounted(){
        this.getLocations();
    },
    methods:{
        sendTypeHour() {
            this.$emit('type_hour',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        getLocations()
        {
            axios.get(`api/auth/location`)
                 .then(response=>{
                     this.locations = response.data.locations;
                 });
        }

    },
    computed:{
        item(){
           let item = this.type_hour
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Horario'
            if(this.item.id)
            {
                title = 'Editar Horario'
            }
            return title
        },
	}
}
</script>
