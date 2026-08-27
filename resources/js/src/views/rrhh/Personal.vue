<style scoped>
.personal-card-hover {
  transition: all 0.2s ease-in-out;
}
.personal-card-hover:hover {
  border-color: var(--v-primary-base);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
</style>

<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Personal y Legajos Digitales</h2>
            <span class="text-caption text-secondary">Padrón oficial de funcionarios, cargos asignados y expedientes laborales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalNuevo()">
            <v-icon left small>mdi-account-plus</v-icon> + Nuevo Funcionario
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
    <v-row class="mb-2">
      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">TOTAL FUNCIONARIOS</div>
            <div class="text-h4 font-weight-bold primary--text">{{ totalPersonal }}</div>
          </div>
          <v-avatar color="primary lighten-5" size="48">
            <v-icon color="primary">mdi-account-multiple</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">CON CARGO ASIGNADO</div>
            <div class="text-h4 font-weight-bold success--text">{{ personalConPuesto }}</div>
          </div>
          <v-avatar color="success lighten-5" size="48">
            <v-icon color="success">mdi-briefcase-check</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">CON ACCESO AL ERP</div>
            <div class="text-h4 font-weight-bold info--text">{{ personalConUsuario }}</div>
          </div>
          <v-avatar color="info lighten-5" size="48">
            <v-icon color="info">mdi-shield-account</v-icon>
          </v-avatar>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA PRINCIPAL DE PERSONAL -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title class="py-3 px-4 d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-format-list-bulleted</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Listado General de Personal</span>
        </div>

        <div class="d-flex align-center" style="max-width: 320px; width: 100%;">
          <v-text-field
            v-model="search"
            placeholder="Buscar por CI o Nombre..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            @input="debouncedBuscar"
          ></v-text-field>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="items"
        :loading="loading"
        :server-items-length="totalPersonal"
        :options.sync="options"
        class="elevation-0"
        loading-text="Cargando funcionarios..."
        no-data-text="No se encontraron registros de personal"
      >
        <!-- AVATAR Y NOMBRE COMPLETO -->
        <template v-slot:item.nombre_completo="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar size="36" color="primary lighten-5" class="mr-3 font-weight-bold primary--text">
              {{ item.nombres ? item.nombres.charAt(0) : 'F' }}
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-2">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido }}</div>
              <div class="text-caption text-secondary">{{ item.correo_electronico_personal || 'Sin correo personal' }}</div>
            </div>
          </div>
        </template>

        <!-- DOCUMENTO DE IDENTIDAD -->
        <template v-slot:item.nro_documento="{ item }">
          <v-chip small label color="grey lighten-3" class="font-weight-bold">
            {{ item.tipo_documento || 'CI' }}: {{ item.nro_documento }}
          </v-chip>
        </template>

        <!-- CARGO / PUESTO ASIGNADO -->
        <template v-slot:item.puesto="{ item }">
          <div v-if="item.asignaciones_puestos && item.asignaciones_puestos.length > 0">
            <div class="font-weight-medium text-caption primary--text">
              {{ item.asignaciones_puestos[0].puesto ? item.asignaciones_puestos[0].puesto.nombre : 'Ítem Asignado' }}
            </div>
            <div class="text-caption text-secondary" style="font-size: 0.72rem !important;" v-if="item.asignaciones_puestos[0].puesto && item.asignaciones_puestos[0].puesto.unidad_organizacional">
              {{ item.asignaciones_puestos[0].puesto.unidad_organizacional.nombre }}
            </div>
          </div>
          <span v-else class="text-caption text-secondary font-italic">Sin Ítem Asignado</span>
        </template>

        <!-- CUENTA ERP -->
        <template v-slot:item.user="{ item }">
          <v-chip x-small :color="item.user ? 'success lighten-5 success--text' : 'grey lighten-3'" class="font-weight-bold">
            <v-icon x-small left :color="item.user ? 'success' : 'grey'">{{ item.user ? 'mdi-check-circle' : 'mdi-close-circle' }}</v-icon>
            {{ item.user ? item.user.usr_usuario : 'Sin Cuenta' }}
          </v-chip>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="primary"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirFichaLegajo(item)"
                  class="mr-1"
                >
                  <v-icon small>mdi-folder-account-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Ficha y Legajo Digital</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: NUEVO FUNCIONARIO -->
    <v-dialog v-model="dialogNuevo" max-width="640" persistent>
      <v-form v-model="formValido" ref="formPersonal" @submit.prevent="guardarFuncionario">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-account-plus</v-icon>
            <span>Registrar Nuevo Funcionario</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogNuevo = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-5">
            <v-row dense>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.nombres"
                  label="Nombres *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-account"
                  :rules="[v => !!v || 'Nombres requeridos']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.primer_apellido"
                  label="Primer Apellido"
                  outlined
                  dense
                  prepend-inner-icon="mdi-account-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.segundo_apellido"
                  label="Segundo Apellido"
                  outlined
                  dense
                  prepend-inner-icon="mdi-account-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.nro_documento"
                  label="C.I. / Documento *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-card-account-details-outline"
                  :rules="[v => !!v || 'Documento requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.genero"
                  :items="generosList"
                  item-text="nombre"
                  item-value="codigo"
                  label="Género"
                  outlined
                  dense
                  prepend-inner-icon="mdi-gender-male-female"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.telefono_celular"
                  label="Teléfono / Celular"
                  outlined
                  dense
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.correo_electronico_personal"
                  label="Correo Personal"
                  outlined
                  dense
                  prepend-inner-icon="mdi-email-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-divider class="my-2"></v-divider>
                <v-checkbox
                  v-model="form.crear_usuario"
                  label="Crear automáticamente cuenta de acceso al ERP para este funcionario"
                  dense
                  hide-details
                  color="primary"
                ></v-checkbox>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogNuevo = false">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="guardando"
              :disabled="guardando || !formValido"
              type="submit"
              class="text-capitalize px-4"
            >
              Registrar
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- DIÁLOGO: FICHA Y LEGAJO DIGITAL -->
    <v-dialog v-model="dialogFicha" max-width="800" persistent scrollable>
      <v-card rounded="lg" v-if="personaSeleccionada">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-folder-account</v-icon>
          <span>Legajo Digital: {{ personaSeleccionada.nombres }} {{ personaSeleccionada.primer_apellido }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogFicha = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-tabs v-model="tabFicha" color="primary" grow>
            <v-tab><v-icon left small>mdi-account-details</v-icon> Datos Generales</v-tab>
            <v-tab><v-icon left small>mdi-briefcase</v-icon> Datos Laborales</v-tab>
            <v-tab><v-icon left small>mdi-school</v-icon> Estudios</v-tab>
            <v-tab><v-icon left small>mdi-certificate</v-icon> CAS</v-tab>
          </v-tabs>

          <v-tabs-items v-model="tabFicha" class="mt-4">
            <!-- TAB 1: DATOS GENERALES -->
            <v-tab-item>
              <v-row dense>
                <v-col cols="6"><div class="text-caption text-secondary">Nombres:</div><div class="font-weight-bold">{{ personaSeleccionada.nombres }}</div></v-col>
                <v-col cols="6"><div class="text-caption text-secondary">Apellidos:</div><div class="font-weight-bold">{{ personaSeleccionada.primer_apellido }} {{ personaSeleccionada.segundo_apellido }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">C.I.:</div><div class="font-weight-bold">{{ personaSeleccionada.nro_documento }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">Celular:</div><div class="font-weight-bold">{{ personaSeleccionada.telefono_celular || 'No registrado' }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">Correo:</div><div class="font-weight-bold">{{ personaSeleccionada.correo_electronico_personal || 'No registrado' }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">Género:</div><div class="font-weight-bold">{{ personaSeleccionada.genero || 'No especificado' }}</div></v-col>
              </v-row>
            </v-tab-item>

            <!-- TAB 2: DATOS LABORALES -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.datos_laborales && personaSeleccionada.ficha_personal.datos_laborales.length > 0">
                <v-card v-for="d in personaSeleccionada.ficha_personal.datos_laborales" :key="d.id" outlined class="pa-3 mb-2 rounded-lg">
                  <div class="font-weight-bold text-body-2">{{ d.cargo }}</div>
                  <div class="text-caption text-secondary">Unidad: {{ d.unidad_organizacional }} • Tipo: {{ d.tipo_funcionario }}</div>
                  <div class="text-caption text-secondary">Fecha Ingreso: {{ d.fecha_ingreso }}</div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin historial laboral registrado.
              </div>
            </v-tab-item>

            <!-- TAB 3: ESTUDIOS -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.estudios_academicos && personaSeleccionada.ficha_personal.estudios_academicos.length > 0">
                <v-card v-for="e in personaSeleccionada.ficha_personal.estudios_academicos" :key="e.id" outlined class="pa-3 mb-2 rounded-lg">
                  <div class="font-weight-bold text-body-2">{{ e.carrera }} - {{ e.nivel_instruccion }}</div>
                  <div class="text-caption text-secondary">Institución: {{ e.institucion }}</div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin estudios académicos registrados.
              </div>
            </v-tab-item>

            <!-- TAB 4: CAS -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.cas && personaSeleccionada.ficha_personal.cas.length > 0">
                <v-card v-for="c in personaSeleccionada.ficha_personal.cas" :key="c.id" outlined class="pa-3 mb-2 rounded-lg">
                  <div class="font-weight-bold text-body-2">Resolución: {{ c.nro_resolucion }}</div>
                  <div class="text-caption text-secondary">Años de Servicio: {{ c.anios }} años, {{ c.meses }} meses, {{ c.dias }} días</div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin certificación CAS registrada.
              </div>
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" class="text-capitalize px-4" @click="dialogFicha = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'Personal',
  data() {
    return {
      items: [],
      totalPersonal: 0,
      loading: false,
      search: '',
      options: {},
      debounceTimeout: null,

      dialogNuevo: false,
      formValido: false,
      guardando: false,
      form: {
        nombres: '',
        primer_apellido: '',
        segundo_apellido: '',
        nro_documento: '',
        genero: 'MASCULINO',
        telefono_celular: '',
        correo_electronico_personal: '',
        crear_usuario: true,
      },

      dialogFicha: false,
      personaSeleccionada: null,
      tabFicha: 0,

      generosList: [
        { codigo: 'M', nombre: 'Masculino' },
        { codigo: 'F', nombre: 'Femenino' },
      ],
      expedidosList: ['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD', 'EX'],
      nivelesInstruccionList: ['PRIMARIA', 'SECUNDARIA', 'TECNICO_MEDIO', 'TECNICO_SUPERIOR', 'LICENCIATURA', 'DIPLOMADO', 'MAESTRIA', 'DOCTORADO'],
      tiposContratoList: ['PLANTA', 'EVENTUAL', 'CONSULTOR', 'PASANTE'],

      headers: [
        { text: 'Funcionario', value: 'nombre_completo' },
        { text: 'Documento', value: 'nro_documento', width: '140px' },
        { text: 'Cargo / Puesto Actual', value: 'puesto' },
        { text: 'Teléfono', value: 'telefono_celular', width: '130px' },
        { text: 'Acceso ERP', value: 'user', width: '130px', align: 'center' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '90px', align: 'center' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    personalConPuesto() {
      return this.items.filter(i => i.asignaciones_puestos && i.asignaciones_puestos.length > 0).length;
    },
    personalConUsuario() {
      return this.items.filter(i => i.user).length;
    },
  },
  watch: {
    options: {
      handler() {
        this.cargarPersonal();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarPersonal();
    this.cargarParametricas();
  },
  methods: {
    cargarParametricas() {
      axios.get('/api/parametrica-api/TABLA_RRHH_GENERO').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.generosList = res.data.map(c => ({ codigo: c.param_codigo, nombre: c.param_nombre }));
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_EXPEDIDO_DOC').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.expedidosList = res.data.map(c => c.param_codigo);
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_NIVEL_INSTRUCCION').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.nivelesInstruccionList = res.data.map(c => c.param_codigo);
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_TIPO_CONTRATO').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.tiposContratoList = res.data.map(c => c.param_codigo);
        }
      });
    },
    cargarPersonal() {
      this.loading = true;
      const { page, itemsPerPage } = this.options;

      axios
        .get('/api/rrhh/personal', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            search: this.search,
          },
        })
        .then(res => {
          this.loading = false;
          if (res.data && res.data.success) {
            this.items = res.data.data || [];
            this.totalPersonal = res.data.total || 0;
          }
        })
        .catch(() => {
          this.loading = false;
          this.showSnackbar('Error al cargar la lista de personal', 'error');
        });
    },

    debouncedBuscar() {
      clearTimeout(this.debounceTimeout);
      this.debounceTimeout = setTimeout(() => {
        this.options.page = 1;
        this.cargarPersonal();
      }, 400);
    },

    abrirModalNuevo() {
      this.form = {
        nombres: '',
        primer_apellido: '',
        segundo_apellido: '',
        nro_documento: '',
        genero: 'MASCULINO',
        telefono_celular: '',
        correo_electronico_personal: '',
        crear_usuario: true,
      };
      this.dialogNuevo = true;
    },

    guardarFuncionario() {
      if (!this.$refs.formPersonal.validate()) return;
      this.guardando = true;

      axios
        .post('/api/rrhh/personal', this.form)
        .then(res => {
          this.guardando = false;
          this.dialogNuevo = false;
          this.showSnackbar(res.data.message || 'Funcionario registrado', 'success');
          this.cargarPersonal();
        })
        .catch(err => {
          this.guardando = false;
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al registrar funcionario';
          this.showSnackbar(msg, 'error');
        });
    },

    abrirFichaLegajo(item) {
      axios
        .get('/api/rrhh/personal/' + item.id)
        .then(res => {
          if (res.data && res.data.success) {
            this.personaSeleccionada = res.data.data;
            this.tabFicha = 0;
            this.dialogFicha = true;
          }
        })
        .catch(() => {
          this.showSnackbar('Error al cargar el legajo del funcionario', 'error');
        });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
