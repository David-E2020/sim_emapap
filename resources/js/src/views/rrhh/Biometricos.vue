<template>
  <div>
    <!-- CABECERA INSTITUCIONAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-fingerprint</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Relojes Biométricos ZKTeco</h2>
            <span class="text-caption text-secondary">
              Infraestructura de hardware, conectividad TCP/IP en red local y sincronización de marcaciones
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <v-btn
            color="primary"
            outlined
            class="rounded-pill font-weight-medium text-capitalize"
            :loading="probandoTodos"
            @click="probarTodosLosPings"
          >
            <v-icon left small>mdi-network-outline</v-icon> Probar Conectividad (Ping)
          </v-btn>

          <v-btn
            color="indigo darken-1"
            dark
            class="rounded-pill font-weight-medium text-capitalize"
            :loading="sincronizandoTodos"
            @click="sincronizarTodosLosRelojes"
          >
            <v-icon left small>mdi-sync</v-icon> Sincronizar Todos
          </v-btn>

          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold text-capitalize"
            @click="abrirModalCrear"
          >
            <v-icon left small>mdi-plus</v-icon> + Nuevo Dispositivo
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS KPIS DE ESTADO -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Dispositivos</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ totales.total }}</div>
          <div class="text-caption text-secondary">Relojes registrados en el sistema</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">En Línea (Online)</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ totales.online }}</div>
          <div class="text-caption success--text text--darken-2 font-weight-medium">Conectados a red local</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Desconectados</div>
          <div class="text-h4 font-weight-black" :class="totales.offline > 0 ? 'error--text' : 'grey--text'">
            {{ totales.offline }}
          </div>
          <div class="text-caption text-secondary">Sin respuesta en puerto 4370</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Protocolo de Red</div>
          <div class="text-h4 font-weight-black indigo--text mt-1">TCP/IP</div>
          <div class="text-caption text-secondary">Puerto estándar 4370 UDP/TCP</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- GRILLA DE DISPOSITIVOS BIOMÉTRICOS -->
    <v-card rounded="lg" class="pa-4 erp-card-elevated">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-devices</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Equipos Biométricos Instalados</span>
          <v-chip color="primary" outlined small class="ml-2 font-weight-bold">{{ biometricos.length }} equipos</v-chip>
        </div>

        <v-btn icon color="primary" :loading="loadingBiometricos" @click="cargarBiometricos">
          <v-icon>mdi-refresh</v-icon>
        </v-btn>
      </div>

      <v-row v-if="biometricos.length > 0">
        <v-col cols="12" md="6" lg="4" v-for="b in biometricos" :key="b.id">
          <v-card outlined rounded="lg" class="pa-4 h-100 d-flex flex-column justify-space-between" style="border: 1px solid #cbd5e1;">
            <div>
              <!-- Header Dispositivo -->
              <div class="d-flex align-start justify-space-between mb-2">
                <div class="d-flex align-center">
                  <v-avatar size="42" :color="b.is_online ? 'success lighten-5' : 'error lighten-5'" class="mr-3">
                    <v-icon :color="b.is_online ? 'success darken-1' : 'error'">mdi-fingerprint</v-icon>
                  </v-avatar>
                  <div>
                    <div class="font-weight-bold text-subtitle-1 mb-0">{{ b.nombre }}</div>
                    <div class="text-caption text-secondary">
                      <v-icon x-small color="grey">mdi-map-marker</v-icon> {{ b.ubicacion || 'Sin ubicación' }}
                    </div>
                  </div>
                </div>

                <v-chip
                  small
                  :color="b.is_online ? 'success' : 'error'"
                  label
                  class="font-weight-bold text-white"
                >
                  <v-icon x-small left color="white">mdi-circle</v-icon>
                  {{ b.is_online ? 'ONLINE' : 'OFFLINE' }}
                </v-chip>
              </div>

              <v-divider class="my-3"></v-divider>

              <!-- Especificaciones Técnicas -->
              <div class="text-caption pa-3 grey lighten-5 rounded mb-3" style="border: 1px solid #e2e8f0; line-height: 1.7;">
                <div class="d-flex justify-space-between">
                  <span class="text-secondary">Dirección IP:</span>
                  <strong><code>{{ b.url }}:{{ b.puerto }}</code></strong>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-secondary">Modelo Hardware:</span>
                  <strong>{{ b.modelo }} ({{ b.tipo }})</strong>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-secondary">Marcaciones en BD:</span>
                  <span class="font-weight-bold text-primary">{{ b.total_marcaciones }} registros</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-secondary">Última Sincronización:</span>
                  <span>{{ b.ultima_sincronizacion || 'Nunca sincronizado' }}</span>
                </div>
              </div>
            </div>

            <!-- Botones de Acción -->
            <div>
              <div class="d-flex align-center justify-space-between pt-2 border-t">
                <div class="d-flex gap-1">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="abrirModalEditar(b)">
                        <v-icon small>mdi-pencil</v-icon>
                      </v-btn>
                    </template>
                    <span>Editar Configuración</span>
                  </v-tooltip>

                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="confirmarEliminar(b)">
                        <v-icon small>mdi-delete</v-icon>
                      </v-btn>
                    </template>
                    <span>Eliminar Dispositivo</span>
                  </v-tooltip>
                </div>

                <div class="d-flex gap-2">
                  <v-btn
                    x-small
                    outlined
                    color="primary"
                    class="rounded-pill text-capitalize"
                    :loading="b.probando"
                    @click="probarConexion(b)"
                  >
                    <v-icon left x-small>mdi-network-outline</v-icon> Ping
                  </v-btn>

                  <v-btn
                    x-small
                    color="primary"
                    class="rounded-pill text-capitalize font-weight-bold"
                    :loading="b.sincronizando"
                    @click="sincronizarReloj(b)"
                  >
                    <v-icon left x-small>mdi-download</v-icon> Sincronizar
                  </v-btn>
                </div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <div v-else-if="loadingBiometricos" class="text-center py-12">
        <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
        <div class="mt-3 font-weight-bold text-secondary">Escaneando dispositivos en red local...</div>
      </div>

      <div v-else class="text-center py-12 grey lighten-5 rounded">
        <v-icon size="56" color="grey lighten-1">mdi-fingerprint-off</v-icon>
        <div class="mt-2 font-weight-bold text-h6 text-secondary">No hay dispositivos biométricos registrados</div>
        <p class="text-caption text-secondary mt-1">
          Presiona el botón "+ Nuevo Dispositivo" para configurar el primer reloj ZKTeco en la red de EMAPAP.
        </p>
        <v-btn color="primary" class="rounded-pill font-weight-bold mt-2" @click="abrirModalCrear">
          <v-icon left small>mdi-plus</v-icon> Registrar Primer Reloj
        </v-btn>
      </div>
    </v-card>

    <!-- MODAL: REGISTRAR / EDITAR BIOMÉTRICO -->
    <v-dialog v-model="dialogBiometrico" max-width="520" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-fingerprint</v-icon>
          <span>{{ esEdicion ? 'Editar Reloj Biométrico' : 'Registrar Nuevo Reloj Biométrico' }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogBiometrico = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-alert dense outlined type="info" class="text-caption mb-3">
            El sistema se comunicará mediante socket UDP/TCP directo hacia la IP especificada en el puerto ZKTeco estándar (4370).
          </v-alert>

          <v-row dense>
            <v-col cols="12">
              <v-text-field
                v-model="formBio.nombre"
                label="Nombre Descriptivo del Reloj *"
                placeholder="Ej: Biométrico Planta Baja - Ingreso"
                outlined
                dense
                prepend-inner-icon="mdi-label"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formBio.modelo"
                :items="['K40', 'IN01-A', 'MB360', 'VF680', 'G3', 'SenseFace 2A', 'ZKTeco Universal']"
                label="Modelo ZKTeco *"
                outlined
                dense
                prepend-inner-icon="mdi-devices"
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formBio.tipo"
                label="Tipo de Conexión *"
                placeholder="ZKTeco Ethernet"
                outlined
                dense
                prepend-inner-icon="mdi-lan"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="8">
              <v-text-field
                v-model="formBio.url"
                label="Dirección IP en Red Local *"
                placeholder="192.168.1.201"
                outlined
                dense
                prepend-inner-icon="mdi-ip-network"
                hint="Debe ser accesible desde el servidor"
                persistent-hint
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field
                v-model.number="formBio.puerto"
                label="Puerto *"
                type="number"
                outlined
                dense
                prepend-inner-icon="mdi-numeric"
                placeholder="4370"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="formBio.ubicacion"
                label="Ubicación Física Institucional"
                placeholder="Ej: Edificio Central - Puerta de Ingreso"
                outlined
                dense
                prepend-inner-icon="mdi-map-marker"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="rounded-pill" @click="dialogBiometrico = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold px-4"
            :loading="guardandoBio"
            @click="guardarBiometrico"
          >
            <v-icon left small>mdi-content-save</v-icon>
            {{ esEdicion ? 'Actualizar Reloj' : 'Guardar Reloj' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" :timeout="4000" top right rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text small v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BiometricosRrhh',
  data() {
    return {
      biometricos: [],
      loadingBiometricos: false,
      probandoTodos: false,
      sincronizandoTodos: false,

      totales: {
        total: 0,
        online: 0,
        offline: 0,
      },

      dialogBiometrico: false,
      esEdicion: false,
      bioEditId: null,
      guardandoBio: false,
      formBio: {
        nombre: '',
        modelo: 'K40',
        tipo: 'ZKTeco Ethernet',
        url: '',
        puerto: 4370,
        ubicacion: '',
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarBiometricos();
  },
  methods: {
    cargarBiometricos() {
      this.loadingBiometricos = true;
      axios.get('/api/rrhh/biometricos')
        .then(res => {
          if (res.data && res.data.success) {
            this.biometricos = (res.data.data || []).map(b => ({
              ...b,
              probando: false,
              sincronizando: false,
            }));
            this.totales = res.data.totales || {
              total: this.biometricos.length,
              online: this.biometricos.filter(b => b.is_online).length,
              offline: this.biometricos.filter(b => !b.is_online).length,
            };
          }
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar relojes biométricos.', 'error');
        })
        .finally(() => {
          this.loadingBiometricos = false;
        });
    },

    abrirModalCrear() {
      this.esEdicion = false;
      this.bioEditId = null;
      this.formBio = {
        nombre: '',
        modelo: 'K40',
        tipo: 'ZKTeco Ethernet',
        url: '192.168.1.201',
        puerto: 4370,
        ubicacion: 'Ingreso Principal',
      };
      this.dialogBiometrico = true;
    },

    abrirModalEditar(b) {
      this.esEdicion = true;
      this.bioEditId = b.id;
      this.formBio = {
        nombre: b.nombre,
        modelo: b.modelo || 'K40',
        tipo: b.tipo || 'ZKTeco Ethernet',
        url: b.url,
        puerto: b.puerto || 4370,
        ubicacion: b.ubicacion || '',
      };
      this.dialogBiometrico = true;
    },

    guardarBiometrico() {
      if (!this.formBio.nombre || !this.formBio.url) {
        this.showSnackbar('Por favor complete el nombre y la dirección IP.', 'warning');
        return;
      }

      this.guardandoBio = true;
      const req = this.esEdicion
        ? axios.put(`/api/rrhh/biometricos/${this.bioEditId}`, this.formBio)
        : axios.post('/api/rrhh/biometricos', this.formBio);

      req
        .then(res => {
          this.showSnackbar(res.data?.message || 'Dispositivo guardado exitosamente.', 'success');
          this.dialogBiometrico = false;
          this.cargarBiometricos();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al guardar dispositivo.', 'error');
        })
        .finally(() => {
          this.guardandoBio = false;
        });
    },

    confirmarEliminar(b) {
      if (!confirm(`¿Está seguro de dar de baja el reloj biométrico "${b.nombre}" (${b.url})?`)) {
        return;
      }

      axios.delete(`/api/rrhh/biometricos/${b.id}`)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Reloj biométrico eliminado.', 'success');
          this.cargarBiometricos();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al eliminar reloj.', 'error');
        });
    },

    probarConexion(b) {
      b.probando = true;
      axios.post(`/api/rrhh/biometricos/${b.id}/probar-conexion`)
        .then(res => {
          b.is_online = res.data.online;
          this.showSnackbar(res.data.message, res.data.online ? 'success' : 'error');
          this.actualizarTotales();
        })
        .catch(() => {
          b.is_online = false;
          this.showSnackbar(`Sin respuesta en ${b.url}:${b.puerto}`, 'error');
          this.actualizarTotales();
        })
        .finally(() => {
          b.probando = false;
        });
    },

    probarTodosLosPings() {
      this.probandoTodos = true;
      axios.post('/api/rrhh/biometricos/probar-todos')
        .then(res => {
          if (res.data && res.data.success) {
            const resultados = res.data.data || [];
            this.biometricos.forEach(b => {
              const r = resultados.find(x => x.id === b.id);
              if (r) b.is_online = r.is_online;
            });
            this.totales.online = res.data.online_count || 0;
            this.totales.offline = res.data.offline_count || 0;
            this.showSnackbar(`Diagnóstico finalizado: ${res.data.online_count} en línea, ${res.data.offline_count} desconectados.`, 'info');
          }
        })
        .catch(() => {
          this.showSnackbar('Error al ejecutar diagnóstico de red.', 'error');
        })
        .finally(() => {
          this.probandoTodos = false;
        });
    },

    sincronizarReloj(b) {
      b.sincronizando = true;
      axios.post(`/api/rrhh/biometricos/${b.id}/sincronizar`)
        .then(res => {
          this.showSnackbar(res.data.message, res.data.success ? 'success' : 'warning');
          this.cargarBiometricos();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al sincronizar reloj.', 'error');
        })
        .finally(() => {
          b.sincronizando = false;
        });
    },

    sincronizarTodosLosRelojes() {
      this.sincronizandoTodos = true;
      axios.post('/api/rrhh/biometricos/sincronizar-todos')
        .then(res => {
          this.showSnackbar(res.data.message || 'Sincronización masiva completada.', 'success');
          this.cargarBiometricos();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al ejecutar sincronización masiva.', 'error');
        })
        .finally(() => {
          this.sincronizandoTodos = false;
        });
    },

    actualizarTotales() {
      this.totales.online = this.biometricos.filter(b => b.is_online).length;
      this.totales.offline = this.biometricos.filter(b => !b.is_online).length;
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
