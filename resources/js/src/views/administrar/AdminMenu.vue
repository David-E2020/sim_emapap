<style scoped>
.show-btns {
  transition: opacity .4s ease-in-out;
}

.handle {
  cursor: row-resize;
}

.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}

.flip-list-move {
  transition: transform 0.3s ease;
}
</style>

<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-sitemap</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Diseñador de Menús del Sistema</h2>
            <span class="text-caption text-secondary">Organización jerárquica de menús principales, submódulos, rutas y orden de navegación</span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="btnNuevoMenu(0)">
            <v-icon left small>mdi-folder-plus-outline</v-icon> Nuevo Menú Principal
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- SECCIÓN ESTRUCTURA DE MENÚS (DRAG-AND-DROP) -->
    <v-row>
      <!-- MENÚS PRINCIPALES (NIVEL 0) -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between py-3">
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-folder-cog-outline</v-icon>
              <span class="text-subtitle-1 font-weight-bold">Menús Principales</span>
            </div>
            <v-btn color="primary" x-small elevation="1" @click="btnNuevoMenu(0)" class="text-capitalize rounded-pill">
              <v-icon x-small left>mdi-plus</v-icon> Añadir Menú
            </v-btn>
          </v-card-title>

          <v-divider></v-divider>

          <v-card-text class="pt-3">
            <div class="text-caption text-secondary mb-3">
              <v-icon small left color="info">mdi-information-outline</v-icon>
              Arrastra el ícono <v-icon small color="primary">mdi-drag-horizontal-variant</v-icon> para reordenar la posición.
            </div>

            <v-list shaped class="pa-0" v-if="menus">
              <v-list-item-group color="primary">
                <draggable
                  v-model="menus"
                  :move="checkMoveMenu"
                  v-bind="dragOptions"
                  @start="dragMenu = true"
                  @end="dragMenu = false"
                  handle=".handle"
                >
                  <transition-group type="transition" :name="!dragMenu ? 'flip-list' : null">
                    <v-list-item
                      v-for="item in menus"
                      :key="item.id || item.order"
                      :input-value="itemSelectMenu && itemSelectMenu.id === item.id"
                      @click="btnItemMenu(item)"
                      class="mb-1 rounded-lg"
                    >
                      <v-list-item-icon class="handle mr-2 my-auto">
                        <v-icon color="primary">mdi-drag-horizontal-variant</v-icon>
                      </v-list-item-icon>

                      <v-list-item-icon class="mr-3 my-auto">
                        <v-icon color="primary">{{ item.icon_mdi || 'mdi-folder-outline' }}</v-icon>
                      </v-list-item-icon>

                      <v-list-item-content>
                        <v-list-item-title class="font-weight-bold text-body-2">{{ item.label }}</v-list-item-title>
                        <v-list-item-subtitle class="text-caption text-secondary" v-if="item.route">
                          Ruta: /{{ item.route }}
                        </v-list-item-subtitle>
                      </v-list-item-content>

                      <v-btn icon color="error" small class="mr-1" @click.stop="btnItemMenuDelete(item)">
                        <v-icon small>mdi-delete</v-icon>
                      </v-btn>

                      <v-btn icon color="primary" small class="mr-1" @click.stop="btnItemMenuEdit(item)">
                        <v-icon small>mdi-pencil</v-icon>
                      </v-btn>

                      <v-chip x-small color="primary" outlined class="font-weight-bold">
                        {{ item.sub_menu_n1 ? item.sub_menu_n1.length : 0 }}
                        <v-icon small right>mdi-arrow-right</v-icon>
                      </v-chip>
                    </v-list-item>
                  </transition-group>
                </draggable>
              </v-list-item-group>
            </v-list>

            <div v-else class="py-8 text-center">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- SUBMENÚS (NIVEL 1) -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <div v-if="itemSelectMenu">
            <v-card-title class="d-flex align-center justify-space-between py-3">
              <div class="d-flex align-center">
                <v-icon color="primary" left>mdi-file-tree</v-icon>
                <div>
                  <span class="text-subtitle-1 font-weight-bold">Submenús de {{ itemSelectMenu.label }}</span>
                </div>
              </div>
              <v-btn color="primary" x-small elevation="1" @click="btnNuevoMenu(1)" class="text-capitalize">
                <v-icon x-small left>mdi-plus</v-icon> Añadir Submenú
              </v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pt-3">
              <div class="text-caption text-secondary mb-3">
                <v-icon small left color="info">mdi-information-outline</v-icon>
                Reordena las opciones del menú desplegable arrastrando cada elemento.
              </div>

              <v-list class="pa-0" v-if="subMenus">
                <v-list-item-group color="primary">
                  <draggable
                    v-model="subMenus"
                    v-bind="dragOptions"
                    :move="checkMoveMenu"
                    @start="dragSubMenu = true"
                    @end="dragSubMenu = false"
                    handle=".handle"
                  >
                    <transition-group type="transition" :name="!dragSubMenu ? 'flip-list' : null">
                      <v-list-item
                        v-for="item in subMenus"
                        :key="item.id || item.order"
                        class="mb-1"
                      >
                        <v-list-item-icon class="handle mr-2 my-auto">
                          <v-icon color="primary">mdi-drag-horizontal-variant</v-icon>
                        </v-list-item-icon>

                        <v-list-item-icon class="mr-3 my-auto">
                          <v-icon small color="secondary">{{ item.icon_mdi || 'mdi-file-outline' }}</v-icon>
                        </v-list-item-icon>

                        <v-list-item-content>
                          <v-list-item-title class="font-weight-medium text-body-2">{{ item.label }}</v-list-item-title>
                          <v-list-item-subtitle class="text-caption text-secondary" v-if="item.route">
                            Route name: {{ item.route }}
                          </v-list-item-subtitle>
                        </v-list-item-content>

                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              color="info"
                              small
                              class="mr-1"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="openPermisosSubmenu(item)"
                            >
                              <v-icon small>mdi-shield-lock-outline</v-icon>
                            </v-btn>
                          </template>
                          <span>Gestionar Permisos Granulares Anidados</span>
                        </v-tooltip>

                        <v-btn icon color="error" small class="mr-1" @click.stop="btnItemMenuDelete(item)">
                          <v-icon small>mdi-delete</v-icon>
                        </v-btn>

                        <v-btn icon color="primary" small @click.stop="btnItemMenuEdit(item)">
                          <v-icon small>mdi-pencil</v-icon>
                        </v-btn>
                      </v-list-item>
                    </transition-group>
                  </draggable>
                </v-list-item-group>
              </v-list>

              <div v-if="!subMenus || subMenus.length === 0" class="py-8 text-center text-secondary">
                <v-icon large color="grey lighten-1" class="d-block mb-1">mdi-file-tree-outline</v-icon>
                Este menú principal aún no tiene submenús registrados.
              </div>
            </v-card-text>
          </div>

          <!-- ESTADO VACÍO -->
          <div v-else class="d-flex flex-column align-center justify-center fill-height py-12 text-center">
            <v-avatar color="primary lighten-5" size="70" class="mb-3">
              <v-icon size="36" color="primary">mdi-hand-pointing-left</v-icon>
            </v-avatar>
            <h3 class="text-h6 font-weight-bold color-primary">Selecciona un Menú Principal</h3>
            <p class="text-caption text-secondary style-sub max-w-sm px-4">
              Haz clic en cualquiera de los menús principales a la izquierda para administrar sus ítems secundarios.
            </p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO CREAR/EDITAR ROL -->
    <v-dialog v-model="dialogChip" persistent max-width="420">
      <v-form v-model="valid" @submit.prevent="submit" ref="form">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-shield-account</v-icon>
            <span>{{ isFormRolEdit ? 'Editar Rol' : 'Nuevo Rol' }}</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogChip = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-4">
            <v-text-field
              v-model="formRol.name"
              label="Nombre del Rol"
              outlined
              dense
              required
              prepend-inner-icon="mdi-account-badge"
              placeholder="Ej. Cajero, Operador Lecturista"
              :rules="[v => !!v || 'El nombre del rol es requerido']"
            ></v-text-field>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" @click="dialogChip = false" class="text-capitalize">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="btnLoadingRol"
              :disabled="btnLoadingRol || !valid"
              type="submit"
              class="text-capitalize px-4"
            >
              {{ isFormRolEdit ? 'Guardar Cambios' : 'Crear Rol' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- DIÁLOGO CREAR/EDITAR MENÚ O SUBMENÚ -->
    <v-dialog v-model="dialogMenu" persistent max-width="480">
      <v-form v-model="validMenu" @submit.prevent="submitMenu" ref="formMenu">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-folder-cog-outline</v-icon>
            <span>{{ isFormMenuEdit ? 'Editar ' : 'Nuevo ' }} {{ (menuNivel == 0) ? 'Menú Principal' : 'Submenú' }}</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogMenu = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-4">
            <v-text-field
              v-if="itemSelectMenu && menuNivel != 0"
              disabled
              v-model="itemSelectMenu.label"
              outlined
              dense
              label="Menú Padre"
              prepend-inner-icon="mdi-folder"
            ></v-text-field>

            <v-text-field
              v-model="formMenu.label"
              outlined
              dense
              label="Etiqueta / Nombre Visible"
              required
              prepend-inner-icon="mdi-format-title"
              :rules="[v => !!v || 'El nombre es requerido']"
            ></v-text-field>

            <v-text-field
              v-model="formMenu.route"
              outlined
              dense
              label="Nombre de Ruta Vue Router"
              hint="Ej. usuarios, cobranzas-lecturas"
              persistent-hint
              prepend-inner-icon="mdi-link-variant"
            ></v-text-field>

            <v-autocomplete
              v-model="formMenu.icon"
              :items="menuList"
              item-text="icon"
              item-value="icon"
              dense
              outlined
              label="Ícono MDI"
              prepend-inner-icon="mdi-palette"
              class="mt-3"
              :rules="[v => !!v || 'El icono es requerido']"
            >
              <template v-slot:selection="{ item, selected }">
                <div :input-value="selected" class="d-flex align-center">
                  <v-icon small class="mr-2" color="primary">{{ item.icon | filterMenuIcon }}</v-icon>
                  <span class="text-body-2 font-weight-medium">{{ item.icon | filterMenuName }}</span>
                </div>
              </template>

              <template v-slot:item="{ item }">
                <v-list-item-avatar class="my-0">
                  <v-icon color="primary">{{ item.icon | filterMenuIcon }}</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="text-body-2">{{ item.icon | filterMenuName }}</v-list-item-title>
                </v-list-item-content>
              </template>
            </v-autocomplete>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" @click="dialogMenu = false" class="text-capitalize">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="btnLoadingMenu"
              :disabled="btnLoadingMenu || !validMenu"
              type="submit"
              class="text-capitalize px-4"
            >
              {{ isFormMenuEdit ? 'Guardar Cambios' : 'Guardar Menú' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- DIÁLOGO CONFIRMAR ELIMINACIÓN -->
    <v-dialog v-model="dialogConfirm" persistent max-width="380">
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon>
          <span>Confirmar Eliminación</span>
        </v-card-title>
        <v-card-text class="pt-4 text-body-1">
          ¿Estás seguro de que deseas eliminar este elemento? La acción afectará la estructura de permisos.
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

    <!-- DIÁLOGO EMERGENTE: PERMISOS GRANULARES ANIDADOS POR SUBMENÚ -->
    <v-dialog v-model="dialogPermisosSubmenu" max-width="520" persistent>
      <v-card rounded="lg" v-if="selectedSubmenuPermisos">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-lock-outline</v-icon>
          <span>Acciones y Permisos Anidados</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogPermisosSubmenu = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-alert color="primary lighten-5" border="left" colored-border dense class="mb-4">
            <div class="d-flex align-center">
              <v-icon small color="primary" class="mr-2">{{ selectedSubmenuPermisos.icon_mdi || 'mdi-file-outline' }}</v-icon>
              <div>
                <span class="font-weight-bold text-body-2 primary--text">{{ selectedSubmenuPermisos.label }}</span>
                <div class="text-caption text-secondary">Ruta: <code>{{ selectedSubmenuPermisos.route || 'N/A' }}</code></div>
              </div>
            </div>
          </v-alert>

          <div class="text-subtitle-2 font-weight-bold color-primary mb-2 d-flex align-center">
            <v-icon small color="primary" class="mr-1">mdi-key-chain</v-icon>
            Acciones Registradas para este Submenú
          </div>

          <div v-if="loadingPermisos" class="py-6 text-center">
            <v-progress-circular indeterminate color="primary" size="32"></v-progress-circular>
          </div>

          <div v-else-if="listaPermisosSubmenu && listaPermisosSubmenu.length > 0" class="d-flex flex-wrap gap-2 mb-4">
            <v-chip
              v-for="p in listaPermisosSubmenu"
              :key="p.id"
              color="primary"
              outlined
              class="font-weight-bold ma-1"
              small
            >
              <v-icon x-small left>mdi-check-decagram</v-icon>
              {{ p.name }}
            </v-chip>
          </div>

          <div v-else class="py-4 text-center text-secondary text-caption">
            No hay acciones granulares registradas aún para este submenú.
          </div>

          <v-divider class="my-3"></v-divider>

          <!-- FORMULARIO CREAR NUEVA ACCIÓN DE PERMISO -->
          <div class="text-subtitle-2 font-weight-bold color-primary mb-2 d-flex align-center">
            <v-icon small color="primary" class="mr-1">mdi-plus-circle-outline</v-icon>
            Agregar Nueva Acción Personalizada
          </div>

          <div class="d-flex align-center gap-2">
            <v-text-field
              v-model="nuevaAccionNombre"
              placeholder="Ej. ver, crear, editar, anular, exportar"
              dense
              outlined
              hide-details
              prepend-inner-icon="mdi-shield-edit-outline"
              @keyup.enter="crearNuevaAccionPermiso"
            ></v-text-field>

            <v-btn
              color="primary"
              elevation="1"
              @click="crearNuevaAccionPermiso"
              :disabled="!nuevaAccionNombre.trim()"
              class="text-capitalize"
            >
              Agregar
            </v-btn>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" @click="dialogPermisosSubmenu = false" class="text-capitalize px-4">
            Aceptar y Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- NOTIFICACIONES SNACKBAR NATIVAS -->
    <v-snackbar v-model="snackbar.status" bottom right :color="snackbar.color" :timeout="2200" rounded="pill">
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
import draggable from 'vuedraggable'

export default {
  data: () => ({
    dragMenu: false,
    dragSubMenu: false,
    enabled: true,
    dragging: false,
    menuList: [],
    snackbar: {
      status: false,
      text: '',
      color: 'success'
    },
    roles: null,
    dialogChip: false,
    isFormRolEdit: false,
    btnLoadingRol: false,
    formRol: {
      name: null
    },
    valid: false,
    dialogConfirm: false,
    dataRolDelete: null,
    menus: null,
    subMenus: [],
    dialogMenu: false,
    validMenu: false,
    isFormMenuEdit: false,
    formMenu: {
      label: null,
      icon: null,
      route: null
    },
    btnLoadingMenu: false,
    dataMenuDelete: null,
    itemSelectMenu: null,
    menuNivel: null,
    dialogPermisosSubmenu: false,
    selectedSubmenuPermisos: null,
    listaPermisosSubmenu: [],
    nuevaAccionNombre: '',
    loadingPermisos: false,
  }),

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      };
    }
  },

  mounted() {
    this.getRoles();
    this.getMenus();
    this.loadMenu();
  },

  filters: {
    filterMenuName: function (v) {
      if (!v) return "";
      return v
        .replace(/([a-z])([A-Z])/g, "$1-$2")
        .replace(/[\s_]+/g, '-')
        .toLowerCase()
        .replace('mdi-', '')
        .replaceAll('-', ' ');
    },
    filterMenuIcon: function (v) {
      if (!v) return "";
      return v
        .replace(/([a-z])([A-Z])/g, "$1-$2")
        .replace(/[\s_]+/g, '-')
        .toLowerCase();
    }
  },

  methods: {
    checkMoveMenu: function (e) {
      var data = {
        dragged_id: e.draggedContext.element.id,
        dragged_order: e.draggedContext.element.order,
        related_id: e.relatedContext.element.id,
        related_order: e.relatedContext.element.order
      };

      var url = 'api/menu/change/cambiar-orden';
      axios.post(url, data).catch(error => {});
    },

    openPermisosSubmenu(item) {
      this.selectedSubmenuPermisos = item;
      this.dialogPermisosSubmenu = true;
      this.loadingPermisos = true;
      this.listaPermisosSubmenu = [];
      this.nuevaAccionNombre = '';

      axios.get('api/menu/permisos-submenu/' + item.id)
        .then(response => {
          this.loadingPermisos = false;
          if (response.data) {
            this.listaPermisosSubmenu = response.data.permisos || [];
          }
        })
        .catch(error => {
          this.loadingPermisos = false;
          this.showSnackbar('Error al cargar permisos del submenú', 'error');
        });
    },

    crearNuevaAccionPermiso() {
      if (!this.nuevaAccionNombre.trim() || !this.selectedSubmenuPermisos) return;

      const data = {
        menu_id: this.selectedSubmenuPermisos.id,
        nombre_accion: this.nuevaAccionNombre,
      };

      axios.post('api/menu/crear-permiso-submenu', data)
        .then(response => {
          if (response.data.success) {
            this.showSnackbar('Acción de permiso creada correctamente', 'success');
            this.nuevaAccionNombre = '';
            this.openPermisosSubmenu(this.selectedSubmenuPermisos);
          }
        })
        .catch(error => {
          this.showSnackbar('Error al crear la acción de permiso', 'error');
        });
    },

    loadMenu() {
      const iconsList = [
        'mdiAccount', 'mdiAccountBox', 'mdiAccountBoxMultiple', 'mdiAccountCheck',
        'mdiAccountCircle', 'mdiAccountGroup', 'mdiAccountMultiple', 'mdiAccountSupervisor',
        'mdiAlarm', 'mdiAlertCircle', 'mdiApi', 'mdiApps', 'mdiBank', 'mdiBarcode',
        'mdiBell', 'mdiBookOpenVariant', 'mdiBookmark', 'mdiBriefcase', 'mdiCalculator',
        'mdiCalendar', 'mdiCardText', 'mdiCash', 'mdiCashRegister', 'mdiChartBar',
        'mdiChartLine', 'mdiCheck', 'mdiCheckCircle', 'mdiClipboardList', 'mdiCloud',
        'mdiCog', 'mdiCogs', 'mdiCreditCard', 'mdiCurrencyUsd', 'mdiDatabase',
        'mdiDatabaseCog', 'mdiFileDocument', 'mdiFolder', 'mdiFolderAccount',
        'mdiHome', 'mdiInvoice', 'mdiMapMarker', 'mdiPencil', 'mdiPrinter',
        'mdiShieldAccount', 'mdiSitemap', 'mdiTag', 'mdiWater', 'mdiWaterPump',
        'mdiWidgets'
      ];
      this.menuList = iconsList.map(icon => ({ icon }));
    },

    submitMenu: function () {
      var validateForm = this.$refs.formMenu.validate();
      if (!validateForm) return false;

      this.btnLoadingMenu = true;
      var dataForm = this.formMenu;

      if (this.isFormMenuEdit) {
        var urlEdit = 'api/menu/' + dataForm.id;
        axios
          .put(urlEdit, dataForm)
          .then(response => {
            this.getMenus();
            this.dialogMenu = false;
            this.btnLoadingMenu = false;
            this.showSnackbar('Menú actualizado correctamente', 'success');
          })
          .catch(error => {
            this.btnLoadingMenu = false;
            this.showSnackbar('Error al actualizar el menú', 'error');
          });
      } else {
        var dataSend = dataForm;
        dataSend.order = this.menus ? this.menus.length + 1 : 1;
        if (this.itemSelectMenu != null && this.menuNivel != 0) {
          dataSend.menu_id = this.itemSelectMenu.id;
          dataSend.level = 1;
          dataSend.order = this.subMenus ? this.subMenus.length + 1 : 1;
        }

        axios
          .post('api/menu', dataSend)
          .then(response => {
            if (this.menuNivel != 0 && this.subMenus) {
              this.subMenus.push(response.data);
            }
            this.getMenus();
            this.dialogMenu = false;
            this.btnLoadingMenu = false;
            this.showSnackbar('Nuevo menú registrado', 'success');
          })
          .catch(error => {
            this.btnLoadingMenu = false;
            this.showSnackbar('Error al registrar menú', 'error');
          });
      }
    },

    submit: function () {
      var validateForm = this.$refs.form.validate();
      if (!validateForm) return false;

      this.btnLoadingRol = true;
      var dataForm = this.formRol;

      if (this.isFormRolEdit) {
        var urlEdit = 'api/rol/' + dataForm.id;
        axios
          .put(urlEdit, dataForm)
          .then(response => {
            this.getRoles();
            this.dialogChip = false;
            this.btnLoadingRol = false;
            this.showSnackbar('Rol actualizado correctamente', 'success');
          })
          .catch(error => {
            this.btnLoadingRol = false;
            this.showSnackbar('Error al actualizar el rol', 'error');
          });
      } else {
        axios
          .post('api/rol', dataForm)
          .then(response => {
            this.getRoles();
            this.dialogChip = false;
            this.btnLoadingRol = false;
            this.showSnackbar('Nuevo rol registrado', 'success');
          })
          .catch(error => {
            this.btnLoadingRol = false;
            this.showSnackbar('Error al registrar el rol', 'error');
          });
      }
    },

    getMenus() {
      axios
        .get('api/menu')
        .then(response => {
          this.menus = response.data;
          if (this.itemSelectMenu) {
            var menu_ = this.menus.find(x => x.id == this.itemSelectMenu.id);
            if (menu_) {
              this.subMenus = menu_.sub_menu_n1 || [];
            }
          }
        })
        .catch(error => {});
    },

    getRoles() {
      axios
        .get('api/rol')
        .then(response => {
          this.roles = response.data;
        })
        .catch(error => {});
    },

    btnItemMenu(item) {
      this.itemSelectMenu = item;
      this.subMenus = item.sub_menu_n1 || [];
    },

    btnChipDeleteRol(item) {
      this.dataMenuDelete = null;
      this.dataRolDelete = item;
      this.dialogConfirm = true;
    },

    btnItemMenuDelete(item) {
      this.dataRolDelete = null;
      this.dataMenuDelete = item;
      this.dialogConfirm = true;
    },

    btnItemMenuEdit(item) {
      this.formMenu = { ...item };
      this.dialogMenu = true;
      this.isFormMenuEdit = true;
    },

    btnNuevoMenu(nivel) {
      this.menuNivel = nivel;
      this.formMenu = {
        label: null,
        icon: null,
        route: null
      };
      this.isFormMenuEdit = false;
      this.dialogMenu = true;
    },

    btnDialogConfirm() {
      if (this.dataRolDelete) {
        var urlEdit = 'api/rol/' + this.dataRolDelete.id;
        axios
          .delete(urlEdit)
          .then(response => {
            this.showSnackbar('Rol eliminado', 'success');
            this.getRoles();
            this.dialogConfirm = false;
            this.dataRolDelete = null;
          })
          .catch(error => {
            this.showSnackbar(error.response ? error.response.data.message : 'Error al eliminar', 'error');
          });
      }

      if (this.dataMenuDelete) {
        var urlEditMenu = 'api/menu/' + this.dataMenuDelete.id;
        axios
          .delete(urlEditMenu)
          .then(response => {
            this.showSnackbar('Menú eliminado', 'success');
            if (this.itemSelectMenu && this.itemSelectMenu.id === this.dataMenuDelete.id) {
              this.itemSelectMenu = null;
              this.subMenus = [];
            }
            this.getMenus();
            this.dialogConfirm = false;
            this.dataMenuDelete = null;
          })
          .catch(error => {
            this.showSnackbar(error.response ? error.response.data.message : 'Error al eliminar', 'error');
          });
      }
    },

    btnChipEditRol(item) {
      this.dialogChip = true;
      this.isFormRolEdit = true;
      this.formRol = { ...item };
    },

    btnChipNuevoRol() {
      this.formRol = { name: null };
      this.isFormRolEdit = false;
      this.dialogChip = true;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    }
  },

  components: {
    draggable
  }
}
</script>
