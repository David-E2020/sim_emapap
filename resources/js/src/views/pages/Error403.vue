<template>
  <div class="d-flex align-center justify-center min-vh-100 pa-4 text-center">
    <v-card max-width="540" class="pa-8 elevation-4 rounded-xl">
      <div class="mb-4">
        <v-avatar size="96" color="error lighten-5">
          <v-icon size="64" color="error">mdi-shield-lock-outline</v-icon>
        </v-avatar>
      </div>

      <h1 class="text-h3 font-weight-bold error--text mb-2">403</h1>
      <h2 class="text-h5 font-weight-bold mb-3">Acceso Restringido</h2>

      <v-alert
        type="warning"
        dense
        outlined
        border="left"
        class="text-left text-body-2 mb-5 mx-2"
      >
        Su usuario no cuenta con los permisos o rol requeridos para ingresar a este módulo institucional de <strong>EMAPAP</strong>.
      </v-alert>

      <p class="text-body-2 text-secondary mb-6 px-4">
        La política de seguridad y segregación de funciones restringe la visualización y manipulación de datos únicamente al personal autorizado.
        Si necesita acceso a este módulo para sus funciones laborales, comuníquese con el <strong>Administrador General del Sistema</strong> o con su jefatura de área.
      </p>

      <div class="d-flex justify-center gap-3">
        <v-btn
          color="primary"
          rounded
          large
          depressed
          :to="{ name: 'dashboard' }"
          class="text-capitalize px-6"
        >
          <v-icon left>mdi-home-variant</v-icon> Volver al Dashboard
        </v-btn>
      </div>
    </v-card>
  </div>
</template>

<script>
export default {
  name: 'Error403',
};
</script>

<style scoped>
.min-vh-100 {
  min-height: 100vh;
  background-color: #f4f5fa;
}
</style>
