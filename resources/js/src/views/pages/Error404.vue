<template>
  <div class="d-flex align-center justify-center min-vh-100 pa-4 text-center">
    <v-card max-width="500" class="pa-8 elevation-3 rounded-xl">
      <div class="mb-4">
        <v-icon size="80" color="primary">mdi-compass-off-outline</v-icon>
      </div>
      <h1 class="text-h3 font-weight-bold primary--text mb-2">404</h1>
      <h2 class="text-h6 font-weight-medium mb-3">Página no encontrada</h2>
      <p class="text-body-2 text-secondary mb-6">
        La ruta solicitada no existe o ha sido reubicada. Utilice el menú lateral de navegación para acceder a los módulos activos del sistema.
      </p>
      <v-btn color="primary" rounded large depressed :to="{ name: 'dashboard' }" class="text-capitalize px-6">
        <v-icon left>mdi-home</v-icon> Volver al Dashboard
      </v-btn>
    </v-card>
  </div>
</template>

<script>
export default {
  name: 'Error404',
};
</script>

<style scoped>
.min-vh-100 {
  min-height: 100vh;
  background-color: #f4f5fa;
}
</style>
