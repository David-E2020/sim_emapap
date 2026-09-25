<template>
  <v-dialog v-model="dialogoVisible" max-width="460" persistent>
    <v-card rounded="xl" class="pa-4 erp-qr-modal elevation-8">
      <!-- CABECERA -->
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="d-flex align-center">
          <v-avatar color="primary" size="40" class="mr-2 elevation-2">
            <v-icon color="white">mdi-qrcode-scan</v-icon>
          </v-avatar>
          <div>
            <div class="text-subtitle-1 font-weight-black text--primary line-height-tight">
              Cobro con Simple QR
            </div>
            <div class="text-caption text-secondary">
              BCB / ASOBAN Interoperable
            </div>
          </div>
        </div>

        <v-btn icon small @click="cerrar">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <v-divider class="mb-3"></v-divider>

      <!-- ERROR NATIVO -->
      <v-alert v-if="errorMensaje" type="error" dense dismissible class="mx-4 my-2">
        {{ errorMensaje }}
      </v-alert>

      <!-- CUERPO PRINCIPAL -->
      <div v-if="cargando" class="text-center py-8">
        <v-progress-circular indeterminate color="primary" size="50"></v-progress-circular>
        <div class="text-body-2 font-weight-bold mt-3 text-secondary">
          Generando código QR con estándar EMVCo...
        </div>
      </div>

      <div v-else-if="qrData" class="text-center">
        <!-- BANCO Y MONTO -->
        <div class="mb-2">
          <v-chip color="primary lighten-5" text-color="primary" class="font-weight-black text-h6 px-4 py-2" pill>
            Bs {{ parseFloat(qrData.monto).toFixed(2) }}
          </v-chip>
        </div>

        <div class="text-caption text-secondary font-weight-medium mb-2">
          {{ qrData.glosa }} &bull; <strong>{{ qrData.banco_destino || 'BANCO UNIÓN S.A.' }}</strong>
        </div>

        <!-- CONTENEDOR DEL CÓDIGO QR -->
        <div class="qr-code-wrapper mx-auto pa-3 rounded-xl elevation-3 my-2" :class="{ 'qr-success-border': estadoPago === 'COMPLETED' }">
          <img
            v-if="qrData.qr_imagen_base64"
            :src="qrData.qr_imagen_base64"
            alt="Código QR de Cobro"
            class="qr-code-image"
          />
          
          <!-- OVERLAY DE ÉXITO -->
          <div v-if="estadoPago === 'COMPLETED'" class="qr-success-overlay d-flex flex-column align-center justify-center">
            <v-icon color="white" size="64">mdi-check-circle</v-icon>
            <span class="text-h6 font-weight-black text-white mt-1">¡PAGO RECIBIDO!</span>
          </div>
        </div>

        <!-- TEMPORIZADOR Y ESTADO -->
        <div class="d-flex align-center justify-center mt-3">
          <v-chip
            v-if="estadoPago === 'PENDING'"
            small
            color="amber darken-3"
            outlined
            class="font-weight-bold mr-2"
          >
            <v-icon left x-small>mdi-clock-outline</v-icon>
            Expira en: {{ tiempoRestanteFormateado }}
          </v-chip>

          <v-chip
            v-if="estadoPago === 'PENDING'"
            small
            color="primary"
            class="font-weight-bold pulse-badge"
          >
            <v-progress-circular indeterminate size="12" width="2" color="white" class="mr-1"></v-progress-circular>
            Esperando pago del cliente...
          </v-chip>

          <v-chip
            v-else-if="estadoPago === 'COMPLETED'"
            small
            color="success"
            class="font-weight-black text-white"
          >
            <v-icon left small color="white">mdi-check</v-icon>
            Pago Aprobado por el Banco
          </v-chip>

          <v-chip
            v-else-if="estadoPago === 'EXPIRED'"
            small
            color="error"
            class="font-weight-bold text-white"
          >
            <v-icon left small color="white">mdi-alert-circle</v-icon>
            Código QR Expirado
          </v-chip>
        </div>

        <p class="text-caption text-secondary mt-3 mb-1 px-4">
          El cliente puede escanear este QR desde cualquier aplicación bancaria boliviana (Banco Unión, BCP, BNB, Banco Sol, Bisa, etc.).
        </p>
      </div>

      <v-divider class="my-3"></v-divider>

      <!-- BOTONES DE ACCIÓN -->
      <div class="d-flex justify-space-between align-center">
        <v-btn text color="secondary" @click="cerrar">
          Cancelar
        </v-btn>

        <!-- BOTÓN DE CONFIRMACIÓN INMEDIATA POR VENTANILLA / CAJERO -->
        <v-btn
          v-if="estadoPago === 'PENDING'"
          color="success"
          class="font-weight-bold rounded-pill"
          :loading="confirmando"
          @click="confirmarPagoManual"
        >
          <v-icon left small>mdi-check-decagram</v-icon>
          Validar Pago en Ventanilla
        </v-btn>

        <v-btn
          v-else-if="estadoPago === 'COMPLETED'"
          color="primary"
          class="font-weight-bold rounded-pill text-white"
          @click="finalizar"
        >
          Continuar y Emitir Factura
        </v-btn>

        <v-btn
          v-else-if="estadoPago === 'EXPIRED'"
          color="warning"
          class="font-weight-bold rounded-pill"
          @click="regenerarQr"
        >
          <v-icon left small>mdi-refresh</v-icon>
          Reintentar
        </v-btn>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ModalCobroQrSimple',
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    monto: {
      type: Number,
      required: true,
    },
    glosa: {
      type: String,
      default: 'Cobro de Agua Potable',
    },
    abonadoId: {
      type: Number,
      default: null,
    },
    sesionCajaId: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      cargando: false,
      confirmando: false,
      errorMensaje: '',
      qrData: null,
      estadoPago: 'PENDING',
      segundosRestantes: 600, // 10 minutos
      timerInterval: null,
      pollingInterval: null,
    };
  },
  computed: {
    dialogoVisible: {
      get() {
        return this.value;
      },
      set(val) {
        this.$emit('input', val);
      },
    },
    tiempoRestanteFormateado() {
      const min = Math.floor(this.segundosRestantes / 60);
      const sec = this.segundosRestantes % 60;
      return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
    },
  },
  watch: {
    value(val) {
      if (val) {
        this.iniciarCobroQr();
      } else {
        this.limpiarIntervalos();
      }
    },
  },
  beforeDestroy() {
    this.limpiarIntervalos();
  },
  methods: {
    async iniciarCobroQr() {
      this.cargando = true;
      this.errorMensaje = '';
      this.qrData = null;
      this.estadoPago = 'PENDING';
      this.segundosRestantes = 600;

      try {
        const payload = {
          monto: this.monto,
          glosa: this.glosa,
          id_abonado: this.abonadoId,
          id_caja_sesion: this.sesionCajaId,
          minutos_vigencia: 10,
        };

        const res = await window.axios.post('/api/facturacion/cobros-qr/generar', payload);
        if (res.data.success) {
          this.qrData = res.data.data;
          this.estadoPago = 'PENDING';
          this.iniciarTemporizador();
          this.iniciarPolling();
        } else {
          this.errorMensaje = res.data.message || 'Error al generar el cobro QR';
        }
      } catch (e) {
        this.errorMensaje = e.response?.data?.message || 'No se pudo conectar con el servicio de Cobros QR.';
      } finally {
        this.cargando = false;
      }
    },

    iniciarTemporizador() {
      if (this.timerInterval) clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        if (this.segundosRestantes > 0) {
          this.segundosRestantes--;
        } else {
          this.estadoPago = 'EXPIRED';
          this.limpiarIntervalos();
        }
      }, 1000);
    },

    iniciarPolling() {
      if (this.pollingInterval) clearInterval(this.pollingInterval);
      this.pollingInterval = setInterval(async () => {
        if (!this.qrData?.uuid || this.estadoPago !== 'PENDING') return;

        try {
          const res = await window.axios.get(`/api/facturacion/cobros-qr/${this.qrData.uuid}/estado`);
          if (res.data.success && res.data.estado === 'COMPLETED') {
            this.estadoPago = 'COMPLETED';
            this.limpiarIntervalos();
            setTimeout(() => {
              this.finalizar();
            }, 1200);
          } else if (res.data.estado === 'EXPIRED') {
            this.estadoPago = 'EXPIRED';
            this.limpiarIntervalos();
          }
        } catch (e) {
          // Polling suave sin interrumpir al usuario
        }
      }, 2000);
    },

    async confirmarPagoManual() {
      if (!this.qrData?.uuid) return;
      this.confirmando = true;
      this.errorMensaje = '';
      try {
        const res = await window.axios.post(`/api/facturacion/cobros-qr/${this.qrData.uuid}/confirmar`, {
          transaccion_banco_id: 'VENTANILLA-' + Date.now(),
          banco_origen: 'Banca Móvil / Confirmación Ventanilla',
        });

        if (res.data.success) {
          this.estadoPago = 'COMPLETED';
          this.limpiarIntervalos();
          setTimeout(() => {
            this.finalizar();
          }, 800);
        }
      } catch (e) {
        this.errorMensaje = e.response?.data?.message || 'Error al confirmar el pago en ventanilla.';
      } finally {
        this.confirmando = false;
      }
    },

    regenerarQr() {
      this.iniciarCobroQr();
    },

    finalizar() {
      this.$emit('pago-completado', {
        uuid: this.qrData?.uuid,
        monto: this.monto,
      });
      this.cerrar();
    },

    cerrar() {
      this.limpiarIntervalos();
      this.dialogoVisible = false;
    },

    limpiarIntervalos() {
      if (this.timerInterval) clearInterval(this.timerInterval);
      if (this.pollingInterval) clearInterval(this.pollingInterval);
      this.timerInterval = null;
      this.pollingInterval = null;
    },
  },
};
</script>

<style scoped>
.erp-qr-modal {
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
}
.theme--dark .erp-qr-modal {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  border-color: rgba(255, 255, 255, 0.1);
}
.qr-code-wrapper {
  position: relative;
  width: 250px;
  height: 250px;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}
.qr-code-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.qr-success-border {
  border-color: #10b981 !important;
}
.qr-success-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(16, 185, 129, 0.92);
  border-radius: 12px;
  animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
.pulse-badge {
  animation: pulseAnimation 2s infinite;
}
@keyframes pulseAnimation {
  0% { transform: scale(1); }
  50% { transform: scale(1.03); }
  100% { transform: scale(1); }
}
</style>
