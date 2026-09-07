<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-package-variant-closed</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Servicios y Tarifas SIN</h2>
            <span class="text-caption text-secondary">Catálogo de servicios de agua potable, alcantarillado sanitario y conexiones de EMAPAP homologados con el SIN</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="rounded-pill elevation-2 text-capitalize" @click="dialogoNuevo = true">
            <v-icon left small>mdi-plus-circle</v-icon> + Nuevo Servicio
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTADO DE PRODUCTOS / SERVICIOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title>
        <v-text-field
          v-model="busqueda"
          prepend-inner-icon="mdi-magnify"
          label="Buscar por descripción, código interno o código SIN..."
          dense
          outlined
          hide-details
          clearable
          style="max-width: 450px;"
        ></v-text-field>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="productos"
        :search="busqueda"
        :loading="cargando"
        class="elevation-0"
        no-data-text="No hay servicios registrados"
      >
        <template v-slot:item.codigo_producto_empresa="{ item }">
          <span class="font-weight-bold text-primary">{{ item.codigo_producto_empresa }}</span>
        </template>

        <template v-slot:item.precio_unitario="{ item }">
          <span class="font-weight-bold">Bs {{ parseFloat(item.precio_unitario).toFixed(2) }}</span>
        </template>

        <template v-slot:item.codigo_producto_sin="{ item }">
          <v-chip x-small color="grey lighten-2">SIN: {{ item.codigo_producto_sin }}</v-chip>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO NUEVO SERVICIO -->
    <v-dialog v-model="dialogoNuevo" max-width="500">
      <v-card rounded="lg" class="pa-4">
        <h3 class="text-h6 font-weight-bold mb-3">Registrar Nuevo Servicio / Tarifa</h3>

        <v-text-field
          v-model="form.codigo_producto_empresa"
          label="Código EMAPAP *"
          placeholder="Ej. AGUA-IND-01, MULTA-01, RECONEX-01"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model="form.descripcion"
          label="Descripción del Servicio *"
          placeholder="Ej. CONSUMO AGUA POTABLE - INDUSTRIAL"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model.number="form.precio_unitario"
          label="Precio Unitario / Tarifa Base (Bs) *"
          type="number"
          step="0.01"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-select
          v-model="form.codigo_producto_sin"
          :items="catalogoSinItems"
          item-text="descripcion"
          item-value="codigo"
          label="Homologación Producto SIN *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <div class="d-flex justify-end gap-2 mt-3">
          <v-btn text @click="dialogoNuevo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardando" @click="guardarServicio">
            Guardar Servicio
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'ProductosServicios',
  data() {
    return {
      cargando: false,
      guardando: false,
      dialogoNuevo: false,
      busqueda: '',
      productos: [],
      form: {
        codigo_producto_empresa: '',
        descripcion: '',
        precio_unitario: 0,
        codigo_producto_sin: '86311',
        codigo_actividad: '360000',
        codigo_unidad_medida: 58,
      },
      catalogoSinItems: [
        { codigo: '86311', descripcion: '86311 - SERVICIOS DE DISTRIBUCIÓN DE AGUA POTABLE' },
        { codigo: '86312', descripcion: '86312 - SERVICIOS DE ALCANTARILLADO Y EVACUACIÓN DE AGUAS RESIDUALES' },
        { codigo: '86313', descripcion: '86313 - INSTALACIÓN DE ACOMETIDAS Y CONEXIÓN DE AGUA' },
        { codigo: '86314', descripcion: '86314 - SERVICIOS DE RECONEXIÓN Y MANTENIMIENTO DE REDES' },
        { codigo: '86315', descripcion: '86315 - INSTALACIÓN Y REPARACIÓN DE MEDIDORES' },
      ],
      headers: [
        { text: 'Código EMAPAP', value: 'codigo_producto_empresa', width: '150px' },
        { text: 'Descripción del Servicio', value: 'descripcion' },
        { text: 'Actividad Económica', value: 'codigo_actividad', width: '160px' },
        { text: 'Homologación SIN', value: 'codigo_producto_sin', width: '160px' },
        { text: 'Precio Venta', value: 'precio_unitario', align: 'right', width: '140px' },
      ],
    };
  },
  mounted() {
    this.cargarProductos();
  },
  methods: {
    async cargarProductos() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/facturacion/siat/productos');
        this.productos = res.data.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.cargando = false;
      }
    },
    async guardarServicio() {
      if (!this.form.codigo_producto_empresa || !this.form.descripcion || this.form.precio_unitario < 0) {
        this.$message.warning('Complete los campos obligatorios');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/facturacion/siat/productos', this.form);
        if (res.data && res.data.success) {
          this.$message.success('Servicio registrado correctamente en el catálogo.');
          this.dialogoNuevo = false;
          this.form = {
            codigo_producto_empresa: '',
            descripcion: '',
            precio_unitario: 0,
            codigo_producto_sin: '86311',
            codigo_actividad: '360000',
            codigo_unidad_medida: 58,
          };
          this.cargarProductos();
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.$message.error(msg);
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
}
.gap-2 {
  gap: 8px;
}
</style>
