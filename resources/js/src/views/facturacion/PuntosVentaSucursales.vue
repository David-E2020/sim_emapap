<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-store-cog-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Sucursales y Puntos de Venta</h2>
            <span class="text-caption text-secondary">Control de establecimientos fiscales, autorización de puntos de venta y códigos CUFD/CUIS con el SIN</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="rounded-pill elevation-2 text-capitalize" @click="abrirModalNuevoPunto(sucursales[0])">
            <v-icon left small>mdi-plus-circle</v-icon> + Nuevo Punto de Venta
          </v-btn>
          <v-btn outlined color="secondary" class="rounded-pill text-capitalize" @click="cargarDatos">
            <v-icon left small>mdi-refresh</v-icon> Actualizar
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE SUCURSALES -->
    <v-row>
      <v-col cols="12" md="6" v-for="sucursal in sucursales" :key="sucursal.id">
        <v-card rounded="lg" class="pa-4 erp-card-elevated h-100">
          <div class="d-flex justify-space-between align-center mb-3">
            <div>
              <div class="d-flex align-center">
                <v-chip color="primary" x-small class="font-weight-bold mr-2 text-white">
                  SUCURSAL {{ sucursal.codigo_sucursal }}
                </v-chip>
                <h3 class="text-h6 font-weight-bold mb-0">{{ sucursal.nombre }}</h3>
              </div>
              <span class="text-caption text-secondary">{{ sucursal.municipio }} - {{ sucursal.departamento }}</span>
            </div>
            <v-avatar color="grey lighten-4" size="40">
              <v-icon color="primary">mdi-store</v-icon>
            </v-avatar>
          </div>

          <v-divider class="mb-3"></v-divider>

          <div class="mb-2 text-body-2">
            <strong>Dirección:</strong> {{ sucursal.direccion }}
          </div>
          <div class="mb-3 text-body-2">
            <strong>Teléfono:</strong> {{ sucursal.telefono || '2147001' }}
          </div>

          <!-- Puntos de Venta asociados -->
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="font-weight-bold text-subtitle-2 text-secondary">
              PUNTOS DE VENTA ACTIVOS ({{ sucursal.puntos_venta ? sucursal.puntos_venta.length : 0 }})
            </span>
            <v-btn x-small text color="primary" @click="abrirModalNuevoPunto(sucursal)">
              <v-icon left x-small>mdi-plus</v-icon> Agregar Punto
            </v-btn>
          </div>

          <v-list dense class="py-0">
            <v-list-item v-for="pv in sucursal.puntos_venta" :key="pv.id" class="px-0 py-1">
              <v-list-item-avatar size="32" color="primary lighten-5" class="my-0 mr-2">
                <v-icon size="18" color="primary">mdi-cash-register</v-icon>
              </v-list-item-avatar>
              <v-list-item-content class="py-0">
                <v-list-item-title class="font-weight-medium text-body-2">
                  Punto {{ pv.codigo_punto_venta }}: {{ pv.nombre }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-caption text-success font-weight-bold">
                  ● CUFD Vigente | Estado: {{ pv._estado }}
                </v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action class="my-0 d-flex flex-row align-center gap-1">
                <!-- Solicitar CUIS -->
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="teal" v-bind="attrs" v-on="on" @click="renovarCuis(pv)">
                      <v-icon small>mdi-key-sync</v-icon>
                    </v-btn>
                  </template>
                  <span>Solicitar / Renovar CUIS</span>
                </v-tooltip>

                <!-- Cerrar Punto de Venta ante SIN -->
                <v-tooltip bottom v-if="pv.codigo_punto_venta > 0 && pv._estado === 'ACTIVO'">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="confirmarCierrePunto(pv)">
                      <v-icon small>mdi-close-circle-outline</v-icon>
                    </v-btn>
                  </template>
                  <span>Cerrar Punto ante el SIN</span>
                </v-tooltip>

                <v-chip x-small :color="pv._estado === 'ACTIVO' ? 'success' : 'grey'" outlined>
                  {{ pv._estado }}
                </v-chip>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO REGISTRAR PUNTO DE VENTA ANTE EL SIN -->
    <v-dialog v-model="dialogoNuevoPunto" max-width="500">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="primary" size="36" class="mr-2 text-white">
            <v-icon small color="white">mdi-store-plus</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Registrar Punto de Venta en el SIN</h3>
        </div>

        <p class="text-caption text-secondary mb-3">
          Se enviará la solicitud al Servicio de Impuestos Nacionales para autorizar el nuevo punto de cobro y emitir su código CUIS oficial.
        </p>

        <v-text-field
          v-model="formPunto.nombre"
          label="Nombre del Punto de Venta *"
          placeholder="Ej. Caja Central 2, Ventanilla Trámites"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model="formPunto.descripcion"
          label="Descripción o Ubicación *"
          placeholder="Ej. Ventanilla de Cobranza de Agua Potable"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-select
          v-model="formPunto.tipo_punto_venta"
          :items="tiposPuntoVenta"
          item-text="descripcion"
          item-value="codigo"
          label="Tipo de Punto de Venta *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <div class="d-flex justify-end gap-2 mt-3">
          <v-btn text @click="dialogoNuevoPunto = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardandoPunto" @click="guardarPuntoVenta">
            Autorizar ante el SIN
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'PuntosVentaSucursales',
  data() {
    return {
      cargando: false,
      guardandoPunto: false,
      dialogoNuevoPunto: false,
      sucursales: [],
      sucursalSeleccionada: null,
      formPunto: {
        id_sucursal: null,
        nombre: '',
        descripcion: '',
        tipo_punto_venta: 5,
      },
      tiposPuntoVenta: [
        { codigo: 1, descripcion: 'Ventanilla de Cobranza / Recaudación' },
        { codigo: 5, descripcion: 'Punto de Venta Fijo' },
        { codigo: 2, descripcion: 'Punto Móvil' },
      ],
    };
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/facturacion/siat/sucursales');
        this.sucursales = res.data.data;
      } catch (e) {
        console.error('Error al cargar sucursales', e);
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevoPunto(sucursal) {
      this.sucursalSeleccionada = sucursal;
      this.formPunto = {
        id_sucursal: sucursal ? sucursal.id : (this.sucursales[0] ? this.sucursales[0].id : 1),
        nombre: '',
        descripcion: '',
        tipo_punto_venta: 5,
      };
      this.dialogoNuevoPunto = true;
    },
    async guardarPuntoVenta() {
      if (!this.formPunto.nombre || !this.formPunto.descripcion) {
        this.$message.warning('Ingrese nombre y descripción del punto de venta');
        return;
      }
      this.guardandoPunto = true;
      try {
        const res = await window.axios.post('/api/facturacion/siat/puntos-venta', this.formPunto);
        if (res.data && res.data.success) {
          this.$message.success('Punto de venta autorizado exitosamente por el SIN.');
          this.dialogoNuevoPunto = false;
          this.cargarDatos();
        } else {
          this.$message.error(res.data.message || 'No se pudo autorizar el punto de venta');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.$message.error(msg);
      } finally {
        this.guardandoPunto = false;
      }
    },
    async renovarCuis(pv) {
      try {
        const res = await window.axios.post(`/api/facturacion/siat/puntos-venta/${pv.id}/cuis`);
        if (res.data && res.data.success) {
          this.$message.success(`CUIS obtenido con éxito: ${res.data.cuis}`);
          this.cargarDatos();
        } else {
          this.$message.error('No se pudo renovar el CUIS');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.$message.error(msg);
      }
    },
    async confirmarCierrePunto(pv) {
      if (!confirm(`¿Está seguro de cerrar formalmente ante el SIN el Punto de Venta N° ${pv.codigo_punto_venta} (${pv.nombre})?`)) {
        return;
      }
      try {
        const res = await window.axios.post(`/api/facturacion/siat/puntos-venta/${pv.id}/cierre`);
        if (res.data && res.data.success) {
          this.$message.success('Punto de venta cerrado formalmente ante el SIN.');
          this.cargarDatos();
        } else {
          this.$message.error(res.data.message || 'Error al cerrar punto de venta');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.$message.error(msg);
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
