<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-sync</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Consola de Integración Contable Automática</h2>
            <span class="text-caption text-secondary">
              Generación de asientos de diario e ingreso automáticos integrados con Cajas, Facturación y Planillas RRHH
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            icon
            color="primary"
            class="ml-2"
            :loading="cargandoPendientes"
            @click="cargarPendientes"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- NAVEGACIÓN POR PESTAÑAS DE INTEGRACIÓN -->
    <v-tabs v-model="tabActivo" color="indigo darken-3" class="mb-4">
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-cash-register</v-icon> Cajas y Recaudaciones (CI)
      </v-tab>
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-water-pump</v-icon> Facturación y Lecturas de Agua (CD)
      </v-tab>
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-account-cash-outline</v-icon> Planillas de Sueldos y Cargas RRHH (CD)
      </v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActivo">
      <!-- PESTAÑA 1: INTEGRACIÓN CON CAJAS COMERCIALES (COMPROBANTES DE INGRESO CI) -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 elevation-1">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3">
            <div>
              <h3 class="text-h6 font-weight-bold mb-1 indigo--text text--darken-3">
                <v-icon left color="indigo darken-3">mdi-check-circle-outline</v-icon>
                Sesiones de Caja Cerradas y Arqueadas
              </h3>
              <p class="text-caption text-secondary mb-0">
                Seleccione un turno cerrado para generar el Comprobante de Ingreso (CI) con débito automático a Caja Efectivo / Banco QR y abono a Cuentas por Cobrar.
              </p>
            </div>
          </div>

          <v-data-table
            :headers="headersCajas"
            :items="sesionesCaja"
            :loading="cargandoPendientes"
            class="elevation-0 border rounded-lg"
            no-data-text="No hay sesiones de caja pendientes de contabilización"
          >
            <template v-slot:item.id="{ item }">
              <strong>#{{ item.id }}</strong>
            </template>

            <template v-slot:item.caja="{ item }">
              <span class="font-weight-medium">{{ item.caja ? item.caja.nombre : 'Caja ' + item.id_punto_venta }}</span>
            </template>

            <template v-slot:item.cajero="{ item }">
              <span>{{ item.cajero ? item.cajero.name : 'Cajero ID ' + item.id_cajero }}</span>
            </template>

            <template v-slot:item.fecha_cierre="{ item }">
              <span>{{ item.fecha_cierre || item.updated_at }}</span>
            </template>

            <template v-slot:item.total_recaudado="{ item }">
              <span class="font-weight-bold text-subtitle-2 green--text text--darken-2">
                Bs. {{ formatearNumero(item.total_recaudado) }}
              </span>
            </template>

            <template v-slot:item.desglose="{ item }">
              <div class="text-caption">
                <div>Efectivo: <strong>Bs. {{ formatearNumero(item.total_efectivo) }}</strong></div>
                <div>Digital/QR: <strong>Bs. {{ formatearNumero(item.total_digital) }}</strong></div>
              </div>
            </template>

            <template v-slot:item.estado_contable="{ item }">
              <v-chip
                x-small
                label
                :color="item.comprobante_id ? 'green darken-2' : 'orange darken-3'"
                text-color="white"
                class="font-weight-bold"
              >
                {{ item.comprobante_id ? 'CONTABILIZADO' : 'PENDIENTE' }}
              </v-chip>
            </template>

            <template v-slot:item.acciones="{ item }">
              <div v-if="item.comprobante_id">
                <v-btn
                  small
                  color="indigo darken-2"
                  dark
                  outlined
                  class="rounded-pill text-capitalize"
                  @click="abrirPdfComprobante(item.comprobante_id)"
                >
                  <v-icon left x-small>mdi-file-pdf-box</v-icon> Ver CI
                </v-btn>
              </div>
              <div v-else>
                <v-btn
                  small
                  color="indigo darken-2"
                  dark
                  class="rounded-pill text-capitalize"
                  :loading="procesandoCajaId === item.id"
                  @click="contabilizarSesionCaja(item)"
                >
                  <v-icon left x-small>mdi-send-check</v-icon> Contabilizar CI
                </v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 2: DEVENGAMIENTO DE FACTURACIÓN Y LECTURAS DE AGUA (COMPROBANTE DE DIARIO CD) -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-5 mb-4 elevation-1">
          <h3 class="text-h6 font-weight-bold mb-1 indigo--text text--darken-3">
            <v-icon left color="indigo darken-3">mdi-water-sync</v-icon>
            Devengamiento Mensual por Facturación de Agua Potable y Alcantarillado
          </h3>
          <p class="text-caption text-secondary mb-4">
            Genera el Comprobante de Diario (CD) automático reconociendo la exigibilidad del derecho de cobro (100% en Cuentas por Cobrar), el Débito Fiscal IVA (13%) y los ingresos institucionales devengados (87%).
          </p>

          <v-row dense align="center" class="mb-4">
            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="periodoAgua.gestion"
                :items="gestionesOpciones"
                label="Gestión Fiscal *"
                outlined
                dense
                hide-details
              ></v-select>
            </v-col>

            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="periodoAgua.mes"
                :items="mesesOpciones"
                label="Mes de Facturación *"
                outlined
                dense
                hide-details
              ></v-select>
            </v-col>

            <v-col cols="12" sm="4" md="3">
              <v-btn
                color="indigo darken-2"
                dark
                large
                class="rounded-pill font-weight-medium"
                :loading="procesandoAgua"
                @click="contabilizarLecturasAgua"
              >
                <v-icon left>mdi-calculator-variant</v-icon> Generar Asiento CD
              </v-btn>
            </v-col>
          </v-row>

          <!-- ESQUEMA SAFCO DE LA OPERACIÓN -->
          <v-card outlined rounded="lg" class="pa-4 grey lighten-5">
            <div class="text-subtitle-2 font-weight-bold indigo--text text--darken-3 mb-2">
              <v-icon small left color="indigo darken-3">mdi-information-outline</v-icon>
              Estructura Automática del Asiento Contable (Ley 1178):
            </div>
            <v-simple-table dense class="transparent">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Cuenta Contable</th>
                  <th>Naturaleza</th>
                  <th class="text-right">Proporción Ley 843</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>1.1.2.01.001</code></td>
                  <td>Cuentas por Cobrar por Servicio de Agua</td>
                  <td><v-chip x-small color="blue darken-2" text-color="white">DEBE</v-chip></td>
                  <td class="text-right font-weight-bold">100% (Total Facturado)</td>
                </tr>
                <tr>
                  <td><code>2.1.2.01</code></td>
                  <td>Débito Fiscal IVA por Pagar</td>
                  <td><v-chip x-small color="purple darken-2" text-color="white">HABER</v-chip></td>
                  <td class="text-right font-weight-bold">13% (Impuesto IVA)</td>
                </tr>
                <tr>
                  <td><code>5.1.1.01</code></td>
                  <td>Ingresos por Venta de Servicio de Agua</td>
                  <td><v-chip x-small color="purple darken-2" text-color="white">HABER</v-chip></td>
                  <td class="text-right font-weight-bold">87% (Importe Neto Agua)</td>
                </tr>
                <tr>
                  <td><code>5.1.1.02</code></td>
                  <td>Ingresos por Servicio de Alcantarillado</td>
                  <td><v-chip x-small color="purple darken-2" text-color="white">HABER</v-chip></td>
                  <td class="text-right font-weight-bold">87% (Importe Neto Alc.)</td>
                </tr>
              </tbody>
            </v-simple-table>
          </v-card>

          <!-- RESULTADO DE LA ÚLTIMA GENERACIÓN -->
          <v-alert
            v-if="resultadoAgua"
            type="success"
            dense
            outlined
            class="mt-4"
          >
            <div class="d-flex align-center justify-space-between flex-wrap">
              <div>
                <strong>Asiento generado exitosamente:</strong>
                Comprobante N° {{ resultadoAgua.nro_comprobante }} por Bs. {{ formatearNumero(resultadoAgua.total_debe) }}.
              </div>
              <v-btn
                small
                color="indigo darken-2"
                dark
                class="rounded-pill mt-2 mt-sm-0"
                @click="abrirPdfComprobante(resultadoAgua.id)"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Ver Comprobante PDF
              </v-btn>
            </div>
          </v-alert>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 3: DEVENGAMIENTO DE PLANILLAS DE SUELDOS Y CARGAS PATRONALES (COMPROBANTE DE DIARIO CD) -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-5 mb-4 elevation-1">
          <h3 class="text-h6 font-weight-bold mb-1 indigo--text text--darken-3">
            <v-icon left color="indigo darken-3">mdi-account-group</v-icon>
            Devengamiento de Planilla de Sueldos y Cargas Sociales RRHH
          </h3>
          <p class="text-caption text-secondary mb-4">
            Genera automáticamente el asiento contable de sueldos y aportes patronales (CNS 10%, Gestora 1.71%, Fondo Solidario 3%, Provivienda 2%), reteniendo los aportes laborales de ley y registrando los pasivos institucionales.
          </p>

          <v-row dense align="center" class="mb-4">
            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="periodoRrhh.gestion"
                :items="gestionesOpciones"
                label="Gestión Fiscal *"
                outlined
                dense
                hide-details
              ></v-select>
            </v-col>

            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="periodoRrhh.mes"
                :items="mesesOpciones"
                label="Mes de la Planilla *"
                outlined
                dense
                hide-details
              ></v-select>
            </v-col>

            <v-col cols="12" sm="4" md="3">
              <v-btn
                color="indigo darken-2"
                dark
                large
                class="rounded-pill font-weight-medium"
                :loading="procesandoRrhh"
                @click="contabilizarPlanillaRrhh"
              >
                <v-icon left>mdi-account-cash</v-icon> Generar Asiento RRHH
              </v-btn>
            </v-col>
          </v-row>

          <!-- RESUMEN DE LEY SOCIAL BOLIVIANA -->
          <v-row dense>
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-3">
                <div class="text-subtitle-2 font-weight-bold green--text text--darken-3 mb-1">
                  <v-icon small left color="green darken-3">mdi-cash-plus</v-icon>
                  Gastos de Operación y Personal (Debe)
                </div>
                <div class="text-caption text-secondary">
                  • Cuenta <code>6.1.1.01</code>: Sueldos Básicos (Total Ganado Bruto)<br />
                  • Cuenta <code>6.1.1.05</code>: Caja Nacional de Salud (10.00% patronal)<br />
                  • Cuenta <code>6.1.1.06</code>: Prima Riesgo Profesional Gestora (1.71% patronal)<br />
                  • Cuenta <code>6.1.1.07</code>: Aporte Patronal Solidario (3.00%)<br />
                  • Cuenta <code>6.1.1.08</code>: Fondo Social Provivienda (2.00%)
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-3">
                <div class="text-subtitle-2 font-weight-bold red--text text--darken-3 mb-1">
                  <v-icon small left color="red darken-3">mdi-cash-minus</v-icon>
                  Pasivos y Obligaciones Laborales (Haber)
                </div>
                <div class="text-caption text-secondary">
                  • Cuenta <code>2.1.1.01</code>: Sueldos y Salarios por Pagar (Líquido Pagable)<br />
                  • Cuenta <code>2.1.2.02</code>: Retenciones Laborales Gestora SIP (12.71% empleado)<br />
                  • Cuenta <code>2.1.2.03</code>: Aportes Patronales por Pagar (16.71% a pagar a Entidades)
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- RESULTADO RRHH -->
          <v-alert
            v-if="resultadoRrhh"
            type="success"
            dense
            outlined
            class="mt-4"
          >
            <div class="d-flex align-center justify-space-between flex-wrap">
              <div>
                <strong>Asiento de Planilla generado exitosamente:</strong>
                Comprobante N° {{ resultadoRrhh.nro_comprobante }} por Bs. {{ formatearNumero(resultadoRrhh.total_debe) }}.
              </div>
              <v-btn
                small
                color="indigo darken-2"
                dark
                class="rounded-pill mt-2 mt-sm-0"
                @click="abrirPdfComprobante(resultadoRrhh.id)"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Ver Comprobante PDF
              </v-btn>
            </div>
          </v-alert>
        </v-card>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>

<script>
export default {
  name: 'InterfasesAutomaticas',
  data() {
    const anioActual = new Date().getFullYear()
    const mesActual = new Date().getMonth() + 1

    return {
      tabActivo: 0,
      cargandoPendientes: false,
      procesandoCajaId: null,
      procesandoAgua: false,
      procesandoRrhh: false,

      sesionesCaja: [],

      headersCajas: [
        { text: 'N° Sesión', value: 'id', width: '90px' },
        { text: 'Caja / Punto Venta', value: 'caja' },
        { text: 'Cajero(a)', value: 'cajero' },
        { text: 'Fecha Cierre', value: 'fecha_cierre', width: '160px' },
        { text: 'Recaudación Total', value: 'total_recaudado', width: '140px', align: 'end' },
        { text: 'Desglose Modalidad', value: 'desglose', width: '160px' },
        { text: 'Estado', value: 'estado_contable', width: '130px', align: 'center' },
        { text: 'Acciones', value: 'acciones', width: '150px', align: 'center', sortable: false },
      ],

      gestionesOpciones: [2025, 2026, 2027],
      mesesOpciones: [
        { text: 'Enero', value: 1 },
        { text: 'Febrero', value: 2 },
        { text: 'Marzo', value: 3 },
        { text: 'Abril', value: 4 },
        { text: 'Mayo', value: 5 },
        { text: 'Junio', value: 6 },
        { text: 'Julio', value: 7 },
        { text: 'Agosto', value: 8 },
        { text: 'Septiembre', value: 9 },
        { text: 'Octubre', value: 10 },
        { text: 'Noviembre', value: 11 },
        { text: 'Diciembre', value: 12 },
      ],

      periodoAgua: {
        gestion: anioActual,
        mes: mesActual,
      },
      resultadoAgua: null,

      periodoRrhh: {
        gestion: anioActual,
        mes: mesActual,
      },
      resultadoRrhh: null,
    }
  },

  mounted() {
    this.cargarPendientes()
  },

  methods: {
    cargarPendientes() {
      this.cargandoPendientes = true
      axios
        .get('/api/contabilidad/interfases/pendientes')
        .then(res => {
          if (res.data && res.data.data) {
            this.sesionesCaja = res.data.data.cajas_pendientes || []
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al consultar sesiones de caja pendientes')
        })
        .finally(() => {
          this.cargandoPendientes = false
        })
    },

    contabilizarSesionCaja(item) {
      this.procesandoCajaId = item.id
      axios
        .post('/api/contabilidad/interfases/contabilizar-caja', {
          caja_sesion_id: item.id,
        })
        .then(res => {
          this.notificar('success', res.data.message || 'Comprobante de Ingreso (CI) generado exitosamente')
          this.cargarPendientes()
          if (res.data && res.data.data && res.data.data.id) {
            this.abrirPdfComprobante(res.data.data.id)
          }
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al contabilizar la sesión de caja'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.procesandoCajaId = null
        })
    },

    contabilizarLecturasAgua() {
      this.procesandoAgua = true
      this.resultadoAgua = null
      axios
        .post('/api/contabilidad/interfases/contabilizar-lecturas', {
          gestion: this.periodoAgua.gestion,
          mes: this.periodoAgua.mes,
        })
        .then(res => {
          this.notificar('success', res.data.message || 'Asiento de devengamiento de agua registrado')
          if (res.data && res.data.data) {
            this.resultadoAgua = res.data.data
          }
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al contabilizar la facturación de agua'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.procesandoAgua = false
        })
    },

    contabilizarPlanillaRrhh() {
      this.procesandoRrhh = true
      this.resultadoRrhh = null
      axios
        .post('/api/contabilidad/interfases/contabilizar-planilla-sueldos', {
          gestion: this.periodoRrhh.gestion,
          mes: this.periodoRrhh.mes,
        })
        .then(res => {
          this.notificar('success', res.data.message || 'Asiento de planilla RRHH devengado exitosamente')
          if (res.data && res.data.data) {
            this.resultadoRrhh = res.data.data
          }
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al contabilizar la planilla de sueldos'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.procesandoRrhh = false
        })
    },

    abrirPdfComprobante(id) {
      window.open('/api/contabilidad/comprobantes/' + id + '/pdf', '_blank')
    },

    formatearNumero(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00'
      return Number(val).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },

    notificar(tipo, mensaje) {
      if (window.iziToast) {
        if (tipo === 'success') {
          window.iziToast.success({ title: 'Éxito', message: mensaje, position: 'topRight' })
        } else {
          window.iziToast.error({ title: 'Error', message: mensaje, position: 'topRight' })
        }
      } else {
        alert(mensaje)
      }
    },
  },
}
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05) !important;
}
.gap-2 {
  gap: 8px;
}
</style>
