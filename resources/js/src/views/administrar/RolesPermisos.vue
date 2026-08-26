<style scoped>
.active-role-item {
  background-color: rgba(var(--v-theme-primary), 0.08) !important;
  border-left: 4px solid var(--v-primary-base) !important;
}

.role-hover-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.submenu-row-item {
  transition: all 0.2s ease-in-out;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.submenu-row-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.3);
  background-color: rgba(0, 0, 0, 0.01);
}

.permiso-modal-card {
  transition: all 0.2s ease;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.permiso-modal-card:hover {
  border-color: var(--v-primary-base);
}

.permiso-modal-active {
  background-color: rgba(var(--v-theme-primary), 0.03);
  border-left: 3px solid var(--v-primary-base);
}

.orphan-chip {
  cursor: pointer;
  transition: transform 0.15s ease;
}

.orphan-chip:hover {
  transform: translateY(-2px);
}

.drag-handle {
  cursor: grab;
}
.drag-handle:active {
  cursor: grabbing;
}

.drag-item-card {
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
  border: 1px solid rgba(0, 0, 0, 0.1);
}
.drag-item-card:hover {
  border-color: var(--v-primary-base);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}
</style>

<template>
  <div>
    <!-- CABECERA PRINCIPAL UNIFICADA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-shield-key-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Centro Integral de Roles, Menús y Permisos</h2>
            <span class="text-caption text-secondary">Gestión unificada de navegación del ERP, roles de usuario y privilegios granulares Spatie</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0 flex-wrap">
          <v-btn color="info" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalReorganizar()">
            <v-icon left small>mdi-swap-vertical</v-icon> ↕️ Reorganizar Menús
          </v-btn>
          <v-btn color="secondary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalCrearMenu(null)">
            <v-icon left small>mdi-folder-plus-outline</v-icon> + Nuevo Menú
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalCrearRol()">
            <v-icon left small>mdi-shield-plus-outline</v-icon> + Nuevo Rol
          </v-btn>
        </div>
      </div>
    </v-card>

    <v-row>
      <!-- COLUMNA IZQUIERDA: ROLES REGISTRADOS -->
      <v-col cols="12" md="4">
        <v-card rounded="lg" elevation="2" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between py-3">
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-account-badge-outline</v-icon>
              <span class="text-subtitle-1 font-weight-bold">Roles de Usuario</span>
            </div>
            <v-chip color="primary" label small class="font-weight-bold" v-if="roles">
              {{ roles.length }}
            </v-chip>
          </v-card-title>

          <v-divider></v-divider>

          <div class="pa-3">
            <v-text-field
              v-model="searchRol"
              placeholder="Buscar rol..."
              dense
              outlined
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
            ></v-text-field>
          </div>

          <v-divider></v-divider>

          <!-- LISTA REACTIVA DE ROLES -->
          <v-list dense class="pa-0" v-if="rolesFiltrados && rolesFiltrados.length > 0">
            <v-list-item-group v-model="selectedRoleIndex" color="primary">
              <v-list-item
                v-for="(rol, i) in rolesFiltrados"
                :key="rol.id"
                @click="seleccionarRol(rol, i)"
                class="px-4 py-2 role-hover-item"
                :class="{ 'active-role-item': rolSeleccionado && rolSeleccionado.id === rol.id }"
              >
                <v-list-item-avatar size="36" color="primary lighten-5" class="my-auto mr-3">
                  <v-icon small color="primary">mdi-shield-account</v-icon>
                </v-list-item-avatar>

                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold text-body-2">{{ rol.name }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption text-secondary">
                    ID: #{{ rol.id }} • Guard: {{ rol.guard_name || 'api' }}
                  </v-list-item-subtitle>
                </v-list-item-content>

                <v-list-item-action class="my-auto d-flex flex-row align-center">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        x-small
                        color="secondary"
                        v-bind="attrs"
                        v-on="on"
                        @click.stop="abrirModalEditarRol(rol)"
                        class="mr-1"
                      >
                        <v-icon x-small>mdi-pencil</v-icon>
                      </v-btn>
                    </template>
                    <span>Editar Nombre del Rol</span>
                  </v-tooltip>

                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        x-small
                        color="error"
                        v-bind="attrs"
                        v-on="on"
                        @click.stop="confirmarEliminarRol(rol)"
                      >
                        <v-icon x-small>mdi-delete</v-icon>
                      </v-btn>
                    </template>
                    <span>Eliminar Rol</span>
                  </v-tooltip>
                </v-list-item-action>
              </v-list-item>
            </v-list-item-group>
          </v-list>

          <div v-else-if="loadingRoles" class="py-12 text-center">
            <v-progress-circular indeterminate color="primary" size="32"></v-progress-circular>
            <div class="text-caption text-secondary mt-2">Cargando roles...</div>
          </div>

          <div v-else class="pa-6 text-center text-caption text-secondary font-italic">
            No se encontraron roles.
          </div>
        </v-card>
      </v-col>

      <!-- COLUMNA DERECHA: ÁRBOL UNIFICADO DE MENÚS Y MATRIZ DE PERMISOS -->
      <v-col cols="12" md="8">
        <v-card rounded="lg" elevation="2" class="fill-height erp-card-elevated" v-if="rolSeleccionado">
          <!-- CABECERA MATRIZ -->
          <v-card-title class="d-flex align-center justify-space-between py-3 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-sitemap</v-icon>
              <div>
                <span class="text-subtitle-1 font-weight-bold">Matriz de Navegación y Permisos: </span>
                <v-chip color="primary" label small class="ml-1 font-weight-bold">
                  {{ rolSeleccionado.name }}
                </v-chip>
              </div>
            </div>

            <div class="d-flex align-center gap-2">
              <v-btn x-small color="primary" outlined class="text-capitalize" @click="recargarMatriz()" :loading="loadingMatriz">
                <v-icon left x-small>mdi-refresh</v-icon> Recargar
              </v-btn>
            </div>
          </v-card-title>

          <v-divider></v-divider>

          <v-card-text class="pa-4">
            <v-alert color="primary lighten-5" border="left" colored-border elevation="1" class="mb-4 text-caption">
              <div class="d-flex align-center justify-space-between flex-wrap">
                <div>
                  <v-icon color="primary" left small>mdi-information-outline</v-icon>
                  <span>
                    El <strong>Switch</strong> activa la visibilidad del menú en la barra lateral. El botón <strong><v-icon x-small color="primary">mdi-shield-key-outline</v-icon> Permisos</strong> configura las acciones granulares (Crear, Editar, Eliminar). Usa las flechas <strong>▲ / ▼</strong> para reordenar rápidamente.
                  </span>
                </div>
                <div class="mt-2 mt-sm-0" v-if="orphanPermissionsCount > 0">
                  <v-chip x-small color="warning" class="font-weight-bold">
                    <v-icon x-small left color="white">mdi-alert-circle</v-icon>
                    {{ orphanPermissionsCount }} Permisos Huérfanos Detectados
                  </v-chip>
                </div>
              </div>
            </v-alert>

            <div v-if="loadingMatriz" class="py-12 text-center">
              <v-progress-circular indeterminate color="primary" size="40"></v-progress-circular>
              <div class="text-caption text-secondary mt-2">Cargando árbol de módulos y permisos...</div>
            </div>

            <!-- ACORDEÓN DE MENÚS Y SUBMÓDULOS -->
            <div v-else-if="menusMatriz && menusMatriz.length > 0">
              <v-expansion-panels multiple accordion class="mb-4">
                <v-expansion-panel
                  v-for="(menu, mIdx) in menusMatriz"
                  :key="menu.id || mIdx"
                  class="mb-3 rounded-lg border"
                >
                  <v-expansion-panel-header class="py-3 px-4">
                    <div class="d-flex align-center justify-space-between w-100 pr-3 flex-wrap gap-2">
                      <div class="d-flex align-center">
                        <v-avatar size="34" color="primary lighten-5" class="mr-3">
                          <v-icon small color="primary">{{ menu.icon_mdi || 'mdi-folder-outline' }}</v-icon>
                        </v-avatar>
                        <div>
                          <div class="font-weight-bold text-body-1">{{ menu.label }}</div>
                          <div class="text-caption text-secondary" style="font-size: 0.75rem !important;">
                            {{ menu.sub_menu ? menu.sub_menu.length : 0 }} submódulos asignados
                          </div>
                        </div>
                      </div>

                      <div class="d-flex align-center">
                        <v-chip x-small label :color="menu.progreso && menu.progreso.countActive > 0 ? 'success' : 'grey lighten-2'" class="font-weight-bold mr-2">
                          {{ menu.progreso ? menu.progreso.countActive : 0 }}/{{ menu.sub_menu ? menu.sub_menu.length : 0 }} Visibles
                        </v-chip>

                        <!-- REORDENAMIENTO RÁPIDO MENÚ PADRE -->
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="primary"
                              class="mr-1"
                              :disabled="mIdx === 0"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="moverMenu(menu, -1)"
                            >
                              <v-icon x-small>mdi-arrow-up-bold</v-icon>
                            </v-btn>
                          </template>
                          <span>Subir Menú Principal</span>
                        </v-tooltip>

                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="primary"
                              class="mr-2"
                              :disabled="mIdx === menusMatriz.length - 1"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="moverMenu(menu, 1)"
                            >
                              <v-icon x-small>mdi-arrow-down-bold</v-icon>
                            </v-btn>
                          </template>
                          <span>Bajar Menú Principal</span>
                        </v-tooltip>

                        <!-- ACCIONES DEL MENÚ PRINCIPAL -->
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="primary"
                              class="mr-1"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="abrirModalCrearMenu(menu)"
                            >
                              <v-icon x-small>mdi-plus-circle-outline</v-icon>
                            </v-btn>
                          </template>
                          <span>Añadir Submódulo a {{ menu.label }}</span>
                        </v-tooltip>

                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="secondary"
                              class="mr-1"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="abrirModalEditarMenu(menu)"
                            >
                              <v-icon x-small>mdi-pencil</v-icon>
                            </v-btn>
                          </template>
                          <span>Editar Menú Principal</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="!menu.sub_menu || menu.sub_menu.length === 0">
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              icon
                              x-small
                              color="error"
                              v-bind="attrs"
                              v-on="on"
                              @click.stop="confirmarEliminarMenu(menu)"
                            >
                              <v-icon x-small>mdi-delete</v-icon>
                            </v-btn>
                          </template>
                          <span>Eliminar Menú</span>
                        </v-tooltip>
                      </div>
                    </div>
                  </v-expansion-panel-header>

                  <v-expansion-panel-content class="px-3 pt-2 pb-3">
                    <v-divider class="mb-3"></v-divider>

                    <div v-if="menu.sub_menu && menu.sub_menu.length > 0">
                      <div
                        v-for="(sub, sIdx) in menu.sub_menu"
                        :key="sub.id || sIdx"
                        class="d-flex align-center justify-space-between pa-3 rounded-lg mb-2 submenu-row-item"
                        :class="{ 'grey lighten-5': sub.active }"
                      >
                        <!-- IZQUIERDA: ICONO, TÍTULO, RUTA Y BADGE DE ACCIONES -->
                        <div class="d-flex align-center pr-2">
                          <v-avatar size="32" color="grey lighten-4" class="my-auto mr-3">
                            <v-icon small :color="sub.active ? 'primary' : 'grey'">
                              {{ sub.icon_mdi || 'mdi-file-outline' }}
                            </v-icon>
                          </v-avatar>

                          <div>
                            <div class="d-flex align-center flex-wrap gap-1">
                              <span class="text-body-2 font-weight-medium" :class="{ 'font-weight-bold primary--text': sub.active }">
                                {{ sub.label }}
                              </span>

                              <!-- BADGE DE PERMISOS GRANULARES -->
                              <v-chip
                                v-if="sub.perm_stats && sub.perm_stats.total > 0"
                                x-small
                                label
                                :color="sub.perm_stats.active > 0 ? 'primary lighten-5 primary--text' : 'grey lighten-3'"
                                class="ml-2 font-weight-bold"
                              >
                                <v-icon x-small left :color="sub.perm_stats.active > 0 ? 'primary' : 'grey'">mdi-shield-check</v-icon>
                                {{ sub.perm_stats.active }}/{{ sub.perm_stats.total }} Acciones
                              </v-chip>
                            </div>

                            <div class="text-caption text-secondary" v-if="sub.route">
                              Ruta: <code>{{ sub.route }}</code>
                            </div>
                          </div>
                        </div>

                        <!-- DERECHA: REORDENAMIENTO, BOTÓN DE PERMISOS, EDICIÓN Y SWITCH -->
                        <div class="d-flex align-center">
                          <!-- FLECHAS IN-PLACE SUBIR/BAJAR SUBMÓDULO -->
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                icon
                                x-small
                                color="grey darken-1"
                                class="mr-1"
                                :disabled="sIdx === 0"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="moverSubmenu(menu, sub, -1)"
                              >
                                <v-icon x-small>mdi-arrow-up</v-icon>
                              </v-btn>
                            </template>
                            <span>Subir Submódulo</span>
                          </v-tooltip>

                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                icon
                                x-small
                                color="grey darken-1"
                                class="mr-2"
                                :disabled="sIdx === menu.sub_menu.length - 1"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="moverSubmenu(menu, sub, 1)"
                              >
                                <v-icon x-small>mdi-arrow-down</v-icon>
                              </v-btn>
                            </template>
                            <span>Bajar Submódulo</span>
                          </v-tooltip>

                          <!-- BOTÓN DE PERMISOS GRANULARES -->
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                small
                                outlined
                                color="primary"
                                class="text-capitalize rounded-pill mr-2"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="abrirModalPermisosGranulares(sub)"
                              >
                                <v-icon small left>mdi-shield-key-outline</v-icon>
                                Permisos
                              </v-btn>
                            </template>
                            <span>Configurar acciones y privilegios (Crear, Editar, Eliminar)</span>
                          </v-tooltip>

                          <!-- EDITAR SUBMÓDULO -->
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                icon
                                x-small
                                color="secondary"
                                class="mr-1"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="abrirModalEditarMenu(sub)"
                              >
                                <v-icon x-small>mdi-pencil</v-icon>
                              </v-btn>
                            </template>
                            <span>Editar Submódulo</span>
                          </v-tooltip>

                          <!-- ELIMINAR SUBMÓDULO -->
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <v-btn
                                icon
                                x-small
                                color="error"
                                class="mr-2"
                                v-bind="attrs"
                                v-on="on"
                                @click.stop="confirmarEliminarMenu(sub)"
                              >
                                <v-icon x-small>mdi-delete</v-icon>
                              </v-btn>
                            </template>
                            <span>Eliminar Submódulo</span>
                          </v-tooltip>

                          <!-- SWITCH DE VISIBILIDAD EN MENÚ -->
                          <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                              <div v-bind="attrs" v-on="on">
                                <v-switch
                                  dense
                                  hide-details
                                  class="ma-0 pa-0"
                                  v-model="sub.active"
                                  color="success"
                                  @change="toggleMenuRol(sub, menu)"
                                ></v-switch>
                              </div>
                            </template>
                            <span>{{ sub.active ? 'Visible en Barra Lateral' : 'Oculto en Barra Lateral' }}</span>
                          </v-tooltip>
                        </div>
                      </div>
                    </div>

                    <div v-else class="py-3 text-center text-caption text-secondary font-italic">
                      Este menú no tiene submódulos asignados.
                    </div>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </v-expansion-panels>
            </div>

            <div v-else class="py-12 text-center text-secondary">
              No hay menús registrados en el sistema.
            </div>
          </v-card-text>
        </v-card>

        <!-- ESTADO VACÍO -->
        <v-card rounded="lg" elevation="2" class="fill-height erp-card-elevated py-12 text-center" v-else>
          <v-avatar color="primary lighten-5" size="72" class="mb-3">
            <v-icon size="40" color="primary">mdi-shield-search</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold color-primary">Selecciona un Rol</h3>
          <p class="text-caption text-secondary max-w-sm mx-auto px-4">
            Elige un rol de la columna izquierda para inspeccionar y configurar sus accesos y acciones.
          </p>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO INTERACTIVO: REORGANIZADOR DE JERARQUÍA Y ORDEN DE NAVEGACIÓN (DRAG & DROP + BOTONES) -->
    <v-dialog v-model="dialogReorganizar" max-width="700" persistent scrollable>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-swap-vertical</v-icon>
          <span>Reorganizador de Jerarquía y Orden de Navegación</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogReorganizar = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-alert color="primary lighten-5" border="left" colored-border elevation="1" class="mb-4 text-caption">
            <div class="d-flex align-center">
              <v-icon color="primary" left small>mdi-cursor-move</v-icon>
              <span>
                Arrastra los elementos con el icono <strong><v-icon small color="primary">mdi-drag-vertical</v-icon></strong> o usa los botones <strong>▲ / ▼</strong> para ajustar el orden en la barra lateral del ERP.
              </span>
            </div>
          </v-alert>

          <!-- LISTA ARRASTRABLE DE MENÚS PRINCIPALES -->
          <draggable v-model="menusReorganizar" handle=".drag-handle-parent" animation="200" class="d-flex flex-column gap-3">
            <div
              v-for="(menu, pIdx) in menusReorganizar"
              :key="menu.id"
              class="pa-3 rounded-lg drag-item-card mb-3 grey lighten-5"
            >
              <!-- CABECERA DEL MENÚ PRINCIPAL -->
              <div class="d-flex align-center justify-space-between mb-2">
                <div class="d-flex align-center">
                  <v-icon color="primary" class="drag-handle drag-handle-parent mr-2">mdi-drag-vertical</v-icon>
                  <v-avatar size="30" color="primary lighten-4" class="mr-2">
                    <v-icon small color="primary">{{ menu.icon_mdi || 'mdi-folder' }}</v-icon>
                  </v-avatar>
                  <span class="font-weight-bold text-body-1">{{ menu.label }}</span>
                  <v-chip x-small color="primary" label class="ml-2 font-weight-bold">
                    Nivel 0 • Posición: {{ pIdx + 1 }}
                  </v-chip>
                </div>

                <div class="d-flex align-center gap-1">
                  <v-btn icon x-small :disabled="pIdx === 0" @click="moverItemArray(menusReorganizar, pIdx, -1)">
                    <v-icon x-small>mdi-arrow-up-bold</v-icon>
                  </v-btn>
                  <v-btn icon x-small :disabled="pIdx === menusReorganizar.length - 1" @click="moverItemArray(menusReorganizar, pIdx, 1)">
                    <v-icon x-small>mdi-arrow-down-bold</v-icon>
                  </v-btn>
                </div>
              </div>

              <!-- SUBMÓDULOS HIJOS ARRASTRABLES DENTRO DEL PADRE -->
              <div class="pl-6 pt-2">
                <draggable
                  v-if="menu.sub_menu && menu.sub_menu.length > 0"
                  v-model="menu.sub_menu"
                  handle=".drag-handle-child"
                  group="submenus"
                  animation="150"
                  class="d-flex flex-column gap-1"
                >
                  <div
                    v-for="(sub, cIdx) in menu.sub_menu"
                    :key="sub.id"
                    class="d-flex align-center justify-space-between pa-2 white rounded border mb-1"
                  >
                    <div class="d-flex align-center">
                      <v-icon small color="grey darken-1" class="drag-handle drag-handle-child mr-2">mdi-drag</v-icon>
                      <v-icon small color="primary" class="mr-2">{{ sub.icon_mdi || 'mdi-file-outline' }}</v-icon>
                      <span class="text-body-2 font-weight-medium">{{ sub.label }}</span>
                      <code class="text-caption ml-2" v-if="sub.route" style="font-size: 0.7rem;">{{ sub.route }}</code>
                    </div>

                    <div class="d-flex align-center gap-1">
                      <span class="text-caption text-secondary mr-2" style="font-size: 0.7rem;">Orden: {{ cIdx + 1 }}</span>
                      <v-btn icon x-small :disabled="cIdx === 0" @click="moverItemArray(menu.sub_menu, cIdx, -1)">
                        <v-icon x-small>mdi-arrow-up</v-icon>
                      </v-btn>
                      <v-btn icon x-small :disabled="cIdx === menu.sub_menu.length - 1" @click="moverItemArray(menu.sub_menu, cIdx, 1)">
                        <v-icon x-small>mdi-arrow-down</v-icon>
                      </v-btn>
                    </div>
                  </div>
                </draggable>

                <div v-else class="text-caption text-secondary font-italic pa-2 bg-white rounded border">
                  Sin submódulos asignados (puedes arrastrar submódulos aquí).
                </div>
              </div>
            </div>
          </draggable>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogReorganizar = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            elevation="1"
            class="text-capitalize px-4"
            :loading="guardandoReorganizacion"
            @click="guardarReorganizacionCompleta()"
          >
            <v-icon left small>mdi-content-save-outline</v-icon>
            Guardar Nueva Estructura
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: CREAR / EDITAR MENÚ O SUBMÓDULO CON SUGERENCIA INTELIGENTE DE PERMISOS HUÉRFANOS -->
    <v-dialog v-model="dialogMenu" max-width="620" persistent>
      <v-form v-model="formMenuValido" ref="formMenu" @submit.prevent="guardarMenu">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">{{ isEditMenu ? 'mdi-pencil-outline' : 'mdi-folder-plus-outline' }}</v-icon>
            <span>{{ isEditMenu ? 'Editar Elemento de Menú' : (formMenu.menu_id ? 'Nuevo Submódulo' : 'Nuevo Menú Principal') }}</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogMenu = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-5">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formMenu.label"
                  label="Etiqueta / Nombre Visible *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-format-title"
                  placeholder="Ej. Lecturaciones y Cortes"
                  :rules="[v => !!v || 'La etiqueta es requerida']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formMenu.icon"
                  label="Icono MDI"
                  outlined
                  dense
                  prepend-inner-icon="mdi-emoticon-outline"
                  placeholder="mdi-folder, mdi-database"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="formMenu.route"
                  label="Ruta Vue Router (Opcional para categorías padre)"
                  outlined
                  dense
                  prepend-inner-icon="mdi-routes"
                  placeholder="Ej. lecturacion_cortes"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- SECCIÓN INTELIGENTE: PERMISOS HUÉRFANOS NO ASIGNADOS (SUGERENCIA PRIORITARIA) -->
            <div v-if="!isEditMenu && orphanPermissions && orphanPermissions.length > 0" class="mt-2">
              <v-alert color="amber lighten-5" border="left" colored-border elevation="1" class="pa-3">
                <div class="d-flex align-center mb-2">
                  <v-icon color="warning" small class="mr-2">mdi-lightbulb-on</v-icon>
                  <span class="font-weight-bold text-caption text-dark">
                    Permisos Backend Detectados No Asignados (Huérfanos):
                  </span>
                </div>
                <p class="text-caption text-secondary mb-2" style="font-size: 0.73rem !important;">
                  Selecciona los permisos detectados en el sistema que deseas vincular automáticamente a este nuevo módulo:
                </p>

                <div class="d-flex flex-wrap gap-1">
                  <v-chip
                    v-for="p in orphanPermissions"
                    :key="p.id"
                    small
                    :color="selectedOrphanIds.includes(p.id) ? 'primary' : 'grey lighten-3'"
                    :class="{ 'white--text': selectedOrphanIds.includes(p.id) }"
                    class="orphan-chip font-weight-medium ma-1"
                    @click="toggleOrphanSelection(p.id)"
                  >
                    <v-icon x-small left :color="selectedOrphanIds.includes(p.id) ? 'white' : 'primary'">
                      {{ selectedOrphanIds.includes(p.id) ? 'mdi-check-circle' : 'mdi-plus-circle-outline' }}
                    </v-icon>
                    {{ p.name }}
                  </v-chip>
                </div>
              </v-alert>
            </div>

            <!-- ASIGNACIÓN INMEDIATA A ROLES -->
            <div v-if="!isEditMenu" class="mt-3">
              <div class="text-subtitle-2 font-weight-bold mb-2">Conceder visibilidad inicial a roles:</div>
              <div class="d-flex flex-wrap gap-2" v-if="roles">
                <v-checkbox
                  v-for="r in roles"
                  :key="r.id"
                  v-model="formMenu.role_ids"
                  :label="r.name"
                  :value="r.id"
                  dense
                  hide-details
                  class="ma-0 mr-4 mb-2"
                ></v-checkbox>
              </div>
            </div>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogMenu = false">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="savingMenu"
              :disabled="savingMenu || !formMenuValido"
              type="submit"
              class="text-capitalize px-4"
            >
              {{ isEditMenu ? 'Guardar Cambios' : 'Crear Menú' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: PERMISOS GRANULARES DE UN SUBMÓDULO (SPATIE RBAC) -->
    <v-dialog v-model="dialogPermisosGranulares" max-width="650" persistent scrollable>
      <v-card rounded="lg" v-if="selectedSubmenuForPerms">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-lock-outline</v-icon>
          <span>Acciones y Privilegios: {{ selectedSubmenuForPerms.label }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogPermisosGranulares = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-alert color="primary lighten-5" border="left" colored-border elevation="1" class="mb-4">
            <div class="d-flex align-center justify-space-between flex-wrap">
              <div>
                <div class="font-weight-bold text-subtitle-2 primary--text">
                  Rol: {{ rolSeleccionado ? rolSeleccionado.name : '' }}
                </div>
                <div class="text-caption text-secondary">
                  Módulo: <strong>{{ selectedSubmenuForPerms.module_name }}</strong> • Control estricto de seguridad Spatie
                </div>
              </div>

              <div class="d-flex align-center gap-1 mt-2 mt-sm-0" v-if="selectedSubmenuForPerms.granular_permissions && selectedSubmenuForPerms.granular_permissions.length > 0">
                <v-btn x-small color="primary" outlined class="text-capitalize" @click="ejecutarBatchPermisos(true)">
                  Conceder Todos
                </v-btn>
                <v-btn x-small color="error" outlined class="text-capitalize" @click="ejecutarBatchPermisos(false)">
                  Revocar Todos
                </v-btn>
              </div>
            </div>
          </v-alert>

          <!-- LISTA DE ACCIONES GRANULARES -->
          <div class="d-flex flex-column gap-2" v-if="selectedSubmenuForPerms.granular_permissions && selectedSubmenuForPerms.granular_permissions.length > 0">
            <v-card
              v-for="perm in selectedSubmenuForPerms.granular_permissions"
              :key="perm.id"
              outlined
              rounded="lg"
              class="pa-3 mb-2 permiso-modal-card"
              :class="{ 'permiso-modal-active': perm.active }"
            >
              <div class="d-flex align-start justify-space-between">
                <div class="pr-3">
                  <div class="font-weight-bold text-body-2 mb-1">{{ formatPermissionTitle(perm.name) }}</div>
                  <div class="text-caption text-secondary mb-1" style="line-height: 1.3;">
                    {{ perm.description }}
                  </div>
                  <code class="text-caption text-primary" style="font-size: 0.7rem !important;">{{ perm.name }}</code>
                </div>

                <v-switch
                  dense
                  hide-details
                  class="ma-0 pa-0"
                  v-model="perm.active"
                  color="success"
                  @change="toggleSpatiePermiso(perm)"
                ></v-switch>
              </div>
            </v-card>
          </div>

          <div v-else class="py-8 text-center text-caption text-secondary font-italic">
            No se han registrado permisos granulares para este módulo.
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" class="text-capitalize px-4" @click="dialogPermisosGranulares = false">
            Listo
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CREAR/EDITAR ROL -->
    <v-dialog v-model="dialogRol" persistent max-width="440">
      <v-form v-model="formRolValido" ref="formRol" @submit.prevent="guardarRol">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-shield-account</v-icon>
            <span>{{ isEditRol ? 'Editar Rol' : 'Nuevo Rol de Usuario' }}</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogRol = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-5">
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
            <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogRol = false">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="savingRol"
              :disabled="savingRol || !formRolValido"
              type="submit"
              class="text-capitalize px-4"
            >
              {{ isEditRol ? 'Guardar Cambios' : 'Crear Rol' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- DIÁLOGO CONFIRMAR ELIMINACIÓN ROL O MENÚ -->
    <v-dialog v-model="dialogEliminar" persistent max-width="380">
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon>
          <span>Confirmar Eliminación</span>
        </v-card-title>
        <v-card-text class="pt-4 text-body-2">
          {{ textoEliminar }}
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogEliminar = false">Cancelar</v-btn>
          <v-btn color="error" elevation="1" class="text-capitalize" :loading="ejecutandoEliminar" @click="ejecutarEliminacion()">
            Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR DE NOTIFICACIÓN -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
  name: 'RolesPermisos',
  components: {
    draggable,
  },
  data() {
    return {
      roles: [],
      searchRol: '',
      selectedRoleIndex: 0,
      rolSeleccionado: null,
      loadingRoles: false,

      // Matriz Unificada
      menusMatriz: [],
      loadingMatriz: false,

      // Reorganizador Modal
      dialogReorganizar: false,
      menusReorganizar: [],
      guardandoReorganizacion: false,

      // Permisos Huérfanos
      orphanPermissions: [],
      orphanPermissionsCount: 0,
      selectedOrphanIds: [],

      // Modal de Permisos Granulares
      dialogPermisosGranulares: false,
      selectedSubmenuForPerms: null,

      // Formulario Menú / Submódulo
      dialogMenu: false,
      isEditMenu: false,
      formMenuValido: false,
      savingMenu: false,
      formMenu: {
        id: null,
        label: '',
        icon: '',
        route: '',
        menu_id: null,
        role_ids: [],
      },

      // Formulario Rol
      dialogRol: false,
      isEditRol: false,
      formRolValido: false,
      savingRol: false,
      formRol: {
        id: null,
        name: '',
      },

      // Eliminación General
      dialogEliminar: false,
      tipoEliminar: '', // 'rol' o 'menu'
      itemAEliminar: null,
      textoEliminar: '',
      ejecutandoEliminar: false,

      snackbar: {
        status: false,
        text: '',
        color: 'success',
      },
    };
  },
  computed: {
    rolesFiltrados() {
      if (!this.searchRol) return this.roles;
      const q = this.searchRol.toLowerCase();
      return this.roles.filter(r => r.name.toLowerCase().includes(q));
    },
  },
  mounted() {
    this.cargarRoles();
    this.consultarPermisosHuerfanos();
  },
  methods: {
    cargarRoles() {
      this.loadingRoles = true;
      axios
        .get('api/rol')
        .then(res => {
          this.loadingRoles = false;
          this.roles = res.data || [];
          if (this.roles.length > 0 && !this.rolSeleccionado) {
            this.seleccionarRol(this.roles[0], 0);
          }
        })
        .catch(() => {
          this.loadingRoles = false;
          this.showSnackbar('Error al cargar roles', 'error');
        });
    },

    consultarPermisosHuerfanos() {
      axios
        .get('api/roles-permisos/permisos-huerfanos')
        .then(res => {
          if (res.data && res.data.success) {
            this.orphanPermissions = res.data.permissions || [];
            this.orphanPermissionsCount = res.data.count || 0;
          }
        })
        .catch(() => {});
    },

    seleccionarRol(rol, index) {
      this.rolSeleccionado = rol;
      this.selectedRoleIndex = index;
      this.cargarMatrizCompleta(rol.id);
    },

    cargarMatrizCompleta(rolId) {
      this.loadingMatriz = true;
      axios
        .get('api/roles-permisos/matriz/' + rolId)
        .then(res => {
          this.loadingMatriz = false;
          if (res.data && res.data.success) {
            this.menusMatriz = res.data.menus || [];
          }
        })
        .catch(() => {
          this.loadingMatriz = false;
          this.showSnackbar('Error al cargar la matriz de menús y permisos', 'error');
        });
    },

    recargarMatriz() {
      if (this.rolSeleccionado) {
        this.cargarMatrizCompleta(this.rolSeleccionado.id);
      }
      this.consultarPermisosHuerfanos();
    },

    // REORGANIZACIÓN MODAL COMPLETA
    abrirModalReorganizar() {
      this.menusReorganizar = JSON.parse(JSON.stringify(this.menusMatriz));
      this.dialogReorganizar = true;
    },

    moverItemArray(arr, index, delta) {
      const newIndex = index + delta;
      if (newIndex < 0 || newIndex >= arr.length) return;
      const item = arr.splice(index, 1)[0];
      arr.splice(newIndex, 0, item);
    },

    guardarReorganizacionCompleta() {
      this.guardandoReorganizacion = true;
      const itemsToUpdate = [];

      this.menusReorganizar.forEach((parentMenu, pIdx) => {
        itemsToUpdate.push({
          id: parentMenu.id,
          order: pIdx + 1,
          menu_id: null,
        });

        if (parentMenu.sub_menu && parentMenu.sub_menu.length > 0) {
          parentMenu.sub_menu.forEach((subMenu, sIdx) => {
            itemsToUpdate.push({
              id: subMenu.id,
              order: sIdx + 1,
              menu_id: parentMenu.id,
            });
          });
        }
      });

      axios
        .post('api/roles-permisos/reordenar-menus', { items: itemsToUpdate })
        .then(res => {
          this.guardandoReorganizacion = false;
          this.dialogReorganizar = false;
          this.showSnackbar(res.data.message || 'Estructura de menús actualizada correctamente', 'success');
          this.recargarMatriz();
        })
        .catch(() => {
          this.guardandoReorganizacion = false;
          this.showSnackbar('Error al guardar la nueva estructura de menús', 'error');
        });
    },

    // REORGANIZACIÓN IN-PLACE (FLECHAS DIRECTAS EN FILAS)
    moverMenu(menu, delta) {
      const idx = this.menusMatriz.findIndex(m => m.id === menu.id);
      if (idx === -1) return;
      const newIdx = idx + delta;
      if (newIdx < 0 || newIdx >= this.menusMatriz.length) return;

      const item = this.menusMatriz.splice(idx, 1)[0];
      this.menusMatriz.splice(newIdx, 0, item);

      const itemsPayload = this.menusMatriz.map((m, i) => ({
        id: m.id,
        order: i + 1,
        menu_id: null,
      }));

      axios
        .post('api/roles-permisos/reordenar-menus', { items: itemsPayload })
        .then(res => {
          this.showSnackbar(res.data.message || 'Orden de menú actualizado', 'success');
        })
        .catch(() => {
          this.recargarMatriz();
          this.showSnackbar('Error al actualizar orden del menú', 'error');
        });
    },

    moverSubmenu(menuPadre, submenu, delta) {
      if (!menuPadre || !menuPadre.sub_menu) return;
      const idx = menuPadre.sub_menu.findIndex(s => s.id === submenu.id);
      if (idx === -1) return;
      const newIdx = idx + delta;
      if (newIdx < 0 || newIdx >= menuPadre.sub_menu.length) return;

      const item = menuPadre.sub_menu.splice(idx, 1)[0];
      menuPadre.sub_menu.splice(newIdx, 0, item);

      const itemsPayload = menuPadre.sub_menu.map((s, i) => ({
        id: s.id,
        order: i + 1,
        menu_id: menuPadre.id,
      }));

      axios
        .post('api/roles-permisos/reordenar-menus', { items: itemsPayload })
        .then(res => {
          this.showSnackbar(res.data.message || 'Orden de submódulo actualizado', 'success');
        })
        .catch(() => {
          this.recargarMatriz();
          this.showSnackbar('Error al actualizar orden del submódulo', 'error');
        });
    },

    abrirModalPermisosGranulares(submenu) {
      this.selectedSubmenuForPerms = submenu;
      this.dialogPermisosGranulares = true;
    },

    toggleSpatiePermiso(permiso) {
      if (!this.rolSeleccionado) return;
      const estadoOriginal = !permiso.active;

      const payload = {
        rol_id: this.rolSeleccionado.id,
        permission_name: permiso.name,
      };

      axios
        .post('api/roles-permisos/toggle-permiso', payload)
        .then(res => {
          if (this.selectedSubmenuForPerms) {
            if (permiso.active) {
              this.selectedSubmenuForPerms.active = true;
            }

            if (this.selectedSubmenuForPerms.perm_stats) {
              const actives = this.selectedSubmenuForPerms.granular_permissions.filter(p => p.active).length;
              this.selectedSubmenuForPerms.perm_stats.active = actives;
              if (actives === 0 && !permiso.active) {
                this.selectedSubmenuForPerms.active = false;
              }
            }
          }
          this.showSnackbar(res.data.message || 'Permiso actualizado', 'success');
        })
        .catch(err => {
          permiso.active = estadoOriginal;
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al actualizar el permiso';
          this.showSnackbar(msg, 'error');
        });
    },

    ejecutarBatchPermisos(grantAll) {
      if (!this.rolSeleccionado || !this.selectedSubmenuForPerms) return;

      const payload = {
        rol_id: this.rolSeleccionado.id,
        module: this.selectedSubmenuForPerms.module_name,
        grant_all: grantAll,
      };

      axios
        .post('api/roles-permisos/batch-module-permissions', payload)
        .then(res => {
          if (this.selectedSubmenuForPerms.granular_permissions) {
            this.selectedSubmenuForPerms.granular_permissions.forEach(p => {
              p.active = grantAll;
            });
            this.selectedSubmenuForPerms.perm_stats.active = grantAll
              ? this.selectedSubmenuForPerms.granular_permissions.length
              : 0;
            this.selectedSubmenuForPerms.active = grantAll;
          }
          this.showSnackbar(res.data.message || 'Permisos por lote actualizados', 'success');
        })
        .catch(() => {
          this.showSnackbar('Error al aplicar permisos por lote', 'error');
        });
    },

    toggleMenuRol(submenu, menuPadre) {
      if (!this.rolSeleccionado) return;
      const estadoOriginal = !submenu.active;

      const payload = {
        menu_id: submenu.id,
        rol_id: this.rolSeleccionado.id,
      };

      axios
        .post('api/roles-permisos/toggle-menu-rol', payload)
        .then(res => {
          if (menuPadre && menuPadre.sub_menu) {
            const activos = menuPadre.sub_menu.filter(s => s.active).length;
            const total = menuPadre.sub_menu.length;
            menuPadre.progreso = {
              porcentaje: total > 0 ? Math.round((activos / total) * 100) : 0,
              countActive: activos,
              total: total,
            };
          }
          if (submenu.granular_permissions) {
            if (submenu.active) {
              const verPerm = submenu.granular_permissions.find(p => p.name.endsWith('.ver'));
              if (verPerm) verPerm.active = true;
              submenu.perm_stats.active = submenu.granular_permissions.filter(p => p.active).length;
            } else {
              submenu.granular_permissions.forEach(p => { p.active = false; });
              submenu.perm_stats.active = 0;
            }
          }
          this.showSnackbar(res.data.message || `Visibilidad de "${submenu.label}" actualizada`, 'success');
        })
        .catch(() => {
          submenu.active = estadoOriginal;
          this.showSnackbar('Error al actualizar la visibilidad del menú', 'error');
        });
    },

    // CREACIÓN Y EDICIÓN DE MENÚS
    abrirModalCrearMenu(menuPadre) {
      this.isEditMenu = false;
      this.selectedOrphanIds = [];
      this.formMenu = {
        id: null,
        label: '',
        icon: menuPadre ? 'mdiFile' : 'mdiFolder',
        route: '',
        menu_id: menuPadre ? menuPadre.id : null,
        role_ids: this.rolSeleccionado ? [this.rolSeleccionado.id] : [],
      };
      this.dialogMenu = true;
      this.consultarPermisosHuerfanos();
    },

    abrirModalEditarMenu(menuItem) {
      this.isEditMenu = true;
      this.formMenu = {
        id: menuItem.id,
        label: menuItem.label,
        icon: menuItem.icon_mdi || menuItem.icon,
        route: menuItem.route || '',
        menu_id: menuItem.menu_id,
        role_ids: [],
      };
      this.dialogMenu = true;
    },

    toggleOrphanSelection(id) {
      const idx = this.selectedOrphanIds.indexOf(id);
      if (idx > -1) {
        this.selectedOrphanIds.splice(idx, 1);
      } else {
        this.selectedOrphanIds.push(id);
      }
    },

    guardarMenu() {
      if (!this.$refs.formMenu.validate()) return;
      this.savingMenu = true;

      if (this.isEditMenu) {
        axios
          .put('api/roles-permisos/actualizar-menu/' + this.formMenu.id, this.formMenu)
          .then(() => {
            this.savingMenu = false;
            this.dialogMenu = false;
            this.showSnackbar('Menú actualizado correctamente', 'success');
            this.recargarMatriz();
          })
          .catch(() => {
            this.savingMenu = false;
            this.showSnackbar('Error al actualizar menú', 'error');
          });
      } else {
        const payload = {
          ...this.formMenu,
          orphan_permission_ids: this.selectedOrphanIds,
        };

        axios
          .post('api/roles-permisos/crear-menu', payload)
          .then(() => {
            this.savingMenu = false;
            this.dialogMenu = false;
            this.showSnackbar('Menú creado exitosamente', 'success');
            this.recargarMatriz();
          })
          .catch(() => {
            this.savingMenu = false;
            this.showSnackbar('Error al crear menú', 'error');
          });
      }
    },

    confirmarEliminarMenu(menu) {
      this.tipoEliminar = 'menu';
      this.itemAEliminar = menu;
      this.textoEliminar = `¿Estás seguro de que deseas eliminar el menú "${menu.label}"?`;
      this.dialogEliminar = true;
    },

    confirmarEliminarRol(rol) {
      this.tipoEliminar = 'rol';
      this.itemAEliminar = rol;
      this.textoEliminar = `¿Estás seguro de que deseas eliminar el rol "${rol.name}"? Esta acción no se puede deshacer.`;
      this.dialogEliminar = true;
    },

    ejecutarEliminacion() {
      if (!this.itemAEliminar) return;
      this.ejecutandoEliminar = true;

      if (this.tipoEliminar === 'menu') {
        axios
          .delete('api/roles-permisos/eliminar-menu/' + this.itemAEliminar.id)
          .then(() => {
            this.ejecutandoEliminar = false;
            this.dialogEliminar = false;
            this.showSnackbar('Menú eliminado correctamente', 'success');
            this.recargarMatriz();
          })
          .catch(err => {
            this.ejecutandoEliminar = false;
            const msg = (err.response && err.response.data && err.response.data.message)
              ? err.response.data.message
              : 'Error al eliminar menú.';
            this.showSnackbar(msg, 'error');
          });
      } else if (this.tipoEliminar === 'rol') {
        axios
          .delete('api/rol/' + this.itemAEliminar.id)
          .then(() => {
            this.ejecutandoEliminar = false;
            this.dialogEliminar = false;
            this.showSnackbar('Rol eliminado correctamente', 'success');
            this.rolSeleccionado = null;
            this.cargarRoles();
          })
          .catch(err => {
            this.ejecutandoEliminar = false;
            const msg = (err.response && err.response.data && err.response.data.message)
              ? err.response.data.message
              : 'No se puede eliminar el rol.';
            this.showSnackbar(msg, 'error');
          });
      }
    },

    abrirModalCrearRol() {
      this.isEditRol = false;
      this.formRol = { id: null, name: '' };
      this.dialogRol = true;
    },

    abrirModalEditarRol(rol) {
      this.isEditRol = true;
      this.formRol = { id: rol.id, name: rol.name };
      this.dialogRol = true;
    },

    guardarRol() {
      if (!this.$refs.formRol.validate()) return;
      this.savingRol = true;

      if (this.isEditRol) {
        axios
          .put('api/rol/' + this.formRol.id, { name: this.formRol.name })
          .then(() => {
            this.savingRol = false;
            this.dialogRol = false;
            this.showSnackbar('Rol actualizado correctamente', 'success');
            this.cargarRoles();
          })
          .catch(() => {
            this.savingRol = false;
            this.showSnackbar('Error al actualizar rol', 'error');
          });
      } else {
        axios
          .post('api/rol', { name: this.formRol.name })
          .then(() => {
            this.savingRol = false;
            this.dialogRol = false;
            this.showSnackbar('Rol creado exitosamente', 'success');
            this.cargarRoles();
          })
          .catch(() => {
            this.savingRol = false;
            this.showSnackbar('Error al crear rol', 'error');
          });
      }
    },

    formatPermissionTitle(name) {
      const map = {
        'admin.usuarios.ver': 'Ver Usuarios y Detalles',
        'admin.usuarios.crear': 'Crear Nuevos Usuarios',
        'admin.usuarios.editar': 'Editar Datos de Usuarios',
        'admin.usuarios.acceso': 'Conceder / Revocar Acceso',
        'admin.usuarios.eliminar': 'Eliminar Cuentas de Usuario',
        'usuarios.exportar_excel': 'Exportar Listado a Excel',

        'admin.roles.ver': 'Ver Roles y Privilegios',
        'admin.roles.crear': 'Crear Nuevos Roles',
        'admin.roles.editar': 'Editar Nombre de Roles',
        'admin.roles.eliminar': 'Eliminar Roles',
        'admin.roles.asignar_permisos': 'Asignar Permisos a Roles',

        'admin.menus.ver': 'Ver Árbol de Menús',
        'admin.menus.crear': 'Añadir Menús y Submódulos',
        'admin.menus.editar': 'Editar Propiedades de Menú',
        'admin.menus.eliminar': 'Eliminar Menús',
        'admin.menus.reordenar': 'Reordenar Jerarquía de Menús',

        'parametricas.ver': 'Consultar Tablas Paramétricas',
        'parametricas.crear': 'Crear Tablas y Campos',
        'parametricas.editar': 'Editar Valores Paramétricos',
        'parametricas.eliminar': 'Eliminar Tablas / Campos',
        'parametricas.exportar_excel': 'Exportar Paramétricas a Excel',

        'admin.auditoria.ver': 'Inspeccionar Bitácora de Auditoría',
        'SIGP': 'Superadministrador (Acceso Maestro)',
      };
      return map[name] || name;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
