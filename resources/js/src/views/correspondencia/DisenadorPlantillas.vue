<template>
  <div class="disenador-plantillas-container">
    <!-- CABECERA -->
    <v-card class="mb-4 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-document-edit-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Diseñador de Plantillas Documentales</h2>
            <span class="text-caption text-secondary">
              Estructura visual, reordenamiento de componentes y configuración de formatos PDF oficiales
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-select
            v-model="idPlantillaSeleccionada"
            :items="plantillas"
            item-text="nombre"
            item-value="id"
            label="Seleccionar Plantilla"
            dense
            outlined
            hide-details
            class="mr-3 selector-plantilla"
            @change="cargarPlantilla"
          ></v-select>

          <v-btn outlined color="indigo darken-2" class="text-capitalize rounded-pill mr-2 font-weight-medium" @click="nuevaPlantilla">
            <v-icon left small>mdi-plus</v-icon> Nueva
          </v-btn>

          <v-btn outlined color="primary" class="text-capitalize rounded-pill mr-2 font-weight-medium" @click="dialogPreview = true">
            <v-icon left small>mdi-file-pdf-box</v-icon> Vista Previa PDF
          </v-btn>

          <v-btn color="primary" class="text-capitalize rounded-pill font-weight-bold elevation-2" :loading="guardando" @click="guardarPlantilla">
            <v-icon left small>mdi-content-save-outline</v-icon> Guardar Plantilla
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- ÁREA DE TRABAJO EN DOS PANELES (CANVAS HOJA A4 vs CAJA DE COMPONENTES) -->
    <v-row>
      <!-- PANEL IZQUIERDO: CANVAS DE LA HOJA DIGITAL A4 (PREVIEW EN VIVO) -->
      <v-col cols="12" md="7" lg="8">
        <v-card rounded="lg" class="erp-card-elevated pa-4 canvas-wrapper">
          <div class="d-flex align-center justify-space-between mb-3">
            <span class="text-subtitle-2 font-weight-bold text-secondary">
              <v-icon small left>mdi-file-outline</v-icon>
              Hoja de Trabajo: {{ formPlantilla.nombre }} ({{ formPlantilla.config_pagina.orientacion }})
            </span>
            <v-chip x-small color="primary" label class="font-weight-bold">
              Formato A4 • {{ formPlantilla.componentes.length }} componentes
            </v-chip>
          </div>

          <!-- CONTENEDOR DE HOJA A4 REAL -->
          <div class="hoja-papel-a4 elevation-3" :class="formPlantilla.config_pagina.orientacion === 'HORIZONTAL' ? 'a4-horizontal' : 'a4-vertical'">
            <!-- 1. MEMBRETE OFICIAL SUPERIOR -->
            <div class="membrete-oficial" v-if="formPlantilla.config_pagina.mostrar_membrete">
              <table class="w-100 mb-2">
                <tr>
                  <td style="width: 20%; vertical-align: middle;">
                    <img src="/images/logoEmapa2.png" alt="EMAPA" style="max-height: 45px; max-width: 100px;">
                  </td>
                  <td style="width: 60%; text-align: center; vertical-align: middle;">
                    <div style="font-size: 11px; font-weight: bold; color: #1e293b; text-transform: uppercase;">
                      EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS
                    </div>
                    <div style="font-size: 9px; color: #64748b;">ESTADO PLURINACIONAL DE BOLIVIA</div>
                  </td>
                  <td style="width: 20%; text-align: right; vertical-align: middle;">
                    <div style="font-size: 10px; font-weight: bold; color: #0284c7;">{{ formPlantilla.sigla }}-{{ formPlantilla.version }}</div>
                  </td>
                </tr>
              </table>
              <div class="linea-membrete"></div>
            </div>

            <!-- 2. RENDERIZADO DINÁMICO DE COMPONENTES ORDENADOS -->
            <div class="componentes-canvas mt-3">
              <div
                v-for="(comp, index) in formPlantilla.componentes"
                :key="index"
                class="componente-bloque"
                :class="{ 'bloque-seleccionado': componenteSeleccionadoIndex === index }"
                @click="seleccionarComponente(index)"
              >
                <!-- BARRA DE ACCIÓN DEL COMPONENTE -->
                <div class="bloque-header d-flex align-center justify-space-between">
                  <div class="d-flex align-center">
                    <v-icon x-small color="primary" class="mr-1">{{ getIconoComponente(comp.tipo_componente) }}</v-icon>
                    <span class="text-caption font-weight-bold">{{ comp.nombre }}</span>
                  </div>
                  <div class="d-flex align-center">
                    <v-btn icon x-small :disabled="index === 0" @click.stop="moverArriba(index)">
                      <v-icon x-small>mdi-arrow-up</v-icon>
                    </v-btn>
                    <v-btn icon x-small :disabled="index === formPlantilla.componentes.length - 1" @click.stop="moverAbajo(index)">
                      <v-icon x-small>mdi-arrow-down</v-icon>
                    </v-btn>
                    <v-btn icon x-small color="red" @click.stop="eliminarComponente(index)">
                      <v-icon x-small>mdi-trash-can-outline</v-icon>
                    </v-btn>
                  </div>
                </div>

                <!-- CONTENIDO VISUAL SEGÚN EL TIPO -->
                <div class="bloque-body pa-2">
                  <!-- Título Documento -->
                  <div v-if="comp.tipo_componente === 'TITULO'" class="text-center font-weight-bold text-h6 primary--text my-1">
                    {{ formPlantilla.nombre }}
                  </div>

                  <!-- Encabezado de Contactos (A, DE, REF, FECHA) -->
                  <div v-else-if="comp.tipo_componente === 'LISTA_CONTACTOS'" class="meta-preview">
                    <table style="width: 100%; font-size: 11px;">
                      <tr><td style="width: 15%; font-weight: bold;">A:</td><td>[DESTINATARIOS DESIGNADOS]</td></tr>
                      <tr><td style="font-weight: bold;">DE:</td><td>[REMITENTE AUTORIZADO]</td></tr>
                      <tr><td style="font-weight: bold;">VÍA:</td><td>[CONDUCTO REGULAR]</td></tr>
                      <tr><td style="font-weight: bold;">REF:</td><td>[ASUNTO O REFERENCIA INSTITUCIONAL]</td></tr>
                      <tr><td style="font-weight: bold;">FECHA:</td><td>{{ fechaHoy }}</td></tr>
                    </table>
                  </div>

                  <!-- Texto Enriquecido / Cuerpo -->
                  <div v-else-if="comp.tipo_componente === 'TEXTO_HTML'" class="texto-preview text-caption">
                    <p class="mb-1">El presente documento establece las disposiciones institucionales y técnicas emanadas por la autoridad competente de la Empresa de Apoyo a la Producción de Alimentos (EMAPA)...</p>
                  </div>

                  <!-- Referencias -->
                  <div v-else-if="comp.tipo_componente === 'REFERENCIA'" class="referencia-preview pa-2 rounded grey lighten-4 text-caption">
                    <strong>Antecedentes:</strong> CITEs, resoluciones y hojas de ruta vinculadas al expediente.
                  </div>

                  <!-- Bloque de Firmas -->
                  <div v-else-if="comp.tipo_componente === 'FIRMAS'" class="firmas-preview d-flex justify-space-around my-2">
                    <div class="sello-firma-demo text-center">
                      <div class="sello-linea"></div>
                      <div class="font-weight-bold" style="font-size: 9px;">[FIRMA DIGITAL]</div>
                      <div style="font-size: 8px; color: #64748b;">Funcionario Emisor</div>
                    </div>
                    <div class="sello-firma-demo text-center">
                      <div class="sello-linea"></div>
                      <div class="font-weight-bold" style="font-size: 9px;">[VISTO BUENO VÍA]</div>
                      <div style="font-size: 8px; color: #64748b;">Jefatura Inmediata</div>
                    </div>
                  </div>

                  <!-- Pie de Página y QR -->
                  <div v-else-if="comp.tipo_componente === 'PIE_PAGINA'" class="pie-preview d-flex align-center justify-space-between pt-2 border-top">
                    <div style="font-size: 8px; color: #64748b;">
                      Documento Oficial EMAPA • Verificación pública con código QR • Página 1 de 1
                    </div>
                    <v-icon color="grey darken-2" size="24">mdi-qrcode</v-icon>
                  </div>
                </div>
              </div>

              <div v-if="formPlantilla.componentes.length === 0" class="text-center py-8 grey--text text-caption">
                <v-icon size="40" color="grey lighten-1">mdi-tray-plus</v-icon>
                <div class="mt-2">Arrastra o añade componentes desde la barra lateral derecha para armar el documento.</div>
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <!-- PANEL DERECHO: HERRAMIENTAS, COMPONENTES Y CONFIGURACIÓN -->
      <v-col cols="12" md="5" lg="4">
        <v-card rounded="lg" class="erp-card-elevated fill-height">
          <v-tabs v-model="tabHerramientas" background-color="grey lighten-4" color="primary" grow dense>
            <v-tab class="text-capitalize font-weight-bold"><v-icon left small>mdi-view-grid-plus</v-icon> Agregar</v-tab>
            <v-tab class="text-capitalize font-weight-bold"><v-icon left small>mdi-format-list-numbered</v-icon> Orden ({{ formPlantilla.componentes.length }})</v-tab>
            <v-tab class="text-capitalize font-weight-bold"><v-icon left small>mdi-cog-outline</v-icon> Página</v-tab>
          </v-tabs>

          <v-divider></v-divider>

          <v-card-text class="pa-3">
            <!-- 1. COMPONENTES DISPONIBLES PARA AGREGAR -->
            <div v-if="tabHerramientas === 0">
              <span class="text-caption font-weight-bold text-secondary text-uppercase d-block mb-2">Componentes Documentales</span>
              <v-list dense class="pa-0">
                <v-list-item
                  v-for="(item, i) in catalogoComponentes"
                  :key="i"
                  class="componente-card mb-2 rounded-lg"
                  @click="agregarComponente(item)"
                >
                  <v-list-item-avatar size="32" color="indigo lighten-5" class="my-0">
                    <v-icon small color="indigo darken-2">{{ item.icon }}</v-icon>
                  </v-list-item-avatar>
                  <v-list-item-content>
                    <v-list-item-title class="font-weight-bold text-body-2">{{ item.nombre }}</v-list-item-title>
                    <v-list-item-subtitle class="text-caption text-secondary">{{ item.descripcion }}</v-list-item-subtitle>
                  </v-list-item-content>
                  <v-list-item-action class="my-0">
                    <v-icon small color="primary">mdi-plus-circle-outline</v-icon>
                  </v-list-item-action>
                </v-list-item>
              </v-list>
            </div>

            <!-- 2. GESTIÓN DEL ORDEN DE COMPONENTES -->
            <div v-else-if="tabHerramientas === 1">
              <span class="text-caption font-weight-bold text-secondary text-uppercase d-block mb-2">Secuencia y Reordenamiento</span>
              <div v-for="(comp, idx) in formPlantilla.componentes" :key="idx" class="d-flex align-center justify-space-between pa-2 mb-2 rounded grey lighten-4">
                <div class="d-flex align-center">
                  <v-chip x-small color="primary" label class="mr-2 font-weight-bold">#{{ idx + 1 }}</v-chip>
                  <span class="text-caption font-weight-bold">{{ comp.nombre }}</span>
                </div>
                <div>
                  <v-btn icon x-small :disabled="idx === 0" @click="moverArriba(idx)">
                    <v-icon small>mdi-arrow-up-bold</v-icon>
                  </v-btn>
                  <v-btn icon x-small :disabled="idx === formPlantilla.componentes.length - 1" @click="moverAbajo(idx)">
                    <v-icon small>mdi-arrow-down-bold</v-icon>
                  </v-btn>
                  <v-btn icon x-small color="red" @click="eliminarComponente(idx)">
                    <v-icon small>mdi-delete</v-icon>
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- 3. CONFIGURACIÓN DE PÁGINA Y METADATOS -->
            <div v-else-if="tabHerramientas === 2">
              <v-text-field
                v-model="formPlantilla.nombre"
                label="Nombre de la Plantilla *"
                dense
                outlined
                class="mb-2"
              ></v-text-field>

              <v-row dense>
                <v-col cols="6">
                  <v-text-field
                    v-model="formPlantilla.sigla"
                    label="Sigla / CITE *"
                    dense
                    outlined
                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="formPlantilla.version"
                    label="Versión"
                    dense
                    outlined
                  ></v-text-field>
                </v-col>
              </v-row>

              <v-select
                v-model="formPlantilla.param_tipo_plantilla"
                :items="['MEMORANDUM', 'INFORME', 'NOTA_INTERNA', 'CIRCULAR', 'CARTA']"
                label="Tipo Documental"
                dense
                outlined
                class="mb-2"
              ></v-select>

              <v-select
                v-model="formPlantilla.config_pagina.orientacion"
                :items="['VERTICAL', 'HORIZONTAL']"
                label="Orientación de la Hoja"
                dense
                outlined
                class="mb-2"
              ></v-select>

              <v-switch
                v-model="formPlantilla.config_pagina.mostrar_membrete"
                label="Incluir Membrete Oficial EMAPA"
                color="primary"
                dense
                inset
                class="mt-0"
              ></v-switch>

              <v-switch
                v-model="formPlantilla.config_pagina.mostrar_pie"
                label="Incluir Pie de Página y QR de Seguridad"
                color="primary"
                dense
                inset
                class="mt-0"
              ></v-switch>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- MODAL DE VISTA PREVIA PDF OFICIAL -->
    <v-dialog v-model="dialogPreview" max-width="850px" scrollable>
      <v-card rounded="lg">
        <v-card-title class="primary white--text font-weight-bold py-3 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon left color="white">mdi-file-pdf-box</v-icon>
            Vista Previa de Documento Oficial - {{ formPlantilla.nombre }}
          </div>
          <v-btn icon dark small @click="dialogPreview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pa-4 bg-grey-lighten-4">
          <div class="hoja-papel-a4 mx-auto elevation-2">
            <!-- Membrete -->
            <div class="membrete-oficial">
              <table class="w-100 mb-2">
                <tr>
                  <td style="width: 20%;">
                    <img src="/images/logoEmapa2.png" alt="EMAPA" style="max-height: 45px;">
                  </td>
                  <td style="width: 60%; text-align: center;">
                    <div style="font-size: 12px; font-weight: bold;">EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS</div>
                    <div style="font-size: 10px; color: #64748b;">ESTADO PLURINACIONAL DE BOLIVIA</div>
                  </td>
                  <td style="width: 20%; text-align: right; font-size: 11px; font-weight: bold; color: #1e3a8a;">
                    {{ formPlantilla.sigla }}-001/2026
                  </td>
                </tr>
              </table>
              <div class="linea-membrete"></div>
            </div>

            <!-- Título y Asunto -->
            <div class="text-center font-weight-bold text-h6 primary--text my-3">
              {{ formPlantilla.nombre }}
            </div>

            <!-- Encabezado de Contactos -->
            <table class="w-100 mb-4" style="font-size: 12px; border-collapse: collapse;">
              <tr><td style="width: 15%; font-weight: bold; padding: 3px 0;">A:</td><td>LIC. FRANKLIN FLORES CÓRDOVA - GERENTE GENERAL</td></tr>
              <tr><td style="font-weight: bold; padding: 3px 0;">DE:</td><td>ING. ROBERTO MAMANI - DIRECTOR DE LOGÍSTICA</td></tr>
              <tr><td style="font-weight: bold; padding: 3px 0;">VÍA:</td><td>GERENCIA DE OPERACIONES</td></tr>
              <tr><td style="font-weight: bold; padding: 3px 0;">REF:</td><td>INFORME TÉCNICO DE ACOPIO DE TRIGO CAMPAÑA 2026</td></tr>
              <tr><td style="font-weight: bold; padding: 3px 0;">FECHA:</td><td>{{ fechaHoy }}</td></tr>
            </table>

            <v-divider class="my-3"></v-divider>

            <!-- Cuerpo -->
            <div style="font-size: 13px; line-height: 1.6; text-align: justify;" class="mb-4">
              <p>Por medio del presente, en cumplimiento a la normativa institucional y los requerimientos de la campaña de acopio de trigo y arroz 2026, se remite la evaluación circunstanciada de los silos y plantas industriales de EMAPA en los departamentos de Santa Cruz, Cochabamba y La Paz.</p>
              <p>Habiéndose verificado la capacidad operativa y los estándares de almacenamiento y custodia de granos, se recomienda proceder con la emisión de las órdenes de compra correspondientes.</p>
            </div>

            <!-- Firmas y QR -->
            <div class="d-flex justify-space-between align-end mt-5 pt-4 border-top">
              <div class="d-flex gap-4">
                <div class="text-center pa-2 border rounded" style="font-size: 9px;">
                  <div style="width: 120px; border-top: 1px dashed #000; margin-top: 30px;"></div>
                  <strong>Firma y Sello Emisor</strong>
                </div>
                <div class="text-center pa-2 border rounded" style="font-size: 9px;">
                  <div style="width: 120px; border-top: 1px dashed #000; margin-top: 30px;"></div>
                  <strong>Visto Bueno Vía</strong>
                </div>
              </div>
              <div class="text-right">
                <v-icon size="64" color="grey darken-3">mdi-qrcode</v-icon>
                <div style="font-size: 8px; font-family: monospace;">Token: PUB-EMAPA-2026</div>
              </div>
            </div>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" @click="dialogPreview = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" top right :timeout="3500">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'DisenadorPlantillas',
  data() {
    return {
      plantillas: [],
      idPlantillaSeleccionada: null,
      guardando: false,
      tabHerramientas: 0,
      componenteSeleccionadoIndex: null,
      dialogPreview: false,

      formPlantilla: {
        id: null,
        nombre: 'MEMORÁNDUM OFICIAL',
        sigla: 'MEM',
        version: '1.0',
        param_tipo_plantilla: 'MEMORANDUM',
        param_validez_legal: 'FIRMA_ELECTRONICA',
        config_pagina: {
          orientacion: 'VERTICAL',
          formato: 'A4',
          mostrar_membrete: true,
          mostrar_pie: true,
        },
        componentes: [
          { nombre: 'Título Documental', tipo_componente: 'TITULO', orden: 1 },
          { nombre: 'Encabezado de Contactos (A, DE, VÍA, REF)', tipo_componente: 'LISTA_CONTACTOS', orden: 2 },
          { nombre: 'Cuerpo de Texto', tipo_componente: 'TEXTO_HTML', orden: 3 },
          { nombre: 'Bloque de Firmas y Rúbricas', tipo_componente: 'FIRMAS', orden: 4 },
          { nombre: 'Pie de Página y Código QR', tipo_componente: 'PIE_PAGINA', orden: 5 },
        ],
      },

      catalogoComponentes: [
        { nombre: 'Título Documental', tipo_componente: 'TITULO', icon: 'mdi-format-title', descripcion: 'Título central del documento oficial' },
        { nombre: 'Lista de Contactos', tipo_componente: 'LISTA_CONTACTOS', icon: 'mdi-card-account-details-outline', descripcion: 'Encabezado DE, A, VÍA, REF y Fecha' },
        { nombre: 'Cuerpo de Texto HTML', tipo_componente: 'TEXTO_HTML', icon: 'mdi-text-box-edit-outline', descripcion: 'Editor de texto enriquecido con formato' },
        { nombre: 'Referencias y Antecedentes', tipo_componente: 'REFERENCIA', icon: 'mdi-link-variant', descripcion: 'Bloque de hojas de ruta vinculadas' },
        { nombre: 'Firmas Digitales', tipo_componente: 'FIRMAS', icon: 'mdi-draw-pen', descripcion: 'Espacio para sellos y rúbricas electrónicas' },
        { nombre: 'Pie de Página y QR', tipo_componente: 'PIE_PAGINA', icon: 'mdi-qrcode', descripcion: 'Sellado de seguridad y numeración de página' },
      ],

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    fechaHoy() {
      return new Date().toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' });
    },
  },
  mounted() {
    this.cargarPlantillas();
  },
  methods: {
    async cargarPlantillas() {
      try {
        const res = await window.axios.get('/api/correspondencia/configuracion/plantillas');
        if (res.data && res.data.success) {
          this.plantillas = res.data.data;
          if (this.plantillas.length > 0 && !this.idPlantillaSeleccionada) {
            this.idPlantillaSeleccionada = this.plantillas[0].id;
            this.cargarPlantilla();
          }
        }
      } catch (e) {}
    },
    async cargarPlantilla() {
      if (!this.idPlantillaSeleccionada) return;
      try {
        const res = await window.axios.get(`/api/correspondencia/configuracion/plantillas/${this.idPlantillaSeleccionada}`);
        if (res.data && res.data.success) {
          const p = res.data.data;
          this.formPlantilla = {
            id: p.id,
            nombre: p.nombre,
            sigla: p.sigla,
            version: p.version || '1.0',
            param_tipo_plantilla: p.param_tipo_plantilla || 'MEMORANDUM',
            config_pagina: p.config_pagina || { orientacion: 'VERTICAL', mostrar_membrete: true, mostrar_pie: true },
            componentes: (p.componentes && p.componentes.length) ? p.componentes : this.formPlantilla.componentes,
          };
        }
      } catch (e) {}
    },
    nuevaPlantilla() {
      this.idPlantillaSeleccionada = null;
      this.formPlantilla = {
        id: null,
        nombre: 'NUEVA PLANTILLA DOCUMENTAL',
        sigla: 'DOC',
        version: '1.0',
        param_tipo_plantilla: 'MEMORANDUM',
        config_pagina: { orientacion: 'VERTICAL', mostrar_membrete: true, mostrar_pie: true },
        componentes: [
          { nombre: 'Título Documental', tipo_componente: 'TITULO', orden: 1 },
          { nombre: 'Lista de Contactos', tipo_componente: 'LISTA_CONTACTOS', orden: 2 },
          { nombre: 'Cuerpo de Texto HTML', tipo_componente: 'TEXTO_HTML', orden: 3 },
          { nombre: 'Firmas Digitales', tipo_componente: 'FIRMAS', orden: 4 },
          { nombre: 'Pie de Página y QR', tipo_componente: 'PIE_PAGINA', orden: 5 },
        ],
      };
      this.tabHerramientas = 2; // Abrir pestaña de configuración
    },
    agregarComponente(item) {
      this.formPlantilla.componentes.push({
        nombre: item.nombre,
        tipo_componente: item.tipo_componente,
        orden: this.formPlantilla.componentes.length + 1,
      });
      this.mostrarMensaje(`Componente "${item.nombre}" agregado.`, 'success');
    },
    seleccionarComponente(index) {
      this.componenteSeleccionadoIndex = index;
    },
    moverArriba(index) {
      if (index === 0) return;
      const temp = this.formPlantilla.componentes[index];
      this.$set(this.formPlantilla.componentes, index, this.formPlantilla.componentes[index - 1]);
      this.$set(this.formPlantilla.componentes, index - 1, temp);
    },
    moverAbajo(index) {
      if (index === this.formPlantilla.componentes.length - 1) return;
      const temp = this.formPlantilla.componentes[index];
      this.$set(this.formPlantilla.componentes, index, this.formPlantilla.componentes[index + 1]);
      this.$set(this.formPlantilla.componentes, index + 1, temp);
    },
    eliminarComponente(index) {
      this.formPlantilla.componentes.splice(index, 1);
    },
    async guardarPlantilla() {
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/configuracion/plantillas', this.formPlantilla);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Plantilla y diseño visual guardados exitosamente.', 'success');
          this.cargarPlantillas();
        }
      } catch (e) {
        this.mostrarMensaje('Error al guardar plantilla.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    getIconoComponente(tipo) {
      const icons = {
        TITULO: 'mdi-format-title',
        LISTA_CONTACTOS: 'mdi-card-account-details-outline',
        TEXTO_HTML: 'mdi-text-box-edit-outline',
        REFERENCIA: 'mdi-link-variant',
        FIRMAS: 'mdi-draw-pen',
        PIE_PAGINA: 'mdi-qrcode',
      };
      return icons[tipo] || 'mdi-puzzle-outline';
    },
    mostrarMensaje(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.selector-plantilla {
  max-width: 250px;
}
.canvas-wrapper {
  background-color: #f1f5f9;
  min-height: 700px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.hoja-papel-a4 {
  background-color: #ffffff;
  color: #0f172a;
  box-sizing: border-box;
  padding: 30px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
}
.a4-vertical {
  width: 100%;
  max-width: 650px;
  min-height: 800px;
}
.a4-horizontal {
  width: 100%;
  max-width: 850px;
  min-height: 550px;
}
.linea-membrete {
  height: 2px;
  background: linear-gradient(90deg, #1e3a8a 0%, #0284c7 100%);
  margin-top: 6px;
}
.componente-bloque {
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  margin-bottom: 12px;
  background-color: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
}
.componente-bloque:hover {
  border-color: #0284c7;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.bloque-seleccionado {
  border: 1.5px solid #1e3a8a !important;
  background-color: #f8fafc;
}
.bloque-header {
  background-color: #f8fafc;
  padding: 4px 8px;
  border-bottom: 1px solid #f1f5f9;
  border-radius: 5px 5px 0 0;
}
.componente-card {
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
}
.componente-card:hover {
  background-color: #f8fafc;
  border-color: #1e3a8a;
}
.sello-firma-demo {
  border: 1px solid #e2e8f0;
  padding: 6px 12px;
  border-radius: 4px;
  background: #f8fafc;
}
.sello-linea {
  width: 100px;
  border-top: 1px dashed #64748b;
  margin-bottom: 4px;
}
</style>
