<template>
  <v-dialog :value="value" max-width="500" persistent @input="$emit('input', $event)">
    <v-card rounded="lg">
      <v-card-title class="primary white--text py-3">
        <v-icon color="white" class="mr-2">mdi-cash-fast</v-icon>
        <span class="text-h6 font-weight-bold">Movimiento Menor de Caja Chica</span>
      </v-card-title>

      <v-card-text class="pt-4">
        <v-radio-group v-model="formulario.tipo" row mandatory class="mt-0 mb-3">
          <v-radio label="Egreso / Salida de Efectivo" value="EGRESO" color="error"></v-radio>
          <v-radio label="Ingreso Extraordinario" value="INGRESO" color="success"></v-radio>
        </v-radio-group>

        <div class="mb-3">
          <v-text-field
            v-model.number="formulario.monto"
            type="number"
            outlined
            dense
            label="Monto *"
            prefix="Bs"
            placeholder="0.00"
            min="0.10"
            step="1"
          ></v-text-field>
        </div>

        <div class="mb-3">
          <v-text-field
            v-model="formulario.concepto"
            outlined
            dense
            label="Concepto / Motivo *"
            placeholder="Ej: Compra de cinta de embalaje, insumos..."
          ></v-text-field>
        </div>

        <div class="mb-3">
          <v-text-field
            v-model="formulario.beneficiario"
            outlined
            dense
            label="Beneficiario / Entregado a"
            placeholder="Nombre de la persona que recibe o entrega el dinero"
          ></v-text-field>
        </div>

        <div>
          <v-text-field
            v-model="formulario.comprobante"
            outlined
            dense
            label="N° Recibo / Factura Externa (Opcional)"
            placeholder="Ej: REC-124, FAC-987"
          ></v-text-field>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="px-4 py-3">
        <v-btn text color="secondary" @click="$emit('cancelar')" :disabled="guardando">
          Cancelar
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn
          :color="formulario.tipo === 'EGRESO' ? 'error' : 'success'"
          class="text-white px-5 rounded-pill font-weight-bold"
          :loading="guardando"
          :disabled="!formulario.concepto || formulario.monto <= 0"
          @click="guardarMovimiento"
        >
          <v-icon left small>mdi-content-save</v-icon> Registrar Movimiento
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModalMovimientoCaja',
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    sesionId: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      guardando: false,
      formulario: {
        tipo: 'EGRESO',
        monto: null,
        concepto: '',
        beneficiario: '',
        comprobante: '',
      },
    };
  },
  methods: {
    async guardarMovimiento() {
      if (!this.sesionId) {
        alert('No hay una sesión activa de caja.');
        return;
      }
      this.guardando = true;
      try {
        const payload = {
          id_sesion: this.sesionId,
          ...this.formulario,
        };
        const res = await axios.post('/api/comercial/caja-sesiones/movimiento', payload);
        alert(res.data?.message || 'Movimiento registrado.');
        this.$emit('movimiento-registrado');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al registrar el movimiento.');
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>
