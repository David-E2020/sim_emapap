<template>
<v-dialog v-model="dialog" max-width="700px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>
                 <v-layout wrap>
                    <v-flex xs6 sm6 md12>
                        <v-text-field label="Nombre" hint="Ingrese Nombre" required v-model="item.name"></v-text-field>
                    </v-flex>
                     <v-flex xs12 sm12 md12>
                        <v-select
                        label="Modalidad de Contrato"
                        v-model="item.id_modalidad"
                        :items="managements"
                        item-text="name"
                        item-value="id"
                        hint=""
                        persistent-hint>
                        </v-select>
                    </v-flex>
                    <v-flex xs12 sm12 md12>
                        <v-btn @click="pickImage" >
                            <v-icon>file_upload</v-icon>
                            Cargar Plantilla
                        </v-btn>
                        <input
                            type="file"
                            style="display: none"
                            ref="image"
                            accept=".docx"
                            @change="previewImage"
                        >
                    </v-flex> 
                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>
                <v-btn color="blue darken-1" flat @click="sendContract()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        contract_type: Object
	},
    data:()=>({

    }),
    mounted(){
        axios.get('/api/auth/contract_modality')
             .then((response)=>{
                // this.employees = response.data;
                this.managements = response.data;
            });
    },
    methods:{
        sendContract() {
            // alert(this.item.name)
            // alert(this.item.managament_id)
            this.$emit('contract_type',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        pickImage(){
             this.$refs.image.click ()
        },
        previewImage(event) {
            // alert(1)
            // Reference to the DOM input element
            var input = event.target;
            // alert(2)
            // Ensure that you have a file before attempting to read it
            if (input.files && input.files[0]) {
            // alert(3)
                // create a new FileReader to read this image and convert to base64 format
                var reader = new FileReader();
            // alert(4)
                // Define a callback function to run, when FileReader finishes its job
                reader.onload = (e) => {
            // alert(5)
                    // Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
                    // Read image as base64 and set to imageData
                    this.item.image_path ='public/'+ e.target.result;
                    this.item.image_file =input.files[0];
                    // this.item.imageData = this.imageData;
                    // alert(this.item.image_path )
                    // alert(this.item.image_file )
                }
            // alert(6)
                // Start the reader job - read file as a data url (base64 format)
                reader.readAsDataURL(input.files[0]);
            }
        },
    },
    computed:{
        item(){
           let item = this.contract_type
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Crear Tipo de Contrato'
            if(this.item.id) {

                title = 'Editar Tipo de Contrato'
            }
            return title
        },
	}
}
</script>
