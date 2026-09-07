<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-view-dashboard-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Dashboard de Correspondencia y KPIs</h2>
            <span class="text-caption text-secondary">Métricas operativas de trámites, tiempos de atención y volumen documental</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn icon color="secondary" @click="cargarMetricas"><v-icon>mdi-refresh</v-icon></v-btn>
        </div>
      </div>
    </v-card>

    <!-- CARDS DE METRICAS DEL FUNCIONARIO -->
    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="3" v-for="(card, idx) in cardsFuncionario" :key="idx">
        <v-card rounded="lg" class="erp-card-elevated pa-4" :style="'border-left: 6px solid var(--v-' + card.color + '-base, #1976D2);'">
          <div class="d-flex justify-space-between align-center">
            <div>
              <div class="text-caption text-secondary font-weight-bold">{{ card.titulo }}</div>
              <div class="text-h4 font-weight-black mt-1">{{ card.contador }}</div>
            </div>
            <v-avatar :color="card.color" size="48" rounded="lg" class="elevation-2">
              <v-icon color="white">{{ card.icon }}</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- METRICAS GLOBALES Y GRAFICAS -->
    <v-row>
      <!-- EFICIENCIA GLOBAL -->
      <v-col cols="12" md="4">
        <v-card rounded="lg" class="erp-card-elevated pa-4 fill-height">
          <h3 class="text-subtitle-1 font-weight-bold mb-3 primary--text">Eficiencia Institucional</h3>
          <div class="text-center py-4">
            <v-progress-circular
              :value="metricasGlobal.porcentaje_concluidas || 0"
              size="130"
              width="14"
              color="success"
            >
              <span class="text-h5 font-weight-bold">{{ metricasGlobal.porcentaje_concluidas || 0 }}%</span>
            </v-progress-circular>
            <div class="text-caption text-secondary mt-3 font-weight-medium">
              {{ metricasGlobal.concluidas || 0 }} de {{ metricasGlobal.total_hojas_ruta || 0 }} expedientes concluidos
            </div>
          </div>
          <v-divider class="my-2"></v-divider>
          <div class="d-flex justify-space-between text-caption font-weight-bold text-secondary">
            <span>Docs Firmados: {{ metricasGlobal.documentos_firmados || 0 }}</span>
            <span>Despachos Pendientes: {{ metricasGlobal.despachos_pendientes || 0 }}</span>
          </div>
        </v-card>
      </v-col>

      <!-- DISTRIBUCIÓN POR ORIGEN -->
      <v-col cols="12" md="8">
        <v-card rounded="lg" class="erp-card-elevated pa-4 fill-height">
          <h3 class="text-subtitle-1 font-weight-bold mb-3 primary--text">Distribución de Trámites por Origen</h3>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Canal de Origen</th>
                  <th class="text-center">Total Expedientes</th>
                  <th class="text-right">Participación</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="orig in chartData.por_origen" :key="orig.origen">
                  <td class="font-weight-bold">
                    <v-icon small class="mr-1" color="primary">
                      {{ orig.origen === 'INTERNO' ? 'mdi-office-building' : (orig.origen === 'VENTANILLA_DIGITAL' ? 'mdi-web' : 'mdi-domain') }}
                    </v-icon>
                    {{ orig.origen }}
                  </td>
                  <td class="text-center font-weight-bold">{{ orig.total }}</td>
                  <td class="text-right">
                    <v-chip x-small label color="blue lighten-5" text-color="primary" class="font-weight-bold">
                      {{ metricasGlobal.total_hojas_ruta > 0 ? ((orig.total / metricasGlobal.total_hojas_ruta) * 100).toFixed(1) : 0 }}%
                    </v-chip>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'DashboardCorrespondencia',
  data() {
    return {
      cardsFuncionario: [],
      metricasGlobal: {},
      chartData: {
        por_origen: [],
        por_prioridad: [],
        por_estado: [],
      },
      cargando: false,
    };
  },
  mounted() {
    this.cargarMetricas();
  },
  methods: {
    async cargarMetricas() {
      this.cargando = true;
      try {
        const [resCards, resCharts] = await Promise.all([
          window.axios.get('/api/correspondencia/dashboard/cards'),
          window.axios.get('/api/correspondencia/dashboard/charts'),
        ]);

        if (resCards.data && resCards.data.success) {
          this.cardsFuncionario = resCards.data.data.funcionario;
          this.metricasGlobal = resCards.data.data.global;
        }

        if (resCharts.data && resCharts.data.success) {
          this.chartData = resCharts.data.data;
        }
      } catch (e) {
      } finally {
        this.cargando = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
</style>
