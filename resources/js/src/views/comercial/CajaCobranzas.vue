<template>
  <div class="caja-cobranzas-container">
    <!-- 1. CABECERA DE VENTANILLA ESTÁNDAR DEL SISTEMA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-cash-register</v-icon>
          </v-avatar>
          <div>
            <div class="d-flex align-center flex-wrap">
              <h2 class="text-h5 font-weight-bold mb-0 mr-2 text--primary">Caja y Facturación en Ventanilla</h2>
              <v-chip
                x-small
                :color="tieneSesionActiva ? 'success' : 'secondary'"
                outlined
                class="font-weight-bold my-1"
              >
                <v-icon left x-small :color="tieneSesionActiva ? 'success' : 'secondary'">
                  {{ tieneSesionActiva ? 'mdi-circle' : 'mdi-circle-outline' }}
                </v-icon>
                {{ tieneSesionActiva ? 'VENTANILLA ACTIVA' : 'VENTANILLA EN ESPERA' }}
              </v-chip>
            </div>
            <span class="text-caption text-secondary">
              Cobranza ágil de consumo de agua, convenios y emisión en línea de Factura SIAT (Sector 13 - Servicios Básicos)
            </span>
          </div>
        </div>

        <!-- Acciones rápidas de cabecera -->
        <div class="d-flex align-center flex-wrap my-1">
          <template v-if="tieneSesionActiva">
            <v-btn
              small
              outlined
              color="primary"
              class="rounded-pill font-weight-medium mr-2 my-1"
              @click="mostrarModalMovimiento = true"
            >
              <v-icon left x-small>mdi-cash-fast</v-icon> Movimiento Gaveta
            </v-btn>

            <v-btn
              small
              color="warning darken-1"
              class="rounded-pill font-weight-medium text-white elevation-1 mr-2 my-1"
              @click="mostrarModalCierre = true"
            >
              <v-icon left x-small>mdi-lock-check</v-icon> Cerrar Turno / Arqueo
            </v-btn>
          </template>

          <template v-else>
            <v-btn
              color="primary"
              class="rounded-pill font-weight-bold text-white elevation-1 px-4 my-1"
              @click="mostrarModalApertura = true"
            >
              <v-icon left small>mdi-lock-open-variant</v-icon> Abrir Turno de Caja
            </v-btn>
          </template>
        </div>
      </div>
    </v-card>

    <!-- 2. BARRA DE ESTADO DE SESIÓN / TURNO ACTIVO (Solo visible cuando hay turno abierto) -->
    <v-card
      v-if="tieneSesionActiva && sesionActiva"
      class="mb-5 py-3 px-4 rounded-lg erp-card-elevated session-active-card"
    >
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-badge
            dot
            bordered
            color="success"
            offset-x="8"
            offset-y="8"
          >
            <v-avatar color="primary" size="40" class="mr-3 text-white elevation-1">
              <v-icon color="white" small>mdi-cash-check</v-icon>
            </v-avatar>
          </v-badge>

          <div>
            <div class="d-flex align-center flex-wrap">
              <span class="text-subtitle-1 font-weight-bold mr-2 text--primary">
                {{ sesionActiva.punto_venta ? sesionActiva.punto_venta.nombre : 'Caja Central' }}
              </span>
              <v-chip x-small color="primary" class="font-weight-bold mr-2 my-1" text-color="white">
                PUNTO {{ sesionActiva.punto_venta ? sesionActiva.punto_venta.codigo_punto_venta : 0 }} (SIAT)
              </v-chip>
              <v-chip x-small color="primary" outlined class="font-weight-bold my-1 mr-2">
                TURNO #{{ sesionActiva.numero_sesion }}
              </v-chip>
            </div>
            <div class="text-caption text-secondary mt-0">
              <strong>Cajero:</strong> {{ sesionActiva.cajero ? sesionActiva.cajero.name : 'Usuario' }} |
              <strong>Apertura:</strong> {{ formatearHora(sesionActiva.fecha_apertura) }} |
              <strong>Fondo Inicial:</strong> Bs {{ (sesionActiva.monto_apertura || 0).toFixed(2) }} |
              <strong>Total Cobrado:</strong> <span class="font-weight-bold success--text">Bs {{ ((sesionActiva.monto_ventas_efectivo || 0) + (sesionActiva.monto_ventas_qr_banco || 0)).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </v-card>

    <!-- BUSCADOR SUPERIOR DE ABONADOS -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- Selector Tipo de Búsqueda -->
        <v-col cols="12" sm="5" md="3">
          <v-select
            v-model="tipoBusqueda"
            :items="tiposBusqueda"
            item-text="texto"
            item-value="valor"
            label="Buscar por..."
            prepend-inner-icon="mdi-format-list-bulleted-type"
            dense
            outlined
            hide-details
            @change="alCambiarTipoBusqueda"
          ></v-select>
        </v-col>

        <!-- Campo de texto de búsqueda -->
        <v-col cols="12" sm="7" :md="estadoCuenta ? 5 : 6">
          <v-text-field
            v-model="codigoBusqueda"
            :label="etiquetaBusqueda"
            :placeholder="placeholderBusqueda"
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            autofocus
            @keyup.enter="buscarAbonado"
            @click:clear="limpiarBusqueda"
          ></v-text-field>
        </v-col>

        <!-- Botón Consultar -->
        <v-col cols="12" sm="6" :md="estadoCuenta ? 2 : 3">
          <v-btn
            color="primary"
            class="rounded-pill elevation-1"
            block
            height="40"
            :loading="buscando"
            @click="buscarAbonado"
          >
            <v-icon left small>mdi-account-search</v-icon> Consultar
          </v-btn>
        </v-col>

        <!-- Botón Limpiar si hay cuenta cargada -->
        <v-col cols="12" sm="6" md="2" class="text-right" v-if="estadoCuenta">
          <v-btn text color="secondary" small @click="limpiarBusqueda">
            <v-icon left x-small>mdi-close</v-icon> Limpiar
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- ESTADO DE CUENTA Y VENTANILLA DE COBRO -->
    <div v-if="estadoCuenta">
      <v-row dense>
        <!-- COLUMNA IZQUIERDA: DATOS Y FACTURAS PENDIENTES -->
        <v-col cols="12" md="8">
          <!-- Tarjeta con resumen del abonado -->
          <v-card rounded="lg" class="mb-4 pa-4 erp-card-elevated">
            <div class="d-flex justify-space-between align-center flex-wrap">
              <div>
                <div class="d-flex align-center flex-wrap">
                  <v-chip color="primary" small class="font-weight-bold mr-2 mb-1">
                    CÓDIGO: {{ estadoCuenta.abonado.codigo }}
                  </v-chip>
                  <h3 class="text-h6 font-weight-bold mb-1 mr-2">{{ estadoCuenta.abonado.nombre_completo }}</h3>
                </div>
                <div class="text-caption text-secondary mt-1">
                  <strong>NIT/CI:</strong> {{ estadoCuenta.abonado.numero_documento || 'Sin doc' }} |
                  <strong>Categoría:</strong> {{ estadoCuenta.abonado.categoria ? estadoCuenta.abonado.categoria.nombre : 'S/C' }} |
                  <strong>Zona:</strong> {{ estadoCuenta.abonado.zona ? estadoCuenta.abonado.zona.nombre : 'S/Z' }}
                  <span v-if="estadoCuenta.abonado.medidor_actual">| <strong>Medidor:</strong> {{ estadoCuenta.abonado.medidor_actual.numero_serie }}</span>
                  <span v-if="estadoCuenta.abonado.es_tercera_edad" class="ml-1 primary--text font-weight-bold">| (Ley 1886 3ra Edad -20%)</span>
                </div>
              </div>

              <div class="text-right mt-2 mt-sm-0">
                <v-chip :color="colorEstado(estadoCuenta.abonado.estado_servicio)" text-color="white" small class="font-weight-bold">
                  {{ estadoCuenta.abonado.estado_servicio }}
                </v-chip>
                <div class="text-h5 font-weight-black mt-1" :class="estadoCuenta.deuda_total > 0 ? 'error--text' : 'success--text'">
                  Deuda: Bs {{ estadoCuenta.deuda_total.toFixed(2) }}
                </div>
                <div class="text-caption font-weight-bold" :class="estadoCuenta.meses_mora > 0 ? 'error--text' : 'secondary--text'">
                  {{ estadoCuenta.meses_mora }} mes(es) pendiente(s) de pago
                </div>
              </div>
            </div>

            <!-- Alerta si el servicio está cortado -->
            <v-alert
              type="error"
              dense
              outlined
              class="mt-3 mb-0"
              v-if="estadoCuenta.esta_cortado"
            >
              <v-icon small left>mdi-alert-octagon</v-icon>
              <strong>Servicio Cortado:</strong> Al cancelar la totalidad de la deuda acumulada se emitirá la Orden de Reconexión inmediata sin costo adicional.
            </v-alert>
          </v-card>

          <!-- TABLA DE LECTURAS IMPAGAS (SELECCIÓN SECUENCIAL TIPO FACTIFIV) -->
          <v-card rounded="lg" class="mb-4 erp-card-elevated">
            <v-card-title class="py-2 px-4 d-flex justify-space-between align-center text-subtitle-1 font-weight-bold section-header-bg flex-wrap">
              <div class="d-flex align-center my-1">
                <v-icon left color="primary" small>mdi-format-list-numbered</v-icon>
                <span class="text--primary">Meses / Facturas Pendientes ({{ estadoCuenta.lecturas_pendientes.length }})</span>
              </div>

              <!-- BOTONERA RÁPIDA DE SELECCIÓN TIPO FACTIFIV -->
              <div class="d-flex align-center flex-wrap my-1" v-if="estadoCuenta.lecturas_pendientes.length > 0">
                <span class="text-caption font-weight-bold mr-2 text-secondary">Pagar:</span>
                <v-btn
                  x-small
                  color="primary"
                  outlined
                  class="font-weight-bold mr-1 my-1"
                  :disabled="estadoCuenta.lecturas_pendientes.length < 1"
                  @click="seleccionarNMeses(1)"
                >
                  1 Mes
                </v-btn>
                <v-btn
                  x-small
                  color="primary"
                  outlined
                  class="font-weight-bold mr-1 my-1"
                  v-if="estadoCuenta.lecturas_pendientes.length >= 2"
                  @click="seleccionarNMeses(2)"
                >
                  2 Meses
                </v-btn>
                <v-btn
                  x-small
                  color="primary"
                  outlined
                  class="font-weight-bold mr-1 my-1"
                  v-if="estadoCuenta.lecturas_pendientes.length >= 3"
                  @click="seleccionarNMeses(3)"
                >
                  3 Meses
                </v-btn>
                <v-btn
                  x-small
                  color="primary"
                  class="font-weight-bold elevation-1 mr-1 my-1"
                  @click="seleccionarNMeses(estadoCuenta.lecturas_pendientes.length)"
                >
                  Todos ({{ estadoCuenta.lecturas_pendientes.length }})
                </v-btn>
                <v-btn
                  x-small
                  text
                  color="secondary"
                  class="my-1"
                  @click="seleccionarNMeses(0)"
                >
                  Limpiar
                </v-btn>
              </div>
            </v-card-title>

            <!-- AVISO DE ORDEN SECUENCIAL -->
            <div class="px-4 py-2 info-banner-bg text-caption d-flex align-center justify-space-between flex-wrap">
              <span>
                <v-icon x-small color="primary" class="mr-1">mdi-information</v-icon>
                <strong>Cobro Secuencial:</strong> Se cancelan obligatoriamente desde el mes más antiguo adeudado hacia el más reciente.
              </span>
              <span class="font-weight-bold primary--text">
                {{ lecturasSeleccionadas.length }} de {{ estadoCuenta.lecturas_pendientes.length }} mes(es) seleccionado(s)
              </span>
            </div>

            <v-simple-table dense class="tabla-cobranza">
              <template v-slot:default>
                <thead>
                  <tr>
                    <th style="width: 48px;" class="text-center">#</th>
                    <th style="width: 50px;" class="text-center">Cobrar</th>
                    <th>Periodo</th>
                    <th class="text-right">Consumo m³</th>
                    <th class="text-right">Agua Potable</th>
                    <th class="text-right">Alcantarillado</th>
                    <th class="text-right">Descto Ley 1886</th>
                    <th class="text-right">Subtotal Factura</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(lec, idx) in estadoCuenta.lecturas_pendientes"
                    :key="lec.id"
                    :class="{ 'fila-seleccionada': lecturasSeleccionadas.includes(lec.id) }"
                    class="cursor-pointer"
                    @click="toggleLectura(idx)"
                  >
                    <td class="text-center font-weight-bold grey--text text--darken-1">
                      {{ idx + 1 }}
                    </td>
                    <td class="text-center">
                      <v-checkbox
                        :input-value="lecturasSeleccionadas.includes(lec.id)"
                        dense
                        hide-details
                        class="ma-0 pa-0 justify-center"
                        color="primary"
                        readonly
                      ></v-checkbox>
                    </td>
                    <td>
                      <div class="font-weight-bold">
                        {{ lec.periodo ? lec.periodo.periodo : '-' }}
                        <v-chip x-small color="primary" text-color="white" class="ml-1" v-if="idx === 0">
                          Más antiguo
                        </v-chip>
                      </div>
                    </td>
                    <td class="text-right">{{ parseFloat(lec.consumo_m3).toFixed(1) }} m³</td>
                    <td class="text-right">Bs {{ parseFloat(lec.monto_agua).toFixed(2) }}</td>
                    <td class="text-right">Bs {{ parseFloat(lec.monto_alcantarillado).toFixed(2) }}</td>
                    <td class="text-right text-success font-weight-bold" v-if="parseFloat(lec.monto_descuento_ley1886) > 0">
                       -Bs {{ parseFloat(lec.monto_descuento_ley1886).toFixed(2) }}
                    </td>
                    <td class="text-right text-secondary" v-else>Bs 0.00</td>
                    <td class="text-right font-weight-bold" :class="lecturasSeleccionadas.includes(lec.id) ? 'primary--text' : ''">
                      Bs {{ parseFloat(lec.total_facturado).toFixed(2) }}
                    </td>
                  </tr>
                  <tr v-if="estadoCuenta.lecturas_pendientes.length === 0">
                    <td colspan="8" class="text-center text-secondary py-4">
                      <v-icon color="success" class="mr-1">mdi-check-circle</v-icon>
                      El abonado está completamente al día. No registra facturas pendientes de cobro.
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-card>

          <!-- CUOTAS DE CONVENIO SI TIENE PENDIENTES -->
          <v-card rounded="lg" class="erp-card-elevated" v-if="estadoCuenta.cuotas_convenio_pendientes.length > 0">
            <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold section-header-bg d-flex justify-space-between">
              <span class="text--primary">Cuotas de Convenio de Pago</span>
              <v-btn text x-small color="primary" @click="toggleTodasCuotas">
                {{ cuotasSeleccionadas.length === estadoCuenta.cuotas_convenio_pendientes.length ? 'Desmarcar Cuotas' : 'Seleccionar Todas' }}
              </v-btn>
            </v-card-title>
            <v-simple-table dense>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th style="width: 48px;" class="text-center">#</th>
                    <th style="width: 50px;">Cobrar</th>
                    <th>Cuota</th>
                    <th>Periodo</th>
                    <th>Vencimiento</th>
                    <th class="text-right">Monto Cuota</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(c, cidx) in estadoCuenta.cuotas_convenio_pendientes" :key="c.id">
                    <td class="text-center text-caption font-weight-bold">{{ cidx + 1 }}</td>
                    <td>
                      <v-checkbox
                        v-model="cuotasSeleccionadas"
                        :value="c.id"
                        dense
                        hide-details
                        class="ma-0 pa-0"
                        color="primary"
                      ></v-checkbox>
                    </td>
                    <td class="font-weight-bold">Cuota #{{ c.numero_cuota }}</td>
                    <td>{{ c.periodo }}</td>
                    <td>{{ c.fecha_vencimiento }}</td>
                    <td class="text-right font-weight-bold">Bs {{ parseFloat(c.monto_cuota).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-card>
        </v-col>

        <!-- COLUMNA DERECHA: PANEL DE COBRO Y FACTURA ELECTRÓNICA SIAT -->
        <v-col cols="12" md="4">
          <v-card rounded="lg" class="pa-4 erp-card-elevated sticky-panel">
            <v-card-title class="pa-0 mb-2 text-h6 font-weight-bold primary--text d-flex align-center">
              <v-icon left color="primary">mdi-receipt-text-check</v-icon>
              <span>Liquidación y Factura</span>
            </v-card-title>

            <v-divider class="mb-3"></v-divider>

            <!-- Resumen de items seleccionados -->
            <div class="d-flex justify-space-between text-body-2 mb-1">
              <span class="text-secondary">Meses de agua seleccionados:</span>
              <span class="font-weight-bold">{{ lecturasSeleccionadas.length }}</span>
            </div>

            <div class="d-flex justify-space-between text-body-2 mb-1" v-if="cuotasSeleccionadas.length > 0">
              <span class="text-secondary">Cuotas de convenio:</span>
              <span class="font-weight-bold">{{ cuotasSeleccionadas.length }}</span>
            </div>

            <div class="d-flex justify-space-between text-caption text-secondary mb-2" v-if="desgloseSeleccionado.descuentoLey > 0">
              <span>Descto. 3ra Edad aplicado:</span>
              <span class="text-success font-weight-bold">-Bs {{ desgloseSeleccionado.descuentoLey.toFixed(2) }}</span>
            </div>

            <!-- Gran Total en grande -->
            <v-card outlined class="pa-3 mb-3 summary-box-active text-center rounded-lg">
              <div class="text-caption text-uppercase font-weight-bold primary--text">
                TOTAL A COBRAR EN VENTANILLA
              </div>
              <div class="text-h4 font-weight-black primary--text mt-1">
                Bs {{ totalSeleccionado.toFixed(2) }}
              </div>
            </v-card>

            <!-- DATOS FISCALES SIAT -->
            <div class="text-caption font-weight-bold grey--text text--darken-2 mb-1">
              DATOS FACTURA ELECTRÓNICA SIAT:
            </div>

            <v-row dense>
              <v-col cols="12" sm="5">
                <v-select
                  v-model="datosCobro.codigo_tipo_documento_identidad"
                  :items="tiposDocumento"
                  item-text="nombre"
                  item-value="codigo"
                  label="Tipo Doc."
                  dense
                  outlined
                  hide-details
                  class="mb-2"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="7">
                <v-text-field
                  v-model="datosCobro.numero_documento"
                  label="N° NIT / CI *"
                  dense
                  outlined
                  hide-details
                  class="mb-2"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-text-field
              v-model="datosCobro.nombre_razon_social"
              label="Razón Social / Nombre Cliente *"
              dense
              outlined
              hide-details
              class="mb-2"
            ></v-text-field>

            <v-text-field
              v-model="datosCobro.correo_electronico"
              label="Correo Electrónico (Envío PDF/XML Opcional)"
              dense
              outlined
              hide-details
              class="mb-2"
            ></v-text-field>

            <!-- Método de Pago -->
            <v-select
              v-model="datosCobro.codigo_metodo_pago"
              :items="metodosPago"
              item-text="nombre"
              item-value="codigo"
              label="Método de Pago *"
              dense
              outlined
              hide-details
              class="mb-3"
            ></v-select>

            <!-- Calculadora de Cambio para Efectivo -->
            <div v-if="datosCobro.codigo_metodo_pago === 1" class="mb-3 pa-3 calculator-card rounded-lg">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-caption font-weight-bold">Efectivo Recibido (Bs):</span>
                <!-- Botón Monto Exacto -->
                <v-btn x-small text color="primary" class="font-weight-bold" @click="efectivoRecibido = totalSeleccionado">
                  Monto Exacto
                </v-btn>
              </div>

              <v-text-field
                v-model.number="efectivoRecibido"
                type="number"
                dense
                outlined
                hide-details
                prefix="Bs"
                class="mb-2"
              ></v-text-field>

              <!-- Botones rápidos de billetes -->
              <div class="d-flex justify-space-between flex-wrap mb-2">
                <v-btn x-small outlined color="primary" class="my-1" @click="agregarEfectivo(10)">+10</v-btn>
                <v-btn x-small outlined color="primary" class="my-1" @click="agregarEfectivo(20)">+20</v-btn>
                <v-btn x-small outlined color="primary" class="my-1" @click="agregarEfectivo(50)">+50</v-btn>
                <v-btn x-small outlined color="primary" class="my-1" @click="agregarEfectivo(100)">+100</v-btn>
                <v-btn x-small outlined color="primary" class="my-1" @click="agregarEfectivo(200)">+200</v-btn>
              </div>

              <!-- Indicador de Cambio -->
              <div class="d-flex justify-space-between text-subtitle-2 font-weight-bold pt-1 border-top">
                <span>Cambio a devolver:</span>
                <span :class="cambioEfectivo >= 0 ? 'success--text text-h6 font-weight-black' : 'error--text'">
                  Bs {{ cambioEfectivo >= 0 ? cambioEfectivo.toFixed(2) : '0.00 (Faltan Bs ' + Math.abs(cambioEfectivo).toFixed(2) + ')' }}
                </span>
              </div>
            </div>

            <!-- Panel de Cobro QR Interoperable (Método 7) -->
            <div v-if="datosCobro.codigo_metodo_pago === 7" class="mb-3 pa-3 calculator-card rounded-lg text-center">
              <div class="text-caption font-weight-bold text-secondary mb-1">
                <v-icon small color="cyan darken-2">mdi-qrcode-scan</v-icon> Cobro Digital Simple QR (BCB)
              </div>
              <div class="text-caption mb-2 text--secondary">
                Genera un código QR dinámico por <strong>Bs {{ totalSeleccionado.toFixed(2) }}</strong> para que el abonado pague desde su celular.
              </div>
              <v-btn
                color="cyan darken-2"
                class="rounded-pill font-weight-bold text-white elevation-1"
                block
                :disabled="totalSeleccionado <= 0"
                @click="mostrarModalQr = true"
              >
                <v-icon left>mdi-qrcode-scan</v-icon> MOSTRAR QR EN PANTALLA
              </v-btn>
            </div>

            <!-- Botón de Cobro y Emisión SIAT -->
            <v-btn
              block
              x-large
              color="primary"
              class="rounded-pill font-weight-bold elevation-2 text-white"
              :disabled="totalSeleccionado <= 0 || (datosCobro.codigo_metodo_pago === 1 && efectivoRecibido < totalSeleccionado)"
              :loading="procesandoCobro"
              @click="procesarCobro(false)"
            >
              <v-icon left>mdi-check-decagram</v-icon> COBRAR Y FACTURAR SIAT
            </v-btn>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- ESTADO EN REPOSO / PANEL DE TURNO Y ÚLTIMOS COBROS -->
    <div v-else>
      <!-- CASO 1: TURNO ABIERTO (PANEL DE OPERACIÓN Y ÚLTIMOS COBROS) -->
      <div v-if="tieneSesionActiva && sesionActiva">
        <!-- MÉTRICAS EN TIEMPO REAL DEL TURNO -->
        <v-row dense class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Fondo de Apertura</div>
              <div class="text-h5 font-weight-black primary--text mt-1">
                Bs {{ parseFloat(sesionActiva.monto_apertura || 0).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Efectivo inicial asignado</div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Cobrado en Efectivo</div>
              <div class="text-h5 font-weight-black success--text mt-1">
                Bs {{ parseFloat(sesionActiva.monto_ventas_efectivo || 0).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Ingresos directos por ventanilla</div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated" rounded="lg">
              <div class="text-caption text-secondary font-weight-bold text-uppercase">Cobrado en QR / Banco</div>
              <div class="text-h5 font-weight-black info--text mt-1">
                Bs {{ parseFloat(sesionActiva.monto_ventas_qr_banco || 0).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Transferencias y pago digital</div>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="pa-3 text-center erp-card-elevated summary-box-active" rounded="lg">
              <div class="text-caption primary--text font-weight-bold text-uppercase">Efectivo en Gaveta</div>
              <div class="text-h5 font-weight-black primary--text mt-1">
                Bs {{ parseFloat(sesionActiva.monto_esperado_efectivo || 0).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Fondo + Ventas Efectivo</div>
            </v-card>
          </v-col>
        </v-row>

        <!-- TABLA DE ÚLTIMOS COBROS DE ESTE TURNO CON BOTÓN DE REIMPRESIÓN RÁPIDA -->
        <v-card rounded="lg" class="erp-card-elevated">
          <v-card-title class="py-3 px-4 d-flex justify-space-between align-center section-header-bg">
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-history</v-icon>
              <span class="text-subtitle-1 font-weight-bold text--primary">Últimos Cobros Realizados en este Turno</span>
            </div>
            <v-chip small color="primary" text-color="white" class="font-weight-bold">
              Turno #{{ sesionActiva.numero_sesion }}
            </v-chip>
          </v-card-title>

          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="font-weight-bold">N° Factura</th>
                  <th class="font-weight-bold">Abonado / Cliente</th>
                  <th class="font-weight-bold">NIT / CI</th>
                  <th class="text-right font-weight-bold">Monto (Bs)</th>
                  <th class="text-center font-weight-bold">Método de Pago</th>
                  <th class="text-center font-weight-bold">Fecha y Hora</th>
                  <th class="text-center font-weight-bold">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="fac in (sesionActiva.facturas || [])" :key="fac.id">
                  <td>
                    <v-chip small color="primary" label class="font-weight-bold">
                      #{{ fac.numero_factura }}
                    </v-chip>
                  </td>
                  <td>
                    <div class="font-weight-bold text-caption">{{ fac.nombre_razon_social }}</div>
                    <span v-if="fac.abonado" class="text-caption text-secondary">Cod: {{ fac.abonado.codigo }}</span>
                  </td>
                  <td class="text-caption">{{ fac.numero_documento || 'S/D' }}</td>
                  <td class="text-right font-weight-black success--text text-caption">
                    Bs {{ parseFloat(fac.monto_total).toFixed(2) }}
                  </td>
                  <td class="text-center">
                    <v-chip x-small :color="fac.codigo_metodo_pago === 1 ? 'success' : 'primary'" text-color="white">
                      {{ fac.codigo_metodo_pago === 1 ? 'EFECTIVO' : 'QR / BANCO' }}
                    </v-chip>
                  </td>
                  <td class="text-center text-caption text-secondary">
                    {{ fac.fecha_emision ? fac.fecha_emision.substring(0, 19).replace('T', ' ') : '-' }}
                  </td>
                  <td class="text-center">
                    <v-tooltip bottom>
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                          icon
                          small
                          color="primary"
                          v-bind="attrs"
                          v-on="on"
                          @click="abrirVisorFactura(fac.id, 'rollo')"
                        >
                          <v-icon small>mdi-printer-pos</v-icon>
                        </v-btn>
                      </template>
                      <span>Reimprimir Ticket 80mm</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                          icon
                          small
                          color="primary"
                          v-bind="attrs"
                          v-on="on"
                          @click="abrirVisorFactura(fac.id, 'carta')"
                        >
                          <v-icon small>mdi-file-document-outline</v-icon>
                        </v-btn>
                      </template>
                      <span>Ver Factura Carta</span>
                    </v-tooltip>
                  </td>
                </tr>
                <tr v-if="!sesionActiva.facturas || sesionActiva.facturas.length === 0">
                  <td colspan="7" class="text-center py-6 text-secondary">
                    <v-icon large color="grey lighten-1" class="d-block mb-2">mdi-receipt-text-outline</v-icon>
                    Aún no se han registrado cobros en este turno.<br>
                    Utilice el buscador superior para consultar abonados por Código, CI o Nombre y registrar pagos.
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card>
      </div>

      <!-- CASO 2: TURNO CERRADO / NO INICIADO -->
      <v-card v-else rounded="lg" class="pa-10 text-center erp-card-elevated my-6 empty-session-card">
        <v-avatar color="primary" size="76" class="mb-4 elevation-2">
          <v-icon size="40" color="white">mdi-cash-register</v-icon>
        </v-avatar>
        <h3 class="text-h5 font-weight-bold mb-2 text--primary">Apertura de Turno de Ventanilla Requerida</h3>
        <p class="text-body-2 text-secondary mb-5" style="max-width: 520px; margin: 0 auto;">
          Para habilitar la cobranza de consumo de agua potable, registrar cuotas y emitir facturas electrónicas oficiales en línea con el SIAT, debe realizar la apertura formal de su turno indicando el fondo de gaveta inicial.
        </p>
        <v-btn
          color="primary"
          class="rounded-pill font-weight-bold text-white elevation-2 px-6"
          large
          @click="mostrarModalApertura = true"
        >
          <v-icon left>mdi-lock-open-variant</v-icon> ABRIR TURNO DE CAJA AHORA
        </v-btn>
      </v-card>
    </div>

    <!-- DIÁLOGO DE COBRO EXITOSO (NOTIFICACIÓN Y ACCESO AL VISOR) -->
    <v-dialog v-model="modalFacturaEmitida" max-width="520" persistent>
      <v-card rounded="lg" v-if="facturaResultado">
        <v-card-title class="primary white--text py-3">
          <v-icon color="white" class="mr-2">mdi-check-circle</v-icon> Cobro y Factura SIAT Emitida
        </v-card-title>
        <v-card-text class="pt-4 text-center">
          <div class="text-caption text-secondary">FACTURA ELECTRÓNICA DE SERVICIO BÁSICO (SECTOR 13)</div>
          <div class="text-h4 font-weight-black primary--text mb-1">N° {{ facturaResultado.numero_factura }}</div>
          <div class="text-body-2 font-weight-bold mb-3">Monto Total: Bs {{ parseFloat(facturaResultado.monto_total).toFixed(2) }}</div>

          <v-card outlined class="pa-3 mb-3 section-header-bg text-left">
            <div class="text-caption"><strong>Cliente:</strong> {{ facturaResultado.nombre_razon_social }}</div>
            <div class="text-caption"><strong>NIT/CI:</strong> {{ facturaResultado.numero_documento }}</div>
            <div class="text-caption text-truncate"><strong>CUF:</strong> {{ facturaResultado.cuf }}</div>
          </v-card>

          <v-alert type="success" dense text v-if="ordenReconexionGenerada" class="text-left mb-3">
            <v-icon small left>mdi-pipe-disconnected</v-icon>
            <strong>¡Reconexión automática programada!</strong> Se generó la Orden #{{ ordenReconexionGenerada.numero_orden }}.
          </v-alert>

          <!-- Botón de apertura del Visor Modal (Térmico 80mm o Carta) SIN PESTAÑAS NUEVAS -->
          <v-btn
            block
            color="primary"
            class="rounded-pill mb-2 font-weight-bold"
            @click="abrirVisorFactura(facturaResultado.id, 'rollo')"
          >
            <v-icon left>mdi-printer-pos</v-icon> Ver e Imprimir Ticket 80mm
          </v-btn>

          <v-btn
            block
            outlined
            color="primary"
            class="rounded-pill mb-2 font-weight-bold"
            @click="abrirVisorFactura(facturaResultado.id, 'carta')"
          >
            <v-icon left>mdi-file-document-outline</v-icon> Ver Factura Tamaño Carta
          </v-btn>

          <v-btn
            block
            text
            color="secondary"
            class="rounded-pill"
            @click="modalFacturaEmitida = false"
          >
            Aceptar y Continuar con Siguiente Abonado
          </v-btn>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO SELECCIÓN DE ABONADO CUANDO HAY MÚLTIPLES COINCIDENCIAS POR CI/NOMBRE -->
    <v-dialog v-model="dialogResultadosBusqueda" max-width="850" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-multiple-check</v-icon>
          <span>Abonados Encontrados ({{ resultadosBusqueda.length }})</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogResultadosBusqueda = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <p class="text-caption text-secondary mb-3">
            Se encontraron varias conexiones o abonados con el criterio: <strong>"{{ codigoBusqueda }}"</strong>.
            Haga clic en <strong>"Cobrar"</strong> en la conexión correspondiente para cargar sus deudas:
          </p>

          <v-simple-table dense class="border rounded custom-hover-table">
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="font-weight-bold">Código</th>
                  <th class="font-weight-bold">Abonado / Nombre</th>
                  <th class="font-weight-bold">CI / NIT</th>
                  <th class="font-weight-bold">Zona / Ubicación</th>
                  <th class="font-weight-bold text-center">Mora</th>
                  <th class="font-weight-bold text-right">Saldo Deuda</th>
                  <th class="text-center font-weight-bold">Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in resultadosBusqueda"
                  :key="item.id"
                  @click="seleccionarAbonado(item.codigo)"
                  style="cursor: pointer;"
                >
                  <td>
                    <v-chip small color="primary" label class="font-weight-bold">
                      {{ item.codigo }}
                    </v-chip>
                  </td>
                  <td class="font-weight-medium text-caption">{{ item.nombre_completo }}</td>
                  <td class="text-caption">{{ item.numero_documento || 'S/D' }}</td>
                  <td class="text-caption">{{ item.zona ? item.zona.nombre : '-' }}</td>
                  <td class="text-center">
                    <v-chip x-small :color="item.meses_mora > 0 ? 'error' : 'success'" text-color="white">
                      {{ item.meses_mora }} mes(es)
                    </v-chip>
                  </td>
                  <td class="text-right font-weight-bold text-caption" :class="parseFloat(item.saldo_deuda || 0) > 0 ? 'error--text' : 'success--text'">
                    Bs {{ parseFloat(item.saldo_deuda || 0).toFixed(2) }}
                  </td>
                  <td class="text-center">
                    <v-btn
                      small
                      color="primary"
                      class="text-capitalize rounded-pill elevation-1"
                      @click.stop="seleccionarAbonado(item.codigo)"
                    >
                      <v-icon left x-small>mdi-cash-register</v-icon> Cobrar
                    </v-btn>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-2">
          <v-spacer></v-spacer>
          <v-btn text color="secondary" @click="dialogResultadosBusqueda = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE PDF (SIN WINDOW.OPEN) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :mostrar-selector-formato="esFacturaVisor"
      :formato-inicial="formatoFacturaVisor"
      @cambio-formato="alCambiarFormatoFactura"
    ></modal-visor-pdf>

    <!-- MODAL APERTURA DE CAJA -->
    <modal-apertura-caja
      v-model="mostrarModalApertura"
      :caja-defecto-id="cajaDefectoId"
      @cancelar="mostrarModalApertura = false"
      @sesion-abierta="alAbrirSesion"
    ></modal-apertura-caja>

    <!-- MODAL CIERRE Y ARQUEO DE CAJA -->
    <modal-cierre-arqueo-caja
      v-model="mostrarModalCierre"
      :sesion="sesionActiva"
      @cancelar="mostrarModalCierre = false"
      @sesion-cerrada="alCerrarSesion"
    ></modal-cierre-arqueo-caja>

    <!-- MODAL MOVIMIENTO MENOR DE CAJA CHICA -->
    <modal-movimiento-caja
      v-model="mostrarModalMovimiento"
      :sesion-id="sesionActiva ? sesionActiva.id : null"
      @cancelar="mostrarModalMovimiento = false"
      :caja-sesion-id="sesionActiva ? sesionActiva.id : null"
      @movimiento-registrado="alRegistrarMovimiento"
    ></modal-movimiento-caja>

    <!-- MODAL DE COBRO CON QR SIMPLE (BCB INTEROPERABLE) -->
    <modal-cobro-qr-simple
      v-model="mostrarModalQr"
      :monto="totalSeleccionado"
      :glosa="'Pago de agua ' + (estadoCuenta && estadoCuenta.abonado ? estadoCuenta.abonado.numero_cuenta : '')"
      :abonado-id="estadoCuenta && estadoCuenta.abonado ? estadoCuenta.abonado.id : null"
      :sesion-caja-id="sesionActiva ? sesionActiva.id : null"
      @pago-completado="alConfirmarPagoQr"
    ></modal-cobro-qr-simple>

    <!-- NOTIFICACIÓN NATIVA DEL SISTEMA (SNACKBAR) -->
    <v-snackbar
      v-model="snackbar.status"
      :color="snackbar.color"
      :timeout="4500"
      top
      right
      rounded="pill"
      elevation="6"
    >
      <div class="d-flex align-center">
        <v-icon dark left class="mr-2">{{ snackbar.icon || 'mdi-information' }}</v-icon>
        <span class="font-weight-medium">{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';
import ModalAperturaCaja from './components/ModalAperturaCaja.vue';
import ModalCierreArqueoCaja from './components/ModalCierreArqueoCaja.vue';
import ModalMovimientoCaja from './components/ModalMovimientoCaja.vue';
import ModalCobroQrSimple from './components/ModalCobroQrSimple.vue';

export default {
  name: 'CajaCobranzas',
  components: {
    ModalVisorPdf,
    ModalAperturaCaja,
    ModalCierreArqueoCaja,
    ModalMovimientoCaja,
    ModalCobroQrSimple,
  },
  data() {
    return {
      // Estado de Sesión / Turno de Caja
      sesionActiva: null,
      tieneSesionActiva: false,
      cajaDefectoId: null,
      cargandoSesion: false,
      mostrarModalApertura: false,
      mostrarModalCierre: false,
      mostrarModalMovimiento: false,
      mostrarModalQr: false,

      buscando: false,
      procesandoCobro: false,
      codigoBusqueda: '',
      tipoBusqueda: 'todos',
      tiposBusqueda: [
        { valor: 'todos', texto: 'Todos los campos' },
        { valor: 'codigo_abonado', texto: 'Código Abonado' },
        { valor: 'carnet_nit', texto: 'C.I. / NIT' },
        { valor: 'cliente', texto: 'Nombres / Apellidos' },
        { valor: 'numero_factura', texto: 'N° de Factura' },
      ],
      estadoCuenta: null,
      lecturasSeleccionadas: [],
      cuotasSeleccionadas: [],
      efectivoRecibido: 0,
      modalFacturaEmitida: false,
      facturaResultado: null,
      ordenReconexionGenerada: null,
      dialogResultadosBusqueda: false,
      resultadosBusqueda: [],

      // Estado del Visor Modal PDF
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      esFacturaVisor: false,
      formatoFacturaVisor: 'rollo',
      facturaVisorId: null,

      datosCobro: {
        codigo_tipo_documento_identidad: 1, // CI
        numero_documento: '',
        nombre_razon_social: '',
        correo_electronico: '',
        codigo_metodo_pago: 1, // Efectivo
      },
      tiposDocumento: [
        { codigo: 1, nombre: '1 - CI (Cédula)' },
        { codigo: 5, nombre: '5 - NIT' },
        { codigo: 2, nombre: '2 - CEX' },
        { codigo: 3, nombre: '3 - Pasaporte' },
        { codigo: 4, nombre: '4 - Otro Doc.' },
      ],
      metodosPago: [
        { codigo: 1, nombre: '1 - Efectivo (Bolivianos)' },
        { codigo: 2, nombre: '2 - Tarjeta Débito / Crédito' },
        { codigo: 7, nombre: '7 - Transferencia Bancaria / QR' },
      ],
      snackbar: {
        status: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle',
      },
    };
  },
  computed: {
    totalSeleccionado() {
      if (!this.estadoCuenta) return 0;
      let total = 0;
      this.estadoCuenta.lecturas_pendientes.forEach(lec => {
        if (this.lecturasSeleccionadas.includes(lec.id)) {
          total += parseFloat(lec.total_facturado);
        }
      });
      this.estadoCuenta.cuotas_convenio_pendientes.forEach(c => {
        if (this.cuotasSeleccionadas.includes(c.id)) {
          total += parseFloat(c.monto_cuota);
        }
      });
      return total;
    },
    desgloseSeleccionado() {
      let agua = 0;
      let alca = 0;
      let descto = 0;
      if (this.estadoCuenta) {
        this.estadoCuenta.lecturas_pendientes.forEach(lec => {
          if (this.lecturasSeleccionadas.includes(lec.id)) {
            agua += parseFloat(lec.monto_agua || 0);
            alca += parseFloat(lec.monto_alcantarillado || 0);
            descto += parseFloat(lec.monto_descuento_ley1886 || 0);
          }
        });
      }
      return { totalAgua: agua, totalAlca: alca, descuentoLey: descto };
    },
    cambioEfectivo() {
      return this.efectivoRecibido - this.totalSeleccionado;
    },
    etiquetaBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Código de Abonado';
        case 'carnet_nit': return 'Cédula de Identidad o NIT';
        case 'cliente': return 'Nombre / Razón Social del Abonado';
        case 'numero_factura': return 'Número de Factura';
        default: return 'Buscar por Código, CI/NIT, Nombre o N° Factura';
      }
    },
    placeholderBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Ej: 00001, 5105...';
        case 'carnet_nit': return 'Ej: 4582910, 1028394019...';
        case 'cliente': return 'Ej: Emilio Sajama, Juan Perez...';
        case 'numero_factura': return 'Ej: 36969...';
        default: return 'Ingrese código, carnet, nombre o factura y presione Enter...';
      }
    },
  },
  watch: {
    '$route.query.codigo': {
      handler(newVal) {
        if (newVal && newVal !== this.codigoBusqueda) {
          this.codigoBusqueda = newVal;
          this.buscarAbonado();
        }
      },
    },
  },
  mounted() {
    this.verificarEstadoSesion();
    const qCod = this.$route.query.codigo;
    if (qCod) {
      this.codigoBusqueda = qCod;
      this.buscarAbonado();
    }
  },
  methods: {
    mostrarNotificacion(texto, color = 'success', icon = 'mdi-check-circle') {
      this.snackbar = {
        status: true,
        text: texto,
        color: color,
        icon: icon,
      };
    },
    formatearHora(fecha) {
      if (!fecha) return '';
      const d = new Date(fecha);
      return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    async verificarEstadoSesion() {
      this.cargandoSesion = true;
      try {
        const res = await axios.get('/api/comercial/caja-sesiones/estado-actual');
        this.tieneSesionActiva = !!res.data?.tiene_sesion_activa;
        this.sesionActiva = res.data?.sesion || null;
        this.cajaDefectoId = res.data?.caja_defecto_id || null;
      } catch (e) {
        console.error('Error al verificar sesión de caja:', e);
      } finally {
        this.cargandoSesion = false;
      }
    },
    alAbrirSesion(sesion) {
      this.sesionActiva = sesion;
      this.tieneSesionActiva = true;
      this.mostrarModalApertura = false;
    },
    alCerrarSesion(sesion) {
      this.mostrarModalCierre = false;
      this.tieneSesionActiva = false;
      this.sesionActiva = null;
      this.estadoCuenta = null;
      // Abrir reporte oficial de arqueo en el visor integrado
      this.abrirVisorArqueo(sesion.id, sesion.numero_sesion);
    },
    alRegistrarMovimiento() {
      this.mostrarModalMovimiento = false;
      this.verificarEstadoSesion();
    },
    abrirVisorArqueo(sesionId, numeroSesion) {
      this.esFacturaVisor = false;
      this.urlVisorPdf = `/api/comercial/caja-sesiones/${sesionId}/reporte-pdf`;
      this.tituloVisorPdf = `Planilla de Arqueo y Cierre - ${numeroSesion}`;
      this.subtituloVisorPdf = 'Reporte Oficial de Cierre Diario de Caja';
      this.mostrarVisorPdf = true;
    },
    colorEstado(estado) {
      switch (estado) {
        case 'ACTIVO': return 'success';
        case 'CORTADO': return 'error';
        case 'EN_MORA': return 'warning';
        default: return 'primary';
      }
    },
    limpiarBusqueda() {
      this.codigoBusqueda = '';
      this.estadoCuenta = null;
      this.lecturasSeleccionadas = [];
      this.cuotasSeleccionadas = [];
      this.efectivoRecibido = 0;
    },
    async seleccionarAbonado(codigo) {
      this.dialogResultadosBusqueda = false;
      this.codigoBusqueda = codigo;
      this.buscando = true;
      try {
        await this.cargarEstadoCuenta(codigo);
      } finally {
        this.buscando = false;
      }
    },
    async cargarEstadoCuenta(codigo) {
      let res;
      try {
        res = await axios.get(`/api/comercial/caja/estado-cuenta/${codigo}`);
      } catch (err) {
        // Si es puramente numérico y falla, probar con auto-pad de 5 dígitos (ej. 5105 -> 05105)
        if (/^\d+$/.test(codigo) && codigo.length < 5) {
          const padded = codigo.padStart(5, '0');
          res = await axios.get(`/api/comercial/caja/estado-cuenta/${padded}`);
        } else {
          throw err;
        }
      }

      this.estadoCuenta = res.data.data;

      // Cargar datos del abonado para la factura SIAT
      const ab = this.estadoCuenta.abonado;
      this.datosCobro.numero_documento = ab.numero_documento || '0';
      this.datosCobro.nombre_razon_social = ab.nombre_completo || 'S/N';
      this.datosCobro.codigo_tipo_documento_identidad = (ab.numero_documento && ab.numero_documento.length > 8) ? 5 : 1;

      // Preseleccionar por defecto todas las facturas impagas (comportamiento estándar de caja)
      this.seleccionarNMeses(this.estadoCuenta.lecturas_pendientes.length);
      this.cuotasSeleccionadas = this.estadoCuenta.cuotas_convenio_pendientes.map(c => c.id);

      // Pre-cargar efectivo recibido con monto exacto
      this.$nextTick(() => {
        this.efectivoRecibido = this.totalSeleccionado;
      });
    },
    alCambiarTipoBusqueda() {
      if (this.codigoBusqueda) {
        this.buscarAbonado();
      }
    },
    async buscarAbonado() {
      const query = (this.codigoBusqueda || '').trim();
      if (!query) return;

      this.buscando = true;
      try {
        // 1. Intentar búsqueda predictiva por criterio seleccionado (Código, CI/NIT, Nombre o N° Factura)
        const searchRes = await axios.get('/api/comercial/caja/buscar-abonados', {
          params: {
            q: query,
            tipo_busqueda: this.tipoBusqueda || 'todos',
          },
        });
        const lista = searchRes.data?.data || [];

        if (lista.length === 1) {
          // Coincidencia única: cargar de inmediato
          this.codigoBusqueda = lista[0].codigo;
          await this.cargarEstadoCuenta(lista[0].codigo);
        } else if (lista.length > 1) {
          // Si el query coincide exactamente con el código de uno de ellos, cargar directamente
          const exacto = (this.tipoBusqueda === 'todos' || this.tipoBusqueda === 'codigo_abonado')
            ? lista.find(x => x.codigo === query || x.codigo === query.padStart(5, '0'))
            : null;
          if (exacto) {
            this.codigoBusqueda = exacto.codigo;
            await this.cargarEstadoCuenta(exacto.codigo);
          } else {
            // Múltiples coincidencias (ej. varias conexiones bajo el mismo carnet o nombre): mostrar selector
            this.resultadosBusqueda = lista;
            this.dialogResultadosBusqueda = true;
          }
        } else {
          // Fallback a búsqueda directa por código solo si aplica
          if (this.tipoBusqueda === 'todos' || this.tipoBusqueda === 'codigo_abonado') {
            await this.cargarEstadoCuenta(query);
          } else {
            this.mostrarNotificacion('No se encontraron abonados con el criterio ingresado.', 'info', 'mdi-account-search');
            this.estadoCuenta = null;
          }
        }
      } catch (e) {
        this.mostrarNotificacion(e.response?.data?.message || 'Abonado no encontrado en el sistema.', 'warning', 'mdi-account-alert');
        this.estadoCuenta = null;
      } finally {
        this.buscando = false;
      }
    },

    /**
     * Selección secuencial de N meses en orden cronológico estricto (de la más antigua a la más reciente)
     */
    seleccionarNMeses(n) {
      if (!this.estadoCuenta || !this.estadoCuenta.lecturas_pendientes) return;
      const count = Math.min(Math.max(0, n), this.estadoCuenta.lecturas_pendientes.length);
      this.lecturasSeleccionadas = this.estadoCuenta.lecturas_pendientes
        .slice(0, count)
        .map(l => l.id);

      // Actualizar efectivo si estaba en monto exacto
      this.$nextTick(() => {
        if (this.efectivoRecibido === 0 || this.efectivoRecibido < this.totalSeleccionado) {
          this.efectivoRecibido = this.totalSeleccionado;
        }
      });
    },

    /**
     * Al hacer clic en una fila, si se hace clic en la fila K, se seleccionan los meses 1..K
     */
    toggleLectura(idx) {
      const objetivo = idx + 1;
      if (this.lecturasSeleccionadas.length === objetivo) {
        // Si hace clic en la última seleccionada, la desmarca (retrocede a idx)
        this.seleccionarNMeses(idx);
      } else {
        // Selecciona hasta esa fila
        this.seleccionarNMeses(objetivo);
      }
    },

    toggleTodasCuotas() {
      if (!this.estadoCuenta) return;
      if (this.cuotasSeleccionadas.length === this.estadoCuenta.cuotas_convenio_pendientes.length) {
        this.cuotasSeleccionadas = [];
      } else {
        this.cuotasSeleccionadas = this.estadoCuenta.cuotas_convenio_pendientes.map(c => c.id);
      }
    },

    agregarEfectivo(monto) {
      this.efectivoRecibido = (parseFloat(this.efectivoRecibido) || 0) + monto;
    },

    alConfirmarPagoQr(datosQr) {
      this.datosCobro.codigo_metodo_pago = 7;
      this.procesarCobro(true);
    },

    async procesarCobro(qrYaValidado = false) {
      if (!this.tieneSesionActiva) {
        this.mostrarNotificacion('Debe realizar la apertura formal del turno de caja antes de cobrar.', 'warning', 'mdi-cash-register');
        this.mostrarModalApertura = true;
        return;
      }
      if (this.totalSeleccionado <= 0) {
        this.mostrarNotificacion('Debe seleccionar al menos una factura para cobrar.', 'warning', 'mdi-alert');
        return;
      }
      if (!this.datosCobro.nombre_razon_social || !this.datosCobro.numero_documento) {
        this.mostrarNotificacion('Ingrese la razón social y número de documento para la factura SIAT.', 'warning', 'mdi-card-account-details-outline');
        return;
      }
      if (this.datosCobro.codigo_metodo_pago === 1 && this.efectivoRecibido < this.totalSeleccionado) {
        this.mostrarNotificacion('El efectivo recibido es menor al total a cobrar.', 'warning', 'mdi-cash-remove');
        return;
      }
      // Si el método de pago es QR y aún no se ha validado, abrir el modal de QR
      if (this.datosCobro.codigo_metodo_pago === 7 && !qrYaValidado) {
        this.mostrarModalQr = true;
        return;
      }

      this.procesandoCobro = true;
      try {
        const payload = {
          id_abonado: this.estadoCuenta.abonado.id,
          lecturas_ids: this.lecturasSeleccionadas,
          cuotas_ids: this.cuotasSeleccionadas,
          codigo_metodo_pago: this.datosCobro.codigo_metodo_pago,
          nombre_razon_social: this.datosCobro.nombre_razon_social,
          numero_documento: this.datosCobro.numero_documento,
          codigo_tipo_documento_identidad: this.datosCobro.codigo_tipo_documento_identidad,
          correo_electronico: this.datosCobro.correo_electronico,
        };

        const res = await axios.post('/api/comercial/caja/cobrar', payload);
        this.facturaResultado = res.data.data.factura;
        this.ordenReconexionGenerada = res.data.data.orden_reconexion;
        this.modalFacturaEmitida = true;

        const numFac = this.facturaResultado.numero_factura || this.facturaResultado.id;
        const montoFac = parseFloat(this.facturaResultado.monto_total || 0).toFixed(2);
        this.mostrarNotificacion(
          `¡Cobro exitoso! Factura SIAT N° ${numFac} emitida por Bs ${montoFac}.`,
          'success',
          'mdi-check-decagram'
        );

        // Abrir automáticamente el visor modal en formato rollo 80mm
        this.abrirVisorFactura(this.facturaResultado.id, 'rollo');

        // Refrescar estado de cuenta del abonado y sesión de caja activa
        this.buscarAbonado();
        await this.verificarEstadoSesion();
      } catch (e) {
        this.mostrarNotificacion(e.response?.data?.message || 'Error al procesar el cobro en ventanilla.', 'error', 'mdi-alert-circle');
      } finally {
        this.procesandoCobro = false;
      }
    },

    /**
     * Abre la factura directamente en el Visor Modal (Cero window.open / Cero pestañas nuevas)
     */
    abrirVisorFactura(facturaId, formato = 'rollo') {
      this.facturaVisorId = facturaId;
      this.formatoFacturaVisor = formato;
      this.esFacturaVisor = true;
      this.urlVisorPdf = `/api/facturacion/publico/facturas/${facturaId}/pdf?formato=${formato}`;
      this.tituloVisorPdf = `Factura SIAT N° ${this.facturaResultado?.numero_factura || facturaId}`;
      this.subtituloVisorPdf = `${this.facturaResultado?.nombre_razon_social || ''} - NIT/CI: ${this.facturaResultado?.numero_documento || ''}`;
      this.mostrarVisorPdf = true;
    },

    alCambiarFormatoFactura(nuevoFormato) {
      if (this.facturaVisorId) {
        this.urlVisorPdf = `/api/facturacion/publico/facturas/${this.facturaVisorId}/pdf?formato=${nuevoFormato}`;
      }
    },
  },
};
</script>

<style scoped>
.sticky-panel {
  position: sticky;
  top: 80px;
}
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.07);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.theme--dark .erp-card-elevated {
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.35);
}
.session-active-card {
  border-left: 4px solid #10b981 !important;
}
.empty-session-card {
  border: 2px dashed rgba(0, 0, 0, 0.12) !important;
}
.theme--dark .empty-session-card {
  border: 2px dashed rgba(255, 255, 255, 0.15) !important;
}
.section-header-bg {
  background-color: #f8fafc;
}
.theme--dark .section-header-bg {
  background-color: rgba(255, 255, 255, 0.04);
}
.info-banner-bg {
  background-color: rgba(37, 99, 235, 0.05);
  border-bottom: 1px solid rgba(37, 99, 235, 0.1);
}
.theme--dark .info-banner-bg {
  background-color: rgba(59, 130, 246, 0.1);
  border-bottom: 1px solid rgba(59, 130, 246, 0.15);
}
.summary-box-active {
  background-color: rgba(37, 99, 235, 0.05) !important;
  border: 1px solid rgba(37, 99, 235, 0.2) !important;
}
.theme--dark .summary-box-active {
  background-color: rgba(37, 99, 235, 0.15) !important;
  border: 1px solid rgba(59, 130, 246, 0.35) !important;
}
.calculator-card {
  background-color: #f8fafc;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.theme--dark .calculator-card {
  background-color: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.fila-seleccionada {
  background-color: rgba(37, 99, 235, 0.08) !important;
}
.theme--dark .fila-seleccionada {
  background-color: rgba(59, 130, 246, 0.18) !important;
}
.tabla-cobranza tr:hover {
  background-color: #f1f5f9;
}
.theme--dark .tabla-cobranza tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
}
.border-top {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}
.theme--dark .border-top {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.cursor-pointer {
  cursor: pointer;
  user-select: none;
}
</style>
