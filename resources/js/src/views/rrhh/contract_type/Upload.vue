<template>
<v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
            <v-card-title class="headline" >
                <span >{{ title }}</span>
            </v-card-title>

            <v-card-text v-if="item">

                <v-layout wrap>
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

            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="secondary darken-1" flat @click="sendClose()">Cerrar</v-btn>
                <!-- <v-btn color="blue darken-1" flat @click="sendRequest()">Guardar/Modificar</v-btn> -->

                <v-btn color="blue darken-1" flat @click="sendRequest()" v-if="this.employee_request['url_contrato'] != '' && this.employee_request['url_contrato'] != null">Modificar</v-btn>
                <v-btn color="blue darken-1" flat @click="sendRequest()" v-if="this.employee_request['url_contrato'] == '' || this.employee_request['url_contrato'] == null">Guardar</v-btn>
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
            // alert(this.item.image_path)
            this.$emit('update_image',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        pickImage(){
             this.$refs.image.click ()
        },
        previewImage(event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = (e) => {
                    this.item.image_path ='public/'+ e.target.result;
                    this.item.image_file =input.files[0];
                }
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
            let title='Subir Plantilla de Contrato'
            return title
        },
	}
}
</script>
