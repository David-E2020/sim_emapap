<template>
  <v-dialog :value="value" max-width="820" persistent @input="$emit('input', $event)">
    <v-card rounded="lg" v-if="sesion">
      <v-card-title class="primary white--text py-3 d-flex justify-space-between align-center">
        <div class="d-flex align-center">
          <v-icon color="white" class="mr-2">mdi-cash-check</v-icon>
          <span class="text-h6 font-weight-bold">Arqueo y Cierre de Turno de Caja</span>
        </div>
        <v-chip color="white" text-color="primary" small class="font-weight-bold">
          Turno #{{ sesion.numero_sesion }}
        </v-chip>
      </v-card-title>

      <v-card-text class="pt-4">
        <!-- Resumen de la Sesión / Turno -->
        <v-row dense class="mb-3">
          <v-col cols="12" md="6">
            <v-card outlined rounded="lg" class="pa-3 section-header-bg">
              <div class="text-caption text-secondary font-weight-bold mb-2 text-uppercase">
                1. Datos del Turno
              </div>
              <div class="text-body-2 mb-1">
                <strong>Ventanilla:</strong> {{ sesion.punto_venta ? sesion.punto_venta.nombre : 'Caja Central' }}
              </div>
              <div class="text-body-2 mb-1">
                <strong>Cajero(a):</strong> {{ sesion.cajero ? sesion.cajero.name : 'Usuario' }}
              </div>
              <div class="text-caption text-secondary">
                <strong>Apertura:</strong> {{ formatearFechaHora(sesion.fecha_apertura) }}
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card outlined rounded="lg" class="pa-3 section-header-bg">
              <div class="text-caption text-secondary font-weight-bold mb-2 text-uppercase">
                2. Resumen de Recaudación
              </div>
              <div class="d-flex justify-space-between text-caption mb-1">
                <span>(+) Fondo Inicial (Apertura):</span>
                <span class="font-weight-bold">Bs {{ (parseFloat(totales.monto_apertura) || 0).toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-space-between text-caption mb-1">
                <span>(+) Ventas Efectivo (Agua/Cuotas/Recibos):</span>
                <span class="font-weight-bold text-success">Bs {{ (parseFloat(totales.total_efectivo) || 0).toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-space-between text-caption mb-1">
                <span>(+) Cobros QR / Tarjeta (Sin efectivo en gaveta):</span>
                <span class="font-weight-bold text-primary">Bs {{ (parseFloat(totales.total_qr_banco) || 0).toFixed(2) }}</span>
              </div>
              <v-divider class="my-1"></v-divider>
              <div class="d-flex justify-space-between text-subtitle-2 font-weight-black primary--text">
                <span>(=) Total Efectivo Esperado:</span>
                <span>Bs {{ totalEsperado.toFixed(2) }}</span>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Matriz de Conteo Físico de Billetes y Monedas -->
        <div class="mb-3">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-subtitle-2 font-weight-bold text-secondary text-uppercase">
              3. Conteo Físico de Efectivo en Gaveta (Arqueo)
            </span>
            <v-btn x-small text color="primary" class="font-weight-bold" @click="autoCompletarExacto">
              <v-icon left x-small>mdi-auto-fix</v-icon> Cuadrar con Monto Esperado
            </v-btn>
          </div>

          <v-simple-table dense class="border rounded">
            <template v-slot:default>
              <thead>
                <tr class="section-header-bg">
                  <th class="font-weight-bold">Corte / Denominación</th>
                  <th class="font-weight-bold text-center" style="width: 140px;">Cantidad</th>
                  <th class="font-weight-bold text-right" style="width: 150px;">Subtotal (Bs)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in cortes" :key="item.key">
                  <td class="text-caption font-weight-medium">
                    <v-icon x-small :color="item.esBillete ? 'primary' : 'warning darken-1'" class="mr-1">
                      {{ item.esBillete ? 'mdi-cash' : 'mdi-circle-multiple' }}
                    </v-icon>
                    {{ item.nombre }}
                  </td>
                  <td class="text-center py-1">
                    <v-text-field
                      v-model.number="desglose[item.key]"
                      type="number"
                      dense
                      outlined
                      hide-details
                      class="centered-input dense-small"
                      min="0"
                      @input="calcularTotalFisico"
                    ></v-text-field>
                  </td>
                  <td class="text-right font-weight-bold text-caption">
                    Bs {{ ((desglose[item.key] || 0) * item.valor).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </div>

        <!-- Indicador de Arqueo y Cuadratura -->
        <v-card outlined rounded="lg" class="pa-3 mb-3" :class="claseCuadratura">
          <div class="d-flex justify-space-between align-center flex-wrap">
            <div>
              <div class="text-caption font-weight-bold text-uppercase">Resultado del Arqueo</div>
              <div class="text-h6 font-weight-black">{{ mensajeCuadratura }}</div>
            </div>
            <div class="text-right">
              <div class="text-caption">Efectivo Contado: <strong>Bs {{ totalContado.toFixed(2) }}</strong></div>
              <div class="text-caption">Diferencia: <strong>Bs {{ diferencia.toFixed(2) }}</strong></div>
            </div>
          </div>
        </v-card>

        <!-- Observaciones de Cierre -->
        <div>
          <label class="text-caption font-weight-bold text-secondary mb-1 d-block">
            Observaciones de Cierre (Opcional)
          </label>
          <v-textarea
            v-model="observacionesCierre"
            rows="2"
            dense
            outlined
            placeholder="Ingrese cualquier detalle relevante del cierre de caja..."
            hide-details
          ></v-textarea>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="px-4 py-3">
        <v-btn text color="secondary" @click="$emit('cancelar')" :disabled="guardando">
          Cancelar
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          class="text-white px-5 rounded-pill font-weight-bold elevation-1"
          :loading="guardando"
          @click="confirmarCierre"
        >
          <v-icon left small>mdi-lock-check</v-icon> Confirmar Cierre y Emitir Arqueo
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModalCierreArqueoCaja',
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    sesion: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      guardando: false,
      totales: {
        monto_apertura: 0,
        total_efectivo: 0,
        total_qr_banco: 0,
        monto_esperado_efectivo: 0,
      },
      observacionesCierre: '',
      cortes: [
        { key: 'b200', nombre: 'Billete Bs. 200', valor: 200, esBillete: true },
        { key: 'b100', nombre: 'Billete Bs. 100', valor: 100, esBillete: true },
        { key: 'b50', nombre: 'Billete Bs. 50', valor: 50, esBillete: true },
        { key: 'b20', nombre: 'Billete Bs. 20', valor: 20, esBillete: true },
        { key: 'b10', nombre: 'Billete Bs. 10', valor: 10, esBillete: true },
        { key: 'm5', nombre: 'Moneda Bs. 5', valor: 5, esBillete: false },
        { key: 'm2', nombre: 'Moneda Bs. 2', valor: 2, esBillete: false },
        { key: 'm1', nombre: 'Moneda Bs. 1', valor: 1, esBillete: false },
        { key: 'm050', nombre: 'Moneda Bs. 0.50', valor: 0.50, esBillete: false },
        { key: 'm020', nombre: 'Moneda Bs. 0.20', valor: 0.20, esBillete: false },
        { key: 'm010', nombre: 'Moneda Bs. 0.10', valor: 0.10, esBillete: false },
      ],
      desglose: {
        b200: 0,
        b100: 0,
        b50: 0,
        b20: 0,
        b10: 0,
        m5: 0,
        m2: 0,
        m1: 0,
        m050: 0,
        m020: 0,
        m010: 0,
      },
      totalContado: 0,
    };
  },
  computed: {
    totalEsperado() {
      return parseFloat(this.totales.monto_esperado_efectivo || this.sesion?.monto_esperado_efectivo || 0);
    },
    diferencia() {
      return this.totalContado - this.totalEsperado;
    },
    mensajeCuadratura() {
      if (Math.abs(this.diferencia) < 0.01) {
        return '✓ Caja Cuadrada Exacta (Diferencia: Bs 0.00)';
      }
      if (this.diferencia > 0) {
        return `▲ Sobrante de Caja: + Bs ${this.diferencia.toFixed(2)}`;
      }
      return `▼ Faltante de Caja: - Bs ${Math.abs(this.diferencia).toFixed(2)}`;
    },
    claseCuadratura() {
      if (Math.abs(this.diferencia) < 0.01) {
        return 'box-cuadratura-exacta';
      }
      if (this.diferencia > 0) {
        return 'box-cuadratura-sobrante';
      }
      return 'box-cuadratura-faltante';
    },
  },
  watch: {
    value(val) {
      if (val) {
        this.cargarResumenArqueo();
      }
    },
  },
  methods: {
    formatearFechaHora(f) {
      if (!f) return '-';
      const d = new Date(f);
      return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    async cargarResumenArqueo() {
      try {
        const res = await axios.get('/api/comercial/caja-sesiones/resumen-arqueo');
        if (res.data?.data?.totales) {
          this.totales = res.data.data.totales;
        }
      } catch (e) {
        console.error('Error al cargar resumen arqueo:', e);
      }
      this.calcularTotalFisico();
    },
    calcularTotalFisico() {
      let suma = 0;
      this.cortes.forEach(c => {
        const cant = parseInt(this.desglose[c.key]) || 0;
        suma += cant * c.valor;
      });
      this.totalContado = parseFloat(suma.toFixed(2));
    },
    autoCompletarExacto() {
      // Descompone el monto esperado en billetes de manera referencial
      let restante = Math.floor(this.totalEsperado);
      let centavos = Math.round((this.totalEsperado - restante) * 100);

      const nuevoDesglose = {
        b200: 0, b100: 0, b50: 0, b20: 0, b10: 0,
        m5: 0, m2: 0, m1: 0, m050: 0, m020: 0, m010: 0,
      };

      const nominales = [
        { k: 'b200', v: 200 }, { k: 'b100', v: 100 }, { k: 'b50', v: 50 },
        { k: 'b20', v: 20 }, { k: 'b10', v: 10 }, { k: 'm5', v: 5 },
        { k: 'm2', v: 2 }, { k: 'm1', v: 1 },
      ];

      nominales.forEach(n => {
        if (restante >= n.v) {
          const c = Math.floor(restante / n.v);
          nuevoDesglose[n.k] = c;
          restante -= c * n.v;
        }
      });

      if (centavos >= 50) { nuevoDesglose.m050 = 1; centavos -= 50; }
      if (centavos >= 40) { nuevoDesglose.m020 = 2; centavos -= 40; }
      else if (centavos >= 20) { nuevoDesglose.m020 = 1; centavos -= 20; }
      if (centavos >= 10) { nuevoDesglose.m010 = 1; }

      this.desglose = nuevoDesglose;
      this.calcularTotalFisico();
    },
    async confirmarCierre() {
      if (!confirm(`¿Está seguro de cerrar el turno de caja?\nEfectivo declarado: Bs ${this.totalContado.toFixed(2)}\nDiferencia: Bs ${this.diferencia.toFixed(2)}`)) {
        return;
      }

      this.guardando = true;
      try {
        const payload = {
          id_sesion: this.sesion.id,
          monto_cierre_declarado: this.totalContado,
          desglose_billetes: this.desglose,
          observaciones_cierre: this.observacionesCierre,
        };

        const res = await axios.post('/api/comercial/caja-sesiones/cerrar', payload);
        this.$emit('sesion-cerrada', res.data.data);
      } catch (e) {
        alert(e.response?.data?.message || 'Error al cerrar la sesión de caja.');
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.dense-small >>> .v-input__control {
  min-height: 28px !important;
}
.dense-small >>> input {
  text-align: center;
  font-size: 11px;
  font-weight: bold;
  padding: 2px 4px !important;
}
.section-header-bg {
  background-color: #f8fafc;
}
.theme--dark .section-header-bg {
  background-color: rgba(255, 255, 255, 0.04);
}
.box-cuadratura-exacta {
  background-color: rgba(16, 185, 129, 0.08) !important;
  border: 1px solid #10b981 !important;
  color: #059669;
}
.theme--dark .box-cuadratura-exacta {
  background-color: rgba(16, 185, 129, 0.15) !important;
  border: 1px solid #059669 !important;
  color: #34d399;
}
.box-cuadratura-sobrante {
  background-color: rgba(37, 99, 235, 0.08) !important;
  border: 1px solid #3b82f6 !important;
  color: #1d4ed8;
}
.theme--dark .box-cuadratura-sobrante {
  background-color: rgba(37, 99, 235, 0.15) !important;
  border: 1px solid #2563eb !important;
  color: #60a5fa;
}
.box-cuadratura-faltante {
  background-color: rgba(239, 68, 68, 0.08) !important;
  border: 1px solid #ef4444 !important;
  color: #b91c1c;
}
.theme--dark .box-cuadratura-faltante {
  background-color: rgba(239, 68, 68, 0.15) !important;
  border: 1px solid #dc2626 !important;
  color: #f87171;
}
</style>
