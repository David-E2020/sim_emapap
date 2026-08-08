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
                        <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name"></v-text-field>
                    </v-flex>
                     <v-flex xs12 sm12 md12>
                        <v-select
                        label="Gerencia"
                        v-model="item.managament_id"
                        :items="managements"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>

                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancel</v-btn>

                <v-btn color="blue darken-1" flat @click="sendUnity()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        unity: Object
	},
    data:()=>({
        managements:[]
    }),
    mounted(){
        axios.get('/api/auth/management')
             .then((response)=>{
                // this.employees = response.data;
                this.managements = response.data;
            });
    },
    methods:{
        sendUnity() {
            this.$emit('unity',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
    },
    computed:{
        item(){
           let item = this.unity
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Unidad'
            if(this.item.id) {

                title = 'Editar Unidad'
            }
            return title
        },
	}
}
</script>
