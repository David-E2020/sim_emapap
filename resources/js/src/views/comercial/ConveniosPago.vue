<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-handshake-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Convenios de Pago</h2>
            <span class="text-caption text-secondary">Refinanciamiento y planes de facilidades de pago en cuotas mensuales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="indigo" dark class="text-capitalize rounded-pill elevation-2" @click="abrirModalNuevo">
            <v-icon left small>mdi-plus-circle</v-icon> + Suscribir Convenio
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABLA DE CONVENIOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title class="pa-4 d-flex justify-space-between align-center flex-wrap">
        <v-text-field
          v-model="busqueda"
          label="Buscar por N° Convenio, Código o Nombre de Abonado..."
          prepend-inner-icon="mdi-magnify"
          dense
          outlined
          hide-details
          clearable
          style="max-width: 400px;"
          @keyup.enter="cargarConvenios"
          @click:clear="cargarConvenios"
        ></v-text-field>

        <v-select
          v-model="filtroEstado"
          :items="['TODOS', 'VIGENTE', 'CUMPLIDO', 'INCUMPLIDO', 'ANULADO']"
          label="Estado"
          dense
          outlined
          hide-details
          style="max-width: 180px;"
          @change="cargarConvenios"
        ></v-select>
      </v-card-title>

      <v-data-table
        :headers="columnas"
        :items="convenios"
        :loading="cargando"
        :items-per-page="15"
        class="elevation-0"
      >
        <!-- N° Convenio -->
        <template v-slot:item.numero_convenio="{ item }">
          <v-chip color="indigo" outlined small class="font-weight-bold">
            {{ item.numero_convenio }}
          </v-chip>
        </template>

        <!-- Abonado -->
        <template v-slot:item.abonado="{ item }">
          <div class="font-weight-bold">{{ item.abonado ? item.abonado.nombre_completo : '-' }}</div>
          <span class="text-caption text-secondary">Cod: {{ item.abonado ? item.abonado.codigo : '-' }}</span>
        </template>

        <!-- Deuda Total -->
        <template v-slot:item.monto_deuda_total="{ item }">
          <span class="font-weight-bold">Bs {{ parseFloat(item.monto_deuda_total).toFixed(2) }}</span>
        </template>

        <!-- Pago Inicial -->
        <template v-slot:item.pago_inicial="{ item }">
          <span>Bs {{ parseFloat(item.pago_inicial).toFixed(2) }}</span>
        </template>

        <!-- Cuota Mensual -->
        <template v-slot:item.cuotas_resumen="{ item }">
          <div class="text-caption">
            <strong>{{ item.plazo_meses }} meses</strong> de Bs {{ parseFloat(item.monto_cuota_mensual).toFixed(2) }}
          </div>
        </template>

        <!-- Estado -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            small
            :color="item.estado === 'VIGENTE' ? 'primary' : (item.estado === 'CUMPLIDO' ? 'success' : 'error')"
            text-color="white"
          >
            {{ item.estado }}
          </v-chip>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <v-btn icon small color="primary" @click="verDetalleCuotas(item)">
            <v-icon small>mdi-format-list-numbered</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL NUEVO CONVENIO -->
    <v-dialog v-model="modalNuevo" max-width="750" persistent>
      <v-card rounded="lg">
        <v-card-title class="indigo white--text py-3">
          <v-icon color="white" class="mr-2">mdi-handshake-outline</v-icon>
          Suscripción de Convenio de Pago
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" sm="8">
              <v-text-field
                v-model="codigoAbonadoBuscar"
                label="Código de Abonado Deudor *"
                prepend-inner-icon="mdi-account-search"
                dense
                outlined
                placeholder="Ej: 00001"
                @keyup.enter="buscarAbonadoDeuda"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="4">
              <v-btn color="primary" block height="40" :loading="buscandoDeuda" @click="buscarAbonadoDeuda">
                Cargar Deuda
              </v-btn>
            </v-col>

            <!-- Datos abonado cargado -->
            <v-col cols="12" v-if="abonadoDeuda">
              <v-card outlined class="pa-3 mb-3 grey lighten-4">
                <div class="d-flex justify-space-between align-center">
                  <div>
                    <strong>{{ abonadoDeuda.nombre_completo }}</strong> (Cod: {{ abonadoDeuda.codigo }})
                    <div class="text-caption text-secondary">
                      Deuda acumulada: Bs {{ parseFloat(abonadoDeuda.saldo_deuda).toFixed(2) }} ({{ abonadoDeuda.meses_mora }} meses)
                    </div>
                  </div>
                  <v-chip color="error" class="font-weight-bold" small>
                    Bs {{ parseFloat(abonadoDeuda.saldo_deuda).toFixed(2) }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>

            <!-- Parámetros del convenio -->
            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="formConvenio.pago_inicial"
                label="Pago Inicial (Bs) *"
                type="number"
                min="0"
                dense
                outlined
                @input="simular"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="formConvenio.plazo_meses"
                label="Plazo en Meses (1 a 36) *"
                type="number"
                min="1"
                max="36"
                dense
                outlined
                @input="simular"
              ></v-text-field>
            </v-col>

            <!-- Previsualización de Cuotas Simuladas -->
            <v-col cols="12" v-if="simulacionResultado">
              <div class="text-subtitle-2 font-weight-bold mb-2">Cronograma de Cuotas Estimadas:</div>
              <v-simple-table dense class="mb-3">
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th>Cuota N°</th>
                      <th>Periodo</th>
                      <th>Vencimiento</th>
                      <th class="text-right">Monto</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in simulacionResultado.cuotas" :key="c.numero_cuota">
                      <td>Cuota {{ c.numero_cuota }} / {{ simulacionResultado.plazo_meses }}</td>
                      <td>{{ c.periodo }}</td>
                      <td>{{ c.fecha_vencimiento }}</td>
                      <td class="text-right font-weight-bold">Bs {{ parseFloat(c.monto_cuota).toFixed(2) }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="formConvenio.glosa"
                label="Observaciones / Justificación"
                rows="2"
                dense
                outlined
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevo = false">Cancelar</v-btn>
          <v-btn
            color="indigo"
            dark
            :disabled="!abonadoDeuda || !simulacionResultado"
            :loading="suscribiendo"
            @click="suscribirConvenio"
          >
            Formalizar Convenio
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DETALLE DE CUOTAS -->
    <v-dialog v-model="modalCuotas" max-width="600">
      <v-card rounded="lg" v-if="convenioSeleccionado">
        <v-card-title class="indigo white--text py-3 d-flex justify-space-between">
          <span>Cuotas de Convenio {{ convenioSeleccionado.numero_convenio }}</span>
          <v-btn icon color="white" @click="modalCuotas = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Periodo</th>
                  <th>Vencimiento</th>
                  <th class="text-right">Monto</th>
                  <th class="text-center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cuota in convenioSeleccionado.cuotas" :key="cuota.id">
                  <td>{{ cuota.numero_cuota }}</td>
                  <td>{{ cuota.periodo }}</td>
                  <td>{{ cuota.fecha_vencimiento }}</td>
                  <td class="text-right font-weight-bold">Bs {{ parseFloat(cuota.monto_cuota).toFixed(2) }}</td>
                  <td class="text-center">
                    <v-chip x-small :color="cuota.estado_pago === 'PAGADO' ? 'success' : 'warning'" text-color="white">
                      {{ cuota.estado_pago }}
                    </v-chip>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ConveniosPago',
  data() {
    return {
      cargando: false,
      busqueda: '',
      filtroEstado: 'TODOS',
      convenios: [],
      columnas: [
        { text: 'N° Convenio', value: 'numero_convenio', width: '150px' },
        { text: 'Abonado', value: 'abonado' },
        { text: 'Deuda Total', value: 'monto_deuda_total', align: 'end', width: '130px' },
        { text: 'Pago Inicial', value: 'pago_inicial', align: 'end', width: '120px' },
        { text: 'Plan de Cuotas', value: 'cuotas_resumen', width: '180px' },
        { text: 'Estado', value: 'estado', width: '120px', align: 'center' },
        { text: 'Detalle', value: 'acciones', sortable: false, width: '80px', align: 'center' },
      ],
      modalNuevo: false,
      modalCuotas: false,
      codigoAbonadoBuscar: '',
      buscandoDeuda: false,
      abonadoDeuda: null,
      suscribiendo: false,
      simulacionResultado: null,
      convenioSeleccionado: null,
      formConvenio: {
        pago_inicial: 0,
        plazo_meses: 6,
        glosa: '',
      },
    };
  },
  mounted() {
    this.cargarConvenios();
  },
  methods: {
    async cargarConvenios() {
      this.cargando = true;
      try {
        const res = await axios.get('/api/comercial/convenios', {
          params: {
            search: this.busqueda || undefined,
            estado: this.filtroEstado !== 'TODOS' ? this.filtroEstado : undefined,
          },
        });
        this.convenios = res.data.data || [];
      } catch (e) {
        console.error('Error cargando convenios:', e);
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevo() {
      this.codigoAbonadoBuscar = '';
      this.abonadoDeuda = null;
      this.simulacionResultado = null;
      this.formConvenio = { pago_inicial: 0, plazo_meses: 6, glosa: '' };
      this.modalNuevo = true;
    },
    async buscarAbonadoDeuda() {
      if (!this.codigoAbonadoBuscar) return;
      this.buscandoDeuda = true;
      try {
        const res = await axios.get(`/api/comercial/caja/estado-cuenta/${this.codigoAbonadoBuscar.trim()}`);
        this.abonadoDeuda = res.data.data.abonado;
        this.simular();
      } catch (e) {
        alert('Abonado no encontrado o sin deuda.');
        this.abonadoDeuda = null;
      } finally {
        this.buscandoDeuda = false;
      }
    },
    async simular() {
      if (!this.abonadoDeuda || this.formConvenio.plazo_meses <= 0) return;
      try {
        const res = await axios.post('/api/comercial/convenios/simular', {
          monto_deuda: this.abonadoDeuda.saldo_deuda,
          pago_inicial: this.formConvenio.pago_inicial || 0,
          plazo_meses: this.formConvenio.plazo_meses,
        });
        this.simulacionResultado = res.data.data;
      } catch (e) {
        this.simulacionResultado = null;
      }
    },
    async suscribirConvenio() {
      if (!this.abonadoDeuda) return;
      this.suscribiendo = true;
      try {
        await axios.post('/api/comercial/convenios', {
          id_abonado: this.abonadoDeuda.id,
          pago_inicial: this.formConvenio.pago_inicial || 0,
          plazo_meses: this.formConvenio.plazo_meses,
          glosa: this.formConvenio.glosa,
        });
        this.modalNuevo = false;
        alert('Convenio formalizado exitosamente.');
        this.cargarConvenios();
      } catch (e) {
        alert(e.response?.data?.message || 'Error al suscribir convenio.');
      } finally {
        this.suscribiendo = false;
      }
    },
    verDetalleCuotas(item) {
      this.convenioSeleccionado = item;
      this.modalCuotas = true;
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
