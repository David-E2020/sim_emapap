<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-sitemap-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Estructura Organizacional</h2>
            <span class="text-caption text-secondary">Organigrama institucional, escalas salariales, puestos y regionales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalPuesto()">
            <v-icon left small>mdi-briefcase-plus</v-icon> + Nuevo Puesto
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalUnidad()">
            <v-icon left small>mdi-domain-plus</v-icon> + Nueva Unidad
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABS DE NAVEGACIÓN -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="primary" grow>
        <v-tab><v-icon left small>mdi-sitemap</v-icon> Árbol de Organigrama y Puestos</v-tab>
        <v-tab><v-icon left small>mdi-cash-multiple</v-icon> Escalas Salariales y Niveles</v-tab>
        <v-tab><v-icon left small>mdi-map-marker-radius</v-icon> Regionales y Gestiones</v-tab>
      </v-tabs>
    </v-card>

    <v-tabs-items v-model="tabActual">
      <!-- TAB 1: ÁRBOL DE ORGANIGRAMA -->
      <v-tab-item>
        <v-row>
          <v-col cols="12" md="5">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Árbol de Unidades</span>
                <v-btn icon small @click="cargarOrganigrama()"><v-icon small>mdi-refresh</v-icon></v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>

              <v-treeview
                :items="unidades"
                item-key="id"
                item-text="nombre"
                item-children="dependencias"
                activatable
                hoverable
                color="primary"
                open-all
                @update:active="onSelectUnidad"
              >
                <template v-slot:prepend="{ item, open }">
                  <v-icon :color="item.es_unidad_recursos_humanos ? 'purple' : 'primary'">
                    {{ item.dependencias && item.dependencias.length > 0 ? (open ? 'mdi-domain' : 'mdi-domain') : 'mdi-office-building' }}
                  </v-icon>
                </template>
                <template v-slot:label="{ item }">
                  <span class="font-weight-medium">{{ item.nombre }}</span>
                  <v-chip x-small v-if="item.sigla" label color="grey lighten-3" class="ml-2 font-weight-bold">{{ item.sigla }}</v-chip>
                </template>
              </v-treeview>
            </v-card>
          </v-col>

          <v-col cols="12" md="7">
            <v-card rounded="lg" class="erp-card-elevated pa-4" v-if="unidadSeleccionada">
              <div class="d-flex align-center justify-space-between mb-2">
                <div>
                  <h3 class="text-h6 font-weight-bold mb-0 text-primary">{{ unidadSeleccionada.nombre }}</h3>
                  <span class="text-caption text-secondary">Sigla: {{ unidadSeleccionada.sigla || 'N/A' }} | Nivel: {{ unidadSeleccionada.nivel || 1 }}</span>
                </div>
                <v-chip small color="primary" outlined>
                  {{ (unidadSeleccionada.puestos || []).length }} Puestos Registrados
                </v-chip>
              </div>
              <v-divider class="my-3"></v-divider>

              <h4 class="text-subtitle-2 font-weight-bold mb-3 d-flex align-center">
                <v-icon small class="mr-1 text-primary">mdi-account-tie</v-icon> Puestos de Trabajo e Ítems
              </h4>

              <v-simple-table dense class="erp-table">
                <thead>
                  <tr>
                    <th>Puesto</th>
                    <th>Tipo</th>
                    <th>Funcionario Asignado</th>
                    <th>Ítem</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in unidadSeleccionada.puestos || []" :key="p.id">
                    <td class="font-weight-medium">{{ p.nombre }}</td>
                    <td><v-chip x-small label color="blue lighten-5" text-color="primary">{{ p.tipo_puesto }}</v-chip></td>
                    <td>
                      <div v-if="p.asignaciones && p.asignaciones.length > 0 && p.asignaciones[0].persona" class="d-flex align-center">
                        <v-avatar size="24" color="primary" class="white--text mr-2 text-caption">
                          {{ p.asignaciones[0].persona.nombres.charAt(0) }}
                        </v-avatar>
                        <span class="text-caption font-weight-bold">{{ p.asignaciones[0].persona.nombre_completo }}</span>
                      </div>
                      <v-chip x-small color="grey lighten-3" text-color="grey" v-else>VACANTE</v-chip>
                    </td>
                    <td>
                      <span v-if="p.asignaciones && p.asignaciones.length > 0" class="text-caption font-weight-bold">
                        #{{ p.asignaciones[0].nro_item }}
                      </span>
                      <span v-else class="text-caption text-secondary">-</span>
                    </td>
                  </tr>
                  <tr v-if="!unidadSeleccionada.puestos || unidadSeleccionada.puestos.length === 0">
                    <td colspan="4" class="text-center text-caption py-4 text-secondary">
                      No hay puestos registrados en esta unidad. Presiona "+ Nuevo Puesto".
                    </td>
                  </tr>
                </tbody>
              </v-simple-table>
            </v-card>
            <v-card v-else rounded="lg" class="erp-card-elevated pa-6 text-center">
              <v-icon size="48" color="grey lighten-1">mdi-cursor-default-click-outline</v-icon>
              <p class="text-caption text-secondary mt-2">Selecciona una unidad en el árbol de la izquierda para ver sus puestos.</p>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>

      <!-- TAB 2: ESCALAS SALARIALES -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated pa-4">
          <div class="d-flex align-center justify-space-between mb-3">
            <div>
              <span class="font-weight-bold text-subtitle-1">Escalas Salariales Institucionales</span>
              <p class="text-caption text-secondary mb-0">Tabulador salarial oficial por puesto y jerarquía</p>
            </div>
            <v-btn color="primary" small class="rounded-pill text-capitalize" @click="dialogEscala = true">
              <v-icon left small>mdi-plus</v-icon> + Nueva Escala
            </v-btn>
          </div>
          <v-divider class="mb-4"></v-divider>

          <v-data-table :headers="headersEscalas" :items="escalasSalariales" class="erp-table" dense>
            <template v-slot:item.salario_mensual="{ item }">
              <span class="font-weight-bold text-success">Bs. {{ Number(item.salario_mensual).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
            </template>
            <template v-slot:item._estado="{ item }">
              <v-chip x-small color="green lighten-5" text-color="green" label>{{ item._estado }}</v-chip>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- TAB 3: REGIONALES Y GESTIONES -->
      <v-tab-item>
        <v-row>
          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Regionales Institucionales</span>
                <v-btn color="primary" small outlined class="rounded-pill text-capitalize" @click="dialogRegional = true">
                  <v-icon left small>mdi-plus</v-icon> + Regional
                </v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>
              <v-simple-table dense class="erp-table">
                <thead><tr><th>Regional</th><th>Sigla</th><th>Estado</th></tr></thead>
                <tbody>
                  <tr v-for="r in regionales" :key="r.id">
                    <td class="font-weight-medium">{{ r.nombre }}</td>
                    <td><v-chip x-small label>{{ r.sigla || 'N/A' }}</v-chip></td>
                    <td><v-chip x-small color="green lighten-5" text-color="green" label>{{ r._estado }}</v-chip></td>
                  </tr>
                  <tr v-if="regionales.length === 0"><td colspan="3" class="text-center text-caption py-3 text-secondary">Sin regionales registradas</td></tr>
                </tbody>
              </v-simple-table>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Gestiones Anuales</span>
                <v-btn color="primary" small outlined class="rounded-pill text-capitalize" @click="dialogGestion = true">
                  <v-icon left small>mdi-plus</v-icon> + Gestión
                </v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>
              <v-simple-table dense class="erp-table">
                <thead><tr><th>Gestión</th><th>Descripción</th><th>Estado</th></tr></thead>
                <tbody>
                  <tr v-for="g in gestiones" :key="g.id">
                    <td class="font-weight-bold text-primary">{{ g.anio }}</td>
                    <td>{{ g.nombre || g.descripcion || 'Gestión Anual' }}</td>
                    <td><v-chip x-small color="green lighten-5" text-color="green" label>{{ g._estado }}</v-chip></td>
                  </tr>
                  <tr v-if="gestiones.length === 0"><td colspan="3" class="text-center text-caption py-3 text-secondary">Sin gestiones registradas</td></tr>
                </tbody>
              </v-simple-table>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>
    </v-tabs-items>

    <!-- DIALOG NUEVA UNIDAD -->
    <v-dialog v-model="dialogUnidad" max-width="500px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Unidad Organizacional</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formUnidad.nombre" label="Nombre de Unidad *" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formUnidad.sigla" label="Sigla (Ej: DGAF)" dense outlined class="mb-2"></v-text-field>
          <v-select v-model="formUnidad.padreId" :items="unidadesPlanas" item-text="nombre" item-value="id" label="Depende de (Unidad Padre)" dense outlined clearable></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogUnidad = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarUnidad()">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVO PUESTO -->
    <v-dialog v-model="dialogPuesto" max-width="500px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nuevo Puesto de Trabajo</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formPuesto.nombre" label="Nombre del Puesto / Cargo *" dense outlined class="mb-2"></v-text-field>
          <v-select v-model="formPuesto.id_unidad_organizacional" :items="unidadesPlanas" item-text="nombre" item-value="id" label="Unidad Organizacional *" dense outlined class="mb-2"></v-select>
          <v-select v-model="formPuesto.tipo_puesto" :items="['PLANTA', 'EVENTUAL', 'CONSULTOR']" label="Tipo de Contrato *" dense outlined></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogPuesto = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarPuesto()">Crear Puesto</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA ESCALA SALARIAL -->
    <v-dialog v-model="dialogEscala" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Escala Salarial</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formEscala.nombre" label="Denominación de Escala *" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formEscala.salario_mensual" label="Salario Mensual (Bs.) *" type="number" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogEscala = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarEscala()">Guardar Escala</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA REGIONAL -->
    <v-dialog v-model="dialogRegional" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Regional</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formRegional.nombre" label="Nombre Regional (Ej: Regional Santa Cruz) *" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formRegional.sigla" label="Sigla (Ej: SCZ)" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogRegional = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarRegional()">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA GESTIÓN -->
    <v-dialog v-model="dialogGestion" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Gestión Institucional</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formGestion.anio" label="Año / Gestión *" type="number" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formGestion.descripcion" label="Descripción *" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogGestion = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarGestion()">Crear Gestión</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3500" top right rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }"><v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn></template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'Organigrama',
  data() {
    return {
      tabActual: 0,
      unidades: [],
      unidadesPlanas: [],
      unidadSeleccionada: null,

      escalasSalariales: [],
      headersEscalas: [
        { text: 'Denominación de Escala', value: 'nombre' },
        { text: 'Salario Mensual', value: 'salario_mensual' },
        { text: 'Estado', value: '_estado' },
      ],

      regionales: [],
      gestiones: [],

      dialogUnidad: false,
      formUnidad: { nombre: '', sigla: '', padreId: null },

      dialogPuesto: false,
      formPuesto: { nombre: '', tipo_puesto: 'PLANTA', id_unidad_organizacional: null },

      dialogEscala: false,
      formEscala: { nombre: '', salario_mensual: '' },

      dialogRegional: false,
      formRegional: { nombre: '', sigla: '' },

      dialogGestion: false,
      formGestion: { anio: new Date().getFullYear(), descripcion: 'Gestión Institucional' },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarOrganigrama();
    this.cargarEscalas();
    this.cargarRegionales();
    this.cargarGestiones();
  },
  methods: {
    cargarOrganigrama() {
      axios.get('/api/rrhh/organigrama').then(res => {
        if (res.data && res.data.success) {
          this.unidades = res.data.data || [];
          this.unidadesPlanas = [];
          this.aplanarUnidades(this.unidades);
          if (this.unidades.length > 0 && !this.unidadSeleccionada) {
            this.unidadSeleccionada = this.unidades[0];
          }
        }
      });
    },

    cargarEscalas() {
      axios.get('/api/rrhh/escalas-salariales').then(res => {
        if (res.data && res.data.success) this.escalasSalariales = res.data.data || [];
      });
    },

    cargarRegionales() {
      axios.get('/api/rrhh/regionales').then(res => {
        if (res.data && res.data.success) this.regionales = res.data.data || [];
      });
    },

    cargarGestiones() {
      axios.get('/api/rrhh/gestiones').then(res => {
        if (res.data && res.data.success) this.gestiones = res.data.data || [];
      });
    },

    aplanarUnidades(lista) {
      lista.forEach(u => {
        this.unidadesPlanas.push({ id: u.id, nombre: u.nombre });
        if (u.dependencias && u.dependencias.length > 0) {
          this.aplanarUnidades(u.dependencias);
        }
      });
    },

    onSelectUnidad(activeKeys) {
      if (!activeKeys || activeKeys.length === 0) return;
      const id = activeKeys[0];
      const buscar = (lista) => {
        for (const u of lista) {
          if (u.id === id) return u;
          if (u.dependencias) {
            const found = buscar(u.dependencias);
            if (found) return found;
          }
        }
        return null;
      };
      this.unidadSeleccionada = buscar(this.unidades);
    },

    abrirModalUnidad() {
      this.formUnidad = { nombre: '', sigla: '', padreId: this.unidadSeleccionada ? this.unidadSeleccionada.id : null };
      this.dialogUnidad = true;
    },

    guardarUnidad() {
      if (!this.formUnidad.nombre) return;
      axios.post('/api/rrhh/unidades-organizacionales', this.formUnidad).then(res => {
        this.dialogUnidad = false;
        this.showSnackbar(res.data.message || 'Unidad creada', 'success');
        this.cargarOrganigrama();
      });
    },

    abrirModalPuesto() {
      this.formPuesto = {
        nombre: '',
        tipo_puesto: 'PLANTA',
        id_unidad_organizacional: this.unidadSeleccionada ? this.unidadSeleccionada.id : null,
      };
      this.dialogPuesto = true;
    },

    guardarPuesto() {
      if (!this.formPuesto.nombre || !this.formPuesto.id_unidad_organizacional) return;
      axios.post('/api/rrhh/puestos', this.formPuesto).then(res => {
        this.dialogPuesto = false;
        this.showSnackbar(res.data.message || 'Puesto creado', 'success');
        this.cargarOrganigrama();
      });
    },

    guardarEscala() {
      if (!this.formEscala.nombre || !this.formEscala.salario_mensual) return;
      axios.post('/api/rrhh/escalas-salariales', this.formEscala).then(res => {
        this.dialogEscala = false;
        this.showSnackbar(res.data.message || 'Escala creada', 'success');
        this.cargarEscalas();
      });
    },

    guardarRegional() {
      if (!this.formRegional.nombre) return;
      axios.post('/api/rrhh/regionales', this.formRegional).then(res => {
        this.dialogRegional = false;
        this.showSnackbar(res.data.message || 'Regional creada', 'success');
        this.cargarRegionales();
      });
    },

    guardarGestion() {
      if (!this.formGestion.anio) return;
      axios.post('/api/rrhh/gestiones', this.formGestion).then(res => {
        this.dialogGestion = false;
        this.showSnackbar(res.data.message || 'Gestión creada', 'success');
        this.cargarGestiones();
      });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
