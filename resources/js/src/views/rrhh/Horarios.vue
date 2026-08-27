<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-timetable</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Gestión de Horarios y Turnos</h2>
            <span class="text-caption text-secondary">Definición de turnos laborales, tolerancias y asignación de horarios a funcionarios</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalAsignacion()">
            <v-icon left small>mdi-account-clock</v-icon> Asignar Horario
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalHorario()">
            <v-icon left small>mdi-plus</v-icon> + Nuevo Horario
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-tabs v-model="activeTab" color="primary" class="px-4 pt-2">
        <v-tab><v-icon left small>mdi-clock-outline</v-icon> Tipos de Horario</v-tab>
        <v-tab><v-icon left small>mdi-account-multiple-check</v-icon> Asignaciones de Personal</v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-tabs-items v-model="activeTab" class="pa-4">
        <!-- PESTAÑA 1: HORARIOS -->
        <v-tab-item>
          <v-row v-if="horarios && horarios.length > 0">
            <v-col cols="12" md="6" v-for="h in horarios" :key="h.id">
              <v-card outlined rounded="lg" class="pa-4 erp-card-elevated">
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="font-weight-bold text-subtitle-1">{{ h.nombre }}</div>
                  <v-chip small color="primary" label class="font-weight-bold">{{ h.tipo }}</v-chip>
                </div>
                <div class="text-caption text-secondary mb-3">
                  Tolerancia de ingreso: <strong>{{ h.tolerancia_minutos }} minutos</strong>
                </div>
                <v-divider class="mb-3"></v-divider>
                <div class="text-subtitle-2 font-weight-bold mb-2">Turnos / Períodos:</div>
                <div v-for="p in h.periodos" :key="p.id" class="d-flex align-center justify-space-between py-1 px-2 mb-1 grey lighten-4 rounded">
                  <span class="text-caption font-weight-bold">Período #{{ p.orden }}:</span>
                  <span class="text-caption">
                    <v-icon x-small color="success">mdi-login</v-icon> {{ p.hora_inicio }} &nbsp;➔&nbsp;
                    <v-icon x-small color="info">mdi-logout</v-icon> {{ p.hora_fin }}
                  </span>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-tab-item>

        <!-- PESTAÑA 2: ASIGNACIONES -->
        <v-tab-item>
          <v-data-table
            :headers="headersAsignaciones"
            :items="asignaciones"
            :loading="loadingAsignaciones"
            class="elevation-0"
            no-data-text="No hay asignaciones registradas"
          >
            <template v-slot:item.funcionario="{ item }">
              <div class="font-weight-bold">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido || '' }}</div>
              <div class="text-caption text-secondary">CI: {{ item.nro_documento }}</div>
            </template>
            <template v-slot:item.horario="{ item }">
              <v-chip small color="primary lighten-5 primary--text" class="font-weight-bold">
                {{ item.horario_nombre }} ({{ item.horario_tipo }})
              </v-chip>
            </template>
            <template v-slot:item.vigencia="{ item }">
              <span class="text-caption" v-if="item.permanente">Indefinido / Permanente</span>
              <span class="text-caption" v-else>{{ item.fecha_inicio }} al {{ item.fecha_fin }}</span>
            </template>
          </v-data-table>
        </v-tab-item>
      </v-tabs-items>
    </v-card>

    <!-- DIÁLOGO: NUEVO HORARIO -->
    <v-dialog v-model="dialogHorario" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-timetable</v-icon>
          <span>Nuevo Horario Laboral</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogHorario = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formHorario.nombre" label="Nombre del Horario *" outlined dense placeholder="Ej. Horario Continuo Central"></v-text-field>
          <v-select v-model="formHorario.tipo" :items="['CONTINUO', 'DISCONTINUO', 'ESPECIAL']" label="Tipo de Jornada *" outlined dense></v-select>
          <v-text-field v-model="formHorario.tolerancia_minutos" type="number" label="Tolerancia de Entrada (minutos) *" outlined dense></v-text-field>

          <v-divider class="my-3"></v-divider>
          <div class="font-weight-bold text-subtitle-2 mb-2">Turnos de Entrada y Salida:</div>
          <div v-for="(p, idx) in formHorario.periodos" :key="idx" class="d-flex align-center gap-2 mb-2">
            <v-text-field v-model="p.hora_inicio" label="Entrada" type="time" outlined dense class="mr-2"></v-text-field>
            <v-text-field v-model="p.hora_fin" label="Salida" type="time" outlined dense></v-text-field>
          </div>
          <v-btn small text color="primary" class="text-capitalize" @click="agregarPeriodo()">+ Agregar Turno</v-btn>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogHorario = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarHorario()">Guardar Horario</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: ASIGNAR HORARIO -->
    <v-dialog v-model="dialogAsignacion" max-width="550" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-clock</v-icon>
          <span>Asignar Horario a Personal</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogAsignacion = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-select v-model="formAsig.id_horario" :items="horarios" item-text="nombre" item-value="id" label="Horario a Asignar *" outlined dense></v-select>
          <v-autocomplete v-model="formAsig.personas_ids" :items="personalList" item-text="nombre_completo" item-value="id" label="Seleccionar Funcionarios *" multiple chips small-chips deletable-chips outlined dense></v-autocomplete>
          <v-text-field v-model="formAsig.fecha_inicio" label="Fecha Inicio Vigencia *" type="date" outlined dense></v-text-field>
          <v-checkbox v-model="formAsig.permanente" label="Asignación Permanente (Indefinida)" dense hide-details color="primary"></v-checkbox>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogAsignacion = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarAsignacion()">Guardar Asignación</v-btn>
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
  name: 'Horarios',
  data() {
    return {
      activeTab: 0,
      horarios: [],
      asignaciones: [],
      loadingAsignaciones: false,
      personalList: [],

      dialogHorario: false,
      formHorario: {
        nombre: '',
        tipo: 'CONTINUO',
        tolerancia_minutos: 10,
        periodos: [{ hora_inicio: '08:30', hora_fin: '16:30' }],
      },

      dialogAsignacion: false,
      formAsig: {
        id_horario: null,
        personas_ids: [],
        fecha_inicio: new Date().toISOString().substr(0, 10),
        permanente: true,
      },

      headersAsignaciones: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Horario Asignado', value: 'horario' },
        { text: 'Tolerancia', value: 'tolerancia_minutos', align: 'center' },
        { text: 'Vigencia', value: 'vigencia' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarHorarios();
    this.cargarAsignaciones();
    this.cargarPersonal();
  },
  methods: {
    cargarHorarios() {
      axios.get('/api/rrhh/horarios').then(res => {
        if (res.data && res.data.success) {
          this.horarios = res.data.data || [];
        }
      });
    },

    cargarAsignaciones() {
      this.loadingAsignaciones = true;
      axios.get('/api/rrhh/asignaciones-horarios').then(res => {
        this.loadingAsignaciones = false;
        if (res.data && res.data.success) {
          this.asignaciones = res.data.data || [];
        }
      }).catch(() => { this.loadingAsignaciones = false; });
    },

    cargarPersonal() {
      axios.get('/api/rrhh/personal?per_page=100').then(res => {
        if (res.data && res.data.success) {
          this.personalList = (res.data.data || []).map(p => ({
            id: p.id,
            nombre_completo: `${p.nombres} ${p.primer_apellido || ''} (CI: ${p.nro_documento})`,
          }));
        }
      });
    },

    abrirModalHorario() {
      this.formHorario = {
        nombre: '',
        tipo: 'CONTINUO',
        tolerancia_minutos: 10,
        periodos: [{ hora_inicio: '08:30', hora_fin: '16:30' }],
      };
      this.dialogHorario = true;
    },

    agregarPeriodo() {
      this.formHorario.periodos.push({ hora_inicio: '14:30', hora_fin: '18:30' });
    },

    guardarHorario() {
      if (!this.formHorario.nombre) return;
      axios.post('/api/rrhh/horarios', this.formHorario).then(res => {
        this.dialogHorario = false;
        this.showSnackbar(res.data.message || 'Horario creado', 'success');
        this.cargarHorarios();
      });
    },

    abrirModalAsignacion() {
      this.formAsig = {
        id_horario: null,
        personas_ids: [],
        fecha_inicio: new Date().toISOString().substr(0, 10),
        permanente: true,
      };
      this.dialogAsignacion = true;
    },

    guardarAsignacion() {
      if (!this.formAsig.id_horario || this.formAsig.personas_ids.length === 0) return;
      axios.post('/api/rrhh/asignaciones-horarios', this.formAsig).then(res => {
        this.dialogAsignacion = false;
        this.showSnackbar(res.data.message || 'Horarios asignados', 'success');
        this.cargarAsignaciones();
      });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
