<template>
<v-dialog v-model="dialog_show" max-width="700px" persistent>
            <v-card>
            <v-card-title class="headline" >
                <span >{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">

                 <v-layout wrap>
                    <v-flex xs12 sm6 md12>
                       <v-timeline>
                            <v-timeline-item
                                class="text-right"
                                v-for="(approve,index) in item.approves"
                                :key="index"
                                :icon="getIcon(approve.state)"
                                :color="getColor(approve.state)"
                            >{{approve.position.name}}</v-timeline-item>
                        </v-timeline>
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

                <!-- <v-btn color="blue darken-1" flat @click="sendRequest()">Guardar</v-btn> -->
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog_show: Boolean,
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
    }),
    mounted(){
    },
    methods:{
        sendRequest() {
            this.$emit('employee_request_show',this.item)
        },
        sendClose() {
            this.$emit('close_show',false)
        },
        getIcon(state){
            let icon = "donut_large";
            if(state=='Aprobado'){
                icon = 'done'
            }
            if(state=='Rechazado'){
                icon = 'cancel'
            }
            return icon;
        },
        getColor(state){
            let color = "grey lighten-2";
            if(state=='Aprobado'){
                color = 'green lighten-1'
            }
            if(state=='Rechazado'){
                color = 'red lighten-2'
            }
            return color;
        }

    },
    computed:{
        item(){
           let item = this.employee_request
           return item
        },
        parent_dialog(){
			return this.dialog_show
        },
        title(){
            let title='Estado de Aprobacion de la boleta'
            // if(this.item.id) {

            //     title = 'Editar Boleta'
            // }
            return title
        },
	}
}
</script>
