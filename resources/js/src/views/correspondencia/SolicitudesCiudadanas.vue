<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="amber darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-voice</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Solicitudes y Trámites Ciudadanos</h2>
            <span class="text-caption text-secondary">Bandeja de solicitudes ingresadas por la ciudadanía mediante el portal web digital de EMAPA</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn icon color="secondary" @click="cargarSolicitudes"><v-icon>mdi-refresh</v-icon></v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE SOLICITUDES -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <v-row dense class="mb-3" align="center">
        <v-col cols="12" md="4">
          <v-select
            v-model="filtroEstado"
            :items="['TODAS', 'REGISTRADA', 'HOJA_RUTA_GENERADA', 'RECHAZADA']"
            label="Estado de Solicitud"
            dense
            outlined
            hide-details
            @change="cargarSolicitudes"
          ></v-select>
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="solicitudes"
        :loading="cargando"
        dense
        class="erp-table"
        :items-per-page="15"
      >
        <!-- CODIGO SOLICITUD -->
        <template v-slot:item.codigo_solicitud="{ item }">
          <v-chip label small color="amber lighten-5" text-color="amber darken-4" class="font-weight-bold">
            {{ item.codigo_solicitud }}
          </v-chip>
        </template>

        <!-- SOLICITANTE -->
        <template v-slot:item.solicitante_nombre="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2">{{ item.solicitante_nombre }}</div>
            <div class="text-caption text-secondary">
              CI/NIT: {{ item.solicitante_ci_nit }} | Telf: {{ item.solicitante_telefono || 'S/N' }}
            </div>
          </div>
        </template>

        <!-- DESCRIPCIÓN -->
        <template v-slot:item.descripcion_solicitud="{ item }">
          <div class="text-caption text-truncate" style="max-width: 320px;">
            <strong>{{ item.tipo_solicitud }}:</strong> {{ item.descripcion_solicitud }}
          </div>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado_solicitud="{ item }">
          <v-chip x-small label :color="getColorEstado(item.estado_solicitud)" class="font-weight-bold white--text">
            {{ item.estado_solicitud }}
          </v-chip>
        </template>

        <!-- FECHA -->
        <template v-slot:item.fecha_solicitud="{ item }">
          <span class="text-caption text-secondary">{{ formatFecha(item.fecha_solicitud) }}</span>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1">
            <v-tooltip bottom v-if="item.estado_solicitud === 'REGISTRADA'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn small color="primary" class="rounded-pill text-capitalize" v-bind="attrs" v-on="on" @click="abrirModalAdmitir(item)">
                  <v-icon left x-small>mdi-file-check</v-icon> Admitir y Derivar
                </v-btn>
              </template>
              <span>Convertir a Hoja de Ruta Oficial</span>
            </v-tooltip>
            <div v-else-if="item.hoja_ruta" class="text-caption font-weight-bold primary--text">
              HR: {{ item.hoja_ruta.nro_hoja_ruta }}
            </div>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL ADMITIR Y DERIVAR -->
    <v-dialog v-model="dialogAdmitir" max-width="650px" persistent>
      <v-card rounded="lg" v-if="solicitudSeleccionada">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-file-check</v-icon> Admitir Trámite Ciudadano: {{ solicitudSeleccionada.codigo_solicitud }}
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3 pa-2 rounded bg-grey-lighten-4 text-caption font-weight-medium">
            <strong>Solicitante:</strong> {{ solicitudSeleccionada.solicitante_nombre }} (CI: {{ solicitudSeleccionada.solicitante_ci_nit }})<br>
            <strong>Detalle:</strong> {{ solicitudSeleccionada.descripcion_solicitud }}
          </div>

          <v-select
            v-model="formAdmitir.id_unidad_destino"
            :items="unidades"
            item-text="nombre"
            item-value="id"
            label="Unidad Destino *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-select
            v-model="formAdmitir.proveido"
            :items="['PARA SU ATENCIÓN Y RESPUESTA', 'PARA INFORME TÉCNICO CIRCUNSTANCIADO', 'PASE A SUS EFECTOS']"
            label="Proveído Oficial *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-text-field
            v-model.number="formAdmitir.dias_plazo"
            label="Días de Plazo para Atención"
            type="number"
            dense
            outlined
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogAdmitir = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="confirmarAdmision">
            Generar Hoja de Ruta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" top right :timeout="3500">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'SolicitudesCiudadanas',
  data() {
    return {
      solicitudes: [],
      unidades: [],
      cargando: false,
      guardando: false,
      filtroEstado: 'TODAS',

      headers: [
        { text: 'Código', value: 'codigo_solicitud', width: '140px' },
        { text: 'Solicitante', value: 'solicitante_nombre', width: '220px' },
        { text: 'Detalle de Solicitud', value: 'descripcion_solicitud' },
        { text: 'Estado', value: 'estado_solicitud', width: '150px' },
        { text: 'Fecha Ingreso', value: 'fecha_solicitud', width: '140px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '180px', align: 'center' },
      ],

      dialogAdmitir: false,
      solicitudSeleccionada: null,
      formAdmitir: {
        id_unidad_destino: null,
        proveido: 'PARA SU ATENCIÓN Y RESPUESTA',
        dias_plazo: 3,
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarSolicitudes();
    this.cargarUnidades();
  },
  methods: {
    async cargarSolicitudes() {
      this.cargando = true;
      try {
        const params = {
          estado: this.filtroEstado !== 'TODAS' ? this.filtroEstado : undefined,
        };
        const res = await window.axios.get('/api/correspondencia/solicitudes-ciudadanas', { params });
        if (res.data && res.data.success) {
          this.solicitudes = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar solicitudes.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async cargarUnidades() {
      try {
        const res = await window.axios.get('/api/rrhh/organigrama');
        if (res.data && res.data.success) {
          const planas = [];
          const aplanar = (items) => {
            items.forEach(u => {
              planas.push({ id: u.id, nombre: u.nombre });
              if (u.dependencias && u.dependencias.length) aplanar(u.dependencias);
            });
          };
          aplanar(res.data.data);
          this.unidades = planas;
        }
      } catch (e) {}
    },
    abrirModalAdmitir(item) {
      this.solicitudSeleccionada = item;
      this.formAdmitir = {
        id_unidad_destino: this.unidades.length ? this.unidades[0].id : null,
        proveido: 'PARA SU ATENCIÓN Y RESPUESTA',
        dias_plazo: 3,
      };
      this.dialogAdmitir = true;
    },
    async confirmarAdmision() {
      if (!this.formAdmitir.id_unidad_destino) {
        this.mostrarMensaje('Selecciona la unidad destino.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post(`/api/correspondencia/solicitudes-ciudadanas/${this.solicitudSeleccionada.id}/convertir-hoja-ruta`, this.formAdmitir);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Hoja de ruta oficial generada exitosamente.', 'success');
          this.dialogAdmitir = false;
          this.cargarSolicitudes();
        }
      } catch (e) {
        this.mostrarMensaje('Error al admitir solicitud.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    getColorEstado(e) {
      const map = { REGISTRADA: 'orange darken-2', HOJA_RUTA_GENERADA: 'green darken-2', RECHAZADA: 'red darken-2' };
      return map[e] || 'grey';
    },
    formatFecha(f) {
      if (!f) return '-';
      const d = new Date(f);
      return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    mostrarMensaje(text, color = 'success') {
      this.snackbar = { status: true, text, color };
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
