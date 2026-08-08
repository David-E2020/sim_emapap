<template>
<v-dialog v-model="dialog" max-width="700px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                 <v-layout wrap>
                    <v-flex xs6 sm6 md12>
                        <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.afp_name"></v-text-field>
                    </v-flex>
                    <!-- <v-flex xs12 sm12 md12>
                        <v-text-field label="Descripcion" hint="Ingrese Descripcion " v-model="item.description"></v-text-field>
                    </v-flex> -->

                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendContribution()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        contribution: Object
	},
    data:()=>({

    }),
    methods:{
        sendContribution() {
            this.$emit('contribution',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
    },
    computed:{
        item(){
           let item = this.contribution
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Tipo Afp'
            if(this.item.id) {

                title = 'Editar Tipo Afp'
            }
            return title
        },
	}
}
</script>
