<template>
  <v-card>
    <template>
      <v-dialog v-model="cargando" hide-overlay persistent width="300">
        <v-card color="primary" dark>
          <v-card-text>Cargando datos...
            <v-progress-linear
              indeterminate color="white" class="mb-0">
            </v-progress-linear>
          </v-card-text>
        </v-card>
      </v-dialog>
  </template>
    <v-card-text>
      <v-row>
        <v-col
          v-for="data in statisticsData"
          :key="data.title"
          cols="2"
          md="2"
          class="d-flex align-center"
        >
          <v-avatar
            size="44"
            :color="resolveStatisticsIconVariation (data.nombre).color"
            rounded
            class="elevation-1"
          >
            <v-icon
              dark
              color="white"
              size="30"
            >
              {{ resolveStatisticsIconVariation (data.nombre).icon }}
            </v-icon>
          </v-avatar>
          <div class="ms-3">
            <p class="text-xs mb-0">
              {{ data.nombre}}
            </p>
            <v-list-item-content>
              <v-list-item-subtitle><b>Kg.</b> {{ data.kilogramo }}</v-list-item-subtitle>
              <v-list-item-subtitle><b>Tn.</b> {{ data.toneladas }}</v-list-item-subtitle>
              <v-list-item-subtitle><b>Fanega.</b>{{ data.fanegas }}</v-list-item-subtitle>
            </v-list-item-content>
          </div>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiAccountOutline, mdiCurrencyUsd, mdiTrendingUp, mdiDotsVertical, mdiLabelOutline,mdiCart,mdiRice,mdiSoySauce,mdiCorn,mdiYurt} from '@mdi/js'

export default {
  data: () => ({
    statisticsData:[],
    cargando:false,
  }),
  mounted() {
    this.listarTotalAcopio();
  },
  watch: {

  },
  created() { },
  methods: {
    listarTotalAcopio() {
      this.cargando = true;
      this.statisticsData = [];
      axios.get('api/total_acopio_general', {})
        .then((response) => {
          this.cargando = false;
          this.statisticsData = response.data.data;
        });
    },
  },
  setup() {
    const resolveStatisticsIconVariation = data => {
      if (data === 'ARROZ') return { icon: mdiRice, color: 'primary' }
      if (data === 'TRIGO') return { icon: mdiYurt, color: 'success' }
      if (data === 'SOYA') return { icon: mdiSoySauce, color: 'warning' }
      if (data === 'MAIZ') return { icon: mdiCorn, color: 'info' }
      if (data === 'PISCICOLA') return { icon: mdiYurt, color: 'warning',fanegas:0 }
      if (data === 'PISCICOLA - ALEVINES') return { icon: mdiYurt, color: 'success',fanegas:0 }
      return { icon: mdiAccountOutline, color: 'success' }
    }

    return {
      resolveStatisticsIconVariation,

      // icons
      icons: {
        mdiDotsVertical,
        mdiTrendingUp,
        mdiAccountOutline,
        mdiLabelOutline,
        mdiCurrencyUsd,
        mdiCart,
        mdiRice,
        mdiSoySauce,
        mdiCorn,
        mdiYurt
      },
    }
  },
}
</script>
