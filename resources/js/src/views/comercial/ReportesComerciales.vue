<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-chart-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Reportes Comerciales y Cuadre de Caja</h2>
            <span class="text-caption text-secondary">
              Recaudación consolidada por día, semana, mes o rango de fechas, arqueos y morosidad institucional
            </span>
          </div>
        </div>
      </div>
    </v-card>

    <v-tabs v-model="tabActual" color="teal darken-3" class="mb-4">
      <v-tab class="font-weight-bold"><v-icon left small>mdi-cash-register</v-icon> Recaudación y Cuadre de Caja</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-chart-pie</v-icon> Resumen por Zonas (Ciclo)</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-pipe-disconnected</v-icon> Nómina de Cortes Masivos</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-account-alert</v-icon> Cartera Vencida y Morosidad</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-chart-bell-curve</v-icon> Balance de Consumo Mensual</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: RECAUDACIÓN Y CUADRE CONSOLIDADO DE CAJA -->
      <v-tab-item>
        <!-- FILTROS AVANZADOS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="d-flex align-center flex-wrap gap-2">
              <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
              <v-btn-toggle v-model="filtros.periodo_tipo" mandatory dense color="teal darken-3" @change="cambiarPeriodoRapido">
                <v-btn small value="hoy" class="text-capitalize">Hoy</v-btn>
                <v-btn small value="semana" class="text-capitalize">Esta Semana</v-btn>
                <v-btn small value="mes" class="text-capitalize">Este Mes</v-btn>
                <v-btn small value="mes_anterior" class="text-capitalize">Mes Anterior</v-btn>
                <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
              </v-btn-toggle>
            </div>

            <!-- BOTONES DE EXPORTACIÓN -->
            <div class="d-flex align-center gap-2">
              <v-btn
                color="teal darken-2"
                dark
                small
                class="rounded-pill font-weight-medium"
                :loading="generandoPdf"
                @click="verPdfConsolidado"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Planilla Oficial PDF
              </v-btn>
              <v-btn
                color="indigo darken-1"
                dark
                small
                outlined
                class="rounded-pill font-weight-medium"
                @click="exportarCsv"
              >
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel/CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <!-- FECHAS -->
            <v-col cols="12" sm="3" md="2">
              <v-text-field
                v-model="filtros.fecha_inicio"
                label="Fecha Desde"
                type="date"
                dense
                outlined
                hide-details
                @change="onFechaChange"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" md="2">
              <v-text-field
                v-model="filtros.fecha_fin"
                label="Fecha Hasta"
                type="date"
                dense
                outlined
                hide-details
                @change="onFechaChange"
              ></v-text-field>
            </v-col>

            <!-- SELECTOR DE CAJA / PUNTO DE VENTA -->
            <v-col cols="12" sm="3" md="3">
              <v-select
                v-model="filtros.id_punto_venta"
                :items="cajasLista"
                item-value="id"
                item-text="nombre"
                label="Caja / Ventanilla"
                dense
                outlined
                hide-details
                prepend-inner-icon="mdi-store"
                clearable
                @change="cargarRecaudacionConsolidada"
              ></v-select>
            </v-col>

            <!-- SELECTOR DE CAJERO -->
            <v-col cols="12" sm="3" md="3">
              <v-select
                v-model="filtros.id_cajero"
                :items="cajerosLista"
                item-value="id"
                item-text="name"
                label="Cajero(a)"
                dense
                outlined
                hide-details
                prepend-inner-icon="mdi-account"
                clearable
                @change="cargarRecaudacionConsolidada"
              ></v-select>
            </v-col>

            <!-- BOTÓN FILTRAR -->
            <v-col cols="12" sm="12" md="2" class="d-flex justify-end">
              <v-btn
                color="teal darken-3"
                dark
                class="rounded-pill w-100 font-weight-bold"
                :loading="cargandoRecaudacion"
                @click="cargarRecaudacionConsolidada"
              >
                <v-icon left small>mdi-filter-check</v-icon> Consultar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS DE MÉTRICAS FINANCIERAS (KPIS) -->
        <div v-if="datosConsolidados && datosConsolidados.metricas">
          <v-row dense class="mb-4">
            <!-- TOTAL RECAUDADO -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Total General Recaudado</div>
                <div class="text-h4 font-weight-black success--text mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_recaudado || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  {{ datosConsolidados.metricas.total_transacciones || 0 }} transacciones realizadas
                </div>
              </v-card>
            </v-col>

            <!-- EFECTIVO FÍSICO GAVETA -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Efectivo Físico en Gaveta</div>
                <div class="text-h4 font-weight-black teal--text text--darken-3 mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_efectivo || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-teal text--darken-2 mt-1 font-weight-medium">
                  Cobros en ventanilla
                </div>
              </v-card>
            </v-col>

            <!-- QR / BANCO -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Cobros QR / Banco Unión</div>
                <div class="text-h4 font-weight-black primary--text mt-1">
                  Bs {{ parseFloat(datosConsolidados.metricas.total_qr_banco || 0).toFixed(2) }}
                </div>
                <div class="text-caption text-primary mt-1 font-weight-medium">
                  Directo a cuenta fiscal
                </div>
              </v-card>
            </v-col>

            <!-- CUADRATURA Y ARQUEO -->
            <v-col cols="12" sm="6" md="3">
              <v-card
                class="pa-3 text-center erp-card-elevated"
                rounded="lg"
                :class="diferenciaArqueo === 0 ? 'bg-success-light' : (diferenciaArqueo > 0 ? 'bg-warning-light' : 'bg-danger-light')"
              >
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Diferencia de Arqueo</div>
                <div
                  class="text-h4 font-weight-black mt-1"
                  :class="diferenciaArqueo === 0 ? 'success--text' : (diferenciaArqueo > 0 ? 'warning--text text--darken-3' : 'error--text')"
                >
                  Bs {{ diferenciaArqueo.toFixed(2) }}
                </div>
                <div class="mt-1">
                  <v-chip
                    x-small
                    :color="diferenciaArqueo === 0 ? 'success' : (diferenciaArqueo > 0 ? 'warning' : 'error')"
                    text-color="white"
                    class="font-weight-bold"
                  >
                    {{ diferenciaArqueo === 0 ? 'CAJAS CUADRADAS' : (diferenciaArqueo > 0 ? 'SOBRANTE' : 'FALTANTE') }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- TABLAS DE DETALLE EN SUB-TABS -->
          <v-card rounded="lg" class="erp-card-elevated">
            <v-tabs v-model="subTabActual" color="teal darken-3" dense background-color="grey lighten-4">
              <v-tab class="font-weight-bold"><v-icon left small>mdi-format-list-bulleted-type</v-icon> 1. Rubros Contables</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-store-outline</v-icon> 2. Por Ventanilla / Caja</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-account-tie</v-icon> 3. Por Cajero(a)</v-tab>
              <v-tab class="font-weight-bold"><v-icon left small>mdi-history</v-icon> 4. Turnos y Arqueos del Período</v-tab>
            </v-tabs>

            <v-divider></v-divider>

            <v-tabs-items v-model="subTabActual" class="pa-4">
              <!-- SUB-TAB 1: DISTRIBUCIÓN POR RUBROS CONTABLES -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Rubro / Partida de Ingreso</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo (Bs)</th>
                        <th class="text-right font-weight-bold">QR / Banco (Bs)</th>
                        <th class="text-right font-weight-bold">Subtotal (Bs)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="r in datosConsolidados.por_rubro" :key="r.nombre">
                        <td class="font-weight-medium">{{ r.nombre }}</td>
                        <td class="text-center">{{ r.cantidad }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(r.efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold text-primary">Bs {{ parseFloat(r.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(r.total).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr class="grey lighten-3 font-weight-black">
                        <td>TOTAL GENERAL RECAUDADO</td>
                        <td class="text-center">{{ datosConsolidados.metricas.total_transacciones }}</td>
                        <td class="text-right">Bs {{ parseFloat(datosConsolidados.metricas.total_efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(datosConsolidados.metricas.total_qr_banco).toFixed(2) }}</td>
                        <td class="text-right success--text">Bs {{ parseFloat(datosConsolidados.metricas.total_recaudado).toFixed(2) }}</td>
                      </tr>
                    </tfoot>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 2: POR VENTANILLA / CAJA -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Caja / Ventanilla Física</th>
                        <th class="text-center font-weight-bold">Punto Venta SIAT</th>
                        <th class="text-center font-weight-bold">Turnos</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo (Bs)</th>
                        <th class="text-right font-weight-bold">QR / Banco (Bs)</th>
                        <th class="text-right font-weight-bold">Total Cobrado (Bs)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="c in datosConsolidados.por_caja" :key="c.id">
                        <td class="font-weight-bold">{{ c.caja }}</td>
                        <td class="text-center">Punto {{ c.codigo_punto_venta }}</td>
                        <td class="text-center">{{ c.turnos }}</td>
                        <td class="text-center">{{ c.transacciones }}</td>
                        <td class="text-right">Bs {{ parseFloat(c.efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(c.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(c.total).toFixed(2) }}</td>
                      </tr>
                      <tr v-if="datosConsolidados.por_caja.length === 0">
                        <td colspan="7" class="text-center py-4 text-secondary">No se registraron cobros en el período seleccionado</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 3: POR CAJERO -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">Cajero(a) / Operador</th>
                        <th class="text-center font-weight-bold">Turnos Operados</th>
                        <th class="text-center font-weight-bold">Transacciones</th>
                        <th class="text-right font-weight-bold">Efectivo Cobrado</th>
                        <th class="text-right font-weight-bold">QR / Banco</th>
                        <th class="text-right font-weight-bold">Total Recaudado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="cj in datosConsolidados.por_cajero" :key="cj.id_cajero">
                        <td class="font-weight-bold">{{ cj.cajero }}</td>
                        <td class="text-center">{{ cj.turnos }}</td>
                        <td class="text-center">{{ cj.transacciones }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(cj.efectivo).toFixed(2) }}</td>
                        <td class="text-right text-primary">Bs {{ parseFloat(cj.qr).toFixed(2) }}</td>
                        <td class="text-right font-weight-black success--text">Bs {{ parseFloat(cj.total).toFixed(2) }}</td>
                      </tr>
                      <tr v-if="datosConsolidados.por_cajero.length === 0">
                        <td colspan="6" class="text-center py-4 text-secondary">Sin movimientos para los cajeros seleccionados</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <!-- SUB-TAB 4: LISTADO DE TURNOS Y ARQUEOS -->
              <v-tab-item>
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th class="font-weight-bold">N° Turno</th>
                        <th class="font-weight-bold">Ventanilla</th>
                        <th class="font-weight-bold">Cajero</th>
                        <th class="font-weight-bold">Apertura</th>
                        <th class="font-weight-bold">Cierre</th>
                        <th class="text-right font-weight-bold">Fondo</th>
                        <th class="text-right font-weight-bold">Ventas Ef.</th>
                        <th class="text-right font-weight-bold">Esperado</th>
                        <th class="text-right font-weight-bold">Declarado</th>
                        <th class="text-right font-weight-bold">Diferencia</th>
                        <th class="text-center font-weight-bold">Estado</th>
                        <th class="text-center font-weight-bold">Planilla</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="t in datosConsolidados.turnos" :key="t.id">
                        <td class="font-weight-bold">{{ t.numero_sesion }}</td>
                        <td>{{ t.caja_nombre }}</td>
                        <td>{{ t.cajero_nombre }}</td>
                        <td class="text-caption">{{ t.fecha_apertura }}</td>
                        <td class="text-caption">{{ t.fecha_cierre || 'EN CURSO' }}</td>
                        <td class="text-right">Bs {{ parseFloat(t.monto_apertura).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_ventas_efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_esperado_efectivo).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(t.monto_cierre_declarado).toFixed(2) }}</td>
                        <td class="text-right font-weight-bold" :class="t.diferencia < 0 ? 'error--text' : (t.diferencia > 0 ? 'warning--text text--darken-3' : 'success--text')">
                          Bs {{ parseFloat(t.diferencia).toFixed(2) }}
                        </td>
                        <td class="text-center">
                          <v-chip
                            x-small
                            :color="t.estado === 'CERRADA' ? (t.diferencia == 0 ? 'success' : 'warning') : 'info'"
                            text-color="white"
                            class="font-weight-bold"
                          >
                            {{ t.estado === 'CERRADA' ? (t.diferencia == 0 ? 'CUADRADO' : (t.diferencia > 0 ? 'SOBRANTE' : 'FALTANTE')) : 'ABIERTA' }}
                          </v-chip>
                        </td>
                        <td class="text-center">
                          <v-btn icon x-small color="teal darken-2" @click="verPdfArqueoIndividual(t.id)">
                            <v-icon small>mdi-file-document-outline</v-icon>
                          </v-btn>
                        </td>
                      </tr>
                      <tr v-if="datosConsolidados.turnos.length === 0">
                        <td colspan="12" class="text-center py-4 text-secondary">No se registraron turnos de caja en este período</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>
            </v-tabs-items>
          </v-card>
        </div>
      </v-tab-item>

      <!-- PESTAÑA 2: RESUMEN DE FACTURACIÓN Y OPERACIONES POR ZONAS (Ciclo) -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap gap-2">
            <div class="d-flex align-center flex-wrap gap-3">
              <v-select
                v-model="filtroPeriodoZonas"
                :items="periodosLista"
                item-text="periodo"
                item-value="id"
                label="Período de Facturación *"
                prepend-inner-icon="mdi-calendar-sync"
                dense
                outlined
                hide-details
                style="min-width: 220px;"
                @change="cargarResumenZonas"
              ></v-select>

              <v-chip v-if="datosResumenZonas && datosResumenZonas.periodo" small color="teal darken-3" dark class="font-weight-bold">
                Estado: {{ datosResumenZonas.periodo.estado }}
              </v-chip>
            </div>

            <div class="d-flex align-center gap-2">
              <v-btn
                color="teal darken-2"
                dark
                small
                class="rounded-pill font-weight-medium"
                :disabled="!filtroPeriodoZonas"
                @click="verPdfResumenZonas"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Planilla Oficial PDF
              </v-btn>
              <v-btn
                color="indigo darken-1"
                dark
                small
                outlined
                class="rounded-pill font-weight-medium"
                :disabled="!filtroPeriodoZonas"
                :loading="exportandoExcel"
                @click="exportarExcelResumenZonas"
              >
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel
              </v-btn>
            </div>
          </div>
        </v-card>

        <!-- KPI Resumen Zonas -->
        <v-row dense class="mb-4" v-if="datosResumenZonas && datosResumenZonas.totales">
          <v-col cols="12" sm="6" md="2">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">TOTAL ABONADOS</div>
              <div class="text-h5 font-weight-black primary--text mt-1">
                {{ Number(datosResumenZonas.totales.abonados).toLocaleString() }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="2">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">VOLUMEN AGUA</div>
              <div class="text-h5 font-weight-black info--text mt-1">
                {{ Number(datosResumenZonas.totales.consumo_m3).toLocaleString() }} m³
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">TOTAL FACTURADO MES</div>
              <div class="text-h5 font-weight-black green--text text--darken-2 mt-1">
                Bs {{ Number(datosResumenZonas.totales.facturado_bs).toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">RECAUDADO VENTANILLA</div>
              <div class="text-h5 font-weight-black primary--text mt-1">
                Bs {{ Number(datosResumenZonas.totales.cobrado_bs).toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="2">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">% COBRO EFECTIVO</div>
              <div class="text-h5 font-weight-black teal--text text--darken-2 mt-1">
                {{ datosResumenZonas.totales.facturado_bs > 0 ? ((datosResumenZonas.totales.cobrado_bs / datosResumenZonas.totales.facturado_bs) * 100).toFixed(1) : 0 }}%
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Tabla Detalle por Zona -->
        <v-card rounded="lg" class="erp-card-elevated mb-4">
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr class="grey lighten-4">
                  <th class="font-weight-bold text-center">N°</th>
                  <th class="font-weight-bold text-center">Código</th>
                  <th class="font-weight-bold">Zona Comercial</th>
                  <th class="text-right font-weight-bold">Abonados</th>
                  <th class="text-right font-weight-bold">Consumo (m³)</th>
                  <th class="text-right font-weight-bold">Agua (Bs)</th>
                  <th class="text-right font-weight-bold">Alcantarillado (Bs)</th>
                  <th class="text-right font-weight-bold">Otros (Bs)</th>
                  <th class="text-right font-weight-bold">Ley 1886 (Bs)</th>
                  <th class="text-right font-weight-bold">Total Facturado (Bs)</th>
                  <th class="text-right font-weight-bold">Recaudado (Bs)</th>
                  <th class="text-center font-weight-bold">% Cobro</th>
                </tr>
              </thead>
              <tbody v-if="datosResumenZonas && datosResumenZonas.filas">
                <tr v-for="(f, idx) in datosResumenZonas.filas" :key="idx">
                  <td class="text-center">{{ idx + 1 }}</td>
                  <td class="text-center font-weight-bold">{{ f.zona_codigo }}</td>
                  <td class="font-weight-medium">{{ f.zona_nombre }}</td>
                  <td class="text-right">{{ Number(f.total_abonados).toLocaleString() }}</td>
                  <td class="text-right">{{ Number(f.consumo_total_m3).toLocaleString() }}</td>
                  <td class="text-right">Bs {{ Number(f.total_agua_bs).toFixed(2) }}</td>
                  <td class="text-right">Bs {{ Number(f.total_alcantarillado_bs).toFixed(2) }}</td>
                  <td class="text-right">Bs {{ Number(f.total_otros_cargos_bs).toFixed(2) }}</td>
                  <td class="text-right error--text">-Bs {{ Number(f.total_ley1886_bs).toFixed(2) }}</td>
                  <td class="text-right font-weight-bold green--text text--darken-2">Bs {{ Number(f.total_facturado_bs).toFixed(2) }}</td>
                  <td class="text-right font-weight-bold primary--text">Bs {{ Number(f.total_cobrado_bs).toFixed(2) }}</td>
                  <td class="text-center font-weight-bold">
                    {{ Number(f.total_facturado_bs) > 0 ? ((Number(f.total_cobrado_bs) / Number(f.total_facturado_bs)) * 100).toFixed(1) : 0 }}%
                  </td>
                </tr>
                <tr v-if="datosResumenZonas.filas.length === 0">
                  <td colspan="12" class="text-center py-4 text-secondary">No existen datos de lecturación para este período.</td>
                </tr>
              </tbody>
              <tfoot v-if="datosResumenZonas && datosResumenZonas.totales" class="grey lighten-3">
                <tr class="font-weight-black">
                  <td colspan="3" class="text-right">TOTALES GENERALES:</td>
                  <td class="text-right">{{ Number(datosResumenZonas.totales.abonados).toLocaleString() }}</td>
                  <td class="text-right">{{ Number(datosResumenZonas.totales.consumo_m3).toLocaleString() }}</td>
                  <td class="text-right">Bs {{ Number(datosResumenZonas.totales.agua_bs).toFixed(2) }}</td>
                  <td class="text-right">Bs {{ Number(datosResumenZonas.totales.alcantarillado_bs).toFixed(2) }}</td>
                  <td class="text-right">Bs {{ Number(datosResumenZonas.totales.otros_cargos_bs).toFixed(2) }}</td>
                  <td class="text-right error--text">-Bs {{ Number(datosResumenZonas.totales.ley1886_bs).toFixed(2) }}</td>
                  <td class="text-right green--text text--darken-2">Bs {{ Number(datosResumenZonas.totales.facturado_bs).toFixed(2) }}</td>
                  <td class="text-right primary--text">Bs {{ Number(datosResumenZonas.totales.cobrado_bs).toFixed(2) }}</td>
                  <td class="text-center">
                    {{ datosResumenZonas.totales.facturado_bs > 0 ? ((datosResumenZonas.totales.cobrado_bs / datosResumenZonas.totales.facturado_bs) * 100).toFixed(1) : 0 }}%
                  </td>
                </tr>
              </tfoot>
            </template>
          </v-simple-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 3: NÓMINA DE CORTES MASIVOS POR ZONA -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap gap-2">
            <div class="d-flex align-center flex-wrap gap-3">
              <v-select
                v-model="filtroZonaCortes"
                :items="zonasLista"
                item-text="nombre"
                item-value="id"
                label="Filtrar por Zona Comercial"
                dense
                outlined
                hide-details
                clearable
                prepend-inner-icon="mdi-map-marker"
                style="min-width: 250px;"
                @change="cargarNominaCortes"
              ></v-select>

              <v-select
                v-model="filtroMesesMoraCortes"
                :items="[2, 3, 4, 5, 6]"
                label="Meses de Mora Mínimos"
                dense
                outlined
                hide-details
                style="width: 170px;"
                @change="cargarNominaCortes"
              >
                <template v-slot:selection="{ item }">&ge; {{ item }} Meses Mora</template>
                <template v-slot:item="{ item }">&ge; {{ item }} Meses Pendientes</template>
              </v-select>
            </div>

            <div class="d-flex align-center gap-2">
              <v-btn
                color="red darken-2"
                dark
                small
                class="rounded-pill font-weight-medium"
                :loading="cargandoNominaCortes"
                @click="verPdfNominaCortes"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Planilla Cuadrilla PDF
              </v-btn>
              <v-btn
                color="indigo darken-1"
                dark
                small
                outlined
                class="rounded-pill font-weight-medium"
                :loading="exportandoExcel"
                @click="exportarExcelNominaCortes"
              >
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel
              </v-btn>
            </div>
          </div>
        </v-card>

        <!-- KPI Nómina Cortes -->
        <v-row dense class="mb-4" v-if="datosNominaCortes">
          <v-col cols="12" sm="4">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">ABONADOS SUJETOS A CORTE</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                {{ datosNominaCortes.total_deudores }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="4">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">DEUDA TOTAL EN RIESGO</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                Bs {{ Number(datosNominaCortes.total_deuda).toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="4">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">CRITERIO DE INTERVENCIÓN</div>
              <div class="text-h4 font-weight-black deep-orange--text mt-1">
                &ge; {{ filtroMesesMoraCortes }} Facturas Impagas
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Tabla Detalle Abonados a Cortar -->
        <v-card rounded="lg" class="erp-card-elevated mb-4">
          <v-card-title class="py-2 px-4 grey lighten-4 d-flex justify-space-between align-center flex-wrap gap-2">
            <span class="text-subtitle-1 font-weight-bold">
              <v-icon left small color="error">mdi-account-cancel</v-icon>
              Listado de Abonados para Notificación o Corte
            </span>
            <v-text-field
              v-model="buscarCorte"
              prepend-inner-icon="mdi-magnify"
              label="Buscar por código, titular, calle o medidor..."
              dense
              outlined
              hide-details
              clearable
              style="max-width: 320px;"
            ></v-text-field>
          </v-card-title>

          <v-data-table
            :headers="headersNominaCortes"
            :items="listaNominaCortes"
            :search="buscarCorte"
            :loading="cargandoNominaCortes"
            :items-per-page="50"
            :footer-props="{
              'items-per-page-options': [25, 50, 100, 250],
              'items-per-page-text': 'Filas por página:'
            }"
            dense
            no-data-text="No existen abonados en mora que cumplan este criterio de corte."
            no-results-text="No se encontraron coincidencias para la búsqueda."
          >
            <template v-slot:item.item_index="{ item }">
              <span class="text-caption text-secondary">{{ item.item_index }}</span>
            </template>
            <template v-slot:item.codigo="{ item }">
              <span class="font-weight-bold">{{ item.codigo }}</span>
            </template>
            <template v-slot:item.nombre_completo="{ item }">
              <span class="font-weight-medium">{{ item.nombre_completo }}</span>
            </template>
            <template v-slot:item.meses_mora="{ item }">
              <v-chip
                x-small
                :color="item.meses_mora >= 3 ? 'error' : 'warning'"
                text-color="white"
                class="font-weight-bold"
              >
                {{ item.meses_mora }} meses
              </v-chip>
            </template>
            <template v-slot:item.saldo_deuda="{ item }">
              <span class="font-weight-bold error--text">
                Bs {{ item.saldo_deuda_num.toFixed(2) }}
              </span>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 4: CARTERA VENCIDA Y MOROSIDAD -->
      <v-tab-item>
        <v-row dense class="mb-4" v-if="datosMora">
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">ABONADOS EN MORA</div>
              <div class="text-h4 font-weight-black error--text mt-1">{{ Number(datosMora.metricas.total_abonados_mora).toLocaleString() }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">DEUDA TOTAL ACUMULADA</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                Bs {{ parseFloat(datosMora.metricas.total_deuda_acumulada).toFixed(2) }}
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">MORA 2 MESES (EN RIESGO)</div>
              <div class="text-h4 font-weight-black warning--text mt-1">{{ Number(datosMora.metricas.mora_2_meses).toLocaleString() }}</div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold">MORA &ge; 3 MESES (CORTADO)</div>
              <div class="text-h4 font-weight-black deep-orange--text mt-1">{{ Number(datosMora.metricas.mora_3_o_mas_meses).toLocaleString() }}</div>
            </v-card>
          </v-col>
        </v-row>

        <v-row dense v-if="datosMora">
          <!-- RESUMEN DE CARTERA VENCIDA POR ZONAS -->
          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated h-100">
              <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
                <v-icon left small color="primary">mdi-map-marker-multiple</v-icon>
                Cartera Vencida por Zonas Comerciales
              </v-card-title>
              <v-simple-table dense>
                <template v-slot:default>
                  <thead>
                    <tr class="grey lighten-4">
                      <th class="font-weight-bold">Zona Comercial</th>
                      <th class="text-center font-weight-bold">Abonados Mora</th>
                      <th class="text-right font-weight-bold">Deuda Acumulada</th>
                      <th class="text-center font-weight-bold">% Cartera</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="z in datosMora.deuda_por_zona" :key="z.zona">
                      <td class="font-weight-medium">{{ z.zona }}</td>
                      <td class="text-center font-weight-bold">{{ z.abonados_mora }}</td>
                      <td class="text-right font-weight-bold error--text">Bs {{ parseFloat(z.total_deuda).toFixed(2) }}</td>
                      <td class="text-center">
                        <span class="text-caption font-weight-bold">
                          {{ datosMora.metricas.total_deuda_acumulada > 0 ? ((z.total_deuda / datosMora.metricas.total_deuda_acumulada) * 100).toFixed(1) : 0 }}%
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card>
          </v-col>

          <!-- TOP 10 MAYORES DEUDORES -->
          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated h-100">
              <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
                <v-icon left small color="error">mdi-alert-octagon</v-icon>
                Top 10 Mayores Deudores Institucionales
              </v-card-title>
              <v-simple-table dense>
                <template v-slot:default>
                  <thead>
                    <tr class="grey lighten-4">
                      <th>Código</th>
                      <th>Titular</th>
                      <th>Zona</th>
                      <th class="text-center">Meses</th>
                      <th class="text-right">Monto Deuda</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="d in datosMora.top_deudores" :key="d.id">
                      <td class="font-weight-bold">{{ d.codigo }}</td>
                      <td class="font-weight-medium text-truncate" style="max-width: 160px;">{{ d.nombre_completo }}</td>
                      <td class="text-caption">{{ d.zona ? d.zona.nombre : '-' }}</td>
                      <td class="text-center">
                        <v-chip x-small color="error" text-color="white" class="font-weight-bold">{{ d.meses_mora }} m</v-chip>
                      </td>
                      <td class="text-right font-weight-black error--text">Bs {{ parseFloat(d.saldo_deuda).toFixed(2) }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>

      <!-- PESTAÑA 3: BALANCE DE CONSUMO MENSUAL -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
            Balance Hídrico y Recaudación por Periodo
          </v-card-title>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr class="grey lighten-4">
                  <th>Periodo</th>
                  <th class="text-center">Abonados Medidos</th>
                  <th class="text-right">Volumen Total (m³)</th>
                  <th class="text-right">Monto Facturado</th>
                  <th class="text-right">Monto Cobrado</th>
                  <th class="text-center">% Recaudación</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in periodosBalance" :key="p.periodo">
                  <td class="font-weight-bold">{{ p.periodo }}</td>
                  <td class="text-center">{{ p.abonados_medidos }}</td>
                  <td class="text-right font-weight-bold primary--text">{{ parseFloat(p.volumen_total_m3).toFixed(1) }} m³</td>
                  <td class="text-right">Bs {{ parseFloat(p.monto_facturado_bs).toFixed(2) }}</td>
                  <td class="text-right font-weight-bold success--text">Bs {{ parseFloat(p.monto_cobrado_bs).toFixed(2) }}</td>
                  <td class="text-center">
                    <v-progress-linear
                      :value="p.porcentaje_recaudacion"
                      height="18"
                      rounded
                      color="success"
                    >
                      <template v-slot:default>
                        <span class="text-caption font-weight-bold white--text">{{ p.porcentaje_recaudacion }}%</span>
                      </template>
                    </v-progress-linear>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-tab-item>
    </v-tabs-items>

    <!-- MODAL VISOR DE PDF INSTITUCIONAL -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlReportePdf"
      :titulo="tituloVisorPdf"
      :url-excel="urlExcelReporte"
      :nombre-descarga="nombrePdfReporte"
      :nombre-excel="nombreExcelReporte"
      @descargar="descargarArchivoPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'ReportesComerciales',
  components: {
    ModalVisorPdf,
  },
  data() {
    const hoy = new Date().toISOString().substr(0, 10);
    return {
      tabActual: 0,
      subTabActual: 0,
      cargandoRecaudacion: false,
      generandoPdf: false,

      // Filtros
      filtros: {
        periodo_tipo: 'hoy',
        fecha_inicio: hoy,
        fecha_fin: hoy,
        id_punto_venta: null,
        id_cajero: null,
        metodo_pago: null,
      },

      // Catálogos para filtros
      cajasLista: [],
      cajerosLista: [],

      // Datos respuesta
      datosConsolidados: null,
      datosMora: null,
      periodosBalance: [],

      // Visor PDF y Exportaciones
      mostrarVisorPdf: false,
      urlReportePdf: '',
      tituloVisorPdf: '',
      urlExcelReporte: '',
      nombrePdfReporte: 'reporte_comercial.pdf',
      nombreExcelReporte: 'reporte_comercial.csv',
      exportandoExcel: false,

      // Resumen por Zonas
      periodosLista: [],
      filtroPeriodoZonas: null,
      datosResumenZonas: null,
      cargandoResumenZonas: false,

      // Nómina de Cortes
      zonasLista: [],
      filtroZonaCortes: null,
      filtroMesesMoraCortes: 2,
      datosNominaCortes: null,
      cargandoNominaCortes: false,
      buscarCorte: '',
      headersNominaCortes: [
        { text: '#', value: 'item_index', sortable: false, width: '45px', align: 'center' },
        { text: 'Código', value: 'codigo', align: 'center', width: '90px' },
        { text: 'Titular / Abonado', value: 'nombre_completo' },
        { text: 'Zona Comercial', value: 'zona_nombre' },
        { text: 'Dirección / Calle', value: 'direccion_completa', sortable: false },
        { text: 'Categoría', value: 'categoria_nombre', align: 'center' },
        { text: 'N° Medidor', value: 'medidor_serie', align: 'center' },
        { text: 'Meses Mora', value: 'meses_mora', align: 'center' },
        { text: 'Deuda Total', value: 'saldo_deuda', align: 'right' },
      ],
    };
  },
  computed: {
    diferenciaArqueo() {
      if (!this.datosConsolidados || !this.datosConsolidados.metricas) return 0;
      return parseFloat(this.datosConsolidados.metricas.diferencia_neta || 0);
    },
    listaNominaCortes() {
      if (!this.datosNominaCortes || !this.datosNominaCortes.abonados) return [];
      return this.datosNominaCortes.abonados.map((a, idx) => ({
        ...a,
        item_index: idx + 1,
        zona_nombre: a.zona ? a.zona.nombre : 'S/Z',
        direccion_completa: `${a.calle ? a.calle.nombre : 'S/C'} ${a.numero_vivienda ? '#' + a.numero_vivienda : ''}`,
        categoria_nombre: a.categoria ? a.categoria.nombre : 'DOMESTICO',
        medidor_serie: a.medidor_actual ? a.medidor_actual.numero_serie : (a.numero_medidor || 'S/M'),
        saldo_deuda_num: parseFloat(a.saldo_deuda || 0),
      }));
    },
  },
  mounted() {
    if (this.$route.name === 'comercial_sesiones_caja' || this.$route.query.tab === 'turnos' || this.$route.query.tab === 'arqueos') {
      this.tabActual = 0;
      this.subTabActual = 3;
    }
    this.cargarCatalogosFiltros();
    this.cargarCatalogosCiclo();
    this.cargarRecaudacionConsolidada();
    this.cargarMorosidad();
    this.cargarBalanceConsumo();
  },
  methods: {
    async cargarCatalogosFiltros() {
      try {
        const [respCajas, respCajeros] = await Promise.all([
          axios.get('/api/comercial/caja-sesiones/cajas-disponibles'),
          axios.get('/api/comercial/caja-sesiones/cajeros'),
        ]);
        this.cajasLista = respCajas.data.data || [];
        this.cajerosLista = respCajeros.data.data || [];
      } catch (e) {
        console.error('Error cargando catálogos de filtros:', e);
      }
    },

    cambiarPeriodoRapido(tipo) {
      const hoy = new Date();
      const format = d => d.toISOString().substr(0, 10);

      if (tipo === 'hoy') {
        this.filtros.fecha_inicio = format(hoy);
        this.filtros.fecha_fin = format(hoy);
      } else if (tipo === 'semana') {
        const d = new Date(hoy);
        const diaSemana = d.getDay() || 7;
        d.setDate(d.getDate() - diaSemana + 1); // Lunes
        this.filtros.fecha_inicio = format(d);
        const finSem = new Date(d);
        finSem.setDate(finSem.getDate() + 6); // Domingo
        this.filtros.fecha_fin = format(finSem);
      } else if (tipo === 'mes') {
        const iniMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        this.filtros.fecha_inicio = format(iniMes);
        this.filtros.fecha_fin = format(finMes);
      } else if (tipo === 'mes_anterior') {
        const iniMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const finMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        this.filtros.fecha_inicio = format(iniMesAnt);
        this.filtros.fecha_fin = format(finMesAnt);
      }

      this.cargarRecaudacionConsolidada();
    },

    onFechaChange() {
      this.filtros.periodo_tipo = 'personalizado';
      this.cargarRecaudacionConsolidada();
    },

    construirQueryParams() {
      const p = {
        periodo_tipo: this.filtros.periodo_tipo,
        fecha_inicio: this.filtros.fecha_inicio,
        fecha_fin: this.filtros.fecha_fin,
      };
      if (this.filtros.id_punto_venta) p.id_punto_venta = this.filtros.id_punto_venta;
      if (this.filtros.id_cajero) p.id_cajero = this.filtros.id_cajero;
      if (this.filtros.metodo_pago) p.metodo_pago = this.filtros.metodo_pago;
      return p;
    },

    async cargarRecaudacionConsolidada() {
      this.cargandoRecaudacion = true;
      try {
        const res = await axios.get('/api/comercial/reportes/recaudacion-consolidada', {
          params: this.construirQueryParams(),
        });
        this.datosConsolidados = res.data.data;
      } catch (e) {
        console.error('Error cargando recaudación consolidada:', e);
      } finally {
        this.cargandoRecaudacion = false;
      }
    },

    // Descarga autenticada en segundo plano (cero pestañas nuevas)
    async descargarArchivoBlob(url, nombreArchivo) {
      this.exportandoExcel = true;
      try {
        const token = localStorage.getItem('token');
        const headers = {};
        if (token) {
          headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
        }
        const client = window.axios || axios;
        const res = await client.get(url, {
          responseType: 'blob',
          headers,
        });
        const blob = new Blob([res.data], {
          type: res.headers['content-type'] || 'text/csv;charset=utf-8;',
        });
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.style.display = 'none';
        link.href = blobUrl;
        link.setAttribute('download', nombreArchivo);
        document.body.appendChild(link);
        link.click();
        setTimeout(() => {
          document.body.removeChild(link);
          URL.revokeObjectURL(blobUrl);
        }, 200);
        this.$toast?.success(`Archivo ${nombreArchivo} descargado exitosamente.`);
      } catch (err) {
        console.error('Error al descargar archivo:', err);
        this.$toast?.error('No se pudo descargar el archivo solicitado.');
      } finally {
        this.exportandoExcel = false;
      }
    },

    verPdfConsolidado() {
      const q = new URLSearchParams(this.construirQueryParams()).toString();
      this.urlReportePdf = `/api/comercial/reportes/recaudacion-consolidada/pdf?${q}`;
      this.urlExcelReporte = `/api/comercial/reportes/recaudacion-consolidada/csv?${q}`;
      this.nombrePdfReporte = `Recaudacion_Consolidada_${this.filtros.fecha_inicio}_${this.filtros.fecha_fin}.pdf`;
      this.nombreExcelReporte = `Recaudacion_Consolidada_${this.filtros.fecha_inicio}_${this.filtros.fecha_fin}.csv`;
      this.tituloVisorPdf = `Planilla Oficial de Recaudación Consolidada (${this.filtros.fecha_inicio} al ${this.filtros.fecha_fin})`;
      this.mostrarVisorPdf = true;
    },

    verPdfArqueoIndividual(sesionId) {
      this.urlReportePdf = `/api/comercial/caja-sesiones/${sesionId}/reporte-pdf`;
      this.urlExcelReporte = '';
      this.nombrePdfReporte = `Arqueo_Turno_${sesionId}.pdf`;
      this.tituloVisorPdf = `Planilla Oficial de Arqueo de Turno`;
      this.mostrarVisorPdf = true;
    },

    async descargarArchivoPdf() {
      if (this.urlReportePdf) {
        await this.descargarArchivoBlob(this.urlReportePdf, this.nombrePdfReporte || 'reporte.pdf');
      }
    },

    async exportarCsv() {
      const q = new URLSearchParams(this.construirQueryParams()).toString();
      const url = `/api/comercial/reportes/recaudacion-consolidada/csv?${q}`;
      await this.descargarArchivoBlob(url, `Recaudacion_Consolidada_${this.filtros.fecha_inicio}_${this.filtros.fecha_fin}.csv`);
    },

    async cargarMorosidad() {
      try {
        const res = await axios.get('/api/comercial/reportes/morosidad');
        this.datosMora = res.data;
      } catch (e) {
        console.error('Error cargando morosidad:', e);
      }
    },

    async cargarBalanceConsumo() {
      try {
        const res = await axios.get('/api/comercial/reportes/balance-consumo');
        this.periodosBalance = res.data.periodos || [];
      } catch (e) {
        console.error('Error cargando balance consumo:', e);
      }
    },

    async cargarCatalogosCiclo() {
      try {
        const [respPeriodos, respZonas] = await Promise.all([
          axios.get('/api/comercial/periodos'),
          axios.get('/api/comercial/zonas'),
        ]);
        this.periodosLista = respPeriodos.data.data || [];
        this.zonasLista = respZonas.data.data || [];

        if (this.periodosLista.length > 0) {
          this.filtroPeriodoZonas = this.periodosLista[0].id;
          this.cargarResumenZonas();
        }
        this.cargarNominaCortes();
      } catch (e) {
        console.error('Error cargando catálogos de ciclo:', e);
      }
    },

    async cargarResumenZonas() {
      if (!this.filtroPeriodoZonas) return;
      this.cargandoResumenZonas = true;
      try {
        const res = await axios.get('/api/comercial/reportes/resumen-operaciones-zonas', {
          params: { id_periodo: this.filtroPeriodoZonas },
        });
        if (res.data && res.data.success) {
          this.datosResumenZonas = res.data;
        }
      } catch (e) {
        console.error('Error al cargar resumen de zonas:', e);
      } finally {
        this.cargandoResumenZonas = false;
      }
    },

    verPdfResumenZonas() {
      if (!this.filtroPeriodoZonas) return;
      const periodoLabel = this.datosResumenZonas?.periodo?.periodo ? String(this.datosResumenZonas.periodo.periodo).replace('/', '_') : this.filtroPeriodoZonas;
      this.tituloVisorPdf = `Resumen de Operaciones y Facturación por Zonas - Período ${this.datosResumenZonas?.periodo?.periodo || ''}`;
      this.urlReportePdf = `/api/comercial/reportes/resumen-operaciones-zonas/pdf?id_periodo=${this.filtroPeriodoZonas}`;
      this.urlExcelReporte = `/api/comercial/reportes/resumen-operaciones-zonas/excel?id_periodo=${this.filtroPeriodoZonas}`;
      this.nombrePdfReporte = `Resumen_Zonas_${periodoLabel}.pdf`;
      this.nombreExcelReporte = `Resumen_Zonas_${periodoLabel}.csv`;
      this.mostrarVisorPdf = true;
    },

    async exportarExcelResumenZonas() {
      if (!this.filtroPeriodoZonas) return;
      const periodoLabel = this.datosResumenZonas?.periodo?.periodo ? String(this.datosResumenZonas.periodo.periodo).replace('/', '_') : this.filtroPeriodoZonas;
      const url = `/api/comercial/reportes/resumen-operaciones-zonas/excel?id_periodo=${this.filtroPeriodoZonas}`;
      await this.descargarArchivoBlob(url, `Resumen_Zonas_${periodoLabel}.csv`);
    },

    async cargarNominaCortes() {
      this.cargandoNominaCortes = true;
      try {
        const params = { meses_mora: this.filtroMesesMoraCortes };
        if (this.filtroZonaCortes) params.id_zona = this.filtroZonaCortes;

        const res = await axios.get('/api/comercial/reportes/nomina-cortes', { params });
        if (res.data && res.data.success) {
          this.datosNominaCortes = res.data;
        }
      } catch (e) {
        console.error('Error al cargar nómina de cortes:', e);
      } finally {
        this.cargandoNominaCortes = false;
      }
    },

    verPdfNominaCortes() {
      const q = new URLSearchParams();
      q.append('meses_mora', this.filtroMesesMoraCortes);
      if (this.filtroZonaCortes) q.append('id_zona', this.filtroZonaCortes);

      this.tituloVisorPdf = `Planilla de Cortes Masivos por Mora (>= ${this.filtroMesesMoraCortes} Meses)`;
      this.urlReportePdf = `/api/comercial/reportes/nomina-cortes/pdf?${q.toString()}`;
      this.urlExcelReporte = `/api/comercial/reportes/nomina-cortes/excel?${q.toString()}`;
      this.nombrePdfReporte = `Nomina_Cortes_Masivos.pdf`;
      this.nombreExcelReporte = `Nomina_Cortes_Masivos.csv`;
      this.mostrarVisorPdf = true;
    },

    async exportarExcelNominaCortes() {
      const q = new URLSearchParams();
      q.append('meses_mora', this.filtroMesesMoraCortes);
      if (this.filtroZonaCortes) q.append('id_zona', this.filtroZonaCortes);

      const url = `/api/comercial/reportes/nomina-cortes/excel?${q.toString()}`;
      await this.descargarArchivoBlob(url, 'Nomina_Cortes_Masivos.csv');
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
.bg-success-light {
  background-color: #f1f8e9 !important;
  border-color: #a5d6a7 !important;
}
.bg-warning-light {
  background-color: #fffde7 !important;
  border-color: #ffe082 !important;
}
.bg-danger-light {
  background-color: #ffebee !important;
  border-color: #ef9a9a !important;
}
.gap-2 {
  gap: 8px;
}
</style>
