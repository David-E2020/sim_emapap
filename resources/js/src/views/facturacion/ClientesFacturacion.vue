<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Clientes Facturación Libre</h2>
            <span class="text-caption text-secondary">Directorio de clientes para facturas especiales y validación de estado activo en el padrón del SIN</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="rounded-pill elevation-2 text-capitalize" @click="dialogoNuevo = true">
            <v-icon left small>mdi-account-plus</v-icon> + Nuevo Cliente
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- LISTA DE CLIENTES -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title>
        <v-text-field
          v-model="busqueda"
          prepend-inner-icon="mdi-magnify"
          label="Buscar por Razón Social, NIT o CI..."
          dense
          outlined
          hide-details
          clearable
          style="max-width: 400px;"
        ></v-text-field>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="clientes"
        :search="busqueda"
        :loading="cargando"
        class="elevation-0"
        no-data-text="No hay clientes registrados"
      >
        <template v-slot:item.numero_documento="{ item }">
          <span class="font-weight-bold">{{ item.numero_documento }} {{ item.complemento ? '-' + item.complemento : '' }}</span>
        </template>

        <template v-slot:item.tipo="{ item }">
          <v-chip x-small outlined :color="item.codigo_tipo_documento_identidad === 5 ? 'primary' : 'secondary'" class="font-weight-medium">
            {{ item.codigo_tipo_documento_identidad === 5 ? 'NIT' : 'CI' }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <v-btn icon small color="primary" @click="verificarNit(item.numero_documento)">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-icon small v-bind="attrs" v-on="on">mdi-check-decagram</v-icon>
              </template>
              <span>Verificar validez ante el SIN</span>
            </v-tooltip>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO NUEVO CLIENTE -->
    <v-dialog v-model="dialogoNuevo" max-width="500">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-account-plus</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Registrar Cliente</h3>
        </div>

        <v-select
          v-model="formCliente.codigo_tipo_documento_identidad"
          :items="[
            { codigo: 1, descripcion: 'CÉDULA DE IDENTIDAD (CI)' },
            { codigo: 5, descripcion: 'NIT' }
          ]"
          item-text="descripcion"
          item-value="codigo"
          label="Tipo de Documento *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <v-text-field
          v-model="formCliente.numero_documento"
          label="Número de Documento / NIT *"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model="formCliente.nombre_razon_social"
          label="Nombre o Razón Social *"
          dense
          outlined
          class="mb-2"
        ></v-text-field>

        <v-text-field
          v-model="formCliente.correo_electronico"
          label="Correo Electrónico (Opcional)"
          type="email"
          dense
          outlined
          class="mb-3"
        ></v-text-field>

        <div class="d-flex justify-end gap-2">
          <v-btn text @click="dialogoNuevo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardando" @click="guardarCliente">Guardar</v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'ClientesFacturacion',
  data() {
    return {
      cargando: false,
      guardando: false,
      dialogoNuevo: false,
      busqueda: '',
      clientes: [],
      formCliente: {
        codigo_tipo_documento_identidad: 1,
        numero_documento: '',
        nombre_razon_social: '',
        correo_electronico: '',
      },
      headers: [
        { text: 'Tipo', value: 'tipo', width: '90px' },
        { text: 'Número Documento', value: 'numero_documento', width: '160px' },
        { text: 'Razón Social / Nombre', value: 'nombre_razon_social' },
        { text: 'Correo Electrónico', value: 'correo_electronico' },
        { text: 'Acciones', value: 'acciones', align: 'right', sortable: false, width: '100px' },
      ],
    };
  },
  mounted() {
    this.cargarClientes();
  },
  methods: {
    async cargarClientes() {
      this.cargando = true;
      try {
        const res = await window.axios.get('/api/facturacion/clientes', {
          params: { search: this.busqueda || '', per_page: 50 },
        });
        this.clientes = res.data.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.cargando = false;
      }
    },
    async verificarNit(nit) {
      try {
        const res = await window.axios.get(`/api/facturacion/siat/verificar-nit/${nit}`);
        if (res.data && res.data.data && res.data.data.valido) {
          this.$message.success('El NIT se encuentra ACTIVO en el padrón de Impuestos Nacionales.');
        } else {
          this.$message.warning('El NIT NO es válido en el SIN.');
        }
      } catch (e) {
        this.$message.error('Error al consultar NIT');
      }
    },
    async guardarCliente() {
      if (!this.formCliente.numero_documento || !this.formCliente.nombre_razon_social) {
        this.$message.warning('Complete los campos obligatorios.');
        return;
      }
      try {
        const res = await window.axios.post('/api/facturacion/clientes', this.formCliente);
        if (res.data && res.data.success) {
          this.$message.success('Cliente registrado correctamente en el padrón.');
          this.dialogoNuevo = false;
          this.formCliente = {
            codigo_tipo_documento_identidad: 1,
            numero_documento: '',
            complemento: '',
            nombre_razon_social: '',
            correo_electronico: '',
            telefono: '',
          };
          this.cargarClientes();
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
  border: 1px solid rgba(0, 0, 0, 0.06) !important;
}
.theme--dark .erp-card-elevated {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.gap-2 {
  gap: 8px;
}
</style>
