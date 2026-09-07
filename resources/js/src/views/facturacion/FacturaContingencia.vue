<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="warning" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-document-alert-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Facturación Manual de Contingencia</h2>
            <span class="text-caption text-secondary">Transcripción y registro de facturas físicas emitidas con talonario CAFC durante contingencias</span>
          </div>
        </div>
      </div>
    </v-card>

    <v-card rounded="lg" class="pa-4 erp-card-elevated">
      <v-alert type="info" text dense class="mb-4">
        Utilice este módulo para transcribir las facturas emitidas manualmente en talonario de contingencia autorizado por el SIN. Deberá consignar el CAFC y la fecha real en que se efectuó la venta.
      </v-alert>

      <v-row dense>
        <v-col cols="12" sm="4">
          <v-text-field
            v-model="form.cafc"
            label="Código CAFC *"
            placeholder="Ej. 1000000000001"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="form.numero_factura"
            label="N° Factura del Talonario *"
            type="number"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="form.fecha_emision"
            label="Fecha y Hora de Emisión Manual *"
            type="datetime-local"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-select
            v-model="form.codigo_tipo_documento_identidad"
            :items="[
              { codigo: 1, descripcion: 'CI - Cédula de Identidad' },
              { codigo: 5, descripcion: 'NIT - Número Identificación Tributaria' }
            ]"
            item-text="descripcion"
            item-value="codigo"
            label="Tipo Documento *"
            dense
            outlined
          ></v-select>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="form.numero_documento"
            label="Número de Documento / NIT *"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="form.nombre_razon_social"
            label="Nombre o Razón Social *"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model.number="form.monto_total"
            label="Monto Total Venta (Bs) *"
            type="number"
            step="0.1"
            dense
            outlined
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="8">
          <v-text-field
            v-model="form.descripcion"
            label="Detalle de Servicios / Glosa *"
            placeholder="Ej. Consumo mensual de agua potable y alcantarillado sanitario"
            dense
            outlined
          ></v-text-field>
        </v-col>
      </v-row>

      <div class="d-flex justify-end gap-2 mt-3">
        <v-btn
          color="primary"
          class="text-capitalize font-weight-bold"
          :loading="guardando"
          :disabled="!form.cafc || !form.numero_factura || !form.numero_documento"
          @click="guardarContingencia"
        >
          <v-icon left>mdi-content-save-check</v-icon> Transcribir y Validar Factura
        </v-btn>
      </div>
    </v-card>
  </div>
</template>

<script>
export default {
  name: 'FacturaContingencia',
  data() {
    return {
      guardando: false,
      form: {
        cafc: '',
        numero_factura: '',
        fecha_emision: new Date().toISOString().slice(0, 16),
        codigo_tipo_documento_identidad: 1,
        numero_documento: '',
        nombre_razon_social: '',
        monto_total: 0,
        descripcion: '',
      },
    };
  },
  methods: {
    async guardarContingencia() {
      this.guardando = true;
      try {
        const payload = {
          id_sucursal: 0,
          id_punto_venta: 0,
          codigo_tipo_documento_identidad: this.form.codigo_tipo_documento_identidad,
          numero_documento: this.form.numero_documento,
          nombre_razon_social: this.form.nombre_razon_social,
          codigo_metodo_pago: 1,
          items: [
            {
              codigo_producto_empresa: 'AGUA-DOM-01',
              codigo_actividad: '360000',
              codigo_producto_sin: '86311',
              descripcion: this.form.descripcion || 'CONSUMO AGUA POTABLE - CONTINGENCIA',
              cantidad: 1,
              precio_unitario: Number(this.form.monto_total),
              monto_descuento: 0,
            },
          ],
        };

        const res = await window.axios.post('/api/facturacion/facturas', payload);
        if (res.data.success) {
          this.$message.success('Factura de contingencia transcrita y guardada.');
          this.$router.push({ name: 'facturacion_bandeja' });
        }
      } catch (e) {
        this.$message.error('Error al guardar contingencia');
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
