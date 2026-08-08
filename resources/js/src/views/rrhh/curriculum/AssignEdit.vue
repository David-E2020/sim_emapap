<template>
   <v-dialog v-model="dialog" persistent max-width="800px">
    <v-card>
    <v-card-title class="rrhh-primary">
        <span class="headline">{{ title }} </span>
    </v-card-title>

    <v-card-text v-if="item">
        <v-container grid-list-md>
            {{item.employee}}
            <v-layout wrap>
                <v-flex xs6 sm6 md6>
                    <v-combobox
                        v-model="type_hour"
                        :items="type_hours"
                        item-text='name'
                        label="Tipos de horario"
                    >
                    </v-combobox>
                </v-flex>
                <v-flex>
                    <v-btn @click="addTypeHour(type_hour)" color="success" > <v-icon>add</v-icon> Asignar Horario </v-btn>
                </v-flex>
                <v-flex xs12 sm12 md12>
                    <table class="table">
                        <thead class="rrhh-primary">
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Tolerancia</th>
                            <th>Dias</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <tr v-for="(item,index) in item.type_hours" :key="index" >
                                <td>{{item.code}}</td>
                                <td>{{item.name}}</td>
                                <td>{{item.entry}}</td>
                                <td>{{item.output}}</td>
                                <td>{{item.tolerance}}</td>
                                <td>
                                    <span class="badge badge-primary" v-if="item.monday">Lunes</span>
                                    <span class="badge badge-primary" v-if="item.tuesday">Martes</span>
                                    <span class="badge badge-primary" v-if="item.wednesday">Miercoles</span>
                                    <span class="badge badge-primary" v-if="item.thursday">Jueves</span>
                                    <span class="badge badge-primary" v-if="item.friday">Viernes</span>
                                    <span class="badge badge-primary" v-if="item.saturday">Sabado</span>
                                    <span class="badge badge-primary" v-if="item.sunday">Domingo</span>
                                </td>
                                <td>
                                    <v-icon @click="deleteTypeHour()">delete</v-icon>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </v-flex>


            </v-layout>
        </v-container>
    </v-card-text>

    <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" flat @click="sendClose()">Cancel</v-btn>

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
        type_hours:[],
        type_hour:null,
    }),
    mounted(){
        this.getTypeHours();
    },
    methods: {
        sendEmployee() {
            this.$emit('employee',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        getTypeHours()
        {
            axios.get('api/type_hour')
                 .then(response=>{
                     this.type_hours = response.data.type_hours;
                 })

        },
        addTypeHour(type_hour)
        {
            if(type_hour)
            {
                this.item.type_hours.push(type_hour);
            }
            else
            {
                 iziToast.info({
                                title: 'Antes de Adicionar',
                                message: 'Debe Seleccionar un Tipo de Horario: ',
                            })
            }
        },
        deleteTypeHour(index)
        {
            this.item.type_hours.splice(index, 1)
        }
    },
    computed:
    {
        item(){
           let item = this.employee
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='HORARIOS'
            if(this.item.id)
            {
                title = `HORARIOS ${this.employee.first_name} ${this.employee.second_name} ${this.employee.last_name} ${this.employee.mother_last_name}`
            }
            return title
        },
    },

}
</script>
