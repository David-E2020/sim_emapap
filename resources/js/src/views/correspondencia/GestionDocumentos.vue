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
            <h2 class="text-h5 font-weight-bold mb-0">Redacción de Documentos</h2>
            <span class="text-caption text-secondary">Elaboración de memorándums, informes técnicos, circulares y notas oficiales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="indigo" class="text-capitalize font-weight-medium rounded-pill elevation-2 white--text" @click="abrirModalRedactar">
            <v-icon left small>mdi-pencil-plus</v-icon> + Redactar Documento
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE DOCUMENTOS -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <v-row dense class="mb-3" align="center">
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
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="filtroTipo"
            :items="['TODOS', 'MEMORANDUM', 'INFORME_TECNICO', 'NOTA_INTERNA', 'CIRCULAR', 'CARTA_EXTERNA']"
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
              {{ item.cite }}
            </v-chip>
            <div class="text-caption text-secondary" style="font-family: monospace;">
              Token QR: {{ item.codigo_verificacion }}
            </div>
          </div>
        </template>

        <!-- TIPO -->
        <template v-slot:item.tipo_documento="{ item }">
          <v-chip x-small label color="blue-grey lighten-4" class="font-weight-bold">
            {{ formatTipoDoc(item.tipo_documento) }}
          </v-chip>
        </template>

        <!-- ASUNTO -->
        <template v-slot:item.asunto="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2 text-truncate" style="max-width: 300px;">
              {{ item.asunto }}
            </div>
            <div class="text-caption text-secondary">
              Por: {{ item.creador ? item.creador.nombre_completo : 'Autoridad EMAPA' }}
            </div>
          </div>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado="{ item }">
          <v-chip x-small label :color="getColorEstado(item.estado)" class="font-weight-bold white--text">
            {{ item.estado }}
          </v-chip>
        </template>

        <!-- FECHA -->
        <template v-slot:item._fecha_creacion="{ item }">
          <span class="text-caption text-secondary">{{ formatFecha(item._fecha_creacion) }}</span>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="indigo" v-bind="attrs" v-on="on" @click="previsualizarDoc(item.id)">
                  <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
              </template>
              <span>Previsualizar Documento Oficial</span>
            </v-tooltip>

            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="teal" v-bind="attrs" v-on="on" @click="abrirModalAdjuntos(item)">
                  <v-icon small>mdi-paperclip</v-icon>
                </v-btn>
              </template>
              <span>Subir Archivos Adjuntos</span>
            </v-tooltip>

            <v-tooltip bottom v-if="item.estado === 'BORRADOR'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="green darken-2" v-bind="attrs" v-on="on" @click="firmarDirecto(item.id)">
                  <v-icon small>mdi-draw-pen</v-icon>
                </v-btn>
              </template>
              <span>Firmar Electrónicamente</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL REDACTAR DOCUMENTO -->
    <v-dialog v-model="dialogRedactar" max-width="850px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 indigo white--text py-3">
          <v-icon left color="white">mdi-pencil-plus</v-icon> Redactar Documento Oficial
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-select
                v-model="formDoc.tipo_documento"
                :items="tiposDocumento"
                item-text="param_nombre"
                item-value="param_codigo"
                label="Tipo de Documento *"
                dense
                outlined
                @change="seleccionarPlantilla"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                v-model="formDoc.id_unidad_generadora"
                :items="unidades"
                item-text="nombre"
                item-value="id"
                label="Unidad Emisora *"
                dense
                outlined
              ></v-select>
            </v-col>
          </v-row>

          <v-text-field
            v-model="formDoc.asunto"
            label="Referencia / Asunto del Documento *"
            dense
            outlined
            class="mb-2"
          ></v-text-field>

          <span class="text-caption font-weight-bold text-secondary">Contenido Oficial (HTML / Texto Formateado) *</span>
          <v-textarea
            v-model="formDoc.contenido_html"
            rows="8"
            outlined
            dense
            class="mt-1 mb-3"
            placeholder="Ingrese los párrafos, antecedentes, análisis técnico o resoluciones del documento..."
          ></v-textarea>

          <v-divider class="my-2"></v-divider>
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-subtitle-2 font-weight-bold indigo--text">Destinatarios y Aprobadores</span>
            <v-btn x-small color="indigo" outlined class="rounded-pill" @click="agregarParticipante">
              <v-icon left x-small>mdi-account-plus</v-icon> + Agregar
            </v-btn>
          </div>

          <v-simple-table dense class="mb-3">
            <template v-slot:default>
              <thead>
                <tr>
                  <th>Rol Participación</th>
                  <th>Funcionario</th>
                  <th class="text-center" style="width: 60px;">Quitar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(p, i) in formDoc.participantes" :key="i">
                  <td>
                    <v-select
                      v-model="p.tipo_participacion"
                      :items="rolesParticipacion"
                      dense
                      hide-details
                    ></v-select>
                  </td>
                  <td>
                    <v-select
                      v-model="p.id_persona"
                      :items="todosFuncionarios"
                      item-text="nombre_completo"
                      item-value="id"
                      dense
                      hide-details
                    ></v-select>
                  </td>
                  <td class="text-center">
                    <v-btn icon x-small color="red" @click="formDoc.participantes.splice(i, 1)"><v-icon x-small>mdi-delete</v-icon></v-btn>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogRedactar = false">Cancelar</v-btn>
          <v-btn color="indigo" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="guardarDocumento">
            Generar CITE y Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL ADJUNTOS -->
    <v-dialog v-model="dialogAdjuntos" max-width="550px" persistent>
      <v-card rounded="lg" v-if="docSeleccionado">
        <v-card-title class="font-weight-bold text-h6 teal white--text py-3">
          <v-icon left color="white">mdi-paperclip</v-icon> Adjuntos: {{ docSeleccionado.cite }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-file-input
            v-model="archivoASubir"
            label="Seleccionar Archivo (PDF, DOCX, ZIP)"
            dense
            outlined
            prepend-icon="mdi-upload"
            show-size
          ></v-file-input>

          <v-list dense class="mt-2">
            <v-subheader class="font-weight-bold">Archivos ya adjuntados</v-subheader>
            <v-list-item v-for="adj in docSeleccionado.archivos_adjuntos" :key="adj.id">
              <v-list-item-icon><v-icon small color="teal">mdi-file-pdf-box</v-icon></v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="text-caption font-weight-medium">{{ adj.nombre_original }}</v-list-item-title>
                <v-list-item-subtitle class="text-caption">{{ (adj.tamanio_bytes / 1024).toFixed(1) }} KB</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <div v-if="!docSeleccionado.archivos_adjuntos || !docSeleccionado.archivos_adjuntos.length" class="text-caption text-secondary pa-2">
              Sin adjuntos subidos aún.
            </div>
          </v-list>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogAdjuntos = false">Cerrar</v-btn>
          <v-btn color="teal" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="subirAdjunto">
            Subir Archivo
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
  name: 'GestionDocumentos',
  data() {
    return {
      documentos: [],
      cargando: false,
      guardando: false,
      busqueda: '',
      filtroTipo: 'TODOS',

      headers: [
        { text: 'CITE Oficial', value: 'cite', width: '220px' },
        { text: 'Tipo', value: 'tipo_documento', width: '130px' },
        { text: 'Asunto / Creador', value: 'asunto' },
        { text: 'Estado', value: 'estado', width: '120px' },
        { text: 'Fecha Creación', value: '_fecha_creacion', width: '140px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '160px', align: 'center' },
      ],

      unidades: [],
      tiposDocumento: [],
      todosFuncionarios: [],
      rolesParticipacion: ['DESTINATARIO_A', 'REMITENTE_DE', 'VIA', 'CON_COPIA_A'],

      dialogRedactar: false,
      formDoc: {
        tipo_documento: 'MEMORANDUM',
        id_unidad_generadora: null,
        asunto: '',
        contenido_html: '',
        participantes: [],
      },

      dialogAdjuntos: false,
      docSeleccionado: null,
      archivoASubir: null,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarDocumentos();
    this.cargarDatosMaestros();
  },
  methods: {
    async cargarDocumentos() {
      this.cargando = true;
      try {
        const params = {
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
    async cargarDatosMaestros() {
      try {
        const [resOrg, resPer] = await Promise.all([
          window.axios.get('/api/rrhh/organigrama'),
          window.axios.get('/api/rrhh/personal?per_page=100'),
        ]);

        if (resOrg.data && resOrg.data.success) {
          const planas = [];
          const aplanar = (items) => {
            items.forEach(u => {
              planas.push({ id: u.id, nombre: u.nombre });
              if (u.dependencias && u.dependencias.length) aplanar(u.dependencias);
            });
          };
          aplanar(resOrg.data.data);
          this.unidades = planas;
        }

        if (resPer.data && resPer.data.success) {
          this.todosFuncionarios = resPer.data.data;
        }

        this.tiposDocumento = [
          { param_codigo: 'MEMORANDUM', param_nombre: 'Memorándum Institucional' },
          { param_codigo: 'INFORME_TECNICO', param_nombre: 'Informe Técnico Oficial' },
          { param_codigo: 'NOTA_INTERNA', param_nombre: 'Nota Interna de Coordinación' },
          { param_codigo: 'CIRCULAR', param_nombre: 'Circular General' },
          { param_codigo: 'CARTA_EXTERNA', param_nombre: 'Carta Externa' },
        ];
      } catch (e) {}
    },
    abrirModalRedactar() {
      this.formDoc = {
        tipo_documento: 'MEMORANDUM',
        id_unidad_generadora: this.unidades.length ? this.unidades[0].id : null,
        asunto: '',
        contenido_html: '<p>Mediante la presente comunicación, se pone a su conocimiento...</p>',
        participantes: [
          { tipo_participacion: 'REMITENTE_DE', id_persona: this.todosFuncionarios.length ? this.todosFuncionarios[0].id : null },
          { tipo_participacion: 'DESTINATARIO_A', id_persona: this.todosFuncionarios.length > 1 ? this.todosFuncionarios[1].id : null },
        ],
      };
      this.dialogRedactar = true;
    },
    agregarParticipante() {
      this.formDoc.participantes.push({
        tipo_participacion: 'DESTINATARIO_A',
        id_persona: this.todosFuncionarios.length ? this.todosFuncionarios[0].id : null,
      });
    },
    seleccionarPlantilla() {
      if (this.formDoc.tipo_documento === 'INFORME_TECNICO') {
        this.formDoc.contenido_html = '<h3>1. ANTECEDENTES</h3><p>...</p><h3>2. ANÁLISIS TÉCNICO</h3><p>...</p><h3>3. CONCLUSIONES</h3><p>...</p>';
      } else if (this.formDoc.tipo_documento === 'MEMORANDUM') {
        this.formDoc.contenido_html = '<p>Por medio del presente memorándum se comunica que...</p>';
      }
    },
    async guardarDocumento() {
      if (!this.formDoc.asunto || !this.formDoc.contenido_html || !this.formDoc.id_unidad_generadora) {
        this.mostrarMensaje('Completa los campos obligatorios.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/documentos', this.formDoc);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento redactado exitosamente.', 'success');
          this.dialogRedactar = false;
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al guardar documento.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    previsualizarDoc(id) {
      window.open(`/api/correspondencia/documentos/${id}/preview`, '_blank');
    },
    abrirModalAdjuntos(item) {
      this.docSeleccionado = item;
      this.archivoASubir = null;
      this.dialogAdjuntos = true;
    },
    async subirAdjunto() {
      if (!this.archivoASubir) {
        this.mostrarMensaje('Selecciona un archivo primero.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const formData = new FormData();
        formData.append('archivo', this.archivoASubir);
        const res = await window.axios.post(`/api/correspondencia/documentos/${this.docSeleccionado.id}/adjuntos`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Archivo adjuntado exitosamente.', 'success');
          this.dialogAdjuntos = false;
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al subir archivo.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async firmarDirecto(idDoc) {
      try {
        const res = await window.axios.post('/api/correspondencia/firmas/firmar', {
          id_documento: idDoc,
          pin: '1234',
          tipo_firma: 'PIN_ELECTRONICO',
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Documento firmado con éxito.', 'success');
          this.cargarDocumentos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al firmar documento.', 'error');
      }
    },
    limpiarFiltros() {
      this.busqueda = '';
      this.filtroTipo = 'TODOS';
      this.cargarDocumentos();
    },
    formatTipoDoc(t) {
      const map = { MEMORANDUM: 'Memorándum', INFORME_TECNICO: 'Informe Técnico', NOTA_INTERNA: 'Nota Interna', CIRCULAR: 'Circular', CARTA_EXTERNA: 'Carta Externa' };
      return map[t] || t;
    },
    getColorEstado(e) {
      const map = { BORRADOR: 'grey darken-1', EN_REVISION: 'orange darken-2', FIRMADO: 'green darken-2', PUBLICADO: 'blue darken-2', ANULADO: 'red darken-2' };
      return map[e] || 'grey';
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
.erp-table {
  border-radius: 8px;
}
</style>
