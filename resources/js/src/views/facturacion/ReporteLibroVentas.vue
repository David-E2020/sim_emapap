<template>
  <div class="reporte-libro-ventas">
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-book-open-page-variant</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0 text--primary">Libro de Ventas IVA (SIAT)</h2>
            <span class="text-caption text-secondary">
              Registro Oficial de Ventas y Débito Fiscal para EMAPAP Patacamaya (Servicio de Impuestos Nacionales)
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 my-1 flex-wrap">
          <v-btn
            color="primary"
            dark
            outlined
            small
            class="rounded-pill font-weight-medium"
            @click="imprimirReporte"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir Libro
          </v-btn>
          <v-btn
            color="success darken-1"
            dark
            small
            class="white--text font-weight-bold rounded-pill elevation-1"
            :loading="cargandoCsv"
            @click="descargarCsv"
          >
            <v-icon left small>mdi-file-delimited</v-icon> Exportar CSV (Normativa SIN)
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- FILTROS AVANZADOS Y PERÍODO RÁPIDO (ESTÁNDAR REPORTES) -->
    <v-card class="mb-4 pa-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
        <div class="d-flex align-center flex-wrap gap-2">
          <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
          <v-btn-toggle v-model="periodoTipo" mandatory dense color="primary" @change="cambiarPeriodoRapido">
            <v-btn small value="hoy" class="text-capitalize">Hoy</v-btn>
            <v-btn small value="semana" class="text-capitalize">Esta Semana</v-btn>
            <v-btn small value="mes" class="text-capitalize">Este Mes</v-btn>
            <v-btn small value="mes_anterior" class="text-capitalize">Mes Anterior</v-btn>
            <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
          </v-btn-toggle>
        </div>

        <div class="text-caption font-weight-medium text-secondary">
          Rango: {{ formatearFecha(filtros.fecha_desde) }} al {{ formatearFecha(filtros.fecha_hasta) }}
        </div>
      </div>

      <v-divider class="mb-3"></v-divider>

      <v-row dense align="center">
        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtros.fecha_desde"
            label="Fecha Desde"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-start"
            @change="onFechaManualChange"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtros.fecha_hasta"
            label="Fecha Hasta"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-end"
            @change="onFechaManualChange"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtros.estado"
            :items="estadosList"
            label="Estado Fiscal SIN"
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-filter"
            @change="consultarReporte"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtros.tipo_emision"
            :items="tiposEmisionList"
            label="Modalidad de Emisión"
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-cloud-sync-outline"
            @change="consultarReporte"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="12" md="2" class="d-flex justify-end gap-2">
          <v-btn
            color="primary"
            dark
            class="rounded-pill w-100 font-weight-bold"
            :loading="cargando"
            @click="consultarReporte"
          >
            <v-icon left small>mdi-filter-check</v-icon> Consultar
          </v-btn>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                outlined
                color="secondary"
                icon
                v-bind="attrs"
                v-on="on"
                @click="limpiarFiltros"
              >
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Restablecer Filtros</span>
          </v-tooltip>
        </v-col>
      </v-row>
    </v-card>

    <!-- TARJETAS DE RESUMEN FISCAL (KPIS ESTANDARIZADOS) -->
    <v-row class="mb-4" dense>
      <!-- TOTAL FACTURADO -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Facturado</div>
          <div class="text-h4 font-weight-black primary--text mt-1">
            Bs {{ formatoMoneda(resumen.total_facturado) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            {{ resumen.total_registros }} facturas computadas
          </div>
        </v-card>
      </v-col>

      <!-- BASE DÉBITO FISCAL -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Base Débito Fiscal</div>
          <div class="text-h4 font-weight-black success--text mt-1">
            Bs {{ formatoMoneda(resumen.total_base_debito_fiscal) }}
          </div>
          <div class="text-caption text-success mt-1 font-weight-medium">
            Importe sujeto al IVA (13%)
          </div>
        </v-card>
      </v-col>

      <!-- DÉBITO FISCAL IVA (13%) -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Débito Fiscal IVA (13%)</div>
          <div class="text-h4 font-weight-black info--text mt-1">
            Bs {{ formatoMoneda(resumen.debito_fiscal_iva) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            Monto a declarar en Form. 200
          </div>
        </v-card>
      </v-col>

      <!-- RESUMEN DE ESTADOS FISCALES -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase text-center mb-1">
            Estados Fiscales SIN
          </div>
          <div class="d-flex flex-column gap-1">
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium success--text">
                <v-icon x-small color="success">mdi-check-circle</v-icon> Válidas en Línea:
              </span>
              <v-chip x-small color="success" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_validas || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium warning--text text--darken-2">
                <v-icon x-small color="warning darken-2">mdi-cloud-off-outline</v-icon> Contingencias:
              </span>
              <v-chip x-small color="warning darken-1" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_contingencias || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium error--text">
                <v-icon x-small color="error">mdi-cancel</v-icon> Anuladas:
              </span>
              <v-chip x-small color="error" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_anuladas || 0 }}
              </v-chip>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA DETALLADA DEL LIBRO DE VENTAS CON TOTALES (TFOOT) -->
    <v-card class="erp-card-elevated" rounded="lg">
      <v-card-title class="py-3 px-4 d-flex justify-space-between align-center">
        <div class="font-weight-bold text-subtitle-1">
          <v-icon left color="primary">mdi-table</v-icon> Detalle de Facturas Registradas
        </div>
        <div class="d-flex align-center gap-2">
          <v-chip small color="primary" outlined class="font-weight-bold">
            {{ facturas.length }} facturas computadas
          </v-chip>
          <v-chip small color="warning darken-1" text-color="white" class="font-weight-bold" v-if="resumen.cantidad_contingencias > 0">
            {{ resumen.cantidad_contingencias }} en Contingencia
          </v-chip>
        </div>
      </v-card-title>
      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="facturas"
        :loading="cargando"
        :items-per-page="25"
        class="erp-table"
        dense
        no-data-text="No se encontraron facturas emitidas en el periodo seleccionado"
      >
        <template v-slot:item.numero_factura="{ item }">
          <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
        </template>

        <template v-slot:item.fecha_emision="{ item }">
          <span class="text-caption font-weight-medium">{{ formatearFechaHora(item.fecha_emision) }}</span>
        </template>

        <template v-slot:item.documento="{ item }">
          <div>
            <strong class="text-body-2">{{ item.numero_documento }}</strong>
            <span v-if="item.complemento" class="grey--text"> - {{ item.complemento }}</span>
          </div>
        </template>

        <template v-slot:item.tipo_emision="{ item }">
          <v-chip
            x-small
            :color="esContingencia(item) ? 'warning darken-1' : 'info'"
            text-color="white"
            class="font-weight-bold"
          >
            <v-icon left x-small>{{ esContingencia(item) ? 'mdi-cloud-off-outline' : 'mdi-cloud-check-outline' }}</v-icon>
            {{ esContingencia(item) ? 'CONTINGENCIA' : 'EN LÍNEA' }}
          </v-chip>
        </template>

        <template v-slot:item.cuf="{ item }">
          <div class="d-flex align-center">
            <span class="text-caption font-mono text-truncate" style="max-width: 140px;" :title="item.cuf">
              {{ item.cuf }}
            </span>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon x-small v-bind="attrs" v-on="on" class="ml-1" @click="copiarTexto(item.cuf)">
                  <v-icon x-small>mdi-content-copy</v-icon>
                </v-btn>
              </template>
              <span>Copiar CUF</span>
            </v-tooltip>
          </div>
        </template>

        <template v-slot:item.monto_total="{ item }">
          <span class="font-weight-bold">Bs {{ formatoMoneda(item.monto_total) }}</span>
        </template>

        <template v-slot:item.debito_fiscal="{ item }">
          <span class="font-weight-bold info--text">
            Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : formatoMoneda(item.monto_total_sujeto_iva * 0.13) }}
          </span>
        </template>

        <template v-slot:item.estado_factura="{ item }">
          <v-chip
            x-small
            :color="colorEstado(item.estado_factura)"
            text-color="white"
            class="font-weight-bold"
          >
            <v-icon left x-small>{{ iconoEstado(item.estado_factura) }}</v-icon>
            {{ labelEstado(item.estado_factura) }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal darken-2"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirPortalSiat(item)"
                >
                  <v-icon small>mdi-qrcode-scan</v-icon>
                </v-btn>
              </template>
              <span>Verificar en Portal Oficial SIAT (SIN)</span>
            </v-tooltip>
          </div>
        </template>

        <!-- FILA TOTALIZADORA (TFOOT) -->
        <template v-slot:body.append>
          <tr class="grey lighten-3 font-weight-black" v-if="facturas.length > 0">
            <td colspan="6" class="text-right text-uppercase">TOTALES DEL PERÍODO:</td>
            <td class="text-right primary--text font-weight-black">Bs {{ formatoMoneda(resumen.total_facturado) }}</td>
            <td class="text-right info--text font-weight-black">Bs {{ formatoMoneda(resumen.debito_fiscal_iva) }}</td>
            <td colspan="2" class="text-center font-weight-black text-caption">
              <span class="success--text font-weight-bold">{{ resumen.cantidad_validas || 0 }} Válidas</span> |
              <span class="warning--text text--darken-2 font-weight-bold">{{ resumen.cantidad_contingencias || 0 }} Conting.</span> |
              <span class="error--text font-weight-bold">{{ resumen.cantidad_anuladas || 0 }} Anul.</span>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReporteLibroVentas',
  data() {
    const hoy = new Date();
    const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().substr(0, 10);
    const hoyStr = hoy.toISOString().substr(0, 10);

    return {
      cargando: false,
      cargandoCsv: false,
      periodoTipo: 'mes',
      filtros: {
        fecha_desde: primerDiaMes,
        fecha_hasta: hoyStr,
        estado: null,
        tipo_emision: null,
      },
      estadosList: [
        { text: 'Todos los estados fiscales', value: null },
        { text: 'Válidas en Línea (V)', value: 'VALIDADA' },
        { text: 'Contingencia / Fuera de Línea (E)', value: 'CONTINGENCIA' },
        { text: 'Anuladas (A)', value: 'ANULADA' },
        { text: 'Rechazadas / Observadas (R)', value: 'RECHAZADA' },
      ],
      tiposEmisionList: [
        { text: 'Todas las modalidades', value: null },
        { text: 'En Línea (1)', value: 1 },
        { text: 'Fuera de Línea / Contingencia (2)', value: 2 },
      ],
      resumen: {
        total_registros: 0,
        cantidad_validas: 0,
        cantidad_contingencias: 0,
        cantidad_anuladas: 0,
        cantidad_rechazadas: 0,
        total_facturado: 0,
        total_descuento: 0,
        total_base_debito_fiscal: 0,
        debito_fiscal_iva: 0,
      },
      facturas: [],
      headers: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha y Hora', value: 'fecha_emision', width: '130px' },
        { text: 'NIT / C.I.', value: 'documento', width: '110px' },
        { text: 'Abonado / Razón Social', value: 'nombre_razon_social' },
        { text: 'Modalidad', value: 'tipo_emision', align: 'center', width: '130px' },
        { text: 'CUF (Autorización)', value: 'cuf', width: '170px' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '110px' },
        { text: 'Débito Fiscal (13%)', value: 'debito_fiscal', align: 'end', width: '130px' },
        { text: 'Estado SIN', value: 'estado_factura', align: 'center', width: '135px' },
        { text: 'SIAT', value: 'acciones', align: 'center', sortable: false, width: '70px' },
      ],
    };
  },
  mounted() {
    this.consultarReporte();
  },
  methods: {
    cambiarPeriodoRapido(val) {
      const hoy = new Date();
      const format = d => d.toISOString().substr(0, 10);

      if (val === 'hoy') {
        this.filtros.fecha_desde = format(hoy);
        this.filtros.fecha_hasta = format(hoy);
      } else if (val === 'semana') {
        const d = new Date(hoy);
        const diaSemana = d.getDay() || 7;
        d.setDate(d.getDate() - diaSemana + 1);
        this.filtros.fecha_desde = format(d);
        const finSem = new Date(d);
        finSem.setDate(finSem.getDate() + 6);
        this.filtros.fecha_hasta = format(finSem);
      } else if (val === 'mes') {
        const iniMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        this.filtros.fecha_desde = format(iniMes);
        this.filtros.fecha_hasta = format(finMes);
      } else if (val === 'mes_anterior') {
        const iniMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const finMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        this.filtros.fecha_desde = format(iniMesAnt);
        this.filtros.fecha_hasta = format(finMesAnt);
      }
      if (val !== 'personalizado') {
        this.consultarReporte();
      }
    },

    onFechaManualChange() {
      this.periodoTipo = 'personalizado';
      this.consultarReporte();
    },

    async consultarReporte() {
      this.cargando = true;
      try {
        const params = { ...this.filtros };
        const res = await axios.get('/api/facturacion/reportes/libro-ventas', { params });
        if (res.data && res.data.success) {
          this.facturas = res.data.data;
          this.resumen = res.data.resumen;
        }
      } catch (error) {
        console.error('Error al consultar el libro de ventas:', error);
      } finally {
        this.cargando = false;
      }
    },

    async descargarCsv() {
      this.cargandoCsv = true;
      try {
        const params = {
          fecha_desde: this.filtros.fecha_desde,
          fecha_hasta: this.filtros.fecha_hasta,
          estado: this.filtros.estado,
          tipo_emision: this.filtros.tipo_emision,
        };
        const response = await axios.get('/api/facturacion/reportes/libro-ventas/csv', {
          params,
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Libro_Ventas_IVA_${this.filtros.fecha_desde}_al_${this.filtros.fecha_hasta}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error al descargar el archivo CSV:', error);
      } finally {
        this.cargandoCsv = false;
      }
    },

    limpiarFiltros() {
      const hoy = new Date();
      this.periodoTipo = 'mes';
      this.filtros.fecha_desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().substr(0, 10);
      this.filtros.fecha_hasta = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().substr(0, 10);
      this.filtros.estado = null;
      this.filtros.tipo_emision = null;
      this.consultarReporte();
    },

    imprimirReporte() {
      window.print();
    },

    formatoMoneda(val) {
      if (!val) return '0.00';
      return parseFloat(val).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    formatearFechaHora(val) {
      if (!val) return '';
      const d = new Date(val);
      return d.toLocaleDateString('es-BO') + ' ' + d.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
    },

    formatearFecha(val) {
      if (!val) return '';
      const d = new Date(val);
      return d.toLocaleDateString('es-BO');
    },

    esContingencia(item) {
      return (
        item.tipo_emision === 2 ||
        item.estado_factura === 'CONTINGENCIA' ||
        item.estado_factura === 'OFFLINE_PENDIENTE' ||
        item.estado_factura === 'OFFLINE_REGULARIZADA'
      );
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
        case 'OFFLINE_PENDIENTE':
          return 'warning darken-1';
        case 'OFFLINE_REGULARIZADA':
          return 'teal darken-1';
        case 'RECHAZADA':
        case 'OBSERVADA':
          return 'deep-orange darken-1';
        default:
          return 'info';
      }
    },

    iconoEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
          return 'mdi-check-circle';
        case 'ANULADA':
          return 'mdi-cancel';
        case 'CONTINGENCIA':
        case 'OFFLINE_PENDIENTE':
          return 'mdi-alert-circle-outline';
        case 'OFFLINE_REGULARIZADA':
          return 'mdi-cloud-check';
        case 'RECHAZADA':
        case 'OBSERVADA':
          return 'mdi-close-octagon';
        default:
          return 'mdi-information-outline';
      }
    },

    labelEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
          return 'VÁLIDA (V)';
        case 'ANULADA':
          return 'ANULADA (A)';
        case 'CONTINGENCIA':
        case 'OFFLINE_PENDIENTE':
          return 'CONTINGENCIA (E)';
        case 'OFFLINE_REGULARIZADA':
          return 'REGULARIZADA (V)';
        case 'RECHAZADA':
          return 'RECHAZADA (R)';
        default:
          return estado || 'VÁLIDA (V)';
      }
    },

    abrirPortalSiat(item) {
      if (item.representacion_grafica_qr) {
        window.open(item.representacion_grafica_qr, '_blank');
      } else {
        const nit = '1002393029';
        const url = `https://siat.impuestos.gob.bo/consulta/QR?nit=${nit}&cuf=${item.cuf}&numero=${item.numero_factura}&t=2`;
        window.open(url, '_blank');
      }
    },

    copiarTexto(txt) {
      if (!txt) return;
      navigator.clipboard.writeText(txt);
    },
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  .reporte-libro-ventas, .reporte-libro-ventas * {
    visibility: visible;
  }
  .reporte-libro-ventas {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    background: white;
  }
}
.gap-2 {
  gap: 8px;
}
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.07);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.theme--dark .erp-card-elevated {
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.35);
}
</style>
