<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="pink darken-1" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-tag-multiple-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Etiquetas y Carpetas Personales</h2>
            <span class="text-caption text-secondary">Organización virtual y clasificación de expedientes por colores y categorías personalizadas</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="pink darken-1" class="text-capitalize font-weight-medium rounded-pill elevation-2 white--text" @click="abrirModalNuevaEtiqueta">
            <v-icon left small>mdi-plus-circle</v-icon> + Nueva Etiqueta
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE ETIQUETAS -->
    <v-row>
      <v-col cols="12" sm="6" md="4" lg="3" v-for="et in etiquetas" :key="et.id">
        <v-card rounded="lg" class="erp-card-elevated pa-4" :style="'border-left: 6px solid ' + et.color + ';'">
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <v-avatar size="24" :color="et.color" class="mr-2"></v-avatar>
              <h3 class="text-subtitle-1 font-weight-bold mb-0">{{ et.nombre }}</h3>
            </div>
            <v-chip x-small label :color="et.color" class="white--text font-weight-bold">
              ACTIVA
            </v-chip>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- MODAL NUEVA ETIQUETA -->
    <v-dialog v-model="dialogNueva" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 pink darken-1 white--text py-3">
          <v-icon left color="white">mdi-tag-plus</v-icon> Crear Etiqueta Personal
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field
            v-model="formEtiqueta.nombre"
            label="Nombre de la Etiqueta *"
            dense
            outlined
            class="mb-3"
            placeholder="Ej: PRIORITARIO, COMPRAS, SILOS"
          ></v-text-field>

          <span class="text-caption font-weight-bold text-secondary mb-2 d-block">Seleccionar Color Identificador:</span>
          <div class="d-flex gap-2 flex-wrap mb-2">
            <v-avatar
              v-for="c in coloresDisponibles"
              :key="c"
              :color="c"
              size="32"
              class="cursor-pointer"
              :style="formEtiqueta.color === c ? 'border: 3px solid #000; transform: scale(1.1);' : ''"
              @click="formEtiqueta.color = c"
            >
              <v-icon v-if="formEtiqueta.color === c" x-small color="white">mdi-check</v-icon>
            </v-avatar>
          </div>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogNueva = false">Cancelar</v-btn>
          <v-btn color="pink darken-1" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="guardarEtiqueta">
            Guardar Etiqueta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" top right :timeout="3500">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'GestionEtiquetas',
  data() {
    return {
      etiquetas: [],
      cargando: false,
      guardando: false,

      coloresDisponibles: ['#1976D2', '#388E3C', '#D32F2F', '#F57C00', '#7B1FA2', '#0097A7', '#5D4037', '#E91E63', '#455A64'],

      dialogNueva: false,
      formEtiqueta: {
        nombre: '',
        color: '#1976D2',
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarEtiquetas();
  },
  methods: {
    async cargarEtiquetas() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/correspondencia/etiquetas');
        if (res.data && res.data.success) {
          this.etiquetas = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar etiquetas.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevaEtiqueta() {
      this.formEtiqueta = {
        nombre: '',
        color: '#1976D2',
      };
      this.dialogNueva = true;
    },
    async guardarEtiqueta() {
      if (!this.formEtiqueta.nombre) {
        this.mostrarMensaje('Ingrese el nombre de la etiqueta.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/etiquetas', this.formEtiqueta);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Etiqueta creada con éxito.', 'success');
          this.dialogNueva = false;
          this.cargarEtiquetas();
        }
      } catch (e) {
        this.mostrarMensaje('Error al guardar etiqueta.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    mostrarMensaje(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.cursor-pointer {
  cursor: pointer;
}
</style>
