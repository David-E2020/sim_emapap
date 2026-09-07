<template>
  <div class="caja-cobranzas-container">
    <!-- CABECERA DE VENTANILLA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="teal darken-2" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-cash-register</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Caja y Facturación en Ventanilla</h2>
            <span class="text-caption text-secondary">
              Cobranza ágil de consumo de agua, cuotas de convenio y emisión en línea de Factura SIAT (Sector 13 - Servicios Básicos)
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-chip color="teal" text-color="white" small class="font-weight-bold">
            <v-icon x-small left color="white">mdi-printer-pos</v-icon> Térmica 80mm / Carta SIAT
          </v-chip>
          <v-chip color="blue-grey" outlined small class="font-weight-bold">
            <v-icon x-small left>mdi-order-numeric-ascending</v-icon> Cobro Secuencial Cronológico
          </v-chip>
        </div>
      </div>
    </v-card>

    <!-- BUSCADOR RÁPIDO DE ABONADO -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="7">
          <v-text-field
            v-model="codigoBusqueda"
            label="Buscar por Código (ej. 05001, 5105), Carnet (CI/NIT) o Nombres y Apellidos..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            autofocus
            placeholder="Ingrese código de abonado, carnet de identidad o apellido y presione Enter..."
            @keyup.enter="buscarAbonado"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-btn
            color="primary"
            class="rounded-pill elevation-1"
            block
            :loading="buscando"
            @click="buscarAbonado"
          >
            <v-icon left small>mdi-account-search</v-icon> Consultar Abonado
          </v-btn>
        </v-col>
        <v-col cols="12" md="2" class="text-right" v-if="estadoCuenta">
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
                  <span v-if="estadoCuenta.abonado.es_tercera_edad" class="ml-1 text-teal font-weight-bold">| (Ley 1886 3ra Edad -20%)</span>
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
            <v-card-title class="py-2 px-4 d-flex justify-space-between align-center text-subtitle-1 font-weight-bold grey lighten-4 flex-wrap">
              <div class="d-flex align-center">
                <v-icon left color="primary" small>mdi-format-list-numbered</v-icon>
                <span>Meses / Facturas Pendientes ({{ estadoCuenta.lecturas_pendientes.length }})</span>
              </div>

              <!-- BOTONERA RÁPIDA DE SELECCIÓN TIPO FACTIFIV -->
              <div class="d-flex align-center gap-1 mt-1 mt-sm-0" v-if="estadoCuenta.lecturas_pendientes.length > 0">
                <span class="text-caption font-weight-bold mr-2 grey--text text--darken-2">Pagar:</span>
                <v-btn
                  x-small
                  color="teal"
                  outlined
                  class="font-weight-bold"
                  :disabled="estadoCuenta.lecturas_pendientes.length < 1"
                  @click="seleccionarNMeses(1)"
                >
                  1 Mes
                </v-btn>
                <v-btn
                  x-small
                  color="teal"
                  outlined
                  class="font-weight-bold"
                  v-if="estadoCuenta.lecturas_pendientes.length >= 2"
                  @click="seleccionarNMeses(2)"
                >
                  2 Meses
                </v-btn>
                <v-btn
                  x-small
                  color="teal"
                  outlined
                  class="font-weight-bold"
                  v-if="estadoCuenta.lecturas_pendientes.length >= 3"
                  @click="seleccionarNMeses(3)"
                >
                  3 Meses
                </v-btn>
                <v-btn
                  x-small
                  color="primary"
                  class="font-weight-bold elevation-1"
                  @click="seleccionarNMeses(estadoCuenta.lecturas_pendientes.length)"
                >
                  Todos ({{ estadoCuenta.lecturas_pendientes.length }})
                </v-btn>
                <v-btn
                  x-small
                  text
                  color="secondary"
                  @click="seleccionarNMeses(0)"
                >
                  Limpiar
                </v-btn>
              </div>
            </v-card-title>

            <!-- AVISO DE ORDEN SECUENCIAL -->
            <div class="px-4 py-1 blue-grey lighten-5 text-caption d-flex align-center justify-space-between">
              <span>
                <v-icon x-small color="teal" class="mr-1">mdi-information</v-icon>
                <strong>Cobro Secuencial:</strong> Se cancelan obligatoriamente desde el mes más antiguo adeudado hacia el más reciente.
              </span>
              <span class="font-weight-bold teal--text">
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
                        color="teal"
                        readonly
                      ></v-checkbox>
                    </td>
                    <td>
                      <div class="font-weight-bold">
                        {{ lec.periodo ? lec.periodo.periodo : '-' }}
                        <v-chip x-small color="teal" text-color="white" class="ml-1" v-if="idx === 0">
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
                    <td class="text-right font-weight-bold" :class="lecturasSeleccionadas.includes(lec.id) ? 'teal--text text--darken-2' : ''">
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
            <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4 d-flex justify-space-between">
              <span>Cuotas de Convenio de Pago</span>
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
                        color="teal"
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
            <v-card outlined class="pa-3 mb-3 teal lighten-5 text-center rounded-lg border-teal">
              <div class="text-caption text-uppercase font-weight-bold teal--text text--darken-4">
                TOTAL A COBRAR EN VENTANILLA
              </div>
              <div class="text-h4 font-weight-black teal--text text--darken-3 mt-1">
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
            <div v-if="datosCobro.codigo_metodo_pago === 1" class="mb-3 pa-2 grey lighten-4 rounded-lg">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-caption font-weight-bold">Efectivo Recibido (Bs):</span>
                <!-- Botón Monto Exacto -->
                <v-btn x-small text color="teal" class="font-weight-bold" @click="efectivoRecibido = totalSeleccionado">
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
                class="mb-2 white"
              ></v-text-field>

              <!-- Botones rápidos de billetes -->
              <div class="d-flex justify-space-between gap-1 mb-2">
                <v-btn x-small outlined color="teal" @click="agregarEfectivo(10)">+10</v-btn>
                <v-btn x-small outlined color="teal" @click="agregarEfectivo(20)">+20</v-btn>
                <v-btn x-small outlined color="teal" @click="agregarEfectivo(50)">+50</v-btn>
                <v-btn x-small outlined color="teal" @click="agregarEfectivo(100)">+100</v-btn>
                <v-btn x-small outlined color="teal" @click="agregarEfectivo(200)">+200</v-btn>
              </div>

              <!-- Indicador de Cambio -->
              <div class="d-flex justify-space-between text-subtitle-2 font-weight-bold pt-1 border-top">
                <span>Cambio a devolver:</span>
                <span :class="cambioEfectivo >= 0 ? 'success--text text-h6 font-weight-black' : 'error--text'">
                  Bs {{ cambioEfectivo >= 0 ? cambioEfectivo.toFixed(2) : '0.00 (Faltan Bs ' + Math.abs(cambioEfectivo).toFixed(2) + ')' }}
                </span>
              </div>
            </div>

            <!-- Botón de Cobro y Emisión SIAT -->
            <v-btn
              block
              x-large
              color="teal darken-2"
              class="rounded-pill font-weight-bold elevation-2 text-white"
              :disabled="totalSeleccionado <= 0 || (datosCobro.codigo_metodo_pago === 1 && efectivoRecibido < totalSeleccionado)"
              :loading="procesandoCobro"
              @click="procesarCobro"
            >
              <v-icon left>mdi-check-decagram</v-icon> COBRAR Y FACTURAR SIAT
            </v-btn>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- DIÁLOGO DE COBRO EXITOSO (NOTIFICACIÓN Y ACCESO AL VISOR) -->
    <v-dialog v-model="modalFacturaEmitida" max-width="520" persistent>
      <v-card rounded="lg" v-if="facturaResultado">
        <v-card-title class="teal darken-2 white--text py-3">
          <v-icon color="white" class="mr-2">mdi-check-circle</v-icon> Cobro y Factura SIAT Emitida
        </v-card-title>
        <v-card-text class="pt-4 text-center">
          <div class="text-caption text-secondary">FACTURA ELECTRÓNICA DE SERVICIO BÁSICO (SECTOR 13)</div>
          <div class="text-h4 font-weight-black primary--text mb-1">N° {{ facturaResultado.numero_factura }}</div>
          <div class="text-body-2 font-weight-bold mb-3">Monto Total: Bs {{ parseFloat(facturaResultado.monto_total).toFixed(2) }}</div>

          <v-card outlined class="pa-3 mb-3 grey lighten-4 text-left">
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
            color="teal"
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
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'CajaCobranzas',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      buscando: false,
      procesandoCobro: false,
      codigoBusqueda: '',
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
    const qCod = this.$route.query.codigo;
    if (qCod) {
      this.codigoBusqueda = qCod;
      this.buscarAbonado();
    }
  },
  methods: {
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
    async buscarAbonado() {
      const query = (this.codigoBusqueda || '').trim();
      if (!query) return;

      this.buscando = true;
      try {
        // 1. Intentar búsqueda predictiva por Código, Carnet (CI/NIT) o Nombres/Apellidos
        const searchRes = await axios.get('/api/comercial/caja/buscar-abonados', {
          params: { q: query },
        });
        const lista = searchRes.data?.data || [];

        if (lista.length === 1) {
          // Coincidencia única: cargar de inmediato
          this.codigoBusqueda = lista[0].codigo;
          await this.cargarEstadoCuenta(lista[0].codigo);
        } else if (lista.length > 1) {
          // Si el query coincide exactamente con el código de uno de ellos, cargar directamente
          const exacto = lista.find(x => x.codigo === query || x.codigo === query.padStart(5, '0'));
          if (exacto) {
            this.codigoBusqueda = exacto.codigo;
            await this.cargarEstadoCuenta(exacto.codigo);
          } else {
            // Múltiples coincidencias (ej. varias conexiones bajo el mismo carnet o nombre): mostrar selector
            this.resultadosBusqueda = lista;
            this.dialogResultadosBusqueda = true;
          }
        } else {
          // Fallback a búsqueda directa por estado-cuenta
          await this.cargarEstadoCuenta(query);
        }
      } catch (e) {
        alert(e.response?.data?.message || 'Abonado no encontrado en el sistema.');
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

    async procesarCobro() {
      if (this.totalSeleccionado <= 0) {
        alert('Debe seleccionar al menos una factura para cobrar.');
        return;
      }
      if (!this.datosCobro.nombre_razon_social || !this.datosCobro.numero_documento) {
        alert('Ingrese la razón social y número de documento para la factura SIAT.');
        return;
      }
      if (this.datosCobro.codigo_metodo_pago === 1 && this.efectivoRecibido < this.totalSeleccionado) {
        alert('El efectivo recibido es menor al total a cobrar.');
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

        // Abrir automáticamente el visor modal en formato rollo 80mm
        this.abrirVisorFactura(this.facturaResultado.id, 'rollo');

        // Refrescar estado de cuenta del abonado
        this.buscarAbonado();
      } catch (e) {
        alert(e.response?.data?.message || 'Error al procesar el cobro en ventanilla.');
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
}
.border-teal {
  border: 1.5px solid #00897b !important;
}
.border-top {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}
.cursor-pointer {
  cursor: pointer;
  user-select: none;
}
.fila-seleccionada {
  background-color: #e0f2f1 !important;
}
.tabla-cobranza tr:hover {
  background-color: #f1f5f9;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
