<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-4 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-database-cog-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Tablas Paramétricas y Catálogos</h2>
            <span class="text-caption text-secondary">
              Administración centralizada de listas maestras y catálogos encapsulados por módulo
            </span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" @click="nuevoRegistro()" class="text-capitalize font-weight-medium rounded-pill elevation-1">
            <v-icon left small>mdi-plus</v-icon> Nuevo Catálogo
          </v-btn>
          <v-btn color="secondary" outlined @click="getParametrica()" :loading="loading" class="text-capitalize rounded-pill">
            <v-icon left small>mdi-refresh</v-icon> Recargar
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- NAVEGACIÓN Y ENCAPSULAMIENTO POR MÓDULOS (TABS) -->
    <v-card class="mb-4 erp-card-elevated" rounded="lg">
      <v-tabs
        v-model="tabModuloActivo"
        color="primary"
        show-arrows
        background-color="transparent"
        class="px-2"
      >
        <v-tab
          v-for="m in modulosDisponibles"
          :key="m.id"
          class="text-capitalize font-weight-medium"
        >
          <v-icon left small :color="m.color">{{ m.icono }}</v-icon>
          {{ m.label }}
          <v-chip
            x-small
            :color="m.color"
            class="ml-2 font-weight-bold"
            :text-color="m.id === 'TODOS' ? '' : 'white'"
          >
            {{ contarTablasPorModulo(m.id) }}
          </v-chip>
        </v-tab>
      </v-tabs>
    </v-card>

    <!-- PANEL PRINCIPAL MAESTRO / DETALLE -->
    <v-row>
      <!-- TABLAS MAESTRAS (COLUMNA IZQUIERDA) -->
      <v-col cols="12" md="5">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between pb-2">
            <div class="d-flex align-center">
              <v-icon small left color="primary">mdi-table-settings</v-icon>
              <span class="text-subtitle-1 font-weight-bold">Catálogos Disponibles</span>
            </div>
            <v-chip size="small" color="primary" outlined small class="font-weight-bold">
              {{ registrosFiltrados.length }} catálogos
            </v-chip>
          </v-card-title>

          <v-divider></v-divider>

          <v-card-text class="pt-3">
            <!-- BUSCADOR IZQUIERDO -->
            <v-text-field
              v-model="searchTabla"
              placeholder="Buscar por nombre, código o vista..."
              dense
              outlined
              rounded
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
              class="mb-3"
            ></v-text-field>

            <!-- TABLA MAESTRA CONTEXTUALIZADA -->
            <v-simple-table fixed-header height="520px" class="custom-hover-table">
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left font-weight-bold">Catálogo y Módulo</th>
                    <th class="text-center font-weight-bold" style="width: 105px;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="item in registrosFiltrados"
                    :key="item.id"
                    :class="{ 'active-row': tabla_seleccionada.id === item.id }"
                    @click="detalleRegistro(item)"
                    style="cursor: pointer;"
                  >
                    <td class="py-2">
                      <div class="d-flex align-center justify-space-between mb-1">
                        <v-chip
                          x-small
                          label
                          :color="item.modulo_color || 'primary'"
                          text-color="white"
                          class="font-weight-bold"
                        >
                          <v-icon x-small left>{{ item.modulo_icono || 'mdi-folder-outline' }}</v-icon>
                          {{ item.modulo_label || item.modulo }}
                        </v-chip>
                        <v-chip x-small outlined color="secondary" class="font-weight-medium">
                          {{ item.param_valor_contador || 0 }} valores
                        </v-chip>
                      </div>

                      <div class="font-weight-bold text-body-2 mb-1" style="line-height: 1.25;">
                        {{ item.nombre_amigable || item.param_nombre }}
                      </div>

                      <div class="text-caption text-secondary font-mono">
                        {{ item.param_tabla }}
                      </div>
                    </td>

                    <td class="text-center" @click.stop>
                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                            icon
                            x-small
                            color="primary"
                            v-bind="attrs"
                            v-on="on"
                            @click="detalleRegistro(item)"
                          >
                            <v-icon small>mdi-eye-outline</v-icon>
                          </v-btn>
                        </template>
                        <span>Ver Valores</span>
                      </v-tooltip>

                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                            icon
                            x-small
                            color="success"
                            v-bind="attrs"
                            v-on="on"
                            @click="editarRegistro(item)"
                          >
                            <v-icon small>mdi-pencil-outline</v-icon>
                          </v-btn>
                        </template>
                        <span>Editar Catálogo</span>
                      </v-tooltip>

                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                            icon
                            x-small
                            color="error"
                            v-bind="attrs"
                            v-on="on"
                            @click="confirmDelete(item, 'n1')"
                          >
                            <v-icon small>mdi-trash-can-outline</v-icon>
                          </v-btn>
                        </template>
                        <span>Eliminar Catálogo</span>
                      </v-tooltip>
                    </td>
                  </tr>

                  <tr v-if="registrosFiltrados.length === 0">
                    <td colspan="2" class="text-center text-muted py-8">
                      <v-icon large color="grey lighten-1" class="d-block mb-1">mdi-database-search-outline</v-icon>
                      No se encontraron catálogos para este módulo o búsqueda
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- VALORES DETALLE (COLUMNA DERECHA) -->
      <v-col cols="12" md="7">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <!-- CABECERA CUANDO HAY TABLA SELECCIONADA -->
          <div v-if="tabla_seleccionada && tabla_seleccionada.param_tabla">
            <v-card-title class="d-flex align-center justify-space-between pb-2 flex-wrap">
              <div class="d-flex align-center mb-1 mb-sm-0">
                <v-avatar :color="(tabla_seleccionada.modulo_color || 'primary') + ' lighten-5'" size="42" class="mr-3">
                  <v-icon :color="tabla_seleccionada.modulo_color || 'primary'">
                    {{ tabla_seleccionada.modulo_icono || 'mdi-format-list-bulleted-type' }}
                  </v-icon>
                </v-avatar>
                <div>
                  <div class="d-flex align-center gap-2 flex-wrap">
                    <span class="text-subtitle-1 font-weight-bold">
                      {{ tabla_seleccionada.nombre_amigable || tabla_seleccionada.param_nombre }}
                    </span>
                    <v-chip x-small :color="tabla_seleccionada.modulo_color || 'primary'" text-color="white" class="font-weight-bold">
                      {{ tabla_seleccionada.modulo_label || tabla_seleccionada.modulo }}
                    </v-chip>
                  </div>
                  <div class="text-caption text-secondary font-mono">
                    Código técnico: <strong>{{ tabla_seleccionada.param_tabla }}</strong>
                  </div>
                </div>
              </div>

              <v-btn color="primary" small elevation="1" @click="nuevoRegistroN2()" class="text-capitalize rounded-pill">
                <v-icon left small>mdi-plus</v-icon> Nuevo Valor
              </v-btn>
            </v-card-title>

            <!-- TARJETA CONTEXTUAL DE ENCAPSULAMIENTO Y USO -->
            <div class="px-4 pb-2">
              <v-sheet rounded="lg" color="grey lighten-4" class="pa-3 text-caption">
                <div class="d-flex align-start mb-1" v-if="tabla_seleccionada.descripcion_uso || tabla_seleccionada.param_descripcion">
                  <v-icon x-small color="grey darken-2" class="mr-1 mt-1">mdi-information-outline</v-icon>
                  <span><strong>Propósito:</strong> {{ tabla_seleccionada.descripcion_uso || tabla_seleccionada.param_descripcion }}</span>
                </div>
                <div
                  class="d-flex align-center flex-wrap gap-1 mt-1"
                  v-if="tabla_seleccionada.vistas_asociadas && tabla_seleccionada.vistas_asociadas.length"
                >
                  <strong class="text-secondary mr-1">Vistas consumidoras:</strong>
                  <v-chip
                    x-small
                    outlined
                    color="primary"
                    class="mr-1 font-weight-medium"
                    v-for="v in tabla_seleccionada.vistas_asociadas"
                    :key="v"
                  >
                    <v-icon x-small left>mdi-monitor-dashboard</v-icon> {{ v }}
                  </v-chip>
                </div>
              </v-sheet>
            </div>

            <v-divider></v-divider>

            <v-card-text class="pt-3">
              <!-- BUSCADOR DETALLE -->
              <v-text-field
                v-model="searchDetalle"
                placeholder="Buscar valor por código, descripción o nombre..."
                dense
                outlined
                rounded
                hide-details
                clearable
                prepend-inner-icon="mdi-magnify"
                class="mb-3"
              ></v-text-field>

              <!-- TABLA VALORES -->
              <v-simple-table fixed-header height="410px">
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th class="text-center font-weight-bold" style="width: 80px;">Código</th>
                      <th class="text-left font-weight-bold">Nombre / Etiqueta</th>
                      <th class="text-center font-weight-bold" style="width: 80px;">Orden</th>
                      <th class="text-left font-weight-bold">Descripción / Detalle</th>
                      <th class="text-center font-weight-bold" style="width: 90px;">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in registrosN2Filtrados" :key="item.id || item.param_codigo">
                      <td class="text-center">
                        <v-chip small label color="primary" outlined class="font-weight-bold font-mono">
                          {{ item.param_codigo || item.param_valor || item.id }}
                        </v-chip>
                      </td>
                      <td class="font-weight-bold text-body-2">
                        {{ item.param_nombre || '-' }}
                      </td>
                      <td class="text-center font-mono text-caption text-secondary">
                        {{ item.param_valor || '-' }}
                      </td>
                      <td class="text-caption text-secondary">
                        {{ item.param_descripcion || '-' }}
                      </td>
                      <td class="text-center">
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="success"
                              v-bind="attrs"
                              v-on="on"
                              @click="editarRegistroN2(item)"
                            >
                              <v-icon small>mdi-pencil-outline</v-icon>
                            </v-btn>
                          </template>
                          <span>Editar Valor</span>
                        </v-tooltip>

                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="error"
                              v-bind="attrs"
                              v-on="on"
                              @click="confirmDelete(item, 'n2')"
                            >
                              <v-icon small>mdi-trash-can-outline</v-icon>
                            </v-btn>
                          </template>
                          <span>Eliminar Valor</span>
                        </v-tooltip>
                      </td>
                    </tr>

                    <tr v-if="registrosN2Filtrados.length === 0">
                      <td colspan="5" class="text-center py-8">
                        <v-icon large color="grey lighten-1" class="d-block mb-2">mdi-inbox-remove-outline</v-icon>
                        <div class="text-subtitle-2 font-weight-bold">No hay valores registrados en este catálogo</div>
                        <div class="text-caption text-secondary mb-3" v-if="esTablaFoxPro(tabla_seleccionada.param_tabla)">
                          Este catálogo corresponde a FoxPro. Puede sincronizarlo automáticamente desde el Migrador o registrar ítems manualmente.
                        </div>
                        <div class="d-flex justify-center gap-2" v-if="esTablaFoxPro(tabla_seleccionada.param_tabla)">
                          <v-btn small color="primary" outlined to="/datos/migrador-respaldos" class="text-capitalize rounded-pill">
                            <v-icon left small>mdi-database-import</v-icon> Ir al Migrador FoxPro
                          </v-btn>
                          <v-btn small color="primary" @click="nuevoRegistroN2()" class="text-capitalize rounded-pill">
                            <v-icon left small>mdi-plus</v-icon> Agregar Valor Manual
                          </v-btn>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
          </div>

          <!-- ESTADO VACÍO (SIN SELECCIÓN O MÓDULO LIMPIO) -->
          <div v-else class="d-flex flex-column align-center justify-center fill-height py-12 text-center">
            <template v-if="moduloSeleccionadoId === 'COMERCIAL' && registrosFiltrados.length === 0">
              <v-avatar color="primary lighten-5" size="80" class="mb-3">
                <v-icon size="40" color="primary">mdi-water-pump</v-icon>
              </v-avatar>
              <h3 class="text-h6 font-weight-bold primary--text">Módulo Comercial Limpio</h3>
              <p class="text-caption text-secondary style-sub max-w-sm px-6 mb-3">
                La base de datos se encuentra limpia. Los catálogos comerciales (Estados de Abonado y Conceptos de Cobro) se generarán automáticamente al ejecutar la migración desde FoxPro (estado.dbf y concepin.dbf).
              </p>
              <div class="d-flex justify-center gap-2">
                <v-btn small color="primary" outlined to="/datos/migrador-respaldos" class="text-capitalize rounded-pill">
                  <v-icon left small>mdi-database-import</v-icon> Ir al Migrador FoxPro
                </v-btn>
                <v-btn small color="primary" @click="nuevoRegistro()" class="text-capitalize rounded-pill">
                  <v-icon left small>mdi-plus</v-icon> Crear Catálogo
                </v-btn>
              </div>
            </template>
            <template v-else>
              <v-avatar color="primary lighten-5" size="80" class="mb-3">
                <v-icon size="40" color="primary">mdi-hand-pointing-left</v-icon>
              </v-avatar>
              <h3 class="text-h6 font-weight-bold color-primary">Selecciona un Catálogo</h3>
              <p class="text-caption text-secondary style-sub max-w-sm px-4">
                Haz clic en cualquier catálogo de la lista izquierda para visualizar, administrar y enriquecer sus ítems.
              </p>
            </template>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO: CREAR / EDITAR TABLA MAESTRA -->
    <v-dialog v-model="dialog" max-width="540" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-table-plus</v-icon>
          <span>{{ isEdit ? 'Editar Catálogo Paramétrico' : 'Nuevo Catálogo Paramétrico' }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form v-model="valid" @submit.prevent="submit" ref="form">
            <v-select
              v-model="formRegistro.modulo"
              :items="modulosSelector"
              item-text="label"
              item-value="id"
              label="Módulo Asignado *"
              outlined
              dense
              prepend-inner-icon="mdi-view-grid-plus"
              @change="actualizarPrefijoTabla"
            ></v-select>

            <v-text-field
              v-model="formRegistro.param_nombre"
              label="Nombre Amigable del Catálogo *"
              placeholder="Ej. Tipos de Válvula, Estados del Medidor..."
              outlined
              dense
              required
              prepend-inner-icon="mdi-format-title"
              :rules="[(v) => !!v || 'El nombre es requerido']"
              @input="sugerirCodigoTabla"
            ></v-text-field>

            <v-text-field
              v-model="formRegistro.param_tabla"
              label="Código Técnico de Tabla *"
              placeholder="TABLA_COMERCIAL_..."
              outlined
              dense
              required
              class="font-mono"
              prepend-inner-icon="mdi-code-tags"
              :rules="[(v) => !!v || 'El código técnico es requerido']"
            ></v-text-field>

            <v-textarea
              v-model="formRegistro.param_descripcion"
              label="Propósito / Descripción de Uso"
              placeholder="Indica para qué sirve este catálogo y qué pantallas o vistas lo consumen..."
              outlined
              dense
              rows="3"
              prepend-inner-icon="mdi-text-short"
            ></v-textarea>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" @click="dialog = false" class="text-capitalize">Cancelar</v-btn>
          <v-btn
            color="primary"
            elevation="1"
            :loading="loading"
            :disabled="loading || !valid"
            @click="submit"
            class="text-capitalize px-4 rounded-pill"
          >
            {{ isEdit ? 'Actualizar Catálogo' : 'Crear Catálogo' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: CREAR / EDITAR VALOR DETALLE -->
    <v-dialog v-model="dialogN2" max-width="520" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-format-list-checks</v-icon>
          <span>{{ isEditN2 ? 'Editar Valor' : 'Nuevo Valor' }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogN2 = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <div class="mb-3 text-caption text-secondary">
            Catálogo destino: <strong>{{ tabla_seleccionada.nombre_amigable || tabla_seleccionada.param_tabla }}</strong>
          </div>

          <v-form v-model="validN2" @submit.prevent="submitN2" ref="formn2">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formRegistroN2.param_codigo"
                  label="Código *"
                  placeholder="Ej. A, C, 01, M..."
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-pound"
                  :rules="[(v) => !!v || 'El código es requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="formRegistroN2.param_valor"
                  type="number"
                  label="Orden / Valor Numérico"
                  outlined
                  dense
                  prepend-inner-icon="mdi-numeric"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="formRegistroN2.param_nombre"
                  label="Nombre / Etiqueta *"
                  placeholder="Ej. ACTIVO, CORTE, RECONEXION..."
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-rename-box"
                  :rules="[(v) => !!v || 'El nombre es requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="formRegistroN2.param_detalle"
                  label="Descripción / Detalle Adicional"
                  outlined
                  dense
                  rows="2"
                  prepend-inner-icon="mdi-text-short"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" @click="dialogN2 = false" class="text-capitalize">Cancelar</v-btn>
          <v-btn
            color="primary"
            elevation="1"
            :loading="loadingN2"
            :disabled="loadingN2 || !validN2"
            @click="submitN2"
            class="text-capitalize px-4 rounded-pill"
          >
            {{ isEditN2 ? 'Guardar Cambios' : 'Guardar Valor' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CONFIRMACIÓN DE ELIMINACIÓN -->
    <v-dialog v-model="dialogConfirm" max-width="400" persistent>
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon>
          <span>Confirmar Eliminación</span>
        </v-card-title>
        <v-card-text class="pt-4 text-body-1">
          ¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.
        </v-card-text>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" @click="dialogConfirm = false" class="text-capitalize">Cancelar</v-btn>
          <v-btn color="error" elevation="1" @click="btnDialogConfirm()" class="text-capitalize px-4 rounded-pill">
            Sí, Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR DE NOTIFICACIÓN -->
    <v-snackbar v-model="snackbar.status" bottom right :color="snackbar.color" :timeout="2500" rounded="pill">
      <div class="d-flex align-center">
        <v-icon left color="white">mdi-check-circle-outline</v-icon>
        <span>{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn icon dark v-bind="attrs" @click="snackbar.status = false">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Parametrica',
  data: () => ({
    tabModuloActivo: 0,
    searchTabla: '',
    searchDetalle: '',
    modulosDisponibles: [
      { id: 'TODOS', label: 'Todos los Catálogos', icono: 'mdi-view-grid-outline', color: 'primary' },
      { id: 'COMERCIAL', label: 'Gestión Comercial', icono: 'mdi-water-pump', color: 'primary' },
      { id: 'RRHH', label: 'Recursos Humanos', icono: 'mdi-account-group', color: 'teal' },
      { id: 'CORRESPONDENCIA', label: 'Correspondencia', icono: 'mdi-email-seal', color: 'deep-orange' },
      { id: 'GENERAL', label: 'General / Sistema', icono: 'mdi-cog', color: 'indigo' },
    ],
    modulosSelector: [
      { id: 'COMERCIAL', label: 'Gestión Comercial' },
      { id: 'RRHH', label: 'Recursos Humanos' },
      { id: 'CORRESPONDENCIA', label: 'Correspondencia y Trámites' },
      { id: 'GENERAL', label: 'General / Sistema' },
    ],
    dataDialogConfirm: {
      item: {},
      tipo: ''
    },
    snackbar: {
      status: false,
      text: "",
      color: "success"
    },
    registros: [],
    registrosN2: [],
    isEditN2: false,
    valid: false,
    validN2: false,
    isEdit: false,
    dialog: false,
    dialogN2: false,
    dialogConfirm: false,
    formRegistro: {
      id: null,
      modulo: 'COMERCIAL',
      param_tabla: '',
      param_nombre: '',
      param_descripcion: '',
    },
    formRegistroN2: {
      id: null,
      param_codigo: '',
      param_nombre: '',
      param_valor: 1,
      param_detalle: '',
      param_tabla: ''
    },
    loading: false,
    loadingN2: false,
    tabla_seleccionada: {},
  }),

  computed: {
    moduloSeleccionadoId() {
      const tab = this.modulosDisponibles[this.tabModuloActivo];
      return tab ? tab.id : 'TODOS';
    },

    registrosFiltrados() {
      let list = this.registros;

      // Filtro por módulo activo de las pestañas
      if (this.moduloSeleccionadoId !== 'TODOS') {
        list = list.filter(item => item.modulo === this.moduloSeleccionadoId);
      }

      // Filtro por término de búsqueda
      if (!this.searchTabla) return list;
      const search = this.searchTabla.toLowerCase();
      return list.filter(item => {
        const tabla = (item.param_tabla || '').toLowerCase();
        const nombre = (item.nombre_amigable || item.param_nombre || '').toLowerCase();
        const desc = (item.descripcion_uso || item.param_descripcion || '').toLowerCase();
        const vistas = (item.vistas_asociadas || []).join(' ').toLowerCase();
        return tabla.includes(search) || nombre.includes(search) || desc.includes(search) || vistas.includes(search);
      });
    },

    registrosN2Filtrados() {
      if (!this.searchDetalle) return this.registrosN2;
      const search = this.searchDetalle.toLowerCase();
      return this.registrosN2.filter(item => {
        const codigo = (item.param_codigo || item.param_valor || '').toString().toLowerCase();
        const nombre = (item.param_nombre || '').toString().toLowerCase();
        const desc = (item.param_descripcion || item.param_detalle || '').toString().toLowerCase();
        return codigo.includes(search) || nombre.includes(search) || desc.includes(search);
      });
    }
  },

  watch: {
    tabModuloActivo() {
      // Si la tabla seleccionada no pertenece al nuevo módulo, seleccionar la primera disponible
      if (this.registrosFiltrados.length > 0) {
        const existeEnModulo = this.registrosFiltrados.some(t => t.id === this.tabla_seleccionada.id);
        if (!existeEnModulo) {
          this.detalleRegistro(this.registrosFiltrados[0]);
        }
      }
    }
  },

  mounted() {
    this.getParametrica();
  },

  methods: {
    contarTablasPorModulo(moduloId) {
      if (moduloId === 'TODOS') return this.registros.length;
      return this.registros.filter(r => r.modulo === moduloId).length;
    },

    esTablaFoxPro(tabla) {
      return ['TABLA_COMERCIAL_ESTADOS_ABONADO', 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS'].includes(tabla);
    },

    getParametrica() {
      this.loading = true;
      axios.get('api/parametrica-api')
        .then((response) => {
          this.registros = response.data || [];
          if (this.registros.length > 0) {
            if (this.tabla_seleccionada.id) {
              const reselect = this.registros.find(r => r.id === this.tabla_seleccionada.id);
              this.detalleRegistro(reselect || this.registros[0]);
            } else {
              this.detalleRegistro(this.registros[0]);
            }
          } else {
            this.tabla_seleccionada = {};
            this.registrosN2 = [];
          }
        })
        .catch(() => {
          this.showSnackbar('Error al cargar tablas paramétricas', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    detalleRegistro(item) {
      this.tabla_seleccionada = item;
      axios.get('api/parametrica-api/' + item.param_tabla)
        .then((response) => {
          this.registrosN2 = response.data || [];
        })
        .catch(() => {
          this.registrosN2 = [];
        });
    },

    actualizarPrefijoTabla() {
      if (this.isEdit) return;
      this.sugerirCodigoTabla();
    },

    sugerirCodigoTabla() {
      if (this.isEdit) return;
      const mod = this.formRegistro.modulo || 'COMERCIAL';
      const clean = (this.formRegistro.param_nombre || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');

      this.formRegistro.param_tabla = `TABLA_${mod}_${clean}`;
    },

    nuevoRegistro() {
      this.isEdit = false;
      this.dialog = true;
      this.formRegistro = {
        id: null,
        modulo: this.moduloSeleccionadoId === 'TODOS' ? 'COMERCIAL' : this.moduloSeleccionadoId,
        param_tabla: '',
        param_nombre: '',
        param_descripcion: '',
      };
      this.sugerirCodigoTabla();
    },

    editarRegistro(item) {
      this.isEdit = true;
      this.dialog = true;
      this.formRegistro = {
        id: item.id,
        modulo: item.modulo || 'COMERCIAL',
        param_tabla: item.param_tabla,
        param_nombre: item.nombre_amigable || item.param_nombre,
        param_descripcion: item.descripcion_uso || item.param_descripcion || '',
      };
    },

    nuevoRegistroN2() {
      this.isEditN2 = false;
      this.dialogN2 = true;
      const siguienteValor = (this.registrosN2.length || 0) + 1;
      this.formRegistroN2 = {
        id: null,
        param_codigo: '',
        param_nombre: '',
        param_valor: siguienteValor,
        param_detalle: '',
        param_tabla: this.tabla_seleccionada.param_tabla
      };
    },

    editarRegistroN2(item) {
      this.isEditN2 = true;
      this.dialogN2 = true;
      this.formRegistroN2 = {
        id: item.id,
        param_codigo: item.param_codigo || '',
        param_nombre: item.param_nombre || '',
        param_valor: item.param_valor || 1,
        param_detalle: item.param_descripcion || '',
        param_tabla: this.tabla_seleccionada.param_tabla
      };
    },

    submit() {
      if (!this.$refs.form.validate()) return;
      this.loading = true;
      axios.post('api/parametrica-api', this.formRegistro)
        .then(() => {
          this.getParametrica();
          this.dialog = false;
          this.showSnackbar(this.isEdit ? 'Catálogo actualizado correctamente' : 'Nuevo catálogo creado', 'success');
        })
        .catch(() => {
          this.showSnackbar('Error al guardar catálogo', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    submitN2() {
      if (!this.$refs.formn2.validate()) return;
      this.loadingN2 = true;
      this.formRegistroN2.param_tabla = this.tabla_seleccionada.param_tabla;

      axios.post('api/registrar_campo', this.formRegistroN2)
        .then(() => {
          this.detalleRegistro(this.tabla_seleccionada);
          this.dialogN2 = false;
          this.showSnackbar(this.isEditN2 ? 'Valor actualizado correctamente' : 'Nuevo valor registrado', 'success');
        })
        .catch(() => {
          this.showSnackbar('Error al guardar el valor', 'error');
        })
        .finally(() => {
          this.loadingN2 = false;
        });
    },

    confirmDelete(item, tipo) {
      this.dataDialogConfirm = { item, tipo };
      this.dialogConfirm = true;
    },

    btnDialogConfirm() {
      if (this.dataDialogConfirm.tipo === 'n1') {
        this.eliminarRegistro(this.dataDialogConfirm.item);
      } else if (this.dataDialogConfirm.tipo === 'n2') {
        this.eliminarRegistroN2(this.dataDialogConfirm.item);
      }
      this.dialogConfirm = false;
    },

    eliminarRegistro(item) {
      axios.delete('api/parametrica-api/' + item.id)
        .then((response) => {
          this.showSnackbar(response.data.mensaje || 'Catálogo eliminado con éxito', 'success');
          if (this.tabla_seleccionada.id === item.id) {
            this.tabla_seleccionada = {};
            this.registrosN2 = [];
          }
          this.getParametrica();
        })
        .catch(() => {
          this.showSnackbar('Error al eliminar catálogo', 'error');
        });
    },

    eliminarRegistroN2(item) {
      axios.delete('api/parametrica-api/' + item.id)
        .then((response) => {
          this.detalleRegistro(this.tabla_seleccionada);
          this.showSnackbar(response.data.mensaje || 'Valor eliminado con éxito', 'success');
        })
        .catch(() => {
          this.showSnackbar('Error al eliminar el valor', 'error');
        });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    }
  }
};
</script>

<style scoped>
.active-row {
  background-color: rgba(var(--v-theme-primary), 0.08) !important;
  border-left: 4px solid var(--v-primary-base);
}

.custom-hover-table tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.style-sub {
  line-height: 1.4;
}
</style>
