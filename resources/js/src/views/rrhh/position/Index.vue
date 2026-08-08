<template>
    <v-card>
        <v-card-title>
            <h3>Cargos</h3>
        <v-spacer></v-spacer>
        <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn>
        </v-card-title>
        <v-card-text>
            <vue-bootstrap4-table :rows="positions" :columns="columns" :config="config" >
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
                <!-- <template slot="active" slot-scope="props">
                <div class="text-xs-center">
                    <v-chip :color="props.row.active?'success':'danger'" :text-color="props.row.active?'white':'danger'" small>{{props.row.active?'Activo':'Inactivo'}}</v-chip>
                    </div>
                </template> -->
                <template slot="option" slot-scope="props">
                    <!-- <v-icon  small>
                        remove_red_eye
                    </v-icon> -->
                    <!--v-icon @click="edit(props.row)" >
                        edit
                    </v-icon-->
                    <!--v-icon @click="destroy(props.row)" >
                        delete
                    </v-icon-->
                </template>
            </vue-bootstrap4-table>
        </v-card-text>
        <edit-position :dialog="dialog" :position="position" @close="close"  @position="update"></edit-position>

    </v-card>
</template>
<script>
import VueBootstrap4Table from 'vue-bootstrap4-table';
import EditPosition from './Edit.vue';
export default {
    data:()=>({
        positions:[],
        position:{},
        dialog:false,
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
                label: "Nombre",
                name: "name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "Escala Salarial",
                name: "salary_scale.name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "Tipo Dependencia",
                name: "type_dependency",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
                },
                sort: true,
            },
            {
                label: "Unidad",
                name: "unity.name",
                filter: {
                    type: "simple",
                    placeholder: "Ingrese Nombre"
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
    methods:{
        search(){
            axios.get('/api/position')
                .then((response)=>{
                    // this.employees = response.data;
                    this.positions = response.data;
                });
        },
        create() {
            this.position ={};
            this.dialog = true;
        },

        edit (item) {

            axios.get(`/api/position/${item.id}/edit`)
            .then(response => {
                this.position = response.data
            })
            .catch(error => {
            });

            this.dialog = true
        },
        update (item) {
            axios.post('/api/position', item)
                .then(response => {
                        iziToast.success({
                            title: '',
                            message: 'Registro Satisfactorio',
                        });
                        this.search();
                    })
                    .catch(function (error) {

                        iziToast.error({
                            title: 'Error',
                            message: 'Contactese con el Administrador de la Pagina: '+error,
                        });
                    });
            this.dialog =false;

        },
        destroy (item) {

            axios.delete(`/api/position/${item.id}`)
            .then((response)=>{

                this.search();
                iziToast.success({
                    title: 'Mensaje:',
                    message: response.data.name,
                });
            })
            .catch(function (error) {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },

        close() {
            this.dialog = false;
        }
    },
    components: {
        VueBootstrap4Table,
        EditPosition
    }
}
</script>
