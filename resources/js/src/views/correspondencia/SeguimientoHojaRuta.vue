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
            <h2 class="text-h5 font-weight-bold mb-0">Seguimiento y Trazabilidad Integral</h2>
            <span class="text-caption text-secondary">Historial cronológico, árbol de derivaciones, documentos oficiales y control de plazos (Estándar Londra)</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0" v-if="seguimientoData">
          <v-btn color="teal darken-1" class="text-capitalize rounded-pill white--text" @click="imprimirCaratula(seguimientoData.hoja_ruta.id)">
            <v-icon left small>mdi-printer</v-icon> Imprimir Carátula Oficial
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BARRA DE BÚSQUEDA DIRECTA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="8">
          <v-text-field
            v-model="citeBusqueda"
            label="Ingrese CITE de Hoja de Ruta (Ej: HR-EMAPA-NAL-0001/2026 o ID)"
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
            <v-icon left small>mdi-compass</v-icon> Rastrear Trámite
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
          <div class="d-flex gap-2 flex-wrap">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
              ESTADO: {{ seguimientoData.hoja_ruta.estado }}
            </v-chip>
            <v-chip label small :color="getColorPrioridad(seguimientoData.hoja_ruta.prioridad)" class="font-weight-bold white--text">
              PRIORIDAD: {{ seguimientoData.hoja_ruta.prioridad }}
            </v-chip>
            <v-chip label small v-if="seguimientoData.hoja_ruta.confidencial" color="red lighten-5" text-color="red" class="font-weight-bold">
              CONFIDENCIAL
            </v-chip>
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

        <div v-if="seguimientoData.hoja_ruta.esta_con" class="mt-2 pa-2 rounded teal lighten-5 text-caption font-weight-bold teal--text text--darken-4">
          <v-icon x-small color="teal darken-3">mdi-account-clock</v-icon>
          Custodia Actual: {{ seguimientoData.hoja_ruta.esta_con.funcionario }} ({{ seguimientoData.hoja_ruta.esta_con.unidad }}) - Estado: {{ seguimientoData.hoja_ruta.esta_con.estado }}
        </div>
      </v-card>

      <!-- DOCUMENTOS OFICIALES ASOCIADOS -->
      <v-card rounded="lg" class="erp-card-elevated mb-5 pa-4" v-if="seguimientoData.hoja_ruta.documentos && seguimientoData.hoja_ruta.documentos.length">
        <h3 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center primary--text">
          <v-icon left small color="primary">mdi-file-document-multiple-outline</v-icon> Documentos Oficiales Emitidos en el Trámite
        </h3>
        <v-simple-table dense>
          <thead>
            <tr>
              <th>CITE Oficial</th>
              <th>Tipo</th>
              <th>Asunto</th>
              <th>Estado</th>
              <th>Firmantes</th>
              <th class="text-center">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="doc in seguimientoData.hoja_ruta.documentos" :key="doc.id">
              <td class="font-weight-bold primary--text">{{ doc.cite }}</td>
              <td>{{ doc.tipo_documento }}</td>
              <td class="text-truncate" style="max-width: 250px;">{{ doc.asunto }}</td>
              <td>
                <v-chip x-small label :color="doc.estado === 'FIRMADO' ? 'green' : 'blue'" class="white--text font-weight-bold">
                  {{ doc.estado }}
                </v-chip>
              </td>
              <td>
                <div v-for="f in doc.firmas_aprobaciones || []" :key="f.id" class="text-caption">
                  <v-icon x-small color="green">mdi-check</v-icon> {{ f.persona ? f.persona.nombre_completo : '' }}
                </div>
              </td>
              <td class="text-center">
                <v-btn x-small icon color="indigo" @click="verDocumento(doc.id)">
                  <v-icon small>mdi-eye</v-icon>
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card>

      <!-- TIMELINE CRONOLÓGICO Y DERIVACIONES -->
      <v-card rounded="lg" class="erp-card-elevated pa-6">
        <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
          <v-icon left color="teal darken-1">mdi-timeline-clock</v-icon>
          Línea de Tiempo y Trazabilidad de Derivaciones
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
                  <v-icon x-small color="grey" class="mx-1">mdi-arrow-right-bold</v-icon>
                  <strong>A:</strong> {{ ev.a }} ({{ ev.a_cargo }} - {{ ev.a_unidad }})
                  <v-chip x-small v-if="ev.es_copia" color="purple lighten-5" text-color="purple darken-2" label class="font-weight-bold ml-1">
                    CC Copia
                  </v-chip>
                </div>
                <div v-if="ev.instruccion" class="pa-2 rounded bg-grey-lighten-4 font-italic mb-1">
                  "{{ ev.instruccion }}"
                </div>
                <div class="d-flex gap-2 align-center mt-1 flex-wrap">
                  <v-chip x-small label color="blue lighten-5" text-color="primary">Estado: {{ ev.subtitulo }}</v-chip>
                  <span v-if="ev.fecha_recepcion" class="green--text font-weight-medium">
                    <v-icon x-small color="green">mdi-check-all</v-icon> Recibido: {{ ev.fecha_recepcion }}
                  </span>
                  <span v-else class="orange--text font-weight-medium">
                    <v-icon x-small color="orange">mdi-clock-alert-outline</v-icon> En Tránsito (Pendiente de Recepción)
                  </span>
                </div>
              </div>

              <!-- SI ES CONCLUIDO -->
              <div v-if="ev.tipo === 'CONCLUIDO'" class="mt-1 text-caption teal--text text--darken-3 font-weight-medium">
                Motivo: {{ ev.subtitulo }}
              </div>
            </v-card>
          </v-timeline-item>
        </v-timeline>
      </v-card>
    </div>

    <!-- ESTADO INICIAL VACÍO -->
    <v-card v-else-if="!cargando" rounded="lg" class="erp-card-elevated pa-10 text-center">
      <v-avatar size="80" color="teal lighten-5" class="mb-4">
        <v-icon size="42" color="teal darken-1">mdi-compass-outline</v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold mb-1">Rastreo de Correspondencia Institucional</h3>
      <p class="text-caption text-secondary">Ingrese el CITE o número de Hoja de Ruta en la barra superior para visualizar la trazabilidad completa.</p>
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
      this.citeBusqueda = this.$route.query.id;
      this.buscarTrazabilidad();
    } else if (this.$route.query.cite) {
      this.citeBusqueda = this.$route.query.cite;
      this.buscarTrazabilidad();
    }
  },
  methods: {
    async buscarTrazabilidad() {
      if (!this.citeBusqueda || !this.citeBusqueda.trim()) {
        this.mostrarMensaje('Ingrese un CITE o ID de Hoja de Ruta.', 'warning');
        return;
      }
      this.cargando = true;
      try {
        const queryVal = encodeURIComponent(this.citeBusqueda.trim());
        const res = await window.axios.get(`/api/correspondencia/seguimiento/${queryVal}/timeline`);
        if (res.data && res.data.success) {
          this.seguimientoData = res.data.data;
        } else {
          this.mostrarMensaje('No se encontró información para el CITE ingresado.', 'error');
        }
      } catch (e) {
        this.mostrarMensaje('Error al consultar trazabilidad o expediente no encontrado.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    imprimirCaratula(id) {
      window.open(`/api/correspondencia/hojas-ruta/${id}/caratula`, '_blank');
    },
    verDocumento(id) {
      window.open(`/api/correspondencia/documentos/${id}/preview`, '_blank');
    },
    getColorPrioridad(p) {
      const map = { URGENTE: 'red darken-1', ALTA: 'orange darken-2', MEDIA: 'blue darken-1', BAJA: 'grey darken-1' };
      return map[p] || 'grey';
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
