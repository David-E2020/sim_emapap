<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-folder-open-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Visor de Expediente Digital 360°</h2>
            <span class="text-caption text-secondary">Consulta integral de documentos, anexos, árbol de derivaciones y permisos de lectura</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0" v-if="hojaRuta">
          <v-btn small outlined color="teal" class="rounded-pill text-capitalize mr-2" @click="imprimirCaratula(hojaRuta.id)">
            <v-icon left small>mdi-printer</v-icon> Carátula Oficial
          </v-btn>
          <v-btn small color="primary" class="rounded-pill text-capitalize white--text" @click="dialogCompartir = true">
            <v-icon left small>mdi-share-variant</v-icon> Compartir Lectura
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- SELECTOR DE EXPEDIENTE SI NO VIENE POR RUTA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated" v-if="!hojaRuta">
      <v-row dense align="center">
        <v-col cols="12" md="8">
          <v-text-field
            v-model="citeBusqueda"
            label="Buscar CITE de Hoja de Ruta para abrir expediente..."
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-magnify"
            @keyup.enter="buscarExpediente"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4" class="d-flex justify-end">
          <v-btn color="primary" class="rounded-pill px-5 text-capitalize" :loading="cargando" @click="buscarExpediente">
            Abrir Expediente
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- DETALLE DEL EXPEDIENTE 360° -->
    <div v-if="hojaRuta">
      <v-card rounded="lg" class="erp-card-elevated mb-5 pa-4">
        <div class="d-flex justify-space-between align-center flex-wrap mb-2">
          <div>
            <span class="text-caption font-weight-bold text-secondary">EXPEDIENTE OFICIAL:</span>
            <h3 class="text-h6 font-weight-black primary--text">{{ hojaRuta.nro_hoja_ruta }}</h3>
          </div>
          <div class="d-flex gap-2">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
              ESTADO: {{ hojaRuta.estado }}
            </v-chip>
            <v-chip label small color="orange lighten-5" text-color="orange darken-4" class="font-weight-bold">
              PRIORIDAD: {{ hojaRuta.prioridad }}
            </v-chip>
          </div>
        </div>

        <div class="pa-3 rounded bg-grey-lighten-4 font-weight-medium text-body-2 mb-3">
          <strong>Asunto:</strong> {{ hojaRuta.asunto }}
        </div>

        <v-row dense class="text-caption text-secondary">
          <v-col cols="12" sm="3">
            <strong>Remitente:</strong> {{ hojaRuta.persona_origen ? hojaRuta.persona_origen.nombre_completo : (hojaRuta.remitente_externo || 'Ventanilla') }}
          </v-col>
          <v-col cols="12" sm="3">
            <strong>Unidad:</strong> {{ hojaRuta.unidad_origen ? hojaRuta.unidad_origen.nombre : 'Externa' }}
          </v-col>
          <v-col cols="12" sm="3">
            <strong>Fojas:</strong> {{ hojaRuta.nro_fojas }} | <strong>Anexos:</strong> {{ hojaRuta.nro_anexos }}
          </v-col>
          <v-col cols="12" sm="3">
            <strong>Fecha Ingreso:</strong> {{ formatFecha(hojaRuta.fecha_solicitud) }}
          </v-col>
        </v-row>
      </v-card>

      <!-- PESTAÑAS 360° -->
      <v-card rounded="lg" class="erp-card-elevated">
        <v-tabs v-model="tabVisor" background-color="transparent" color="primary" grow>
          <v-tab><v-icon left small>mdi-file-document-outline</v-icon> Documentos Generados ({{ hojaRuta.documentos ? hojaRuta.documentos.length : 0 }})</v-tab>
          <v-tab><v-icon left small>mdi-paperclip</v-icon> Archivos Adjuntos ({{ hojaRuta.archivos_adjuntos ? hojaRuta.archivos_adjuntos.length : 0 }})</v-tab>
          <v-tab><v-icon left small>mdi-sitemap</v-icon> Árbol de Derivaciones</v-tab>
        </v-tabs>

        <v-card-text class="pa-4">
          <!-- 1. DOCUMENTOS VINCULADOS -->
          <div v-if="tabVisor === 0">
            <v-list dense v-if="hojaRuta.documentos && hojaRuta.documentos.length">
              <v-list-item v-for="doc in hojaRuta.documentos" :key="doc.id" class="border rounded-lg mb-2">
                <v-list-item-icon><v-icon color="indigo">mdi-file-certificate</v-icon></v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold text-subtitle-2">{{ doc.cite }} - {{ doc.tipo_documento }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">{{ doc.asunto }}</v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn small color="indigo" outlined class="rounded-pill text-capitalize" @click="previsualizarDoc(doc.id)">
                    <v-icon left small>mdi-eye</v-icon> Ver Documento
                  </v-btn>
                </v-list-item-action>
              </v-list-item>
            </v-list>
            <div v-else class="text-center pa-6 text-caption text-secondary">
              Sin documentos oficiales redactados en esta hoja de ruta aún.
            </div>
          </div>

          <!-- 2. ARCHIVOS ADJUNTOS -->
          <div v-else-if="tabVisor === 1">
            <v-list dense v-if="hojaRuta.archivos_adjuntos && hojaRuta.archivos_adjuntos.length">
              <v-list-item v-for="adj in hojaRuta.archivos_adjuntos" :key="adj.id" class="border rounded-lg mb-2">
                <v-list-item-icon><v-icon color="teal">mdi-file-pdf-box</v-icon></v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold text-subtitle-2">{{ adj.nombre_original }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">{{ (adj.tamanio_bytes / 1024).toFixed(1) }} KB</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <div v-else class="text-center pa-6 text-caption text-secondary">
              Sin archivos adjuntos registrados.
            </div>
          </div>

          <!-- 3. ÁRBOL DE DERIVACIONES -->
          <div v-else-if="tabVisor === 2">
            <v-timeline dense align-top>
              <v-timeline-item
                v-for="(der, idx) in hojaRuta.derivaciones"
                :key="der.id"
                color="primary"
                small
                fill-dot
              >
                <v-card outlined rounded="lg" class="pa-3 mb-2">
                  <div class="d-flex justify-space-between align-center">
                    <span class="font-weight-bold primary--text">Paso #{{ idx + 1 }}: {{ der.proveido }}</span>
                    <span class="text-caption text-secondary">{{ formatFecha(der.fecha_derivacion) }}</span>
                  </div>
                  <div class="text-caption mt-1">
                    <strong>De:</strong> {{ der.funcionario_origen ? der.funcionario_origen.nombre_completo : 'N/A' }}
                    <v-icon x-small color="grey">mdi-arrow-right</v-icon>
                    <strong>A:</strong> {{ der.funcionario_destino ? der.funcionario_destino.nombre_completo : 'N/A' }} ({{ der.unidad_destino ? der.unidad_destino.nombre : 'N/A' }})
                  </div>
                  <div v-if="der.instruccion_detalle" class="font-italic text-caption text-secondary mt-1">
                    "{{ der.instruccion_detalle }}"
                  </div>
                  <div class="mt-1">
                    <v-chip x-small label color="blue lighten-5" text-color="primary">Estado: {{ der.estado_derivacion }}</v-chip>
                  </div>
                </v-card>
              </v-timeline-item>
            </v-timeline>
          </div>
        </v-card-text>
      </v-card>
    </div>

    <!-- MODAL COMPARTIR EXPEDIENTE -->
    <v-dialog v-model="dialogCompartir" max-width="500px" persistent>
      <v-card rounded="lg" v-if="hojaRuta">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-share-variant</v-icon> Conceder Acceso de Lectura Temporal
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary mb-3">
            Permite que otro funcionario consulte el expediente <strong>{{ hojaRuta.nro_hoja_ruta }}</strong> en modo lectura durante un plazo determinado.
          </p>

          <v-select
            v-model="formCompartir.id_usuario_destinatario"
            :items="usuarios"
            item-text="name"
            item-value="id"
            label="Funcionario / Usuario *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-text-field
            v-model.number="formCompartir.dias_validez"
            label="Días de Acceso Habilitados"
            type="number"
            dense
            outlined
            class="mb-2"
          ></v-text-field>

          <v-text-field
            v-model="formCompartir.motivo"
            label="Motivo del Acceso"
            dense
            outlined
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogCompartir = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="confirmarCompartir">
            Conceder Acceso
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
  name: 'VisorExpedienteDigital',
  data() {
    return {
      citeBusqueda: '',
      hojaRuta: null,
      cargando: false,
      guardando: false,
      tabVisor: 0,

      dialogCompartir: false,
      usuarios: [],
      formCompartir: {
        id_usuario_destinatario: null,
        dias_validez: 7,
        motivo: 'Para revisión técnica de antecedentes.',
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarUsuarios();
    if (this.$route.query.id) {
      this.cargarPorId(this.$route.query.id);
    }
  },
  methods: {
    async cargarPorId(id) {
      this.cargando = true;
      try {
        const res = await window.axios.get(`/api/correspondencia/hojas-ruta/${id}`);
        if (res.data && res.data.success) {
          this.hojaRuta = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar expediente.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async buscarExpediente() {
      if (!this.citeBusqueda) return;
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/correspondencia/hojas-ruta', {
          params: { q: this.citeBusqueda.trim(), bandeja: 'TODOS' },
        });
        if (res.data && res.data.success && res.data.data.length > 0) {
          await this.cargarPorId(res.data.data[0].id);
        } else {
          this.mostrarMensaje('No se encontró la hoja de ruta.', 'warning');
        }
      } catch (e) {
        this.mostrarMensaje('Error al buscar expediente.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async cargarUsuarios() {
      try {
        const res = await window.axios.get('/api/usuario');
        if (res.data && res.data.data) {
          this.usuarios = res.data.data;
        }
      } catch (e) {}
    },
    async confirmarCompartir() {
      if (!this.formCompartir.id_usuario_destinatario) {
        this.mostrarMensaje('Selecciona el usuario.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/compartidos/compartir', {
          id_hoja_ruta: this.hojaRuta.id,
          id_usuario_destinatario: this.formCompartir.id_usuario_destinatario,
          dias_validez: this.formCompartir.dias_validez,
          motivo: this.formCompartir.motivo,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Expediente compartido con éxito.', 'success');
          this.dialogCompartir = false;
        }
      } catch (e) {
        this.mostrarMensaje('Error al compartir expediente.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    previsualizarDoc(id) {
      window.open(`/api/correspondencia/documentos/${id}/preview`, '_blank');
    },
    imprimirCaratula(id) {
      window.open(`/api/correspondencia/hojas-ruta/${id}/caratula`, '_blank');
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
</style>
