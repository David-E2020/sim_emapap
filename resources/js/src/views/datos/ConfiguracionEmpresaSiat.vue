<template>
  <div class="config-empresa-siat-container">
    <!-- CABECERA INSTITUCIONAL Y ESTADOS -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-shield-crown-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Configuración Empresa y SIAT</h2>
            <span class="text-caption text-secondary">
              Parámetros institucionales, credenciales SIAT v2 y certificado digital para facturación electrónica
            </span>
          </div>
        </div>

        <div class="d-flex align-center flex-wrap gap-2 mt-3 mt-md-0">
          <!-- CHIP DE AMBIENTE -->
          <v-chip
            :color="form.codigo_ambiente === 1 ? 'success' : 'warning'"
            text-color="white"
            class="font-weight-bold mr-2"
            small
          >
            <v-icon left x-small>
              {{ form.codigo_ambiente === 1 ? 'mdi-check-decagram' : 'mdi-flask-outline' }}
            </v-icon>
            {{ form.codigo_ambiente === 1 ? 'Ambiente: 1 - Producción' : 'Ambiente: 2 - Pruebas / Piloto' }}
          </v-chip>

          <!-- CHIP DE MODALIDAD -->
          <v-chip
            :color="form.codigo_modalidad === 1 ? 'primary' : 'info'"
            text-color="white"
            class="font-weight-bold mr-2"
            small
          >
            <v-icon left x-small>mdi-laptop</v-icon>
            {{ form.codigo_modalidad === 1 ? 'Electrónica en Línea' : 'Computarizada en Línea' }}
          </v-chip>

          <!-- BOTÓN PROBAR CONEXIÓN SIAT -->
          <v-btn
            color="indigo darken-1"
            dark
            class="text-capitalize font-weight-medium rounded-pill"
            :loading="testingConnection"
            @click="probarConexionSiat()"
            small
          >
            <v-icon left small>mdi-wifi-check</v-icon>
            Probar Conexión SIN
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- FORMULARIO PRINCIPAL EN TABS -->
    <v-card elevation="2" rounded="lg">
      <v-tabs
        v-model="activeTab"
        background-color="transparent"
        color="primary"
        slider-color="primary"
        class="border-bottom"
      >
        <v-tab class="text-capitalize font-weight-bold">
          <v-icon left small>mdi-office-building-outline</v-icon>
          Identidad Institucional
        </v-tab>
        <v-tab class="text-capitalize font-weight-bold">
          <v-icon left small>mdi-cloud-key-outline</v-icon>
          Parámetros SIAT / SIN
        </v-tab>
        <v-tab class="text-capitalize font-weight-bold">
          <v-icon left small>mdi-certificate-outline</v-icon>
          Firma Digital ADSIB (.p12)
        </v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-card-text class="pa-6">
        <v-form ref="configForm" v-model="validForm" @submit.prevent="guardarConfiguracion">
          <v-tabs-items v-model="activeTab">
            <!-- TAB 1: IDENTIDAD INSTITUCIONAL -->
            <v-tab-item>
              <v-row>
                <v-col cols="12" md="8">
                  <v-row dense>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.razon_social"
                        label="Razón Social *"
                        outlined
                        dense
                        prepend-inner-icon="mdi-domain"
                        :rules="[v => !!v || 'La razón social es requerida']"
                        hint="Nombre legal registrado ante Impuestos Nacionales"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.nombre_comercial"
                        label="Nombre Comercial / Sistema"
                        outlined
                        dense
                        prepend-inner-icon="mdi-storefront-outline"
                        hint="Nombre visible en membretes o portal web"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.nit"
                        label="NIT Institucional *"
                        outlined
                        dense
                        prepend-inner-icon="mdi-numeric"
                        :rules="[v => !!v || 'El NIT es requerido']"
                        hint="Número de Identificación Tributaria del emisor"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.telefono"
                        label="Teléfono / Contacto"
                        outlined
                        dense
                        prepend-inner-icon="mdi-phone-outline"
                        hint="Teléfono impreso en la factura"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.correo"
                        label="Correo Electrónico de Facturación"
                        outlined
                        dense
                        prepend-inner-icon="mdi-email-outline"
                        hint="Correo oficial de contacto tributario"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="form.municipio"
                        label="Municipio *"
                        outlined
                        dense
                        prepend-inner-icon="mdi-map-marker-outline"
                        :rules="[v => !!v || 'El municipio es requerido']"
                        hint="Ej: Patacamaya, La Paz, Bolivia"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <v-col cols="12">
                      <v-textarea
                        v-model="form.direccion"
                        label="Dirección Principal *"
                        outlined
                        dense
                        rows="2"
                        prepend-inner-icon="mdi-map-marker-radius-outline"
                        :rules="[v => !!v || 'La dirección es requerida']"
                        hint="Dirección de la casa matriz que figurará en el pie de factura"
                        persistent-hint
                      ></v-textarea>
                    </v-col>
                  </v-row>
                </v-col>

                <!-- COLUMNA DERECHA: LOGOTIPO -->
                <v-col cols="12" md="4" class="text-center">
                  <v-card outlined rounded="lg" class="pa-4 bg-light">
                    <div class="text-subtitle-2 font-weight-bold mb-3 text-secondary">
                      Logotipo Institucional
                    </div>
                    
                    <div class="logo-preview-box mb-3 d-flex align-center justify-center">
                      <v-img
                        v-if="logoPreviewUrl || form.logo_url"
                        :src="logoPreviewUrl || form.logo_url"
                        max-height="140"
                        contain
                        alt="Logo Institucional"
                      ></v-img>
                      <div v-else class="text-muted text-caption py-8">
                        <v-icon size="48" color="grey lighten-1">mdi-image-outline</v-icon>
                        <div>Sin logotipo asignado</div>
                      </div>
                    </div>

                    <v-file-input
                      v-model="archivoLogo"
                      accept="image/*"
                      label="Seleccionar nuevo logo"
                      outlined
                      dense
                      show-size
                      prepend-icon=""
                      prepend-inner-icon="mdi-camera"
                      @change="onLogoSelected"
                      hint="Formatos: PNG, JPG, WEBP (Máx. 2MB)"
                      persistent-hint
                    ></v-file-input>
                  </v-card>
                </v-col>
              </v-row>
            </v-tab-item>

            <!-- TAB 2: PARÁMETROS SIAT / SIN -->
            <v-tab-item>
              <v-alert
                border="left"
                colored-border
                color="info"
                elevation="1"
                class="mb-4"
                dense
              >
                <div class="text-body-2">
                  <strong>Aviso SIAT:</strong> Los cambios en el <strong>Ambiente</strong> o <strong>Código de Sistema</strong>
                  afectan directamente la comunicación SOAP con el SIN para la sincronización de catálogos, obtención de CUFD
                  y emisión de facturas oficiales.
                </div>
              </v-alert>

              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-card outlined rounded="lg" class="pa-4 mb-3">
                    <div class="text-subtitle-2 font-weight-bold mb-2">
                      <v-icon small color="primary">mdi-lan</v-icon> Ambiente de Conexión SIN *
                    </div>
                    <v-radio-group v-model="form.codigo_ambiente" row :rules="[v => !!v || 'Seleccione un ambiente']">
                      <v-radio
                        label="1 - Producción"
                        :value="1"
                        color="success"
                      ></v-radio>
                      <v-radio
                        label="2 - Pruebas / Piloto"
                        :value="2"
                        color="warning"
                      ></v-radio>
                    </v-radio-group>
                    <span class="text-caption text-muted">
                      {{ form.codigo_ambiente === 1
                        ? 'En producción (siat), las facturas emitidas son fiscalmente válidas ante el SIN.'
                        : 'En pruebas (piloto-siat), los documentos no tienen validez fiscal oficial.' }}
                    </span>
                  </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                  <v-card outlined rounded="lg" class="pa-4 mb-3">
                    <div class="text-subtitle-2 font-weight-bold mb-2">
                      <v-icon small color="primary">mdi-receipt-text-outline</v-icon> Modalidad de Facturación *
                    </div>
                    <v-radio-group v-model="form.codigo_modalidad" row :rules="[v => !!v || 'Seleccione modalidad']">
                      <v-radio
                        label="1 - Electrónica en Línea"
                        :value="1"
                        color="primary"
                      ></v-radio>
                      <v-radio
                        label="2 - Computarizada en Línea"
                        :value="2"
                        color="info"
                      ></v-radio>
                    </v-radio-group>
                    <span class="text-caption text-muted">
                      {{ form.codigo_modalidad === 1
                        ? 'Requiere firma digital XML (certificado .p12 de ADSIB).'
                        : 'No requiere firma digital XML, utiliza código hash estándar.' }}
                    </span>
                  </v-card>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="form.codigo_sistema"
                    label="Código de Sistema Otorgado por el SIN *"
                    outlined
                    dense
                    prepend-inner-icon="mdi-identifier"
                    :rules="[v => !!v || 'El código de sistema es requerido']"
                    hint="Código alfanumérico generado por Impuestos Nacionales al autorizar el sistema"
                    persistent-hint
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="form.token_delegado"
                    label="Token Delegado del SIN *"
                    outlined
                    dense
                    rows="4"
                    prepend-inner-icon="mdi-key-chain"
                    :rules="[v => !!v || 'El token delegado es requerido']"
                    hint="Token Bearer / Delegado provisto por el SIN para autenticar operaciones SOAP"
                    persistent-hint
                  >
                    <template v-slot:append>
                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn icon small v-bind="attrs" v-on="on" @click="copiarToken">
                            <v-icon small>mdi-content-copy</v-icon>
                          </v-btn>
                        </template>
                        <span>Copiar Token</span>
                      </v-tooltip>
                    </template>
                  </v-textarea>
                </v-col>
              </v-row>
            </v-tab-item>

            <!-- TAB 3: FIRMA DIGITAL ADSIB (.p12) -->
            <v-tab-item>
              <v-alert
                border="left"
                colored-border
                :color="form.tiene_certificado ? 'success' : 'warning'"
                elevation="1"
                class="mb-4"
                dense
              >
                <div class="d-flex align-center justify-space-between flex-wrap">
                  <div>
                    <strong>Estado del Certificado Digital:</strong>
                    <v-chip
                      small
                      :color="form.tiene_certificado ? 'success' : 'warning'"
                      text-color="white"
                      class="ml-2 font-weight-bold"
                    >
                      <v-icon left x-small>
                        {{ form.tiene_certificado ? 'mdi-check-circle' : 'mdi-alert-circle' }}
                      </v-icon>
                      {{ form.tiene_certificado ? 'Certificado .p12 presente en el servidor' : 'Sin certificado cargado' }}
                    </v-chip>
                  </div>
                  <div class="text-caption text-secondary mt-1 mt-sm-0">
                    Ubicación interna: <code>storage/app/siat/certs/certificado.p12</code>
                  </div>
                </div>
              </v-alert>

              <v-row dense>
                <v-col cols="12" md="6">
                  <v-card outlined rounded="lg" class="pa-4 fill-height">
                    <div class="text-subtitle-2 font-weight-bold mb-3">
                      <v-icon small color="primary">mdi-file-certificate-outline</v-icon> Subir Archivo PKCS#12 (.p12 / .pfx)
                    </div>

                    <v-file-input
                      v-model="archivoCertificado"
                      accept=".p12,.pfx"
                      label="Seleccionar archivo .p12 de ADSIB"
                      outlined
                      dense
                      show-size
                      prepend-icon=""
                      prepend-inner-icon="mdi-upload"
                      hint="Cargue el archivo emitido por la ADSIB para el representante de la entidad"
                      persistent-hint
                    ></v-file-input>

                    <div class="text-caption text-muted mt-2">
                      <v-icon x-small color="grey">mdi-information-outline</v-icon>
                      Si ya cuenta con un certificado cargado y no desea reemplazarlo, deje este campo vacío.
                    </div>
                  </v-card>
                </v-col>

                <v-col cols="12" md="6">
                  <v-card outlined rounded="lg" class="pa-4 fill-height">
                    <div class="text-subtitle-2 font-weight-bold mb-3">
                      <v-icon small color="primary">mdi-lock-outline</v-icon> Contraseña del Certificado
                    </div>

                    <v-text-field
                      v-model="form.certificado_password"
                      :type="showPassword ? 'text' : 'password'"
                      :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                      @click:append="showPassword = !showPassword"
                      label="Contraseña de la Clave Privada"
                      outlined
                      dense
                      prepend-inner-icon="mdi-key-variant"
                      hint="Dejar vacío si no desea cambiar la contraseña actual"
                      persistent-hint
                    ></v-text-field>

                    <div v-if="form.tiene_password" class="text-caption text-success mt-2">
                      <v-icon x-small color="success">mdi-check</v-icon>
                      Actualmente ya existe una contraseña guardada en el sistema.
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-tab-item>
          </v-tabs-items>

          <v-divider class="my-5"></v-divider>

          <!-- BOTONES DE ACCIÓN PRINCIPAL -->
          <div class="d-flex align-center justify-end">
            <v-btn
              text
              color="grey darken-1"
              class="text-capitalize mr-2"
              @click="cargarConfiguracion"
              :disabled="loading"
            >
              <v-icon left small>mdi-refresh</v-icon>
              Recargar Datos
            </v-btn>

            <v-btn
              color="primary"
              elevation="2"
              class="text-capitalize px-6 font-weight-bold"
              type="submit"
              :loading="loading"
              :disabled="loading"
            >
              <v-icon left small>mdi-content-save</v-icon>
              Guardar Configuración
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- DIÁLOGO DIAGNÓSTICO DE CONEXIÓN AL SIN -->
    <v-dialog v-model="dialogConexion" max-width="600" persistent>
      <v-card rounded="lg">
        <v-card-title class="indigo darken-1 white--text py-3">
          <v-icon left color="white">mdi-lan-check</v-icon>
          <span>Resultado de Diagnóstico SIN</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogConexion = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <div v-if="resultadoConexion">
            <v-alert
              :color="resultadoConexion.success ? 'success' : 'error'"
              text
              dense
              border="left"
              class="mb-3"
            >
              <div class="text-subtitle-2 font-weight-bold">
                {{ resultadoConexion.success ? '¡Comunicación Exitosa con el SIN!' : 'Error de Conexión al SIN' }}
              </div>
              <div class="text-caption">
                {{ resultadoConexion.message }}
              </div>
            </v-alert>

            <v-simple-table dense class="border rounded">
              <template v-slot:default>
                <tbody>
                  <tr>
                    <td class="font-weight-bold text-caption">Ambiente Evaluado</td>
                    <td>
                      <v-chip
                        x-small
                        :color="resultadoConexion.ambiente === 1 ? 'success' : 'warning'"
                        text-color="white"
                      >
                        {{ resultadoConexion.ambiente === 1 ? '1 - Producción' : '2 - Piloto / Pruebas' }}
                      </v-chip>
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-caption">Latencia de Respuesta</td>
                    <td>
                      <span class="font-weight-medium">
                        {{ resultadoConexion.tiempo_respuesta_ms || 0 }} ms
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-caption">Servicio Web Consultado</td>
                    <td class="text-caption text-truncate" style="max-width: 340px;">
                      {{ resultadoConexion.endpoint }}
                    </td>
                  </tr>
                  <tr v-if="resultadoConexion.transaccion">
                    <td class="font-weight-bold text-caption">Transacción SIN</td>
                    <td class="text-caption">
                      {{ resultadoConexion.transaccion }}
                    </td>
                  </tr>
                  <tr v-if="resultadoConexion.codigo_recepcion">
                    <td class="font-weight-bold text-caption">Código de Recepción</td>
                    <td class="text-caption">
                      <code>{{ resultadoConexion.codigo_recepcion }}</code>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            class="text-capitalize"
            @click="dialogConexion = false"
          >
            Aceptar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- NOTIFICACIONES SNACKBAR -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3500"
      bottom
      right
      rounded="pill"
    >
      <div class="d-flex align-center">
        <v-icon left color="white">{{ snackbar.icon }}</v-icon>
        <span>{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn icon dark v-bind="attrs" @click="snackbar.show = false">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'ConfiguracionEmpresaSiat',
  data() {
    return {
      activeTab: 0,
      validForm: false,
      loading: false,
      testingConnection: false,
      dialogConexion: false,
      resultadoConexion: null,
      showPassword: false,
      archivoLogo: null,
      archivoCertificado: null,
      logoPreviewUrl: null,
      form: {
        id: null,
        razon_social: '',
        nombre_comercial: '',
        nit: '',
        telefono: '',
        correo: '',
        direccion: '',
        municipio: 'Patacamaya',
        codigo_ambiente: 1,
        codigo_modalidad: 1,
        codigo_sistema: '',
        token_delegado: '',
        certificado_password: '',
        tiene_certificado: false,
        tiene_password: false,
        logo_url: null,
      },
      snackbar: {
        show: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle-outline',
      },
    };
  },

  mounted() {
    this.cargarConfiguracion();
  },

  methods: {
    cargarConfiguracion() {
      this.loading = true;
      axios
        .get('api/datos/empresa')
        .then(response => {
          const data = response.data.data;
          if (data) {
            this.form = {
              ...this.form,
              id: data.id,
              razon_social: data.razon_social || '',
              nombre_comercial: data.nombre_comercial || '',
              nit: data.nit || '',
              telefono: data.telefono || '',
              correo: data.correo || '',
              direccion: data.direccion || '',
              municipio: data.municipio || 'Patacamaya',
              codigo_ambiente: data.codigo_ambiente || 1,
              codigo_modalidad: data.codigo_modalidad || 1,
              codigo_sistema: data.codigo_sistema || '',
              token_delegado: data.token_delegado || '',
              certificado_password: '', // Por seguridad no devolvemos el password plano
              tiene_certificado: !!data.tiene_certificado,
              tiene_password: !!data.tiene_password,
              logo_url: data.logo_url || null,
            };
          }
        })
        .catch(error => {
          this.mostrarNotificacion(
            'Error al cargar la configuración: ' + (error.response?.data?.mensaje || error.message),
            'error',
            'mdi-alert-circle-outline'
          );
        })
        .finally(() => {
          this.loading = false;
        });
    },

    onLogoSelected(file) {
      if (file) {
        this.logoPreviewUrl = URL.createObjectURL(file);
      } else {
        this.logoPreviewUrl = null;
      }
    },

    copiarToken() {
      if (!this.form.token_delegado) {
        this.mostrarNotificacion('No hay token para copiar', 'warning', 'mdi-alert-outline');
        return;
      }
      navigator.clipboard
        .writeText(this.form.token_delegado)
        .then(() => {
          this.mostrarNotificacion('Token copiado al portapapeles', 'info', 'mdi-content-copy');
        })
        .catch(() => {
          this.mostrarNotificacion('No se pudo copiar el token', 'error', 'mdi-alert-circle-outline');
        });
    },

    guardarConfiguracion() {
      if (!this.$refs.configForm.validate()) {
        this.mostrarNotificacion('Por favor revise los campos requeridos', 'warning', 'mdi-alert-outline');
        return;
      }

      this.loading = true;
      const formData = new FormData();

      // Campos de texto
      formData.append('razon_social', this.form.razon_social || '');
      formData.append('nombre_comercial', this.form.nombre_comercial || '');
      formData.append('nit', this.form.nit || '');
      formData.append('telefono', this.form.telefono || '');
      formData.append('correo', this.form.correo || '');
      formData.append('direccion', this.form.direccion || '');
      formData.append('municipio', this.form.municipio || '');
      formData.append('codigo_ambiente', this.form.codigo_ambiente);
      formData.append('codigo_modalidad', this.form.codigo_modalidad);
      formData.append('codigo_sistema', this.form.codigo_sistema || '');
      formData.append('token_delegado', this.form.token_delegado || '');

      if (this.form.certificado_password) {
        formData.append('certificado_password', this.form.certificado_password);
      }

      // Archivos opcionales
      if (this.archivoLogo) {
        formData.append('logo', this.archivoLogo);
      }
      if (this.archivoCertificado) {
        formData.append('certificado_p12', this.archivoCertificado);
      }

      axios
        .post('api/datos/empresa', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        .then(response => {
          this.mostrarNotificacion(
            response.data.mensaje || 'Configuración guardada exitosamente',
            'success',
            'mdi-check-circle-outline'
          );
          // Limpiar inputs de archivo
          this.archivoLogo = null;
          this.archivoCertificado = null;
          this.cargarConfiguracion();
        })
        .catch(error => {
          const mensaje = error.response?.data?.mensaje || error.message;
          this.mostrarNotificacion('Error al guardar: ' + mensaje, 'error', 'mdi-alert-circle-outline');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    probarConexionSiat() {
      this.testingConnection = true;
      axios
        .post('api/datos/empresa/probar-conexion', {
          codigo_ambiente: this.form.codigo_ambiente,
          token_delegado: this.form.token_delegado,
        })
        .then(response => {
          this.resultadoConexion = response.data;
          this.dialogConexion = true;
        })
        .catch(error => {
          this.resultadoConexion = {
            success: false,
            message: error.response?.data?.mensaje || error.message,
            ambiente: this.form.codigo_ambiente,
            endpoint: 'Servicios SOAP SIAT',
          };
          this.dialogConexion = true;
        })
        .finally(() => {
          this.testingConnection = false;
        });
    },

    mostrarNotificacion(text, color = 'success', icon = 'mdi-check-circle-outline') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.icon = icon;
      this.snackbar.show = true;
    },
  },
};
</script>

<style scoped>
.config-empresa-siat-container {
  max-width: 1400px;
  margin: 0 auto;
}

.erp-card-elevated {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
}

.logo-preview-box {
  min-height: 140px;
  border: 1px dashed rgba(0, 0, 0, 0.15);
  border-radius: 8px;
  background-color: #fafbfc;
}

.gap-2 {
  gap: 8px;
}

.border-bottom {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
