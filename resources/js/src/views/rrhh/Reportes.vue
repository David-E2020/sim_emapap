<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-file-chart-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Planillas Salariales y Cargas Patronales</h2>
            <span class="text-caption text-secondary">
              Gestión {{ filtroAnio }} — Haberes, Gestora (12.71%), Aportes Patronales (17.21%) y Declaraciones Oficiales
            </span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 flex-wrap">
          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold text-capitalize elevation-2"
            @click="abrirModalGenerarPlanilla()"
          >
            <v-icon left small>mdi-plus-circle-outline</v-icon> Generar / Procesar Planilla
          </v-btn>
          <v-btn
            color="blue-grey darken-3"
            dark
            outlined
            class="rounded-pill font-weight-medium text-capitalize"
            to="/rrhh/calculos-laborales"
          >
            <v-icon left small>mdi-calculator-variant</v-icon> Parámetros Laborales
          </v-btn>
          <v-select
            v-model="filtroAnio"
            :items="aniosDisponibles"
            label="Gestión"
            outlined
            dense
            hide-details
            style="max-width: 120px;"
            @change="cargarPlanillasRegistradas"
          ></v-select>
          <v-btn icon color="primary" @click="cargarPlanillasRegistradas" title="Recargar lista">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- SELECTOR DE TIPO DE PLANILLA Y KPIS -->
    <v-card rounded="lg" class="pa-3 mb-4 erp-card-elevated">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center gap-3 flex-wrap">
          <span class="text-caption font-weight-bold text-secondary">TIPO DE PLANILLA:</span>
          <v-btn-toggle v-model="filtroTipoPlanilla" mandatory dense color="primary" @change="cargarPlanillasRegistradas">
            <v-btn small value="TODOS" class="text-capitalize px-3">
              <v-icon left x-small>mdi-format-list-bulleted</v-icon> Todas
            </v-btn>
            <v-btn small value="PLANTA_PERMANENTE" class="text-capitalize px-3">
              <v-icon left x-small>mdi-badge-account</v-icon> Planta Permanente
            </v-btn>
            <v-btn small value="PERSONAL_EVENTUAL" class="text-capitalize px-3">
              <v-icon left x-small>mdi-account-clock</v-icon> Personal Eventual
            </v-btn>
            <v-btn small value="DIETAS_DIRECTORIO" class="text-capitalize px-3">
              <v-icon left x-small>mdi-account-tie</v-icon> Dietas Directorio
            </v-btn>
          </v-btn-toggle>
        </div>

        <!-- KPIs del año basados en registros reales -->
        <div class="d-flex align-center gap-4 flex-wrap" v-if="!loadingPlanillas">
          <div class="text-center">
            <div class="text-caption text-secondary font-weight-bold">PLANILLAS REGISTRADAS</div>
            <div class="text-h6 font-weight-black primary--text">{{ totalPlanillasGeneradas }}</div>
          </div>
          <v-divider vertical></v-divider>
          <div class="text-center">
            <div class="text-caption text-secondary font-weight-bold">DECLARADAS OFICIALES</div>
            <div class="text-h6 font-weight-black purple--text text--darken-3">{{ totalPlanillasDeclaradas }}</div>
          </div>
          <v-divider vertical></v-divider>
          <div class="text-center">
            <div class="text-caption text-secondary font-weight-bold">TOTAL PAGADO DECLARADO</div>
            <div class="text-h6 font-weight-black success--text">Bs. {{ formatoBs(totalAnualPagado) }}</div>
          </div>
        </div>
      </div>
    </v-card>

    <!-- TABLA DE PLANILLAS REGISTRADAS Y GENERADAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title class="py-3 px-4 d-flex align-center justify-space-between flex-wrap gap-2">
        <div>
          <span class="font-weight-bold text-subtitle-1">
            <v-icon left color="primary" small>mdi-format-list-checks</v-icon>
            Planillas Registradas — Gestión {{ filtroAnio }}
          </span>
          <div class="text-caption text-secondary">
            Mostrando únicamente las planillas generadas y guardadas en la base de datos
          </div>
        </div>
        <div class="d-flex align-center gap-2">
          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold text-capitalize"
            small
            @click="abrirModalGenerarPlanilla()"
          >
            <v-icon left small>mdi-plus-circle-outline</v-icon> Generar Planilla
          </v-btn>
          <v-btn icon color="primary" @click="cargarPlanillasRegistradas" title="Recargar lista">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </v-card-title>
      <v-divider></v-divider>

      <v-data-table
        :headers="headersPlanillas"
        :items="planillasRegistradas"
        :loading="loadingPlanillas"
        hide-default-footer
        disable-pagination
        class="elevation-0"
      >
        <!-- ESTADO VACÍO CUANDO NO HAY PLANILLAS GENERADAS -->
        <template v-slot:no-data>
          <div class="py-12 text-center">
            <v-icon size="64" color="grey lighten-1">mdi-clipboard-text-clock-outline</v-icon>
            <div class="text-h6 font-weight-bold grey--text text--darken-2 mt-3">
              No hay planillas generadas para la gestión {{ filtroAnio }}
            </div>
            <p class="text-body-2 grey--text text--darken-1 mb-4" style="max-width: 520px; margin: 0 auto;">
              La base de datos no contiene planillas para el filtro seleccionado. Solo aparecerán registros una vez que hayan sido generados o declarados.
            </p>
            <v-btn
              color="primary"
              class="rounded-pill font-weight-bold text-capitalize px-5 elevation-2"
              @click="abrirModalGenerarPlanilla()"
            >
              <v-icon left small>mdi-plus-circle-outline</v-icon> Generar / Procesar Planilla
            </v-btn>
          </div>
        </template>

        <!-- MES / PERIODO -->
        <template v-slot:item.mes="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar :color="item.es_declarada ? 'purple lighten-5' : 'amber lighten-5'" size="36" class="mr-3">
              <span class="text-caption font-weight-black" :class="item.es_declarada ? 'purple--text text--darken-3' : 'amber--text text--darken-4'">
                {{ String(item.mes).padStart(2,'0') }}
              </span>
            </v-avatar>
            <div>
              <div class="font-weight-bold">{{ item.mes_nombre }} {{ item.gestion }}</div>
              <div class="text-caption text-secondary">{{ item.tipo_planilla_label }}</div>
            </div>
          </div>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            small
            label
            :color="item.es_declarada ? 'purple lighten-5' : 'amber lighten-5'"
            :text-color="item.es_declarada ? 'purple darken-3' : 'amber darken-4'"
            class="font-weight-bold"
          >
            <v-icon left x-small>{{ item.es_declarada ? 'mdi-shield-check' : 'mdi-file-document-edit-outline' }}</v-icon>
            {{ item.es_declarada ? 'DECLARADA OFICIAL' : 'BORRADOR GENERADO' }}
          </v-chip>
        </template>

        <!-- CITE -->
        <template v-slot:item.cite_oficial="{ item }">
          <span v-if="item.cite_oficial" class="font-weight-medium primary--text text-caption font-monospace">{{ item.cite_oficial }}</span>
          <span v-else class="text-caption grey--text font-italic">Sin CITE (Borrador)</span>
        </template>

        <!-- TOTAL GANADO -->
        <template v-slot:item.total_ganado_bs="{ item }">
          <span class="font-weight-bold">
            Bs. {{ formatoBs(item.total_ganado_bs) }}
          </span>
        </template>

        <!-- GESTORA / DESCUENTOS -->
        <template v-slot:item.total_descuentos_bs="{ item }">
          <span class="warning--text text--darken-3 font-weight-medium">
            Bs. {{ formatoBs(item.total_descuentos_bs) }}
          </span>
        </template>

        <!-- LÍQUIDO PAGABLE -->
        <template v-slot:item.total_liquido_pagable_bs="{ item }">
          <span class="success--text font-weight-bold">
            Bs. {{ formatoBs(item.total_liquido_pagable_bs) }}
          </span>
        </template>

        <!-- NÓMINA -->
        <template v-slot:item.nomina="{ item }">
          <v-chip x-small label color="blue lighten-5" text-color="blue darken-3" class="font-weight-bold">
            {{ item.cantidad_funcionarios }} func.
          </v-chip>
        </template>

        <!-- FECHA CIERRE / REGISTRO -->
        <template v-slot:item.fecha_cierre="{ item }">
          <span class="text-caption text-secondary">
            {{ item.fecha_cierre ? item.fecha_cierre.substring(0, 10) : (item.fecha_creacion ? item.fecha_creacion.substring(0, 10) : '—') }}
          </span>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center flex-wrap gap-1">
            <!-- CASO 1: DECLARADA -->
            <template v-if="item.es_declarada">
              <v-btn x-small outlined color="primary" class="rounded-pill text-capitalize px-2 font-weight-medium" @click="abrirDetallePlanilla(item)">
                <v-icon left x-small>mdi-eye</v-icon> Ver
              </v-btn>
              <v-btn x-small color="green darken-2" dark class="rounded-pill text-capitalize px-2 font-weight-medium" @click="descargarExcel({ mes_num: item.mes })">
                <v-icon left x-small>mdi-file-excel</v-icon> Excel
              </v-btn>
              <v-btn x-small color="deep-purple" dark class="rounded-pill text-capitalize px-2 font-weight-medium" @click="abrirVisorPlanillaPdfDesdeFila(item)">
                <v-icon left x-small>mdi-file-pdf-box</v-icon> PDF
              </v-btn>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon x-small color="amber darken-3" v-bind="attrs" v-on="on" @click="confirmarReabrir(item)">
                    <v-icon small>mdi-lock-open-variant-outline</v-icon>
                  </v-btn>
                </template>
                <span>Reabrir / Desconsolidar para realizar correcciones</span>
              </v-tooltip>
            </template>

            <!-- CASO 2: BORRADOR GENERADO -->
            <template v-else>
              <v-btn x-small outlined color="primary" class="rounded-pill text-capitalize px-2 font-weight-medium" @click="abrirDetallePlanilla(item)">
                <v-icon left x-small>mdi-file-document-edit-outline</v-icon> Revisar
              </v-btn>
              <v-btn x-small color="purple darken-1" dark class="rounded-pill text-capitalize px-2 font-weight-bold" @click="confirmarDeclarar(item)">
                <v-icon left x-small>mdi-lock-check</v-icon> Declarar
              </v-btn>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon x-small color="red" v-bind="attrs" v-on="on" @click="confirmarEliminarBorrador(item)">
                    <v-icon small>mdi-delete-outline</v-icon>
                  </v-btn>
                </template>
                <span>Eliminar borrador</span>
              </v-tooltip>
            </template>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- ============================================================== -->
    <!-- MODAL DE DETALLE DEL MES — Registros + Declarar                -->
    <!-- ============================================================== -->
    <v-dialog v-model="dialogDetalleMes" max-width="1180" scrollable>
      <v-card rounded="lg" v-if="mesSeleccionado">
        <!-- CABECERA DEL MODAL NATIVA INSTITUCIONAL -->
        <v-card-title class="primary white--text py-3 px-4 d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="d-flex align-center">
            <v-avatar color="white" size="38" class="mr-3 elevation-1">
              <v-icon color="primary" small>mdi-file-table-box-outline</v-icon>
            </v-avatar>
            <div>
              <div class="text-subtitle-1 font-weight-bold leading-tight">
                Planilla de Sueldos y Salarios — {{ mesSeleccionado.mes_nombre }} {{ filtroAnio }}
              </div>
              <div class="text-caption font-weight-medium" style="opacity: 0.9;">
                {{ getNombreTipoPlanilla(filtroTipoPlanilla) }} ·
                <span v-if="mesSeleccionado.es_declarada && mesSeleccionado.cite">Oficial Declarada · {{ mesSeleccionado.cite }}</span>
                <span v-else-if="mesSeleccionado.es_declarada">Oficial Declarada</span>
                <span v-else>Pre-liquidación en Borrador (Pendiente de Declaración)</span>
              </div>
            </div>
          </div>

          <div class="d-flex align-center gap-2">
            <v-chip
              small
              label
              :color="mesSeleccionado.es_declarada ? 'success darken-1' : 'amber darken-2'"
              class="font-weight-bold text-white px-2"
            >
              <v-icon left x-small>{{ mesSeleccionado.es_declarada ? 'mdi-shield-check' : 'mdi-file-edit-outline' }}</v-icon>
              {{ mesSeleccionado.es_declarada ? 'DECLARADA' : 'BORRADOR' }}
            </v-chip>
            <v-btn icon dark small @click="dialogDetalleMes = false" class="ml-1">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>

        <!-- BARRA COMPACTA DE ESTADO Y ACCIÓN PRINCIPAL (REEMPLAZA ALERTAS REDUNDANTES) -->
        <div class="px-4 py-2 grey lighten-4 d-flex align-center justify-space-between flex-wrap gap-2 border-b">
          <div class="d-flex align-center gap-2 flex-wrap">
            <v-icon small :color="mesSeleccionado.es_declarada ? 'success darken-1' : 'amber darken-3'">
              {{ mesSeleccionado.es_declarada ? 'mdi-shield-check' : 'mdi-information-outline' }}
            </v-icon>
            <span class="text-caption font-weight-medium">
              <template v-if="mesSeleccionado.es_declarada">
                <strong>Planilla Oficial Declarada</strong>{{ mesSeleccionado.cite ? ' (' + mesSeleccionado.cite + ')' : '' }}. Los datos se encuentran consolidados para contabilidad y auditoría.
              </template>
              <template v-else>
                <strong>Planilla en Borrador</strong> ({{ planillaMes.length }} funcionarios computados). Puede revisar los importes antes de consolidar.
              </template>
            </span>
            <v-chip v-if="alertaAsistenciaModal" x-small color="amber lighten-4" text-color="amber darken-4" class="font-weight-medium">
              <v-icon left x-small color="amber darken-4">mdi-clock-alert-outline</v-icon> Días base (sin biométrico)
            </v-chip>
          </div>

          <div class="d-flex align-center gap-2">
            <v-btn
              v-if="!mesSeleccionado.es_declarada && planillaMes.length > 0"
              small
              color="primary"
              class="rounded-pill font-weight-bold text-capitalize px-3 elevation-1"
              :loading="cerrandoPlanilla"
              @click="confirmarDeclarar(mesSeleccionado)"
            >
              <v-icon left x-small>mdi-lock-check</v-icon> Consolidar y Declarar Planilla
            </v-btn>
            <v-btn
              v-else-if="mesSeleccionado.es_declarada"
              x-small
              outlined
              color="amber darken-3"
              class="rounded-pill font-weight-medium text-capitalize px-3"
              @click="confirmarReabrir(mesSeleccionado)"
            >
              <v-icon left x-small>mdi-lock-open-variant</v-icon> Reabrir Planilla
            </v-btn>
          </div>
        </div>

        <v-card-text class="pa-4">
          <!-- RESUMEN FINANCIERO CON ESTILOS ARMONIOSOS (SIN CAJAS NEGRAS DESALINEADAS) -->
          <v-row dense class="mb-4">
            <v-col cols="6" sm="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Ganado</div>
                <div class="text-h5 font-weight-black primary--text mt-1">Bs. {{ formatoBs(totalesModal.total_ganado) }}</div>
                <div class="text-caption text-secondary">{{ planillaMes.length }} funcionarios</div>
              </v-card>
            </v-col>

            <v-col cols="6" sm="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Gestora Laboral (12.71%)</div>
                <div class="text-h5 font-weight-black warning--text text--darken-3 mt-1">Bs. {{ formatoBs(totalesModal.gestora_12_71) }}</div>
                <div class="text-caption text-secondary">Retención a dependientes</div>
              </v-card>
            </v-col>

            <v-col cols="6" sm="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Líquido Pagable</div>
                <div class="text-h5 font-weight-black success--text mt-1">Bs. {{ formatoBs(totalesModal.liquido_salarial) }}</div>
                <div class="text-caption success--text text--darken-2 font-weight-medium">Neto total a desembolsar</div>
              </v-card>
            </v-col>

            <v-col cols="6" sm="3">
              <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
                <div class="text-caption text-secondary font-weight-bold text-uppercase">Carga Patronal (17.21%)</div>
                <div class="text-h5 font-weight-black info--text text--darken-2 mt-1">Bs. {{ formatoBs(patronalModal.total_patronal_bs) }}</div>
                <div class="text-caption text-secondary">CNS Salud: Bs. {{ formatoBs(patronalModal.cns_10_bs) }}</div>
              </v-card>
            </v-col>
          </v-row>

          <!-- TABLA DE FUNCIONARIOS -->
          <v-data-table
            :headers="headersPlanillaModal"
            :items="planillaMes"
            :loading="loadingDetalle"
            class="border rounded elevation-0"
            dense
            :footer-props="{ 'items-per-page-options': [10, 20, -1] }"
            no-data-text="No se encontraron registros para este mes."
          >
            <template v-slot:item.item="{ item }">
              <v-chip x-small outlined color="secondary" class="font-weight-bold">{{ item.item || '-' }}</v-chip>
            </template>
            <template v-slot:item.funcionario="{ item }">
              <div class="font-weight-bold text-body-2">{{ item.funcionario }}</div>
              <div class="text-caption text-secondary">{{ item.cargo || 'Funcionario' }} · CI: {{ item.ci || '-' }}</div>
            </template>
            <template v-slot:item.dias_trabajados="{ item }">
              <span class="text-caption font-weight-bold">{{ item.dias_trabajados || 30 }} d.</span>
            </template>
            <template v-slot:item.haber_basico="{ item }">
              <span class="font-weight-medium">Bs. {{ formatoBs(item.haber_basico) }}</span>
            </template>
            <template v-slot:item.bono_antiguedad="{ item }">
              <div>
                <span class="font-weight-bold">Bs. {{ formatoBs(item.bono_antiguedad) }}</span>
                <div class="text-caption text-secondary" v-if="item.porcentaje_bono > 0">
                  ({{ item.porcentaje_bono }}% · {{ item.anios_antiguedad }}a)
                </div>
              </div>
            </template>
            <template v-slot:item.total_ganado="{ item }">
              <span class="font-weight-black primary--text">Bs. {{ formatoBs(item.total_ganado) }}</span>
            </template>
            <template v-slot:item.gestora_12_71="{ item }">
              <span class="warning--text text--darken-3 font-weight-medium">Bs. {{ formatoBs(item.gestora_12_71) }}</span>
            </template>
            <template v-slot:item.liquido_salarial="{ item }">
              <span class="font-weight-black success--text text-body-2">
                Bs. {{ formatoBs(item.liquido_salarial) }}
              </span>
            </template>
            <template v-slot:item.acciones="{ item }">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    x-small
                    color="primary"
                    outlined
                    class="rounded-pill font-weight-bold text-capitalize px-2"
                    v-bind="attrs"
                    v-on="on"
                    @click="abrirBoletaIndividual(item)"
                  >
                    <v-icon left x-small>mdi-receipt-text-outline</v-icon> Boleta
                  </v-btn>
                </template>
                <span>Ver / Imprimir Papeleta Oficial de Pago</span>
              </v-tooltip>
            </template>
          </v-data-table>
        </v-card-text>

        <!-- ACCIONES DEL MODAL ORGANIZADAS -->
        <v-divider></v-divider>
        <v-card-actions class="pa-3 px-4 d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="d-flex align-center gap-2 flex-wrap">
            <v-btn small outlined color="primary" class="rounded-pill font-weight-bold text-capitalize" @click="abrirVisorPlanillaPdf">
              <v-icon left small>mdi-printer</v-icon> Imprimir Planilla
            </v-btn>
            <v-btn small outlined color="primary" class="rounded-pill font-weight-bold text-capitalize" @click="abrirVisorBoletasMasivasPdf">
              <v-icon left small>mdi-receipt-text-check</v-icon> Boletas Masivas
            </v-btn>
            <v-btn small outlined color="success darken-2" class="rounded-pill font-weight-bold text-capitalize" :loading="exportandoExcel" @click="descargarExcel(mesSeleccionado)">
              <v-icon left small>mdi-file-excel</v-icon> Excel (.xlsx)
            </v-btn>
            <v-btn small text color="secondary" class="rounded-pill text-capitalize" @click="exportarCsvModal">
              <v-icon left small>mdi-file-delimited-outline</v-icon> CSV
            </v-btn>
          </div>

          <div>
            <v-btn small text class="rounded-pill text-capitalize px-4" @click="dialogDetalleMes = false">Cerrar</v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR PDF — BOLETA INDIVIDUAL (igual que Facturación) -->
    <modal-visor-pdf
      v-model="mostrarVisorBoletaPdf"
      :url="urlVisorBoletaPdf"
      :titulo="tituloVisorBoletaPdf"
      :subtitulo="subtituloVisorBoletaPdf"
      :nombre-descarga="nombreDescargaBoletaPdf"
      max-width="1100px"
    ></modal-visor-pdf>

    <!-- VISOR PDF — PLANILLA MENSUAL (igual que Facturación) -->
    <modal-visor-pdf
      v-model="mostrarVisorPlanillaPdf"
      :url="urlVisorPlanillaPdf"
      :titulo="tituloVisorPlanillaPdf"
      :subtitulo="subtituloVisorPlanillaPdf"
      :nombre-descarga="nombreDescargaPlanillaPdf"
      max-width="1280px"
    ></modal-visor-pdf>

    <!-- VISOR PDF — BOLETAS MASIVAS (igual que Facturación) -->
    <modal-visor-pdf
      v-model="mostrarVisorBoletasMasivas"
      :url="urlVisorBoletasMasivas"
      :titulo="tituloVisorBoletasMasivas"
      :subtitulo="subtituloVisorBoletasMasivas"
      :nombre-descarga="nombreDescargaBoletasMasivas"
      max-width="1100px"
    ></modal-visor-pdf>


    <!-- Confirmación siguiente -->

    <!-- CONFIRMACIÓN DE DECLARACIÓN -->
    <v-dialog v-model="dialogConfirmarDeclarar" max-width="480" persistent>
      <v-card rounded="lg" v-if="mesADeclarar">
        <v-card-title class="purple darken-1 white--text py-3">
          <v-icon left color="white">mdi-lock-check</v-icon> Confirmar Declaración
        </v-card-title>
        <v-card-text class="pa-5">
          <div class="text-center mb-4">
            <v-icon color="purple darken-1" size="56">mdi-shield-lock</v-icon>
          </div>
          <p class="text-body-1 text-center">
            ¿Está seguro de <strong>cerrar y declarar oficialmente</strong> la planilla de
            <strong>{{ mesADeclarar.mes_nombre }} / {{ filtroAnio }}</strong>?
          </p>
          <v-alert type="warning" dense outlined class="mt-3 text-caption">
            Una vez declarada se generará su <strong>CITE oficial</strong> y quedará <strong>inmutable e inamovible</strong>. Esta acción no se puede deshacer.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4 d-flex justify-end gap-2">
          <v-btn outlined class="rounded-pill text-capitalize" small @click="dialogConfirmarDeclarar = false">Cancelar</v-btn>
          <v-btn color="purple darken-1" dark class="rounded-pill text-capitalize font-weight-bold" small :loading="cerrandoPlanilla" @click="ejecutarDeclarar">
            <v-icon left small>mdi-lock-check</v-icon> Sí, Declarar Planilla
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- CONFIRMACIÓN DE REAPERTURA / DESCONSOLIDACIÓN -->
    <v-dialog v-model="dialogConfirmarReabrir" max-width="480" persistent>
      <v-card rounded="lg" v-if="mesAReabrir">
        <v-card-title class="amber darken-3 white--text py-3">
          <v-icon left color="white">mdi-lock-open-variant</v-icon> Reabrir / Desconsolidar Planilla
        </v-card-title>
        <v-card-text class="pa-5">
          <div class="text-center mb-4">
            <v-icon color="amber darken-3" size="56">mdi-lock-open-alert</v-icon>
          </div>
          <p class="text-body-1 text-center">
            ¿Está seguro de <strong>reabrir la planilla</strong> de
            <strong>{{ mesAReabrir.mes_nombre }} / {{ filtroAnio }}</strong>?
          </p>
          <v-alert type="info" dense outlined class="mt-3 text-caption">
            La planilla volverá al estado de <strong>SIMULACIÓN / BORRADOR</strong>, permitiéndole corregir asistencias, permisos o escalas salariales y volver a declararla cuando lo desee.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4 d-flex justify-end gap-2">
          <v-btn outlined class="rounded-pill text-capitalize" small @click="dialogConfirmarReabrir = false">Cancelar</v-btn>
          <v-btn color="amber darken-3" dark class="rounded-pill text-capitalize font-weight-bold" small :loading="reabriendoPlanilla" @click="ejecutarReabrir">
            <v-icon left small>mdi-lock-open-variant</v-icon> Sí, Reabrir Planilla
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- CONFIRMACIÓN DE ELIMINACIÓN DE BORRADOR -->
    <v-dialog v-model="dialogConfirmarEliminar" max-width="460" persistent>
      <v-card rounded="lg" v-if="planillaAEliminar">
        <v-card-title class="error white--text py-3">
          <v-icon left color="white">mdi-delete-alert</v-icon> Eliminar Borrador de Planilla
        </v-card-title>
        <v-card-text class="pa-5">
          <div class="text-center mb-3">
            <v-icon color="error" size="56">mdi-trash-can-outline</v-icon>
          </div>
          <p class="text-body-1 text-center">
            ¿Está seguro de eliminar el borrador de planilla de
            <strong>{{ planillaAEliminar.mes_nombre }} / {{ planillaAEliminar.gestion }}</strong>?
          </p>
          <v-alert type="warning" dense outlined class="text-caption">
            El registro será eliminado de la base de datos. Podrá volver a generar la planilla en cualquier momento.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4 d-flex justify-end gap-2">
          <v-btn outlined class="rounded-pill text-capitalize" small @click="dialogConfirmarEliminar = false">Cancelar</v-btn>
          <v-btn color="error" dark class="rounded-pill text-capitalize font-weight-bold" small :loading="eliminandoPlanilla" @click="ejecutarEliminarBorrador">
            <v-icon left small>mdi-delete</v-icon> Sí, Eliminar Borrador
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL GENERAR / PROCESAR PLANILLA ASISTENTE -->
    <v-dialog v-model="dialogGenerarPlanilla" max-width="680" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3 px-4 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon left color="white">mdi-plus-circle-outline</v-icon>
            <span class="text-h6 font-weight-bold">Generar / Procesar Planilla</span>
          </div>
          <v-btn icon dark x-small @click="dialogGenerarPlanilla = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <p class="text-body-2 text-secondary mb-4">
            Seleccione el mes y tipo de nómina a generar. El sistema evaluará el personal activo, la escala salarial vigente y las marcaciones de asistencia para calcular y registrar la planilla.
          </p>

          <v-row dense>
            <v-col cols="12" sm="4">
              <v-select
                v-model="filtroAnio"
                :items="aniosDisponibles"
                label="Gestión / Año *"
                dense
                outlined
                @change="cargarPreviaAsistente"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="mesAsistente"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes a Procesar *"
                dense
                outlined
                @change="cargarPreviaAsistente"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="tipoAsistente"
                :items="tiposPlanillaAsistente"
                item-text="nombre"
                item-value="id"
                label="Tipo de Planilla *"
                dense
                outlined
                @change="cargarPreviaAsistente"
              ></v-select>
            </v-col>
          </v-row>

          <!-- CARD DE PREVIA / ESTADO DEL PERIODO -->
          <v-card outlined class="pa-4 rounded-lg grey lighten-5 mt-2" v-if="cargandoAsistente">
            <div class="text-center py-4">
              <v-progress-circular indeterminate color="primary" size="28" width="3"></v-progress-circular>
              <div class="text-caption text-secondary mt-2">Calculando parámetros salariales del periodo...</div>
            </div>
          </v-card>

          <div v-else-if="resumenAsistente">
            <!-- CASO SIN PERSONAL ACTIVO -->
            <v-alert
              v-if="resumenAsistente.cantidad_funcionarios === 0"
              type="warning"
              dense
              outlined
              class="mt-3 text-caption"
            >
              <div class="font-weight-bold mb-1">
                <v-icon left small color="warning">mdi-account-alert</v-icon> Sin personal activo asignado
              </div>
              No se encontraron funcionarios activos para <strong>{{ getNombreTipoPlanilla(tipoAsistente) }}</strong> en el periodo seleccionado. Debe registrar empleados con sus puestos en <strong>Personal y Legajos</strong> o sincronizarlos desde Excel antes de generar la planilla.
              <div class="mt-2">
                <v-btn small color="warning darken-2" dark class="rounded-pill text-capitalize" to="/rrhh/personal">
                  <v-icon left small>mdi-account-plus</v-icon> Ir a Gestión de Personal
                </v-btn>
              </div>
            </v-alert>

            <!-- CASO CON PERSONAL ACTIVO -->
            <v-card outlined class="pa-4 rounded-lg grey lighten-5 mt-2" v-else>
              <div class="d-flex align-center justify-space-between mb-3 flex-wrap gap-2">
                <div>
                  <span class="text-caption text-secondary font-weight-bold text-uppercase">ESTADO EN EL SISTEMA:</span>
                  <v-chip
                    small
                    label
                    :color="resumenAsistente.es_declarada ? 'purple lighten-5' : 'amber lighten-5'"
                    :text-color="resumenAsistente.es_declarada ? 'purple darken-3' : 'amber darken-4'"
                    class="font-weight-bold ml-2"
                  >
                    <v-icon left x-small>{{ resumenAsistente.es_declarada ? 'mdi-shield-check' : 'mdi-timer-sand' }}</v-icon>
                    {{ resumenAsistente.es_declarada ? 'DECLARADA OFICIAL' : (tipoAsistente === 'TODAS' ? 'LISTAS PARA GENERAR EN LOTE' : 'LISTA PARA GENERAR') }}
                  </v-chip>
                  <div v-if="tipoAsistente === 'TODAS'" class="text-caption primary--text mt-1 d-flex align-center">
                    <v-icon x-small color="primary" class="mr-1">mdi-layers-triple-outline</v-icon>
                    Generación masiva: Procesará Planta Permanente, Personal Eventual y Dietas de Directorio en lote.
                  </div>
                </div>

                <span v-if="resumenAsistente.cite" class="text-caption font-weight-bold primary--text">
                  {{ resumenAsistente.cite }}
                </span>
              </div>

              <v-row dense>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary">Nómina Activa</div>
                  <div class="font-weight-black text-subtitle-1">{{ resumenAsistente.cantidad_funcionarios }} funcionarios</div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary">Total Ganado</div>
                  <div class="font-weight-black text-subtitle-1 primary--text">Bs. {{ formatoBs(resumenAsistente.total_ganado) }}</div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary">Gestora (12.71%)</div>
                  <div class="font-weight-black text-subtitle-1 warning--text text--darken-3">Bs. {{ formatoBs(resumenAsistente.total_gestora) }}</div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary">Líquido Pagable</div>
                  <div class="font-weight-black text-subtitle-1 success--text">Bs. {{ formatoBs(resumenAsistente.total_liquido) }}</div>
                </v-col>
              </v-row>
            </v-card>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4 d-flex align-center justify-space-between flex-wrap gap-2">
          <v-btn text class="rounded-pill text-capitalize" @click="dialogGenerarPlanilla = false">Cancelar</v-btn>

          <div class="d-flex align-center gap-2">
            <template v-if="resumenAsistente && !resumenAsistente.es_declarada && resumenAsistente.cantidad_funcionarios > 0">
              <v-btn
                outlined
                color="primary"
                class="rounded-pill font-weight-medium text-capitalize"
                :loading="generandoPlanilla"
                @click="ejecutarGenerarPlanilla('BORRADOR')"
              >
                <v-icon left small>mdi-file-clock-outline</v-icon>
                {{ tipoAsistente === 'TODAS' ? 'Guardar Todas como Borrador' : 'Guardar como Borrador' }}
              </v-btn>

              <v-btn
                color="purple darken-1"
                dark
                class="rounded-pill font-weight-bold text-capitalize"
                :loading="generandoPlanilla"
                @click="ejecutarGenerarPlanilla('DECLARADA')"
              >
                <v-icon left small>mdi-lock-check</v-icon>
                {{ tipoAsistente === 'TODAS' ? 'Consolidar y Declarar Todas' : 'Consolidar y Declarar' }}
              </v-btn>
            </template>

            <v-btn
              v-else-if="resumenAsistente && resumenAsistente.es_declarada"
              color="green darken-2"
              dark
              class="rounded-pill font-weight-bold text-capitalize"
              @click="descargarExcel({ mes_num: mesAsistente })"
            >
              <v-icon left small>mdi-file-excel</v-icon> Exportar Excel
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" top right :timeout="5000" rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text small v-bind="attrs" @click="snackbar.status = false">✕</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'ReportesRrhh',
  components: { ModalVisorPdf },
  data() {
    const ahora = new Date();
    const mesActual = ahora.getMonth() + 1;
    return {
      filtroTipoPlanilla: 'TODOS',
      filtroAnio: ahora.getFullYear(),
      mesActual,

      // Planillas reales registradas en base de datos
      planillasRegistradas: [],
      loadingPlanillas: false,

      // Eliminación de borrador
      dialogConfirmarEliminar: false,
      planillaAEliminar: null,
      eliminandoPlanilla: false,

      // Detalle del mes seleccionado
      mesSeleccionado: null,
      planillaMes: [],
      loadingDetalle: false,
      dialogDetalleMes: false,
      alertaAsistenciaModal: null,

      totalesModal: { total_ganado: 0, gestora_12_71: 0, liquido_salarial: 0 },
      patronalModal: { cns_10_bs: 0, gestora_patronal_bs: 0, total_patronal_bs: 0 },

      // Declaración
      cerrandoPlanilla: false,
      dialogConfirmarDeclarar: false,
      mesADeclarar: null,

      // Reapertura
      dialogConfirmarReabrir: false,
      mesAReabrir: null,
      reabriendoPlanilla: false,

      // Generador Asistente
      dialogGenerarPlanilla: false,
      mesAsistente: mesActual,
      tipoAsistente: 'TODAS',
      cargandoAsistente: false,
      generandoPlanilla: false,
      resumenAsistente: null,

      // Exportación
      exportandoExcel: false,

      // Boletas (legacy)
      dialogBoletaPago: false,
      boletaSeleccionada: null,
      dialogBoletasMasivas: false,

      // ── ModalVisorPdf — Boleta Individual ──
      mostrarVisorBoletaPdf: false,
      urlVisorBoletaPdf: '',
      tituloVisorBoletaPdf: '',
      subtituloVisorBoletaPdf: '',
      nombreDescargaBoletaPdf: 'boleta_pago.pdf',

      // ── ModalVisorPdf — Planilla Mensual ──
      mostrarVisorPlanillaPdf: false,
      urlVisorPlanillaPdf: '',
      tituloVisorPlanillaPdf: '',
      subtituloVisorPlanillaPdf: '',
      nombreDescargaPlanillaPdf: 'planilla_sueldos.pdf',

      // ── ModalVisorPdf — Boletas Masivas ──
      mostrarVisorBoletasMasivas: false,
      urlVisorBoletasMasivas: '',
      tituloVisorBoletasMasivas: '',
      subtituloVisorBoletasMasivas: '',
      nombreDescargaBoletasMasivas: 'boletas_masivas.pdf',

      tiposPlanilla: [
        { id: 'TODOS', nombre: 'Todas las Nóminas' },
        { id: 'PLANTA_PERMANENTE', nombre: 'Personal de Planta Permanente' },
        { id: 'PERSONAL_EVENTUAL', nombre: 'Personal Eventual' },
        { id: 'DIETAS_DIRECTORIO', nombre: 'Dietas Directorio' },
      ],

      tiposPlanillaAsistente: [
        { id: 'TODAS', nombre: 'Todas (Planta, Eventual y Directorio)' },
        { id: 'PLANTA_PERMANENTE', nombre: 'Personal de Planta Permanente' },
        { id: 'PERSONAL_EVENTUAL', nombre: 'Personal Eventual' },
        { id: 'DIETAS_DIRECTORIO', nombre: 'Dietas Directorio' },
      ],

      meses: [
        { id: 1, nombre: 'Enero' }, { id: 2, nombre: 'Febrero' }, { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' }, { id: 5, nombre: 'Mayo' }, { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' }, { id: 8, nombre: 'Agosto' }, { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' }, { id: 11, nombre: 'Noviembre' }, { id: 12, nombre: 'Diciembre' },
      ],

      headersPlanillas: [
        { text: 'Mes / Periodo', value: 'mes', sortable: false },
        { text: 'Tipo de Nómina', value: 'tipo_planilla_label', sortable: false },
        { text: 'Estado', value: 'estado', align: 'center', sortable: false },
        { text: 'CITE Oficial', value: 'cite_oficial', sortable: false },
        { text: 'Total Ganado', value: 'total_ganado_bs', align: 'right', sortable: false },
        { text: 'Descuentos (Gestora)', value: 'total_descuentos_bs', align: 'right', sortable: false },
        { text: 'Líquido Pagable', value: 'total_liquido_pagable_bs', align: 'right', sortable: false },
        { text: 'Nómina', value: 'nomina', align: 'center', sortable: false },
        { text: 'Fecha Cierre', value: 'fecha_cierre', align: 'center', sortable: false },
        { text: 'Acciones', value: 'acciones', align: 'center', sortable: false },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    aniosDisponibles() {
      const base = new Date().getFullYear();
      return [base - 2, base - 1, base, base + 1];
    },
    totalPlanillasGeneradas() {
      return this.planillasRegistradas.length;
    },
    totalPlanillasDeclaradas() {
      return this.planillasRegistradas.filter(p => p.es_declarada).length;
    },
    totalAnualPagado() {
      return this.planillasRegistradas
        .filter(p => p.es_declarada)
        .reduce((s, p) => s + Number(p.total_liquido_pagable_bs || 0), 0);
    },
    headersPlanillaModal() {
      if (this.filtroTipoPlanilla === 'DIETAS_DIRECTORIO') {
        return [
          { text: 'Ítem', value: 'item' },
          { text: 'Funcionario', value: 'funcionario' },
          { text: 'Sesiones', value: 'dias_trabajados', align: 'center' },
          { text: 'Dieta x Sesión', value: 'haber_basico', align: 'right' },
          { text: 'Líquido', value: 'liquido_salarial', align: 'right' },
          { text: '', value: 'acciones', align: 'center', sortable: false },
        ];
      }
      if (this.filtroTipoPlanilla === 'PERSONAL_EVENTUAL') {
        return [
          { text: 'Ítem', value: 'item' },
          { text: 'Funcionario', value: 'funcionario' },
          { text: 'Días', value: 'dias_trabajados', align: 'center' },
          { text: 'Honorario', value: 'haber_basico', align: 'right' },
          { text: 'Líquido', value: 'liquido_salarial', align: 'right' },
          { text: '', value: 'acciones', align: 'center', sortable: false },
        ];
      }
      return [
        { text: 'Ítem', value: 'item' },
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Días', value: 'dias_trabajados', align: 'center' },
        { text: 'Haber Básico', value: 'haber_basico', align: 'right' },
        { text: 'Bono Antigüedad', value: 'bono_antiguedad', align: 'right' },
        { text: 'Total Ganado', value: 'total_ganado', align: 'right' },
        { text: 'Gestora (12.71%)', value: 'gestora_12_71', align: 'right' },
        { text: 'Líquido Pagable', value: 'liquido_salarial', align: 'right' },
        { text: '', value: 'acciones', align: 'center', sortable: false },
      ];
    },
  },
  mounted() {
    this.cargarPlanillasRegistradas();
  },
  methods: {
    formatoBs(val) {
      if (val === null || val === undefined || isNaN(Number(val))) return '0.00';
      return Number(val).toFixed(2);
    },

    /** Carga únicamente las planillas que han sido generadas y registradas en el sistema */
    cargarPlanillasRegistradas() {
      this.loadingPlanillas = true;
      const params = { gestion: this.filtroAnio };
      if (this.filtroTipoPlanilla && this.filtroTipoPlanilla !== 'TODOS') {
        params.tipo_planilla = this.filtroTipoPlanilla;
      }

      axios.get('/api/rrhh/reportes/planillas-registradas', { params })
        .then(res => {
          if (res.data?.success) {
            this.planillasRegistradas = res.data.data || [];
          }
        })
        .catch(() => {
          this.showSnackbar('Error al cargar la lista de planillas.', 'error');
        })
        .finally(() => {
          this.loadingPlanillas = false;
        });
    },

    /** Abre el modal de detalle desde la fila de la tabla */
    abrirDetallePlanilla(item) {
      this.mesSeleccionado = {
        ...item,
        mes_num: item.mes,
        mes_nombre: item.mes_nombre,
        es_declarada: item.es_declarada,
        cite: item.cite_oficial,
      };
      this.filtroTipoPlanilla = item.tipo_planilla;
      this.abrirDetalleMes(this.mesSeleccionado);
    },

    /** Abre el visor PDF directamente desde la fila */
    abrirVisorPlanillaPdfDesdeFila(item) {
      const mes = item.mes;
      const anio = item.gestion;
      const tipo = item.tipo_planilla;
      this.urlVisorPlanillaPdf = `/api/rrhh/reportes/planilla-sueldos/pdf?mes=${mes}&anio=${anio}&tipo_planilla=${tipo}`;
      this.tituloVisorPlanillaPdf = `Planilla de Sueldos — ${item.mes_nombre} / ${anio}`;
      this.subtituloVisorPlanillaPdf = `${item.tipo_planilla_label} · ${item.cantidad_funcionarios} funcionarios`;
      this.nombreDescargaPlanillaPdf = `PLANILLA_EMAPAP_${mes}_${anio}.pdf`;
      this.mostrarVisorPlanillaPdf = true;
    },

    /** Abre el modal de detalle del mes y carga sus registros */
    abrirDetalleMes(item) {
      this.mesSeleccionado = item;
      this.dialogDetalleMes = true;
      this.loadingDetalle = true;
      this.planillaMes = [];
      this.alertaAsistenciaModal = null;

      const mesNum = item.mes_num || item.mes;
      const anioNum = item.gestion || this.filtroAnio;
      const tipo = item.tipo_planilla || (this.filtroTipoPlanilla !== 'TODOS' ? this.filtroTipoPlanilla : 'PLANTA_PERMANENTE');
      const params = { mes: mesNum, anio: anioNum, tipo_planilla: tipo };

      // Verificar asistencia
      axios.get(`/api/rrhh/asistencias/verificar-mes?mes=${mesNum}&anio=${anioNum}`)
        .then(res => {
          if (res.data?.success && !res.data.tiene_asistencia) {
            this.alertaAsistenciaModal = `Sin marcaciones registradas para ${item.mes_nombre}/${anioNum}. La planilla se calcula con días base.`;
          }
        }).catch(() => {});

      axios.get('/api/rrhh/reportes/planilla-sueldos', { params })
        .then(res => {
          if (res.data?.success) {
            this.planillaMes = res.data.data || [];
            const items = this.planillaMes;
            this.totalesModal = {
              total_ganado: items.reduce((s, i) => s + Number(i.total_ganado || 0), 0),
              gestora_12_71: items.reduce((s, i) => s + Number(i.gestora_12_71 || 0), 0),
              liquido_salarial: items.reduce((s, i) => s + Number(i.liquido_salarial || 0), 0),
            };
            this.patronalModal = res.data.patronal || { cns_10_bs: 0, gestora_patronal_bs: 0, total_patronal_bs: 0 };
            const esDecl = res.data.estado_planilla === 'DECLARADA' || (Boolean(res.data.es_declarada) && res.data.estado_planilla !== 'BORRADOR');
            this.mesSeleccionado = {
              ...this.mesSeleccionado,
              es_declarada: esDecl,
              cite: esDecl ? (res.data.cite_oficial || this.mesSeleccionado.cite) : null,
            };
          }
        })
        .catch(() => this.showSnackbar('Error al cargar detalle del mes.', 'error'))
        .finally(() => { this.loadingDetalle = false; });
    },

    confirmarDeclarar(item) {
      this.mesADeclarar = item;
      this.dialogConfirmarDeclarar = true;
    },

    ejecutarDeclarar() {
      if (!this.mesADeclarar) return;
      this.cerrandoPlanilla = true;

      const mes = this.mesADeclarar.mes || this.mesADeclarar.mes_num;
      const anio = this.mesADeclarar.gestion || this.filtroAnio;
      const tipo = this.mesADeclarar.tipo_planilla || (this.filtroTipoPlanilla !== 'TODOS' ? this.filtroTipoPlanilla : 'PLANTA_PERMANENTE');

      axios.post('/api/rrhh/reportes/planilla-sueldos/declarar', {
        mes: mes,
        anio: anio,
        tipo_planilla: tipo,
      }).then(res => {
          this.showSnackbar(res.data?.message || 'Planilla declarada exitosamente.', 'success');
          this.dialogConfirmarDeclarar = false;
          this.dialogDetalleMes = false;
          this.cargarPlanillasRegistradas();
        })
        .catch(err => this.showSnackbar(err.response?.data?.message || 'Error al declarar planilla.', 'error'))
        .finally(() => { this.cerrandoPlanilla = false; });
    },

    confirmarReabrir(item) {
      this.mesAReabrir = item;
      this.dialogConfirmarReabrir = true;
    },

    ejecutarReabrir() {
      if (!this.mesAReabrir) return;
      this.reabriendoPlanilla = true;

      const mes = this.mesAReabrir.mes || this.mesAReabrir.mes_num;
      const anio = this.mesAReabrir.gestion || this.filtroAnio;
      const tipo = this.mesAReabrir.tipo_planilla || (this.filtroTipoPlanilla !== 'TODOS' ? this.filtroTipoPlanilla : 'PLANTA_PERMANENTE');

      axios.post('/api/rrhh/reportes/planilla-sueldos/reabrir', {
        mes: mes,
        anio: anio,
        tipo_planilla: tipo,
      }).then(res => {
        this.showSnackbar(res.data?.message || 'Planilla reabierta exitosamente.', 'success');
        this.dialogConfirmarReabrir = false;
        this.dialogDetalleMes = false;
        this.cargarPlanillasRegistradas();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al reabrir planilla.', 'error');
      }).finally(() => {
        this.reabriendoPlanilla = false;
      });
    },

    confirmarEliminarBorrador(item) {
      this.planillaAEliminar = item;
      this.dialogConfirmarEliminar = true;
    },

    ejecutarEliminarBorrador() {
      if (!this.planillaAEliminar) return;
      this.eliminandoPlanilla = true;
      axios.delete(`/api/rrhh/reportes/planillas/${this.planillaAEliminar.id}`)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Planilla borrador eliminada exitosamente.', 'success');
          this.dialogConfirmarEliminar = false;
          this.cargarPlanillasRegistradas();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al eliminar planilla.', 'error');
        })
        .finally(() => {
          this.eliminandoPlanilla = false;
        });
    },

    abrirModalGenerarPlanilla() {
      this.mesAsistente = this.mesActual;
      this.tipoAsistente = (this.filtroTipoPlanilla && this.filtroTipoPlanilla !== 'TODOS')
        ? this.filtroTipoPlanilla
        : 'TODAS';
      this.dialogGenerarPlanilla = true;
      this.cargarPreviaAsistente();
    },

    cargarPreviaAsistente() {
      this.cargandoAsistente = true;
      this.resumenAsistente = null;
      axios.get('/api/rrhh/reportes/planilla-sueldos', {
        params: { mes: this.mesAsistente, anio: this.filtroAnio, tipo_planilla: this.tipoAsistente },
      }).then(res => {
        const d = res.data;
        const items = d.data || [];
        this.resumenAsistente = {
          es_declarada: !!(d.declarada || d.es_declarada),
          cite: d.cite_oficial || '',
          cantidad_funcionarios: items.length,
          total_ganado: d.total_ganado_bs !== undefined ? Number(d.total_ganado_bs) : items.reduce((s, i) => s + Number(i.total_ganado || 0), 0),
          total_gestora: d.total_descuentos_bs !== undefined ? Number(d.total_descuentos_bs) : items.reduce((s, i) => s + Number(i.gestora_12_71 || 0), 0),
          total_liquido: d.total_liquido_salarial_bs !== undefined ? Number(d.total_liquido_salarial_bs) : items.reduce((s, i) => s + Number(i.liquido_salarial || 0), 0),
          patronal: d.patronal || null,
        };
      }).catch(() => {
        this.resumenAsistente = {
          es_declarada: false,
          cite: '',
          cantidad_funcionarios: 0,
          total_ganado: 0,
          total_gestora: 0,
          total_liquido: 0,
        };
      }).finally(() => {
        this.cargandoAsistente = false;
      });
    },

    /** Ejecuta la creación o consolidación de la planilla desde el modal asistente */
    ejecutarGenerarPlanilla(estadoDeseado) {
      this.generandoPlanilla = true;
      axios.post('/api/rrhh/reportes/planillas/generar', {
        mes: this.mesAsistente,
        anio: this.filtroAnio,
        tipo_planilla: this.tipoAsistente,
        estado: estadoDeseado,
      }).then(res => {
        this.showSnackbar(res.data?.message || 'Planilla procesada exitosamente.', 'success');
        this.dialogGenerarPlanilla = false;
        if (this.tipoAsistente === 'TODAS') {
          this.filtroTipoPlanilla = 'TODOS';
        }
        this.cargarPlanillasRegistradas();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al procesar planilla.', 'error');
      }).finally(() => {
        this.generandoPlanilla = false;
      });
    },

    asistenteRevisarDetalle() {
      this.dialogGenerarPlanilla = false;
      this.filtroTipoPlanilla = this.tipoAsistente;
      const mesItem = this.meses.find(m => m.id === this.mesAsistente);
      const item = {
        mes_num: this.mesAsistente,
        mes: this.mesAsistente,
        mes_nombre: mesItem ? mesItem.nombre : `Mes ${this.mesAsistente}`,
        tipo_planilla: this.tipoAsistente,
      };
      this.abrirDetalleMes(item);
    },

    asistenteDeclarar() {
      this.dialogGenerarPlanilla = false;
      this.filtroTipoPlanilla = this.tipoAsistente;
      const mesItem = this.meses.find(m => m.id === this.mesAsistente);
      const item = {
        mes_num: this.mesAsistente,
        mes: this.mesAsistente,
        mes_nombre: mesItem ? mesItem.nombre : `Mes ${this.mesAsistente}`,
        tipo_planilla: this.tipoAsistente,
      };
      this.confirmarDeclarar(item);
    },

    abrirBoletaIndividual(item) {
      // ModalVisorPdf — igual que Facturación SIAT
      const mes = this.mesSeleccionado?.mes_num;
      const anio = this.filtroAnio;
      this.urlVisorBoletaPdf = `/api/rrhh/reportes/boleta-pago/${item.id_persona}/pdf?mes=${mes}&anio=${anio}`;
      this.tituloVisorBoletaPdf = `Boleta Oficial de Pago — ${item.funcionario}`;
      this.subtituloVisorBoletaPdf = `${this.mesSeleccionado?.mes_nombre} / ${anio} · Ítem ${item.item || '-'} · ${item.cargo || ''}`;
      this.nombreDescargaBoletaPdf = `BOLETA_${(item.funcionario || 'funcionario').replace(/ /g, '_')}_${mes}_${anio}.pdf`;
      this.mostrarVisorBoletaPdf = true;
    },

    abrirVisorPlanillaPdf() {
      if (!this.mesSeleccionado) return;
      const mes = this.mesSeleccionado.mes_num;
      const anio = this.filtroAnio;
      const tipo = this.filtroTipoPlanilla;
      this.urlVisorPlanillaPdf = `/api/rrhh/reportes/planilla-sueldos/pdf?mes=${mes}&anio=${anio}&tipo_planilla=${tipo}`;
      this.tituloVisorPlanillaPdf = `Planilla de Sueldos — ${this.mesSeleccionado.mes_nombre} / ${anio}`;
      this.subtituloVisorPlanillaPdf = `${this.getNombreTipoPlanilla(tipo)} · ${this.planillaMes.length} funcionarios`;
      this.nombreDescargaPlanillaPdf = `PLANILLA_EMAPAP_${mes}_${anio}.pdf`;
      this.mostrarVisorPlanillaPdf = true;
    },

    abrirBoletasMasivas() {
      this.abrirVisorBoletasMasivasPdf();
    },

    abrirVisorBoletasMasivasPdf() {
      if (!this.mesSeleccionado) return;
      const mes = this.mesSeleccionado.mes_num;
      const anio = this.filtroAnio;
      const tipo = this.filtroTipoPlanilla;
      this.urlVisorBoletasMasivas = `/api/rrhh/reportes/boletas-masivas/pdf?mes=${mes}&anio=${anio}&tipo_planilla=${tipo}`;
      this.tituloVisorBoletasMasivas = `Boletas Masivas — ${this.mesSeleccionado.mes_nombre} / ${anio}`;
      this.subtituloVisorBoletasMasivas = `Todas las papeletas de pago individuales · ${this.planillaMes.length} funcionarios`;
      this.nombreDescargaBoletasMasivas = `BOLETAS_MASIVAS_${mes}_${anio}.pdf`;
      this.mostrarVisorBoletasMasivas = true;
    },

    descargarExcel(item) {
      this.exportandoExcel = true;
      const mes = item ? item.mes_num : (this.mesSeleccionado ? this.mesSeleccionado.mes_num : 1);
      axios.get(`/api/rrhh/reportes/planilla-sueldos/excel?mes=${mes}&anio=${this.filtroAnio}`, { responseType: 'blob' })
        .then(res => {
          const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.setAttribute('download', `PLANILLA_EMAPAP_${mes}_${this.filtroAnio}.xlsx`);
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          this.showSnackbar('Archivo Excel exportado exitosamente.', 'success');
        })
        .catch(() => this.showSnackbar('Error al exportar Excel.', 'error'))
        .finally(() => { this.exportandoExcel = false; });
    },

    exportarCsvModal() {
      if (!this.planillaMes.length) { this.showSnackbar('No hay datos para exportar.', 'warning'); return; }
      let csv = 'Item;Funcionario;CI;Cargo;Dias;Haber Basico;Bono Antiguedad;Total Ganado;Gestora 12.71%;Liquido\n';
      this.planillaMes.forEach(r => {
        csv += `"${r.item||'-'}";"${r.funcionario}";"${r.ci}";"${r.cargo}";"${r.dias_trabajados||30}";"${r.haber_basico}";"${r.bono_antiguedad}";"${r.total_ganado}";"${r.gestora_12_71}";"${r.liquido_salarial}"\n`;
      });
      const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      const mes = this.mesSeleccionado ? this.mesSeleccionado.mes_num : '';
      link.setAttribute('download', `planilla_${mes}_${this.filtroAnio}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      this.showSnackbar('CSV exportado.', 'success');
    },

    // Métodos de impresión legacy eliminados — ahora usa ModalVisorPdf

    getNombreTipoPlanilla(id) {
      if (id === 'TODAS' || id === 'TODOS' || id === 'CONSOLIDADO_GENERAL') return 'Todas las Nóminas';
      const item = this.tiposPlanilla.find(t => t.id === id);
      return item ? item.nombre : 'Planilla';
    },

    getNombreMes(idMes) {
      const item = this.meses.find(m => m.id === Number(idMes));
      return item ? item.nombre : 'Mes';
    },

    convertirNumeroALetras(monto) {
      if (!monto) return 'CERO 00/100 BOLIVIANOS';
      const entero = Math.floor(monto);
      const centavos = String(Math.round((monto - entero) * 100)).padStart(2, '0');
      return `${entero} ${centavos}/100 BOLIVIANOS`;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
@media print {
  body * { visibility: hidden; }
  #boleta-individual-print-area, #boleta-individual-print-area *,
  #boletas-masivas-print-area, #boletas-masivas-print-area * {
    visibility: visible;
  }
  #boleta-individual-print-area, #boletas-masivas-print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
.linea-firma {
  border-top: 1px solid #555;
  width: 160px;
  margin: 0 auto 4px;
}
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.border-b { border-bottom: 1px solid #e0e0e0; }
</style>
