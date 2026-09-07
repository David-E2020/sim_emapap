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
            <span class="text-caption text-secondary">Bandeja de documentos oficiales pendientes de visto bueno (VIA) y rúbrica de emisión (DE)</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn-toggle v-model="filtroBandeja" mandatory dense color="primary" class="mr-2" @change="cargarPendientes">
            <v-btn value="MIS_PENDIENTES" small class="text-capitalize">
              <v-icon left small>mdi-account-clock</v-icon> Mis Pendientes
            </v-btn>
            <v-btn value="TODOS" small class="text-capitalize">
              <v-icon left small>mdi-office-building</v-icon> Todas (EMAPA)
            </v-btn>
          </v-btn-toggle>

          <v-btn icon color="secondary" @click="cargarPendientes"><v-icon>mdi-refresh</v-icon></v-btn>
        </div>
      </div>

      <!-- BARRA DE BÚSQUEDA Y CONTADORES -->
      <v-divider class="my-3"></v-divider>
      <div class="d-flex align-center gap-3 flex-wrap justify-space-between">
        <v-text-field
          v-model="busqueda"
          prepend-inner-icon="mdi-magnify"
          label="Buscar por CITE, Asunto o Remitente..."
          dense
          outlined
          hide-details
          clearable
          style="max-width: 420px;"
        ></v-text-field>
        <div class="d-flex gap-2">
          <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-medium">
            <v-icon left x-small>mdi-file-document-multiple-outline</v-icon> Total en Bandeja: {{ pendientesFiltrados.length }}
          </v-chip>
        </div>
      </div>
    </v-card>

    <!-- LISTA DE DOCUMENTOS PENDIENTES -->
    <v-row v-if="pendientesFiltrados.length">
      <v-col cols="12" md="6" lg="4" v-for="doc in pendientesFiltrados" :key="doc.id">
        <v-card rounded="lg" class="erp-card-elevated h-100 d-flex flex-column justify-space-between">
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
                {{ doc.cite || 'DOCUMENTO #' + doc.id }}
              </v-chip>
              <v-chip x-small label :color="doc.estado === 'OBSERVADO' ? 'red lighten-4' : 'orange lighten-4'" :text-color="doc.estado === 'OBSERVADO' ? 'red darken-4' : 'orange darken-4'" class="font-weight-bold">
                {{ doc.estado === 'EN_REVISION' ? 'EN REVISIÓN' : doc.estado }}
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
            <div class="text-caption font-weight-medium text-secondary mb-1">Circuito de Firmas y Vistos Buenos:</div>
            <div class="d-flex flex-wrap gap-1">
              <v-chip
                x-small
                v-for="p in doc.participantes"
                :key="p.id"
                label
                outlined
                :color="tieneFirma(doc, p.id_persona) ? 'success' : (p.tipo_participacion === 'VIA' ? 'purple' : 'primary')"
              >
                <v-icon left x-small v-if="tieneFirma(doc, p.id_persona)">mdi-check-circle</v-icon>
                {{ p.tipo_participacion }}: {{ p.persona ? p.persona.nombre_completo : '' }}
              </v-chip>
            </div>
          </v-card-text>

          <v-card-actions class="pa-4 bg-grey-lighten-5 border-t">
            <v-btn small text color="indigo" class="rounded-pill text-capitalize font-weight-bold" @click="abrirVisorModal(doc)">
              <v-icon left small>mdi-eye-outline</v-icon> Ver
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn small outlined color="red" class="rounded-pill text-capitalize mr-2" @click="abrirModalObservar(doc)">
              <v-icon left small>mdi-close-circle</v-icon> Observar
            </v-btn>
            <v-btn small color="success" class="rounded-pill text-capitalize white--text px-3 font-weight-bold" @click="abrirModalFirmar(doc)">
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
      <h3 class="text-h6 font-weight-bold mb-1">No hay documentos pendientes</h3>
      <p class="text-caption text-secondary">
        {{ filtroBandeja === 'MIS_PENDIENTES' ? 'No tienes firmas pendientes asignadas a tu cuenta personal.' : 'No existen documentos pendientes de rúbrica en la institución.' }}
      </p>
      <v-btn v-if="filtroBandeja === 'MIS_PENDIENTES'" color="primary" outlined class="rounded-pill text-capitalize mt-2" @click="filtroBandeja = 'TODOS'; cargarPendientes();">
        <v-icon left small>mdi-office-building</v-icon> Ver Todas las Firmas de EMAPA
      </v-btn>
    </v-card>

    <div v-if="cargando" class="text-center pa-10">
      <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL VENTANA EMERGENTE: VISOR COMPLETO DE DOCUMENTO OFICIAL              -->
    <!-- ========================================================================= -->
    <v-dialog v-model="dialogVisor" max-width="960px" scrollable>
      <v-card rounded="lg" v-if="docVisor">
        <v-card-title class="py-3 px-5 d-flex justify-space-between align-center bg-slate-800 white--text" style="background-color: #1e293b;">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">mdi-file-document-outline</v-icon>
            <div>
              <span class="text-subtitle-1 font-weight-bold white--text">{{ docVisor.cite || 'DOCUMENTO OFICIAL' }}</span>
              <span class="text-caption text-grey-lighten-2 d-block" style="color: #94a3b8 !important;">{{ docVisor.tipo_documento }} &bull; {{ docVisor.estado }}</span>
            </div>
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn icon dark small @click="imprimirDocumento"><v-icon>mdi-printer</v-icon></v-btn>
            <v-btn icon dark small @click="dialogVisor = false"><v-icon>mdi-close</v-icon></v-btn>
          </div>
        </v-card-title>

        <v-card-text class="pa-6" style="max-height: 75vh; background-color: #f8fafc;">
          <!-- HOJA DE DOCUMENTO FORMATO OFICIAL -->
          <div class="bg-white pa-8 rounded-lg shadow-sm border mb-4" style="background-color: #ffffff; border: 1px solid #e2e8f0; color: #1e293b;">
            <!-- CABECERA INSTITUCIONAL -->
            <div class="text-center border-b pb-4 mb-4" style="border-bottom: 2px solid #0f172a;">
              <h3 class="text-h6 font-weight-bold mb-0" style="color: #0f172a; letter-spacing: 0.5px;">EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA</h3>
              <div class="text-caption font-weight-medium text-secondary">ESTADO PLURINACIONAL DE BOLIVIA</div>
              <div class="mt-2 font-weight-bold text-subtitle-2 text-uppercase text-primary">{{ docVisor.tipo_documento }}</div>
              <div class="text-caption font-weight-bold text-secondary">CITE: {{ docVisor.cite || 'EN TRÁMITE' }}</div>
            </div>

            <!-- METADATOS DE CABECERA -->
            <v-row class="mb-4 text-body-2" dense>
              <v-col cols="12" sm="6">
                <strong>Unidad Generadora:</strong> {{ docVisor.unidad_generadora ? docVisor.unidad_generadora.nombre : 'EMAPA' }}
              </v-col>
              <v-col cols="12" sm="6">
                <strong>Fecha:</strong> {{ formatFecha(docVisor._fecha_creacion) }}
              </v-col>
              <v-col cols="12">
                <strong>Asunto:</strong> {{ docVisor.asunto }}
              </v-col>
              <v-col cols="12" v-if="docVisor.codigo_verificacion">
                <strong>Código de Verificación QR:</strong> <code>{{ docVisor.codigo_verificacion }}</code>
              </v-col>
            </v-row>

            <v-divider class="my-4"></v-divider>

            <!-- CONTENIDO HTML DEL DOCUMENTO -->
            <div class="document-html-content py-2 text-body-1" style="line-height: 1.7; color: #334155;" v-html="docVisor.contenido_html || '<p class=text-secondary>Sin contenido redactado.</p>'"></div>

            <v-divider class="my-5"></v-divider>

            <!-- CIRCUITO DE FIRMAS Y VISTOS BUENOS -->
            <div class="mt-4">
              <h4 class="text-subtitle-2 font-weight-bold mb-3 text-secondary text-uppercase">Rúbricas y Vistos Buenos Electrónicos</h4>
              <v-row dense>
                <v-col cols="12" md="6" v-for="p in docVisor.participantes" :key="p.id">
                  <div class="pa-3 rounded-lg border d-flex align-center justify-space-between" :style="tieneFirma(docVisor, p.id_persona) ? 'background-color: #f0fdf4; border-color: #86efac !important;' : 'background-color: #fefce8; border-color: #fef08a !important;'">
                    <div>
                      <div class="font-weight-bold text-body-2" style="color: #0f172a;">{{ p.persona ? p.persona.nombre_completo : 'Funcionario' }}</div>
                      <div class="text-caption text-secondary">{{ p.cargo_snapshot || (p.puesto ? p.puesto.nombre : 'Funcionario EMAPA') }}</div>
                      <div class="text-caption font-weight-bold" :class="tieneFirma(docVisor, p.id_persona) ? 'green--text' : 'orange--text text--darken-3'">
                        {{ p.tipo_participacion }}: {{ tieneFirma(docVisor, p.id_persona) ? '✓ FIRMADO / APROBADO' : '⏳ PENDIENTE' }}
                      </div>
                    </div>
                    <v-avatar size="36" :color="tieneFirma(docVisor, p.id_persona) ? 'success' : 'orange lighten-4'">
                      <v-icon small :color="tieneFirma(docVisor, p.id_persona) ? 'white' : 'orange darken-3'">
                        {{ tieneFirma(docVisor, p.id_persona) ? 'mdi-check-decagram' : 'mdi-clock-outline' }}
                      </v-icon>
                    </v-avatar>
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="pa-4 bg-white border-t d-flex justify-space-between flex-wrap gap-2">
          <v-btn outlined color="secondary" class="rounded-pill text-capitalize" @click="dialogVisor = false">
            Cerrar
          </v-btn>

          <div class="d-flex gap-2">
            <v-btn outlined color="red" class="rounded-pill text-capitalize" @click="dialogVisor = false; abrirModalObservar(docVisor)">
              <v-icon left small>mdi-close-circle</v-icon> Observar
            </v-btn>
            <v-btn color="success" class="rounded-pill text-capitalize white--text px-4 font-weight-bold" @click="dialogVisor = false; abrirModalFirmar(docVisor)">
              <v-icon left small>mdi-draw-pen</v-icon> Firmar este Documento
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ========================================================================= -->
    <!-- MODAL FIRMAR CON PIN O CERTIFICADO                                       -->
    <!-- ========================================================================= -->
    <v-dialog v-model="dialogFirmar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 green darken-2 white--text py-3">
          <v-icon left color="white">mdi-shield-lock-outline</v-icon> Sello Electrónico de Conformidad
        </v-card-title>
        <v-card-text class="pt-4 text-left">
          <p class="text-caption text-secondary mb-3">
            Está por rubricar oficialmente el documento <strong>{{ docSeleccionado.cite || docSeleccionado.asunto }}</strong>. Se estampará su firma con sello de tiempo y hash SHA-256 inmutable.
          </p>

          <!-- SELECCIONAR PARTICIPANTE PARA FIRMAR (SI HAY MÚLTIPLES) -->
          <v-select
            v-if="participantesDisponiblesParaFirma.length > 1"
            v-model="idPersonaSeleccionada"
            :items="participantesDisponiblesParaFirma"
            item-text="label"
            item-value="id_persona"
            label="Firmar en calidad de *"
            dense
            outlined
            class="mb-3"
          ></v-select>

          <div v-else-if="participantesDisponiblesParaFirma.length === 1" class="mb-3 pa-2 rounded border bg-light text-caption">
            <strong>Firmante:</strong> {{ participantesDisponiblesParaFirma[0].label }}
          </div>

          <v-select
            v-model="tipoFirma"
            :items="[
              { text: 'PIN Electrónico Institucional (1234)', value: 'PIN_ELECTRONICO' },
              { text: 'Token / Certificado Digital ADSIB', value: 'TOKEN_DIGITAL' },
              { text: 'Ciudadanía Digital', value: 'CIUDADANIA_DIGITAL' }
            ]"
            item-text="text"
            item-value="value"
            label="Método de Firma"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-text-field
            v-if="tipoFirma === 'PIN_ELECTRONICO'"
            v-model="pinFirma"
            label="PIN Electrónico de Aprobación"
            type="password"
            outlined
            dense
            placeholder="Ingrese su PIN (ej. 1234)"
            prepend-inner-icon="mdi-lock"
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogFirmar = false">Cancelar</v-btn>
          <v-btn color="success" class="rounded-pill text-capitalize px-4 font-weight-bold" :loading="firmando" @click="confirmarFirma">
            Rubricar Documento
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ========================================================================= -->
    <!-- MODAL OBSERVAR DOCUMENTO                                                 -->
    <!-- ========================================================================= -->
    <v-dialog v-model="dialogObservar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 red darken-2 white--text py-3">
          <v-icon left color="white">mdi-close-circle</v-icon> Observar y Rechazar Documento
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            Al observar este documento, se pausará el flujo de firmas y retornará al redactor para que efectúe las correcciones técnicas pertinentes.
          </p>

          <v-select
            v-if="participantesDisponiblesParaFirma.length > 1"
            v-model="idPersonaSeleccionada"
            :items="participantesDisponiblesParaFirma"
            item-text="label"
            item-value="id_persona"
            label="Observar en calidad de *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-textarea
            v-model="motivoObservacion"
            label="Motivo de la Observación *"
            rows="3"
            dense
            outlined
            placeholder="Detalle las observaciones técnicas..."
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogObservar = false">Cancelar</v-btn>
          <v-btn color="red darken-2" class="rounded-pill text-capitalize white--text px-4 font-weight-bold" :loading="firmando" @click="confirmarObservacion">
            Confirmar Rechazo
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
      busqueda: '',
      filtroBandeja: 'MIS_PENDIENTES',
      cargando: false,
      firmando: false,
      docSeleccionado: null,

      // Visor Modal Emergente
      dialogVisor: false,
      docVisor: null,

      // Modal Firmar
      dialogFirmar: false,
      idPersonaSeleccionada: null,
      pinFirma: '1234',
      tipoFirma: 'PIN_ELECTRONICO',

      // Modal Observar
      dialogObservar: false,
      motivoObservacion: '',

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    pendientesFiltrados() {
      if (!this.busqueda || !this.busqueda.trim()) {
        return this.pendientes;
      }
      const q = this.busqueda.toLowerCase().trim();
      return this.pendientes.filter(doc => {
        const cite = (doc.cite || '').toLowerCase();
        const asunto = (doc.asunto || '').toLowerCase();
        const creador = (doc.creador && doc.creador.nombre_completo ? doc.creador.nombre_completo : '').toLowerCase();
        return cite.includes(q) || asunto.includes(q) || creador.includes(q);
      });
    },
    participantesDisponiblesParaFirma() {
      if (!this.docSeleccionado || !this.docSeleccionado.participantes) return [];
      return this.docSeleccionado.participantes
        .filter(p => !this.tieneFirma(this.docSeleccionado, p.id_persona) && (p.tipo_participacion === 'VIA' || p.tipo_participacion === 'REMITENTE_DE'))
        .map(p => ({
          id_persona: p.id_persona,
          label: `${p.tipo_participacion}: ${p.persona ? p.persona.nombre_completo : 'Funcionario'} (${p.cargo_snapshot || 'Funcionario'})`,
        }));
    },
  },
  mounted() {
    this.cargarPendientes();
  },
  methods: {
    async cargarPendientes() {
      this.cargando = true;
      try {
        const params = {};
        const userStored = localStorage.getItem('user');
        if (userStored) {
          try {
            const u = JSON.parse(userStored);
            if (u.usr_externo_id) params.id_persona = u.usr_externo_id;
            else if (u.id_persona) params.id_persona = u.id_persona;
          } catch (_e) {}
        }
        if (this.filtroBandeja === 'TODOS') {
          params.todas = 1;
          params.bandeja = 'TODAS';
        }
        const res = await window.axios.get('/api/correspondencia/firmas/pendientes', { params });
        if (res.data && res.data.success) {
          this.pendientes = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar firmas pendientes.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    tieneFirma(doc, idPersona) {
      if (!doc || !doc.firmas_aprobaciones) return false;
      return doc.firmas_aprobaciones.some(f => f.id_persona === idPersona && f.estado === 'FIRMADO');
    },
    abrirVisorModal(doc) {
      this.docVisor = doc;
      this.dialogVisor = true;
    },
    abrirModalFirmar(doc) {
      this.docSeleccionado = doc;
      this.pinFirma = '1234';
      this.tipoFirma = 'PIN_ELECTRONICO';

      // Auto-seleccionar primer firmante pendiente disponible
      const pendientes = this.participantesDisponiblesParaFirma;
      if (pendientes && pendientes.length > 0) {
        this.idPersonaSeleccionada = pendientes[0].id_persona;
      } else {
        this.idPersonaSeleccionada = null;
      }

      this.dialogFirmar = true;
    },
    async confirmarFirma() {
      this.firmando = true;
      try {
        const payload = {
          id_documento: this.docSeleccionado.id,
          pin: this.pinFirma,
          tipo_firma: this.tipoFirma,
          id_persona: this.idPersonaSeleccionada,
        };
        const res = await window.axios.post('/api/correspondencia/firmas/firmar', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento rubricado y sellado exitosamente.', 'success');
          this.dialogFirmar = false;
          this.cargarPendientes();
        }
      } catch (e) {
        const msg = e.response && e.response.data && e.response.data.message ? e.response.data.message : 'Error al firmar documento.';
        this.mostrarMensaje(msg, 'error');
      } finally {
        this.firmando = false;
      }
    },
    abrirModalObservar(doc) {
      this.docSeleccionado = doc;
      this.motivoObservacion = '';
      const pendientes = this.participantesDisponiblesParaFirma;
      if (pendientes && pendientes.length > 0) {
        this.idPersonaSeleccionada = pendientes[0].id_persona;
      }
      this.dialogObservar = true;
    },
    async confirmarObservacion() {
      if (!this.motivoObservacion.trim()) {
        this.mostrarMensaje('Debe especificar el motivo del rechazo.', 'warning');
        return;
      }
      this.firmando = true;
      try {
        const payload = {
          id_documento: this.docSeleccionado.id,
          motivo: this.motivoObservacion,
          id_persona: this.idPersonaSeleccionada,
        };
        const res = await window.axios.post('/api/correspondencia/firmas/rechazar', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento devuelto con observaciones.', 'success');
          this.dialogObservar = false;
          this.cargarPendientes();
        }
      } catch (e) {
        const msg = e.response && e.response.data && e.response.data.message ? e.response.data.message : 'Error al observar documento.';
        this.mostrarMensaje(msg, 'error');
      } finally {
        this.firmando = false;
      }
    },
    imprimirDocumento() {
      window.print();
    },
    formatFecha(f) {
      if (!f) return '-';
      const d = new Date(f);
      return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
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
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
.gap-3 {
  gap: 12px;
}
.document-html-content p {
  margin-bottom: 0.75rem;
}
</style>
