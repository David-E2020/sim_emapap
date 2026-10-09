<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-clock-check-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">
              {{ esAdmin ? 'Control Institucional de Asistencias' : 'Mi Control de Asistencia Personal' }}
            </h2>
            <span class="text-caption text-secondary">
              {{ esAdmin ? 'Monitoreo de marcaciones del personal, cálculo de tolerancias, atrasos y refrigerios' : 'Registro de mis marcaciones biométricas, control de puntualidad y refrigerios' }}
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <!-- Botón de acceso a Biométricos (Solo administradores) -->
          <v-btn
            v-if="esAdmin"
            color="blue-grey darken-3"
            dark
            outlined
            class="rounded-pill font-weight-medium text-capitalize"
            to="/rrhh/biometricos"
          >
            <v-icon left small>mdi-fingerprint</v-icon> Relojes Biométricos
          </v-btn>

          <!-- Botón Marcar Asistencia Masiva (Solo administradores) -->
          <v-btn
            v-if="esAdmin"
            color="success darken-1"
            dark
            class="rounded-pill font-weight-medium text-capitalize"
            @click="abrirModalMarcarMasivo"
          >
            <v-icon left small>mdi-account-check</v-icon> Marcar Asistencia Masiva
          </v-btn>

          <!-- Botón Calcular Asistencia del Día (Solo administradores) -->
          <v-btn
            v-if="esAdmin"
            color="primary"
            outlined
            class="rounded-pill font-weight-medium text-capitalize"
            @click="dialogCalcular = true"
          >
            <v-icon left small>mdi-calculator</v-icon> Calcular Asistencia
          </v-btn>

          <!-- Imprimir -->
          <v-btn
            color="primary"
            dark
            class="rounded-pill font-weight-medium text-capitalize"
            @click="imprimirReporte"
          >
            <v-icon left small>mdi-printer</v-icon> Imprimir
          </v-btn>

          <!-- Exportar CSV -->
          <v-btn
            color="indigo darken-1"
            dark
            class="rounded-pill font-weight-medium text-capitalize"
            @click="exportarCsv"
          >
            <v-icon left small>mdi-file-delimited</v-icon> CSV
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS KPIS DE ASISTENCIA Y PUNTUALIDAD -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Registros</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ totales.total_registros }}</div>
          <div class="text-caption text-secondary">
            {{ modoVista === 'diario' ? 'Funcionarios evaluados hoy' : 'Jornadas del periodo' }}
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Asistencias / Presentes</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ totales.presentes }}</div>
          <div class="text-caption success--text text--darken-2 font-weight-medium">Jornadas con marcación</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Atrasos Detectados</div>
          <div class="text-h4 font-weight-black warning--text text--darken-3 mt-1">
            {{ totales.atrasos }} <small class="text-caption font-weight-bold grey--text">({{ totales.total_minutos_atraso }} min)</small>
          </div>
          <div class="text-caption text-secondary">Minutos acumulados fuera de tolerancia</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Refrigerios Habilitados</div>
          <div class="text-h4 font-weight-black green--text text--darken-2 mt-1">
            {{ totales.refrigerios_habilitados }}
          </div>
          <div class="text-caption font-weight-bold green--text text--darken-3">
            Bs. {{ totales.monto_refrigerio_bs.toFixed(2) }} (a Bs. 18/día)
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- CARD PRINCIPAL: FILTROS Y TABLA DE ASISTENCIAS -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <!-- SELECTOR DE MODO DE VISTA (Solo Administrador / RRHH) -->
      <div v-if="esAdmin" class="d-flex align-center justify-space-between flex-wrap gap-2 mb-3 pb-3 border-b">
        <div class="d-flex align-center gap-2 flex-wrap">
          <span class="text-caption font-weight-bold text-secondary mr-2">MODALIDAD DE CONSULTA:</span>
          <v-btn-toggle v-model="modoVista" mandatory dense color="primary" @change="cambiarModoVista">
            <v-btn small value="diario" class="text-capitalize">
              <v-icon left x-small>mdi-calendar-today</v-icon> Parte Diario General
            </v-btn>
            <v-btn small value="mensual" class="text-capitalize">
              <v-icon left x-small>mdi-calendar-month</v-icon> Historial Mensual
            </v-btn>
            <v-btn small value="personal" class="text-capitalize">
              <v-icon left x-small>mdi-account-clock</v-icon> Mi Asistencia
            </v-btn>
          </v-btn-toggle>
        </div>

        <div class="d-flex align-center gap-2">
          <v-chip small color="primary" outlined class="font-weight-bold">
            Perfil: {{ esAdmin ? 'ADMINISTRADOR RRHH' : 'FUNCIONARIO' }}
          </v-chip>
        </div>
      </div>

      <!-- FILTROS REACTIVOS -->
      <v-row dense align="center" class="mb-3">
        <!-- Si modo es DIARIO: Selector de Fecha -->
        <v-col v-if="modoVista === 'diario'" cols="12" sm="6" md="3">
          <v-menu
            v-model="menuFecha"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="auto"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                v-model="filtroFecha"
                label="Fecha de Control *"
                prepend-inner-icon="mdi-calendar"
                readonly
                outlined
                dense
                hide-details
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker v-model="filtroFecha" no-title @input="menuFecha = false; cargarAsistencias()"></v-date-picker>
          </v-menu>
        </v-col>

        <!-- Si modo es MENSUAL o PERSONAL: Mes y Año -->
        <template v-if="modoVista === 'mensual' || modoVista === 'personal'">
          <v-col cols="12" sm="6" md="3">
            <v-select
              v-model="filtroMes"
              :items="meses"
              item-text="nombre"
              item-value="id"
              label="Mes de Reporte"
              outlined
              dense
              hide-details
              prepend-inner-icon="mdi-calendar-month"
              @change="cargarAsistencias"
            ></v-select>
          </v-col>
          <v-col cols="12" sm="6" md="2">
            <v-text-field
              v-model.number="filtroAnio"
              label="Gestión"
              type="number"
              outlined
              dense
              hide-details
              prepend-inner-icon="mdi-calendar"
              @change="cargarAsistencias"
            ></v-text-field>
          </v-col>
        </template>

        <!-- Si Admin en mensual: Selector opcional de Funcionario -->
        <v-col v-if="esAdmin && modoVista === 'mensual'" cols="12" sm="6" md="4">
          <v-autocomplete
            v-model="filtroIdPersona"
            :items="catalogoPersonal"
            item-text="nombre_completo"
            item-value="id"
            label="Filtrar por Funcionario (Opcional)"
            placeholder="Todos los funcionarios"
            outlined
            dense
            clearable
            hide-details
            prepend-inner-icon="mdi-account-search"
            @change="cargarAsistencias"
          ></v-autocomplete>
        </v-col>

        <!-- Búsqueda rápida por texto -->
        <v-col cols="12" :sm="esAdmin ? 6 : 12" :md="modoVista === 'diario' ? 6 : 3">
          <v-text-field
            v-model="busquedaTexto"
            label="Buscar por nombre, CI o cargo..."
            outlined
            dense
            hide-details
            prepend-inner-icon="mdi-magnify"
            clearable
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="2" class="d-flex justify-end">
          <v-btn
            color="primary"
            dark
            class="rounded-pill w-100 font-weight-bold"
            :loading="loadingAsistencias"
            @click="cargarAsistencias"
          >
            <v-icon left small>mdi-refresh</v-icon> Actualizar
          </v-btn>
        </v-col>
      </v-row>

      <v-divider class="mb-3"></v-divider>

      <!-- TABLA DE RESULTADOS DE ASISTENCIA -->
      <div id="asistencias-print-area">
        <!-- Membrete solo para impresión -->
        <div class="d-none d-print-block text-center mb-4">
          <h3 class="font-weight-bold text-h6">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA</h3>
          <p class="text-caption mb-1">REPORTE OFICIAL DE CONTROL DE ASISTENCIA Y PUNTUALIDAD</p>
          <p class="text-caption font-weight-bold">
            {{ modoVista === 'diario' ? `PARTE DEL DÍA: ${filtroFecha}` : `GESTIÓN: ${filtroMes}/${filtroAnio}` }}
          </p>
        </div>

        <v-data-table
          :headers="headersComputados"
          :items="asistenciasFiltradas"
          :loading="loadingAsistencias"
          class="erp-table"
          dense
          :items-per-page="25"
          :footer-props="{ 'items-per-page-options': [15, 25, 50, 100] }"
        >
          <!-- FUNCIONARIO -->
          <template v-slot:item.funcionario="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar size="32" color="primary" class="white--text mr-2 font-weight-bold text-caption">
                {{ item.funcionario ? item.funcionario.charAt(0) : 'F' }}
              </v-avatar>
              <div>
                <div class="font-weight-bold text-body-2">{{ item.funcionario }}</div>
                <div class="text-caption text-secondary">
                  C.I. {{ item.ci }} &bull; <span class="primary--text font-weight-medium">{{ item.cargo }}</span>
                </div>
              </div>
            </div>
          </template>

          <!-- FECHA Y DÍA (Para vista personal o mensual) -->
          <template v-slot:item.fecha="{ item }">
            <div>
              <span class="font-weight-bold">{{ item.fecha }}</span>
              <div class="text-caption text-secondary text-capitalize">{{ item.dia }}</div>
            </div>
          </template>

          <!-- ENTRADA 1 -->
          <template v-slot:item.entrada_1="{ item }">
            <span :class="item.entrada_1 !== '-' ? 'font-weight-medium text-dark' : 'grey--text'">
              <v-icon x-small color="success" v-if="item.entrada_1 !== '-'">mdi-login</v-icon>
              {{ item.entrada_1 }}
            </span>
          </template>

          <!-- SALIDA 1 -->
          <template v-slot:item.salida_1="{ item }">
            <span :class="item.salida_1 !== '-' ? 'font-weight-medium text-dark' : 'grey--text'">
              <v-icon x-small color="info" v-if="item.salida_1 !== '-'">mdi-logout</v-icon>
              {{ item.salida_1 }}
            </span>
          </template>

          <!-- HORAS TRABAJADAS -->
          <template v-slot:item.horas_trabajadas="{ item }">
            <span class="font-weight-bold">{{ item.horas_trabajadas }} hrs</span>
          </template>

          <!-- MINUTOS DE ATRASO -->
          <template v-slot:item.minutos_atraso="{ item }">
            <span v-if="item.minutos_atraso > 0" class="error--text font-weight-black">
              {{ item.minutos_atraso }} min
            </span>
            <span v-else class="success--text font-weight-medium">0 min</span>
          </template>

          <!-- ESTADO INSTITUCIONAL -->
          <template v-slot:item.estado="{ item }">
            <v-chip small :color="getColorEstado(item.estado)" label class="font-weight-bold">
              {{ item.estado }}
            </v-chip>
          </template>

          <!-- REFRIGERIO -->
          <template v-slot:item.merece_refrigerio="{ item }">
            <v-chip
              x-small
              :color="item.merece_refrigerio ? 'green lighten-5' : 'grey lighten-3'"
              :text-color="item.merece_refrigerio ? 'green darken-3' : 'grey darken-1'"
              label
              class="font-weight-bold"
            >
              <v-icon left x-small :color="item.merece_refrigerio ? 'green darken-3' : 'grey darken-1'">
                {{ item.merece_refrigerio ? 'mdi-check-circle' : 'mdi-close-circle' }}
              </v-icon>
              {{ item.merece_refrigerio ? 'HABILITADO (Bs 18)' : 'NO APLICA' }}
            </v-chip>
          </template>
        </v-data-table>
      </div>
    </v-card>

    <!-- DIÁLOGO: PROCESAR / CALCULAR ASISTENCIA DEL DÍA -->
    <v-dialog v-model="dialogCalcular" max-width="440" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calculator</v-icon>
          <span>Procesar Asistencia y Marcaciones</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCalcular = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pa-4">
          <p class="text-caption text-secondary mb-3">
            Esta acción consolidará las marcaciones registradas por los relojes biométricos, calculará tolerancias de entrada (08:30), minutos de atraso y asignará refrigerios diarios para todo el personal.
          </p>
          <v-text-field
            v-model="fechaCalculo"
            type="date"
            label="Fecha a Procesar *"
            outlined
            dense
            prepend-inner-icon="mdi-calendar"
          ></v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogCalcular = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold px-4"
            :loading="ejecutandoCalculo"
            @click="ejecutarCalculo"
          >
            <v-icon left small>mdi-play</v-icon> Iniciar Proceso
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: MARCAR ASISTENCIA MASIVA / REGULARIZAR LIBRO -->
    <v-dialog v-model="dialogMarcarMasivo" max-width="620" persistent>
      <v-card rounded="lg">
        <v-card-title class="success darken-1 white--text py-3">
          <v-icon left color="white">mdi-account-check</v-icon>
          <span>Registro / Regularización Masiva de Asistencia</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogMarcarMasivo = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-tabs v-model="tabMasivo" color="success darken-1" class="px-4 pt-2">
          <v-tab><v-icon left small>mdi-calendar-today</v-icon> Marcación por Fecha</v-tab>
          <v-tab><v-icon left small>mdi-book-open-page-variant</v-icon> Asistencia Mensual (Libro)</v-tab>
        </v-tabs>
        <v-divider></v-divider>

        <v-tabs-items v-model="tabMasivo" class="pa-4">
          <!-- SUB-TAB 0: MARCACIÓN POR FECHA -->
          <v-tab-item>
            <v-alert dense outlined type="info" class="text-caption mb-3">
              Permite registrar la asistencia de uno, dos o muchos funcionarios para una fecha en base al libro de firmas.
            </v-alert>

            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-caption font-weight-bold">Funcionarios a marcar:</span>
              <div>
                <v-btn x-small text color="primary" @click="seleccionarTodosDiario">Seleccionar Todos</v-btn>
                <v-btn x-small text color="grey" @click="formMasivoDiario.personas_ids = []">Limpiar</v-btn>
              </div>
            </div>

            <v-autocomplete
              v-model="formMasivoDiario.personas_ids"
              :items="catalogoPersonal"
              item-text="nombre_completo"
              item-value="id"
              label="Seleccionar Funcionarios *"
              multiple
              chips
              small-chips
              deletable-chips
              outlined
              dense
              prepend-inner-icon="mdi-account-multiple"
            ></v-autocomplete>

            <v-row dense class="mt-1">
              <v-col cols="12" sm="6">
                <v-text-field v-model="formMasivoDiario.fecha" label="Fecha *" type="date" outlined dense></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="formMasivoDiario.estado"
                  :items="['PRESENTE', 'ATRASO', 'FALTA', 'PERMISO', 'COMISION']"
                  label="Estado Asistencia *"
                  outlined
                  dense
                ></v-select>
              </v-col>
              <v-col cols="6" sm="3">
                <v-text-field v-model="formMasivoDiario.entrada_1" label="Entrada" type="time" outlined dense></v-text-field>
              </v-col>
              <v-col cols="6" sm="3">
                <v-text-field v-model="formMasivoDiario.salida_1" label="Salida" type="time" outlined dense></v-text-field>
              </v-col>
              <v-col cols="6" sm="3">
                <v-text-field v-model.number="formMasivoDiario.minutos_atraso" label="Atraso (min)" type="number" outlined dense></v-text-field>
              </v-col>
              <v-col cols="6" sm="3">
                <v-checkbox v-model="formMasivoDiario.merece_refrigerio" label="Refrigerio" dense hide-details class="mt-2"></v-checkbox>
              </v-col>
            </v-row>

            <div class="d-flex justify-end mt-4">
              <v-btn text color="grey darken-1" class="rounded-pill mr-2" @click="dialogMarcarMasivo = false">Cancelar</v-btn>
              <v-btn color="success darken-1" dark class="rounded-pill font-weight-bold px-4" :loading="guardandoMasivo" @click="guardarMarcacionDiaria">
                <v-icon left small>mdi-check-all</v-icon> Registrar Marcación
              </v-btn>
            </div>
          </v-tab-item>

          <!-- SUB-TAB 1: ASISTENCIA MENSUAL (LIBRO DE FIRMAS / EXCEL) -->
          <v-tab-item>
            <v-alert dense outlined type="success" class="text-caption mb-3">
              Genera los registros de asistencia para todos los días hábiles del mes seleccionado (lunes a viernes). Esto permite alimentar la base de datos de asistencia a partir del libro de firmas para respaldar la planilla mensual oficial.
            </v-alert>

            <v-row dense>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="formMasivoMensual.mes"
                  :items="meses"
                  item-text="nombre"
                  item-value="id"
                  label="Mes de Asistencia *"
                  outlined
                  dense
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model.number="formMasivoMensual.anio" label="Gestión *" type="number" outlined dense></v-text-field>
              </v-col>
            </v-row>

            <div class="d-flex align-center justify-space-between mb-2 mt-2">
              <span class="text-caption font-weight-bold">Funcionarios a procesar:</span>
              <div>
                <v-btn x-small text color="primary" @click="seleccionarTodosMensual">Todos ({{ catalogoPersonal.length }})</v-btn>
                <v-btn x-small text color="grey" @click="formMasivoMensual.personas_ids = []">Limpiar</v-btn>
              </div>
            </div>

            <v-autocomplete
              v-model="formMasivoMensual.personas_ids"
              :items="catalogoPersonal"
              item-text="nombre_completo"
              item-value="id"
              label="Funcionarios *"
              multiple
              chips
              small-chips
              deletable-chips
              outlined
              dense
              prepend-inner-icon="mdi-account-multiple"
              hint="Por defecto se aplica a todo el personal institucional"
              persistent-hint
            ></v-autocomplete>

            <v-row dense class="mt-3">
              <v-col cols="6">
                <v-text-field v-model="formMasivoMensual.hora_entrada" label="Hora de Entrada Estándar" type="time" outlined dense></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field v-model="formMasivoMensual.hora_salida" label="Hora de Salida Estándar" type="time" outlined dense></v-text-field>
              </v-col>
            </v-row>

            <div class="d-flex justify-end mt-4">
              <v-btn text color="grey darken-1" class="rounded-pill mr-2" @click="dialogMarcarMasivo = false">Cancelar</v-btn>
              <v-btn color="success darken-1" dark class="rounded-pill font-weight-bold px-4" :loading="guardandoMasivo" @click="guardarAsistenciaMensual">
                <v-icon left small>mdi-file-check</v-icon> Registrar Mes Completo
              </v-btn>
            </div>
          </v-tab-item>
        </v-tabs-items>
      </v-card>
    </v-dialog>

    <!-- MODAL VISOR PDF OFICIAL (Igual que Facturación y Reportes) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :nombre-descarga="nombreDescargaPdf"
      max-width="1150px"
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
import { isSuperAdmin, can, hasRole } from '@/utils/helpers';

export default {
  name: 'ControlAsistencia',
  components: {
    ModalVisorPdf,
  },
  data() {
    const ahora = new Date();
    return {
      // Visor PDF Oficial
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      nombreDescargaPdf: 'reporte_asistencia.pdf',

      esAdmin: false,
      modoVista: 'diario', // 'diario' | 'mensual' | 'personal'
      filtroFecha: ahora.toISOString().substr(0, 10),
      menuFecha: false,
      filtroMes: ahora.getMonth() + 1,
      filtroAnio: ahora.getFullYear(),
      filtroIdPersona: null,
      busquedaTexto: '',

      asistencias: [],
      loadingAsistencias: false,
      catalogoPersonal: [],

      totales: {
        total_registros: 0,
        presentes: 0,
        atrasos: 0,
        faltas: 0,
        total_minutos_atraso: 0,
        refrigerios_habilitados: 0,
        monto_refrigerio_bs: 0,
      },

      dialogCalcular: false,
      fechaCalculo: ahora.toISOString().substr(0, 10),
      ejecutandoCalculo: false,

      dialogMarcarMasivo: false,
      tabMasivo: 0,
      guardandoMasivo: false,
      formMasivoDiario: {
        fecha: ahora.toISOString().substr(0, 10),
        personas_ids: [],
        estado: 'PRESENTE',
        entrada_1: '08:30',
        salida_1: '16:30',
        minutos_atraso: 0,
        merece_refrigerio: true,
      },
      formMasivoMensual: {
        mes: ahora.getMonth() + 1,
        anio: ahora.getFullYear(),
        personas_ids: [],
        hora_entrada: '08:30',
        hora_salida: '16:30',
      },

      meses: [
        { id: 1, nombre: 'Enero' }, { id: 2, nombre: 'Febrero' }, { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' }, { id: 5, nombre: 'Mayo' }, { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' }, { id: 8, nombre: 'Agosto' }, { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' }, { id: 11, nombre: 'Noviembre' }, { id: 12, nombre: 'Diciembre' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    headersComputados() {
      // Si la consulta es por día (muchos funcionarios en 1 día)
      if (this.modoVista === 'diario') {
        return [
          { text: 'Funcionario', value: 'funcionario' },
          { text: 'Unidad Organizacional', value: 'unidad' },
          { text: 'Entrada 1', value: 'entrada_1' },
          { text: 'Salida 1', value: 'salida_1' },
          { text: 'Horas Trab.', value: 'horas_trabajadas', align: 'center' },
          { text: 'Atraso', value: 'minutos_atraso', align: 'center' },
          { text: 'Estado', value: 'estado', align: 'center' },
          { text: 'Refrigerio', value: 'merece_refrigerio', align: 'center' },
        ];
      }

      // Si la consulta es mensual / historial (muchos días de 1 o varios funcionarios)
      return [
        { text: 'Fecha', value: 'fecha' },
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Entrada 1', value: 'entrada_1' },
        { text: 'Salida 1', value: 'salida_1' },
        { text: 'Horas Trab.', value: 'horas_trabajadas', align: 'center' },
        { text: 'Atraso', value: 'minutos_atraso', align: 'center' },
        { text: 'Estado', value: 'estado', align: 'center' },
        { text: 'Refrigerio', value: 'merece_refrigerio', align: 'center' },
      ];
    },

    asistenciasFiltradas() {
      if (!this.busquedaTexto) return this.asistencias;
      const q = this.busquedaTexto.toLowerCase();
      return this.asistencias.filter(item => {
        return (
          (item.funcionario && item.funcionario.toLowerCase().includes(q)) ||
          (item.ci && item.ci.toLowerCase().includes(q)) ||
          (item.cargo && item.cargo.toLowerCase().includes(q)) ||
          (item.unidad && item.unidad.toLowerCase().includes(q))
        );
      });
    },
  },
  mounted() {
    this.determinarPermisos();
    this.cargarCatalogoPersonal();
    this.cargarAsistencias();
  },
  methods: {
    determinarPermisos() {
      // Determinar si es Administrador o Encargado de RRHH
      this.esAdmin = isSuperAdmin() || can('rrhh.asistencias.ver_todos') || can('rrhh.asistencias.administrar') || hasRole('rrhh');
      if (!this.esAdmin) {
        this.modoVista = 'personal';
      }
    },

    cambiarModoVista(nuevoModo) {
      this.modoVista = nuevoModo;
      this.cargarAsistencias();
    },

    cargarCatalogoPersonal() {
      if (!this.esAdmin) return;
      axios.get('/api/rrhh/personal')
        .then(res => {
          if (res.data && res.data.success) {
            this.catalogoPersonal = res.data.data || [];
          }
        })
        .catch(() => {});
    },

    cargarAsistencias() {
      this.loadingAsistencias = true;

      const params = {
        tipo_vista: this.modoVista,
      };

      if (this.modoVista === 'diario') {
        params.fecha = this.filtroFecha;
      } else {
        params.mes = this.filtroMes;
        params.anio = this.filtroAnio;
        if (this.esAdmin && this.filtroIdPersona) {
          params.id_persona = this.filtroIdPersona;
        }
      }

      axios.get('/api/rrhh/asistencias', { params })
        .then(res => {
          if (res.data && res.data.success) {
            this.asistencias = res.data.data || [];
            if (res.data.totales) {
              this.totales = res.data.totales;
            }
            if (res.data.es_admin !== undefined) {
              this.esAdmin = res.data.es_admin;
            }
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar asistencias.', 'error');
        })
        .finally(() => {
          this.loadingAsistencias = false;
        });
    },

    ejecutarCalculo() {
      this.ejecutandoCalculo = true;
      axios.post('/api/rrhh/asistencias/calcular', { fecha: this.fechaCalculo })
        .then(res => {
          this.dialogCalcular = false;
          this.showSnackbar(res.data.message || 'Cálculo de asistencias procesado correctamente.', 'success');
          this.filtroFecha = this.fechaCalculo;
          this.modoVista = 'diario';
          this.cargarAsistencias();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al procesar asistencia.', 'error');
        })
        .finally(() => {
          this.ejecutandoCalculo = false;
        });
    },

    getColorEstado(estado) {
      switch (estado) {
        case 'PRESENTE': return 'success lighten-5 green--text text--darken-3';
        case 'ATRASO': return 'warning lighten-5 orange--text text--darken-3';
        case 'FALTA': return 'error lighten-5 red--text text--darken-2';
        case 'PERMISO': return 'info lighten-5 blue--text text--darken-2';
        case 'COMISION': return 'purple lighten-5 purple--text';
        default: return 'grey lighten-4';
      }
    },

    imprimirReporte() {
      const params = new URLSearchParams();
      params.append('tipo', this.modoVista);
      if (this.modoVista === 'diario') {
        params.append('fecha', this.filtroFecha);
        this.tituloVisorPdf = `Reporte Diario de Asistencia (${this.filtroFecha})`;
        this.nombreDescargaPdf = `asistencia_diaria_${this.filtroFecha}.pdf`;
      } else {
        params.append('mes', this.filtroMes);
        params.append('anio', this.filtroAnio);
        if (this.filtroIdPersona) {
          params.append('id_persona', this.filtroIdPersona);
        }
        this.tituloVisorPdf = `Reporte Mensual de Asistencia (${this.filtroMes}/${this.filtroAnio})`;
        this.nombreDescargaPdf = `asistencia_mensual_${this.filtroAnio}_${this.filtroMes}.pdf`;
      }
      this.urlVisorPdf = `/api/rrhh/asistencias/pdf?${params.toString()}`;
      this.subtituloVisorPdf = 'Empresa Municipal de Agua Potable y Alcantarillado - EMAPAP';
      this.mostrarVisorPdf = true;
    },

    exportarCsv() {
      if (!this.asistenciasFiltradas.length) {
        this.showSnackbar('No hay registros para exportar.', 'warning');
        return;
      }

      let csv = 'Fecha;Dia;Funcionario;CI;Cargo;Unidad;Entrada 1;Salida 1;Horas Trabajadas;Atraso (Min);Estado;Refrigerio;Refrigerio (Bs)\n';
      this.asistenciasFiltradas.forEach(row => {
        csv += `"${row.fecha}";"${row.dia}";"${row.funcionario}";"${row.ci}";"${row.cargo}";"${row.unidad}";"${row.entrada_1}";"${row.salida_1}";"${row.horas_trabajadas}";"${row.minutos_atraso}";"${row.estado}";"${row.merece_refrigerio ? 'SI' : 'NO'}";"${row.refrigerio_bs}"\n`;
      });

      const blob = new Blob(["\ufeff" + csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', `asistencias_${this.modoVista}_${this.filtroFecha || (this.filtroMes + '_' + this.filtroAnio)}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      this.showSnackbar('Archivo CSV exportado exitosamente.', 'success');
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },

    abrirModalMarcarMasivo() {
      this.formMasivoDiario.fecha = this.filtroFecha;
      this.formMasivoMensual.mes = this.filtroMes;
      this.formMasivoMensual.anio = this.filtroAnio;
      this.formMasivoMensual.personas_ids = this.catalogoPersonal.map(p => p.id);
      this.dialogMarcarMasivo = true;
    },

    seleccionarTodosDiario() {
      this.formMasivoDiario.personas_ids = this.catalogoPersonal.map(p => p.id);
    },

    seleccionarTodosMensual() {
      this.formMasivoMensual.personas_ids = this.catalogoPersonal.map(p => p.id);
    },

    guardarMarcacionDiaria() {
      if (!this.formMasivoDiario.personas_ids.length || !this.formMasivoDiario.fecha) {
        this.showSnackbar('Seleccione al menos un funcionario y la fecha.', 'warning');
        return;
      }
      this.guardandoMasivo = true;
      axios.post('/api/rrhh/asistencias/marcar-masivo', this.formMasivoDiario)
        .then(res => {
          this.showSnackbar(res.data.message || 'Marcación diaria registrada exitosamente.', 'success');
          this.dialogMarcarMasivo = false;
          this.filtroFecha = this.formMasivoDiario.fecha;
          this.modoVista = 'diario';
          this.cargarAsistencias();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al registrar marcación.', 'error');
        })
        .finally(() => {
          this.guardandoMasivo = false;
        });
    },

    guardarAsistenciaMensual() {
      if (!this.formMasivoMensual.mes || !this.formMasivoMensual.anio) {
        this.showSnackbar('Seleccione el mes y la gestión.', 'warning');
        return;
      }
      this.guardandoMasivo = true;
      axios.post('/api/rrhh/asistencias/marcar-mes-completo', this.formMasivoMensual)
        .then(res => {
          this.showSnackbar(res.data.message || 'Asistencia mensual procesada correctamente.', 'success');
          this.dialogMarcarMasivo = false;
          this.filtroMes = this.formMasivoMensual.mes;
          this.filtroAnio = this.formMasivoMensual.anio;
          this.modoVista = 'mensual';
          this.cargarAsistencias();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al regularizar asistencia mensual.', 'error');
        })
        .finally(() => {
          this.guardandoMasivo = false;
        });
    },
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #asistencias-print-area, #asistencias-print-area * {
    visibility: visible;
  }
  #asistencias-print-area {
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
.border-b {
  border-bottom: 1px solid #e2e8f0;
}
</style>
