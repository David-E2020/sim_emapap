<template>
<v-dialog v-model="dialog" max-width="700px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                 <v-layout wrap>
                    <v-flex xs6 sm6 md6>
                        <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name"></v-text-field>
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
                    <v-flex xs6 sm6 md12>
                        <v-text-field label="Descripcion" hint="Ingrese Descripcion" required v-model="item.description"></v-text-field>
                    </v-flex>
                    <!-- <v-flex xs12 sm12 md12>
                        <v-text-field label="Nombre Corto" hint="Ingrese Nombre Corto " v-model="item.shortened"></v-text-field>
                    </v-flex> -->

                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendHolyday()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        holyday: Object
	},
    data:()=>({
        menu:false,
    }),
    methods:{
        sendHolyday() {
            this.$emit('holyday',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
    },
    computed:{
        item(){
           let item = this.holyday
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Dia Festivo'
            if(this.item.id) {

                title = 'Editar Dia Festivo'
            }
            return title
        },
	}
}
</script>
