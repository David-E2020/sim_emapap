<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-airplane</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Comisiones, Omisiones y Aprobaciones</h2>
            <span class="text-caption text-secondary">Viajes oficiales, viáticos, regularización de marcaciones y bandeja de firmas de supervisores</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="dialogOmision = true">
            <v-icon left small>mdi-clock-alert-outline</v-icon> Regularizar Omisión
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="dialogComision = true">
            <v-icon left small>mdi-airplane-takeoff</v-icon> + Nueva Comisión
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-tabs v-model="activeTab" color="primary" class="px-4 pt-2">
        <v-tab><v-icon left small>mdi-inbox-arrow-down</v-icon> Bandeja de Aprobaciones ({{ pendientes.length }})</v-tab>
        <v-tab><v-icon left small>mdi-airplane</v-icon> Comisiones de Viaje</v-tab>
        <v-tab><v-icon left small>mdi-clock-alert</v-icon> Omisiones de Marcado</v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-tabs-items v-model="activeTab" class="pa-4">
        <!-- PESTAÑA 1: BANDEJA DE APROBACIONES -->
        <v-tab-item>
          <v-data-table
            :headers="headersAprobacion"
            :items="pendientes"
            class="elevation-0"
            no-data-text="No hay solicitudes pendientes de firma"
          >
            <template v-slot:item.funcionario="{ item }">
              <div class="font-weight-bold">{{ item.nombres }} {{ item.primer_apellido }}</div>
              <div class="text-caption text-secondary">CI: {{ item.nro_documento }}</div>
            </template>

            <template v-slot:item.tipo="{ item }">
              <v-chip small color="primary lighten-5 primary--text" class="font-weight-bold">
                {{ item.permiso_nombre }} ({{ item.permiso_sigla }})
              </v-chip>
              <div class="text-caption text-secondary mt-1">{{ item.motivo }}</div>
            </template>

            <template v-slot:item.fechas="{ item }">
              <span class="text-caption">{{ item.fecha_inicio }} al {{ item.fecha_fin }}</span>
            </template>

            <template v-slot:item.estado="{ item }">
              <v-chip x-small :color="getEstadoColor(item.estado_aprobacion)" label class="font-weight-bold text-white">
                {{ item.estado_aprobacion }}
              </v-chip>
            </template>

            <template v-slot:item.acciones="{ item }">
              <div class="d-flex align-center gap-1" v-if="item.estado_aprobacion === 'PENDIENTE'">
                <v-btn x-small color="success" class="text-capitalize mr-1 rounded-pill" @click="resolver(item, 'APROBADO')">
                  <v-icon left x-small>mdi-check</v-icon> Aprobar
                </v-btn>
                <v-btn x-small color="error" outlined class="text-capitalize rounded-pill" @click="resolver(item, 'RECHAZADO')">
                  <v-icon left x-small>mdi-close</v-icon> Rechazar
                </v-btn>
              </div>
              <span v-else class="text-caption text-secondary font-italic">Revisado</span>
            </template>
          </v-data-table>
        </v-tab-item>

        <!-- PESTAÑA 2: COMISIONES DE VIAJE -->
        <v-tab-item>
          <v-data-table :headers="headersComisiones" :items="comisiones" class="elevation-0" no-data-text="No hay comisiones registradas">
            <template v-slot:item.cite="{ item }">
              <v-chip small label color="primary lighten-5 primary--text" class="font-weight-bold">{{ item.cite }}</v-chip>
            </template>
            <template v-slot:item.fechas="{ item }">
              <span class="text-caption">{{ item.fecha_inicio }} al {{ item.fecha_fin }}</span>
            </template>
            <template v-slot:item.estado="{ item }">
              <v-chip x-small :color="getEstadoColor(item._estado)" label class="font-weight-bold text-white">{{ item._estado }}</v-chip>
            </template>
          </v-data-table>
        </v-tab-item>

        <!-- PESTAÑA 3: OMISIONES DE MARCADO -->
        <v-tab-item>
          <v-data-table :headers="headersOmisiones" :items="omisiones" class="elevation-0" no-data-text="No hay omisiones registradas">
            <template v-slot:item.cite="{ item }">
              <v-chip small label color="warning lighten-5 warning--text" class="font-weight-bold">{{ item.cite }}</v-chip>
            </template>
            <template v-slot:item.turno="{ item }">
              <span class="text-caption font-weight-bold">{{ item.turno_periodo }} ({{ item.hora_marcado_omision }})</span>
            </template>
            <template v-slot:item.estado="{ item }">
              <v-chip x-small :color="getEstadoColor(item._estado)" label class="font-weight-bold text-white">{{ item._estado }}</v-chip>
            </template>
          </v-data-table>
        </v-tab-item>
      </v-tabs-items>
    </v-card>

    <!-- DIÁLOGO: NUEVA COMISIÓN -->
    <v-dialog v-model="dialogComision" max-width="550" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-airplane</v-icon>
          <span>Registrar Comisión Oficial de Viaje</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogComision = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-autocomplete v-model="formCom.id_persona" :items="personalList" item-text="nombre_completo" item-value="id" label="Funcionario en Comisión *" outlined dense></v-autocomplete>
          <v-text-field v-model="formCom.lugar" label="Destino / Lugar de Comisión *" outlined dense placeholder="Ej. Planta Silos San Pedro (Santa Cruz)"></v-text-field>
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="formCom.fecha_inicio" label="Fecha Salida *" type="date" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-text-field v-model="formCom.fecha_fin" label="Fecha Retorno *" type="date" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-text-field v-model="formCom.monto_viatico" label="Monto Viático (Bs.)" type="number" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-select v-model="formCom.transporte" :items="['TERRESTRE', 'AEREO', 'MIXTO']" label="Medio Transporte" outlined dense></v-select></v-col>
          </v-row>
          <v-textarea v-model="formCom.motivo" label="Objetivo y Actividades de la Comisión *" rows="2" outlined dense></v-textarea>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogComision = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarComision()">Registrar Comisión</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: NUEVA OMISIÓN -->
    <v-dialog v-model="dialogOmision" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="warning white--text py-3">
          <v-icon left color="white">mdi-clock-alert</v-icon>
          <span>Regularizar Omisión de Marcado</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogOmision = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-autocomplete v-model="formOmi.id_persona" :items="personalList" item-text="nombre_completo" item-value="id" label="Funcionario *" outlined dense></v-autocomplete>
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="formOmi.fecha" label="Fecha del Olvido *" type="date" outlined dense></v-text-field></v-col>
            <v-col cols="6"><v-text-field v-model="formOmi.hora_marcado_omision" label="Hora Estimada *" type="time" outlined dense></v-text-field></v-col>
          </v-row>
          <v-select v-model="formOmi.turno_periodo" :items="['ENTRADA MAÑANA', 'SALIDA MAÑANA', 'ENTRADA TARDE', 'SALIDA TARDE']" label="Turno a Regularizar *" outlined dense></v-select>
          <v-textarea v-model="formOmi.motivo" label="Justificación del Olvido o Fallo *" rows="2" outlined dense placeholder="Explique por qué no se registró la marcación..."></v-textarea>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogOmision = false">Cancelar</v-btn>
          <v-btn color="warning" elevation="1" class="text-capitalize" @click="guardarOmision()">Solicitar Regularización</v-btn>
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
  name: 'ComisionesOmisiones',
  data() {
    return {
      activeTab: 0,
      pendientes: [],
      comisiones: [],
      omisiones: [],
      personalList: [],

      dialogComision: false,
      formCom: {
        id_persona: null,
        lugar: '',
        fecha_inicio: new Date().toISOString().substr(0, 10),
        fecha_fin: new Date().toISOString().substr(0, 10),
        monto_viatico: 0,
        transporte: 'TERRESTRE',
        motivo: '',
      },

      dialogOmision: false,
      formOmi: {
        id_persona: null,
        fecha: new Date().toISOString().substr(0, 10),
        hora_marcado_omision: '08:30',
        turno_periodo: 'ENTRADA MAÑANA',
        motivo: '',
      },

      headersAprobacion: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Tipo de Solicitud y Motivo', value: 'tipo' },
        { text: 'Fechas', value: 'fechas', width: '170px' },
        { text: 'Estado', value: 'estado', width: '110px', align: 'center' },
        { text: 'Acción', value: 'acciones', sortable: false, width: '180px', align: 'center' },
      ],

      headersComisiones: [
        { text: 'CITE', value: 'cite', width: '150px' },
        { text: 'Destino', value: 'lugar' },
        { text: 'Objetivo', value: 'motivo' },
        { text: 'Vigencia', value: 'fechas', width: '180px' },
        { text: 'Estado', value: 'estado', width: '110px', align: 'center' },
      ],

      headersOmisiones: [
        { text: 'CITE', value: 'cite', width: '150px' },
        { text: 'Turno', value: 'turno' },
        { text: 'Justificación', value: 'motivo' },
        { text: 'Estado', value: 'estado', width: '110px', align: 'center' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarPendientes();
    this.cargarComisiones();
    this.cargarOmisiones();
    this.cargarPersonal();
  },
  methods: {
    cargarPendientes() {
      axios.get('/api/rrhh/bandeja-aprobaciones').then(res => {
        if (res.data && res.data.success) {
          this.pendientes = res.data.data || [];
        }
      });
    },

    cargarComisiones() {
      axios.get('/api/rrhh/comisiones').then(res => {
        if (res.data && res.data.success) {
          this.comisiones = res.data.data || [];
        }
      });
    },

    cargarOmisiones() {
      axios.get('/api/rrhh/omisiones').then(res => {
        if (res.data && res.data.success) {
          this.omisiones = res.data.data || [];
        }
      });
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

    guardarComision() {
      if (!this.formCom.id_persona || !this.formCom.lugar) return;
      axios.post('/api/rrhh/comisiones', this.formCom).then(res => {
        this.dialogComision = false;
        this.showSnackbar(res.data.message || 'Comisión registrada', 'success');
        this.cargarComisiones();
        this.cargarPendientes();
      });
    },

    guardarOmision() {
      if (!this.formOmi.id_persona || !this.formOmi.motivo) return;
      axios.post('/api/rrhh/omisiones', this.formOmi).then(res => {
        this.dialogOmision = false;
        this.showSnackbar(res.data.message || 'Omisión solicitada', 'success');
        this.cargarOmisiones();
        this.cargarPendientes();
      });
    },

    resolver(item, estado) {
      axios.put(`/api/rrhh/bandeja-aprobaciones/${item.solicitud_id}/resolver`, { estado }).then(res => {
        this.showSnackbar(res.data.message || 'Solicitud resuelta', 'success');
        this.cargarPendientes();
        this.cargarComisiones();
        this.cargarOmisiones();
      });
    },

    getEstadoColor(estado) {
      if (estado === 'APROBADO' || estado === 'ACTIVO') return 'success';
      if (estado === 'RECHAZADO') return 'error';
      return 'warning';
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
