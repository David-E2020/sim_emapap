<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-clock-check-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Control de Asistencia y Relojes Biométricos</h2>
            <span class="text-caption text-secondary">Sincronización ZKTeco en red local, registro diario y portal de asistencia personal</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="dialogCalcular = true">
            <v-icon left small>mdi-calculator</v-icon> Calcular Asistencia
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalBiometrico()">
            <v-icon left small>mdi-fingerprint</v-icon> + Nuevo Reloj
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS PRINCIPALES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-tabs v-model="activeTab" color="primary" class="px-4 pt-2">
        <v-tab><v-icon left small>mdi-calendar-check</v-icon> Asistencias Diarias</v-tab>
        <v-tab><v-icon left small>mdi-account-clock-outline</v-icon> Mi Asistencia Personal</v-tab>
        <v-tab><v-icon left small>mdi-devices</v-icon> Relojes Biométricos en Red ({{ biometricos.length }})</v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-tabs-items v-model="activeTab" class="pa-4">
        <!-- PESTAÑA 1: ASISTENCIAS DIARIAS -->
        <v-tab-item>
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-calendar</v-icon>
              <span class="text-subtitle-1 font-weight-bold">Fecha de Reporte:</span>
              <v-chip color="primary" label small class="ml-2 font-weight-bold">{{ fechaSeleccionada }}</v-chip>
            </div>

            <div class="d-flex align-center gap-2">
              <v-menu
                v-model="menuFecha"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="fechaSeleccionada"
                    label="Cambiar Fecha"
                    prepend-inner-icon="mdi-calendar"
                    readonly
                    outlined
                    dense
                    hide-details
                    v-bind="attrs"
                    v-on="on"
                    style="max-width: 170px;"
                  ></v-text-field>
                </template>
                <v-date-picker v-model="fechaSeleccionada" no-title @input="menuFecha = false; cargarAsistencias()"></v-date-picker>
              </v-menu>

              <v-btn icon color="primary" @click="cargarAsistencias()"><v-icon>mdi-refresh</v-icon></v-btn>
            </div>
          </div>

          <v-data-table
            :headers="headersAsistencia"
            :items="asistencias"
            :loading="loadingAsistencias"
            class="erp-table"
            dense
          >
            <template v-slot:item.funcionario="{ item }">
              <div class="d-flex align-center py-2">
                <v-avatar size="32" color="primary" class="white--text mr-2 font-weight-bold text-caption">
                  {{ item.persona ? item.persona.nombres.charAt(0) : 'F' }}
                </v-avatar>
                <div>
                  <div class="font-weight-bold text-body-2">{{ item.persona ? item.persona.nombre_completo : 'Funcionario' }}</div>
                  <div class="text-caption text-secondary">CI: {{ item.persona ? item.persona.numero_documento : 'N/A' }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.estado="{ item }">
              <v-chip small :color="getColorEstado(item.estado)" label class="font-weight-bold">
                {{ item.estado }}
              </v-chip>
            </template>

            <template v-slot:item.minutos_atraso="{ item }">
              <span v-if="item.minutos_atraso > 0" class="error--text font-weight-bold">
                {{ item.minutos_atraso }} min
              </span>
              <span v-else class="text-secondary">-</span>
            </template>

            <template v-slot:item.merece_refrigerio="{ item }">
              <v-icon small :color="item.merece_refrigerio ? 'success' : 'grey lighten-1'">
                {{ item.merece_refrigerio ? 'mdi-check-circle' : 'mdi-close-circle-outline' }}
              </v-icon>
            </template>
          </v-data-table>
        </v-tab-item>

        <!-- PESTAÑA 2: MI ASISTENCIA PERSONAL -->
        <v-tab-item>
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold mb-0">Mis Registros de Asistencia</h3>
              <p class="text-caption text-secondary mb-0">Historial personal de marcaciones biométricas del mes actual</p>
            </div>
            <v-chip color="primary" outlined label small class="font-weight-bold">
              <v-icon left x-small>mdi-food</v-icon> Refrigerio: Bs. 18.00 / día
            </v-chip>
          </div>

          <v-data-table
            :headers="headersMiAsistencia"
            :items="misAsistencias"
            :loading="loadingMisAsistencias"
            class="erp-table"
            dense
          >
            <template v-slot:item.fecha="{ item }">
              <span class="font-weight-bold">{{ item.fecha }}</span>
            </template>
            <template v-slot:item.estado="{ item }">
              <v-chip x-small :color="getColorEstado(item.estado)" label class="font-weight-bold">
                {{ item.estado }}
              </v-chip>
            </template>
            <template v-slot:item.minutos_atraso="{ item }">
              <span v-if="item.minutos_atraso > 0" class="error--text font-weight-bold">
                {{ item.minutos_atraso }} min
              </span>
              <span v-else class="success--text">0 min</span>
            </template>
            <template v-slot:item.merece_refrigerio="{ item }">
              <v-chip x-small :color="item.merece_refrigerio ? 'success lighten-5' : 'grey lighten-3'" :text-color="item.merece_refrigerio ? 'success' : 'grey'" label class="font-weight-bold">
                {{ item.merece_refrigerio ? 'HABILITADO (Bs. 18)' : 'NO APLICA' }}
              </v-chip>
            </template>
          </v-data-table>
        </v-tab-item>

        <!-- PESTAÑA 3: RELOJES BIOMÉTRICOS -->
        <v-tab-item>
          <v-row v-if="biometricos && biometricos.length > 0">
            <v-col cols="12" md="6" v-for="b in biometricos" :key="b.id">
              <v-card outlined rounded="lg" class="pa-4 erp-card-elevated">
                <div class="d-flex align-start justify-space-between mb-2">
                  <div class="d-flex align-center">
                    <v-avatar size="40" :color="b.is_online ? 'success lighten-5' : 'error lighten-5'" class="mr-3">
                      <v-icon :color="b.is_online ? 'success' : 'error'">mdi-fingerprint</v-icon>
                    </v-avatar>
                    <div>
                      <div class="font-weight-bold text-subtitle-1">{{ b.nombre }}</div>
                      <div class="text-caption text-secondary">Ubicación: {{ b.ubicacion || 'Oficina' }}</div>
                    </div>
                  </div>

                  <v-chip small :color="b.is_online ? 'success' : 'error'" label class="font-weight-bold text-white">
                    <v-icon x-small left color="white">mdi-circle</v-icon>
                    {{ b.is_online ? 'EN LÍNEA' : 'DESCONECTADO' }}
                  </v-chip>
                </div>

                <v-divider class="my-3"></v-divider>

                <div class="d-flex justify-space-between text-caption mb-3">
                  <div><strong>Dirección IP:</strong> <code>{{ b.url }}:{{ b.puerto }}</code></div>
                  <div><strong>Modelo:</strong> {{ b.modelo }} ({{ b.tipo }})</div>
                </div>

                <div class="d-flex align-center justify-end gap-2">
                  <v-btn small outlined color="primary" class="text-capitalize rounded-pill mr-2" :loading="b.probando" @click="probarConexion(b)">
                    <v-icon left x-small>mdi-network-outline</v-icon> Probar Ping
                  </v-btn>
                  <v-btn small color="primary" class="text-capitalize rounded-pill" :loading="b.sincronizando" @click="sincronizarReloj(b)">
                    <v-icon left x-small>mdi-download</v-icon> Sincronizar Marcaciones
                  </v-btn>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <div v-else-if="loadingBiometricos" class="text-center py-12">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>

          <div v-else class="text-center py-12 text-secondary">
            No hay relojes biométricos registrados. Presiona "+ Nuevo Reloj".
          </div>
        </v-tab-item>
      </v-tabs-items>
    </v-card>

    <!-- DIÁLOGO: NUEVO BIOMÉTRICO -->
    <v-dialog v-model="dialogBiometrico" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-fingerprint</v-icon>
          <span>Registrar Reloj Biométrico</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogBiometrico = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formBio.nombre" label="Nombre del Dispositivo *" outlined dense prepend-inner-icon="mdi-label" placeholder="Biométrico Central PB"></v-text-field>
          <v-select v-model="formBio.modelo" :items="['K40', 'IN01-A', 'MB360', 'VF680', 'G3', 'SenseFace 2A']" label="Modelo ZKTeco *" outlined dense prepend-inner-icon="mdi-devices"></v-select>
          <v-text-field v-model="formBio.url" label="Dirección IP *" outlined dense prepend-inner-icon="mdi-ip-network" placeholder="192.168.1.201"></v-text-field>
          <v-text-field v-model="formBio.puerto" label="Puerto *" type="number" outlined dense prepend-inner-icon="mdi-numeric" placeholder="4370"></v-text-field>
          <v-text-field v-model="formBio.ubicacion" label="Ubicación Física" outlined dense prepend-inner-icon="mdi-map-marker" placeholder="Ingreso Principal"></v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogBiometrico = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" @click="guardarBiometrico()">Guardar Reloj</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: CALCULAR ASISTENCIA -->
    <v-dialog v-model="dialogCalcular" max-width="400" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calculator</v-icon>
          <span>Procesar Asistencias</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCalcular = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            Esta acción consolidará las marcaciones del día y calculará atrasos, tolerancias y refrigerios para todos los funcionarios.
          </p>
          <v-text-field v-model="fechaCalculo" type="date" label="Fecha a Procesar" outlined dense></v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogCalcular = false">Cancelar</v-btn>
          <v-btn color="primary" elevation="1" class="text-capitalize" :loading="ejecutandoCalculo" @click="ejecutarCalculo()">Procesar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3500" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }"><v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn></template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'ControlAsistencia',
  data() {
    return {
      activeTab: 0,
      fechaSeleccionada: new Date().toISOString().substr(0, 10),
      menuFecha: false,

      asistencias: [],
      misAsistencias: [],
      loadingAsistencias: false,
      loadingMisAsistencias: false,

      biometricos: [],
      loadingBiometricos: false,

      dialogBiometrico: false,
      formBio: { nombre: '', modelo: 'K40', url: '', puerto: 4370, ubicacion: '' },

      dialogCalcular: false,
      fechaCalculo: new Date().toISOString().substr(0, 10),
      ejecutandoCalculo: false,

      headersAsistencia: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'Entrada 1', value: 'entrada_1' },
        { text: 'Salida 1', value: 'salida_1' },
        { text: 'Entrada 2', value: 'entrada_2' },
        { text: 'Salida 2', value: 'salida_2' },
        { text: 'Horas Trabajadas', value: 'horas_trabajadas' },
        { text: 'Atraso', value: 'minutos_atraso' },
        { text: 'Estado', value: 'estado' },
        { text: 'Refrigerio', value: 'merece_refrigerio' },
      ],

      headersMiAsistencia: [
        { text: 'Fecha', value: 'fecha' },
        { text: 'Entrada 1', value: 'entrada_1' },
        { text: 'Salida 1', value: 'salida_1' },
        { text: 'Entrada 2', value: 'entrada_2' },
        { text: 'Salida 2', value: 'salida_2' },
        { text: 'Horas Trabajadas', value: 'horas_trabajadas' },
        { text: 'Atraso', value: 'minutos_atraso' },
        { text: 'Estado', value: 'estado' },
        { text: 'Refrigerio', value: 'merece_refrigerio' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarAsistencias();
    this.cargarMisAsistencias();
    this.cargarBiometricos();
  },
  methods: {
    cargarAsistencias() {
      this.loadingAsistencias = true;
      axios.get(`/api/rrhh/asistencias?fecha=${this.fechaSeleccionada}`)
        .then(res => {
          if (res.data && res.data.success) {
            this.asistencias = res.data.data || [];
          }
        })
        .finally(() => {
          this.loadingAsistencias = false;
        });
    },

    cargarMisAsistencias() {
      this.loadingMisAsistencias = true;
      axios.get(`/api/rrhh/asistencias`)
        .then(res => {
          if (res.data && res.data.success) {
            this.misAsistencias = res.data.data || [];
          }
        })
        .finally(() => {
          this.loadingMisAsistencias = false;
        });
    },

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
          }
        })
        .finally(() => {
          this.loadingBiometricos = false;
        });
    },

    probarConexion(bio) {
      bio.probando = true;
      axios.post(`/api/rrhh/biometricos/${bio.id}/probar-conexion`)
        .then(res => {
          bio.is_online = res.data.is_online;
          this.showSnackbar(res.data.message, res.data.is_online ? 'success' : 'error');
        })
        .catch(() => {
          bio.is_online = false;
          this.showSnackbar('No se pudo establecer conexión TCP/IP con el reloj.', 'error');
        })
        .finally(() => {
          bio.probando = false;
        });
    },

    sincronizarReloj(bio) {
      bio.sincronizando = true;
      axios.post(`/api/rrhh/biometricos/${bio.id}/sincronizar`)
        .then(res => {
          this.showSnackbar(res.data.message, res.data.success ? 'success' : 'warning');
          this.cargarAsistencias();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al sincronizar marcaciones.', 'error');
        })
        .finally(() => {
          bio.sincronizando = false;
        });
    },

    abrirModalBiometrico() {
      this.formBio = { nombre: '', modelo: 'K40', url: '', puerto: 4370, ubicacion: '' };
      this.dialogBiometrico = true;
    },

    guardarBiometrico() {
      if (!this.formBio.nombre || !this.formBio.url) {
        this.showSnackbar('Por favor complete los campos obligatorios.', 'warning');
        return;
      }

      axios.post('/api/rrhh/biometricos', this.formBio)
        .then(res => {
          this.dialogBiometrico = false;
          this.showSnackbar(res.data.message || 'Reloj registrado exitosamente.', 'success');
          this.cargarBiometricos();
        })
        .catch(() => {
          this.showSnackbar('Error al registrar reloj.', 'error');
        });
    },

    ejecutarCalculo() {
      this.ejecutandoCalculo = true;
      axios.post('/api/rrhh/asistencias/calcular', { fecha: this.fechaCalculo })
        .then(res => {
          this.dialogCalcular = false;
          this.showSnackbar(res.data.message || 'Cálculo procesado.', 'success');
          this.fechaSeleccionada = this.fechaCalculo;
          this.cargarAsistencias();
        })
        .catch(() => {
          this.showSnackbar('Error al ejecutar cálculo de asistencia.', 'error');
        })
        .finally(() => {
          this.ejecutandoCalculo = false;
        });
    },

    getColorEstado(estado) {
      switch (estado) {
        case 'PRESENTE': return 'success lighten-5 green--text';
        case 'ATRASO': return 'warning lighten-5 orange--text text--darken-2';
        case 'FALTA': return 'error lighten-5 red--text';
        case 'PERMISO': return 'info lighten-5 blue--text';
        case 'COMISION': return 'purple lighten-5 purple--text';
        default: return 'grey lighten-4';
      }
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
