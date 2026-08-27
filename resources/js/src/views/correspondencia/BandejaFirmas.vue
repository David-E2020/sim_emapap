<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="green darken-2" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-draw-pen</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Firmas y Aprobaciones Electrónicas</h2>
            <span class="text-caption text-secondary">Bandeja de documentos oficiales pendientes de rúbrica digital y sello hash</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn icon color="secondary" @click="cargarPendientes"><v-icon>mdi-refresh</v-icon></v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTA DE DOCUMENTOS PENDIENTES -->
    <v-row v-if="pendientes.length">
      <v-col cols="12" md="6" lg="4" v-for="doc in pendientes" :key="doc.id">
        <v-card rounded="lg" class="erp-card-elevated h-100 d-flex flex-column justify-space-between">
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
                {{ doc.cite }}
              </v-chip>
              <v-chip x-small label color="orange lighten-4" text-color="orange darken-4" class="font-weight-bold">
                PENDIENTE FIRMA
              </v-chip>
            </div>

            <h3 class="text-subtitle-1 font-weight-bold mb-1 text-truncate-2" style="color: #0f172a; line-height: 1.3;">
              {{ doc.asunto }}
            </h3>

            <div class="text-caption text-secondary mb-3">
              <div><strong>Tipo:</strong> {{ doc.tipo_documento }}</div>
              <div><strong>Elaborado por:</strong> {{ doc.creador ? doc.creador.nombre_completo : 'N/A' }}</div>
              <div><strong>Unidad:</strong> {{ doc.unidad_generadora ? doc.unidad_generadora.nombre : 'EMAPA' }}</div>
              <div><strong>Fecha:</strong> {{ formatFecha(doc._fecha_creacion) }}</div>
            </div>

            <v-divider class="mb-3"></v-divider>
            <div class="text-caption font-weight-medium text-secondary mb-1">Participantes y Firmantes:</div>
            <div class="d-flex flex-wrap gap-1">
              <v-chip x-small v-for="p in doc.participantes" :key="p.id" label outlined color="primary">
                {{ p.tipo_participacion }}: {{ p.persona ? p.persona.nombre_completo : '' }}
              </v-chip>
            </div>
          </v-card-text>

          <v-card-actions class="pa-4 bg-grey-lighten-5 border-t">
            <v-btn small text color="indigo" class="rounded-pill text-capitalize" @click="previsualizarDoc(doc.id)">
              <v-icon left small>mdi-eye</v-icon> Leer
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn small outlined color="red" class="rounded-pill text-capitalize mr-2" @click="abrirModalObservar(doc)">
              <v-icon left small>mdi-close-circle</v-icon> Observar
            </v-btn>
            <v-btn small color="success" class="rounded-pill text-capitalize white--text" @click="abrirModalFirmar(doc)">
              <v-icon left small>mdi-draw-pen</v-icon> Firmar
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- BANDEJA VACÍA -->
    <v-card v-else-if="!cargando" rounded="lg" class="erp-card-elevated pa-10 text-center">
      <v-avatar size="80" color="green lighten-5" class="mb-4">
        <v-icon size="42" color="success">mdi-check-decagram-outline</v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold mb-1">¡Al día! No tienes firmas pendientes</h3>
      <p class="text-caption text-secondary">Todos los documentos oficiales asignados a tu persona han sido rubricados y aprobados.</p>
    </v-card>

    <div v-if="cargando" class="text-center pa-10">
      <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
    </div>

    <!-- MODAL FIRMAR CON PIN -->
    <v-dialog v-model="dialogFirmar" max-width="450px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 green darken-2 white--text py-3">
          <v-icon left color="white">mdi-shield-lock-outline</v-icon> Sello Electrónico de Conformidad
        </v-card-title>
        <v-card-text class="pt-4 text-center">
          <p class="text-caption text-secondary mb-3">
            Está por rubricar oficialmente el documento <strong>{{ docSeleccionado.cite }}</strong>. Se registrará su estampa de tiempo y hash criptográfico SHA-256.
          </p>

          <v-text-field
            v-model="pinFirma"
            label="PIN Electrónico de Aprobación"
            type="password"
            outlined
            dense
            prepend-inner-icon="mdi-key-variant"
            placeholder="Ingrese su PIN de 4 dígitos"
            class="mt-2"
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogFirmar = false">Cancelar</v-btn>
          <v-btn color="success" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="confirmarFirma">
            Rubricar y Firmar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL OBSERVAR DOCUMENTO -->
    <v-dialog v-model="dialogObservar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 red darken-2 white--text py-3">
          <v-icon left color="white">mdi-alert-circle</v-icon> Observar Documento
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            El documento será devuelto al redactor en estado <strong>BORRADOR</strong> con sus observaciones para corrección.
          </p>
          <v-textarea
            v-model="motivoObservacion"
            label="Detalle de la Observación *"
            rows="3"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogObservar = false">Cancelar</v-btn>
          <v-btn color="red" class="rounded-pill text-capitalize white--text" :loading="guardando" @click="confirmarObservacion">
            Devolver con Observación
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
  name: 'BandejaFirmas',
  data() {
    return {
      pendientes: [],
      cargando: false,
      guardando: false,

      dialogFirmar: false,
      dialogObservar: false,
      docSeleccionado: null,
      pinFirma: '1234',
      motivoObservacion: '',

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarPendientes();
  },
  methods: {
    async cargarPendientes() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/correspondencia/firmas/pendientes');
        if (res.data && res.data.success) {
          this.pendientes = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar firmas pendientes.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    abrirModalFirmar(doc) {
      this.docSeleccionado = doc;
      this.pinFirma = '1234';
      this.dialogFirmar = true;
    },
    async confirmarFirma() {
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/firmas/firmar', {
          id_documento: this.docSeleccionado.id,
          pin: this.pinFirma,
          tipo_firma: 'PIN_ELECTRONICO',
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento rubricado y firmado exitosamente.', 'success');
          this.dialogFirmar = false;
          this.cargarPendientes();
        }
      } catch (e) {
        this.mostrarMensaje('Error al firmar documento.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    abrirModalObservar(doc) {
      this.docSeleccionado = doc;
      this.motivoObservacion = '';
      this.dialogObservar = true;
    },
    async confirmarObservacion() {
      if (!this.motivoObservacion) {
        this.mostrarMensaje('Escribe el motivo de la observación.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/firmas/rechazar', {
          id_documento: this.docSeleccionado.id,
          motivo: this.motivoObservacion,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento devuelto con observaciones.', 'success');
          this.dialogObservar = false;
          this.cargarPendientes();
        }
      } catch (e) {
        this.mostrarMensaje('Error al observar documento.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    previsualizarDoc(id) {
      window.open(`/api/correspondencia/documentos/${id}/preview`, '_blank');
    },
    formatFecha(f) {
      if (!f) return '-';
      const d = new Date(f);
      return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
