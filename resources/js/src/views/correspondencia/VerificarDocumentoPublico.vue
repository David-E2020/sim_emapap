<template>
  <v-app class="bg-slate-50">
    <v-main>
      <v-container class="py-10 fill-height d-flex justify-center align-center">
        <v-card rounded="xl" max-width="650px" width="100%" class="elevation-4 pa-6">
          <div class="text-center mb-6">
            <img src="/images/logoEmapa2.png" alt="EMAPA" style="max-height: 70px;" class="mb-2">
            <h2 class="text-h5 font-weight-black text-slate-800">EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS</h2>
            <div class="text-caption text-secondary font-weight-bold">PORTAL DE VALIDACIÓN Y AUTENTICIDAD DE DOCUMENTOS OFICIALES</div>
          </div>

          <!-- BÚSQUEDA -->
          <div class="d-flex mb-6 gap-2">
            <v-text-field
              v-model="tokenBusqueda"
              label="Código de Verificación o CITE"
              outlined
              dense
              hide-details
              placeholder="Ej: ABC12345 o EMAPA/GG/INF/001/2026"
              @keyup.enter="verificar"
            ></v-text-field>
            <v-btn color="primary" class="rounded-pill px-5 text-capitalize" :loading="cargando" @click="verificar">
              Validar
            </v-btn>
          </div>

          <!-- RESULTADO DE VALIDACIÓN -->
          <div v-if="resultado && resultado.valido">
            <v-alert type="success" text class="rounded-lg mb-4">
              <div class="font-weight-bold text-subtitle-1">✓ DOCUMENTO OFICIAL AUTÉNTICO EMAPA</div>
              <div class="text-caption">El documento ha sido verificado con éxito en la base de datos central de EMAPA.</div>
            </v-alert>

            <v-simple-table dense class="mb-4">
              <template v-slot:default>
                <tbody>
                  <tr>
                    <td class="font-weight-bold text-secondary" style="width: 35%;">CITE Oficial:</td>
                    <td class="font-weight-black primary--text">{{ resultado.data.cite }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-secondary">Tipo de Documento:</td>
                    <td>{{ resultado.data.tipo_documento }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-secondary">Asunto / Referencia:</td>
                    <td class="font-weight-bold">{{ resultado.data.asunto }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-secondary">Unidad Emisora:</td>
                    <td>{{ resultado.data.unidad_emisora }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-secondary">Fecha de Emisión:</td>
                    <td>{{ resultado.data.fecha_emision }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-secondary">Estado Actual:</td>
                    <td>
                      <v-chip small label color="green lighten-4" text-color="green darken-4" class="font-weight-bold">
                        {{ resultado.data.estado }}
                      </v-chip>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>

            <h4 class="text-subtitle-2 font-weight-bold mb-2 primary--text">Firmas y Rúbricas Electrónicas Registradas:</h4>
            <v-card outlined rounded="lg" class="pa-3 mb-2" v-for="(f, i) in resultado.data.firmantes" :key="i">
              <div class="d-flex justify-space-between align-center">
                <strong>{{ f.funcionario }} (CI: {{ f.ci }})</strong>
                <v-chip x-small color="success" label>FIRMADO</v-chip>
              </div>
              <div class="text-caption text-secondary mt-1">
                <div>Fecha/Hora: {{ f.fecha_firma }} | Tipo: {{ f.tipo_firma }}</div>
                <div style="font-family: monospace; font-size: 10px;">Hash SHA-256: {{ f.hash_sha256 }}</div>
              </div>
            </v-card>
          </div>

          <v-alert v-else-if="errorMsg" type="error" text class="rounded-lg">
            <div class="font-weight-bold">DOCUMENTO NO ENCONTRADO O INVÁLIDO</div>
            <div class="text-caption">{{ errorMsg }}</div>
          </v-alert>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: 'VerificarDocumentoPublico',
  data() {
    return {
      tokenBusqueda: '',
      resultado: null,
      errorMsg: '',
      cargando: false,
    };
  },
  mounted() {
    if (this.$route.params.codigo) {
      this.tokenBusqueda = this.$route.params.codigo;
      this.verificar();
    } else if (this.$route.query.cite) {
      this.tokenBusqueda = this.$route.query.cite;
      this.verificar();
    }
  },
  methods: {
    async verificar() {
      if (!this.tokenBusqueda) return;
      this.cargando = true;
      this.errorMsg = '';
      this.resultado = null;
      try {
        const res = await window.axios.get(`/api/correspondencia/publico/verificar-documento/${encodeURIComponent(this.tokenBusqueda.trim())}`);
        if (res.data && res.data.success) {
          this.resultado = res.data;
        } else {
          this.errorMsg = res.data.message || 'Código no válido.';
        }
      } catch (e) {
        this.errorMsg = e.response?.data?.message || 'El documento no fue encontrado en los registros de EMAPA.';
      } finally {
        this.cargando = false;
      }
    },
  },
};
</script>
