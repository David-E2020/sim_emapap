<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="deep-purple darken-1" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-truck-delivery-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Gestor de Salida y Despacho Externo</h2>
            <span class="text-caption text-secondary">Control de envíos físicos hacia ministerios, gobernaciones y entidades externas con acuse de recibo</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="deep-purple darken-1" class="text-capitalize font-weight-medium rounded-pill elevation-2 white--text" @click="abrirModalNuevoDespacho">
            <v-icon left small>mdi-plus-circle</v-icon> + Registrar Despacho
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE DESPACHOS -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <v-row dense class="mb-3" align="center">
        <v-col cols="12" md="4">
          <v-select
            v-model="filtroEstado"
            :items="['TODOS', 'PENDIENTE_DESPACHO', 'EN_CAMINO', 'ENTREGADO_CON_ACUSE', 'OBSERVADO']"
            label="Estado del Despacho"
            dense
            outlined
            hide-details
            @change="cargarDespachos"
          ></v-select>
        </v-col>
        <v-col cols="12" md="8" class="d-flex justify-end">
          <v-btn icon color="secondary" @click="cargarDespachos"><v-icon>mdi-refresh</v-icon></v-btn>
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="despachos"
        :loading="cargando"
        dense
        class="erp-table"
        :items-per-page="15"
      >
        <!-- NRO GUIA / CITE -->
        <template v-slot:item.nro_guia_despacho="{ item }">
          <div class="py-2">
            <v-chip label small color="deep-purple lighten-5" text-color="deep-purple" class="font-weight-bold mb-1">
              {{ item.nro_guia_despacho || 'S/N' }}
            </v-chip>
            <div class="text-caption text-secondary" v-if="item.hoja_ruta">
              HR: {{ item.hoja_ruta.nro_hoja_ruta }}
            </div>
          </div>
        </template>

        <!-- DESTINATARIO -->
        <template v-slot:item.destinatario_institucion="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2">{{ item.destinatario_institucion }}</div>
            <div class="text-caption text-secondary">
              {{ item.destinatario_persona ? 'Att: ' + item.destinatario_persona : '' }}
              {{ item.destinatario_ciudad ? ' (' + item.destinatario_ciudad + ')' : '' }}
            </div>
          </div>
        </template>

        <!-- TIPO DESPACHO -->
        <template v-slot:item.tipo_despacho="{ item }">
          <v-chip x-small label color="blue-grey lighten-4" class="font-weight-bold">
            {{ item.tipo_despacho }}
          </v-chip>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado_despacho="{ item }">
          <v-chip x-small label :color="getColorEstado(item.estado_despacho)" class="font-weight-bold white--text">
            {{ item.estado_despacho }}
          </v-chip>
        </template>

        <!-- FECHA -->
        <template v-slot:item.fecha_despacho="{ item }">
          <span class="text-caption text-secondary">{{ formatFecha(item.fecha_despacho) }}</span>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1">
            <v-tooltip bottom v-if="item.estado_despacho !== 'ENTREGADO_CON_ACUSE'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn small color="success" class="rounded-pill text-capitalize" v-bind="attrs" v-on="on" @click="abrirModalEntregar(item)">
                  <v-icon left x-small>mdi-check-all</v-icon> Acuse
                </v-btn>
              </template>
              <span>Registrar Acuse de Recibo</span>
            </v-tooltip>
            <v-chip x-small v-else label color="green lighten-5" text-color="green darken-3" class="font-weight-bold">
              ✓ ENTREGADO
            </v-chip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL NUEVO DESPACHO -->
    <v-dialog v-model="dialogNuevoDespacho" max-width="650px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 deep-purple darken-1 white--text py-3">
          <v-icon left color="white">mdi-truck-delivery</v-icon> Registrar Despacho Externo
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-select
                v-model="formDespacho.tipo_despacho"
                :items="['MENSAJERIA_INTERNA', 'COURIER_POSTAL', 'ENTREGA_DIRECTA']"
                label="Tipo de Despacho *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formDespacho.nro_guia_despacho"
                label="Nro. Guía / Código de Envío"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>

          <v-text-field
            v-model="formDespacho.destinatario_institucion"
            label="Institución Destinataria *"
            dense
            outlined
            class="mb-2"
          ></v-text-field>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formDespacho.destinatario_persona"
                label="Nombre del Destinatario (Opcional)"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formDespacho.destinatario_ciudad"
                label="Ciudad de Destino"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>

          <v-text-field
            v-model="formDespacho.destinatario_direccion"
            label="Dirección de Entrega"
            dense
            outlined
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogNuevoDespacho = false">Cancelar</v-btn>
          <v-btn color="deep-purple darken-1" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="guardarDespacho">
            Registrar Salida
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL REGISTRAR ENTREGA CON ACUSE -->
    <v-dialog v-model="dialogEntregar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="despachoSeleccionado">
        <v-card-title class="font-weight-bold text-h6 green darken-2 white--text py-3">
          <v-icon left color="white">mdi-file-check</v-icon> Registrar Acuse de Entrega
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3 pa-2 rounded bg-grey-lighten-4 text-caption font-weight-medium">
            <strong>Destino:</strong> {{ despachoSeleccionado.destinatario_institucion }}
          </div>

          <v-file-input
            v-model="archivoAcuse"
            label="Subir Boleta de Entrega / Acuse Escaneado (PDF/JPG)"
            dense
            outlined
            prepend-icon="mdi-camera"
          ></v-file-input>

          <v-textarea
            v-model="observacionEntrega"
            label="Observaciones de Entrega (Nombre de quien recibió, sello, etc.)"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogEntregar = false">Cancelar</v-btn>
          <v-btn color="success" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="confirmarEntrega">
            Confirmar Entrega
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
  name: 'BandejaSalidaExterna',
  data() {
    return {
      despachos: [],
      cargando: false,
      guardando: false,
      filtroEstado: 'TODOS',

      headers: [
        { text: 'Guía / CITE', value: 'nro_guia_despacho', width: '180px' },
        { text: 'Destinatario e Institución', value: 'destinatario_institucion' },
        { text: 'Tipo Despacho', value: 'tipo_despacho', width: '150px' },
        { text: 'Estado', value: 'estado_despacho', width: '150px' },
        { text: 'Fecha Salida', value: 'fecha_despacho', width: '140px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '130px', align: 'center' },
      ],

      dialogNuevoDespacho: false,
      formDespacho: {
        tipo_despacho: 'MENSAJERIA_INTERNA',
        nro_guia_despacho: '',
        destinatario_institucion: '',
        destinatario_persona: '',
        destinatario_ciudad: 'La Paz',
        destinatario_direccion: '',
      },

      dialogEntregar: false,
      despachoSeleccionado: null,
      archivoAcuse: null,
      observacionEntrega: 'Entregado con sello oficial y firma de recepción.',

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarDespachos();
  },
  methods: {
    async cargarDespachos() {
      this.cargando = true;
      try {
        const params = {
          estado: this.filtroEstado !== 'TODOS' ? this.filtroEstado : undefined,
        };
        const res = await window.axios.get('/api/correspondencia/despachos', { params });
        if (res.data && res.data.success) {
          this.despachos = res.data.data;
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar despachos.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevoDespacho() {
      this.formDespacho = {
        tipo_despacho: 'MENSAJERIA_INTERNA',
        nro_guia_despacho: '',
        destinatario_institucion: '',
        destinatario_persona: '',
        destinatario_ciudad: 'La Paz',
        destinatario_direccion: '',
      };
      this.dialogNuevoDespacho = true;
    },
    async guardarDespacho() {
      if (!this.formDespacho.destinatario_institucion) {
        this.mostrarMensaje('Ingrese la institución destinataria.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/despachos', this.formDespacho);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Despacho registrado con éxito.', 'success');
          this.dialogNuevoDespacho = false;
          this.cargarDespachos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al registrar despacho.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    abrirModalEntregar(item) {
      this.despachoSeleccionado = item;
      this.archivoAcuse = null;
      this.observacionEntrega = 'Entregado con sello oficial y firma de recepción.';
      this.dialogEntregar = true;
    },
    async confirmarEntrega() {
      this.guardando = true;
      try {
        const formData = new FormData();
        formData.append('observaciones', this.observacionEntrega);
        if (this.archivoAcuse) {
          formData.append('acuse', this.archivoAcuse);
        }
        const res = await window.axios.post(`/api/correspondencia/despachos/${this.despachoSeleccionado.id}/entregar`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Acuse de entrega registrado.', 'success');
          this.dialogEntregar = false;
          this.cargarDespachos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al registrar acuse.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    getColorEstado(e) {
      const map = { PENDIENTE_DESPACHO: 'orange darken-2', EN_CAMINO: 'blue darken-1', ENTREGADO_CON_ACUSE: 'green darken-2', OBSERVADO: 'red darken-2' };
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
