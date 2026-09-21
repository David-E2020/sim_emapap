<template>
  <div class="d-flex align-center flex-wrap gap-2">
    <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
    <v-btn-toggle
      :value="value"
      mandatory
      dense
      :color="color"
      @change="onPeriodoChange"
    >
      <v-btn small value="hoy" class="text-capitalize">Hoy</v-btn>
      <v-btn small value="semana" class="text-capitalize">Esta Semana</v-btn>
      <v-btn small value="mes" class="text-capitalize">Este Mes</v-btn>
      <v-btn small value="mes_anterior" class="text-capitalize">Mes Anterior</v-btn>
      <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
    </v-btn-toggle>
  </div>
</template>

<script>
export default {
  name: 'ErpPeriodoRapido',
  props: {
    value: {
      type: String,
      default: 'mes',
    },
    color: {
      type: String,
      default: 'primary',
    },
  },
  methods: {
    onPeriodoChange(val) {
      const hoy = new Date();
      const format = d => d.toISOString().substr(0, 10);
      let fecha_inicio = format(hoy);
      let fecha_fin = format(hoy);

      if (val === 'hoy') {
        fecha_inicio = format(hoy);
        fecha_fin = format(hoy);
      } else if (val === 'semana') {
        const d = new Date(hoy);
        const diaSemana = d.getDay() || 7;
        d.setDate(d.getDate() - diaSemana + 1); // Lunes
        fecha_inicio = format(d);
        const finSem = new Date(d);
        finSem.setDate(finSem.getDate() + 6); // Domingo
        fecha_fin = format(finSem);
      } else if (val === 'mes') {
        const iniMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        fecha_inicio = format(iniMes);
        fecha_fin = format(finMes);
      } else if (val === 'mes_anterior') {
        const iniMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const finMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        fecha_inicio = format(iniMesAnt);
        fecha_fin = format(finMesAnt);
      }

      this.$emit('input', val);
      this.$emit('change', val, { fecha_inicio, fecha_fin, mes: hoy.getMonth() + 1, anio: hoy.getFullYear() });
    },
  },
};
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style>
