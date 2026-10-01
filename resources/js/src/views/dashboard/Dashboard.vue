<template>
  <div class="dashboard-container">
    <!-- 1. CABECERA EJECUTIVA ESTÁNDAR DEL SISTEMA (ERP CARD) -->
    <v-card class="mb-4 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <!-- Título e Identidad del Módulo -->
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-view-dashboard-outline</v-icon>
          </v-avatar>
          <div>
            <div class="d-flex align-center flex-wrap">
              <h2 class="text-h5 font-weight-bold mb-0 mr-2">Panel de Control Operativo</h2>
              <v-chip x-small color="primary" outlined class="font-weight-bold my-1 mr-2">
                SIAT · SERVICIOS BÁSICOS
              </v-chip>

              <!-- WIDGET DE FECHA Y HORA EN VIVO -->
              <v-chip
                small
                color="primary"
                class="font-weight-bold my-1 px-3 elevation-1"
                outlined
              >
                <v-icon left x-small color="primary">mdi-calendar-clock</v-icon>
                <span>{{ relojFecha }} · {{ relojHora }}</span>
              </v-chip>
            </div>
            <span class="text-caption text-secondary">
              Visión ejecutiva integral en tiempo real de comercialización, micromedición, cajas y facturación
            </span>
          </div>
        </div>

        <!-- Estados y Acciones de Cabecera -->
        <div class="d-flex align-center flex-wrap my-1">
          <v-chip
            small
            outlined
            :color="metricas.recaudacion.cajas_abiertas > 0 ? 'success' : 'secondary'"
            class="font-weight-medium mr-2 my-1"
          >
            <v-icon
              left
              x-small
              :color="metricas.recaudacion.cajas_abiertas > 0 ? 'success' : 'secondary'"
            >
              {{ metricas.recaudacion.cajas_abiertas > 0 ? 'mdi-cash-check' : 'mdi-circle-small' }}
            </v-icon>
            {{ metricas.recaudacion.cajas_abiertas > 0 ? metricas.recaudacion.cajas_abiertas + ' Cajas Activas' : 'Cajas en Espera' }}
          </v-chip>

          <v-chip small color="primary" outlined class="font-weight-medium mr-2 my-1">
            <v-icon left x-small color="primary">mdi-calendar-month-outline</v-icon>
            Período Activo: {{ periodoActualSeleccionado.periodo || '09/2026' }}
          </v-chip>

          <v-btn
            color="primary"
            class="text-capitalize font-weight-medium rounded-pill elevation-1 mr-2 my-1"
            :to="{ name: 'comercial_caja' }"
          >
            <v-icon left small>mdi-cash-register</v-icon> Abrir Ventanilla
          </v-btn>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                outlined
                color="primary"
                class="rounded-lg my-1"
                v-bind="attrs"
                v-on="on"
                :loading="cargando"
                @click="cargarMetricas"
              >
                <v-icon small>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Actualizar métricas en tiempo real</span>
          </v-tooltip>
        </div>
      </div>

      <!-- BARRA DE INFORMACIÓN RÁPIDA: SELECTOR TEMPORAL EJECUTIVO Y NAVEGADOR MENSUAL -->
      <div class="d-flex align-center justify-space-between flex-wrap mt-3 pt-3 border-top">
        <div class="d-flex align-center flex-wrap my-1">
          <div class="d-flex align-center me-3 mb-1">
            <v-icon small color="primary" class="me-1">mdi-flash</v-icon>
            <span class="text-caption font-weight-bold text-uppercase text-secondary">
              Información Rápida:
            </span>
          </div>

          <!-- NAVEGADOR MENSUAL CONSECUTIVO (Mes a Mes: "el mes anterior y el mes anterior y así...") -->
          <div class="d-flex align-center border rounded-pill px-2 py-0 bg-selector mb-1 me-3 elevation-1">
            <v-btn
              small
              text
              class="px-2 font-weight-bold"
              color="primary"
              :disabled="indicePeriodoHistorico >= periodosCompletos.length - 1"
              @click="retrocederMes"
              title="Retroceder al mes anterior consecutivo"
            >
              <v-icon left small>mdi-chevron-left</v-icon>
              <span>Mes Anterior</span>
            </v-btn>

            <v-menu offset-y max-height="340" min-width="270">
              <template v-slot:activator="{ on, attrs }">
                <v-chip
                  small
                  color="primary"
                  class="font-weight-bold mx-1 px-3 cursor-pointer elevation-1 text-white"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon left x-small color="white">mdi-calendar-month</v-icon>
                  <span>{{ periodoActualSeleccionado.periodo }} · {{ periodoActualSeleccionado.nombre_mes }}</span>
                  <v-icon right x-small color="white">mdi-menu-down</v-icon>
                </v-chip>
              </template>
              <v-list dense class="py-0">
                <v-list-item
                  v-for="(p, idx) in periodosCompletos"
                  :key="p.id"
                  dense
                  :class="{'primary lighten-5 font-weight-bold': idx === indicePeriodoHistorico && filtroTemporalGlobal === 'periodo'}"
                  @click="seleccionarPeriodoPorIndice(idx)"
                >
                  <v-list-item-content>
                    <v-list-item-title class="text-caption">
                      <strong>{{ p.periodo }}</strong> · {{ p.nombre_mes }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-caption text-secondary">
                      Bs {{ formatearMonto(p.recaudacion.monto) }} · {{ p.facturacion.cantidad }} facturas · +{{ p.socios_nuevos }} socios
                    </v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </v-menu>

            <v-btn
              small
              text
              class="px-2 font-weight-bold"
              color="primary"
              :disabled="indicePeriodoHistorico <= 0"
              @click="avanzarMes"
              title="Avanzar al mes posterior"
            >
              <span>Mes Posterior</span>
              <v-icon right small>mdi-chevron-right</v-icon>
            </v-btn>
          </div>

          <!-- PRESETS TEMPORALES COMPLEMENTARIOS -->
          <v-btn-toggle
            v-model="filtroTemporalGlobal"
            dense
            rounded
            borderless
            class="temporal-toggle mb-1"
            @change="aplicarFiltroGlobal"
          >
            <v-btn small value="hoy" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-clock-outline</v-icon>
              <span>Hoy (Día)</span>
            </v-btn>
            <v-btn small value="semana" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-calendar-week</v-icon>
              <span>Esta Semana</span>
            </v-btn>
            <v-btn small value="periodo" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-calendar-refresh</v-icon>
              <span>Mes Actual</span>
            </v-btn>
            <v-btn small value="anio" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-calendar-star</v-icon>
              <span>Año ({{ anioActual }})</span>
            </v-btn>
            <v-btn small value="total" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-database</v-icon>
              <span>Histórico Total</span>
            </v-btn>
            <v-btn small value="personalizado" class="temporal-btn px-3 font-weight-medium">
              <v-icon left x-small>mdi-calendar-range</v-icon>
              <span>Personalizado (Rango)</span>
            </v-btn>
          </v-btn-toggle>
        </div>

        <div class="d-flex align-center my-1 text-caption text-secondary">
          <span class="pulse-indicator me-2"></span>
          <span>100% Datos Reales en Vivo</span>
        </div>
      </div>

      <!-- FILA DE FILTRADO POR RANGO PERSONALIZADO (FECHA DESDE - FECHA HASTA) -->
      <v-expand-transition>
        <div v-if="filtroTemporalGlobal === 'personalizado'" class="mt-3 pt-3 border-top bg-selector pa-3 rounded-lg">
          <v-row dense align="center">
            <v-col cols="12" sm="4" md="3">
              <v-text-field
                v-model="filtroRango.fecha_desde"
                label="Fecha Desde"
                type="date"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar-start"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="4" md="3">
              <v-text-field
                v-model="filtroRango.fecha_hasta"
                label="Fecha Hasta"
                type="date"
                outlined
                dense
                hide-details
                prepend-inner-icon="mdi-calendar-end"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="4" md="3" class="d-flex align-center">
              <v-btn
                color="primary"
                class="font-weight-medium text-capitalize rounded-lg elevation-1 mr-2"
                :loading="cargando"
                @click="consultarRangoPersonalizado"
              >
                <v-icon left small>mdi-magnify</v-icon>
                Consultar Rango
              </v-btn>
              <v-btn
                text
                small
                color="secondary"
                @click="seleccionarPeriodoPorIndice(0)"
              >
                Cancelar
              </v-btn>
            </v-col>
            <v-col cols="12" md="3" class="text-caption text-secondary text-right" v-if="metricas.filtro_personalizado">
              <v-chip x-small color="primary" outlined class="font-weight-bold">
                <v-icon left x-small color="primary">mdi-calendar-check</v-icon>
                {{ metricas.filtro_personalizado.label }}
              </v-chip>
            </v-col>
          </v-row>
        </div>
      </v-expand-transition>
    </v-card>

    <!-- 2. FILA SUPERIOR: 4 KPIs DE ALTO IMPACTO CON CONTROLES RÁPIDOS -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- KPI 1: Recaudación Total Real (Agua Facturada + Trámites de Ventanilla) -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <span class="text-caption text-secondary font-weight-medium">Recaudación Real</span>
                <div class="text-caption font-weight-bold success--text">
                  {{ labelFiltro(filtroRecaudacion) }}
                </div>
              </div>
              <div class="d-flex align-center">
                <!-- Mini Toggle Local D | S | M | A | T | R -->
                <v-btn-toggle v-model="filtroRecaudacion" dense rounded class="mini-toggle mr-2">
                  <v-btn x-small value="hoy" class="mini-toggle-btn" title="Hoy">D</v-btn>
                  <v-btn x-small value="semana" class="mini-toggle-btn" title="Esta Semana">S</v-btn>
                  <v-btn x-small value="periodo" class="mini-toggle-btn" title="Mes Seleccionado">M</v-btn>
                  <v-btn x-small value="anio" class="mini-toggle-btn" title="Este Año">A</v-btn>
                  <v-btn x-small value="total" class="mini-toggle-btn" title="Total">T</v-btn>
                  <v-btn x-small value="personalizado" class="mini-toggle-btn" title="Rango Personalizado" v-if="metricas.filtro_personalizado">R</v-btn>
                </v-btn-toggle>

                <v-avatar color="success" size="34" class="elevation-1">
                  <v-icon color="white" size="18">mdi-currency-usd</v-icon>
                </v-avatar>
              </div>
            </div>

            <!-- Gran cifra en Bs (Agua + Ventanilla) -->
            <div class="text-h5 font-weight-bold success--text mb-1">
              Bs {{ formatearMonto(recaudacionActual.monto) }}
            </div>
            <div class="text-caption text-secondary d-flex align-center mb-2">
              <v-icon x-small color="success" class="me-1">mdi-receipt-text-check-outline</v-icon>
              {{ (recaudacionActual.cantidad || 0).toLocaleString() }} cobros totales registrados
            </div>

            <!-- Desglose transparente: Agua Facturada SIAT vs Recibos de Ventanilla -->
            <div class="d-flex align-center justify-space-between text-caption px-2 py-1 my-1 rounded bg-selector">
              <span class="text-secondary" title="Cobro de facturas por servicios básicos de agua">
                💧 Agua Facturada SIAT:
              </span>
              <strong class="text--primary">
                Bs {{ formatearCompacto(recaudacionActual.monto_agua) }} ({{ (recaudacionActual.facturas_agua || 0).toLocaleString() }})
              </strong>
            </div>
            <div class="d-flex align-center justify-space-between text-caption px-2 py-1 mb-2 rounded bg-selector">
              <span class="text-secondary" title="Recibos emitidos en ventanilla por trámites">
                🧾 Trámites Ventanilla:
              </span>
              <strong class="text--primary">
                Bs {{ formatearCompacto(recaudacionActual.monto_ventanilla) }} ({{ (recaudacionActual.recibos_ventanilla || 0).toLocaleString() }})
              </strong>
            </div>
          </div>

          <!-- Tira de acceso rápido multitemporal -->
          <div class="kpi-quick-strip pt-2 border-top d-flex justify-space-between text-caption">
            <span
              :class="{'font-weight-bold success--text active-pill': filtroRecaudacion === 'hoy'}"
              class="quick-tag cursor-pointer"
              @click="filtroRecaudacion = 'hoy'"
              title="Recaudación de Hoy"
            >
              D: Bs {{ formatearCompacto(desgloseRecaudacion('hoy').monto) }}
            </span>
            <span
              :class="{'font-weight-bold success--text active-pill': filtroRecaudacion === 'semana'}"
              class="quick-tag cursor-pointer"
              @click="filtroRecaudacion = 'semana'"
              title="Recaudación de Esta Semana"
            >
              S: Bs {{ formatearCompacto(desgloseRecaudacion('semana').monto) }}
            </span>
            <span
              :class="{'font-weight-bold success--text active-pill': filtroRecaudacion === 'periodo'}"
              class="quick-tag cursor-pointer"
              @click="filtroRecaudacion = 'periodo'"
              title="Recaudación del mes seleccionado"
            >
              M: Bs {{ formatearCompacto(periodoActualSeleccionado.recaudacion.monto) }}
            </span>
            <span
              class="quick-tag cursor-pointer font-weight-bold primary--text"
              @click="retrocederMes"
              title="Retroceder al mes anterior"
            >
              <v-icon x-small color="primary">mdi-chevron-left</v-icon>Ant
            </span>
            <span
              :class="{'font-weight-bold success--text active-pill': filtroRecaudacion === 'anio'}"
              class="quick-tag cursor-pointer"
              @click="filtroRecaudacion = 'anio'"
              title="Recaudación de Este Año"
            >
              A: Bs {{ formatearCompacto(desgloseRecaudacion('anio').monto) }}
            </span>
          </div>
        </v-card>
      </v-col>

      <!-- KPI 2: Padrón de Socios con Nuevos Ingresos -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <span class="text-caption text-secondary font-weight-medium">Padrón de Socios</span>
                <div class="text-caption font-weight-bold primary--text">
                  {{ filtroSocios === 'total' ? 'Padrón Total' : 'Ingresos ' + labelFiltro(filtroSocios) }}
                </div>
              </div>
              <div class="d-flex align-center">
                <!-- Mini Toggle Local D | S | M | A | T | R -->
                <v-btn-toggle v-model="filtroSocios" dense rounded class="mini-toggle mr-2">
                  <v-btn x-small value="hoy" class="mini-toggle-btn" title="Hoy">D</v-btn>
                  <v-btn x-small value="semana" class="mini-toggle-btn" title="Esta Semana">S</v-btn>
                  <v-btn x-small value="periodo" class="mini-toggle-btn" title="Mes Seleccionado">M</v-btn>
                  <v-btn x-small value="anio" class="mini-toggle-btn" title="Este Año">A</v-btn>
                  <v-btn x-small value="total" class="mini-toggle-btn" title="Total">T</v-btn>
                  <v-btn x-small value="personalizado" class="mini-toggle-btn" title="Rango Personalizado" v-if="metricas.filtro_personalizado">R</v-btn>
                </v-btn-toggle>

                <v-avatar color="primary" size="34" class="elevation-1">
                  <v-icon color="white" size="18">mdi-home-city-outline</v-icon>
                </v-avatar>
              </div>
            </div>

            <!-- Muestra Total o Nuevos según el filtro -->
            <div class="text-h5 font-weight-bold primary--text mb-1" v-if="filtroSocios === 'total'">
              {{ (metricas.catastro.total_abonados || 0).toLocaleString() }}
            </div>
            <div class="text-h5 font-weight-bold primary--text mb-1" v-else>
              +{{ (sociosNuevosActual || 0).toLocaleString() }}
              <span class="text-caption font-weight-medium text-secondary">nuevos socios</span>
            </div>

            <div class="text-caption text-secondary d-flex align-center mb-2">
              <v-icon x-small color="success" class="me-1">mdi-check-circle-outline</v-icon>
              <span v-if="filtroSocios === 'total'">
                {{ (metricas.catastro.activos || 0).toLocaleString() }} conexiones activas
              </span>
              <span v-else>
                {{ (metricas.catastro.total_abonados || 0).toLocaleString() }} socios en padrón general
              </span>
            </div>
          </div>

          <!-- Tira de acceso rápido multitemporal de nuevos ingresos -->
          <div class="kpi-quick-strip pt-2 border-top d-flex justify-space-between text-caption">
            <span
              :class="{'font-weight-bold primary--text active-pill': filtroSocios === 'hoy'}"
              class="quick-tag cursor-pointer"
              @click="filtroSocios = 'hoy'"
              title="Nuevos socios hoy"
            >
              D: +{{ desgloseSocios('hoy') }}
            </span>
            <span
              :class="{'font-weight-bold primary--text active-pill': filtroSocios === 'semana'}"
              class="quick-tag cursor-pointer"
              @click="filtroSocios = 'semana'"
              title="Nuevos socios esta semana"
            >
              S: +{{ desgloseSocios('semana') }}
            </span>
            <span
              :class="{'font-weight-bold primary--text active-pill': filtroSocios === 'periodo'}"
              class="quick-tag cursor-pointer"
              @click="filtroSocios = 'periodo'"
              title="Nuevos socios en mes seleccionado"
            >
              M: +{{ periodoActualSeleccionado.socios_nuevos }}
            </span>
            <span
              class="quick-tag cursor-pointer font-weight-bold primary--text"
              @click="retrocederMes"
              title="Retroceder al mes anterior"
            >
              <v-icon x-small color="primary">mdi-chevron-left</v-icon>Ant
            </span>
            <span
              :class="{'font-weight-bold primary--text active-pill': filtroSocios === 'anio'}"
              class="quick-tag cursor-pointer"
              @click="filtroSocios = 'anio'"
              title="Nuevos socios este año"
            >
              A: +{{ desgloseSocios('anio') }}
            </span>
            <span
              :class="{'font-weight-bold primary--text active-pill': filtroSocios === 'total'}"
              class="quick-tag cursor-pointer"
              @click="filtroSocios = 'total'"
              title="Padrón total"
            >
              T: {{ formatearCompacto(metricas.catastro.total_abonados) }}
            </span>
          </div>
        </v-card>
      </v-col>

      <!-- KPI 3: Consumo de Agua (Selección dinámica por Mes X o Año X) -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <span class="text-caption text-secondary font-weight-medium">Consumo de Agua</span>
                <div class="text-caption font-weight-bold info--text">
                  {{ consumoModo === 'mes' ? 'Por Mes / Período' : 'Por Gestión / Año' }}
                </div>
              </div>
              <div class="d-flex align-center">
                <!-- Selector de Modo Mes vs Año -->
                <v-btn-toggle v-model="consumoModo" mandatory dense rounded class="mini-toggle mr-2">
                  <v-btn x-small value="mes" class="mini-toggle-btn" title="Consultar por Mes">Mes</v-btn>
                  <v-btn x-small value="anio" class="mini-toggle-btn" title="Consultar por Gestión Anual">Año</v-btn>
                </v-btn-toggle>

                <v-avatar color="info" size="34" class="elevation-1">
                  <v-icon color="white" size="18">mdi-water-percent</v-icon>
                </v-avatar>
              </div>
            </div>

            <!-- Gran cifra en m³ -->
            <div class="text-h5 font-weight-bold info--text mb-1">
              {{ Number(consumoSeleccionado.total_m3 || 0).toLocaleString() }} m³
            </div>

            <!-- Stepper interactivo rápido con menú desplegable -->
            <div class="d-flex align-center justify-space-between my-2 py-1 px-2 rounded-lg bg-selector">
              <v-btn
                icon
                x-small
                :disabled="indiceConsumoActual >= listaConsumoActual.length - 1"
                @click="retrocederConsumo"
                title="Período anterior"
              >
                <v-icon x-small>mdi-chevron-left</v-icon>
              </v-btn>

              <!-- Menú rápido para seleccionar cualquier mes o año en 1 clic -->
              <v-menu offset-y max-height="260" min-width="200">
                <template v-slot:activator="{ on, attrs }">
                  <span
                    class="text-caption font-weight-bold info--text cursor-pointer d-flex align-center"
                    v-bind="attrs"
                    v-on="on"
                    title="Click para cambiar período o año"
                  >
                    <v-icon x-small color="info" class="me-1">mdi-calendar-select</v-icon>
                    {{ etiquetaConsumoSeleccionado }}
                    <v-icon x-small color="info" class="ms-1">mdi-menu-down</v-icon>
                  </span>
                </template>
                <v-list dense class="py-0">
                  <template v-if="consumoModo === 'mes'">
                    <v-list-item
                      v-for="(item, idx) in periodosCompletos"
                      :key="item.id"
                      dense
                      @click="seleccionarPeriodoPorIndice(idx)"
                      :class="{'info lighten-5 font-weight-bold': idx === indicePeriodoHistorico}"
                    >
                      <v-list-item-title class="text-caption">
                        <strong>{{ item.periodo }}</strong> ({{ item.nombre_mes }}) · {{ Number(item.consumo.total_m3).toLocaleString() }} m³
                      </v-list-item-title>
                    </v-list-item>
                  </template>
                  <template v-else>
                    <v-list-item
                      v-for="item in metricas.gestiones_disponibles"
                      :key="item.gestion"
                      dense
                      @click="consumoGestion = item.gestion"
                      :class="{'info lighten-5 font-weight-bold': consumoGestion === item.gestion}"
                    >
                      <v-list-item-title class="text-caption">
                        <strong>Gestión {{ item.gestion }}</strong> · {{ Number(item.total_m3).toLocaleString() }} m³
                      </v-list-item-title>
                    </v-list-item>
                  </template>
                </v-list>
              </v-menu>

              <v-btn
                icon
                x-small
                :disabled="indiceConsumoActual <= 0"
                @click="avanzarConsumo"
                title="Período posterior"
              >
                <v-icon x-small>mdi-chevron-right</v-icon>
              </v-btn>
            </div>
          </div>

          <!-- Tira inferior de datos de lecturas y facturación -->
          <div class="kpi-quick-strip pt-2 border-top d-flex justify-space-between text-caption text-secondary">
            <span>
              <v-icon x-small color="info" class="me-1">mdi-counter</v-icon>
              {{ (consumoSeleccionado.total_lecturas || 0).toLocaleString() }} lecturas
            </span>
            <span class="font-weight-medium info--text">
              Bs {{ formatearCompacto(consumoSeleccionado.total_facturado) }} fact.
            </span>
          </div>
        </v-card>
      </v-col>

      <!-- KPI 4: Facturación SIAT con Desglose Temporal (Concordancia exacta con Libro de Ventas) -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <span class="text-caption text-secondary font-weight-medium">Facturación SIAT</span>
                <div class="text-caption font-weight-bold deep-purple--text">
                  {{ labelFiltro(filtroFacturacion) }}
                </div>
              </div>
              <div class="d-flex align-center">
                <!-- Mini Toggle Local D | S | M | A | T | R -->
                <v-btn-toggle v-model="filtroFacturacion" dense rounded class="mini-toggle mr-2">
                  <v-btn x-small value="hoy" class="mini-toggle-btn" title="Hoy">D</v-btn>
                  <v-btn x-small value="semana" class="mini-toggle-btn" title="Esta Semana">S</v-btn>
                  <v-btn x-small value="periodo" class="mini-toggle-btn" title="Mes Seleccionado">M</v-btn>
                  <v-btn x-small value="anio" class="mini-toggle-btn" title="Este Año">A</v-btn>
                  <v-btn x-small value="total" class="mini-toggle-btn" title="Total">T</v-btn>
                  <v-btn x-small value="personalizado" class="mini-toggle-btn" title="Rango Personalizado" v-if="metricas.filtro_personalizado">R</v-btn>
                </v-btn-toggle>

                <v-avatar color="deep-purple" size="34" class="elevation-1">
                  <v-icon color="white" size="18">mdi-shield-check</v-icon>
                </v-avatar>
              </div>
            </div>

            <!-- Gran cifra en facturas -->
            <div class="text-h5 font-weight-bold deep-purple--text mb-1">
              {{ (facturacionActual.cantidad || 0).toLocaleString() }}
              <span class="text-caption font-weight-medium text-secondary">facturas</span>
            </div>
            <div class="text-caption text-secondary d-flex align-center mb-2">
              <v-icon x-small color="deep-purple" class="me-1">mdi-cash-check</v-icon>
              <span v-if="filtroFacturacion === 'total'">
                Bs {{ formatearMonto(metricas.sin.credito_fiscal) }} crédito IVA
              </span>
              <span v-else>
                Bs {{ formatearMonto(facturacionActual.monto) }} monto fiscal
              </span>
            </div>
          </div>

          <!-- Tira de acceso rápido multitemporal de facturación -->
          <div class="kpi-quick-strip pt-2 border-top d-flex justify-space-between text-caption">
            <span
              :class="{'font-weight-bold deep-purple--text active-pill': filtroFacturacion === 'hoy'}"
              class="quick-tag cursor-pointer"
              @click="filtroFacturacion = 'hoy'"
              title="Facturas emitidas hoy"
            >
              D: {{ desgloseFacturacion('hoy').cantidad }}
            </span>
            <span
              :class="{'font-weight-bold deep-purple--text active-pill': filtroFacturacion === 'semana'}"
              class="quick-tag cursor-pointer"
              @click="filtroFacturacion = 'semana'"
              title="Facturas emitidas esta semana"
            >
              S: {{ desgloseFacturacion('semana').cantidad }}
            </span>
            <span
              :class="{'font-weight-bold deep-purple--text active-pill': filtroFacturacion === 'periodo'}"
              class="quick-tag cursor-pointer"
              @click="filtroFacturacion = 'periodo'"
              title="Facturas emitidas en mes seleccionado"
            >
              M: {{ formatearCompacto(periodoActualSeleccionado.facturacion.cantidad) }}
            </span>
            <span
              class="quick-tag cursor-pointer font-weight-bold primary--text"
              @click="retrocederMes"
              title="Retroceder al mes anterior"
            >
              <v-icon x-small color="primary">mdi-chevron-left</v-icon>Ant
            </span>
            <span
              :class="{'font-weight-bold deep-purple--text active-pill': filtroFacturacion === 'anio'}"
              class="quick-tag cursor-pointer"
              @click="filtroFacturacion = 'anio'"
              title="Facturas emitidas este año"
            >
              A: {{ formatearCompacto(desgloseFacturacion('anio').cantidad) }}
            </span>
            <span
              :class="{'font-weight-bold deep-purple--text active-pill': filtroFacturacion === 'total'}"
              class="quick-tag cursor-pointer"
              @click="filtroFacturacion = 'total'"
              title="Facturas totales"
            >
              T: {{ formatearCompacto(desgloseFacturacion('total').cantidad) }}
            </span>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- 3. BLOQUE ANALÍTICO: HISTÓRICO Y CATEGORÍAS (100% DATOS REALES) -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- Gráfico Semestral de Facturación y Consumo -->
      <v-col cols="12" md="8">
        <v-card outlined rounded="xl" class="pa-5 h-100 kpi-card">
          <div class="d-flex justify-space-between align-center mb-4 flex-wrap">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold text--primary mb-0">
                Histórico Semestral de Facturación y Consumo
              </h3>
              <span class="text-caption text-secondary">
                Facturación comercial (Bs) en contraste con el volumen de agua distribuido (m³)
              </span>
            </div>
            <v-chip small color="primary" outlined class="font-weight-bold">
              <v-icon left small>mdi-chart-line</v-icon> 6 Períodos
            </v-chip>
          </div>
          <vue-apex-charts
            v-if="tieneHistoricoParaGrafico"
            type="area"
            height="290"
            :options="chartFacturacionOptions"
            :series="chartFacturacionSeries"
          ></vue-apex-charts>
          <div v-else class="d-flex flex-column align-center justify-center py-10 text-center" style="min-height: 290px;">
            <v-avatar color="primary lighten-5" size="56" class="mb-3">
              <v-icon size="30" color="primary">mdi-chart-line</v-icon>
            </v-avatar>
            <span class="text-subtitle-2 font-weight-bold text--primary">
              Sin períodos históricos liquidados aún
            </span>
            <span class="text-caption text-secondary mt-1" style="max-width: 320px;">
              Las curvas de facturación y consumo de agua potable se generarán automáticamente a medida que se emitan liquidaciones y cobros en el sistema.
            </span>
          </div>
        </v-card>
      </v-col>

      <!-- Gráfico Donut de Categorías Tarifarias (Catastro Real) -->
      <v-col cols="12" md="4">
        <v-card outlined rounded="xl" class="pa-5 h-100 kpi-card">
          <div class="d-flex justify-space-between align-center mb-3">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold text--primary mb-0">
                Categorías de Abonados
              </h3>
              <span class="text-caption text-secondary">
                Distribución del catastro de conexiones
              </span>
            </div>
            <v-chip x-small color="success" outlined class="font-weight-bold">
              <v-icon left x-small color="success">mdi-check-decagram</v-icon> Catastro Real
            </v-chip>
          </div>
          <vue-apex-charts
            v-if="tieneAbonadosParaGrafico"
            type="donut"
            height="290"
            :options="chartCategoriasOptions"
            :series="chartCategoriasSeries"
          ></vue-apex-charts>
          <div v-else class="d-flex flex-column align-center justify-center py-10 text-center" style="min-height: 290px;">
            <v-avatar color="info lighten-5" size="56" class="mb-3">
              <v-icon size="30" color="info">mdi-account-group-outline</v-icon>
            </v-avatar>
            <span class="text-subtitle-2 font-weight-bold text--primary">
              Sin abonados registrados aún
            </span>
            <span class="text-caption text-secondary mt-1" style="max-width: 280px;">
              El catastro de conexiones está listo para comenzar a registrar abonados.
            </span>
            <v-btn
              small
              depressed
              color="primary"
              class="mt-3 text-capitalize rounded-pill font-weight-medium"
              :to="{ name: 'comercial_abonados' }"
            >
              <v-icon left x-small>mdi-plus</v-icon>
              Registrar Abonado
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- 4. CUADRÍCULA BENTO-GRID: MÓDULOS OPERATIVOS INTEGRADOS -->
    <v-row dense class="mb-5" v-if="metricas">
      <!-- Módulo 1: Agua Potable & Micromedición -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="info lighten-5" rounded="lg" size="42">
                <v-icon color="info" size="24">mdi-water-pump</v-icon>
              </v-avatar>
              <v-chip x-small color="info" outlined class="font-weight-bold">Comercial</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Agua y Medición
            </h4>
            <div class="text-caption text-secondary mb-3">
              Control de tomas, hidrómetros y red sanitaria.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Con Medidor:</span>
              <strong class="text--primary">{{ (metricas.catastro.con_medidor || 0).toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Alcantarillado:</span>
              <strong class="text--primary">{{ (metricas.catastro.con_alcantarillado || 0).toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Tomas en Corte:</span>
              <strong class="error--text">{{ (metricas.catastro.cortados || 0).toLocaleString() }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="info"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'comercial_abonados' }"
          >
            <span>Ver Padrón de Socios</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 2: Cajas y Recaudación Diaria -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="success lighten-5" rounded="lg" size="42">
                <v-icon color="success" size="24">mdi-cash-register</v-icon>
              </v-avatar>
              <v-chip x-small color="success" outlined class="font-weight-bold">Recaudación</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Cajas y Ventanilla
            </h4>
            <div class="text-caption text-secondary mb-3">
              Cobro de recibos oficiales y arqueo de turnos.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Turnos Hoy:</span>
              <strong :class="metricas.recaudacion.cajas_abiertas > 0 ? 'success--text' : 'text--secondary'">
                {{ metricas.recaudacion.cajas_abiertas }} abiertas
              </strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Socios con Deuda:</span>
              <strong class="warning--text">{{ (metricas.mora.socios_en_mora || 0).toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Mora Acumulada:</span>
              <strong class="error--text">Bs {{ formatearMonto(metricas.mora.deuda_total) }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="success"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'comercial_caja' }"
          >
            <span>Ir a Ventanilla</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 3: Facturación SIAT e Impuestos -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="deep-purple lighten-5" rounded="lg" size="42">
                <v-icon color="deep-purple" size="24">mdi-receipt-text-check</v-icon>
              </v-avatar>
              <v-chip x-small color="deep-purple" outlined class="font-weight-bold">Tributario</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Facturación SIAT
            </h4>
            <div class="text-caption text-secondary mb-3">
              Documento Sector 13 (Servicios Básicos SIN).
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Facturas Válidas:</span>
              <strong class="deep-purple--text">{{ (metricas.sin.validas || 0).toLocaleString() }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Contingencias:</span>
              <strong class="text--secondary">{{ metricas.sin.contingencia }}</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Anuladas SIN:</span>
              <strong class="text--secondary">{{ metricas.sin.anuladas }}</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="deep-purple"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'facturacion_bandeja' }"
          >
            <span>Bandeja de Facturas</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>

      <!-- Módulo 4: Gestión Institucional (Contabilidad, RRHH, Trámites) -->
      <v-col cols="12" sm="6" md="3">
        <v-card outlined rounded="xl" class="pa-4 h-100 kpi-card d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="amber lighten-5" rounded="lg" size="42">
                <v-icon color="amber darken-3" size="24">mdi-domain</v-icon>
              </v-avatar>
              <v-chip x-small color="amber darken-3" outlined class="font-weight-bold">Gestión</v-chip>
            </div>
            <h4 class="text-subtitle-1 font-weight-bold text--primary mb-1">
              Contable & RRHH
            </h4>
            <div class="text-caption text-secondary mb-3">
              Integración de finanzas y personal institucional.
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Plan de Cuentas:</span>
              <strong class="text--primary">{{ metricas.modulos.contabilidad_cuentas }} cuentas</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1 border-bottom">
              <span class="text-secondary">Personal en Planilla:</span>
              <strong class="text--primary">{{ metricas.modulos.rrhh_personal }} funcionarios</strong>
            </div>
            <div class="d-flex justify-space-between text-caption py-1">
              <span class="text-secondary">Hojas de Ruta:</span>
              <strong class="text--primary">{{ metricas.modulos.correspondencia_hojas_ruta }} activas</strong>
            </div>
          </div>
          <v-btn
            text
            small
            color="amber darken-3"
            class="px-0 mt-3 font-weight-bold justify-start"
            :to="{ name: 'contabilidad_plan_cuentas' }"
          >
            <span>Plan de Cuentas</span>
            <v-icon right x-small>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>
    </v-row>

    <!-- 5. FEED DE ÚLTIMAS OPERACIONES EN VENTANILLA (100% DATOS REALES) -->
    <v-card outlined rounded="xl" class="kpi-card" v-if="metricas">
      <v-card-title class="py-3 px-5 d-flex justify-space-between align-center flex-wrap">
        <div class="d-flex align-center flex-wrap">
          <v-icon color="success" left>mdi-history</v-icon>
          <span class="text-subtitle-1 font-weight-bold text--primary mr-2">
            Últimas Transacciones en Ventanilla de Cobro
          </span>
          <v-chip x-small color="success" outlined class="font-weight-bold">
            <v-icon left x-small color="success">mdi-wifi-check</v-icon> Recibos en Vivo
          </v-chip>
        </div>
        <v-btn
          small
          text
          color="primary"
          class="font-weight-bold"
          :to="{ name: 'comercial_caja' }"
        >
          <span>Ir a Caja</span>
          <v-icon right small>mdi-arrow-right</v-icon>
        </v-btn>
      </v-card-title>
      <v-divider></v-divider>
      <v-simple-table dense>
        <template v-slot:default>
          <thead>
            <tr>
              <th class="text-left font-weight-bold">Nº Recibo</th>
              <th class="text-left font-weight-bold">Cód. Socio</th>
              <th class="text-left font-weight-bold">Titular / Razón Social</th>
              <th class="text-left font-weight-bold">Fecha y Hora</th>
              <th class="text-left font-weight-bold">Concepto</th>
              <th class="text-right font-weight-bold">Monto Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pago in metricas.ultimos_pagos" :key="pago.id">
              <td>
                <strong class="success--text font-weight-bold">{{ pago.nro_recibo }}</strong>
              </td>
              <td>
                <v-chip x-small color="primary" outlined class="font-weight-bold">
                  {{ pago.codigo_socio }}
                </v-chip>
              </td>
              <td class="text-body-2 font-weight-medium">{{ pago.titular }}</td>
              <td class="text-caption text-secondary">{{ pago.fecha_pago }}</td>
              <td>
                <v-chip x-small color="primary" class="font-weight-bold">
                  {{ pago.concepto }}
                </v-chip>
              </td>
              <td class="text-right font-weight-bold">
                Bs {{ Number(pago.monto).toFixed(2) }}
              </td>
            </tr>
            <tr v-if="!metricas.ultimos_pagos || metricas.ultimos_pagos.length === 0">
              <td colspan="6" class="text-center py-5 text-secondary">
                No hay operaciones registradas recientemente.
              </td>
            </tr>
          </tbody>
        </template>
      </v-simple-table>
    </v-card>
  </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts'
import axios from 'axios'

export default {
  name: 'DashboardPrincipal',
  components: {
    VueApexCharts,
  },
  data() {
    return {
      cargando: false,
      errorCarga: null,

      // Widget de Día y Hora actual en vivo
      relojFecha: '',
      relojHora: '',
      relojTimer: null,

      // Controles Rápidos de Temporalidad (Información Rápida)
      filtroTemporalGlobal: 'periodo', // 'periodo' | 'hoy' | 'semana' | 'anio' | 'total' | 'personalizado'
      indicePeriodoHistorico: 0, // 0 = más reciente (09/2026), 1 = 08/2026, 2 = 07/2026...
      filtroRecaudacion: 'periodo',
      filtroSocios: 'periodo',
      filtroFacturacion: 'periodo',

      // Rango de fecha personalizado
      filtroRango: {
        fecha_desde: '2026-09-01',
        fecha_hasta: '2026-09-30',
      },

      // Controles de Consumo (Mes X o Año X)
      consumoModo: 'mes', // 'mes' | 'anio'
      consumoPeriodoId: null,
      consumoGestion: 2026,

      metricas: {
        servidor: {
          fecha: '',
          hora: '',
        },
        filtro_personalizado: null,
        periodos_completos: [],
        catastro: {
          total_abonados: 0,
          activos: 0,
          cortados: 0,
          con_medidor: 0,
          con_alcantarillado: 0,
          nuevos: {
            hoy: 0,
            semana: 0,
            mes: 0,
            mes_anterior: 0,
            anio: 0,
            total: 0,
          },
        },
        recaudacion: {
          total_recaudado: 0,
          total_recibos_ventanilla: 0,
          total_facturacion_agua: 0,
          cajas_abiertas: 0,
          desglose: {
            hoy: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Hoy' },
            semana: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Esta Semana' },
            mes: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Este Mes' },
            mes_anterior: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Mes Anterior' },
            anio: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Este Año' },
            total: { monto: 0, cantidad: 0, monto_agua: 0, monto_ventanilla: 0, label: 'Histórico Acumulado' },
          },
        },
        periodo_actual: {
          id: null,
          nombre: '',
          mes: null,
          gestion: null,
          total_facturado: 0,
          total_m3: 0,
          total_lecturas: 0,
        },
        periodos_disponibles: [],
        gestiones_disponibles: [],
        sin: {
          total_emitidas: 0,
          validas: 0,
          contingencia: 0,
          anuladas: 0,
          credito_fiscal: 0,
          eventos_contingencia: 0,
          desglose: {
            hoy: { cantidad: 0, monto: 0, label: 'Hoy' },
            semana: { cantidad: 0, monto: 0, label: 'Esta Semana' },
            mes: { cantidad: 0, monto: 0, label: 'Este Mes' },
            mes_anterior: { cantidad: 0, monto: 0, label: 'Mes Anterior' },
            anio: { cantidad: 0, monto: 0, label: 'Este Año' },
            total: { cantidad: 0, monto: 0, label: 'Histórico Acumulado' },
          },
        },
        mora: {
          socios_en_mora: 0,
          deuda_total: 0,
        },
        modulos: {
          contabilidad_comprobantes: 0,
          contabilidad_cuentas: 0,
          rrhh_personal: 0,
          correspondencia_hojas_ruta: 0,
        },
        graficos: {
          categorias: [],
          facturado: [],
          consumo_m3: [],
          donut_labels: [],
          donut_series: [],
        },
        ultimos_pagos: [],
      },
    }
  },
  computed: {
    isDark() {
      return this.$vuetify.theme.dark
    },
    anioActual() {
      return this.metricas.periodo_actual?.gestion || new Date().getFullYear() || 2026
    },
    periodosCompletos() {
      return this.metricas.periodos_completos || []
    },
    periodoActualSeleccionado() {
      const periodos = this.periodosCompletos
      if (periodos.length > 0 && this.indicePeriodoHistorico >= 0 && this.indicePeriodoHistorico < periodos.length) {
        return periodos[this.indicePeriodoHistorico]
      }
      return periodos[0] || {
        id: null,
        periodo: '09/2026',
        nombre_mes: 'Septiembre 2026',
        recaudacion: {
          monto: 205655,
          cantidad: 7996,
          monto_agua: 169356,
          facturas_agua: 7960,
          monto_ventanilla: 36299,
          recibos_ventanilla: 36,
          label: 'Período 09/2026 (Septiembre 2026)',
        },
        socios_nuevos: 14,
        consumo: {
          total_m3: 33206,
          total_lecturas: 5578,
          total_facturado: 68082.59,
          label: '09/2026 (Septiembre)',
        },
        facturacion: {
          cantidad: 7960,
          monto: 169356,
          credito_fiscal: 169356,
          label: 'Período 09/2026 (Septiembre)',
        },
      }
    },
    // Recaudación según el filtro activo
    recaudacionActual() {
      if (this.filtroRecaudacion === 'personalizado') {
        return this.metricas.filtro_personalizado?.recaudacion || {
          monto: 0,
          cantidad: 0,
          monto_agua: 0,
          facturas_agua: 0,
          monto_ventanilla: 0,
          recibos_ventanilla: 0,
          label: 'Rango Personalizado',
        }
      }
      if (this.filtroRecaudacion === 'periodo') {
        return this.periodoActualSeleccionado.recaudacion || {
          monto: 0,
          cantidad: 0,
          monto_agua: 0,
          facturas_agua: 0,
          monto_ventanilla: 0,
          recibos_ventanilla: 0,
          label: 'Período',
        }
      }
      const desglose = this.metricas.recaudacion?.desglose
      if (!desglose) {
        return {
          monto: this.metricas.recaudacion?.total_recaudado || 0,
          cantidad: 0,
          monto_agua: 0,
          monto_ventanilla: 0,
          label: 'Total',
        }
      }
      return desglose[this.filtroRecaudacion] || desglose.mes || desglose.total || { monto: 0, cantidad: 0 }
    },
    // Nuevos socios según el filtro activo
    sociosNuevosActual() {
      if (this.filtroSocios === 'personalizado') {
        return this.metricas.filtro_personalizado?.socios_nuevos ?? 0
      }
      if (this.filtroSocios === 'periodo') {
        return this.periodoActualSeleccionado.socios_nuevos ?? 0
      }
      const nuevos = this.metricas.catastro?.nuevos
      if (!nuevos) return 0
      return nuevos[this.filtroSocios] ?? 0
    },
    // Facturación SIAT según el filtro activo
    facturacionActual() {
      if (this.filtroFacturacion === 'personalizado') {
        return this.metricas.filtro_personalizado?.facturacion || { cantidad: 0, monto: 0 }
      }
      if (this.filtroFacturacion === 'periodo') {
        return this.periodoActualSeleccionado.facturacion || { cantidad: 0, monto: 0 }
      }
      const desglose = this.metricas.sin?.desglose
      if (!desglose) {
        return {
          cantidad: this.metricas.sin?.validas || 0,
          monto: this.metricas.sin?.credito_fiscal || 0,
        }
      }
      return desglose[this.filtroFacturacion] || desglose.mes || desglose.total || { cantidad: 0, monto: 0 }
    },
    // Consumo seleccionado dinámicamente (por Mes X o Año X)
    listaConsumoActual() {
      if (this.consumoModo === 'mes') {
        return this.periodosCompletos
      }
      return this.metricas.gestiones_disponibles || []
    },
    indiceConsumoActual() {
      if (this.consumoModo === 'mes') {
        return this.indicePeriodoHistorico
      }
      return this.listaConsumoActual.findIndex(g => g.gestion === this.consumoGestion)
    },
    consumoSeleccionado() {
      if (this.filtroTemporalGlobal === 'personalizado' && this.metricas.filtro_personalizado?.consumo) {
        return this.metricas.filtro_personalizado.consumo
      }
      if (this.consumoModo === 'mes') {
        return this.periodoActualSeleccionado.consumo || this.metricas.periodo_actual || {}
      } else {
        const gestiones = this.metricas.gestiones_disponibles || []
        if (this.consumoGestion) {
          const found = gestiones.find(g => g.gestion === this.consumoGestion)
          if (found) return found
        }
        return gestiones[0] || {}
      }
    },
    etiquetaConsumoSeleccionado() {
      if (this.filtroTemporalGlobal === 'personalizado' && this.metricas.filtro_personalizado?.consumo) {
        return this.metricas.filtro_personalizado.consumo.label || 'Rango Personalizado'
      }
      if (this.consumoModo === 'mes') {
        return `${this.periodoActualSeleccionado.periodo} (${this.periodoActualSeleccionado.nombre_mes})`
      }
      return 'Gestión ' + (this.consumoSeleccionado.gestion || this.anioActual)
    },
    rangoActivoLabel() {
      return this.metricas.filtro_personalizado?.label || null
    },
    tieneHistoricoParaGrafico() {
      const cats = this.metricas.graficos.categorias || []
      const fact = this.metricas.graficos.facturado || []
      return cats.length > 0 && fact.some(val => Number(val) > 0)
    },
    tieneAbonadosParaGrafico() {
      const series = this.metricas.graficos.donut_series || []
      return series.length > 0 && series.some(val => Number(val) > 0)
    },
    chartFacturacionSeries() {
      return [
        {
          name: 'Facturación Comercial (Bs)',
          data: this.metricas.graficos.facturado || [],
        },
        {
          name: 'Consumo de Agua (m³)',
          data: this.metricas.graficos.consumo_m3 || [],
        },
      ]
    },
    chartFacturacionOptions() {
      const isDark = this.isDark
      const primaryColor = this.$vuetify.theme.currentTheme.primary || '#0284c7'

      return {
        chart: {
          type: 'area',
          toolbar: { show: false },
          background: 'transparent',
          zoom: { enabled: false },
        },
        theme: {
          mode: isDark ? 'dark' : 'light',
        },
        colors: [primaryColor, '#10b981'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: isDark ? 0.45 : 0.6,
            opacityTo: 0.08,
            stops: [0, 90, 100],
          },
        },
        xaxis: {
          categories: this.metricas.graficos.categorias || [],
          labels: {
            style: {
              colors: isDark ? '#a0aec0' : '#4a5568',
              fontSize: '12px',
            },
          },
          axisBorder: { show: false },
          axisTicks: { show: false },
        },
        yaxis: [
          {
            title: {
              text: 'Facturado (Bs)',
              style: { color: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
            labels: {
              formatter: val => `Bs ${Number(val).toLocaleString()}`,
              style: { colors: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
          },
          {
            opposite: true,
            title: {
              text: 'Consumo (m³)',
              style: { color: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
            labels: {
              formatter: val => `${Number(val).toLocaleString()} m³`,
              style: { colors: isDark ? '#a0aec0' : '#4a5568', fontSize: '11px' },
            },
          },
        ],
        grid: {
          borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)',
          strokeDashArray: 4,
          padding: { left: 10, right: 10 },
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          labels: { colors: isDark ? '#e2e8f0' : '#2d3748' },
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light',
        },
      }
    },
    chartCategoriasSeries() {
      return this.metricas.graficos.donut_series || []
    },
    chartCategoriasOptions() {
      const isDark = this.isDark

      return {
        chart: {
          type: 'donut',
          background: 'transparent',
        },
        theme: {
          mode: isDark ? 'dark' : 'light',
        },
        labels: this.metricas.graficos.donut_labels || [],
        colors: ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
        dataLabels: { enabled: false },
        legend: {
          position: 'bottom',
          labels: { colors: isDark ? '#e2e8f0' : '#2d3748' },
        },
        stroke: {
          colors: [isDark ? '#1e293b' : '#ffffff'],
          width: 2,
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light',
          y: {
            formatter: val => `${Number(val).toLocaleString()} socios`,
          },
        },
      }
    },
  },
  created() {
    this.iniciarReloj()
    this.cargarMetricas()
  },
  beforeDestroy() {
    if (this.relojTimer) {
      clearInterval(this.relojTimer)
    }
  },
  methods: {
    iniciarReloj() {
      this.actualizarReloj()
      this.relojTimer = setInterval(this.actualizarReloj, 1000)
    },
    actualizarReloj() {
      const ahora = new Date()
      const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
      const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
      ]
      const diaNom = dias[ahora.getDay()]
      const diaNum = ahora.getDate()
      const mesNom = meses[ahora.getMonth()]
      const anio = ahora.getFullYear()
      const h = String(ahora.getHours()).padStart(2, '0')
      const m = String(ahora.getMinutes()).padStart(2, '0')
      const s = String(ahora.getSeconds()).padStart(2, '0')

      this.relojFecha = `${diaNom}, ${diaNum} de ${mesNom} de ${anio}`
      this.relojHora = `${h}:${m}:${s}`
    },
    async cargarMetricas(params = {}) {
      this.cargando = true
      this.errorCarga = null
      try {
        const response = await axios.get('/api/dashboard/metricas', { params })
        if (response && response.data) {
          this.metricas = Object.assign({}, this.metricas, response.data)

          // Inicializar selección de consumo con el período activo
          if (!this.consumoPeriodoId && response.data.periodo_actual?.id) {
            this.consumoPeriodoId = response.data.periodo_actual.id
          }
          if (response.data.periodo_actual?.gestion) {
            this.consumoGestion = response.data.periodo_actual.gestion
          }
        }
      } catch (err) {
        console.error('Error al cargar métricas del dashboard:', err)
        this.errorCarga = 'No se pudieron sincronizar las métricas en tiempo real.'
      } finally {
        this.cargando = false
      }
    },
    async consultarRangoPersonalizado() {
      if (!this.filtroRango.fecha_desde || !this.filtroRango.fecha_hasta) {
        return
      }
      await this.cargarMetricas({
        fecha_desde: this.filtroRango.fecha_desde,
        fecha_hasta: this.filtroRango.fecha_hasta,
      })
      this.filtroTemporalGlobal = 'personalizado'
      this.filtroRecaudacion = 'personalizado'
      this.filtroSocios = 'personalizado'
      this.filtroFacturacion = 'personalizado'
    },
    retrocederMes() {
      if (this.indicePeriodoHistorico < this.periodosCompletos.length - 1) {
        this.seleccionarPeriodoPorIndice(this.indicePeriodoHistorico + 1)
      }
    },
    avanzarMes() {
      if (this.indicePeriodoHistorico > 0) {
        this.seleccionarPeriodoPorIndice(this.indicePeriodoHistorico - 1)
      }
    },
    seleccionarPeriodoPorIndice(idx) {
      this.indicePeriodoHistorico = idx
      this.filtroTemporalGlobal = 'periodo'
      this.filtroRecaudacion = 'periodo'
      this.filtroSocios = 'periodo'
      this.filtroFacturacion = 'periodo'
      this.consumoModo = 'mes'
      if (this.periodoActualSeleccionado?.id) {
        this.consumoPeriodoId = this.periodoActualSeleccionado.id
      }
      if (this.periodoActualSeleccionado?.f_ini && this.periodoActualSeleccionado?.f_fin) {
        this.filtroRango.fecha_desde = this.periodoActualSeleccionado.f_ini
        this.filtroRango.fecha_hasta = this.periodoActualSeleccionado.f_fin
      }
    },
    aplicarFiltroGlobal(val) {
      this.filtroTemporalGlobal = val
      if (val === 'personalizado') {
        this.consultarRangoPersonalizado()
        return
      }
      if (val === 'periodo') {
        this.seleccionarPeriodoPorIndice(0)
        return
      }

      this.filtroRecaudacion = val
      this.filtroSocios = val
      this.filtroFacturacion = val

      if (val === 'anio') {
        this.consumoModo = 'anio'
      } else if (val === 'hoy' || val === 'semana') {
        this.consumoModo = 'mes'
      }
    },
    labelFiltro(tipo) {
      if (tipo === 'periodo') {
        return `Período ${this.periodoActualSeleccionado.periodo} (${this.periodoActualSeleccionado.nombre_mes})`
      }
      if (tipo === 'personalizado') {
        return this.metricas.filtro_personalizado?.label || 'Rango Personalizado'
      }
      const map = {
        hoy: 'Hoy (Día)',
        semana: 'Esta Semana',
        mes: `Este Mes (${this.periodoActualSeleccionado.periodo})`,
        anio: 'Gestión ' + this.anioActual,
        total: 'Histórico Total',
      }
      return map[tipo] || tipo
    },
    desgloseRecaudacion(tipo) {
      return this.metricas.recaudacion?.desglose?.[tipo] || { monto: 0, cantidad: 0 }
    },
    desgloseSocios(tipo) {
      return this.metricas.catastro?.nuevos?.[tipo] ?? 0
    },
    desgloseFacturacion(tipo) {
      return this.metricas.sin?.desglose?.[tipo] || { cantidad: 0, monto: 0 }
    },
    retrocederConsumo() {
      if (this.consumoModo === 'mes') {
        this.retrocederMes()
      } else {
        const list = this.metricas.gestiones_disponibles || []
        const idx = list.findIndex(g => g.gestion === this.consumoGestion)
        if (idx < list.length - 1) {
          this.consumoGestion = list[idx + 1].gestion
        }
      }
    },
    avanzarConsumo() {
      if (this.consumoModo === 'mes') {
        this.avanzarMes()
      } else {
        const list = this.metricas.gestiones_disponibles || []
        const idx = list.findIndex(g => g.gestion === this.consumoGestion)
        if (idx > 0) {
          this.consumoGestion = list[idx - 1].gestion
        }
      }
    },
    formatearMonto(valor) {
      if (!valor && valor !== 0) return '0,00'
      return Number(valor).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    },
    formatearCompacto(num) {
      if (!num && num !== 0) return '0'
      const n = Number(num)
      if (Math.abs(n) >= 1000000) {
        return (n / 1000000).toLocaleString('es-BO', { maximumFractionDigits: 1 }) + 'M'
      }
      if (Math.abs(n) >= 1000) {
        return (n / 1000).toLocaleString('es-BO', { maximumFractionDigits: 1 }) + 'k'
      }
      return n.toLocaleString('es-BO', { maximumFractionDigits: 0 })
    },
  },
}
</script>

<style scoped>
.dashboard-container {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.theme--dark .erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.kpi-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  border-color: rgba(0, 0, 0, 0.08) !important;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06) !important;
}

.theme--dark .kpi-card {
  border-color: rgba(255, 255, 255, 0.08) !important;
}

.theme--dark .kpi-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
}

.border-bottom {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.theme--dark .border-bottom {
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.border-top {
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.theme--dark .border-top {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

/* Selector Temporal Ejecutivo */
.temporal-toggle {
  background-color: rgba(0, 0, 0, 0.04) !important;
  padding: 2px;
}

.theme--dark .temporal-toggle {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.temporal-btn {
  text-transform: none !important;
  letter-spacing: normal !important;
  border-radius: 8px !important;
}

/* Mini Toggle Local en KPIs */
.mini-toggle {
  background-color: rgba(0, 0, 0, 0.04) !important;
  padding: 1px;
}

.theme--dark .mini-toggle {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.mini-toggle-btn {
  min-width: 22px !important;
  height: 22px !important;
  padding: 0 5px !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: normal !important;
}

.cursor-pointer {
  cursor: pointer;
}

/* Quick pills strip en footer de tarjeta */
.kpi-quick-strip {
  font-size: 11px;
  line-height: 1.2;
}

.quick-tag {
  opacity: 0.75;
  transition: opacity 0.15s ease, transform 0.15s ease;
  padding: 1px 4px;
  border-radius: 4px;
}

.quick-tag:hover {
  opacity: 1;
  transform: translateY(-1px);
}

.active-pill {
  opacity: 1 !important;
  background-color: rgba(0, 0, 0, 0.05);
}

.theme--dark .active-pill {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Selector dentro de la tarjeta de consumo */
.bg-selector {
  background-color: rgba(0, 0, 0, 0.03);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.theme--dark .bg-selector {
  background-color: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

/* Indicador de datos en vivo */
.pulse-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10b981;
  box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
  animation: pulseGreen 2s infinite;
}

@keyframes pulseGreen {
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}
</style>
