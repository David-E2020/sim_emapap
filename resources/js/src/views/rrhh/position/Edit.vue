<template>
    <v-dialog v-model="dialog" max-width="700px">
        <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md12>
                            <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-combobox label="Escala Salarial" v-model="item.salary_scale" item-text="name"
                                :items="salaries">
                            </v-combobox>
                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-select label="Tipo de Dependencia" v-model="item.type_dependency"
                                :items="type_dependencies">
                            </v-select>
                        </v-flex>
                        <v-flex xs12 sm12 md12 v-if="item.type_dependency">
                            <v-select label="Unidad" v-model="item.unit_id" :items="unities" item-text="name"
                                item-value="id" v-if="item.type_dependency=='Gerencia'|| item.type_dependency=='Unidad' ">
                            </v-select>
                            <v-select label="Gerencia" v-model="item.managament_id" :items="managements"
                                item-text="name" item-value="id" v-if="item.type_dependency=='Gerencia Ejecutiva'">
                            </v-select>
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

                <v-btn color="blue darken-1" flat @click="sendPosition()">Guardar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        position: Object
	},
    data:()=>({
        unities:[],
        managements:[],
        salaries:[],
        type_dependencies:['Gerencia Ejecutiva','Gerencia','Unidad'],
    }),
    mounted(){
        this.getUnities();
        this.getManagements();
        this.getSalaries();
    },
    methods:{
        sendPosition() {
            this.$emit('position',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        getUnities (){
            axios.get('/api/auth/unity')
            .then(response => {
                this.unities = response.data;
            })
            .catch(error => {
            });
        },
        getManagements (){
            axios.get('/api/auth/management')
            .then(response => {
                this.managements = response.data;
            })
            .catch(error => {
            });
        },
        getSalaries()
        {
            axios.get(`api/auth/salary`)
                .then(response=>{
                    this.salaries = response.data.salaries;
                });
        }
    },
    computed:{
        item(){
            let item = this.position
            return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Cargo'
            if(this.item.id) {

                title = 'Editar Cargo'
            }
            return title
        },
	}
}
</script>
