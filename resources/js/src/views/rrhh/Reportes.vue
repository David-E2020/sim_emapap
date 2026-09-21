<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-file-chart-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Centro de Reportes y Planillas Oficiales</h2>
            <span class="text-caption text-secondary">
              Planillas salariales, boletas de pago, padrón institucional, kardex, refrigerios y reportes personalizados
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="primary"
            dark
            outlined
            class="rounded-pill font-weight-medium"
            @click="imprimirPestanaActual"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir Vista
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS DIRECTAS SOBRE EL LIENZO (ESTÁNDAR ERP) -->
    <v-tabs v-model="activeTab" color="primary" class="mb-4" show-arrows>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-currency-usd</v-icon> Planilla de Sueldos</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-chart-box-outline</v-icon> Consolidado Asistencia</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-account-group</v-icon> Padrón de Personal</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-food</v-icon> Planilla Refrigerios</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-beach</v-icon> Kardex Vacaciones</v-tab>
      <v-tab class="font-weight-bold"><v-icon left small>mdi-tune</v-icon> Constructor Personalizado</v-tab>
    </v-tabs>

    <v-tabs-items v-model="activeTab">
      <!-- ========================================== -->
      <!-- PESTAÑA 0: PLANILLA DE SUELDOS Y SALARIOS  -->
      <!-- ========================================== -->
      <v-tab-item>
        <!-- FILTROS AVANZADOS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="d-flex align-center flex-wrap gap-2">
              <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO DE PROCESO:</span>
              <v-btn-toggle v-model="periodoPlanillaPreset" mandatory dense color="primary" @change="cambiarMesPreset">
                <v-btn small value="actual" class="text-capitalize">Mes Actual</v-btn>
                <v-btn small value="anterior" class="text-capitalize">Mes Anterior</v-btn>
                <v-btn small value="personalizado" class="text-capitalize">Otro Mes</v-btn>
              </v-btn-toggle>
            </div>

            <!-- BOTONES DE ACCIÓN Y EXPORTACIÓN -->
            <div class="d-flex align-center gap-2 flex-wrap">
              <v-btn
                v-if="!esDeclarada && planillaSueldos.length > 0"
                color="purple darken-1"
                dark
                small
                class="rounded-pill font-weight-medium"
                :loading="cerrandoPlanilla"
                @click="cerrarYDeclarar()"
              >
                <v-icon left small>mdi-lock-check</v-icon> Cerrar y Declarar Planilla
              </v-btn>

              <v-btn
                color="primary"
                dark
                small
                class="rounded-pill font-weight-medium"
                @click="imprimirArea()"
              >
                <v-icon left small>mdi-file-pdf-box</v-icon> Planilla Oficial PDF
              </v-btn>
              <v-btn
                color="indigo darken-1"
                dark
                small
                outlined
                class="rounded-pill font-weight-medium"
                @click="exportarCsvPlanilla"
              >
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel/CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="filtroMes"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes de Planilla"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar-month"
                @change="cargarPlanillaSueldos()"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4" md="3">
              <v-text-field
                v-model="filtroAnio"
                label="Gestión Fiscal"
                type="number"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar"
                @change="cargarPlanillaSueldos()"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="4" md="3" class="d-flex align-center">
              <v-chip v-if="esDeclarada" color="purple lighten-5" text-color="purple darken-3" label class="font-weight-bold">
                <v-icon left small color="purple darken-3">mdi-shield-lock</v-icon>
                INMUTABLE (CITE: {{ citeOficial }})
              </v-chip>
              <v-chip v-else color="amber lighten-5" text-color="amber darken-4" label class="font-weight-bold">
                <v-icon left small color="amber darken-4">mdi-timer-sand</v-icon>
                BORRADOR (Simulación en Vivo)
              </v-chip>
            </v-col>

            <v-col cols="12" sm="12" md="3" class="d-flex justify-end">
              <v-btn
                color="primary"
                dark
                class="rounded-pill w-100 font-weight-bold"
                :loading="loadingSueldos"
                @click="cargarPlanillaSueldos()"
              >
                <v-icon left small>mdi-filter-check</v-icon> Consultar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS DE MÉTRICAS / KPIS (ESTILO REPORTES COMERCIALES) -->
        <v-row dense class="mb-4">
          <!-- TOTAL GANADO BRUTO -->
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Ganado Bruto</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                Bs {{ formatoMoneda(resumenSueldos.total_ganado_bs) }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Haberes básicos + antigüedad
              </div>
            </v-card>
          </v-col>

          <!-- DESCUENTOS GESTORA -->
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Aportes Gestora (12.71%)</div>
              <div class="text-h4 font-weight-black error--text mt-1">
                Bs {{ formatoMoneda(resumenSueldos.total_descuentos_bs) }}
              </div>
              <div class="text-caption error--text text--darken-2 mt-1 font-weight-medium">
                Retenciones de ley laboral
              </div>
            </v-card>
          </v-col>

          <!-- TOTAL LÍQUIDO PAGABLE -->
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Líquido Pagable Total</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                Bs {{ formatoMoneda(resumenSueldos.total_planilla_bs) }}
              </div>
              <div class="text-caption text-primary mt-1 font-weight-medium">
                Desembolso neto a funcionarios
              </div>
            </v-card>
          </v-col>

          <!-- ESTADO PLANILLA / AUDITORÍA -->
          <v-col cols="12" sm="6" md="3">
            <v-card
              class="pa-3 text-center erp-card-elevated"
              rounded="lg"
              :class="esDeclarada ? 'bg-success-light' : 'bg-warning-light'"
            >
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Estado Institucional</div>
              <div
                class="text-h4 font-weight-black mt-1"
                :class="esDeclarada ? 'success--text' : 'warning--text text--darken-3'"
              >
                {{ esDeclarada ? 'DECLARADA' : 'BORRADOR' }}
              </div>
              <div class="mt-1">
                <v-chip
                  x-small
                  :color="esDeclarada ? 'success' : 'warning'"
                  text-color="white"
                  class="font-weight-bold"
                >
                  {{ esDeclarada ? (citeOficial || 'OFICIAL CONGELADA') : 'EN REVISIÓN ACTIVA' }}
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA DE SUELDOS CON TFOOT TOTALIZADOR -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-3 px-4 d-flex justify-space-between align-center">
            <div class="font-weight-bold text-subtitle-1">
              <v-icon left color="primary">mdi-table</v-icon>
              Detalle Salarial por Funcionario (Gestión {{ filtroMes }}/{{ filtroAnio }})
            </div>
            <v-chip small color="primary" outlined class="font-weight-bold">
              {{ planillaSueldos.length }} funcionarios computados
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>

          <v-data-table
            :headers="headersPlanillaSueldos"
            :items="planillaSueldos"
            :loading="loadingSueldos"
            class="erp-table"
            dense
            :items-per-page="25"
          >
            <template v-slot:item.haber_basico="{ item }">
              <span>Bs {{ formatoMoneda(item.haber_basico) }}</span>
            </template>
            <template v-slot:item.bono_antiguedad="{ item }">
              <span class="text-primary font-weight-medium">Bs {{ formatoMoneda(item.bono_antiguedad) }} ({{ item.anios_antiguedad }}a)</span>
            </template>
            <template v-slot:item.total_ganado="{ item }">
              <span class="font-weight-bold">Bs {{ formatoMoneda(item.total_ganado) }}</span>
            </template>
            <template v-slot:item.gestora_12_71="{ item }">
              <span class="error--text">Bs {{ formatoMoneda(item.gestora_12_71) }}</span>
            </template>
            <template v-slot:item.refrigerio_bs="{ item }">
              <span class="success--text font-weight-medium">+ Bs {{ formatoMoneda(item.refrigerio_bs) }}</span>
            </template>
            <template v-slot:item.liquido_pagable_total="{ item }">
              <v-chip small color="success lighten-5" text-color="green darken-3" label class="font-weight-bold">
                Bs {{ formatoMoneda(item.liquido_pagable_total) }}
              </v-chip>
            </template>
            <template v-slot:item.acciones="{ item }">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon color="primary" small v-bind="attrs" v-on="on" @click="verBoletaPago(item)">
                    <v-icon small>mdi-receipt-text-outline</v-icon>
                  </v-btn>
                </template>
                <span>Ver / Imprimir Boleta de Pago</span>
              </v-tooltip>
            </template>

            <!-- FILA TOTALIZADORA (TFOOT) -->
            <template v-slot:body.append>
              <tr class="grey lighten-3 font-weight-black" v-if="planillaSueldos.length > 0">
                <td colspan="4" class="text-right text-uppercase">TOTALES GENERALES PLANILLA:</td>
                <td>Bs {{ formatoMoneda(totalesPlanillaSueldos.haber_basico) }}</td>
                <td class="text-primary">Bs {{ formatoMoneda(totalesPlanillaSueldos.bono_antiguedad) }}</td>
                <td class="success--text">Bs {{ formatoMoneda(resumenSueldos.total_ganado_bs) }}</td>
                <td class="error--text">Bs {{ formatoMoneda(resumenSueldos.total_descuentos_bs) }}</td>
                <td class="success--text">Bs {{ formatoMoneda(totalesPlanillaSueldos.refrigerio_bs) }}</td>
                <td class="primary--text">Bs {{ formatoMoneda(resumenSueldos.total_planilla_bs) }}</td>
                <td></td>
              </tr>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ========================================== -->
      <!-- PESTAÑA 1: CONSOLIDADO DE ASISTENCIA       -->
      <!-- ========================================== -->
      <v-tab-item>
        <!-- FILTROS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="d-flex align-center flex-wrap gap-2">
              <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO:</span>
              <v-btn-toggle v-model="periodoAsistenciaPreset" mandatory dense color="primary" @change="cambiarMesPresetAsistencia">
                <v-btn small value="actual" class="text-capitalize">Mes Actual</v-btn>
                <v-btn small value="anterior" class="text-capitalize">Mes Anterior</v-btn>
                <v-btn small value="personalizado" class="text-capitalize">Otro Mes</v-btn>
              </v-btn-toggle>
            </div>

            <div class="d-flex align-center gap-2">
              <v-btn color="primary" dark small class="rounded-pill font-weight-medium" @click="imprimirArea()">
                <v-icon left small>mdi-file-pdf-box</v-icon> Imprimir Planilla Asistencia
              </v-btn>
              <v-btn color="indigo darken-1" dark small outlined class="rounded-pill font-weight-medium" @click="exportarCsvAsistencia">
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel/CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="filtroMes"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar-month"
                @change="cargarAsistenciaMensual()"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4" md="3">
              <v-text-field
                v-model="filtroAnio"
                label="Gestión Fiscal"
                type="number"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar"
                @change="cargarAsistenciaMensual()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="12" md="3" offset-md="3" class="d-flex justify-end">
              <v-btn
                color="primary"
                dark
                class="rounded-pill w-100 font-weight-bold"
                :loading="loadingAsistencia"
                @click="cargarAsistenciaMensual()"
              >
                <v-icon left small>mdi-filter-check</v-icon> Consultar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS KPIS ASISTENCIA -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Funcionarios Controlados</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                {{ asistenciasConsolidado.length }}
              </div>
              <div class="text-caption text-secondary mt-1">
                En planilla de marcaciones
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Días Asistidos Efectivos</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                {{ metricasAsistencia.totalDiasAsistidos }}
              </div>
              <div class="text-caption text-success mt-1 font-weight-medium">
                Jornadas laborales cumplidas
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Permisos y Comisiones</div>
              <div class="text-h4 font-weight-black indigo--text mt-1">
                {{ metricasAsistencia.totalPermisosComisiones }}
              </div>
              <div class="text-caption text-indigo mt-1 font-weight-medium">
                Licencias y salidas oficiales
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card
              class="pa-3 text-center erp-card-elevated"
              rounded="lg"
              :class="metricasAsistencia.totalAtrasosMin === 0 ? 'bg-success-light' : 'bg-warning-light'"
            >
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Atrasos Registrados</div>
              <div
                class="text-h4 font-weight-black mt-1"
                :class="metricasAsistencia.totalAtrasosMin === 0 ? 'success--text' : 'warning--text text--darken-3'"
              >
                {{ metricasAsistencia.totalAtrasosMin }} min
              </div>
              <div class="mt-1">
                <v-chip
                  x-small
                  :color="metricasAsistencia.totalAtrasosMin === 0 ? 'success' : 'warning'"
                  text-color="white"
                  class="font-weight-bold"
                >
                  {{ metricasAsistencia.totalAtrasosMin === 0 ? 'PUNTUALIDAD ÓPTIMA' : 'CON DEMORAS' }}
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA ASISTENCIA -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            :headers="headersAsistencia"
            :items="asistenciasConsolidado"
            :loading="loadingAsistencia"
            class="erp-table"
            dense
            :items-per-page="25"
          >
            <template v-slot:item.dias_asistidos="{ item }">
              <v-chip small color="primary lighten-5" text-color="primary" label class="font-weight-bold">
                {{ item.dias_asistidos }} / {{ item.dias_laborables }} días
              </v-chip>
            </template>
            <template v-slot:item.minutos_atraso="{ item }">
              <v-chip x-small :color="item.minutos_atraso > 0 ? 'error lighten-5 text--darken-2' : 'success lighten-5'" :text-color="item.minutos_atraso > 0 ? 'red' : 'green'" label class="font-weight-bold">
                {{ item.minutos_atraso }} min
              </v-chip>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ========================================== -->
      <!-- PESTAÑA 2: PADRÓN GENERAL DE PERSONAL      -->
      <!-- ========================================== -->
      <v-tab-item>
        <!-- FILTROS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="text-caption font-weight-bold text-secondary">
              REGISTRO NOMINAL Y LEGAJO DEL PERSONAL INSTITUCIONAL
            </div>
            <div class="d-flex align-center gap-2">
              <v-btn color="secondary" outlined small class="rounded-pill font-weight-medium" @click="imprimirPadron()">
                <v-icon left small>mdi-printer</v-icon> Imprimir Padrón
              </v-btn>
              <v-btn color="indigo darken-1" dark small outlined class="rounded-pill font-weight-medium" @click="exportarCsvPadron">
                <v-icon left small>mdi-file-excel</v-icon> Exportar CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <v-col cols="12" sm="6" md="4">
              <v-text-field
                v-model="busquedaPadron"
                prepend-inner-icon="mdi-magnify"
                placeholder="Buscar por nombre, CI o cargo..."
                outlined
                dense
                hide-details
                clearable
                @input="cargarPadronPersonal()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="3" md="2">
              <v-select
                v-model="filtroContratoPadron"
                :items="['TODOS', 'PLANTA', 'EVENTUAL', 'CONSULTOR']"
                label="Tipo Contrato"
                outlined
                dense
                hide-details
              ></v-select>
            </v-col>
            <v-col cols="12" sm="3" md="2" class="d-flex align-center">
              <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarPadronPersonal()">
                <v-icon left small>mdi-refresh</v-icon> Actualizar
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS KPIS PADRÓN -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Personal Registrado</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                {{ padronPersonal.length }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Legajos institucionales activos
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Personal de Planta</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                {{ metricasPadron.totalPlanta }}
              </div>
              <div class="text-caption text-success mt-1 font-weight-medium">
                Ítems presupuestados fijos
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Eventuales y Consultores</div>
              <div class="text-h4 font-weight-black warning--text mt-1">
                {{ metricasPadron.totalEventuales }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Plazo determinado
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated bg-primary-light" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Acceso al ERP EMAPAP</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                {{ metricasPadron.totalConAcceso }}
              </div>
              <div class="mt-1">
                <v-chip x-small color="primary" text-color="white" class="font-weight-bold">
                  USUARIOS HABILITADOS
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA PADRÓN -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            :headers="headersPadron"
            :items="padronFiltrado"
            :loading="loadingPadron"
            class="erp-table"
            dense
            :items-per-page="25"
          >
            <template v-slot:item.tipo_contrato="{ item }">
              <v-chip small label :color="item.tipo_contrato === 'PLANTA' ? 'primary lighten-5' : 'orange lighten-5'" :text-color="item.tipo_contrato === 'PLANTA' ? 'primary' : 'orange darken-4'">
                {{ item.tipo_contrato }}
              </v-chip>
            </template>
            <template v-slot:item.tiene_acceso_erp="{ item }">
              <v-chip x-small label :color="item.tiene_acceso_erp === 'SI' ? 'success lighten-5' : 'grey lighten-3'" :text-color="item.tiene_acceso_erp === 'SI' ? 'green darken-2' : 'grey darken-1'">
                {{ item.tiene_acceso_erp === 'SI' ? 'ACTIVO' : 'SIN ACCESO' }}
              </v-chip>
            </template>
            <template v-slot:item.acciones="{ item }">
              <div class="d-flex">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon color="info" small v-bind="attrs" v-on="on" @click="verKardex(item)">
                      <v-icon small>mdi-card-account-details-outline</v-icon>
                    </v-btn>
                  </template>
                  <span>Ver Hoja de Vida / Kardex</span>
                </v-tooltip>

                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon color="indigo" small v-bind="attrs" v-on="on" @click="verCertificadoTrabajo(item)">
                      <v-icon small>mdi-certificate-outline</v-icon>
                    </v-btn>
                  </template>
                  <span>Emitir Certificado Laboral</span>
                </v-tooltip>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ========================================== -->
      <!-- PESTAÑA 3: PLANILLA DE REFRIGERIOS         -->
      <!-- ========================================== -->
      <v-tab-item>
        <!-- FILTROS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="d-flex align-center flex-wrap gap-2">
              <span class="text-caption font-weight-bold text-secondary mr-2">PERÍODO:</span>
              <v-btn-toggle v-model="periodoRefrigerioPreset" mandatory dense color="primary" @change="cambiarMesPresetRefrigerio">
                <v-btn small value="actual" class="text-capitalize">Mes Actual</v-btn>
                <v-btn small value="anterior" class="text-capitalize">Mes Anterior</v-btn>
                <v-btn small value="personalizado" class="text-capitalize">Otro Mes</v-btn>
              </v-btn-toggle>
            </div>

            <div class="d-flex align-center gap-2">
              <v-btn color="primary" dark small class="rounded-pill font-weight-medium" @click="imprimirArea()">
                <v-icon left small>mdi-file-pdf-box</v-icon> Imprimir Planilla Refrigerios
              </v-btn>
              <v-btn color="indigo darken-1" dark small outlined class="rounded-pill font-weight-medium" @click="exportarCsvRefrigerios">
                <v-icon left small>mdi-file-excel</v-icon> Exportar Excel/CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <v-row dense align="center">
            <v-col cols="12" sm="4" md="3">
              <v-select
                v-model="filtroMes"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar-month"
                @change="cargarRefrigerioMensual()"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4" md="3">
              <v-text-field
                v-model="filtroAnio"
                label="Gestión"
                type="number"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar"
                @change="cargarRefrigerioMensual()"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="12" md="3" offset-md="3" class="d-flex justify-end">
              <v-btn
                color="primary"
                dark
                class="rounded-pill w-100 font-weight-bold"
                :loading="loadingRefrigerio"
                @click="cargarRefrigerioMensual()"
              >
                <v-icon left small>mdi-calculator</v-icon> Calcular
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- TARJETAS KPIS REFRIGERIOS -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Presupuesto Refrigerios</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                Bs {{ formatoMoneda(totalRefrigerioBs) }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Monto total asignado del mes
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Funcionarios Beneficiarios</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                {{ planillaRefrigerio.length }}
              </div>
              <div class="text-caption text-primary mt-1 font-weight-medium">
                Con días efectivos cumplidos
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Días Computados</div>
              <div class="text-h4 font-weight-black teal--text text--darken-3 mt-1">
                {{ metricasRefrigerio.totalDiasEfectivos }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Días de asistencia validados
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated bg-primary-light" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Tarifa Diaria Promedio</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                Bs {{ metricasRefrigerio.tarifaPromedio }}
              </div>
              <div class="mt-1">
                <v-chip x-small color="primary" text-color="white" class="font-weight-bold">
                  ASIGNACIÓN INSTITUCIONAL
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA REFRIGERIOS -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            :headers="headersRefrigerio"
            :items="planillaRefrigerio"
            :loading="loadingRefrigerio"
            class="erp-table"
            dense
            :items-per-page="25"
          >
            <template v-slot:item.monto_total_bs="{ item }">
              <span class="font-weight-bold text-success">Bs {{ formatoMoneda(item.monto_total_bs) }}</span>
            </template>

            <!-- TFOOT TOTALIZADOR -->
            <template v-slot:body.append>
              <tr class="grey lighten-3 font-weight-black" v-if="planillaRefrigerio.length > 0">
                <td colspan="3" class="text-right text-uppercase">TOTAL PLANILLA REFRIGERIOS:</td>
                <td>{{ metricasRefrigerio.totalDiasEfectivos }} días</td>
                <td>-</td>
                <td class="success--text font-weight-black">Bs {{ formatoMoneda(totalRefrigerioBs) }}</td>
              </tr>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ========================================== -->
      <!-- PESTAÑA 4: KARDEX DE VACACIONES            -->
      <!-- ========================================== -->
      <v-tab-item>
        <!-- FILTROS -->
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between flex-wrap mb-3 gap-2">
            <div class="text-caption font-weight-bold text-secondary">
              CONTROL INSTITUCIONAL DE VACACIONES Y DERECHO ANUAL ACUMULADO
            </div>
            <div class="d-flex align-center gap-2">
              <v-btn color="primary" dark small class="rounded-pill font-weight-medium" @click="imprimirArea()">
                <v-icon left small>mdi-file-pdf-box</v-icon> Imprimir Saldos
              </v-btn>
              <v-btn color="indigo darken-1" dark small outlined class="rounded-pill font-weight-medium" @click="exportarCsvVacaciones">
                <v-icon left small>mdi-file-excel</v-icon> Exportar CSV
              </v-btn>
            </div>
          </div>

          <v-divider class="mb-3"></v-divider>

          <div class="d-flex align-center gap-2">
            <v-btn color="primary" class="rounded-pill text-capitalize" :loading="loadingVacaciones" @click="cargarVacaciones()">
              <v-icon left small>mdi-refresh</v-icon> Actualizar Saldos de Vacaciones
            </v-btn>
          </div>
        </v-card>

        <!-- TARJETAS KPIS VACACIONES -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Servidores con Derecho</div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                {{ kardexVacaciones.length }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Funcionarios con CAS vigente
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Saldo Disponible</div>
              <div class="text-h4 font-weight-black success--text mt-1">
                {{ metricasVacaciones.totalDisponibles }}
              </div>
              <div class="text-caption text-success mt-1 font-weight-medium">
                Días acumulados pendientes de uso
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Días Utilizados en Gestión</div>
              <div class="text-h4 font-weight-black warning--text mt-1">
                {{ metricasVacaciones.totalUtilizados }}
              </div>
              <div class="text-caption text-secondary mt-1">
                Días de vacación gozados
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated bg-info-light" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Saldo Promedio por Servidor</div>
              <div class="text-h4 font-weight-black info--text mt-1">
                {{ metricasVacaciones.promedioDisponible }} días
              </div>
              <div class="mt-1">
                <v-chip x-small color="info" text-color="white" class="font-weight-bold">
                  BALANCE VACACIONAL
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA VACACIONES -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table
            :headers="headersVacaciones"
            :items="kardexVacaciones"
            :loading="loadingVacaciones"
            class="erp-table"
            dense
            :items-per-page="25"
          >
            <template v-slot:item.saldo_disponible="{ item }">
              <v-chip small color="primary lighten-5" text-color="primary" label class="font-weight-bold">
                {{ item.saldo_disponible }} días disponibles
              </v-chip>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- ========================================== -->
      <!-- PESTAÑA 5: GENERADOR DE REPORTES PERSONALIZADOS -->
      <!-- ========================================== -->
      <v-tab-item>
        <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
          <div class="font-weight-bold text-subtitle-1 mb-2">
            <v-icon left color="primary">mdi-format-list-checks</v-icon>
            Constructor Dinámico: Seleccione los Campos del Reporte
          </div>
          <div class="text-caption text-secondary mb-3">Marque las columnas que desea incluir en la tabla personalizada y aplique filtros:</div>

          <div class="d-flex flex-wrap gap-2 mb-4">
            <v-chip
              v-for="col in catalogoColumnas"
              :key="col.id"
              :input-value="columnasSeleccionadas.includes(col.id)"
              filter
              color="primary"
              outlined
              class="ma-1 font-weight-medium"
              @click="toggleColumna(col.id)"
            >
              {{ col.nombre }}
            </v-chip>
          </div>

          <v-divider class="mb-3"></v-divider>

          <div class="d-flex align-center gap-3 flex-wrap">
            <v-select
              v-model="filtroPersonalizadoTipoContrato"
              :items="['TODOS', 'PLANTA', 'EVENTUAL', 'CONSULTOR', 'PASANTE']"
              label="Tipo de Contrato"
              outlined
              dense
              hide-details
              style="max-width: 180px;"
            ></v-select>

            <v-select
              v-model="filtroPersonalizadoGenero"
              :items="['TODOS', 'MASCULINO', 'FEMENINO']"
              label="Género"
              outlined
              dense
              hide-details
              style="max-width: 160px;"
            ></v-select>

            <v-btn color="primary" class="rounded-pill text-capitalize" :loading="loadingPersonalizado" @click="ejecutarReportePersonalizado()">
              <v-icon left small>mdi-play</v-icon> Generar Reporte
            </v-btn>

            <v-spacer></v-spacer>

            <v-btn v-if="datosPersonalizados.length > 0" color="secondary" outlined class="rounded-pill text-capitalize" @click="imprimirReportePersonalizado()">
              <v-icon left small>mdi-printer</v-icon> Imprimir Reporte
            </v-btn>
          </div>
        </v-card>

        <v-card v-if="datosPersonalizados.length > 0" rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-3 px-4 d-flex justify-space-between align-center">
            <span class="font-weight-bold text-subtitle-1">Resultados del Reporte a la Medida</span>
            <v-chip small color="primary" outlined>{{ datosPersonalizados.length }} registros</v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-data-table
            :headers="headersDinamicosPersonalizados"
            :items="datosPersonalizados"
            :loading="loadingPersonalizado"
            class="erp-table"
            dense
            :items-per-page="25"
          ></v-data-table>
        </v-card>
      </v-tab-item>
    </v-tabs-items>

    <!-- DIÁLOGO IMPRIMIBLE: BOLETA OFICIAL DE PAGO -->
    <v-dialog v-model="dialogBoletaPago" max-width="700">
      <v-card rounded="lg" v-if="boletaSeleccionada">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-receipt-text</v-icon>
          <span>Papeleta Oficial de Pago Salarial</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogBoletaPago = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento">
            <div class="d-flex align-center justify-space-between mb-3 border-b pb-2">
              <div>
                <div class="font-weight-bold text-subtitle-2">{{ boletaSeleccionada.institucion }}</div>
                <div class="text-caption text-secondary">BOLETA DE PAGO INDIVIDUAL - {{ boletaSeleccionada.periodo }}</div>
              </div>
              <div class="text-right">
                <div class="font-weight-bold text-caption text-primary">CITE: {{ boletaSeleccionada.boleta_nro }}</div>
                <div class="text-caption text-secondary">Emisión: {{ boletaSeleccionada.fecha_emision }}</div>
              </div>
            </div>

            <!-- DATOS DEL FUNCIONARIO -->
            <div class="pa-3 bg-light rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
              <div class="row dense">
                <div class="col-6"><strong>Funcionario:</strong> {{ boletaSeleccionada.funcionario.nombre_completo }}</div>
                <div class="col-6"><strong>C.I.:</strong> {{ boletaSeleccionada.funcionario.ci }}</div>
                <div class="col-6 mt-1"><strong>Cargo:</strong> {{ boletaSeleccionada.funcionario.cargo }}</div>
                <div class="col-6 mt-1"><strong>Ítem:</strong> {{ boletaSeleccionada.funcionario.item }}</div>
                <div class="col-12 mt-1"><strong>Unidad:</strong> {{ boletaSeleccionada.funcionario.unidad }}</div>
              </div>
            </div>

            <!-- DETALLE DE INGRESOS Y DESCUENTOS -->
            <div class="boleta-grid mt-3">
              <!-- INGRESOS -->
              <div>
                <div class="font-weight-bold text-caption text-primary mb-1 border-b pb-1">INGRESOS Y HABERES (Bs.)</div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Haber Básico:</span> <span>{{ formatoMoneda(boletaSeleccionada.ingresos.haber_basico) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Bono Antigüedad ({{ boletaSeleccionada.ingresos.porcentaje_antiguedad }}%):</span> <span>{{ formatoMoneda(boletaSeleccionada.ingresos.bono_antiguedad) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 font-weight-bold border-t mt-1 pt-1"><span>TOTAL GANADO:</span> <span>{{ formatoMoneda(boletaSeleccionada.ingresos.total_ganado) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 text-success"><span>+ Refrigerios ({{ boletaSeleccionada.ingresos.dias_refrigerio }} días):</span> <span>+ {{ formatoMoneda(boletaSeleccionada.ingresos.refrigerios_bs) }}</span></div>
              </div>

              <!-- DESCUENTOS -->
              <div>
                <div class="font-weight-bold text-caption text-error mb-1 border-b pb-1">DESCUENTOS DE LEY (Bs.)</div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Gestora Pública (12.71%):</span> <span>{{ formatoMoneda(boletaSeleccionada.descuentos.gestora_12_71) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Descuento Atrasos ({{ boletaSeleccionada.descuentos.minutos_atraso }}m):</span> <span>{{ formatoMoneda(boletaSeleccionada.descuentos.descuento_atraso) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 font-weight-bold border-t mt-1 pt-1 text-error"><span>TOTAL DESCUENTOS:</span> <span>{{ formatoMoneda(boletaSeleccionada.descuentos.total_descuentos) }}</span></div>
              </div>
            </div>

            <!-- TOTAL LIQUIDO -->
            <div class="pa-3 mt-4 text-center rounded" style="background-color: #ecfdf5; border: 2px solid #10b981;">
              <div class="text-caption text-secondary">LÍQUIDO TOTAL A PERCIBIR:</div>
              <div class="text-h5 font-weight-bold" style="color: #065f46;">Bs {{ formatoMoneda(boletaSeleccionada.liquido_pagable) }}</div>
            </div>

            <!-- FIRMAS -->
            <div class="d-flex justify-space-between mt-6 px-4">
              <div class="firma-box">Firma del Funcionario<br>C.I. {{ boletaSeleccionada.funcionario.ci }}</div>
              <div class="firma-box">Responsable de Recursos Humanos<br>EMAPAP Central</div>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogBoletaPago = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Boleta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO IMPRIMIBLE: CERTIFICADO DE TRABAJO -->
    <v-dialog v-model="dialogCertificado" max-width="700">
      <v-card rounded="lg" v-if="certificadoSeleccionado">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-certificate</v-icon>
          <span>Certificación Laboral Oficial</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCertificado = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento text-justify" style="line-height: 1.8;">
            <div class="text-center mb-6">
              <div class="font-weight-bold text-subtitle-1">{{ certificadoSeleccionado.institucion }}</div>
              <div class="text-caption text-secondary">GERENCIA DE ADMINISTRACIÓN Y FINANZAS - RRHH</div>
              <div class="font-weight-bold text-h6 mt-4">CERTIFICADO DE TRABAJO</div>
              <div class="text-caption text-primary font-weight-bold">CITE: {{ certificadoSeleccionado.cite }}</div>
            </div>

            <p>La Responsable de Recursos Humanos de la <strong>Empresa Municipal de Agua Potable y Alcantarillado Patacamaya (EMAPAP)</strong>, en uso de sus atribuciones y a solicitud de la parte interesada:</p>

            <p class="font-weight-bold text-center my-4">CERTIFICA:</p>

            <p>Que, revisados los antecedentes y el legajo digital institucional, se evidencia que el/la señor(a) <strong>{{ certificadoSeleccionado.funcionario }}</strong> con Cédula de Identidad N° <strong>{{ certificadoSeleccionado.ci }}</strong>, presta sus servicios en esta entidad desempeñando el cargo de <strong>{{ certificadoSeleccionado.cargo }}</strong> en la <strong>{{ certificadoSeleccionado.unidad }}</strong>, bajo la modalidad de <strong>{{ certificadoSeleccionado.tipo_contrato }}</strong> desde fecha <strong>{{ certificadoSeleccionado.fecha_ingreso }}</strong> hasta la fecha presente, habiendo demostrado idoneidad y responsabilidad en el ejercicio de sus funciones.</p>

            <p class="mt-4">Es cuanto se certifica en honor a la verdad y para los fines que convengan a la parte interesada.</p>

            <div class="text-right mt-6">Patacamaya, {{ certificadoSeleccionado.fecha_emision }}</div>

            <div class="d-flex justify-center mt-6">
              <div class="firma-box">Responsable de Recursos Humanos<br>EMAPAP</div>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogCertificado = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Certificado
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO IMPRIMIBLE: KARDEX / HOJA DE VIDA -->
    <v-dialog v-model="dialogKardex" max-width="750">
      <v-card rounded="lg" v-if="kardexSeleccionado">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-card-account-details</v-icon>
          <span>Kardex Institucional del Funcionario</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogKardex = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento">
            <div class="d-flex align-center justify-space-between mb-4 border-b pb-2">
              <div>
                <div class="font-weight-bold text-subtitle-1">{{ kardexSeleccionado.institucion }}</div>
                <div class="text-caption text-secondary">HOJA DE VIDA Y KARDEX INSTITUCIONAL</div>
              </div>
              <div class="text-right text-caption text-secondary">
                Fecha Emisión: {{ kardexSeleccionado.fecha_emision }}
              </div>
            </div>

            <!-- DATOS GENERALES -->
            <div class="pa-3 bg-light rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
              <div class="font-weight-bold text-caption text-primary mb-2">1. DATOS PERSONALES Y PUESTO ACTUAL</div>
              <div class="row dense">
                <div class="col-6"><strong>Nombre:</strong> {{ kardexSeleccionado.persona.nombre_completo }}</div>
                <div class="col-6"><strong>C.I.:</strong> {{ kardexSeleccionado.persona.nro_documento }}</div>
                <div class="col-6 mt-1"><strong>Cargo Actual:</strong> {{ kardexSeleccionado.puesto_actual ? kardexSeleccionado.puesto_actual.nombre : 'Sin Asignar' }}</div>
                <div class="col-6 mt-1"><strong>Unidad:</strong> {{ kardexSeleccionado.unidad ? kardexSeleccionado.unidad.nombre : 'Sin Unidad' }}</div>
                <div class="col-6 mt-1"><strong>Celular:</strong> {{ kardexSeleccionado.persona.telefono_celular || '-' }}</div>
                <div class="col-6 mt-1"><strong>Correo:</strong> {{ kardexSeleccionado.persona.correo_electronico_personal || '-' }}</div>
              </div>
            </div>

            <!-- ESTUDIOS ACADÉMICOS -->
            <div class="mb-3">
              <div class="font-weight-bold text-caption text-primary mb-1">2. FORMACIÓN ACADÉMICA</div>
              <table class="tabla-reporte-print">
                <thead><tr><th>Nivel</th><th>Carrera / Especialidad</th><th>Institución</th></tr></thead>
                <tbody>
                  <tr v-for="est in kardexSeleccionado.estudios" :key="est.id">
                    <td>{{ est.nivel_instruccion }}</td><td>{{ est.carrera }}</td><td>{{ est.institucion }}</td>
                  </tr>
                  <tr v-if="!kardexSeleccionado.estudios || kardexSeleccionado.estudios.length === 0"><td colspan="3" class="text-center font-italic text-secondary">Sin estudios registrados</td></tr>
                </tbody>
              </table>
            </div>

            <!-- AÑOS DE SERVICIO (CAS) -->
            <div class="mb-3">
              <div class="font-weight-bold text-caption text-primary mb-1">3. CALIFICACIÓN DE AÑOS DE SERVICIO (CAS)</div>
              <table class="tabla-reporte-print">
                <thead><tr><th>Resolución</th><th>Años Reconocidos</th><th>Meses</th><th>Fecha Emisión</th></tr></thead>
                <tbody>
                  <tr v-for="c in kardexSeleccionado.cas" :key="c.id">
                    <td>{{ c.nro_resolucion || 'N/D' }}</td><td>{{ c.anios }} años</td><td>{{ c.meses }} meses</td><td>{{ c.fecha_emision || '-' }}</td>
                  </tr>
                  <tr v-if="!kardexSeleccionado.cas || kardexSeleccionado.cas.length === 0"><td colspan="4" class="text-center font-italic text-secondary">Sin registros CAS</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogKardex = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Kardex
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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

export default {
  name: 'ReportesRrhh',
  data() {
    const ahora = new Date();
    return {
      activeTab: 0,
      filtroMes: ahora.getMonth() + 1,
      filtroAnio: ahora.getFullYear(),
      periodoPlanillaPreset: 'actual',
      periodoAsistenciaPreset: 'actual',
      periodoRefrigerioPreset: 'actual',
      filtroContratoPadron: 'TODOS',
      meses: [
        { id: 1, nombre: 'Enero' }, { id: 2, nombre: 'Febrero' }, { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' }, { id: 5, nombre: 'Mayo' }, { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' }, { id: 8, nombre: 'Agosto' }, { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' }, { id: 11, nombre: 'Noviembre' }, { id: 12, nombre: 'Diciembre' },
      ],

      // TAB 0: Sueldos
      planillaSueldos: [],
      resumenSueldos: {
        total_planilla_bs: 0,
        total_ganado_bs: 0,
        total_descuentos_bs: 0,
      },
      esDeclarada: false,
      citeOficial: '',
      loadingSueldos: false,
      cerrandoPlanilla: false,
      headersPlanillaSueldos: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Ítem', value: 'item' },
        { text: 'Haber Básico', value: 'haber_basico' },
        { text: 'Bono Antigüedad', value: 'bono_antiguedad' },
        { text: 'Total Ganado', value: 'total_ganado' },
        { text: 'Gestora (12.71%)', value: 'gestora_12_71' },
        { text: 'Refrigerios', value: 'refrigerio_bs' },
        { text: 'Líquido Pagable', value: 'liquido_pagable_total' },
        { text: 'Boleta', value: 'acciones', sortable: false, align: 'center', width: '70px' },
      ],

      // TAB 1: Asistencias
      asistenciasConsolidado: [],
      loadingAsistencia: false,
      headersAsistencia: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Días Asistidos', value: 'dias_asistidos' },
        { text: 'Permisos', value: 'permisos' },
        { text: 'Comisiones', value: 'comisiones' },
        { text: 'Faltas', value: 'faltas' },
        { text: 'Minutos Atraso', value: 'minutos_atraso' },
      ],

      // TAB 2: Padrón
      padronPersonal: [],
      loadingPadron: false,
      busquedaPadron: '',
      headersPadron: [
        { text: 'Funcionario', value: 'nombre_completo' },
        { text: 'C.I.', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Unidad Organizacional', value: 'unidad_organizacional' },
        { text: 'Tipo Contrato', value: 'tipo_contrato' },
        { text: 'Fecha Ingreso', value: 'fecha_ingreso' },
        { text: 'CAS', value: 'anios_cas' },
        { text: 'Acceso ERP', value: 'tiene_acceso_erp', align: 'center' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '100px' },
      ],

      // TAB 3: Refrigerios
      planillaRefrigerio: [],
      totalRefrigerioBs: 0,
      loadingRefrigerio: false,
      headersRefrigerio: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Días Asistidos', value: 'dias_efectivos' },
        { text: 'Tarifa Diaria', value: 'tarifa_diaria' },
        { text: 'Total a Pagar (Bs.)', value: 'monto_total_bs' },
      ],

      // TAB 4: Vacaciones
      kardexVacaciones: [],
      loadingVacaciones: false,
      headersVacaciones: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Antigüedad (CAS)', value: 'anios_antiguedad' },
        { text: 'Días Derecho Anual', value: 'dias_derecho_anual' },
        { text: 'Días Utilizados', value: 'dias_utilizados' },
        { text: 'Saldo Disponible', value: 'saldo_disponible' },
      ],

      // TAB 5: Personalizados
      catalogoColumnas: [
        { id: 'nombres', nombre: 'Nombres y Apellidos' },
        { id: 'ci', nombre: 'Cédula de Identidad' },
        { id: 'cargo', nombre: 'Cargo / Puesto' },
        { id: 'item', nombre: 'N° de Ítem' },
        { id: 'unidad', nombre: 'Unidad Organizacional' },
        { id: 'tipo_contrato', nombre: 'Tipo de Contrato' },
        { id: 'genero', nombre: 'Género' },
        { id: 'celular', nombre: 'Teléfono / Celular' },
        { id: 'correo', nombre: 'Correo Electrónico' },
        { id: 'fecha_ingreso', nombre: 'Fecha de Ingreso' },
        { id: 'anios_cas', nombre: 'Años de Antigüedad (CAS)' },
        { id: 'formacion', nombre: 'Formación / Título' },
      ],
      columnasSeleccionadas: ['nombres', 'ci', 'cargo', 'unidad', 'tipo_contrato', 'celular'],
      filtroPersonalizadoTipoContrato: 'TODOS',
      filtroPersonalizadoGenero: 'TODOS',
      datosPersonalizados: [],
      loadingPersonalizado: false,

      // Modales y Reportes Imprimibles
      dialogBoletaPago: false,
      boletaSeleccionada: null,
      dialogCertificado: false,
      certificadoSeleccionado: null,
      dialogKardex: false,
      kardexSeleccionado: null,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    headersDinamicosPersonalizados() {
      return this.columnasSeleccionadas.map(colId => {
        const cat = this.catalogoColumnas.find(c => c.id === colId);
        return {
          text: cat ? cat.nombre : colId,
          value: colId,
        };
      });
    },

    totalesPlanillaSueldos() {
      return this.planillaSueldos.reduce((acc, item) => {
        acc.haber_basico += parseFloat(item.haber_basico || 0);
        acc.bono_antiguedad += parseFloat(item.bono_antiguedad || 0);
        acc.refrigerio_bs += parseFloat(item.refrigerio_bs || 0);
        return acc;
      }, { haber_basico: 0, bono_antiguedad: 0, refrigerio_bs: 0 });
    },

    metricasAsistencia() {
      const totalDiasAsistidos = this.asistenciasConsolidado.reduce((acc, i) => acc + Number(i.dias_asistidos || 0), 0);
      const totalPermisosComisiones = this.asistenciasConsolidado.reduce((acc, i) => acc + Number(i.permisos || 0) + Number(i.comisiones || 0), 0);
      const totalAtrasosMin = this.asistenciasConsolidado.reduce((acc, i) => acc + Number(i.minutos_atraso || 0), 0);
      return { totalDiasAsistidos, totalPermisosComisiones, totalAtrasosMin };
    },

    metricasPadron() {
      const totalPlanta = this.padronPersonal.filter(p => p.tipo_contrato === 'PLANTA').length;
      const totalEventuales = this.padronPersonal.filter(p => p.tipo_contrato !== 'PLANTA').length;
      const totalConAcceso = this.padronPersonal.filter(p => p.tiene_acceso_erp === 'SI').length;
      return { totalPlanta, totalEventuales, totalConAcceso };
    },

    padronFiltrado() {
      if (this.filtroContratoPadron === 'TODOS') return this.padronPersonal;
      return this.padronPersonal.filter(p => p.tipo_contrato === this.filtroContratoPadron);
    },

    metricasRefrigerio() {
      const totalDiasEfectivos = this.planillaRefrigerio.reduce((acc, i) => acc + Number(i.dias_efectivos || 0), 0);
      const tarifaPromedio = this.planillaRefrigerio.length > 0 && this.planillaRefrigerio[0].tarifa_diaria
        ? parseFloat(this.planillaRefrigerio[0].tarifa_diaria).toFixed(2)
        : '18.00';
      return { totalDiasEfectivos, tarifaPromedio };
    },

    metricasVacaciones() {
      const totalDisponibles = this.kardexVacaciones.reduce((acc, i) => acc + Number(i.saldo_disponible || 0), 0);
      const totalUtilizados = this.kardexVacaciones.reduce((acc, i) => acc + Number(i.dias_utilizados || 0), 0);
      const promedioDisponible = this.kardexVacaciones.length > 0
        ? (totalDisponibles / this.kardexVacaciones.length).toFixed(1)
        : '0.0';
      return { totalDisponibles, totalUtilizados, promedioDisponible };
    },
  },
  mounted() {
    this.cargarPlanillaSueldos();
    this.cargarAsistenciaMensual();
    this.cargarPadronPersonal();
    this.cargarRefrigerioMensual();
    this.cargarVacaciones();
  },
  methods: {
    formatoMoneda(valor) {
      return parseFloat(valor || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    cambiarMesPreset(val) {
      const hoy = new Date();
      if (val === 'actual') {
        this.filtroMes = hoy.getMonth() + 1;
        this.filtroAnio = hoy.getFullYear();
        this.cargarPlanillaSueldos();
      } else if (val === 'anterior') {
        const d = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        this.filtroMes = d.getMonth() + 1;
        this.filtroAnio = d.getFullYear();
        this.cargarPlanillaSueldos();
      }
    },

    cambiarMesPresetAsistencia(val) {
      const hoy = new Date();
      if (val === 'actual') {
        this.filtroMes = hoy.getMonth() + 1;
        this.filtroAnio = hoy.getFullYear();
        this.cargarAsistenciaMensual();
      } else if (val === 'anterior') {
        const d = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        this.filtroMes = d.getMonth() + 1;
        this.filtroAnio = d.getFullYear();
        this.cargarAsistenciaMensual();
      }
    },

    cambiarMesPresetRefrigerio(val) {
      const hoy = new Date();
      if (val === 'actual') {
        this.filtroMes = hoy.getMonth() + 1;
        this.filtroAnio = hoy.getFullYear();
        this.cargarRefrigerioMensual();
      } else if (val === 'anterior') {
        const d = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        this.filtroMes = d.getMonth() + 1;
        this.filtroAnio = d.getFullYear();
        this.cargarRefrigerioMensual();
      }
    },

    cargarAsistenciaMensual() {
      this.loadingAsistencia = true;
      axios.get(`/api/rrhh/reportes/asistencia-mensual?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) this.asistenciasConsolidado = res.data.data || [];
      }).finally(() => { this.loadingAsistencia = false; });
    },

    cargarPlanillaSueldos() {
      this.loadingSueldos = true;
      axios.get(`/api/rrhh/reportes/planilla-sueldos?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.planillaSueldos = res.data.data || [];
          this.esDeclarada = !!res.data.es_declarada;
          this.citeOficial = res.data.cite_oficial || '';
          this.resumenSueldos = {
            total_planilla_bs: res.data.total_planilla_bs || 0,
            total_ganado_bs: res.data.total_ganado_bs || 0,
            total_descuentos_bs: res.data.total_descuentos_bs || 0,
          };
        }
      }).finally(() => { this.loadingSueldos = false; });
    },

    cerrarYDeclarar() {
      if (!confirm(`¿Confirma el cierre y declaración oficial de la planilla de ${this.filtroMes}/${this.filtroAnio}? Esta acción congelará los datos y generará un CITE inmutable.`)) {
        return;
      }
      this.cerrandoPlanilla = true;
      axios.post('/api/rrhh/reportes/cerrar-declarar-planilla', {
        mes: this.filtroMes,
        anio: this.filtroAnio,
      })
      .then(res => {
        this.showSnackbar(res.data.message || 'Planilla declarada y congelada exitosamente.', 'success');
        this.cargarPlanillaSueldos();
      })
      .catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al cerrar planilla.', 'error');
      })
      .finally(() => {
        this.cerrandoPlanilla = false;
      });
    },

    cargarPadronPersonal() {
      this.loadingPadron = true;
      axios.get('/api/rrhh/reportes/padron-personal', {
        params: { search: this.busquedaPadron },
      }).then(res => {
        if (res.data && res.data.success) this.padronPersonal = res.data.data || [];
      }).finally(() => { this.loadingPadron = false; });
    },

    cargarRefrigerioMensual() {
      this.loadingRefrigerio = true;
      axios.get(`/api/rrhh/reportes/refrigerio-mensual?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.planillaRefrigerio = res.data.data || [];
          this.totalRefrigerioBs = res.data.total_general_bs || 0;
        }
      }).finally(() => { this.loadingRefrigerio = false; });
    },

    cargarVacaciones() {
      this.loadingVacaciones = true;
      axios.get('/api/rrhh/reportes/saldo-vacaciones').then(res => {
        if (res.data && res.data.success) this.kardexVacaciones = res.data.data || [];
      }).finally(() => { this.loadingVacaciones = false; });
    },

    toggleColumna(colId) {
      const idx = this.columnasSeleccionadas.indexOf(colId);
      if (idx > -1) {
        if (this.columnasSeleccionadas.length > 1) {
          this.columnasSeleccionadas.splice(idx, 1);
        }
      } else {
        this.columnasSeleccionadas.push(colId);
      }
    },

    ejecutarReportePersonalizado() {
      this.loadingPersonalizado = true;
      axios.post('/api/rrhh/reportes/generar-personalizado', {
        columnas: this.columnasSeleccionadas,
        tipo_contrato: this.filtroPersonalizadoTipoContrato === 'TODOS' ? null : this.filtroPersonalizadoTipoContrato,
        genero: this.filtroPersonalizadoGenero === 'TODOS' ? null : this.filtroPersonalizadoGenero,
      })
      .then(res => {
        if (res.data && res.data.success) {
          this.datosPersonalizados = res.data.data || [];
          this.showSnackbar(`Reporte generado: ${this.datosPersonalizados.length} registros`, 'success');
        }
      })
      .catch(() => {
        this.showSnackbar('Error al generar el reporte personalizado', 'error');
      })
      .finally(() => {
        this.loadingPersonalizado = false;
      });
    },

    verBoletaPago(item) {
      const id = item.id_persona || item.id;
      axios.get(`/api/rrhh/reportes/boleta-pago/${id}/html?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.boletaSeleccionada = res.data.data;
          this.dialogBoletaPago = true;
        }
      });
    },

    verCertificadoTrabajo(item) {
      axios.get(`/api/rrhh/reportes/certificado-trabajo/${item.id}/html`).then(res => {
        if (res.data && res.data.success) {
          this.certificadoSeleccionado = res.data.data;
          this.dialogCertificado = true;
        }
      });
    },

    verKardex(item) {
      axios.get(`/api/rrhh/reportes/kardex-funcionario/${item.id}/html`).then(res => {
        if (res.data && res.data.success) {
          this.kardexSeleccionado = res.data.data;
          this.dialogKardex = true;
        }
      });
    },

    imprimirArea() {
      window.print();
    },

    imprimirPadron() {
      window.print();
    },

    imprimirReportePersonalizado() {
      window.print();
    },

    imprimirPestanaActual() {
      window.print();
    },

    exportarCsvPlanilla() {
      if (!this.planillaSueldos.length) return;
      let csv = 'Funcionario;CI;Cargo;Item;Haber Basico;Bono Antiguedad;Total Ganado;Gestora;Refrigerio;Liquido Pagable\n';
      this.planillaSueldos.forEach(row => {
        csv += `"${row.funcionario}";"${row.ci}";"${row.cargo}";"${row.item}";"${row.haber_basico}";"${row.bono_antiguedad}";"${row.total_ganado}";"${row.gestora_12_71}";"${row.refrigerio_bs}";"${row.liquido_pagable_total}"\n`;
      });
      this.descargarCsvBlob(csv, `planilla_sueldos_${this.filtroMes}_${this.filtroAnio}.csv`);
    },

    exportarCsvAsistencia() {
      if (!this.asistenciasConsolidado.length) return;
      let csv = 'Funcionario;CI;Cargo;Dias Asistidos;Dias Laborables;Permisos;Comisiones;Faltas;Minutos Atraso\n';
      this.asistenciasConsolidado.forEach(row => {
        csv += `"${row.funcionario}";"${row.ci}";"${row.cargo}";"${row.dias_asistidos}";"${row.dias_laborables}";"${row.permisos}";"${row.comisiones}";"${row.faltas}";"${row.minutos_atraso}"\n`;
      });
      this.descargarCsvBlob(csv, `asistencia_consolidada_${this.filtroMes}_${this.filtroAnio}.csv`);
    },

    exportarCsvPadron() {
      if (!this.padronPersonal.length) return;
      let csv = 'Funcionario;CI;Cargo;Unidad;Tipo Contrato;Fecha Ingreso;CAS;Acceso ERP\n';
      this.padronPersonal.forEach(row => {
        csv += `"${row.nombre_completo}";"${row.ci}";"${row.cargo}";"${row.unidad_organizacional}";"${row.tipo_contrato}";"${row.fecha_ingreso}";"${row.anios_cas}";"${row.tiene_acceso_erp}"\n`;
      });
      this.descargarCsvBlob(csv, `padron_personal.csv`);
    },

    exportarCsvRefrigerios() {
      if (!this.planillaRefrigerio.length) return;
      let csv = 'Funcionario;CI;Cargo;Dias Efectivos;Tarifa Diaria;Total Bs\n';
      this.planillaRefrigerio.forEach(row => {
        csv += `"${row.funcionario}";"${row.ci}";"${row.cargo}";"${row.dias_efectivos}";"${row.tarifa_diaria}";"${row.monto_total_bs}"\n`;
      });
      this.descargarCsvBlob(csv, `planilla_refrigerios_${this.filtroMes}_${this.filtroAnio}.csv`);
    },

    exportarCsvVacaciones() {
      if (!this.kardexVacaciones.length) return;
      let csv = 'Funcionario;CI;CAS;Dias Derecho;Dias Utilizados;Saldo Disponible\n';
      this.kardexVacaciones.forEach(row => {
        csv += `"${row.funcionario}";"${row.ci}";"${row.anios_antiguedad}";"${row.dias_derecho_anual}";"${row.dias_utilizados}";"${row.saldo_disponible}"\n`;
      });
      this.descargarCsvBlob(csv, `kardex_vacaciones.csv`);
    },

    descargarCsvBlob(csvContent, filename) {
      const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
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
  #reporte-print-area, #reporte-print-area * {
    visibility: visible;
  }
  #reporte-print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    background: white;
  }
}
.reporte-documento {
  border: 2px solid #1e293b;
  border-radius: 8px;
  padding: 28px;
  background: white;
  color: #0f172a;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.boleta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.firma-box {
  border-top: 1px solid #64748b;
  width: 220px;
  text-align: center;
  margin-top: 60px;
  padding-top: 8px;
  font-size: 0.8rem;
}
.tabla-reporte-print {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
.tabla-reporte-print th, .tabla-reporte-print td {
  border: 1px solid #cbd5e1;
  padding: 8px 10px;
  font-size: 0.85rem;
}
.tabla-reporte-print th {
  background-color: #f1f5f9;
  font-weight: bold;
}
.gap-2 {
  gap: 8px;
}
.gap-3 {
  gap: 12px;
}
</style>
