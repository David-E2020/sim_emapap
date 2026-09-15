<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-receipt-text-plus</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Emitir Factura Electrónica</h2>
            <span class="text-caption text-secondary">Modalidad en Línea - Sistema Integrado de Facturación SIAT / EMAPAP Patacamaya</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-chip :color="conexionSiat ? 'success' : 'warning'" text-color="white" small class="mr-2">
            <v-icon left small>{{ conexionSiat ? 'mdi-wifi-check' : 'mdi-wifi-alert' }}</v-icon>
            {{ conexionSiat ? 'SIAT EN LÍNEA' : 'SIAT DESCONECTADO' }}
          </v-chip>
          <v-btn color="secondary" outlined small class="rounded-pill" @click="probarConexion">
            <v-icon left small>mdi-refresh</v-icon> Probar Conexión
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BANNER DE ENLACE RÁPIDO A CAJA DE ABONADOS / FACTIFIV -->
    <v-alert
      type="info"
      dense
      text
      class="mb-4 d-flex align-center"
      icon="mdi-information"
    >
      <div class="d-flex align-center justify-space-between w-100 flex-wrap">
        <div>
          <strong>¿Cobro en ventanilla a abonados por consumo de agua?</strong>
          Para liquidar lecturas mensuales y cuotas de convenio con cobro secuencial tipo FACTIFIV, utilice la caja de ventanilla.
        </div>
        <v-btn
          color="primary"
          small
          class="rounded-pill font-weight-bold ml-2 mt-1 mt-sm-0 text-white elevation-1"
          to="/facturacion/caja"
        >
          <v-icon left small>mdi-cash-register</v-icon> Ir a Caja y Cobranzas Ventanilla
        </v-btn>
      </div>
    </v-alert>

    <v-row>
      <!-- FORMULARIO CABECERA DE FACTURA -->
      <v-col cols="12" lg="8">
        <!-- DATOS DEL CLIENTE -->
        <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
          <div class="d-flex align-center mb-3">
            <v-icon color="primary" class="mr-2">mdi-account-card-outline</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Datos del Cliente y Facturación</h3>
          </div>

          <v-row dense>
            <v-col cols="12" sm="4">
              <v-select
                v-model="form.codigo_tipo_documento_identidad"
                :items="tiposDocumento"
                item-text="descripcion"
                item-value="codigo"
                label="Tipo de Documento *"
                dense
                outlined
              ></v-select>
            </v-col>

            <v-col cols="12" sm="5">
              <v-text-field
                v-model="form.numero_documento"
                label="Número de Documento / NIT *"
                dense
                outlined
                clearable
                append-icon="mdi-account-search"
                @click:append="verificarNitCliente"
                @keyup.enter="verificarNitCliente"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="3">
              <v-text-field
                v-model="form.complemento"
                label="Complemento"
                placeholder="Ej. 1A"
                dense
                outlined
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.nombre_razon_social"
                label="Nombre o Razón Social *"
                dense
                outlined
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.correo_electronico"
                label="Correo Electrónico (Para envío de factura)"
                type="email"
                dense
                outlined
                append-icon="mdi-email-outline"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="form.codigo_metodo_pago"
                :items="metodosPago"
                item-text="descripcion"
                item-value="codigo"
                label="Método de Pago *"
                dense
                outlined
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6" v-if="form.codigo_metodo_pago === 2">
              <v-text-field
                v-model="form.numero_tarjeta"
                label="Últimos 4 dígitos de tarjeta"
                placeholder="0000"
                maxlength="4"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card>

        <!-- SELECCIÓN Y AGREGADO DE PRODUCTOS -->
        <v-card rounded="lg" class="pa-4 erp-card-elevated">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-cart-plus</v-icon>
              <h3 class="text-subtitle-1 font-weight-bold mb-0">Detalle de Servicios de Agua y Alcantarillado</h3>
            </div>
            <v-btn color="primary" text small @click="agregarItemVacio">
              <v-icon left small>mdi-plus</v-icon> Agregar Fila
            </v-btn>
          </div>

          <!-- Selector rápido de catálogo EMAPAP -->
          <v-autocomplete
            v-model="productoSeleccionado"
            :items="listaProductos"
            item-text="descripcion"
            return-object
            label="Buscar servicio en catálogo EMAPAP (Agua Domiciliaria, Comercial, Alcantarillado, Medidor...)"
            placeholder="Escriba para buscar servicio..."
            prepend-inner-icon="mdi-magnify"
            outlined
            dense
            clearable
            @change="seleccionarProductoRapido"
          >
            <template v-slot:item="{ item }">
              <v-list-item-content>
                <v-list-item-title class="font-weight-medium">{{ item.descripcion }}</v-list-item-title>
                <v-list-item-subtitle class="text-caption">
                  Cód: {{ item.codigo_producto_empresa }} | Precio: Bs {{ parseFloat(item.precio_unitario).toFixed(2) }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </template>
          </v-autocomplete>

          <!-- TABLA DE ITEMS -->
          <v-simple-table dense class="mb-3">
            <template v-slot:default>
              <thead>
                <tr>
                  <th style="width: 15%;">Código</th>
                  <th style="width: 35%;">Descripción</th>
                  <th style="width: 12%;" class="text-center">Cant.</th>
                  <th style="width: 15%;" class="text-right">Precio U. (Bs)</th>
                  <th style="width: 15%;" class="text-right">Subtotal (Bs)</th>
                  <th style="width: 8%;" class="text-center">Quitar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in form.items" :key="idx">
                  <td>
                    <v-text-field v-model="item.codigo_producto_empresa" dense hide-details class="pt-0 mt-0"></v-text-field>
                  </td>
                  <td>
                    <v-text-field v-model="item.descripcion" dense hide-details class="pt-0 mt-0"></v-text-field>
                  </td>
                  <td>
                    <v-text-field
                      v-model.number="item.cantidad"
                      type="number"
                      min="1"
                      dense
                      hide-details
                      class="pt-0 mt-0 text-center"
                      @input="calcularTotales"
                    ></v-text-field>
                  </td>
                  <td>
                    <v-text-field
                      v-model.number="item.precio_unitario"
                      type="number"
                      step="0.1"
                      min="0"
                      dense
                      hide-details
                      class="pt-0 mt-0 text-right"
                      @input="calcularTotales"
                    ></v-text-field>
                  </td>
                  <td class="text-right font-weight-bold">
                    Bs {{ (item.cantidad * item.precio_unitario).toFixed(2) }}
                  </td>
                  <td class="text-center">
                    <v-btn icon small color="error" @click="quitarItem(idx)">
                      <v-icon small>mdi-delete-outline</v-icon>
                    </v-btn>
                  </td>
                </tr>
                <tr v-if="form.items.length === 0">
                  <td colspan="6" class="text-center py-4 text-secondary">
                    No hay productos añadidos a la factura. Seleccione uno arriba o agregue una fila.
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </v-col>

      <!-- RESUMEN Y BOTÓN DE EMISIÓN -->
      <v-col cols="12" lg="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated mb-5">
          <div class="d-flex align-center mb-3">
            <v-icon color="primary" class="mr-2">mdi-calculator-variant</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Resumen Fiscal</h3>
          </div>

          <v-divider class="mb-3"></v-divider>

          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2">Subtotal:</span>
            <span class="font-weight-medium">Bs {{ subtotalCalculado.toFixed(2) }}</span>
          </div>

          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-body-2">Descuento adicional:</span>
            <v-text-field
              v-model.number="form.monto_descuento"
              type="number"
              min="0"
              dense
              outlined
              hide-details
              style="max-width: 110px;"
              class="text-right"
              @input="calcularTotales"
            ></v-text-field>
          </div>

          <v-divider class="my-3"></v-divider>

          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-h6 font-weight-bold">TOTAL A PAGAR:</span>
            <span class="text-h5 font-weight-bold text-success">Bs {{ totalPagar.toFixed(2) }}</span>
          </div>

          <div class="d-flex justify-space-between text-caption text-secondary mb-4">
            <span>Importe Base Crédito Fiscal (13%):</span>
            <span>Bs {{ totalPagar.toFixed(2) }}</span>
          </div>

          <v-btn
            color="success"
            block
            x-large
            class="font-weight-bold elevation-3 text-capitalize rounded-lg mb-2"
            :loading="guardandoFactura"
            :disabled="form.items.length === 0 || !form.numero_documento || !form.nombre_razon_social"
            @click="emitirFactura"
          >
            <v-icon left>mdi-check-decagram</v-icon>
            Emitir Factura SIAT
          </v-btn>

          <div class="text-center text-caption text-secondary mt-2">
            <v-icon small color="secondary">mdi-shield-check</v-icon>
            Firma XMLDSig y validación automática ante el SIN.
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO DE ÉXITO POST-EMISIÓN -->
    <v-dialog v-model="dialogoExito" max-width="500" persistent>
      <v-card rounded="lg" class="pa-4 text-center">
        <v-avatar color="success" size="64" class="mb-3 text-white">
          <v-icon size="36" color="white">mdi-check-circle-outline</v-icon>
        </v-avatar>

        <h3 class="text-h5 font-weight-bold mb-1">¡Factura Emitida con Éxito!</h3>
        <p class="text-body-2 text-secondary mb-3">La factura ha sido registrada y validada ante el SIN.</p>

        <v-card outlined class="pa-3 mb-4 text-left receipt-summary-card">
          <div class="mb-1"><strong>N° Factura:</strong> {{ facturaEmitida.numero_factura }}</div>
          <div class="mb-1"><strong>Cliente:</strong> {{ facturaEmitida.nombre_razon_social }}</div>
          <div class="mb-1"><strong>NIT/CI:</strong> {{ facturaEmitida.numero_documento }}</div>
          <div class="mb-1"><strong>Monto Total:</strong> Bs {{ parseFloat(facturaEmitida.monto_total || 0).toFixed(2) }}</div>
          <div class="text-caption text-truncate" :title="facturaEmitida.cuf">
            <strong>CUF:</strong> {{ facturaEmitida.cuf }}
          </div>
        </v-card>

        <div class="d-flex flex-column gap-2">
          <v-btn color="primary" block class="mb-2" @click="imprimirPdf(facturaEmitida.id)">
            <v-icon left>mdi-printer</v-icon> Imprimir / Ver PDF
          </v-btn>
          <v-btn color="secondary" outlined block class="mb-2" @click="descargarXml(facturaEmitida.id)">
            <v-icon left>mdi-xml</v-icon> Descargar XML Firmado
          </v-btn>
          <v-btn text block @click="reiniciarFormulario">
            Emitir Nueva Factura
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE FACTURA SIAT (CARTA / ROLLO 80MM) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :mostrar-selector-formato="true"
      @cambio-formato="alCambiarFormatoPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'CrearFactura',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      facturaVisorId: null,
      conexionSiat: true,
      guardandoFactura: false,
      dialogoExito: false,
      facturaEmitida: {},
      tiposDocumento: [],
      metodosPago: [],
      listaProductos: [],
      productoSeleccionado: null,
      form: {
        id_sucursal: 0,
        id_punto_venta: 0,
        codigo_tipo_documento_identidad: 1,
        numero_documento: '',
        complemento: '',
        nombre_razon_social: '',
        correo_electronico: '',
        codigo_metodo_pago: 1,
        numero_tarjeta: '',
        monto_descuento: 0,
        items: [],
      },
    };
  },
  computed: {
    subtotalCalculado() {
      return this.form.items.reduce((acc, item) => {
        return acc + (Number(item.cantidad || 0) * Number(item.precio_unitario || 0));
      }, 0);
    },
    totalPagar() {
      const tot = this.subtotalCalculado - Number(this.form.monto_descuento || 0);
      return tot > 0 ? tot : 0;
    },
  },
  mounted() {
    this.cargarCatalogos();
    this.cargarProductos();
    this.probarConexion();
  },
  methods: {
    async probarConexion() {
      try {
        const res = await window.axios.get('/api/facturacion/siat/estado-conexion');
        this.conexionSiat = res.data.success;
      } catch (e) {
        this.conexionSiat = false;
      }
    },
    async cargarCatalogos() {
      try {
        const res = await window.axios.get('/api/facturacion/siat/catalogos');
        this.tiposDocumento = res.data.data.tipos_documento;
        this.metodosPago = res.data.data.metodos_pago;
      } catch (e) {
        console.error('Error al cargar catálogos', e);
      }
    },
    async cargarProductos() {
      try {
        const res = await window.axios.get('/api/facturacion/siat/productos');
        this.listaProductos = res.data.data;
        if (this.form.items.length === 0 && this.listaProductos.length > 0) {
          // Agregar primer producto como ejemplo amigable
          this.seleccionarProductoRapido(this.listaProductos[0]);
        }
      } catch (e) {
        console.error('Error al cargar productos', e);
      }
    },
    seleccionarProductoRapido(prod) {
      if (!prod) return;
      this.form.items.push({
        codigo_producto_empresa: prod.codigo_producto_empresa,
        codigo_actividad: prod.codigo_actividad || '472110',
        codigo_producto_sin: prod.codigo_producto_sin || '841001',
        descripcion: prod.descripcion,
        cantidad: 1,
        precio_unitario: Number(prod.precio_unitario),
        monto_descuento: 0,
      });
      this.productoSeleccionado = null;
    },
    agregarItemVacio() {
      this.form.items.push({
        codigo_producto_empresa: 'PROD-' + (this.form.items.length + 1),
        codigo_actividad: '472110',
        codigo_producto_sin: '841001',
        descripcion: '',
        cantidad: 1,
        precio_unitario: 0,
        monto_descuento: 0,
      });
    },
    quitarItem(idx) {
      this.form.items.splice(idx, 1);
    },
    calcularTotales() {
      // Reactividad forzada
      this.$forceUpdate();
    },
    async verificarNitCliente() {
      if (!this.form.numero_documento) return;
      if (this.form.codigo_tipo_documento_identidad !== 5) return; // Solo NIT

      try {
        const res = await window.axios.get(`/api/facturacion/siat/verificar-nit/${this.form.numero_documento}`);
        if (res.data.data.valido) {
          this.$message.success('NIT válido en el padrón nacional del SIN.');
        } else {
          this.$message.warning('El NIT consultado no se encuentra activo o es inválido ante el SIN.');
        }
      } catch (e) {
        // En offline continúa
      }
    },
    async emitirFactura() {
      this.guardandoFactura = true;
      try {
        const res = await window.axios.post('/api/facturacion/facturas', this.form);
        if (res.data.success) {
          this.facturaEmitida = res.data.data;
          this.dialogoExito = true;
          this.$message.success('Factura emitida exitosamente.');
        } else {
          this.$message.error(res.data.message || 'Error al emitir factura');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.$message.error(msg);
      } finally {
        this.guardandoFactura = false;
      }
    },
    imprimirPdf(id) {
      this.facturaVisorId = id;
      this.urlVisorPdf = `/api/facturacion/facturas/${id}/pdf?formato=carta`;
      this.tituloVisorPdf = `Factura Electrónica SIAT N° ${this.facturaEmitida.numero_factura || id}`;
      this.subtituloVisorPdf = `${this.facturaEmitida.nombre_razon_social || ''} | NIT/CI: ${this.facturaEmitida.numero_documento || ''}`;
      this.mostrarVisorPdf = true;
    },
    alCambiarFormatoPdf(nuevoFormato) {
      if (this.facturaVisorId) {
        this.urlVisorPdf = `/api/facturacion/facturas/${this.facturaVisorId}/pdf?formato=${nuevoFormato}`;
      }
    },
    descargarXml(id) {
      const link = document.createElement('a');
      link.href = `/api/facturacion/facturas/${id}/xml`;
      link.download = `factura_${id}.xml`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    reiniciarFormulario() {
      this.dialogoExito = false;
      this.form.numero_documento = '';
      this.form.complemento = '';
      this.form.nombre_razon_social = '';
      this.form.correo_electronico = '';
      this.form.monto_descuento = 0;
      this.form.items = [];
      if (this.listaProductos.length > 0) {
        this.seleccionarProductoRapido(this.listaProductos[0]);
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
.receipt-summary-card {
  background-color: rgba(0, 0, 0, 0.03) !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
.theme--dark .receipt-summary-card {
  background-color: rgba(255, 255, 255, 0.04) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.gap-2 {
  gap: 8px;
}
</style>
