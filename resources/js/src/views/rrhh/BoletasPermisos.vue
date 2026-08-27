<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-document-edit-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Boletas de Salida y Permisos</h2>
            <span class="text-caption text-secondary">Gestión de licencias, comisiones oficiales, permisos particulares y flujo de aprobación</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalSolicitud()">
            <v-icon left small>mdi-file-plus</v-icon> + Nueva Boleta de Salida
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE SOLICITUDES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title class="py-3 px-4 d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-format-list-checks</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Historial de Solicitudes y Permisos</span>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="solicitudes"
        :loading="loading"
        class="elevation-0"
        no-data-text="No se encontraron solicitudes registradas"
      >
        <template v-slot:item.cite="{ item }">
          <v-chip small label color="primary lighten-5 primary--text" class="font-weight-bold">
            {{ item.cite }}
          </v-chip>
        </template>

        <template v-slot:item.permiso="{ item }">
          <div class="font-weight-bold text-body-2" v-if="item.permiso">
            {{ item.permiso.nombre }}
          </div>
          <div class="text-caption text-secondary">{{ item.motivo }}</div>
        </template>

        <template v-slot:item.fechas="{ item }">
          <div class="text-caption">
            <strong>Desde:</strong> {{ item.fecha_inicio }} {{ item.hora_inicio || '' }}<br>
            <strong>Hasta:</strong> {{ item.fecha_fin }} {{ item.hora_fin || '' }}
          </div>
        </template>

        <template v-slot:item.horas="{ item }">
          <v-chip x-small color="grey lighten-3" class="font-weight-bold">
            {{ item.horas_solicitadas > 0 ? item.horas_solicitadas + ' hrs' : 'Día Completo' }}
          </v-chip>
        </template>

        <template v-slot:item.estado="{ item }">
          <v-chip x-small :color="getEstadoColor(item._estado)" label class="font-weight-bold text-white">
            {{ item._estado || 'PENDIENTE' }}
          </v-chip>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: NUEVA SOLICITUD -->
    <v-dialog v-model="dialogSolicitud" max-width="600" persistent>
      <v-form v-model="formValido" ref="formSol" @submit.prevent="guardarSolicitud">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-file-plus</v-icon>
            <span>Emitir Boleta de Salida / Permiso</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogSolicitud = false"><v-icon>mdi-close</v-icon></v-btn>
          </v-card-title>

          <v-card-text class="pt-5">
            <v-row dense>
              <v-col cols="12">
                <v-autocomplete
                  v-model="form.id_persona"
                  :items="personalList"
                  item-text="nombre_completo"
                  item-value="id"
                  label="Funcionario Solicitante *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-account"
                  :rules="[v => !!v || 'Debe seleccionar un funcionario']"
                ></v-autocomplete>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.id_permiso"
                  :items="permisosList"
                  item-text="nombre"
                  item-value="id"
                  label="Tipo de Permiso *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-format-list-checks"
                  :rules="[v => !!v || 'Tipo de permiso requerido']"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.id_justificacion"
                  :items="justificacionesList"
                  item-text="nombre"
                  item-value="id"
                  label="Justificación Oficial"
                  outlined
                  dense
                  prepend-inner-icon="mdi-help-circle-outline"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field v-model="form.fecha_inicio" label="Fecha Inicio *" type="date" outlined dense required :rules="[v => !!v || 'Requerido']"></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field v-model="form.fecha_fin" label="Fecha Fin *" type="date" outlined dense required :rules="[v => !!v || 'Requerido']"></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field v-model="form.hora_inicio" label="Hora Salida" type="time" outlined dense></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field v-model="form.hora_fin" label="Hora Retorno" type="time" outlined dense></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field v-model="form.horas_solicitadas" label="Horas Solicitadas" type="number" step="0.5" outlined dense></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea v-model="form.motivo" label="Motivo de la Salida *" rows="2" outlined dense placeholder="Detalle la razón del permiso o comisión oficial..." :rules="[v => !!v || 'Motivo requerido']"></v-textarea>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogSolicitud = false">Cancelar</v-btn>
            <v-btn color="primary" elevation="1" type="submit" :loading="guardando" class="text-capitalize px-4">Guardar Boleta</v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
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
  name: 'BoletasPermisos',
  data() {
    return {
      solicitudes: [],
      loading: false,

      dialogSolicitud: false,
      formValido: false,
      guardando: false,
      form: {
        id_persona: null,
        id_permiso: null,
        id_justificacion: null,
        fecha_inicio: new Date().toISOString().substr(0, 10),
        fecha_fin: new Date().toISOString().substr(0, 10),
        hora_inicio: '09:00',
        hora_fin: '11:00',
        horas_solicitadas: 2,
        motivo: '',
      },

      personalList: [],
      permisosList: [],
      justificacionesList: [],

      headers: [
        { text: 'CITE Oficial', value: 'cite', width: '150px' },
        { text: 'Permiso y Motivo', value: 'permiso' },
        { text: 'Fechas y Horarios', value: 'fechas', width: '180px' },
        { text: 'Tiempo', value: 'horas', width: '100px', align: 'center' },
        { text: 'Estado', value: 'estado', width: '110px', align: 'center' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarSolicitudes();
    this.cargarCatalogos();
  },
  methods: {
    cargarSolicitudes() {
      this.loading = true;
      axios
        .get('/api/rrhh/solicitudes')
        .then(res => {
          this.loading = false;
          if (res.data && res.data.success) {
            this.solicitudes = res.data.data || [];
          }
        })
        .catch(() => {
          this.loading = false;
        });
    },

    cargarCatalogos() {
      axios.get('/api/rrhh/permisos/catalogo').then(res => {
        if (res.data && res.data.success) {
          this.permisosList = res.data.permisos || [];
          this.justificacionesList = res.data.justificaciones || [];
        }
      });

      axios.get('/api/rrhh/personal?per_page=100').then(res => {
        if (res.data && res.data.success) {
          this.personalList = (res.data.data || []).map(p => ({
            id: p.id,
            nombre_completo: `${p.nombres} ${p.primer_apellido || ''} (CI: ${p.nro_documento})`,
          }));
        }
      });
    },

    abrirModalSolicitud() {
      this.dialogSolicitud = true;
    },

    guardarSolicitud() {
      if (!this.$refs.formSol.validate()) return;
      this.guardando = true;

      axios
        .post('/api/rrhh/solicitudes', this.form)
        .then(res => {
          this.guardando = false;
          this.dialogSolicitud = false;
          this.showSnackbar(res.data.message || 'Boleta de salida guardada', 'success');
          this.cargarSolicitudes();
        })
        .catch(err => {
          this.guardando = false;
          const msg = (err.response && err.response.data && err.response.data.message) || 'Error al guardar solicitud';
          this.showSnackbar(msg, 'error');
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
