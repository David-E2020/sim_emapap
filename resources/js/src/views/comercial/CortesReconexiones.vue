<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="error" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-pipe-disconnected</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Cortes y Reconexiones de Servicio</h2>
            <span class="text-caption text-secondary">Control operativo de cuadrillas técnicas, cortes por mora acumulada y rehabilitaciones</span>
          </div>
        </div>
      </div>
    </v-card>

    <v-tabs v-model="tabActual" color="error" class="mb-4">
      <v-tab><v-icon left small>mdi-alert</v-icon> Abonados en Riesgo de Corte</v-tab>
      <v-tab><v-icon left small>mdi-clipboard-list-outline</v-icon> Órdenes de Trabajo Activas</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: CANDIDATOS A CORTE -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex justify-space-between align-center flex-wrap">
            <div class="d-flex align-center gap-3">
              <span class="text-subtitle-2 font-weight-bold">Mora mínima:</span>
              <v-chip color="error" outlined small>2 o más meses pendientes</v-chip>
            </div>
            <v-btn
              color="error"
              class="rounded-pill elevation-2"
              :disabled="seleccionadosCorte.length === 0"
              :loading="generandoCortes"
              @click="abrirModalGenerarCorte"
            >
              <v-icon left small>mdi-pipe-disconnected</v-icon>
              Generar Órdenes de Corte ({{ seleccionadosCorte.length }})
            </v-btn>
          </div>
        </v-card>

        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            v-model="seleccionadosCorte"
            show-select
            :headers="columnasCandidatos"
            :items="candidatos"
            :loading="cargandoCandidatos"
            :items-per-page="15"
            class="elevation-0"
          >
            <template v-slot:item.codigo="{ item }">
              <v-chip small outlined color="primary" class="font-weight-bold">{{ item.codigo }}</v-chip>
            </template>
            <template v-slot:item.nombre_completo="{ item }">
              <div class="font-weight-bold">{{ item.nombre_completo }}</div>
              <span class="text-caption text-secondary">{{ item.zona ? item.zona.nombre : '' }}</span>
            </template>
            <template v-slot:item.meses_mora="{ item }">
              <v-chip small color="error" text-color="white" class="font-weight-black">
                {{ item.meses_mora }} meses
              </v-chip>
            </template>
            <template v-slot:item.saldo_deuda="{ item }">
              <span class="font-weight-bold error--text">Bs {{ parseFloat(item.saldo_deuda).toFixed(2) }}</span>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 2: BANDEJA DE ÓRDENES DE TRABAJO -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            :headers="columnasOrdenes"
            :items="ordenes"
            :loading="cargandoOrdenes"
            :items-per-page="15"
            class="elevation-0"
          >
            <template v-slot:item.numero_orden="{ item }">
              <v-chip small outlined :color="item.tipo_orden === 'CORTE_POR_MORA' ? 'error' : 'success'" class="font-weight-bold">
                {{ item.numero_orden }}
              </v-chip>
            </template>
            <template v-slot:item.abonado="{ item }">
              <div class="font-weight-bold">{{ item.abonado ? item.abonado.nombre_completo : '-' }}</div>
              <span class="text-caption text-secondary">Cod: {{ item.abonado ? item.abonado.codigo : '-' }}</span>
            </template>
            <template v-slot:item.tipo_orden="{ item }">
              <v-chip x-small :color="item.tipo_orden === 'CORTE_POR_MORA' ? 'error' : 'success'" text-color="white">
                {{ item.tipo_orden }}
              </v-chip>
            </template>
            <template v-slot:item.estado="{ item }">
              <v-chip x-small :color="item.estado === 'EJECUTADO' ? 'success' : 'warning'" text-color="white">
                {{ item.estado }}
              </v-chip>
            </template>
            <template v-slot:item.acciones="{ item }">
              <div class="d-flex align-center gap-1">
                <v-btn
                  icon
                  small
                  color="secondary"
                  title="Imprimir Orden de Trabajo"
                  @click="imprimirOrden(item)"
                >
                  <v-icon small>mdi-printer</v-icon>
                </v-btn>
                <v-btn
                  v-if="item.estado === 'PENDIENTE'"
                  small
                  color="primary"
                  outlined
                  class="rounded-pill text-caption"
                  @click="abrirModalEjecutar(item)"
                >
                  Registrar Ejecución
                </v-btn>
                <span v-else class="text-caption text-secondary">
                  <v-icon x-small color="success">mdi-check</v-icon> {{ item.fecha_ejecucion ? item.fecha_ejecucion.substr(0,10) : 'Ejecutado' }}
                </span>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>
    </v-tabs-items>

    <!-- MODAL GENERAR CORTES -->
    <v-dialog v-model="modalGenerarCorte" max-width="450">
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon color="white" class="mr-2">mdi-pipe-disconnected</v-icon>
          Programar Cuadrilla de Corte
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="text-body-2 mb-3">
            Se generarán órdenes de corte para <strong>{{ seleccionadosCorte.length }} abonados</strong> con mora >= 2 meses.
          </div>
          <v-text-field
            v-model="fechaProgramadaCorte"
            label="Fecha Programada *"
            type="date"
            dense
            outlined
          ></v-text-field>
          <v-textarea
            v-model="motivoCorte"
            label="Instrucciones para la cuadrilla"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalGenerarCorte = false">Cancelar</v-btn>
          <v-btn color="error" :loading="generandoCortes" @click="confirmarGenerarCortes">
            Generar Órdenes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL REGISTRAR EJECUCIÓN EN CAMPO -->
    <v-dialog v-model="modalEjecutar" max-width="450">
      <v-card rounded="lg" v-if="ordenSeleccionada">
        <v-card-title class="primary white--text py-3">
          Registrar Ejecución en Campo
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="text-body-2 mb-2">
            Orden: <strong>{{ ordenSeleccionada.numero_orden }}</strong> ({{ ordenSeleccionada.tipo_orden }})
          </div>
          <div class="text-caption text-secondary mb-3">
            Abonado: {{ ordenSeleccionada && ordenSeleccionada.abonado ? ordenSeleccionada.abonado.nombre_completo : '' }}
          </div>

          <div v-if="ordenSeleccionada.tipo_orden === 'CORTE_POR_MORA'">
            <v-text-field
              v-model.number="formEjecucion.lectura_en_corte"
              label="Lectura del Medidor al Cortar (m³) *"
              type="number"
              dense
              outlined
            ></v-text-field>
            <v-text-field
              v-model="formEjecucion.numero_precinto"
              label="N° Precinto de Seguridad *"
              dense
              outlined
              placeholder="Ej: PREC-2026-A1"
            ></v-text-field>
          </div>

          <v-textarea
            v-model="formEjecucion.informe_tecnico"
            label="Informe Técnico / Observaciones"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalEjecutar = false">Cancelar</v-btn>
          <v-btn color="success" :loading="guardandoEjecucion" @click="confirmarEjecucion">
            Confirmar Ejecución
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE ÓRDENES DE TRABAJO -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'CortesReconexiones',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      tabActual: 0,
      cargandoCandidatos: false,
      cargandoOrdenes: false,
      generandoCortes: false,
      guardandoEjecucion: false,
      modalGenerarCorte: false,
      modalEjecutar: false,
      candidatos: [],
      seleccionadosCorte: [],
      ordenes: [],
      ordenSeleccionada: null,
      fechaProgramadaCorte: new Date().toISOString().substr(0, 10),
      motivoCorte: 'Corte por mora acumulada superior a 2 meses',
      formEjecucion: {
        lectura_en_corte: 0,
        numero_precinto: '',
        informe_tecnico: '',
      },
      columnasCandidatos: [
        { text: 'Código', value: 'codigo', width: '90px' },
        { text: 'Abonado y Ubicación', value: 'nombre_completo' },
        { text: 'Meses Mora', value: 'meses_mora', width: '130px', align: 'center' },
        { text: 'Total Deuda', value: 'saldo_deuda', width: '140px', align: 'end' },
      ],
      columnasOrdenes: [
        { text: 'N° Orden', value: 'numero_orden', width: '160px' },
        { text: 'Abonado', value: 'abonado' },
        { text: 'Tipo de Orden', value: 'tipo_orden', width: '160px' },
        { text: 'Fecha Programada', value: 'fecha_programada', width: '140px' },
        { text: 'Estado', value: 'estado', width: '110px', align: 'center' },
        { text: 'Acción', value: 'acciones', sortable: false, width: '150px', align: 'center' },
      ],
    };
  },
  mounted() {
    this.cargarCandidatos();
    this.cargarOrdenes();
  },
  methods: {
    async cargarCandidatos() {
      this.cargandoCandidatos = true;
      try {
        const res = await axios.get('/api/comercial/cortes/candidatos', { params: { meses_mora: 2 } });
        this.candidatos = res.data.data || [];
      } catch (e) {
        console.error('Error cargando candidatos a corte:', e);
      } finally {
        this.cargandoCandidatos = false;
      }
    },
    async cargarOrdenes() {
      this.cargandoOrdenes = true;
      try {
        const res = await axios.get('/api/comercial/cortes/ordenes');
        this.ordenes = res.data.data || [];
      } catch (e) {
        console.error('Error cargando órdenes de trabajo:', e);
      } finally {
        this.cargandoOrdenes = false;
      }
    },
    abrirModalGenerarCorte() {
      this.modalGenerarCorte = true;
    },
    async confirmarGenerarCortes() {
      this.generandoCortes = true;
      try {
        await axios.post('/api/comercial/cortes/generar', {
          abonados_ids: this.seleccionadosCorte.map(a => a.id),
          fecha_programada: this.fechaProgramadaCorte,
          motivo: this.motivoCorte,
        });
        this.modalGenerarCorte = false;
        this.seleccionadosCorte = [];
        this.cargarCandidatos();
        this.cargarOrdenes();
        this.tabActual = 1;
        alert('Órdenes de corte generadas exitosamente.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al generar cortes.');
      } finally {
        this.generandoCortes = false;
      }
    },
    abrirModalEjecutar(item) {
      this.ordenSeleccionada = item;
      this.formEjecucion = {
        lectura_en_corte: item.abonado?.medidor_actual?.lectura_inicial || 0,
        numero_precinto: '',
        informe_tecnico: '',
      };
      this.modalEjecutar = true;
    },
    async confirmarEjecucion() {
      if (!this.ordenSeleccionada) return;
      this.guardandoEjecucion = true;
      try {
        if (this.ordenSeleccionada.tipo_orden === 'CORTE_POR_MORA') {
          await axios.post(`/api/comercial/cortes/${this.ordenSeleccionada.id}/ejecutar`, this.formEjecucion);
        } else {
          await axios.post(`/api/comercial/reconexiones/${this.ordenSeleccionada.id}/ejecutar`, {
            informe_tecnico: this.formEjecucion.informe_tecnico,
          });
        }
        this.modalEjecutar = false;
        this.cargarOrdenes();
        this.cargarCandidatos();
        alert('Orden ejecutada y estado del servicio actualizado.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al registrar ejecución.');
      } finally {
        this.guardandoEjecucion = false;
      }
    },
    imprimirOrden(item) {
      if (!item) return;
      this.urlVisorPdf = `/api/comercial/cortes/ordenes/${item.id}/pdf`;
      this.tituloVisorPdf = `Orden de ${item.tipo_orden} N° ${item.numero_orden}`;
      this.subtituloVisorPdf = `Abonado: ${item.abonado || ''}`;
      this.mostrarVisorPdf = true;
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
</style>
