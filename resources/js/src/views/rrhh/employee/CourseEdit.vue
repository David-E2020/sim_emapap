<template>
    <v-dialog v-model="dialog" persistent max-width="800px">
            <v-card>
            <v-card-title>
                <span class="headline">{{ title }} </span>
            </v-card-title>

            <v-card-text v-if="item">
                <v-container grid-list-md>

                 <v-layout wrap>


                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Insitución" hint="Ingrese Institución" v-model="item.institution"></v-text-field>
                    </v-flex>
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Nombre" hint="Ingrese Nombre" v-model="item.name"></v-text-field>
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
                                v-model="item.date"
                                label="Gestión"
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
                    <v-flex xs6 sm6 md3>
                        <v-text-field label="Duración (Horas) " hint="Ingrese Duración" v-model="item.hours"></v-text-field>
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

                <v-btn color="blue darken-1" flat @click="sendCourse()">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
</template>
<script>

export default
{
    props:{
        dialog: Boolean,
        course: Object
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
        sendCourse() {
            this.gallery=[];
            this.$emit('course',this.item)
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
            const files = e.target.files;
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
           let item = this.course
           return item
        },
        parent_dialog(){
			return this.dialog
        },
        title(){
            let title='Adicionar Curso'
            if(this.item.id) {

                title = 'Editar Curso'
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
    },

}
</script>
<style lang="scss">
$primColor: #008484;
$secTextColor: #6f6f6f;

.fs-file-selector {
  margin-top: 1rem;
  user-select: none;
  position: sticky !important;
  top: -2px;
  text-align: center;
  background-color: rgba($primColor, 0.05);
  backdrop-filter: blur(35px) saturate(200%);
  border-top: 2px solid $primColor;
  border-bottom: 2px solid $primColor;
  transition: all ease 300ms;
  .fs-droppable {
    padding: 2rem 0;
    transition: all ease 200ms;
  }
  .fs-btn-select {
    background-color: $primColor;
    padding: 0.75rem 2rem;
    color: #fff;
    border-radius: 1px;
    transition: all ease 200ms;
    &:hover {
      cursor: pointer;
      background-color: lighten($primColor, 5);
    }
    &:active {
      background-color: darken($primColor, 5);
      transform: scale(0.95);
      transition: all ease 60ms;
    }
  }
  .fs-loader {
    background-color: transparent !important;
  }
  &.fs-drag-enter {
    background-color: rgba($primColor, 0.1);
    .fs-droppable {
      transition: all ease 100ms;
      transform: scale(0.98);
    }
  }
}
.btn-back {
  display: inline-block;
  padding: 1rem 0;
  position: sticky;
  top: 1rem;
  z-index: 10;
  font-weight: 600;
}
.section-top {
  margin-bottom: 2rem;
  color: $secTextColor;
  font-size: 0.875rem;
}
.section-bottom {
  margin-top: 2rem;
  color: $secTextColor;
  font-size: 0.875rem;
}
.section-loader {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all ease 300ms;
  background-color: rgba(#fff, 0.9);
  backdrop-filter: blur(20px);
}
.gallery {
  margin-top: 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  grid-column-gap: 1rem;
  grid-row-gap: 1rem;
  .gallery-item {
    height: 150px;
    overflow: hidden;
    display: grid;
    grid-template-rows: 1fr min-content;
    align-items: center;
    justify-content: center;
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
    background-color: rgba($primColor, 0.05);
    .img {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      img {
        max-width: 100%;
        max-height: 100%;
      }
    }
    .img-info {
      margin: 1rem 0;
      overflow: hidden;
      text-align: center;
      .img-name {
        white-space: nowrap;
        text-overflow: ellipsis;
        font-size: 0.875rem;
        max-width: 100%;
        overflow: hidden;
        padding: 0 1rem;
      }
      .img-size {
        font-size: 0.75rem;
        color: $secTextColor;
        text-align: center;
        padding: 0 1rem;
      }
    }
  }
}
</style>
