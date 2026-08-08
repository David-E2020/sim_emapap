<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>

                 <v-layout wrap>

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Primer Nombre" hint="Ingrese Primer Nombre" required v-model="item.first_name"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Segundo Nombre" hint="Ingrese Seugndo Nombre" required v-model="item.second_name"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Apellido Paterno" hint="Ingrese Apellido Paterno" v-model="item.last_name"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Apellido Materno" hint="Ingrese Apellido Materno" v-model="item.mother_last_name"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-select
                        label="Parentesco"
                        v-model="item.kinship_id"
                        :items="kinships"
                        item-text="name"
                        :rules="[rules.required]"
                        item-value="id"
                        :hint="`Selecciones Parentesco`"
                        >
                        </v-select>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-menu
                            ref="menu_birth_date"
                            v-model="menu_birth_date"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            full-width
                            min-width="290px"
                        >
                            <template v-slot:activator="{ on }">
                            <v-text-field
                                v-model="item.birth_date"
                                label="Fecha de Nacimiento"
                                prepend-icon="event"
                                :rules="[rules.required]"
                                readonly
                                v-on="on"
                            ></v-text-field>
                            </template>
                            <v-date-picker
                            ref="picker"
                            v-model="item.menu_birth_date"
                            :max="new Date().toISOString().substr(0, 10)"
                            min="1950-01-01"
                            @change="save"
                            ></v-date-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Caja de Seguro"
                        v-model="item.healh_box_id"
                        :items="health_boxes"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Nro de Matricula" hint="Ingrese  Matricula" v-model="item.number_healt_box"></v-text-field>
                    </v-flex>
                     <v-flex xs6 sm6 md3>
                        <v-text-field label="Edad" hint=" Edad" v-model="getAge" disabled></v-text-field>
                    </v-flex>

                    <v-flex xs6 sm6 md3 v-if="item.age >= 9 && item.age<=12 && item.kinship_id==2">
                        <v-switch v-model="item.has_vaccine" :label="`Vacuna (VPH):${item.has_vaccine?'Si':'No'}`"></v-switch>
                    </v-flex>

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Teléfono" hint="Ingrese Teléfono" v-model="item.phone"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Celular" hint="Ingrese Celular" v-model="item.cellphone"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md6>
                        <v-text-field label="Dirección" hint="Ingrese Dirección" v-model="item.address"></v-text-field>
                    </v-flex>
                    <!-- <v-flex xs6 sm6 md3>
                        <v-switch v-model="item.disability" :label="`Certificado de Discapacidad:${item.disability?'Si':'No'}`"></v-switch>
                    </v-flex> -->
                    <v-flex xs6 sm6 md3>
                        <v-switch v-model="item.is_reference" :label="`Es Contacto de Emergencia?:${item.is_reference?'Si':'No'}`"></v-switch>
                    </v-flex>
                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendFamily()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        family: Object
	},
    data:()=>({
        kinships: [],
        contributions: [],
        civil_statuses:[{id:'C',name:'Casado(a)'},{id:'S',name:'Soltero(a)'},{id:'V',name:'Viudo(a)'},{id:'D',name:'Divorciado(a)'}],

        genders:[{id:'M',name:'Masculino'} ,{id:'F',name:'Femenino'}],
        date: new Date().toISOString().substr(0, 10),
        date2: new Date().toISOString().substr(0, 10),
        menu: false,
        menu_birth_date:false,
        modal: false,
        menu2: false,
        imageData: "",
        health_boxes:[],
        rules: {
          required: value => !!value || 'Campo obligatorio.',
        },


    }),
    mounted(){
        this.getHealthBoxes();
        this.getKinships();

    },
    methods:{

        getKinships (){
            axios.get('/api/kinship')
            .then(response => {
                this.kinships = response.data.kinships;
            })
            .catch(error => {
            });
        },
        sendFamily()
        {
             this.item.kinship = _.find(this.kinships, (o) => { return o.id == this.item.kinship_id; });
             this.$emit('family',this.item);
             //this.GuardarFamiliar(this.item);            
        },
        sendClose() {
            this.$emit('close',false)
        },
        save (date) {
            this.item.birth_date = date;
            // this.calculateAge();
            // this,item.age = this.calculateAge(date);
            this.$refs.menu_birth_date.save(date)
        },
        getHealthBoxes (){
            axios.get('/api/health_box')
            .then(response => {
                this.health_boxes = response.data.health_boxes;
            })
            .catch(error => {
            });
        },

       

    },
    computed:{
        item(){
           let item = this.family
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title()
        {
            let title='Adicionar Familiar'
            if(this.item.id)
            {
                title = 'Datos Familiar'
            }
            return title
        },
        getAge()
        {
            let age=0;
            if(this.item)
            {
                let today = new Date();
                let birthDate = new Date(this.item.birth_date);
                age = today.getFullYear() - birthDate.getFullYear();
                let m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate()))
                {
                    age--;
                }

                this.item.age = age;
            }

            return age;
        }

    },
    watch: {
      menu_birth_date (val) {
        val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
      },
    },

}
</script>
