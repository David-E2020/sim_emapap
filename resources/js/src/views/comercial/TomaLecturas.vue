<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="info" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-counter</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Toma de Lecturas Mensuales</h2>
            <span class="text-caption text-secondary">Captura de consumos de medidores por zona, control de saltos y liquidación masiva</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn outlined color="primary" class="text-capitalize rounded-pill elevation-1" @click="modalAbrirPeriodo = true">
            <v-icon left small>mdi-calendar-plus</v-icon> Abrir Nuevo Periodo
          </v-btn>
          <v-btn
            color="success"
            class="text-capitalize rounded-pill elevation-2"
            :disabled="!periodoSeleccionado"
            :loading="liquidando"
            @click="liquidarPeriodoActual"
          >
            <v-icon left small>mdi-check-all</v-icon> Liquidar y Facturar Mes
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BARRA DE SELECCIÓN DE PERIODO Y FILTROS -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="periodoSeleccionado"
            :items="periodos"
            item-text="periodo"
            item-value="id"
            label="Seleccionar Periodo *"
            prepend-inner-icon="mdi-calendar-month"
            dense
            outlined
            hide-details
            @change="cargarPlanilla"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtroZona"
            :items="zonas"
            item-text="nombre"
            item-value="id"
            label="Filtrar por Zona"
            dense
            outlined
            hide-details
            clearable
            @change="cargarPlanilla"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="4">
          <v-text-field
            v-model="busquedaPlanilla"
            label="Buscar por Código o Nombre en la lista..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="2" class="text-right">
          <v-btn
            color="primary"
            class="text-capitalize rounded-pill"
            :loading="guardandoLote"
            :disabled="lecturasModificadas.length === 0"
            @click="guardarCambiosLote"
          >
            <v-icon left small>mdi-content-save</v-icon> Guardar ({{ lecturasModificadas.length }})
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- RESUMEN DEL PERIODO -->
    <v-row dense class="mb-4" v-if="periodoInfo">
      <v-col cols="12" sm="4">
        <v-card class="pa-3 text-center" rounded="lg" elevation="1">
          <div class="text-caption text-secondary">Abonados en Planilla</div>
          <div class="text-h5 font-weight-black primary--text">{{ planilla.length }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-3 text-center" rounded="lg" elevation="1">
          <div class="text-caption text-secondary">Lecturas con Consumo Registrado</div>
          <div class="text-h5 font-weight-black success--text">{{ lecturasConConsumo }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-3 text-center" rounded="lg" elevation="1">
          <div class="text-caption text-secondary">Volumen Total Estimado</div>
          <div class="text-h5 font-weight-black info--text">{{ volumenTotalM3.toFixed(1) }} m³</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA PLANILLA DE LECTURACIÓN -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="columnas"
        :items="planillaFiltrada"
        :loading="cargandoPlanilla"
        :items-per-page="20"
        class="elevation-0"
      >
        <!-- Código -->
        <template v-slot:item.abonado.codigo="{ item }">
          <v-chip small outlined color="primary" class="font-weight-bold">
            {{ item.abonado ? item.abonado.codigo : '-' }}
          </v-chip>
        </template>

        <!-- Nombre -->
        <template v-slot:item.abonado.nombre_completo="{ item }">
          <div class="font-weight-medium">{{ item.abonado ? item.abonado.nombre_completo : '-' }}</div>
          <span class="text-caption text-secondary">
            {{ item.abonado && item.abonado.zona ? item.abonado.zona.nombre : '' }} {{ item.abonado && item.abonado.calle ? ' - ' + item.abonado.calle.nombre : '' }}
          </span>
        </template>

        <!-- N° Medidor -->
        <template v-slot:item.medidor="{ item }">
          <span v-if="item.medidor" class="text-caption font-weight-medium">
            <v-icon x-small color="blue">mdi-counter</v-icon> {{ item.medidor.numero_serie }}
          </span>
          <span v-else class="text-caption text-secondary">Sin medidor</span>
        </template>

        <!-- Lectura Anterior -->
        <template v-slot:item.lectura_anterior="{ item }">
          <span class="font-weight-bold grey--text text--darken-2">
            {{ parseFloat(item.lectura_anterior).toFixed(1) }} m³
          </span>
        </template>

        <!-- Lectura Actual (Input Reactivo) -->
        <template v-slot:item.lectura_actual="{ item }">
          <v-text-field
            v-model.number="item.lectura_actual"
            type="number"
            min="0"
            step="1"
            dense
            outlined
            hide-details
            class="lectura-input"
            @input="marcarModificado(item)"
          ></v-text-field>
        </template>

        <!-- Consumo Calculado -->
        <template v-slot:item.consumo_m3="{ item }">
          <div class="font-weight-bold text-center">
            <span :class="consumoColor(item)">
              {{ calcularConsumo(item).toFixed(1) }} m³
            </span>
            <v-icon x-small color="warning" v-if="calcularConsumo(item) > 40" title="Consumo alto detectado">
              mdi-alert
            </v-icon>
          </div>
        </template>

        <!-- Total Facturado Estimado -->
        <template v-slot:item.total_facturado="{ item }">
          <div class="text-right font-weight-bold">
            Bs {{ parseFloat(item.total_facturado || 0).toFixed(2) }}
          </div>
        </template>

        <!-- Estado Pago -->
        <template v-slot:item.estado_pago="{ item }">
          <v-chip x-small :color="item.estado_pago === 'PAGADO' ? 'success' : 'warning'" text-color="white">
            {{ item.estado_pago }}
          </v-chip>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL ABRIR NUEVO PERIODO -->
    <v-dialog v-model="modalAbrirPeriodo" max-width="500">
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon color="white" class="mr-2">mdi-calendar-plus</v-icon>
          Abrir Nuevo Periodo de Consumo
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="6">
              <v-select
                v-model="nuevoPeriodo.mes"
                :items="meses"
                item-text="nombre"
                item-value="numero"
                label="Mes *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model.number="nuevoPeriodo.gestion"
                label="Gestión / Año *"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="nuevoPeriodo.fecha_inicio_consumo"
                label="Fecha Inicio Consumo *"
                type="date"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="nuevoPeriodo.fecha_fin_consumo"
                label="Fecha Fin Consumo *"
                type="date"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="nuevoPeriodo.fecha_vencimiento_pago"
                label="Fecha Límite de Pago *"
                type="date"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalAbrirPeriodo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="abriendoPeriodo" @click="crearNuevoPeriodo">Abrir Periodo</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TomaLecturas',
  data() {
    return {
      cargandoPlanilla: false,
      guardandoLote: false,
      liquidando: false,
      abriendoPeriodo: false,
      modalAbrirPeriodo: false,
      periodos: [],
      zonas: [],
      periodoSeleccionado: null,
      filtroZona: null,
      busquedaPlanilla: '',
      planilla: [],
      lecturasModificadas: [],
      columnas: [
        { text: 'Código', value: 'abonado.codigo', width: '90px' },
        { text: 'Abonado y Dirección', value: 'abonado.nombre_completo' },
        { text: 'Medidor', value: 'medidor', width: '120px' },
        { text: 'Lectura Anterior', value: 'lectura_anterior', width: '130px', align: 'end' },
        { text: 'Lectura Actual', value: 'lectura_actual', width: '140px', align: 'center' },
        { text: 'Consumo m³', value: 'consumo_m3', width: '120px', align: 'center' },
        { text: 'Importe Bs', value: 'total_facturado', width: '110px', align: 'end' },
        { text: 'Estado', value: 'estado_pago', width: '100px', align: 'center' },
      ],
      nuevoPeriodo: {
        mes: new Date().getMonth() + 1,
        gestion: new Date().getFullYear(),
        fecha_inicio_consumo: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substr(0, 10),
        fecha_fin_consumo: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().substr(0, 10),
        fecha_vencimiento_pago: new Date(new Date().getFullYear(), new Date().getMonth() + 2, 15).toISOString().substr(0, 10),
      },
      meses: [
        { numero: 1, nombre: 'Enero' }, { numero: 2, nombre: 'Febrero' }, { numero: 3, nombre: 'Marzo' },
        { numero: 4, nombre: 'Abril' }, { numero: 5, nombre: 'Mayo' }, { numero: 6, nombre: 'Junio' },
        { numero: 7, nombre: 'Julio' }, { numero: 8, nombre: 'Agosto' }, { numero: 9, nombre: 'Septiembre' },
        { numero: 10, nombre: 'Octubre' }, { numero: 11, nombre: 'Noviembre' }, { numero: 12, nombre: 'Diciembre' },
      ],
    };
  },
  computed: {
    periodoInfo() {
      return this.periodos.find(p => p.id === this.periodoSeleccionado);
    },
    planillaFiltrada() {
      if (!this.busquedaPlanilla) return this.planilla;
      const b = this.busquedaPlanilla.toLowerCase();
      return this.planilla.filter(
        item => (item.abonado?.nombre_completo || '').toLowerCase().includes(b) ||
                (item.abonado?.codigo || '').includes(b)
      );
    },
    lecturasConConsumo() {
      return this.planilla.filter(item => parseFloat(item.consumo_m3) > 0 || parseFloat(item.lectura_actual) > parseFloat(item.lectura_anterior)).length;
    },
    volumenTotalM3() {
      return this.planilla.reduce((sum, item) => sum + this.calcularConsumo(item), 0);
    },
  },
  mounted() {
    this.cargarPeriodos();
    this.cargarZonas();
  },
  methods: {
    calcularConsumo(item) {
      const ant = parseFloat(item.lectura_anterior) || 0;
      const act = parseFloat(item.lectura_actual) || 0;
      return Math.max(0, act - ant);
    },
    consumoColor(item) {
      const c = this.calcularConsumo(item);
      if (c === 0) return 'text-secondary';
      if (c > 35) return 'error--text';
      if (c > 15) return 'warning--text text--darken-2';
      return 'success--text text--darken-2';
    },
    marcarModificado(item) {
      if (!this.lecturasModificadas.includes(item.id)) {
        this.lecturasModificadas.push(item.id);
      }
    },
    async cargarPeriodos() {
      try {
        const res = await axios.get('/api/comercial/periodos');
        this.periodos = res.data.data || [];
        if (this.periodos.length > 0 && !this.periodoSeleccionado) {
          this.periodoSeleccionado = this.periodos[0].id;
          this.cargarPlanilla();
        }
      } catch (e) {
        console.error('Error cargando periodos:', e);
      }
    },
    async cargarZonas() {
      try {
        const res = await axios.get('/api/comercial/zonas');
        this.zonas = res.data.data || [];
      } catch (e) {
        console.error('Error cargando zonas:', e);
      }
    },
    async cargarPlanilla() {
      if (!this.periodoSeleccionado) return;
      this.cargandoPlanilla = true;
      this.lecturasModificadas = [];
      try {
        const res = await axios.get(`/api/comercial/periodos/${this.periodoSeleccionado}/planilla`, {
          params: { id_zona: this.filtroZona || undefined },
        });
        this.planilla = res.data.data || [];
      } catch (e) {
        console.error('Error cargando planilla:', e);
      } finally {
        this.cargandoPlanilla = false;
      }
    },
    async guardarCambiosLote() {
      if (this.lecturasModificadas.length === 0) return;
      this.guardandoLote = true;

      const payload = this.planilla
        .filter(item => this.lecturasModificadas.includes(item.id))
        .map(item => ({
          id: item.id,
          lectura_actual: item.lectura_actual,
          es_estimada: false,
        }));

      try {
        await axios.post('/api/comercial/lecturas/lote', { lecturas: payload });
        this.lecturasModificadas = [];
        this.cargarPlanilla();
        alert('Lecturas guardadas y calculadas exitosamente.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al guardar lote de lecturas.');
      } finally {
        this.guardandoLote = false;
      }
    },
    async liquidarPeriodoActual() {
      if (!confirm('¿Confirma liquidar y facturar todas las lecturas del periodo actual?')) return;
      this.liquidando = true;
      try {
        await axios.post(`/api/comercial/periodos/${this.periodoSeleccionado}/liquidar`);
        alert('Periodo liquidado y facturado exitosamente.');
        this.cargarPlanilla();
      } catch (e) {
        alert(e.response?.data?.message || 'Error al liquidar periodo.');
      } finally {
        this.liquidando = false;
      }
    },
    async crearNuevoPeriodo() {
      this.abriendoPeriodo = true;
      try {
        const res = await axios.post('/api/comercial/periodos/abrir', this.nuevoPeriodo);
        this.modalAbrirPeriodo = false;
        await this.cargarPeriodos();
        this.periodoSeleccionado = res.data.data.id;
        this.cargarPlanilla();
        alert('Nuevo periodo abierto exitosamente.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al abrir periodo.');
      } finally {
        this.abriendoPeriodo = false;
      }
    },
  },
};
</script>

<style scoped>
.lectura-input {
  max-width: 120px;
  margin: 0 auto;
}
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
</style>
