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
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Gestión de Usuarios</h2>
            <span class="text-caption text-secondary">Administración integral de cuentas, perfiles, roles y auditoría</span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalCrear()">
            <v-icon left small>mdi-account-plus-outline</v-icon> Nuevo Usuario
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETA PRINCIPAL Y TABLA -->
    <v-card rounded="lg" elevation="2" v-if="usuarios" class="erp-card-elevated">
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
            <v-avatar color="primary lighten-5" size="36" class="mr-3 elevation-1">
              <v-icon small color="primary">mdi-account-outline</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-2">{{ item.usr_usuario }}</div>
              <div class="text-caption text-secondary" v-if="item.email">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <!-- COLUMNA NOMBRE -->
        <template v-slot:item.name="{ item }">
          <span class="font-weight-medium">{{ item.name || '-' }}</span>
        </template>

        <!-- COLUMNA ESTADO DE ACCESO -->
        <template v-slot:item.estado="{ item }">
          <v-chip
            small
            label
            :color="hasAccess(item) ? 'success' : 'grey lighten-2'"
            :class="{ 'white--text': hasAccess(item), 'grey--text text--darken-3': !hasAccess(item) }"
            class="font-weight-bold"
          >
            <v-icon x-small left :color="hasAccess(item) ? 'white' : 'grey darken-3'">
              {{ hasAccess(item) ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
            </v-icon>
            {{ hasAccess(item) ? 'Acceso Habilitado' : 'Sin Acceso' }}
          </v-chip>
        </template>

        <!-- COLUMNA ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center">
            <!-- VER DETALLE -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  v-bind="attrs"
                  v-on="on"
                  @click="btnVerDetalle(item)"
                  icon
                  small
                  color="info"
                  class="mr-1"
                >
                  <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Perfil y Detalle</span>
            </v-tooltip>

            <!-- EDITAR DATOS -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  v-bind="attrs"
                  v-on="on"
                  @click="btnEditarUsuario(item)"
                  icon
                  small
                  color="secondary"
                  class="mr-1"
                >
                  <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
              </template>
              <span>Editar Datos</span>
            </v-tooltip>

            <!-- RESTABLECER CONTRASEÑA INSTITUCIONAL -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirDialogResetPassword(item)"
                  icon
                  small
                  color="amber darken-3"
                  class="mr-1"
                >
                  <v-icon small>mdi-lock-reset</v-icon>
                </v-btn>
              </template>
              <span>Restablecer Contraseña Institucional</span>
            </v-tooltip>

            <!-- ASIGNAR ROL DE ACCESO -->
            <div v-if="hasAccess(item)" class="d-inline-flex align-center">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-bind="attrs"
                    v-on="on"
                    @click="btnAbrirAsignarRol(item)"
                    icon
                    small
                    color="primary"
                    class="mr-1"
                  >
                    <v-icon small>mdi-shield-account-outline</v-icon>
                  </v-btn>
                </template>
                <span>Asignar Rol</span>
              </v-tooltip>

              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-bind="attrs"
                    v-on="on"
                    @click="abrirDialogRevocar(item)"
                    icon
                    small
                    color="error"
                  >
                    <v-icon small>mdi-account-remove-outline</v-icon>
                  </v-btn>
                </template>
                <span>Revocar Acceso</span>
              </v-tooltip>
            </div>

            <!-- HABILITAR ACCESO -->
            <div v-else class="d-inline-flex align-center">
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
                <span>Conceder Acceso</span>
              </v-tooltip>
            </div>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- CARGANDO STUB -->
    <v-card elevation="2" rounded="lg" v-if="!usuarios" class="py-12 text-center erp-card-elevated">
      <v-card-text>
        <v-progress-circular :size="48" color="primary" indeterminate></v-progress-circular>
        <div class="text-subtitle-2 text-secondary mt-3">Cargando lista de usuarios...</div>
      </v-card-text>
    </v-card>

    <!-- DIÁLOGO: CREAR NUEVO USUARIO -->
    <v-dialog v-model="dialogCrear" max-width="600" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-plus</v-icon>
          <span>Registrar Nuevo Usuario</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCrear = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <v-form ref="formCrear" v-model="formCrearValido">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="nuevoUsuario.usr_usuario"
                  label="Nombre de Usuario"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-account"
                  placeholder="Ej. jperez"
                  :rules="[v => !!v || 'El nombre de usuario es requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="nuevoUsuario.name"
                  label="Nombre Completo"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-card-account-details-outline"
                  placeholder="Ej. Juan Carlos Pérez"
                  :rules="[v => !!v || 'El nombre completo es requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="nuevoUsuario.email"
                  label="Correo Electrónico"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-email-outline"
                  placeholder="Ej. juan.perez@emapa.gob.bo"
                  type="email"
                  :rules="[
                    v => !!v || 'El correo electrónico es requerido',
                    v => /.+@.+\..+/.test(v) || 'Ingrese un correo electrónico válido'
                  ]"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="nuevoUsuario.password"
                  label="Contraseña"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-lock-outline"
                  placeholder="Mínimo 6 caracteres"
                  type="password"
                  :rules="[
                    v => !!v || 'La contraseña es requerida',
                    v => (v && v.length >= 6) || 'La contraseña debe tener al menos 6 caracteres'
                  ]"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="nuevoUsuario.rol_id"
                  :items="listaRolesDisponibles"
                  item-text="name"
                  item-value="id"
                  label="Rol del Usuario"
                  outlined
                  dense
                  prepend-inner-icon="mdi-shield-account-outline"
                  placeholder="Seleccionar rol de acceso"
                  clearable
                ></v-select>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogCrear = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" :loading="guardandoUsuario" @click="guardarNuevoUsuario()">
            Guardar Usuario
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: EDITAR USUARIO -->
    <v-dialog v-model="dialogEditar" max-width="550" persistent>
      <v-card rounded="lg" v-if="usuarioEditando">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-edit</v-icon>
          <span>Editar Usuario: {{ usuarioEditando.usr_usuario }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogEditar = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <v-form ref="formEditar" v-model="formEditarValido">
            <v-text-field
              v-model="usuarioEditando.name"
              label="Nombre Completo"
              outlined
              dense
              required
              prepend-inner-icon="mdi-card-account-details-outline"
              placeholder="Ej. Juan Carlos Pérez"
              :rules="[v => !!v || 'El nombre es requerido']"
            ></v-text-field>

            <v-text-field
              v-model="usuarioEditando.email"
              label="Correo Electrónico"
              outlined
              dense
              required
              prepend-inner-icon="mdi-email-outline"
              placeholder="Ej. juan.perez@emapa.gob.bo"
              type="email"
              :rules="[
                v => !!v || 'El correo es requerido',
                v => /.+@.+\..+/.test(v) || 'Ingrese un correo electrónico válido'
              ]"
            ></v-text-field>

            <v-text-field
              v-model="usuarioEditando.new_password"
              label="Nueva Contraseña (Opcional)"
              outlined
              dense
              prepend-inner-icon="mdi-lock-reset"
              placeholder="Dejar en blanco para mantener la actual"
              type="password"
            ></v-text-field>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogEditar = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" :loading="guardandoUsuario" @click="actualizarUsuario()">
            Actualizar Datos
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: VER DETALLE COMPLETO DEL USUARIO -->
    <v-dialog v-model="dialogDetalle" max-width="650">
      <v-card rounded="lg" v-if="detalleUsuario">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-card-account-details-outline</v-icon>
          <span>Perfil y Detalle del Usuario</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogDetalle = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <div class="d-flex align-center mb-4">
            <v-avatar color="primary" size="64" class="mr-4 elevation-2 text-white">
              <v-icon size="36" color="white">mdi-account</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold mb-0">{{ detalleUsuario.name || detalleUsuario.usr_usuario }}</h3>
              <div class="text-subtitle-2 primary--text">@{{ detalleUsuario.usr_usuario }}</div>
              <div class="text-caption text-secondary">{{ detalleUsuario.email }}</div>
            </div>
          </div>

          <v-divider class="my-3"></v-divider>

          <v-row dense class="text-body-2">
            <v-col cols="6">
              <strong>Estado de Cuenta:</strong>
              <v-chip x-small label :color="detalleUsuario.usr_estado === 'A' ? 'success' : 'error'" class="ml-2 font-weight-bold">
                {{ detalleUsuario.usr_estado === 'A' ? 'Activo' : 'Inactivo' }}
              </v-chip>
            </v-col>
            <v-col cols="6">
              <strong>Fecha de Creación:</strong> {{ formatDate(detalleUsuario.created_at) }}
            </v-col>
          </v-row>

          <div class="mt-4">
            <div class="text-subtitle-2 font-weight-bold mb-2">
              <v-icon small color="primary">mdi-shield-check</v-icon> Roles Asignados:
            </div>
            <div v-if="detalleUsuario.roles && detalleUsuario.roles.length > 0">
              <v-chip v-for="r in detalleUsuario.roles" :key="r.id" small color="primary" outlined class="mr-2 mb-1 font-weight-bold">
                {{ r.name }}
              </v-chip>
            </div>
            <div v-else class="text-caption text-secondary font-italic">Sin roles asignados</div>
          </div>

          <div class="mt-4" v-if="detalleLogs && detalleLogs.length > 0">
            <div class="text-subtitle-2 font-weight-bold mb-2">
              <v-icon small color="primary">mdi-history</v-icon> Actividad Reciente de Auditoría:
            </div>
            <v-list dense class="grey lighten-5 rounded">
              <v-list-item v-for="l in detalleLogs" :key="l.id" class="px-2">
                <v-list-item-content>
                  <v-list-item-title class="text-caption font-weight-bold">{{ l.event }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption" style="font-size: 0.7rem !important;">
                    {{ formatDate(l.created_at) }} • IP: {{ l.ip_address }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" class="rounded-pill" @click="dialogDetalle = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: ASIGNAR ROL AL USUARIO -->
    <v-dialog v-model="dialogAsignarRol" max-width="500" persistent>
      <v-card rounded="lg" v-if="selectUsuario">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-account</v-icon>
          <span>Asignar Rol de Acceso</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogAsignarRol = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <v-alert color="primary lighten-5" border="left" colored-border elevation="1" class="mb-4">
            <div class="d-flex align-center">
              <v-avatar color="primary" size="36" class="mr-3 text-white">
                <v-icon small color="white">mdi-account</v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold primary--text">{{ selectUsuario.name || selectUsuario.usr_usuario }}</div>
                <div class="text-caption text-secondary">Usuario: <strong>@{{ selectUsuario.usr_usuario }}</strong></div>
              </div>
            </div>
          </v-alert>

          <div class="text-subtitle-2 font-weight-bold mb-2">Selecciona el Rol para este usuario:</div>

          <div v-if="roles" class="d-flex flex-wrap gap-2 mb-3">
            <v-chip
              v-for="item in roles"
              :key="item.id"
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

          <div v-else class="py-4 text-center">
            <v-progress-circular indeterminate color="primary" size="24"></v-progress-circular>
          </div>

          <div class="text-caption text-secondary mt-3 d-flex align-center">
            <v-icon x-small color="info" class="mr-1">mdi-information-outline</v-icon>
            Para configurar los menús y privilegios que incluye cada rol, ingresa a <strong>Roles y Permisos</strong>.
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" class="text-capitalize px-4" @click="dialogAsignarRol = false">
            Listo
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: CONFIRMAR RESTABLECIMIENTO DE CONTRASEÑA -->
    <v-dialog v-model="dialogConfirmarReset" max-width="480" persistent>
      <v-card rounded="lg" v-if="usuarioAResetear">
        <v-card-title class="amber darken-3 white--text py-3">
          <v-icon left color="white">mdi-lock-reset</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Restablecer Contraseña</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogConfirmarReset = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <div class="d-flex align-center mb-4">
            <v-avatar color="amber lighten-5" size="48" class="mr-3">
              <v-icon color="amber darken-3">mdi-shield-key-outline</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-subtitle-1">{{ usuarioAResetear.name || usuarioAResetear.usr_usuario }}</div>
              <div class="text-caption text-secondary">
                Cuenta de Acceso: <strong>@{{ usuarioAResetear.usr_usuario }}</strong>
              </div>
            </div>
          </div>

          <v-alert
            dense
            outlined
            color="amber darken-3"
            icon="mdi-alert-circle-outline"
            class="text-body-2 mb-3"
          >
            Se generará una nueva contraseña temporal bajo el <strong>estándar institucional</strong> del sistema EMAPAP.
          </v-alert>

          <p class="text-caption grey--text text--darken-2 mb-0">
            La clave anterior quedará invalidada inmediatamente y este evento se registrará en la bitácora de auditoría.
          </p>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogConfirmarReset = false">
            Cancelar
          </v-btn>
          <v-btn
            color="amber darken-3"
            elevation="1"
            dark
            class="text-capitalize px-4"
            :loading="ejecutandoReset"
            @click="confirmarResetPasswordInstitucional"
          >
            <v-icon left small>mdi-lock-reset</v-icon>
            Restablecer Clave
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: CONFIRMAR REVOCAR ACCESO -->
    <v-dialog v-model="dialogConfirmarRevocar" max-width="480" persistent>
      <v-card rounded="lg" v-if="usuarioARevocar">
        <v-card-title class="error darken-1 white--text py-3">
          <v-icon left color="white">mdi-account-remove-outline</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Revocar Acceso al Sistema</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogConfirmarRevocar = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <div class="d-flex align-center mb-4">
            <v-avatar color="red lighten-5" size="48" class="mr-3">
              <v-icon color="error">mdi-shield-lock-outline</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-subtitle-1">{{ usuarioARevocar.name || usuarioARevocar.usr_usuario }}</div>
              <div class="text-caption text-secondary">
                Usuario: <strong>@{{ usuarioARevocar.usr_usuario }}</strong>
              </div>
            </div>
          </div>

          <v-alert
            dense
            outlined
            color="error"
            icon="mdi-alert-circle-outline"
            class="text-body-2 mb-3"
          >
            Se deshabilitará la cuenta y se revocarán todos los roles y permisos de acceso al sistema.
          </v-alert>

          <p class="text-caption grey--text text--darken-2 mb-0">
            El registro del usuario <strong>permanecerá en esta lista</strong> con el estado <strong>"Sin Acceso"</strong>, y podrá concederle acceso nuevamente cuando lo desee.
          </p>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogConfirmarRevocar = false">
            Cancelar
          </v-btn>
          <v-btn
            color="error"
            elevation="1"
            dark
            class="text-capitalize px-4"
            :loading="ejecutandoRevocar"
            @click="confirmarRevocarAcceso"
          >
            <v-icon left small>mdi-account-remove</v-icon>
            Sí, Revocar Acceso
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: CREDENCIALES RESTABLECIDAS EXITOSAMENTE -->
    <v-dialog v-model="dialogCredenciales" max-width="480" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-check</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Contraseña Restablecida</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCredenciales = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <div class="text-center mb-4">
            <v-avatar color="primary lighten-5" size="56" class="mb-2">
              <v-icon size="32" color="primary">mdi-key-check</v-icon>
            </v-avatar>
            <h3 class="text-subtitle-1 font-weight-bold">{{ credencialesGeneradas.nombre }}</h3>
            <span class="text-caption text-secondary">Acceso al Sistema ERP EMAPAP</span>
          </div>

          <v-sheet color="grey lighten-4" rounded="lg" class="pa-4 mb-3 border">
            <!-- USUARIO -->
            <div class="mb-3">
              <div class="text-caption grey--text text--darken-2 font-weight-bold mb-1">USUARIO INSTITUCIONAL:</div>
              <div class="d-flex align-center justify-space-between white pa-2 rounded border">
                <span class="text-h6 font-weight-bold primary--text font-monospace">{{ credencialesGeneradas.usuario }}</span>
                <v-btn icon small color="primary" @click="copiarTexto(credencialesGeneradas.usuario, 'Usuario copiado al portapapeles')">
                  <v-icon small>mdi-content-copy</v-icon>
                </v-btn>
              </div>
            </div>

            <!-- CONTRASEÑA -->
            <div>
              <div class="text-caption grey--text text--darken-2 font-weight-bold mb-1">NUEVA CONTRASEÑA TEMPORAL:</div>
              <div class="d-flex align-center justify-space-between white pa-2 rounded border">
                <span class="text-subtitle-1 font-weight-bold font-monospace">
                  {{ mostrarPassword ? credencialesGeneradas.password : '••••••••••••' }}
                </span>
                <div>
                  <v-btn icon small @click="mostrarPassword = !mostrarPassword" class="mr-1">
                    <v-icon small>{{ mostrarPassword ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
                  </v-btn>
                  <v-btn icon small color="success" @click="copiarTexto(credencialesGeneradas.password, 'Contraseña copiada al portapapeles')">
                    <v-icon small>mdi-content-copy</v-icon>
                  </v-btn>
                </div>
              </div>
            </div>
          </v-sheet>

          <v-alert dense outlined type="info" class="text-caption mb-0">
            <strong>Instrucciones:</strong> Entregue estas credenciales al funcionario. Se le solicitará cambio de clave en su próximo inicio de sesión.
          </v-alert>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" class="px-5 rounded-pill" @click="dialogCredenciales = false">
            Entendido
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
export default {
  data() {
    return {
      search: '',
      usuarios: null,
      headers: [
        { text: 'Usuario', value: 'usr_usuario', sortable: true },
        { text: 'Nombre Completo', value: 'name', sortable: true },
        { text: 'Estado Acceso', value: 'estado', sortable: false, align: 'center' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center' },
      ],
      // Creación
      dialogCrear: false,
      formCrearValido: false,
      guardandoUsuario: false,
      nuevoUsuario: {
        usr_usuario: '',
        name: '',
        email: '',
        password: '',
        rol_id: null,
      },
      listaRolesDisponibles: [],

      // Edición
      dialogEditar: false,
      formEditarValido: false,
      usuarioEditando: null,

      // Detalle
      dialogDetalle: false,
      detalleUsuario: null,
      detalleLogs: [],

      // Rol de Acceso
      dialogAsignarRol: false,
      selectUsuario: null,
      selectedItemRol: null,
      roles: null,

      // Restablecer contraseña institucional
      dialogConfirmarReset: false,
      dialogCredenciales: false,
      usuarioAResetear: null,
      ejecutandoReset: false,
      mostrarPassword: true,
      credencialesGeneradas: {
        nombre: '',
        usuario: '',
        password: '',
      },

      // Revocar acceso
      dialogConfirmarRevocar: false,
      usuarioARevocar: null,
      ejecutandoRevocar: false,

      snackbar: {
        status: false,
        text: '',
        color: 'success',
      },
    }
  },
  mounted() {
    this.getUsuarios();
    this.cargarRoles();
  },
  methods: {
    getUsuarios() {
      axios
        .get('api/usuario')
        .then(response => {
          this.usuarios = response.data;
        })
        .catch(error => {
          this.showSnackbar('Error al obtener la lista de usuarios', 'error');
        });
    },

    cargarRoles() {
      axios.get('api/rol').then(res => {
        this.listaRolesDisponibles = res.data || [];
      }).catch(() => {});
    },

    hasAccess(item) {
      if (!item) return false;
      const isActivo = item.usr_estado === 'A';
      const hasRol = item.roles && item.roles.length > 0;
      const hasPerm = item.permissions && item.permissions.length > 0;
      const hasRolPerm = item.rol_persmisos && (Array.isArray(item.rol_persmisos) ? item.rol_persmisos.length > 0 : !!item.rol_persmisos);
      return isActivo && (hasRol || hasPerm || hasRolPerm);
    },

    abrirModalCrear() {
      this.nuevoUsuario = {
        usr_usuario: '',
        name: '',
        email: '',
        password: '',
        rol_id: null,
      };
      this.dialogCrear = true;
    },

    guardarNuevoUsuario() {
      if (!this.$refs.formCrear.validate()) return;
      this.guardandoUsuario = true;

      axios.post('api/usuario', this.nuevoUsuario)
        .then(res => {
          this.guardandoUsuario = false;
          this.dialogCrear = false;
          this.showSnackbar('Usuario registrado exitosamente', 'success');
          this.getUsuarios();
        })
        .catch(err => {
          this.guardandoUsuario = false;
          const msg = err.response && err.response.data && err.response.data.message 
            ? err.response.data.message 
            : 'Error al registrar usuario';
          this.showSnackbar(msg, 'error');
        });
    },

    btnVerDetalle(item) {
      axios.get('api/usuario/' + item.id)
        .then(res => {
          this.detalleUsuario = res.data.user;
          this.detalleLogs = res.data.recent_logs || [];
          this.dialogDetalle = true;
        })
        .catch(() => {
          this.showSnackbar('Error al consultar detalle del usuario', 'error');
        });
    },

    btnEditarUsuario(item) {
      this.usuarioEditando = {
        id: item.id,
        usr_usuario: item.usr_usuario,
        name: item.name,
        email: item.email,
        new_password: '',
      };
      this.dialogEditar = true;
    },

    actualizarUsuario() {
      if (!this.$refs.formEditar.validate()) return;
      this.guardandoUsuario = true;

      const data = {
        name: this.usuarioEditando.name,
        email: this.usuarioEditando.email,
        password: this.usuarioEditando.new_password || undefined,
      };

      axios.put('api/usuario/' + this.usuarioEditando.id, data)
        .then(res => {
          this.guardandoUsuario = false;
          this.dialogEditar = false;
          this.showSnackbar('Usuario actualizado correctamente', 'success');
          this.getUsuarios();
        })
        .catch(err => {
          this.guardandoUsuario = false;
          const msg = err.response && err.response.data && err.response.data.message 
            ? err.response.data.message 
            : 'Error al actualizar usuario';
          this.showSnackbar(msg, 'error');
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

    abrirDialogRevocar(item) {
      this.usuarioARevocar = item;
      this.dialogConfirmarRevocar = true;
    },

    confirmarRevocarAcceso() {
      if (!this.usuarioARevocar) return;
      this.ejecutandoRevocar = true;

      axios
        .get('api/usuario/quitar-sistema/' + this.usuarioARevocar.id)
        .then(response => {
          this.ejecutandoRevocar = false;
          this.dialogConfirmarRevocar = false;
          if (response.data) {
            this.showSnackbar(response.data.message || 'Acceso revocado correctamente', 'warning');
            this.getUsuarios();
          }
        })
        .catch(error => {
          this.ejecutandoRevocar = false;
          this.dialogConfirmarRevocar = false;
          this.showSnackbar('Error al revocar acceso', 'error');
        });
    },

    btnQuitarAcceso(item) {
      this.abrirDialogRevocar(item);
    },

    btnAbrirAsignarRol(item) {
      this.dialogAsignarRol = true;
      this.selectUsuario = item;
      this.roles = null;
      this.selectedItemRol = null;

      axios
        .get('api/usuario/rol-user/' + item.id)
        .then(response => {
          const data = response.data;
          this.roles = data.roles;
          this.selectedItemRol = data.rolUser;
        })
        .catch(() => {
          this.showSnackbar('Error al cargar roles del usuario', 'error');
        });
    },

    btnChipRol(item) {
      if (this.selectUsuario) {
        const data = {
          rol_id: item.id,
          usuario_id: this.selectUsuario.id,
        };

        axios
          .post('api/rol-user', data)
          .then(response => {
            this.selectedItemRol = item.id;
            this.getUsuarios();
            this.showSnackbar('Rol asignado correctamente a ' + this.selectUsuario.usr_usuario, 'success');
          })
          .catch(error => {
            this.showSnackbar('Error al asignar el rol', 'error');
          });
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      return window.moment ? window.moment(dateStr).format('DD/MM/YYYY HH:mm') : dateStr;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },

    abrirDialogResetPassword(item) {
      this.usuarioAResetear = item;
      this.dialogConfirmarReset = true;
    },

    confirmarResetPasswordInstitucional() {
      if (!this.usuarioAResetear) return;
      this.ejecutandoReset = true;
      axios
        .post(`api/usuario/${this.usuarioAResetear.id}/reset-password`)
        .then(res => {
          this.ejecutandoReset = false;
          this.dialogConfirmarReset = false;
          if (res.data && res.data.success) {
            this.credencialesGeneradas = res.data.credenciales || {
              nombre: this.usuarioAResetear.name,
              usuario: this.usuarioAResetear.usr_usuario,
              password: '',
            };
            this.mostrarPassword = true;
            this.dialogCredenciales = true;
            this.getUsuarios();
          } else {
            this.showSnackbar(res.data.message || 'Error al restablecer contraseña', 'error');
          }
        })
        .catch(err => {
          this.ejecutandoReset = false;
          this.dialogConfirmarReset = false;
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al restablecer la contraseña';
          this.showSnackbar(msg, 'error');
        });
    },

    copiarTexto(texto, mensaje) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(() => {
          this.showSnackbar(mensaje, 'success');
        }).catch(() => {
          this.showSnackbar(mensaje, 'success');
        });
      } else {
        const input = document.createElement('textarea');
        input.value = texto;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        this.showSnackbar(mensaje, 'success');
      }
    }
  }
}
</script>
