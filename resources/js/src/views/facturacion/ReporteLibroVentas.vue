<template>
  <div class="reporte-libro-ventas">
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-book-open-page-variant</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0 text--primary">Libro de Ventas IVA (SIAT)</h2>
            <span class="text-caption text-secondary">
              Registro Oficial de Ventas y Débito Fiscal para EMAPAP Patacamaya
            </span>
          </div>
        </div>
        <div class="d-flex align-center my-1">
          <v-btn
            color="success darken-1"
            class="white--text font-weight-bold rounded-pill elevation-1"
            :loading="cargandoCsv"
            @click="descargarCsv"
          >
            <v-icon left small>mdi-file-delimited</v-icon>
            Exportar CSV (Normativa SIN)
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- Filtros de Fecha y Búsqueda -->
    <v-card class="mb-5 pa-4 erp-card-elevated" rounded="lg">
      <v-card-text class="pa-5">
        <v-row dense align="center">
          <v-col cols="12" sm="3">
            <v-text-field
              v-model="filtros.fecha_desde"
              label="Fecha Desde"
              type="date"
              outlined
              dense
              hide-details
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="3">
            <v-text-field
              v-model="filtros.fecha_hasta"
              label="Fecha Hasta"
              type="date"
              outlined
              dense
              hide-details
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="3">
            <v-select
              v-model="filtros.estado"
              :items="estadosList"
              label="Estado de Factura"
              outlined
              dense
              hide-details
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" sm="3" class="d-flex align-center">
            <v-btn
              color="primary"
              class="px-5 mr-2"
              height="40"
              :loading="cargando"
              @click="consultarReporte"
            >
              <v-icon left>mdi-magnify</v-icon>
              Consultar
            </v-btn>
            <v-btn
              outlined
              color="secondary"
              height="40"
              @click="limpiarFiltros"
            >
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Tarjetas de Resumen Fiscal -->
    <v-row class="mb-5" dense>
      <v-col cols="12" sm="6" md="3">
        <v-card class="erp-card-elevated rounded-lg pa-4 border-left-primary">
          <div class="text-caption text-uppercase font-weight-bold secondary--text">Total Facturado</div>
          <div class="text-h4 font-weight-black primary--text mt-1">
            Bs {{ formatoMoneda(resumen.total_facturado) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            {{ resumen.total_registros }} facturas en periodo
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="erp-card-elevated rounded-lg pa-4 border-left-success">
          <div class="text-caption text-uppercase font-weight-bold secondary--text">Base Débito Fiscal</div>
          <div class="text-h4 font-weight-black success--text mt-1">
            Bs {{ formatoMoneda(resumen.total_base_debito_fiscal) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            Importe sujeto al IVA (13%)
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="erp-card-elevated rounded-lg pa-4 border-left-info">
          <div class="text-caption text-uppercase font-weight-bold secondary--text">Débito Fiscal IVA (13%)</div>
          <div class="text-h4 font-weight-black info--text mt-1">
            Bs {{ formatoMoneda(resumen.debito_fiscal_iva) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            Monto a declarar en Form. 200
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="erp-card-elevated rounded-lg pa-4 border-left-warning">
          <div class="text-caption text-uppercase font-weight-bold secondary--text">Válidas / Anuladas</div>
          <div class="text-h4 font-weight-black warning--text mt-1">
            {{ resumen.cantidad_validas }} <span class="text-subtitle-1 secondary--text">/ {{ resumen.cantidad_anuladas }}</span>
          </div>
          <div class="text-caption text-secondary mt-1">
            {{ resumen.cantidad_validas }} activas tributariamente
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Tabla Detallada del Libro de Ventas -->
    <v-card class="erp-card-elevated rounded-lg">
      <v-card-title class="py-3 px-5 d-flex justify-space-between align-center">
        <span class="text-h6 font-weight-bold">Detalle de Facturas Registradas</span>
        <v-chip small color="primary" outlined>
          {{ facturas.length }} registros cargados
        </v-chip>
      </v-card-title>
      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="facturas"
        :loading="cargando"
        :items-per-page="15"
        class="elevation-0"
        no-data-text="No se encontraron facturas emitidas en el periodo seleccionado"
      >
        <template v-slot:item.numero_factura="{ item }">
          <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
        </template>

        <template v-slot:item.fecha_emision="{ item }">
          <span>{{ formatearFecha(item.fecha_emision) }}</span>
        </template>

        <template v-slot:item.documento="{ item }">
          <div>
            <strong>{{ item.numero_documento }}</strong>
            <span v-if="item.complemento" class="grey--text"> - {{ item.complemento }}</span>
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
            small
            :color="item.estado_factura === 'ANULADA' ? 'error' : 'success'"
            text-color="white"
            class="font-weight-bold"
          >
            {{ item.estado_factura === 'ANULADA' ? 'ANULADA (A)' : 'VÁLIDA (V)' }}
          </v-chip>
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
      filtros: {
        fecha_desde: primerDiaMes,
        fecha_hasta: hoyStr,
        estado: null,
      },
      estadosList: [
        { text: 'Todas las facturas', value: null },
        { text: 'Solo Válidas (V)', value: 'VALIDADA' },
        { text: 'Solo Anuladas (A)', value: 'ANULADA' },
      ],
      resumen: {
        total_registros: 0,
        cantidad_validas: 0,
        cantidad_anuladas: 0,
        total_facturado: 0,
        total_descuento: 0,
        total_base_debito_fiscal: 0,
        debito_fiscal_iva: 0,
      },
      facturas: [],
      headers: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '100px' },
        { text: 'Fecha', value: 'fecha_emision', width: '110px' },
        { text: 'NIT / C.I.', value: 'documento', width: '130px' },
        { text: 'Abonado / Razón Social', value: 'nombre_razon_social' },
        { text: 'CUF (Autorización)', value: 'cuf', width: '180px' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '120px' },
        { text: 'Débito Fiscal (13%)', value: 'debito_fiscal', align: 'end', width: '140px' },
        { text: 'Estado', value: 'estado_factura', align: 'center', width: '130px' },
      ],
    };
  },
  mounted() {
    this.consultarReporte();
  },
  methods: {
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
      this.filtros.fecha_desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().substr(0, 10);
      this.filtros.fecha_hasta = hoy.toISOString().substr(0, 10);
      this.filtros.estado = null;
      this.consultarReporte();
    },
    formatoMoneda(val) {
      if (!val) return '0.00';
      return parseFloat(val).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    formatearFecha(val) {
      if (!val) return '';
      const d = new Date(val);
      return d.toLocaleDateString('es-BO');
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.07);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.theme--dark .erp-card-elevated {
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.35);
}
.border-left-primary {
  border-left: 5px solid #1976d2 !important;
}
.border-left-success {
  border-left: 5px solid #4caf50 !important;
}
.border-left-info {
  border-left: 5px solid #00bcd4 !important;
}
.border-left-warning {
  border-left: 5px solid #fb8c00 !important;
}
</style>
