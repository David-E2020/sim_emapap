<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-4 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="warning" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-alert-octagon-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Eventos Significativos y Contingencias</h2>
            <span class="text-caption text-secondary">
              Gestión de contingencias tributarias del SIN (RND 102100000011 / cortes de luz, internet y empaquetado 48h)
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize rounded-pill" :loading="cargando" @click="cargarEventos">
            <v-icon left small>mdi-refresh</v-icon> Actualizar
          </v-btn>
          <v-btn color="error" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="abrirDialogoNuevo">
            <v-icon left small>mdi-alert-circle</v-icon> + Iniciar Contingencia
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BANNER DE CONTINGENCIAS ACTIVAS -->
    <v-alert
      v-if="eventosActivos.length > 0"
      type="warning"
      prominent
      border="left"
      class="mb-4 elevation-2 rounded-lg"
    >
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div>
          <div class="text-subtitle-1 font-weight-bold">
            ⚠️ CONTINGENCIA ACTIVA: {{ eventosActivos.length }} evento(s) en curso operando Fuera de Línea (Offline)
          </div>
          <div class="text-caption">
            Las facturas emitidas en los puntos afectados se generan en modo Contingencia (Tipo Emisión 2). Al restablecerse el servicio, debe cerrar el evento para comprimir en .tar.gz y remitir al SIN dentro del plazo legal de 48 horas.
          </div>
        </div>
      </div>
    </v-alert>

    <!-- FILTROS SUPERIORES -->
    <v-card class="mb-4 pa-4 erp-card-elevated" rounded="lg">
      <v-row dense align="center">
        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filtroSucursal"
            :items="opcionesSucursalesFiltro"
            item-text="text"
            item-value="value"
            label="Sucursal"
            dense
            outlined
            hide-details
            @change="cambioFiltroSucursal"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filtroPuntoVenta"
            :items="opcionesPuntosVentaFiltro"
            item-text="text"
            item-value="value"
            label="Punto de Venta / Caja"
            dense
            outlined
            hide-details
            @change="cargarEventos"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filtroEstado"
            :items="opcionesEstadoFiltro"
            item-text="text"
            item-value="value"
            label="Estado del Evento"
            dense
            outlined
            hide-details
            @change="cargarEventos"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="12" md="3" class="d-flex justify-end align-center">
          <span class="text-caption text-secondary">
            Total registrados: <strong>{{ totalEventos }}</strong>
          </span>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE EVENTOS SIGNIFICATIVOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="headers"
        :items="eventos"
        :loading="cargando"
        class="elevation-0"
        no-data-text="No hay eventos significativos registrados para los filtros seleccionados"
      >
        <!-- CÓDIGO Y MOTIVO -->
        <template v-slot:item.codigo_evento="{ item }">
          <div class="py-1">
            <v-chip x-small color="error" text-color="white" class="font-weight-bold mr-1">
              CÓD. {{ item.codigo_evento }}
            </v-chip>
            <span class="font-weight-bold text-caption d-block mt-1">{{ obtenerNombreEvento(item.codigo_evento) }}</span>
            <span class="text-caption text-secondary text-truncate d-block" style="max-width: 250px;">
              {{ item.descripcion }}
            </span>
            <v-chip v-if="item.cafc" x-small outlined color="secondary" class="mt-1">
              CAFC: {{ item.cafc }}
            </v-chip>
          </div>
        </template>

        <!-- SUCURSAL Y PUNTO DE VENTA -->
        <template v-slot:item.sucursal="{ item }">
          <div>
            <v-chip x-small color="primary" outlined class="font-weight-medium mb-1">
              {{ item.sucursal ? item.sucursal.nombre : ('Sucursal ' + item.id_sucursal) }}
            </v-chip>
            <br />
            <v-chip x-small color="purple" outlined class="font-weight-medium">
              {{ item.punto_venta ? item.punto_venta.nombre : (item.id_punto_venta !== null && item.id_punto_venta !== undefined ? ('PV ' + item.id_punto_venta) : 'Toda la Sucursal') }}
            </v-chip>
          </div>
        </template>

        <!-- FECHA INICIO -->
        <template v-slot:item.fecha_inicio="{ item }">
          <div class="text-caption">
            <strong>{{ formatearFecha(item.fecha_inicio) }}</strong>
          </div>
        </template>

        <!-- FECHA FIN -->
        <template v-slot:item.fecha_fin="{ item }">
          <div class="text-caption">
            <template v-if="item.fecha_fin">
              {{ formatearFecha(item.fecha_fin) }}
            </template>
            <template v-else>
              <v-chip x-small color="warning" text-color="white" class="font-weight-bold pulse-chip">
                EN CURSO
              </v-chip>
            </template>
          </div>
        </template>

        <!-- PLAZO 48 HORAS SIN -->
        <template v-slot:item.plazo_48h="{ item }">
          <div class="text-center">
            <v-chip
              x-small
              :color="calcularPlazo(item).color"
              text-color="white"
              class="font-weight-bold"
            >
              {{ calcularPlazo(item).texto }}
            </v-chip>
          </div>
        </template>

        <!-- FACTURAS EMITIDAS -->
        <template v-slot:item.facturas_count="{ item }">
          <div class="text-center">
            <v-btn
              x-small
              outlined
              :color="item.facturas && item.facturas.length > 0 ? 'info' : 'grey'"
              class="rounded-pill font-weight-bold"
              @click="verFacturasEvento(item)"
            >
              <v-icon left x-small>mdi-receipt-text-outline</v-icon>
              {{ item.facturas ? item.facturas.length : 0 }} facturas
            </v-btn>
          </div>
        </template>

        <!-- PAQUETE SIAT (.TAR.GZ) -->
        <template v-slot:item.paquete="{ item }">
          <div v-if="item.paquetes && item.paquetes.length > 0">
            <div v-for="paq in item.paquetes" :key="paq.id" class="mb-1 d-flex align-center gap-1">
              <v-chip
                x-small
                :color="paq.estado_paquete === 'VALIDADA' ? 'success' : (paq.estado_paquete === 'OBSERVADA' ? 'error' : 'info')"
                text-color="white"
                class="font-weight-bold"
              >
                {{ paq.estado_paquete || 'RECIBIDO' }}
              </v-chip>
              <span class="text-caption font-mono text-truncate" style="max-width: 110px;" :title="paq.codigo_recepcion_paquete">
                {{ paq.codigo_recepcion_paquete }}
              </span>

              <!-- BOTÓN VALIDAR CON SIN -->
              <v-btn
                icon
                x-small
                color="primary"
                title="Consultar Validación ante el SIN"
                :loading="validandoPaqueteId === paq.id"
                @click="validarPaquete(paq.id)"
              >
                <v-icon small>mdi-cloud-sync</v-icon>
              </v-btn>

              <!-- BOTÓN DESCARGAR .TAR.GZ -->
              <v-btn
                icon
                x-small
                color="secondary"
                title="Descargar paquete comprimido (.tar.gz)"
                :loading="descargandoPaqueteId === paq.id"
                @click="descargarPaquete(paq.id)"
              >
                <v-icon small>mdi-download</v-icon>
              </v-btn>
            </div>
          </div>
          <div v-else class="text-caption text-secondary font-italic text-center">
            {{ item.estado_evento === 'INICIADO' ? 'Pendiente cierre' : 'Sin paquete' }}
          </div>
        </template>

        <!-- ESTADO EVENTO -->
        <template v-slot:item.estado_evento="{ item }">
          <v-chip
            :color="item.estado_evento === 'INICIADO' ? 'warning' : 'success'"
            text-color="white"
            x-small
            class="font-weight-bold text-uppercase"
          >
            {{ item.estado_evento }}
          </v-chip>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="text-right">
            <v-btn
              v-if="item.estado_evento === 'INICIADO'"
              color="success"
              small
              class="rounded-pill elevation-1"
              :loading="cerrandoId === item.id"
              @click="confirmarCierreEvento(item)"
            >
              <v-icon left small>mdi-check-all</v-icon> Cerrar y Empaquetar
            </v-btn>
            <span v-else class="text-caption text-secondary font-italic">
              <v-icon x-small color="success">mdi-check-circle</v-icon> Concluido
            </span>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO PARA INICIAR EVENTO SIGNIFICATIVO -->
    <v-dialog v-model="dialogoNuevo" max-width="580" persistent>
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center justify-space-between mb-3">
          <div class="d-flex align-center">
            <v-avatar color="error" rounded="lg" size="36" class="mr-2 text-white elevation-1">
              <v-icon small color="white">mdi-alert-octagon</v-icon>
            </v-avatar>
            <h3 class="text-h6 font-weight-bold mb-0">Iniciar Evento de Contingencia</h3>
          </div>
          <v-btn icon small @click="dialogoNuevo = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>

        <v-alert dense outlined type="warning" class="text-caption mb-3">
          Al iniciar este evento, las facturas emitidas en la sucursal o caja seleccionada operarán en modo <strong>Fuera de Línea (Offline)</strong> hasta que concluya la contingencia.
        </v-alert>

        <v-row dense>
          <!-- SUCURSAL -->
          <v-col cols="12" sm="6">
            <v-select
              v-model="formEvento.id_sucursal"
              :items="sucursalesForm"
              item-text="text"
              item-value="value"
              label="Sucursal Afectada *"
              dense
              outlined
              class="mb-2"
              @change="cambioSucursalForm"
            ></v-select>
          </v-col>

          <!-- PUNTO DE VENTA -->
          <v-col cols="12" sm="6">
            <v-select
              v-model="formEvento.id_punto_venta"
              :items="puntosVentaForm"
              item-text="text"
              item-value="value"
              label="Punto de Venta / Caja *"
              dense
              outlined
              class="mb-2"
            ></v-select>
          </v-col>

          <!-- MOTIVO CATÁLOGO SIN -->
          <v-col cols="12">
            <v-select
              v-model="formEvento.codigo_evento"
              :items="catalogoEventos"
              item-text="descripcion"
              item-value="codigo"
              label="Motivo del Evento Significativo (Normativa SIN) *"
              dense
              outlined
              class="mb-2"
            ></v-select>
          </v-col>

          <!-- HORA DE INICIO -->
          <v-col cols="12">
            <div class="d-flex align-center gap-2 mb-2">
              <v-checkbox
                v-model="usarHoraActual"
                label="Registrar inicio en este momento exacto"
                dense
                hide-details
                class="mt-0"
              ></v-checkbox>
            </div>
            <v-text-field
              v-if="!usarHoraActual"
              v-model="formEvento.fecha_inicio"
              label="Fecha y Hora de Inicio del Incidente *"
              type="datetime-local"
              dense
              outlined
              hint="Indique cuándo ocurrió efectivamente el corte o falla"
              persistent-hint
              class="mb-2"
            ></v-text-field>
          </v-col>

          <!-- DETALLE / OBSERVACIONES -->
          <v-col cols="12">
            <v-textarea
              v-model="formEvento.descripcion"
              label="Detalle / Observaciones Técnicas *"
              placeholder="Ej. Corte imprevisto de suministro de energía eléctrica en sector Patacamaya Central."
              dense
              outlined
              rows="2"
              class="mb-2"
            ></v-textarea>
          </v-col>

          <!-- CAFC -->
          <v-col cols="12">
            <v-text-field
              v-model="formEvento.cafc"
              label="Código CAFC (Opcional, sólo para facturación de contingencia manual preimpresa)"
              dense
              outlined
              placeholder="Ej. CAFC10283910"
              class="mb-1"
            ></v-text-field>
          </v-col>
        </v-row>

        <div class="d-flex justify-end gap-2 mt-3">
          <v-btn text class="rounded-pill" @click="dialogoNuevo = false">Cancelar</v-btn>
          <v-btn color="error" class="rounded-pill elevation-2" :loading="guardando" @click="iniciarEvento">
            Iniciar Contingencia
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CONFIRMAR CIERRE Y EMPAQUETADO -->
    <v-dialog v-model="dialogoCierre" max-width="480">
      <v-card rounded="lg" class="pa-4" v-if="eventoACerrar">
        <div class="d-flex align-center mb-3">
          <v-avatar color="success" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-check-all</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Cerrar Contingencia y Empaquetar</h3>
        </div>

        <p class="text-body-2 mb-3">
          Se procederá a dar por finalizado el evento <strong>#{{ eventoACerrar.id }} (Código {{ eventoACerrar.codigo_evento }})</strong>.
        </p>

        <v-alert dense outlined type="info" class="text-caption mb-3">
          El sistema comprimirá <strong>{{ eventoACerrar.facturas ? eventoACerrar.facturas.length : 0 }} facturas offline</strong> en un archivo <code>.tar.gz</code> firmado con SHA-256 y lo remitirá automáticamente al SIN.
        </v-alert>

        <div class="d-flex justify-end gap-2">
          <v-btn text class="rounded-pill" @click="dialogoCierre = false">Cancelar</v-btn>
          <v-btn color="success" class="rounded-pill elevation-2" :loading="cerrandoId === eventoACerrar.id" @click="ejecutarCierreEvento">
            Confirmar y Empaquetar
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DETALLE DE FACTURAS EN CONTINGENCIA -->
    <v-dialog v-model="dialogoFacturas" max-width="850">
      <v-card rounded="lg" class="pa-4" v-if="eventoSeleccionado">
        <div class="d-flex align-center justify-space-between mb-3">
          <div class="d-flex align-center">
            <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
              <v-icon small color="white">mdi-receipt-text-outline</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold mb-0">
                Facturas en Contingencia - Evento #{{ eventoSeleccionado.id }}
              </h3>
              <span class="text-caption text-secondary">
                {{ obtenerNombreEvento(eventoSeleccionado.codigo_evento) }} | Total: {{ facturasEvento.length }} emitidas
              </span>
            </div>
          </div>
          <v-btn icon small @click="dialogoFacturas = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>

        <v-data-table
          :headers="headersFacturas"
          :items="facturasEvento"
          dense
          class="elevation-0 mb-3"
          no-data-text="No se emitieron facturas bajo este evento de contingencia"
        >
          <template v-slot:item.numero_factura="{ item }">
            <span class="font-weight-bold font-mono">#{{ item.numero_factura }}</span>
          </template>

          <template v-slot:item.fecha_emision="{ item }">
            <span class="text-caption">{{ formatearFecha(item.fecha_emision) }}</span>
          </template>

          <template v-slot:item.monto_total="{ item }">
            <span class="font-weight-bold">Bs {{ Number(item.monto_total).toFixed(2) }}</span>
          </template>

          <template v-slot:item.estado_factura="{ item }">
            <v-chip
              x-small
              :color="item.estado_factura === 'VALIDADA' ? 'success' : (item.estado_factura === 'CONTINGENCIA' ? 'warning' : 'grey')"
              text-color="white"
              class="font-weight-bold"
            >
              {{ item.estado_factura }}
            </v-chip>
          </template>
        </v-data-table>

        <div class="d-flex align-center justify-space-between flex-wrap pt-2 border-top">
          <div class="text-caption">
            Monto total acumulado: <strong>Bs {{ totalMontoFacturasEvento.toFixed(2) }}</strong>
          </div>
          <v-btn color="primary" outlined class="rounded-pill" @click="dialogoFacturas = false">
            Cerrar Detalle
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'EventosSignificativos',
  data() {
    return {
      cargando: false,
      guardando: false,
      cerrandoId: null,
      validandoPaqueteId: null,
      descargandoPaqueteId: null,

      dialogoNuevo: false,
      dialogoCierre: false,
      dialogoFacturas: false,

      eventoACerrar: null,
      eventoSeleccionado: null,

      eventos: [],
      totalEventos: 0,
      sucursalesList: [],

      // Filtros
      filtroSucursal: null,
      filtroPuntoVenta: null,
      filtroEstado: null,

      opcionesEstadoFiltro: [
        { text: 'Todos los estados', value: null },
        { text: 'INICIADO (En curso)', value: 'INICIADO' },
        { text: 'CERRADO (Concluido)', value: 'CERRADO' },
      ],

      // Formulario nuevo evento
      usarHoraActual: true,
      formEvento: {
        codigo_evento: 1,
        descripcion: '',
        id_sucursal: 0,
        id_punto_venta: 0,
        fecha_inicio: '',
        cafc: '',
      },

      catalogoEventos: [
        { codigo: 1, descripcion: 'CORTE DEL SERVICIO DE ENERGÍA ELÉCTRICA' },
        { codigo: 2, descripcion: 'CORTE DE ACCESO A INTERNET' },
        { codigo: 3, descripcion: 'INACCESIBILIDAD AL SERVICIO WEB DE LA ADMINISTRACIÓN TRIBUTARIA' },
        { codigo: 4, descripcion: 'INGRESO A ZONAS SIN ACCESO A INTERNET' },
        { codigo: 5, descripcion: 'VENTA EN LUGARES SIN INTERNET (MÓVILES / TEMPORALES)' },
        { codigo: 6, descripcion: 'VENTA EN LUGARES CON INTERNET PERO FALLA DE HARDWARE / SOFTWARE' },
        { codigo: 7, descripcion: 'CAMBIO DE INFRAESTRUCTURA O CORTE PROGRAMADO DE SISTEMAS' },
      ],

      headers: [
        { text: 'Motivo / Incidente', value: 'codigo_evento', width: '240px' },
        { text: 'Sucursal / PV', value: 'sucursal', width: '160px' },
        { text: 'Inicio (UTC-4)', value: 'fecha_inicio', width: '140px' },
        { text: 'Fin (UTC-4)', value: 'fecha_fin', width: '140px' },
        { text: 'Plazo 48h SIN', value: 'plazo_48h', align: 'center', width: '130px', sortable: false },
        { text: 'Facturas', value: 'facturas_count', align: 'center', width: '120px' },
        { text: 'Paquete SIAT (.tar.gz)', value: 'paquete', width: '220px', sortable: false },
        { text: 'Estado', value: 'estado_evento', align: 'center', width: '100px' },
        { text: 'Acciones', value: 'acciones', align: 'right', sortable: false, width: '160px' },
      ],

      headersFacturas: [
        { text: 'Nº Factura', value: 'numero_factura', width: '100px' },
        { text: 'Fecha Emisión', value: 'fecha_emision', width: '150px' },
        { text: 'Razón Social / Cliente', value: 'nombre_razon_social' },
        { text: 'NIT / CI', value: 'numero_documento', width: '110px' },
        { text: 'Total', value: 'monto_total', align: 'right', width: '100px' },
        { text: 'Estado', value: 'estado_factura', align: 'center', width: '110px' },
      ],
    };
  },
  computed: {
    eventosActivos() {
      return this.eventos.filter(e => e.estado_evento === 'INICIADO');
    },
    opcionesSucursalesFiltro() {
      const items = [{ text: 'Todas las sucursales', value: null }];
      this.sucursalesList.forEach(s => {
        items.push({
          text: `Suc. ${s.codigo_sucursal} - ${s.nombre}`,
          value: s.codigo_sucursal,
        });
      });
      return items;
    },
    opcionesPuntosVentaFiltro() {
      const items = [{ text: 'Todos los puntos de venta', value: null }];
      if (this.filtroSucursal !== null && this.filtroSucursal !== undefined) {
        const suc = this.sucursalesList.find(s => s.codigo_sucursal === this.filtroSucursal);
        if (suc && suc.puntos_venta) {
          suc.puntos_venta.forEach(pv => {
            items.push({
              text: `PV ${pv.codigo_punto_venta} - ${pv.nombre}`,
              value: pv.codigo_punto_venta,
            });
          });
        }
      } else {
        // Todos los puntos de venta existentes
        this.sucursalesList.forEach(s => {
          if (s.puntos_venta) {
            s.puntos_venta.forEach(pv => {
              items.push({
                text: `Suc. ${s.codigo_sucursal} | PV ${pv.codigo_punto_venta} - ${pv.nombre}`,
                value: pv.codigo_punto_venta,
              });
            });
          }
        });
      }
      return items;
    },
    sucursalesForm() {
      return this.sucursalesList.map(s => ({
        text: `Suc. ${s.codigo_sucursal} - ${s.nombre}`,
        value: s.codigo_sucursal,
      }));
    },
    puntosVentaForm() {
      const items = [
        { text: 'Afecta a Toda la Sucursal (Sin caja específica)', value: null },
      ];
      const suc = this.sucursalesList.find(s => s.codigo_sucursal === this.formEvento.id_sucursal);
      if (suc && suc.puntos_venta) {
        suc.puntos_venta.forEach(pv => {
          items.push({
            text: `PV ${pv.codigo_punto_venta} - ${pv.nombre}`,
            value: pv.codigo_punto_venta,
          });
        });
      }
      return items;
    },
    facturasEvento() {
      return (this.eventoSeleccionado && this.eventoSeleccionado.facturas) ? this.eventoSeleccionado.facturas : [];
    },
    totalMontoFacturasEvento() {
      return this.facturasEvento.reduce((sum, f) => sum + Number(f.monto_total || 0), 0);
    },
  },
  mounted() {
    this.cargarSucursales();
    this.cargarEventos();
  },
  methods: {
    async cargarSucursales() {
      try {
        const res = await window.axios.get('/api/facturacion/siat/sucursales');
        this.sucursalesList = res.data.data || [];
        if (this.sucursalesList.length > 0 && this.formEvento.id_sucursal === null) {
          this.formEvento.id_sucursal = this.sucursalesList[0].codigo_sucursal;
        }
      } catch (e) {
        console.error('Error cargando sucursales', e);
      }
    },
    cambioFiltroSucursal() {
      this.filtroPuntoVenta = null;
      this.cargarEventos();
    },
    cambioSucursalForm() {
      this.formEvento.id_punto_venta = null;
    },
    async cargarEventos() {
      this.cargando = true;
      try {
        const params = {};
        if (this.filtroSucursal !== null && this.filtroSucursal !== undefined) {
          params.id_sucursal = this.filtroSucursal;
        }
        if (this.filtroPuntoVenta !== null && this.filtroPuntoVenta !== undefined) {
          params.id_punto_venta = this.filtroPuntoVenta;
        }
        if (this.filtroEstado) {
          params.estado_evento = this.filtroEstado;
        }

        const res = await window.axios.get('/api/facturacion/eventos-significativos', { params });
        this.eventos = res.data.data || [];
        this.totalEventos = res.data.total || this.eventos.length;
      } catch (e) {
        console.error('Error al cargar eventos', e);
      } finally {
        this.cargando = false;
      }
    },
    obtenerNombreEvento(codigo) {
      const match = this.catalogoEventos.find(c => c.codigo === Number(codigo));
      return match ? match.descripcion : `Evento Código ${codigo}`;
    },
    formatearFecha(str) {
      if (!str) return '-';
      try {
        const clean = typeof str === 'string' ? str.replace('Z', '') : str;
        const f = new Date(clean);
        return new Intl.DateTimeFormat('es-BO', {
          timeZone: 'America/La_Paz',
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false,
        }).format(f);
      } catch (e) {
        return str;
      }
    },
    calcularPlazo(item) {
      const refStr = item.fecha_fin || item.fecha_inicio;
      if (!refStr) return { texto: 'N/A', color: 'grey' };

      const clean = typeof refStr === 'string' ? refStr.replace('Z', '') : refStr;
      const refDate = new Date(clean);
      const plazoMs = 48 * 3600 * 1000;
      const deadline = new Date(refDate.getTime() + plazoMs);
      const now = new Date();
      const diffMs = deadline - now;

      if (diffMs <= 0) {
        return { texto: 'Fuera de plazo (>48h)', color: 'error' };
      }

      const totalHoras = Math.floor(diffMs / (3600 * 1000));
      const mins = Math.floor((diffMs % (3600 * 1000)) / (60 * 1000));

      let color = 'success';
      if (totalHoras < 12) {
        color = 'error';
      } else if (totalHoras < 24) {
        color = 'warning';
      }

      return {
        texto: `${totalHoras}h ${mins}m restantes`,
        color,
      };
    },
    mostrarMensaje(tipo, texto) {
      if (this.$message && typeof this.$message[tipo] === 'function') {
        this.$message[tipo](texto);
      } else if (tipo === 'error') {
        console.error(texto);
      } else {
        console.log(texto);
      }
    },
    abrirDialogoNuevo() {
      this.usarHoraActual = true;
      const codSucursal = this.sucursalesList.length > 0 ? this.sucursalesList[0].codigo_sucursal : 0;
      this.formEvento = {
        codigo_evento: 1,
        descripcion: '',
        id_sucursal: codSucursal,
        id_punto_venta: null,
        fecha_inicio: '',
        cafc: '',
      };
      this.dialogoNuevo = true;
    },
    async iniciarEvento() {
      if (!this.formEvento.descripcion) {
        this.mostrarMensaje('warning', 'Ingrese el detalle o justificación del evento.');
        return;
      }

      const payload = {
        codigo_evento: this.formEvento.codigo_evento,
        descripcion: this.formEvento.descripcion,
        id_sucursal: this.formEvento.id_sucursal,
        id_punto_venta: this.formEvento.id_punto_venta !== null && this.formEvento.id_punto_venta !== undefined ? this.formEvento.id_punto_venta : null,
        cafc: this.formEvento.cafc || null,
      };

      if (!this.usarHoraActual && this.formEvento.fecha_inicio) {
        payload.fecha_inicio = this.formEvento.fecha_inicio;
      }

      this.guardando = true;
      try {
        const res = await window.axios.post('/api/facturacion/eventos-significativos', payload);
        if (res.data.success) {
          this.mostrarMensaje('success', 'Evento de contingencia iniciado. El punto opera fuera de línea.');
          this.dialogoNuevo = false;
          this.cargarEventos();
        }
      } catch (e) {
        const msg = e.response && e.response.data && e.response.data.message ? e.response.data.message : 'Error al iniciar contingencia';
        this.mostrarMensaje('error', msg);
      } finally {
        this.guardando = false;
      }
    },
    confirmarCierreEvento(item) {
      this.eventoACerrar = item;
      this.dialogoCierre = true;
    },
    async ejecutarCierreEvento() {
      if (!this.eventoACerrar) return;
      const id = this.eventoACerrar.id;
      this.cerrandoId = id;
      try {
        const res = await window.axios.post(`/api/facturacion/eventos-significativos/${id}/cerrar`);
        if (res.data.success) {
          this.mostrarMensaje('success', res.data.message || 'Contingencia cerrada y paquete remitido al SIN.');
          this.dialogoCierre = false;
          this.eventoACerrar = null;
          this.cargarEventos();
        }
      } catch (e) {
        const msg = e.response && e.response.data && e.response.data.message ? e.response.data.message : 'Error al cerrar contingencia';
        this.mostrarMensaje('error', msg);
      } finally {
        this.cerrandoId = null;
      }
    },
    async validarPaquete(paqueteId) {
      this.validandoPaqueteId = paqueteId;
      try {
        const res = await window.axios.post(`/api/facturacion/eventos-significativos/paquetes/${paqueteId}/validar`);
        if (res.data.success) {
          const estado = res.data.data ? res.data.data.estado_paquete : 'VALIDADA';
          this.mostrarMensaje('success', `Validación consultada con el SIN. Estado: ${estado}`);
          this.cargarEventos();
        } else {
          this.mostrarMensaje('warning', res.data.mensaje_siat || 'El paquete aún no ha sido procesado por el SIN.');
        }
      } catch (e) {
        this.mostrarMensaje('error', 'Error al consultar validación del paquete ante el SIN.');
      } finally {
        this.validandoPaqueteId = null;
      }
    },
    async descargarPaquete(paqueteId) {
      this.descargandoPaqueteId = paqueteId;
      try {
        const res = await window.axios.get(`/api/facturacion/eventos-significativos/paquetes/${paqueteId}/descargar`, {
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `paquete_evento_${paqueteId}.tar.gz`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        this.mostrarMensaje('success', 'Descarga del archivo .tar.gz completada.');
      } catch (e) {
        this.mostrarMensaje('error', 'No se pudo descargar el archivo del paquete.');
      } finally {
        this.descargandoPaqueteId = null;
      }
    },
    verFacturasEvento(item) {
      this.eventoSeleccionado = item;
      this.dialogoFacturas = true;
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
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
.font-mono {
  font-family: monospace;
}
.pulse-chip {
  animation: pulse-animation 2s infinite;
}
@keyframes pulse-animation {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
  100% {
    opacity: 1;
  }
}
.border-top {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
