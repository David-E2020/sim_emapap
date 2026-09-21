<template>
  <v-card
    class="pa-3 text-center erp-card-elevated"
    rounded="lg"
    :class="bgClass"
  >
    <div class="text-caption text-secondary font-weight-bold text-uppercase">
      {{ titulo }}
    </div>
    <div class="text-h4 font-weight-black mt-1" :class="valorColor">
      {{ valor }}
    </div>
    <div v-if="subtitulo" class="text-caption text-secondary mt-1 font-weight-medium">
      {{ subtitulo }}
    </div>
    <div v-if="chipTexto" class="mt-1">
      <v-chip
        x-small
        :color="chipColor || 'success'"
        text-color="white"
        class="font-weight-bold"
      >
        {{ chipTexto }}
      </v-chip>
    </div>
  </v-card>
</template>

<script>
export default {
  name: 'ErpKpiCard',
  props: {
    titulo: {
      type: String,
      required: true,
    },
    valor: {
      type: [String, Number],
      default: '0',
    },
    subtitulo: {
      type: String,
      default: '',
    },
    valorColor: {
      type: String,
      default: 'primary--text',
    },
    bgClass: {
      type: String,
      default: '',
    },
    chipTexto: {
      type: String,
      default: '',
    },
    chipColor: {
      type: String,
      default: 'success',
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.erp-card-elevated:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.08);
}
</style>
