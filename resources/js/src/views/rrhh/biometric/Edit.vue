<template>
    <v-dialog v-model="dialog" max-width="700px">
        <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md6>
                            <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md6>
                            <v-text-field label="Locacion" hint="Ingrese Locacion" required v-model="item.address">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md6>
                            <v-text-field label="Direccion Ip" hint="Ingrese Ip" required v-model="item.ip">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md6>
                            <v-text-field label="Puerto" hint="Ingrese Puerto" required v-model="item.port">
                            </v-text-field>
                        </v-flex>
                        <!-- <v-flex xs12 sm12 md12>
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
                        </v-flex> -->
                        <!-- <v-flex xs12 sm12 md12>
                        <v-text-field label="Descripcion" hint="Ingrese Descripcion " v-model="item.description"></v-text-field>
                    </v-flex> -->
                    </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>
                <v-btn color="blue darken-1" flat @click="sendBiometric()">Guardar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        biometric: Object
	},
    data:()=>({

    }),
    mounted(){

    },
    methods:{
        sendBiometric() {
            this.$emit('biometric',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },

    },
    computed:{
        item(){
           let item = this.biometric
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Biometrico'
            if(this.item.id)
            {
                title = 'Editar Biometrico'
            }
            return title
        },
	}
}
</script>
