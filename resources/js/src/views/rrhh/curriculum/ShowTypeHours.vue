<template>
    <v-card>
        <v-card-title>
            <h3>Mis Horarios</h3>
        <v-spacer></v-spacer>

        <!-- <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn> -->
        </v-card-title>
        <v-card-text v-if="employee">
            <!-- {{employee}} -->
            <table class="table">
                <thead class="rrhh-primary">
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tolerancia</th>
                    <th>Dias</th>
                </thead>
                <tbody>
                    <tr v-for="(item,index) in employee.type_hours" :key="index" >
                        <td>{{item.code}}</td>
                        <td>{{item.name}}</td>
                        <td>{{item.entry}}</td>
                        <td>{{item.output}}</td>
                        <td>{{item.tolerance}}</td>
                        <td>
                            <span class="badge badge-success" v-if="item.monday">Lunes</span>
                            <span class="badge badge-success" v-if="item.tuesday">Martes</span>
                            <span class="badge badge-success" v-if="item.wednesday">Miercoles</span>
                            <span class="badge badge-success" v-if="item.thursday">Jueves</span>
                            <span class="badge badge-success" v-if="item.friday">Viernes</span>
                            <span class="badge badge-success" v-if="item.saturday">Sabado</span>
                            <span class="badge badge-success" v-if="item.sunday">Domingo</span>
                        </td>

                    </tr>
                </tbody>
            </table>
        </v-card-text>

    </v-card>
</template>
<script>
export default {
    data:()=>({
        employee:null
    }),
    mounted(){
        this.getTypeHour();
    },
    methods:
    {
        getTypeHour()
        {
            axios.get(`api/auth/employee/${this.user.usr_prs_id}`)
                 .then(response=>{
                     this.employee = response.data.employee;
                 })
        }
    },
    computed:{
        user(){
            return this.$store.state.auth.user;
        }
    }
}
</script>
