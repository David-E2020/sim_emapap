<template>
  <div class="aportes-instalaciones-container">
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
      <div>
        <h2 class="text-h5 font-weight-bold primary--text mb-1">
          <v-icon color="primary" class="mr-2">mdi-pipe-wrench</v-icon>
          Aportes e Instalaciones de Conexión Domiciliaria
        </h2>
        <div class="text-caption text-secondary">
          Registro, planes de pago diferido y emisión de contratos de Alcantarillado Sanitario y Agua Potable (apagua / alcanta)
        </div>
      </div>
      <div class="d-flex align-center flex-wrap gap-2">
        <v-btn
          color="teal darken-2"
          dark
          class="text-capitalize rounded-pill font-weight-bold mr-2 elevation-1"
          @click="abrirModalNuevo('ALCANTARILLADO')"
        >
          <v-icon left small>mdi-pipe-wrench</v-icon> Registro de Alcantarillado
        </v-btn>
        <v-btn
          color="primary"
          class="text-capitalize rounded-pill font-weight-bold mr-2 elevation-1"
          @click="abrirModalNuevo('AGUA')"
        >
          <v-icon left small>mdi-water-plus</v-icon> Conexión de Agua
        </v-btn>
        <v-btn icon color="primary" :loading="cargando" title="Recargar listado" @click="cargarAportes">
          <v-icon>mdi-refresh</v-icon>
        </v-btn>
      </div>
    </div>

    <!-- TARJETAS KPI RESUMEN -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Total Contratos</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ resumen.total_contratos }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Conexiones Agua</div>
          <div class="text-h4 font-weight-black blue--text text--darken-2 mt-1">{{ resumen.total_agua }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Alcantarillado</div>
          <div class="text-h4 font-weight-black teal--text text--darken-2 mt-1">{{ resumen.total_alcantarillado }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Monto Total Contratado</div>
          <div class="text-h4 font-weight-black success--text mt-1">Bs {{ formatearMonto(resumen.monto_total) }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- BARRA DE FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-4 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- Pestañas de Servicio: Todos / Agua / Alcantarillado -->
        <v-col cols="12" md="4">
          <v-btn-toggle v-model="filtroTipoServicio" mandatory dense color="primary" class="rounded-pill" @change="onCambioFiltro">
            <v-btn value="TODOS" small class="text-capitalize">Todos</v-btn>
            <v-btn value="AGUA" small class="text-capitalize">
              <v-icon x-small left>mdi-water</v-icon> Agua Potable
            </v-btn>
            <v-btn value="ALCANTARILLADO" small class="text-capitalize">
              <v-icon x-small left>mdi-pipe</v-icon> Alcantarillado
            </v-btn>
          </v-btn-toggle>
        </v-col>

        <!-- Selector Tipo de Búsqueda (Idéntico a Caja y Facturación) -->
        <v-col cols="12" sm="5" md="3">
          <v-select
            v-model="tipoBusqueda"
            :items="tiposBusqueda"
            item-text="texto"
            item-value="valor"
            label="Buscar por..."
            prepend-inner-icon="mdi-format-list-bulleted-type"
            dense
            outlined
            hide-details
            @change="onCambioTipoBusqueda"
          ></v-select>
        </v-col>

        <!-- Campo de texto de búsqueda -->
        <v-col cols="12" sm="7" md="4">
          <v-text-field
            v-model="busqueda"
            :placeholder="placeholderBusqueda"
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarAportes"
            @click:clear="onClearBusqueda"
          ></v-text-field>
        </v-col>

        <!-- Filtro Estado Pago -->
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroEstadoPago"
            :items="[
              { text: 'Todos los Pagos', value: 'TODOS' },
              { text: 'Pagados', value: 'PAGADO' },
              { text: 'Pendientes', value: 'PENDIENTE' }
            ]"
            item-text="text"
            item-value="value"
            label="Estado Pago"
            dense
            outlined
            hide-details
            @change="cargarAportes"
          ></v-select>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE CONTRATOS DE APORTES E INSTALACIONES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="columnas"
        :items="aportes"
        :loading="cargando"
        :server-items-length="totalAportes"
        :options.sync="opcionesPaginacion"
        :footer-props="{ 'items-per-page-options': [15, 25, 50, 100] }"
        class="elevation-0"
      >
        <!-- Código Socio -->
        <template v-slot:item.codigo_socio="{ item }">
          <v-chip color="primary" outlined small class="font-weight-bold">
            {{ item.codigo_socio }}
          </v-chip>
        </template>

        <!-- Abonado / Solicitante -->
        <template v-slot:item.nombre_socio="{ item }">
          <div>
            <div class="font-weight-bold text-subtitle-2 grey--text text--darken-3">
              {{ item.nombre_socio }}
            </div>
            <div class="text-caption text-secondary">
              <v-icon x-small left>mdi-map-marker</v-icon>
              {{ item.zona || 'Sin Zona' }}
            </div>
          </div>
        </template>

        <!-- Tipo de Servicio -->
        <template v-slot:item.tipo_servicio="{ item }">
          <v-chip x-small :color="item.tipo_servicio === 'AGUA' ? 'blue darken-2' : 'teal darken-2'" text-color="white" class="font-weight-bold">
            <v-icon x-small left>{{ item.tipo_servicio === 'AGUA' ? 'mdi-water' : 'mdi-pipe' }}</v-icon>
            {{ item.tipo_servicio === 'AGUA' ? 'Agua Potable' : 'Alcantarillado' }}
          </v-chip>
        </template>

        <!-- Periodo -->
        <template v-slot:item.periodo="{ item }">
          <span class="text-caption font-weight-bold">
            {{ item.periodo || '-' }}
          </span>
        </template>

        <!-- Fecha -->
        <template v-slot:item.fecha="{ item }">
          <span class="text-caption font-weight-medium">
            {{ item.fecha ? item.fecha.slice(0, 10) : '-' }}
          </span>
        </template>

        <!-- Total Contrato -->
        <template v-slot:item.total_contrato="{ item }">
          <div class="text-right">
            <span class="font-weight-bold grey--text text--darken-3">
              Bs {{ calcularTotalContrato(item) }}
            </span>
            <div v-if="item.tipo_servicio === 'AGUA' && (parseFloat(item.aporte) + parseFloat(item.instalacion)) > 0" class="text-caption text-secondary">
              Ap. {{ parseFloat(item.aporte).toFixed(0) }} + Inst. {{ parseFloat(item.instalacion).toFixed(0) }}
            </div>
          </div>
        </template>

        <!-- Monto Cuota -->
        <template v-slot:item.total="{ item }">
          <div class="text-right">
            <span class="font-weight-bold primary--text">
              Bs {{ parseFloat(item.total).toFixed(2) }}
            </span>
            <div v-if="item.orden" class="text-caption grey--text text--darken-1 font-italic">
              {{ item.orden }}
            </div>
            <div v-else-if="item.plazo > 1" class="text-caption grey--text text--darken-1 font-italic">
              Cuota de {{ item.plazo }} m.
            </div>
          </div>
        </template>

        <!-- Plazo -->
        <template v-slot:item.plazo="{ item }">
          <div class="text-center font-weight-medium">
            {{ item.plazo }} mes(es)
          </div>
        </template>

        <!-- Estado Pago -->
        <template v-slot:item.pagado="{ item }">
          <v-chip x-small :color="item.pagado ? 'success' : 'warning'" text-color="white" class="font-weight-medium">
            {{ item.pagado ? 'PAGADO' : 'PENDIENTE' }}
          </v-chip>
        </template>

        <!-- Factura / Recibo -->
        <template v-slot:item.factura="{ item }">
          <v-chip
            v-if="item.factura"
            small
            outlined
            color="primary"
            class="font-weight-bold cursor-pointer"
            title="Ver Factura / Recibo de Pago"
            @click="verFactura(item)"
          >
            <v-icon x-small left color="primary">mdi-receipt-text-outline</v-icon>
            #{{ item.factura }}
          </v-chip>
          <span v-else class="grey--text">-</span>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <v-btn
              small
              color="primary"
              outlined
              class="text-capitalize rounded-pill font-weight-bold"
              title="Reimprimir Contrato de Pago Diferido (apagua.frx / alcanta.frx)"
              @click="reimprimirContrato(item)"
            >
              <v-icon small left>mdi-file-document-outline</v-icon> Contrato PDF
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO NATIVO: REGISTRO / INSTALACIÓN DE SERVICIOS (ALCANTARILLADO Y AGUA) -->
    <v-dialog v-model="modalNuevo" max-width="850" persistent>
      <v-card rounded="lg" class="overflow-hidden">
        <!-- BARRA DE TÍTULO -->
        <v-card-title
          :class="formContrato.tipo_servicio === 'ALCANTARILLADO' ? 'teal darken-2' : 'primary'"
          class="white--text py-3 d-flex justify-space-between align-center"
        >
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">
              {{ formContrato.tipo_servicio === 'ALCANTARILLADO' ? 'mdi-pipe-wrench' : 'mdi-water-plus' }}
            </v-icon>
            <span class="text-subtitle-1 font-weight-bold">
              {{ formContrato.tipo_servicio === 'ALCANTARILLADO' ? 'Registro / Instalación de Alcantarillado' : 'Registro de Conexión de Agua Potable' }}
            </span>
          </div>
          <v-btn icon color="white" small @click="modalNuevo = false">
            <v-icon small>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4 px-5">
          <v-form ref="formNuevoRef" v-model="formValido">
            <!-- 1. TIPO DE SERVICIO (ALCANTARILLADO / AGUA) -->
            <div class="d-flex align-center justify-space-between flex-wrap mb-4 pb-2 border-bottom">
              <span class="text-subtitle-2 font-weight-bold grey--text text--darken-3">
                Tipo de Trámite:
              </span>
              <v-btn-toggle
                v-model="formContrato.tipo_servicio"
                mandatory
                dense
                color="primary"
                class="rounded-pill"
                @change="onTipoServicioChange"
              >
                <v-btn value="ALCANTARILLADO" small class="text-capitalize px-4 font-weight-bold">
                  <v-icon x-small left color="teal">mdi-pipe</v-icon> Instalación Alcantarillado
                </v-btn>
                <v-btn value="AGUA" small class="text-capitalize px-4 font-weight-bold">
                  <v-icon x-small left color="blue">mdi-water</v-icon> Conexión Agua Potable
                </v-btn>
              </v-btn-toggle>
            </div>

            <!-- 2. BÚSQUEDA DEL SOCIO / ABONADO -->
            <v-row dense class="mb-2">
              <v-col cols="12" sm="5">
                <v-text-field
                  v-model="formContrato.codigo_socio"
                  label="Código de Socio *"
                  placeholder="Ej: 00038"
                  dense
                  outlined
                  required
                  prepend-inner-icon="mdi-card-account-details-outline"
                  append-outer-icon="mdi-magnify"
                  :loading="buscandoSocio"
                  @click:append-outer="buscarDatosSocio"
                  @keyup.enter="buscarDatosSocio"
                  @blur="buscarDatosSocio"
                  hint="Ingrese el código y presione Enter o la lupa"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="7" class="d-flex align-center">
                <v-alert
                  v-if="errorBusqueda"
                  dense
                  outlined
                  type="warning"
                  class="mb-0 py-1 text-caption w-100"
                >
                  {{ errorBusqueda }}
                </v-alert>
                <div v-else-if="!socioEncontrado" class="text-caption text-secondary font-italic">
                  Ingrese el código del socio registrado en EMAPA para cargar automáticamente su expediente.
                </div>
              </v-col>
            </v-row>

            <!-- FICHA DETALLADA DEL SOCIO (IDÉNTICA A LA CABECERA DE FOXPRO) -->
            <v-card v-if="socioEncontrado" outlined rounded="lg" class="pa-3 mb-4 blue-grey lighten-5">
              <v-row dense>
                <v-col cols="12" sm="6">
                  <div class="text-caption text-secondary text-uppercase font-weight-bold">Nombre o Razón Social</div>
                  <div class="text-subtitle-2 font-weight-bold primary--text">
                    {{ socioEncontrado.nombre_completo || '-' }}
                  </div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary text-uppercase font-weight-bold">NIT / C.I.</div>
                  <div class="text-body-2 font-weight-medium">
                    {{ socioEncontrado.numero_documento || 'S/N' }}
                  </div>
                </v-col>
                <v-col cols="6" sm="3">
                  <div class="text-caption text-secondary text-uppercase font-weight-bold">Categoría</div>
                  <div class="text-body-2 font-weight-medium">
                    {{ formContrato.categoria || 'DOMICILIARIA' }}
                  </div>
                </v-col>
                <v-col cols="12" sm="6">
                  <div class="text-caption text-secondary text-uppercase font-weight-bold">Zona</div>
                  <div class="text-body-2 font-weight-medium">
                    {{ formContrato.zona || '-' }}
                  </div>
                </v-col>
                <v-col cols="12" sm="6">
                  <div class="text-caption text-secondary text-uppercase font-weight-bold">Dirección / Calle</div>
                  <div class="text-body-2 font-weight-medium">
                    {{ formContrato.direccion || '-' }}
                  </div>
                </v-col>
                <v-col cols="12" class="mt-1" v-if="formContrato.tipo_servicio === 'ALCANTARILLADO'">
                  <v-chip x-small :color="socioEncontrado.tiene_alcantarillado ? 'warning' : 'success'" text-color="white" class="font-weight-bold">
                    <v-icon x-small left>{{ socioEncontrado.tiene_alcantarillado ? 'mdi-alert' : 'mdi-plus-circle' }}</v-icon>
                    {{ socioEncontrado.tiene_alcantarillado ? 'Registrado con Alcantarillado previamente en el Padrón' : 'Nueva Conexión / Acometida de Alcantarillado' }}
                  </v-chip>
                </v-col>
              </v-row>
            </v-card>

            <!-- AVISO DE CAMBIO DE TITULARIDAD O ANTECEDENTE HISTÓRICO -->
            <v-alert
              v-if="avisoHistoricoNombre"
              dense
              text
              type="info"
              icon="mdi-account-switch"
              class="mb-3 text-caption rounded-lg"
            >
              <strong>Antecedente Histórico Encontrado:</strong>
              Este código registra contratos de gestiones anteriores a nombre de <b>{{ avisoHistoricoNombre }}</b>.
              El nuevo contrato se registrará a nombre del titular actual del Padrón: <b>{{ formContrato.nombre_socio }}</b>.
            </v-alert>

            <!-- 3. CONDICIONES COMERCIALES Y PLAZO DE FINANCIAMIENTO -->
            <v-row dense class="mb-2">
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formContrato.fecha"
                  label="Fecha de Suscripción *"
                  type="date"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model.number="formContrato.instalacion"
                  label="Instalación (Bs) *"
                  type="number"
                  step="0.01"
                  dense
                  outlined
                  required
                  prefix="Bs"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4" v-if="formContrato.tipo_servicio === 'AGUA'">
                <v-text-field
                  v-model.number="formContrato.aporte"
                  label="Aporte Institucional (Bs)"
                  type="number"
                  step="0.01"
                  dense
                  outlined
                  prefix="Bs"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-select
                  v-model.number="formContrato.plazo"
                  :items="listaPlazos"
                  item-text="text"
                  item-value="value"
                  label="Plazo (Meses) *"
                  dense
                  outlined
                  required
                  prepend-inner-icon="mdi-calendar-month"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model.number="formContrato.abono"
                  label="Cuota Inicial / Contado (Bs)"
                  type="number"
                  step="0.01"
                  dense
                  outlined
                  prefix="Bs"
                  hint="Anticipo pagado al suscribir (opcional)"
                  persistent-hint
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- 4. GRILLA DINÁMICA DE CUOTAS (PLAN DE PAGOS DE FOXPRO) -->
            <v-card outlined rounded="lg" class="pa-0 overflow-hidden mb-3">
              <div class="px-3 py-2 grey lighten-3 d-flex justify-space-between align-center">
                <span class="text-caption font-weight-bold text-uppercase grey--text text--darken-3">
                  <v-icon x-small color="grey darken-3" left>mdi-calendar-clock</v-icon>
                  Plan de Pagos Programados / Calendario de Cuotas ({{ cuotasCalculadas.length }} Cuotas)
                </span>
                <span class="text-caption font-weight-bold teal--text text--darken-3">
                  {{ formContrato.tipo_servicio === 'ALCANTARILLADO' ? 'ALCANTARILLADO' : 'AGUA POTABLE' }}
                </span>
              </div>

              <v-simple-table dense class="tabla-cuotas-foxpro">
                <template v-slot:default>
                  <thead>
                    <tr class="grey lighten-4">
                      <th class="text-center font-weight-bold" style="width: 60px;">N°</th>
                      <th class="text-left font-weight-bold">Concepto</th>
                      <th class="text-center font-weight-bold">Periodo</th>
                      <th class="text-center font-weight-bold">Fecha Prog.</th>
                      <th class="text-right font-weight-bold">Total (Bs)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in cuotasCalculadas" :key="c.numero">
                      <td class="text-center text-caption font-weight-bold">{{ c.numero }}</td>
                      <td class="text-left text-caption">
                        <v-chip x-small v-if="c.tipo === 'ANTICIPO'" color="success" text-color="white" class="font-weight-bold mr-1">
                          Anticipo Contado
                        </v-chip>
                        <span v-else class="grey--text text--darken-2">{{ c.concepto }}</span>
                      </td>
                      <td class="text-center text-caption font-weight-medium">{{ c.periodo }}</td>
                      <td class="text-center text-caption">{{ c.fecha_programada }}</td>
                      <td class="text-right font-weight-bold primary--text">
                        Bs {{ c.monto.toFixed(2) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="blue-grey lighten-5 font-weight-bold">
                      <td colspan="4" class="text-right py-2 text-subtitle-2 font-weight-bold">
                        Total Contrato Bs:
                      </td>
                      <td class="text-right py-2 text-subtitle-1 success--text font-weight-black">
                        Bs {{ totalCalculado.toFixed(2) }}
                      </td>
                    </tr>
                  </tfoot>
                </template>
              </v-simple-table>
            </v-card>

            <!-- 5. OBSERVACIONES -->
            <v-textarea
              v-model="formContrato.observaciones"
              label="Observaciones del Trámite (Opcional)"
              placeholder="Ej: Acometida aprobada en inspección técnica, diferida en cuotas mensuales."
              rows="2"
              dense
              outlined
              hide-details
            ></v-textarea>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <!-- BOTONES DE ACCIÓN (REGISTRAR / OTRO / SALIR) -->
        <v-card-actions class="pa-3 px-5 d-flex justify-space-between align-center flex-wrap gap-2">
          <!-- OTRO (FOXPRO) -->
          <v-btn
            outlined
            color="secondary"
            class="text-capitalize rounded-pill font-weight-medium"
            @click="limpiarParaOtro"
          >
            <v-icon left small>mdi-autorenew</v-icon> Otro
          </v-btn>

          <div class="d-flex align-center gap-2">
            <!-- SALIR (FOXPRO) -->
            <v-btn text color="grey darken-1" class="text-capitalize" @click="modalNuevo = false">
              <v-icon left small>mdi-close</v-icon> Salir
            </v-btn>

            <!-- REGISTRAR (FOXPRO) -->
            <v-btn
              :color="formContrato.tipo_servicio === 'ALCANTARILLADO' ? 'teal darken-2' : 'primary'"
              dark
              :loading="guardando"
              class="font-weight-bold text-capitalize rounded-pill px-5"
              @click="guardarNuevoContrato"
            >
              <v-icon left small>mdi-content-save-check</v-icon>
              Registrar
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE PDF (CONTRATO O FACTURA / RECIBO) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :mostrar-selector-formato="esFacturaVisor"
      :formato-inicial="formatoVisor"
      @cambio-formato="alCambiarFormatoVisor"
    ></modal-visor-pdf>

    <!-- SNACKBAR DE NOTIFICACIÓN -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3500" top right>
      <v-icon left small color="white">{{ snackbar.icon }}</v-icon>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'AportesInstalaciones',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      cargando: false,
      guardando: false,
      buscandoSocio: false,
      errorBusqueda: '',
      avisoHistoricoNombre: '',
      socioEncontrado: null,
      busqueda: '',
      tipoBusqueda: 'codigo_abonado',
      tiposBusqueda: [
        { valor: 'codigo_abonado', texto: 'Código Abonado' },
        { valor: 'carnet_nit', texto: 'C.I. / NIT' },
        { valor: 'cliente', texto: 'Nombres / Apellidos' },
        { valor: 'numero_factura', texto: 'N° de Factura' },
        { valor: 'todos', texto: 'Todos los campos' },
      ],
      filtroTipoServicio: 'TODOS',
      filtroEstadoPago: 'TODOS',
      aportes: [],
      totalAportes: 0,
      opcionesPaginacion: {},
      resumen: {
        total_contratos: 0,
        total_agua: 0,
        total_alcantarillado: 0,
        monto_total: 0,
        total_pagados: 0,
        total_pendientes: 0,
      },
      columnas: [
        { text: 'Código', value: 'codigo_socio', width: '90px' },
        { text: 'Abonado / Solicitante', value: 'nombre_socio' },
        { text: 'Servicio', value: 'tipo_servicio', width: '130px' },
        { text: 'Periodo', value: 'periodo', width: '90px' },
        { text: 'Fecha', value: 'fecha', width: '100px' },
        { text: 'Total Contrato', value: 'total_contrato', align: 'end', width: '130px' },
        { text: 'Monto Cuota', value: 'total', align: 'end', width: '120px' },
        { text: 'Plazo', value: 'plazo', align: 'center', width: '85px' },
        { text: 'Estado Pago', value: 'pagado', align: 'center', width: '100px' },
        { text: 'Factura', value: 'factura', align: 'center', width: '90px' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '130px' },
      ],
      listaPlazos: [
        { text: '1 Mes (Contado / Cuota Única)', value: 1 },
        { text: '2 Meses', value: 2 },
        { text: '3 Meses', value: 3 },
        { text: '4 Meses (Estándar FoxPro)', value: 4 },
        { text: '5 Meses', value: 5 },
        { text: '6 Meses', value: 6 },
        { text: '7 Meses', value: 7 },
        { text: '8 Meses', value: 8 },
        { text: '9 Meses', value: 9 },
        { text: '10 Meses (Máximo FoxPro)', value: 10 },
        { text: '12 Meses (1 Año)', value: 12 },
      ],
      modalNuevo: false,
      formValido: true,
      formContrato: {
        codigo_socio: '',
        nombre_socio: '',
        zona: '',
        direccion: '',
        numero_documento: '',
        categoria: '',
        tipo_servicio: 'ALCANTARILLADO',
        fecha: new Date().toISOString().slice(0, 10),
        plazo: 4,
        aporte: 0.00,
        instalacion: 600.00,
        abono: 0.00,
        observaciones: '',
      },
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      esFacturaVisor: false,
      formatoVisor: 'rollo',
      itemVisorActual: null,
      snackbar: {
        status: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle',
      },
    };
  },
  computed: {
    placeholderBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado':
          return 'Buscar por Código de Abonado (ej. 04000)...';
        case 'carnet_nit':
          return 'Buscar por C.I. o NIT...';
        case 'cliente':
          return 'Buscar por Nombre o Apellidos...';
        case 'numero_factura':
          return 'Buscar por N° de Factura u Orden...';
        default:
          return 'Buscar por Código, CI/NIT, Nombre o N° Factura...';
      }
    },
    totalCalculado() {
      const inst = parseFloat(this.formContrato.instalacion) || 0;
      const ap = parseFloat(this.formContrato.aporte) || 0;
      return Math.max(0, inst + ap);
    },
    cuotasCalculadas() {
      const plazo = parseInt(this.formContrato.plazo) || 1;
      const total = this.totalCalculado;
      const abono = parseFloat(this.formContrato.abono) || 0;
      const saldoFinanciado = Math.max(0, total - abono);

      const fechaStr = this.formContrato.fecha || new Date().toISOString().slice(0, 10);
      const partes = fechaStr.split('-');
      const year = parseInt(partes[0]) || new Date().getFullYear();
      const month = parseInt(partes[1]) || (new Date().getMonth() + 1);
      const day = parseInt(partes[2]) || new Date().getDate();

      const baseDate = new Date(year, month - 1, day);
      const diaSuscripcion = String(day).padStart(2, '0');
      const mesSuscripcion = String(month).padStart(2, '0');
      const fechaSuscripcionFormateada = `${diaSuscripcion}/${mesSuscripcion}/${year}`;

      const cuotas = [];

      if (total <= 0) {
        return [];
      }

      // Caso A: Con Anticipo / Cuota Inicial al Contado
      if (abono > 0) {
        cuotas.push({
          numero: 1,
          tipo: 'ANTICIPO',
          concepto: 'Anticipo al Contado',
          periodo: `${mesSuscripcion}/${year}`,
          fecha_programada: fechaSuscripcionFormateada,
          monto: abono,
        });

        const cuotasRestantes = Math.max(1, plazo - 1);
        const montoBase = Math.floor((saldoFinanciado / cuotasRestantes) * 100) / 100;
        let acumulado = 0;

        for (let i = 0; i < cuotasRestantes; i++) {
          const pDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + i, 1);
          const progDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + i + 1, baseDate.getDate());
          const pMonth = String(pDate.getMonth() + 1).padStart(2, '0');
          const pYear = pDate.getFullYear();
          const prDay = String(progDate.getDate()).padStart(2, '0');
          const prMonth = String(progDate.getMonth() + 1).padStart(2, '0');
          const prYear = progDate.getFullYear();

          const cuotaMonto = (i === cuotasRestantes - 1)
            ? Math.round((saldoFinanciado - acumulado) * 100) / 100
            : montoBase;
          acumulado += cuotaMonto;

          cuotas.push({
            numero: i + 2,
            tipo: 'DIFERIDO',
            concepto: `Cuota diferida ${i + 1}/${cuotasRestantes}`,
            periodo: `${pMonth}/${pYear}`,
            fecha_programada: `${prDay}/${prMonth}/${prYear}`,
            monto: cuotaMonto,
          });
        }
        return cuotas;
      }

      // Caso B: Sin Anticipo (Diferido 100% en cuotas iguales)
      const montoBase = Math.floor((total / plazo) * 100) / 100;
      let acumulado = 0;

      for (let i = 0; i < plazo; i++) {
        const pDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + i, 1);
        const progDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + i + 1, baseDate.getDate());
        const pMonth = String(pDate.getMonth() + 1).padStart(2, '0');
        const pYear = pDate.getFullYear();
        const prDay = String(progDate.getDate()).padStart(2, '0');
        const prMonth = String(progDate.getMonth() + 1).padStart(2, '0');
        const prYear = progDate.getFullYear();

        const cuotaMonto = (i === plazo - 1)
          ? Math.round((total - acumulado) * 100) / 100
          : montoBase;
        acumulado += cuotaMonto;

        cuotas.push({
          numero: i + 1,
          tipo: 'DIFERIDO',
          concepto: `Cuota diferida ${i + 1}/${plazo}`,
          periodo: `${pMonth}/${pYear}`,
          fecha_programada: `${prDay}/${prMonth}/${prYear}`,
          monto: cuotaMonto,
        });
      }

      return cuotas;
    },
  },
  watch: {
    opcionesPaginacion: {
      handler() {
        this.cargarAportes();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarAportes();
  },
  methods: {
    formatearMonto(val) {
      return parseFloat(val || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    mostrarSnackbar(text, color = 'success', icon = 'mdi-check-circle') {
      this.snackbar = { status: true, text, color, icon };
    },
    onCambioFiltro() {
      this.opcionesPaginacion.page = 1;
      this.cargarAportes();
    },
    onCambioTipoBusqueda() {
      if (this.busqueda) {
        this.opcionesPaginacion.page = 1;
        this.cargarAportes();
      }
    },
    onClearBusqueda() {
      this.busqueda = '';
      this.cargarAportes();
    },
    calcularTotalContrato(item) {
      if (!item) return '0.00';
      const ap = parseFloat(item.aporte) || 0;
      const inst = parseFloat(item.instalacion) || 0;
      const tot = parseFloat(item.total) || 0;
      if (ap + inst > 0) {
        return (ap + inst).toFixed(2);
      }
      return tot.toFixed(2);
    },
    async cargarAportes() {
      this.cargando = true;
      const { page, itemsPerPage } = this.opcionesPaginacion;
      try {
        const res = await axios.get('/api/comercial/aportes', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            tipo_busqueda: this.tipoBusqueda,
            search: this.busqueda || undefined,
            tipo_servicio: this.filtroTipoServicio !== 'TODOS' ? this.filtroTipoServicio : undefined,
            estado_pago: this.filtroEstadoPago !== 'TODOS' ? this.filtroEstadoPago : undefined,
          },
        });

        this.aportes = res.data.data || [];
        this.totalAportes = res.data.total || 0;
        if (res.data.resumen) {
          this.resumen = res.data.resumen;
        }
      } catch (e) {
        console.error('Error cargando aportes:', e);
      } finally {
        this.cargando = false;
      }
    },
    reimprimirContrato(item) {
      if (!item) return;
      this.esFacturaVisor = false;
      this.itemVisorActual = item;
      const esAgua = (item.tipo_servicio || 'AGUA').toUpperCase() === 'AGUA';
      this.urlVisorPdf = `/api/comercial/aportes/${item.id}/contrato-pdf`;
      this.tituloVisorPdf = esAgua
        ? `Contrato de Pago Diferido de Conexión Domiciliaria - Socio #${item.codigo_socio}`
        : `Cronograma de Pagos / Instalación Alcantarillado - Socio #${item.codigo_socio}`;
      const tot = this.calcularTotalContrato(item);
      this.subtituloVisorPdf = `${item.nombre_socio || ''} | Total Contrato: Bs ${tot}`;
      this.mostrarVisorPdf = true;
    },
    verFactura(item) {
      if (!item || !item.factura) return;
      this.itemVisorActual = item;
      this.esFacturaVisor = true;
      this.formatoVisor = 'rollo';
      this.urlVisorPdf = `/api/comercial/aportes/${item.id}/factura-pdf?formato=rollo`;
      this.tituloVisorPdf = `Factura / Recibo Oficial N° ${item.factura}`;
      this.subtituloVisorPdf = `Abonado: [${item.codigo_socio}] ${item.nombre_socio || ''} | Periodo: ${item.periodo || '-'} | Total: Bs ${parseFloat(item.total).toFixed(2)}`;
      this.mostrarVisorPdf = true;
    },
    alCambiarFormatoVisor(nuevoFormato) {
      this.formatoVisor = nuevoFormato;
      if (this.itemVisorActual && this.esFacturaVisor) {
        this.urlVisorPdf = `/api/comercial/aportes/${this.itemVisorActual.id}/factura-pdf?formato=${nuevoFormato}&_t=${Date.now()}`;
      }
    },
    abrirModalNuevo(tipo = 'ALCANTARILLADO') {
      const esAlcantarillado = tipo === 'ALCANTARILLADO';
      this.socioEncontrado = null;
      this.errorBusqueda = '';
      this.avisoHistoricoNombre = '';
      this.formContrato = {
        codigo_socio: '',
        nombre_socio: '',
        zona: '',
        direccion: '',
        numero_documento: '',
        categoria: '',
        tipo_servicio: tipo,
        fecha: new Date().toISOString().slice(0, 10),
        plazo: 4,
        aporte: esAlcantarillado ? 0.00 : 294.90,
        instalacion: esAlcantarillado ? 600.00 : 1592.10,
        abono: 0.00,
        observaciones: '',
      };
      this.modalNuevo = true;
    },
    onTipoServicioChange() {
      const esAlc = this.formContrato.tipo_servicio === 'ALCANTARILLADO';
      if (esAlc) {
        this.formContrato.aporte = 0.00;
        if (this.formContrato.instalacion === 1592.10 || !this.formContrato.instalacion) {
          this.formContrato.instalacion = 600.00;
        }
      } else {
        if (!this.formContrato.aporte) {
          this.formContrato.aporte = 294.90;
        }
        if (this.formContrato.instalacion === 600.00 || !this.formContrato.instalacion) {
          this.formContrato.instalacion = 1592.10;
        }
      }
    },
    limpiarParaOtro() {
      const tipoActual = this.formContrato.tipo_servicio;
      this.abrirModalNuevo(tipoActual);
    },
    async buscarDatosSocio() {
      const cod = (this.formContrato.codigo_socio || '').trim();
      if (!cod) return;
      this.buscandoSocio = true;
      this.errorBusqueda = '';
      this.avisoHistoricoNombre = '';
      try {
        // Consultar por endpoint específico de aportes con historial
        const res = await axios.get(`/api/comercial/aportes/abonado/${cod}`);
        if (res.data?.success && res.data?.abonado) {
          const ab = res.data.abonado;
          this.asignarDatosSocio(ab);
          if (ab.nombre_historico_diferente) {
            this.avisoHistoricoNombre = ab.nombre_historico_diferente;
          }
        } else {
          // Fallback a /api/comercial/abonados si no tiene contratos
          const resFallback = await axios.get('/api/comercial/abonados', {
            params: { search: cod, per_page: 5 }
          });
          const lista = resFallback.data?.data || [];
          if (lista.length > 0) {
            const exacto = lista.find(x => x.codigo === cod || x.codigo === cod.padStart(5, '0')) || lista[0];
            this.asignarDatosSocio(exacto);
          } else {
            this.socioEncontrado = null;
            this.errorBusqueda = `No se encontró ningún socio registrado con el código "${cod}".`;
          }
        }
      } catch (e) {
        try {
          const resFallback = await axios.get('/api/comercial/abonados', {
            params: { search: cod, per_page: 5 }
          });
          const lista = resFallback.data?.data || [];
          if (lista.length > 0) {
            const exacto = lista.find(x => x.codigo === cod || x.codigo === cod.padStart(5, '0')) || lista[0];
            this.asignarDatosSocio(exacto);
          } else {
            this.socioEncontrado = null;
            this.errorBusqueda = `No se encontró ningún socio registrado con el código "${cod}".`;
          }
        } catch (err) {
          this.errorBusqueda = 'Error al consultar la ficha del abonado.';
        }
      } finally {
        this.buscandoSocio = false;
      }
    },
    asignarDatosSocio(ab) {
      this.socioEncontrado = ab;
      this.formContrato.codigo_socio = ab.codigo;
      this.formContrato.nombre_socio = ab.nombre_completo;
      this.formContrato.zona = ab.zona?.nombre || ab.zona || '';
      this.formContrato.direccion = (ab.calle?.nombre || ab.calle || '') + (ab.numero_vivienda ? ' #' + ab.numero_vivienda : '');
      this.formContrato.numero_documento = ab.numero_documento || '';
      this.formContrato.categoria = ab.categoria?.nombre || ab.categoria || 'DOMICILIARIA';
      this.errorBusqueda = '';
    },
    async guardarNuevoContrato() {
      if (!this.formContrato.codigo_socio || !this.formContrato.nombre_socio) {
        this.mostrarSnackbar('Por favor ingrese el Código del Socio y asegúrese de que esté registrado.', 'error', 'mdi-alert');
        return;
      }

      if (this.totalCalculado <= 0) {
        this.mostrarSnackbar('El costo de instalación o aporte debe ser mayor a 0.', 'warning', 'mdi-alert-circle');
        return;
      }

      this.guardando = true;
      try {
        const payload = {
          ...this.formContrato,
          cuotas: this.cuotasCalculadas,
        };
        const res = await axios.post('/api/comercial/aportes', payload);
        this.modalNuevo = false;
        this.mostrarSnackbar(res.data.message || 'Contrato registrado exitosamente', 'success');
        this.cargarAportes();

        // Abrir inmediatamente el contrato para impresión / firma
        if (res.data.data && res.data.data.id) {
          this.reimprimirContrato(res.data.data);
        }
      } catch (e) {
        this.mostrarSnackbar(e.response?.data?.message || 'Error al registrar el contrato de conexión.', 'error', 'mdi-alert-circle');
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.tabla-cuotas-foxpro {
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.border-bottom {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
