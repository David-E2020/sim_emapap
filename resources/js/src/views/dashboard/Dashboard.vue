<template>
  <v-row>
    <v-col cols="12">
      <v-card class="pa-5 text-center">
        <v-card-title class="justify-center text-h4 font-weight-bold primary--text">
          Sistema Municipal de Cobranzas de Agua
        </v-card-title>
        <v-card-subtitle class="text-subtitle-1 mt-2">
          Bienvenido al Panel Principal de Administración
        </v-card-subtitle>
        <v-card-text class="mt-4">
          <v-row justify="center">
            <v-col cols="12" md="4">
              <v-card outlined class="pa-4">
                <v-icon size="48" color="primary">mdi-water-pump</v-icon>
                <h3 class="mt-2">Agua Potable</h3>
                <p class="text-caption">Gestión de Usuarios y Lecturas</p>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card outlined class="pa-4">
                <v-icon size="48" color="success">mdi-receipt</v-icon>
                <h3 class="mt-2">Facturación SIN</h3>
                <p class="text-caption">Facturación en Línea SIAT</p>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card outlined class="pa-4">
                <v-icon size="48" color="warning">mdi-cash-register</v-icon>
                <h3 class="mt-2">Cobranzas & Cajas</h3>
                <p class="text-caption">Ventanilla de Pagos y Recaudación</p>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {}
  }
}
</script>
