<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-shield-search</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Bitácora de Auditoría y Seguridad</h2>
            <span class="text-caption text-secondary">Registro inmutable (append-only) de eventos del sistema, accesos y cambios críticos</span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize rounded-pill" @click="fetchLogs()" :loading="loading">
            <v-icon left small>mdi-refresh</v-icon> Actualizar
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETA PRINCIPAL Y FILTROS -->
    <v-card rounded="lg" elevation="2" class="erp-card-elevated">
      <v-card-title class="py-3 d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-format-list-bulleted</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Eventos Registrados</span>
          <v-chip color="primary" label small class="ml-2 font-weight-bold" v-if="pagination.total">
            {{ pagination.total }} Registros
          </v-chip>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap" style="max-width: 600px; width: 100%;">
          <v-select
            v-model="filters.event"
            :items="eventTypes"
            placeholder="Filtrar por evento..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-filter-variant"
            @change="fetchLogs(1)"
            style="max-width: 220px;"
          ></v-select>

          <v-text-field
            v-model="filters.search"
            placeholder="Buscar por IP o recurso..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            @keyup.enter="fetchLogs(1)"
            @click:clear="fetchLogs(1)"
          ></v-text-field>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <!-- TABLA DE LOGS DE AUDITORÍA -->
      <v-data-table
        :headers="headers"
        :items="logs"
        :loading="loading"
        :server-items-length="pagination.total"
        :options.sync="tableOptions"
        class="custom-hover-table"
        :footer-props="{
          'items-per-page-options': [10, 20, 50],
          'items-per-page-text': 'Filas por página:'
        }"
      >
        <!-- COLUMNA EVENTO -->
        <template v-slot:item.event="{ item }">
          <v-chip small label class="font-weight-bold" :color="getEventColor(item.event)">
            <v-icon x-small left>{{ getEventIcon(item.event) }}</v-icon>
            {{ formatEventName(item.event) }}
          </v-chip>
        </template>

        <!-- COLUMNA USUARIO -->
        <template v-slot:item.user="{ item }">
          <div v-if="item.user" class="d-flex align-center py-1">
            <v-avatar size="28" color="primary lighten-5" class="mr-2">
              <v-icon x-small color="primary">mdi-account</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-caption">{{ item.user.usr_usuario }}</div>
              <div class="text-caption text-secondary" style="font-size: 0.7rem !important;">{{ item.user.name }}</div>
            </div>
          </div>
          <span v-else class="text-caption text-secondary font-italic">Sistema / Anónimo</span>
        </template>

        <!-- COLUMNA RECURSO -->
        <template v-slot:item.auditable_type="{ item }">
          <div v-if="item.auditable_type">
            <span class="font-weight-medium text-caption">{{ formatModelName(item.auditable_type) }}</span>
            <span class="text-caption text-secondary ml-1 font-weight-bold" v-if="item.auditable_id">#{{ item.auditable_id }}</span>
          </div>
          <span v-else class="text-caption text-secondary">-</span>
        </template>

        <!-- COLUMNA IP / CLIENTE -->
        <template v-slot:item.ip_address="{ item }">
          <div class="text-caption">
            <v-icon x-small color="secondary" left>mdi-ip-network-outline</v-icon>
            <code>{{ item.ip_address || '127.0.0.1' }}</code>
          </div>
        </template>

        <!-- COLUMNA FECHA -->
        <template v-slot:item.created_at="{ item }">
          <span class="text-caption font-weight-medium">{{ formatDate(item.created_at) }}</span>
        </template>

        <!-- COLUMNA ACCIONES / DETALLES -->
        <template v-slot:item.actions="{ item }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="primary"
                v-bind="attrs"
                v-on="on"
                @click="viewLogDetail(item)"
              >
                <v-icon small>mdi-code-json</v-icon>
              </v-btn>
            </template>
            <span>Ver Cambios JSON</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: DETALLES Y DIFERENCIAS JSON -->
    <v-dialog v-model="dialogDetail" max-width="700">
      <v-card rounded="lg" v-if="selectedLog">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-check-outline</v-icon>
          <span>Inspección de Evento: {{ formatEventName(selectedLog.event) }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogDetail = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <div class="mb-3">
            <strong>ID Evento (UUID):</strong> <code>{{ selectedLog.id }}</code><br>
            <strong>Fecha y Hora:</strong> {{ formatDate(selectedLog.created_at) }}<br>
            <strong>IP:</strong> {{ selectedLog.ip_address }} | <strong>User Agent:</strong> <span class="text-caption">{{ selectedLog.user_agent }}</span>
          </div>

          <v-row v-if="selectedLog.old_values || selectedLog.new_values">
            <v-col cols="12" md="6" v-if="selectedLog.old_values">
              <div class="font-weight-bold error--text mb-1">
                <v-icon x-small color="error">mdi-minus-box</v-icon> Valores Anteriores:
              </div>
              <pre class="pa-2 grey lighten-4 rounded text-caption" style="max-height: 250px; overflow: auto;">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
            </v-col>

            <v-col cols="12" :md="selectedLog.old_values ? 6 : 12" v-if="selectedLog.new_values">
              <div class="font-weight-bold success--text mb-1">
                <v-icon x-small color="success">mdi-plus-box</v-icon> Valores Nuevos:
              </div>
              <pre class="pa-2 grey lighten-4 rounded text-caption" style="max-height: 250px; overflow: auto;">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
            </v-col>
          </v-row>

          <v-alert v-else type="info" text dense class="mt-2 text-caption">
            Este evento no incluye mutaciones de atributos de modelo (evento de autenticación o acceso).
          </v-alert>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" class="rounded-pill" @click="dialogDetail = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'AuditoriaSeguridad',
  data() {
    return {
      loading: false,
      logs: [],
      pagination: {
        total: 0,
        page: 1,
        perPage: 10,
      },
      tableOptions: {
        page: 1,
        itemsPerPage: 10,
      },
      filters: {
        event: null,
        search: '',
      },
      eventTypes: [
        { text: 'Todos los Eventos', value: null },
        { text: 'Inicio de Sesión Exitoso', value: 'auth_login_success' },
        { text: 'Fallo de Credenciales', value: 'auth_login_failed' },
        { text: 'Cierre de Sesión', value: 'auth_logout' },
        { text: 'Usuario Creado', value: 'user_created' },
        { text: 'Acceso Asignado', value: 'user_access_enabled' },
        { text: 'Acceso Revocado', value: 'user_access_revoked' },
        { text: 'Rol Actualizado', value: 'user_role_updated' },
        { text: 'Contraseña Modificada', value: 'user_password_changed' },
        { text: 'Paramétrica Creada/Editada', value: 'parametrica_created' },
        { text: 'Paramétrica Eliminada', value: 'parametrica_deleted' },
      ],
      headers: [
        { text: 'Evento', value: 'event', sortable: false },
        { text: 'Usuario Ejecutor', value: 'user', sortable: false },
        { text: 'Recurso Afectado', value: 'auditable_type', sortable: false },
        { text: 'Dirección IP', value: 'ip_address', sortable: false },
        { text: 'Fecha y Hora', value: 'created_at', sortable: false },
        { text: 'Detalle', value: 'actions', sortable: false, align: 'center' },
      ],
      dialogDetail: false,
      selectedLog: null,
    }
  },
  watch: {
    tableOptions: {
      handler() {
        this.fetchLogs(this.tableOptions.page, this.tableOptions.itemsPerPage);
      },
      deep: true,
    },
  },
  mounted() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs(page = 1, perPage = 10) {
      this.loading = true;
      const params = {
        page: page,
        per_page: perPage,
        event: this.filters.event || undefined,
        search: this.filters.search || undefined,
      };

      this.$http
        .get('api/audit-logs', { params })
        .then(res => {
          this.loading = false;
          if (res.data && res.data.data) {
            this.logs = res.data.data.data || [];
            this.pagination.total = res.data.data.total || 0;
            this.pagination.page = res.data.data.current_page || 1;
          }
        })
        .catch(err => {
          this.loading = false;
          console.error('Error cargando bitácora de auditoría', err);
        });
    },
    viewLogDetail(item) {
      this.selectedLog = item;
      this.dialogDetail = true;
    },
    formatDate(dateStr) {
      if (!dateStr) return '-';
      return window.moment ? window.moment(dateStr).format('DD/MM/YYYY HH:mm:ss') : dateStr;
    },
    formatModelName(typeStr) {
      if (!typeStr) return '-';
      const parts = typeStr.split('\\');
      return parts[parts.length - 1];
    },
    formatEventName(event) {
      const map = {
        auth_login_success: 'Login Exitoso',
        auth_login_failed: 'Login Fallido',
        auth_logout: 'Logout',
        user_created: 'Usuario Creado',
        user_access_enabled: 'Acceso Concedido',
        user_access_revoked: 'Acceso Revocado',
        user_role_updated: 'Rol Asignado',
        user_password_changed: 'Clave Modificada',
        parametrica_created: 'Paramétrica Guardada',
        parametrica_updated: 'Paramétrica Editada',
        parametrica_deleted: 'Paramétrica Eliminada',
        user_module_access_updated: 'Módulos Guardados',
      };
      return map[event] || event;
    },
    getEventColor(event) {
      if (event.includes('failed') || event.includes('revoked') || event.includes('deleted')) {
        return 'error lighten-1 white--text';
      }
      if (event.includes('success') || event.includes('enabled') || event.includes('created')) {
        return 'success lighten-1 white--text';
      }
      if (event.includes('updated') || event.includes('changed')) {
        return 'info lighten-1 white--text';
      }
      return 'primary lighten-1 white--text';
    },
    getEventIcon(event) {
      if (event.includes('failed')) return 'mdi-alert-circle';
      if (event.includes('login')) return 'mdi-login';
      if (event.includes('logout')) return 'mdi-logout';
      if (event.includes('revoked') || event.includes('deleted')) return 'mdi-trash-can';
      if (event.includes('enabled') || event.includes('created')) return 'mdi-plus-circle';
      return 'mdi-information-outline';
    },
  },
}
</script>
