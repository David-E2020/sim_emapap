<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-book-multiple-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Comprobantes de Contabilidad (CI - CE - CD)</h2>
            <span class="text-caption text-secondary">
              Registro y control de asientos contables con estricto principio de partida doble y firma SAFCO
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="indigo darken-2"
            dark
            class="rounded-pill font-weight-medium elevation-1"
            @click="abrirModalNuevoComprobante"
          >
            <v-icon left>mdi-plus-circle-outline</v-icon> Nuevo Comprobante
          </v-btn>
          <v-btn
            icon
            color="primary"
            class="ml-2"
            :loading="cargando"
            @click="cargarComprobantes"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE RESUMEN -->
    <v-row class="mb-4" dense>
      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-primary">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">COMPROBANTES TOTALES</div>
              <div class="text-h5 font-weight-bold indigo--text text--darken-3">{{ comprobantes.length }}</div>
            </div>
            <v-avatar color="indigo lighten-5" size="40">
              <v-icon color="indigo darken-2">mdi-receipt-text-outline</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-success">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">INGRESOS (CI)</div>
              <div class="text-h5 font-weight-bold green--text text--darken-2">Bs. {{ formatearNumero(totalIngresos) }}</div>
            </div>
            <v-avatar color="green lighten-5" size="40">
              <v-icon color="green darken-2">mdi-cash-plus</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-danger">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">EGRESOS (CE)</div>
              <div class="text-h5 font-weight-bold red--text text--darken-2">Bs. {{ formatearNumero(totalEgresos) }}</div>
            </div>
            <v-avatar color="red lighten-5" size="40">
              <v-icon color="red darken-2">mdi-cash-minus</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-info">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">DIARIOS / TRASPASOS (CD)</div>
              <div class="text-h5 font-weight-bold blue--text text--darken-2">Bs. {{ formatearNumero(totalDiarios) }}</div>
            </div>
            <v-avatar color="blue lighten-5" size="40">
              <v-icon color="blue darken-2">mdi-swap-horizontal</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- BARRA DE FILTROS -->
    <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- BUSCADOR -->
        <v-col cols="12" sm="4" md="3">
          <v-text-field
            v-model="busqueda"
            label="Buscar por N°, glosa, beneficiario..."
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
          ></v-text-field>
        </v-col>

        <!-- TIPO DE COMPROBANTE -->
        <v-col cols="12" sm="4" md="2">
          <v-select
            v-model="filtroTipo"
            :items="tiposComprobanteOpciones"
            label="Tipo Comprobante"
            outlined
            dense
            hide-details
            clearable
            @change="cargarComprobantes"
          ></v-select>
        </v-col>

        <!-- ESTADO -->
        <v-col cols="12" sm="4" md="2">
          <v-select
            v-model="filtroEstado"
            :items="estadosOpciones"
            label="Estado"
            outlined
            dense
            hide-details
            clearable
            @change="cargarComprobantes"
          ></v-select>
        </v-col>

        <!-- FECHA INICIO -->
        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtroFechaInicio"
            label="Fecha Desde"
            type="date"
            outlined
            dense
            hide-details
            @change="cargarComprobantes"
          ></v-text-field>
        </v-col>

        <!-- FECHA FIN -->
        <v-col cols="12" sm="6" md="2">
          <v-text-field
            v-model="filtroFechaFin"
            label="Fecha Hasta"
            type="date"
            outlined
            dense
            hide-details
            @change="cargarComprobantes"
          ></v-text-field>
        </v-col>

        <!-- BOTÓN APLICAR -->
        <v-col cols="12" sm="12" md="1" class="text-right">
          <v-btn
            color="indigo darken-2"
            dark
            block
            dense
            @click="cargarComprobantes"
            :loading="cargando"
          >
            Filtrar
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- LISTADO DE COMPROBANTES -->
    <v-card rounded="lg" class="elevation-1">
      <v-data-table
        :headers="headers"
        :items="comprobantesFiltrados"
        :loading="cargando"
        :items-per-page="15"
        class="elevation-0"
        no-data-text="No se encontraron comprobantes para el criterio especificado"
        loading-text="Cargando comprobantes..."
      >
        <!-- NRO COMPROBANTE -->
        <template v-slot:item.nro_comprobante="{ item }">
          <span class="font-weight-bold indigo--text text--darken-3">
            <code>{{ item.nro_comprobante }}</code>
          </span>
        </template>

        <!-- TIPO -->
        <template v-slot:item.tipo="{ item }">
          <v-chip
            x-small
            label
            :color="colorTipo(item.tipo)"
            text-color="white"
            class="font-weight-bold"
          >
            {{ etiquetaTipo(item.tipo) }}
          </v-chip>
        </template>

        <!-- FECHA -->
        <template v-slot:item.fecha="{ item }">
          <span>{{ item.fecha }}</span>
        </template>

        <!-- BENEFICIARIO / GLOSA -->
        <template v-slot:item.glosa="{ item }">
          <div>
            <div class="font-weight-medium text-body-2">{{ item.beneficiario || 'S/B' }}</div>
            <div class="text-caption text-secondary text-truncate" style="max-width: 320px;">
              {{ item.glosa }}
            </div>
          </div>
        </template>

        <!-- TOTAL DEBE -->
        <template v-slot:item.total_debe="{ item }">
          <span class="font-weight-bold">Bs. {{ formatearNumero(item.total_debe) }}</span>
        </template>

        <!-- TOTAL HABER -->
        <template v-slot:item.total_haber="{ item }">
          <span class="font-weight-bold">Bs. {{ formatearNumero(item.total_haber) }}</span>
        </template>

        <!-- ESTADO -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            x-small
            :color="colorEstado(item.estado)"
            text-color="white"
            class="font-weight-bold"
          >
            {{ item.estado }}
          </v-chip>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center gap-1">
            <v-btn
              icon
              small
              color="primary"
              @click="verDetalleComprobante(item)"
              title="Ver detalle del asiento"
            >
              <v-icon small>mdi-eye-outline</v-icon>
            </v-btn>

            <v-btn
              icon
              small
              color="red darken-2"
              @click="descargarPdf(item.id)"
              title="Descargar PDF Oficial SAFCO"
            >
              <v-icon small>mdi-file-pdf-box</v-icon>
            </v-btn>

            <v-btn
              icon
              small
              color="orange darken-3"
              v-if="item.estado !== 'ANULADO'"
              @click="abrirDialogoAnular(item)"
              title="Anular comprobante"
            >
              <v-icon small>mdi-cancel</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: NUEVO COMPROBANTE MANUAL -->
    <v-dialog v-model="dialogoNuevo" max-width="1100px" persistent>
      <v-card rounded="lg">
        <v-card-title class="indigo darken-3 white--text py-3">
          <v-icon left color="white">mdi-receipt-text-plus</v-icon>
          <span>Nuevo Comprobante Contable Manual (Partida Doble)</span>
          <v-spacer></v-spacer>
          <v-btn icon dark small @click="dialogoNuevo = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <v-form ref="formNuevo" v-model="formValido">
            <!-- CABECERA DEL COMPROBANTE -->
            <v-row dense class="mb-3">
              <v-col cols="12" sm="3">
                <v-select
                  v-model="nuevoComp.tipo"
                  :items="tiposCreacion"
                  label="Tipo de Comprobante *"
                  outlined
                  dense
                  :rules="[v => !!v || 'Campo requerido']"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="nuevoComp.fecha"
                  label="Fecha de Registro *"
                  type="date"
                  outlined
                  dense
                  :rules="[v => !!v || 'Campo requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="nuevoComp.beneficiario"
                  label="Beneficiario / Razón Social"
                  placeholder="Ej: Banco Unión S.A. / Empresa Pública"
                  outlined
                  dense
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="nuevoComp.glosa"
                  label="Glosa General de la Operación *"
                  rows="2"
                  outlined
                  dense
                  placeholder="Describa el hecho económico o transacción contable..."
                  :rules="[v => !!v || 'La glosa es requerida']"
                ></v-textarea>
              </v-col>
            </v-row>

            <!-- DETALLE DE ASIENTOS (LÍNEAS CONTABLES) -->
            <div class="d-flex align-center justify-space-between mb-2">
              <h3 class="text-subtitle-1 font-weight-bold indigo--text text--darken-3 mb-0">
                <v-icon left small color="indigo darken-3">mdi-format-list-bulleted-type</v-icon>
                Líneas del Asiento Contable
              </h3>
              <v-btn
                color="indigo darken-2"
                dark
                small
                outlined
                class="rounded-pill"
                @click="agregarFilaDetalle"
              >
                <v-icon left small>mdi-plus</v-icon> Agregar Fila
              </v-btn>
            </div>

            <!-- TABLA DINÁMICA DE DETALLE -->
            <v-simple-table dense class="border rounded-lg mb-4">
              <thead>
                <tr class="grey lighten-4">
                  <th style="width: 35%;">Cuenta Contable Imputable *</th>
                  <th style="width: 20%;">Centro de Costo</th>
                  <th style="width: 20%;">Glosa Específica</th>
                  <th style="width: 11%;" class="text-right">Debe (Bs.)</th>
                  <th style="width: 11%;" class="text-right">Haber (Bs.)</th>
                  <th style="width: 3%;" class="text-center"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(linea, idx) in nuevoComp.detalles" :key="'linea-' + idx">
                  <!-- CUENTA -->
                  <td class="py-2">
                    <v-autocomplete
                      v-model="linea.plan_cuenta_id"
                      :items="cuentasImputables"
                      item-value="id"
                      :item-text="item => item.codigo + ' - ' + item.nombre"
                      placeholder="Seleccione cuenta..."
                      outlined
                      dense
                      hide-details
                    ></v-autocomplete>
                  </td>

                  <!-- CENTRO COSTO -->
                  <td class="py-2">
                    <v-select
                      v-model="linea.centro_costo_id"
                      :items="centrosCosto"
                      item-value="id"
                      item-text="nombre"
                      placeholder="General"
                      outlined
                      dense
                      hide-details
                      clearable
                    ></v-select>
                  </td>

                  <!-- GLOSA LÍNEA -->
                  <td class="py-2">
                    <v-text-field
                      v-model="linea.glosa"
                      placeholder="Opcional..."
                      outlined
                      dense
                      hide-details
                    ></v-text-field>
                  </td>

                  <!-- DEBE -->
                  <td class="py-2 text-right">
                    <v-text-field
                      v-model.number="linea.debe"
                      type="number"
                      step="0.01"
                      min="0"
                      class="text-right font-weight-bold"
                      outlined
                      dense
                      hide-details
                      @input="linea.haber = 0"
                    ></v-text-field>
                  </td>

                  <!-- HABER -->
                  <td class="py-2 text-right">
                    <v-text-field
                      v-model.number="linea.haber"
                      type="number"
                      step="0.01"
                      min="0"
                      class="text-right font-weight-bold"
                      outlined
                      dense
                      hide-details
                      @input="linea.debe = 0"
                    ></v-text-field>
                  </td>

                  <!-- ELIMINAR FILA -->
                  <td class="py-2 text-center">
                    <v-btn
                      icon
                      x-small
                      color="red"
                      :disabled="nuevoComp.detalles.length <= 2"
                      @click="eliminarFilaDetalle(idx)"
                    >
                      <v-icon small>mdi-delete-outline</v-icon>
                    </v-btn>
                  </td>
                </tr>
              </tbody>

              <!-- FOOTER DE SUMAS -->
              <tfoot>
                <tr class="indigo lighten-5 font-weight-bold">
                  <td colspan="3" class="text-right pr-3 font-weight-bold text-subtitle-2">TOTALES:</td>
                  <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-4">
                    Bs. {{ formatearNumero(sumaDebeNuevo) }}
                  </td>
                  <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-4">
                    Bs. {{ formatearNumero(sumaHaberNuevo) }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </v-simple-table>

            <!-- TARJETA DE VERIFICACIÓN PARTIDA DOBLE -->
            <v-alert
              :type="esPartidaDobleValida ? 'success' : 'error'"
              dense
              outlined
              class="mb-0"
            >
              <div class="d-flex align-center justify-space-between flex-wrap">
                <div>
                  <strong>{{ esPartidaDobleValida ? 'CUADRE PERFECTO' : 'DESBALANCEADO' }}:</strong>
                  {{ esPartidaDobleValida
                    ? 'Cumple la partida doble (Debe = Haber = Bs. ' + formatearNumero(sumaDebeNuevo) + ').'
                    : 'La diferencia es de Bs. ' + formatearNumero(diferenciaNuevo) + '. El asiento debe cuadrar exactamente.'
                  }}
                </div>
                <div class="font-weight-bold">
                  Diferencia: Bs. {{ formatearNumero(diferenciaNuevo) }}
                </div>
              </div>
            </v-alert>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-5 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogoNuevo = false" :disabled="guardando">Cancelar</v-btn>
          <v-btn
            color="indigo darken-2"
            dark
            class="px-5 rounded-pill font-weight-medium"
            :loading="guardando"
            :disabled="!esPartidaDobleValida || sumaDebeNuevo <= 0"
            @click="guardarComprobante"
          >
            Registrar y Aprobar Comprobante
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: VER DETALLE DEL COMPROBANTE -->
    <v-dialog v-model="dialogoDetalle" max-width="900px">
      <v-card rounded="lg" v-if="comprobanteDetalle">
        <v-card-title class="indigo darken-3 white--text py-3">
          <v-icon left color="white">mdi-eye-outline</v-icon>
          <span>Detalle de Asiento - {{ comprobanteDetalle.nro_comprobante }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark small @click="dialogoDetalle = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <!-- DATOS PRINCIPALES -->
          <v-row dense class="mb-3">
            <v-col cols="12" sm="4">
              <div class="text-caption text-secondary">TIPO Y NÚMERO</div>
              <div class="font-weight-bold indigo--text text--darken-3">
                {{ etiquetaTipo(comprobanteDetalle.tipo) }} - {{ comprobanteDetalle.nro_comprobante }}
              </div>
            </v-col>
            <v-col cols="12" sm="4">
              <div class="text-caption text-secondary">FECHA DE EMISIÓN</div>
              <div class="font-weight-medium">{{ comprobanteDetalle.fecha }}</div>
            </v-col>
            <v-col cols="12" sm="4">
              <div class="text-caption text-secondary">ESTADO</div>
              <div>
                <v-chip x-small :color="colorEstado(comprobanteDetalle.estado)" text-color="white" class="font-weight-bold">
                  {{ comprobanteDetalle.estado }}
                </v-chip>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-secondary">BENEFICIARIO</div>
              <div class="font-weight-medium">{{ comprobanteDetalle.beneficiario || 'S/B' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-secondary">ORIGEN DE INTEGRACIÓN</div>
              <div class="font-weight-medium">{{ comprobanteDetalle.origen_modulo || 'MANUAL' }}</div>
            </v-col>
            <v-col cols="12">
              <div class="text-caption text-secondary">GLOSA GENERAL</div>
              <div class="text-body-2 font-italic">{{ comprobanteDetalle.glosa }}</div>
            </v-col>
          </v-row>

          <v-divider class="my-3"></v-divider>

          <!-- TABLA DE LÍNEAS -->
          <v-simple-table dense class="border rounded-lg mb-4">
            <thead>
              <tr class="grey lighten-4">
                <th>Código</th>
                <th>Cuenta Contable</th>
                <th>Centro Costo</th>
                <th>Glosa Específica</th>
                <th class="text-right">Debe (Bs.)</th>
                <th class="text-right">Haber (Bs.)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in comprobanteDetalle.detalles" :key="'det-' + d.id">
                <td><code>{{ d.cuenta ? d.cuenta.codigo : '-' }}</code></td>
                <td class="font-weight-medium">{{ d.cuenta ? d.cuenta.nombre : '-' }}</td>
                <td>{{ d.centro_costo ? d.centro_costo.nombre : '-' }}</td>
                <td class="text-caption">{{ d.glosa }}</td>
                <td class="text-right font-weight-bold">{{ Number(d.debe) > 0 ? formatearNumero(d.debe) : '-' }}</td>
                <td class="text-right font-weight-bold">{{ Number(d.haber) > 0 ? formatearNumero(d.haber) : '-' }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="indigo lighten-5 font-weight-bold">
                <td colspan="4" class="text-right pr-3 text-subtitle-2">TOTALES:</td>
                <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                  Bs. {{ formatearNumero(comprobanteDetalle.total_debe) }}
                </td>
                <td class="text-right font-weight-bold text-subtitle-2 indigo--text text--darken-3">
                  Bs. {{ formatearNumero(comprobanteDetalle.total_haber) }}
                </td>
              </tr>
            </tfoot>
          </v-simple-table>
        </v-card-text>

        <v-card-actions class="px-5 pb-4">
          <v-btn
            color="red darken-2"
            dark
            class="rounded-pill"
            @click="descargarPdf(comprobanteDetalle.id)"
          >
            <v-icon left>mdi-file-pdf-box</v-icon> Imprimir Comprobante Oficial PDF
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn text @click="dialogoDetalle = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: CONFIRMAR ANULACIÓN -->
    <v-dialog v-model="dialogoAnular" max-width="500px">
      <v-card rounded="lg">
        <v-card-title class="orange darken-3 white--text py-3">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon>
          <span>Anular Comprobante Contable</span>
        </v-card-title>
        <v-card-text class="pt-4">
          <p>¿Está seguro de que desea anular el comprobante <strong>{{ comprobanteAAnular ? comprobanteAAnular.nro_comprobante : '' }}</strong>?</p>
          <v-textarea
            v-model="motivoAnulacion"
            label="Motivo de la anulación *"
            outlined
            dense
            rows="2"
            placeholder="Escriba la justificación institucional..."
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-5 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogoAnular = false">Cancelar</v-btn>
          <v-btn
            color="orange darken-3"
            dark
            class="rounded-pill"
            :loading="anulando"
            :disabled="!motivoAnulacion || motivoAnulacion.trim() === ''"
            @click="confirmarAnulacion"
          >
            Confirmar Anulación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'ComprobantesContables',
  data() {
    const hoy = new Date().toISOString().substring(0, 10)
    return {
      cargando: false,
      guardando: false,
      anulando: false,
      comprobantes: [],
      cuentasImputables: [],
      centrosCosto: [],

      busqueda: '',
      filtroTipo: null,
      filtroEstado: null,
      filtroFechaInicio: '',
      filtroFechaFin: '',

      headers: [
        { text: 'N° Comprobante', value: 'nro_comprobante', width: '160px', sortable: true },
        { text: 'Tipo', value: 'tipo', width: '110px', sortable: true },
        { text: 'Fecha', value: 'fecha', width: '120px', sortable: true },
        { text: 'Beneficiario / Glosa', value: 'glosa', sortable: false },
        { text: 'Total Debe', value: 'total_debe', width: '130px', align: 'end', sortable: true },
        { text: 'Total Haber', value: 'total_haber', width: '130px', align: 'end', sortable: true },
        { text: 'Estado', value: 'estado', width: '110px', sortable: true },
        { text: 'Acciones', value: 'acciones', width: '130px', sortable: false, align: 'center' },
      ],

      tiposComprobanteOpciones: [
        { text: 'Todos los tipos', value: null },
        { text: 'CI - Ingreso', value: 'CI' },
        { text: 'CE - Egreso', value: 'CE' },
        { text: 'CD - Diario / Traspaso', value: 'CD' },
      ],

      tiposCreacion: [
        { text: 'CI - Comprobante de Ingreso', value: 'CI' },
        { text: 'CE - Comprobante de Egreso', value: 'CE' },
        { text: 'CD - Comprobante de Diario', value: 'CD' },
      ],

      estadosOpciones: [
        { text: 'Todos los estados', value: null },
        { text: 'APROBADO', value: 'APROBADO' },
        { text: 'BORRADOR', value: 'BORRADOR' },
        { text: 'ANULADO', value: 'ANULADO' },
      ],

      dialogoNuevo: false,
      formValido: false,
      nuevoComp: {
        tipo: 'CD',
        fecha: hoy,
        beneficiario: '',
        glosa: '',
        detalles: [
          { plan_cuenta_id: null, centro_costo_id: null, glosa: '', debe: 0, haber: 0 },
          { plan_cuenta_id: null, centro_costo_id: null, glosa: '', debe: 0, haber: 0 },
        ],
      },

      dialogoDetalle: false,
      comprobanteDetalle: null,

      dialogoAnular: false,
      comprobanteAAnular: null,
      motivoAnulacion: '',
    }
  },

  computed: {
    totalIngresos() {
      return this.comprobantes
        .filter(c => c.tipo === 'CI' && c.estado !== 'ANULADO')
        .reduce((acc, curr) => acc + Number(curr.total_debe || 0), 0)
    },
    totalEgresos() {
      return this.comprobantes
        .filter(c => c.tipo === 'CE' && c.estado !== 'ANULADO')
        .reduce((acc, curr) => acc + Number(curr.total_debe || 0), 0)
    },
    totalDiarios() {
      return this.comprobantes
        .filter(c => c.tipo === 'CD' && c.estado !== 'ANULADO')
        .reduce((acc, curr) => acc + Number(curr.total_debe || 0), 0)
    },

    comprobantesFiltrados() {
      return this.comprobantes.filter(c => {
        if (!this.busqueda || this.busqueda.trim() === '') return true
        const q = this.busqueda.toLowerCase()
        const nro = c.nro_comprobante ? c.nro_comprobante.toLowerCase() : ''
        const glo = c.glosa ? c.glosa.toLowerCase() : ''
        const ben = c.beneficiario ? c.beneficiario.toLowerCase() : ''
        return nro.includes(q) || glo.includes(q) || ben.includes(q)
      })
    },

    sumaDebeNuevo() {
      if (!this.nuevoComp.detalles) return 0
      const sum = this.nuevoComp.detalles.reduce((acc, curr) => acc + (Number(curr.debe) || 0), 0)
      return Math.round(sum * 100) / 100
    },

    sumaHaberNuevo() {
      if (!this.nuevoComp.detalles) return 0
      const sum = this.nuevoComp.detalles.reduce((acc, curr) => acc + (Number(curr.haber) || 0), 0)
      return Math.round(sum * 100) / 100
    },

    diferenciaNuevo() {
      return Math.round(Math.abs(this.sumaDebeNuevo - this.sumaHaberNuevo) * 100) / 100
    },

    esPartidaDobleValida() {
      return this.sumaDebeNuevo > 0 && this.diferenciaNuevo === 0
    },
  },

  mounted() {
    this.cargarComprobantes()
    this.cargarCatalogosAuxiliares()
  },

  methods: {
    cargarComprobantes() {
      this.cargando = true
      const params = {}
      if (this.filtroTipo) params.tipo = this.filtroTipo
      if (this.filtroEstado) params.estado = this.filtroEstado
      if (this.filtroFechaInicio) params.fecha_inicio = this.filtroFechaInicio
      if (this.filtroFechaFin) params.fecha_fin = this.filtroFechaFin

      axios
        .get('/api/contabilidad/comprobantes', { params })
        .then(res => {
          if (res.data && res.data.data) {
            this.comprobantes = res.data.data
          }
        })
        .catch(err => {
          this.notificar('error', 'Error al consultar comprobantes contables')
        })
        .finally(() => {
          this.cargando = false
        })
    },

    cargarCatalogosAuxiliares() {
      axios
        .get('/api/contabilidad/plan-cuentas/imputables')
        .then(res => {
          if (res.data && res.data.data) {
            this.cuentasImputables = res.data.data
          }
        })
        .catch(() => {})

      axios
        .get('/api/contabilidad/centros-costo')
        .then(res => {
          if (res.data && res.data.data) {
            this.centrosCosto = res.data.data
          }
        })
        .catch(() => {})
    },

    colorTipo(tipo) {
      if (tipo === 'CI') return 'green darken-2'
      if (tipo === 'CE') return 'red darken-2'
      return 'blue darken-2'
    },

    etiquetaTipo(tipo) {
      if (tipo === 'CI') return 'CI - Ingreso'
      if (tipo === 'CE') return 'CE - Egreso'
      return 'CD - Diario'
    },

    colorEstado(estado) {
      if (estado === 'APROBADO') return 'green darken-2'
      if (estado === 'BORRADOR') return 'orange darken-2'
      return 'red darken-3'
    },

    formatearNumero(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00'
      return Number(val).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },

    abrirModalNuevoComprobante() {
      const hoy = new Date().toISOString().substring(0, 10)
      this.nuevoComp = {
        tipo: 'CD',
        fecha: hoy,
        beneficiario: '',
        glosa: '',
        detalles: [
          { plan_cuenta_id: null, centro_costo_id: null, glosa: '', debe: 0, haber: 0 },
          { plan_cuenta_id: null, centro_costo_id: null, glosa: '', debe: 0, haber: 0 },
        ],
      }
      this.dialogoNuevo = true
      this.$nextTick(() => {
        if (this.$refs.formNuevo) this.$refs.formNuevo.resetValidation()
      })
    },

    agregarFilaDetalle() {
      this.nuevoComp.detalles.push({
        plan_cuenta_id: null,
        centro_costo_id: null,
        glosa: '',
        debe: 0,
        haber: 0,
      })
    },

    eliminarFilaDetalle(idx) {
      if (this.nuevoComp.detalles.length > 2) {
        this.nuevoComp.detalles.splice(idx, 1)
      }
    },

    guardarComprobante() {
      if (!this.esPartidaDobleValida) {
        this.notificar('error', 'El asiento está desbalanceado. El total Debe debe ser igual al total Haber.')
        return
      }

      // Validar que todas las líneas tengan cuenta imputable seleccionada
      const sinCuenta = this.nuevoComp.detalles.some(d => !d.plan_cuenta_id)
      if (sinCuenta) {
        this.notificar('error', 'Seleccione la cuenta contable en todas las líneas del asiento')
        return
      }

      this.guardando = true
      axios
        .post('/api/contabilidad/comprobantes', this.nuevoComp)
        .then(res => {
          this.notificar('success', res.data.message || 'Comprobante registrado y aprobado correctamente')
          this.dialogoNuevo = false
          this.cargarComprobantes()
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al registrar el comprobante'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.guardando = false
        })
    },

    verDetalleComprobante(item) {
      axios
        .get('/api/contabilidad/comprobantes/' + item.id)
        .then(res => {
          if (res.data && res.data.data) {
            this.comprobanteDetalle = res.data.data
            this.dialogoDetalle = true
          }
        })
        .catch(() => {
          this.notificar('error', 'Error al cargar detalles del comprobante')
        })
    },

    descargarPdf(id) {
      window.open('/api/contabilidad/comprobantes/' + id + '/pdf', '_blank')
    },

    abrirDialogoAnular(item) {
      this.comprobanteAAnular = item
      this.motivoAnulacion = ''
      this.dialogoAnular = true
    },

    confirmarAnulacion() {
      if (!this.comprobanteAAnular || !this.motivoAnulacion) return

      this.anulando = true
      axios
        .put('/api/contabilidad/comprobantes/' + this.comprobanteAAnular.id + '/anular', {
          motivo: this.motivoAnulacion,
        })
        .then(res => {
          this.notificar('success', res.data.message || 'Comprobante anulado correctamente')
          this.dialogoAnular = false
          this.cargarComprobantes()
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al anular comprobante'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.anulando = false
        })
    },

    notificar(tipo, mensaje) {
      if (window.iziToast) {
        if (tipo === 'success') {
          window.iziToast.success({ title: 'Éxito', message: mensaje, position: 'topRight' })
        } else {
          window.iziToast.error({ title: 'Error', message: mensaje, position: 'topRight' })
        }
      } else {
        alert(mensaje)
      }
    },
  },
}
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05) !important;
}
.border-l-primary {
  border-left: 4px solid #3f51b5 !important;
}
.border-l-success {
  border-left: 4px solid #4caf50 !important;
}
.border-l-danger {
  border-left: 4px solid #f44336 !important;
}
.border-l-info {
  border-left: 4px solid #2196f3 !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
