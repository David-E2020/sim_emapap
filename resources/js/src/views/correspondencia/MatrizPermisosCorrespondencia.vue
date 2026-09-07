<template>
  <div class="permisos-derivacion-container">
    <!-- CABECERA PRINCIPAL -->
    <v-card class="mb-4 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="indigo darken-2" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-shield-account-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Permisos de Derivación</h2>
            <span class="text-caption text-secondary">
              Gestor y control jerárquico de derivaciones entre Sedes, Unidades, Puestos y Ventanillas
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            outlined
            color="indigo darken-2"
            class="text-capitalize rounded-pill mr-2 font-weight-medium"
            :disabled="!sujetoSeleccionado && tabActual !== 'VENTANILLA_DIGITAL'"
            @click="abrirModalResumen"
          >
            <v-icon left small>mdi-eye-outline</v-icon> Ver Resumen
          </v-btn>
          <v-btn
            outlined
            color="red darken-2"
            class="text-capitalize rounded-pill mr-2 font-weight-medium"
            :disabled="!sujetoSeleccionado && tabActual !== 'VENTANILLA_DIGITAL'"
            @click="dialogRestablecer = true"
          >
            <v-icon left small>mdi-restore</v-icon> Restablecer
          </v-btn>
          <v-btn
            color="indigo darken-2"
            class="text-capitalize rounded-pill font-weight-bold white--text elevation-2"
            :loading="guardando"
            :disabled="!sujetoSeleccionado && tabActual !== 'VENTANILLA_DIGITAL'"
            @click="guardarPermisos"
          >
            <v-icon left small>mdi-content-save-outline</v-icon> Guardar Permisos
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS DE ASIGNACIÓN (COPIA FIEL LONDRA) -->
    <v-card class="mb-4" rounded="lg" elevation="1">
      <v-tabs
        v-model="tabIndex"
        background-color="indigo darken-3"
        dark
        slider-color="amber accent-3"
        centered
        grow
        @change="onTabChange"
      >
        <v-tab v-for="tab in tabsAsignacion" :key="tab.value" class="text-capitalize font-weight-bold">
          <v-icon left small>{{ tab.icon }}</v-icon> {{ tab.label }}
        </v-tab>
      </v-tabs>
    </v-card>

    <!-- PANEL DE SELECCIÓN DE ORIGEN Y SEDE REGIONAL -->
    <v-card class="mb-4 pa-4 erp-card-elevated" rounded="lg">
      <v-row dense align="center">
        <!-- FILTRO DE SEDE REGIONAL -->
        <v-col cols="12" md="4" v-if="tabActual !== 'VENTANILLA_DIGITAL'">
          <v-select
            v-model="filtroRegional"
            :items="regionales"
            item-text="nombre"
            item-value="id"
            label="Sede / Regional"
            outlined
            dense
            clearable
            prepend-inner-icon="mdi-map-marker-radius-outline"
            hide-details
            @change="cargarSujetos"
          >
            <template v-slot:item="{ item }">
              <span
                class="regional-dot mr-2"
                :style="{ backgroundColor: getRegionalColor(item.id) }"
              ></span>
              {{ item.nombre }}
            </template>
          </v-select>
        </v-col>

        <!-- AUTOCOMPLETE DE SUJETO ORIGEN -->
        <v-col cols="12" :md="tabActual !== 'VENTANILLA_DIGITAL' ? 8 : 12">
          <v-autocomplete
            v-if="tabActual !== 'VENTANILLA_DIGITAL'"
            v-model="sujetoSeleccionado"
            :items="sujetos"
            :loading="cargandoSujetos"
            item-text="nombre"
            return-object
            :label="labelBuscadorSujeto"
            outlined
            dense
            clearable
            prepend-inner-icon="mdi-account-search-outline"
            hide-details
            @change="onSujetoSeleccionado"
          >
            <template v-slot:item="{ item }">
              <v-list-item-avatar size="32" color="indigo lighten-5">
                <v-icon small color="indigo darken-2">{{ getIconoTipo(item.tipo) }}</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title class="font-weight-bold">{{ item.nombre }}</v-list-item-title>
                <v-list-item-subtitle class="text-caption text-secondary">
                  {{ item.regional || 'Nacional' }} <span v-if="item.unidad">• {{ item.unidad }}</span>
                </v-list-item-subtitle>
              </v-list-item-content>
            </template>
          </v-autocomplete>

          <!-- BANNER INFORMATIVO PARA VENTANILLA DIGITAL -->
          <v-alert
            v-else
            dense
            text
            color="indigo darken-2"
            icon="mdi-earth"
            class="mb-0 font-weight-medium"
          >
            Configurando permisos de derivación para los trámites ingresados mediante la <strong>Ventanilla Única Digital (Portal Ciudadano)</strong>.
          </v-alert>
        </v-col>
      </v-row>
    </v-card>

    <!-- ÁRBOL JERÁRQUICO DE DESTINOS CON CHECKBOXES (TREEVIEW) -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <div class="d-flex align-center justify-space-between mb-3 flex-wrap">
        <div>
          <h3 class="text-subtitle-1 font-weight-bold mb-0">
            <v-icon left small color="indigo darken-2">mdi-file-tree-outline</v-icon>
            Estructura Organizacional y Destinos Autorizados
          </h3>
          <span class="text-caption text-secondary">
            Marque las unidades o puestos a los cuales {{ textoSujetoActivo }} tiene permitido derivar correspondencia.
          </span>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-text-field
            v-model="filtroArbol"
            prepend-inner-icon="mdi-magnify"
            label="Filtrar en el árbol..."
            dense
            outlined
            hide-details
            clearable
            class="filtro-arbol-input mr-2"
          ></v-text-field>

          <v-btn small text color="indigo darken-2" @click="marcarTodos">
            <v-icon left small>mdi-checkbox-multiple-marked-outline</v-icon> Marcar Todos
          </v-btn>
          <v-btn small text color="grey darken-1" @click="desmarcarTodos">
            <v-icon left small>mdi-checkbox-multiple-blank-outline</v-icon> Desmarcar
          </v-btn>
        </div>
      </div>

      <v-divider class="mb-4"></v-divider>

      <!-- SPINNER CARGANDO ÁRBOL -->
      <div v-if="cargandoArbol" class="text-center py-8">
        <v-progress-circular indeterminate color="indigo darken-2" size="48"></v-progress-circular>
        <div class="text-caption text-secondary mt-2">Cargando estructura institucional de EMAPA...</div>
      </div>

      <!-- COMPONENTE TREEVIEW JERÁRQUICO -->
      <v-treeview
        v-else
        v-model="destinosSeleccionados"
        :items="arbolJerarquico"
        :search="filtroArbol"
        selectable
        selection-type="leaf"
        hoverable
        dense
        open-on-click
        item-key="id"
        item-text="name"
        class="arbol-destinos"
      >
        <template v-slot:prepend="{ item, open }">
          <!-- ICONO POR TIPO DE NODO -->
          <span
            v-if="item.tipo === 'REGIONAL'"
            class="regional-dot mr-2"
            :style="{ backgroundColor: item.color || '#1976D2' }"
          ></span>

          <v-icon v-if="item.tipo === 'REGIONAL'" small color="indigo darken-2">
            {{ open ? 'mdi-domain' : 'mdi-domain' }}
          </v-icon>
          <v-icon v-else-if="item.tipo === 'UNIDAD'" small color="blue darken-2">
            mdi-office-building-outline
          </v-icon>
          <v-icon v-else-if="item.tipo === 'CARGO'" small color="teal darken-2">
            mdi-badge-account-horizontal-outline
          </v-icon>
        </template>

        <template v-slot:label="{ item }">
          <div class="d-inline-flex align-center">
            <span :class="item.tipo === 'REGIONAL' ? 'font-weight-bold indigo--text text--darken-3' : (item.tipo === 'UNIDAD' ? 'font-weight-medium' : '')">
              {{ item.name }}
            </span>
            <v-chip
              v-if="item.sigla"
              x-small
              label
              color="indigo lighten-5"
              class="indigo--text text--darken-3 ml-2 font-weight-bold"
            >
              {{ item.sigla }}
            </v-chip>
          </div>
        </template>
      </v-treeview>
    </v-card>

    <!-- DIÁLOGO MODAL: RESUMEN DE PERMISOS AUTORIZADOS -->
    <v-dialog v-model="dialogResumen" max-width="750px" scrollable>
      <v-card rounded="lg">
        <v-card-title class="indigo darken-2 white--text font-weight-bold py-3 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon left color="white">mdi-file-document-check-outline</v-icon>
            Resumen de Permisos Asignados
          </div>
          <v-btn icon dark small @click="dialogResumen = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4" id="seccion-impresion-resumen">
          <div class="mb-3 pa-3 rounded-lg grey lighten-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-secondary">Sujeto Evaluado:</div>
              <div class="font-weight-bold text-subtitle-1 indigo--text text--darken-2">
                {{ sujetoSeleccionado ? sujetoSeleccionado.nombre : 'Ventanilla Digital Web' }}
              </div>
            </div>
            <div class="text-right">
              <div class="text-caption text-secondary">Total Destinos Permitidos:</div>
              <v-chip small color="green darken-2" class="white--text font-weight-bold">
                {{ destinosSeleccionados.length }} destinos autorizados
              </v-chip>
            </div>
          </div>

          <h4 class="font-weight-bold text-subtitle-2 mb-2 indigo--text">
            <v-icon small left color="indigo">mdi-format-list-checks</v-icon> Detalle de Destinos Autorizados
          </h4>

          <v-simple-table dense class="elevation-1 rounded-lg">
            <template v-slot:default>
              <thead>
                <tr class="grey lighten-3">
                  <th class="font-weight-bold">Tipo</th>
                  <th class="font-weight-bold">Destino Autorizado</th>
                  <th class="font-weight-bold text-center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="dest in resumenDetalle" :key="dest.id">
                  <td>
                    <v-chip x-small label :color="dest.tipo === 'UNIDAD' ? 'blue lighten-4' : 'teal lighten-4'" :class="dest.tipo === 'UNIDAD' ? 'blue--text text--darken-3' : 'teal--text text--darken-3'">
                      {{ dest.tipo }}
                    </v-chip>
                  </td>
                  <td class="font-weight-medium">{{ dest.nombre }}</td>
                  <td class="text-center">
                    <v-chip x-small color="green darken-1" class="white--text font-weight-bold">AUTORIZADO</v-chip>
                  </td>
                </tr>
                <tr v-if="resumenDetalle.length === 0">
                  <td colspan="3" class="text-center py-4 text-secondary">No tiene destinos asignados actualmente.</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3 bg-light">
          <v-btn text class="rounded-pill text-capitalize" @click="imprimirResumen">
            <v-icon left small>mdi-printer</v-icon> Imprimir Resumen
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn color="indigo darken-2" class="rounded-pill text-capitalize white--text px-4" @click="dialogResumen = false">
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CONFIRMAR RESTABLECER -->
    <v-dialog v-model="dialogRestablecer" max-width="450px">
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-subtitle-1 red darken-1 white--text py-3">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon> Restablecer Permisos de Derivación
        </v-card-title>
        <v-card-text class="pt-4 text-body-2">
          ¿Está seguro de restablecer los permisos de derivación para <strong>{{ textoSujetoActivo }}</strong>? Se reestablecerán las autorizaciones por defecto del organigrama institucional.
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogRestablecer = false">Cancelar</v-btn>
          <v-btn color="red darken-2" class="rounded-pill text-capitalize white--text px-4" :loading="restableciendo" @click="restablecerPermisos">
            Sí, Restablecer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR DE NOTIFICACIONES -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" top right :timeout="3500">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'MatrizPermisosCorrespondencia',
  data() {
    return {
      tabIndex: 0,
      tabsAsignacion: [
        { value: 'UNIDAD', label: 'Unidades', icon: 'mdi-domain' },
        { value: 'CARGO', label: 'Usuarios / Puestos', icon: 'mdi-account-multiple' },
        { value: 'VENTANILLA', label: 'Ventanillas', icon: 'mdi-inbox-multiple' },
        { value: 'GESTOR_CORRESPONDENCIA', label: 'Gestor Correspondencia', icon: 'mdi-archive-cog' },
        { value: 'VENTANILLA_DIGITAL', label: 'Ventanilla Digital', icon: 'mdi-earth' },
      ],

      regionales: [],
      filtroRegional: null,
      sujetos: [],
      sujetoSeleccionado: null,

      arbolJerarquico: [],
      destinosSeleccionados: [],
      filtroArbol: '',

      cargandoSujetos: false,
      cargandoArbol: false,
      guardando: false,
      restableciendo: false,

      dialogResumen: false,
      dialogRestablecer: false,
      resumenDetalle: [],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    tabActual() {
      return this.tabsAsignacion[this.tabIndex] ? this.tabsAsignacion[this.tabIndex].value : 'UNIDAD';
    },
    labelBuscadorSujeto() {
      const labels = {
        UNIDAD: 'Buscar Unidad Organizacional por nombre...',
        CARGO: 'Buscar Usuario / Puesto de Trabajo...',
        VENTANILLA: 'Buscar Ventanilla de Recepción...',
        GESTOR_CORRESPONDENCIA: 'Buscar Asistente / Gestor de Despacho...',
        VENTANILLA_DIGITAL: 'Ventanilla Única Digital',
      };
      return labels[this.tabActual] || 'Buscar...';
    },
    textoSujetoActivo() {
      if (this.tabActual === 'VENTANILLA_DIGITAL') return 'la Ventanilla Digital';
      return this.sujetoSeleccionado ? this.sujetoSeleccionado.nombre : 'el sujeto seleccionado';
    },
  },
  mounted() {
    this.cargarRegionales();
    this.cargarArbolJerarquico();
    this.cargarSujetos();
  },
  methods: {
    onTabChange(newIndex) {
      this.sujetoSeleccionado = null;
      this.destinosSeleccionados = [];
      this.cargarSujetos();
      if (this.tabActual === 'VENTANILLA_DIGITAL') {
        this.cargarDestinosAsignados();
      }
    },
    async cargarRegionales() {
      try {
        const res = await window.axios.get('/api/rrhh/regionales');
        if (res.data && res.data.success) {
          this.regionales = res.data.data;
        }
      } catch (e) {}
    },
    async cargarArbolJerarquico() {
      this.cargandoArbol = true;
      try {
        const res = await window.axios.get('/api/correspondencia/permisos/arbol-jerarquico');
        if (res.data && res.data.success) {
          this.arbolJerarquico = res.data.data;
        }
      } catch (e) {
      } finally {
        this.cargandoArbol = false;
      }
    },
    async cargarSujetos() {
      this.cargandoSujetos = true;
      try {
        const params = {
          id_regional: this.filtroRegional,
        };
        const res = await window.axios.get(`/api/correspondencia/permisos/lista-grupos/${this.tabActual}`, { params });
        if (res.data && res.data.success) {
          this.sujetos = res.data.data.filas || [];
        }
      } catch (e) {
      } finally {
        this.cargandoSujetos = false;
      }
    },
    onSujetoSeleccionado() {
      if (this.sujetoSeleccionado) {
        this.cargarDestinosAsignados();
      } else {
        this.destinosSeleccionados = [];
      }
    },
    async cargarDestinosAsignados() {
      try {
        const params = {
          id_origen: this.sujetoSeleccionado ? this.sujetoSeleccionado.id : 'VENTANILLA_DIGITAL_01',
          tipo_origen: this.tabActual,
        };
        const res = await window.axios.get('/api/correspondencia/permisos/destinos-asignados', { params });
        if (res.data && res.data.success) {
          this.destinosSeleccionados = res.data.data;
        }
      } catch (e) {}
    },
    marcarTodos() {
      const todos = [];
      const extraerIds = (items) => {
        items.forEach(node => {
          if (node.children && node.children.length) {
            extraerIds(node.children);
          } else {
            todos.push(node.id);
          }
        });
      };
      extraerIds(this.arbolJerarquico);
      this.destinosSeleccionados = todos;
    },
    desmarcarTodos() {
      this.destinosSeleccionados = [];
    },
    async guardarPermisos() {
      this.guardando = true;
      try {
        const payload = {
          id_origen: this.sujetoSeleccionado ? this.sujetoSeleccionado.id : 'VENTANILLA_DIGITAL_01',
          tipo_origen: this.tabActual,
          destinos: this.destinosSeleccionados,
        };
        const res = await window.axios.post('/api/correspondencia/permisos/guardar-lote', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje(res.data.message || 'Permisos guardados exitosamente.', 'success');
        }
      } catch (e) {
        this.mostrarMensaje('Error al guardar permisos de derivación.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async restablecerPermisos() {
      this.restableciendo = true;
      try {
        const payload = {
          id_origen: this.sujetoSeleccionado ? this.sujetoSeleccionado.id : 'VENTANILLA_DIGITAL_01',
          tipo_origen: this.tabActual,
        };
        const res = await window.axios.post('/api/correspondencia/permisos/restablecer', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje(res.data.message, 'success');
          this.destinosSeleccionados = [];
          this.dialogRestablecer = false;
        }
      } catch (e) {
        this.mostrarMensaje('Error al restablecer permisos.', 'error');
      } finally {
        this.restableciendo = false;
      }
    },
    abrirModalResumen() {
      const mapaNombres = {};
      const indexar = (items) => {
        items.forEach(item => {
          mapaNombres[item.id] = { nombre: item.name, tipo: item.tipo };
          if (item.children && item.children.length) indexar(item.children);
        });
      };
      indexar(this.arbolJerarquico);

      this.resumenDetalle = this.destinosSeleccionados.map(id => ({
        id,
        nombre: mapaNombres[id] ? mapaNombres[id].nombre : id,
        tipo: mapaNombres[id] ? mapaNombres[id].tipo : 'UNIDAD',
      }));

      this.dialogResumen = true;
    },
    imprimirResumen() {
      const contenido = document.getElementById('seccion-impresion-resumen').innerHTML;
      const ventana = window.open('', '_blank');
      ventana.document.write(`
        <html>
          <head>
            <title>Resumen de Permisos de Derivación - EMAPA</title>
            <style>
              body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
              table { width: 100%; border-collapse: collapse; margin-top: 15px; }
              th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
              th { background-color: #f2f2f2; text-align: left; }
              .header { border-bottom: 2px solid #1976D2; padding-bottom: 10px; margin-bottom: 20px; }
            </style>
          </head>
          <body>
            <div class="header">
              <h2>EMAPA - Empresa de Apoyo a la Producción de Alimentos</h2>
              <h3>Matriz de Permisos de Derivación Autorizados</h3>
            </div>
            ${contenido}
          </body>
        </html>
      `);
      ventana.document.close();
      ventana.focus();
      setTimeout(() => ventana.print(), 500);
    },
    getRegionalColor(id) {
      const colors = {
        1: '#1976D2', 2: '#388E3C', 3: '#F57C00', 4: '#7B1FA2',
        5: '#C2185B', 6: '#00796B', 7: '#E64A19', 8: '#5D4037', 9: '#455A64',
      };
      return colors[id] || '#4F46E5';
    },
    getIconoTipo(tipo) {
      const icons = {
        UNIDAD: 'mdi-domain',
        CARGO: 'mdi-account-tie',
        VENTANILLA: 'mdi-inbox-multiple',
        GESTOR_CORRESPONDENCIA: 'mdi-archive-cog',
        VENTANILLA_DIGITAL: 'mdi-earth',
      };
      return icons[tipo] || 'mdi-account';
    },
    mostrarMensaje(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.regional-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
.arbol-destinos {
  max-height: 520px;
  overflow-y: auto;
}
.filtro-arbol-input {
  max-width: 250px;
}
</style>
