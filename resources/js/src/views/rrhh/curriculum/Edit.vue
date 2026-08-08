<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
        <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                    {{item.employee}}
                 <v-layout wrap class="center">  
                     <center>
                    <v-flex xs12 sm12 md12 v-if="employee.img_profile">
                          <img  v-bind:src="'/storage/employee_images/' + employee.employee_image_path" style="height: 15vw; width: 15vw; border: 2px solid #fff;border-radius: 50%;box-shadow: 0 0 5px gray;display: inline-block;margin-left: auto;margin-right: auto;" class="center"><br>                            
                    </v-flex>
                    <v-flex xs12 sm12 md12 v-else>
                          <img src="/img/sinfoto.png" style="height: 15vw; width: 15vw; border: 2px solid #fff;border-radius: 50%;box-shadow: 0 0 5px gray;display: inline-block;margin-left: auto;margin-right: auto;" class="center"><br>                                
                    </v-flex>   
                    </center>
                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Salir</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
import { ModelSelect } from 'vue-search-select';

export default
{
    props:{
        dialog: Boolean,
        employee: Object
	},
    data:()=>({
        valid: true,
        lazy: false,
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
        civil_statuses:[{id:'C',name:'Casado(a)'},{id:'S',name:'Soltero(a)'},{id:'V',name:'Viudo(a)'},{id:'D',name:'Divorciado(a)'}],
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
        locations:[],
        rules: {
          required: value => !!value || 'Required.',
        },
    }),
    mounted(){
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
                var obj=[];
                //this.positions = response.data;
                for (var i = 0; i < response.data.length; i++) {
                    obj.push({value:response.data[i].id,
                    text:response.data[i].name});
                }
                this.positions=obj;
            })
            .catch(error => {
            });
        },
        getLocations (){
            axios.get('/api/location')
            .then(response => {
                this.locations = response.data.locations;
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
        sendEmployee() {
            // this.$emit('employee',this.item)
        },
        sendClose() {
            this.$emit('close',true)
        },
        save (date) {
            this.item.birth_date = date;
            this.$refs.menu_birth_date.save(date)
        },
        validate () {
            /*var data={
                'employee_id':this.select.value,
                'days_work_month':this.days_work_month,
                'sunday_holiday':this.sunday_holiday,
                'low_license':this.low_license,
                'faults':this.faults,
                'holidays':this.holidays,
                'commissions':this.commissions,
                'ross_amount':this.ross_amount,
                'invoices_110':this.invoices_110,
                'hold_time':this.hold_time,
                'total_net_snack':this.total_net_snack,
                'days_subject_to_payment':this.days_subject_to_payment,
                'product_for_consumption':this.product_for_consumption,
                'commission_for_deposit':this.commission_for_deposit,
                'total_snack_to_deposit':this.total_snack_to_deposit,
                'date':this.fecha,
            };*/
            if (this.$refs.form.validate()) {
                this.isLoading = true;
                /*axios.post('/api/refreshment',data)
                .then((response)=>{
                    this.isLoading = false;
                    this.listEmploye();
                    this.getData();
                    iziToast.success({
                        position: 'topRight',
                        title: 'Asignacion del refrigerio se realizo correctamente',
                        message: 'Ya se asigno el refrigerio correctamente',
                        theme: 'light', // dark
                        color: 'green', // blue, red, green, yellow
                    });
                }).catch((error)=> {
                    this.isLoading = false;
                    iziToast.danger({
                        position: 'topRight',
                        title: 'Debe elegir un usuario',
                        message: 'error debe elegir un funcionario',
                        theme: 'light', // dark
                        color: 'red', // blue, red, green, yellow
                    });
                });*/
            }
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

                title = 'Editar Empleado'
            }
            return title
        },
    },
    watch: {
      menu_birth_date (val) {
        val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
      },
    },
     components: {
        ModelSelect,
    }

}
</script>
