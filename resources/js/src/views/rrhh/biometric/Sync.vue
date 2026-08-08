<template>
    <v-dialog v-model="dialog" max-width="700px">
        <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>

            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex md12 class="text-lg-right">
                             Ip: {{item.ip}} Puerto: {{item.port}} Nombre: {{name}} Hora: {{time}}

                        </v-flex>
                        <v-flex md12 v-if="show_progress">
                            <v-progress-circular
                                :size="25"
                                color="primary"
                                indeterminate
                            >
                            </v-progress-circular>
                            Estableciendo Conexion
                        </v-flex>


                        <!-- <v-flex xs12 sm12 md6>
                            <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name">
                            </v-text-field>
                        </v-flex> -->


                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cerrar</v-btn>
                <v-btn color="blue darken-1" flat @click="getInfo()">Conectar</v-btn>
                <v-btn color="success darken-1" flat @click="Sync()">Sincronizar Todo</v-btn>
                <v-btn color="warning darken-1" flat @click="SyncDiary()">Sincronizar Fecha Actual</v-btn>
                <v-spacer></v-spacer>
            </v-card-actions>
            <v-card-actions>
                    <v-flex xs3 sm3 md3 >
                            <v-menu
                            ref="menu"
                            v-model="menu"
                            :close-on-content-click="false"
                            full-width
                            max-width="290px"
                            min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                                <v-text-field
                                v-model="fecha_general"
                                label="De Fecha"
                                hint="YYYY-MM-DD"
                                persistent-hint
                                prepend-icon="event"
                                v-on="on"
                                ></v-text-field>
                            </template>
                            <v-date-picker v-model="fecha_general" no-title @input="menu = false"></v-date-picker>
                            </v-menu>
                    </v-flex>
                <v-btn color="danger darken-2" flat @click="SyncDiaryFecha()">Sincronizar por Fecha</v-btn>
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
        show_progress: false,
        name: '',
        time:'',
        fecha_general:'',
        menu:false,
    }),
    mounted(){

    },
    methods:
    {
        Sync()
        {
            this.show_progress = true;

            axios.post('/api/sync_biometric',this.item)
                 .then(response=>{
                    iziToast.success({
                        title: 'Sincronizacion exitosa',
                        message: 'Se realizo la sincronizacion correctamente',
                    });
                    this.show_progress = false;
                 })
                 .catch( (error) => {
                    this.show_progress = false;
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo establecer conexion con el biometrico',
                    });
                  });
        },
        SyncDiary()
        {
            this.show_progress = true;

            axios.post('/api/sync_biometric_diary',this.item)
                 .then(response=>{
                    iziToast.success({
                        title: 'Sincronizacion exitosa',
                        message: 'Se realizo la sincronizacion correctamente',
                    });
                    this.show_progress = false;
                 })
                 .catch( (error) => {
                    this.show_progress = false;
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo establecer conexion con el biometrico',
                    });
                  });
        },
        SyncDiaryFecha(){
            this.show_progress = true;
            this.item.fecha_general=this.fecha_general;
            axios.post('/api/sync_biometric_diary_fecha',this.item)
                 .then(response=>{
                    iziToast.success({
                        title: 'Sincronizacion exitosa',
                        message: 'Se realizo la sincronizacion correctamente',
                    });
                    this.show_progress = false;
                 })
                 .catch( (error) => {
                    this.show_progress = false;
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo establecer conexion con el biometrico',
                    });
                  });
        },
        // connect(){

        // },
        getInfo()
        {
            this.show_progress = true;
            axios.get(`/api/info_biometric/${this.item.id}`)
                 .then(response=>{
                     this.name = response.data.name;
                     this.time = response.data.time;
                     this.show_progress = false;
                 })
                 .catch( (error) => {
                    // handle error
                    this.show_progress = false;
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo establecer conexion con el biometrico',
                    });
                  });
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
            let title=''
            if(this.item.id)
            {
                title = 'Sincronizar '+this.item.name
            }
            return title
        },
	}
}
</script>
