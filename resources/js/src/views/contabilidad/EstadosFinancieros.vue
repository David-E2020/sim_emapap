<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-chart-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Estados Financieros Oficiales (NBSCI - Ley 1178)</h2>
            <span class="text-caption text-secondary">
              Balance de Comprobación de Sumas y Saldos, Balance General y Estado de Rendimiento Institucional
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="indigo darken-2"
            dark
            outlined
            class="rounded-pill font-weight-medium"
            @click="imprimirEstado"
          >
            <v-icon left>mdi-printer</v-icon> Imprimir Reporte
          </v-btn>
          <v-btn
            color="teal darken-2"
            dark
            class="rounded-pill font-weight-medium elevation-1"
            @click="exportarCsv"
          >
            <v-icon left>mdi-file-delimited-outline</v-icon> Exportar CSV
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BARRA DE FILTROS GLOBALES -->
    <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" sm="3">
          <v-select
            v-model="filtros.gestion"
            :items="gestiones"
            item-value="gestion"
            item-text="gestion"
            label="Gestión Fiscal"
            outlined
            dense
            hide-details
            @change="cargarReporteActual"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="3">
          <v-text-field
            v-model="filtros.fecha_inicio"
            label="Fecha Inicio"
            type="date"
            outlined
            dense
            hide-details
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="3">
          <v-text-field
            v-model="filtros.fecha_fin"
            label="Fecha Fin (Corte)"
            type="date"
            outlined
            dense
            hide-details
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="3">
          <v-btn
            color="indigo darken-2"
            dark
            block
            class="rounded-pill font-weight-medium"
            :loading="cargando"
            @click="cargarReporteActual"
          >
            <v-icon left>mdi-refresh</v-icon> Actualizar Estados
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- PESTAÑAS DE REPORTES -->
    <v-tabs v-model="tabActual" color="indigo darken-3" class="mb-4" @change="alCambiarTab">
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-table-headers-eye</v-icon> Balance de Comprobación (Sumas y Saldos)
      </v-tab>
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-scale-balance</v-icon> Balance General Clasificado
      </v-tab>
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-chart-line</v-icon> Estado de Rendimiento (Pérdidas y Ganancias)
      </v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- TAB 1: BALANCE DE COMPROBACIÓN DE SUMAS Y SALDOS -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 elevation-1">
          <!-- VERIFICACIÓN DE CUADRE SAFCO -->
          <div class="pa-3 mb-4 rounded-lg d-flex align-center justify-space-between flex-wrap indigo lighten-5">
            <div>
              <span class="text-caption text-secondary font-weight-bold">SUMAS TOTALES:</span>
              <div class="text-subtitle-1 font-weight-bold">
                Debe: <span class="green--text text--darken-2">Bs. {{ formatearNumero(totalesSumasSaldos.total_debe) }}</span>
                | Haber: <span class="green--text text--darken-2">Bs. {{ formatearNumero(totalesSumasSaldos.total_haber) }}</span>
              </div>
            </div>

            <div>
              <span class="text-caption text-secondary font-weight-bold">SALDOS TOTALES:</span>
              <div class="text-subtitle-1 font-weight-bold">
                Deudor: <span class="indigo--text text--darken-3">Bs. {{ formatearNumero(totalesSumasSaldos.total_deudor) }}</span>
                | Acreedor: <span class="indigo--text text--darken-3">Bs. {{ formatearNumero(totalesSumasSaldos.total_acreedor) }}</span>
              </div>
            </div>

            <div>
              <v-chip
                label
                :color="cuadrePerfectoSumasSaldos ? 'green darken-2' : 'red darken-2'"
                text-color="white"
                class="font-weight-bold"
              >
                {{ cuadrePerfectoSumasSaldos ? 'CUADRE SAFCO PERFECTO' : 'DESCUADRE DETECTADO' }}
              </v-chip>
            </div>
          </div>

          <!-- TABLA DE 6 COLUMNAS -->
          <v-data-table
            :headers="headersSumasSaldos"
            :items="cuentasSumasSaldos"
            :loading="cargando"
            :items-per-page="20"
            class="elevation-0 border rounded-lg"
            no-data-text="No hay movimientos registrados para el balance de comprobación"
          >
            <template v-slot:item.codigo="{ item }">
              <code>{{ item.codigo }}</code>
            </template>

            <template v-slot:item.nombre="{ item }">
              <span class="font-weight-medium">{{ item.nombre }}</span>
            </template>

            <template v-slot:item.suma_debe="{ item }">
              <span class="font-weight-medium">{{ Number(item.suma_debe) > 0 ? formatearNumero(item.suma_debe) : '-' }}</span>
            </template>

            <template v-slot:item.suma_haber="{ item }">
              <span class="font-weight-medium">{{ Number(item.suma_haber) > 0 ? formatearNumero(item.suma_haber) : '-' }}</span>
            </template>

            <template v-slot:item.saldo_deudor="{ item }">
              <span class="font-weight-bold indigo--text text--darken-3">
                {{ Number(item.saldo_deudor) > 0 ? formatearNumero(item.saldo_deudor) : '-' }}
              </span>
            </template>

            <template v-slot:item.saldo_acreedor="{ item }">
              <span class="font-weight-bold purple--text text--darken-3">
                {{ Number(item.saldo_acreedor) > 0 ? formatearNumero(item.saldo_acreedor) : '-' }}
              </span>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- TAB 2: BALANCE GENERAL CLASIFICADO -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-5 mb-4 elevation-1" v-if="balanceGeneralDatos">
          <!-- ECUACIÓN FUNDAMENTAL -->
          <div class="pa-4 mb-4 rounded-lg d-flex align-center justify-space-around flex-wrap text-center indigo lighten-5">
            <div>
              <div class="text-caption text-secondary font-weight-bold">TOTAL ACTIVO</div>
              <div class="text-h6 font-weight-bold green--text text--darken-3">
                Bs. {{ formatearNumero(balanceGeneralDatos.total_activo) }}
              </div>
            </div>
            <div class="text-h5 font-weight-bold indigo--text">=</div>
            <div>
              <div class="text-caption text-secondary font-weight-bold">TOTAL PASIVO</div>
              <div class="text-h6 font-weight-bold red--text text--darken-3">
                Bs. {{ formatearNumero(balanceGeneralDatos.total_pasivo) }}
              </div>
            </div>
            <div class="text-h5 font-weight-bold indigo--text">+</div>
            <div>
              <div class="text-caption text-secondary font-weight-bold">PATRIMONIO + RESULTADO</div>
              <div class="text-h6 font-weight-bold purple--text text--darken-3">
                Bs. {{ formatearNumero(Number(balanceGeneralDatos.total_patrimonio) + Number(balanceGeneralDatos.resultado_gestion)) }}
              </div>
            </div>
            <div>
              <v-chip
                label
                :color="balanceGeneralDatos.balance_cuadrado ? 'green darken-2' : 'red darken-2'"
                text-color="white"
                class="font-weight-bold"
              >
                {{ balanceGeneralDatos.balance_cuadrado ? 'ACTIVO = PASIVO + PATRIMONIO' : 'DESCUADRE EN BALANCE' }}
              </v-chip>
            </div>
          </div>

          <v-row dense>
            <!-- COLUMNA ACTIVO -->
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-4">
                <div class="text-h6 font-weight-bold green--text text--darken-3 mb-2">
                  <v-icon color="green darken-3" left>mdi-plus-circle-outline</v-icon>
                  1. ACTIVO INSTITUCIONAL
                </div>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Cuenta</th>
                      <th class="text-right">Saldo (Bs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in balanceGeneralDatos.cuentas_activo" :key="'act-' + c.id">
                      <td><code>{{ c.codigo }}</code></td>
                      <td>{{ c.nombre }}</td>
                      <td class="text-right font-weight-bold">Bs. {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="green lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL ACTIVO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 green--text text--darken-4">
                        Bs. {{ formatearNumero(balanceGeneralDatos.total_activo) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>

            <!-- COLUMNA PASIVO Y PATRIMONIO -->
            <v-col cols="12" md="6">
              <!-- PASIVO -->
              <v-card outlined rounded="lg" class="pa-4 mb-4">
                <div class="text-h6 font-weight-bold red--text text--darken-3 mb-2">
                  <v-icon color="red darken-3" left>mdi-minus-circle-outline</v-icon>
                  2. PASIVO (OBLIGACIONES)
                </div>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Cuenta</th>
                      <th class="text-right">Saldo (Bs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in balanceGeneralDatos.cuentas_pasivo" :key="'pas-' + c.id">
                      <td><code>{{ c.codigo }}</code></td>
                      <td>{{ c.nombre }}</td>
                      <td class="text-right font-weight-bold">Bs. {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="red lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL PASIVO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 red--text text--darken-4">
                        Bs. {{ formatearNumero(balanceGeneralDatos.total_pasivo) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>

              <!-- PATRIMONIO -->
              <v-card outlined rounded="lg" class="pa-4">
                <div class="text-h6 font-weight-bold purple--text text--darken-3 mb-2">
                  <v-icon color="purple darken-3" left>mdi-domain</v-icon>
                  3. PATRIMONIO INSTITUCIONAL
                </div>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Cuenta</th>
                      <th class="text-right">Saldo (Bs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in balanceGeneralDatos.cuentas_patrimonio" :key="'pat-' + c.id">
                      <td><code>{{ c.codigo }}</code></td>
                      <td>{{ c.nombre }}</td>
                      <td class="text-right font-weight-bold">Bs. {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                    <tr>
                      <td><code>3.1.3</code></td>
                      <td class="font-weight-medium">Resultado del Ejercicio (Superávit/Déficit)</td>
                      <td class="text-right font-weight-bold indigo--text text--darken-3">
                        Bs. {{ formatearNumero(balanceGeneralDatos.resultado_gestion) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="purple lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL PASIVO + PATRIMONIO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 purple--text text--darken-4">
                        Bs. {{ formatearNumero(Number(balanceGeneralDatos.total_pasivo) + Number(balanceGeneralDatos.total_patrimonio) + Number(balanceGeneralDatos.resultado_gestion)) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-tab-item>

      <!-- TAB 3: ESTADO DE RENDIMIENTO INSTITUCIONAL (RESULTADOS) -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-5 mb-4 elevation-1" v-if="estadoResultadosDatos">
          <!-- TARJETA RESUMEN RESULTADO -->
          <div class="pa-4 mb-4 rounded-lg d-flex align-center justify-space-between flex-wrap indigo lighten-5">
            <div>
              <span class="text-caption text-secondary font-weight-bold">INGRESOS DE OPERACIÓN:</span>
              <div class="text-h6 font-weight-bold green--text text--darken-2">
                Bs. {{ formatearNumero(estadoResultadosDatos.total_ingresos) }}
              </div>
            </div>

            <div>
              <span class="text-caption text-secondary font-weight-bold">GASTOS DE OPERACIÓN:</span>
              <div class="text-h6 font-weight-bold red--text text--darken-2">
                Bs. {{ formatearNumero(estadoResultadosDatos.total_gastos) }}
              </div>
            </div>

            <div>
              <span class="text-caption text-secondary font-weight-bold">RESULTADO NETO:</span>
              <div class="text-h5 font-weight-bold" :class="Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'green--text text--darken-3' : 'red--text text--darken-3'">
                Bs. {{ formatearNumero(estadoResultadosDatos.resultado_neto) }}
                ({{ estadoResultadosDatos.tipo_resultado }})
              </div>
            </div>
          </div>

          <v-row dense>
            <!-- INGRESOS -->
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-4">
                <div class="text-h6 font-weight-bold teal--text text--darken-3 mb-2">
                  <v-icon color="teal darken-3" left>mdi-cash-plus</v-icon>
                  5. INGRESOS CORRIENTES Y DE OPERACIÓN
                </div>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Concepto de Ingreso</th>
                      <th class="text-right">Monto (Bs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in estadoResultadosDatos.cuentas_ingreso" :key="'ing-' + c.id">
                      <td><code>{{ c.codigo }}</code></td>
                      <td>{{ c.nombre }}</td>
                      <td class="text-right font-weight-bold">Bs. {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="teal lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL INGRESOS:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 teal--text text--darken-4">
                        Bs. {{ formatearNumero(estadoResultadosDatos.total_ingresos) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>

            <!-- GASTOS -->
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-4">
                <div class="text-h6 font-weight-bold red--text text--darken-3 mb-2">
                  <v-icon color="red darken-3" left>mdi-cash-minus</v-icon>
                  6. GASTOS DE OPERACIÓN Y FUNCIONAMIENTO
                </div>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Concepto de Gasto</th>
                      <th class="text-right">Monto (Bs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in estadoResultadosDatos.cuentas_gasto" :key="'gas-' + c.id">
                      <td><code>{{ c.codigo }}</code></td>
                      <td>{{ c.nombre }}</td>
                      <td class="text-right font-weight-bold">Bs. {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="red lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL GASTOS:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 red--text text--darken-4">
                        Bs. {{ formatearNumero(estadoResultadosDatos.total_gastos) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>

<script>
export default {
  name: 'EstadosFinancieros',
  data() {
    const anioActual = new Date().getFullYear()
    const primerDiaAnio = anioActual + '-01-01'
    const hoy = new Date().toISOString().substring(0, 10)

    return {
      tabActual: 0,
      cargando: false,

      filtros: {
        gestion: anioActual,
        fecha_inicio: primerDiaAnio,
        fecha_fin: hoy,
      },

      gestiones: [
        { gestion: 2025 },
        { gestion: 2026 },
        { gestion: 2027 },
      ],

      // DATOS TAB 1
      cuentasSumasSaldos: [],
      totalesSumasSaldos: {
        total_debe: 0,
        total_haber: 0,
        total_deudor: 0,
        total_acreedor: 0,
      },
      headersSumasSaldos: [
        { text: 'Código', value: 'codigo', width: '160px' },
        { text: 'Nombre de la Cuenta', value: 'nombre' },
        { text: 'Suma Debe (Bs.)', value: 'suma_debe', align: 'end', width: '140px' },
        { text: 'Suma Haber (Bs.)', value: 'suma_haber', align: 'end', width: '140px' },
        { text: 'Saldo Deudor (Bs.)', value: 'saldo_deudor', align: 'end', width: '140px' },
        { text: 'Saldo Acreedor (Bs.)', value: 'saldo_acreedor', align: 'end', width: '140px' },
      ],

      // DATOS TAB 2
      balanceGeneralDatos: null,

      // DATOS TAB 3
      estadoResultadosDatos: null,
    }
  },

  computed: {
    cuadrePerfectoSumasSaldos() {
      const debe = Math.round(Number(this.totalesSumasSaldos.total_debe) * 100)
      const haber = Math.round(Number(this.totalesSumasSaldos.total_haber) * 100)
      const deudor = Math.round(Number(this.totalesSumasSaldos.total_deudor) * 100)
      const acreedor = Math.round(Number(this.totalesSumasSaldos.total_acreedor) * 100)
      return debe === haber && deudor === acreedor
    },
  },

  mounted() {
    this.cargarGestiones()
    this.cargarReporteActual()
  },

  methods: {
    cargarGestiones() {
      axios
        .get('/api/contabilidad/gestiones')
        .then(res => {
          if (res.data && res.data.data && res.data.data.length > 0) {
            this.gestiones = res.data.data
          }
        })
        .catch(() => {})
    },

    alCambiarTab(nuevoIndex) {
      this.cargarReporteActual()
    },

    cargarReporteActual() {
      if (this.tabActual === 0) {
        this.cargarSumasSaldos()
      } else if (this.tabActual === 1) {
        this.cargarBalanceGeneral()
      } else if (this.tabActual === 2) {
        this.cargarEstadoResultados()
      }
    },

    cargarSumasSaldos() {
      this.cargando = true
      axios
        .get('/api/contabilidad/reportes/balance-comprobacion', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.cuentasSumasSaldos = res.data.data.cuentas || []
            this.totalesSumasSaldos = res.data.data.totales || {
              total_debe: 0,
              total_haber: 0,
              total_deudor: 0,
              total_acreedor: 0,
            }
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Balance de Comprobación')
        })
        .finally(() => {
          this.cargando = false
        })
    },

    cargarBalanceGeneral() {
      this.cargando = true
      axios
        .get('/api/contabilidad/reportes/balance-general', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.balanceGeneralDatos = res.data.data
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Balance General')
        })
        .finally(() => {
          this.cargando = false
        })
    },

    cargarEstadoResultados() {
      this.cargando = true
      axios
        .get('/api/contabilidad/reportes/estado-resultados', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.estadoResultadosDatos = res.data.data
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Estado de Rendimiento')
        })
        .finally(() => {
          this.cargando = false
        })
    },

    imprimirEstado() {
      window.print()
    },

    exportarCsv() {
      if (this.tabActual === 0 && this.cuentasSumasSaldos.length > 0) {
        let csvContent = 'data:text/csv;charset=utf-8,Codigo,Nombre,Suma Debe,Suma Haber,Saldo Deudor,Saldo Acreedor\n'
        this.cuentasSumasSaldos.forEach(row => {
          csvContent += `"${row.codigo}","${row.nombre}",${row.suma_debe},${row.suma_haber},${row.saldo_deudor},${row.saldo_acreedor}\n`
        })
        const encodedUri = encodeURI(csvContent)
        const link = document.createElement('a')
        link.setAttribute('href', encodedUri)
        link.setAttribute('download', `balance_comprobacion_${this.filtros.gestion}.csv`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
      } else {
        this.notificar('info', 'Exportación disponible para Balance de Sumas y Saldos')
      }
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
