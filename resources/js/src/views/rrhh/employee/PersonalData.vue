<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    {{item.employee}} 
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

                    <v-flex xs6 sm6 md2>
                        <v-text-field label="C.I." hint="Ingrese cedula de identidad" v-model="item.identity_card" :rules="[rules.required]"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm12 md1>
                        <v-select
                        label="Ciudad"
                        v-model="item.city_identity_card_id"
                        :rules="[rules.required]"
                        :items="cities"
                        item-text="dep_nombre"
                        item-value="id"
                        :hint="``"
                        persistent-hint>
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
                                prepend-icon="mdi-calendar"
                                readonly
                                v-on="on"
                                :rules="[rules.required]"
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
                         <v-autocomplete ref="selectCountry" required v-model="item.country_id"
                          :items="countries" dense outlined hide-details="auto" item-text="nombre"
                          item-value="id" :rules="rulesSelect" label="Pais">
                          <template slot="selection" slot-scope="{ item }">
                            {{ item.nombre }}
                          </template>
                        </v-autocomplete>
                    </v-flex>
                     <v-flex xs12 sm12 md3>
                        <v-select
                        label="Género"
                        v-model="item.gender"
                        :rules="[rules.required]"
                        :items="genders"
                        hint="Seleccione Genero"
                        item-text="name"
                        item-value="id"
                        >
                        </v-select>
                    </v-flex>
                     <!-- <v-flex xs12 sm12 md3>
                        <v-select
                        label="AFP"
                        v-model="item.contribution_id"
                        :items="contributions"
                        item-text="afp_name"
                        item-value="id"
                        hint="AFP"
                        >
                        </v-select>
                    </v-flex> -->
                    <!-- <v-flex xs6 sm6 md3>
                        <v-text-field label="Nua/Cua" hint="Ingrese Nua Cua" v-model="item.cua_nua"></v-text-field>
                    </v-flex> -->

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Telefono1" hint="Ingrese Teléfono" v-model="item.phone"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Celular" hint="Ingrese Celular" v-model="item.cellphone"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Celular Corporativo" hint="Ingrese Celular Corporativo" v-model="item.corporate_cell"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Email" hint="Ingrese Email" v-model="item.personal_email"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Email Institucional" hint="Ingrese Email" v-model="item.corporate_email"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-select
                        label="Estado Civil"
                        v-model="item.civil_status"
                        :items="civil_statuses"
                        item-text="name"
                        item-value="id"
                        :hint="`Selecciones estado Civil`"
                        >
                        </v-select>
                    </v-flex>

                    <!-- <v-flex xs6 sm6 md6>
                        <v-text-field label="Profesion" hint="Ingrese Profesion" v-model="item.profession"></v-text-field>
                    </v-flex> -->
                    <v-flex xs6 sm6 md6>
                        <v-text-field label="Dirección" hint="Ingrese Dirección" v-model="item.address"></v-text-field>
                    </v-flex>

<!--
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Modalidad Contrato"
                        v-model="item.contract_modality_id"
                        :items="contract_modalities"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md3>
                        <v-select
                        label="Tipo de Contrato"
                        v-model="item.contract_type_id"
                        :items="contract_types"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex> -->
                    <v-flex xs12 sm12 md6>
                        <v-select
                        label="Gerencia"
                        v-model="item.management_id"
                        :items="managements"
                        :rules="[rules.required]"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md6>
                        <v-select
                        label="Departamento"
                        :rules="[rules.required]"
                        v-model="item.unit_id"
                        :items="unities"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md6>
                        <v-combobox
                        label="Cargo"
                        v-model="item.position"
                        :items="positions"
                        item-text="name"
                        :rules="[rules.required]"
                        :hint="`Descripcion del tipo seleccionado`"
                        persistent-hint>
                        </v-combobox>
                         <model-select :options="positions"
                                v-model="item.position"
                                placeholder="seleccionar cargo">
                        </model-select>
                    </v-flex>
                    <!-- <v-flex xs6 sm6 md3>
                        <v-text-field label="Salario" hint="Ingrese total ganado" v-model="item.salary"></v-text-field>
                    </v-flex> -->
                    <v-flex xs6 sm6 md3>
                        <v-switch v-model="item.has_military_card" :label="`Libreta Militar:${item.has_military_card?'Si':'No'}`"></v-switch>
                    </v-flex>
                    <v-flex xs6 sm6 md3 v-if="item.has_military_card">
                        <v-text-field label="Nro de Libreta" hint="Nro de Libreta" v-model="item.military_serial_number"></v-text-field>
                    </v-flex>

                    <v-flex xs6 sm6 md3>
                        <v-switch v-model="item.disability" :label="`Certificado de Discapacidad:${item.disability?'Si':'No'}`"></v-switch>
                    </v-flex>
                    <v-flex xs6 sm6 md3 v-if="item.disability">
                        <v-text-field label="Tutor" hint="Tutor del Descapacitado" v-model="item.tutor"></v-text-field>
                    </v-flex>



                    <!-- <v-flex xs6 sm6 md3>

                        <v-img
                            :src="item.employee_image_path?item.employee_image_path.toString().substring(7,item.employee_image_path.length):''"
                            :lazy-src="item.employee_image_path?item.employee_image_path.toString().substring(7,item.employee_image_path.length):''"
                            aspect-ratio="1"
                            class="grey lighten-2"
                            >
                            <template v-slot:placeholder>
                                <v-layout
                                fill-height
                                align-center
                                justify-center
                                ma-0
                                >
                                <v-progress-circular indeterminate color="grey lighten-5"></v-progress-circular>
                                </v-layout>
                            </template>
                        </v-img>
                        <v-btn @click="pickImage" >
                            <v-icon>file_upload</v-icon>
                            Subir Imagen
                        </v-btn>
                        <input
                            type="file"
                            style="display: none"
                            ref="image"
                            accept="image/*"
                            @change="previewImage"
                        >
                    </v-flex> -->

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
        areas: [],
        cities: [],
        countries: [],
        types: [],
        positions: [],
        contract_types: [],
        contract_modalities: [],
        document_types: [],
        managements: [],
        unities: [],
        contributions: [],
        civil_statuses:[{id:'C',name:'Casado(a)'},{id:'S',name:'Soltero(a)'},{id:'V',name:'Viudo(a)'},{id:'D',name:'Divorciado(a)'},{id:'U',name:'Concubinato'}],
        curriculum_name:'',
        curriculum_file:'',
        curriculum_url:'',
        genders:[{id:'M',name:'Masculino'} ,{id:'F',name:'Femenino'}],
        date: new Date().toISOString().substr(0, 10),
        date2: new Date().toISOString().substr(0, 10),
        menu: false,
        menu_birth_date:false,
        modal: false,
        menu2: false,
        imageData: "",
        rules: {
          required: value => !!value || 'Required.',
        },


    }),
    mounted(){
        // this.getAreas();
        // this.getTypes();
        this.getPositions();
        this.getCities();
        this.getCountries();
        this.getDocumentTypes();
        this.getContractTypes();
        this.getContractModalities();
        this.getManagements();
        this.getUnities();
        this.getContributions();

    },
    methods:{
        pickFile () {
            this.$refs.curriculum.click ()
        },
        pickImage(){
             this.$refs.image.click ()
        },
        onFilePicked (e) {
            const files = e.target.files
			if(files[0] !== undefined) {
				this.curriculum_name = files[0].name
				if(this.curriculum_name.lastIndexOf('.') <= 0) {
					return
				}
				const fr = new FileReader ()
				fr.readAsDataURL(files[0])
				fr.addEventListener('load', () => {
					this.curriculum_url = fr.result
                    this.curriculum_file = files[0] // this is an excel file that can be sent to server...
                    this.item.curriculum_file = this.curriculum_file;
				})
			} else {
				this.curriculum_name = ''
				this.curriculum_file = ''
				this.curriculum_url = ''
			}
        },
        previewImage(event) {
            // Reference to the DOM input element
            var input = event.target;
            // Ensure that you have a file before attempting to read it
            if (input.files && input.files[0]) {
                // create a new FileReader to read this image and convert to base64 format
                var reader = new FileReader();
                // Define a callback function to run, when FileReader finishes its job
                reader.onload = (e) => {
                    // Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
                    // Read image as base64 and set to imageData
                    this.item.employee_image_path ='public/'+ e.target.result;
                    this.item.image_file =input.files[0];
                    // this.item.imageData = this.imageData;
                }
                // Start the reader job - read file as a data url (base64 format)
                reader.readAsDataURL(input.files[0]);
            }
        },
        getPositions (){
            axios.get('/api/position')
            .then(response => {
                this.positions = response.data;
            })
            .catch(error => {
            });
        },
        getCities (){
            axios.get('/api/city')
            .then(response => {
                this.cities = response.data;
            })
            .catch(error => {
            });
        },
        getCountries (){
            axios.get('/api/country')
            .then(response => {
                this.countries = response.data;
            })
            .catch(error => {
            });
        },
        getContributions (){
            axios.get('/api/contribution')
            .then(response => {
                this.contributions = response.data;
            })
            .catch(error => {
            });
        },
        // getTypes() {
        //     axios.get('/api/employee_type')
        //     .then(response => {
        //         this.types = response.data.types
        //     })
        //     .catch(error => {
        //     });
        // },
        getDocumentTypes() {
            axios.get('/api/document_type')
            .then(response => {
                this.document_types = response.data
            })
            .catch(error => {
            });
        },
        getContractTypes() {
            axios.get('/api/contract_type')
            .then(response => {
                this.contract_types = response.data
            })
            .catch(error => {
            });
        },
        getContractModalities() {
            axios.get('/api/contract_modality')
            .then(response => {
                this.contract_modalities = response.data
            })
            .catch(error => {
            });
        },
        getManagements() {
            axios.get('/api/management')
            .then(response => {
                this.managements = response.data
            })
            .catch(error => {
            });
        },
        getUnities() {
            axios.get('/api/unity')
            .then(response => {
                this.unities = response.data
            })
            .catch(error => {
            });
        },
        // getAreas() {
        //     axios.get('/api/area')
        //     .then(response => {
        //         this.areas = response.data.areas
        //     })
        //     .catch(error => {
        //     });
        // },
        // getContracTypes() {
        //     axios.get('/api/employee_contract_type')
        //     .then(response => {
        //         this.contract_types = response.data.contract_types
        //     })
        //     .catch(error => {
        //     });
        // },
        sendEmployee()
        {
            //before send
            this.item.country = _.find(this.countries, (o) => { return o.id == this.item.country_id; });
            this.$emit('employee',this.item)
            this.GuardarDatosPersonales(this.item);            
        },
        sendClose() {
            this.$emit('close',false)
        },
        save (date) {
            this.item.birth_date = date;
            this.$refs.menu_birth_date.save(date)
        },
        GuardarDatosPersonales(data){
            axios.post('/api/actualizar_empleado',data)
                .then(response=>{
                    iziToast.success({
                                position: 'topRight',
                                title: 'Datos del empleado',
                                message: 'Se actualizado los datos del empleado'
                    });
                     this.$emit('employee',response.data.employee)
                });
        }
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
