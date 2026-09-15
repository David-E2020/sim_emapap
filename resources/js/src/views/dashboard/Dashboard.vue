<template>
  <div class="dashboard-container">
    <!-- 1. CABECERA EJECUTIVA ESTÁNDAR DEL SISTEMA (ERP CARD) -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <!-- Título e Identidad del Módulo -->
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-view-dashboard-outline</v-icon>
          </v-avatar>
          <div>
            <div class="d-flex align-center flex-wrap">
              <h2 class="text-h5 font-weight-bold mb-0 mr-2">Panel de Control Operativo</h2>
              <v-chip x-small color="primary" outlined class="font-weight-bold my-1">
                SIAT · SERVICIOS BÁSICOS
              </v-chip>
            </div>
            <span class="text-caption text-secondary">
              Visión ejecutiva integral en tiempo real de comercialización, micromedición, cajas y facturación
            </span>
          </div>
        </div>

        <!-- Estados y Acciones de Cabecera -->
        <div class="d-flex align-center flex-wrap my-1">
          <v-chip
            small
            outlined
            :color="metricas.recaudacion.cajas_abiertas > 0 ? 'success' : 'secondary'"
            class="font-weight-medium mr-2 my-1"
          >
            <v-icon
              left
              x-small
              :color="metricas.recaudacion.cajas_abiertas > 0 ? 'success' : 'secondary'"
            >
              {{ metricas.recaudacion.cajas_abiertas > 0 ? 'mdi-cash-check' : 'mdi-circle-small' }}
            </v-icon>
            {{ metricas.recaudacion.cajas_abiertas > 0 ? metricas.recaudacion.cajas_abiertas + ' Cajas Activas' : 'Cajas en Espera' }}
          </v-chip>

          <v-chip small color="primary" outlined class="font-weight-medium mr-2 my-1">
            <v-icon left x-small color="primary">mdi-calendar-month-outline</v-icon>
            Período: {{ metricas.periodo_actual.nombre || '08/2026' }}
          </v-chip>

          <v-btn
            color="primary"
            class="text-capitalize font-weight-medium rounded-pill elevation-1 mr-2 my-1"
            :to="{ name: 'comercial_caja' }"
          >
            <v-icon left small>mdi-cash-register</v-icon> Abrir Ventanilla
          </v-btn>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                outlined
                color="primary"
                class="rounded-lg my-1"
                v-bind="attrs"
                v-on="on"
                :loading="cargando"
                @click="cargarMetricas"
              >
                <v-icon small>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Actualizar métricas en tiempo real</span>
          </v-tooltip>
        </div>
      </div>
    </v-card>

    <!-- 2. FILA SUPERIOR: 4 KPIs DE ALTO IMPACTO -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- KPI 1: Recaudación Total -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card">
          <div class="d-flex justify-space-between align-start mb-2">
            <span class="text-caption text-secondary font-weight-medium">Recaudación Total</span>
            <v-avatar color="success" size="36" class="elevation-1">
              <v-icon color="white" size="20">mdi-currency-usd</v-icon>
            </v-avatar>
          </div>
          <div class="text-h5 font-weight-bold success--text mb-1">
            Bs {{ formatearMonto(metricas.recaudacion.total_recaudado) }}
          </div>
          <div class="text-caption text-secondary d-flex align-center">
            <v-icon x-small color="success" class="me-1">mdi-receipt-text-outline</v-icon>
            Cobros registrados en ventanillas
          </div>
        </v-card>
      </v-col>

      <!-- KPI 2: Padrón de Socios -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card">
          <div class="d-flex justify-space-between align-start mb-2">
            <span class="text-caption text-secondary font-weight-medium">Padrón de Socios</span>
            <v-avatar color="primary" size="36" class="elevation-1">
              <v-icon color="white" size="20">mdi-home-city-outline</v-icon>
            </v-avatar>
          </div>
          <div class="text-h5 font-weight-bold primary--text mb-1">
            {{ metricas.catastro.total_abonados.toLocaleString() }}
          </div>
          <div class="text-caption text-secondary d-flex align-center">
            <v-icon x-small color="success" class="me-1">mdi-check-circle-outline</v-icon>
            {{ metricas.catastro.activos.toLocaleString() }} conexiones activas
          </div>
        </v-card>
      </v-col>

      <!-- KPI 3: Consumo de Agua -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card">
          <div class="d-flex justify-space-between align-start mb-2">
            <span class="text-caption text-secondary font-weight-medium">Consumo Período</span>
            <v-avatar color="info" size="36" class="elevation-1">
              <v-icon color="white" size="20">mdi-water-percent</v-icon>
            </v-avatar>
          </div>
          <div class="text-h5 font-weight-bold info--text mb-1">
            {{ Number(metricas.periodo_actual.total_m3).toLocaleString() }} m³
          </div>
          <div class="text-caption text-secondary d-flex align-center">
            <v-icon x-small color="info" class="me-1">mdi-calendar-outline</v-icon>
            {{ metricas.periodo_actual.total_lecturas.toLocaleString() }} lecturas ({{ metricas.periodo_actual.nombre }})
          </div>
        </v-card>
      </v-col>

      <!-- KPI 4: Facturación SIAT -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card">
          <div class="d-flex justify-space-between align-start mb-2">
            <span class="text-caption text-secondary font-weight-medium">Facturación SIAT</span>
            <v-avatar color="deep-purple" size="36" class="elevation-1">
              <v-icon color="white" size="20">mdi-shield-check</v-icon>
            </v-avatar>
          </div>
          <div class="text-h5 font-weight-bold deep-purple--text mb-1">
            {{ metricas.sin.validas.toLocaleString() }}
          </div>
          <div class="text-caption text-secondary d-flex align-center">
            <v-icon x-small color="deep-purple" class="me-1">mdi-cash-check</v-icon>
            Bs {{ formatearMonto(metricas.sin.credito_fiscal) }} crédito IVA
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- 3. BLOQUE ANALÍTICO: HISTÓRICO Y CATEGORÍAS -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- Gráfico Semestral de Facturación y Consumo -->
      <v-col cols="12" md="8">
        <v-card outlined rounded="xl" class="pa-5 h-100 kpi-card">
          <div class="d-flex justify-space-between align-center mb-4 flex-wrap">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold text--primary mb-0">
                Histórico Semestral de Facturación y Consumo
              </h3>
              <span class="text-caption text-secondary">
                Facturación comercial (Bs) en contraste con el volumen de agua distribuido (m³)
              </span>
            </div>
            <v-chip small color="primary" outlined class="font-weight-bold">
              <v-icon left small>mdi-chart-line</v-icon> 6 Períodos
            </v-chip>
          </div>
          <vue-apex-charts
            type="area"
            height="290"
            :options="chartFacturacionOptions"
            :series="chartFacturacionSeries"
          ></vue-apex-charts>
        </v-card>
      </v-col>

      <!-- Gráfico Donut de Categorías Tarifarias -->
      <v-col cols="12" md="4">
        <v-card outlined rounded="xl" class="pa-5 h-100 kpi-card">
          <div class="d-flex justify-space-between align-center mb-3">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold text--primary mb-0">
                Categorías de Abonados
              </h3>
              <span class="text-caption text-secondary">
                Distribución del catastro de conexiones
              </span>
            </div>
          </div>
          <vue-apex-charts
            type="donut"
            height="290"
            :options="chartCategoriasOptions"
            :series="chartCategoriasSeries"
          ></vue-apex-charts>
        </v-card>
      </v-col>
    </v-row>

    <!-- 4. CUADRÍCULA BENTO-GRID: MÓDULOS OPERATIVOS INTEGRADOS -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- Módulo 1: Agua Potable & Micromedición -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="info lighten-5" rounded="lg" size="42">
                <v-icon color="info" size="24">mdi-water-pump</v-icon>
              </v-avatar>
              <v-chip x-small color="info" outlined class="font-weight-bold">Comercial</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Agua y Medición
            </h4>
            <div class="text-caption text-secondary mb-3">
              Control de tomas, hidrómetros y red sanitaria.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Con Medidor:</span>
              <strong class="text--primary">{{ metricas.catastro.con_medidor.toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Alcantarillado:</span>
              <strong class="text--primary">{{ metricas.catastro.con_alcantarillado.toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Tomas en Corte:</span>
              <strong class="error--text">{{ metricas.catastro.cortados.toLocaleString() }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="info"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'comercial_abonados' }"
          >
            <span>Ver Padrón de Socios</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 2: Cajas y Recaudación Diaria -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="success lighten-5" rounded="lg" size="42">
                <v-icon color="success" size="24">mdi-cash-register</v-icon>
              </v-avatar>
              <v-chip x-small color="success" outlined class="font-weight-bold">Recaudación</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Cajas y Ventanilla
            </h4>
            <div class="text-caption text-secondary mb-3">
              Cobro de recibos oficiales y arqueo de turnos.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Turnos Hoy:</span>
              <strong :class="metricas.recaudacion.cajas_abiertas > 0 ? 'success--text' : 'text--secondary'">
                {{ metricas.recaudacion.cajas_abiertas }} abiertas
              </strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Socios con Deuda:</span>
              <strong class="warning--text">{{ metricas.mora.socios_en_mora.toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Mora Acumulada:</span>
              <strong class="error--text">Bs {{ formatearMonto(metricas.mora.deuda_total) }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="success"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'comercial_caja' }"
          >
            <span>Ir a Ventanilla</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 3: Facturación SIAT e Impuestos -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="deep-purple lighten-5" rounded="lg" size="42">
                <v-icon color="deep-purple" size="24">mdi-receipt-text-check</v-icon>
              </v-avatar>
              <v-chip x-small color="deep-purple" outlined class="font-weight-bold">Tributario</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Facturación SIAT
            </h4>
            <div class="text-caption text-secondary mb-3">
              Documento Sector 13 (Servicios Básicos SIN).
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Facturas Válidas:</span>
              <strong class="deep-purple--text">{{ metricas.sin.validas.toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Contingencias:</span>
              <strong class="text--secondary">{{ metricas.sin.contingencia }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Anuladas SIN:</span>
              <strong class="text--secondary">{{ metricas.sin.anuladas }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="deep-purple"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'facturacion_bandeja' }"
          >
            <span>Bandeja de Facturas</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 4: Gestión Institucional (Contabilidad, RRHH, Trámites) -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="amber lighten-5" rounded="lg" size="42">
                <v-icon color="amber darken-3" size="24">mdi-domain</v-icon>
              </v-avatar>
              <v-chip x-small color="amber darken-3" outlined class="font-weight-bold">Gestión</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Contable & RRHH
            </h4>
            <div class="text-caption text-secondary mb-3">
              Integración de finanzas y personal institucional.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Plan de Cuentas:</span>
              <strong class="text--primary">{{ metricas.modulos.contabilidad_cuentas }} cuentas</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Personal en Planilla:</span>
              <strong class="text--primary">{{ metricas.modulos.rrhh_personal }} funcionarios</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Hojas de Ruta:</span>
              <strong class="text--primary">{{ metricas.modulos.correspondencia_hojas_ruta }} activas</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="amber darken-3"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'contabilidad_plan_cuentas' }"
          >
            <span>Plan de Cuentas</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>
    </v-row>

    <!-- 5. FEED DE ÚLTIMAS OPERACIONES EN VENTANILLA -->
    <v-card outlined rounded="xl" class="kpi-card" v-if="metricas">
      <v-card-title class="py-3 px-5 d-flex justify-space-between align-center flex-wrap">
        <div class="d-flex align-center">
          <v-icon color="success" left>mdi-history</v-icon>
          <span class="text-subtitle-1 font-weight-bold text--primary">
            Últimas Transacciones en Ventanilla de Cobro
          </span>
        </div>
        <v-btn
          small
          text
          color="primary"
          class="font-weight-bold"
          :to="{ name: 'comercial_caja' }"
        >
          <span>Ir a Caja</span>
          <v-icon right small>mdi-arrow-right</v-icon>
        </v-btn>
      </v-card-title>
      <v-divider></v-divider>
      <v-simple-table dense>
        <template v-slot:default>
          <thead>
            <tr>
              <th class="text-left font-weight-bold">Nº Recibo</th>
              <th class="text-left font-weight-bold">Cód. Socio</th>
              <th class="text-left font-weight-bold">Titular / Razón Social</th>
              <th class="text-left font-weight-bold">Fecha y Hora</th>
              <th class="text-left font-weight-bold">Concepto</th>
              <th class="text-right font-weight-bold">Monto Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pago in metricas.ultimos_pagos" :key="pago.id">
              <td>
                <strong class="success--text font-weight-bold">{{ pago.nro_recibo }}</strong>
              </td>
              <td>
                <v-chip x-small color="primary" outlined class="font-weight-bold">
                  {{ pago.codigo_socio }}
                </v-chip>
              </td>
              <td class="text-body-2 font-weight-medium">{{ pago.titular }}</td>
              <td class="text-caption text-secondary">{{ pago.fecha_pago }}</td>
              <td>
                <v-chip x-small color="primary" class="font-weight-bold">
                  {{ pago.concepto }}
                </v-chip>
              </td>
              <td class="text-right font-weight-bold">
                Bs {{ Number(pago.monto).toFixed(2) }}
              </td>
            </tr>
            <tr v-if="!metricas.ultimos_pagos || metricas.ultimos_pagos.length === 0">
              <td colspan="6" class="text-center py-5 text-secondary">
                No hay operaciones registradas recientemente.
              </td>
            </tr>
          </tbody>
        </template>
      </v-simple-table>
    </v-card>
  </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts'
import axios from 'axios'

export default {
  name: 'DashboardPrincipal',
  components: {
    VueApexCharts,
  },
  data() {
    return {
      cargando: false,
      errorCarga: null,
      metricas: {
        catastro: {
          total_abonados: 0,
          activos: 0,
          cortados: 0,
          con_medidor: 0,
          con_alcantarillado: 0,
        },
        recaudacion: {
          total_recaudado: 0,
          cajas_abiertas: 0,
        },
        periodo_actual: {
          nombre: '',
          mes: null,
          gestion: null,
          total_facturado: 0,
          total_m3: 0,
          total_lecturas: 0,
        },
        sin: {
          total_emitidas: 0,
          validas: 0,
          contingencia: 0,
          anuladas: 0,
          credito_fiscal: 0,
          eventos_contingencia: 0,
        },
        mora: {
          socios_en_mora: 0,
          deuda_total: 0,
        },
        modulos: {
          contabilidad_comprobantes: 0,
          contabilidad_cuentas: 0,
          rrhh_personal: 0,
          correspondencia_hojas_ruta: 0,
        },
        graficos: {
          categorias: [],
          facturado: [],
          consumo_m3: [],
          donut_labels: [],
          donut_series: [],
        },
        ultimos_pagos: [],
      },
    }
  },
  computed: {
    isDark() {
      return this.$vuetify.theme.dark
    },
    chartFacturacionSeries() {
      return [
        {
          name: 'Facturación Comercial (Bs)',
          data: this.metricas.graficos.facturado || [],
        },
        {
          name: 'Consumo de Agua (m³)',
          data: this.metricas.graficos.consumo_m3 || [],
        },
      ]
    },
    chartFacturacionOptions() {
      const isDark = this.isDark
      const primaryColor = this.$vuetify.theme.currentTheme.primary || '#0284c7'

      return {
        chart: {
          type: 'area',
          toolbar: { show: false },
          background: 'transparent',
          zoom: { enabled: false },
        },
        theme: {
          mode: isDark ? 'dark' : 'light',
        },
        colors: [primaryColor, '#10b981'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: isDark ? 0.45 : 0.6,
            opacityTo: 0.08,
            stops: [0, 90, 100],
          },
        },
        xaxis: {
          categories: this.metricas.graficos.categorias || [],
          labels: {
            style: {
              colors: isDark ? '#a0aec0' : '#4a5568',
              fontSize: '12px',
            },
          },
          axisBorder: { show: false },
          axisTicks: { show: false },
        },
        yaxis: [
          {
            title: {
              text: 'Facturado (Bs)',
              style: { color: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
            labels: {
              formatter: val => `Bs ${Number(val).toLocaleString()}`,
              style: { colors: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
          },
          {
            opposite: true,
            title: {
              text: 'Consumo (m³)',
              style: { color: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
            labels: {
              formatter: val => `${Number(val).toLocaleString()} m³`,
              style: { colors: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
          },
        ],
        grid: {
          borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)',
          strokeDashArray: 4,
          padding: { left: 10, right: 10 },
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          labels: { colors: isDark ? '#e2e8f0' : '#2d3748' },
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light',
        },
      }
    },
    chartCategoriasSeries() {
      return this.metricas.graficos.donut_series || []
    },
    chartCategoriasOptions() {
      const isDark = this.isDark

      return {
        chart: {
          type: 'donut',
          background: 'transparent',
        },
        theme: {
          mode: isDark ? 'dark' : 'light',
        },
        labels: this.metricas.graficos.donut_labels || [],
        colors: ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
        dataLabels: { enabled: false },
        legend: {
          position: 'bottom',
          labels: { colors: isDark ? '#e2e8f0' : '#2d3748' },
        },
        stroke: {
          colors: [isDark ? '#1e293b' : '#ffffff'],
          width: 2,
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light',
          y: {
            formatter: val => `${Number(val).toLocaleString()} socios`,
          },
        },
      }
    },
  },
  created() {
    this.cargarMetricas()
  },
  methods: {
    async cargarMetricas() {
      this.cargando = true
      this.errorCarga = null
      try {
        const response = await axios.get('/api/dashboard/metricas')
        if (response && response.data) {
          this.metricas = Object.assign({}, this.metricas, response.data)
        }
      } catch (err) {
        console.error('Error al cargar métricas del dashboard:', err)
        this.errorCarga = 'No se pudieron sincronizar las métricas en tiempo real.'
      } finally {
        this.cargando = false
      }
    },
    formatearMonto(valor) {
      if (!valor && valor !== 0) return '0.00'
      return Number(valor).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    },
  },
}
</script>

<style scoped>
.dashboard-container {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.theme--dark .erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.kpi-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  border-color: rgba(0, 0, 0, 0.08) !important;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06) !important;
}

.theme--dark .kpi-card {
  border-color: rgba(255, 255, 255, 0.08) !important;
}

.theme--dark .kpi-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
}

.border-bottom {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.theme--dark .border-bottom {
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
</style>

