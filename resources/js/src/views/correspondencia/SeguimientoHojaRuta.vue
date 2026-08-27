<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-1" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-timeline-text-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Seguimiento y Trazabilidad</h2>
            <span class="text-caption text-secondary">Historial cronológico completo, árbol de derivaciones y estado actual de expedientes</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- BARRA DE BÚSQUEDA DIRECTA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="8">
          <v-text-field
            v-model="citeBusqueda"
            label="Ingrese CITE de Hoja de Ruta (Ej: HR-EMAPA-NAL-0001/2026)"
            prepend-inner-icon="mdi-magnify"
            outlined
            dense
            hide-details
            clearable
            @keyup.enter="buscarTrazabilidad"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4" class="d-flex justify-end">
          <v-btn color="teal darken-1" class="text-capitalize rounded-pill white--text px-5" :loading="cargando" @click="buscarTrazabilidad">
            <v-icon left small>mdi-compass</v-icon> Rastrear Expediente
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- CONTENIDO DEL SEGUIMIENTO -->
    <div v-if="seguimientoData">
      <!-- RESUMEN DEL EXPEDIENTE -->
      <v-card rounded="lg" class="erp-card-elevated mb-5 pa-4">
        <div class="d-flex justify-space-between align-center flex-wrap mb-2">
          <div>
            <span class="text-caption font-weight-bold text-secondary">CÓDIGO ÚNICO DE TRÁMITE:</span>
            <h3 class="text-h6 font-weight-black primary--text">{{ seguimientoData.hoja_ruta.nro_hoja_ruta }}</h3>
          </div>
          <div class="d-flex gap-2">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
              ESTADO: {{ seguimientoData.hoja_ruta.estado }}
            </v-chip>
            <v-chip label small color="orange lighten-5" text-color="orange darken-4" class="font-weight-bold">
              PRIORIDAD: {{ seguimientoData.hoja_ruta.prioridad }}
            </v-chip>
            <v-btn small outlined color="teal" class="rounded-pill text-capitalize" @click="imprimirCaratula(seguimientoData.hoja_ruta.id)">
              <v-icon left small>mdi-printer</v-icon> Carátula Oficial
            </v-btn>
          </div>
        </div>

        <div class="pa-3 rounded bg-grey-lighten-4 font-weight-medium text-body-2 mb-3">
          <strong>Asunto:</strong> {{ seguimientoData.hoja_ruta.asunto }}
        </div>

        <v-row dense class="text-caption text-secondary">
          <v-col cols="12" sm="4">
            <strong>Remitente Origen:</strong> {{ seguimientoData.hoja_ruta.persona_origen ? seguimientoData.hoja_ruta.persona_origen.nombre_completo : (seguimientoData.hoja_ruta.remitente_externo || 'Ventanilla') }}
          </v-col>
          <v-col cols="12" sm="4">
            <strong>Unidad Origen:</strong> {{ seguimientoData.hoja_ruta.unidad_origen ? seguimientoData.hoja_ruta.unidad_origen.nombre : 'Externa' }}
          </v-col>
          <v-col cols="12" sm="4">
            <strong>Fojas / Anexos:</strong> {{ seguimientoData.hoja_ruta.nro_fojas }} Fojas | {{ seguimientoData.hoja_ruta.nro_anexos }} Anexos
          </v-col>
        </v-row>
      </v-card>

      <!-- TIMELINE CRONOLÓGICO -->
      <v-card rounded="lg" class="erp-card-elevated pa-6">
        <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
          <v-icon left color="teal darken-1">mdi-timeline-clock</v-icon>
          Línea de Tiempo del Trámite Institucional
        </h3>

        <v-timeline dense align-top>
          <v-timeline-item
            v-for="(ev, idx) in seguimientoData.timeline"
            :key="idx"
            :color="ev.color || 'primary'"
            :icon="ev.icono || 'mdi-circle'"
            fill-dot
            small
          >
            <v-card rounded="lg" class="elevation-1 pa-3 mb-2" :style="'border-left: 4px solid var(--v-' + (ev.color || 'primary') + '-base);'">
              <div class="d-flex justify-space-between align-center flex-wrap">
                <span class="font-weight-bold text-subtitle-2" :class="(ev.color || 'primary') + '--text'">
                  {{ ev.titulo }}
                </span>
                <span class="text-caption text-secondary">{{ ev.fecha_formateada }}</span>
              </div>

              <!-- SI ES DERIVACIÓN -->
              <div v-if="ev.tipo === 'DERIVACION'" class="mt-2 text-caption">
                <div class="mb-1">
                  <strong>De:</strong> {{ ev.de }} ({{ ev.de_unidad }})
                  <v-icon x-small color="grey">mdi-arrow-right-bold</v-icon>
                  <strong>A:</strong> {{ ev.a }} ({{ ev.a_cargo }} - {{ ev.a_unidad }})
                </div>
                <div v-if="ev.instruccion" class="pa-2 rounded bg-grey-lighten-4 font-italic mb-1">
                  "{{ ev.instruccion }}"
                </div>
                <div class="d-flex gap-2 align-center mt-1">
                  <v-chip x-small label color="blue lighten-5" text-color="primary">Estado: {{ ev.subtitulo }}</v-chip>
                  <span v-if="ev.fecha_recepcion" class="text-success font-weight-medium">
                    <v-icon x-small color="success">mdi-check-all</v-icon> Recibido: {{ ev.fecha_recepcion }}
                  </span>
                  <span v-else class="text-warning font-weight-medium">
                    <v-icon x-small color="warning">mdi-clock-alert-outline</v-icon> Pendiente de Recepción
                  </span>
                </div>
              </div>

              <!-- SI ES CREACIÓN -->
              <div v-else-if="ev.tipo === 'CREACION'" class="mt-2 text-caption">
                <div><strong>Iniciado por:</strong> {{ ev.actor }} ({{ ev.cargo }} - {{ ev.unidad }})</div>
              </div>
            </v-card>
          </v-timeline-item>
        </v-timeline>
      </v-card>
    </div>

    <!-- ESTADO INICIAL O NO ENCONTRADO -->
    <v-card v-else-if="!cargando" rounded="lg" class="erp-card-elevated pa-10 text-center">
      <v-avatar size="80" color="teal lighten-5" class="mb-4">
        <v-icon size="42" color="teal">mdi-file-search-outline</v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold mb-1">Rastreo de Hojas de Ruta EMAPA</h3>
      <p class="text-caption text-secondary">Ingrese el CITE o seleccione un expediente desde la bandeja para visualizar su trazabilidad.</p>
    </v-card>

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
  name: 'SeguimientoHojaRuta',
  data() {
    return {
      citeBusqueda: '',
      seguimientoData: null,
      cargando: false,
      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    if (this.$route.query.id) {
      this.cargarPorId(this.$route.query.id);
    }
  },
  methods: {
    async cargarPorId(id) {
      this.cargando = true;
      try {
        const res = await window.axios.get(`/api/correspondencia/seguimiento/${id}/timeline`);
        if (res.data && res.data.success) {
          this.seguimientoData = res.data.data;
          this.citeBusqueda = this.seguimientoData.hoja_ruta.nro_hoja_ruta;
        }
      } catch (e) {
        this.mostrarMensaje('Error al obtener la trazabilidad.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async buscarTrazabilidad() {
      if (!this.citeBusqueda) {
        this.mostrarMensaje('Ingrese un CITE para buscar.', 'warning');
        return;
      }
      this.cargando = true;
      try {
        // Buscar por CITE
        const resList = await window.axios.get('/api/correspondencia/hojas-ruta', {
          params: { q: this.citeBusqueda.trim(), bandeja: 'TODOS' },
        });
        if (resList.data && resList.data.success && resList.data.data.length > 0) {
          const id = resList.data.data[0].id;
          await this.cargarPorId(id);
        } else {
          this.mostrarMensaje('No se encontró ninguna hoja de ruta con ese CITE.', 'warning');
          this.seguimientoData = null;
        }
      } catch (e) {
        this.mostrarMensaje('Error al buscar expediente.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    imprimirCaratula(id) {
      window.open(`/api/correspondencia/hojas-ruta/${id}/caratula`, '_blank');
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
