<template>
  <div class="aportes-instalaciones-container">
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
      <div>
        <h2 class="text-h5 font-weight-bold primary--text mb-1">
          <v-icon color="primary" class="mr-2">mdi-pipe-wrench</v-icon>
          Aportes e Instalaciones de Conexión Domiciliaria
        </h2>
        <div class="text-caption text-secondary">
          Emisión y reimpresión de contratos de pago diferido y cuotas de conexión de Agua Potable y Alcantarillado (apagua / alcanta)
        </div>
      </div>
      <div class="d-flex align-center gap-2">
        <v-btn color="primary" class="text-capitalize rounded-pill font-weight-bold" @click="abrirModalNuevo">
          <v-icon left>mdi-plus</v-icon> Nuevo Contrato de Conexión
        </v-btn>
        <v-btn icon color="primary" :loading="cargando" title="Recargar listado" @click="cargarAportes">
          <v-icon>mdi-refresh</v-icon>
        </v-btn>
      </div>
    </div>

    <!-- TARJETAS KPI RESUMEN -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Total Contratos</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ resumen.total_contratos }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Conexiones Agua</div>
          <div class="text-h4 font-weight-black blue--text text--darken-2 mt-1">{{ resumen.total_agua }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Alcantarillado</div>
          <div class="text-h4 font-weight-black teal--text text--darken-2 mt-1">{{ resumen.total_alcantarillado }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Monto Total Contratado</div>
          <div class="text-h4 font-weight-black success--text mt-1">Bs {{ formatearMonto(resumen.monto_total) }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- BARRA DE FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-4 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- Pestañas de Servicio: Todos / Agua / Alcantarillado -->
        <v-col cols="12" md="4">
          <v-btn-toggle v-model="filtroTipoServicio" mandatory dense color="primary" class="rounded-pill" @change="onCambioFiltro">
            <v-btn value="TODOS" small class="text-capitalize">Todos</v-btn>
            <v-btn value="AGUA" small class="text-capitalize">
              <v-icon x-small left>mdi-water</v-icon> Agua Potable
            </v-btn>
            <v-btn value="ALCANTARILLADO" small class="text-capitalize">
              <v-icon x-small left>mdi-pipe</v-icon> Alcantarillado
            </v-btn>
          </v-btn-toggle>
        </v-col>

        <!-- Buscador Reactivo -->
        <v-col cols="12" sm="8" md="5">
          <v-text-field
            v-model="busqueda"
            label="Buscar por Código, Socio, Factura u Orden..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarAportes"
            @click:clear="onClearBusqueda"
          ></v-text-field>
        </v-col>

        <!-- Filtro Estado Pago -->
        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filtroEstadoPago"
            :items="[
              { text: 'Todos los Pagos', value: 'TODOS' },
              { text: 'Pagados', value: 'PAGADO' },
              { text: 'Pendientes', value: 'PENDIENTE' }
            ]"
            item-text="text"
            item-value="value"
            label="Estado Pago"
            dense
            outlined
            hide-details
            @change="cargarAportes"
          ></v-select>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE CONTRATOS DE APORTES E INSTALACIONES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="columnas"
        :items="aportes"
        :loading="cargando"
        :server-items-length="totalAportes"
        :options.sync="opcionesPaginacion"
        :footer-props="{ 'items-per-page-options': [15, 25, 50, 100] }"
        class="elevation-0"
      >
        <!-- Código Socio -->
        <template v-slot:item.codigo_socio="{ item }">
          <v-chip color="primary" outlined small class="font-weight-bold">
            {{ item.codigo_socio }}
          </v-chip>
        </template>

        <!-- Tipo de Servicio -->
        <template v-slot:item.tipo_servicio="{ item }">
          <v-chip x-small :color="item.tipo_servicio === 'AGUA' ? 'blue darken-2' : 'teal darken-2'" text-color="white" class="font-weight-bold">
            <v-icon x-small left>{{ item.tipo_servicio === 'AGUA' ? 'mdi-water' : 'mdi-pipe' }}</v-icon>
            {{ item.tipo_servicio === 'AGUA' ? 'Agua Potable' : 'Alcantarillado' }}
          </v-chip>
        </template>

        <!-- Fecha -->
        <template v-slot:item.fecha="{ item }">
          <span class="text-caption font-weight-medium">
            {{ item.fecha ? item.fecha.slice(0, 10) : '-' }}
          </span>
        </template>

        <!-- Aporte Bs -->
        <template v-slot:item.aporte="{ item }">
          <div class="text-right">
            Bs {{ parseFloat(item.aporte).toFixed(2) }}
          </div>
        </template>

        <!-- Instalación Bs -->
        <template v-slot:item.instalacion="{ item }">
          <div class="text-right">
            Bs {{ parseFloat(item.instalacion).toFixed(2) }}
          </div>
        </template>

        <!-- Total Bs -->
        <template v-slot:item.total="{ item }">
          <div class="text-right font-weight-bold primary--text">
            Bs {{ parseFloat(item.total).toFixed(2) }}
          </div>
        </template>

        <!-- Plazo -->
        <template v-slot:item.plazo="{ item }">
          <div class="text-center font-weight-medium">
            {{ item.plazo }} mes(es)
          </div>
        </template>

        <!-- Estado Pago -->
        <template v-slot:item.pagado="{ item }">
          <v-chip x-small :color="item.pagado ? 'success' : 'warning'" text-color="white" class="font-weight-medium">
            {{ item.pagado ? 'PAGADO' : 'PENDIENTE' }}
          </v-chip>
        </template>

        <!-- Factura -->
        <template v-slot:item.factura="{ item }">
          <span v-if="item.factura" class="text-caption font-weight-bold grey--text text--darken-3">
            #{{ item.factura }}
          </span>
          <span v-else class="grey--text">-</span>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <v-btn
              small
              color="primary"
              outlined
              class="text-capitalize rounded-pill font-weight-bold"
              title="Reimprimir Contrato de Pago Diferido (apagua.frx)"
              @click="reimprimirContrato(item)"
            >
              <v-icon small left>mdi-file-document-outline</v-icon> Contrato PDF
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO NUEVO CONTRATO DE CONEXIÓN DOMICILIARIA -->
    <v-dialog v-model="modalNuevo" max-width="650" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3 d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">mdi-file-sign</v-icon>
            <span class="text-subtitle-1 font-weight-bold">Nuevo Contrato de Conexión Domiciliaria</span>
          </div>
          <v-btn icon color="white" small @click="modalNuevo = false">
            <v-icon small>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form ref="formNuevoRef" v-model="formValido">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formContrato.codigo_socio"
                  label="Código de Socio *"
                  placeholder="Ej: 05595"
                  dense
                  outlined
                  required
                  @blur="buscarDatosSocio"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="formContrato.tipo_servicio"
                  :items="[
                    { text: 'Conexión de Agua Potable', value: 'AGUA' },
                    { text: 'Instalación de Alcantarillado', value: 'ALCANTARILLADO' }
                  ]"
                  item-text="text"
                  item-value="value"
                  label="Tipo de Servicio *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="formContrato.nombre_socio"
                  label="Nombre Completo del Socio *"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formContrato.fecha"
                  label="Fecha de Suscripción *"
                  type="date"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formContrato.plazo"
                  label="Plazo en Meses *"
                  type="number"
                  min="1"
                  max="36"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model.number="formContrato.aporte"
                  label="Monto Aporte (Bs) *"
                  type="number"
                  step="0.01"
                  dense
                  outlined
                  required
                  @input="recalcularTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model.number="formContrato.instalacion"
                  label="Costo Instalación (Bs) *"
                  type="number"
                  step="0.01"
                  dense
                  outlined
                  required
                  @input="recalcularTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="4">
                <v-text-field
                  :value="(parseFloat(formContrato.aporte || 0) + parseFloat(formContrato.instalacion || 0)).toFixed(2)"
                  label="Total Contratado (Bs)"
                  dense
                  outlined
                  readonly
                  background-color="grey lighten-4"
                  class="font-weight-bold"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="formContrato.observaciones"
                  label="Observaciones o Notas del Trámite"
                  rows="2"
                  dense
                  outlined
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardando" class="font-weight-bold" @click="guardarNuevoContrato">
            Registrar y Emitir Contrato
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE PDF (CONTRATO DE PAGO DIFERIDO) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'AportesInstalaciones',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      cargando: false,
      guardando: false,
      busqueda: '',
      filtroTipoServicio: 'TODOS',
      filtroEstadoPago: 'TODOS',
      aportes: [],
      totalAportes: 0,
      opcionesPaginacion: {},
      resumen: {
        total_contratos: 0,
        total_agua: 0,
        total_alcantarillado: 0,
        monto_total: 0,
        total_pagados: 0,
        total_pendientes: 0,
      },
      columnas: [
        { text: 'Código', value: 'codigo_socio', width: '90px' },
        { text: 'Abonado / Solicitante', value: 'nombre_socio' },
        { text: 'Servicio', value: 'tipo_servicio', width: '130px' },
        { text: 'Periodo', value: 'periodo', width: '90px' },
        { text: 'Fecha', value: 'fecha', width: '100px' },
        { text: 'Aporte', value: 'aporte', align: 'end', width: '100px' },
        { text: 'Instalación', value: 'instalacion', align: 'end', width: '110px' },
        { text: 'Total', value: 'total', align: 'end', width: '110px' },
        { text: 'Plazo', value: 'plazo', align: 'center', width: '80px' },
        { text: 'Estado Pago', value: 'pagado', align: 'center', width: '100px' },
        { text: 'Factura', value: 'factura', align: 'center', width: '90px' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '140px' },
      ],
      modalNuevo: false,
      formValido: true,
      formContrato: {
        codigo_socio: '',
        nombre_socio: '',
        tipo_servicio: 'AGUA',
        fecha: new Date().toISOString().slice(0, 10),
        plazo: 1,
        aporte: 294.90,
        instalacion: 1592.10,
        observaciones: '',
      },
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
    };
  },
  watch: {
    opcionesPaginacion: {
      handler() {
        this.cargarAportes();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarAportes();
  },
  methods: {
    formatearMonto(val) {
      return parseFloat(val || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    onCambioFiltro() {
      this.opcionesPaginacion.page = 1;
      this.cargarAportes();
    },
    onClearBusqueda() {
      this.busqueda = '';
      this.cargarAportes();
    },
    async cargarAportes() {
      this.cargando = true;
      const { page, itemsPerPage } = this.opcionesPaginacion;
      try {
        const res = await axios.get('/api/comercial/aportes', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            search: this.busqueda || undefined,
            tipo_servicio: this.filtroTipoServicio !== 'TODOS' ? this.filtroTipoServicio : undefined,
            estado_pago: this.filtroEstadoPago !== 'TODOS' ? this.filtroEstadoPago : undefined,
          },
        });

        this.aportes = res.data.data || [];
        this.totalAportes = res.data.total || 0;
        if (res.data.resumen) {
          this.resumen = res.data.resumen;
        }
      } catch (e) {
        console.error('Error cargando aportes:', e);
      } finally {
        this.cargando = false;
      }
    },
    reimprimirContrato(item) {
      if (!item) return;
      const esAgua = (item.tipo_servicio || 'AGUA').toUpperCase() === 'AGUA';
      this.urlVisorPdf = `/api/comercial/aportes/${item.id}/contrato-pdf`;
      this.tituloVisorPdf = esAgua
        ? `Contrato de Pago Diferido de Conexión Domiciliaria - Socio #${item.codigo_socio}`
        : `Cronograma de Pagos / Instalación Alcantarillado - Socio #${item.codigo_socio}`;
      this.subtituloVisorPdf = `${item.nombre_socio || ''} | Total: Bs ${parseFloat(item.total).toFixed(2)}`;
      this.mostrarVisorPdf = true;
    },
    abrirModalNuevo() {
      this.formContrato = {
        codigo_socio: '',
        nombre_socio: '',
        tipo_servicio: 'AGUA',
        fecha: new Date().toISOString().slice(0, 10),
        plazo: 1,
        aporte: 294.90,
        instalacion: 1592.10,
        observaciones: '',
      };
      this.modalNuevo = true;
    },
    async buscarDatosSocio() {
      if (!this.formContrato.codigo_socio) return;
      try {
        const cod = this.formContrato.codigo_socio.trim();
        const res = await axios.get('/api/comercial/abonados', {
          params: { search: cod, tipo_busqueda: 'codigo_abonado', per_page: 1 }
        });
        if (res.data.data && res.data.data.length > 0) {
          const ab = res.data.data[0];
          this.formContrato.nombre_socio = ab.nombre_completo;
        }
      } catch (e) {
        // Silencioso
      }
    },
    recalcularTotal() {
      // Reactivo
    },
    async guardarNuevoContrato() {
      if (!this.formContrato.codigo_socio || !this.formContrato.nombre_socio) {
        alert('Por favor complete los campos obligatorios (*).');
        return;
      }

      this.guardando = true;
      try {
        const res = await axios.post('/api/comercial/aportes', this.formContrato);
        this.modalNuevo = false;
        this.cargarAportes();
        // Abrir inmediatamente el contrato para impresión
        if (res.data.data && res.data.data.id) {
          this.reimprimirContrato(res.data.data);
        }
      } catch (e) {
        alert(e.response?.data?.message || 'Error al registrar el contrato de conexión.');
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
</style>
