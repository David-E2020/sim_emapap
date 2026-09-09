<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Padrón de Abonados</h2>
            <span class="text-caption text-secondary">Administración integral del padrón de usuarios, conexiones y micromedición</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="abrirModalNuevo">
            <v-icon left small>mdi-account-plus</v-icon> + Nuevo Abonado
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE RESUMEN -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Total Abonados</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ totalAbonados }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Servicios Activos</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ activosCount }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">En Mora (>= 2 meses)</div>
          <div class="text-h4 font-weight-black warning--text mt-1">{{ moraCount }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Servicios Cortados</div>
          <div class="text-h4 font-weight-black error--text mt-1">{{ cortadosCount }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="busqueda"
            label="Buscar por Código, Nombre, CI/NIT o N° Medidor..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarAbonados"
            @click:clear="cargarAbonados"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtroZona"
            :items="zonas"
            item-text="nombre"
            item-value="id"
            label="Filtrar por Zona"
            dense
            outlined
            hide-details
            clearable
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroCategoria"
            :items="categorias"
            item-text="nombre"
            item-value="id"
            label="Categoría"
            dense
            outlined
            hide-details
            clearable
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroEstado"
            :items="['TODOS', 'ACTIVO', 'EN_MORA', 'CORTADO', 'BAJA']"
            label="Estado"
            dense
            outlined
            hide-details
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="1" class="text-center">
          <v-btn icon color="primary" :loading="cargando" @click="cargarAbonados">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE ABONADOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="columnas"
        :items="abonados"
        :loading="cargando"
        :server-items-length="totalAbonados"
        :options.sync="opcionesPaginacion"
        :footer-props="{ 'items-per-page-options': [10, 15, 25, 50] }"
        class="elevation-0"
      >
        <!-- Código -->
        <template v-slot:item.codigo="{ item }">
          <v-chip color="primary" outlined small class="font-weight-bold">
            {{ item.codigo }}
          </v-chip>
        </template>

        <!-- Nombre -->
        <template v-slot:item.nombre_completo="{ item }">
          <div>
            <div class="font-weight-bold">{{ item.nombre_completo }}</div>
            <span class="text-caption text-secondary" v-if="item.numero_documento">
              Doc: {{ item.numero_documento }}
            </span>
          </div>
        </template>

        <!-- Ubicación -->
        <template v-slot:item.ubicacion="{ item }">
          <div class="text-caption">
            <v-icon x-small color="grey">mdi-map-marker</v-icon>
            <strong>{{ item.zona ? item.zona.nombre : 'S/Z' }}</strong>
            <span v-if="item.calle"> - {{ item.calle.nombre }}</span>
            <span v-if="item.numero_vivienda"> #{{ item.numero_vivienda }}</span>
          </div>
        </template>

        <!-- Categoría -->
        <template v-slot:item.categoria="{ item }">
          <v-chip x-small color="info" outlined>
            {{ item.categoria ? item.categoria.nombre : 'S/C' }}
          </v-chip>
          <v-icon x-small color="amber darken-2" v-if="item.es_tercera_edad" title="Aplica 20% Ley 1886">
            mdi-account-star
          </v-icon>
        </template>

        <!-- Medidor -->
        <template v-slot:item.medidor="{ item }">
          <span v-if="item.medidor_actual" class="text-caption font-weight-medium">
            <v-icon x-small color="blue">mdi-counter</v-icon> {{ item.medidor_actual.numero_serie }}
          </span>
          <span v-else class="text-caption text-secondary">Sin medidor</span>
        </template>

        <!-- Estado de Servicio -->
        <template v-slot:item.estado_servicio="{ item }">
          <v-chip
            small
            :color="colorEstado(item.estado_servicio)"
            text-color="white"
            class="font-weight-medium"
          >
            {{ item.estado_servicio }}
          </v-chip>
        </template>

        <!-- Saldo Deuda -->
        <template v-slot:item.saldo_deuda="{ item }">
          <div class="text-right font-weight-bold" :class="item.saldo_deuda > 0 ? 'error--text' : 'success--text'">
            Bs {{ parseFloat(item.saldo_deuda).toFixed(2) }}
            <div class="text-caption text-secondary" v-if="item.meses_mora > 0">
              ({{ item.meses_mora }} meses)
            </div>
          </div>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center gap-1">
            <v-btn icon small color="primary" title="Ver ficha completa" @click="verFicha(item)">
              <v-icon small>mdi-eye</v-icon>
            </v-btn>
            <v-btn icon small color="teal" title="Cobrar en ventanilla" :to="{ path: '/comercial/caja', query: { codigo: item.codigo } }">
              <v-icon small>mdi-cash-register</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO NUEVO ABONADO -->
    <v-dialog v-model="modalNuevo" max-width="800" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon color="white" class="mr-2">mdi-account-plus</v-icon>
          Registrar Nuevo Abonado
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form ref="formNuevo" v-model="formValido">
            <v-row dense>
              <v-col cols="12" sm="4">
                <v-select
                  v-model="nuevo.tipo_persona"
                  :items="['NATURAL', 'JURIDICA']"
                  label="Tipo Persona *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" sm="8">
                <v-text-field
                  v-model="nuevo.nombre_completo"
                  label="Nombres y Apellidos o Razón Social *"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="nuevo.numero_documento"
                  label="N° CI o NIT"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="nuevo.telefono"
                  label="Teléfono"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="nuevo.celular"
                  label="Celular"
                  dense
                  outlined
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="nuevo.id_zona"
                  :items="zonas"
                  item-text="nombre"
                  item-value="id"
                  label="Zona Territorial *"
                  dense
                  outlined
                  required
                  @change="cargarCallesPorZona"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="nuevo.id_calle"
                  :items="callesForm"
                  item-text="nombre"
                  item-value="id"
                  label="Calle / Avenida"
                  dense
                  outlined
                  clearable
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="nuevo.numero_vivienda"
                  label="N° Puerta / Vivienda"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="4">
                <v-select
                  v-model="nuevo.id_categoria"
                  :items="categorias"
                  item-text="nombre"
                  item-value="id"
                  label="Categoría Tarifaria *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="nuevo.numero_medidor"
                  label="N° Serie Medidor"
                  dense
                  outlined
                  placeholder="Ej: S-202601"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-checkbox
                  v-model="nuevo.tiene_alcantarillado"
                  label="Cuenta con Alcantarillado Sanitario"
                  dense
                  hide-details
                ></v-checkbox>
              </v-col>
              <v-col cols="12" sm="6">
                <v-checkbox
                  v-model="nuevo.es_tercera_edad"
                  label="Beneficiario 3ra Edad (20% Ley 1886)"
                  dense
                  hide-details
                ></v-checkbox>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardando" @click="guardarAbonado">
            Guardar Abonado
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO FICHA ABONADO -->
    <v-dialog v-model="modalFicha" max-width="850">
      <v-card rounded="lg" v-if="abonadoSeleccionado">
        <v-card-title class="primary white--text py-3 d-flex justify-space-between">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">mdi-card-account-details-outline</v-icon>
            Ficha del Abonado #{{ abonadoSeleccionado.codigo }}
          </div>
          <v-btn icon color="white" @click="modalFicha = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="8">
              <h3 class="text-h6 font-weight-bold">{{ abonadoSeleccionado.nombre_completo }}</h3>
              <div class="text-body-2 text-secondary">
                Documento: {{ abonadoSeleccionado.numero_documento || 'No registrado' }} | Tipo: {{ abonadoSeleccionado.tipo_persona }}
              </div>
              <div class="text-body-2 mt-1">
                <v-icon small color="grey">mdi-map-marker</v-icon>
                {{ abonadoSeleccionado.zona ? abonadoSeleccionado.zona.nombre : '' }}
                {{ abonadoSeleccionado.calle ? ' - ' + abonadoSeleccionado.calle.nombre : '' }}
                {{ abonadoSeleccionado.numero_vivienda ? ' #' + abonadoSeleccionado.numero_vivienda : '' }}
              </div>
            </v-col>
            <v-col cols="12" md="4" class="text-right">
              <v-chip :color="colorEstado(abonadoSeleccionado.estado_servicio)" text-color="white" class="font-weight-bold mb-1">
                {{ abonadoSeleccionado.estado_servicio }}
              </v-chip>
              <div class="text-h6 font-weight-black" :class="abonadoSeleccionado.saldo_deuda > 0 ? 'error--text' : 'success--text'">
                Bs {{ parseFloat(abonadoSeleccionado.saldo_deuda).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Deuda Acumulada ({{ abonadoSeleccionado.meses_mora }} meses)</div>
            </v-col>
          </v-row>

          <!-- BOTONES DE ACCIÓN FICHA -->
          <div class="d-flex align-center gap-2 my-3 flex-wrap">
            <v-btn color="primary" outlined small class="rounded-pill text-capitalize" @click="descargarExtracto(abonadoSeleccionado)">
              <v-icon left small>mdi-file-pdf-box</v-icon> Descargar Extracto Histórico
            </v-btn>
            <v-btn color="warning" outlined small class="rounded-pill text-capitalize" @click="abrirCambioMedidor(abonadoSeleccionado)">
              <v-icon left small>mdi-counter</v-icon> Cambiar Medidor
            </v-btn>
            <v-btn color="error" outlined small class="rounded-pill text-capitalize" v-if="abonadoSeleccionado.estado_servicio !== 'BAJA'" @click="abrirDarBaja(abonadoSeleccionado)">
              <v-icon left small>mdi-account-cancel</v-icon> Dar de Baja
            </v-btn>
          </div>

          <v-divider class="my-4"></v-divider>

          <v-tabs v-model="tabFicha" color="primary" dense>
            <v-tab><v-icon small left>mdi-counter</v-icon> Historial Lecturas</v-tab>
            <v-tab><v-icon small left>mdi-handshake-outline</v-icon> Convenios</v-tab>
          </v-tabs>

          <v-tabs-items v-model="tabFicha" class="pt-3">
            <v-tab-item>
              <div style="max-height: 420px; overflow-y: auto;">
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr>
                        <th>Periodo</th>
                        <th class="text-right">Anterior</th>
                        <th class="text-right">Actual</th>
                        <th class="text-right">Consumo m³</th>
                        <th class="text-right">Total Bs</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Fecha Pago</th>
                        <th class="text-center">N° Factura</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="lec in abonadoSeleccionado.lecturas" :key="lec.id">
                        <td><strong>{{ lec.periodo ? lec.periodo.periodo : '-' }}</strong></td>
                        <td class="text-right">{{ lec.lectura_anterior }}</td>
                        <td class="text-right">{{ lec.lectura_actual }}</td>
                        <td class="text-right font-weight-bold">{{ lec.consumo_m3 }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(lec.total_facturado).toFixed(2) }}</td>
                        <td class="text-center">
                          <v-chip x-small :color="lec.estado_pago === 'PAGADO' ? 'success' : 'warning'" text-color="white">
                            {{ lec.estado_pago }}
                          </v-chip>
                        </td>
                        <td class="text-center text-caption font-weight-medium">
                          <span v-if="lec.fecha_pago" class="success--text">
                            <v-icon x-small color="success">mdi-calendar-check</v-icon> {{ formatearFecha(lec.fecha_pago) }}
                          </span>
                          <span v-else class="grey--text">-</span>
                        </td>
                        <td class="text-center text-caption">
                          <template v-if="lec.factura_siat && lec.factura_siat.numero_factura">
                            <v-btn
                              x-small
                              text
                              color="primary"
                              class="font-weight-bold"
                              @click="abrirFacturaDesdeFicha(lec.factura_siat.id, lec.factura_siat.numero_factura)"
                            >
                              <v-icon x-small left>mdi-file-document-outline</v-icon> #{{ lec.factura_siat.numero_factura }}
                            </v-btn>
                          </template>
                          <span v-else-if="lec.id_factura" class="font-weight-medium">
                            #{{ lec.id_factura }}
                          </span>
                          <span v-else class="grey--text">-</span>
                        </td>
                      </tr>
                      <tr v-if="!abonadoSeleccionado.lecturas || abonadoSeleccionado.lecturas.length === 0">
                        <td colspan="8" class="text-center text-secondary py-3">No registra lecturas en el historial</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </div>
            </v-tab-item>

            <v-tab-item>
              <div v-if="abonadoSeleccionado.convenios && abonadoSeleccionado.convenios.length > 0">
                <v-card outlined class="mb-2 pa-3" v-for="conv in abonadoSeleccionado.convenios" :key="conv.id">
                  <div class="d-flex justify-space-between align-center">
                    <div>
                      <strong>{{ conv.numero_convenio }}</strong> - {{ conv.plazo_meses }} Cuotas de Bs {{ conv.monto_cuota_mensual }}
                      <div class="text-caption text-secondary">Suscrito: {{ conv.fecha_suscripcion }}</div>
                    </div>
                    <v-chip small color="primary" outlined>{{ conv.estado }}</v-chip>
                  </div>
                </v-card>
              </div>
              <div v-else class="text-center text-secondary py-4">No tiene convenios de pago registrados</div>
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CAMBIO DE MEDIDOR -->
    <v-dialog v-model="modalCambioMedidor" max-width="500">
      <v-card rounded="lg">
        <v-card-title class="warning white--text py-3">
          <v-icon color="white" class="mr-2">mdi-counter</v-icon>
          Reemplazo / Cambio de Medidor
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.lectura_final_anterior"
                label="Lectura Final Medidor Anterior *"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.numero_serie_nuevo"
                label="N° Serie Nuevo Medidor *"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.marca"
                label="Marca Nuevo Medidor"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.lectura_inicial_nuevo"
                label="Lectura Inicial Nuevo"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="formCambio.motivo"
                label="Motivo del Reemplazo *"
                rows="2"
                dense
                outlined
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalCambioMedidor = false">Cancelar</v-btn>
          <v-btn color="warning" :loading="guardandoCambio" @click="confirmarCambioMedidor">
            Registrar Cambio
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DAR DE BAJA -->
    <v-dialog v-model="modalDarBaja" max-width="450">
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon color="white" class="mr-2">mdi-account-cancel</v-icon>
          Baja Definitiva de Servicio
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="text-body-2 mb-3">
            ¿Está seguro de procesar la baja definitiva del servicio para este abonado?
          </div>
          <v-text-field
            v-model="formBaja.lectura_retiro"
            label="Lectura de Retiro del Medidor"
            type="number"
            dense
            outlined
          ></v-text-field>
          <v-textarea
            v-model="formBaja.motivo"
            label="Motivo de la Baja Definitiva *"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalDarBaja = false">Cancelar</v-btn>
          <v-btn color="error" :loading="guardandoBaja" @click="confirmarDarBaja">
            Confirmar Baja
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE PDF (EXTRACTO DE CUENTA / HISTORIAL) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'PadronAbonados',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      cargando: false,
      guardando: false,
      busqueda: '',
      filtroZona: null,
      filtroCategoria: null,
      filtroEstado: 'TODOS',
      abonados: [],
      zonas: [],
      categorias: [],
      callesForm: [],
      totalAbonados: 0,
      activosCount: 0,
      moraCount: 0,
      cortadosCount: 0,
      opcionesPaginacion: {},
      columnas: [
        { text: 'Código', value: 'codigo', width: '90px' },
        { text: 'Abonado / Razón Social', value: 'nombre_completo' },
        { text: 'Ubicación', value: 'ubicacion' },
        { text: 'Categoría', value: 'categoria', width: '130px' },
        { text: 'Medidor', value: 'medidor', width: '120px' },
        { text: 'Estado', value: 'estado_servicio', width: '110px' },
        { text: 'Saldo Deuda', value: 'saldo_deuda', align: 'end', width: '130px' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '90px' },
      ],
      modalNuevo: false,
      modalFicha: false,
      modalCambioMedidor: false,
      guardandoCambio: false,
      formCambio: {
        lectura_final_anterior: 0,
        numero_serie_nuevo: '',
        marca: '',
        lectura_inicial_nuevo: 0,
        motivo: '',
      },
      modalDarBaja: false,
      guardandoBaja: false,
      formBaja: {
        lectura_retiro: null,
        motivo: '',
      },
      formValido: true,
      tabFicha: 0,
      abonadoSeleccionado: null,
      nuevo: {
        tipo_persona: 'NATURAL',
        nombre_completo: '',
        numero_documento: '',
        telefono: '',
        celular: '',
        id_zona: null,
        id_calle: null,
        numero_vivienda: '',
        id_categoria: null,
        numero_medidor: '',
        tiene_alcantarillado: true,
        es_tercera_edad: false,
      },
    };
  },
  watch: {
    opcionesPaginacion: {
      handler() {
        this.cargarAbonados();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarParametricas();
    this.cargarAbonados();
  },
  methods: {
    colorEstado(estado) {
      switch (estado) {
        case 'ACTIVO': return 'success';
        case 'CORTADO': return 'error';
        case 'EN_MORA': return 'warning';
        case 'BAJA': return 'grey';
        default: return 'primary';
      }
    },
    async cargarParametricas() {
      try {
        const [resZonas, resCategorias] = await Promise.all([
          axios.get('/api/comercial/zonas'),
          axios.get('/api/comercial/tarifas'),
        ]);
        this.zonas = resZonas.data.data || [];
        this.categorias = resCategorias.data.data || [];
      } catch (e) {
        console.error('Error cargando zonas y tarifas:', e);
      }
    },
    async cargarCallesPorZona() {
      if (!this.nuevo.id_zona) {
        this.callesForm = [];
        return;
      }
      try {
        const res = await axios.get('/api/comercial/calles', {
          params: { id_zona: this.nuevo.id_zona },
        });
        this.callesForm = res.data.data || [];
      } catch (e) {
        console.error('Error cargando calles:', e);
      }
    },
    async cargarAbonados() {
      this.cargando = true;
      const { page, itemsPerPage } = this.opcionesPaginacion;
      try {
        const res = await axios.get('/api/comercial/abonados', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            search: this.busqueda || undefined,
            id_zona: this.filtroZona || undefined,
            id_categoria: this.filtroCategoria || undefined,
            estado_servicio: this.filtroEstado !== 'TODOS' ? this.filtroEstado : undefined,
          },
        });

        this.abonados = res.data.data || [];
        this.totalAbonados = res.data.total || 0;

        // Métricas básicas para las tarjetas
        this.activosCount = this.abonados.filter(a => a.estado_servicio === 'ACTIVO').length;
        this.moraCount = this.abonados.filter(a => a.meses_mora >= 2).length;
        this.cortadosCount = this.abonados.filter(a => a.estado_servicio === 'CORTADO').length;
      } catch (e) {
        console.error('Error cargando abonados:', e);
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevo() {
      this.nuevo = {
        tipo_persona: 'NATURAL',
        nombre_completo: '',
        numero_documento: '',
        telefono: '',
        celular: '',
        id_zona: this.zonas.length > 0 ? this.zonas[0].id : null,
        id_calle: null,
        numero_vivienda: '',
        id_categoria: this.categorias.length > 0 ? this.categorias[0].id : null,
        numero_medidor: '',
        tiene_alcantarillado: true,
        es_tercera_edad: false,
      };
      this.cargarCallesPorZona();
      this.modalNuevo = true;
    },
    async guardarAbonado() {
      if (!this.nuevo.nombre_completo || !this.nuevo.id_zona || !this.nuevo.id_categoria) {
        alert('Por favor complete los campos obligatorios.');
        return;
      }

      this.guardando = true;
      try {
        await axios.post('/api/comercial/abonados', this.nuevo);
        this.modalNuevo = false;
        this.cargarAbonados();
      } catch (e) {
        alert(e.response?.data?.message || 'Error al registrar abonado.');
      } finally {
        this.guardando = false;
      }
    },
    async verFicha(item) {
      try {
        const res = await axios.get(`/api/comercial/abonados/${item.id}`);
        this.abonadoSeleccionado = res.data.data;
        this.modalFicha = true;
      } catch (e) {
        console.error('Error cargando ficha:', e);
      }
    },
    descargarExtracto(item) {
      if (!item) return;
      this.urlVisorPdf = `/api/comercial/abonados/${item.id}/extracto/pdf`;
      this.tituloVisorPdf = `Extracto de Cuenta - Abonado #${item.codigo}`;
      this.subtituloVisorPdf = `${item.nombre_completo || ''} | NIT/CI: ${item.numero_documento || 'S/N'}`;
      this.mostrarVisorPdf = true;
    },
    abrirFacturaDesdeFicha(facturaId, numeroFactura) {
      this.urlVisorPdf = `/api/facturacion/facturas/${facturaId}/pdf?formato=rollo`;
      this.tituloVisorPdf = `Factura Electrónica SIAT #${numeroFactura}`;
      this.subtituloVisorPdf = 'Comprobante Oficial Autorizado por el SIN';
      this.mostrarVisorPdf = true;
    },
    abrirCambioMedidor(item) {
      this.formCambio = {
        lectura_final_anterior: item.medidor_actual?.lectura_inicial || 0,
        numero_serie_nuevo: '',
        marca: '',
        lectura_inicial_nuevo: 0,
        motivo: '',
      };
      this.modalCambioMedidor = true;
    },
    async confirmarCambioMedidor() {
      if (!this.formCambio.numero_serie_nuevo || !this.formCambio.motivo) {
        alert('Ingrese el nuevo número de serie y el motivo.');
        return;
      }
      this.guardandoCambio = true;
      try {
        await axios.post(`/api/comercial/abonados/${this.abonadoSeleccionado.id}/cambiar-medidor`, this.formCambio);
        this.modalCambioMedidor = false;
        await this.verFicha(this.abonadoSeleccionado);
        this.cargarAbonados();
        alert('Medidor reemplazado exitosamente.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al reemplazar medidor.');
      } finally {
        this.guardandoCambio = false;
      }
    },
    abrirDarBaja(item) {
      this.formBaja = {
        lectura_retiro: item.medidor_actual?.lectura_inicial || null,
        motivo: '',
      };
      this.modalDarBaja = true;
    },
    async confirmarDarBaja() {
      if (!this.formBaja.motivo) {
        alert('Debe ingresar un motivo para la baja definitiva.');
        return;
      }
      this.guardandoBaja = true;
      try {
        await axios.post(`/api/comercial/abonados/${this.abonadoSeleccionado.id}/dar-baja`, this.formBaja);
        this.modalDarBaja = false;
        await this.verFicha(this.abonadoSeleccionado);
        this.cargarAbonados();
        alert('Servicio dado de baja.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al dar de baja servicio.');
      } finally {
        this.guardandoBaja = false;
      }
    },
    formatearFecha(fecha) {
      if (!fecha) return '-';
      const f = fecha.toString().substring(0, 10);
      const partes = f.split('-');
      if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
      }
      return f;
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
</style>
