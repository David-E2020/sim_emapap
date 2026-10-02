<template>
  <div class="registro-periodos-container">
    <!-- Encabezado y KPIs -->
    <v-row class="mb-4">
      <v-col cols="12" md="7">
        <div class="d-flex align-center">
          <v-avatar color="primary lighten-4" size="48" class="mr-3">
            <v-icon color="primary" size="30">mdi-calendar-sync</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Registro y Ciclo de Períodos</h2>
            <span class="text-caption text-secondary">
              Control del ciclo de vida operativo: Toma de Lecturas &rarr; Facturación &rarr; Cierre y Cortes
            </span>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end align-center flex-wrap">
        <!-- Chip de proceso o descarga en segundo plano -->
        <v-chip
          v-if="exportacionJob.procesando"
          color="warning"
          class="font-weight-bold mr-2 mb-1 pulse-chip cursor-pointer"
          outlined
          @click="modalExportarHistorico = true"
          title="Ver progreso de exportación en segundo plano"
        >
          <v-progress-circular indeterminate size="16" width="2" color="warning" class="mr-1"></v-progress-circular>
          Exportando ({{ exportacionJob.progreso || 0 }}%)
        </v-chip>

        <v-chip
          v-else-if="exportacionJob.completado && exportacionJob.jobId"
          color="success"
          class="font-weight-bold mr-2 mb-1 cursor-pointer elevation-1 text-white"
          @click="modalExportarHistorico = true"
          title="Reporte listo para descargar"
        >
          <v-icon left small color="white">mdi-check-decagram</v-icon>
          Reporte Listo (Descargar)
        </v-chip>

        <!-- Botón Exportar Histórico -->
        <v-btn
          color="teal darken-1"
          class="mr-2 mb-1 text-none font-weight-bold text-white elevation-2"
          @click="abrirModalExportarHistorico"
        >
          <v-icon left>mdi-file-excel-box</v-icon>
          Exportar Histórico
        </v-btn>

        <v-btn
          color="primary"
          class="mr-2 mb-1 text-none font-weight-bold"
          elevation="2"
          @click="abrirModalNuevoPeriodo"
        >
          <v-icon left>mdi-plus-circle</v-icon>
          Abrir Período ("Otro")
        </v-btn>
        <v-btn
          outlined
          color="secondary"
          class="text-none mb-1"
          :loading="cargando"
          @click="cargarPeriodos"
        >
          <v-icon left>mdi-refresh</v-icon>
          Refrescar
        </v-btn>
      </v-col>
    </v-row>

    <!-- Tarjetas de Estado del Ciclo -->
    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card outlined class="rounded-lg">
          <v-card-text class="d-flex align-center py-3">
            <v-avatar color="blue lighten-5" size="42" class="mr-3">
              <v-icon color="blue darken-2">mdi-calendar-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Período Activo</div>
              <div class="text-h6 font-weight-bold">{{ periodoActivo ? periodoActivo.periodo : 'N/A' }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card outlined class="rounded-lg">
          <v-card-text class="d-flex align-center py-3">
            <v-avatar :color="obtenerColorEstado(periodoActivo ? periodoActivo.estado : '') + ' lighten-5'" size="42" class="mr-3">
              <v-icon :color="obtenerColorEstado(periodoActivo ? periodoActivo.estado : '')">
                {{ obtenerIconoEstado(periodoActivo ? periodoActivo.estado : '') }}
              </v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Estado Ciclo</div>
              <div class="text-subtitle-1 font-weight-bold">
                <v-chip x-small :color="obtenerColorEstado(periodoActivo ? periodoActivo.estado : '')" dark class="font-weight-bold">
                  {{ periodoActivo ? periodoActivo.estado_label : 'Sin Datos' }}
                </v-chip>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card outlined class="rounded-lg">
          <v-card-text class="d-flex align-center py-3">
            <v-avatar color="green lighten-5" size="42" class="mr-3">
              <v-icon color="green darken-2">mdi-counter</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Abonados en Ciclo</div>
              <div class="text-h6 font-weight-bold">
                {{ periodoActivo ? (periodoActivo.lecturas_count || 0) : 0 }}
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card outlined class="rounded-lg">
          <v-card-text class="d-flex align-center py-3">
            <v-avatar color="purple lighten-5" size="42" class="mr-3">
              <v-icon color="purple darken-2">mdi-history</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Historial Períodos</div>
              <div class="text-h6 font-weight-bold">{{ periodos.length }} Registrados</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Tabla Principal de Períodos (Formato FoxPro idéntico) -->
    <v-card elevation="2" class="rounded-lg">
      <v-card-title class="py-3 px-4 d-flex align-center justify-space-between border-bottom">
        <div class="d-flex align-center">
          <v-icon color="primary" class="mr-2">mdi-table-clock</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Historial Cronológico de Períodos de Facturación</span>
        </div>
        <v-text-field
          v-model="busqueda"
          prepend-inner-icon="mdi-magnify"
          placeholder="Buscar período o gestión..."
          dense
          outlined
          hide-details
          clearable
          style="max-width: 260px;"
        ></v-text-field>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="periodos"
        :search="busqueda"
        :loading="cargando"
        :items-per-page="15"
        :footer-props="{ 'items-per-page-options': [10, 15, 25, 50, -1] }"
        class="elevation-0"
        no-data-text="No existen períodos de facturación registrados."
      >
        <!-- Formato Mes -->
        <template v-slot:item.mes="{ item }">
          <v-chip small outlined class="font-weight-bold text-center" style="min-width: 36px; justify-content: center;">
            {{ String(item.mes).padStart(2, '0') }}
          </v-chip>
        </template>

        <!-- Formato Año -->
        <template v-slot:item.gestion="{ item }">
          <span class="font-weight-bold">{{ item.gestion }}</span>
        </template>

        <!-- Período -->
        <template v-slot:item.periodo="{ item }">
          <router-link
            :to="{ path: '/comercial/lecturas', query: { periodo_id: item.id, periodo: item.periodo } }"
            class="text-decoration-none font-weight-bold primary--text d-inline-flex align-center"
            title="Ir a Toma de Lecturas de este período"
          >
            {{ item.periodo }}
            <v-icon x-small color="primary" class="ml-1">mdi-arrow-right</v-icon>
          </router-link>
        </template>

        <!-- Fechas Formateadas -->
        <template v-slot:item.fecha_inicio_consumo="{ item }">
          <span>{{ formatearFecha(item.fecha_inicio_consumo) }}</span>
        </template>
        <template v-slot:item.fecha_fin_consumo="{ item }">
          <span>{{ formatearFecha(item.fecha_fin_consumo) }}</span>
        </template>
        <template v-slot:item.fecha_vencimiento_pago="{ item }">
          <span class="font-weight-medium text-error">{{ formatearFecha(item.fecha_vencimiento_pago) }}</span>
        </template>

        <!-- Selector / Badge de Estado -->
        <template v-slot:item.estado="{ item }">
          <v-menu offset-y>
            <template v-slot:activator="{ on, attrs }">
              <v-chip
                small
                :color="obtenerColorEstado(item.estado)"
                dark
                v-bind="attrs"
                v-on="on"
                class="font-weight-bold cursor-pointer"
              >
                <v-icon left x-small>{{ obtenerIconoEstado(item.estado) }}</v-icon>
                {{ item.estado_label || item.estado }}
                <v-icon right x-small>mdi-menu-down</v-icon>
              </v-chip>
            </template>
            <v-list dense>
              <v-subheader class="text-caption font-weight-bold">CAMBIAR ETAPA COMERCIAL</v-subheader>
              <v-list-item @click="solicitarCambioEstado(item, 'LECTURA')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="blue" small>mdi-counter</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Lectura (Toma en Campo)</v-list-item-title>
              </v-list-item>
              <v-list-item @click="solicitarCambioEstado(item, 'FACTURACION')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="orange darken-2" small>mdi-cash-register</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Facturación (Cobranza y Avisos)</v-list-item-title>
              </v-list-item>
              <v-list-item @click="solicitarCambioEstado(item, 'CERRADO')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="grey darken-2" small>mdi-lock</v-icon>
                </v-list-item-icon>
                <v-list-item-title>Cerrado (Vencido y Mora)</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>

        <!-- Lecturas / Abonados -->
        <template v-slot:item.lecturas_count="{ item }">
          <span class="font-weight-bold">{{ item.lecturas_count || 0 }}</span>
        </template>

        <!-- Botones de Acción Operativa por Fila -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <!-- 0. Ir a Toma de Lecturas del Período con flecha -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="blue darken-2"
                  class="mr-1"
                  v-bind="attrs"
                  v-on="on"
                  :to="{ path: '/comercial/lecturas', query: { periodo_id: item.id, periodo: item.periodo } }"
                >
                  <v-icon small>mdi-arrow-right-bold-circle</v-icon>
                </v-btn>
              </template>
              <span>Ver Toma de Lecturas (Período {{ item.periodo }})</span>
            </v-tooltip>

            <!-- 1. Reporte: Planilla de Campo (Lecturas) -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="blue darken-2"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirDialogoPlanillaCampo(item)"
                >
                  <v-icon small>mdi-clipboard-list-outline</v-icon>
                </v-btn>
              </template>
              <span>Planilla de Campo (Toma de Lecturas)</span>
            </v-tooltip>

            <!-- 2. Reporte: Resumen de Operaciones por Zonas -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="green darken-2"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirDialogoResumenZonas(item)"
                >
                  <v-icon small>mdi-chart-pie</v-icon>
                </v-btn>
              </template>
              <span>Resumen de Facturación por Zonas</span>
            </v-tooltip>

            <!-- 3. Reporte: Nómina de Cortes Masivos -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="red darken-2"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirDialogoNominaCortes(item)"
                >
                  <v-icon small>mdi-pipe-disconnected</v-icon>
                </v-btn>
              </template>
              <span>Nómina de Cortes por Mora</span>
            </v-tooltip>

            <!-- Editar Fechas -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="grey darken-1"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirModalEditarPeriodo(item)"
                >
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
              </template>
              <span>Editar Fechas y Observaciones</span>
            </v-tooltip>

            <!-- Eliminar Período -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="red"
                  v-bind="attrs"
                  v-on="on"
                  @click="confirmarEliminarPeriodo(item)"
                >
                  <v-icon small>mdi-delete-outline</v-icon>
                </v-btn>
              </template>
              <span>Eliminar Período</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: ABRIR NUEVO PERÍODO ("OTRO") -->
    <v-dialog v-model="modalNuevoPeriodo" max-width="500px" persistent>
      <v-card class="rounded-lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left dark>mdi-calendar-plus</v-icon>
          Apertura de Nuevo Período Mensual
        </v-card-title>
        <v-card-text class="pt-4">
          <v-alert type="info" text dense class="mb-3">
            Al abrir el período, se inicializarán automáticamente las órdenes de lectura para todos los abonados activos.
          </v-alert>

          <v-row dense>
            <v-col cols="6">
              <v-text-field
                v-model.number="formPeriodo.mes"
                label="Mes (1 - 12)"
                type="number"
                min="1"
                max="12"
                outlined
                dense
                required
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model.number="formPeriodo.gestion"
                label="Año / Gestión"
                type="number"
                min="2020"
                max="2050"
                outlined
                dense
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formPeriodo.fecha_inicio_consumo"
                label="Fecha Desde (Inicio Consumo)"
                type="date"
                outlined
                dense
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formPeriodo.fecha_fin_consumo"
                label="Fecha Hasta (Fin Consumo)"
                type="date"
                outlined
                dense
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formPeriodo.fecha_vencimiento_pago"
                label="Fecha Vencimiento Pago"
                type="date"
                outlined
                dense
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="formPeriodo.observaciones"
                label="Observaciones (Opcional)"
                outlined
                dense
                rows="2"
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevoPeriodo = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="px-4 font-weight-bold"
            :loading="guardandoPeriodo"
            @click="guardarNuevoPeriodo"
          >
            <v-icon left>mdi-check</v-icon>
            Abrir Período
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: EDITAR PERÍODO -->
    <v-dialog v-model="modalEditarPeriodo" max-width="500px" persistent>
      <v-card class="rounded-lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left dark>mdi-pencil</v-icon>
          Editar Fechas del Período {{ formEdicion.periodo }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                v-model="formEdicion.fecha_inicio_consumo"
                label="Fecha Desde (Inicio Consumo)"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formEdicion.fecha_fin_consumo"
                label="Fecha Hasta (Fin Consumo)"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formEdicion.fecha_vencimiento_pago"
                label="Fecha Vencimiento Pago"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="formEdicion.observaciones"
                label="Observaciones"
                outlined
                dense
                rows="2"
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="modalEditarPeriodo = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="px-4 font-weight-bold"
            :loading="guardandoEdicion"
            @click="guardarEdicionPeriodo"
          >
            <v-icon left>mdi-content-save</v-icon>
            Guardar Cambios
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: OPCIONES DE PLANILLA DE CAMPO -->
    <v-dialog v-model="modalPlanillaCampo" max-width="520px">
      <v-card class="rounded-lg">
        <v-card-title class="blue darken-3 white--text py-3">
          <v-icon left dark>mdi-clipboard-text</v-icon>
          Planilla de Campo para Toma de Lecturas
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary mb-3">
            Emite las planillas de lectura para las cuadrillas de campo con los códigos de abonado, titular, dirección y medidor asignado.
          </p>

          <v-select
            v-model="opcionesPlanilla.id_zona"
            :items="zonas"
            item-text="nombre"
            item-value="id"
            label="Filtrar por Zona Comercial"
            outlined
            dense
            clearable
            prepend-inner-icon="mdi-map-marker"
          ></v-select>

          <v-switch
            v-model="opcionesPlanilla.a_ciegas"
            label="Modalidad A Ciegas (Ocultar lectura anterior para evitar fraudes en campo)"
            color="primary"
            class="mt-1"
            hint="En esta modalidad, la casilla de lectura anterior aparece bloqueada forzando al lecturador a revisar físicamente el medidor."
            persistent-hint
          ></v-switch>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-btn
            color="green darken-2"
            outlined
            class="text-none font-weight-bold"
            :loading="exportandoExcel"
            @click="exportarPlanillaExcel"
          >
            <v-icon left>mdi-file-excel</v-icon>
            Exportar Excel
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn text @click="modalPlanillaCampo = false">Cerrar</v-btn>
          <v-btn
            color="primary"
            class="font-weight-bold"
            @click="verPlanillaPdf"
          >
            <v-icon left>mdi-file-pdf-box</v-icon>
            Generar PDF
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: OPCIONES DE RESUMEN POR ZONAS (VENTANA EMERGENTE) -->
    <v-dialog v-model="modalResumenZonas" max-width="520px">
      <v-card class="rounded-lg">
        <v-card-title class="green darken-3 white--text py-3">
          <v-icon left dark>mdi-chart-pie</v-icon>
          Resumen de Facturación por Zonas
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-body-2 mb-3">
            Emite el informe consolidado comercial del período
            <strong>{{ periodoResumenZonas ? periodoResumenZonas.periodo : '' }}</strong>,
            con el desglose de abonados, volumen consumido (m³), facturación por consumo, alcantarillado, cargos fijos y mora.
          </p>
          <v-alert type="info" text dense class="mb-0">
            Puede ver la vista previa en documento PDF o descargar directamente la planilla en formato Excel / CSV.
          </v-alert>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-btn
            color="green darken-2"
            outlined
            class="text-none font-weight-bold"
            :loading="exportandoExcel"
            @click="exportarResumenZonasExcel"
          >
            <v-icon left>mdi-file-excel</v-icon>
            Exportar Excel
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn text @click="modalResumenZonas = false">Cerrar</v-btn>
          <v-btn
            color="primary"
            class="font-weight-bold"
            @click="verResumenZonasPdf"
          >
            <v-icon left>mdi-file-pdf-box</v-icon>
            Ver PDF
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: OPCIONES DE NÓMINA DE CORTES (VENTANA EMERGENTE) -->
    <v-dialog v-model="modalNominaCortes" max-width="520px">
      <v-card class="rounded-lg">
        <v-card-title class="red darken-3 white--text py-3">
          <v-icon left dark>mdi-pipe-disconnected</v-icon>
          Planilla de Cortes Masivos por Mora
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-body-2 mb-3">
            Genera la lista operativa de cortes de servicio por mora acumulada del período
            <strong>{{ periodoNominaCortes ? periodoNominaCortes.periodo : '' }}</strong>
            para asignación a técnicos de campo.
          </p>

          <v-select
            v-model="opcionesNominaCortes.meses_mora"
            :items="[
              { text: 'A partir de 2 Meses de Mora (Estándar)', value: 2 },
              { text: 'A partir de 3 Meses de Mora (Alta Mora)', value: 3 },
              { text: 'A partir de 4 Meses de Mora (Crítico)', value: 4 },
              { text: 'A partir de 6 Meses de Mora (Extremo)', value: 6 },
            ]"
            item-text="text"
            item-value="value"
            label="Criterio de Mora Mínima"
            outlined
            dense
          ></v-select>

          <v-select
            v-model="opcionesNominaCortes.id_zona"
            :items="zonas"
            item-text="nombre"
            item-value="id"
            label="Filtrar por Zona Comercial (Opcional)"
            outlined
            dense
            clearable
            prepend-inner-icon="mdi-map-marker"
          ></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-btn
            color="green darken-2"
            outlined
            class="text-none font-weight-bold"
            :loading="exportandoExcel"
            @click="exportarNominaCortesExcel"
          >
            <v-icon left>mdi-file-excel</v-icon>
            Exportar Excel
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn text @click="modalNominaCortes = false">Cerrar</v-btn>
          <v-btn
            color="red darken-2"
            dark
            class="font-weight-bold"
            @click="verNominaCortesPdf"
          >
            <v-icon left>mdi-file-pdf-box</v-icon>
            Ver PDF
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL DE REPORTES PDF (VENTANA EMERGENTE SEGURA CON TOKEN JWT) -->
    <modal-visor-pdf
      v-model="modalVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :url-excel="urlExcelVisorPdf"
      :nombre-descarga="nombrePdfVisor"
      :nombre-excel="nombreExcelVisor"
    ></modal-visor-pdf>

    <!-- DIÁLOGO CONFIRMAR CAMBIO DE ESTADO -->
    <v-dialog v-model="modalConfirmarEstado" max-width="450px">
      <v-card class="rounded-lg">
        <v-card-title class="orange darken-3 white--text py-3">
          <v-icon left dark>mdi-alert-circle</v-icon>
          Confirmar Transición de Estado
        </v-card-title>
        <v-card-text class="pt-4">
          <p>
            ¿Está seguro de cambiar el período <strong>{{ periodoSeleccionado ? periodoSeleccionado.periodo : '' }}</strong>
            al estado <strong>{{ nuevoEstadoObjetivo }}</strong>?
          </p>
          <v-alert v-if="nuevoEstadoObjetivo === 'FACTURACION'" type="warning" text dense>
            Al pasar a <strong>Facturación</strong>, se liquidarán los consumos con las tarifas vigentes y se habilitará el cobro en ventanilla de caja.
          </v-alert>
          <v-alert v-if="nuevoEstadoObjetivo === 'CERRADO'" type="info" text dense>
            Al <strong>Cerrar</strong> el período, todas las deudas pendientes se consolidarán como mora mensual y se actualizarán los abonados sujetos a corte.
          </v-alert>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="modalConfirmarEstado = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="font-weight-bold"
            :loading="cambiandoEstado"
            @click="ejecutarCambioEstado"
          >
            Confirmar Cambio
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- 7. MODAL DE EXPORTACIÓN HISTÓRICA MASIVA EN SEGUNDO PLANO (EXCEL / CSV) -->
    <v-dialog
      v-model="modalExportarHistorico"
      max-width="960"
      persistent
      scrollable
    >
      <v-card class="rounded-xl">
        <!-- Cabecera del Modal -->
        <v-toolbar color="teal darken-2" dark flat dense>
          <v-avatar color="teal darken-4" size="36" class="mr-3">
            <v-icon small color="white">mdi-file-excel-box</v-icon>
          </v-avatar>
          <v-toolbar-title class="font-weight-bold text-subtitle-1">
            Exportación Histórica Masiva de Lecturas y Abonados
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-chip x-small color="teal lighten-3" class="teal--text text--darken-4 font-weight-bold mr-2">
            HILO EN SEGUNDO PLANO
          </v-chip>
          <v-btn icon @click="modalExportarHistorico = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <!-- Cuerpo del Modal -->
        <v-card-text class="pt-4">
          <!-- VISTA 1: CONFIGURACIÓN INICIAL DE PARÁMETROS -->
          <div v-if="!exportacionJob.procesando && !exportacionJob.completado">
            <!-- 1.1 Alcance Temporal (Presets de Rango) -->
            <v-card outlined class="rounded-lg pa-3 mb-4 bg-light-panel">
              <div class="d-flex align-center justify-space-between mb-2 flex-wrap">
                <div class="d-flex align-center">
                  <v-icon small color="teal darken-2" class="mr-2">mdi-calendar-range</v-icon>
                  <span class="font-weight-bold text-caption text-uppercase text-secondary">
                    1. Alcance Temporal del Reporte:
                  </span>
                </div>
                <v-chip x-small color="success" outlined class="font-weight-bold">
                  Recomendado: 5 Años (Hasta 60 Períodos)
                </v-chip>
              </div>

              <v-btn-toggle
                v-model="opcionesExportar.tipo_rango"
                mandatory
                dense
                rounded
                class="temporal-export-toggle flex-wrap mb-2"
              >
                <v-btn small value="5_anios" class="px-3 font-weight-bold">
                  <v-icon left x-small>mdi-history</v-icon>
                  Últimos 5 Años (60 Meses)
                </v-btn>
                <v-btn small value="3_anios" class="px-3 font-weight-bold">
                  <v-icon left x-small>mdi-calendar-clock</v-icon>
                  Últimos 3 Años (36 Meses)
                </v-btn>
                <v-btn small value="1_anio" class="px-3 font-weight-bold">
                  <v-icon left x-small>mdi-calendar-month</v-icon>
                  Último Año (12 Meses)
                </v-btn>
                <v-btn small value="actual" class="px-3 font-weight-bold">
                  <v-icon left x-small>mdi-clock-check</v-icon>
                  Período Activo
                </v-btn>
                <v-btn small value="personalizado" class="px-3 font-weight-bold">
                  <v-icon left x-small>mdi-calendar-filter</v-icon>
                  Personalizado (Rango)
                </v-btn>
              </v-btn-toggle>

              <!-- Selectores de Rango Personalizado -->
              <v-expand-transition>
                <v-row dense class="mt-2" v-if="opcionesExportar.tipo_rango === 'personalizado'">
                  <v-col cols="12" sm="6">
                    <v-select
                      v-model="opcionesExportar.periodo_id_desde"
                      :items="periodosDisponiblesExportar"
                      item-text="periodo"
                      item-value="id"
                      label="Período Inicial (Desde)"
                      outlined
                      dense
                      hide-details
                      prepend-inner-icon="mdi-calendar-start"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" sm="6">
                    <v-select
                      v-model="opcionesExportar.periodo_id_hasta"
                      :items="periodosDisponiblesExportar"
                      item-text="periodo"
                      item-value="id"
                      label="Período Final (Hasta)"
                      outlined
                      dense
                      hide-details
                      prepend-inner-icon="mdi-calendar-end"
                    ></v-select>
                  </v-col>
                </v-row>
              </v-expand-transition>
            </v-card>

            <!-- 1.2 Filtros Opcionales de Segmentación -->
            <v-card outlined class="rounded-lg pa-3 mb-4">
              <div class="d-flex align-center mb-2">
                <v-icon small color="teal darken-2" class="mr-2">mdi-filter-variant</v-icon>
                <span class="font-weight-bold text-caption text-uppercase text-secondary">
                  2. Filtros de Segmentación (Opcionales):
                </span>
              </div>
              <v-row dense>
                <v-col cols="12" sm="4">
                  <v-select
                    v-model="opcionesExportar.id_zona"
                    :items="zonasExportar"
                    item-text="nombre"
                    item-value="id"
                    label="Zona / Barrio"
                    outlined
                    dense
                    clearable
                    hide-details
                    placeholder="Todas las Zonas"
                  ></v-select>
                </v-col>
                <v-col cols="12" sm="4">
                  <v-select
                    v-model="opcionesExportar.id_categoria"
                    :items="categoriasExportar"
                    item-text="nombre"
                    item-value="id"
                    label="Categoría Tarifaria"
                    outlined
                    dense
                    clearable
                    hide-details
                    placeholder="Todas las Categorías"
                  ></v-select>
                </v-col>
                <v-col cols="12" sm="4">
                  <v-select
                    v-model="opcionesExportar.estado_pago"
                    :items="[
                      { text: 'Todos los Estados', value: 'TODOS' },
                      { text: 'Pendiente de Pago', value: 'PENDIENTE' },
                      { text: 'Pagado', value: 'PAGADO' },
                      { text: 'En Convenio', value: 'EN_CONVENIO' },
                    ]"
                    label="Estado de Cobro"
                    outlined
                    dense
                    hide-details
                  ></v-select>
                </v-col>
              </v-row>
            </v-card>

            <!-- 1.3 Formato de Archivo -->
            <v-card outlined class="rounded-lg pa-3 mb-4">
              <div class="d-flex align-center mb-2">
                <v-icon small color="teal darken-2" class="mr-2">mdi-file-cog</v-icon>
                <span class="font-weight-bold text-caption text-uppercase text-secondary">
                  3. Formato del Archivo:
                </span>
              </div>
              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-card
                    outlined
                    :class="{'teal lighten-5 border-teal': opcionesExportar.formato === 'xlsx'}"
                    class="pa-3 rounded-lg cursor-pointer h-100"
                    @click="opcionesExportar.formato = 'xlsx'"
                  >
                    <div class="d-flex align-start">
                      <v-radio
                        :value="'xlsx'"
                        v-model="opcionesExportar.formato"
                        class="ma-0 mr-2"
                        color="teal darken-2"
                      ></v-radio>
                      <div>
                        <div class="font-weight-bold text-body-2 teal--text text--darken-3">
                          Excel Oficial (.xlsx)
                        </div>
                        <span class="text-caption text-secondary">
                          Diseño corporativo EMAPAP con grilla, colores, fórmulas de sumatoria y hojas organizadas por gestión anual.
                        </span>
                      </div>
                    </div>
                  </v-card>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-card
                    outlined
                    :class="{'teal lighten-5 border-teal': opcionesExportar.formato === 'csv'}"
                    class="pa-3 rounded-lg cursor-pointer h-100"
                    @click="opcionesExportar.formato = 'csv'"
                  >
                    <div class="d-flex align-start">
                      <v-radio
                        :value="'csv'"
                        v-model="opcionesExportar.formato"
                        class="ma-0 mr-2"
                        color="teal darken-2"
                      ></v-radio>
                      <div>
                        <div class="font-weight-bold text-body-2 teal--text text--darken-3">
                          CSV Masivo para Excel (.csv)
                        </div>
                        <span class="text-caption text-secondary">
                          Delimitador punto y coma (;) con UTF-8 BOM. Se abre nativamente en Excel a máxima velocidad sin límites de memoria.
                        </span>
                      </div>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-card>

            <!-- 1.4 Resumen de la Selección y Grilla de Períodos -->
            <v-card outlined class="rounded-lg pa-3 mb-2">
              <div class="d-flex align-center justify-space-between mb-2">
                <div class="d-flex align-center">
                  <v-icon small color="teal darken-2" class="mr-2">mdi-table-clock</v-icon>
                  <span class="font-weight-bold text-caption text-uppercase text-secondary">
                    4. Grilla de Períodos Incluidos y Estimación:
                  </span>
                </div>
                <div>
                  <v-chip small color="teal darken-1" class="font-weight-bold mr-1 text-white">
                    {{ periodosSeleccionadosFiltrados.length }} Períodos
                  </v-chip>
                  <v-chip small color="blue darken-2" class="font-weight-bold text-white">
                    ~{{ totalLecturasEstimadas.toLocaleString() }} Filas Estimadas
                  </v-chip>
                </div>
              </div>

              <!-- Tabla de previsualización compacta de los períodos que se procesarán -->
              <v-data-table
                :headers="headersPrevisualizacionExportar"
                :items="periodosSeleccionadosFiltrados"
                dense
                :items-per-page="5"
                class="elevation-0 border rounded"
                no-data-text="No hay períodos en el rango seleccionado"
              >
                <template v-slot:item.periodo="{ item }">
                  <span class="font-weight-bold teal--text text--darken-3">{{ item.periodo }}</span>
                </template>
                <template v-slot:item.total_lecturas="{ item }">
                  <span>{{ Number(item.total_lecturas || 0).toLocaleString() }} abonados</span>
                </template>
                <template v-slot:item.estado="{ item }">
                  <v-chip x-small :color="obtenerColorEstado(item.estado)" outlined class="font-weight-bold">
                    {{ item.estado }}
                  </v-chip>
                </template>
              </v-data-table>

              <!-- Selector Interactivo de Columnas -->
              <div class="mt-3 pt-2 border-top">
                <div class="d-flex align-center justify-space-between flex-wrap mb-2">
                  <div>
                    <span class="text-caption font-weight-bold text-secondary text-uppercase">
                      Columnas que se generarán en la planilla:
                    </span>
                    <span
                      class="text-caption ml-1 font-weight-bold"
                      :class="columnasSeleccionadas.length > 0 ? 'teal--text text--darken-2' : 'red--text'"
                    >
                      ({{ columnasSeleccionadas.length }} de {{ todasLasColumnas.length }} seleccionadas)
                    </span>
                  </div>
                  <div>
                    <v-btn
                      x-small
                      text
                      color="primary"
                      class="text-none px-2 font-weight-bold"
                      @click="seleccionarTodasColumnas"
                    >
                      <v-icon left x-small>mdi-checkbox-multiple-marked</v-icon>
                      Todas
                    </v-btn>
                    <v-btn
                      x-small
                      text
                      color="teal darken-2"
                      class="text-none px-2 font-weight-bold"
                      @click="seleccionarColumnasEsenciales"
                    >
                      <v-icon left x-small>mdi-star</v-icon>
                      Esenciales
                    </v-btn>
                    <v-btn
                      x-small
                      text
                      color="secondary"
                      class="text-none px-2"
                      @click="deseleccionarTodasColumnas"
                    >
                      <v-icon left x-small>mdi-checkbox-blank-outline</v-icon>
                      Mínimo
                    </v-btn>
                  </div>
                </div>

                <div class="d-flex flex-wrap">
                  <v-chip
                    v-for="col in todasLasColumnas"
                    :key="col.id"
                    x-small
                    class="ma-1 font-weight-medium cursor-pointer elevation-1"
                    :color="columnasSeleccionadas.includes(col.id) ? (col.destacado ? col.destacado : 'teal darken-1') : 'grey lighten-4'"
                    :dark="columnasSeleccionadas.includes(col.id)"
                    :outlined="!columnasSeleccionadas.includes(col.id)"
                    @click="toggleColumnaExportar(col.id)"
                    :title="columnasSeleccionadas.includes(col.id) ? 'Clic para excluir del reporte' : 'Clic para incluir en el reporte'"
                  >
                    <v-icon left x-small v-if="columnasSeleccionadas.includes(col.id)">mdi-check</v-icon>
                    <v-icon left x-small v-else color="grey">mdi-plus</v-icon>
                    {{ col.label }}
                  </v-chip>
                </div>
              </div>
            </v-card>
          </div>

          <!-- VISTA 2: PROCESAMIENTO EN SEGUNDO PLANO EN VIVO -->
          <div v-else-if="exportacionJob.procesando" class="py-6 px-4 text-center">
            <v-avatar color="teal lighten-5" size="80" class="mb-4">
              <v-icon color="teal darken-2" size="48">mdi-progress-download</v-icon>
            </v-avatar>

            <h3 class="text-h6 font-weight-bold mb-1 teal--text text--darken-3">
              Generando Reporte Histórico en Segundo Plano
            </h3>
            <p class="text-caption text-secondary mb-4">
              El proceso se ejecuta en un hilo asíncrono para garantizar alta velocidad y cero bloqueos de pantalla.
            </p>

            <v-card outlined class="pa-4 rounded-xl mb-4 bg-light-panel">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-caption font-weight-bold text-uppercase text-secondary">
                  Progreso General:
                </span>
                <span class="text-h6 font-weight-bold teal--text text--darken-2">
                  {{ exportacionJob.progreso || 0 }}%
                </span>
              </div>

              <v-progress-linear
                :value="exportacionJob.progreso || 0"
                height="22"
                rounded
                striped
                color="teal darken-1"
                class="mb-3"
              >
                <template v-slot:default>
                  <strong class="text-white text-caption">{{ exportacionJob.progreso || 0 }}%</strong>
                </template>
              </v-progress-linear>

              <div class="d-flex align-center justify-space-between text-caption text-secondary border-top pt-2">
                <div>
                  <v-icon x-small color="teal">mdi-calendar-sync</v-icon>
                  Período en Proceso: <strong>{{ exportacionJob.periodo_actual || 'Iniciando...' }}</strong>
                </div>
                <div>
                  <v-icon x-small color="teal">mdi-check-circle-outline</v-icon>
                  Avance: <strong>{{ exportacionJob.periodos_procesados || 0 }} / {{ exportacionJob.total_periodos || 0 }} períodos</strong>
                </div>
                <div>
                  <v-icon x-small color="teal">mdi-database-export</v-icon>
                  Filas Procesadas: <strong>{{ (exportacionJob.filas_exportadas || 0).toLocaleString() }}</strong>
                </div>
              </div>

              <div class="text-caption text-secondary mt-2 text-center" v-if="exportacionJob.mensaje">
                <em>{{ exportacionJob.mensaje }}</em>
              </div>
            </v-card>

            <v-alert
              dense
              outlined
              type="info"
              class="text-caption text-left rounded-lg mb-4"
            >
              <strong>Trabajo en segundo plano:</strong> Puedes cerrar esta ventana con total tranquilidad y continuar utilizando el sistema. El proceso seguirá ejecutándose y te mostraremos un aviso en cabecera en cuanto el archivo esté disponible para descargar.
            </v-alert>

            <div class="d-flex justify-center">
              <v-btn
                outlined
                color="secondary"
                class="mr-3 text-none"
                @click="modalExportarHistorico = false"
              >
                <v-icon left small>mdi-window-minimize</v-icon>
                Continuar Trabajando (Minimizar)
              </v-btn>
              <v-btn
                outlined
                color="error"
                class="text-none"
                @click="cancelarExportacionJob"
              >
                <v-icon left small>mdi-stop-circle-outline</v-icon>
                Cancelar Exportación
              </v-btn>
            </div>
          </div>

          <!-- VISTA 3: EXPORTACIÓN COMPLETADA Y DESCARGA LISTA -->
          <div v-else-if="exportacionJob.completado" class="py-6 px-4 text-center">
            <v-avatar color="green lighten-5" size="86" class="mb-3">
              <v-icon color="green darken-2" size="54">mdi-check-decagram</v-icon>
            </v-avatar>

            <h3 class="text-h6 font-weight-bold mb-1 green--text text--darken-3">
              ¡Reporte Histórico Preparado con Éxito!
            </h3>
            <p class="text-caption text-secondary mb-4">
              El archivo fue consolidado y empaquetado en segundo plano, listo para su almacenamiento o análisis.
            </p>

            <v-card outlined class="pa-4 rounded-xl mb-4 bg-light-panel text-left">
              <v-row dense>
                <v-col cols="12" sm="6">
                  <div class="text-caption text-secondary font-weight-medium">Archivo Generado:</div>
                  <div class="font-weight-bold teal--text text--darken-3 text-body-2">
                    {{ exportacionJob.nombre_archivo || 'Reporte_Historico.xlsx' }}
                  </div>
                </v-col>
                <v-col cols="12" sm="3">
                  <div class="text-caption text-secondary font-weight-medium">Peso del Archivo:</div>
                  <div class="font-weight-bold text-body-2">
                    {{ exportacionJob.peso_archivo || '-' }}
                  </div>
                </v-col>
                <v-col cols="12" sm="3">
                  <div class="text-caption text-secondary font-weight-medium">Total Registros:</div>
                  <div class="font-weight-bold text-body-2">
                    {{ (exportacionJob.filas_exportadas || 0).toLocaleString() }} filas
                  </div>
                </v-col>
              </v-row>
            </v-card>

            <div class="d-flex justify-center align-center flex-wrap">
              <v-btn
                color="success"
                x-large
                class="font-weight-bold mr-3 elevation-3 text-none px-6"
                :loading="descargandoArchivoJob"
                @click="descargarReporteExportado"
              >
                <v-icon left>mdi-download</v-icon>
                Descargar Reporte ({{ exportacionJob.peso_archivo }})
              </v-btn>

              <v-btn
                text
                color="secondary"
                class="text-none font-weight-medium"
                @click="resetearFormularioExportacion"
              >
                <v-icon left small>mdi-refresh</v-icon>
                Configurar Otra Exportación
              </v-btn>
            </div>
          </div>
        </v-card-text>

        <!-- Botones de Acción en Vista de Configuración -->
        <v-card-actions class="px-4 py-3 border-top" v-if="!exportacionJob.procesando && !exportacionJob.completado">
          <v-btn text color="secondary" @click="modalExportarHistorico = false">
            Cerrar
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn
            color="teal darken-2"
            dark
            class="px-5 font-weight-bold text-none elevation-2"
            :loading="exportacionJob.iniciando"
            :disabled="periodosSeleccionadosFiltrados.length === 0"
            @click="iniciarExportacionHistorica"
          >
            <v-icon left>mdi-play-circle-outline</v-icon>
            Iniciar Exportación en Segundo Plano ({{ periodosSeleccionadosFiltrados.length }} Períodos)
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'RegistroPeriodos',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      cargando: false,
      periodos: [],
      zonas: [],
      busqueda: '',

      headers: [
        { text: 'MES', value: 'mes', align: 'center', width: '70px', sortable: true },
        { text: 'AÑO', value: 'gestion', align: 'center', width: '75px', sortable: true },
        { text: 'PERÍODO', value: 'periodo', align: 'center', width: '105px' },
        { text: 'FECHA DESDE', value: 'fecha_inicio_consumo', width: '110px' },
        { text: 'FECHA HASTA', value: 'fecha_fin_consumo', width: '110px' },
        { text: 'FECHA VNCMTO.', value: 'fecha_vencimiento_pago', width: '115px' },
        { text: 'ESTADO', value: 'estado', align: 'center', width: '140px' },
        { text: 'ABONADOS', value: 'lecturas_count', align: 'center', width: '95px' },
        { text: 'ACCIONES OPERATIVAS', value: 'acciones', align: 'center', sortable: false, width: '215px' },
      ],

      // Nuevo Período
      modalNuevoPeriodo: false,
      guardandoPeriodo: false,
      formPeriodo: {
        mes: 1,
        gestion: 2026,
        fecha_inicio_consumo: '',
        fecha_fin_consumo: '',
        fecha_vencimiento_pago: '',
        observaciones: '',
      },

      // Edición
      modalEditarPeriodo: false,
      guardandoEdicion: false,
      formEdicion: {
        id: null,
        periodo: '',
        fecha_inicio_consumo: '',
        fecha_fin_consumo: '',
        fecha_vencimiento_pago: '',
        observaciones: '',
      },

      // Planilla de Campo
      modalPlanillaCampo: false,
      periodoPlanilla: null,
      opcionesPlanilla: {
        id_zona: null,
        a_ciegas: false,
      },

      // Resumen por Zonas
      modalResumenZonas: false,
      periodoResumenZonas: null,

      // Nómina de Cortes
      modalNominaCortes: false,
      periodoNominaCortes: null,
      opcionesNominaCortes: {
        meses_mora: 2,
        id_zona: null,
      },

      // Cambio de estado
      modalConfirmarEstado: false,
      cambiandoEstado: false,
      periodoSeleccionado: null,
      nuevoEstadoObjetivo: '',

      // Visor PDF y Exportaciones
      modalVisorPdf: false,
      urlVisorPdf: '',
      urlExcelVisorPdf: '',
      tituloVisorPdf: '',
      nombrePdfVisor: '',
      nombreExcelVisor: '',
      exportandoExcel: false,

      // Exportación Masiva Histórica (Segundo Plano)
      modalExportarHistorico: false,
      periodosDisponiblesExportar: [],
      zonasExportar: [],
      categoriasExportar: [],
      cargandoMetadatosExportar: false,
      opcionesExportar: {
        tipo_rango: '5_anios',
        periodo_id_desde: null,
        periodo_id_hasta: null,
        id_zona: null,
        id_categoria: null,
        estado_pago: 'TODOS',
        formato: 'xlsx',
      },
      todasLasColumnas: [
        { id: 'nro', label: 'N° Correlativo', default: true },
        { id: 'gestion', label: 'Gestión', default: true },
        { id: 'mes', label: 'Mes', default: true },
        { id: 'periodo', label: 'Período', default: true },
        { id: 'codigo', label: 'Código Socio', default: true, destacado: 'primary' },
        { id: 'nombre_completo', label: 'Nombre Abonado', default: true, destacado: 'primary' },
        { id: 'numero_documento', label: 'C.I. / NIT', default: true },
        { id: 'zona_nombre', label: 'Zona / Barrio', default: true },
        { id: 'calle_nombre', label: 'Calle / Dirección', default: true },
        { id: 'numero_vivienda', label: 'N° Casa', default: true },
        { id: 'categoria_nombre', label: 'Categoría', default: true },
        { id: 'tiene_medidor', label: 'Tiene Medidor', default: true },
        { id: 'medidor_serie', label: 'N° Serie Medidor', default: true },
        { id: 'lectura_anterior', label: 'Lectura Anterior', default: true, destacado: 'info' },
        { id: 'lectura_actual', label: 'Lectura Actual', default: true, destacado: 'info' },
        { id: 'consumo_m3', label: 'Consumo (m³)', default: true, destacado: 'info' },
        { id: 'es_estimada', label: 'Estimada', default: true },
        { id: 'monto_agua', label: 'Monto Agua', default: true },
        { id: 'monto_alcantarillado', label: 'Alcantarillado', default: true },
        { id: 'monto_descuento_ley1886', label: 'Ley 1886', default: true },
        { id: 'total_facturado', label: 'Total Facturado (Bs)', default: true, destacado: 'success' },
        { id: 'estado_pago', label: 'Estado Pago', default: true },
        { id: 'fecha_lectura', label: 'Fecha Lectura', default: true },
        { id: 'observacion_lectura', label: 'Observaciones', default: true },
      ],
      columnasSeleccionadas: [
        'nro', 'gestion', 'mes', 'periodo', 'codigo', 'nombre_completo',
        'numero_documento', 'zona_nombre', 'calle_nombre', 'numero_vivienda',
        'categoria_nombre', 'tiene_medidor', 'medidor_serie', 'lectura_anterior',
        'lectura_actual', 'consumo_m3', 'es_estimada', 'monto_agua',
        'monto_alcantarillado', 'monto_descuento_ley1886', 'total_facturado',
        'estado_pago', 'fecha_lectura', 'observacion_lectura'
      ],
      exportacionJob: {
        jobId: null,
        procesando: false,
        completado: false,
        iniciando: false,
        progreso: 0,
        periodo_actual: '',
        periodos_procesados: 0,
        total_periodos: 0,
        filas_exportadas: 0,
        mensaje: '',
        nombre_archivo: '',
        peso_archivo: '',
        timer: null,
      },
      descargandoArchivoJob: false,
      headersPrevisualizacionExportar: [
        { text: 'PERÍODO', value: 'periodo', align: 'center', width: '100px' },
        { text: 'AÑO', value: 'gestion', align: 'center', width: '70px' },
        { text: 'MES', value: 'mes', align: 'center', width: '60px' },
        { text: 'ESTADO', value: 'estado', align: 'center', width: '110px' },
        { text: 'ABONADOS ESTIMADOS', value: 'total_lecturas', align: 'right' },
      ],
      tituloVisorPdf: 'Visor de Reporte Comercial',
      urlExcelVisorPdf: '',
      nombrePdfVisor: 'reporte_comercial.pdf',
      nombreExcelVisor: 'reporte_comercial.csv',
      exportandoExcel: false,
    };
  },
  computed: {
    periodoActivo() {
      if (!this.periodos || this.periodos.length === 0) return null;
      // Buscar primero el que esté en FACTURACION o LECTURA o ABIERTO
      const activo = this.periodos.find(
        (p) => ['LECTURA', 'FACTURACION', 'ABIERTO', 'FACTURADO', 'L', 'F'].includes(String(p.estado).toUpperCase())
      );
      return activo || this.periodos[0];
    },
    periodosSeleccionadosFiltrados() {
      const lista = this.periodosDisponiblesExportar || [];
      if (lista.length === 0) return [];

      if (this.opcionesExportar.tipo_rango === '5_anios') {
        return lista.slice(0, 60);
      }
      if (this.opcionesExportar.tipo_rango === '3_anios') {
        return lista.slice(0, 36);
      }
      if (this.opcionesExportar.tipo_rango === '1_anio') {
        return lista.slice(0, 12);
      }
      if (this.opcionesExportar.tipo_rango === 'actual') {
        return lista.slice(0, 1);
      }
      if (this.opcionesExportar.tipo_rango === 'personalizado') {
        const idDesde = this.opcionesExportar.periodo_id_desde;
        const idHasta = this.opcionesExportar.periodo_id_hasta;
        const pDesde = lista.find(p => p.id === idDesde);
        const pHasta = lista.find(p => p.id === idHasta);
        if (!pDesde || !pHasta) return lista.slice(0, 12);

        const valDesde = (pDesde.gestion * 100) + pDesde.mes;
        const valHasta = (pHasta.gestion * 100) + pHasta.mes;
        const valMin = Math.min(valDesde, valHasta);
        const valMax = Math.max(valDesde, valHasta);

        return lista.filter(p => {
          const v = (p.gestion * 100) + p.mes;
          return v >= valMin && v <= valMax;
        });
      }
      return lista.slice(0, 12);
    },
    totalLecturasEstimadas() {
      return this.periodosSeleccionadosFiltrados.reduce((acc, p) => acc + (Number(p.total_lecturas) || 5578), 0);
    },
  },
  beforeDestroy() {
    if (this.exportacionJob && this.exportacionJob.timer) {
      clearInterval(this.exportacionJob.timer);
    }
  },
  created() {
    this.cargarPeriodos();
    this.cargarZonas();
  },
  methods: {
    async cargarPeriodos() {
      this.cargando = true;
      try {
        const res = await axios.get('/api/comercial/periodos');
        if (res.data && res.data.success) {
          this.periodos = res.data.data;
        }
      } catch (error) {
        console.error('Error al cargar períodos:', error);
        this.$toast?.error('Error al obtener la lista de períodos de facturación.');
      } finally {
        this.cargando = false;
      }
    },

    async cargarZonas() {
      try {
        const res = await axios.get('/api/comercial/zonas');
        if (res.data && res.data.success) {
          this.zonas = res.data.data;
        }
      } catch (e) {
        console.error('Error al cargar zonas:', e);
      }
    },

    obtenerColorEstado(estado) {
      const e = String(estado || '').toUpperCase();
      if (['LECTURA', 'L', 'ABIERTO'].includes(e)) return 'blue darken-1';
      if (['FACTURACION', 'F', 'FACTURADO'].includes(e)) return 'orange darken-2';
      if (['CERRADO', 'C'].includes(e)) return 'blue-grey darken-1';
      return 'grey';
    },

    obtenerIconoEstado(estado) {
      const e = String(estado || '').toUpperCase();
      if (['LECTURA', 'L', 'ABIERTO'].includes(e)) return 'mdi-counter';
      if (['FACTURACION', 'F', 'FACTURADO'].includes(e)) return 'mdi-cash-register';
      if (['CERRADO', 'C'].includes(e)) return 'mdi-lock';
      return 'mdi-clock-outline';
    },

    formatearFecha(fechaStr) {
      if (!fechaStr) return '-';
      const parts = String(fechaStr).substring(0, 10).split('-');
      if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
      }
      return fechaStr;
    },

    abrirModalNuevoPeriodo() {
      // Calcular automáticamente el siguiente mes cronológico
      let sigMes = 1;
      let sigGestion = new Date().getFullYear();

      if (this.periodos && this.periodos.length > 0) {
        const ultimo = this.periodos[0];
        if (ultimo.mes === 12) {
          sigMes = 1;
          sigGestion = Number(ultimo.gestion) + 1;
        } else {
          sigMes = Number(ultimo.mes) + 1;
          sigGestion = Number(ultimo.gestion);
        }
      }

      // Calcular fechas por defecto
      const mesStr = String(sigMes).padStart(2, '0');
      const ultDiaMes = new Date(sigGestion, sigMes, 0).getDate();

      this.formPeriodo = {
        mes: sigMes,
        gestion: sigGestion,
        fecha_inicio_consumo: `${sigGestion}-${mesStr}-01`,
        fecha_fin_consumo: `${sigGestion}-${mesStr}-${String(ultDiaMes).padStart(2, '0')}`,
        fecha_vencimiento_pago: `${sigGestion}-${mesStr}-25`,
        observaciones: '',
      };

      this.modalNuevoPeriodo = true;
    },

    async guardarNuevoPeriodo() {
      this.guardandoPeriodo = true;
      try {
        const res = await axios.post('/api/comercial/periodos/abrir', this.formPeriodo);
        if (res.data && res.data.success) {
          this.$toast?.success(res.data.message || 'Período abierto exitosamente.');
          this.modalNuevoPeriodo = false;
          await this.cargarPeriodos();
        }
      } catch (error) {
        const msg = error.response?.data?.message || 'Error al abrir el período.';
        this.$toast?.error(msg);
      } finally {
        this.guardandoPeriodo = false;
      }
    },

    abrirModalEditarPeriodo(item) {
      this.formEdicion = {
        id: item.id,
        periodo: item.periodo,
        fecha_inicio_consumo: String(item.fecha_inicio_consumo || '').substring(0, 10),
        fecha_fin_consumo: String(item.fecha_fin_consumo || '').substring(0, 10),
        fecha_vencimiento_pago: String(item.fecha_vencimiento_pago || '').substring(0, 10),
        observaciones: item.observaciones || '',
      };
      this.modalEditarPeriodo = true;
    },

    async guardarEdicionPeriodo() {
      this.guardandoEdicion = true;
      try {
        const res = await axios.put(`/api/comercial/periodos/${this.formEdicion.id}`, this.formEdicion);
        if (res.data && res.data.success) {
          this.$toast?.success('Período actualizado con éxito.');
          this.modalEditarPeriodo = false;
          await this.cargarPeriodos();
        }
      } catch (error) {
        const msg = error.response?.data?.message || 'Error al actualizar el período.';
        this.$toast?.error(msg);
      } finally {
        this.guardandoEdicion = false;
      }
    },

    solicitarCambioEstado(item, nuevoEstado) {
      this.periodoSeleccionado = item;
      this.nuevoEstadoObjetivo = nuevoEstado;
      this.modalConfirmarEstado = true;
    },

    async ejecutarCambioEstado() {
      this.cambiandoEstado = true;
      try {
        const res = await axios.post(`/api/comercial/periodos/${this.periodoSeleccionado.id}/cambiar-estado`, {
          estado: this.nuevoEstadoObjetivo,
        });
        if (res.data && res.data.success) {
          this.$toast?.success(res.data.message || 'Estado actualizado correctamente.');
          this.modalConfirmarEstado = false;
          await this.cargarPeriodos();
        }
      } catch (error) {
        const msg = error.response?.data?.message || 'Error al cambiar estado del período.';
        this.$toast?.error(msg);
      } finally {
        this.cambiandoEstado = false;
      }
    },

    async confirmarEliminarPeriodo(item) {
      if (!confirm(`¿Está seguro de eliminar el período ${item.periodo}? Esta acción solo es permitida si no contiene cobros registrados.`)) {
        return;
      }
      try {
        const res = await axios.delete(`/api/comercial/periodos/${item.id}`);
        if (res.data && res.data.success) {
          this.$toast?.success('Período eliminado exitosamente.');
          await this.cargarPeriodos();
        }
      } catch (error) {
        const msg = error.response?.data?.message || 'No se pudo eliminar el período.';
        this.$toast?.error(msg);
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

    // 1. Reporte: Planilla de Campo (Toma de Lecturas)
    abrirDialogoPlanillaCampo(item) {
      this.periodoPlanilla = item;
      this.opcionesPlanilla = {
        id_zona: null,
        a_ciegas: false,
      };
      this.modalPlanillaCampo = true;
    },

    verPlanillaPdf() {
      const q = new URLSearchParams();
      q.append('id_periodo', this.periodoPlanilla.id);
      if (this.opcionesPlanilla.id_zona) q.append('id_zona', this.opcionesPlanilla.id_zona);
      if (this.opcionesPlanilla.a_ciegas) q.append('a_ciegas', '1');

      const periodoNombre = String(this.periodoPlanilla.periodo).replace('/', '_');
      this.tituloVisorPdf = `Planilla de Campo - Período ${this.periodoPlanilla.periodo}`;
      this.urlVisorPdf = `/api/comercial/reportes/planilla-lecturas/pdf?${q.toString()}`;
      this.urlExcelVisorPdf = `/api/comercial/reportes/planilla-lecturas/excel?${q.toString()}`;
      this.nombrePdfVisor = `Planilla_Campo_${periodoNombre}.pdf`;
      this.nombreExcelVisor = `Planilla_Campo_${periodoNombre}.csv`;
      this.modalPlanillaCampo = false;
      this.modalVisorPdf = true;
    },

    async exportarPlanillaExcel() {
      const q = new URLSearchParams();
      q.append('id_periodo', this.periodoPlanilla.id);
      if (this.opcionesPlanilla.id_zona) q.append('id_zona', this.opcionesPlanilla.id_zona);
      if (this.opcionesPlanilla.a_ciegas) q.append('a_ciegas', '1');

      const periodoNombre = String(this.periodoPlanilla.periodo).replace('/', '_');
      const url = `/api/comercial/reportes/planilla-lecturas/excel?${q.toString()}`;
      await this.descargarArchivoBlob(url, `Planilla_Campo_${periodoNombre}.csv`);
      this.modalPlanillaCampo = false;
    },

    // 2. Reporte: Resumen de Operaciones y Facturación por Zonas
    abrirDialogoResumenZonas(item) {
      this.periodoResumenZonas = item;
      this.modalResumenZonas = true;
    },

    verResumenZonasPdf() {
      if (!this.periodoResumenZonas) return;
      const periodoNombre = String(this.periodoResumenZonas.periodo).replace('/', '_');
      this.tituloVisorPdf = `Resumen de Operaciones y Facturación por Zonas - Período ${this.periodoResumenZonas.periodo}`;
      this.urlVisorPdf = `/api/comercial/reportes/resumen-operaciones-zonas/pdf?id_periodo=${this.periodoResumenZonas.id}`;
      this.urlExcelVisorPdf = `/api/comercial/reportes/resumen-operaciones-zonas/excel?id_periodo=${this.periodoResumenZonas.id}`;
      this.nombrePdfVisor = `Resumen_Zonas_${periodoNombre}.pdf`;
      this.nombreExcelVisor = `Resumen_Zonas_${periodoNombre}.csv`;
      this.modalResumenZonas = false;
      this.modalVisorPdf = true;
    },

    async exportarResumenZonasExcel() {
      if (!this.periodoResumenZonas) return;
      const periodoNombre = String(this.periodoResumenZonas.periodo).replace('/', '_');
      const url = `/api/comercial/reportes/resumen-operaciones-zonas/excel?id_periodo=${this.periodoResumenZonas.id}`;
      await this.descargarArchivoBlob(url, `Resumen_Zonas_${periodoNombre}.csv`);
      this.modalResumenZonas = false;
    },

    // 3. Reporte: Nómina de Cortes Masivos por Mora
    abrirDialogoNominaCortes(item) {
      this.periodoNominaCortes = item;
      this.opcionesNominaCortes = {
        meses_mora: 2,
        id_zona: null,
      };
      this.modalNominaCortes = true;
    },

    verNominaCortesPdf() {
      const q = new URLSearchParams();
      q.append('meses_mora', this.opcionesNominaCortes.meses_mora || 2);
      if (this.opcionesNominaCortes.id_zona) q.append('id_zona', this.opcionesNominaCortes.id_zona);

      const periodoNombre = this.periodoNominaCortes ? String(this.periodoNominaCortes.periodo).replace('/', '_') : 'Actual';
      this.tituloVisorPdf = `Planilla de Cortes Masivos por Mora (>= ${this.opcionesNominaCortes.meses_mora} Meses)`;
      this.urlVisorPdf = `/api/comercial/reportes/nomina-cortes/pdf?${q.toString()}`;
      this.urlExcelVisorPdf = `/api/comercial/reportes/nomina-cortes/excel?${q.toString()}`;
      this.nombrePdfVisor = `Nomina_Cortes_Mora_${periodoNombre}.pdf`;
      this.nombreExcelVisor = `Nomina_Cortes_Mora_${periodoNombre}.csv`;
      this.modalNominaCortes = false;
      this.modalVisorPdf = true;
    },

    async exportarNominaCortesExcel() {
      const q = new URLSearchParams();
      q.append('meses_mora', this.opcionesNominaCortes.meses_mora || 2);
      if (this.opcionesNominaCortes.id_zona) q.append('id_zona', this.opcionesNominaCortes.id_zona);

      const periodoNombre = this.periodoNominaCortes ? String(this.periodoNominaCortes.periodo).replace('/', '_') : 'Actual';
      const url = `/api/comercial/reportes/nomina-cortes/excel?${q.toString()}`;
      await this.descargarArchivoBlob(url, `Nomina_Cortes_Mora_${periodoNombre}.csv`);
      this.modalNominaCortes = false;
    },

    // Métodos para la exportación masiva histórica en segundo plano
    async abrirModalExportarHistorico() {
      this.modalExportarHistorico = true;
      if (!this.periodosDisponiblesExportar || this.periodosDisponiblesExportar.length === 0) {
        await this.cargarMetadatosExportacion();
      }
    },

    async cargarMetadatosExportacion() {
      this.cargandoMetadatosExportar = true;
      try {
        const res = await axios.get('/api/comercial/periodos/exportar-historico/periodos-disponibles');
        if (res.data) {
          this.periodosDisponiblesExportar = res.data.periodos || [];
          this.zonasExportar = res.data.zonas || [];
          this.categoriasExportar = res.data.categorias || [];

          if (this.periodosDisponiblesExportar.length > 0) {
            this.opcionesExportar.periodo_id_hasta = this.periodosDisponiblesExportar[0].id;
            const idxDesde = Math.min(59, this.periodosDisponiblesExportar.length - 1);
            this.opcionesExportar.periodo_id_desde = this.periodosDisponiblesExportar[idxDesde].id;
          }

          if (res.data.columnas && res.data.columnas.length > 0) {
            this.todasLasColumnas = res.data.columnas;
            if (!this.columnasSeleccionadas || this.columnasSeleccionadas.length === 0) {
              this.columnasSeleccionadas = this.todasLasColumnas.map(c => c.id);
            }
          }
        }
      } catch (err) {
        console.error('Error al cargar metadatos de exportación:', err);
      } finally {
        this.cargandoMetadatosExportar = false;
      }
    },

    async iniciarExportacionHistorica() {
      if (this.periodosSeleccionadosFiltrados.length === 0) {
        alert('Debe seleccionar al menos un período para exportar.');
        return;
      }

      if (this.columnasSeleccionadas.length === 0) {
        alert('Debe seleccionar al menos una columna para exportar en la planilla.');
        return;
      }

      this.exportacionJob.iniciando = true;
      try {
        const payload = {
          tipo_rango: this.opcionesExportar.tipo_rango,
          periodo_id_desde: this.opcionesExportar.periodo_id_desde,
          periodo_id_hasta: this.opcionesExportar.periodo_id_hasta,
          periodo_ids: this.periodosSeleccionadosFiltrados.map(p => p.id),
          formato: this.opcionesExportar.formato,
          id_zona: this.opcionesExportar.id_zona,
          id_categoria: this.opcionesExportar.id_categoria,
          estado_pago: this.opcionesExportar.estado_pago,
          columnas: this.columnasSeleccionadas,
        };

        const res = await axios.post('/api/comercial/periodos/exportar-historico/iniciar', payload);
        if (res.data && res.data.status === 'success') {
          this.exportacionJob.jobId = res.data.job_id;
          this.exportacionJob.procesando = true;
          this.exportacionJob.completado = false;
          this.exportacionJob.progreso = 5;
          this.exportacionJob.total_periodos = res.data.total_periodos;
          this.exportacionJob.periodos_procesados = 0;
          this.exportacionJob.filas_exportadas = 0;
          this.exportacionJob.mensaje = 'Exportación encolada. Iniciando hilo en segundo plano...';

          if (this.exportacionJob.timer) {
            clearInterval(this.exportacionJob.timer);
          }
          this.exportacionJob.timer = setInterval(this.consultarEstadoJobExportacion, 1500);
        } else {
          alert(res.data?.message || 'No se pudo iniciar la exportación.');
        }
      } catch (err) {
        console.error('Error al iniciar exportación:', err);
        alert(err.response?.data?.message || 'Error al iniciar el proceso en segundo plano.');
      } finally {
        this.exportacionJob.iniciando = false;
      }
    },

    async consultarEstadoJobExportacion() {
      if (!this.exportacionJob.jobId) return;

      try {
        const res = await axios.get(`/api/comercial/periodos/exportar-historico/${this.exportacionJob.jobId}/estado`);
        if (res.data) {
          const st = res.data;
          this.exportacionJob.progreso = st.progreso || 0;
          this.exportacionJob.mensaje = st.mensaje || '';
          this.exportacionJob.periodo_actual = st.periodo_actual || '';
          this.exportacionJob.periodos_procesados = st.periodos_procesados || 0;
          this.exportacionJob.total_periodos = st.total_periodos || this.exportacionJob.total_periodos;
          this.exportacionJob.filas_exportadas = st.filas_exportadas || 0;
          this.exportacionJob.nombre_archivo = st.nombre_archivo || '';
          this.exportacionJob.peso_archivo = st.peso_archivo || '';

          if (st.estado === 'COMPLETADO') {
            if (this.exportacionJob.timer) {
              clearInterval(this.exportacionJob.timer);
              this.exportacionJob.timer = null;
            }
            this.exportacionJob.procesando = false;
            this.exportacionJob.completado = true;
          } else if (st.estado === 'ERROR' || st.estado === 'CANCELADO') {
            if (this.exportacionJob.timer) {
              clearInterval(this.exportacionJob.timer);
              this.exportacionJob.timer = null;
            }
            this.exportacionJob.procesando = false;
            alert(st.mensaje || st.error || 'La exportación se detuvo.');
          }
        }
      } catch (err) {
        console.error('Error al consultar estado de exportación:', err);
      }
    },

    async cancelarExportacionJob() {
      if (!this.exportacionJob.jobId) return;
      if (!confirm('¿Desea cancelar el proceso de exportación en segundo plano?')) return;

      try {
        await axios.post(`/api/comercial/periodos/exportar-historico/${this.exportacionJob.jobId}/cancelar`);
        if (this.exportacionJob.timer) {
          clearInterval(this.exportacionJob.timer);
          this.exportacionJob.timer = null;
        }
        this.exportacionJob.procesando = false;
        this.exportacionJob.jobId = null;
      } catch (err) {
        console.error('Error al cancelar exportación:', err);
      }
    },

    async descargarReporteExportado() {
      if (!this.exportacionJob.jobId) return;
      this.descargandoArchivoJob = true;
      try {
        const url = `/api/comercial/periodos/exportar-historico/${this.exportacionJob.jobId}/descargar`;
        await this.descargarArchivoBlob(url, this.exportacionJob.nombre_archivo || 'Reporte_Historico_Lecturas.xlsx');
      } catch (err) {
        console.error('Error al descargar archivo:', err);
        alert('Error al descargar el archivo generado.');
      } finally {
        this.descargandoArchivoJob = false;
      }
    },

    resetearFormularioExportacion() {
      this.exportacionJob.completado = false;
      this.exportacionJob.procesando = false;
      this.exportacionJob.jobId = null;
      this.exportacionJob.progreso = 0;
    },

    toggleColumnaExportar(colId) {
      const idx = this.columnasSeleccionadas.indexOf(colId);
      if (idx > -1) {
        if (this.columnasSeleccionadas.length <= 1) {
          alert('Debe mantener al menos una columna seleccionada para la planilla.');
          return;
        }
        this.columnasSeleccionadas.splice(idx, 1);
      } else {
        this.columnasSeleccionadas.push(colId);
      }
    },

    seleccionarTodasColumnas() {
      this.columnasSeleccionadas = this.todasLasColumnas.map(c => c.id);
    },

    seleccionarColumnasEsenciales() {
      this.columnasSeleccionadas = [
        'codigo',
        'nombre_completo',
        'zona_nombre',
        'calle_nombre',
        'lectura_anterior',
        'lectura_actual',
        'consumo_m3',
        'total_facturado',
      ];
    },

    deseleccionarTodasColumnas() {
      this.columnasSeleccionadas = ['codigo', 'nombre_completo'];
    },
  },
};
</script>

<style scoped>
.border-bottom {
  border-bottom: 1px solid #e0e0e0;
}
.cursor-pointer {
  cursor: pointer;
}
.border-teal {
  border: 2px solid #00897b !important;
}
.bg-light-panel {
  background-color: rgba(0, 137, 123, 0.04);
}
.pulse-chip {
  animation: pulseAnimation 2s infinite ease-in-out;
}
@keyframes pulseAnimation {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.03); opacity: 0.85; }
  100% { transform: scale(1); opacity: 1; }
}
</style>
