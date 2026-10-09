<template>
  <div>
    <!-- CABECERA INSTITUCIONAL UNIFICADA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-calculator-variant</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Parámetros Laborales y Salariales</h2>
            <span class="text-caption text-secondary">
              Configuración institucional unificada: Salario Mínimo, Gestora Laboral (12.71%), Cargas Patronales (17.21%) y Escala de Antigüedad
            </span>
          </div>
        </div>

        <!-- BOTONES DE ACCIÓN PRINCIPALES -->
        <div class="d-flex align-center gap-2">
          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold text-capitalize px-5 elevation-1"
            :loading="guardando"
            @click="guardarParametros"
          >
            <v-icon left small>mdi-content-save</v-icon> Guardar Configuración
          </v-btn>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon color="primary" v-bind="attrs" v-on="on" @click="cargarParametros" :loading="loading">
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Recargar parámetros guardados</span>
          </v-tooltip>
        </div>
      </div>
    </v-card>

    <!-- LIENZO UNIFICADO CONTINUO (TODO EN UNA SOLA VISTA SIN SEPARACIONES) -->
    <div class="unified-workspace">
      <!-- BLOQUE 1: REGISTRO PATRONAL E IDENTIFICACIÓN INSTITUCIONAL -->
      <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
        <div class="d-flex align-center justify-space-between pb-2 border-b mb-3">
          <div class="d-flex align-center">
            <v-icon color="primary" small class="mr-2">mdi-domain</v-icon>
            <span class="font-weight-bold text-subtitle-2 text-slate-800">
              1. Registro Patronal e Identificación Institucional
            </span>
          </div>
          <span class="text-caption text-secondary">Identificación oficial para membretes de planillas y boletas (Min. Trabajo, C.N.S. y NIT)</span>
        </div>

        <v-row dense>
          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="form.nro_patronal_min_trabajo"
              label="Nº Empleador Min. de Trabajo (ROE) *"
              outlined
              dense
              prepend-inner-icon="mdi-briefcase-outline"
              hint="Registro Obligatorio de Empleadores"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="form.nro_patronal_cns"
              label="Nº Empleador C.N.S. Patronal *"
              outlined
              dense
              prepend-inner-icon="mdi-hospital-box-outline"
              hint="Caja Nacional de Salud"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="form.nit_institucional"
              label="NIT Institucional *"
              outlined
              dense
              prepend-inner-icon="mdi-card-bulleted-outline"
              hint="Número de Identificación Tributaria"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="6" class="mt-2">
            <v-text-field
              v-model="form.ubicacion_geografica"
              label="Municipio / Departamento *"
              outlined
              dense
              prepend-inner-icon="mdi-city-variant-outline"
              hint="Ej: PATACAMAYA-LA PAZ-BOLIVIA"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="6" class="mt-2">
            <v-text-field
              v-model="form.direccion_institucional"
              label="Dirección Oficial *"
              outlined
              dense
              prepend-inner-icon="mdi-map-marker-outline"
              hint="Ej: PLAZA BOLIVAR - ZONA ESTACION"
              persistent-hint
            ></v-text-field>
          </v-col>
        </v-row>
      </v-card>

      <!-- BLOQUE 2: SALARIO MÍNIMO NACIONAL, JORNADA Y REFRIGERIO -->
      <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
        <div class="d-flex align-center justify-space-between pb-2 border-b mb-3">
          <div class="d-flex align-center">
            <v-icon color="primary" small class="mr-2">mdi-cash-multiple</v-icon>
            <span class="font-weight-bold text-subtitle-2 text-slate-800">
              2. Salario Mínimo Nacional, Jornada y Refrigerio
            </span>
          </div>
          <span class="text-caption text-secondary">Base para cálculo salarial y asistencia</span>
        </div>

        <v-row dense>
          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model.number="form.salario_minimo_nacional"
              label="Salario Mínimo Nacional (SMN) *"
              prefix="Bs."
              type="number"
              step="10"
              outlined
              dense
              hint="Monto oficial según Decreto Supremo"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model.number="form.monto_refrigerio_diario"
              label="Monto Refrigerio Diario *"
              prefix="Bs."
              type="number"
              step="1"
              outlined
              dense
              hint="Por día de asistencia efectiva"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model.number="form.dias_laborables_mes"
              label="Días Laborables Mes Estándar *"
              suffix="días"
              type="number"
              outlined
              dense
              hint="Base mensual de cómputo (estándar 30)"
              persistent-hint
            ></v-text-field>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model.number="form.horas_jornada_ordinaria"
              label="Horas de Jornada Diaria *"
              suffix="hrs"
              type="number"
              outlined
              dense
              hint="Jornada ordinaria (8 horas)"
              persistent-hint
            ></v-text-field>
          </v-col>
        </v-row>

        <!-- INDICADOR EN VIVO DE BASE DE CÁLCULO DE 3 SMN -->
        <div class="d-flex align-center justify-space-between flex-wrap gap-2 pa-2 mt-3 bg-light rounded border text-caption">
          <div class="d-flex align-center">
            <v-icon x-small color="primary" class="mr-1">mdi-calculator</v-icon>
            <span>Base legal para cálculo del Bono de Antigüedad (3 SMN según D.S. 21060 Art. 60):</span>
          </div>
          <strong class="primary--text">
            3 × Bs. {{ Number(form.salario_minimo_nacional || 0).toFixed(2) }} = Bs. {{ (Number(form.salario_minimo_nacional || 0) * 3).toFixed(2) }}
          </strong>
        </div>
      </v-card>

      <!-- BLOQUE 2: TASAS PREVISIONALES (GESTORA 12.71% Y PATRONALES 17.21% EN PARALELO) -->
      <v-row dense class="mb-4">
        <!-- COLUMNA IZQUIERDA: GESTORA LABORAL (12.71%) -->
        <v-col cols="12" md="6">
          <v-card rounded="lg" class="pa-4 h-100 d-flex flex-column erp-card-elevated">
            <div class="d-flex align-center justify-space-between pb-2 border-b mb-3">
              <div class="d-flex align-center">
                <v-icon color="indigo" small class="mr-2">mdi-shield-account</v-icon>
                <span class="font-weight-bold text-subtitle-2 text-slate-800">
                  3. Retenciones Gestora Laboral
                </span>
              </div>
              <v-chip small color="indigo lighten-5 indigo--text" class="font-weight-bold">
                Total: {{ totalGestoraLaboral }}%
              </v-chip>
            </div>

            <p class="text-caption text-secondary mb-3">
              Deducción de ley efectuada sobre el Total Ganado de cada trabajador:
            </p>

            <v-row dense class="flex-grow-1">
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="form.gestora_vejez_porcentaje"
                  label="Fondo de Vejez *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Cuenta individual (10.00%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="form.gestora_riesgo_comun_porcentaje"
                  label="Riesgo Común *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Seguro de invalidez (1.71%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="mt-2">
                <v-text-field
                  v-model.number="form.gestora_comision_porcentaje"
                  label="Comisión Gestora *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Comisión gestora (0.50%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="mt-2">
                <v-text-field
                  v-model.number="form.gestora_laboral_solidario_porcentaje"
                  label="Aporte Solidario Laboral *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Fondo solidario (0.50%)"
                  persistent-hint
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card>
        </v-col>

        <!-- COLUMNA DERECHA: APORTES PATRONALES (17.21%) -->
        <v-col cols="12" md="6">
          <v-card rounded="lg" class="pa-4 h-100 d-flex flex-column erp-card-elevated">
            <div class="d-flex align-center justify-space-between pb-2 border-b mb-3">
              <div class="d-flex align-center">
                <v-icon color="purple darken-2" small class="mr-2">mdi-domain</v-icon>
                <span class="font-weight-bold text-subtitle-2 text-slate-800">
                  4. Aportes Patronales (Cargas Sociales EMAPAP)
                </span>
              </div>
              <v-chip small color="purple lighten-5 purple--text text--darken-2" class="font-weight-bold">
                Total: {{ totalAportesPatronales }}%
              </v-chip>
            </div>

            <p class="text-caption text-secondary mb-3">
              Obligaciones sociales asumidas íntegramente por EMAPAP sobre la planilla:
            </p>

            <v-row dense class="flex-grow-1">
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="form.patronal_cns_porcentaje"
                  label="Caja Nacional de Salud (CNS) *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Salud corto plazo (10.00%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="form.patronal_riesgo_profesional_porcentaje"
                  label="Riesgo Profesional Patronal *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Accidentes de trabajo (1.71%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="mt-2">
                <v-text-field
                  v-model.number="form.patronal_pro_vivienda_porcentaje"
                  label="Fondo Pro-Vivienda *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Aporte pro-vivienda (2.00%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="mt-2">
                <v-text-field
                  v-model.number="form.patronal_solidario_porcentaje"
                  label="Aporte Solidario Patronal *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Fondo solidario empresa (3.00%)"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="12" class="mt-2">
                <v-text-field
                  v-model.number="form.patronal_comision_porcentaje"
                  label="Comisión Gestora Patronal *"
                  suffix="%"
                  type="number"
                  step="0.01"
                  outlined
                  dense
                  hint="Comisión previsional Gestora Pública (0.50%)"
                  persistent-hint
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <!-- BLOQUE 3: ESCALA LEGAL DEL BONO DE ANTIGÜEDAD -->
      <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
        <div class="d-flex align-center justify-space-between pb-2 border-b mb-3 flex-wrap gap-2">
          <div class="d-flex align-center">
            <v-icon color="amber darken-3" small class="mr-2">mdi-stairs</v-icon>
            <div>
              <span class="font-weight-bold text-subtitle-2 text-slate-800">
                5. Escala de Porcentajes del Bono de Antigüedad (D.S. 21060 Art. 60)
              </span>
              <span class="text-caption text-secondary ml-2 d-none d-sm-inline">
                Porcentajes aplicados sobre 3 SMN (Bs. {{ (Number(form.salario_minimo_nacional || 0) * 3).toFixed(2) }})
              </span>
            </div>
          </div>

          <v-btn small color="primary" outlined class="rounded-pill font-weight-medium text-capitalize" @click="agregarTramoAntiguedad">
            <v-icon left small>mdi-plus</v-icon> Añadir Tramo
          </v-btn>
        </div>

        <v-simple-table dense class="border rounded overflow-hidden">
          <thead>
            <tr class="grey lighten-4">
              <th class="font-weight-bold">Años Desde</th>
              <th class="font-weight-bold">Años Hasta</th>
              <th class="font-weight-bold">Porcentaje Legal (%)</th>
              <th class="font-weight-bold">Bono Computado en Bolivianos</th>
              <th class="text-center font-weight-bold">Eliminar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(t, idx) in form.escala_antiguedad" :key="idx">
              <td style="width: 140px;">
                <v-text-field v-model.number="t.anios_min" type="number" dense hide-details outlined></v-text-field>
              </td>
              <td style="width: 140px;">
                <v-text-field v-model.number="t.anios_max" type="number" dense hide-details outlined></v-text-field>
              </td>
              <td style="width: 150px;">
                <v-text-field v-model.number="t.porcentaje" type="number" step="0.5" suffix="%" dense hide-details outlined></v-text-field>
              </td>
              <td class="font-weight-bold primary--text">
                Bs. {{ ((Number(form.salario_minimo_nacional || 0) * 3) * (Number(t.porcentaje || 0) / 100)).toFixed(2) }}
              </td>
              <td class="text-center" style="width: 70px;">
                <v-btn icon small color="error" @click="eliminarTramoAntiguedad(idx)">
                  <v-icon small>mdi-delete</v-icon>
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card>

      <!-- BLOQUE 4: RESPALDO NORMATIVO Y TRAZABILIDAD AUDITABLE -->
      <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
        <div class="d-flex align-center pb-2 border-b mb-3">
          <v-icon color="teal" small class="mr-2">mdi-shield-lock-outline</v-icon>
          <span class="font-weight-bold text-subtitle-2 text-slate-800">
            6. Respaldo Normativo y Registro de Auditoría
          </span>
        </div>

        <v-textarea
          v-model="form.notas_resolucion"
          label="Decreto Supremo / Resolución Administrativa de Aprobación *"
          outlined
          dense
          rows="2"
          placeholder="Ej: Aprobado conforme Decreto Supremo vigente de incremento salarial para la gestión 2026."
          hint="Este justificativo queda registrado en el historial de auditoría de RRHH para fiscalización de la Contraloría."
          persistent-hint
        ></v-textarea>
      </v-card>

      <!-- BOTÓN DE GUARDADO FINAL -->
      <div class="d-flex justify-end pt-2 pb-4">
        <v-btn
          color="primary"
          class="rounded-pill font-weight-bold text-capitalize px-6 elevation-1"
          :loading="guardando"
          @click="guardarParametros"
        >
          <v-icon left small>mdi-content-save</v-icon> Guardar Toda la Configuración Laboral
        </v-btn>
      </div>
    </div>

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
  name: 'CalculosLaboralesRrhh',
  data() {
    return {
      loading: false,
      guardando: false,

      form: {
        nro_patronal_min_trabajo: '1002393029-1',
        nro_patronal_cns: '01-521-00002',
        nit_institucional: '1002393029',
        ubicacion_geografica: 'PATACAMAYA-LA PAZ-BOLIVIA',
        direccion_institucional: 'PLAZA BOLIVAR - ZONA ESTACION',

        salario_minimo_nacional: 3300.00,
        monto_refrigerio_diario: 18.00,
        dias_laborables_mes: 30,
        horas_jornada_ordinaria: 8,
        factor_horas_extra: 2.0,

        gestora_vejez_porcentaje: 10.0,
        gestora_riesgo_comun_porcentaje: 1.71,
        gestora_comision_porcentaje: 0.50,
        gestora_laboral_solidario_porcentaje: 0.50,

        patronal_cns_porcentaje: 10.0,
        patronal_riesgo_profesional_porcentaje: 1.71,
        patronal_pro_vivienda_porcentaje: 2.0,
        patronal_solidario_porcentaje: 3.0,
        patronal_comision_porcentaje: 0.50,

        escala_antiguedad: [
          { anios_min: 2, anios_max: 4, porcentaje: 5 },
          { anios_min: 5, anios_max: 7, porcentaje: 11 },
          { anios_min: 8, anios_max: 10, porcentaje: 18 },
          { anios_min: 11, anios_max: 14, porcentaje: 26 },
          { anios_min: 15, anios_max: 19, porcentaje: 34 },
          { anios_min: 20, anios_max: 24, porcentaje: 42 },
          { anios_min: 25, anios_max: 50, porcentaje: 50 },
        ],
        notas_resolucion: '',
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    totalGestoraLaboral() {
      const v = Number(this.form.gestora_vejez_porcentaje || 0);
      const r = Number(this.form.gestora_riesgo_comun_porcentaje || 0);
      const c = Number(this.form.gestora_comision_porcentaje || 0);
      const s = Number(this.form.gestora_laboral_solidario_porcentaje || 0);
      return Number(v + r + c + s).toFixed(2);
    },

    totalAportesPatronales() {
      const cns = Number(this.form.patronal_cns_porcentaje || 0);
      const rp = Number(this.form.patronal_riesgo_profesional_porcentaje || 0);
      const pv = Number(this.form.patronal_pro_vivienda_porcentaje || 0);
      const sp = Number(this.form.patronal_solidario_porcentaje || 0);
      const com = Number(this.form.patronal_comision_porcentaje || 0);
      return Number(cns + rp + pv + sp + com).toFixed(2);
    },
  },
  mounted() {
    this.cargarParametros();
  },
  methods: {
    cargarParametros() {
      this.loading = true;
      axios.get('/api/rrhh/configuracion-laboral')
        .then(res => {
          if (res.data && res.data.success && res.data.data) {
            const d = res.data.data;
            this.form = {
              nro_patronal_min_trabajo: d.nro_patronal_min_trabajo || '1002393029-1',
              nro_patronal_cns: d.nro_patronal_cns || '01-521-00002',
              nit_institucional: d.nit_institucional || '1002393029',
              ubicacion_geografica: d.ubicacion_geografica || 'PATACAMAYA-LA PAZ-BOLIVIA',
              direccion_institucional: d.direccion_institucional || 'PLAZA BOLIVAR - ZONA ESTACION',

              salario_minimo_nacional: Number(d.salario_minimo_nacional || 3300),
              monto_refrigerio_diario: Number(d.tarifa_refrigerio_diaria ?? d.monto_refrigerio_diario ?? 18),
              dias_laborables_mes: Number(d.dias_laborales_base ?? d.dias_laborables_mes ?? 30),
              horas_jornada_ordinaria: Number(d.horas_jornada_diaria ?? d.horas_jornada_ordinaria ?? 8),
              factor_horas_extra: Number(d.factor_hora_extra ?? d.factor_horas_extra ?? 2.0),

              gestora_vejez_porcentaje: d.porcentaje_gestora_vejez > 1 ? Number(d.porcentaje_gestora_vejez) : Number((d.porcentaje_gestora_vejez * 100) || (d.gestora_vejez_porcentaje > 1 ? d.gestora_vejez_porcentaje : (d.gestora_vejez_porcentaje * 100)) || 10),
              gestora_riesgo_comun_porcentaje: d.porcentaje_gestora_riesgo_comun > 1 ? Number(d.porcentaje_gestora_riesgo_comun) : Number((d.porcentaje_gestora_riesgo_comun * 100) || (d.gestora_riesgo_comun_porcentaje > 1 ? d.gestora_riesgo_comun_porcentaje : (d.gestora_riesgo_comun_porcentaje * 100)) || 1.71),
              gestora_comision_porcentaje: d.porcentaje_gestora_comision > 1 ? Number(d.porcentaje_gestora_comision) : Number((d.porcentaje_gestora_comision * 100) || (d.gestora_comision_porcentaje > 1 ? d.gestora_comision_porcentaje : (d.gestora_comision_porcentaje * 100)) || 0.50),
              gestora_laboral_solidario_porcentaje: d.porcentaje_gestora_solidario > 1 ? Number(d.porcentaje_gestora_solidario) : Number((d.porcentaje_gestora_solidario * 100) || (d.gestora_laboral_solidario_porcentaje > 1 ? d.gestora_laboral_solidario_porcentaje : (d.gestora_laboral_solidario_porcentaje * 100)) || 0.50),

              patronal_cns_porcentaje: d.porcentaje_patronal_cns > 1 ? Number(d.porcentaje_patronal_cns) : Number((d.porcentaje_patronal_cns * 100) || (d.patronal_cns_porcentaje > 1 ? d.patronal_cns_porcentaje : (d.patronal_cns_porcentaje * 100)) || 10),
              patronal_riesgo_profesional_porcentaje: d.porcentaje_patronal_gestora_riesgo > 1 ? Number(d.porcentaje_patronal_gestora_riesgo) : Number((d.porcentaje_patronal_gestora_riesgo * 100) || (d.patronal_riesgo_profesional_porcentaje > 1 ? d.patronal_riesgo_profesional_porcentaje : (d.patronal_riesgo_profesional_porcentaje * 100)) || 1.71),
              patronal_pro_vivienda_porcentaje: d.porcentaje_patronal_gestora_pro_vivienda > 1 ? Number(d.porcentaje_patronal_gestora_pro_vivienda) : Number((d.porcentaje_patronal_gestora_pro_vivienda * 100) || (d.patronal_pro_vivienda_porcentaje > 1 ? d.patronal_pro_vivienda_porcentaje : (d.patronal_pro_vivienda_porcentaje * 100)) || 2.0),
              patronal_solidario_porcentaje: d.porcentaje_patronal_gestora_sol > 1 ? Number(d.porcentaje_patronal_gestora_sol) : Number((d.porcentaje_patronal_gestora_sol * 100) || (d.patronal_solidario_porcentaje > 1 ? d.patronal_solidario_porcentaje : (d.patronal_solidario_porcentaje * 100)) || 3.0),
              patronal_comision_porcentaje: d.porcentaje_patronal_gestora_comision ? Number(d.porcentaje_patronal_gestora_comision > 1 ? d.porcentaje_patronal_gestora_comision : (d.porcentaje_patronal_gestora_comision * 100)) : 0.50,

              escala_antiguedad: d.escalas_bono_antiguedad || d.escala_antiguedad || this.form.escala_antiguedad,
              notas_resolucion: d.descripcion || d.notas_resolucion || '',
            };
          }
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;
        });
    },

    guardarParametros() {
      this.guardando = true;
      const payload = {
        gestion: new Date().getFullYear(),
        nro_patronal_min_trabajo: this.form.nro_patronal_min_trabajo,
        nro_patronal_cns: this.form.nro_patronal_cns,
        nit_institucional: this.form.nit_institucional,
        ubicacion_geografica: this.form.ubicacion_geografica,
        direccion_institucional: this.form.direccion_institucional,

        salario_minimo_nacional: this.form.salario_minimo_nacional,
        tarifa_refrigerio_diaria: this.form.monto_refrigerio_diario,
        monto_refrigerio_diario: this.form.monto_refrigerio_diario,
        dias_laborales_base: this.form.dias_laborables_mes,
        dias_laborables_mes: this.form.dias_laborables_mes,
        horas_jornada_diaria: this.form.horas_jornada_ordinaria,
        horas_jornada_ordinaria: this.form.horas_jornada_ordinaria,
        factor_hora_extra: this.form.factor_horas_extra,
        factor_horas_extra: this.form.factor_horas_extra,
        factor_dominical: 2.0,
        multiplicador_smn_antiguedad: 3,

        porcentaje_gestora_vejez: this.form.gestora_vejez_porcentaje,
        gestora_vejez_porcentaje: this.form.gestora_vejez_porcentaje,
        porcentaje_gestora_riesgo_comun: this.form.gestora_riesgo_comun_porcentaje,
        gestora_riesgo_comun_porcentaje: this.form.gestora_riesgo_comun_porcentaje,
        porcentaje_gestora_comision: this.form.gestora_comision_porcentaje,
        gestora_comision_porcentaje: this.form.gestora_comision_porcentaje,
        porcentaje_gestora_solidario: this.form.gestora_laboral_solidario_porcentaje,
        gestora_laboral_solidario_porcentaje: this.form.gestora_laboral_solidario_porcentaje,

        porcentaje_patronal_cns: this.form.patronal_cns_porcentaje,
        patronal_cns_porcentaje: this.form.patronal_cns_porcentaje,
        porcentaje_patronal_gestora_riesgo: this.form.patronal_riesgo_profesional_porcentaje,
        patronal_riesgo_profesional_porcentaje: this.form.patronal_riesgo_profesional_porcentaje,
        porcentaje_patronal_gestora_pro_vivienda: this.form.patronal_pro_vivienda_porcentaje,
        patronal_pro_vivienda_porcentaje: this.form.patronal_pro_vivienda_porcentaje,
        porcentaje_patronal_gestora_sol: this.form.patronal_solidario_porcentaje,
        patronal_solidario_porcentaje: this.form.patronal_solidario_porcentaje,
        porcentaje_patronal_gestora_comision: this.form.patronal_comision_porcentaje,
        patronal_comision_porcentaje: this.form.patronal_comision_porcentaje,

        escalas_bono_antiguedad: this.form.escala_antiguedad,
        escala_antiguedad: this.form.escala_antiguedad,
        descripcion: this.form.notas_resolucion,
        notas_resolucion: this.form.notas_resolucion,
      };

      axios.post('/api/rrhh/configuracion-laboral', payload)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Configuración laboral actualizada exitosamente.', 'success');
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al guardar configuración laboral.', 'error');
        })
        .finally(() => {
          this.guardando = false;
        });
    },

    agregarTramoAntiguedad() {
      this.form.escala_antiguedad.push({ anios_min: 0, anios_max: 0, porcentaje: 0 });
    },

    eliminarTramoAntiguedad(idx) {
      this.form.escala_antiguedad.splice(idx, 1);
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.unified-workspace {
  max-width: 1200px;
  margin: 0 auto;
}

.border-b {
  border-bottom: 1px solid #e2e8f0;
}

.border {
  border: 1px solid #e2e8f0;
}

.bg-light {
  background-color: #f8fafc;
}

.text-slate-800 {
  color: #1e293b;
}

.gap-2 {
  gap: 8px;
}
</style>
