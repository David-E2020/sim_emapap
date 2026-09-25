<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-table-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Bandeja de Facturas Emitidas</h2>
            <span class="text-caption text-secondary">Registro, visualización, impresión y anulación de documentos fiscales oficiales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            outlined
            color="primary"
            class="text-capitalize font-weight-medium rounded-pill elevation-1"
            :loading="sincronizandoReloj"
            @click="sincronizarRelojSiat"
          >
            <v-icon left small>mdi-clock-check-outline</v-icon> Sincronizar Hora SIAT
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" :to="{ name: 'facturacion_crear' }">
            <v-icon left small>mdi-plus-circle</v-icon> + Nueva Factura
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- FILTROS Y BÚSQUEDA -->
    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- Criterio / Tipo de búsqueda -->
        <v-col cols="12" sm="5" md="3">
          <v-select
            v-model="tipoBusqueda"
            :items="tiposBusqueda"
            item-text="texto"
            item-value="valor"
            label="Buscar por..."
            prepend-inner-icon="mdi-format-list-bulleted-type"
            dense
            outlined
            hide-details
            @change="cargarFacturas"
          ></v-select>
        </v-col>

        <!-- Campo de texto de búsqueda -->
        <v-col cols="12" sm="7" md="4">
          <v-text-field
            v-model="busqueda"
            :label="etiquetaBusqueda"
            :placeholder="placeholderBusqueda"
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarFacturas"
            @click:clear="cargarFacturas"
          ></v-text-field>
        </v-col>

        <!-- Estado Fiscal -->
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroEstado"
            :items="['TODOS', 'VALIDADA', 'ANULADA', 'OBSERVADA', 'CONTINGENCIA']"
            label="Estado Fiscal"
            dense
            outlined
            hide-details
            @change="cargarFacturas"
          ></v-select>
        </v-col>

        <!-- Fecha Emisión -->
        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtroFecha"
            label="Fecha Emisión"
            type="date"
            dense
            outlined
            hide-details
            clearable
            @change="cargarFacturas"
          ></v-text-field>
        </v-col>

        <!-- Botón Filtrar -->
        <v-col cols="12" md="1">
          <v-btn color="primary" dense block height="40" class="elevation-1" @click="cargarFacturas" title="Buscar">
            <v-icon>mdi-magnify</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE FACTURAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="headers"
        :items="facturas"
        :loading="cargando"
        :server-items-length="totalFacturas"
        :options.sync="opciones"
        class="elevation-0"
        no-data-text="No se encontraron facturas emitidas"
      >
        <!-- N° Factura -->
        <template v-slot:item.numero_factura="{ item }">
          <span class="font-weight-bold text-primary">N° {{ item.numero_factura }}</span>
        </template>

        <!-- Fecha Emisión -->
        <template v-slot:item.fecha_emision="{ item }">
          <span class="text-caption">{{ formatearFecha(item.fecha_emision) }}</span>
        </template>

        <!-- Cliente / NIT / Abonado -->
        <template v-slot:item.nombre_razon_social="{ item }">
          <div class="font-weight-medium">{{ item.nombre_razon_social }}</div>
          <div class="d-flex align-center gap-1 text-caption text-secondary flex-wrap mt-1">
            <span>Doc: {{ item.numero_documento }} {{ item.complemento ? '-' + item.complemento : '' }}</span>
            <v-chip v-if="item.abonado" x-small outlined color="primary" class="font-weight-bold ml-1">
              <v-icon x-small left>mdi-water</v-icon>
              Abonado #{{ item.abonado.codigo }}
            </v-chip>
          </div>
        </template>

        <!-- Monto Total -->
        <template v-slot:item.monto_total="{ item }">
          <span class="font-weight-bold">Bs {{ parseFloat(item.monto_total).toFixed(2) }}</span>
        </template>

        <!-- Estado Fiscal -->
        <template v-slot:item.estado_factura="{ item }">
          <v-chip
            :color="colorEstado(item.estado_factura)"
            text-color="white"
            x-small
            class="font-weight-bold text-uppercase"
          >
            {{ item.estado_factura }}
          </v-chip>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-end gap-1">
            <!-- Imprimir Carta / Rollo 80mm -->
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on: tooltipOn }">
                    <v-btn icon small color="primary" v-bind="attrs" v-on="{ ...on, ...tooltipOn }">
                      <v-icon small>mdi-printer</v-icon>
                    </v-btn>
                  </template>
                  <span>Imprimir / Descargar PDF</span>
                </v-tooltip>
              </template>
              <v-list dense>
                <v-list-item @click="imprimirPdf(item, 'carta')">
                  <v-list-item-icon class="mr-2">
                    <v-icon small color="primary">mdi-file-document-outline</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Imprimir Formato Carta (Oficial)</v-list-item-title>
                </v-list-item>
                <v-list-item @click="imprimirPdf(item, 'rollo')">
                  <v-list-item-icon class="mr-2">
                    <v-icon small color="orange darken-2">mdi-receipt-text-outline</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Imprimir Rollo Térmico (80mm POS)</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>

            <!-- Descargar XML -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="secondary" v-bind="attrs" v-on="on" @click="descargarXml(item.id)">
                  <v-icon small>mdi-xml</v-icon>
                </v-btn>
              </template>
              <span>Descargar XML Firmado</span>
            </v-tooltip>

            <!-- Reenviar Factura a SIAT (Regularizar Contingencia) -->
            <v-tooltip bottom v-if="item.estado_factura === 'CONTINGENCIA'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="amber darken-3"
                  v-bind="attrs"
                  v-on="on"
                  :loading="reenviandoId === item.id"
                  @click="reenviarFacturaAlSiat(item)"
                >
                  <v-icon small>mdi-cloud-upload-outline</v-icon>
                </v-btn>
              </template>
              <span>Reenviar a SIAT (Regularizar Contingencia)</span>
            </v-tooltip>

            <!-- Verificar Estado en Línea con SIAT -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="primary"
                  v-bind="attrs"
                  v-on="on"
                  :loading="verificandoId === item.id"
                  @click="verificarEstadoEnSin(item)"
                >
                  <v-icon small>mdi-cloud-sync-outline</v-icon>
                </v-btn>
              </template>
              <span>Verificar Estado en Línea (SIAT)</span>
            </v-tooltip>

            <!-- Consultar Directamente en Portal SIAT Oficial (Impuestos) -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal darken-1"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirPortalSiatOficial(item)"
                >
                  <v-icon small>mdi-qrcode-scan</v-icon>
                </v-btn>
              </template>
              <span>Verificar en Portal SIAT Oficial (Impuestos)</span>
            </v-tooltip>

            <!-- Reenviar por Correo -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="info" v-bind="attrs" v-on="on" @click="abrirDialogoCorreo(item)">
                  <v-icon small>mdi-email-fast-outline</v-icon>
                </v-btn>
              </template>
              <span>Enviar Factura por Correo</span>
            </v-tooltip>

            <!-- Anular Factura Ordinaria o Administrativa -->
            <v-tooltip bottom v-if="item.estado_factura !== 'ANULADA' && item.estado_factura !== 'CANCELLED'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="abrirDialogoAnulacion(item)">
                  <v-icon small>mdi-cancel</v-icon>
                </v-btn>
              </template>
              <span>Anular Factura ante el SIN</span>
            </v-tooltip>

            <!-- Revertir Anulación (Restaurar a VALIDADA) -->
            <v-tooltip bottom v-if="item.estado_factura === 'ANULADA' || item.estado_factura === 'CANCELLED'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal"
                  v-bind="attrs"
                  v-on="on"
                  :loading="revertiendoId === item.id"
                  @click="confirmarReversionAnulacion(item)"
                >
                  <v-icon small>mdi-restore-alert</v-icon>
                </v-btn>
              </template>
              <span>Revertir Anulación (Restaurar en SIAT)</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO DE ANULACIÓN -->
    <v-dialog v-model="dialogoAnular" max-width="480">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="error" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-alert</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Anular Factura Fiscal</h3>
        </div>

        <p class="text-body-2 text-secondary mb-3">
          Está a punto de anular la <strong>Factura N° {{ facturaSeleccionada.numero_factura }}</strong> emitida a nombre de <strong>{{ facturaSeleccionada.nombre_razon_social }}</strong> por Bs {{ parseFloat(facturaSeleccionada.monto_total || 0).toFixed(2) }}.
        </p>

        <v-select
          v-model="motivoAnulacionSeleccionado"
          :items="motivosAnulacion"
          item-text="descripcion"
          item-value="codigo"
          label="Motivo de Anulación Oficial del SIN *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <!-- Conmutador para Anulación Administrativa Fuera de Plazo (RND 102600000025) -->
        <v-switch
          v-model="esAnulacionAdministrativa"
          label="Anulación Administrativa Fuera de Plazo (RND 102600000025)"
          color="warning"
          dense
          class="mt-1 mb-2"
        ></v-switch>

        <div v-if="esAnulacionAdministrativa" class="pa-3 mb-3 amber lighten-5 rounded-lg border-amber">
          <div class="text-caption font-weight-bold amber--text text--darken-4 mb-2">
            <v-icon x-small color="amber darken-4">mdi-gavel</v-icon> Respaldo RND 102600000025 del SIN:
          </div>
          <v-text-field
            v-model="nroResolucion"
            label="N° Resolución Administrativa *"
            placeholder="Ej. RA-GDLPZ-2026-0045"
            dense
            outlined
            class="mb-2"
          ></v-text-field>
          <v-text-field
            v-model="fechaResolucion"
            type="date"
            label="Fecha de Resolución Administrativa *"
            dense
            outlined
          ></v-text-field>
        </div>

        <div class="d-flex justify-end gap-2">
          <v-btn text @click="dialogoAnular = false">Cancelar</v-btn>
          <v-btn
            color="error"
            :loading="anulando"
            :disabled="!motivoAnulacionSeleccionado || (esAnulacionAdministrativa && (!nroResolucion || !fechaResolucion))"
            @click="confirmarAnulacion"
          >
            {{ esAnulacionAdministrativa ? 'Confirmar Anulación R.A.' : 'Confirmar Anulación' }}
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DE ENVÍO POR CORREO -->
    <v-dialog v-model="dialogoCorreo" max-width="480">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-email-outline</v-icon>
          </v-avatar>
          <div class="text-h6 font-weight-bold">Enviar Factura por Correo</div>
        </div>

        <p class="text-body-2 text-secondary mb-3">
          Se enviará la Factura <strong>N° {{ facturaSeleccionada.numero_factura }}</strong> con sus adjuntos oficiales en formato PDF y XML firmado.
        </p>

        <v-text-field
          v-model="correoDestino"
          label="Correo Electrónico del Abonado"
          outlined
          dense
          placeholder="ejemplo@correo.bo"
          prepend-inner-icon="mdi-email"
        ></v-text-field>

        <v-card-actions class="px-0 pb-0">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogoCorreo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="enviandoCorreo" @click="confirmarEnvioCorreo">
            <v-icon left small>mdi-send</v-icon> Enviar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE FACTURA SIAT (CARTA / ROLLO 80MM) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :mostrar-selector-formato="true"
      :formato-inicial="formatoVisor"
      @cambio-formato="alCambiarFormatoPdf"
    ></modal-visor-pdf>

    <!-- NOTIFICACIÓN NATIVA DEL SISTEMA (SNACKBAR) -->
    <v-snackbar
      v-model="snackbar.status"
      :color="snackbar.color"
      :timeout="4500"
      top
      right
      rounded="pill"
      elevation="6"
    >
      <div class="d-flex align-center">
        <v-icon dark left class="mr-2">{{ snackbar.icon || 'mdi-information' }}</v-icon>
        <span class="font-weight-medium">{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'BandejaFacturas',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      facturaVisorId: null,
      formatoVisor: 'carta',
      cargando: false,
      verificandoId: null,
      reenviandoId: null,
      sincronizandoReloj: false,
      dialogoAnular: false,
      dialogoCorreo: false,
      anulando: false,
      enviandoCorreo: false,
      correoDestino: '',
      busqueda: '',
      tipoBusqueda: 'todos',
      tiposBusqueda: [
        { texto: 'Todos los campos', valor: 'todos' },
        { texto: 'N° de Factura', valor: 'numero_factura' },
        { texto: 'C.I. / NIT', valor: 'carnet_nit' },
        { texto: 'Código de Abonado', valor: 'codigo_abonado' },
        { texto: 'Razón Social / Nombre', valor: 'cliente' },
      ],
      filtroEstado: 'TODOS',
      filtroFecha: null,
      totalFacturas: 0,
      opciones: {},
      facturas: [],
      facturaSeleccionada: {},
      motivoAnulacionSeleccionado: 1,
      esAnulacionAdministrativa: false,
      nroResolucion: '',
      fechaResolucion: '',
      revertiendoId: null,
      motivosAnulacion: [
        { codigo: 1, descripcion: 'FACTURA MAL EMITIDA' },
        { codigo: 2, descripcion: 'DATOS DE EMISIÓN INCORRECTOS' },
        { codigo: 3, descripcion: 'FACTURA O NOTA FISCAL DEVUELTA' },
        { codigo: 5, descripcion: 'AUTORIZACIÓN ADMINISTRATIVA / R.A.' },
      ],
      headers: [
        { text: 'N° Factura', value: 'numero_factura', width: '120px' },
        { text: 'Fecha Emisión', value: 'fecha_emision', width: '150px' },
        { text: 'Razón Social / Cliente', value: 'nombre_razon_social' },
        { text: 'Total', value: 'monto_total', align: 'right', width: '120px' },
        { text: 'Estado SIN', value: 'estado_factura', align: 'center', width: '130px' },
        { text: 'Acciones', value: 'acciones', align: 'right', sortable: false, width: '130px' },
      ],
      snackbar: {
        status: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle',
      },
    };
  },
  computed: {
    etiquetaBusqueda() {
      switch (this.tipoBusqueda) {
        case 'numero_factura': return 'Número de Factura';
        case 'carnet_nit': return 'C.I. / NIT del Cliente';
        case 'codigo_abonado': return 'Código de Abonado';
        case 'cliente': return 'Razón Social / Nombre';
        default: return 'Buscar factura...';
      }
    },
    placeholderBusqueda() {
      switch (this.tipoBusqueda) {
        case 'numero_factura': return 'Ej. 1045, 23010...';
        case 'carnet_nit': return 'Ej. 4582910, 10239401...';
        case 'codigo_abonado': return 'Ej. 00001, 5105...';
        case 'cliente': return 'Ej. Juan Pérez, Empresa...';
        default: return 'N° Factura, Carnet, Código o Nombre...';
      }
    },
  },
  watch: {
    opciones: {
      handler() {
        this.cargarFacturas();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarFacturas();
  },
  methods: {
    async cargarFacturas() {
      this.cargando = true;
      try {
        const { page, itemsPerPage } = this.opciones;
        const params = {
          page: page || 1,
          per_page: itemsPerPage || 15,
          search: this.busqueda || '',
          tipo_busqueda: this.tipoBusqueda || 'todos',
          estado: this.filtroEstado === 'TODOS' ? '' : this.filtroEstado,
          fecha_inicio: this.filtroFecha || '',
          fecha_fin: this.filtroFecha || '',
        };

        const res = await window.axios.get('/api/facturacion/facturas', { params });
        this.facturas = res.data.data;
        this.totalFacturas = res.data.total;
      } catch (e) {
        console.error('Error al cargar facturas', e);
      } finally {
        this.cargando = false;
      }
    },
    formatearFecha(fechaStr) {
      if (!fechaStr) return '-';
      const f = new Date(fechaStr);
      return f.toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' });
    },
    colorEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
        case 'VALIDATED':
          return 'success';
        case 'ANULADA':
        case 'CANCELLED':
          return 'error';
        case 'CONTINGENCIA':
        case 'CONTINGENCY':
        case 'OFFLINE':
          return 'warning';
        case 'PENDIENTE':
        case 'PENDING':
          return 'info';
        case 'RECHAZADA':
        case 'REJECTED':
        case 'OBSERVADA':
          return 'deep-orange';
        default:
          return 'grey';
      }
    },
    mostrarNotificacion(texto, color = 'success', icon = 'mdi-check-circle') {
      this.snackbar = {
        status: true,
        text: texto,
        color: color,
        icon: icon,
      };
    },
    imprimirPdf(item, formato = 'carta') {
      const id = typeof item === 'object' ? item.id : item;
      const numFactura = (typeof item === 'object' && item.numero_factura) ? item.numero_factura : id;
      this.facturaVisorId = id;
      this.formatoVisor = formato;
      this.urlVisorPdf = `/api/facturacion/facturas/${id}/pdf?formato=${formato}`;
      this.tituloVisorPdf = `Factura Electrónica SIAT N° ${numFactura}`;
      this.subtituloVisorPdf = 'Visualización e Impresión de Documento Fiscal Autorizado';
      this.mostrarVisorPdf = true;
    },
    alCambiarFormatoPdf(nuevoFormato) {
      if (this.facturaVisorId) {
        this.urlVisorPdf = `/api/facturacion/facturas/${this.facturaVisorId}/pdf?formato=${nuevoFormato}`;
      }
    },
    async verificarEstadoEnSin(factura) {
      this.verificandoId = factura.id;
      try {
        const res = await window.axios.get(`/api/facturacion/facturas/${factura.id}/verificar-estado-sin`);
        if (res.data && res.data.success) {
          const sinDesc = (res.data.siat && res.data.siat.codigoDescripcion) || res.data.estado_local;
          this.mostrarNotificacion(`SIAT en Línea: Factura N° ${factura.numero_factura} ${sinDesc}`, 'success', 'mdi-check-decagram');
          this.cargarFacturas();
        } else {
          const msg = (res.data && res.data.message) || 'El SIN no pudo validar el estado de la factura.';
          this.mostrarNotificacion(`SIAT: ${msg}`, 'warning', 'mdi-alert');
          this.cargarFacturas();
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error de comunicación con SIAT: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.verificandoId = null;
      }
    },
    async reenviarFacturaAlSiat(factura) {
      this.reenviandoId = factura.id;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${factura.id}/enviar-siat`);
        if (res.data && res.data.success) {
          this.mostrarNotificacion(
            `SIAT: Factura N° ${factura.numero_factura} enviada y VALIDADA exitosamente ante el SIN.`,
            'success',
            'mdi-check-decagram'
          );
          this.cargarFacturas();
        } else {
          const msg = (res.data && res.data.message) || 'No se pudo regularizar la factura ante el SIN.';
          this.mostrarNotificacion(`SIAT: ${msg}`, 'warning', 'mdi-alert');
          this.cargarFacturas();
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error al reenviar a SIAT: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.reenviandoId = null;
      }
    },
    abrirPortalSiatOficial(item) {
      const nit = '1002393029';
      const cuf = item.cuf;
      const numero = item.numero_factura;
      const url = `https://siat.impuestos.gob.bo/consulta/QR?nit=${nit}&cuf=${cuf}&numero=${numero}&t=2`;
      window.open(url, '_blank');
    },
    async sincronizarRelojSiat() {
      this.sincronizandoReloj = true;
      try {
        const res = await window.axios.get('/api/facturacion/siat/sincronizar-hora');
        if (res.data && res.data.success) {
          const dif = res.data.diferencia_segundos !== undefined ? ` (Desfase: ${res.data.diferencia_segundos}s)` : '';
          this.mostrarNotificacion(`Hora oficial SIN: ${res.data.fecha_hora_sin || res.data.fecha_hora}${dif}`, 'success', 'mdi-clock-check');
        } else {
          const msg = (res.data && res.data.message) || 'Error al sincronizar reloj con SIAT';
          this.mostrarNotificacion(msg, 'warning', 'mdi-clock-alert');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error al sincronizar: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.sincronizandoReloj = false;
      }
    },
    descargarXml(id) {
      const link = document.createElement('a');
      link.href = `/api/facturacion/facturas/${id}/xml`;
      link.download = `factura_${id}.xml`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    abrirDialogoAnulacion(factura) {
      this.facturaSeleccionada = factura;
      this.motivoAnulacionSeleccionado = 1;
      this.esAnulacionAdministrativa = false;
      this.nroResolucion = '';
      this.fechaResolucion = '';
      this.dialogoAnular = true;
    },
    async confirmarAnulacion() {
      this.anulando = true;
      try {
        let res;
        if (this.esAnulacionAdministrativa) {
          res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/anular-administrativa`, {
            codigo_motivo: this.motivoAnulacionSeleccionado,
            nro_resolucion: this.nroResolucion,
            fecha_resolucion: this.fechaResolucion,
          });
        } else {
          res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/anular`, {
            codigo_motivo: this.motivoAnulacionSeleccionado,
          });
        }

        if (res.data.success) {
          const msg = res.data.message || 'Factura anulada satisfactoriamente.';
          this.mostrarNotificacion(msg, 'success', 'mdi-check-circle');
          this.dialogoAnular = false;
          this.cargarFacturas();
        } else {
          const msg = res.data.message || 'Error al anular la factura';
          this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
      } finally {
        this.anulando = false;
      }
    },
    async confirmarReversionAnulacion(factura) {
      this.revertiendoId = factura.id;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${factura.id}/revertir-anulacion`);
        if (res.data.success) {
          const msg = res.data.message || 'Factura restituida a estado VALIDADA.';
          this.mostrarNotificacion(msg, 'success', 'mdi-check-circle');
          this.cargarFacturas();
        } else {
          this.mostrarNotificacion(res.data.message || 'Error al revertir anulación.', 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        this.mostrarNotificacion(e.response?.data?.message || 'Error al procesar la reversión.', 'error', 'mdi-alert-circle');
      } finally {
        this.revertiendoId = null;
      }
    },
    abrirDialogoCorreo(factura) {
      this.facturaSeleccionada = factura;
      this.correoDestino = (factura.cliente && factura.cliente.correo_electronico) || '';
      this.dialogoCorreo = true;
    },
    async confirmarEnvioCorreo() {
      if (!this.correoDestino) {
        this.mostrarNotificacion('Ingrese un correo electrónico válido', 'warning', 'mdi-alert');
        return;
      }
      this.enviandoCorreo = true;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/enviar-correo`, {
          correo_electronico: this.correoDestino,
        });
        if (res.data && res.data.success) {
          this.mostrarNotificacion('Factura fiscal enviada exitosamente por correo.', 'success', 'mdi-email-check');
          this.dialogoCorreo = false;
        } else {
          this.mostrarNotificacion(res.data.message || 'Error al enviar correo', 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
      } finally {
        this.enviandoCorreo = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
  border: 1px solid rgba(0, 0, 0, 0.06) !important;
}
.theme--dark .erp-card-elevated {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
