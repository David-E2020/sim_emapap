<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="cyan darken-3" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-domain-plus</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Ventanilla Única de Correspondencia</h2>
            <span class="text-caption text-secondary">Recepción de correspondencia externa, ministerios, entidades públicas y particulares</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="cyan darken-3" class="text-capitalize font-weight-medium rounded-pill elevation-2 white--text" @click="abrirModalRegistro">
            <v-icon left small>mdi-file-import</v-icon> + Recepcionar Documento Externo
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- VENTANILLAS ACTIVAS -->
    <v-row class="mb-5">
      <v-col cols="12" md="6" v-for="v in ventanillas" :key="v.id">
        <v-card rounded="lg" class="erp-card-elevated pa-4">
          <div class="d-flex justify-space-between align-start mb-2">
            <div>
              <h3 class="text-subtitle-1 font-weight-bold primary--text">{{ v.nombre }}</h3>
              <span class="text-caption text-secondary">{{ v.direccion }}</span>
            </div>
            <v-chip small label :color="v.tipo_atencion === 'DIGITAL' ? 'purple' : 'teal'" class="white--text font-weight-bold">
              {{ v.tipo_atencion }}
            </v-chip>
          </div>
          <div class="text-caption text-secondary">
            <strong>Regional:</strong> {{ v.regional ? v.regional.nombre : 'Sede Central Nacional' }}
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- MODAL RECEPCIÓN EXTERNA -->
    <v-dialog v-model="dialogRegistro" max-width="750px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 cyan darken-3 white--text py-3">
          <v-icon left color="white">mdi-file-import</v-icon> Recepcionar Correspondencia Externa
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-select
                v-model="formVentanilla.id_ventanilla"
                :items="ventanillas"
                item-text="nombre"
                item-value="id"
                label="Mesón / Ventanilla de Ingreso *"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formVentanilla.remitente_externo"
                label="Institución o Remitente Externo *"
                dense
                outlined
                placeholder="Ej: Ministerio de Desarrollo Productivo"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-text-field
            v-model="formVentanilla.referencia"
            label="Nro. CITE / Nota Externa"
            dense
            outlined
            placeholder="Ej: CITE MDPyEP/DESP/045/2026"
            class="mb-2"
          ></v-text-field>

          <v-textarea
            v-model="formVentanilla.asunto"
            label="Asunto / Objeto de la Nota *"
            rows="2"
            dense
            outlined
            class="mb-2"
          ></v-textarea>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-select
                v-model="formVentanilla.prioridad"
                :items="['URGENTE', 'ALTA', 'MEDIA', 'BAJA']"
                label="Prioridad"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="formVentanilla.nro_fojas"
                label="Nro. Fojas"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="formVentanilla.nro_anexos"
                label="Nro. Anexos"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>

          <v-divider class="my-3"></v-divider>
          <span class="text-subtitle-2 font-weight-bold cyan--text text--darken-3">Derivación Interna Inmediata</span>

          <v-row dense class="mt-1">
            <v-col cols="12" md="6">
              <v-select
                v-model="formVentanilla.id_unidad_destino"
                :items="unidades"
                item-text="nombre"
                item-value="id"
                label="Unidad Destino *"
                dense
                outlined
                @change="cargarFuncionariosUnidad"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                v-model="formVentanilla.id_funcionario_destino"
                :items="funcionariosDestino"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario Destinatario"
                dense
                outlined
                clearable
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogRegistro = false">Cancelar</v-btn>
          <v-btn color="cyan darken-3" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="registrarEntrada">
            Emitir CITE y Comprobante
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL COMPROBANTE DE INGRESO EMITIDO -->
    <v-dialog v-model="dialogComprobante" max-width="500px" persistent>
      <v-card rounded="lg" v-if="comprobanteEmitido">
        <v-card-title class="font-weight-bold text-h6 success white--text py-3">
          <v-icon left color="white">mdi-check-circle</v-icon> Comprobante de Ingreso EMAPA
        </v-card-title>
        <v-card-text class="pt-4 text-center">
          <div class="text-caption font-weight-bold text-secondary mb-1">CÓDIGO DE SEGUIMIENTO CIUDADANO:</div>
          <div class="text-h5 font-weight-black primary--text mb-3">{{ comprobanteEmitido.nro_hoja_ruta }}</div>

          <p class="text-caption text-secondary">
            El trámite ha sido registrado satisfactoriamente e ingresado a los sistemas de correspondencia de EMAPA. Puede entregar este CITE al solicitante para su posterior rastreo.
          </p>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogComprobante = false">Cerrar</v-btn>
          <v-btn color="teal" class="rounded-pill text-capitalize white--text" @click="imprimirCaratula(comprobanteEmitido.id)">
            <v-icon left small>mdi-printer</v-icon> Imprimir Carátula con QR
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
  name: 'VentanillaExterna',
  data() {
    return {
      ventanillas: [],
      unidades: [],
      funcionariosDestino: [],
      guardando: false,

      dialogRegistro: false,
      formVentanilla: {
        id_ventanilla: null,
        remitente_externo: '',
        referencia: '',
        asunto: '',
        prioridad: 'ALTA',
        nro_fojas: 1,
        nro_anexos: 0,
        id_unidad_destino: null,
        id_funcionario_destino: null,
      },

      dialogComprobante: false,
      comprobanteEmitido: null,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  mounted() {
    this.cargarVentanillas();
    this.cargarUnidades();
  },
  methods: {
    async cargarVentanillas() {
      try {
        const res = await window.axios.get('/api/correspondencia/ventanillas');
        if (res.data && res.data.success) {
          this.ventanillas = res.data.data;
        }
      } catch (e) {}
    },
    async cargarUnidades() {
      try {
        const res = await window.axios.get('/api/rrhh/organigrama');
        if (res.data && res.data.success) {
          const planas = [];
          const aplanar = (items) => {
            items.forEach(u => {
              planas.push({ id: u.id, nombre: u.nombre, puestos: u.puestos || [] });
              if (u.dependencias && u.dependencias.length) aplanar(u.dependencias);
            });
          };
          aplanar(res.data.data);
          this.unidades = planas;
        }
      } catch (e) {}
    },
    cargarFuncionariosUnidad() {
      const u = this.unidades.find(x => x.id === this.formVentanilla.id_unidad_destino);
      if (u && u.puestos) {
        const funcs = [];
        u.puestos.forEach(p => {
          if (p.asignaciones && p.asignaciones.length && p.asignaciones[0].persona) {
            funcs.push({
              id: p.asignaciones[0].persona.id,
              nombre_completo: `${p.asignaciones[0].persona.nombre_completo} (${p.nombre})`,
            });
          }
        });
        this.funcionariosDestino = funcs;
      } else {
        this.funcionariosDestino = [];
      }
    },
    abrirModalRegistro() {
      this.formVentanilla = {
        id_ventanilla: this.ventanillas.length ? this.ventanillas[0].id : null,
        remitente_externo: '',
        referencia: '',
        asunto: '',
        prioridad: 'ALTA',
        nro_fojas: 1,
        nro_anexos: 0,
        id_unidad_destino: this.unidades.length ? this.unidades[0].id : null,
        id_funcionario_destino: null,
      };
      this.dialogRegistro = true;
    },
    async registrarEntrada() {
      if (!this.formVentanilla.remitente_externo || !this.formVentanilla.asunto || !this.formVentanilla.id_unidad_destino) {
        this.mostrarMensaje('Completa los campos obligatorios.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/ventanillas/entrada', this.formVentanilla);
        if (res.data && res.data.success) {
          this.comprobanteEmitido = res.data.data;
          this.dialogRegistro = false;
          this.dialogComprobante = true;
          this.mostrarMensaje('Correspondencia externa recepcionada.', 'success');
        }
      } catch (e) {
        this.mostrarMensaje('Error al recepcionar trámite.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    imprimirCaratula(id) {
      window.open(`/api/correspondencia/hojas-ruta/${id}/caratula`, '_blank');
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
