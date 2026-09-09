<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-chart-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Reportes Comerciales y Cuadre de Caja</h2>
            <span class="text-caption text-secondary">
              Recaudación consolidada por día, semana, mes o rango de fechas, arqueos y morosidad institucional
            </span>
          </div>
        </div>
      </div>
    </v-card>

    <v-tabs v-model="tabActual" color="teal darken-3" class="mb-4">
      <v-tab class="font-weight-bold"><v-icon left small>mdi-cash-register</v-icon> Recaudación y Cuadre de Caja</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-account-alert</v-icon> Cartera Vencida y Morosidad</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-chart-bell-curve</v-icon> Balance de Consumo Mensual</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: RECAUDACIÓN Y CUADRE CONSOLIDADO DE CAJA -->
      <v-tab-item>
        <!-- FILTROS AVANZADOS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="d-flex align-center flex-wrap gap-2">
              <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
              <v-btn-toggle v-model="filtros.periodo_tipo" mandatory dense color="teal darken-3" @change="cambiarPeriodoRapido">
                <v-btn small value="hoy" class="text-capitalize">Hoy</v-btn>
                <v-btn small value="semana" class="text-capitalize">Esta Semana</v-btn>
                <v-btn small value="mes" class="text-capitalize">Este Mes</v-btn>
                <v-btn small value="mes_anterior" class="text-capitalize">Mes Anterior</v-btn>
                <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
              </v-btn-toggle>
            </div>

            <!-- BOTONES DE EXPORTACIÓN -->
            <div class="d-flex align-center gap-2">
              <v-btn
                color="teal darken-2"
                dark
                small
                class="rounded-pill font-weight-medium"
                :loading="generandoPdf"
                @click="verPdfConsolidado"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Planilla Oficial PDF
              </v-btn>
              <v-btn
                color="indigo darken-1"
                dark
                small
                outlined
                class="rounded-pill font-weight-medium"
                @click="exportarCsv"
              >
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel/CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <!-- FECHAS -->
            <v-col cols="12" sm="3" md="2">
              <v-text-field
                v-model="filtros.fecha_inicio"
                label="Fecha Desde"
                type="date"
                dense
                outlined
                hide-details
                @change="onFechaChange"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" md="2">
              <v-text-field
                v-model="filtros.fecha_fin"
                label="Fecha Hasta"
                type="date"
                dense
                outlined
                hide-details
                @change="onFechaChange"
              ></v-text-field>
            </v-col>

            <!-- SELECTOR DE CAJA / PUNTO DE VENTA -->
            <v-col cols="12" sm="3" md="3">
              <v-select
                v-model="filtros.id_punto_venta"
                :items="cajasLista"
                item-value="id"
                item-text="nombre"
                label="Caja / Ventanilla"
                dense
                outlined
                hide-details
                prepend-inner-icon="mdi-store"
                clearable
                @change="cargarRecaudacionConsolidada"
              ></v-select>
            </v-col>

            <!-- SELECTOR DE CAJERO -->
            <v-col cols="12" sm="3" md="3">
              <v-select
                v-model="filtros.id_cajero"
                :items="cajerosLista"
                item-value="id"
                item-text="name"
                label="Cajero(a)"
                dense
                outlined
                hide-details
                prepend-inner-icon="mdi-account"
                clearable
                @change="cargarRecaudacionConsolidada"
              ></v-select>
            </v-col>

            <!-- BOTÓN FILTRAR -->
            <v-col cols="12" sm="12" md="2" class="d-flex justify-end">
              <v-btn
                color="teal darken-3"
                dark
                class="rounded-pill w-100 font-weight-bold"
                :loading="cargandoRecaudacion"
                @click="cargarRecaudacionConsolidada"
              >
                <v-icon left small>mdi-filter-check</v-icon> Consultar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS DE MÉTRICAS FINANCIERAS (KPIS) -->
        <div v-if="datosConsolidados && datosConsolidados.metricas">
          <v-row dense class="mb-4">
            <!-- TOTAL RECAUDADO -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Total General Recaudado</div>
                <div class="text-h4 font-weight-black success--text mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_recaudado || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  {{ datosConsolidados.metricas.total_transacciones || 0 }} transacciones realizadas
                </div>
              </v-card>
            </v-col>

            <!-- EFECTIVO FÍSICO GAVETA -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Efectivo Físico en Gaveta</div>
                <div class="text-h4 font-weight-black teal--text text--darken-3 mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_efectivo || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-teal text--darken-2 mt-1 font-weight-medium">
                  Cobros en ventanilla
                </div>
              </v-card>
            </v-col>

            <!-- QR / BANCO -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Cobros QR / Banco Unión</div>
                <div class="text-h4 font-weight-black primary--text mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_qr_banco || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-primary mt-1 font-weight-medium">
                  Directo a cuenta fiscal
                </div>
              </v-card>
            </v-col>

            <!-- CUADRATURA Y ARQUEO -->
            <v-col cols="12" sm="6" md="3">
              <v-card
                class="pa-3 text-center erp-card-elevated"
                rounded="lg"
                :class="diferenciaArqueo === 0 ? 'bg-success-light' : (diferenciaArqueo > 0 ? 'bg-warning-light' : 'bg-danger-light')"
              >
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Diferencia de Arqueo</div>
                <div
                  class="text-h4 font-weight-black mt-1"
                  :class="diferenciaArqueo === 0 ? 'success--text' : (diferenciaArqueo > 0 ? 'warning--text text--darken-3' : 'error--text')"
                >
                  Bs {{ diferenciaArqueo.toFixed(2) }}
                </div>
                <div class="mt-1">
                  <v-chip
                    x-small
                    :color="diferenciaArqueo === 0 ? 'success' : (diferenciaArqueo > 0 ? 'warning' : 'error')"
                    text-color="white"
                    class="font-weight-bold"
                  >
                    {{ diferenciaArqueo === 0 ? 'CAJAS CUADRADAS' : (diferenciaArqueo > 0 ? 'SOBRANTE' : 'FALTANTE') }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- TABLAS DE DETALLE EN SUB-TABS -->
          <v-card rounded="lg" class="erp-card-elevated">
            <v-tabs v-model="subTabActual" color="teal darken-3" dense background-color="grey lighten-4">
              <v-tab class="font-weight-bold"><v-icon left small>mdi-format-list-bulleted-type</v-icon> 1. Rubros Contables</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-store-outline</v-icon> 2. Por Ventanilla / Caja</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-account-tie</v-icon> 3. Por Cajero(a)</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-history</v-icon> 4. Turnos y Arqueos del Período</v-tab>
            </v-tabs>

            <v-divider></v-divider>

            <v-tabs-items v-model="subTabActual" class="pa-4">
              <!-- SUB-TAB 1: DISTRIBUCIÓN POR RUBROS CONTABLES -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Rubro / Partida de Ingreso</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo (Bs)</th>
                        <th class="text-right font-weight-bold">QR / Banco (Bs)</th>
                        <th class="text-right font-weight-bold">Subtotal (Bs)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="r in datosConsolidados.por_rubro" :key="r.nombre">
                        <td class="font-weight-medium">{{ r.nombre }}</td>
                        <td class="text-center">{{ r.cantidad }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(r.efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold text-primary">Bs {{ parseFloat(r.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(r.total).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr class="grey lighten-3 font-weight-black">
                        <td>TOTAL GENERAL RECAUDADO</td>
                        <td class="text-center">{{ datosConsolidados.metricas.total_transacciones }}</td>
                        <td class="text-right">Bs {{ parseFloat(datosConsolidados.metricas.total_efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(datosConsolidados.metricas.total_qr_banco).toFixed(2) }}</td>
                        <td class="text-right success--text">Bs {{ parseFloat(datosConsolidados.metricas.total_recaudado).toFixed(2) }}</td>
                      </tr>
                    </tfoot>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 2: POR VENTANILLA / CAJA -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Caja / Ventanilla Física</th>
                        <th class="text-center font-weight-bold">Punto Venta SIAT</th>
                        <th class="text-center font-weight-bold">Turnos</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo (Bs)</th>
                        <th class="text-right font-weight-bold">QR / Banco (Bs)</th>
                        <th class="text-right font-weight-bold">Total Cobrado (Bs)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="c in datosConsolidados.por_caja" :key="c.id">
                        <td class="font-weight-bold">{{ c.caja }}</td>
                        <td class="text-center">Punto {{ c.codigo_punto_venta }}</td>
                        <td class="text-center">{{ c.turnos }}</td>
                        <td class="text-center">{{ c.transacciones }}</td>
                        <td class="text-right">Bs {{ parseFloat(c.efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(c.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(c.total).toFixed(2) }}</td>
                      </tr>
                      <tr v-if="datosConsolidados.por_caja.length === 0">
                        <td colspan="7" class="text-center py-4 text-secondary">No se registraron cobros en el período seleccionado</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 3: POR CAJERO -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Cajero(a) / Operador</th>
                        <th class="text-center font-weight-bold">Turnos Operados</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo Cobrado</th>
                        <th class="text-right font-weight-bold">QR / Banco</th>
                        <th class="text-right font-weight-bold">Total Recaudado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="cj in datosConsolidados.por_cajero" :key="cj.id_cajero">
                        <td class="font-weight-bold">{{ cj.cajero }}</td>
                        <td class="text-center">{{ cj.turnos }}</td>
                        <td class="text-center">{{ cj.transacciones }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(cj.efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(cj.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(cj.total).toFixed(2) }}</td>
                      </tr>
                      <tr v-if="datosConsolidados.por_cajero.length === 0">
                        <td colspan="6" class="text-center py-4 text-secondary">Sin movimientos para los cajeros seleccionados</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 4: LISTADO DE TURNOS Y ARQUEOS -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">N° Turno</th>
                        <th class="font-weight-bold">Ventanilla</th>
                        <th class="font-weight-bold">Cajero</th>
                        <th class="font-weight-bold">Apertura</th>
                        <th class="font-weight-bold">Cierre</th>
                        <th class="text-right font-weight-bold">Fondo</th>
                        <th class="text-right font-weight-bold">Ventas Ef.</th>
                        <th class="text-right font-weight-bold">Esperado</th>
                        <th class="text-right font-weight-bold">Declarado</th>
                        <th class="text-right font-weight-bold">Diferencia</th>
                        <th class="text-center font-weight-bold">Estado</th>
                        <th class="text-center font-weight-bold">Planilla</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in datosConsolidados.turnos" :key="t.id">
                        <td class="font-weight-bold">{{ t.numero_sesion }}</td>
                        <td>{{ t.caja_nombre }}</td>
                        <td>{{ t.cajero_nombre }}</td>
                        <td class="text-caption">{{ t.fecha_apertura }}</td>
                        <td class="text-caption">{{ t.fecha_cierre || 'EN CURSO' }}</td>
                        <td class="text-right">Bs {{ parseFloat(t.monto_apertura).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_ventas_efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_esperado_efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_cierre_declarado).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold" :class="t.diferencia < 0 ? 'error--text' : (t.diferencia > 0 ? 'warning--text text--darken-3' : 'success--text')">
                          Bs {{ parseFloat(t.diferencia).toFixed(2) }}
                        </td>
                        <td class="text-center">
                          <v-chip
                            x-small
                            :color="t.estado === 'CERRADA' ? (t.diferencia == 0 ? 'success' : 'warning') : 'info'"
                            text-color="white"
                            class="font-weight-bold"
                          >
                            {{ t.estado === 'CERRADA' ? (t.diferencia == 0 ? 'CUADRADO' : (t.diferencia > 0 ? 'SOBRANTE' : 'FALTANTE')) : 'ABIERTA' }}
                          </v-chip>
                        </td>
                        <td class="text-center">
                          <v-btn icon x-small color="teal darken-2" @click="verPdfArqueoIndividual(t.id)">
                            <v-icon small>mdi-file-document-outline</v-icon>
                          </v-btn>
                        </td>
                      </tr>
                      <tr v-if="datosConsolidados.turnos.length === 0">
                        <td colspan="12" class="text-center py-4 text-secondary">No se registraron turnos de caja en este período</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>
            </v-tabs-items>
          </v-card>
        </div>
      </v-tab-item>

      <!-- PESTAÑA 2: CARTERA VENCIDA Y MOROSIDAD -->
      <v-tab-item>
        <v-row dense class="mb-4" v-if="datosMora">
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">ABONADOS EN MORA</div>
              <div class="text-h4 font-weight-black error--text mt-1">{{ datosMora.metricas.total_abonados_mora }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">DEUDA TOTAL ACUMULADA</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                Bs {{ parseFloat(datosMora.metricas.total_deuda_acumulada).toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">MORA 2 MESES (EN RIESGO CORTE)</div>
              <div class="text-h4 font-weight-black warning--text mt-1">{{ datosMora.metricas.mora_2_meses }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">MORA >= 3 MESES (CORTADO)</div>
              <div class="text-h4 font-weight-black deep-orange--text mt-1">{{ datosMora.metricas.mora_3_o_mas_meses }}</div>
            </v-card>
          </v-col>
        </v-row>

        <v-card rounded="lg" class="erp-card-elevated" v-if="datosMora">
          <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
            Top 10 Mayores Deudores
          </v-card-title>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr class="grey lighten-4">
                  <th>Código</th>
                  <th>Abonado</th>
                  <th>Zona</th>
                  <th class="text-center">Meses Mora</th>
                  <th class="text-right">Monto Deuda</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="d in datosMora.top_deudores" :key="d.id">
                  <td class="font-weight-bold">{{ d.codigo }}</td>
                  <td>{{ d.nombre_completo }}</td>
                  <td>{{ d.zona ? d.zona.nombre : '-' }}</td>
                  <td class="text-center font-weight-bold error--text">{{ d.meses_mora }}</td>
                  <td class="text-right font-weight-bold error--text">Bs {{ parseFloat(d.saldo_deuda).toFixed(2) }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 3: BALANCE DE CONSUMO MENSUAL -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
            Balance Hídrico y Recaudación por Periodo
          </v-card-title>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr class="grey lighten-4">
                  <th>Periodo</th>
                  <th class="text-center">Abonados Medidos</th>
                  <th class="text-right">Volumen Total (m³)</th>
                  <th class="text-right">Monto Facturado</th>
                  <th class="text-right">Monto Cobrado</th>
                  <th class="text-center">% Recaudación</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in periodosBalance" :key="p.periodo">
                  <td class="font-weight-bold">{{ p.periodo }}</td>
                  <td class="text-center">{{ p.abonados_medidos }}</td>
                  <td class="text-right font-weight-bold primary--text">{{ parseFloat(p.volumen_total_m3).toFixed(1) }} m³</td>
                  <td class="text-right">Bs {{ parseFloat(p.monto_facturado_bs).toFixed(2) }}</td>
                  <td class="text-right font-weight-bold success--text">Bs {{ parseFloat(p.monto_cobrado_bs).toFixed(2) }}</td>
                  <td class="text-center">
                    <v-progress-linear
                      :value="p.porcentaje_recaudacion"
                      height="18"
                      rounded
                      color="success"
                    >
                      <template v-slot:default>
                        <span class="text-caption font-weight-bold white--text">{{ p.porcentaje_recaudacion }}%</span>
                      </template>
                    </v-progress-linear>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-tab-item>
    </v-tabs-items>

    <!-- MODAL VISOR DE PDF INSTITUCIONAL -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlReportePdf"
      :titulo="tituloVisorPdf"
      @descargar="descargarArchivoPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'ReportesComerciales',
  components: {
    ModalVisorPdf,
  },
  data() {
    const hoy = new Date().toISOString().substr(0, 10);
    return {
      tabActual: 0,
      subTabActual: 0,
      cargandoRecaudacion: false,
      generandoPdf: false,

      // Filtros
      filtros: {
        periodo_tipo: 'hoy',
        fecha_inicio: hoy,
        fecha_fin: hoy,
        id_punto_venta: null,
        id_cajero: null,
        metodo_pago: null,
      },

      // Catálogos para filtros
      cajasLista: [],
      cajerosLista: [],

      // Datos respuesta
      datosConsolidados: null,
      datosMora: null,
      periodosBalance: [],

      // Visor PDF
      mostrarVisorPdf: false,
      urlReportePdf: '',
      tituloVisorPdf: '',
    };
  },
  computed: {
    diferenciaArqueo() {
      if (!this.datosConsolidados || !this.datosConsolidados.metricas) return 0;
      return parseFloat(this.datosConsolidados.metricas.diferencia_neta || 0);
    },
  },
  mounted() {
    if (this.$route.name === 'comercial_sesiones_caja' || this.$route.query.tab === 'turnos' || this.$route.query.tab === 'arqueos') {
      this.tabActual = 0;
      this.subTabActual = 3;
    }
    this.cargarCatalogosFiltros();
    this.cargarRecaudacionConsolidada();
    this.cargarMorosidad();
    this.cargarBalanceConsumo();
  },
  methods: {
    async cargarCatalogosFiltros() {
      try {
        const [respCajas, respCajeros] = await Promise.all([
          axios.get('/api/comercial/caja-sesiones/cajas-disponibles'),
          axios.get('/api/comercial/caja-sesiones/cajeros'),
        ]);
        this.cajasLista = respCajas.data.data || [];
        this.cajerosLista = respCajeros.data.data || [];
      } catch (e) {
        console.error('Error cargando catálogos de filtros:', e);
      }
    },

    cambiarPeriodoRapido(tipo) {
      const hoy = new Date();
      const format = d => d.toISOString().substr(0, 10);

      if (tipo === 'hoy') {
        this.filtros.fecha_inicio = format(hoy);
        this.filtros.fecha_fin = format(hoy);
      } else if (tipo === 'semana') {
        const d = new Date(hoy);
        const diaSemana = d.getDay() || 7;
        d.setDate(d.getDate() - diaSemana + 1); // Lunes
        this.filtros.fecha_inicio = format(d);
        const finSem = new Date(d);
        finSem.setDate(finSem.getDate() + 6); // Domingo
        this.filtros.fecha_fin = format(finSem);
      } else if (tipo === 'mes') {
        const iniMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        this.filtros.fecha_inicio = format(iniMes);
        this.filtros.fecha_fin = format(finMes);
      } else if (tipo === 'mes_anterior') {
        const iniMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const finMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        this.filtros.fecha_inicio = format(iniMesAnt);
        this.filtros.fecha_fin = format(finMesAnt);
      }

      this.cargarRecaudacionConsolidada();
    },

    onFechaChange() {
      this.filtros.periodo_tipo = 'personalizado';
      this.cargarRecaudacionConsolidada();
    },

    construirQueryParams() {
      const p = {
        periodo_tipo: this.filtros.periodo_tipo,
        fecha_inicio: this.filtros.fecha_inicio,
        fecha_fin: this.filtros.fecha_fin,
      };
      if (this.filtros.id_punto_venta) p.id_punto_venta = this.filtros.id_punto_venta;
      if (this.filtros.id_cajero) p.id_cajero = this.filtros.id_cajero;
      if (this.filtros.metodo_pago) p.metodo_pago = this.filtros.metodo_pago;
      return p;
    },

    async cargarRecaudacionConsolidada() {
      this.cargandoRecaudacion = true;
      try {
        const res = await axios.get('/api/comercial/reportes/recaudacion-consolidada', {
          params: this.construirQueryParams(),
        });
        this.datosConsolidados = res.data.data;
      } catch (e) {
        console.error('Error cargando recaudación consolidada:', e);
      } finally {
        this.cargandoRecaudacion = false;
      }
    },

    verPdfConsolidado() {
      const q = new URLSearchParams(this.construirQueryParams()).toString();
      this.urlReportePdf = `/api/comercial/reportes/recaudacion-consolidada/pdf?${q}`;
      this.tituloVisorPdf = `Planilla Oficial de Recaudación Consolidada (${this.filtros.fecha_inicio} al ${this.filtros.fecha_fin})`;
      this.mostrarVisorPdf = true;
    },

    verPdfArqueoIndividual(sesionId) {
      this.urlReportePdf = `/api/comercial/caja-sesiones/${sesionId}/reporte-pdf`;
      this.tituloVisorPdf = `Planilla Oficial de Arqueo de Turno`;
      this.mostrarVisorPdf = true;
    },

    descargarArchivoPdf() {
      if (this.urlReportePdf) {
        window.open(this.urlReportePdf, '_blank');
      }
    },

    exportarCsv() {
      const q = new URLSearchParams(this.construirQueryParams()).toString();
      window.open(`/api/comercial/reportes/recaudacion-consolidada/csv?${q}`, '_blank');
    },

    async cargarMorosidad() {
      try {
        const res = await axios.get('/api/comercial/reportes/morosidad');
        this.datosMora = res.data;
      } catch (e) {
        console.error('Error cargando morosidad:', e);
      }
    },

    async cargarBalanceConsumo() {
      try {
        const res = await axios.get('/api/comercial/reportes/balance-consumo');
        this.periodosBalance = res.data.periodos || [];
      } catch (e) {
        console.error('Error cargando balance consumo:', e);
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
.bg-success-light {
  background-color: #f1f8e9 !important;
  border-color: #a5d6a7 !important;
}
.bg-warning-light {
  background-color: #fffde7 !important;
  border-color: #ffe082 !important;
}
.bg-danger-light {
  background-color: #ffebee !important;
  border-color: #ef9a9a !important;
}
.gap-2 {
  gap: 8px;
}
</style>
