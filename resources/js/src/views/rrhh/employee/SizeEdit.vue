<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }} </span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    <!-- {{item.employee}} -->
                 <v-layout wrap>

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Numero de Botas " hint="Ingrese Nro Botas" v-model="item.boots_number"></v-text-field>
                    </v-flex>

                    <v-flex xs12 sm12 md3 v-if="employee.gender == 'F'">
                        <v-select
                        label="Talla de Blusas"
                        v-model="item.blouses"
                        :items="sizes"
                        >
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Talla de Camisas"
                        v-model="item.shirt"
                        :items="sizes"
                        >
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Talla de Poleras"
                        v-model="item.t_shirt"
                        :items="sizes"
                        >
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Talla de Chamarras"
                        v-model="item.jacket"
                        :items="sizes"
                        >
                        </v-select>
                    </v-flex>



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
        sizes: ['XS','S','M','L','XL','XXL','XXXL'],

    }),
    mounted(){


    },
    methods:{


        sendEmployee() {
            this.$emit('employee',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        save (date) {
            this.item.birth_date = date;
            this.$refs.menu_birth_date.save(date)
        },
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
            let title='Crear Empleado'
            if(this.item.id) {

                title = 'Editar Datos Personales'
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
