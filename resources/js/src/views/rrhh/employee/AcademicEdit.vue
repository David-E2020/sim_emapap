<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title class="rrhh-primary">
                <span class="headline">{{ title }} </span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>

                <v-layout wrap>

                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Formación Académica" hint="Ingrese Formación Académica" required v-model="item.name"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Documento" hint="Ingrese Documento" required v-model="item.document"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Estado" hint="Ingrese Estado" v-model="item.state"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Insitución" hint="Ingrese Institución" v-model="item.instituion"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md4>
                        <v-text-field label="Grado" hint="Ingrese Grado" v-model="item.grade"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md4>
                        <v-switch v-model="item.has_title" :label="`Cuenta con Título:${item.has_title?'Si':'No'}`"></v-switch>
                    </v-flex>
                    <v-flex xs6 sm6 md4>
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
                                v-model="item.date"
                                label="Fecha de Emisión"
                                prepend-icon="event"
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
                    <v-flex xs6 sm6 md6>
                        <file-selector
                          accept-extensions=".jpg,.png,.svg,.pdf"
                          :multiple="true"
                          :is-loading="isLoading"
                          :max-file-size="1 * 1024 * 1024"
                          @validated="handleFilesValidated"
                          @changed="handleFilesChanged"
                        >
                          Selecione un documento
                          <div slot="top" class="section-top">
                            Maximo tamaño de peso: 1 MB.<br/>
                            Acepta extenciones: JPG, SVG. PDF
                             No acepta otro tipo de Formato
                          </div>
                        </file-selector>
                         <div class="gallery" v-if="gallery.length">
                          <div
                            v-for="(img, index) in gallery"
                            class="gallery-item"
                            :key="index"
                          >
                            <div class="img"><img :src="img.src"></div>
                            <div class="img-info">
                              <div class="img-name" :title="img.name">{{ img.name }}</div>
                              <div class="img-size">{{ formatNumber(img.size) }} bytes</div>
                            </div>
                          </div>
                        </div>
                    </v-flex>

                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="sendClose()">Cancelar</v-btn>

                <v-btn color="blue darken-1" flat @click="sendAcademic()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>
export default
{
    props:{
        dialog: Boolean,
        academic: Object
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
        isLoading: false,
        gallery: [],
    }),
    mounted(){


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
        sendAcademic() {
            this.gallery=[];
            this.$emit('academic',this.item)
        },
        sendClose() {
            this.$emit('close',false)
        },
        save (date) {
            this.item.date = date;
            this.$refs.menu_birth_date.save(date)
        },
        pickFile () {
            this.$refs.curriculum.click ()
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
        handleFilesValidated(result, files) {
          if (result=='EXTENSION_ERROR') {
                iziToast.success({
                            position: 'topRight',
                            title: "NO PUEDE SUBIR CON ESA EXTENDION",
                            message: 'ERROR!',
                            theme: 'question', // dark
                            color: 'red', // blue, red, green, yellow
                        });
          }else if(result=='FILE_SIZE_ERROR'){
             iziToast.success({
                            position: 'topRight',
                            title: "SUPERO EL TAMAÑO DE DOCUMENTO NO PUEDE SUPERAR LOS 1MB",
                            message: 'ERROR!',
                            theme: 'question', // dark
                            color: 'red', // blue, red, green, yellow
                        });

          }else if(result=='MULTIFILES_ERROR')
          {
                    iziToast.success({
                            position: 'topRight',
                            title: "NO PUEDE SUBIR MUCHOS ARCHIVOS",
                            message: 'ERROR!',
                            theme: 'question', // dark
                            color: 'red', // blue, red, green, yellow
                        });
          }
        },
        async handleFilesChanged(files) {
          this.isLoading = true;
          // console.table(files);
          const list = Array.from(files);
          for (const file of list) {
            const img = await this.loadImgAsDataUrl(file);
            this.gallery.push({
              name: file.name,
              size: file.size,
              src: img,
            });
             this.item.curriculum_file=JSON.stringify(this.gallery[0]);
          }
          this.isLoading = false;
        },
        async loadImgAsDataUrl(file) {
          const url = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => resolve(e.target.result);
          });
          return url;
        },
        formatNumber(num) {
          return new Intl.NumberFormat('en', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          }).format(num);
        },
    },
    computed:{
        item(){
            let item = this.academic
            return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Adicionar Formacion Academica'
            if(this.item.id) {

                title = 'Editar Formacion Academica'
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
