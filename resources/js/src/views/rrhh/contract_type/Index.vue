<template>
    <v-card>
        <v-card-title>
            <h3>Tipos de Contrato</h3>
        <v-spacer></v-spacer>
        <v-btn @click="new_modalidad_contrato();" color="success" dark class="mb-2">Modalidad Contrato</v-btn>
        <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn>
        </v-card-title>
        <v-card-text>
             <vue-bootstrap4-table :rows="contract_types" :columns="columns" :config="config" >
                <template slot="sort-asc-icon">
                    <i class="fa fa-sort-asc"></i>
                </template>
                <template slot="sort-desc-icon">
                    <i class="fa fa-sort-desc"></i>
                </template>
                <template slot="no-sort-icon">
                    <i class="fa fa-sort"></i>
                </template>
                <template slot="pagination-info" slot-scope="props">
                    De {{props.currentPageRowsLength}}
                    a {{props.filteredRowsLength}}
                    ({{props.originalRowsLength}} Total Registros)
                </template>
                <template slot="option" slot-scope="props">
                    <v-icon @click="edit(props.row)" >
                        edit
                    </v-icon>
                    <v-icon @click="destroy(props.row)" >
                        delete
                    </v-icon>
                    <v-icon @click="show_upload(props.row)" v-if="props.row.url_contrato==null">
                        file_upload
                    </v-icon>
                    <v-icon color='blue' @click="show_upload(props.row)" v-else> 
                        description
                    </v-icon>
                </template>
            </vue-bootstrap4-table>
        </v-card-text>
        <upload-image :dialog="dialog_upload" :employee_request="employee_request" @close="close_upload" @update_image="update_image"></upload-image>
        <edit-contract :dialog="dialog" :contract_type="contract_type" @close="close"  @contract_type="update"></edit-contract>

    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditContract from './Edit.vue';
import UploadImage from './Upload.vue';
export default {
    data:()=>({
        contract_types:[],
        contract_type:{},
        employee_request:{},
        dialog:false,
        dialog_upload:false,
        columns: [
            {
                label: "Codigo",
                name: "id",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Codigo"
                },
                sort: true,
            },
            {
                label: "Tipo de Contrato",
                name: "name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre de Tipo de Contrato"
                },
                sort: true,
            },
            {
                label: "Modalidad de Contrato",
                name: "contract_modality.name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre de Modalidad de Contrato"
                },
                sort: true,
            },

            {
                label: "Opciones",
                name: "option",
                sort: false,
            }],

        config: {
            checkbox_rows: false,
            rows_selectable: false,
            pagination: true,
            card_mode: false,
            show_refresh_button:  false,
            show_reset_button:  false,
            global_search:  {
                placeholder:  "Enter custom Search text",
                visibility:  false,
                case_sensitive:  false
            },
            per_page_options:  [5,  10,  20,  30],
            server_mode:  false,
        },


    }),
    mounted(){
        this.search();
    },
    computed:{
        getUrl()
        {
            let url=''
            if(this.employee_request.id)
            {
                url= `${this.employee_request.image_path?this.employee_request.image_path.toString().substring(7,(this.employee_request.image_path).length ):''}`;
            }
            return url;
        }
    },
    methods:{
        search(){
            axios.get('/api/contract_type1')
                 .then((response)=>{
                    this.contract_types = response.data;
                    this.contract_types.forEach( function(valor, indice, array){
                    });
                    
                });
        },
        create() {
            this.contract_type ={};
            this.dialog = true;
        },

        edit (item) {
            axios.get(`/api/contract_type/${item.id}/edit`)
            .then(response => {
                this.contract_type = response.data.contract_type
            })
            .catch(error => {
            });

            this.dialog = true
        },
        update (item) {
            if(item.image_path){
                let formData = new FormData();
                if(item.id){
                    formData.set('id',item.id);
                }
                formData.set('name',item.name || '');
                formData.set('id_modalidad',item.id_modalidad);
                formData.append("image_file", item.image_file || '');
                axios.post('/api/contract_type2', formData,{
                            headers: {
                                    'Content-Type': 'multipart/form-data'
                                    }
                        })
                    .then(response => {
                        this.isLoading = false;
                        if (response.data.success=="true") {
                            iziToast.success({
                                title: 'Exitoso',
                                message: 'Datos del contrato Guardados!',
                            });
                            this.search();
                        }else if(response.data.success=="false"){
                            iziToast.error({
                                title: 'Error',
                                message: 'No se puede guardar los datos llene los campos vacios faltantes!',
                            });
                        }else if(response.data.success=="ci"){
                            iziToast.error({
                                title: 'Error',
                                message: response.data.mensaje,
                            });
                        }
                    })
                    .catch(function (error) {
                        //this.$store.dispatch('template/showMessage',{message:error,color:'danger'});
                        this.isLoading = true;
                        iziToast.error({
                            title: 'Error',
                            message: 'No se pudo registrar, error contactase con el ',
                        });

                    });
                this.dialog =false;            
            }else{
                axios.post('/api/contract_type', item)
                    .then(response => {
                            iziToast.success({
                                title: 'Registro Satisfactorio',
                                message: 'Se registro '+response.data.name,
                            });
                            this.search();
                        })
                        .catch( (error) => {

                            iziToast.error({
                                title: 'Error',
                                message: 'Contactese con el Administrador de la Pagina: '+error,
                            });
                        });
            }
            this.dialog =false;

        },
        destroy (item) {

            axios.delete(`/api/contract_type/${item.id}`)
            .then((response)=>{

                this.search();
                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },

        close() {
            this.dialog = false;
        },
        close_upload() {
            this.dialog_upload = false;
        },
        show_upload(item)
        {
            // alert()
            this.employee_request = item;
            this.dialog_upload = true;
        },
        update_image(item){
            let formData = new FormData();
            formData.set('id',item.id);
            formData.append("image_file", item.image_file || '');
            axios.post('/api/contract_type_plantilla', formData,{
                        headers: {
                                'Content-Type': 'multipart/form-data'
                                }
                    })
                .then(response => {
                    iziToast.success({
                        title: 'OK',
                        message: 'Se Subio el contrato con exito!',
                    });
                    this.search();
                })
                .catch(function (error) {
                    iziToast.error({
                        title: 'Error',
                        message: 'No se pudo registrar la imagen, error contactase con el administrador del sistema',
                    });

                });
            this.dialog_upload = false;
        },
        new_modalidad_contrato(){
            var link = document.createElement("a");
            link.href = `/contract_modality`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
    },
    components: {
        VueBootstrap4Table,
        EditContract,
        UploadImage
    }
}
</script>
