<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="blue-grey darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-cog-sync-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Configuración de Correspondencia</h2>
            <span class="text-caption text-secondary">Administración de plantillas oficiales, correlativos anuales, proveídos y secretarios delegados</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- TABS -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="primary" grow>
        <v-tab><v-icon left small>mdi-file-document-multiple-outline</v-icon> Plantillas Oficiales</v-tab>
        <v-tab><v-icon left small>mdi-numeric</v-icon> Correlativos y CITEs</v-tab>
        <v-tab><v-icon left small>mdi-format-list-checks</v-icon> Proveídos Oficiales</v-tab>
        <v-tab><v-icon left small>mdi-account-tie</v-icon> Secretarios Delegados</v-tab>
      </v-tabs>
    </v-card>

    <!-- CONTENIDO TABS -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <!-- 1. PLANTILLAS -->
      <div v-if="tabActual === 0">
        <v-data-table :headers="headersPlantillas" :items="plantillas" dense class="erp-table">
          <template v-slot:item.sigla="{ item }">
            <v-chip label small color="primary" class="font-weight-bold">{{ item.sigla }}</v-chip>
          </template>
          <template v-slot:item.version="{ item }">
            <span class="text-caption">v{{ item.version }}</span>
          </template>
        </v-data-table>
      </div>

      <!-- 2. CORRELATIVOS -->
      <div v-else-if="tabActual === 1">
        <v-data-table :headers="headersCorrelativos" :items="correlativos" dense class="erp-table">
          <template v-slot:item.correlativo_actual="{ item }">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold">
              {{ item.correlativo_actual }}
            </v-chip>
          </template>
          <template v-slot:item.unidad_organizacional="{ item }">
            {{ item.unidad_organizacional ? item.unidad_organizacional.nombre : (item.sigla_regional || 'Nacional') }}
          </template>
        </v-data-table>
      </div>

      <!-- 3. PROVEÍDOS -->
      <div v-else-if="tabActual === 2">
        <v-data-table :headers="headersProveidos" :items="proveidos" dense class="erp-table">
          <template v-slot:item.param_nombre="{ item }">
            <strong class="primary--text">{{ item.param_nombre }}</strong>
          </template>
        </v-data-table>
      </div>

      <!-- 4. SECRETARIOS -->
      <div v-else-if="tabActual === 3">
        <div class="d-flex justify-space-between align-center mb-3">
          <span class="text-subtitle-2 font-weight-bold">Asistentes Ejecutivos Habilitados para Despacho</span>
          <v-btn small color="primary" class="rounded-pill text-capitalize" @click="dialogSecretario = true">
            <v-icon left small>mdi-plus</v-icon> + Asignar Asistente
          </v-btn>
        </div>

        <v-data-table :headers="headersSecretarios" :items="secretarios" dense class="erp-table">
          <template v-slot:item.usuario="{ item }">
            <strong>{{ item.user ? item.user.persona ? item.user.persona.nombre_completo : item.user.name : 'N/A' }}</strong>
          </template>
          <template v-slot:item.puesto="{ item }">
            {{ item.puesto_titular ? item.puesto_titular.nombre : 'N/A' }}
          </template>
        </v-data-table>
      </div>
    </v-card>

    <!-- MODAL NUEVO SECRETARIO -->
    <v-dialog v-model="dialogSecretario" max-width="550px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-account-tie</v-icon> Asignar Secretario Delegado
        </v-card-title>
        <v-card-text class="pt-4">
          <v-select
            v-model="formSecretario.id_usuario"
            :items="usuarios"
            item-text="name"
            item-value="id"
            label="Usuario / Asistente *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-select
            v-model="formSecretario.id_puesto_titular"
            :items="puestos"
            item-text="nombre"
            item-value="id"
            label="Puesto de la Autoridad Titular *"
            dense
            outlined
          ></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogSecretario = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="guardarSecretario">
            Guardar Delegación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
  name: 'ConfiguracionCorrespondencia',
  data() {
    return {
      tabActual: 0,
      guardando: false,

      plantillas: [],
      correlativos: [],
      proveidos: [],
      secretarios: [],
      usuarios: [],
      puestos: [],

      headersPlantillas: [
        { text: 'Sigla', value: 'sigla', width: '100px' },
        { text: 'Nombre de la Plantilla', value: 'nombre' },
        { text: 'Tipo', value: 'param_tipo_plantilla', width: '140px' },
        { text: 'Versión', value: 'version', width: '90px' },
      ],

      headersCorrelativos: [
        { text: 'Gestión', value: 'gestion', width: '100px' },
        { text: 'Tipo', value: 'tipo_correlativo', width: '140px' },
        { text: 'Unidad / Regional', value: 'unidad_organizacional' },
        { text: 'Formato CITE', value: 'formato_cite' },
        { text: 'Último Nro.', value: 'correlativo_actual', width: '120px' },
      ],

      headersProveidos: [
        { text: 'Código', value: 'param_codigo', width: '110px' },
        { text: 'Proveído Oficial EMAPA', value: 'param_nombre' },
        { text: 'Descripción / Acción Requerida', value: 'param_descripcion' },
      ],

      headersSecretarios: [
        { text: 'Secretario / Asistente', value: 'usuario' },
        { text: 'Autoridad / Puesto Titular', value: 'puesto' },
      ],

      dialogSecretario: false,
      formSecretario: {
        id_usuario: null,
        id_puesto_titular: null,
      },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const [resPlan, resCorr, resProv, resSec, resUsers, resPuestos] = await Promise.all([
          window.axios.get('/api/correspondencia/configuracion/plantillas'),
          window.axios.get('/api/correspondencia/configuracion/correlativos'),
          window.axios.get('/api/correspondencia/configuracion/proveidos'),
          window.axios.get('/api/correspondencia/configuracion/secretarios'),
          window.axios.get('/api/usuario'),
          window.axios.get('/api/rrhh/organigrama/puestos'),
        ]);

        if (resPlan.data && resPlan.data.success) this.plantillas = resPlan.data.data;
        if (resCorr.data && resCorr.data.success) this.correlativos = resCorr.data.data;
        if (resProv.data && resProv.data.success) this.proveidos = resProv.data.data;
        if (resSec.data && resSec.data.success) this.secretarios = resSec.data.data;
        if (resUsers.data && resUsers.data.data) this.usuarios = resUsers.data.data;
        if (resPuestos.data && resPuestos.data.success) this.puestos = resPuestos.data.data;
      } catch (e) {}
    },
    async guardarSecretario() {
      if (!this.formSecretario.id_usuario || !this.formSecretario.id_puesto_titular) {
        this.mostrarMensaje('Completa los campos.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/configuracion/secretarios', this.formSecretario);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Delegación secretarial guardada.', 'success');
          this.dialogSecretario = false;
          this.cargarDatos();
        }
      } catch (e) {
        this.mostrarMensaje('Error al asignar secretario.', 'error');
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
.erp-table {
  border-radius: 8px;
}
</style>
