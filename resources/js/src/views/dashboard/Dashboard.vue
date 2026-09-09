<template>
  <div>
    <!-- BIENVENIDA Y CABECERA -->
    <v-card class="mb-6 py-5 px-6 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-4 text-white elevation-3" size="56">
            <v-icon color="white" large>mdi-water-pump</v-icon>
          </v-avatar>
          <div>
            <h1 class="text-h4 font-weight-bold mb-1 primary--text">EMAPAP - Sistema Integral de Agua Potable</h1>
            <span class="text-subtitle-2 text-secondary">
              Gestión Comercial, Facturación Electrónica en Línea SIAT y Control de Cajas Recaudadoras
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-3 mt-md-0">
          <v-btn
            color="teal darken-2"
            class="rounded-pill font-weight-bold text-white elevation-2 px-5"
            large
            :to="{ name: 'comercial_caja' }"
          >
            <v-icon left>mdi-cash-register</v-icon> Abrir Ventanilla de Cobranzas
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- ACCESOS DIRECTOS PRINCIPALES -->
    <v-row dense class="mb-6">
      <!-- 1. CAJA Y COBRANZAS -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'comercial_caja' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="teal lighten-5" rounded="lg" size="48">
                <v-icon color="teal darken-2" size="28">mdi-cash-register</v-icon>
              </v-avatar>
              <v-chip color="teal darken-2" outlined small class="font-weight-bold">Cobros SIAT</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Caja y Cobranzas</h3>
            <p class="text-caption text-secondary mb-0">
              Apertura y cierre de turnos, arqueos en vivo, cobro de planillas mensuales con factura electrónica y recibos.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption teal--text text--darken-2 font-weight-bold">
            <span>Ingresar a Ventanilla</span>
            <v-icon x-small color="teal darken-2" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>

      <!-- 2. PADRÓN DE ABONADOS -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'comercial_abonados' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="primary lighten-5" rounded="lg" size="48">
                <v-icon color="primary" size="28">mdi-account-group</v-icon>
              </v-avatar>
              <v-chip color="primary" outlined small class="font-weight-bold">Abonados</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Padrón de Abonados</h3>
            <p class="text-caption text-secondary mb-0">
              Registro de conexiones, micromedición, cambio de medidores, extractos de cuenta históricos y control de mora.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption primary--text font-weight-bold">
            <span>Gestionar Padrón</span>
            <v-icon x-small color="primary" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>

      <!-- 3. BANDEJA DE FACTURAS SIAT -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'facturacion_bandeja' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="blue lighten-5" rounded="lg" size="48">
                <v-icon color="blue darken-2" size="28">mdi-file-table-box-outline</v-icon>
              </v-avatar>
              <v-chip color="blue darken-2" outlined small class="font-weight-bold">SIAT en Línea</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Bandeja de Facturas</h3>
            <p class="text-caption text-secondary mb-0">
              Búsqueda por factura/CI/código, reimpresión térmica y carta, anulación ante el SIN y estado de emisión.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption blue--text text--darken-2 font-weight-bold">
            <span>Ver Facturas Emitidas</span>
            <v-icon x-small color="blue darken-2" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>

      <!-- 4. RECAUDACIÓN Y REPORTES CONSOLIDADOS -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'comercial_reportes' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="indigo lighten-5" rounded="lg" size="48">
                <v-icon color="indigo darken-2" size="28">mdi-chart-box-outline</v-icon>
              </v-avatar>
              <v-chip color="indigo darken-2" outlined small class="font-weight-bold">Financiero</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Recaudación y Reportes</h3>
            <p class="text-caption text-secondary mb-0">
              Consolidado por día, semana, mes, caja y cajero. Desglose por rubros contables, exportación PDF y CSV oficial.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption indigo--text text--darken-2 font-weight-bold">
            <span>Ver Reportes de Recaudación</span>
            <v-icon x-small color="indigo darken-2" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>

      <!-- 5. HISTORIAL DE TURNOS Y ARQUEOS -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'comercial_sesiones_caja' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="purple lighten-5" rounded="lg" size="48">
                <v-icon color="purple darken-2" size="28">mdi-history</v-icon>
              </v-avatar>
              <v-chip color="purple darken-2" outlined small class="font-weight-bold">Auditoría</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Turnos y Arqueos de Caja</h3>
            <p class="text-caption text-secondary mb-0">
              Supervisión de sesiones operadas por cajero, cuadres físicos, faltantes, sobrantes e impresión de planillas de arqueo.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption purple--text text--darken-2 font-weight-bold">
            <span>Auditar Sesiones de Caja</span>
            <v-icon x-small color="purple darken-2" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>

      <!-- 6. PUNTOS DE VENTA Y SUCURSALES -->
      <v-col cols="12" sm="6" md="4">
        <v-card
          outlined
          rounded="lg"
          class="pa-5 h-100 hover-elevation-card cursor-pointer d-flex flex-column justify-space-between"
          :to="{ name: 'facturacion_puntos_venta' }"
        >
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <v-avatar color="amber lighten-5" rounded="lg" size="48">
                <v-icon color="amber darken-3" size="28">mdi-store-cog-outline</v-icon>
              </v-avatar>
              <v-chip color="amber darken-3" outlined small class="font-weight-bold">Configuración</v-chip>
            </div>
            <h3 class="text-h6 font-weight-bold mb-1">Sucursales y Cajas Físicas</h3>
            <p class="text-caption text-secondary mb-0">
              Asignación de cajeros por ventanilla, sincronización de CUIS / CUFD ante el SIN y estado operativo en tiempo real.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top d-flex align-center text-caption amber--text text--darken-4 font-weight-bold">
            <span>Administrar Ventanillas</span>
            <v-icon x-small color="amber darken-3" class="ml-1">mdi-arrow-right</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
};
</script>

<style scoped>
.hover-elevation-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-color: rgba(15, 23, 42, 0.08) !important;
}
.hover-elevation-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
  border-color: #00897b !important;
}
.cursor-pointer {
  cursor: pointer;
}
.border-top {
  border-top: 1px solid rgba(15, 23, 42, 0.08);
}

.theme--dark .hover-elevation-card {
  border-color: rgba(255, 255, 255, 0.08) !important;
}

.theme--dark .border-top {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
</style>
