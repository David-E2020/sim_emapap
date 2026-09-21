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

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0 flex-wrap">
          <v-btn
            color="indigo darken-2"
            dark
            outlined
            small
            class="rounded-pill font-weight-medium"
            @click="imprimirEstado"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir Reporte
          </v-btn>
          <v-btn
            color="teal darken-2"
            dark
            small
            class="rounded-pill font-weight-medium elevation-1"
            @click="exportarCsv"
          >
            <v-icon left small>mdi-file-delimited-outline</v-icon> Exportar CSV
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BARRA DE FILTROS GLOBALES CON PERÍODO RÁPIDO -->
    <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
      <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
        <div class="d-flex align-center flex-wrap gap-2">
          <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
          <v-btn-toggle v-model="periodoPreset" mandatory dense color="indigo darken-3" @change="cambiarPeriodoRapido">
            <v-btn small value="gestion" class="text-capitalize">Gestión Completa</v-btn>
            <v-btn small value="semestre1" class="text-capitalize">1er Semestre</v-btn>
            <v-btn small value="semestre2" class="text-capitalize">2do Semestre</v-btn>
            <v-btn small value="corte_hoy" class="text-capitalize">Al Corte de Hoy</v-btn>
            <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
          </v-btn-toggle>
        </div>

        <div class="text-caption font-weight-medium text-secondary">
          Corte Fiscal: {{ filtros.fecha_inicio }} al {{ filtros.fecha_fin }}
        </div>
      </div>

      <v-divider class="mb-3"></v-divider>

      <v-row dense align="center">
        <v-col cols="12" sm="3" md="3">
          <v-select
            v-model="filtros.gestion"
            :items="gestiones"
            item-value="gestion"
            item-text="gestion"
            label="Gestión Fiscal"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-check"
            @change="alCambiarGestion"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="3" md="3">
          <v-text-field
            v-model="filtros.fecha_inicio"
            label="Fecha Inicio"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-start"
            @change="periodoPreset = 'personalizado'; cargarReporteActual()"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="3" md="3">
          <v-text-field
            v-model="filtros.fecha_fin"
            label="Fecha Fin (Corte)"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-end"
            @change="periodoPreset = 'personalizado'; cargarReporteActual()"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="3" md="3">
          <v-btn
            color="indigo darken-2"
            dark
            block
            class="rounded-pill font-weight-bold"
            :loading="cargando"
            @click="cargarReporteActual"
          >
            <v-icon left small>mdi-refresh</v-icon> Actualizar Estados
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- PESTAÑAS DIRECTAS SOBRE EL LIENZO (ESTÁNDAR ERP) -->
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
      <!-- ==================================================== -->
      <!-- TAB 0: BALANCE DE COMPROBACIÓN (SUMAS Y SALDOS)      -->
      <!-- ==================================================== -->
      <v-tab-item>
        <!-- TARJETAS DE MÉTRICAS / KPIS SAFCO -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Sumas Debe</div>
              <div class="text-h4 font-weight-black green--text text--darken-2 mt-1">
                Bs {{ formatearNumero(totalesSumasSaldos.total_debe) }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Cargos computados en comprobantes
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Sumas Haber</div>
              <div class="text-h4 font-weight-black green--text text--darken-2 mt-1">
                Bs {{ formatearNumero(totalesSumasSaldos.total_haber) }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Abonos computados en comprobantes
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Saldos (Deudor/Acreedor)</div>
              <div class="text-h4 font-weight-black indigo--text text--darken-3 mt-1">
                Bs {{ formatearNumero(totalesSumasSaldos.total_deudor) }}
              </div>
              <div class="text-caption text-indigo mt-1 font-weight-medium">
                Saldos contables de cierre
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card
              class="pa-3 text-center erp-card-elevated"
              rounded="lg"
              :class="cuadrePerfectoSumasSaldos ? 'bg-success-light' : 'bg-danger-light'"
            >
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Cuadre SAFCO (Ley 1178)</div>
              <div
                class="text-h4 font-weight-black mt-1"
                :class="cuadrePerfectoSumasSaldos ? 'success--text' : 'error--text'"
              >
                {{ cuadrePerfectoSumasSaldos ? 'CUADRADO' : 'DESCUADRE' }}
              </div>
              <div class="mt-1">
                <v-chip
                  x-small
                  :color="cuadrePerfectoSumasSaldos ? 'success' : 'error'"
                  text-color="white"
                  class="font-weight-bold"
                >
                  {{ cuadrePerfectoSumasSaldos ? 'PARTIDA DOBLE EXACTA' : 'REVISAR ASIENTOS CONTABLES' }}
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA DE 6 COLUMNAS CON TFOOT TOTALIZADOR -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-3 px-4 d-flex justify-space-between align-center">
            <div class="font-weight-bold text-subtitle-1">
              <v-icon left color="indigo darken-3">mdi-table</v-icon>
              Matriz de Sumas y Saldos (6 Columnas)
            </div>
            <v-chip small color="indigo darken-3" outlined class="font-weight-bold">
              {{ cuentasSumasSaldos.length }} cuentas con movimiento
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>

          <v-data-table
            :headers="headersSumasSaldos"
            :items="cuentasSumasSaldos"
            :loading="cargando"
            :items-per-page="25"
            class="erp-table"
            dense
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

            <!-- FILA TOTALIZADORA (TFOOT) -->
            <template v-slot:body.append>
              <tr class="grey lighten-3 font-weight-black" v-if="cuentasSumasSaldos.length > 0">
                <td colspan="2" class="text-right text-uppercase">TOTALES DEL BALANCE DE COMPROBACIÓN:</td>
                <td class="text-right green--text text--darken-3">Bs {{ formatearNumero(totalesSumasSaldos.total_debe) }}</td>
                <td class="text-right green--text text--darken-3">Bs {{ formatearNumero(totalesSumasSaldos.total_haber) }}</td>
                <td class="text-right indigo--text text--darken-3">Bs {{ formatearNumero(totalesSumasSaldos.total_deudor) }}</td>
                <td class="text-right purple--text text--darken-3">Bs {{ formatearNumero(totalesSumasSaldos.total_acreedor) }}</td>
              </tr>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ==================================================== -->
      <!-- TAB 1: BALANCE GENERAL CLASIFICADO                   -->
      <!-- ==================================================== -->
      <v-tab-item>
        <div v-if="balanceGeneralDatos">
          <!-- TARJETAS DE MÉTRICAS KPIS BALANCE GENERAL -->
          <v-row dense class="mb-4">
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Activo</div>
                <div class="text-h4 font-weight-black teal--text text--darken-3 mt-1">
                  Bs {{ formatearNumero(balanceGeneralDatos.total_activo) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  Bienes, derechos y recursos
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Pasivo</div>
                <div class="text-h4 font-weight-black red--text text--darken-3 mt-1">
                  Bs {{ formatearNumero(balanceGeneralDatos.total_pasivo) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  Obligaciones y deudas institucionales
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Patrimonio Neto + Resultado</div>
                <div class="text-h4 font-weight-black purple--text text--darken-3 mt-1">
                  Bs {{ formatearNumero(Number(balanceGeneralDatos.total_patrimonio) + Number(balanceGeneralDatos.resultado_gestion)) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  Capital y excedente acumulado
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card
                class="pa-3 text-center erp-card-elevated"
                rounded="lg"
                :class="balanceGeneralDatos.balance_cuadrado ? 'bg-success-light' : 'bg-danger-light'"
              >
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Ecuación Fundamental</div>
                <div
                  class="text-h4 font-weight-black mt-1"
                  :class="balanceGeneralDatos.balance_cuadrado ? 'success--text' : 'error--text'"
                >
                  {{ balanceGeneralDatos.balance_cuadrado ? 'EQUILIBRIO' : 'DESCUADRE' }}
                </div>
                <div class="mt-1">
                  <v-chip
                    x-small
                    :color="balanceGeneralDatos.balance_cuadrado ? 'success' : 'error'"
                    text-color="white"
                    class="font-weight-bold"
                  >
                    {{ balanceGeneralDatos.balance_cuadrado ? 'A = P + PATRIMONIO' : 'VERIFICAR CIERRE' }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- DETALLE CLASIFICADO ACTIVO / PASIVO Y PATRIMONIO -->
          <v-row dense>
            <!-- COLUMNA ACTIVO -->
            <v-col cols="12" md="6">
              <v-card rounded="lg" class="pa-4 erp-card-elevated">
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
                      <td class="text-right font-weight-bold">Bs {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="green lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL ACTIVO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 green--text text--darken-4">
                        Bs {{ formatearNumero(balanceGeneralDatos.total_activo) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>

            <!-- COLUMNA PASIVO Y PATRIMONIO -->
            <v-col cols="12" md="6">
              <!-- PASIVO -->
              <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
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
                      <td class="text-right font-weight-bold">Bs {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="red lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL PASIVO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 red--text text--darken-4">
                        Bs {{ formatearNumero(balanceGeneralDatos.total_pasivo) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>

              <!-- PATRIMONIO -->
              <v-card rounded="lg" class="pa-4 erp-card-elevated">
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
                      <td class="text-right font-weight-bold">Bs {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                    <tr>
                      <td><code>3.1.3</code></td>
                      <td class="font-weight-medium">Resultado del Ejercicio (Superávit/Déficit)</td>
                      <td class="text-right font-weight-bold indigo--text text--darken-3">
                        Bs {{ formatearNumero(balanceGeneralDatos.resultado_gestion) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="purple lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL PASIVO + PATRIMONIO:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 purple--text text--darken-4">
                        Bs {{ formatearNumero(Number(balanceGeneralDatos.total_pasivo) + Number(balanceGeneralDatos.total_patrimonio) + Number(balanceGeneralDatos.resultado_gestion)) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>
          </v-row>
        </div>
      </v-tab-item>

      <!-- ==================================================== -->
      <!-- TAB 2: ESTADO DE RENDIMIENTO (PÉRDIDAS Y GANANCIAS) -->
      <!-- ==================================================== -->
      <v-tab-item>
        <div v-if="estadoResultadosDatos">
          <!-- TARJETAS DE MÉTRICAS KPIS ESTADO DE RESULTADOS -->
          <v-row dense class="mb-4">
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Ingresos de Operación</div>
                <div class="text-h4 font-weight-black green--text text--darken-2 mt-1">
                  Bs {{ formatearNumero(estadoResultadosDatos.total_ingresos) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  Ventas de agua y servicios
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Gastos de Operación</div>
                <div class="text-h4 font-weight-black red--text text--darken-2 mt-1">
                  Bs {{ formatearNumero(estadoResultadosDatos.total_gastos) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  Costos operativos y administrativos
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Resultado Neto</div>
                <div
                  class="text-h4 font-weight-black mt-1"
                  :class="Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'green--text text--darken-3' : 'red--text text--darken-3'"
                >
                  Bs {{ formatearNumero(estadoResultadosDatos.resultado_neto) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  {{ estadoResultadosDatos.tipo_resultado || 'Ejercicio' }}
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
              <v-card
                class="pa-3 text-center erp-card-elevated"
                rounded="lg"
                :class="Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'bg-success-light' : 'bg-danger-light'"
              >
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Rendimiento Institucional</div>
                <div
                  class="text-h4 font-weight-black mt-1"
                  :class="Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'success--text' : 'error--text'"
                >
                  {{ Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'SUPERÁVIT' : 'DÉFICIT' }}
                </div>
                <div class="mt-1">
                  <v-chip
                    x-small
                    :color="Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'success' : 'error'"
                    text-color="white"
                    class="font-weight-bold"
                  >
                    {{ Number(estadoResultadosDatos.resultado_neto) >= 0 ? 'SALDO FAVORABLE' : 'EJERCICIO EN PÉRDIDA' }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <v-row dense>
            <!-- INGRESOS -->
            <v-col cols="12" md="6">
              <v-card rounded="lg" class="pa-4 erp-card-elevated">
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
                      <td class="text-right font-weight-bold">Bs {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="teal lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL INGRESOS:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 teal--text text--darken-4">
                        Bs {{ formatearNumero(estadoResultadosDatos.total_ingresos) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>

            <!-- GASTOS -->
            <v-col cols="12" md="6">
              <v-card rounded="lg" class="pa-4 erp-card-elevated">
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
                      <td class="text-right font-weight-bold">Bs {{ formatearNumero(c.saldo) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="red lighten-5 font-weight-bold">
                      <td colspan="2" class="text-right pr-3 font-weight-bold">TOTAL GASTOS:</td>
                      <td class="text-right font-weight-bold text-subtitle-2 red--text text--darken-4">
                        Bs {{ formatearNumero(estadoResultadosDatos.total_gastos) }}
                      </td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-card>
            </v-col>
          </v-row>
        </div>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EstadosFinancieros',
  data() {
    const anioActual = new Date().getFullYear();
    const primerDiaAnio = anioActual + '-01-01';
    const hoy = new Date().toISOString().substring(0, 10);

    return {
      tabActual: 0,
      cargando: false,
      periodoPreset: 'gestion',

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

      // DATOS TAB 0
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

      // DATOS TAB 1
      balanceGeneralDatos: null,

      // DATOS TAB 2
      estadoResultadosDatos: null,
    };
  },

  computed: {
    cuadrePerfectoSumasSaldos() {
      const debe = Math.round(Number(this.totalesSumasSaldos.total_debe) * 100);
      const haber = Math.round(Number(this.totalesSumasSaldos.total_haber) * 100);
      const deudor = Math.round(Number(this.totalesSumasSaldos.total_deudor) * 100);
      const acreedor = Math.round(Number(this.totalesSumasSaldos.total_acreedor) * 100);
      return debe === haber && deudor === acreedor;
    },
  },

  mounted() {
    this.cargarGestiones();
    this.cargarReporteActual();
  },

  methods: {
    cambiarPeriodoRapido(val) {
      const g = this.filtros.gestion || new Date().getFullYear();
      const hoy = new Date().toISOString().substring(0, 10);

      if (val === 'gestion') {
        this.filtros.fecha_inicio = `${g}-01-01`;
        this.filtros.fecha_fin = `${g}-12-31`;
      } else if (val === 'semestre1') {
        this.filtros.fecha_inicio = `${g}-01-01`;
        this.filtros.fecha_fin = `${g}-06-30`;
      } else if (val === 'semestre2') {
        this.filtros.fecha_inicio = `${g}-07-01`;
        this.filtros.fecha_fin = `${g}-12-31`;
      } else if (val === 'corte_hoy') {
        this.filtros.fecha_inicio = `${g}-01-01`;
        this.filtros.fecha_fin = hoy;
      }

      if (val !== 'personalizado') {
        this.cargarReporteActual();
      }
    },

    alCambiarGestion() {
      this.cambiarPeriodoRapido(this.periodoPreset);
    },

    cargarGestiones() {
      axios
        .get('/api/contabilidad/gestiones')
        .then(res => {
          if (res.data && res.data.data && res.data.data.length > 0) {
            this.gestiones = res.data.data;
          }
        })
        .catch(() => {});
    },

    alCambiarTab() {
      this.cargarReporteActual();
    },

    cargarReporteActual() {
      if (this.tabActual === 0) {
        this.cargarSumasSaldos();
      } else if (this.tabActual === 1) {
        this.cargarBalanceGeneral();
      } else if (this.tabActual === 2) {
        this.cargarEstadoResultados();
      }
    },

    cargarSumasSaldos() {
      this.cargando = true;
      axios
        .get('/api/contabilidad/reportes/balance-comprobacion', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.cuentasSumasSaldos = res.data.data.cuentas || [];
            this.totalesSumasSaldos = res.data.data.totales || {
              total_debe: 0,
              total_haber: 0,
              total_deudor: 0,
              total_acreedor: 0,
            };
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Balance de Comprobación');
        })
        .finally(() => {
          this.cargando = false;
        });
    },

    cargarBalanceGeneral() {
      this.cargando = true;
      axios
        .get('/api/contabilidad/reportes/balance-general', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.balanceGeneralDatos = res.data.data;
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Balance General');
        })
        .finally(() => {
          this.cargando = false;
        });
    },

    cargarEstadoResultados() {
      this.cargando = true;
      axios
        .get('/api/contabilidad/reportes/estado-resultados', { params: this.filtros })
        .then(res => {
          if (res.data && res.data.data) {
            this.estadoResultadosDatos = res.data.data;
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al generar Estado de Rendimiento');
        })
        .finally(() => {
          this.cargando = false;
        });
    },

    imprimirEstado() {
      window.print();
    },

    exportarCsv() {
      if (this.tabActual === 0 && this.cuentasSumasSaldos.length > 0) {
        let csvContent = 'data:text/csv;charset=utf-8,Codigo,Nombre,Suma Debe,Suma Haber,Saldo Deudor,Saldo Acreedor\n';
        this.cuentasSumasSaldos.forEach(row => {
          csvContent += `"${row.codigo}","${row.nombre}",${row.suma_debe},${row.suma_haber},${row.saldo_deudor},${row.saldo_acreedor}\n`;
        });
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `balance_comprobacion_${this.filtros.gestion}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else {
        this.notificar('info', 'Exportación disponible para Balance de Sumas y Saldos');
      }
    },

    formatearNumero(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00';
      return Number(val).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    notificar(tipo, mensaje) {
      if (window.iziToast) {
        if (tipo === 'success') {
          window.iziToast.success({ title: 'Éxito', message: mensaje, position: 'topRight' });
        } else {
          window.iziToast.error({ title: 'Error', message: mensaje, position: 'topRight' });
        }
      } else {
        alert(mensaje);
      }
    },
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  .erp-card-elevated, .erp-card-elevated * {
    visibility: visible;
  }
}
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.gap-2 {
  gap: 8px;
}
</style>
