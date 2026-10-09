<template>
  <div>
    <!-- CABECERA INSTITUCIONAL NATIVA MATERIO CON TÍTULO CONTEXTUAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">{{ iconoModulo }}</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">{{ tituloModulo }}</h2>
            <span class="text-caption text-secondary">
              {{ subtituloModulo }}
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon color="primary" v-bind="attrs" v-on="on" @click="recargarTodo" :loading="cargandoGeneral" class="mr-1">
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Actualizar trámites</span>
          </v-tooltip>

          <v-menu offset-y>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" class="rounded-pill font-weight-bold text-capitalize px-4 elevation-1" v-bind="attrs" v-on="on">
                <v-icon left small>mdi-plus</v-icon> + Nuevo Trámite <v-icon right small>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list dense class="py-1">
              <v-list-item @click="abrirModalSolicitud">
                <v-list-item-icon class="mr-2"><v-icon small color="primary">mdi-file-document-edit</v-icon></v-list-item-icon>
                <v-list-item-title class="font-weight-medium">Boleta de Salida / Permiso</v-list-item-title>
              </v-list-item>
              <v-list-item @click="abrirModalComision">
                <v-list-item-icon class="mr-2"><v-icon small color="indigo">mdi-airplane-takeoff</v-icon></v-list-item-icon>
                <v-list-item-title class="font-weight-medium">Comisión Oficial de Viaje</v-list-item-title>
              </v-list-item>
              <v-list-item @click="abrirModalOmision">
                <v-list-item-icon class="mr-2"><v-icon small color="warning darken-2">mdi-clock-alert-outline</v-icon></v-list-item-icon>
                <v-list-item-title class="font-weight-medium">Regularizar Omisión de Marcado</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE ESTADÍSTICAS KPIS INTERACTIVAS -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-4 erp-card-elevated d-flex align-center justify-space-between"
          :class="{ 'card-kpi-activa': tipoTramiteActivo === 'FIRMA' }"
          rounded="lg"
          @click="tipoTramiteActivo = 'FIRMA'"
          style="cursor: pointer;"
        >
          <div>
            <div class="text-caption text-secondary font-weight-bold text-uppercase">Para Mi Firma</div>
            <div class="text-h4 font-weight-black" :class="totalPendientes > 0 ? 'error--text' : 'primary--text'">{{ totalPendientes }}</div>
            <div class="text-caption text-secondary">Pendientes de aprobación</div>
          </div>
          <v-avatar :color="totalPendientes > 0 ? 'error lighten-5' : 'primary lighten-5'" size="48">
            <v-icon :color="totalPendientes > 0 ? 'error' : 'primary'">mdi-inbox-arrow-down</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-4 erp-card-elevated d-flex align-center justify-space-between"
          :class="{ 'card-kpi-activa': tipoTramiteActivo === 'SOLICITUDES' }"
          rounded="lg"
          @click="tipoTramiteActivo = 'SOLICITUDES'"
          style="cursor: pointer;"
        >
          <div>
            <div class="text-caption text-secondary font-weight-bold text-uppercase">Boletas de Salida</div>
            <div class="text-h4 font-weight-black primary--text">{{ listaSolicitudes.length }}</div>
            <div class="text-caption text-secondary">Permisos y salidas particulares</div>
          </div>
          <v-avatar color="primary lighten-5" size="48">
            <v-icon color="primary">mdi-file-document-outline</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-4 erp-card-elevated d-flex align-center justify-space-between"
          :class="{ 'card-kpi-activa': tipoTramiteActivo === 'COMISIONES' }"
          rounded="lg"
          @click="tipoTramiteActivo = 'COMISIONES'"
          style="cursor: pointer;"
        >
          <div>
            <div class="text-caption text-secondary font-weight-bold text-uppercase">Comisiones Oficiales</div>
            <div class="text-h4 font-weight-black indigo--text">{{ listaComisiones.length }}</div>
            <div class="text-caption text-secondary">Viajes y asignaciones externas</div>
          </div>
          <v-avatar color="indigo lighten-5" size="48">
            <v-icon color="indigo">mdi-airplane-takeoff</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-4 erp-card-elevated d-flex align-center justify-space-between"
          :class="{ 'card-kpi-activa': tipoTramiteActivo === 'OMISIONES' }"
          rounded="lg"
          @click="tipoTramiteActivo = 'OMISIONES'"
          style="cursor: pointer;"
        >
          <div>
            <div class="text-caption text-secondary font-weight-bold text-uppercase">Omisiones de Marcación</div>
            <div class="text-h4 font-weight-black warning--text text--darken-2">{{ listaOmisiones.length }}</div>
            <div class="text-caption text-secondary">Justificaciones biométricas</div>
          </div>
          <v-avatar color="warning lighten-5" size="48">
            <v-icon color="warning darken-2">mdi-clock-alert-outline</v-icon>
          </v-avatar>
        </v-card>
      </v-col>
    </v-row>

    <!-- SELECTOR DE TIPO DE TRÁMITE (ESTILO PLANILLAS CON v-btn-toggle Y SELECT ALTERNATIVO) -->
    <v-card rounded="lg" class="pa-3 mb-4 erp-card-elevated">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center gap-3 flex-wrap">
          <span class="text-caption font-weight-bold text-secondary text-uppercase">TIPO DE TRÁMITE:</span>

          <!-- V-BTN-TOGGLE (IDÉNTICO AL COMPONENTE DE PLANILLAS/REPORTES) -->
          <v-btn-toggle
            v-model="tipoTramiteActivo"
            mandatory
            dense
            color="primary"
            class="d-none d-md-inline-flex border rounded"
          >
            <v-btn small value="TODOS" class="text-capitalize px-3">
              <v-icon left x-small>mdi-format-list-bulleted</v-icon> Todas
            </v-btn>
            <v-btn small value="FIRMA" class="text-capitalize px-3">
              <v-icon left x-small :color="totalPendientes > 0 ? 'error' : ''">mdi-inbox-arrow-down</v-icon>
              Para Mi Firma
              <v-chip v-if="totalPendientes > 0" x-small color="error" class="ml-1 px-1 font-weight-bold" style="height: 16px;">
                {{ totalPendientes }}
              </v-chip>
            </v-btn>
            <v-btn small value="SOLICITUDES" class="text-capitalize px-3">
              <v-icon left x-small color="primary">mdi-file-document-outline</v-icon> Boletas de Salida
            </v-btn>
            <v-btn small value="COMISIONES" class="text-capitalize px-3">
              <v-icon left x-small color="indigo">mdi-airplane-takeoff</v-icon> Comisiones Oficiales
            </v-btn>
            <v-btn small value="OMISIONES" class="text-capitalize px-3">
              <v-icon left x-small color="warning darken-2">mdi-clock-alert-outline</v-icon> Omisiones de Marcación
            </v-btn>
          </v-btn-toggle>

          <!-- SELECT ALTERNATIVO PARA PANTALLAS COMPACTAS O MÓVILES -->
          <div class="d-md-none" style="min-width: 240px;">
            <v-select
              v-model="tipoTramiteActivo"
              :items="opcionesTipoTramiteSelect"
              item-text="text"
              item-value="value"
              dense
              outlined
              hide-details
              prepend-inner-icon="mdi-filter-variant"
              class="text-caption"
            ></v-select>
          </div>
        </div>

        <!-- INDICADORES TOTALIZADORES A LA DERECHA -->
        <div class="d-flex align-center gap-3">
          <div class="text-center">
            <div class="text-caption text-secondary font-weight-bold">TOTAL TRÁMITES</div>
            <div class="text-h6 font-weight-black primary--text">{{ totalGeneral }}</div>
          </div>
          <v-divider vertical class="mx-1"></v-divider>
          <div class="text-center">
            <div class="text-caption text-secondary font-weight-bold">PENDIENTES FIRMA</div>
            <div class="text-h6 font-weight-black" :class="totalPendientes > 0 ? 'error--text' : 'success--text'">
              {{ totalPendientes }}
            </div>
          </div>
        </div>
      </div>
    </v-card>

    <!-- TARJETA PRINCIPAL CON TABLA DE TRÁMITES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <!-- BARRA DE HERRAMIENTAS: FILTRO POR ESTADO (CON v-btn-toggle) Y BÚSQUEDA -->
      <div class="px-4 py-3 d-flex align-center justify-space-between flex-wrap gap-2 border-b">
        <div class="d-flex align-center flex-wrap gap-2">
          <span class="text-caption font-weight-bold text-secondary mr-1 text-uppercase">ESTADO:</span>
          <v-btn-toggle v-model="filtroEstado" mandatory dense color="primary" class="border rounded">
            <v-btn small value="TODOS" class="text-capitalize px-3">
              <v-icon left x-small>mdi-circle-outline</v-icon> Todos
            </v-btn>
            <v-btn small value="PENDIENTE" class="text-capitalize px-3 warning--text text--darken-2">
              <v-icon left x-small color="warning darken-2">mdi-clock-outline</v-icon> Pendientes
            </v-btn>
            <v-btn small value="APROBADO" class="text-capitalize px-3 success--text">
              <v-icon left x-small color="success">mdi-check-circle-outline</v-icon> Aprobados
            </v-btn>
            <v-btn small value="RECHAZADO" class="text-capitalize px-3 error--text">
              <v-icon left x-small color="error">mdi-close-circle-outline</v-icon> Rechazados
            </v-btn>
          </v-btn-toggle>
        </div>

        <div style="max-width: 320px; width: 100%;">
          <v-text-field
            v-model="busquedaTexto"
            placeholder="Buscar funcionario, CI, CITE..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            class="rounded-lg text-caption"
          ></v-text-field>
        </div>
      </div>

      <!-- TABLA DE DATOS INSTITUCIONAL -->
      <v-data-table
        :headers="tableHeaders"
        :items="itemsFiltrados"
        :loading="cargandoGeneral"
        :items-per-page="10"
        class="elevation-0"
        no-data-text="No se encontraron trámites registrados con estos filtros"
        loading-text="Cargando trámites oficiales..."
      >
        <!-- TIPO Y CITE -->
        <template v-slot:item.cite_tipo="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar size="32" :color="getColorTipo(item) + ' lighten-4'" class="mr-2">
              <v-icon size="18" :color="getColorTipo(item)">{{ getIconoTipo(item) }}</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-caption text-truncate" :class="getColorTipo(item) + '--text'">
                {{ item.cite || 'S/C' }}
              </div>
              <div class="text-caption text-secondary text-truncate" style="font-size: 11px !important;">
                {{ item.permiso_sigla || getSiglaTipo(item) }} • {{ item.permiso_nombre || getTituloTipo(item) }}
              </div>
            </div>
          </div>
        </template>

        <!-- FUNCIONARIO -->
        <template v-slot:item.funcionario="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar size="28" color="primary" class="white--text font-weight-bold mr-2 text-caption">
              {{ getNombreFuncionario(item).charAt(0) }}
            </v-avatar>
            <div>
              <div class="font-weight-bold text-caption text-slate-800">
                {{ getNombreFuncionario(item) }}
              </div>
              <div class="text-caption text-secondary" style="font-size: 11px !important;">
                CI: {{ item.nro_documento || '-' }}
              </div>
            </div>
          </div>
        </template>

        <!-- FECHAS Y HORAS -->
        <template v-slot:item.fechas="{ item }">
          <div class="text-caption py-2">
            <div class="d-flex align-center">
              <v-icon x-small color="grey" class="mr-1">mdi-calendar</v-icon>
              <strong>{{ item.fecha_inicio || item.fecha }}</strong>
              <span v-if="item.fecha_fin && item.fecha_fin !== item.fecha_inicio" class="text-secondary ml-1">al {{ item.fecha_fin }}</span>
            </div>
            <div v-if="item.hora_inicio || item.hora_marcado_omision" class="text-secondary mt-1" style="font-size: 11px !important;">
              <v-icon x-small color="grey" class="mr-1">mdi-clock-outline</v-icon>
              {{ item.hora_inicio ? item.hora_inicio + (item.hora_fin ? ' - ' + item.hora_fin : '') : item.hora_marcado_omision }}
            </div>
          </div>
        </template>

        <!-- DETALLE / VIÁTICO -->
        <template v-slot:item.detalle="{ item }">
          <div class="text-caption py-2">
            <div v-if="item.horas_solicitadas">
              <v-chip x-small color="grey lighten-3" class="font-weight-bold">
                {{ item.horas_solicitadas }} hrs
              </v-chip>
            </div>
            <div v-else-if="item.metadata && item.metadata.monto_viatico">
              <strong class="primary--text">Bs. {{ item.metadata.monto_viatico }}</strong>
              <div class="text-secondary" style="font-size: 11px !important;">{{ item.metadata.transporte || 'TERRESTRE' }}</div>
            </div>
            <div v-else-if="item.turno_periodo">
              <v-chip x-small color="warning lighten-4" text-color="warning darken-3" class="font-weight-bold">
                {{ item.turno_periodo }}
              </v-chip>
            </div>
            <div v-else class="text-secondary">-</div>
          </div>
        </template>

        <!-- MOTIVO -->
        <template v-slot:item.motivo="{ item }">
          <div class="text-caption text-truncate-2 py-2" style="max-width: 250px;">
            <span v-if="item.lugar" class="font-weight-bold primary--text d-block text-truncate">
              <v-icon x-small color="primary">mdi-map-marker</v-icon> {{ item.lugar }}
            </span>
            {{ item.motivo || 'Sin observaciones' }}
          </div>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            small
            :color="getEstadoColor(item._estado || item.estado_aprobacion)"
            label
            class="font-weight-bold text-white text-capitalize"
          >
            {{ (item._estado || item.estado_aprobacion || 'PENDIENTE').toLowerCase() }}
          </v-chip>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center gap-1">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="verDetalleItem(item)">
                  <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver detalle y firma</span>
            </v-tooltip>

            <template v-if="(item._estado === 'PENDIENTE' || item.estado_aprobacion === 'PENDIENTE')">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon small color="success" v-bind="attrs" v-on="on" :loading="resolviendo" @click="resolverItem(item, 'APROBADO')">
                    <v-icon small>mdi-check-circle-outline</v-icon>
                  </v-btn>
                </template>
                <span>Aprobar trámite</span>
              </v-tooltip>

              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon small color="error" v-bind="attrs" v-on="on" :loading="resolviendo" @click="resolverItem(item, 'RECHAZADO')">
                    <v-icon small>mdi-close-circle-outline</v-icon>
                  </v-btn>
                </template>
                <span>Rechazar trámite</span>
              </v-tooltip>
            </template>

            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="secondary" v-bind="attrs" v-on="on" @click="imprimirDocumento(item)">
                  <v-icon small>mdi-printer</v-icon>
                </v-btn>
              </template>
              <span>Imprimir boleta oficial</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO DETALLE Y AUTORIZACIÓN DEL TRÁMITE -->
    <v-dialog v-model="dialogDetalle" max-width="800" scrollable>
      <v-card v-if="itemSeleccionado" rounded="lg">
        <v-card-title class="primary white--text py-3 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar size="32" color="white" class="mr-2">
              <v-icon size="18" :color="getColorTipo(itemSeleccionado)">{{ getIconoTipo(itemSeleccionado) }}</v-icon>
            </v-avatar>
            <span class="text-subtitle-1 font-weight-bold">
              {{ itemSeleccionado.permiso_nombre || getTituloTipo(itemSeleccionado) }}
            </span>
          </div>
          <div class="d-flex align-center">
            <v-chip small :color="getEstadoColor(itemSeleccionado._estado || itemSeleccionado.estado_aprobacion)" label class="font-weight-bold text-white mr-2">
              {{ itemSeleccionado._estado || itemSeleccionado.estado_aprobacion || 'PENDIENTE' }}
            </v-chip>
            <v-btn icon dark x-small @click="dialogDetalle = false"><v-icon>mdi-close</v-icon></v-btn>
          </div>
        </v-card-title>

        <v-card-text class="pa-4">
          <!-- CABECERA INSTITUCIONAL CON CITE -->
          <div class="d-flex align-center justify-space-between pb-3 mb-3 border-b flex-wrap gap-2">
            <div>
              <div class="text-caption text-secondary font-weight-bold text-uppercase">CÓDIGO OFICIAL (CITE)</div>
              <h3 class="text-h6 font-weight-black primary--text mb-0">{{ itemSeleccionado.cite || 'DOCUMENTO INSTITUCIONAL' }}</h3>
            </div>
            <v-btn color="primary" outlined small class="rounded-pill" @click="imprimirDocumento(itemSeleccionado)">
              <v-icon left small>mdi-printer</v-icon> Imprimir Boleta
            </v-btn>
          </div>

          <!-- DATOS DEL FUNCIONARIO -->
          <v-card outlined rounded="lg" class="pa-3 mb-4 bg-light">
            <div class="text-caption text-secondary font-weight-bold text-uppercase mb-2">FUNCIONARIO SOLICITANTE</div>
            <div class="d-flex align-center">
              <v-avatar size="44" color="primary" class="white--text font-weight-bold mr-3">
                {{ getNombreFuncionario(itemSeleccionado).charAt(0) }}
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-bold text-slate-800">
                  {{ getNombreFuncionario(itemSeleccionado) }}
                </div>
                <div class="text-caption text-secondary">
                  C.I.: <strong>{{ itemSeleccionado.nro_documento || '-' }}</strong> • Funcionario Oficial EMAPAP
                </div>
              </div>
            </div>
          </v-card>

          <!-- DETALLES OFICIALES -->
          <div class="text-caption text-secondary font-weight-bold text-uppercase mb-2">DETALLES OFICIALES DEL TRÁMITE</div>
          <v-row dense class="text-body-2 mb-3">
            <v-col cols="12" sm="6">
              <span class="text-secondary text-caption d-block">Fecha Inicio / Salida:</span>
              <strong>{{ itemSeleccionado.fecha_inicio || itemSeleccionado.fecha || '-' }}</strong>
              <span v-if="itemSeleccionado.hora_inicio" class="text-secondary ml-1">({{ itemSeleccionado.hora_inicio }})</span>
            </v-col>
            <v-col cols="12" sm="6">
              <span class="text-secondary text-caption d-block">Fecha Fin / Retorno:</span>
              <strong>{{ itemSeleccionado.fecha_fin || itemSeleccionado.fecha || '-' }}</strong>
              <span v-if="itemSeleccionado.hora_fin" class="text-secondary ml-1">({{ itemSeleccionado.hora_fin }})</span>
            </v-col>
            <v-col cols="12" sm="6" v-if="itemSeleccionado.horas_solicitadas">
              <span class="text-secondary text-caption d-block">Tiempo Solicitado:</span>
              <v-chip small color="grey lighten-3" class="font-weight-bold">{{ itemSeleccionado.horas_solicitadas }} Horas</v-chip>
            </v-col>
            <v-col cols="12" sm="6" v-if="itemSeleccionado.lugar">
              <span class="text-secondary text-caption d-block">Destino Oficial:</span>
              <v-icon x-small color="primary" left>mdi-map-marker</v-icon>
              <strong>{{ itemSeleccionado.lugar }}</strong>
            </v-col>
            <v-col cols="12" sm="6" v-if="itemSeleccionado.metadata && itemSeleccionado.metadata.monto_viatico">
              <span class="text-secondary text-caption d-block">Monto de Viático:</span>
              <strong class="primary--text">Bs. {{ itemSeleccionado.metadata.monto_viatico }}</strong>
              <span class="text-caption text-secondary ml-1">({{ itemSeleccionado.metadata.transporte || 'TERRESTRE' }})</span>
            </v-col>
            <v-col cols="12" sm="6" v-if="itemSeleccionado.turno_periodo">
              <span class="text-secondary text-caption d-block">Turno Omitido:</span>
              <strong>{{ itemSeleccionado.turno_periodo }}</strong> (Hora: {{ itemSeleccionado.hora_marcado_omision || '08:30' }})
            </v-col>
            <v-col cols="12" class="mt-2">
              <span class="text-secondary text-caption d-block">Motivo y Justificación Declarada:</span>
              <div class="pa-3 bg-light rounded text-body-2 text-slate-800 border">
                {{ itemSeleccionado.motivo || 'Sin observaciones registradas' }}
              </div>
            </v-col>
          </v-row>

          <!-- FLUJO DE FIRMAS -->
          <div class="pt-3 border-t">
            <div class="text-caption text-secondary font-weight-bold text-uppercase mb-3">FLUJO INSTITUCIONAL DE FIRMA Y AUTORIZACIÓN</div>
            <v-row dense class="text-center">
              <v-col cols="12" sm="4">
                <div class="firma-box border rounded pa-2">
                  <v-icon small color="success">mdi-check-decagram</v-icon>
                  <div class="text-caption font-weight-bold mt-1">1. FUNCIONARIO</div>
                  <div class="text-caption text-secondary">Solicitud Emitida</div>
                </div>
              </v-col>
              <v-col cols="12" sm="4">
                <div class="firma-box border rounded pa-2" :class="itemSeleccionado._estado === 'APROBADO' ? 'bg-success-light' : 'bg-light'">
                  <v-icon small :color="itemSeleccionado._estado === 'APROBADO' ? 'success' : 'grey'">
                    {{ itemSeleccionado._estado === 'APROBADO' ? 'mdi-check-decagram' : 'mdi-clock-outline' }}
                  </v-icon>
                  <div class="text-caption font-weight-bold mt-1">2. SUPERVISOR / JEFE</div>
                  <div class="text-caption text-secondary">
                    {{ itemSeleccionado._estado === 'APROBADO' ? 'Autorizado' : 'Pendiente' }}
                  </div>
                </div>
              </v-col>
              <v-col cols="12" sm="4">
                <div class="firma-box border rounded pa-2">
                  <v-icon small color="primary">mdi-shield-check</v-icon>
                  <div class="text-caption font-weight-bold mt-1">3. RECURSOS HUMANOS</div>
                  <div class="text-caption text-secondary">Registro en Sistema</div>
                </div>
              </v-col>
            </v-row>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3 d-flex align-center justify-end gap-2">
          <template v-if="(itemSeleccionado._estado === 'PENDIENTE' || itemSeleccionado.estado_aprobacion === 'PENDIENTE')">
            <v-btn color="error" outlined small class="rounded-pill font-weight-bold text-capitalize px-3" :loading="resolviendo" @click="resolverItem(itemSeleccionado, 'RECHAZADO')">
              <v-icon left small>mdi-close</v-icon> Rechazar Solicitud
            </v-btn>
            <v-btn color="success" small class="rounded-pill font-weight-bold text-capitalize px-4 elevation-1" :loading="resolviendo" @click="resolverItem(itemSeleccionado, 'APROBADO')">
              <v-icon left small>mdi-check</v-icon> Aprobar y Autorizar Firma
            </v-btn>
          </template>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogDetalle = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGOS DE CREACIÓN DE TRÁMITES -->
    <!-- DIÁLOGO: NUEVA BOLETA DE SALIDA -->
    <v-dialog v-model="dialogSolicitud" max-width="580" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-file-document-edit</v-icon>
          <span>Emitir Boleta Oficial de Salida</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogSolicitud = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="12">
              <v-autocomplete
                v-model="formSol.id_persona"
                :items="personalList"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario Solicitante *"
                outlined
                dense
                prepend-inner-icon="mdi-account"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formSol.id_permiso"
                :items="permisosList"
                item-text="nombre"
                item-value="id"
                label="Tipo de Permiso *"
                outlined
                dense
                prepend-inner-icon="mdi-format-list-checks"
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formSol.id_justificacion"
                :items="justificacionesList"
                item-text="nombre"
                item-value="id"
                label="Justificación Oficial"
                outlined
                dense
                prepend-inner-icon="mdi-help-circle-outline"
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formSol.fecha_inicio" label="Fecha Inicio *" type="date" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formSol.fecha_fin" label="Fecha Fin *" type="date" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field v-model="formSol.hora_inicio" label="Hora Salida" type="time" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field v-model="formSol.hora_fin" label="Hora Retorno" type="time" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field v-model.number="formSol.horas_solicitadas" label="Horas Solicitadas" type="number" step="0.5" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-textarea v-model="formSol.motivo" label="Motivo y Justificación *" rows="2" outlined dense placeholder="Detalle el motivo del permiso o salida"></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogSolicitud = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill font-weight-bold px-4" :loading="guardandoSol" @click="guardarSolicitud">
            <v-icon left small>mdi-content-save</v-icon> Registrar Boleta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: NUEVA COMISIÓN DE VIAJE -->
    <v-dialog v-model="dialogComision" max-width="560" persistent>
      <v-card rounded="lg">
        <v-card-title class="indigo darken-1 white--text py-3">
          <v-icon left color="white">mdi-airplane-takeoff</v-icon>
          <span>Registrar Comisión Oficial de Viaje</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogComision = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="12">
              <v-autocomplete
                v-model="formCom.id_persona"
                :items="personalList"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario en Comisión *"
                outlined
                dense
                prepend-inner-icon="mdi-account"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="formCom.lugar"
                label="Destino / Lugar de Comisión *"
                outlined
                dense
                placeholder="Ej. Ciudad de La Paz / Planta de Tratamiento"
                prepend-inner-icon="mdi-map-marker"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formCom.fecha_inicio" label="Fecha Salida *" type="date" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formCom.fecha_fin" label="Fecha Retorno *" type="date" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="formCom.monto_viatico"
                label="Monto Viático (Bs.)"
                type="number"
                outlined
                dense
                prepend-inner-icon="mdi-cash"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formCom.transporte"
                :items="['TERRESTRE', 'AEREO', 'MIXTO', 'VEHICULO INSTITUCIONAL']"
                label="Medio de Transporte"
                outlined
                dense
              ></v-select>
            </v-col>

            <v-col cols="12">
              <v-textarea v-model="formCom.motivo" label="Objetivo y Actividades de la Comisión *" rows="2" outlined dense></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogComision = false">Cancelar</v-btn>
          <v-btn color="indigo darken-1" dark class="rounded-pill font-weight-bold px-4" :loading="guardandoCom" @click="guardarComision">
            <v-icon left small>mdi-content-save</v-icon> Registrar Comisión
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: NUEVA OMISIÓN DE MARCACIÓN -->
    <v-dialog v-model="dialogOmision" max-width="520" persistent>
      <v-card rounded="lg">
        <v-card-title class="warning darken-2 white--text py-3">
          <v-icon left color="white">mdi-clock-alert-outline</v-icon>
          <span>Regularizar Omisión de Marcado</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogOmision = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="12">
              <v-autocomplete
                v-model="formOm.id_persona"
                :items="personalList"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario *"
                outlined
                dense
                prepend-inner-icon="mdi-account"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formOm.fecha" label="Fecha de la Omisión *" type="date" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formOm.turno"
                :items="['ENTRADA_MANANA', 'SALIDA_MANANA', 'ENTRADA_TARDE', 'SALIDA_TARDE']"
                label="Turno Omitido *"
                outlined
                dense
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model="formOm.hora" label="Hora Estimada *" type="time" outlined dense></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-textarea v-model="formOm.motivo" label="Causa de la Omisión *" rows="2" outlined dense placeholder="Ej. Olvido involuntario, corte de energía, falla en lector"></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogOmision = false">Cancelar</v-btn>
          <v-btn color="warning darken-2" dark class="rounded-pill font-weight-bold px-4" :loading="guardandoOm" @click="guardarOmision">
            <v-icon left small>mdi-content-save</v-icon> Registrar Omisión
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL VISOR PDF OFICIAL -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :nombre-descarga="nombreDescargaPdf"
      max-width="1000px"
    ></modal-visor-pdf>

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
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'PermisosLicenciasRrhh',
  components: {
    ModalVisorPdf,
  },
  data() {
    const hoy = new Date().toISOString().substr(0, 10);
    return {
      // Visor PDF Oficial
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      nombreDescargaPdf: 'boleta_salida.pdf',

      // Tipo de trámite activo ('TODOS', 'SOLICITUDES', 'COMISIONES', 'OMISIONES', 'FIRMA')
      tipoTramiteActivo: 'TODOS',
      filtroEstado: 'TODOS', // 'TODOS', 'PENDIENTE', 'APROBADO', 'RECHAZADO'
      busquedaTexto: '',

      cargandoGeneral: false,
      resolviendo: false,

      // Listas de datos
      listaBandeja: [],
      listaSolicitudes: [],
      listaComisiones: [],
      listaOmisiones: [],
      personalList: [],
      permisosList: [],
      justificacionesList: [],

      // Elemento activo y diálogo de detalle
      itemSeleccionado: null,
      dialogDetalle: false,

      // Modales de creación
      dialogSolicitud: false,
      dialogComision: false,
      dialogOmision: false,

      guardandoSol: false,
      guardandoCom: false,
      guardandoOm: false,

      formSol: {
        id_persona: null,
        id_permiso: null,
        id_justificacion: null,
        motivo: '',
        fecha_inicio: hoy,
        fecha_fin: hoy,
        hora_inicio: '08:30',
        hora_fin: '10:30',
        horas_solicitadas: 2.0,
      },
      formCom: {
        id_persona: null,
        lugar: '',
        motivo: '',
        fecha_inicio: hoy,
        fecha_fin: hoy,
        monto_viatico: 0,
        transporte: 'TERRESTRE',
      },
      formOm: {
        id_persona: null,
        fecha: hoy,
        turno: 'ENTRADA_MANANA',
        hora: '08:30',
        motivo: '',
      },

      tableHeaders: [
        { text: 'CITE / Trámite', value: 'cite_tipo', sortable: false },
        { text: 'Funcionario', value: 'funcionario', sortable: false },
        { text: 'Fechas y Horario', value: 'fechas', sortable: false },
        { text: 'Detalle / Viático', value: 'detalle', sortable: false },
        { text: 'Motivo / Justificación', value: 'motivo', sortable: false },
        { text: 'Estado', value: 'estado', align: 'center', sortable: false },
        { text: 'Acciones', value: 'acciones', align: 'center', sortable: false },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    iconoModulo() {
      return 'mdi-folder-account-outline';
    },

    tituloModulo() {
      return 'Permisos, Comisiones y Licencias';
    },

    subtituloModulo() {
      return 'Despacho institucional y firma oficial de boletas de salida, comisiones de viaje y omisiones de marcado';
    },

    totalPendientes() {
      return this.listaBandeja.filter(it => (it.estado_aprobacion === 'PENDIENTE' || it._estado === 'PENDIENTE')).length;
    },

    totalGeneral() {
      return this.listaSolicitudes.length + this.listaComisiones.length + this.listaOmisiones.length;
    },

    opcionesTipoTramiteSelect() {
      return [
        { text: `Todas (${this.totalGeneral})`, value: 'TODOS' },
        { text: `Para Mi Firma (${this.totalPendientes})`, value: 'FIRMA' },
        { text: `Boletas de Salida (${this.listaSolicitudes.length})`, value: 'SOLICITUDES' },
        { text: `Comisiones de Viaje (${this.listaComisiones.length})`, value: 'COMISIONES' },
        { text: `Omisiones de Marcación (${this.listaOmisiones.length})`, value: 'OMISIONES' },
      ];
    },

    itemsActuales() {
      switch (this.tipoTramiteActivo) {
        case 'FIRMA':
          return this.listaBandeja;
        case 'SOLICITUDES':
          return this.listaSolicitudes;
        case 'COMISIONES':
          return this.listaComisiones;
        case 'OMISIONES':
          return this.listaOmisiones;
        case 'TODOS':
        default:
          return [...this.listaSolicitudes, ...this.listaComisiones, ...this.listaOmisiones];
      }
    },

    itemsFiltrados() {
      let list = Array.isArray(this.itemsActuales) ? this.itemsActuales : [];

      // Filtro de estado
      if (this.filtroEstado !== 'TODOS') {
        list = list.filter(it => {
          const est = it._estado || it.estado_aprobacion || 'PENDIENTE';
          return est.toUpperCase() === this.filtroEstado.toUpperCase();
        });
      }

      // Filtro de búsqueda
      if (this.busquedaTexto) {
        const q = this.busquedaTexto.toLowerCase();
        list = list.filter(it => {
          const nom = this.getNombreFuncionario(it).toLowerCase();
          const ci = (it.nro_documento || '').toLowerCase();
          const mot = (it.motivo || '').toLowerCase();
          const lug = (it.lugar || '').toLowerCase();
          const cite = (it.cite || '').toLowerCase();
          return nom.includes(q) || ci.includes(q) || mot.includes(q) || lug.includes(q) || cite.includes(q);
        });
      }

      return list;
    },
  },
  watch: {
    '$route.query.tipo': {
      immediate: true,
      handler(tipo) {
        if (tipo) {
          const t = tipo.toUpperCase();
          if (['TODOS', 'FIRMA', 'SOLICITUDES', 'COMISIONES', 'OMISIONES'].includes(t)) {
            this.tipoTramiteActivo = t;
          }
        }
      },
    },
  },
  mounted() {
    this.recargarTodo();
    this.cargarCatalogos();
  },
  methods: {
    recargarTodo() {
      this.cargandoGeneral = true;
      Promise.all([
        this.cargarBandeja(),
        this.cargarSolicitudes(),
        this.cargarComisiones(),
        this.cargarOmisiones(),
      ]).finally(() => {
        this.cargandoGeneral = false;
      });
    },

    cargarCatalogos() {
      axios.get('/api/rrhh/personal?per_page=100').then(res => {
        const raw = res.data?.data || res.data || [];
        this.personalList = (Array.isArray(raw) ? raw : []).map(p => ({
          id: p.id,
          nombre_completo: `${p.nombres || ''} ${p.primer_apellido || ''} ${p.segundo_apellido || ''} (${p.nro_documento || ''})`.trim(),
        }));
      }).catch(() => {});

      axios.get('/api/rrhh/permisos/catalogo').then(res => {
        this.permisosList = Array.isArray(res.data?.permisos) ? res.data.permisos : [];
        this.justificacionesList = Array.isArray(res.data?.justificaciones) ? res.data.justificaciones : [];
      }).catch(() => {});
    },

    cargarBandeja() {
      return axios.get('/api/rrhh/bandeja-aprobaciones')
        .then(res => {
          const raw = res.data?.data || res.data || [];
          this.listaBandeja = Array.isArray(raw) ? raw : [];
        })
        .catch(() => {
          this.listaBandeja = [];
        });
    },

    cargarSolicitudes() {
      return axios.get('/api/rrhh/solicitudes')
        .then(res => {
          const raw = res.data?.data || res.data || [];
          this.listaSolicitudes = Array.isArray(raw) ? raw : [];
        })
        .catch(() => {
          this.listaSolicitudes = [];
        });
    },

    cargarComisiones() {
      return axios.get('/api/rrhh/comisiones')
        .then(res => {
          const raw = res.data?.data || res.data || [];
          this.listaComisiones = Array.isArray(raw) ? raw : [];
        })
        .catch(() => {
          this.listaComisiones = [];
        });
    },

    cargarOmisiones() {
      return axios.get('/api/rrhh/omisiones')
        .then(res => {
          const raw = res.data?.data || res.data || [];
          this.listaOmisiones = Array.isArray(raw) ? raw : [];
        })
        .catch(() => {
          this.listaOmisiones = [];
        });
    },

    verDetalleItem(item) {
      this.itemSeleccionado = item;
      this.dialogDetalle = true;
    },

    resolverItem(item, nuevoEstado) {
      const id = item.solicitud_id || item.id;
      const accion = nuevoEstado === 'APROBADO' ? 'aprobar' : 'rechazar';
      if (!confirm(`¿Desea ${accion} oficialmente este trámite?`)) return;

      this.resolviendo = true;
      axios.put(`/api/rrhh/bandeja-aprobaciones/${id}/resolver`, { estado: nuevoEstado })
        .then(() => {
          this.showSnackbar(`Trámite ${nuevoEstado.toLowerCase()} exitosamente.`);
          if (this.itemSeleccionado && (this.itemSeleccionado.id === id || this.itemSeleccionado.solicitud_id === id)) {
            this.itemSeleccionado._estado = nuevoEstado;
            this.itemSeleccionado.estado_aprobacion = nuevoEstado;
          }
          item._estado = nuevoEstado;
          item.estado_aprobacion = nuevoEstado;
          this.recargarTodo();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al procesar trámite.', 'error');
        })
        .finally(() => {
          this.resolviendo = false;
        });
    },

    abrirModalSolicitud() {
      this.dialogSolicitud = true;
    },

    abrirModalComision() {
      this.dialogComision = true;
    },

    abrirModalOmision() {
      this.dialogOmision = true;
    },

    guardarSolicitud() {
      if (!this.formSol.id_persona || !this.formSol.id_permiso || !this.formSol.motivo) {
        this.showSnackbar('Por favor complete los campos obligatorios.', 'warning');
        return;
      }
      this.guardandoSol = true;
      axios.post('/api/rrhh/solicitudes', this.formSol)
        .then(res => {
          this.showSnackbar('Boleta de salida registrada correctamente.');
          this.dialogSolicitud = false;
          this.recargarTodo();
          if (res.data?.data) {
            this.verDetalleItem(res.data.data);
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al registrar boleta.', 'error');
        })
        .finally(() => {
          this.guardandoSol = false;
        });
    },

    guardarComision() {
      if (!this.formCom.id_persona || !this.formCom.lugar || !this.formCom.motivo) {
        this.showSnackbar('Complete los campos obligatorios de la comisión.', 'warning');
        return;
      }
      this.guardandoCom = true;
      axios.post('/api/rrhh/comisiones', this.formCom)
        .then(res => {
          this.showSnackbar('Comisión de viaje registrada correctamente.');
          this.dialogComision = false;
          this.recargarTodo();
          if (res.data?.data) {
            this.verDetalleItem(res.data.data);
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al registrar comisión.', 'error');
        })
        .finally(() => {
          this.guardandoCom = false;
        });
    },

    guardarOmision() {
      if (!this.formOm.id_persona || !this.formOm.fecha || !this.formOm.turno || !this.formOm.motivo) {
        this.showSnackbar('Complete los datos requeridos para la omisión.', 'warning');
        return;
      }
      this.guardandoOm = true;
      const payload = {
        id_persona: this.formOm.id_persona,
        fecha: this.formOm.fecha,
        turno_periodo: this.formOm.turno,
        hora_marcado_omision: this.formOm.hora,
        motivo: this.formOm.motivo,
      };

      axios.post('/api/rrhh/omisiones', payload)
        .then(res => {
          this.showSnackbar('Omisión registrada exitosamente.');
          this.dialogOmision = false;
          this.recargarTodo();
          if (res.data?.data) {
            this.verDetalleItem(res.data.data);
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al registrar omisión.', 'error');
        })
        .finally(() => {
          this.guardandoOm = false;
        });
    },

    imprimirDocumento(item) {
      if (!item) return;
      const itemId = item.id || item.solicitud_id;
      this.urlVisorPdf = `/api/rrhh/reportes/boleta-salida/${itemId}/pdf`;
      this.tituloVisorPdf = `Boleta Oficial — ${item.cite || 'Salida / Comisión'}`;
      this.subtituloVisorPdf = `${this.getNombreFuncionario(item)} · ${item.permiso_nombre || this.getTituloTipo(item)}`;
      const safeCite = (item.cite || 'BOLETA_SALIDA').replace(/[^A-Za-z0-9_-]/g, '_');
      this.nombreDescargaPdf = `${safeCite}.pdf`;
      this.mostrarVisorPdf = true;
    },

    getNombreFuncionario(item) {
      if (!item) return 'Funcionario';
      if (item.persona && item.persona.nombre_completo) return item.persona.nombre_completo;
      const nom = `${item.nombres || ''} ${item.primer_apellido || ''} ${item.segundo_apellido || ''}`.trim();
      return nom || item.funcionario || 'Funcionario EMAPAP';
    },

    getColorTipo(item) {
      if (!item) return 'primary';
      if (item.lugar || item.tipo_accion === 'COMISION' || (item.permiso_sigla === 'C.O.')) return 'indigo';
      if (item.turno_periodo || item.tipo_accion === 'OMISION' || (item.permiso_sigla === 'OM')) return 'warning darken-2';
      return 'primary';
    },

    getIconoTipo(item) {
      if (!item) return 'mdi-file-document-outline';
      if (item.lugar || item.tipo_accion === 'COMISION' || (item.permiso_sigla === 'C.O.')) return 'mdi-airplane-takeoff';
      if (item.turno_periodo || item.tipo_accion === 'OMISION' || (item.permiso_sigla === 'OM')) return 'mdi-clock-alert-outline';
      return 'mdi-file-document-edit';
    },

    getSiglaTipo(item) {
      if (!item) return 'P.P.';
      if (item.lugar || item.tipo_accion === 'COMISION') return 'C.O.';
      if (item.turno_periodo || item.tipo_accion === 'OMISION') return 'OM';
      return 'P.P.';
    },

    getTituloTipo(item) {
      if (!item) return 'Boleta de Permiso';
      if (item.lugar || item.tipo_accion === 'COMISION') return 'Comisión Oficial de Viaje';
      if (item.turno_periodo || item.tipo_accion === 'OMISION') return 'Omisión de Marcado Biométrico';
      return 'Boleta de Salida y Permiso';
    },

    getEstadoColor(estado) {
      switch (estado) {
        case 'APROBADO': return 'success';
        case 'RECHAZADO': return 'error';
        case 'ANULADO': return 'grey';
        default: return 'warning darken-2';
      }
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
.gap-3 {
  gap: 12px;
}
.bg-light {
  background-color: #f8fafc;
}
.bg-success-light {
  background-color: #ecfdf5 !important;
  border-color: #a7f3d0 !important;
}
.text-slate-800 {
  color: #1e293b;
}
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.border-b {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
.border-t {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}
.border {
  border: 1px solid rgba(0, 0, 0, 0.12);
}
.firma-box {
  background: transparent;
}
.card-kpi-activa {
  border: 2px solid var(--v-primary-base) !important;
}
</style>
