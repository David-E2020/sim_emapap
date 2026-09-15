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
            <v-avatar color="primary" rounded="lg" size="40" class="elevation-1">
              <v-icon color="white">mdi-store</v-icon>
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

          <v-list dense class="py-0 transparent">
            <v-list-item v-for="pv in sucursal.puntos_venta" :key="pv.id" class="px-0 py-1">
              <v-list-item-avatar size="32" class="my-0 mr-2 pv-avatar">
                <v-icon size="18" color="primary">mdi-cash-register</v-icon>
              </v-list-item-avatar>
              <v-list-item-content class="py-0">
                <v-list-item-title class="font-weight-medium text-body-2">
                  Punto {{ pv.codigo_punto_venta }}: {{ pv.nombre }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-caption">
                  <span class="text-success font-weight-bold">● CUFD Vigente</span> |
                  <span v-if="pv.sesion_activa" class="success--text font-weight-bold">
                    <v-icon x-small color="success">mdi-lock-open-variant</v-icon> Turno #{{ pv.sesion_activa.numero_sesion }} ({{ pv.sesion_activa.cajero ? pv.sesion_activa.cajero.name : 'En curso' }})
                  </span>
                  <span v-else class="grey--text">
                    <v-icon x-small color="grey">mdi-lock-outline</v-icon> Caja Cerrada
                  </span>
                  |
                  <span v-if="pv.cajero_defecto" class="primary--text font-weight-bold">
                    <v-icon x-small color="primary">mdi-account-check</v-icon> {{ pv.cajero_defecto.name }}
                  </span>
                  <span v-else class="text-secondary">Sin cajero habitual</span>
                </v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action class="my-0 d-flex flex-row align-center gap-1">
                <!-- Ir a Cobranzas / Ventanilla -->
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="primary" v-bind="attrs" v-on="on" :to="{ path: '/facturacion/caja' }">
                      <v-icon small>mdi-cash-register</v-icon>
                    </v-btn>
                  </template>
                  <span>Ir a Ventanilla de Cobranzas</span>
                </v-tooltip>

                <!-- Asignar Cajero Habitual -->
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="abrirAsignarCajero(pv)">
                      <v-icon small>mdi-account-cog</v-icon>
                    </v-btn>
                  </template>
                  <span>Asignar Cajero Habitual a esta Caja</span>
                </v-tooltip>

                <!-- Solicitar CUIS -->
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="secondary" v-bind="attrs" v-on="on" @click="renovarCuis(pv)">
                      <v-icon small>mdi-key-change</v-icon>
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
          <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
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

    <!-- DIÁLOGO ASIGNAR CAJERO HABITUAL -->
    <v-dialog v-model="dialogoAsignarCajero" max-width="480">
      <v-card rounded="lg" v-if="puntoSeleccionado" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-account-cog</v-icon>
          </v-avatar>
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Asignar Cajero Habitual</h3>
            <span class="text-caption text-secondary">
              Punto {{ puntoSeleccionado.codigo_punto_venta }}: {{ puntoSeleccionado.nombre }}
            </span>
          </div>
        </div>

        <p class="text-caption text-secondary mb-3">
          Seleccione el funcionario que operará habitualmente esta ventanilla física al abrir su turno de recaudación:
        </p>

        <v-select
          v-model="cajeroSeleccionadoId"
          :items="cajeros"
          item-text="name"
          item-value="id"
          label="Funcionario / Cajero Asignado"
          placeholder="Seleccione o deje sin asignar..."
          outlined
          dense
          clearable
          prepend-inner-icon="mdi-account"
        >
          <template v-slot:item="{ item }">
            <div class="d-flex flex-column py-1">
              <span class="font-weight-bold text-body-2">{{ item.name }}</span>
              <span class="text-caption text-secondary">Usuario: {{ item.usr_usuario }}</span>
            </div>
          </template>
        </v-select>

        <div class="d-flex justify-end gap-2 mt-4">
          <v-btn text @click="dialogoAsignarCajero = false">Cancelar</v-btn>
          <v-btn color="primary" class="text-white rounded-pill font-weight-bold" :loading="guardandoAsignacion" @click="guardarAsignacionCajero">
            Guardar Asignación
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
      dialogoAsignarCajero: false,
      puntoSeleccionado: null,
      cajeroSeleccionadoId: null,
      guardandoAsignacion: false,
      cajeros: [],
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
    async cargarCajeros() {
      try {
        const res = await window.axios.get('/api/comercial/caja-sesiones/cajeros');
        this.cajeros = res.data?.data || [];
      } catch (e) {
        console.error('Error al cargar cajeros:', e);
      }
    },
    abrirAsignarCajero(pv) {
      this.puntoSeleccionado = pv;
      this.cajeroSeleccionadoId = pv.id_cajero_defecto || null;
      this.cargarCajeros();
      this.dialogoAsignarCajero = true;
    },
    async guardarAsignacionCajero() {
      if (!this.puntoSeleccionado) return;
      this.guardandoAsignacion = true;
      try {
        await window.axios.post(`/api/comercial/cajas/${this.puntoSeleccionado.id}/asignar-cajero`, {
          id_cajero: this.cajeroSeleccionadoId,
        });
        if (this.$message) {
          this.$message.success('Cajero habitual asignado a la caja correctamente.');
        } else {
          alert('Cajero habitual asignado a la caja correctamente.');
        }
        this.dialogoAsignarCajero = false;
        this.cargarDatos();
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al asignar cajero a la caja.';
        if (this.$message) {
          this.$message.error(msg);
        } else {
          alert(msg);
        }
      } finally {
        this.guardandoAsignacion = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
  border: 1px solid rgba(0, 0, 0, 0.06) !important;
}
.theme--dark .erp-card-elevated {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.pv-avatar {
  background: rgba(43, 108, 176, 0.12) !important;
}
.theme--dark .pv-avatar {
  background: rgba(255, 255, 255, 0.08) !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
