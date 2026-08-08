<template>
    <v-card>
        <v-card-title>
            <h3>Mis Horarios Asignados</h3>
        <v-spacer></v-spacer>

        <!-- <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn> -->
        </v-card-title>
        <v-card-text v-if="employee">
            <!-- {{employee}} -->
            <table class="table">
                <thead class="rrhh-primary">
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tolerancia Ingreso</th>
                    <th>Tolerancia Salida</th>
                    <th>Dias</th>
                </thead>
                <tbody>
                    <tr v-for="(item,index) in employee.type_hours_employee" :key="index" >
                        <td>{{item.type_date.code}}</td>
                        <td>{{item.type_date.name}}</td>
                        <td>{{item.date_start}}</td>
                         <td>{{item.date_finish}}</td>
                        <td>{{item.type_date.entry}}</td>
                        <td>{{item.type_date.output}}</td>
                        <td>{{item.type_date.tolerance_entry}}</td>
                        <td>{{item.type_date.tolerance_output}}</td>
                        <td>
                            <span class="badge badge-success" v-if="item.type_date.monday">Lunes</span>
                            <span class="badge badge-success" v-if="item.type_date.tuesday">Martes</span>
                            <span class="badge badge-success" v-if="item.type_date.wednesday">Miercoles</span>
                            <span class="badge badge-success" v-if="item.type_date.thursday">Jueves</span>
                            <span class="badge badge-success" v-if="item.type_date.friday">Viernes</span>
                            <span class="badge badge-success" v-if="item.type_date.saturday">Sabado</span>
                            <span class="badge badge-success" v-if="item.type_date.sunday">Domingo</span>
                        </td>

                    </tr>
                </tbody>
            </table>
            <div class="bg-dark">
                <b style="color:white">HORARIOS EVENTUALES</b>
            </div>
            <table class="table">
                <thead class="rrhh-primary">
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tolerancia Ingreso</th>
                    <th>Tolerancia Salida</th>
                    <th>Dias</th>
                </thead>
                <tbody>
                    <tr v-for="(item,index) in employee.eventual_schedule" :key="index" >
                        <td>{{item.type_hour.code}}</td>
                        <td>{{item.type_hour.name}}</td>
                        <td>{{item.date}}</td>
                        <td>{{item.type_hour.entry}}</td>
                        <td>{{item.type_hour.output}}</td>
                        <td>{{item.type_hour.tolerance_entry}}</td>
                        <td>{{item.type_hour.tolerance_output}}</td>
                        <td>
                            <span class="badge badge-success" v-if="item.type_hour.monday">Lunes</span>
                            <span class="badge badge-success" v-if="item.type_hour.tuesday">Martes</span>
                            <span class="badge badge-success" v-if="item.type_hour.wednesday">Miercoles</span>
                            <span class="badge badge-success" v-if="item.type_hour.thursday">Jueves</span>
                            <span class="badge badge-success" v-if="item.type_hour.friday">Viernes</span>
                            <span class="badge badge-success" v-if="item.type_hour.saturday">Sabado</span>
                            <span class="badge badge-success" v-if="item.type_hour.sunday">Domingo</span>
                        </td>

                    </tr>
                </tbody>
            </table>
        </v-card-text>
        <!-- <edit-biometric :dialog="dialog" :biometric="biometric" @close="close"  @biometric="update"></edit-biometric>
        <sync-biometric :dialog="dialog_sync" :biometric="biometric" @close="close_sync" ></sync-biometric> -->

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
            axios.get(`api/employee/${this.user.usr_prs_id}`)
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
