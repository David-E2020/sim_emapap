<template>
  <v-dialog :value="value" max-width="560" persistent @input="$emit('input', $event)">
    <v-card rounded="lg">
      <v-card-title class="primary white--text py-3">
        <v-icon color="white" class="mr-2">mdi-cash-register</v-icon>
        <span class="text-h6 font-weight-bold">Apertura de Turno de Caja</span>
      </v-card-title>

      <v-card-text class="pt-4">
        <v-alert v-if="errorMensaje" type="error" dense dismissible class="mb-3">
          {{ errorMensaje }}
        </v-alert>

        <v-alert dense text color="primary" class="mb-4 text-caption">
          <v-icon small color="primary" class="mr-1">mdi-shield-check</v-icon>
          Al abrir la caja, se verificará la vigencia de su <strong>CUFD diario ante el SIAT</strong> y quedará vinculada a su usuario para toda la recaudación de la jornada.
        </v-alert>

        <!-- Selector de Caja / Punto de Venta -->
        <div class="mb-3">
          <label class="text-subtitle-2 font-weight-bold text-secondary mb-1 d-block">
            1. Seleccione la Caja / Ventanilla Física *
          </label>

          <v-select
            v-model="formulario.id_punto_venta"
            :items="cajas"
            item-value="id"
            item-text="nombre"
            outlined
            dense
            :loading="cargandoCajas"
            placeholder="Seleccione la ventanilla que operará hoy..."
            prepend-inner-icon="mdi-store-clock"
            hide-details="auto"
          >
            <template v-slot:item="{ item }">
              <div class="d-flex justify-space-between align-center w-100 py-1">
                <div>
                  <div class="font-weight-bold text-body-2">{{ item.nombre }}</div>
                  <span class="text-caption text-secondary">
                    Punto de Venta {{ item.codigo_punto_venta }} (SIAT)
                    <span v-if="item.cajero_defecto" class="ml-1 primary--text">
                      | Habitual: {{ item.cajero_defecto.nombre }}
                    </span>
                  </span>
                </div>
                <div>
                  <v-chip v-if="item.esta_abierta" x-small color="error" text-color="white" class="font-weight-bold">
                    Ocupada ({{ item.sesion_activa ? item.sesion_activa.cajero_nombre : '' }})
                  </v-chip>
                  <v-chip v-else x-small color="success" text-color="white" class="font-weight-bold">
                    Disponible
                  </v-chip>
                </div>
              </div>
            </template>
          </v-select>
        </div>

        <!-- Monto de Apertura / Fondo de Gaveta -->
        <div class="mb-3">
          <div class="d-flex justify-space-between align-center mb-1">
            <label class="text-subtitle-2 font-weight-bold text-secondary">
              2. Fondo Inicial de Gaveta (Sencillo de Cambio) *
            </label>
            <span class="text-caption text-secondary">Bolivianos (Bs)</span>
          </div>

          <v-text-field
            v-model.number="formulario.monto_apertura"
            type="number"
            outlined
            dense
            prefix="Bs"
            placeholder="0.00"
            prepend-inner-icon="mdi-cash"
            hide-details="auto"
            min="0"
            step="10"
          ></v-text-field>

          <!-- Atajos rápidos para montos de sencillo -->
          <div class="d-flex flex-wrap mt-2">
            <v-chip small outlined color="primary" class="mr-2 mb-1" @click="formulario.monto_apertura = 0">Bs. 0 (Sin fondo)</v-chip>
            <v-chip small outlined color="primary" class="mr-2 mb-1" @click="formulario.monto_apertura = 100">Bs. 100</v-chip>
            <v-chip small outlined color="primary" class="mr-2 mb-1" @click="formulario.monto_apertura = 150">Bs. 150</v-chip>
            <v-chip small outlined color="primary" class="mr-2 mb-1" @click="formulario.monto_apertura = 200">Bs. 200</v-chip>
            <v-chip small outlined color="primary" class="mr-2 mb-1" @click="formulario.monto_apertura = 300">Bs. 300</v-chip>
          </div>
        </div>

        <!-- Observaciones -->
        <div class="mb-1">
          <label class="text-subtitle-2 font-weight-bold text-secondary mb-1 d-block">
            3. Observaciones de Apertura (Opcional)
          </label>
          <v-textarea
            v-model="formulario.observaciones_apertura"
            rows="2"
            dense
            outlined
            placeholder="Ej: Turno de la mañana, asignación de gaveta N° 2..."
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
          :disabled="!formulario.id_punto_venta"
          @click="abrirCaja"
        >
          <v-icon left small>mdi-lock-open-check</v-icon>
          Iniciar Turno y Abrir Caja
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModalAperturaCaja',
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    cajaDefectoId: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      cargandoCajas: false,
      guardando: false,
      errorMensaje: '',
      cajas: [],
      formulario: {
        id_punto_venta: null,
        monto_apertura: 100,
        observaciones_apertura: '',
      },
    };
  },
  watch: {
    value(val) {
      if (val) {
        this.cargarCajasDisponibles();
      }
    },
  },
  mounted() {
    if (this.value) {
      this.cargarCajasDisponibles();
    }
  },
  methods: {
    async cargarCajasDisponibles() {
      this.cargandoCajas = true;
      try {
        const res = await axios.get('/api/comercial/caja-sesiones/cajas-disponibles');
        this.cajas = res.data?.data || [];

        // Preseleccionar caja: primero la asignada por defecto si está libre, o la primera disponible
        if (this.cajaDefectoId) {
          const defaultCaja = this.cajas.find(c => c.id === this.cajaDefectoId);
          if (defaultCaja && !defaultCaja.esta_abierta) {
            this.formulario.id_punto_venta = defaultCaja.id;
            return;
          }
        }

        const libre = this.cajas.find(c => !c.esta_abierta);
        if (libre) {
          this.formulario.id_punto_venta = libre.id;
        }
      } catch (e) {
        console.error('Error al cargar cajas disponibles:', e);
      } finally {
        this.cargandoCajas = false;
      }
    },
    async abrirCaja() {
      if (!this.formulario.id_punto_venta) {
        this.errorMensaje = 'Seleccione una caja física / ventanilla.';
        return;
      }

      this.guardando = true;
      this.errorMensaje = '';
      try {
        const res = await axios.post('/api/comercial/caja-sesiones/abrir', this.formulario);
        this.$emit('sesion-abierta', res.data.data);
      } catch (e) {
        this.errorMensaje = e.response?.data?.message || 'Error al realizar la apertura de caja.';
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>
