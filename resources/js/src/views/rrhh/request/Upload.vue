<template>
<v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
            <v-card-title class="headline" >
                <span >{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">

                <v-layout wrap>
                <v-flex xs12 sm12 md12>

                        <v-img
                            :src="item.image_path?item.image_path.toString().substring(7,item.image_path.length):''"
                            :lazy-src="item.image_path?item.image_path.toString().substring(7,item.image_path.length):''"
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
                </v-flex>

                </v-layout>

            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="secondary darken-1" flat @click="sendClose()">Cerrar</v-btn>
                <v-btn color="blue darken-1" flat @click="sendRequest()">Subir Imagen</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        employee_request: Object
	},
    data:()=>({
        menu: false,
        time: null,
        menu2: false,
        time2: null,
        menu3:false,
        menu4:false,
    }),
    mounted(){

    },
    methods:{
        sendRequest() {
            this.$emit('update_image',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        pickImage(){
             this.$refs.image.click ()
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
                    this.item.image_path ='public/'+ e.target.result;
                    this.item.image_file =input.files[0];
                    // this.item.imageData = this.imageData;
                }
                // Start the reader job - read file as a data url (base64 format)
                reader.readAsDataURL(input.files[0]);
            }
        },

    },
    computed:{
        item(){
           let item = this.employee_request
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Subir Boleta Escaneada '
            // if(this.item.employee) {

            //     title = this.item.employee.first_name +' '+this.item.employee.second_name+' '+this.item.employee.last_name+ ' '+ this.item.employee.mother_last_name;
            // }
            return title
        },
	}
}
</script>
