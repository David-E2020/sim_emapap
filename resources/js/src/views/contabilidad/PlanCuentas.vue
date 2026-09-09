<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-file-tree-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Plan Único de Cuentas (NBSCI - Ley 1178)</h2>
            <span class="text-caption text-secondary">
              Estructura codificada jerárquica de 5 niveles para la Contabilidad Gubernamental Integrada de EMAPAP
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="indigo darken-2"
            dark
            class="rounded-pill font-weight-medium elevation-1"
            @click="abrirModalCrear"
          >
            <v-icon left>mdi-plus-circle-outline</v-icon> Nueva Cuenta
          </v-btn>
          <v-btn
            icon
            color="primary"
            class="ml-2"
            :loading="cargando"
            @click="cargarPlanCuentas"
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
              <div class="text-caption text-secondary font-weight-bold">TOTAL CUENTAS</div>
              <div class="text-h5 font-weight-bold indigo--text text--darken-3">{{ totalCuentas }}</div>
            </div>
            <v-avatar color="indigo lighten-5" size="40">
              <v-icon color="indigo darken-2">mdi-format-list-numbered</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-success">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">IMPUTABLES (N5)</div>
              <div class="text-h5 font-weight-bold green--text text--darken-2">{{ totalImputables }}</div>
            </div>
            <v-avatar color="green lighten-5" size="40">
              <v-icon color="green darken-2">mdi-check-decagram</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-info">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">BALANCE (ACTIVO/PASIVO)</div>
              <div class="text-h5 font-weight-bold blue--text text--darken-2">{{ totalBalance }}</div>
            </div>
            <v-avatar color="blue lighten-5" size="40">
              <v-icon color="blue darken-2">mdi-scale-balance</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card rounded="lg" class="pa-3 elevation-1 border-l-warning">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary font-weight-bold">RESULTADOS (ING/GAS)</div>
              <div class="text-h5 font-weight-bold amber--text text--darken-3">{{ totalResultados }}</div>
            </div>
            <v-avatar color="amber lighten-5" size="40">
              <v-icon color="amber darken-3">mdi-chart-areaspline</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- BARRA DE FILTROS -->
    <v-card rounded="lg" class="pa-4 mb-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" sm="5">
          <v-text-field
            v-model="busqueda"
            label="Buscar por código o nombre de cuenta..."
            outlined
            dense
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="3">
          <v-select
            v-model="filtroNivel"
            :items="nivelesOpciones"
            label="Filtrar por Nivel"
            outlined
            dense
            hide-details
            clearable
          ></v-select>
        </v-col>

        <v-col cols="12" sm="4">
          <v-select
            v-model="filtroTipo"
            :items="tiposOpciones"
            label="Tipo de Cuenta"
            outlined
            dense
            hide-details
            clearable
          ></v-select>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA JERÁRQUICA DE CUENTAS -->
    <v-card rounded="lg" class="elevation-1">
      <v-data-table
        :headers="headers"
        :items="cuentasFiltradas"
        :loading="cargando"
        :items-per-page="15"
        class="elevation-0"
        no-data-text="No se encontraron cuentas contables"
        loading-text="Cargando catálogo contable..."
      >
        <!-- CÓDIGO -->
        <template v-slot:item.codigo="{ item }">
          <span :style="{ fontWeight: item.nivel <= 3 ? 'bold' : 'normal', paddingLeft: (item.nivel - 1) * 12 + 'px' }">
            <v-icon x-small class="mr-1" v-if="item.es_imputable" color="green darken-2">mdi-circle-medium</v-icon>
            <v-icon x-small class="mr-1" v-else color="indigo darken-1">mdi-folder-outline</v-icon>
            <code>{{ item.codigo }}</code>
          </span>
        </template>

        <!-- NOMBRE -->
        <template v-slot:item.nombre="{ item }">
          <span :style="{ fontWeight: item.nivel <= 2 ? 'bold' : item.nivel === 3 ? '600' : 'normal' }">
            {{ item.nombre }}
          </span>
        </template>

        <!-- NIVEL -->
        <template v-slot:item.nivel="{ item }">
          <v-chip
            x-small
            label
            :color="colorNivel(item.nivel)"
            text-color="white"
            class="font-weight-bold"
          >
            N{{ item.nivel }} - {{ etiquetaNivel(item.nivel) }}
          </v-chip>
        </template>

        <!-- NATURALEZA -->
        <template v-slot:item.naturaleza="{ item }">
          <v-chip
            x-small
            outlined
            :color="item.naturaleza === 'DEUDOR' ? 'blue darken-2' : 'purple darken-2'"
            class="font-weight-medium"
          >
            {{ item.naturaleza }}
          </v-chip>
        </template>

        <!-- TIPO DE CUENTA -->
        <template v-slot:item.tipo_cuenta="{ item }">
          <v-chip
            x-small
            label
            :color="colorTipoCuenta(item.tipo_cuenta)"
            text-color="white"
          >
            {{ item.tipo_cuenta }}
          </v-chip>
        </template>

        <!-- IMPUTABLE -->
        <template v-slot:item.es_imputable="{ item }">
          <v-chip
            x-small
            :color="item.es_imputable ? 'green lighten-5' : 'grey lighten-4'"
            :text-color="item.es_imputable ? 'green darken-3' : 'grey darken-2'"
            class="font-weight-bold"
          >
            {{ item.es_imputable ? 'SÍ (N5)' : 'NO' }}
          </v-chip>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <v-btn
            icon
            small
            color="primary"
            @click="abrirModalEditar(item)"
            title="Editar cuenta"
          >
            <v-icon small>mdi-pencil-outline</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL REGISTRO / EDICIÓN DE CUENTA -->
    <v-dialog v-model="dialogoCuenta" max-width="650px" persistent>
      <v-card rounded="lg">
        <v-card-title class="indigo darken-3 white--text py-3">
          <v-icon left color="white">mdi-book-open-outline</v-icon>
          <span>{{ esEdicion ? 'Editar Cuenta Contable' : 'Nueva Cuenta Contable' }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark small @click="cerrarModal">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <v-form ref="formCuenta" v-model="formValido">
            <v-row dense>
              <!-- CUENTA PADRE -->
              <v-col cols="12">
                <v-autocomplete
                  v-model="cuentaForm.padre_id"
                  :items="cuentasPadreDisponibles"
                  item-value="id"
                  :item-text="item => item.codigo + ' - ' + item.nombre"
                  label="Cuenta Superior / Padre (Opcional si es Nivel 1)"
                  outlined
                  dense
                  clearable
                  prepend-inner-icon="mdi-arrow-up-bold-box-outline"
                  @change="alCambiarPadre"
                ></v-autocomplete>
              </v-col>

              <!-- CÓDIGO -->
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="cuentaForm.codigo"
                  label="Código Contable *"
                  hint="Ejemplo: 1.1.1.01.003"
                  persistent-hint
                  outlined
                  dense
                  :rules="[v => !!v || 'El código es obligatorio']"
                ></v-text-field>
              </v-col>

              <!-- NIVEL -->
              <v-col cols="12" sm="6">
                <v-select
                  v-model="cuentaForm.nivel"
                  :items="[1, 2, 3, 4, 5]"
                  label="Nivel Jerárquico *"
                  outlined
                  dense
                  :rules="[v => !!v || 'El nivel es obligatorio']"
                  @change="alCambiarNivel"
                ></v-select>
              </v-col>

              <!-- NOMBRE -->
              <v-col cols="12">
                <v-text-field
                  v-model="cuentaForm.nombre"
                  label="Nombre de la Cuenta *"
                  outlined
                  dense
                  :rules="[v => !!v || 'El nombre es obligatorio']"
                ></v-text-field>
              </v-col>

              <!-- TIPO DE CUENTA -->
              <v-col cols="12" sm="6">
                <v-select
                  v-model="cuentaForm.tipo_cuenta"
                  :items="tiposCuentaOpciones"
                  label="Tipo de Cuenta *"
                  outlined
                  dense
                  :rules="[v => !!v || 'Seleccione el tipo de cuenta']"
                  @change="alCambiarTipoCuenta"
                ></v-select>
              </v-col>

              <!-- NATURALEZA -->
              <v-col cols="12" sm="6">
                <v-select
                  v-model="cuentaForm.naturaleza"
                  :items="['DEUDOR', 'ACREEDOR']"
                  label="Naturaleza del Saldo *"
                  outlined
                  dense
                  :rules="[v => !!v || 'Seleccione la naturaleza']"
                ></v-select>
              </v-col>

              <!-- ES IMPUTABLE (RECIBE ASIENTOS) -->
              <v-col cols="12" sm="6">
                <v-switch
                  v-model="cuentaForm.es_imputable"
                  label="Es Imputable (Nivel Operativo / Recibe Asientos)"
                  color="success"
                  dense
                  inset
                  hide-details
                ></v-switch>
              </v-col>

              <!-- ESTADO ACTIVO -->
              <v-col cols="12" sm="6">
                <v-switch
                  v-model="cuentaForm.estado"
                  label="Cuenta Habilitada / Activa"
                  color="primary"
                  dense
                  inset
                  hide-details
                ></v-switch>
              </v-col>

              <!-- DESCRIPCIÓN -->
              <v-col cols="12" class="mt-3">
                <v-textarea
                  v-model="cuentaForm.descripcion"
                  label="Notas o Especificación Técnica SAFCO (Opcional)"
                  rows="2"
                  outlined
                  dense
                  hide-details
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-5 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="cerrarModal" :disabled="guardando">Cancelar</v-btn>
          <v-btn
            color="indigo darken-2"
            dark
            class="px-5 rounded-pill font-weight-medium"
            :loading="guardando"
            @click="guardarCuenta"
          >
            Guardar Cuenta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'PlanCuentas',
  data() {
    return {
      cargando: false,
      guardando: false,
      cuentas: [],
      busqueda: '',
      filtroNivel: null,
      filtroTipo: null,

      headers: [
        { text: 'Código', value: 'codigo', width: '220px', sortable: true },
        { text: 'Nombre de la Cuenta', value: 'nombre', sortable: true },
        { text: 'Nivel', value: 'nivel', width: '150px', sortable: true },
        { text: 'Naturaleza', value: 'naturaleza', width: '120px', sortable: true },
        { text: 'Tipo', value: 'tipo_cuenta', width: '130px', sortable: true },
        { text: 'Imputable', value: 'es_imputable', width: '110px', sortable: true },
        { text: 'Acciones', value: 'acciones', width: '90px', sortable: false, align: 'center' },
      ],

      nivelesOpciones: [
        { text: 'Todos los niveles', value: null },
        { text: 'Nivel 1 - Grupo', value: 1 },
        { text: 'Nivel 2 - Rubro', value: 2 },
        { text: 'Nivel 3 - Cuenta', value: 3 },
        { text: 'Nivel 4 - Subcuenta', value: 4 },
        { text: 'Nivel 5 - Auxiliar (Imputable)', value: 5 },
      ],

      tiposOpciones: [
        { text: 'Todos los tipos', value: null },
        { text: 'ACTIVO', value: 'ACTIVO' },
        { text: 'PASIVO', value: 'PASIVO' },
        { text: 'PATRIMONIO', value: 'PATRIMONIO' },
        { text: 'INGRESO', value: 'INGRESO' },
        { text: 'GASTO', value: 'GASTO' },
        { text: 'COSTOS', value: 'COSTOS' },
        { text: 'ORDEN', value: 'ORDEN' },
      ],

      tiposCuentaOpciones: ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO', 'COSTOS', 'ORDEN'],

      dialogoCuenta: false,
      esEdicion: false,
      formValido: false,
      cuentaForm: {
        id: null,
        codigo: '',
        nombre: '',
        nivel: 5,
        naturaleza: 'DEUDOR',
        tipo_cuenta: 'ACTIVO',
        es_imputable: true,
        padre_id: null,
        estado: true,
        descripcion: '',
      },
    }
  },

  computed: {
    totalCuentas() {
      return this.cuentas.length
    },
    totalImputables() {
      return this.cuentas.filter(c => c.es_imputable).length
    },
    totalBalance() {
      return this.cuentas.filter(c => c.tipo_cuenta === 'ACTIVO' || c.tipo_cuenta === 'PASIVO' || c.tipo_cuenta === 'PATRIMONIO').length
    },
    totalResultados() {
      return this.cuentas.filter(c => c.tipo_cuenta === 'INGRESO' || c.tipo_cuenta === 'GASTO' || c.tipo_cuenta === 'COSTOS').length
    },

    cuentasPadreDisponibles() {
      return this.cuentas.filter(c => !c.es_imputable)
    },

    cuentasFiltradas() {
      return this.cuentas.filter(c => {
        let coincideBusqueda = true
        if (this.busqueda && this.busqueda.trim() !== '') {
          const q = this.busqueda.toLowerCase()
          const cod = c.codigo ? c.codigo.toLowerCase() : ''
          const nom = c.nombre ? c.nombre.toLowerCase() : ''
          coincideBusqueda = cod.includes(q) || nom.includes(q)
        }

        let coincideNivel = true
        if (this.filtroNivel !== null && this.filtroNivel !== undefined) {
          coincideNivel = c.nivel === this.filtroNivel
        }

        let coincideTipo = true
        if (this.filtroTipo !== null && this.filtroTipo !== undefined) {
          coincideTipo = c.tipo_cuenta === this.filtroTipo
        }

        return coincideBusqueda && coincideNivel && coincideTipo
      })
    },
  },

  mounted() {
    this.cargarPlanCuentas()
  },

  methods: {
    cargarPlanCuentas() {
      this.cargando = true
      axios
        .get('/api/contabilidad/plan-cuentas')
        .then(res => {
          if (res.data && res.data.data) {
            this.cuentas = res.data.data
          }
        })
        .catch(err => {
          this.notificar('error', 'Error al cargar el plan de cuentas contable')
        })
        .finally(() => {
          this.cargando = false
        })
    },

    colorNivel(nivel) {
      const mapa = {
        1: 'blue-grey darken-3',
        2: 'indigo darken-2',
        3: 'blue darken-2',
        4: 'teal darken-2',
        5: 'green darken-2',
      }
      return mapa[nivel] || 'grey'
    },

    etiquetaNivel(nivel) {
      const mapa = {
        1: 'Grupo',
        2: 'Rubro',
        3: 'Cuenta',
        4: 'Subcuenta',
        5: 'Auxiliar',
      }
      return mapa[nivel] || 'Nivel'
    },

    colorTipoCuenta(tipo) {
      const mapa = {
        ACTIVO: 'blue darken-3',
        PASIVO: 'deep-orange darken-2',
        PATRIMONIO: 'purple darken-2',
        INGRESO: 'teal darken-3',
        GASTO: 'red darken-3',
        COSTOS: 'brown darken-2',
        ORDEN: 'grey darken-3',
      }
      return mapa[tipo] || 'primary'
    },

    alCambiarPadre(padreId) {
      if (!padreId) return
      const padre = this.cuentas.find(c => c.id === padreId)
      if (padre) {
        this.cuentaForm.nivel = Math.min(padre.nivel + 1, 5)
        this.cuentaForm.tipo_cuenta = padre.tipo_cuenta
        this.cuentaForm.naturaleza = padre.naturaleza
        this.cuentaForm.es_imputable = this.cuentaForm.nivel === 5
        if (!this.cuentaForm.codigo || !this.cuentaForm.codigo.startsWith(padre.codigo)) {
          this.cuentaForm.codigo = padre.codigo + '.'
        }
      }
    },

    alCambiarNivel(nivel) {
      this.cuentaForm.es_imputable = nivel === 5
    },

    alCambiarTipoCuenta(tipo) {
      if (tipo === 'ACTIVO' || tipo === 'GASTO' || tipo === 'COSTOS') {
        this.cuentaForm.naturaleza = 'DEUDOR'
      } else {
        this.cuentaForm.naturaleza = 'ACREEDOR'
      }
    },

    abrirModalCrear() {
      this.esEdicion = false
      this.cuentaForm = {
        id: null,
        codigo: '',
        nombre: '',
        nivel: 5,
        naturaleza: 'DEUDOR',
        tipo_cuenta: 'ACTIVO',
        es_imputable: true,
        padre_id: null,
        estado: true,
        descripcion: '',
      }
      this.dialogoCuenta = true
      this.$nextTick(() => {
        if (this.$refs.formCuenta) this.$refs.formCuenta.resetValidation()
      })
    },

    abrirModalEditar(item) {
      this.esEdicion = true
      this.cuentaForm = {
        id: item.id,
        codigo: item.codigo,
        nombre: item.nombre,
        nivel: item.nivel,
        naturaleza: item.naturaleza,
        tipo_cuenta: item.tipo_cuenta,
        es_imputable: Boolean(item.es_imputable),
        padre_id: item.padre_id,
        estado: Boolean(item.estado),
        descripcion: item.descripcion || '',
      }
      this.dialogoCuenta = true
    },

    cerrarModal() {
      this.dialogoCuenta = false
    },

    guardarCuenta() {
      if (!this.$refs.formCuenta.validate()) return

      this.guardando = true
      axios
        .post('/api/contabilidad/plan-cuentas', this.cuentaForm)
        .then(res => {
          this.notificar('success', res.data.message || 'Cuenta contable guardada exitosamente')
          this.dialogoCuenta = false
          this.cargarPlanCuentas()
        })
        .catch(err => {
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al registrar la cuenta contable'
          this.notificar('error', msg)
        })
        .finally(() => {
          this.guardando = false
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
.border-l-info {
  border-left: 4px solid #2196f3 !important;
}
.border-l-warning {
  border-left: 4px solid #ff9800 !important;
}
.gap-2 {
  gap: 8px;
}
</style>
