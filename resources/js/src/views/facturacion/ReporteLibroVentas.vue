<template>
  <div class="reporte-libro-ventas">
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-book-open-page-variant</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0 text--primary">Libro de Ventas IVA (SIAT)</h2>
            <span class="text-caption text-secondary">
              Registro Oficial de Ventas y Débito Fiscal para EMAPAP Patacamaya (Servicio de Impuestos Nacionales)
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 my-1 flex-wrap">
          <v-btn
            color="indigo darken-2"
            dark
            small
            class="white--text font-weight-bold rounded-pill elevation-2"
            @click="abrirDialogoConciliador"
          >
            <v-icon left small>mdi-scale-balance</v-icon> Cotejador SIN (Excel)
          </v-btn>

          <v-btn
            color="primary"
            dark
            outlined
            small
            class="rounded-pill font-weight-medium"
            @click="imprimirReporte"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir Libro
          </v-btn>

          <v-btn
            color="success darken-1"
            dark
            small
            class="white--text font-weight-bold rounded-pill elevation-1"
            :loading="cargandoCsv"
            @click="descargarCsv"
          >
            <v-icon left small>mdi-file-delimited</v-icon> Exportar CSV (Normativa SIN)
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- FILTROS AVANZADOS Y PERÍODO RÁPIDO (ESTÁNDAR REPORTES) -->
    <v-card class="mb-4 pa-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
        <div class="d-flex align-center flex-wrap gap-2">
          <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO RÁPIDO:</span>
          <v-btn-toggle v-model="periodoTipo" mandatory dense color="primary" @change="cambiarPeriodoRapido">
            <v-btn small value="hoy" class="text-capitalize">Hoy</v-btn>
            <v-btn small value="semana" class="text-capitalize">Esta Semana</v-btn>
            <v-btn small value="mes" class="text-capitalize">Este Mes</v-btn>
            <v-btn small value="mes_anterior" class="text-capitalize">Mes Anterior</v-btn>
            <v-btn small value="agosto_2026" class="text-capitalize font-weight-bold primary--text">Agosto 2026</v-btn>
            <v-btn small value="septiembre_2026" class="text-capitalize font-weight-bold primary--text">Septiembre 2026</v-btn>
            <v-btn small value="personalizado" class="text-capitalize">Personalizado</v-btn>
          </v-btn-toggle>
        </div>

        <div class="text-caption font-weight-medium text-secondary">
          Rango: {{ formatearFecha(filtros.fecha_desde) }} al {{ formatearFecha(filtros.fecha_hasta) }}
        </div>
      </div>

      <v-divider class="mb-3"></v-divider>

      <v-row dense align="center">
        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtros.fecha_desde"
            label="Fecha Desde"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-start"
            @change="onFechaManualChange"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtros.fecha_hasta"
            label="Fecha Hasta"
            type="date"
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-calendar-end"
            @change="onFechaManualChange"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtros.estado"
            :items="estadosList"
            label="Estado Fiscal SIN"
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-filter"
            @change="consultarReporte"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtros.tipo_emision"
            :items="tiposEmisionList"
            label="Modalidad de Emisión"
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-cloud-sync-outline"
            @change="consultarReporte"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="12" md="2" class="d-flex justify-end gap-2">
          <v-btn
            color="primary"
            dark
            class="rounded-pill w-100 font-weight-bold"
            :loading="cargando"
            @click="consultarReporte"
          >
            <v-icon left small>mdi-filter-check</v-icon> Consultar
          </v-btn>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                outlined
                color="secondary"
                icon
                v-bind="attrs"
                v-on="on"
                @click="limpiarFiltros"
              >
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Restablecer Filtros</span>
          </v-tooltip>
        </v-col>
      </v-row>
    </v-card>

    <!-- TARJETAS DE RESUMEN FISCAL (KPIS ESTANDARIZADOS) -->
    <v-row class="mb-4" dense>
      <!-- TOTAL FACTURADO -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Facturado</div>
          <div class="text-h4 font-weight-black primary--text mt-1">
            Bs {{ formatoMoneda(resumen.total_facturado) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            {{ resumen.total_registros }} facturas computadas
          </div>
        </v-card>
      </v-col>

      <!-- BASE DÉBITO FISCAL -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Base Débito Fiscal</div>
          <div class="text-h4 font-weight-black success--text mt-1">
            Bs {{ formatoMoneda(resumen.total_base_debito_fiscal) }}
          </div>
          <div class="text-caption text-success mt-1 font-weight-medium">
            Importe sujeto al IVA (13%)
          </div>
        </v-card>
      </v-col>

      <!-- DÉBITO FISCAL IVA (13%) -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Débito Fiscal IVA (13%)</div>
          <div class="text-h4 font-weight-black info--text mt-1">
            Bs {{ formatoMoneda(resumen.debito_fiscal_iva) }}
          </div>
          <div class="text-caption text-secondary mt-1">
            Monto a declarar en Form. 200
          </div>
        </v-card>
      </v-col>

      <!-- RESUMEN DE ESTADOS FISCALES -->
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase text-center mb-1">
            Estados Fiscales SIN
          </div>
          <div class="d-flex flex-column gap-1">
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium success--text">
                <v-icon x-small color="success">mdi-check-circle</v-icon> Válidas en Línea:
              </span>
              <v-chip x-small color="success" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_validas || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium warning--text text--darken-2">
                <v-icon x-small color="warning darken-2">mdi-cloud-off-outline</v-icon> Contingencias:
              </span>
              <v-chip x-small color="warning darken-1" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_contingencias || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium error--text">
                <v-icon x-small color="error">mdi-cancel</v-icon> Anuladas:
              </span>
              <v-chip x-small color="error" text-color="white" class="font-weight-bold">
                {{ resumen.cantidad_anuladas || 0 }}
              </v-chip>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA DETALLADA DEL LIBRO DE VENTAS CON TOTALES (TFOOT) -->
    <v-card class="erp-card-elevated" rounded="lg">
      <v-card-title class="py-3 px-4 d-flex justify-space-between align-center">
        <div class="font-weight-bold text-subtitle-1">
          <v-icon left color="primary">mdi-table</v-icon> Detalle de Facturas Registradas
        </div>
        <div class="d-flex align-center gap-2">
          <v-chip small color="primary" outlined class="font-weight-bold">
            {{ facturas.length }} facturas computadas
          </v-chip>
          <v-chip small color="warning darken-1" text-color="white" class="font-weight-bold" v-if="resumen.cantidad_contingencias > 0">
            {{ resumen.cantidad_contingencias }} en Contingencia
          </v-chip>
        </div>
      </v-card-title>
      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="facturas"
        :loading="cargando"
        :items-per-page="25"
        class="erp-table"
        dense
        no-data-text="No se encontraron facturas emitidas en el periodo seleccionado"
      >
        <template v-slot:item.numero_factura="{ item }">
          <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
        </template>

        <template v-slot:item.fecha_emision="{ item }">
          <span class="text-caption font-weight-medium">{{ formatearFechaHora(item.fecha_emision) }}</span>
        </template>

        <template v-slot:item.documento="{ item }">
          <div>
            <strong class="text-body-2">{{ item.numero_documento }}</strong>
            <span v-if="item.complemento" class="grey--text"> - {{ item.complemento }}</span>
          </div>
        </template>

        <template v-slot:item.tipo_emision="{ item }">
          <v-chip
            x-small
            :color="esContingencia(item) ? 'warning darken-1' : 'info'"
            text-color="white"
            class="font-weight-bold"
          >
            <v-icon left x-small>{{ esContingencia(item) ? 'mdi-cloud-off-outline' : 'mdi-cloud-check-outline' }}</v-icon>
            {{ esContingencia(item) ? 'CONTINGENCIA' : 'EN LÍNEA' }}
          </v-chip>
        </template>

        <template v-slot:item.cuf="{ item }">
          <div class="d-flex align-center">
            <span class="text-caption font-mono text-truncate" style="max-width: 140px;" :title="item.cuf">
              {{ item.cuf }}
            </span>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon x-small v-bind="attrs" v-on="on" class="ml-1" @click="copiarTexto(item.cuf)">
                  <v-icon x-small>mdi-content-copy</v-icon>
                </v-btn>
              </template>
              <span>Copiar CUF</span>
            </v-tooltip>
          </div>
        </template>

        <template v-slot:item.monto_total="{ item }">
          <span class="font-weight-bold">Bs {{ formatoMoneda(item.monto_total) }}</span>
        </template>

        <template v-slot:item.monto_total_sujeto_iva="{ item }">
          <span class="font-weight-medium success--text text--darken-2">
            Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : formatoMoneda(item.monto_total_sujeto_iva) }}
          </span>
        </template>

        <template v-slot:item.debito_fiscal="{ item }">
          <span class="font-weight-bold info--text">
            Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : formatoMoneda(item.monto_total_sujeto_iva * 0.13) }}
          </span>
        </template>

        <template v-slot:item.estado_factura="{ item }">
          <v-chip
            x-small
            :color="colorEstado(item.estado_factura)"
            text-color="white"
            class="font-weight-bold"
          >
            <v-icon left x-small>{{ iconoEstado(item.estado_factura) }}</v-icon>
            {{ labelEstado(item.estado_factura) }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal darken-2"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirPortalSiat(item)"
                >
                  <v-icon small>mdi-qrcode-scan</v-icon>
                </v-btn>
              </template>
              <span>Verificar en Portal Oficial SIAT (SIN)</span>
            </v-tooltip>
          </div>
        </template>

        <!-- FILA TOTALIZADORA (TFOOT) -->
        <template v-slot:body.append>
          <tr class="grey lighten-3 font-weight-black" v-if="facturas.length > 0">
            <td colspan="6" class="text-right text-uppercase">TOTALES DEL PERÍODO:</td>
            <td class="text-right primary--text font-weight-black">Bs {{ formatoMoneda(resumen.total_facturado) }}</td>
            <td class="text-right success--text text--darken-2 font-weight-black">Bs {{ formatoMoneda(resumen.total_base_debito_fiscal) }}</td>
            <td class="text-right info--text font-weight-black">Bs {{ formatoMoneda(resumen.debito_fiscal_iva) }}</td>
            <td colspan="2" class="text-center font-weight-black text-caption">
              <span class="success--text font-weight-bold">{{ resumen.cantidad_validas || 0 }} Válidas</span> |
              <span class="warning--text text--darken-2 font-weight-bold">{{ resumen.cantidad_contingencias || 0 }} Conting.</span> |
              <span class="error--text font-weight-bold">{{ resumen.cantidad_anuladas || 0 }} Anul.</span>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card>

    <!-- =============================================================== -->
    <!-- DIÁLOGO / MODAL COTEJADOR Y CONCILIADOR TRIBUTARIO SIAT vs SISTEMA -->
    <!-- =============================================================== -->
    <v-dialog v-model="dialogoConciliador" max-width="1250px" persistent scrollable>
      <v-card class="dialog-conciliador" rounded="lg">
        <!-- BARRA DE TÍTULO -->
        <v-card-title class="indigo darken-3 text-white py-3 px-4 d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar color="white" size="38" class="mr-3">
              <v-icon color="indigo darken-3">mdi-scale-balance</v-icon>
            </v-avatar>
            <div>
              <div class="text-h6 font-weight-bold text-white mb-0">Cotejador y Conciliador Tributario SIAT vs Sistema</div>
              <div class="text-caption text-indigo-lighten-4">
                Auditoría tributaria automática entre el Registro de Ventas (RCV) de Impuestos Nacionales y SIM-EMAPAP
              </div>
            </div>
          </div>
          <v-btn icon dark @click="cerrarDialogoConciliador">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pa-4 bg-grey-lighten-4">
          <!-- SECCIÓN 1: SELECCIÓN DE ARCHIVO DEL SIN Y RANGO DE FECHAS -->
          <v-card outlined class="mb-4 pa-4 white" rounded="lg">
            <div class="d-flex justify-space-between align-center mb-2 flex-wrap">
              <div class="text-subtitle-2 font-weight-bold indigo--text text--darken-3">
                <v-icon small color="indigo darken-3">mdi-file-excel</v-icon> 1. Seleccionar Reporte Oficial del SIN (Excel RCV)
              </div>
              <div class="text-caption text-secondary">
                <v-icon x-small color="grey darken-1">mdi-calendar-range</v-icon> Rango de fechas personalizable o autodetectable
              </div>
            </div>

            <v-row dense align="center">
              <v-col cols="12" md="5">
                <v-file-input
                  v-model="archivoSinUpload"
                  label="Cargar archivo Excel del SIN (.xlsx, .xls)"
                  prepend-icon="mdi-paperclip"
                  outlined
                  dense
                  hide-details
                  show-size
                  accept=".xlsx, .xls, .csv"
                  placeholder="Seleccione el archivo Excel descargado del SIAT..."
                  @change="resultadoConciliacion = null"
                ></v-file-input>
              </v-col>

              <v-col cols="12" sm="6" md="2">
                <v-text-field
                  v-model="filtroCotejoFechaDesde"
                  label="Fecha Desde (A)"
                  type="date"
                  outlined
                  dense
                  hide-details
                  clearable
                  placeholder="AAAA-MM-DD"
                  hint="Opcional"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" md="2">
                <v-text-field
                  v-model="filtroCotejoFechaHasta"
                  label="Fecha Hasta (B)"
                  type="date"
                  outlined
                  dense
                  hide-details
                  clearable
                  placeholder="AAAA-MM-DD"
                  hint="Opcional"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="3">
                <v-btn
                  color="indigo darken-2"
                  dark
                  block
                  class="font-weight-bold rounded-pill elevation-1"
                  :loading="cargandoCotejo"
                  :disabled="!archivoSinUpload"
                  @click="ejecutarCotejo"
                >
                  <v-icon left small>mdi-file-find</v-icon> Ejecutar Cotejo Fiscal
                </v-btn>
              </v-col>
            </v-row>
            <div class="text-caption text-secondary mt-2 d-flex align-center">
              <v-icon x-small color="info" class="mr-1">mdi-information-outline</v-icon>
              <span>
                <strong>Modo inteligente:</strong> Si ingresas <em>Fecha Desde (A)</em> y <em>Fecha Hasta (B)</em>, el cotejo se filtrará a ese rango. Si las dejas en blanco, el sistema autodetectará automáticamente el rango de fechas que contenga el reporte Excel descargado.
              </span>
            </div>
          </v-card>

          <!-- SECCIÓN 2: RESULTADOS DE LA AUDITORÍA Y COTEJO -->
          <div v-if="resultadoConciliacion">
            <!-- BANNER DE RESUMEN TRIBUTARIO -->
            <v-alert
              border="left"
              colored-border
              :color="(resultadoConciliacion.kpis.resumen_cotejo.validas_faltantes > 0 || resultadoConciliacion.kpis.resumen_cotejo.anuladas_faltantes > 0) ? 'warning' : 'success'"
              elevation="1"
              class="mb-4 white"
            >
              <div class="d-flex justify-space-between align-center flex-wrap gap-2">
                <div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">
                    <v-icon left :color="(resultadoConciliacion.kpis.resumen_cotejo.validas_faltantes > 0 || resultadoConciliacion.kpis.resumen_cotejo.anuladas_faltantes > 0) ? 'warning darken-2' : 'success'">
                      {{ (resultadoConciliacion.kpis.resumen_cotejo.validas_faltantes > 0 || resultadoConciliacion.kpis.resumen_cotejo.anuladas_faltantes > 0) ? 'mdi-alert-circle' : 'mdi-check-decagram' }}
                    </v-icon>
                    Período Fiscal Analizado: {{ formatearFecha(resultadoConciliacion.periodo.desde) }} al {{ formatearFecha(resultadoConciliacion.periodo.hasta) }}
                  </h3>
                  <div class="text-caption text-secondary">
                    Archivo: <strong>{{ resultadoConciliacion.archivo }}</strong> | Coincidencia en Válidas:
                    <strong :class="resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct === 100 ? 'success--text' : 'warning--text text--darken-2'" class="font-weight-bold">
                      {{ resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct }}%
                    </strong> | Coincidencia Global:
                    <strong :class="resultadoConciliacion.kpis.resumen_cotejo.porcentaje_coincidencia === 100 ? 'success--text' : 'primary--text'">
                      {{ resultadoConciliacion.kpis.resumen_cotejo.porcentaje_coincidencia }}%
                    </strong>
                  </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <v-chip color="success" text-color="white" small class="font-weight-bold">
                    <v-icon left x-small>mdi-check</v-icon> {{ resultadoConciliacion.kpis.resumen_cotejo.coincidentes_exactas }} Coincidentes
                  </v-chip>
                  <v-chip color="error" text-color="white" small class="font-weight-bold" v-if="resultadoConciliacion.kpis.resumen_cotejo.validas_faltantes > 0">
                    <v-icon left x-small>mdi-alert</v-icon> {{ resultadoConciliacion.kpis.resumen_cotejo.validas_faltantes }} Válidas ausentes
                  </v-chip>
                  <v-chip color="warning darken-1" text-color="white" small class="font-weight-bold" v-if="resultadoConciliacion.kpis.resumen_cotejo.anuladas_faltantes > 0">
                    <v-icon left x-small>mdi-alert-circle-outline</v-icon> {{ resultadoConciliacion.kpis.resumen_cotejo.anuladas_faltantes }} Anuladas ausentes
                  </v-chip>
                  <v-chip color="info" text-color="white" small class="font-weight-bold" v-if="resultadoConciliacion.kpis.resumen_cotejo.diferencias_ley1886 > 0">
                    <v-icon left x-small>mdi-account-supervisor</v-icon> {{ resultadoConciliacion.kpis.resumen_cotejo.diferencias_ley1886 }} Descuentos Ley 1886
                  </v-chip>
                </div>
              </div>
            </v-alert>

            <!-- TARJETAS COMPARATIVAS KPIS SIN vs SISTEMA -->
            <v-row dense class="mb-4">
              <!-- KPI REGISTROS -->
              <v-col cols="12" sm="6" md="3">
                <v-card class="pa-3 text-center erp-card-elevated white" rounded="lg">
                  <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Facturas</div>
                  <div class="d-flex justify-space-around align-center mt-2">
                    <div>
                      <div class="text-caption text-secondary">Reporte SIN</div>
                      <div class="text-h5 font-weight-bold indigo--text">{{ resultadoConciliacion.kpis.sin.total_registros }}</div>
                    </div>
                    <v-divider vertical class="mx-2"></v-divider>
                    <div>
                      <div class="text-caption text-secondary">SIM-EMAPAP</div>
                      <div class="text-h5 font-weight-bold teal--text">{{ resultadoConciliacion.kpis.sistema.total_registros }}</div>
                    </div>
                  </div>
                  <div class="text-caption error--text font-weight-bold mt-1" v-if="resultadoConciliacion.kpis.sin.total_registros !== resultadoConciliacion.kpis.sistema.total_registros">
                    Diferencia: {{ Math.abs(resultadoConciliacion.kpis.sin.total_registros - resultadoConciliacion.kpis.sistema.total_registros) }} facturas
                  </div>
                  <div class="text-caption text-secondary font-weight-bold mt-1" v-else-if="resultadoConciliacion.kpis.sin.total_registros === 0">
                    Sin facturas en este rango
                  </div>
                  <div class="text-caption success--text font-weight-bold mt-1" v-else>
                    ¡Registros 100% Cuadrados!
                  </div>
                </v-card>
              </v-col>

              <!-- KPI FACTURAS VÁLIDAS -->
              <v-col cols="12" sm="6" md="3">
                <v-card class="pa-3 text-center erp-card-elevated white" rounded="lg">
                  <div class="text-caption text-secondary font-weight-bold text-uppercase">Facturas Válidas</div>
                  <div class="d-flex justify-space-around align-center mt-2">
                    <div>
                      <div class="text-caption text-secondary">En SIN</div>
                      <div class="text-h5 font-weight-bold success--text">{{ resultadoConciliacion.kpis.sin.total_validas }}</div>
                    </div>
                    <v-divider vertical class="mx-2"></v-divider>
                    <div>
                      <div class="text-caption text-secondary">En Sistema</div>
                      <div class="text-h5 font-weight-bold" :class="resultadoConciliacion.kpis.sistema.total_validas > 0 ? 'success--text' : 'grey--text'">
                        {{ resultadoConciliacion.kpis.sistema.total_validas }}
                      </div>
                    </div>
                  </div>
                  <div class="text-caption text-secondary font-weight-bold mt-1" v-if="resultadoConciliacion.kpis.sin.total_validas === 0 && resultadoConciliacion.kpis.sistema.total_validas === 0">
                    Sin válidas en este rango
                  </div>
                  <div class="text-caption font-weight-bold mt-1" v-else :class="resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct === 100 ? 'success--text' : 'warning--text text--darken-2'">
                    <v-icon x-small :color="resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct === 100 ? 'success' : 'warning'">
                      {{ resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct === 100 ? 'mdi-check-circle' : 'mdi-alert' }}
                    </v-icon>
                    {{ resultadoConciliacion.kpis.resumen_cotejo.coincidencia_validas_pct }}% Coincidencia en Válidas
                  </div>
                </v-card>
              </v-col>

              <!-- KPI TOTAL FACTURADO -->
              <v-col cols="12" sm="6" md="3">
                <v-card class="pa-3 text-center erp-card-elevated white" rounded="lg">
                  <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Ventas Válidas</div>
                  <div class="d-flex justify-space-around align-center mt-2">
                    <div>
                      <div class="text-caption text-secondary">SIN (Oficial)</div>
                      <div class="text-h6 font-weight-bold primary--text">Bs {{ formatoMoneda(resultadoConciliacion.kpis.sin.total_facturado) }}</div>
                      <div class="text-caption text-secondary" style="font-size: 11px !important;">
                        Excel: Bs {{ formatoMoneda(resultadoConciliacion.kpis.sin.total_excel_bruto) }}
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-icon x-small color="grey" v-bind="attrs" v-on="on">mdi-information-outline</v-icon>
                          </template>
                          <span>La suma de la columna H en Excel incluye Bs 1.727,30 de 72 facturas anuladas que no tienen validez fiscal.</span>
                        </v-tooltip>
                      </div>
                    </div>
                    <v-divider vertical class="mx-2"></v-divider>
                    <div>
                      <div class="text-caption text-secondary">Sistema (Neto)</div>
                      <div class="text-h6 font-weight-bold secondary--text">Bs {{ formatoMoneda(resultadoConciliacion.kpis.sistema.total_facturado) }}</div>
                    </div>
                  </div>
                  <div class="text-caption text-secondary mt-1" v-if="resultadoConciliacion.kpis.sistema.total_registros === 0">
                    Base de datos sin registros
                  </div>
                  <div class="text-caption text-secondary mt-1" v-else-if="resultadoConciliacion.kpis.resumen_cotejo.diferencias_ley1886 > 0">
                    Diferencia de Bs {{ formatoMoneda(resultadoConciliacion.kpis.sin.total_facturado - resultadoConciliacion.kpis.sistema.total_facturado) }} por Ley 1886
                  </div>
                  <div class="text-caption success--text font-weight-bold mt-1" v-else>
                    Montos facturados coincidentes
                  </div>
                </v-card>
              </v-col>

              <!-- KPI DÉBITO FISCAL -->
              <v-col cols="12" sm="6" md="3">
                <v-card class="pa-3 text-center erp-card-elevated white" rounded="lg">
                  <div class="text-caption text-secondary font-weight-bold text-uppercase">Débito Fiscal IVA (13%)</div>
                  <div class="d-flex justify-space-around align-center mt-2">
                    <div>
                      <div class="text-caption text-secondary">SIN RCV</div>
                      <div class="text-h6 font-weight-bold info--text">Bs {{ formatoMoneda(resultadoConciliacion.kpis.sin.total_debito_fiscal) }}</div>
                      <div class="text-caption text-secondary" style="font-size: 11px !important;">
                        Excel: Bs {{ formatoMoneda(resultadoConciliacion.kpis.sin.total_excel_debito_fiscal) }}
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-icon x-small color="grey" v-bind="attrs" v-on="on">mdi-information-outline</v-icon>
                          </template>
                          <span>La suma de la columna T en Excel incluye Bs 213,46 de facturas anuladas; tributariamente en Form. 200 el débito es Bs 11.453,09.</span>
                        </v-tooltip>
                      </div>
                    </div>
                    <v-divider vertical class="mx-2"></v-divider>
                    <div>
                      <div class="text-caption text-secondary">Sistema</div>
                      <div class="text-h6 font-weight-bold info--text">Bs {{ formatoMoneda(resultadoConciliacion.kpis.sistema.total_debito_fiscal) }}</div>
                    </div>
                  </div>
                  <div class="text-caption font-weight-bold mt-1" :class="resultadoConciliacion.kpis.sin.total_debito_fiscal === resultadoConciliacion.kpis.sistema.total_debito_fiscal && resultadoConciliacion.kpis.sistema.total_debito_fiscal > 0 ? 'success--text' : (resultadoConciliacion.kpis.sistema.total_debito_fiscal === 0 ? 'warning--text text--darken-2' : 'warning--text text--darken-2')">
                    <v-icon x-small :color="resultadoConciliacion.kpis.sin.total_debito_fiscal === resultadoConciliacion.kpis.sistema.total_debito_fiscal && resultadoConciliacion.kpis.sistema.total_debito_fiscal > 0 ? 'success' : 'warning'">
                      {{ resultadoConciliacion.kpis.sin.total_debito_fiscal === resultadoConciliacion.kpis.sistema.total_debito_fiscal && resultadoConciliacion.kpis.sistema.total_debito_fiscal > 0 ? 'mdi-check-circle' : 'mdi-alert' }}
                    </v-icon>
                    <span v-if="resultadoConciliacion.kpis.sin.total_debito_fiscal === resultadoConciliacion.kpis.sistema.total_debito_fiscal && resultadoConciliacion.kpis.sistema.total_debito_fiscal > 0">
                      Base Imponible Idéntica
                    </span>
                    <span v-else-if="resultadoConciliacion.kpis.sistema.total_debito_fiscal === 0">
                      Débito Fiscal Sistema: Bs 0.00
                    </span>
                    <span v-else>
                      Diferencia: Bs {{ formatoMoneda(Math.abs(resultadoConciliacion.kpis.sin.total_debito_fiscal - resultadoConciliacion.kpis.sistema.total_debito_fiscal)) }}
                    </span>
                  </div>
                </v-card>
              </v-col>
            </v-row>

            <!-- EXPLICACIÓN TÉCNICA DINÁMICA DEL DIAGNÓSTICO -->
            <v-alert
              dense
              outlined
              :color="resultadoConciliacion.kpis.diagnostico ? (resultadoConciliacion.kpis.diagnostico.color || 'indigo') : 'indigo'"
              class="mb-4 white text-caption"
              rounded="lg"
            >
              <div class="font-weight-bold mb-1" :class="(resultadoConciliacion.kpis.diagnostico ? (resultadoConciliacion.kpis.diagnostico.color || 'indigo') : 'indigo') + '--text'">
                <v-icon small :color="resultadoConciliacion.kpis.diagnostico ? (resultadoConciliacion.kpis.diagnostico.color || 'indigo') : 'indigo'">
                  {{ (resultadoConciliacion.kpis.diagnostico && resultadoConciliacion.kpis.diagnostico.color === 'error') ? 'mdi-alert-octagon' : ((resultadoConciliacion.kpis.diagnostico && resultadoConciliacion.kpis.diagnostico.color === 'warning') ? 'mdi-alert-circle' : 'mdi-information') }}
                </v-icon>
                {{ resultadoConciliacion.kpis.diagnostico ? resultadoConciliacion.kpis.diagnostico.titulo : 'Diagnóstico Fiscal' }}
              </div>
              <p class="mb-1 text-secondary">
                {{ resultadoConciliacion.kpis.diagnostico ? resultadoConciliacion.kpis.diagnostico.descripcion : '' }}
              </p>
              <div class="mb-2 text-caption text-secondary" v-if="resultadoConciliacion.kpis.diagnostico && resultadoConciliacion.kpis.diagnostico.nota_excel">
                <v-icon x-small color="grey darken-1">mdi-help-circle-outline</v-icon>
                <em>{{ resultadoConciliacion.kpis.diagnostico.nota_excel }}</em>
              </div>
              <div class="font-weight-medium text-caption" :class="(resultadoConciliacion.kpis.diagnostico ? (resultadoConciliacion.kpis.diagnostico.color || 'indigo') : 'indigo') + '--text'">
                <strong>Acción sugerida:</strong> {{ resultadoConciliacion.kpis.diagnostico ? resultadoConciliacion.kpis.diagnostico.accion_sugerida : '' }}
              </div>
            </v-alert>

            <!-- PESTAÑAS DE DETALLE DE FACTURAS -->
            <v-card outlined class="white" rounded="lg">
              <v-tabs v-model="tabCotejo" color="indigo darken-3" dense>
                <!-- Pestaña Válidas Faltantes (si existen) -->
                <v-tab class="text-capitalize font-weight-bold" v-if="resultadoConciliacion.detalles.validas_faltantes && resultadoConciliacion.detalles.validas_faltantes.length > 0">
                  <v-badge
                    :content="resultadoConciliacion.detalles.validas_faltantes.length.toString()"
                    :value="resultadoConciliacion.detalles.validas_faltantes.length"
                    color="error"
                    inline
                  >
                    Válidas en SIN ausentes en Sistema
                  </v-badge>
                </v-tab>
                <v-tab class="text-capitalize font-weight-bold">
                  <v-badge
                    :content="resultadoConciliacion.detalles.anuladas_faltantes.length.toString()"
                    :value="resultadoConciliacion.detalles.anuladas_faltantes.length"
                    color="warning darken-2"
                    inline
                  >
                    Anuladas en SIN ausentes en Sistema
                  </v-badge>
                </v-tab>
                <v-tab class="text-capitalize font-weight-bold">
                  <v-badge
                    :content="resultadoConciliacion.detalles.diferencias_ley1886.length.toString()"
                    :value="resultadoConciliacion.detalles.diferencias_ley1886.length"
                    color="info"
                    inline
                  >
                    Descuentos Ley 1886 (Neto vs Bruto)
                  </v-badge>
                </v-tab>
                <v-tab class="text-capitalize font-weight-bold">
                  Coincidentes Exactas ({{ resultadoConciliacion.kpis.resumen_cotejo.coincidentes_exactas }})
                </v-tab>
              </v-tabs>

              <v-divider></v-divider>

              <v-tabs-items v-model="tabCotejo">
                <!-- TAB VÁLIDAS FALTANTES (SI APLICA) -->
                <v-tab-item v-if="resultadoConciliacion.detalles.validas_faltantes && resultadoConciliacion.detalles.validas_faltantes.length > 0">
                  <div class="pa-3">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <div class="text-caption font-weight-bold text-secondary">
                        Facturas válidas emitidas en el SIN que no existen en el sistema (requieren importación):
                      </div>
                      <v-btn
                        color="primary"
                        small
                        dark
                        class="font-weight-bold rounded-pill elevation-1"
                        :loading="cargandoSincronizacion"
                        @click="confirmarSincronizacion"
                      >
                        <v-icon left small>mdi-cloud-download</v-icon> Importar Facturas del SIN
                      </v-btn>
                    </div>

                    <v-data-table
                      :headers="headersValidasFaltantes"
                      :items="resultadoConciliacion.detalles.validas_faltantes"
                      dense
                      :items-per-page="10"
                      class="elevation-0"
                    >
                      <template v-slot:item.numero_factura="{ item }">
                        <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
                      </template>
                      <template v-slot:item.monto_total="{ item }">
                        <span class="font-weight-bold">Bs {{ formatoMoneda(item.monto_total) }}</span>
                      </template>
                      <template v-slot:item.debito_fiscal="{ item }">
                        <span class="font-weight-bold info--text">Bs {{ formatoMoneda(item.debito_fiscal) }}</span>
                      </template>
                      <template v-slot:item.cuf_sin="{ item }">
                        <span class="text-caption font-mono text-truncate d-inline-block" style="max-width: 140px;" :title="item.cuf_sin">
                          {{ item.cuf_sin }}
                        </span>
                      </template>
                    </v-data-table>
                  </div>
                </v-tab-item>

                <!-- TAB 1: ANULADAS FALTANTES -->
                <v-tab-item>
                  <div class="pa-3">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <div class="text-caption font-weight-bold text-secondary">
                        Listado de facturas anuladas oficiales emitidas en el SIN que no figuraban en el archivo FoxPro de origen:
                      </div>
                      <v-btn
                        v-if="resultadoConciliacion.detalles.anuladas_faltantes.length > 0"
                        color="success darken-1"
                        small
                        dark
                        class="font-weight-bold rounded-pill elevation-1"
                        :loading="cargandoSincronizacion"
                        @click="confirmarSincronizacion"
                      >
                        <v-icon left small>mdi-cloud-sync</v-icon> Sincronizar y Regularizar Estados con el SIN
                      </v-btn>
                    </div>

                    <v-data-table
                      :headers="headersAnuladas"
                      :items="resultadoConciliacion.detalles.anuladas_faltantes"
                      dense
                      :items-per-page="10"
                      class="elevation-0"
                      no-data-text="No hay facturas anuladas pendientes de sincronizar. ¡Todo está en orden!"
                    >
                      <template v-slot:item.numero_factura="{ item }">
                        <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
                      </template>
                      <template v-slot:item.monto_total="{ item }">
                        <span class="font-weight-bold">Bs {{ formatoMoneda(item.monto_total) }}</span>
                      </template>
                      <template v-slot:item.cuf_sin="{ item }">
                        <span class="text-caption font-mono text-truncate d-inline-block" style="max-width: 140px;" :title="item.cuf_sin">
                          {{ item.cuf_sin }}
                        </span>
                      </template>
                      <template v-slot:item.explicacion="{ item }">
                        <div class="d-flex align-center">
                          <v-chip x-small color="info" text-color="white" class="mr-1 font-weight-bold" v-if="item.tiene_reemision_valida">
                            Reemitida
                          </v-chip>
                          <span class="text-caption">{{ item.explicacion }}</span>
                        </div>
                      </template>
                    </v-data-table>
                  </div>
                </v-tab-item>

                <!-- TAB 2: LEY 1886 -->
                <v-tab-item>
                  <div class="pa-3">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <div class="text-caption font-weight-bold text-secondary">
                        Facturas con beneficio de adulto mayor (50% de descuento) donde FoxPro grabó el importe neto:
                      </div>
                      <v-btn
                        v-if="resultadoConciliacion.detalles.diferencias_ley1886.length > 0"
                        color="success darken-1"
                        small
                        dark
                        class="font-weight-bold rounded-pill elevation-1"
                        :loading="cargandoSincronizacion"
                        @click="confirmarSincronizacion"
                      >
                        <v-icon left small>mdi-check-all</v-icon> Regularizar Desglose Bruto/Neto
                      </v-btn>
                    </div>

                    <v-data-table
                      :headers="headersLey1886"
                      :items="resultadoConciliacion.detalles.diferencias_ley1886"
                      dense
                      :items-per-page="10"
                      class="elevation-0"
                    >
                      <template v-slot:item.numero_factura="{ item }">
                        <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
                      </template>
                      <template v-slot:item.monto_sin_bruto="{ item }">
                        <span class="font-weight-bold success--text">Bs {{ formatoMoneda(item.monto_sin_bruto) }}</span>
                      </template>
                      <template v-slot:item.descuento_sin="{ item }">
                        <span class="font-weight-bold warning--text text--darken-2">Bs {{ formatoMoneda(item.descuento_sin) }}</span>
                      </template>
                      <template v-slot:item.base_debito_sin="{ item }">
                        <span class="font-weight-bold info--text">Bs {{ formatoMoneda(item.base_debito_sin) }}</span>
                      </template>
                      <template v-slot:item.monto_sistema="{ item }">
                        <span class="font-weight-bold secondary--text">Bs {{ formatoMoneda(item.monto_sistema) }}</span>
                      </template>
                    </v-data-table>
                  </div>
                </v-tab-item>

                <!-- TAB 3: COINCIDENTES -->
                <v-tab-item>
                  <div class="pa-3">
                    <div class="text-caption font-weight-bold text-secondary mb-2">
                      Muestra de facturas con validación idéntica entre el SIN y SIM-EMAPAP:
                    </div>

                    <v-data-table
                      :headers="headersCoincidentes"
                      :items="resultadoConciliacion.detalles.coincidentes_muestra"
                      dense
                      :items-per-page="10"
                      class="elevation-0"
                    >
                      <template v-slot:item.numero_factura="{ item }">
                        <span class="font-weight-bold primary--text">#{{ item.numero_factura }}</span>
                      </template>
                      <template v-slot:item.monto_total="{ item }">
                        <span class="font-weight-bold">Bs {{ formatoMoneda(item.monto_total) }}</span>
                      </template>
                      <template v-slot:item.debito_fiscal="{ item }">
                        <span class="font-weight-bold info--text">Bs {{ formatoMoneda(item.debito_fiscal) }}</span>
                      </template>
                      <template v-slot:item.estado="{ item }">
                        <v-chip x-small color="success" text-color="white" class="font-weight-bold">
                          <v-icon left x-small>mdi-check</v-icon> {{ item.estado_sin }}
                        </v-chip>
                      </template>
                    </v-data-table>
                  </div>
                </v-tab-item>
              </v-tabs-items>
            </v-card>
          </div>

          <!-- ESTADO INICIAL: ESPERANDO ARCHIVO -->
          <v-card outlined class="pa-10 text-center white my-2" rounded="lg" v-else>
            <v-avatar color="indigo lighten-5" size="80" class="mb-3">
              <v-icon size="44" color="indigo darken-2">mdi-file-upload-outline</v-icon>
            </v-avatar>
            <h3 class="text-subtitle-1 font-weight-bold grey--text text--darken-3 mb-1">
              Esperando archivo Excel del SIN
            </h3>
            <p class="text-caption text-secondary mb-3 mx-auto" style="max-width: 520px;">
              Haga clic en <strong>"Cargar archivo Excel del SIN"</strong> para seleccionar su archivo oficial en formato <code>.xlsx</code> o <code>.xls</code> descargado del portal SIAT, y luego presione <strong>"Ejecutar Cotejo Fiscal"</strong>.
            </p>
            <div class="d-inline-flex align-center text-caption text-secondary grey lighten-4 px-3 py-1 rounded-pill">
              <v-icon x-small color="success" class="mr-1">mdi-shield-check</v-icon>
              Auditoría segura: La comparación no altera la base de datos hasta que decida sincronizar.
            </div>
          </v-card>
        </v-card-text>

        <!-- ACCIONES DEL MODAL -->
        <v-divider></v-divider>
        <v-card-actions class="pa-3 d-flex justify-space-between align-center">
          <div class="text-caption text-secondary">
            EMAPAP Patacamaya — Módulo de Conciliación Tributaria SIAT
          </div>

          <div class="d-flex gap-2">
            <v-btn
              v-if="resultadoConciliacion && ((resultadoConciliacion.detalles.validas_faltantes && resultadoConciliacion.detalles.validas_faltantes.length > 0) || resultadoConciliacion.detalles.anuladas_faltantes.length > 0 || resultadoConciliacion.detalles.diferencias_ley1886.length > 0)"
              color="success darken-1"
              dark
              class="font-weight-bold rounded-pill elevation-2"
              :loading="cargandoSincronizacion"
              @click="confirmarSincronizacion"
            >
              <v-icon left small>mdi-cloud-sync</v-icon>
              {{ (resultadoConciliacion.detalles.validas_faltantes && resultadoConciliacion.detalles.validas_faltantes.length > 0) ? 'Importar Facturas Oficiales del SIN (' + resultadoConciliacion.kpis.sin.total_registros + ')' : 'Sincronizar y Regularizar con el SIN' }}
            </v-btn>

            <v-btn
              outlined
              color="secondary"
              class="font-weight-bold rounded-pill"
              @click="cerrarDialogoConciliador"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- CONFIRMACIÓN DE SINCRONIZACIÓN -->
    <v-dialog v-model="dialogoConfirmarSync" max-width="580px" persistent>
      <v-card rounded="lg" v-if="resultadoConciliacion">
        <v-card-title class="success darken-1 text-white py-3 px-4">
          <v-icon left color="white">mdi-shield-check</v-icon> Confirmar Regularización Fiscal SIN
        </v-card-title>
        <v-card-text class="pa-4 text-body-2">
          <p class="mb-2">
            Esta acción sincronizará los registros de facturación de SIM-EMAPAP con el reporte oficial del Servicio de Impuestos Nacionales (SIN):
          </p>
          <ul class="mb-3">
            <li class="mb-1" v-if="resultadoConciliacion.detalles.validas_faltantes && resultadoConciliacion.detalles.validas_faltantes.length > 0">
              <strong>Importar {{ resultadoConciliacion.detalles.validas_faltantes.length }} facturas válidas del SIN:</strong>
              Se insertarán como facturas válidas oficiales con sus respectivos CUF y montos.
            </li>
            <li class="mb-1" v-if="resultadoConciliacion.detalles.anuladas_faltantes && resultadoConciliacion.detalles.anuladas_faltantes.length > 0">
              <strong>Importar {{ resultadoConciliacion.detalles.anuladas_faltantes.length }} facturas anuladas oficiales:</strong>
              Se registrarán con su CUF original del SIAT y base imponible Bs 0.00 para cuadrar el Libro de Ventas.
            </li>
            <li class="mb-1" v-if="resultadoConciliacion.detalles.diferencias_ley1886 && resultadoConciliacion.detalles.diferencias_ley1886.length > 0">
              <strong>Ajustar {{ resultadoConciliacion.detalles.diferencias_ley1886.length }} facturas Ley 1886:</strong>
              Se actualizará el desglose de importe bruto y descuento según la normativa RND del SIN.
            </li>
          </ul>
          <p class="text-caption text-secondary mb-0">
            Esta operación es 100% segura, reversible y no modifica los cobros netos realizados en ventanilla.
          </p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-3 justify-end gap-2">
          <v-btn text color="secondary" @click="dialogoConfirmarSync = false">Cancelar</v-btn>
          <v-btn
            color="success darken-1"
            dark
            class="font-weight-bold rounded-pill px-4"
            :loading="cargandoSincronizacion"
            @click="ejecutarSincronizacion"
          >
            Confirmar e Importar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- NOTIFICACIONES TOAST (SNACKBAR) -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.show = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReporteLibroVentas',
  data() {
    const hoy = new Date();
    const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().substr(0, 10);
    const hoyStr = hoy.toISOString().substr(0, 10);

    return {
      cargando: false,
      cargandoCsv: false,
      periodoTipo: 'mes',
      filtros: {
        fecha_desde: primerDiaMes,
        fecha_hasta: hoyStr,
        estado: null,
        tipo_emision: null,
      },
      estadosList: [
        { text: 'Todos los estados fiscales', value: null },
        { text: 'Válidas en Línea (V)', value: 'VALIDADA' },
        { text: 'Contingencia / Fuera de Línea (E)', value: 'CONTINGENCIA' },
        { text: 'Anuladas (A)', value: 'ANULADA' },
        { text: 'Rechazadas / Observadas (R)', value: 'RECHAZADA' },
      ],
      tiposEmisionList: [
        { text: 'Todas las modalidades', value: null },
        { text: 'En Línea (1)', value: 1 },
        { text: 'Fuera de Línea / Contingencia (2)', value: 2 },
      ],
      resumen: {
        total_registros: 0,
        cantidad_validas: 0,
        cantidad_contingencias: 0,
        cantidad_anuladas: 0,
        cantidad_rechazadas: 0,
        total_facturado: 0,
        total_descuento: 0,
        total_base_debito_fiscal: 0,
        debito_fiscal_iva: 0,
      },
      facturas: [],
      headers: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha y Hora', value: 'fecha_emision', width: '130px' },
        { text: 'NIT / C.I.', value: 'documento', width: '110px' },
        { text: 'Abonado / Razón Social', value: 'nombre_razon_social' },
        { text: 'Modalidad', value: 'tipo_emision', align: 'center', width: '130px' },
        { text: 'CUF (Autorización)', value: 'cuf', width: '170px' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '110px' },
        { text: 'Base Sujeta IVA', value: 'monto_total_sujeto_iva', align: 'end', width: '125px' },
        { text: 'Débito Fiscal (13%)', value: 'debito_fiscal', align: 'end', width: '130px' },
        { text: 'Estado SIN', value: 'estado_factura', align: 'center', width: '135px' },
        { text: 'SIAT', value: 'acciones', align: 'center', sortable: false, width: '70px' },
      ],

      // MODAL DE CONCILIACIÓN
      dialogoConciliador: false,
      cargandoCotejo: false,
      cargandoSincronizacion: false,
      dialogoConfirmarSync: false,
      archivoSinUpload: null,
      archivoServidorSeleccionado: null,
      archivoDetectado: null,
      resultadoConciliacion: null,
      tabCotejo: 0,
      filtroCotejoFechaDesde: '',
      filtroCotejoFechaHasta: '',

      headersValidasFaltantes: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha y Hora', value: 'fecha', width: '130px' },
        { text: 'NIT / C.I.', value: 'nit', width: '100px' },
        { text: 'Cliente', value: 'cliente' },
        { text: 'CUF del SIN', value: 'cuf_sin', width: '160px' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '100px' },
        { text: 'Débito Fiscal', value: 'debito_fiscal', align: 'end', width: '100px' },
      ],

      headersAnuladas: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha y Hora', value: 'fecha', width: '110px' },
        { text: 'NIT / C.I.', value: 'nit', width: '90px' },
        { text: 'Cliente', value: 'cliente' },
        { text: 'CUF del SIN', value: 'cuf_sin', width: '160px' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '100px' },
        { text: 'Diagnóstico Reemisión', value: 'explicacion' },
      ],

      headersLey1886: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha', value: 'fecha', width: '100px' },
        { text: 'Cliente (Tercera Edad)', value: 'cliente' },
        { text: 'Total SIN (Bruto)', value: 'monto_sin_bruto', align: 'end', width: '110px' },
        { text: 'Descuento Ley 1886', value: 'descuento_sin', align: 'end', width: '120px' },
        { text: 'Base Débito Fiscal', value: 'base_debito_sin', align: 'end', width: '120px' },
        { text: 'Monto en Sistema (Neto)', value: 'monto_sistema', align: 'end', width: '130px' },
      ],

      headersCoincidentes: [
        { text: 'N° Factura', value: 'numero_factura', align: 'center', width: '90px' },
        { text: 'Fecha', value: 'fecha', width: '100px' },
        { text: 'NIT / C.I.', value: 'nit', width: '90px' },
        { text: 'Cliente', value: 'cliente' },
        { text: 'Total Venta', value: 'monto_total', align: 'end', width: '110px' },
        { text: 'Débito Fiscal', value: 'debito_fiscal', align: 'end', width: '110px' },
        { text: 'Estado Fiscal', value: 'estado', align: 'center', width: '100px' },
      ],

      snackbar: {
        show: false,
        text: '',
        color: 'success',
      },
    };
  },
  mounted() {
    this.consultarReporte();
  },
  methods: {
    cambiarPeriodoRapido(val) {
      const hoy = new Date();
      const format = d => d.toISOString().substr(0, 10);

      if (val === 'hoy') {
        this.filtros.fecha_desde = format(hoy);
        this.filtros.fecha_hasta = format(hoy);
      } else if (val === 'semana') {
        const d = new Date(hoy);
        const diaSemana = d.getDay() || 7;
        d.setDate(d.getDate() - diaSemana + 1);
        this.filtros.fecha_desde = format(d);
        const finSem = new Date(d);
        finSem.setDate(finSem.getDate() + 6);
        this.filtros.fecha_hasta = format(finSem);
      } else if (val === 'mes') {
        const iniMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        this.filtros.fecha_desde = format(iniMes);
        this.filtros.fecha_hasta = format(finMes);
      } else if (val === 'mes_anterior') {
        const iniMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        const finMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        this.filtros.fecha_desde = format(iniMesAnt);
        this.filtros.fecha_hasta = format(finMesAnt);
      } else if (val === 'agosto_2026') {
        this.filtros.fecha_desde = '2026-08-01';
        this.filtros.fecha_hasta = '2026-08-31';
      } else if (val === 'septiembre_2026') {
        this.filtros.fecha_desde = '2026-09-01';
        this.filtros.fecha_hasta = '2026-09-30';
      }
      if (val !== 'personalizado') {
        this.consultarReporte();
      }
    },

    onFechaManualChange() {
      this.periodoTipo = 'personalizado';
      this.consultarReporte();
    },

    async consultarReporte() {
      this.cargando = true;
      try {
        const params = { ...this.filtros, limite: 10000 };
        const res = await axios.get('/api/facturacion/reportes/libro-ventas', { params });
        if (res.data && res.data.success) {
          this.facturas = res.data.data;
          this.resumen = res.data.resumen;
        }
      } catch (error) {
        console.error('Error al consultar el libro de ventas:', error);
      } finally {
        this.cargando = false;
      }
    },

    async descargarCsv() {
      this.cargandoCsv = true;
      try {
        const params = {
          fecha_desde: this.filtros.fecha_desde,
          fecha_hasta: this.filtros.fecha_hasta,
          estado: this.filtros.estado,
          tipo_emision: this.filtros.tipo_emision,
        };
        const response = await axios.get('/api/facturacion/reportes/libro-ventas/csv', {
          params,
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Libro_Ventas_IVA_${this.filtros.fecha_desde}_al_${this.filtros.fecha_hasta}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error al descargar el archivo CSV:', error);
      } finally {
        this.cargandoCsv = false;
      }
    },

    limpiarFiltros() {
      const hoy = new Date();
      this.periodoTipo = 'mes';
      this.filtros.fecha_desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().substr(0, 10);
      this.filtros.fecha_hasta = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().substr(0, 10);
      this.filtros.estado = null;
      this.filtros.tipo_emision = null;
      this.consultarReporte();
    },

    imprimirReporte() {
      window.print();
    },

    // MÉTODOS DEL COTEJADOR TRIBUTARIO
    abrirDialogoConciliador() {
      this.dialogoConciliador = true;
      this.resultadoConciliacion = null;
      this.archivoSinUpload = null;
      this.archivoServidorSeleccionado = null;
    },

    cerrarDialogoConciliador() {
      this.dialogoConciliador = false;
      this.resultadoConciliacion = null;
      this.archivoSinUpload = null;
      this.archivoServidorSeleccionado = null;
      this.filtroCotejoFechaDesde = '';
      this.filtroCotejoFechaHasta = '';
    },

    async ejecutarCotejo() {
      if (!this.archivoSinUpload) {
        this.mostrarNotificacion('Por favor seleccione un archivo Excel del SIN primero.', 'warning');
        return;
      }

      this.cargandoCotejo = true;
      try {
        const formData = new FormData();
        formData.append('archivo', this.archivoSinUpload);
        if (this.filtroCotejoFechaDesde) {
          formData.append('fecha_desde', this.filtroCotejoFechaDesde);
        }
        if (this.filtroCotejoFechaHasta) {
          formData.append('fecha_hasta', this.filtroCotejoFechaHasta);
        }

        const res = await axios.post('/api/facturacion/reportes/libro-ventas/conciliar-sin', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (res && res.data && res.data.success) {
          this.resultadoConciliacion = res.data;
          this.mostrarNotificacion('Cotejo tributario realizado con éxito', 'success');
        } else {
          this.mostrarNotificacion(res?.data?.message || 'Error al cotejar archivo', 'error');
        }
      } catch (err) {
        console.error('Error al cotejar archivo SIN:', err);
        this.mostrarNotificacion(err.response?.data?.message || 'Ocurrió un error al procesar el archivo Excel', 'error');
      } finally {
        this.cargandoCotejo = false;
      }
    },

    confirmarSincronizacion() {
      this.dialogoConfirmarSync = true;
    },

    async ejecutarSincronizacion() {
      this.cargandoSincronizacion = true;
      try {
        let res;
        if (this.archivoSinUpload) {
          const formData = new FormData();
          formData.append('archivo', this.archivoSinUpload);
          formData.append('importar_validas', '1');
          formData.append('importar_anuladas', '1');
          formData.append('regularizar_ley1886', '1');
          if (this.filtroCotejoFechaDesde) {
            formData.append('fecha_desde', this.filtroCotejoFechaDesde);
          }
          if (this.filtroCotejoFechaHasta) {
            formData.append('fecha_hasta', this.filtroCotejoFechaHasta);
          }
          res = await axios.post('/api/facturacion/reportes/libro-ventas/sincronizar-sin', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });
        } else {
          res = await axios.post('/api/facturacion/reportes/libro-ventas/sincronizar-sin', {
            ruta_archivo: this.archivoServidorSeleccionado,
            importar_validas: true,
            importar_anuladas: true,
            regularizar_ley1886: true,
            fecha_desde: this.filtroCotejoFechaDesde || null,
            fecha_hasta: this.filtroCotejoFechaHasta || null,
          });
        }

        if (res && res.data && res.data.success) {
          this.dialogoConfirmarSync = false;
          this.mostrarNotificacion(res.data.mensaje, 'success');
          // Actualizar cotejo para reflejar la sincronización
          await this.ejecutarCotejo();
          // Recargar la tabla principal
          await this.consultarReporte();
        } else {
          this.mostrarNotificacion(res?.data?.message || 'Error en la sincronización', 'error');
        }
      } catch (err) {
        console.error('Error al sincronizar con SIN:', err);
        this.mostrarNotificacion(err.response?.data?.message || 'Error al regularizar datos con el SIN', 'error');
      } finally {
        this.cargandoSincronizacion = false;
      }
    },

    mostrarNotificacion(texto, color = 'success') {
      this.snackbar.text = texto;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },

    formatoMoneda(val) {
      if (!val) return '0.00';
      return parseFloat(val).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    formatearFechaHora(val) {
      if (!val) return '';
      const d = new Date(val);
      return d.toLocaleDateString('es-BO') + ' ' + d.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
    },

    formatearFecha(val) {
      if (!val) return '';
      if (typeof val === 'string' && val.includes('-')) {
        const partes = val.split('T')[0].split('-');
        if (partes.length === 3) {
          return `${parseInt(partes[2], 10)}/${parseInt(partes[1], 10)}/${partes[0]}`;
        }
      }
      const d = new Date(val);
      return d.toLocaleDateString('es-BO');
    },

    esContingencia(item) {
      return (
        item.tipo_emision === 2 ||
        item.estado_factura === 'CONTINGENCIA' ||
        item.estado_factura === 'OFFLINE_PENDIENTE' ||
        item.estado_factura === 'OFFLINE_REGULARIZADA'
      );
    },

    colorEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
        case 'VALIDATED':
          return 'success';
        case 'ANULADA':
        case 'CANCELLED':
          return 'error';
        case 'CONTINGENCIA':
        case 'OFFLINE_PENDIENTE':
          return 'warning darken-1';
        case 'OFFLINE_REGULARIZADA':
          return 'teal darken-1';
        case 'RECHAZADA':
        case 'OBSERVADA':
          return 'deep-orange darken-1';
        default:
          return 'info';
      }
    },

    iconoEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
          return 'mdi-check-circle';
        case 'ANULADA':
          return 'mdi-cancel';
        case 'CONTINGENCIA':
        case 'OFFLINE_PENDIENTE':
          return 'mdi-alert-circle-outline';
        case 'OFFLINE_REGULARIZADA':
          return 'mdi-cloud-check';
        case 'RECHAZADA':
        case 'OBSERVADA':
          return 'mdi-close-octagon';
        default:
          return 'mdi-information-outline';
      }
    },

    labelEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
          return 'VÁLIDA (V)';
        case 'ANULADA':
          return 'ANULADA (A)';
        case 'CONTINGENCIA':
        case 'OFFLINE_PENDIENTE':
          return 'CONTINGENCIA (E)';
        case 'OFFLINE_REGULARIZADA':
          return 'REGULARIZADA (V)';
        case 'RECHAZADA':
          return 'RECHAZADA (R)';
        default:
          return estado || 'VÁLIDA (V)';
      }
    },

    abrirPortalSiat(item) {
      if (item.representacion_grafica_qr) {
        window.open(item.representacion_grafica_qr, '_blank');
      } else {
        const nit = '1002393029';
        const url = `https://siat.impuestos.gob.bo/consulta/QR?nit=${nit}&cuf=${item.cuf}&numero=${item.numero_factura}&t=2`;
        window.open(url, '_blank');
      }
    },

    copiarTexto(txt) {
      if (!txt) return;
      navigator.clipboard.writeText(txt);
      this.mostrarNotificacion('CUF copiado al portapapeles', 'info');
    },
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  .reporte-libro-ventas, .reporte-libro-ventas * {
    visibility: visible;
  }
  .reporte-libro-ventas {
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
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.07);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.theme--dark .erp-card-elevated {
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.35);
}
.dialog-conciliador {
  border-radius: 12px;
  overflow: hidden;
}
</style>
