<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="warning" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-alert-octagon-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Eventos Significativos y Contingencias</h2>
            <span class="text-caption text-secondary">Control de contingencias tributarias del SIN (cortes de luz, internet, fallas del sistema)</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="error" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="dialogoNuevo = true">
            <v-icon left small>mdi-alert-circle</v-icon> + Iniciar Contingencia
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE EVENTOS SIGNIFICATIVOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="headers"
        :items="eventos"
        :loading="cargando"
        class="elevation-0"
        no-data-text="No hay eventos significativos registrados"
      >
        <template v-slot:item.codigo_evento="{ item }">
          <span class="font-weight-bold">Código {{ item.codigo_evento }}</span>
        </template>

        <template v-slot:item.fecha_inicio="{ item }">
          <span class="text-caption">{{ formatearFecha(item.fecha_inicio) }}</span>
        </template>

        <template v-slot:item.fecha_fin="{ item }">
          <span class="text-caption">{{ item.fecha_fin ? formatearFecha(item.fecha_fin) : 'EN CURSO' }}</span>
        </template>

        <template v-slot:item.estado_evento="{ item }">
          <v-chip
            :color="item.estado_evento === 'INICIADO' ? 'warning' : 'success'"
            text-color="white"
            x-small
            class="font-weight-bold text-uppercase"
          >
            {{ item.estado_evento }}
          </v-chip>
        </template>

        <template v-slot:item.facturas_count="{ item }">
          <v-chip small outlined color="primary">
            {{ item.facturas ? item.facturas.length : 0 }} facturas
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <v-btn
            v-if="item.estado_evento === 'INICIADO'"
            color="success"
            small
            outlined
            class="rounded-pill"
            :loading="cerrandoId === item.id"
            @click="cerrarEvento(item.id)"
          >
            <v-icon left small>mdi-check-all</v-icon> Cerrar y Empaquetar
          </v-btn>
          <span v-else class="text-caption text-secondary font-italic">Concluido</span>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO PARA INICIAR EVENTO SIGNIFICATIVO -->
    <v-dialog v-model="dialogoNuevo" max-width="520">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="error" size="36" class="mr-2 text-white">
            <v-icon small color="white">mdi-alert-octagon</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Iniciar Evento de Contingencia</h3>
        </div>

        <p class="text-caption text-secondary mb-3">
          Al iniciar este evento, las facturas emitidas operarán en modo Fuera de Línea (Offline) hasta que se restablezca el servicio.
        </p>

        <v-select
          v-model="formEvento.codigo_evento"
          :items="catalogoEventos"
          item-text="descripcion"
          item-value="codigo"
          label="Motivo del Evento Significativo *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <v-text-field
          v-model="formEvento.descripcion"
          label="Detalle / Observaciones *"
          placeholder="Ej. Corte de suministro de energía eléctrica en sucursal"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model="formEvento.cafc"
          label="Código CAFC (Opcional para contingencias manuales)"
          dense
          outlined
          class="mb-3"
        ></v-text-field>

        <div class="d-flex justify-end gap-2">
          <v-btn text @click="dialogoNuevo = false">Cancelar</v-btn>
          <v-btn color="error" :loading="guardando" @click="iniciarEvento">
            Iniciar Contingencia
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'EventosSignificativos',
  data() {
    return {
      cargando: false,
      guardando: false,
      cerrandoId: null,
      dialogoNuevo: false,
      eventos: [],
      catalogoEventos: [
        { codigo: 1, descripcion: 'CORTE DEL SERVICIO DE ENERGÍA ELÉCTRICA' },
        { codigo: 2, descripcion: 'CORTE DE ACCESO A INTERNET' },
        { codigo: 3, descripcion: 'INACCESIBILIDAD AL SERVICIO WEB DEL SIN' },
        { codigo: 4, descripcion: 'INGRESO DE ZONAS SIN INTERNET' },
        { codigo: 5, descripcion: 'VENTA EN LUGARES SIN INTERNET' },
        { codigo: 6, descripcion: 'VIRUS O FALLA DE SOFTWARE / HARDWARE' },
        { codigo: 7, descripcion: 'CAMBIO DE INFRAESTRUCTURA O CORTE PROGRAMADO' },
      ],
      formEvento: {
        codigo_evento: 1,
        descripcion: '',
        id_sucursal: 0,
        id_punto_venta: 0,
        cafc: '',
      },
      headers: [
        { text: 'Evento', value: 'codigo_evento', width: '110px' },
        { text: 'Descripción / Motivo', value: 'descripcion' },
        { text: 'Inicio', value: 'fecha_inicio', width: '150px' },
        { text: 'Fin', value: 'fecha_fin', width: '150px' },
        { text: 'Facturas Emitidas', value: 'facturas_count', align: 'center', width: '140px' },
        { text: 'Estado', value: 'estado_evento', align: 'center', width: '120px' },
        { text: 'Acciones', value: 'acciones', align: 'right', sortable: false, width: '180px' },
      ],
    };
  },
  mounted() {
    this.cargarEventos();
  },
  methods: {
    async cargarEventos() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/facturacion/eventos-significativos');
        this.eventos = res.data.data;
      } catch (e) {
        console.error('Error al cargar eventos', e);
      } finally {
        this.cargando = false;
      }
    },
    formatearFecha(str) {
      if (!str) return '-';
      const f = new Date(str);
      return f.toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' });
    },
    mostrarMensaje(tipo, texto) {
      if (this.$message && typeof this.$message[tipo] === 'function') {
        this.$message[tipo](texto);
      } else if (tipo === 'error') {
        console.error(texto);
      } else {
        console.log(texto);
      }
    },
    async iniciarEvento() {
      if (!this.formEvento.descripcion) {
        this.mostrarMensaje('warning', 'Ingrese una descripción.');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/facturacion/eventos-significativos', this.formEvento);
        if (res.data.success) {
          this.mostrarMensaje('success', 'Evento de contingencia iniciado.');
          this.dialogoNuevo = false;
          this.cargarEventos();
        }
      } catch (e) {
        this.mostrarMensaje('error', 'Error al iniciar contingencia');
      } finally {
        this.guardando = false;
      }
    },
    async cerrarEvento(id) {
      this.cerrandoId = id;
      try {
        const res = await window.axios.post(`/api/facturacion/eventos-significativos/${id}/cerrar`);
        if (res.data.success) {
          this.mostrarMensaje('success', res.data.message);
          this.cargarEventos();
        }
      } catch (e) {
        this.mostrarMensaje('error', 'Error al cerrar contingencia');
      } finally {
        this.cerrandoId = null;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
}
.gap-2 {
  gap: 8px;
}
</style>
