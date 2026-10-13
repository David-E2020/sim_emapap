<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-table-box-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Bandeja de Facturas Emitidas</h2>
            <span class="text-caption text-secondary">Registro, visualización, impresión y anulación de documentos fiscales oficiales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0 flex-wrap">
          <v-btn
            outlined
            color="indigo darken-2"
            class="text-capitalize font-weight-medium rounded-pill elevation-1"
            to="/facturacion/libro-ventas"
          >
            <v-icon left small>mdi-scale-balance</v-icon> Libro de Ventas / Cotejo SIN
          </v-btn>
          <v-btn
            outlined
            color="primary"
            class="text-capitalize font-weight-medium rounded-pill elevation-1"
            :loading="sincronizandoReloj"
            @click="sincronizarRelojSiat"
          >
            <v-icon left small>mdi-clock-check-outline</v-icon> Sincronizar Hora SIAT
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" :to="{ name: 'facturacion_crear' }">
            <v-icon left small>mdi-plus-circle</v-icon> + Nueva Factura
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- Criterio / Tipo de búsqueda -->
        <v-col cols="12" sm="6" md="3">
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
            @change="cargarFacturas"
          ></v-select>
        </v-col>

        <!-- Campo de texto de búsqueda -->
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="busqueda"
            :label="etiquetaBusqueda"
            :placeholder="placeholderBusqueda"
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarFacturas"
            @click:clear="alLimpiarBusqueda"
          ></v-text-field>
        </v-col>

        <!-- Filtro Sucursal -->
        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtroSucursal"
            :items="opcionesSucursales"
            item-text="texto"
            item-value="id"
            label="Sucursal"
            prepend-inner-icon="mdi-office-building-marker"
            dense
            outlined
            hide-details
            clearable
            @change="alCambiarSucursal"
          ></v-select>
        </v-col>

        <!-- Filtro Punto de Venta / Caja -->
        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtroPuntoVenta"
            :items="opcionesPuntosVenta"
            item-text="texto"
            item-value="id"
            label="Punto de Venta / Caja"
            prepend-inner-icon="mdi-cash-register"
            dense
            outlined
            hide-details
            clearable
            @change="cargarFacturas"
          ></v-select>
        </v-col>

        <!-- Estado Fiscal -->
        <v-col cols="12" sm="4" md="4">
          <v-select
            v-model="filtroEstado"
            :items="['TODOS', 'VALIDADA', 'ANULADA', 'OBSERVADA', 'CONTINGENCIA', 'DUPLICADAS']"
            label="Estado Fiscal"
            prepend-inner-icon="mdi-shield-check-outline"
            dense
            outlined
            hide-details
            @change="cargarFacturas"
          ></v-select>
        </v-col>

        <!-- Fecha Desde -->
        <v-col cols="12" sm="4" md="4">
          <v-text-field
            v-model="filtroFechaInicio"
            label="Fecha Desde"
            type="date"
            prepend-inner-icon="mdi-calendar-start"
            dense
            outlined
            hide-details
            clearable
            @change="cargarFacturas"
          ></v-text-field>
        </v-col>

        <!-- Fecha Hasta -->
        <v-col cols="12" sm="4" md="4">
          <v-text-field
            v-model="filtroFechaFin"
            label="Fecha Hasta"
            type="date"
            prepend-inner-icon="mdi-calendar-end"
            dense
            outlined
            hide-details
            clearable
            @change="cargarFacturas"
          ></v-text-field>
        </v-col>
      </v-row>

      <!-- BARRA DE ACCIONES Y ATAJOS DE FECHA -->
      <div class="d-flex align-center justify-space-between flex-wrap mt-3 pt-3" style="border-top: 1px dashed #e0e0e0;">
        <!-- Atajos rápidos -->
        <div class="d-flex align-center flex-wrap gap-1">
          <span class="text-caption text-secondary mr-1 font-weight-medium">Atajos de fecha:</span>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="teal darken-2" @click="establecerRangoRapido('junio_2026')">
            Junio 2026 (RCV)
          </v-btn>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="teal darken-2" @click="establecerRangoRapido('julio_2026')">
            Julio 2026 (RCV)
          </v-btn>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="primary" @click="establecerRangoRapido('hoy')">
            Hoy
          </v-btn>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="primary" @click="establecerRangoRapido('semana')">
            Esta Semana
          </v-btn>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="primary" @click="establecerRangoRapido('mes')">
            Este Mes
          </v-btn>
          <v-btn x-small text class="text-capitalize font-weight-bold" color="primary" @click="establecerRangoRapido('gestion')">
            Año {{ new Date().getFullYear() }}
          </v-btn>
          <v-btn
            v-if="busqueda || filtroEstado !== 'TODOS' || filtroSucursal || filtroPuntoVenta || filtroFechaInicio || filtroFechaFin"
            x-small
            text
            class="text-capitalize font-weight-bold ml-1"
            color="red darken-1"
            @click="limpiarFiltros"
          >
            <v-icon x-small left>mdi-close-circle-outline</v-icon> Limpiar Filtros
          </v-btn>
        </div>

        <!-- Botones de Acción: Buscar y Exportar -->
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="primary"
            class="text-capitalize font-weight-medium rounded-pill elevation-1 px-4"
            small
            :loading="cargando"
            @click="cargarFacturas"
          >
            <v-icon left small>mdi-magnify</v-icon> Buscar
          </v-btn>

          <!-- MENÚ DE EXPORTACIÓN (PDF / EXCEL) -->
          <v-menu offset-y transition="slide-y-transition">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                color="teal darken-1"
                dark
                class="text-capitalize font-weight-medium rounded-pill elevation-1 px-4"
                small
                v-bind="attrs"
                v-on="on"
                :loading="exportando"
              >
                <v-icon left small>mdi-file-export-outline</v-icon>
                Exportar
                <v-icon right small>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list dense>
              <v-list-item @click="exportarReporte('pdf')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="red darken-1" small>mdi-file-pdf-box</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">Planilla Oficial en PDF</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">Documento formal con firmas, KPIs y diseño institucional</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>

              <v-divider class="my-1"></v-divider>

              <v-list-item @click="exportarReporte('excel')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="green darken-2" small>mdi-file-excel-box</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">Planilla Excel Profesional (.xlsx)</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">Libro formateado con colores, cuadrícula, totales y fórmulas</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>

              <v-list-item @click="exportarReporte('csv')">
                <v-list-item-icon class="mr-2">
                  <v-icon color="blue-grey darken-1" small>mdi-file-delimited-outline</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">Exportar a CSV Masivo (.csv)</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">Datos planos delimitados por punto y coma (para grandes volúmenes)</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>

      <!-- ALERTA / INDICADOR DE INTEGRIDAD FISCAL Y CUFS ÚNICOS -->
      <div class="d-flex align-center justify-space-between flex-wrap mt-3 pt-2" style="border-top: 1px dashed #e0e0e0;">
        <div class="d-flex align-center flex-wrap gap-2">
          <v-chip
            v-if="!(auditoriaCuf.total_duplicadas || auditoriaCuf.numeros_duplicados || auditoriaCuf.cufs_duplicados || auditoriaCuf.total_cufs_duplicados)"
            small
            color="green lighten-5"
            class="font-weight-medium text-caption green--text text--darken-3"
            outlined
          >
            <v-icon left x-small color="green darken-2">mdi-shield-check</v-icon>
            Integridad SIAT: 100% Facturas Únicas (0 facturas duplicadas)
          </v-chip>

          <v-chip
            v-else
            small
            color="red lighten-5"
            class="font-weight-bold text-caption red--text text--darken-3"
            outlined
            @click="filtrarDuplicadas"
          >
            <v-icon left x-small color="red darken-2">mdi-alert-octagon</v-icon>
            Alerta Fiscal: {{ auditoriaCuf.total_duplicadas || auditoriaCuf.numeros_duplicados || auditoriaCuf.cufs_duplicados || auditoriaCuf.total_cufs_duplicados || 0 }} Factura(s) Duplicada(s) detectada(s) (ej: N° {{ (auditoriaCuf.ejemplos_numeros || []).slice(0, 3).join(', ') }}) — Ver Duplicadas
          </v-chip>
        </div>

        <div v-if="filtroEstado === 'DUPLICADAS'" class="text-caption red--text font-weight-bold d-flex align-center mt-1 mt-sm-0">
          <v-icon x-small color="red" class="mr-1">mdi-filter-variant</v-icon>
          Mostrando únicamente facturas duplicadas (por CUF o N° correlativo validado)
          <v-btn x-small text color="primary" class="ml-2 font-weight-bold" @click="filtroEstado = 'TODOS'; cargarFacturas()">
            Quitar filtro
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE RESUMEN TRIBUTARIO Y FINANCIERO (KPIS BANDEJA) -->
    <v-row class="mb-4" dense v-if="resumenKpis && resumenKpis.total_registros > 0">
      <v-col cols="12" sm="6" md="2">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg" style="border-top: 3px solid #004D40;">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Total Facturado</div>
          <div class="text-h6 font-weight-black primary--text mt-1">
            Bs {{ Number(resumenKpis.total_facturado || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}
          </div>
          <div class="text-caption text-secondary">
            {{ resumenKpis.total_registros }} facturas
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="2">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg" style="border-top: 3px solid #0288D1;">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Tasas No IVA</div>
          <div class="text-h6 font-weight-black blue--text text--darken-2 mt-1">
            Bs {{ Number(resumenKpis.total_tasas || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}
          </div>
          <div class="text-caption text-secondary">
            Aseo Urbano / Exentas
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg" style="border-top: 3px solid #2E7D32;">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Base Débito Fiscal</div>
          <div class="text-h6 font-weight-black success--text mt-1">
            Bs {{ Number(resumenKpis.total_base_debito_fiscal || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}
          </div>
          <div class="text-caption text-success font-weight-medium">
            Sujeto al IVA (13%)
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="2">
        <v-card class="pa-3 text-center erp-card-elevated" rounded="lg" style="border-top: 3px solid #5E35B1;">
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Débito Fiscal (13%)</div>
          <div class="text-h6 font-weight-black purple--text text--darken-2 mt-1">
            Bs {{ Number(resumenKpis.total_debito_fiscal || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}
          </div>
          <div class="text-caption text-secondary">
            Form. 200 SIN
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="12" md="3">
        <v-card class="pa-3 erp-card-elevated" rounded="lg">
          <div class="text-caption text-secondary font-weight-bold text-uppercase text-center mb-1">
            Estados Fiscales
          </div>
          <div class="d-flex flex-column gap-1">
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium success--text">
                <v-icon x-small color="success">mdi-check-circle</v-icon> Válidas:
              </span>
              <v-chip x-small color="success" text-color="white" class="font-weight-bold">
                {{ resumenKpis.cantidad_validas || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between" v-if="resumenKpis.cantidad_contingencias > 0">
              <span class="text-caption font-weight-medium warning--text text--darken-2">
                <v-icon x-small color="warning darken-2">mdi-cloud-off-outline</v-icon> Contingencias:
              </span>
              <v-chip x-small color="warning darken-1" text-color="white" class="font-weight-bold">
                {{ resumenKpis.cantidad_contingencias || 0 }}
              </v-chip>
            </div>
            <div class="d-flex align-center justify-space-between">
              <span class="text-caption font-weight-medium error--text">
                <v-icon x-small color="error">mdi-cancel</v-icon> Anuladas:
              </span>
              <v-chip x-small color="error" text-color="white" class="font-weight-bold">
                {{ resumenKpis.cantidad_anuladas || 0 }}
              </v-chip>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA DE FACTURAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="headers"
        :items="facturas"
        :loading="cargando"
        :server-items-length="totalFacturas"
        :options.sync="opciones"
        class="elevation-0"
        no-data-text="No se encontraron facturas emitidas"
      >
        <!-- N° Factura -->
        <template v-slot:item.numero_factura="{ item }">
          <div class="d-flex flex-column py-1">
            <div class="d-flex align-center flex-wrap">
              <span class="font-weight-bold text-primary">N° {{ item.numero_factura }}</span>
              <v-tooltip v-if="item.es_duplicada || item.es_cuf_duplicado" bottom color="error">
                <template v-slot:activator="{ on, attrs }">
                  <v-chip v-bind="attrs" v-on="on" x-small color="error" class="ml-1 font-weight-bold">
                    <v-icon x-small left>mdi-alert</v-icon> DUPLICADA
                  </v-chip>
                </template>
                <span>Alerta Fiscal: Factura duplicada detectada (número repetido con estado VALIDADA o CUF redundante)</span>
              </v-tooltip>
            </div>
            <div class="d-flex align-center gap-1 mt-1 flex-wrap">
              <v-chip
                v-if="item.tipo_emision === 2"
                x-small
                color="amber lighten-5"
                class="amber--text text--darken-4 font-weight-bold"
              >
                <v-icon left x-small color="amber darken-4">mdi-wifi-off</v-icon>
                CONTINGENCIA
              </v-chip>
              <v-chip
                x-small
                outlined
                :color="item.codigo_documento_sector === 13 ? 'blue darken-2' : 'purple darken-2'"
                class="font-weight-medium"
              >
                <v-icon left x-small>{{ item.codigo_documento_sector === 13 ? 'mdi-water-outline' : 'mdi-cart-outline' }}</v-icon>
                {{ item.codigo_documento_sector === 13 ? 'Serv. Básicos' : 'Compra Venta' }}
              </v-chip>
            </div>
          </div>
        </template>

        <!-- Fecha Emisión -->
        <template v-slot:item.fecha_emision="{ item }">
          <div class="d-flex flex-column">
            <span class="text-caption font-weight-medium">{{ formatearFecha(item.fecha_emision) }}</span>
            <span
              v-if="item.cuf"
              class="text-caption grey--text text-truncate font-monospace"
              style="max-width: 145px; font-size: 10px;"
              :title="'CUF: ' + item.cuf"
            >
              CUF: {{ item.cuf.slice(0, 10) }}...
            </span>
          </div>
        </template>

        <!-- Sucursal / Caja (Punto de Venta) -->
        <template v-slot:item.sucursal_punto_venta="{ item }">
          <div class="d-flex flex-column py-1" style="min-width: 180px;">
            <div
              class="d-flex align-center font-weight-medium text-caption text-truncate"
              :title="item.sucursal ? item.sucursal.nombre : 'Casa Matriz'"
            >
              <v-icon x-small color="primary" class="mr-1">mdi-office-building-marker</v-icon>
              <span>{{ item.sucursal ? `Suc. ${item.sucursal.codigo_sucursal} - ${item.sucursal.nombre}` : 'Casa Matriz (Suc. 0)' }}</span>
            </div>
            <div class="d-flex align-center flex-wrap gap-1 mt-1">
              <v-chip
                v-if="item.punto_venta"
                x-small
                color="teal lighten-5"
                class="teal--text text--darken-3 font-weight-medium"
                :title="item.punto_venta.descripcion || item.punto_venta.nombre"
              >
                <v-icon left x-small color="teal darken-2">mdi-cash-register</v-icon>
                PV {{ item.punto_venta.codigo_punto_venta }}: {{ item.punto_venta.nombre }}
              </v-chip>
              <v-chip
                v-else
                x-small
                color="grey lighten-4"
                class="grey--text text--darken-2"
                title="Documento histórico migrado desde FoxPro (sin punto de venta registrado)"
              >
                <v-icon left x-small color="grey">mdi-database-clock-outline</v-icon>
                Migrada (Sin PV)
              </v-chip>

              <!-- Cajero si tiene sesión de caja -->
              <v-tooltip v-if="item.sesion_caja && item.sesion_caja.cajero" bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-chip v-bind="attrs" v-on="on" x-small outlined color="indigo" class="font-weight-medium">
                    <v-icon left x-small>mdi-account</v-icon>
                    {{ item.sesion_caja.cajero.name.split(' ')[0] }}
                  </v-chip>
                </template>
                <span>Cajero: {{ item.sesion_caja.cajero.name }} (Sesión #{{ item.sesion_caja.numero_sesion }})</span>
              </v-tooltip>
            </div>
          </div>
        </template>

        <!-- Cliente / NIT / Abonado -->
        <template v-slot:item.nombre_razon_social="{ item }">
          <div class="font-weight-medium">{{ item.nombre_razon_social }}</div>
          <div class="d-flex align-center gap-1 text-caption text-secondary flex-wrap mt-1">
            <span>Doc: {{ item.numero_documento }} {{ item.complemento ? '-' + item.complemento : '' }}</span>
            <v-chip v-if="item.abonado" x-small outlined color="primary" class="font-weight-bold ml-1">
              <v-icon x-small left>mdi-water</v-icon>
              Abonado #{{ item.abonado.codigo }}
            </v-chip>
          </div>
        </template>

        <!-- Monto Total -->
        <template v-slot:item.monto_total="{ item }">
          <div class="d-flex flex-column align-end">
            <span class="font-weight-bold text-subtitle-2" :style="item.estado_factura === 'ANULADA' ? 'color: #999; text-decoration: line-through;' : ''">
              Bs {{ parseFloat(item.monto_total).toFixed(2) }}
            </span>
          </div>
        </template>

        <!-- Tasas No Sujetas a IVA -->
        <template v-slot:item.monto_tasas="{ item }">
          <div class="d-flex flex-column align-end">
            <span class="font-weight-medium text-caption blue--text text--darken-2">
              Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : Math.max(0, (item.monto_total || 0) - (item.monto_total_sujeto_iva || 0) - (item.monto_descuento || 0)).toFixed(2) }}
            </span>
          </div>
        </template>

        <!-- Monto Base Sujeto a IVA -->
        <template v-slot:item.monto_total_sujeto_iva="{ item }">
          <div class="d-flex flex-column align-end">
            <span class="font-weight-bold text-caption success--text text--darken-2">
              Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : parseFloat(item.monto_total_sujeto_iva).toFixed(2) }}
            </span>
          </div>
        </template>

        <!-- Débito Fiscal 13% -->
        <template v-slot:item.debito_fiscal="{ item }">
          <div class="d-flex flex-column align-end">
            <span class="font-weight-bold text-caption purple--text text--darken-2">
              Bs {{ item.estado_factura === 'ANULADA' ? '0.00' : (parseFloat(item.monto_total_sujeto_iva) * 0.13).toFixed(2) }}
            </span>
          </div>
        </template>

        <!-- Estado Fiscal -->
        <template v-slot:item.estado_factura="{ item }">
          <v-chip
            :color="colorEstado(item.estado_factura)"
            text-color="white"
            x-small
            class="font-weight-bold text-uppercase"
          >
            {{ item.estado_factura }}
          </v-chip>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-end gap-1">
            <!-- Ver Detalle / Desglose Fiscal -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="indigo darken-2" v-bind="attrs" v-on="on" @click="abrirModalDetalle(item)">
                  <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Detalle y Desglose Tributario</span>
            </v-tooltip>
            <!-- Imprimir Carta / Rollo 80mm -->
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on: tooltipOn }">
                    <v-btn icon small color="primary" v-bind="attrs" v-on="{ ...on, ...tooltipOn }">
                      <v-icon small>mdi-printer</v-icon>
                    </v-btn>
                  </template>
                  <span>Imprimir / Descargar PDF</span>
                </v-tooltip>
              </template>
              <v-list dense>
                <v-list-item @click="imprimirPdf(item, 'carta')">
                  <v-list-item-icon class="mr-2">
                    <v-icon small color="primary">mdi-file-document-outline</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Imprimir Formato Carta (Oficial)</v-list-item-title>
                </v-list-item>
                <v-list-item @click="imprimirPdf(item, 'rollo')">
                  <v-list-item-icon class="mr-2">
                    <v-icon small color="orange darken-2">mdi-receipt-text-outline</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>Imprimir Rollo Térmico (80mm POS)</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>

            <!-- Descargar XML -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="secondary" v-bind="attrs" v-on="on" @click="descargarXml(item.id)">
                  <v-icon small>mdi-xml</v-icon>
                </v-btn>
              </template>
              <span>Descargar XML Firmado</span>
            </v-tooltip>

            <!-- Reenviar Factura a SIAT (Regularizar Contingencia) -->
            <v-tooltip bottom v-if="item.estado_factura === 'CONTINGENCIA'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="amber darken-3"
                  v-bind="attrs"
                  v-on="on"
                  :loading="reenviandoId === item.id"
                  @click="reenviarFacturaAlSiat(item)"
                >
                  <v-icon small>mdi-cloud-upload-outline</v-icon>
                </v-btn>
              </template>
              <span>Reenviar a SIAT (Regularizar Contingencia)</span>
            </v-tooltip>

            <!-- Verificar Estado en Línea con SIAT -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="primary"
                  v-bind="attrs"
                  v-on="on"
                  :loading="verificandoId === item.id"
                  @click="verificarEstadoEnSin(item)"
                >
                  <v-icon small>mdi-cloud-sync-outline</v-icon>
                </v-btn>
              </template>
              <span>Verificar Estado en Línea (SIAT)</span>
            </v-tooltip>

            <!-- Consultar Directamente en Portal SIAT Oficial (Impuestos) -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal darken-1"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirPortalSiatOficial(item)"
                >
                  <v-icon small>mdi-qrcode-scan</v-icon>
                </v-btn>
              </template>
              <span>Verificar en Portal SIAT Oficial (Impuestos)</span>
            </v-tooltip>

            <!-- Reenviar por Correo -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="info" v-bind="attrs" v-on="on" @click="abrirDialogoCorreo(item)">
                  <v-icon small>mdi-email-fast-outline</v-icon>
                </v-btn>
              </template>
              <span>Enviar Factura por Correo</span>
            </v-tooltip>

            <!-- Anular Factura Ordinaria o Administrativa -->
            <v-tooltip bottom v-if="item.estado_factura !== 'ANULADA' && item.estado_factura !== 'CANCELLED'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="abrirDialogoAnulacion(item)">
                  <v-icon small>mdi-cancel</v-icon>
                </v-btn>
              </template>
              <span>Anular Factura ante el SIN</span>
            </v-tooltip>

            <!-- Revertir Anulación (Restaurar a VALIDADA) -->
            <v-tooltip bottom v-if="item.estado_factura === 'ANULADA' || item.estado_factura === 'CANCELLED'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="teal"
                  v-bind="attrs"
                  v-on="on"
                  :loading="revertiendoId === item.id"
                  @click="confirmarReversionAnulacion(item)"
                >
                  <v-icon small>mdi-restore-alert</v-icon>
                </v-btn>
              </template>
              <span>Revertir Anulación (Restaurar en SIAT)</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO DE ANULACIÓN -->
    <v-dialog v-model="dialogoAnular" max-width="480">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="error" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-alert</v-icon>
          </v-avatar>
          <h3 class="text-h6 font-weight-bold mb-0">Anular Factura Fiscal</h3>
        </div>

        <p class="text-body-2 text-secondary mb-3">
          Está a punto de anular la <strong>Factura N° {{ facturaSeleccionada.numero_factura }}</strong> emitida a nombre de <strong>{{ facturaSeleccionada.nombre_razon_social }}</strong> por Bs {{ parseFloat(facturaSeleccionada.monto_total || 0).toFixed(2) }}.
        </p>

        <v-alert
          v-if="facturaSeleccionada.es_duplicada"
          dense
          outlined
          type="warning"
          class="text-caption mb-3"
        >
          <strong>Atención:</strong> Esta factura está detectada como duplicada (N° {{ facturaSeleccionada.numero_factura }}). 
          Se ha preseleccionado el motivo <strong>Código 4: FACTURA DUPLICADA</strong>. Verifique que no esté anulando el ejemplar impreso que el cliente tiene en su poder.
        </v-alert>

        <v-select
          v-model="motivoAnulacionSeleccionado"
          :items="motivosAnulacion"
          item-text="descripcion"
          item-value="codigo"
          label="Motivo de Anulación Oficial del SIN *"
          dense
          outlined
          class="mb-2"
        ></v-select>

        <!-- Conmutador para Anulación Administrativa Fuera de Plazo (RND 102600000025) -->
        <v-switch
          v-model="esAnulacionAdministrativa"
          label="Anulación Administrativa Fuera de Plazo (RND 102600000025)"
          color="warning"
          dense
          class="mt-1 mb-2"
        ></v-switch>

        <div v-if="esAnulacionAdministrativa" class="pa-3 mb-3 amber lighten-5 rounded-lg border-amber">
          <div class="text-caption font-weight-bold amber--text text--darken-4 mb-2">
            <v-icon x-small color="amber darken-4">mdi-gavel</v-icon> Respaldo RND 102600000025 del SIN:
          </div>
          <v-text-field
            v-model="nroResolucion"
            label="N° Resolución Administrativa *"
            placeholder="Ej. RA-GDLPZ-2026-0045"
            dense
            outlined
            class="mb-2"
          ></v-text-field>
          <v-text-field
            v-model="fechaResolucion"
            type="date"
            label="Fecha de Resolución Administrativa *"
            dense
            outlined
          ></v-text-field>
        </div>

        <div class="d-flex justify-end gap-2">
          <v-btn text @click="dialogoAnular = false">Cancelar</v-btn>
          <v-btn
            color="error"
            :loading="anulando"
            :disabled="!motivoAnulacionSeleccionado || (esAnulacionAdministrativa && (!nroResolucion || !fechaResolucion))"
            @click="confirmarAnulacion"
          >
            {{ esAnulacionAdministrativa ? 'Confirmar Anulación R.A.' : 'Confirmar Anulación' }}
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DE ENVÍO POR CORREO -->
    <v-dialog v-model="dialogoCorreo" max-width="480">
      <v-card rounded="lg" class="pa-4">
        <div class="d-flex align-center mb-3">
          <v-avatar color="primary" rounded="lg" size="36" class="mr-2 text-white elevation-1">
            <v-icon small color="white">mdi-email-outline</v-icon>
          </v-avatar>
          <div class="text-h6 font-weight-bold">Enviar Factura por Correo</div>
        </div>

        <p class="text-body-2 text-secondary mb-3">
          Se enviará la Factura <strong>N° {{ facturaSeleccionada.numero_factura }}</strong> con sus adjuntos oficiales en formato PDF y XML firmado.
        </p>

        <v-text-field
          v-model="correoDestino"
          label="Correo Electrónico del Abonado"
          outlined
          dense
          placeholder="ejemplo@correo.bo"
          prepend-inner-icon="mdi-email"
        ></v-text-field>

        <v-card-actions class="px-0 pb-0">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogoCorreo = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="enviandoCorreo" @click="confirmarEnvioCorreo">
            <v-icon left small>mdi-send</v-icon> Enviar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DE PROGRESO DE EXPORTACIÓN (EXCEL / PDF) -->
    <v-dialog v-model="dialogoExportando" persistent max-width="480">
      <v-card rounded="lg" class="pa-5">
        <div class="d-flex align-center mb-3">
          <v-avatar :color="formatoExportando === 'pdf' ? 'red darken-1' : 'green darken-2'" size="46" class="mr-3 elevation-2 text-white">
            <v-icon color="white">{{ formatoExportando === 'pdf' ? 'mdi-file-pdf-box' : 'mdi-file-excel-box' }}</v-icon>
          </v-avatar>
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">
              {{ formatoExportando === 'pdf' ? 'Generando Planilla PDF Oficial' : (formatoExportando === 'csv' ? 'Generando Archivo CSV Masivo' : 'Generando Planilla Excel Profesional (.xlsx)') }}
            </h3>
            <span class="text-caption text-secondary">
              {{ formatoExportando === 'pdf' ? 'Compilando documento fiscal para impresión y auditoría...' : 'Formateando celdas, colores institucionales y fórmulas de suma...' }}
            </span>
          </div>
        </div>

        <v-card-text class="pa-0 mt-3">
          <div class="d-flex justify-space-between align-center mb-1">
            <span class="text-caption font-weight-medium grey--text text--darken-2">
              <v-icon x-small color="teal" class="mr-1">mdi-progress-download</v-icon>
              {{ mensajeProgresoExportacion }}
            </span>
            <span class="text-caption font-weight-bold teal--text text--darken-2">{{ progresoExportacion }}%</span>
          </div>

          <v-progress-linear
            :value="progresoExportacion"
            color="teal darken-1"
            height="10"
            rounded
            striped
          ></v-progress-linear>

          <div v-if="totalFacturas > 5000 && !filtroFechaInicio && !filtroFechaFin" class="mt-3 pa-2 rounded grey lighten-4 d-flex align-center text-caption text-secondary">
            <v-icon small color="amber darken-3" class="mr-2">mdi-lightbulb-on-outline</v-icon>
            <span>Consejo: Puede seleccionar un <strong>Rango de Fechas</strong> o un <strong>Abonado</strong> para acotar el reporte y acelerar la descarga.</span>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DE DETALLE Y DESGLOSE TRIBUTARIO DE FACTURA -->
    <v-dialog v-model="dialogoDetalle" max-width="840" scrollable>
      <v-card rounded="lg" v-if="facturaDetalleSeleccionada">
        <v-card-title class="py-3 px-4 d-flex justify-space-between align-center primary white--text">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">mdi-receipt-text-outline</v-icon>
            <span class="font-weight-bold text-h6">
              Factura N° {{ facturaDetalleSeleccionada.numero_factura }}
            </span>
            <v-chip small class="ml-3 font-weight-bold" :color="colorEstado(facturaDetalleSeleccionada.estado_factura)" text-color="white">
              {{ facturaDetalleSeleccionada.estado_factura }}
            </v-chip>
          </div>
          <v-btn icon dark small @click="dialogoDetalle = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-alert
            v-if="facturaDetalleSeleccionada.es_duplicada"
            dense
            color="red lighten-5"
            class="red--text text--darken-3 text-caption mb-3"
            outlined
          >
            <div class="d-flex align-center font-weight-bold mb-1">
              <v-icon small color="red darken-2" class="mr-1">mdi-alert-octagon</v-icon>
              Alerta de Duplicidad Fiscal Detectada (N° {{ facturaDetalleSeleccionada.numero_factura }})
            </div>
            <div>
              Esta factura comparte número correlativo con otra factura en estado <strong>VALIDADA</strong> emitida en la misma gestión. 
              Verifique los orígenes (Caja local vs. Conciliación SIN) para confirmar cuál ejemplar posee el usuario físicamente antes de anular la redundante.
            </div>
          </v-alert>

          <v-row dense class="mb-3">
            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-3 fill-height">
                <div class="text-caption font-weight-bold text-secondary text-uppercase mb-2">
                  <v-icon x-small color="primary" class="mr-1">mdi-account-circle</v-icon> Datos del Abonado / Cliente
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom">
                  <span class="text-caption grey--text">Razón Social / Nombre:</span>
                  <strong class="text-caption text-right">{{ facturaDetalleSeleccionada.nombre_razon_social }}</strong>
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom">
                  <span class="text-caption grey--text">NIT / CI / CEX:</span>
                  <span class="text-caption font-weight-bold">{{ facturaDetalleSeleccionada.numero_documento }}{{ facturaDetalleSeleccionada.complemento ? '-' + facturaDetalleSeleccionada.complemento : '' }}</span>
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom" v-if="facturaDetalleSeleccionada.abonado">
                  <span class="text-caption grey--text">Código de Abonado:</span>
                  <v-chip x-small color="teal" text-color="white" class="font-weight-bold">
                    #{{ facturaDetalleSeleccionada.abonado.codigo }}
                  </v-chip>
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom" v-if="facturaDetalleSeleccionada.numero_medidor">
                  <span class="text-caption grey--text">N° Medidor:</span>
                  <span class="text-caption font-mono">{{ facturaDetalleSeleccionada.numero_medidor }}</span>
                </div>
                <div class="d-flex justify-space-between py-1" v-if="facturaDetalleSeleccionada.consumo_periodo">
                  <span class="text-caption grey--text">Consumo Periodo:</span>
                  <span class="text-caption font-weight-bold">{{ facturaDetalleSeleccionada.consumo_periodo }} m³</span>
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card outlined rounded="lg" class="pa-3 fill-height">
                <div class="text-caption font-weight-bold text-secondary text-uppercase mb-2">
                  <v-icon x-small color="primary" class="mr-1">mdi-shield-check</v-icon> Datos Fiscales y Autorización
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom">
                  <span class="text-caption grey--text">Fecha de Emisión:</span>
                  <span class="text-caption font-weight-bold">{{ formatearFecha(facturaDetalleSeleccionada.fecha_emision) }}</span>
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom">
                  <span class="text-caption grey--text">Modalidad:</span>
                  <span class="text-caption">{{ facturaDetalleSeleccionada.tipo_emision === 2 ? 'Contingencia (Fuera de Línea)' : 'Electrónica en Línea' }}</span>
                </div>
                <div class="d-flex justify-space-between py-1 border-bottom">
                  <span class="text-caption grey--text">Sector SIN:</span>
                  <span class="text-caption font-weight-medium">Sector 13 - Servicios Básicos</span>
                </div>
                <div class="py-1">
                  <span class="text-caption grey--text d-block">Código Autorización (CUF):</span>
                  <div class="d-flex align-center justify-space-between bg-light pa-1 rounded mt-1">
                    <span class="text-caption font-mono text-truncate" style="max-width: 280px;" :title="facturaDetalleSeleccionada.cuf">
                      {{ facturaDetalleSeleccionada.cuf }}
                    </span>
                    <v-btn icon x-small @click="copiarCuf(facturaDetalleSeleccionada.cuf)">
                      <v-icon x-small>mdi-content-copy</v-icon>
                    </v-btn>
                  </div>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- DESGLOSE TRIBUTARIO OFICIAL -->
          <v-card outlined rounded="lg" class="pa-3 mb-3 bg-light">
            <div class="text-caption font-weight-bold text-primary text-uppercase mb-3 d-flex align-center">
              <v-icon small color="primary" class="mr-1">mdi-calculator-variant</v-icon> Desglose de Liquidación Fiscal (Bs)
            </div>
            <v-simple-table dense>
              <tbody>
                <tr>
                  <td>Importe Total Venta:</td>
                  <td class="text-right font-weight-bold">Bs {{ Number(facturaDetalleSeleccionada.monto_total || 0).toFixed(2) }}</td>
                </tr>
                <tr>
                  <td class="blue--text text--darken-3 font-weight-medium">(-) Tasas No Sujetas a IVA (Tasa Aseo Urbano):</td>
                  <td class="text-right font-weight-bold blue--text text--darken-3">Bs {{ Number(desgloseFiscalSeleccionado ? desgloseFiscalSeleccionado.monto_tasas : Math.max(0, (facturaDetalleSeleccionada.monto_total || 0) - (facturaDetalleSeleccionada.monto_total_sujeto_iva || 0) - (facturaDetalleSeleccionada.monto_descuento || 0))).toFixed(2) }}</td>
                </tr>
                <tr>
                  <td class="orange--text text--darken-3 font-weight-medium">(-) Descuentos / Beneficios Ley 1886:</td>
                  <td class="text-right font-weight-bold orange--text text--darken-3">Bs {{ Number(facturaDetalleSeleccionada.monto_descuento || 0).toFixed(2) }}</td>
                </tr>
                <tr class="green lighten-5 font-weight-bold">
                  <td class="green--text text--darken-4">(=) Importe Base Débito Fiscal:</td>
                  <td class="text-right font-weight-black green--text text--darken-4">Bs {{ Number(facturaDetalleSeleccionada.monto_total_sujeto_iva || 0).toFixed(2) }}</td>
                </tr>
                <tr class="purple lighten-5 font-weight-bold">
                  <td class="purple--text text--darken-4">(=) Débito Fiscal IVA (13%):</td>
                  <td class="text-right font-weight-black purple--text text--darken-4">Bs {{ Number(desgloseFiscalSeleccionado ? desgloseFiscalSeleccionado.debito_fiscal_iva : ((facturaDetalleSeleccionada.monto_total_sujeto_iva || 0) * 0.13)).toFixed(2) }}</td>
                </tr>
              </tbody>
            </v-simple-table>
          </v-card>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="pa-3 d-flex justify-space-between flex-wrap gap-2">
          <v-btn
            color="teal darken-2"
            dark
            small
            class="rounded-pill font-weight-bold"
            @click="abrirPortalSiatOficial(facturaDetalleSeleccionada)"
          >
            <v-icon left small>mdi-qrcode-scan</v-icon> Verificar en SIAT (SIN)
          </v-btn>

          <div class="d-flex gap-2">
            <v-btn
              color="primary"
              outlined
              small
              class="rounded-pill font-weight-bold"
              @click="imprimirPdf(facturaDetalleSeleccionada, 'carta')"
            >
              <v-icon left small>mdi-printer</v-icon> PDF Carta
            </v-btn>
            <v-btn
              color="primary"
              outlined
              small
              class="rounded-pill font-weight-bold"
              @click="imprimirPdf(facturaDetalleSeleccionada, 'rollo')"
            >
              <v-icon left small>mdi-receipt</v-icon> PDF Rollo
            </v-btn>
            <v-btn
              color="secondary"
              text
              small
              @click="dialogoDetalle = false"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE FACTURA SIAT (CARTA / ROLLO 80MM) / PLANILLA -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
      :mostrar-selector-formato="mostrarSelectorFormatoVisor"
      :formato-inicial="formatoVisor"
      :url-excel="urlExcelReporte"
      :nombre-excel="nombreExcelReporte"
      @cambio-formato="alCambiarFormatoPdf"
    ></modal-visor-pdf>

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
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'BandejaFacturas',
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
      formatoVisor: 'carta',
      mostrarSelectorFormatoVisor: true,
      urlExcelReporte: '',
      nombreExcelReporte: 'Planilla_Facturas_EMAPAP.xlsx',
      exportando: false,
      dialogoExportando: false,
      formatoExportando: 'excel',
      progresoExportacion: 0,
      mensajeProgresoExportacion: '',
      intervaloProgreso: null,
      cargando: false,
      verificandoId: null,
      reenviandoId: null,
      sincronizandoReloj: false,
      dialogoAnular: false,
      dialogoCorreo: false,
      anulando: false,
      enviandoCorreo: false,
      correoDestino: '',
      busqueda: this.$route.query.codigo || this.$route.query.codigo_abonado || this.$route.query.search || '',
      tipoBusqueda: this.$route.query.tipo_busqueda || 'codigo_abonado',
      tiposBusqueda: [
        { texto: 'Código de Abonado', valor: 'codigo_abonado' },
        { texto: 'Todos los campos', valor: 'todos' },
        { texto: 'N° de Factura', valor: 'numero_factura' },
        { texto: 'C.I. / NIT', valor: 'carnet_nit' },
        { texto: 'Razón Social / Nombre', valor: 'cliente' },
      ],
      filtroEstado: 'TODOS',
      filtroSucursal: null,
      filtroPuntoVenta: null,
      filtroFechaInicio: null,
      filtroFechaFin: null,
      sucursales: [],
      totalFacturas: 0,
      opciones: {},
      facturas: [],
      facturaSeleccionada: {},
      motivoAnulacionSeleccionado: 1,
      esAnulacionAdministrativa: false,
      nroResolucion: '',
      fechaResolucion: '',
      revertiendoId: null,
      motivosAnulacion: [
        { codigo: 1, descripcion: 'FACTURA MAL EMITIDA' },
        { codigo: 2, descripcion: 'DATOS DE EMISIÓN INCORRECTOS' },
        { codigo: 3, descripcion: 'FACTURA O NOTA FISCAL DEVUELTA' },
        { codigo: 5, descripcion: 'AUTORIZACIÓN ADMINISTRATIVA / R.A.' },
      ],
      headers: [
        { text: 'N° Factura', value: 'numero_factura', width: '130px' },
        { text: 'Fecha Emisión', value: 'fecha_emision', width: '145px' },
        { text: 'Sucursal / Caja (PV)', value: 'sucursal_punto_venta', width: '180px' },
        { text: 'Razón Social / Cliente', value: 'nombre_razon_social' },
        { text: 'Total Venta', value: 'monto_total', align: 'right', width: '110px' },
        { text: 'Tasas No IVA', value: 'monto_tasas', align: 'right', width: '110px' },
        { text: 'Base Sujeta IVA', value: 'monto_total_sujeto_iva', align: 'right', width: '120px' },
        { text: 'Débito Fiscal (13%)', value: 'debito_fiscal', align: 'right', width: '120px' },
        { text: 'Estado SIN', value: 'estado_factura', align: 'center', width: '120px' },
        { text: 'Acciones', value: 'acciones', align: 'right', sortable: false, width: '170px' },
      ],
      resumenKpis: {
        total_registros: 0,
        cantidad_validas: 0,
        cantidad_contingencias: 0,
        cantidad_anuladas: 0,
        total_facturado: 0,
        total_tasas: 0,
        total_base_debito_fiscal: 0,
        total_debito_fiscal: 0,
      },
      dialogoDetalle: false,
      cargandoDetalle: false,
      facturaDetalleSeleccionada: null,
      desgloseFiscalSeleccionado: null,
      snackbar: {
        status: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle',
      },
      auditoriaCuf: {
        cufs_duplicados: 0,
        integro: true,
        ejemplos: [],
      },
    };
  },
  computed: {
    etiquetaBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Código de Abonado';
        case 'numero_factura': return 'Número de Factura';
        case 'carnet_nit': return 'C.I. / NIT del Cliente';
        case 'cliente': return 'Razón Social / Nombre';
        default: return 'Buscar factura...';
      }
    },
    placeholderBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Ej. 00001, 5105...';
        case 'numero_factura': return 'Ej. 1045, 23010...';
        case 'carnet_nit': return 'Ej. 4582910, 10239401...';
        case 'cliente': return 'Ej. Juan Pérez, Empresa...';
        default: return 'N° Factura, Carnet, Código o Nombre...';
      }
    },
    opcionesSucursales() {
      const lista = [{ id: null, texto: 'Todas las Sucursales' }];
      (this.sucursales || []).forEach(s => {
        lista.push({
          id: s.id,
          texto: `Suc. ${s.codigo_sucursal} - ${s.nombre}`,
          codigo: s.codigo_sucursal,
        });
      });
      return lista;
    },
    opcionesPuntosVenta() {
      const lista = [
        { id: null, texto: 'Todos los Puntos de Venta / Cajas' },
        { id: 'sin_pv', texto: 'Históricas (Sin Punto de Venta)' },
      ];
      if (this.filtroSucursal) {
        const suc = (this.sucursales || []).find(s => s.id === this.filtroSucursal);
        if (suc && suc.puntos_venta) {
          suc.puntos_venta.forEach(pv => {
            lista.push({
              id: pv.id,
              texto: `PV ${pv.codigo_punto_venta}: ${pv.nombre}`,
              codigo: pv.codigo_punto_venta,
            });
          });
        }
      } else {
        (this.sucursales || []).forEach(s => {
          (s.puntos_venta || []).forEach(pv => {
            lista.push({
              id: pv.id,
              texto: `[Suc. ${s.codigo_sucursal}] PV ${pv.codigo_punto_venta}: ${pv.nombre}`,
              codigo: pv.codigo_punto_venta,
            });
          });
        });
      }
      return lista;
    },
  },
  watch: {
    opciones: {
      handler() {
        this.cargarFacturas();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarSucursales();
    this.cargarFacturas();
  },
  methods: {
    async cargarSucursales() {
      try {
        const res = await window.axios.get('/api/facturacion/siat/sucursales');
        if (res.data && res.data.data) {
          this.sucursales = res.data.data;
        }
      } catch (e) {
        console.error('Error al cargar sucursales en bandeja:', e);
      }
    },
    alCambiarSucursal() {
      this.filtroPuntoVenta = null;
      this.cargarFacturas();
    },
    async cargarFacturas() {
      this.cargando = true;
      try {
        const { page, itemsPerPage } = this.opciones;
        const params = {
          page: page || 1,
          per_page: itemsPerPage || 15,
          search: this.busqueda || '',
          tipo_busqueda: this.tipoBusqueda || 'codigo_abonado',
          estado: this.filtroEstado === 'TODOS' ? '' : this.filtroEstado,
          id_sucursal: this.filtroSucursal || '',
          id_punto_venta: this.filtroPuntoVenta || '',
          fecha_inicio: this.filtroFechaInicio || '',
          fecha_fin: this.filtroFechaFin || '',
        };

        const res = await window.axios.get('/api/facturacion/facturas', { params });
        this.facturas = res.data.data;
        this.totalFacturas = res.data.total;
        if (res.data.resumen) {
          this.resumenKpis = res.data.resumen;
        }
        if (res.data.auditoria_cuf) {
          this.auditoriaCuf = res.data.auditoria_cuf;
        }
      } catch (e) {
        console.error('Error al cargar facturas', e);
      } finally {
        this.cargando = false;
      }
    },
    filtrarDuplicadas() {
      this.filtroEstado = 'DUPLICADAS';
      this.cargarFacturas();
    },
    alLimpiarBusqueda() {
      this.busqueda = '';
      this.cargarFacturas();
    },
    establecerRangoRapido(tipo) {
      const hoy = new Date();
      const formatear = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
      };

      if (tipo === 'hoy') {
        const hoyStr = formatear(hoy);
        this.filtroFechaInicio = hoyStr;
        this.filtroFechaFin = hoyStr;
      } else if (tipo === 'semana') {
        const inicioSemana = new Date(hoy);
        const dia = inicioSemana.getDay() || 7;
        inicioSemana.setDate(inicioSemana.getDate() - dia + 1);
        this.filtroFechaInicio = formatear(inicioSemana);
        this.filtroFechaFin = formatear(hoy);
      } else if (tipo === 'mes') {
        const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        this.filtroFechaInicio = formatear(inicioMes);
        this.filtroFechaFin = formatear(hoy);
      } else if (tipo === 'junio_2026') {
        this.filtroFechaInicio = '2026-06-01';
        this.filtroFechaFin = '2026-06-30';
      } else if (tipo === 'julio_2026') {
        this.filtroFechaInicio = '2026-07-01';
        this.filtroFechaFin = '2026-07-31';
      } else if (tipo === 'gestion') {
        const inicioGestion = new Date(hoy.getFullYear(), 0, 1);
        this.filtroFechaInicio = formatear(inicioGestion);
        this.filtroFechaFin = formatear(hoy);
      }
      this.cargarFacturas();
    },
    limpiarFiltros() {
      this.busqueda = '';
      this.tipoBusqueda = 'codigo_abonado';
      this.filtroEstado = 'TODOS';
      this.filtroSucursal = null;
      this.filtroPuntoVenta = null;
      this.filtroFechaInicio = null;
      this.filtroFechaFin = null;
      this.cargarFacturas();
    },
    exportarReporte(formato) {
      const params = new URLSearchParams();
      if (this.busqueda) params.append('search', this.busqueda);
      params.append('tipo_busqueda', this.tipoBusqueda || 'codigo_abonado');
      if (this.filtroEstado && this.filtroEstado !== 'TODOS') params.append('estado', this.filtroEstado);
      if (this.filtroSucursal) params.append('id_sucursal', this.filtroSucursal);
      if (this.filtroPuntoVenta) params.append('id_punto_venta', this.filtroPuntoVenta);
      if (this.filtroFechaInicio) params.append('fecha_inicio', this.filtroFechaInicio);
      if (this.filtroFechaFin) params.append('fecha_fin', this.filtroFechaFin);

      const q = params.toString();
      const hoy = new Date().toISOString().slice(0, 10);

      if (formato === 'pdf') {
        this.iniciarProgresoExportacion('pdf', 'Preparando documento fiscal oficial en PDF...');
        this.facturaVisorId = null;
        this.mostrarSelectorFormatoVisor = false;
        this.urlExcelReporte = `/api/facturacion/facturas/exportar-excel?formato=xlsx&${q}`;
        this.nombreExcelReporte = `Planilla_Facturas_EMAPAP_${hoy}.xlsx`;
        this.urlVisorPdf = `/api/facturacion/facturas/exportar-pdf?${q}`;
        this.tituloVisorPdf = 'Planilla Oficial de Facturas Emitidas';
        this.subtituloVisorPdf = 'Visualización e impresión oficial de documentos fiscales';

        this.avanzarProgreso(30, 'Consultando base de datos y consolidando totales...');
        setTimeout(() => {
          this.avanzarProgreso(75, 'Renderizando maquetación apaisada de alta resolución...');
          setTimeout(() => {
            this.avanzarProgreso(100, '¡Planilla generada con éxito!');
            setTimeout(() => {
              this.finalizarProgreso();
              this.mostrarVisorPdf = true;
            }, 300);
          }, 450);
        }, 350);
      } else if (formato === 'excel') {
        this.descargarArchivoExcel(q, 'xlsx');
      } else if (formato === 'csv') {
        this.descargarArchivoExcel(q, 'csv');
      }
    },
    iniciarProgresoExportacion(formato, mensajeInicial) {
      if (this.intervaloProgreso) clearInterval(this.intervaloProgreso);
      this.formatoExportando = formato;
      this.progresoExportacion = 10;
      this.mensajeProgresoExportacion = mensajeInicial;
      this.dialogoExportando = true;
    },
    avanzarProgreso(pct, mensaje) {
      this.progresoExportacion = pct;
      if (mensaje) this.mensajeProgresoExportacion = mensaje;
    },
    finalizarProgreso() {
      if (this.intervaloProgreso) {
        clearInterval(this.intervaloProgreso);
        this.intervaloProgreso = null;
      }
      this.dialogoExportando = false;
      this.progresoExportacion = 0;
    },
    async descargarArchivoExcel(q, tipo = 'xlsx') {
      const extension = tipo === 'xlsx' ? 'xlsx' : 'csv';
      const mensajeTipo = tipo === 'xlsx' ? 'Excel Profesional (.xlsx)' : 'CSV Masivo (.csv)';
      this.iniciarProgresoExportacion(
        tipo,
        `Iniciando generación de planilla ${mensajeTipo}...`
      );

      // Simular progreso activo durante el procesamiento del servidor
      let paso = 15;
      this.intervaloProgreso = setInterval(() => {
        if (paso < 85) {
          paso += 12;
          let msg = 'Recopilando registros fiscales y aplicando filtros...';
          if (paso >= 40 && paso < 70) {
            msg = tipo === 'xlsx'
              ? 'Aplicando cuadrícula profesional, colores EMAPAP y fórmulas...'
              : 'Estructurando registros con codificación UTF-8...';
          } else if (paso >= 70) {
            msg = 'Empaquetando archivo y transmitiendo datos...';
          }
          this.avanzarProgreso(paso, msg);
        }
      }, 300);

      try {
        const url = `/api/facturacion/facturas/exportar-excel?formato=${tipo}&${q}`;
        const res = await window.axios.get(url, {
          responseType: 'blob',
          onDownloadProgress: (progressEvent) => {
            if (progressEvent.total) {
              const porcentaje = Math.round((progressEvent.loaded * 100) / progressEvent.total);
              if (porcentaje > 85) {
                this.avanzarProgreso(Math.min(porcentaje, 99), 'Descargando datos recibidos...');
              }
            }
          },
        });

        this.avanzarProgreso(100, `¡Planilla ${extension.toUpperCase()} generada exitosamente!`);

        const mime = tipo === 'xlsx'
          ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
          : 'text/csv;charset=utf-8;';
        const blob = new Blob([res.data], { type: mime });
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.style.display = 'none';
        link.href = blobUrl;
        const hoy = new Date().toISOString().slice(0, 10);
        link.setAttribute('download', `Planilla_Facturas_EMAPAP_${hoy}.${extension}`);
        document.body.appendChild(link);
        link.click();

        setTimeout(() => {
          document.body.removeChild(link);
          URL.revokeObjectURL(blobUrl);
          this.finalizarProgreso();
          this.mostrarNotificacion(`Planilla ${extension.toUpperCase()} descargada exitosamente`);
        }, 500);
      } catch (err) {
        console.error('Error al exportar archivo:', err);
        this.finalizarProgreso();
        this.mostrarNotificacion(`No se pudo generar el archivo ${extension.toUpperCase()}`, 'error', 'mdi-alert-circle');
      }
    },
    formatearFecha(fechaStr) {
      if (!fechaStr) return '-';
      try {
        if (typeof fechaStr === 'string' && /^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/.test(fechaStr)) {
          const limpia = fechaStr.replace('T', ' ');
          const [fecha, horaCompleta] = limpia.split(' ');
          const [y, m, d] = fecha.split('-');
          const hora = horaCompleta.slice(0, 8);
          return `${d}/${m}/${y} ${hora}`;
        }
        const d = new Date(fechaStr);
        if (!isNaN(d.getTime())) {
          return new Intl.DateTimeFormat('es-BO', {
            timeZone: 'America/La_Paz',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
          }).format(d);
        }
      } catch (e) {
        console.error('Error al formatear fecha:', e);
      }
      return String(fechaStr);
    },
    colorEstado(estado) {
      switch (estado) {
        case 'VALIDADA':
        case 'VALIDATED':
          return 'success';
        case 'ANULADA':
        case 'CANCELLED':
          return 'error';
        case 'CONTINGENCIA':
        case 'CONTINGENCY':
        case 'OFFLINE':
          return 'warning';
        case 'PENDIENTE':
        case 'PENDING':
          return 'info';
        case 'RECHAZADA':
        case 'REJECTED':
        case 'OBSERVADA':
          return 'deep-orange';
        default:
          return 'grey';
      }
    },
    mostrarNotificacion(texto, color = 'success', icon = 'mdi-check-circle') {
      this.snackbar = {
        status: true,
        text: texto,
        color: color,
        icon: icon,
      };
    },
    imprimirPdf(item, formato = 'carta') {
      const id = typeof item === 'object' ? item.id : item;
      const numFactura = (typeof item === 'object' && item.numero_factura) ? item.numero_factura : id;
      this.facturaVisorId = id;
      this.formatoVisor = formato;
      this.mostrarSelectorFormatoVisor = true;
      this.urlExcelReporte = '';
      this.urlVisorPdf = `/api/facturacion/facturas/${id}/pdf?formato=${formato}`;
      this.tituloVisorPdf = `Factura Electrónica SIAT N° ${numFactura}`;
      this.subtituloVisorPdf = 'Visualización e Impresión de Documento Fiscal Autorizado';
      this.mostrarVisorPdf = true;
    },
    alCambiarFormatoPdf(nuevoFormato) {
      if (this.facturaVisorId) {
        this.urlVisorPdf = `/api/facturacion/facturas/${this.facturaVisorId}/pdf?formato=${nuevoFormato}`;
      }
    },
    async verificarEstadoEnSin(factura) {
      this.verificandoId = factura.id;
      try {
        const res = await window.axios.get(`/api/facturacion/facturas/${factura.id}/verificar-estado-sin`);
        if (res.data && res.data.success) {
          const sinDesc = (res.data.siat && res.data.siat.codigoDescripcion) || res.data.estado_local;
          this.mostrarNotificacion(`SIAT en Línea: Factura N° ${factura.numero_factura} ${sinDesc}`, 'success', 'mdi-check-decagram');
          this.cargarFacturas();
        } else {
          const msg = (res.data && res.data.message) || 'El SIN no pudo validar el estado de la factura.';
          this.mostrarNotificacion(`SIAT: ${msg}`, 'warning', 'mdi-alert');
          this.cargarFacturas();
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error de comunicación con SIAT: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.verificandoId = null;
      }
    },
    async reenviarFacturaAlSiat(factura) {
      this.reenviandoId = factura.id;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${factura.id}/enviar-siat`);
        if (res.data && res.data.success) {
          this.mostrarNotificacion(
            `SIAT: Factura N° ${factura.numero_factura} enviada y VALIDADA exitosamente ante el SIN.`,
            'success',
            'mdi-check-decagram'
          );
          this.cargarFacturas();
        } else {
          const msg = (res.data && res.data.message) || 'No se pudo regularizar la factura ante el SIN.';
          this.mostrarNotificacion(`SIAT: ${msg}`, 'warning', 'mdi-alert');
          this.cargarFacturas();
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error al reenviar a SIAT: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.reenviandoId = null;
      }
    },
    abrirPortalSiatOficial(item) {
      const nit = '1002393029';
      const cuf = item.cuf;
      const numero = item.numero_factura;
      const url = `https://siat.impuestos.gob.bo/consulta/QR?nit=${nit}&cuf=${cuf}&numero=${numero}&t=2`;
      window.open(url, '_blank');
    },
    async sincronizarRelojSiat() {
      this.sincronizandoReloj = true;
      try {
        const res = await window.axios.get('/api/facturacion/siat/sincronizar-hora');
        if (res.data && res.data.success) {
          const dif = res.data.diferencia_segundos !== undefined ? ` (Desfase: ${res.data.diferencia_segundos}s)` : '';
          this.mostrarNotificacion(`Hora oficial SIN: ${res.data.fecha_hora_sin || res.data.fecha_hora}${dif}`, 'success', 'mdi-clock-check');
        } else {
          const msg = (res.data && res.data.message) || 'Error al sincronizar reloj con SIAT';
          this.mostrarNotificacion(msg, 'warning', 'mdi-clock-alert');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(`Error al sincronizar: ${msg}`, 'error', 'mdi-alert-circle');
      } finally {
        this.sincronizandoReloj = false;
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
    abrirDialogoAnulacion(factura) {
      this.facturaSeleccionada = factura;
      this.motivoAnulacionSeleccionado = factura.es_duplicada ? 4 : 1;
      this.esAnulacionAdministrativa = false;
      this.nroResolucion = '';
      this.fechaResolucion = '';
      this.dialogoAnular = true;
    },
    async confirmarAnulacion() {
      this.anulando = true;
      try {
        let res;
        if (this.esAnulacionAdministrativa) {
          res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/anular-administrativa`, {
            codigo_motivo: this.motivoAnulacionSeleccionado,
            nro_resolucion: this.nroResolucion,
            fecha_resolucion: this.fechaResolucion,
          });
        } else {
          res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/anular`, {
            codigo_motivo: this.motivoAnulacionSeleccionado,
          });
        }

        if (res.data.success) {
          const msg = res.data.message || 'Factura anulada satisfactoriamente.';
          this.mostrarNotificacion(msg, 'success', 'mdi-check-circle');
          this.dialogoAnular = false;
          this.cargarFacturas();
        } else {
          const msg = res.data.message || 'Error al anular la factura';
          this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
      } finally {
        this.anulando = false;
      }
    },
    async confirmarReversionAnulacion(factura) {
      this.revertiendoId = factura.id;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${factura.id}/revertir-anulacion`);
        if (res.data.success) {
          const msg = res.data.message || 'Factura restituida a estado VALIDADA.';
          this.mostrarNotificacion(msg, 'success', 'mdi-check-circle');
          this.cargarFacturas();
        } else {
          this.mostrarNotificacion(res.data.message || 'Error al revertir anulación.', 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        this.mostrarNotificacion(e.response?.data?.message || 'Error al procesar la reversión.', 'error', 'mdi-alert-circle');
      } finally {
        this.revertiendoId = null;
      }
    },
    abrirDialogoCorreo(factura) {
      this.facturaSeleccionada = factura;
      this.correoDestino = (factura.cliente && factura.cliente.correo_electronico) || '';
      this.dialogoCorreo = true;
    },
    async confirmarEnvioCorreo() {
      if (!this.correoDestino) {
        this.mostrarNotificacion('Ingrese un correo electrónico válido', 'warning', 'mdi-alert');
        return;
      }
      this.enviandoCorreo = true;
      try {
        const res = await window.axios.post(`/api/facturacion/facturas/${this.facturaSeleccionada.id}/enviar-correo`, {
          correo_electronico: this.correoDestino,
        });
        if (res.data && res.data.success) {
          this.mostrarNotificacion('Factura fiscal enviada exitosamente por correo.', 'success', 'mdi-email-check');
          this.dialogoCorreo = false;
        } else {
          this.mostrarNotificacion(res.data.message || 'Error al enviar correo', 'error', 'mdi-alert-circle');
        }
      } catch (e) {
        const msg = e.response && e.response.data ? e.response.data.message : e.message;
        this.mostrarNotificacion(msg, 'error', 'mdi-alert-circle');
      } finally {
        this.enviandoCorreo = false;
      }
    },
    async abrirModalDetalle(item) {
      this.facturaDetalleSeleccionada = item;
      this.desgloseFiscalSeleccionado = null;
      this.dialogoDetalle = true;
      this.cargandoDetalle = true;
      try {
        const res = await window.axios.get(`/api/facturacion/facturas/${item.id}`);
        if (res.data && res.data.success) {
          this.facturaDetalleSeleccionada = res.data.data;
          this.desgloseFiscalSeleccionado = res.data.desglose_fiscal;
        }
      } catch (err) {
        console.error('Error al cargar detalle de factura:', err);
      } finally {
        this.cargandoDetalle = false;
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
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
</style>
