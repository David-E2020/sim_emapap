<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-database-cog-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Tablas Paramétricas</h2>
            <span class="text-caption text-secondary">Administración de catálogos y listas maestras del sistema</span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" @click="nuevoRegistro()" class="text-capitalize font-weight-medium rounded-pill">
            <v-icon left small>mdi-plus</v-icon> Nueva Tabla
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PANEL PRINCIPAL MAESTRO / DETALLE -->
    <v-row>
      <!-- TABLAS MAESTRAS (COLUMNA IZQUIERDA) -->
      <v-col cols="12" md="5">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between pb-2">
            <span class="text-subtitle-1 font-weight-bold">
              <v-icon small left color="primary">mdi-table</v-icon> Catálogos Registrados
            </span>
            <v-chip size="small" color="primary" label small class="font-weight-bold">
              {{ registrosFiltrados.length }}
            </v-chip>
          </v-card-title>
          
          <v-divider></v-divider>

          <v-card-text class="pt-3">
            <!-- BUSCADOR IZQUIERDO -->
            <v-text-field
              v-model="searchTabla"
              placeholder="Buscar catálogo..."
              dense
              outlined
              rounded
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
              class="mb-3"
            ></v-text-field>

            <!-- TABLA MAESTRA -->
            <v-simple-table fixed-header height="500px" class="custom-hover-table">
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left font-weight-bold">Nombre del Catálogo</th>
                    <th class="text-center font-weight-bold" style="width: 110px;">Acciones</th>
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
                    <td>
                      <div class="d-flex align-center">
                        <v-icon small color="primary" class="mr-2">mdi-folder-outline</v-icon>
                        <span class="font-weight-medium text-body-2">{{ item.param_tabla }}</span>
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
                        <span>Editar Tabla</span>
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
                        <span>Eliminar Tabla</span>
                      </v-tooltip>
                    </td>
                  </tr>
                  <tr v-if="registrosFiltrados.length === 0">
                    <td colspan="2" class="text-center text-muted py-6">
                      <v-icon large color="grey lighten-1" class="d-block mb-1">mdi-database-search-outline</v-icon>
                      No se encontraron catálogos
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
            <v-card-title class="d-flex align-center justify-space-between pb-2">
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-format-list-bulleted-type</v-icon>
                <div>
                  <span class="text-subtitle-1 font-weight-bold">{{ tabla_seleccionada.param_tabla }}</span>
                  <div class="text-caption text-secondary">Valores pertenecientes al catálogo</div>
                </div>
              </div>
              <v-btn color="primary" small elevation="1" @click="nuevoRegistroN2()" class="text-capitalize rounded-pill">
                <v-icon left small>mdi-plus</v-icon> Nuevo Valor
              </v-btn>
            </v-card-title>
            
            <v-divider></v-divider>

            <v-card-text class="pt-3">
              <!-- BUSCADOR DETALLE -->
              <v-text-field
                v-model="searchDetalle"
                placeholder="Buscar valor o código..."
                dense
                outlined
                rounded
                hide-details
                clearable
                prepend-inner-icon="mdi-magnify"
                class="mb-3"
              ></v-text-field>

              <!-- TABLA VALORES -->
              <v-simple-table fixed-header height="490px">
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th class="text-left font-weight-bold" style="width: 100px;">Código</th>
                      <th class="text-left font-weight-bold">Nombre / Valor</th>
                      <th class="text-left font-weight-bold">Detalle / Descripción</th>
                      <th class="text-center font-weight-bold" style="width: 90px;">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in registrosN2Filtrados" :key="item.id || item.param_codigo">
                      <td>
                        <v-chip small label color="primary" outlined class="font-weight-bold">
                          {{ item.param_codigo || item.param_valor || item.id }}
                        </v-chip>
                      </td>
                      <td class="font-weight-medium">
                        {{ item.param_nombre || item.param_valor || '-' }}
                      </td>
                      <td class="text-caption text-secondary">
                        {{ item.param_descripcion || item.param_detalle || '-' }}
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
                      <td colspan="4" class="text-center text-muted py-8">
                        <v-icon large color="grey lighten-1" class="d-block mb-1">mdi-inbox-remove-outline</v-icon>
                        No hay valores registrados en este catálogo
                      </td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
          </div>

          <!-- ESTADO VACÍO (SIN SELECCIÓN) -->
          <div v-else class="d-flex flex-column align-center justify-center fill-height py-12 text-center">
            <v-avatar color="primary lighten-5" size="80" class="mb-3">
              <v-icon size="40" color="primary">mdi-hand-pointing-left</v-icon>
            </v-avatar>
            <h3 class="text-h6 font-weight-bold color-primary">Selecciona un Catálogo</h3>
            <p class="text-caption text-secondary style-sub max-w-sm px-4">
              Haz clic en cualquier catálogo de la lista izquierda para visualizar y administrar sus ítems.
            </p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO: CREAR / EDITAR TABLA MAESTRA -->
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-table-cog</v-icon>
          <span>{{ isEdit ? 'Editar Tabla Paramétrica' : 'Nueva Tabla Paramétrica' }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form v-model="valid" @submit.prevent="submit" ref="form">
            <v-text-field
              v-model="formRegistro.param_tabla"
              label="Nombre del Catálogo / Tabla"
              outlined
              dense
              required
              hint="Ej: TABLA_TIPO_DOCUMENTO, TABLA_ESTADO_MEDIDOR"
              persistent-hint
              prepend-inner-icon="mdi-label-outline"
              :rules="[(v) => !!v || 'El nombre es requerido']"
            ></v-text-field>
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
            class="text-capitalize px-4"
          >
            {{ isEdit ? 'Guardar Cambios' : 'Guardar Tabla' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: CREAR / EDITAR VALOR DETALLE -->
    <v-dialog v-model="dialogN2" max-width="520" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-format-list-checks</v-icon>
          <span>{{ isEditN2 ? 'Editar Valor de ' + (tabla_seleccionada.param_tabla || '') : 'Nuevo Valor para ' + (tabla_seleccionada.param_tabla || '') }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogN2 = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form v-model="validN2" @submit.prevent="submitN2" ref="formn2">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formRegistroN2.param_codigo"
                  label="Código"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-pound"
                  :rules="[(v) => !!v || 'El código es requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formRegistroN2.param_nombre"
                  label="Nombre / Valor"
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
                  rows="3"
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
            class="text-capitalize px-4"
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
          <v-btn color="error" elevation="1" @click="btnDialogConfirm()" class="text-capitalize px-4">
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
export default {
  data: () => ({
    searchTabla: '',
    searchDetalle: '',
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
      param_tabla: ''
    },
    formRegistroN2: {
      param_foranea: '',
      param_codigo: '',
      param_nombre: '',
      param_detalle: ''
    },
    loading: false,
    loadingN2: false,
    tabla_seleccionada: {},
  }),

  computed: {
    registrosFiltrados() {
      if (!this.searchTabla) return this.registros;
      const search = this.searchTabla.toLowerCase();
      return this.registros.filter(item =>
        item.param_tabla && item.param_tabla.toLowerCase().includes(search)
      );
    },
    registrosN2Filtrados() {
      if (!this.searchDetalle) return this.registrosN2;
      const search = this.searchDetalle.toLowerCase();
      return this.registrosN2.filter(item => {
        const codigo = (item.param_codigo || item.param_valor || '').toString().toLowerCase();
        const nombre = (item.param_nombre || item.param_valor || '').toString().toLowerCase();
        const desc = (item.param_descripcion || item.param_detalle || '').toString().toLowerCase();
        return codigo.includes(search) || nombre.includes(search) || desc.includes(search);
      });
    }
  },

  mounted() {
    this.getParametrica();
  },

  methods: {
    getParametrica() {
      this.registros = [];
      axios.get(`api/parametrica-api`)
        .then((response) => {
          this.registros = response.data;
          if (this.registros.length > 0 && !this.tabla_seleccionada.id) {
            this.detalleRegistro(this.registros[0]);
          }
        })
        .catch((error) => {
          this.showSnackbar('Error al cargar paramétricas', 'error');
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

    eliminarRegistroN2(item) {
      const url = "api/parametrica-api/" + item.id;
      axios.delete(url).then((response) => {
        this.detalleRegistro(this.tabla_seleccionada);
        this.showSnackbar(response.data.mensaje || 'Registro eliminado con éxito', 'success');
      }).catch(() => {
        this.showSnackbar('Error al eliminar el registro', 'error');
      });
    },

    eliminarRegistro(item) {
      const url = "api/parametrica-api/" + item.id;
      axios.delete(url).then((response) => {
        this.showSnackbar(response.data.mensaje || 'Catálogo eliminado con éxito', 'success');
        if (this.tabla_seleccionada.id === item.id) {
          this.tabla_seleccionada = {};
          this.registrosN2 = [];
        }
        this.getParametrica();
      }).catch((error) => {
        this.showSnackbar('Error al eliminar catálogo', 'error');
      });
    },

    detalleRegistro(item) {
      this.tabla_seleccionada = item;
      axios.get('api/parametrica-api/' + item.param_tabla)
        .then((response) => {
          this.registrosN2 = response.data;
        })
        .catch(() => {
          this.registrosN2 = [];
        });
    },

    editarRegistro(item) {
      this.isEdit = true;
      this.dialog = true;
      this.formRegistro = {
        id: item.id,
        param_tabla: item.param_tabla
      };
    },

    editarRegistroN2(item) {
      this.isEditN2 = true;
      this.dialogN2 = true;
      this.formRegistroN2 = {
        id: item.id,
        param_codigo: item.param_codigo || item.param_valor || '',
        param_nombre: item.param_nombre || item.param_valor || '',
        param_detalle: item.param_descripcion || item.param_detalle || '',
        param_tabla: this.tabla_seleccionada.param_tabla
      };
    },

    nuevoRegistroN2() {
      this.isEditN2 = false;
      this.dialogN2 = true;
      this.formRegistroN2 = {
        id: "",
        param_codigo: "",
        param_nombre: "",
        param_detalle: "",
        param_tabla: this.tabla_seleccionada.param_tabla
      };
    },

    nuevoRegistro() {
      this.isEdit = false;
      this.dialog = true;
      this.formRegistro = { param_tabla: '' };
    },

    submitN2() {
      if (!this.$refs.formn2.validate()) return;
      this.loadingN2 = true;
      this.formRegistroN2.param_tabla = this.tabla_seleccionada.param_tabla;
      
      axios.post("api/registrar_campo", this.formRegistroN2)
        .then((response) => {
          this.detalleRegistro(this.tabla_seleccionada);
          this.dialogN2 = false;
          this.loadingN2 = false;
          this.showSnackbar(this.isEditN2 ? "Valor actualizado correctamente" : "Nuevo valor registrado", "success");
        })
        .catch(() => {
          this.loadingN2 = false;
          this.showSnackbar("Error al guardar el valor", "error");
        });
    },

    submit() {
      if (!this.$refs.form.validate()) return;
      this.loading = true;
      axios.post("api/parametrica-api", this.formRegistro)
        .then((response) => {
          this.getParametrica();
          this.dialog = false;
          this.loading = false;
          this.showSnackbar(this.isEdit ? "Catálogo actualizado" : "Nuevo catálogo creado", "success");
        })
        .catch(() => {
          this.loading = false;
          this.showSnackbar("Error al guardar catálogo", "error");
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
