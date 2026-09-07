<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-document-edit-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Gestión de Documentos Oficiales</h2>
            <span class="text-caption text-secondary">Redacción, revisión secuencial (VIA -> DE), firmas electrónicas y emisión oficial (Estándar Londra)</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="indigo" class="text-capitalize font-weight-medium rounded-pill elevation-2 white--text" @click="abrirModalRedactar">
            <v-icon left small>mdi-pencil-plus</v-icon> + Redactar Documento
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABS DE BANDEJA DOCUMENTAL (LONDRA) -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="indigo" grow @change="cargarDocumentos">
        <v-tab><v-icon left small>mdi-file-account-outline</v-icon> Mis Documentos</v-tab>
        <v-tab><v-icon left small>mdi-file-clock-outline</v-icon> En Revisión</v-tab>
        <v-tab><v-icon left small>mdi-file-check-outline</v-icon> Firmados / Aprobados</v-tab>
        <v-tab><v-icon left small>mdi-file-alert-outline</v-icon> Observados</v-tab>
        <v-tab><v-icon left small>mdi-file-multiple-outline</v-icon> Todos</v-tab>
      </v-tabs>
    </v-card>

    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="busqueda"
            label="Buscar por CITE o Asunto..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarDocumentos"
            @click:clear="cargarDocumentos"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="filtroTipo"
            :items="['TODOS', 'MEMORANDUM', 'INFORME_TECNICO', 'NOTA_INTERNA', 'CIRCULAR', 'CARTA_EXTERNA', 'RESOLUCION']"
            label="Tipo Documento"
            dense
            outlined
            hide-details
            @change="cargarDocumentos"
          ></v-select>
        </v-col>
        <v-col cols="12" md="3" class="d-flex justify-end">
          <v-btn color="indigo" outlined class="text-capitalize rounded-pill mr-2" @click="cargarDocumentos">
            <v-icon left small>mdi-filter</v-icon> Filtrar
          </v-btn>
          <v-btn icon color="secondary" @click="limpiarFiltros"><v-icon>mdi-refresh</v-icon></v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- LISTADO DE DOCUMENTOS -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <v-data-table
        :headers="headers"
        :items="documentos"
        :loading="cargando"
        dense
        class="erp-table"
        :items-per-page="15"
      >
        <!-- CITE -->
        <template v-slot:item.cite="{ item }">
          <div class="py-2">
            <v-chip label small color="indigo lighten-5" text-color="indigo" class="font-weight-bold mb-1">
              <v-icon left x-small>mdi-file-certificate-outline</v-icon>
              {{ item.cite }}
            </v-chip>
            <div class="text-caption text-secondary font-italic" v-if="item.codigo_verificacion">
              Token QR: <span style="font-family: monospace;">{{ item.codigo_verificacion }}</span>
            </div>
          </div>
        </template>

        <!-- TIPO -->
        <template v-slot:item.tipo_documento="{ item }">
          <v-chip x-small label color="blue-grey lighten-4" class="font-weight-bold">
            {{ formatTipoDoc(item.tipo_documento) }}
          </v-chip>
        </template>

        <!-- ASUNTO Y AUTOR -->
        <template v-slot:item.asunto="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2 text-truncate" style="max-width: 320px;">
              {{ item.asunto }}
            </div>
            <div class="text-caption text-secondary">
              Por: {{ item.creador ? item.creador.nombre_completo : 'Funcionario EMAPA' }}
              <span v-if="item.unidad_generadora"> | {{ item.unidad_generadora.nombre }}</span>
            </div>
            <div v-if="item.estado === 'OBSERVADO' && item.motivo_anulacion" class="text-caption orange--text text--darken-3 font-weight-bold">
              {{ item.motivo_anulacion }}
            </div>
          </div>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado="{ item }">
          <v-chip x-small label :color="getColorEstado(item.estado)" class="font-weight-bold white--text">
            {{ item.estado }}
          </v-chip>
        </template>

        <!-- ACCIONES (LONDRA REVERSE ENGINE) -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1 flex-wrap">
            <!-- Previsualizar / Imprimir -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="indigo" v-bind="attrs" v-on="on" @click="previsualizar(item.id)">
                  <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver / Imprimir Documento</span>
            </v-tooltip>

            <!-- Editar Documento (Borrador u Observado) -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_editar">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="abrirModalEditar(item)">
                  <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
              </template>
              <span>Editar y Subsanar</span>
            </v-tooltip>

            <!-- Enviar a Revisión -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_enviar_revision && item.estado === 'BORRADOR'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="teal" v-bind="attrs" v-on="on" @click="enviarARevision(item)">
                  <v-icon small>mdi-send-check-outline</v-icon>
                </v-btn>
              </template>
              <span>Enviar a Flujo de Firmas</span>
            </v-tooltip>

            <!-- Adjuntar Archivos -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="blue-grey" v-bind="attrs" v-on="on" @click="abrirModalAdjuntos(item)">
                  <v-icon small>mdi-paperclip</v-icon>
                </v-btn>
              </template>
              <span>Archivos Adjuntos ({{ item.archivos_adjuntos ? item.archivos_adjuntos.length : 0 }})</span>
            </v-tooltip>

            <!-- Anular Documento -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_anular && item.estado !== 'ANULADO'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="red darken-1" v-bind="attrs" v-on="on" @click="abrirModalAnular(item)">
                  <v-icon small>mdi-close-circle-outline</v-icon>
                </v-btn>
              </template>
              <span>Anular Documento</span>
            </v-tooltip>

            <!-- Eliminar Borrador -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_eliminar">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="red lighten-1" v-bind="attrs" v-on="on" @click="eliminarBorrador(item.id)">
                  <v-icon small>mdi-delete-outline</v-icon>
                </v-btn>
              </template>
              <span>Eliminar Borrador</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL REDACTAR / EDITAR DOCUMENTO -->
    <v-dialog v-model="dialogRedactar" max-width="900px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 indigo white--text py-3">
          <v-icon left color="white">mdi-pencil-plus</v-icon>
          {{ esEdicion ? 'Editar Documento: ' + formDoc.cite : 'Redactar Nuevo Documento Oficial' }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="6" v-if="!esEdicion">
              <v-select
                v-model="formDoc.tipo_documento"
                :items="tiposDocumento"
                item-text="nombre"
                item-value="codigo"
                label="Tipo de Documento Oficial *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12" md="6" v-if="!esEdicion">
              <v-select
                v-model="formDoc.id_unidad_generadora"
                :items="unidades"
                item-text="nombre"
                item-value="id"
                label="Unidad Organizacional Generadora *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formDoc.asunto"
                label="Asunto / Objeto del Documento *"
                dense
                outlined
                :rules="[v => !!v || 'El asunto es obligatorio']"
              ></v-text-field>
            </v-col>
          </v-row>

          <!-- PARTICIPANTES (SOLO EN CREACIÓN) -->
          <div v-if="!esEdicion" class="mt-2 mb-3">
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-subtitle-2 font-weight-bold indigo--text">Participantes y Firmantes del Documento</span>
              <v-btn x-small color="indigo" outlined class="rounded-pill text-capitalize" @click="agregarParticipante">
                <v-icon left x-small>mdi-plus</v-icon> + Agregar Participante
              </v-btn>
            </div>

            <div v-for="(p, idx) in formDoc.participantes" :key="'part-' + idx" class="pa-2 mb-2 grey lighten-4 rounded">
              <v-row dense align="center">
                <v-col cols="12" md="3">
                  <v-select
                    v-model="p.tipo_participacion"
                    :items="rolesParticipacion"
                    item-text="text"
                    item-value="val"
                    label="Rol *"
                    dense
                    outlined
                    hide-details
                  ></v-select>
                </v-col>
                <v-col cols="12" md="4">
                  <v-select
                    v-model="p.id_unidad"
                    :items="unidades"
                    item-text="nombre"
                    item-value="id"
                    label="Unidad *"
                    dense
                    outlined
                    hide-details
                    @change="cargarFuncionariosParticipante(idx)"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="4">
                  <v-select
                    v-model="p.id_persona"
                    :items="p.funcionariosDisponibles || []"
                    item-text="nombre_completo"
                    item-value="id"
                    label="Funcionario *"
                    dense
                    outlined
                    hide-details
                  ></v-select>
                </v-col>
                <v-col cols="12" md="1" class="text-center">
                  <v-btn icon x-small color="red" @click="eliminarParticipante(idx)" v-if="formDoc.participantes.length > 1">
                    <v-icon x-small>mdi-delete</v-icon>
                  </v-btn>
                </v-col>
              </v-row>
            </div>
          </div>

          <!-- CONTENIDO HTML -->
          <span class="text-subtitle-2 font-weight-bold">Cuerpo / Contenido del Documento *</span>
          <v-textarea
            v-model="formDoc.contenido_html"
            rows="6"
            dense
            outlined
            class="mt-1 font-italic"
            placeholder="Redacte el texto oficial aquí..."
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogRedactar = false">Cancelar</v-btn>
          <v-btn
            v-if="!esEdicion"
            color="secondary"
            outlined
            class="rounded-pill text-capitalize"
            :loading="guardando"
            @click="guardarDocumento(false)"
          >
            Guardar Borrador
          </v-btn>
          <v-btn color="indigo" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="guardarDocumento(true)">
            {{ esEdicion ? 'Actualizar y Enviar' : 'Generar y Enviar a Revisión' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL ANULAR DOCUMENTO -->
    <v-dialog v-model="dialogAnular" max-width="500px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 red darken-2 white--text py-3">
          <v-icon left color="white">mdi-close-circle</v-icon> Anular Documento: {{ docSeleccionado.cite }}
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            Esta acción anulará el documento de manera permanente registrando la justificación técnica en la bitácora de auditoría.
          </p>
          <v-textarea
            v-model="motivoAnulacion"
            label="Motivo de Anulación *"
            rows="3"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogAnular = false">Cancelar</v-btn>
          <v-btn color="red darken-2" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="confirmarAnulacion">
            Confirmar Anulación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL ADJUNTOS -->
    <v-dialog v-model="dialogAdjuntos" max-width="600px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 blue-grey darken-2 white--text py-3">
          <v-icon left color="white">mdi-paperclip</v-icon> Archivos Adjuntos: {{ docSeleccionado.cite }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-file-input
            v-model="archivoASubir"
            label="Seleccionar Archivo (PDF, Word, Excel, ZIP)..."
            dense
            outlined
            show-size
            class="mb-3"
          ></v-file-input>

          <v-btn color="blue-grey" class="white--text text-capitalize rounded-pill mb-4" :loading="guardando" @click="subirAdjunto">
            <v-icon left small>mdi-cloud-upload</v-icon> Subir Archivo
          </v-btn>

          <v-divider class="mb-3"></v-divider>
          <span class="text-subtitle-2 font-weight-bold">Archivos Subidos</span>
          <v-list dense class="pa-0">
            <v-list-item v-for="(adj, idx) in docSeleccionado.archivos_adjuntos || []" :key="'adj-' + idx">
              <v-list-item-icon><v-icon color="primary">mdi-file-document-outline</v-icon></v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="font-weight-bold">{{ adj.nombre_original }}</v-list-item-title>
                <v-list-item-subtitle class="text-caption">{{ (adj.tamanio_bytes / 1024).toFixed(1) }} KB | SHA256: {{ adj.hash_sha256.substring(0, 16) }}...</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="blue-grey" text class="rounded-pill text-capitalize" @click="dialogAdjuntos = false">Cerrar</v-btn>
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
  name: 'GestionDocumentos',
  data() {
    return {
      tabActual: 0,
      documentos: [],
      cargando: false,
      guardando: false,
      busqueda: '',
      filtroTipo: 'TODOS',

      headers: [
        { text: 'CITE Oficial', value: 'cite', width: '220px' },
        { text: 'Tipo', value: 'tipo_documento', width: '130px' },
        { text: 'Asunto / Generador', value: 'asunto' },
        { text: 'Estado', value: 'estado', width: '120px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '220px', align: 'center' },
      ],

      tiposDocumento: [
        { codigo: 'MEMORANDUM', nombre: 'Memorándum (MEM)' },
        { codigo: 'INFORME_TECNICO', nombre: 'Informe Técnico (INF)' },
        { codigo: 'NOTA_INTERNA', nombre: 'Nota Interna (NI)' },
        { codigo: 'CIRCULAR', nombre: 'Circular (CIR)' },
        { codigo: 'CARTA_EXTERNA', nombre: 'Carta Externa (CAR)' },
        { codigo: 'RESOLUCION', nombre: 'Resolución Administrativa (RES)' },
      ],

      rolesParticipacion: [
        { val: 'REMITENTE_DE', text: 'DE: Autor / Remitente' },
        { val: 'VIA', text: 'VIA: Revisor Intermedio' },
        { val: 'DESTINATARIO_A', text: 'A: Destinatario' },
        { val: 'CON_COPIA_A', text: 'CC: Copia Informativa' },
      ],

      unidades: [],
      dialogRedactar: false,
      esEdicion: false,
      docSeleccionado: null,

      formDoc: {
        id: null,
        cite: '',
        tipo_documento: 'MEMORANDUM',
        id_unidad_generadora: null,
        asunto: '',
        contenido_html: '',
        participantes: [],
      },

      dialogAnular: false,
      motivoAnulacion: '',

      dialogAdjuntos: false,
      archivoASubir: null,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarDocumentos();
    this.cargarUnidades();
  },
  methods: {
    async cargarDocumentos() {
      this.cargando = true;
      try {
        const bandejas = ['MIS_DOCUMENTOS', 'EN_REVISION', 'FIRMADOS', 'OBSERVADOS', 'TODOS'];
        const params = {
          bandeja: bandejas[this.tabActual] || 'MIS_DOCUMENTOS',
          tipo_documento: this.filtroTipo !== 'TODOS' ? this.filtroTipo : undefined,
          q: this.busqueda || undefined,
        };
        const res = await window.axios.get('/api/correspondencia/documentos', { params });
        if (res.data && res.data.success) {
          this.documentos = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar documentos.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async cargarUnidades() {
      try {
        const res = await window.axios.get('/api/rrhh/organigrama');
        if (res.data && res.data.success) {
          const planas = [];
          const aplanar = (items) => {
            items.forEach(u => {
              planas.push({ id: u.id, nombre: u.nombre, puestos: u.puestos || [] });
              if (u.dependencias && u.dependencias.length) aplanar(u.dependencias);
            });
          };
          aplanar(res.data.data);
          this.unidades = planas;
        }
      } catch (e) {}
    },
    cargarFuncionariosParticipante(idx) {
      const p = this.formDoc.participantes[idx];
      if (!p) return;
      const u = this.unidades.find(x => x.id === p.id_unidad);
      if (u && u.puestos) {
        const funcs = [];
        u.puestos.forEach(pto => {
          if (pto.asignaciones && pto.asignaciones.length && pto.asignaciones[0].persona) {
            funcs.push({
              id: pto.asignaciones[0].persona.id,
              nombre_completo: `${pto.asignaciones[0].persona.nombre_completo} (${pto.nombre})`,
            });
          }
        });
        this.$set(p, 'funcionariosDisponibles', funcs);
      }
    },
    abrirModalRedactar() {
      this.esEdicion = false;
      this.formDoc = {
        id: null,
        cite: '',
        tipo_documento: 'MEMORANDUM',
        id_unidad_generadora: this.unidades.length ? this.unidades[0].id : null,
        asunto: '',
        contenido_html: '',
        participantes: [
          { tipo_participacion: 'REMITENTE_DE', id_unidad: null, id_persona: null, funcionariosDisponibles: [] },
          { tipo_participacion: 'DESTINATARIO_A', id_unidad: null, id_persona: null, funcionariosDisponibles: [] },
        ],
      };
      this.dialogRedactar = true;
    },
    abrirModalEditar(item) {
      this.esEdicion = true;
      this.docSeleccionado = item;
      this.formDoc = {
        id: item.id,
        cite: item.cite,
        tipo_documento: item.tipo_documento,
        id_unidad_generadora: item.id_unidad_generadora,
        asunto: item.asunto,
        contenido_html: item.contenido_html,
        participantes: [],
      };
      this.dialogRedactar = true;
    },
    agregarParticipante() {
      this.formDoc.participantes.push({
        tipo_participacion: 'VIA',
        id_unidad: null,
        id_persona: null,
        funcionariosDisponibles: [],
      });
    },
    eliminarParticipante(idx) {
      this.formDoc.participantes.splice(idx, 1);
    },
    async guardarDocumento(enviarARevision = false) {
      if (!this.formDoc.asunto || !this.formDoc.contenido_html) {
        this.mostrarMensaje('Completa el asunto y el contenido del documento.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        if (this.esEdicion) {
          const res = await window.axios.put(`/api/correspondencia/documentos/${this.formDoc.id}`, {
            asunto: this.formDoc.asunto,
            contenido_html: this.formDoc.contenido_html,
            enviar_a_revision: enviarARevision,
          });
          if (res.data && res.data.success) {
            this.mostrarMensaje('Documento actualizado exitosamente.', 'success');
            this.dialogRedactar = false;
            this.cargarDocumentos();
          }
        } else {
          const payload = {
            tipo_documento: this.formDoc.tipo_documento,
            id_unidad_generadora: this.formDoc.id_unidad_generadora,
            asunto: this.formDoc.asunto,
            contenido_html: this.formDoc.contenido_html,
            enviar_a_revision: enviarARevision,
            participantes: this.formDoc.participantes.filter(p => p.id_persona),
          };
          const res = await window.axios.post('/api/correspondencia/documentos', payload);
          if (res.data && res.data.success) {
            this.mostrarMensaje('Documento generado exitosamente.', 'success');
            this.dialogRedactar = false;
            this.cargarDocumentos();
          }
        }
      } catch (e) {
        this.mostrarMensaje('Error al guardar documento.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async enviarARevision(item) {
      try {
        const res = await window.axios.post(`/api/correspondencia/documentos/${item.id}/enviar-revision`);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento enviado al flujo de firmas.', 'success');
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al enviar a revisión.', 'error');
      }
    },
    abrirModalAnular(item) {
      this.docSeleccionado = item;
      this.motivoAnulacion = '';
      this.dialogAnular = true;
    },
    async confirmarAnulacion() {
      if (!this.motivoAnulacion.trim()) {
        this.mostrarMensaje('Debe especificar el motivo de anulación.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post(`/api/correspondencia/documentos/${this.docSeleccionado.id}/anular`, {
          motivo: this.motivoAnulacion,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento anulado con éxito.', 'success');
          this.dialogAnular = false;
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al anular documento.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async eliminarBorrador(id) {
      if (!confirm('¿Desea eliminar este borrador?')) return;
      try {
        const res = await window.axios.delete(`/api/correspondencia/documentos/${id}`);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Borrador eliminado.', 'success');
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al eliminar borrador.', 'error');
      }
    },
    abrirModalAdjuntos(item) {
      this.docSeleccionado = item;
      this.archivoASubir = null;
      this.dialogAdjuntos = true;
    },
    async subirAdjunto() {
      if (!this.archivoASubir) {
        this.mostrarMensaje('Seleccione un archivo.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const formData = new FormData();
        formData.append('archivo', this.archivoASubir);
        const res = await window.axios.post(`/api/correspondencia/documentos/${this.docSeleccionado.id}/adjuntos`, formData);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Archivo adjuntado exitosamente.', 'success');
          if (!this.docSeleccionado.archivos_adjuntos) this.docSeleccionado.archivos_adjuntos = [];
          this.docSeleccionado.archivos_adjuntos.push(res.data.data);
          this.archivoASubir = null;
        }
      } catch (e) {
        this.mostrarMensaje('Error al subir adjunto.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    previsualizar(id) {
      window.open(`/api/correspondencia/documentos/${id}/preview`, '_blank');
    },
    limpiarFiltros() {
      this.busqueda = '';
      this.filtroTipo = 'TODOS';
      this.cargarDocumentos();
    },
    formatTipoDoc(t) {
      const map = {
        MEMORANDUM: 'Memorándum',
        INFORME_TECNICO: 'Informe Técnico',
        NOTA_INTERNA: 'Nota Interna',
        CIRCULAR: 'Circular',
        CARTA_EXTERNA: 'Carta Externa',
        RESOLUCION: 'Resolución Adm.',
      };
      return map[t] || t;
    },
    getColorEstado(e) {
      const map = {
        BORRADOR: 'grey darken-1',
        EN_REVISION: 'blue darken-1',
        OBSERVADO: 'orange darken-3',
        FIRMADO: 'green darken-2',
        ANULADO: 'red darken-2',
      };
      return map[e] || 'grey';
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
.erp-table {
  border-radius: 8px;
}
</style>
