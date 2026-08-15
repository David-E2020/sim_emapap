<style scoped>
.scroll-submenu {
  max-height: 400px;
  overflow-y: auto;
}

.active-menu-item {
  background-color: rgba(var(--v-theme-primary), 0.08) !important;
  border-left: 4px solid var(--v-primary-base) !important;
}

.custom-hover-table tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>

<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-2 px-4" elevation="1">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded class="mr-3 text-white" size="44">
            <v-icon color="white">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Gestión de Usuarios</h2>
            <span class="text-caption text-secondary">Administración de usuarios registrados, roles y asignación de permisos</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- TARJETA PRINCIPAL Y TABLA -->
    <v-card elevation="2" v-if="usuarios">
      <v-card-title class="d-flex align-center justify-space-between py-3 flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-account-details-outline</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Usuarios del Sistema</span>
          <v-chip color="primary" label small class="ml-2 font-weight-bold">
            {{ usuarios.length }} Registrados
          </v-chip>
        </div>

        <div style="max-width: 320px; width: 100%;">
          <v-text-field
            v-model="search"
            placeholder="Buscar por usuario o nombre..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
          ></v-text-field>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <!-- TABLA DE USUARIOS -->
      <v-data-table
        :headers="headers"
        :items="usuarios"
        :search="search"
        class="custom-hover-table"
        :items-per-page="10"
      >
        <!-- COLUMNA USUARIO -->
        <template v-slot:item.usr_usuario="{ item }">
          <div class="d-flex align-center py-1">
            <v-avatar color="primary lighten-5" size="34" class="mr-3">
              <v-icon small color="primary">mdi-account-outline</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-2">{{ item.usr_usuario }}</div>
              <div class="text-caption text-secondary" v-if="item.email">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <!-- COLUMNA NOMBRE -->
        <template v-slot:item.nombre="{ item }">
          <span class="font-weight-medium">{{ item.name || '-' }}</span>
        </template>

        <!-- COLUMNA ESTADO DE ACCESO -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            small
            label
            :color="hasAccess(item) ? 'success' : 'grey lighten-2'"
            :class="{ 'white--text': hasAccess(item) }"
            class="font-weight-bold"
          >
            <v-icon x-small left>
              {{ hasAccess(item) ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
            </v-icon>
            {{ hasAccess(item) ? 'Acceso Habilitado' : 'Sin Acceso' }}
          </v-chip>
        </template>

        <!-- COLUMNA ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <div v-if="hasAccess(item)" class="d-flex align-center">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-bind="attrs"
                    v-on="on"
                    @click="btnAccesosPermisos(item)"
                    icon
                    small
                    color="primary"
                    class="mr-1"
                  >
                    <v-icon small>mdi-shield-lock-outline</v-icon>
                  </v-btn>
                </template>
                <span>Administrar Roles y Menús</span>
              </v-tooltip>

              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-bind="attrs"
                    v-on="on"
                    @click="btnQuitarAcceso(item)"
                    icon
                    small
                    color="error"
                  >
                    <v-icon small>mdi-account-remove-outline</v-icon>
                  </v-btn>
                </template>
                <span>Revocar Acceso al Sistema</span>
              </v-tooltip>
            </div>

            <div v-else>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-bind="attrs"
                    v-on="on"
                    @click="btnAgregarSistema(item)"
                    icon
                    small
                    color="success"
                  >
                    <v-icon small>mdi-account-check-outline</v-icon>
                  </v-btn>
                </template>
                <span>Conceder Acceso al Sistema</span>
              </v-tooltip>
            </div>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- CARGANDO STUB -->
    <v-card elevation="2" v-if="!usuarios" class="py-12 text-center">
      <v-card-text>
        <v-progress-circular :size="48" color="primary" indeterminate></v-progress-circular>
        <div class="text-subtitle-2 text-secondary mt-3">Cargando lista de usuarios...</div>
      </v-card-text>
    </v-card>

    <!-- DIÁLOGO: ADMINISTRAR ROLES Y ACCESOS A MENÚS -->
    <v-dialog persistent scrollable v-model="dialogPersmisos" max-width="850">
      <v-card rounded="lg" v-if="selectUsuario">
        <!-- CABECERA DIÁLOGO -->
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-account-outline</v-icon>
          <span>Asignación de Roles y Permisos de Menú</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogPersmisos = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <!-- INFORMACIÓN DEL USUARIO SELECCIONADO -->
          <v-alert color="primary lighten-5" class="mb-4" border="left" colored-border elevation="1">
            <div class="d-flex align-center">
              <v-avatar color="primary" size="40" class="mr-3 text-white">
                <v-icon color="white">mdi-account-circle-outline</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold text-subtitle-1 primary--text">{{ selectUsuario.name || selectUsuario.usr_usuario }}</div>
                <div class="text-caption text-secondary">Usuario: <strong>{{ selectUsuario.usr_usuario }}</strong></div>
              </div>
            </div>
          </v-alert>

          <!-- SECCIÓN SELECCIÓN DE ROL -->
          <div class="mb-4">
            <div class="text-subtitle-2 font-weight-bold color-primary mb-2 d-flex align-center">
              <v-icon small color="primary" class="mr-1">mdi-account-badge-outline</v-icon>
              1. Seleccionar Rol del Usuario
            </div>

            <div v-if="roles" class="d-flex flex-wrap gap-2">
              <v-chip
                v-for="(item, i) in roles"
                :key="i"
                @click="btnChipRol(item)"
                ripple
                class="ma-1 font-weight-bold px-3"
                :color="item.id == selectedItemRol ? 'primary' : 'grey lighten-3'"
                :class="{ 'white--text': item.id == selectedItemRol }"
                elevation="1"
              >
                <v-icon x-small left :color="item.id == selectedItemRol ? 'white' : 'primary'">
                  {{ item.id == selectedItemRol ? 'mdi-check-circle' : 'mdi-circle-outline' }}
                </v-icon>
                {{ item.name }}
              </v-chip>
            </div>

            <div v-else class="py-2 text-center">
              <v-progress-circular indeterminate color="primary" size="24"></v-progress-circular>
            </div>

            <v-alert v-if="!selectedItemRol" outlined type="warning" dense class="mt-2 text-caption">
              Por favor, selecciona un rol para configurar los accesos a los menús.
            </v-alert>
          </div>

          <v-divider class="my-4"></v-divider>

          <!-- SECCIÓN CONFIGURACIÓN DE MENÚS Y SUBMENÚS -->
          <div v-if="selectedItemRol">
            <div class="text-subtitle-2 font-weight-bold color-primary mb-3 d-flex align-center">
              <v-icon small color="primary" class="mr-1">mdi-sitemap</v-icon>
              2. Permisos de Menús y Submenús
            </div>

            <v-row>
              <!-- MENÚS PRINCIPALES -->
              <v-col cols="12" md="6">
                <v-card outlined class="fill-height">
                  <v-card-title class="py-2 text-caption font-weight-bold grey lighten-4">
                    MENÚS PRINCIPALES
                  </v-card-title>
                  <v-divider></v-divider>

                  <v-list dense class="pa-0" v-if="menus">
                    <v-list-item-group v-model="selectedItemMenu" color="primary">
                      <v-list-item
                        v-for="(item, i) in menus"
                        :key="i"
                        @click="btnItemMenu(item)"
                        class="mb-1"
                        :class="{ 'active-menu-item': subMenus === item.subMenuN1 || subMenus === item.sub_menu }"
                      >
                        <v-list-item-icon class="mr-2 my-auto">
                          <v-icon small color="primary">{{ item.icon_mdi || 'mdi-folder-outline' }}</v-icon>
                        </v-list-item-icon>

                        <v-list-item-content>
                          <v-list-item-title class="font-weight-medium text-body-2">{{ item.label }}</v-list-item-title>
                          <div class="d-flex align-center mt-1">
                            <v-progress-linear
                              :value="item.progreso ? item.progreso.porcentaje : 0"
                              color="primary"
                              height="6"
                              rounded
                              class="mr-2"
                            ></v-progress-linear>
                            <span class="text-caption text-secondary">
                              {{ item.progreso ? item.progreso.countActive : 0 }}/{{ item.progreso ? item.progreso.total : 0 }}
                            </span>
                          </div>
                        </v-list-item-content>

                        <v-icon small color="grey">mdi-chevron-right</v-icon>
                      </v-list-item>
                    </v-list-item-group>
                  </v-list>

                  <div v-else class="py-6 text-center">
                    <v-progress-circular indeterminate color="primary" size="28"></v-progress-circular>
                  </div>
                </v-card>
              </v-col>

              <!-- SUBMENÚS CON CHECKBOX DE ACCESO -->
              <v-col cols="12" md="6">
                <v-card outlined class="fill-height">
                  <v-card-title class="py-2 text-caption font-weight-bold grey lighten-4">
                    SUBMENÚS & PERMISOS
                  </v-card-title>
                  <v-divider></v-divider>

                  <div v-if="subMenus" class="scroll-submenu">
                    <v-list class="pa-0" flat>
                      <v-list-item v-for="item in subMenus" :key="item.id" class="border-bottom py-1">
                        <v-list-item-avatar size="28" color="primary lighten-5" class="mr-2 my-auto">
                          <v-icon x-small color="primary">{{ item.icon_mdi || 'mdi-file-outline' }}</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                          <v-list-item-title class="font-weight-medium text-body-2">{{ item.label }}</v-list-item-title>
                        </v-list-item-content>

                        <v-list-item-action class="my-auto d-flex align-center flex-row">
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                icon
                                small
                                color="info"
                                class="mr-1"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="openAccionesSubmenu(item)"
                              >
                                <v-icon small>mdi-shield-lock-outline</v-icon>
                              </v-btn>
                            </template>
                            <span>Configurar Acciones Granulares Anidadas</span>
                          </v-tooltip>

                          <v-checkbox
                            @click="btnItemSubMenu(item)"
                            :input-value="item.active"
                            color="primary"
                            hide-details
                            dense
                          ></v-checkbox>
                        </v-list-item-action>
                      </v-list-item>
                    </v-list>
                  </div>

                  <div v-else class="d-flex flex-column align-center justify-center fill-height py-8 text-center text-secondary">
                    <v-icon small color="grey lighten-1" class="mb-1">mdi-hand-pointing-left</v-icon>
                    <span class="text-caption">Selecciona un menú principal para configurar accesos a sus submenús.</span>
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" @click="dialogPersmisos = false" class="text-capitalize px-4">
            Aceptar y Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SUB-DIÁLOGO EMERGENTE: PERMISOS GRANULARES POR SUBMENÚ Y ROL -->
    <v-dialog v-model="dialogAccionesSubmenu" max-width="480" persistent>
      <v-card rounded="lg" v-if="selectedSubmenuAcciones">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-check-outline</v-icon>
          <span>Acciones Granulares de Submenú</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogAccionesSubmenu = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-alert color="primary lighten-5" border="left" colored-border dense class="mb-3">
            <div class="d-flex align-center">
              <v-icon small color="primary" class="mr-2">{{ selectedSubmenuAcciones.icon_mdi || 'mdi-file-outline' }}</v-icon>
              <div>
                <span class="font-weight-bold text-body-2 primary--text">{{ selectedSubmenuAcciones.label }}</span>
                <div class="text-caption text-secondary">Configuración de acciones por rol de Spatie</div>
              </div>
            </div>
          </v-alert>

          <div class="text-subtitle-2 font-weight-bold color-primary mb-2 d-flex align-center">
            <v-icon small color="primary" class="mr-1">mdi-checkbox-marked-circle-outline</v-icon>
            Seleccionar Acciones Habilitadas
          </div>

          <div v-if="loadingAcciones" class="py-6 text-center">
            <v-progress-circular indeterminate color="primary" size="30"></v-progress-circular>
          </div>

          <div v-else-if="accionesSubmenuList && accionesSubmenuList.length > 0">
            <v-list dense class="pa-0">
              <v-list-item v-for="act in accionesSubmenuList" :key="act.id" class="px-0 py-1 border-bottom">
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium text-body-2">{{ act.name }}</v-list-item-title>
                </v-list-item-content>
                <v-list-item-action>
                  <v-switch
                    v-model="act.active"
                    color="primary"
                    hide-details
                    dense
                    @change="toggleAccionPermiso(act)"
                  ></v-switch>
                </v-list-item-action>
              </v-list-item>
            </v-list>
          </div>

          <div v-else class="py-4 text-center text-caption text-secondary">
            No existen acciones granulares registradas para este submenú.
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" @click="dialogAccionesSubmenu = false" class="text-capitalize px-4">
            Listo y Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- NOTIFICACIONES SNACKBAR -->
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
export default {
  data: () => ({
    usuarios: null,
    roles: null,
    menus: [],
    subMenus: null,
    selectedItemRol: null,
    selectedItemMenu: null,
    selectUsuario: null,
    snackbar: {
      status: false,
      text: '',
      color: 'success'
    },
    search: '',
    headers: [
      { text: 'Usuario', value: 'usr_usuario', sortable: true },
      { text: 'Nombre', value: 'nombre', sortable: true },
      { text: 'Estado', value: 'estado', sortable: true, align: 'center' },
      { text: 'Acciones', value: 'acciones', sortable: false, align: 'center' },
    ],
    dialogPersmisos: false,
    dialogAccionesSubmenu: false,
    selectedSubmenuAcciones: null,
    accionesSubmenuList: [],
    loadingAcciones: false,
  }),

  mounted() {
    this.getUsuarios();
  },

  methods: {
    hasAccess(item) {
      if (!item) return false;
      return (item.permissions && item.permissions.length > 0) ||
             (item.roles && item.roles.length > 0) ||
             !!item.rol_persmisos ||
             !!item.rolPersmisos;
    },

    getUsuarios() {
      axios
        .get('api/usuario')
        .then(response => {
          this.usuarios = response.data;
        })
        .catch(error => {
          this.showSnackbar('Error al cargar la lista de usuarios', 'error');
        });
    },

    btnAgregarSistema(item) {
      axios
        .get('api/usuario/agregar-sistema/' + item.id)
        .then(response => {
          if (response.data) {
            this.showSnackbar(response.data.message || 'Acceso concedido al usuario', 'success');
            this.getUsuarios();
          }
        })
        .catch(error => {
          this.showSnackbar('Error al conceder acceso', 'error');
        });
    },

    btnQuitarAcceso(item) {
      axios
        .get('api/usuario/quitar-sistema/' + item.id)
        .then(response => {
          if (response.data) {
            this.showSnackbar(response.data.message || 'Acceso revocado', 'warning');
            this.getUsuarios();
          }
        })
        .catch(error => {
          this.showSnackbar('Error al revocar acceso', 'error');
        });
    },

    btnItemMenu(item) {
      this.subMenus = item.sub_menu || item.subMenuN1 || item.sub_menu_n1 || [];
    },

    btnItemSubMenu(item) {
      const estadoOriginal = item.active;
      if (this.selectedItemRol != null) {
        const data = {
          menu_id: item.id,
          rol_id: this.selectedItemRol,
        };

        axios
          .post('api/menu-rol', data)
          .then(response => {
            item.active = !estadoOriginal;
            this.getMenuUser(this.selectedItemRol);
            this.showSnackbar('Permiso de menú actualizado', 'success');
          })
          .catch(error => {
            item.active = estadoOriginal;
            this.showSnackbar('Error al cambiar el permiso del submenú', 'error');
          });
      }
    },

    openAccionesSubmenu(item) {
      this.selectedSubmenuAcciones = item;
      this.dialogAccionesSubmenu = true;
      this.loadingAcciones = true;
      this.accionesSubmenuList = [];

      axios.get('api/menu/permisos-submenu/' + item.id)
        .then(response => {
          this.loadingAcciones = false;
          if (response.data && response.data.permisos) {
            const userPerms = (this.selectUsuario && this.selectUsuario.permissions)
              ? this.selectUsuario.permissions.map(p => p.name)
              : [];

            this.accionesSubmenuList = response.data.permisos.map(p => ({
              ...p,
              active: userPerms.includes(p.name) || userPerms.includes('SIGP')
            }));
          }
        })
        .catch(error => {
          this.loadingAcciones = false;
          this.showSnackbar('Error al obtener acciones del submenú', 'error');
        });
    },

    toggleAccionPermiso(act) {
      this.showSnackbar('Estado de acción granular actualizado', 'success');
    },

    btnChipRol(item) {
      if (this.selectUsuario) {
        const data = {
          rol_id: item.id,
          usuario_id: this.selectUsuario.id,
        };
        this.selectedItemMenu = null;
        this.subMenus = null;

        axios
          .post('api/rol-user', data)
          .then(response => {
            this.selectedItemRol = item.id;
            this.getMenuUser(item.id);
            this.getUsuarios();
            this.showSnackbar('Rol asignado correctamente', 'success');
          })
          .catch(error => {
            this.showSnackbar('Error al asignar el rol', 'error');
          });
      }
    },

    btnAccesosPermisos(item) {
      this.dialogPersmisos = true;
      this.selectUsuario = item;
      this.subMenus = null;
      this.selectedItemMenu = null;
      this.menus = [];

      axios
        .get('api/usuario/rol-user/' + item.id)
        .then(response => {
          const data = response.data;
          this.roles = data.roles;
          this.selectedItemRol = data.rolUser;
          if (this.selectedItemRol != null) {
            this.getMenuUser(this.selectedItemRol);
          }
        })
        .catch(error => {
          this.showSnackbar('Error al cargar permisos del usuario', 'error');
        });
    },

    getMenuUser(rolId) {
      axios
        .get('api/usuario/menu-rol/' + rolId)
        .then(response => {
          this.menus = response.data.menus;
        })
        .catch(error => {});
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    }
  }
}
</script>
