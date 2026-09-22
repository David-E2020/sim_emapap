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
      <v-col cols="12" md="5" class="d-flex justify-end align-center">
        <v-btn
          color="primary"
          class="mr-2 text-none font-weight-bold"
          elevation="2"
          @click="abrirModalNuevoPeriodo"
        >
          <v-icon left>mdi-plus-circle</v-icon>
          Abrir Período ("Otro")
        </v-btn>
        <v-btn
          outlined
          color="secondary"
          class="text-none"
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
          <span class="font-weight-bold text-primary">{{ item.periodo }}</span>
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
          <div class="d-flex align-center">
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
        { text: 'PERÍODO', value: 'periodo', align: 'center', width: '90px' },
        { text: 'FECHA DESDE', value: 'fecha_inicio_consumo', width: '110px' },
        { text: 'FECHA HASTA', value: 'fecha_fin_consumo', width: '110px' },
        { text: 'FECHA VNCMTO.', value: 'fecha_vencimiento_pago', width: '115px' },
        { text: 'ESTADO', value: 'estado', align: 'center', width: '140px' },
        { text: 'ABONADOS', value: 'lecturas_count', align: 'center', width: '95px' },
        { text: 'ACCIONES OPERATIVAS', value: 'acciones', align: 'center', sortable: false, width: '180px' },
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
</style>
