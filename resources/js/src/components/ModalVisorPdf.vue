<template>
  <v-dialog
    v-model="dialogVisible"
    :max-width="maxWidth"
    persistent
    scrollable
    transition="dialog-bottom-transition"
  >
    <v-card class="modal-visor-card" rounded="lg">
      <!-- BARRA SUPERIOR / TOOLBAR -->
      <v-card-title class="py-3 px-4 d-flex justify-space-between align-center bg-toolbar">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-2" size="38" class="mr-3 text-white elevation-1">
            <v-icon color="white" small>mdi-file-pdf-box</v-icon>
          </v-avatar>
          <div>
            <h3 class="text-h6 font-weight-bold mb-0 text-truncate" style="max-width: 500px;">
              {{ titulo || 'Visor de Documento Oficial' }}
            </h3>
            <span class="text-caption grey--text text--darken-1" v-if="subtitulo">
              {{ subtitulo }}
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Selector de formato si aplica (Carta / Rollo) -->
          <v-btn-toggle
            v-if="mostrarSelectorFormato"
            v-model="formatoActual"
            mandatory
            dense
            color="primary"
            class="mr-2"
            @change="cambiarFormato"
          >
            <v-btn small value="rollo">
              <v-icon x-small left>mdi-receipt</v-icon> Rollo 80mm
            </v-btn>
            <v-btn small value="carta">
              <v-icon x-small left>mdi-file-document-outline</v-icon> Carta
            </v-btn>
          </v-btn-toggle>

          <!-- Botón Exportar Excel Directo si aplica (sin abrir pestañas) -->
          <v-btn
            v-if="urlExcel"
            color="green darken-2"
            outlined
            class="rounded-pill mr-2 font-weight-bold"
            small
            :loading="descargandoExcel"
            @click="descargarExcelDirecto"
          >
            <v-icon left small>mdi-file-excel</v-icon> Excel
          </v-btn>

          <!-- Botón Descargar PDF Directo -->
          <v-btn
            v-if="blobUrl"
            :href="blobUrl"
            :download="nombreDescarga"
            color="grey darken-3"
            outlined
            class="rounded-pill mr-2 font-weight-bold"
            small
          >
            <v-icon left small>mdi-download</v-icon> Descargar
          </v-btn>

          <!-- Botón Imprimir Directo -->
          <v-btn
            color="primary"
            class="rounded-pill mr-2 font-weight-bold elevation-1"
            small
            @click="imprimirDocumento"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir
          </v-btn>

          <!-- Botón Cerrar -->
          <v-btn icon color="grey darken-2" @click="cerrar">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <!-- CONTENEDOR DEL VISOR PDF -->
      <v-card-text class="pa-0 modal-visor-body">
        <!-- Indicador de carga -->
        <div v-if="cargando" class="visor-loading-overlay">
          <v-progress-circular indeterminate color="teal" size="52" width="5"></v-progress-circular>
          <div class="mt-3 text-subtitle-2 font-weight-bold grey--text text--darken-3">
            Cargando documento PDF de forma segura...
          </div>
          <div class="text-caption grey--text">Validando credenciales y generando archivo</div>
        </div>

        <!-- Estado de Error -->
        <div v-else-if="errorCarga" class="d-flex flex-column align-center justify-center fill-height pa-8 text-center">
          <v-icon size="64" color="red lighten-2" class="mb-3">mdi-alert-circle-outline</v-icon>
          <h4 class="text-h6 font-weight-bold white--text mb-1">No se pudo cargar el documento</h4>
          <p class="text-body-2 grey--text text--lighten-2 mb-4" style="max-width: 500px;">
            {{ mensajeError }}
          </p>
          <v-btn color="teal" dark class="rounded-pill font-weight-bold elevation-2" @click="cargarPdf">
            <v-icon left small>mdi-refresh</v-icon> Reintentar Carga
          </v-btn>
        </div>

        <!-- Visor Embebido Iframe con Blob URL Autenticado -->
        <iframe
          v-else-if="blobUrl"
          ref="pdfIframe"
          :src="pdfSrc"
          class="visor-iframe"
          frameborder="0"
        ></iframe>

        <div v-else class="text-center pa-10 grey--text fill-height d-flex flex-column align-center justify-center">
          <v-icon size="48" color="grey lighten-1">mdi-file-question-outline</v-icon>
          <div class="text-subtitle-1 mt-2">No se especificó la URL del documento.</div>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <!-- BARRA INFERIOR / ACCIONES -->
      <v-card-actions class="py-2 px-4 grey lighten-4 justify-space-between">
        <div class="text-caption grey--text d-flex align-center">
          <v-icon x-small color="teal" class="mr-1">mdi-shield-check</v-icon>
          <span>Documento verificado por el Sistema Integrado EMAPAP Patacamaya</span>
        </div>

        <v-btn text color="secondary" class="rounded-pill font-weight-bold" @click="cerrar">
          Cerrar Visor
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModalVisorPdf',
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    url: {
      type: String,
      default: '',
    },
    titulo: {
      type: String,
      default: 'Visor de Documento Oficial',
    },
    subtitulo: {
      type: String,
      default: '',
    },
    mostrarSelectorFormato: {
      type: Boolean,
      default: false,
    },
    formatoInicial: {
      type: String,
      default: 'rollo',
    },
    maxWidth: {
      type: String,
      default: '1050px',
    },
    nombreDescarga: {
      type: String,
      default: 'documento_oficial.pdf',
    },
    urlExcel: {
      type: String,
      default: '',
    },
    nombreExcel: {
      type: String,
      default: 'reporte.csv',
    },
  },
  data() {
    return {
      cargando: false,
      errorCarga: false,
      mensajeError: '',
      blobUrl: null,
      formatoActual: this.formatoInicial,
      descargandoExcel: false,
    };
  },
  computed: {
    dialogVisible: {
      get() {
        return this.value;
      },
      set(val) {
        this.$emit('input', val);
      },
    },
    urlCompleta() {
      if (!this.url) return '';
      let target = this.url;
      if (this.mostrarSelectorFormato) {
        const separator = target.includes('?') ? '&' : '?';
        if (target.includes('formato=')) {
          target = target.replace(/formato=[a-zA-Z0-9]+/, `formato=${this.formatoActual}`);
        } else {
          target = `${target}${separator}formato=${this.formatoActual}`;
        }
      }
      return target;
    },
    urlConToken() {
      if (!this.urlCompleta) return '';
      const token = localStorage.getItem('token');
      if (!token) return this.urlCompleta;
      const cleanToken = token.replace(/^Bearer\s+/i, '');
      const separator = this.urlCompleta.includes('?') ? '&' : '?';
      return `${this.urlCompleta}${separator}token=${encodeURIComponent(cleanToken)}`;
    },
    pdfSrc() {
      if (!this.blobUrl) return null;
      return `${this.blobUrl}#toolbar=1&navpanes=0&view=FitH`;
    },
  },
  watch: {
    value(val) {
      if (val) {
        this.$nextTick(() => {
          this.cargarPdf();
        });
      } else {
        this.limpiarBlob();
      }
    },
    urlCompleta() {
      if (this.value) {
        this.$nextTick(() => {
          this.cargarPdf();
        });
      }
    },
    formatoInicial(nuevo) {
      if (nuevo) {
        this.formatoActual = nuevo;
      }
    },
  },
  mounted() {
    if (this.value && this.urlCompleta) {
      this.cargarPdf();
    }
  },
  beforeDestroy() {
    this.limpiarBlob();
  },
  methods: {
    limpiarBlob() {
      if (this.blobUrl) {
        URL.revokeObjectURL(this.blobUrl);
        this.blobUrl = null;
      }
      this.cargando = false;
      this.errorCarga = false;
      this.mensajeError = '';
    },
    async cargarPdf() {
      if (!this.urlCompleta) return;
      this.cargando = true;
      this.errorCarga = false;
      this.mensajeError = '';

      if (this.blobUrl) {
        URL.revokeObjectURL(this.blobUrl);
        this.blobUrl = null;
      }

      try {
        const token = localStorage.getItem('token');
        const headers = {};
        if (token) {
          headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
        }

        const res = await window.axios.get(this.urlConToken, {
          responseType: 'blob',
          headers,
        });

        const blob = new Blob([res.data], { type: 'application/pdf' });
        this.blobUrl = URL.createObjectURL(blob);
        this.cargando = false;
      } catch (err) {
        console.error('Error al cargar PDF en modal visor:', err);
        this.cargando = false;
        this.errorCarga = true;
        if (err.response && err.response.status === 401) {
          this.mensajeError = 'Sesión no autorizada o expirada (Error 401). Por favor recargue el sistema o vuelva a iniciar sesión.';
        } else if (err.response && err.response.status === 404) {
          this.mensajeError = 'El documento solicitado no fue encontrado en el servidor (Error 404).';
        } else {
          this.mensajeError = 'Ocurrió un inconveniente al generar la vista previa del documento. Verifique que los datos del registro sean válidos.';
        }
      }
    },
    cambiarFormato(nuevoFormato) {
      this.formatoActual = nuevoFormato;
      this.$emit('cambio-formato', nuevoFormato);
      this.cargarPdf();
    },
    imprimirDocumento() {
      const iframe = this.$refs.pdfIframe;
      if (iframe && iframe.contentWindow) {
        try {
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
          return;
        } catch (e) {
          console.warn('Impresión directa por iframe restringida, abriendo diálogo alterno:', e);
        }
      }
      if (this.blobUrl) {
        const printWindow = window.open(this.blobUrl, '_blank');
        if (printWindow) {
          printWindow.focus();
          printWindow.print();
        }
      }
    },
    async descargarExcelDirecto() {
      if (!this.urlExcel) return;
      this.descargandoExcel = true;
      try {
        const token = localStorage.getItem('token');
        const headers = {};
        if (token) {
          headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
        }
        const client = window.axios || axios;
        const res = await client.get(this.urlExcel, {
          responseType: 'blob',
          headers,
        });

        const blob = new Blob([res.data], {
          type: res.headers['content-type'] || 'text/csv;charset=utf-8;',
        });
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.style.display = 'none';
        link.href = blobUrl;
        link.setAttribute('download', this.nombreExcel || 'reporte.csv');
        document.body.appendChild(link);
        link.click();
        setTimeout(() => {
          document.body.removeChild(link);
          URL.revokeObjectURL(blobUrl);
        }, 200);
      } catch (err) {
        console.error('Error al exportar Excel directo:', err);
      } finally {
        this.descargandoExcel = false;
      }
    },
    cerrar() {
      this.limpiarBlob();
      this.$emit('input', false);
      this.$emit('cerrar');
    },
  },
};
</script>

<style scoped>
.modal-visor-card {
  overflow: hidden;
}
.bg-toolbar {
  background: #f8fafc;
}
.modal-visor-body {
  position: relative;
  height: 75vh;
  min-height: 600px;
  max-height: 860px;
  background: #525659;
}
.visor-iframe {
  width: 100%;
  height: 100%;
  min-height: 600px;
  display: block;
}
.visor-loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 10;
  text-align: center;
}
.gap-2 {
  gap: 8px;
}
</style>
