<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-calendar-range</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Feriados y Fechas de Corte</h2>
            <span class="text-caption text-secondary">Calendario de feriados nacionales/departamentales y periodos de corte mensual</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="dialogCorte = true">
            <v-icon left small>mdi-calendar-clock</v-icon> Configurar Corte
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="dialogFeriado = true">
            <v-icon left small>mdi-calendar-plus</v-icon> + Nuevo Feriado
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-tabs v-model="activeTab" color="primary" class="px-4 pt-2">
        <v-tab><v-icon left small>mdi-calendar-star</v-icon> Calendario de Feriados ({{ feriados.length }})</v-tab>
        <v-tab><v-icon left small>mdi-calendar-sync</v-icon> Fechas de Corte Mensual ({{ cortes.length }})</v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-tabs-items v-model="activeTab" class="pa-4">
        <!-- PESTAÑA 1: FERIADOS -->
        <v-tab-item>
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center">
              <span class="font-weight-bold text-subtitle-2 mr-2">Gestión / Año:</span>
              <v-select v-model="anioSeleccionado" :items="[2024, 2025, 2026, 2027]" outlined dense hide-details style="max-width: 120px;" @change="cargarFeriados()"></v-select>
            </div>
          </div>

          <v-row v-if="feriados && feriados.length > 0">
            <v-col cols="12" sm="6" md="4" v-for="f in feriados" :key="f.id">
              <v-card outlined rounded="lg" class="pa-3 erp-card-elevated">
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="font-weight-bold text-body-2">{{ f.nombre }}</div>
                  <v-chip x-small :color="f.es_feriado_nacional ? 'primary' : 'purple'" label class="font-weight-bold text-white">
                    {{ f.es_feriado_nacional ? 'NACIONAL' : (f.departamento ? f.departamento.nombre : 'DEPARTAMENTAL') }}
                  </v-chip>
                </div>
                <div class="d-flex align-center text-caption text-secondary">
                  <v-icon x-small color="primary" left>mdi-calendar</v-icon>
                  Fecha: <strong>{{ f.dia }}/{{ f.mes }}/{{ f.anio }}</strong>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-tab-item>

        <!-- PESTAÑA 2: FECHAS DE CORTE -->
        <v-tab-item>
          <v-data-table :headers="headersCortes" :items="cortes" class="elevation-0" no-data-text="No hay fechas de corte configuradas">
            <template v-slot:item.periodo="{ item }">
              <span class="font-weight-bold">{{ getMesNombre(item.mes) }} {{ item.anio }}</span>
            </template>
            <template v-slot:item.rango="{ item }">
              <v-chip small color="primary lighten-5 primary--text" class="font-weight-bold">
                {{ item.fecha_inicio }} &nbsp;➔&nbsp; {{ item.fecha_fin }}
              </v-chip>
            </template>
          </v-data-table>
        </v-tab-item>
      </v-tabs-items>
    </v-card>

    <!-- DIÁLOGO: NUEVO FERIADO -->
    <v-dialog v-model="dialogFeriado" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calendar-plus</v-icon>
          <span>Registrar Feriado</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogFeriado = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formFeriado.nombre" label="Nombre del Feriado *" outlined dense placeholder="Ej. Aniversario Departamental"></v-text-field>
          <v-row dense>
            <v-col cols="4"><v-text-field v-model="formFeriado.dia" label="Día *" type="number" outlined dense></v-text-field></v-col>
            <v-col cols="4"><v-text-field v-model="formFeriado.mes" label="Mes *" type="number" outlined dense></v-text-field></v-col>
            <v-col cols="4"><v-text-field v-model="formFeriado.anio" label="Año *" type="number" outlined dense></v-text-field></v-col>
          </v-row>
          <v-checkbox v-model="formFeriado.es_feriado_nacional" label="Es Feriado Nacional" dense hide-details color="primary"></v-checkbox>
          <v-select v-if="!formFeriado.es_feriado_nacional" v-model="formFeriado.id_departamento" :items="departamentos" item-text="nombre" item-value="id" label="Departamento" outlined dense class="mt-3"></v-select>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogFeriado = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarFeriado()">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: NUEVA FECHA DE CORTE -->
    <v-dialog v-model="dialogCorte" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calendar-clock</v-icon>
          <span>Configurar Periodo de Corte</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCorte = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="6"><v-select v-model="formCorte.mes" :items="meses" item-text="nombre" item-value="id" label="Mes de Planilla *" outlined dense></v-select></v-col>
            <v-col cols="6"><v-text-field v-model="formCorte.anio" label="Año *" type="number" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-text-field v-model="formCorte.fecha_inicio" label="Fecha Inicio *" type="date" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-text-field v-model="formCorte.fecha_fin" label="Fecha Fin *" type="date" outlined dense></v-text-field></v-col>
          </v-row>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogCorte = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarCorte()">Guardar Corte</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }"><v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn></template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'FeriadosCortes',
  data() {
    return {
      activeTab: 0,
      anioSeleccionado: new Date().getFullYear(),
      feriados: [],
      departamentos: [],
      cortes: [],

      dialogFeriado: false,
      formFeriado: {
        nombre: '',
        dia: 1,
        mes: 1,
        anio: new Date().getFullYear(),
        es_feriado_nacional: true,
        id_departamento: null,
      },

      dialogCorte: false,
      formCorte: {
        mes: new Date().getMonth() + 1,
        anio: new Date().getFullYear(),
        fecha_inicio: new Date().toISOString().substr(0, 10),
        fecha_fin: new Date().toISOString().substr(0, 10),
      },

      meses: [
        { id: 1, nombre: 'Enero' }, { id: 2, nombre: 'Febrero' }, { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' }, { id: 5, nombre: 'Mayo' }, { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' }, { id: 8, nombre: 'Agosto' }, { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' }, { id: 11, nombre: 'Noviembre' }, { id: 12, nombre: 'Diciembre' },
      ],

      headersCortes: [
        { text: 'Mes / Gestión', value: 'periodo' },
        { text: 'Rango de Fechas Computables', value: 'rango' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarFeriados();
    this.cargarCortes();
  },
  methods: {
    cargarFeriados() {
      axios.get(`/api/rrhh/feriados?anio=${this.anioSeleccionado}`).then(res => {
        if (res.data && res.data.success) {
          this.feriados = res.data.feriados || [];
          this.departamentos = res.data.departamentos || [];
        }
      });
    },

    cargarCortes() {
      axios.get('/api/rrhh/fechas-corte').then(res => {
        if (res.data && res.data.success) {
          this.cortes = res.data.data || [];
        }
      });
    },

    guardarFeriado() {
      if (!this.formFeriado.nombre) return;
      axios.post('/api/rrhh/feriados', this.formFeriado).then(res => {
        this.dialogFeriado = false;
        this.showSnackbar(res.data.message || 'Feriado registrado', 'success');
        this.cargarFeriados();
      });
    },

    guardarCorte() {
      axios.post('/api/rrhh/fechas-corte', this.formCorte).then(res => {
        this.dialogCorte = false;
        this.showSnackbar(res.data.message || 'Fecha corte configurada', 'success');
        this.cargarCortes();
      });
    },

    getMesNombre(mes) {
      const m = this.meses.find(x => x.id === Number(mes));
      return m ? m.nombre : `Mes ${mes}`;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
