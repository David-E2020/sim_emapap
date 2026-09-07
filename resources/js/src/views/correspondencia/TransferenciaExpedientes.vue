<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-2" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-switch-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Transferencia Masiva de Expedientes</h2>
            <span class="text-caption text-secondary">Reasignación de bandejas de entrada y documentos pendientes por desvinculación, rotación o suplencia</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- FORMULARIO DE TRANSFERENCIA -->
    <v-row justify="center">
      <v-col cols="12" md="8">
        <v-card rounded="lg" class="erp-card-elevated pa-5">
          <h3 class="text-subtitle-1 font-weight-bold mb-4 teal--text text--darken-2">Reasignación de Bandeja</h3>

          <v-alert dense outlined type="info" class="mb-4 text-caption">
            Esta operación transferirá automáticamente todas las Hojas de Ruta y Documentos en estado <strong>PENDIENTE_RECEPCION</strong> y <strong>RECIBIDO</strong> del funcionario emisor hacia el receptor.
          </v-alert>

          <v-row dense>
            <v-col cols="12" sm="6">
              <v-select
                v-model="formTransferencia.id_funcionario_origen"
                :items="funcionarios"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario Saliente / Cedente *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="formTransferencia.id_funcionario_destino"
                :items="funcionarios"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario Receptor *"
                dense
                outlined
              ></v-select>
            </v-col>
          </v-row>

          <v-textarea
            v-model="formTransferencia.motivo"
            label="Motivo Justificativo de la Transferencia *"
            rows="3"
            dense
            outlined
            class="mb-3"
            placeholder="Ej: Transferencia de expedientes por conclusión de contrato de prestación de servicios."
          ></v-textarea>

          <div class="d-flex justify-end">
            <v-btn color="teal darken-2" class="rounded-pill text-capitalize white--text px-6 elevation-2" :loading="guardando" @click="ejecutarTransferencia">
              <v-icon left small>mdi-swap-horizontal-bold</v-icon> Ejecutar Transferencia
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- SNACKBAR -->
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
  name: 'TransferenciaExpedientes',
  data() {
    return {
      funcionarios: [],
      guardando: false,

      formTransferencia: {
        id_funcionario_origen: null,
        id_funcionario_destino: null,
        motivo: 'Reasignación de bandeja por rotación de puesto.',
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarFuncionarios();
  },
  methods: {
    async cargarFuncionarios() {
      try {
        const res = await window.axios.get('/api/rrhh/personal');
        if (res.data && res.data.data) {
          this.funcionarios = res.data.data;
        }
      } catch (e) {}
    },
    async ejecutarTransferencia() {
      if (!this.formTransferencia.id_funcionario_origen || !this.formTransferencia.id_funcionario_destino) {
        this.mostrarMensaje('Selecciona los funcionarios emisor y receptor.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/transferencias', this.formTransferencia);
        if (res.data && res.data.success) {
          this.mostrarMensaje(res.data.message || 'Transferencia realizada con éxito.', 'success');
          this.formTransferencia.id_funcionario_origen = null;
          this.formTransferencia.id_funcionario_destino = null;
        }
      } catch (e) {
        this.mostrarMensaje('Error al ejecutar transferencia.', 'error');
      } finally {
        this.guardando = false;
      }
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
</style>
