<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-book-open-page-variant-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Libros Oficiales: Diario y Mayor Analítico</h2>
            <span class="text-caption text-secondary">
              Registros cronológicos obligatorios y movimientos por cuenta para auditoría y control gubernamental
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="indigo darken-2"
            dark
            outlined
            class="rounded-pill font-weight-medium"
            @click="imprimirReporte"
          >
            <v-icon left>mdi-printer</v-icon> Imprimir Libro
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS: LIBRO DIARIO vs LIBRO MAYOR -->
    <v-tabs v-model="tabActual" color="indigo darken-3" class="mb-4">
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-format-list-numbered-rtl</v-icon> Libro Diario General
      </v-tab>
      <v-tab class="font-weight-bold">
        <v-icon left small>mdi-table-account</v-icon> Libro Mayor Analítico por Cuenta
      </v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: LIBRO DIARIO GENERAL -->
      <v-tab-item>
        <!-- FILTROS DIARIO -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <v-row dense align="center">
            <v-col cols="12" sm="3">
              <v-text-field
                v-model="filtroDiario.fecha_inicio"
                label="Fecha Desde"
                type="date"
                outlined
                dense
                hide-details
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="3">
              <v-text-field
                v-model="filtroDiario.fecha_fin"
                label="Fecha Hasta"
                type="date"
                outlined
                dense
                hide-details
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="3">
              <v-select
                v-model="filtroDiario.tipo"
                :items="tiposDiario"
                label="Tipo de Comprobante"
                outlined
                dense
                hide-details
                clearable
              ></v-select>
            </v-col>

            <v-col cols="12" sm="3">
              <v-btn
                color="indigo darken-2"
                dark
                block
                class="rounded-pill font-weight-medium"
                :loading="cargandoDiario"
                @click="cargarLibroDiario"
              >
                <v-icon left>mdi-magnify</v-icon> Consultar Diario
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETA TOTALES DIARIO -->
        <v-card rounded="lg" class="pa-3 mb-4 indigo lighten-5 elevation-1">
          <div class="d-flex align-center justify-space-around flex-wrap text-center">
            <div>
              <span class="text-caption font-weight-bold grey--text text--darken-2">ASIENTOS CONTABLES:</span>
              <div class="text-h6 font-weight-bold indigo--text text--darken-3">{{ asientosDiario.length }}</div>
            </div>
            <v-divider vertical class="mx-3"></v-divider>
            <div>
              <span class="text-caption font-weight-bold grey--text text--darken-2">SUMA TOTAL DEBE:</span>
              <div class="text-h6 font-weight-bold green--text text--darken-2">Bs. {{ formatearNumero(totalDebeDiario) }}</div>
            </div>
            <v-divider vertical class="mx-3"></v-divider>
            <div>
              <span class="text-caption font-weight-bold grey--text text--darken-2">SUMA TOTAL HABER:</span>
              <div class="text-h6 font-weight-bold green--text text--darken-2">Bs. {{ formatearNumero(totalHaberDiario) }}</div>
            </div>
            <v-divider vertical class="mx-3"></v-divider>
            <div>
              <span class="text-caption font-weight-bold grey--text text--darken-2">ESTADO DE CUADRE:</span>
              <div>
                <v-chip x-small color="green darken-2" text-color="white" class="font-weight-bold" v-if="totalDebeDiario === totalHaberDiario">
                  CUADRE EXACTO
                </v-chip>
                <v-chip x-small color="red darken-2" text-color="white" class="font-weight-bold" v-else>
                  DESCUADRE
                </v-chip>
              </div>
            </div>
          </div>
        </v-card>

        <!-- LISTA DE ASIENTOS DEL DIARIO -->
        <div v-if="cargandoDiario" class="text-center py-6">
          <v-progress-circular indeterminate color="indigo" size="40"></v-progress-circular>
          <div class="text-caption mt-2">Generando Libro Diario...</div>
        </div>

        <div v-else-if="asientosDiario.length === 0" class="text-center py-6 grey lighten-4 rounded-lg">
          <v-icon size="48" color="grey">mdi-book-open-outline</v-icon>
          <div class="text-subtitle-1 text-secondary mt-2">No se encontraron asientos contables en el rango seleccionado</div>
        </div>

        <div v-else>
          <v-card
            v-for="(asiento, idx) in asientosDiario"
            :key="'asiento-' + asiento.id"
            rounded="lg"
            class="mb-4 elevation-1 border"
          >
            <!-- CABECERA DEL ASIENTO -->
            <div class="pa-3 grey lighten-4 d-flex align-center justify-space-between flex-wrap">
              <div class="d-flex align-center gap-2">
                <v-chip small label :color="colorTipo(asiento.tipo)" text-color="white" class="font-weight-bold">
                  {{ asiento.tipo }}
                </v-chip>
                <span class="font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                  N° {{ asiento.nro_comprobante }}
                </span>
                <span class="text-caption grey--text text--darken-1">| Fecha: {{ asiento.fecha }}</span>
                <span class="text-caption font-weight-medium" v-if="asiento.beneficiario">| Beneficiario: {{ asiento.beneficiario }}</span>
              </div>

              <div class="d-flex align-center">
                <v-btn
                  x-small
                  color="red darken-2"
                  dark
                  outlined
                  class="rounded-pill"
                  @click="abrirPdfComprobante(asiento.id)"
                >
                  <v-icon left x-small>mdi-file-pdf-box</v-icon> PDF
                </v-btn>
              </div>
            </div>

            <!-- GLOSA -->
            <div class="px-4 py-2 text-caption grey lighten-5 border-b font-italic">
              Glosa: {{ asiento.glosa }}
            </div>

            <!-- TABLA DE LÍNEAS -->
            <v-simple-table dense>
              <thead>
                <tr>
                  <th style="width: 180px;">Código Cuenta</th>
                  <th>Denominación de la Cuenta</th>
                  <th>Centro Costo</th>
                  <th style="width: 140px;" class="text-right">Debe (Bs.)</th>
                  <th style="width: 140px;" class="text-right">Haber (Bs.)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="det in asiento.detalles" :key="'det-linea-' + det.id">
                  <td><code>{{ det.cuenta ? det.cuenta.codigo : '-' }}</code></td>
                  <td>{{ det.cuenta ? det.cuenta.nombre : '-' }}</td>
                  <td class="text-caption text-secondary">{{ det.centro_costo ? det.centro_costo.nombre : '-' }}</td>
                  <td class="text-right font-weight-bold">{{ Number(det.debe) > 0 ? formatearNumero(det.debe) : '-' }}</td>
                  <td class="text-right font-weight-bold">{{ Number(det.haber) > 0 ? formatearNumero(det.haber) : '-' }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="grey lighten-4 font-weight-bold">
                  <td colspan="3" class="text-right pr-3 text-caption">SUBTOTAL ASIENTO:</td>
                  <td class="text-right text-caption font-weight-bold indigo--text text--darken-3">
                    Bs. {{ formatearNumero(asiento.total_debe) }}
                  </td>
                  <td class="text-right text-caption font-weight-bold indigo--text text--darken-3">
                    Bs. {{ formatearNumero(asiento.total_haber) }}
                  </td>
                </tr>
              </tfoot>
            </v-simple-table>
          </v-card>
        </div>
      </v-tab-item>

      <!-- PESTAÑA 2: LIBRO MAYOR ANALÍTICO -->
      <v-tab-item>
        <!-- FILTROS MAYOR -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <v-row dense align="center">
            <v-col cols="12" sm="5">
              <v-autocomplete
                v-model="filtroMayor.plan_cuenta_id"
                :items="cuentasImputables"
                item-value="id"
                :item-text="item => item.codigo + ' - ' + item.nombre"
                label="Seleccione Cuenta Contable (Imputable) *"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-format-list-checks"
                @change="cargarLibroMayor"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12" sm="3">
              <v-text-field
                v-model="filtroMayor.fecha_inicio"
                label="Fecha Desde"
                type="date"
                outlined
                dense
                hide-details
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="2">
              <v-text-field
                v-model="filtroMayor.fecha_fin"
                label="Fecha Hasta"
                type="date"
                outlined
                dense
                hide-details
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="2">
              <v-btn
                color="indigo darken-2"
                dark
                block
                class="rounded-pill font-weight-medium"
                :loading="cargandoMayor"
                :disabled="!filtroMayor.plan_cuenta_id"
                @click="cargarLibroMayor"
              >
                <v-icon left>mdi-magnify</v-icon> Ver Mayor
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- DETALLE CUENTA Y SALDO INICIAL -->
        <div v-if="datosMayor && datosMayor.cuenta">
          <v-card rounded="lg" class="pa-4 mb-4 elevation-1">
            <div class="d-flex align-center justify-space-between flex-wrap">
              <div>
                <span class="text-caption text-secondary">CUENTA MAYORIZADA</span>
                <div class="text-h6 font-weight-bold indigo--text text--darken-3">
                  <code>{{ datosMayor.cuenta.codigo }}</code> - {{ datosMayor.cuenta.nombre }}
                </div>
                <div class="text-caption mt-1">
                  Naturaleza: <strong>{{ datosMayor.cuenta.naturaleza }}</strong> | Tipo: <strong>{{ datosMayor.cuenta.tipo_cuenta }}</strong>
                </div>
              </div>

              <div class="text-right mt-2 mt-sm-0">
                <span class="text-caption text-secondary">SALDO ANTERIOR / INICIAL:</span>
                <div class="text-h6 font-weight-bold">Bs. {{ formatearNumero(datosMayor.saldo_inicial) }}</div>
              </div>
            </div>
          </v-card>

          <!-- TABLA DE MOVIMIENTOS DEL MAYOR -->
          <v-card rounded="lg" class="elevation-1">
            <v-simple-table dense>
              <thead>
                <tr class="grey lighten-4">
                  <th style="width: 110px;">Fecha</th>
                  <th style="width: 140px;">N° Comprobante</th>
                  <th style="width: 80px;">Tipo</th>
                  <th>Glosa / Detalle</th>
                  <th style="width: 130px;" class="text-right">Debe (Bs.)</th>
                  <th style="width: 130px;" class="text-right">Haber (Bs.)</th>
                  <th style="width: 140px;" class="text-right">Saldo Acum. (Bs.)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="datosMayor.movimientos.length === 0">
                  <td colspan="7" class="text-center py-4 text-secondary">
                    No hubo movimientos contables en el período seleccionado
                  </td>
                </tr>
                <tr v-for="(mov, idx) in datosMayor.movimientos" :key="'mov-' + idx">
                  <td>{{ mov.fecha }}</td>
                  <td>
                    <span class="font-weight-medium indigo--text text--darken-2">
                      <code>{{ mov.nro_comprobante }}</code>
                    </span>
                  </td>
                  <td>
                    <v-chip x-small label :color="colorTipo(mov.tipo)" text-color="white">
                      {{ mov.tipo }}
                    </v-chip>
                  </td>
                  <td class="text-caption">{{ mov.glosa }}</td>
                  <td class="text-right font-weight-bold">{{ Number(mov.debe) > 0 ? formatearNumero(mov.debe) : '-' }}</td>
                  <td class="text-right font-weight-bold">{{ Number(mov.haber) > 0 ? formatearNumero(mov.haber) : '-' }}</td>
                  <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                    Bs. {{ formatearNumero(mov.saldo_acumulado) }}
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="indigo lighten-5 font-weight-bold">
                  <td colspan="4" class="text-right pr-3 text-subtitle-2">TOTALES Y SALDO FINAL:</td>
                  <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                    Bs. {{ formatearNumero(datosMayor.total_debe) }}
                  </td>
                  <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                    Bs. {{ formatearNumero(datosMayor.total_haber) }}
                  </td>
                  <td class="text-right font-weight-bold text-subtitle-2 green--text text--darken-3">
                    Bs. {{ formatearNumero(datosMayor.saldo_final) }}
                  </td>
                </tr>
              </tfoot>
            </v-simple-table>
          </v-card>
        </div>

        <div v-else class="text-center py-8 grey lighten-4 rounded-lg">
          <v-icon size="48" color="indigo lighten-3">mdi-folder-account-outline</v-icon>
          <div class="text-subtitle-1 text-secondary mt-2">
            Seleccione una cuenta contable imputable para generar su extracto y mayor analítico
          </div>
        </div>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>

<script>
export default {
  name: 'LibroMayorDiario',
  data() {
    const primerDiaMes = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substring(0, 10)
    const hoy = new Date().toISOString().substring(0, 10)

    return {
      tabActual: 0,
      cargandoDiario: false,
      cargandoMayor: false,

      filtroDiario: {
        fecha_inicio: primerDiaMes,
        fecha_fin: hoy,
        tipo: null,
      },
      asientosDiario: [],

      filtroMayor: {
        plan_cuenta_id: null,
        fecha_inicio: primerDiaMes,
        fecha_fin: hoy,
      },
      datosMayor: null,
      cuentasImputables: [],

      tiposDiario: [
        { text: 'Todos los tipos', value: null },
        { text: 'CI - Comprobantes de Ingreso', value: 'CI' },
        { text: 'CE - Comprobantes de Egreso', value: 'CE' },
        { text: 'CD - Comprobantes de Diario', value: 'CD' },
      ],
    }
  },

  computed: {
    totalDebeDiario() {
      const sum = this.asientosDiario.reduce((acc, curr) => acc + Number(curr.total_debe || 0), 0)
      return Math.round(sum * 100) / 100
    },
    totalHaberDiario() {
      const sum = this.asientosDiario.reduce((acc, curr) => acc + Number(curr.total_haber || 0), 0)
      return Math.round(sum * 100) / 100
    },
  },

  mounted() {
    this.cargarCuentasImputables()
    this.cargarLibroDiario()
  },

  methods: {
    cargarCuentasImputables() {
      axios
        .get('/api/contabilidad/plan-cuentas/imputables')
        .then(res => {
          if (res.data && res.data.data) {
            this.cuentasImputables = res.data.data
          }
        })
        .catch(() => {})
    },

    cargarLibroDiario() {
      this.cargandoDiario = true
      const params = {}
      if (this.filtroDiario.fecha_inicio) params.fecha_inicio = this.filtroDiario.fecha_inicio
      if (this.filtroDiario.fecha_fin) params.fecha_fin = this.filtroDiario.fecha_fin
      if (this.filtroDiario.tipo) params.tipo = this.filtroDiario.tipo

      axios
        .get('/api/contabilidad/reportes/libro-diario', { params })
        .then(res => {
          if (res.data && res.data.data) {
            this.asientosDiario = res.data.data
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al cargar el Libro Diario')
        })
        .finally(() => {
          this.cargandoDiario = false
        })
    },

    cargarLibroMayor() {
      if (!this.filtroMayor.plan_cuenta_id) return

      this.cargandoMayor = true
      const params = {
        plan_cuenta_id: this.filtroMayor.plan_cuenta_id,
      }
      if (this.filtroMayor.fecha_inicio) params.fecha_inicio = this.filtroMayor.fecha_inicio
      if (this.filtroMayor.fecha_fin) params.fecha_fin = this.filtroMayor.fecha_fin

      axios
        .get('/api/contabilidad/reportes/libro-mayor', { params })
        .then(res => {
          if (res.data && res.data.data) {
            this.datosMayor = res.data.data
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al consultar el Libro Mayor')
        })
        .finally(() => {
          this.cargandoMayor = false
        })
    },

    abrirPdfComprobante(id) {
      window.open('/api/contabilidad/comprobantes/' + id + '/pdf', '_blank')
    },

    imprimirReporte() {
      window.print()
    },

    colorTipo(tipo) {
      if (tipo === 'CI') return 'green darken-2'
      if (tipo === 'CE') return 'red darken-2'
      return 'blue darken-2'
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
