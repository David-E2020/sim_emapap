<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-beach</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Kardex de Vacaciones y Descansos</h2>
            <span class="text-caption text-secondary">
              Cálculo de derecho vacacional de ley según antigüedad (LGT), días utilizados y saldos vigentes
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <v-btn
            color="primary"
            outlined
            class="rounded-pill font-weight-medium text-capitalize"
            @click="imprimirKardex"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir Kardex
          </v-btn>

          <v-btn
            color="indigo darken-1"
            dark
            class="rounded-pill font-weight-medium text-capitalize"
            @click="exportarCsv"
          >
            <v-icon left small>mdi-file-delimited</v-icon> Exportar CSV
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS KPIS -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Funcionarios Evaluados</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ listaVacaciones.length }}</div>
          <div class="text-caption text-secondary">Personal con registro laboral</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Días de Derecho Acumulado</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ totalDiasDerecho }}</div>
          <div class="text-caption text-secondary">Según escala de la LGT</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Días Utilizados / Gozados</div>
          <div class="text-h4 font-weight-black warning--text text--darken-3 mt-1">{{ totalDiasGozados }}</div>
          <div class="text-caption text-secondary">Licencias anuales tomadas</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Saldo Total Disponible</div>
          <div class="text-h4 font-weight-black indigo--text mt-1">{{ totalDiasSaldo }}</div>
          <div class="text-caption text-secondary">Días pendientes de goce</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ESCALA LEGAL INFORMATIVA -->
    <v-alert dense outlined type="info" class="text-caption mb-4">
      <strong>Escala Legal de Vacaciones (Ley General del Trabajo - Bolivia):</strong>
      De 1 a 5 años de servicio: <strong>15 días hábiles</strong> &bull;
      De 5 a 10 años de servicio: <strong>20 días hábiles</strong> &bull;
      De 10 años en adelante: <strong>30 días hábiles</strong>.
    </v-alert>

    <!-- CARD PRINCIPAL: TABLA DE KARDEX -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-account-clock</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Kardex General de Saldos Vacacionales</span>
        </div>

        <div class="d-flex align-center gap-2" style="max-width: 320px; width: 100%;">
          <v-text-field
            v-model="busqueda"
            label="Buscar por funcionario o CI..."
            dense
            outlined
            hide-details
            prepend-inner-icon="mdi-magnify"
            clearable
          ></v-text-field>

          <v-btn icon color="primary" :loading="loading" @click="cargarKardex">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>

      <div id="vacaciones-print-area">
        <div class="d-none d-print-block text-center mb-4">
          <h3 class="font-weight-bold">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA</h3>
          <p class="text-caption">KARDEX INSTITUCIONAL DE CONTROL DE VACACIONES</p>
        </div>

        <v-data-table
          :headers="headers"
          :items="vacacionesFiltradas"
          :loading="loading"
          class="erp-table"
          dense
          :items-per-page="25"
          no-data-text="No se encontraron registros de vacaciones"
        >
          <!-- FUNCIONARIO -->
          <template v-slot:item.funcionario="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar size="32" color="primary" class="white--text mr-2 font-weight-bold text-caption">
                {{ item.nombres ? item.nombres.charAt(0) : 'F' }}
              </v-avatar>
              <div>
                <div class="font-weight-bold text-body-2">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido || '' }}</div>
                <div class="text-caption text-secondary">
                  C.I. {{ item.nro_documento }} &bull; <span class="primary--text font-weight-medium">{{ item.cargo || 'Funcionario' }}</span>
                </div>
              </div>
            </div>
          </template>

          <!-- ANTIGÜEDAD -->
          <template v-slot:item.antiguedad="{ item }">
            <div>
              <span class="font-weight-bold">{{ item.anios_antiguedad || 0 }} años</span>
              <div class="text-caption text-secondary">Ingreso: {{ item.fecha_ingreso || 'Sin registro' }}</div>
            </div>
          </template>

          <!-- ESCALA LEGAL -->
          <template v-slot:item.escala="{ item }">
            <v-chip x-small color="purple lighten-5 purple--text" label class="font-weight-bold">
              {{ getEscalaTexto(item.anios_antiguedad) }}
            </v-chip>
          </template>

          <!-- DERECHO -->
          <template v-slot:item.dias_derecho="{ item }">
            <span class="font-weight-bold success--text">{{ item.dias_derecho || 0 }} días</span>
          </template>

          <!-- GOZADOS -->
          <template v-slot:item.dias_gozados="{ item }">
            <span class="font-weight-medium warning--text text--darken-3">{{ item.dias_gozados || 0 }} días</span>
          </template>

          <!-- SALDO DISPONIBLE -->
          <template v-slot:item.saldo="{ item }">
            <v-chip
              small
              :color="(item.saldo || 0) > 0 ? 'success' : 'grey lighten-3'"
              :class="(item.saldo || 0) > 0 ? 'white--text font-weight-bold' : 'grey--text text--darken-1 font-weight-bold'"
            >
              {{ item.saldo || 0 }} días libres
            </v-chip>
          </template>
        </v-data-table>
      </div>
    </v-card>

    <!-- MODAL VISOR PDF OFICIAL (Igual que Facturación y Reportes) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :nombre-descarga="nombreDescargaPdf"
      max-width="1100px"
    ></modal-visor-pdf>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" :timeout="4000" top right rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text small v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'VacacionesRrhh',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      // Visor PDF Oficial
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      nombreDescargaPdf: 'KARDEX_VACACIONES.pdf',

      busqueda: '',
      loading: false,
      listaVacaciones: [],

      headers: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Antigüedad Institucional', value: 'antiguedad' },
        { text: 'Tramo de Ley', value: 'escala', align: 'center' },
        { text: 'Días Derecho', value: 'dias_derecho', align: 'center' },
        { text: 'Días Tomados', value: 'dias_gozados', align: 'center' },
        { text: 'Saldo Vacacional', value: 'saldo', align: 'center' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    vacacionesFiltradas() {
      if (!this.busqueda) return this.listaVacaciones;
      const q = this.busqueda.toLowerCase();
      return this.listaVacaciones.filter(v => {
        const nom = `${v.nombres || ''} ${v.primer_apellido || ''} ${v.segundo_apellido || ''}`.toLowerCase();
        const ci = (v.nro_documento || '').toLowerCase();
        const car = (v.cargo || '').toLowerCase();
        return nom.includes(q) || ci.includes(q) || car.includes(q);
      });
    },

    totalDiasDerecho() {
      return this.listaVacaciones.reduce((acc, v) => acc + (v.dias_derecho || 0), 0);
    },

    totalDiasGozados() {
      return this.listaVacaciones.reduce((acc, v) => acc + (v.dias_gozados || 0), 0);
    },

    totalDiasSaldo() {
      return this.listaVacaciones.reduce((acc, v) => acc + (v.saldo || 0), 0);
    },
  },
  mounted() {
    this.cargarKardex();
  },
  methods: {
    cargarKardex() {
      this.loading = true;
      axios.get('/api/rrhh/reportes/kardex-vacaciones')
        .then(res => {
          if (res.data && res.data.success) {
            this.listaVacaciones = (res.data.data || []).map(item => {
              const anios = item.anios_antiguedad || 0;
              let derecho = 15;
              if (anios >= 10) derecho = 30;
              else if (anios >= 5) derecho = 20;

              const gozados = item.dias_solicitados || 0;
              const saldo = Math.max(0, derecho - gozados);

              return {
                ...item,
                dias_derecho: derecho,
                dias_gozados: gozados,
                saldo: saldo,
              };
            });
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar kardex de vacaciones.', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    getEscalaTexto(anios) {
      if (!anios || anios < 5) return '1 a 5 años (15 días)';
      if (anios < 10) return '5 a 10 años (20 días)';
      return '10+ años (30 días)';
    },

    imprimirKardex() {
      this.urlVisorPdf = '/api/rrhh/reportes/kardex-vacaciones/pdf';
      this.tituloVisorPdf = 'Kardex Oficial de Vacaciones (LGT)';
      this.subtituloVisorPdf = `Personal Activo de EMAPAP · ${this.listaVacaciones.length} funcionarios`;
      this.nombreDescargaPdf = `KARDEX_VACACIONES_EMAPAP_${new Date().getFullYear()}.pdf`;
      this.mostrarVisorPdf = true;
    },

    exportarCsv() {
      if (!this.vacacionesFiltradas.length) {
        this.showSnackbar('No hay registros para exportar.', 'warning');
        return;
      }
      let csv = 'Funcionario;CI;Cargo;Fecha Ingreso;Anios Antiguedad;Dias Derecho;Dias Gozados;Saldo\n';
      this.vacacionesFiltradas.forEach(v => {
        csv += `"${v.nombres} ${v.primer_apellido}";"${v.nro_documento}";"${v.cargo}";"${v.fecha_ingreso}";"${v.anios_antiguedad}";"${v.dias_derecho}";"${v.dias_gozados}";"${v.saldo}"\n`;
      });
      const blob = new Blob(["\ufeff" + csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', `kardex_vacaciones_${new Date().getFullYear()}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      this.showSnackbar('Kardex de vacaciones exportado exitosamente.', 'success');
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #vacaciones-print-area, #vacaciones-print-area * {
    visibility: visible;
  }
  #vacaciones-print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    background: white;
  }
}
.gap-2 {
  gap: 8px;
}
</style>
