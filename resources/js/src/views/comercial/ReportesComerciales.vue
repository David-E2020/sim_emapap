<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-chart-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Reportes Comerciales y Operativos</h2>
            <span class="text-caption text-secondary">Cuadre de caja diaria, análisis de cartera en mora y balance hídrico mensual</span>
          </div>
        </div>
      </div>
    </v-card>

    <v-tabs v-model="tabActual" color="teal" class="mb-4">
      <v-tab><v-icon left small>mdi-cash-multiple</v-icon> Recaudación Diaria de Caja</v-tab>
      <v-tab><v-icon left small>mdi-account-alert</v-icon> Cartera Vencida y Morosidad</v-tab>
      <v-tab><v-icon left small>mdi-chart-bell-curve</v-icon> Balance de Consumo Mensual</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: RECAUDACIÓN DIARIA -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <v-row dense align="center">
            <v-col cols="12" sm="4">
              <v-text-field
                v-model="fechaRecaudacion"
                label="Fecha de Cuadre *"
                type="date"
                dense
                outlined
                hide-details
                @change="cargarRecaudacionDiaria"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="2">
              <v-btn color="teal" dark class="rounded-pill" :loading="cargandoRecaudacion" @click="cargarRecaudacionDiaria">
                <v-icon left small>mdi-refresh</v-icon> Consultar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <v-row dense class="mb-4" v-if="datosRecaudacion">
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary font-weight-bold">TOTAL COBRADO</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                Bs {{ datosRecaudacion.metricas.total_recaudado.toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary font-weight-bold">AGUA POTABLE</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                Bs {{ datosRecaudacion.metricas.total_agua.toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary font-weight-bold">ALCANTARILLADO</div>
              <div class="text-h4 font-weight-black info--text mt-1">
                Bs {{ datosRecaudacion.metricas.total_alcantarillado.toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary font-weight-bold">DESCUENTO 3ra EDAD</div>
              <div class="text-h4 font-weight-black warning--text mt-1">
                Bs {{ datosRecaudacion.metricas.total_descuentos_ley1886.toFixed(2) }}
              </div>
            </v-card>
          </v-col>
        </v-row>

        <v-card rounded="lg" class="erp-card-elevated" v-if="datosRecaudacion">
          <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
            Resumen de Recaudación por Cajero
          </v-card-title>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th>Cajero / Operador</th>
                  <th class="text-center">Transacciones Realizadas</th>
                  <th class="text-right">Monto Total Recaudado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in datosRecaudacion.por_cajero" :key="c.cajero">
                  <td class="font-weight-bold">{{ c.cajero }}</td>
                  <td class="text-center">{{ c.transacciones }}</td>
                  <td class="text-right font-weight-bold success--text">Bs {{ c.total.toFixed(2) }}</td>
                </tr>
                <tr v-if="datosRecaudacion.por_cajero.length === 0">
                  <td colspan="3" class="text-center text-secondary py-3">No hubo cobranzas en la fecha seleccionada</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 2: MOROSIDAD -->
      <v-tab-item>
        <v-row dense class="mb-4" v-if="datosMora">
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary">Abonados en Mora</div>
              <div class="text-h4 font-weight-black error--text mt-1">{{ datosMora.metricas.total_abonados_mora }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary">Deuda Total Acumulada</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                Bs {{ datosMora.metricas.total_deuda_acumulada.toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary">Mora 2 Meses (Corte)</div>
              <div class="text-h4 font-weight-black warning--text mt-1">{{ datosMora.metricas.mora_2_meses }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center" rounded="lg" elevation="1">
              <div class="text-caption text-secondary">Mora >= 3 Meses</div>
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
                <tr>
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

      <!-- PESTAÑA 3: BALANCE DE CONSUMO -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
            Balance Hídrico y Recaudación por Periodo
          </v-card-title>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
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
                  <td class="text-right font-weight-bold primary--text">{{ p.volumen_total_m3.toFixed(1) }} m³</td>
                  <td class="text-right">Bs {{ p.monto_facturado_bs.toFixed(2) }}</td>
                  <td class="text-right font-weight-bold success--text">Bs {{ p.monto_cobrado_bs.toFixed(2) }}</td>
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
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReportesComerciales',
  data() {
    return {
      tabActual: 0,
      cargandoRecaudacion: false,
      fechaRecaudacion: new Date().toISOString().substr(0, 10),
      datosRecaudacion: null,
      datosMora: null,
      periodosBalance: [],
    };
  },
  mounted() {
    this.cargarRecaudacionDiaria();
    this.cargarMorosidad();
    this.cargarBalanceConsumo();
  },
  methods: {
    async cargarRecaudacionDiaria() {
      this.cargandoRecaudacion = true;
      try {
        const res = await axios.get('/api/comercial/reportes/recaudacion-diaria', {
          params: { fecha: this.fechaRecaudacion },
        });
        this.datosRecaudacion = res.data;
      } catch (e) {
        console.error('Error cargando recaudación:', e);
      } finally {
        this.cargandoRecaudacion = false;
      }
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
</style>
