<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Padrón de Abonados</h2>
            <span class="text-caption text-secondary">Administración integral del padrón de usuarios, conexiones y micromedición</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="abrirModalNuevo">
            <v-icon left small>mdi-account-plus</v-icon> + Nuevo Abonado
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE RESUMEN -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Total Abonados</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ totalGlobalAbonados || totalAbonados }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Servicios Activos</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ activosCount }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">En Mora (>= 2 meses)</div>
          <div class="text-h4 font-weight-black warning--text mt-1">{{ moraCount }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-3 text-center" rounded="lg" elevation="2">
          <div class="text-caption text-uppercase text-secondary font-weight-bold">Servicios Cortados</div>
          <div class="text-h4 font-weight-black error--text mt-1">{{ cortadosCount }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <!-- 1. Selector Tipo de Búsqueda (como en Caja) -->
        <v-col cols="12" sm="5" md="2">
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
            @change="alCambiarTipoBusqueda"
          ></v-select>
        </v-col>

        <!-- 2. Campo de búsqueda reactivo -->
        <v-col cols="12" sm="7" md="3">
          <v-text-field
            v-model="busqueda"
            :label="etiquetaBusqueda"
            :placeholder="placeholderBusqueda"
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarAbonados"
            @click:clear="onClearBusqueda"
          ></v-text-field>
        </v-col>

        <!-- 3. Filtro Zona -->
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroZona"
            :items="zonas"
            item-text="nombre"
            item-value="id"
            label="Filtrar por Zona"
            dense
            outlined
            hide-details
            clearable
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <!-- 4. Filtro Categoría -->
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroCategoria"
            :items="categorias"
            item-text="nombre"
            item-value="id"
            label="Categoría"
            dense
            outlined
            hide-details
            clearable
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <!-- 5. Filtro Estado de Servicio (Mapeo a datos reales FoxPro/DB) -->
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filtroEstado"
            :items="estadosOpciones"
            item-text="texto"
            item-value="valor"
            label="Estado"
            dense
            outlined
            hide-details
            @change="cargarAbonados"
          ></v-select>
        </v-col>

        <!-- 6. Botón Refrescar -->
        <v-col cols="12" sm="6" md="1" class="text-center d-flex align-center justify-center">
          <v-btn icon color="primary" :loading="cargando" title="Actualizar datos" @click="cargarAbonados">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE ABONADOS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-data-table
        :headers="columnas"
        :items="abonados"
        :loading="cargando"
        :server-items-length="totalAbonados"
        :options.sync="opcionesPaginacion"
        :footer-props="{ 'items-per-page-options': [10, 15, 25, 50] }"
        class="elevation-0"
      >
        <!-- Código -->
        <template v-slot:item.codigo="{ item }">
          <v-chip color="primary" outlined small class="font-weight-bold">
            {{ item.codigo }}
          </v-chip>
        </template>

        <!-- Nombre -->
        <template v-slot:item.nombre_completo="{ item }">
          <div>
            <div class="font-weight-bold">{{ item.nombre_completo }}</div>
            <span class="text-caption text-secondary" v-if="item.numero_documento">
              Doc: {{ item.numero_documento }}
            </span>
          </div>
        </template>

        <!-- Ubicación -->
        <template v-slot:item.ubicacion="{ item }">
          <div class="text-caption">
            <v-icon x-small color="grey">mdi-map-marker</v-icon>
            <strong>{{ item.zona ? item.zona.nombre : 'S/Z' }}</strong>
            <span v-if="item.calle"> - {{ item.calle.nombre }}</span>
            <span v-if="item.numero_vivienda"> #{{ item.numero_vivienda }}</span>
          </div>
        </template>

        <!-- Categoría -->
        <template v-slot:item.categoria="{ item }">
          <v-chip x-small color="info" outlined>
            {{ item.categoria ? item.categoria.nombre : 'S/C' }}
          </v-chip>
          <v-icon x-small color="amber darken-2" v-if="item.es_tercera_edad" title="Aplica 20% Ley 1886">
            mdi-account-star
          </v-icon>
        </template>

        <!-- Medidor -->
        <template v-slot:item.medidor="{ item }">
          <span v-if="item.medidor_actual" class="text-caption font-weight-medium">
            <v-icon x-small color="blue">mdi-counter</v-icon> {{ item.medidor_actual.numero_serie }}
          </span>
          <span v-else class="text-caption text-secondary">Sin medidor</span>
        </template>

        <!-- Estado de Servicio -->
        <template v-slot:item.estado_servicio="{ item }">
          <v-chip
            small
            :color="colorEstado(item.estado_servicio)"
            text-color="white"
            class="font-weight-medium"
          >
            {{ item.estado_servicio }}
          </v-chip>
        </template>

        <!-- Saldo Deuda -->
        <template v-slot:item.saldo_deuda="{ item }">
          <div class="text-right font-weight-bold" :class="item.saldo_deuda > 0 ? 'error--text' : 'success--text'">
            Bs {{ parseFloat(item.saldo_deuda).toFixed(2) }}
            <div class="text-caption text-secondary" v-if="item.meses_mora > 0">
              ({{ item.meses_mora }} meses)
            </div>
          </div>
        </template>

        <!-- Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-center gap-1">
            <v-btn icon small color="primary" title="Ver ficha completa" @click="verFicha(item)">
              <v-icon small>mdi-eye</v-icon>
            </v-btn>
            <v-btn icon small color="amber darken-2" title="Editar datos del socio" @click="abrirModalEditar(item)">
              <v-icon small>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon small color="teal" title="Cobrar en ventanilla" :to="{ path: '/comercial/caja', query: { codigo: item.codigo } }">
              <v-icon small>mdi-cash-register</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO CREAR / EDITAR ABONADO (HOMOLOGADO CON FOXPRO) -->
    <v-dialog v-model="modalForm" max-width="950" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3 d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">{{ esEdicion ? 'mdi-account-edit' : 'mdi-account-plus' }}</v-icon>
            <span class="text-subtitle-1 font-weight-bold">
              {{ esEdicion ? 'Editar Socio #' + formAbonado.codigo : 'Registrar Nuevo Socio / Abonado' }}
            </span>
          </div>
          <v-btn icon color="white" small @click="modalForm = false">
            <v-icon small>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4" style="max-height: 75vh; overflow-y: auto;">
          <v-form ref="formAbonadoRef" v-model="formValido">
            <!-- SECCIÓN 1: IDENTIFICACIÓN Y ESTADO -->
            <div class="d-flex align-center mb-2">
              <v-icon small color="primary" class="mr-1">mdi-badge-account-horizontal</v-icon>
              <span class="text-caption font-weight-bold text-uppercase primary--text">1. Identificación y Datos Personales</span>
            </div>
            <v-divider class="mb-3"></v-divider>

            <v-row dense>
              <v-col cols="12" sm="3">
                <v-select
                  v-model="formAbonado.tipo_persona"
                  :items="[
                    { text: 'Persona Natural', value: 'NATURAL' },
                    { text: 'Persona Jurídica', value: 'JURIDICA' }
                  ]"
                  item-text="text"
                  item-value="value"
                  label="Tipo de Persona *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.numero_documento"
                  label="N° C.I. / NIT *"
                  dense
                  outlined
                  required
                  placeholder="Ej: 2163442"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="2">
                <v-text-field
                  v-model="formAbonado.complemento"
                  label="Complemento"
                  dense
                  outlined
                  placeholder="Ej: 1B, LP"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.fecha_ingreso"
                  label="Fecha de Ingreso *"
                  type="date"
                  dense
                  outlined
                  required
                ></v-text-field>
              </v-col>

              <!-- Nombres desglosados (como en FoxPro) -->
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.primer_apellido"
                  label="Apellido Paterno"
                  dense
                  outlined
                  placeholder="Ej: CARI"
                  @input="actualizarNombreCompleto"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.segundo_apellido"
                  label="Apellido Materno"
                  dense
                  outlined
                  placeholder="Ej: MAMANI"
                  @input="actualizarNombreCompleto"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.nombres"
                  label="Nombres"
                  dense
                  outlined
                  placeholder="Ej: WILFREDO"
                  @input="actualizarNombreCompleto"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="formAbonado.nombre_completo"
                  label="Nombre Completo o Razón Social *"
                  dense
                  outlined
                  required
                  placeholder="Ej: CARI MAMANI WILFREDO"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- SECCIÓN 2: CONTACTO Y REFERENCIA -->
            <div class="d-flex align-center mt-3 mb-2">
              <v-icon small color="primary" class="mr-1">mdi-phone-outline</v-icon>
              <span class="text-caption font-weight-bold text-uppercase primary--text">2. Contacto y Referencias</span>
            </div>
            <v-divider class="mb-3"></v-divider>

            <v-row dense>
              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.celular"
                  label="Teléfono Celular"
                  dense
                  outlined
                  prepend-inner-icon="mdi-cellphone"
                  placeholder="Ej: 73527585"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.telefono"
                  label="Teléfono Fijo"
                  dense
                  outlined
                  prepend-inner-icon="mdi-phone"
                  placeholder="Ej: 2-8147000"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.email"
                  label="Correo Electrónico"
                  dense
                  outlined
                  prepend-inner-icon="mdi-email-outline"
                  placeholder="ejemplo@gmail.com"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.persona_contacto"
                  label="Persona de Contacto / Ref."
                  dense
                  outlined
                  placeholder="Familiar o referencia"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- SECCIÓN 3: UBICACIÓN TÉCNICA DEL PREDIO -->
            <div class="d-flex align-center mt-3 mb-2">
              <v-icon small color="primary" class="mr-1">mdi-map-marker-radius</v-icon>
              <span class="text-caption font-weight-bold text-uppercase primary--text">3. Ubicación del Inmueble</span>
            </div>
            <v-divider class="mb-3"></v-divider>

            <v-row dense>
              <v-col cols="12" sm="4">
                <v-select
                  v-model="formAbonado.id_zona"
                  :items="zonas"
                  item-text="nombre"
                  item-value="id"
                  label="Zona Territorial *"
                  dense
                  outlined
                  required
                  @change="cargarCallesPorZona(formAbonado.id_zona)"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-select
                  v-model="formAbonado.id_calle"
                  :items="callesForm"
                  item-text="nombre"
                  item-value="id"
                  label="Calle / Avenida / Plaza"
                  dense
                  outlined
                  clearable
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.numero_vivienda"
                  label="N° Vivienda / Puerta"
                  dense
                  outlined
                  placeholder="S/N o N° 124"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.edificio"
                  label="Edificio"
                  dense
                  outlined
                  placeholder="Torre o bloque"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="formAbonado.departamento"
                  label="Departamento / Piso"
                  dense
                  outlined
                  placeholder="Piso 1, Dpto 2"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="formAbonado.referencia_direccion"
                  label="Referencia de Ubicación"
                  dense
                  outlined
                  placeholder="Ej: A media cuadra de la plaza principal"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- SECCIÓN 4: CONDICIONES DE SUMINISTRO Y MEDIDOR -->
            <div class="d-flex align-center mt-3 mb-2">
              <v-icon small color="primary" class="mr-1">mdi-water-pump</v-icon>
              <span class="text-caption font-weight-bold text-uppercase primary--text">4. Condiciones del Servicio y Micromedición</span>
            </div>
            <v-divider class="mb-3"></v-divider>

            <v-row dense>
              <v-col cols="12" sm="4">
                <v-select
                  v-model="formAbonado.id_categoria"
                  :items="categorias"
                  item-text="nombre"
                  item-value="id"
                  label="Categoría Tarifaria *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-select
                  v-model="formAbonado.estado_servicio"
                  :items="[
                    { text: 'Activo', value: 'ACTIVO' },
                    { text: 'Cortado / Corte', value: 'CORTE' },
                    { text: 'Suspendido Temporal', value: 'SUSPENDIDO' },
                    { text: 'Permiso Especial', value: 'PERMISO' },
                    { text: 'Dado de Baja', value: 'BAJA' }
                  ]"
                  item-text="text"
                  item-value="value"
                  label="Estado del Servicio *"
                  dense
                  outlined
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="formAbonado.numero_medidor"
                  label="N° Serie Medidor"
                  dense
                  outlined
                  placeholder="Ej: A25LM0412090"
                  :disabled="esEdicion && !!formAbonado.id_medidor_actual"
                  :hint="esEdicion && formAbonado.id_medidor_actual ? 'Use botón Cambiar Medidor en la ficha' : ''"
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-card outlined class="pa-2 mt-1">
                  <v-checkbox
                    v-model="formAbonado.tiene_alcantarillado"
                    label="Cuenta con Servicio de Alcantarillado"
                    color="primary"
                    dense
                    hide-details
                  ></v-checkbox>
                </v-card>
              </v-col>

              <v-col cols="12" sm="6">
                <v-card outlined class="pa-2 mt-1">
                  <v-checkbox
                    v-model="formAbonado.es_tercera_edad"
                    label="Beneficiario 3ra Edad (20% Descuento Ley 1886)"
                    color="purple"
                    dense
                    hide-details
                  ></v-checkbox>
                </v-card>
              </v-col>

              <v-col cols="12" class="mt-2">
                <v-textarea
                  v-model="formAbonado.observaciones"
                  label="Observaciones Generales"
                  rows="2"
                  dense
                  outlined
                  placeholder="Notas adicionales sobre el predio o titular"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalForm = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardando" @click="guardarAbonado">
            <v-icon left small>mdi-content-save</v-icon>
            {{ esEdicion ? 'Actualizar Cambios' : 'Guardar Abonado' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO FICHA ABONADO -->
    <v-dialog v-model="modalFicha" max-width="850">
      <v-card rounded="lg" v-if="abonadoSeleccionado">
        <v-card-title class="primary white--text py-3 d-flex justify-space-between">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-2">mdi-card-account-details-outline</v-icon>
            Ficha del Abonado #{{ abonadoSeleccionado.codigo }}
          </div>
          <v-btn icon color="white" @click="modalFicha = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" md="8">
              <h3 class="text-h6 font-weight-bold">{{ abonadoSeleccionado.nombre_completo }}</h3>
              <div class="text-body-2 text-secondary">
                Documento: {{ abonadoSeleccionado.numero_documento || 'No registrado' }} | Tipo: {{ abonadoSeleccionado.tipo_persona }}
              </div>
              <div class="text-body-2 mt-1">
                <v-icon small color="grey">mdi-map-marker</v-icon>
                {{ abonadoSeleccionado.zona ? abonadoSeleccionado.zona.nombre : '' }}
                {{ abonadoSeleccionado.calle ? ' - ' + abonadoSeleccionado.calle.nombre : '' }}
                {{ abonadoSeleccionado.numero_vivienda ? ' #' + abonadoSeleccionado.numero_vivienda : '' }}
              </div>
            </v-col>
            <v-col cols="12" md="4" class="text-right">
              <v-chip :color="colorEstado(abonadoSeleccionado.estado_servicio)" text-color="white" class="font-weight-bold mb-1">
                {{ abonadoSeleccionado.estado_servicio }}
              </v-chip>
              <div class="text-h6 font-weight-black" :class="abonadoSeleccionado.saldo_deuda > 0 ? 'error--text' : 'success--text'">
                Bs {{ parseFloat(abonadoSeleccionado.saldo_deuda).toFixed(2) }}
              </div>
              <div class="text-caption text-secondary">Deuda Acumulada ({{ abonadoSeleccionado.meses_mora }} meses)</div>
            </v-col>
          </v-row>

          <!-- BOTONES DE ACCIÓN FICHA -->
          <div class="d-flex align-center gap-2 my-3 flex-wrap">
            <v-btn color="amber darken-2" outlined small class="rounded-pill text-capitalize font-weight-bold" @click="abrirModalEditar(abonadoSeleccionado)">
              <v-icon left small>mdi-pencil</v-icon> Editar Datos
            </v-btn>
            <v-btn color="primary" outlined small class="rounded-pill text-capitalize" @click="descargarExtracto(abonadoSeleccionado)">
              <v-icon left small>mdi-file-pdf-box</v-icon> Descargar Extracto Histórico
            </v-btn>
            <v-btn color="warning" outlined small class="rounded-pill text-capitalize" @click="abrirCambioMedidor(abonadoSeleccionado)">
              <v-icon left small>mdi-counter</v-icon> Cambiar Medidor
            </v-btn>
            <v-btn color="error" outlined small class="rounded-pill text-capitalize" v-if="abonadoSeleccionado.estado_servicio !== 'BAJA'" @click="abrirDarBaja(abonadoSeleccionado)">
              <v-icon left small>mdi-account-cancel</v-icon> Dar de Baja
            </v-btn>
          </div>

          <!-- CONDICIONES Y DATOS DETALLADOS HOMOLOGADOS CON FOXPRO -->
          <v-card outlined rounded="lg" class="pa-3 my-3">
            <!-- Badges Técnicos -->
            <div class="d-flex align-center gap-2 flex-wrap mb-2">
              <v-chip x-small :color="abonadoSeleccionado.tiene_alcantarillado ? 'cyan darken-2' : 'grey'" text-color="white" class="font-weight-medium">
                <v-icon left x-small>mdi-pipe</v-icon> {{ abonadoSeleccionado.tiene_alcantarillado ? 'Alcantarillado: SÍ' : 'Alcantarillado: NO' }}
              </v-chip>
              <v-chip x-small :color="abonadoSeleccionado.es_tercera_edad ? 'purple darken-1' : 'grey'" text-color="white" class="font-weight-medium">
                <v-icon left x-small>mdi-account-star</v-icon> {{ abonadoSeleccionado.es_tercera_edad ? '3ra Edad (Ley 1886): SÍ' : '3ra Edad: NO' }}
              </v-chip>
              <v-chip x-small outlined color="primary" class="font-weight-medium">
                <v-icon left x-small>mdi-calendar-check</v-icon> Ingreso: {{ abonadoSeleccionado.fecha_ingreso || 'S/F' }}
              </v-chip>
              <v-chip x-small outlined color="teal" class="font-weight-medium">
                <v-icon left x-small>mdi-counter</v-icon> Medidor: {{ abonadoSeleccionado.medidor_actual ? abonadoSeleccionado.medidor_actual.numero_serie : 'Sin medidor' }}
              </v-chip>
              <v-chip x-small outlined color="indigo" class="font-weight-medium">
                <v-icon left x-small>mdi-tag</v-icon> Cat: {{ abonadoSeleccionado.categoria ? abonadoSeleccionado.categoria.nombre : '-' }}
              </v-chip>
            </div>

            <!-- Fila de Contacto y Dirección -->
            <v-row dense class="text-caption mt-1">
              <v-col cols="12" sm="6">
                <div><strong>C.I. / Documento:</strong> {{ abonadoSeleccionado.numero_documento || '-' }} {{ abonadoSeleccionado.complemento ? '(' + abonadoSeleccionado.complemento + ')' : '' }}</div>
                <div><strong>Celular:</strong> {{ abonadoSeleccionado.celular || 'No registrado' }} | <strong>Teléfono:</strong> {{ abonadoSeleccionado.telefono || '-' }}</div>
                <div><strong>Correo Electrónico:</strong> {{ abonadoSeleccionado.email || 'No registrado' }}</div>
                <div><strong>Persona Contacto / Ref:</strong> {{ abonadoSeleccionado.persona_contacto || '-' }}</div>
              </v-col>
              <v-col cols="12" sm="6">
                <div><strong>Zona:</strong> {{ abonadoSeleccionado.zona ? abonadoSeleccionado.zona.nombre : '-' }}</div>
                <div><strong>Calle / Avenida:</strong> {{ abonadoSeleccionado.calle ? abonadoSeleccionado.calle.nombre : 'S/N' }}</div>
                <div><strong>N° Vivienda / Edif / Dpto:</strong> {{ [abonadoSeleccionado.numero_vivienda ? '#' + abonadoSeleccionado.numero_vivienda : '', abonadoSeleccionado.edificio ? 'Edif. ' + abonadoSeleccionado.edificio : '', abonadoSeleccionado.departamento ? 'Dpto. ' + abonadoSeleccionado.departamento : ''].filter(Boolean).join(' - ') || 'S/N' }}</div>
                <div v-if="abonadoSeleccionado.referencia_direccion"><strong>Referencia:</strong> {{ abonadoSeleccionado.referencia_direccion }}</div>
              </v-col>
              <v-col cols="12" v-if="abonadoSeleccionado.observaciones" class="mt-1">
                <div class="text-secondary"><strong>Observaciones:</strong> {{ abonadoSeleccionado.observaciones }}</div>
              </v-col>
            </v-row>
          </v-card>

          <v-divider class="my-3"></v-divider>

          <v-tabs v-model="tabFicha" color="primary" dense>
            <v-tab><v-icon small left>mdi-counter</v-icon> Historial Lecturas</v-tab>
            <v-tab><v-icon small left>mdi-handshake-outline</v-icon> Convenios</v-tab>
          </v-tabs>

          <v-tabs-items v-model="tabFicha" class="pt-3">
            <v-tab-item>
              <div style="max-height: 420px; overflow-y: auto;">
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr>
                        <th>Periodo</th>
                        <th class="text-right">Anterior</th>
                        <th class="text-right">Actual</th>
                        <th class="text-right">Consumo m³</th>
                        <th class="text-right">Total Bs</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Fecha Pago</th>
                        <th class="text-center">N° Factura</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="lec in abonadoSeleccionado.lecturas" :key="lec.id">
                        <td><strong>{{ lec.periodo ? lec.periodo.periodo : '-' }}</strong></td>
                        <td class="text-right">{{ lec.lectura_anterior }}</td>
                        <td class="text-right">{{ lec.lectura_actual }}</td>
                        <td class="text-right font-weight-bold">{{ lec.consumo_m3 }}</td>
                        <td class="text-right font-weight-bold">Bs {{ parseFloat(lec.total_facturado).toFixed(2) }}</td>
                        <td class="text-center">
                          <v-chip x-small :color="lec.estado_pago === 'PAGADO' ? 'success' : 'warning'" text-color="white">
                            {{ lec.estado_pago }}
                          </v-chip>
                        </td>
                        <td class="text-center text-caption font-weight-medium">
                          <span v-if="lec.fecha_pago" class="success--text">
                            <v-icon x-small color="success">mdi-calendar-check</v-icon> {{ formatearFecha(lec.fecha_pago) }}
                          </span>
                          <span v-else class="grey--text">-</span>
                        </td>
                        <td class="text-center text-caption">
                          <template v-if="lec.factura_siat && lec.factura_siat.numero_factura">
                            <v-btn
                              x-small
                              text
                              color="primary"
                              class="font-weight-bold"
                              @click="abrirFacturaDesdeFicha(lec.factura_siat.id, lec.factura_siat.numero_factura)"
                            >
                              <v-icon x-small left>mdi-file-document-outline</v-icon> #{{ lec.factura_siat.numero_factura }}
                            </v-btn>
                          </template>
                          <span v-else-if="lec.id_factura" class="font-weight-medium">
                            #{{ lec.id_factura }}
                          </span>
                          <span v-else class="grey--text">-</span>
                        </td>
                      </tr>
                      <tr v-if="!abonadoSeleccionado.lecturas || abonadoSeleccionado.lecturas.length === 0">
                        <td colspan="8" class="text-center text-secondary py-3">No registra lecturas en el historial</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </div>
            </v-tab-item>

            <v-tab-item>
              <div v-if="abonadoSeleccionado.convenios && abonadoSeleccionado.convenios.length > 0">
                <v-card outlined class="mb-2 pa-3" v-for="conv in abonadoSeleccionado.convenios" :key="conv.id">
                  <div class="d-flex justify-space-between align-center">
                    <div>
                      <strong>{{ conv.numero_convenio }}</strong> - {{ conv.plazo_meses }} Cuotas de Bs {{ conv.monto_cuota_mensual }}
                      <div class="text-caption text-secondary">Suscrito: {{ conv.fecha_suscripcion }}</div>
                    </div>
                    <v-chip small color="primary" outlined>{{ conv.estado }}</v-chip>
                  </div>
                </v-card>
              </div>
              <div v-else class="text-center text-secondary py-4">No tiene convenios de pago registrados</div>
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CAMBIO DE MEDIDOR -->
    <v-dialog v-model="modalCambioMedidor" max-width="500">
      <v-card rounded="lg">
        <v-card-title class="warning white--text py-3">
          <v-icon color="white" class="mr-2">mdi-counter</v-icon>
          Reemplazo / Cambio de Medidor
        </v-card-title>
        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.lectura_final_anterior"
                label="Lectura Final Medidor Anterior *"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.numero_serie_nuevo"
                label="N° Serie Nuevo Medidor *"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.marca"
                label="Marca Nuevo Medidor"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formCambio.lectura_inicial_nuevo"
                label="Lectura Inicial Nuevo"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="formCambio.motivo"
                label="Motivo del Reemplazo *"
                rows="2"
                dense
                outlined
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalCambioMedidor = false">Cancelar</v-btn>
          <v-btn color="warning" :loading="guardandoCambio" @click="confirmarCambioMedidor">
            Registrar Cambio
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO DAR DE BAJA -->
    <v-dialog v-model="modalDarBaja" max-width="450">
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3">
          <v-icon color="white" class="mr-2">mdi-account-cancel</v-icon>
          Baja Definitiva de Servicio
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="text-body-2 mb-3">
            ¿Está seguro de procesar la baja definitiva del servicio para este abonado?
          </div>
          <v-text-field
            v-model="formBaja.lectura_retiro"
            label="Lectura de Retiro del Medidor"
            type="number"
            dense
            outlined
          ></v-text-field>
          <v-textarea
            v-model="formBaja.motivo"
            label="Motivo de la Baja Definitiva *"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalDarBaja = false">Cancelar</v-btn>
          <v-btn color="error" :loading="guardandoBaja" @click="confirmarDarBaja">
            Confirmar Baja
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- VISOR UNIVERSAL MODAL DE PDF (EXTRACTO DE CUENTA / HISTORIAL) -->
    <modal-visor-pdf
      v-model="mostrarVisorPdf"
      :url="urlVisorPdf"
      :titulo="tituloVisorPdf"
      :subtitulo="subtituloVisorPdf"
    ></modal-visor-pdf>
  </div>
</template>

<script>
import axios from 'axios';
import ModalVisorPdf from '@/components/ModalVisorPdf.vue';

export default {
  name: 'PadronAbonados',
  components: {
    ModalVisorPdf,
  },
  data() {
    return {
      mostrarVisorPdf: false,
      urlVisorPdf: '',
      tituloVisorPdf: '',
      subtituloVisorPdf: '',
      cargando: false,
      guardando: false,
      tipoBusqueda: 'todos',
      tiposBusqueda: [
        { valor: 'todos', texto: 'Todos los campos' },
        { valor: 'codigo_abonado', texto: 'Código Abonado' },
        { valor: 'carnet_nit', texto: 'C.I. / NIT' },
        { valor: 'cliente', texto: 'Nombres / Apellidos' },
        { valor: 'medidor', texto: 'N° de Medidor' },
      ],
      estadosOpciones: [
        { valor: 'TODOS', texto: 'Todos los Estados' },
        { valor: 'ACTIVO', texto: 'Activo' },
        { valor: 'CORTE', texto: 'En Corte' },
        { valor: 'SUSPENDIDO', texto: 'Suspendido' },
        { valor: 'BAJA', texto: 'Baja Definitiva' },
        { valor: 'PERMISO', texto: 'Permiso Temporal' },
        { valor: 'EN_MORA', texto: 'En Mora (>= 2 meses)' },
      ],
      busqueda: '',
      filtroZona: null,
      filtroCategoria: null,
      filtroEstado: 'TODOS',
      abonados: [],
      zonas: [],
      categorias: [],
      callesForm: [],
      totalAbonados: 0,
      totalGlobalAbonados: 0,
      activosCount: 0,
      moraCount: 0,
      cortadosCount: 0,
      opcionesPaginacion: {},
      columnas: [
        { text: 'Código', value: 'codigo', width: '90px' },
        { text: 'Abonado / Razón Social', value: 'nombre_completo' },
        { text: 'Ubicación', value: 'ubicacion' },
        { text: 'Categoría', value: 'categoria', width: '130px' },
        { text: 'Medidor', value: 'medidor', width: '120px' },
        { text: 'Estado', value: 'estado_servicio', width: '110px' },
        { text: 'Saldo Deuda', value: 'saldo_deuda', align: 'end', width: '130px' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '90px' },
      ],
      modalForm: false,
      esEdicion: false,
      modalFicha: false,
      modalCambioMedidor: false,
      guardandoCambio: false,
      formCambio: {
        lectura_final_anterior: 0,
        numero_serie_nuevo: '',
        marca: '',
        lectura_inicial_nuevo: 0,
        motivo: '',
      },
      modalDarBaja: false,
      guardandoBaja: false,
      formBaja: {
        lectura_retiro: null,
        motivo: '',
      },
      formValido: true,
      tabFicha: 0,
      abonadoSeleccionado: null,
      formAbonado: {
        id: null,
        codigo: '',
        tipo_persona: 'NATURAL',
        primer_apellido: '',
        segundo_apellido: '',
        nombres: '',
        nombre_completo: '',
        numero_documento: '',
        complemento: '',
        telefono: '',
        celular: '',
        email: '',
        persona_contacto: '',
        id_zona: null,
        id_calle: null,
        numero_vivienda: '',
        edificio: '',
        departamento: '',
        referencia_direccion: '',
        id_categoria: null,
        estado_servicio: 'ACTIVO',
        fecha_ingreso: '',
        numero_medidor: '',
        tiene_alcantarillado: true,
        es_tercera_edad: false,
        observaciones: '',
      },
    };
  },
  computed: {
    etiquetaBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Buscar por Código de Abonado';
        case 'carnet_nit': return 'Buscar por C.I. o NIT';
        case 'cliente': return 'Buscar por Nombres o Apellidos';
        case 'medidor': return 'Buscar por N° de Medidor';
        default: return 'Buscar por Código, CI/NIT, Nombre o Medidor...';
      }
    },
    placeholderBusqueda() {
      switch (this.tipoBusqueda) {
        case 'codigo_abonado': return 'Ej: 05586 o 5586';
        case 'carnet_nit': return 'Ej: 2163442';
        case 'cliente': return 'Ej: CARI MAMANI';
        case 'medidor': return 'Ej: A25LM0412090';
        default: return 'Escriba criterio de búsqueda...';
      }
    },
  },
  watch: {
    opcionesPaginacion: {
      handler() {
        this.cargarAbonados();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarParametricas();
    this.cargarAbonados();
  },
  methods: {
    alCambiarTipoBusqueda() {
      if (this.busqueda) {
        this.cargarAbonados();
      }
    },
    onClearBusqueda() {
      this.busqueda = '';
      this.cargarAbonados();
    },
    colorEstado(estado) {
      switch (estado) {
        case 'ACTIVO': return 'success';
        case 'CORTE':
        case 'CORTADO': return 'error';
        case 'SUSPENDIDO': return 'blue-grey darken-1';
        case 'PERMISO': return 'cyan darken-2';
        case 'EN_MORA': return 'warning';
        case 'BAJA': return 'grey darken-2';
        default: return 'primary';
      }
    },
    async cargarParametricas() {
      try {
        const [resZonas, resCategorias] = await Promise.all([
          axios.get('/api/comercial/zonas'),
          axios.get('/api/comercial/tarifas'),
        ]);
        this.zonas = resZonas.data.data || [];
        this.categorias = resCategorias.data.data || [];
      } catch (e) {
        console.error('Error cargando zonas y tarifas:', e);
      }
    },
    async cargarCallesPorZona(idZona) {
      const zonaId = idZona || this.formAbonado.id_zona;
      if (!zonaId) {
        this.callesForm = [];
        return;
      }
      try {
        const res = await axios.get('/api/comercial/calles', {
          params: { id_zona: zonaId },
        });
        this.callesForm = res.data.data || [];
      } catch (e) {
        console.error('Error cargando calles:', e);
      }
    },
    actualizarNombreCompleto() {
      if (this.formAbonado.tipo_persona === 'NATURAL') {
        const partes = [
          this.formAbonado.primer_apellido,
          this.formAbonado.segundo_apellido,
          this.formAbonado.nombres
        ].map(p => (p || '').trim()).filter(Boolean);
        if (partes.length > 0) {
          this.formAbonado.nombre_completo = partes.join(' ').toUpperCase();
        }
      }
    },
    async cargarAbonados() {
      this.cargando = true;
      const { page, itemsPerPage } = this.opcionesPaginacion;
      try {
        const res = await axios.get('/api/comercial/abonados', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            search: this.busqueda || undefined,
            tipo_busqueda: this.tipoBusqueda,
            id_zona: this.filtroZona || undefined,
            id_categoria: this.filtroCategoria || undefined,
            estado_servicio: this.filtroEstado !== 'TODOS' ? this.filtroEstado : undefined,
          },
        });

        this.abonados = res.data.data || [];
        this.totalAbonados = res.data.total || 0;

        // Si el backend devuelve resumen global, utilizarlo para que las tarjetas no se reinicien al buscar
        if (res.data.resumen) {
          this.totalGlobalAbonados = res.data.resumen.total_abonados || 0;
          this.activosCount = res.data.resumen.servicios_activos || 0;
          this.moraCount = res.data.resumen.en_mora || 0;
          this.cortadosCount = res.data.resumen.servicios_cortados || 0;
        } else {
          this.activosCount = this.abonados.filter(a => a.estado_servicio === 'ACTIVO').length;
          this.moraCount = this.abonados.filter(a => a.meses_mora >= 2).length;
          this.cortadosCount = this.abonados.filter(a => ['CORTE', 'CORTADO'].includes(a.estado_servicio)).length;
        }
      } catch (e) {
        console.error('Error cargando abonados:', e);
      } finally {
        this.cargando = false;
      }
    },
    abrirModalNuevo() {
      this.esEdicion = false;
      this.formAbonado = {
        id: null,
        codigo: '',
        tipo_persona: 'NATURAL',
        primer_apellido: '',
        segundo_apellido: '',
        nombres: '',
        nombre_completo: '',
        numero_documento: '',
        complemento: '',
        telefono: '',
        celular: '',
        email: '',
        persona_contacto: '',
        id_zona: this.zonas.length > 0 ? this.zonas[0].id : null,
        id_calle: null,
        numero_vivienda: '',
        edificio: '',
        departamento: '',
        referencia_direccion: '',
        id_categoria: this.categorias.length > 0 ? this.categorias[0].id : null,
        estado_servicio: 'ACTIVO',
        fecha_ingreso: new Date().toISOString().slice(0, 10),
        numero_medidor: '',
        tiene_alcantarillado: true,
        es_tercera_edad: false,
        observaciones: '',
      };
      this.cargarCallesPorZona(this.formAbonado.id_zona);
      this.modalForm = true;
    },
    async abrirModalEditar(item) {
      if (!item) return;
      this.esEdicion = true;
      try {
        const res = await axios.get(`/api/comercial/abonados/${item.id}`);
        const ab = res.data.data;
        this.formAbonado = {
          id: ab.id,
          codigo: ab.codigo,
          tipo_persona: ab.tipo_persona || 'NATURAL',
          primer_apellido: ab.primer_apellido || '',
          segundo_apellido: ab.segundo_apellido || '',
          nombres: ab.nombres || '',
          nombre_completo: ab.nombre_completo || '',
          numero_documento: ab.numero_documento || '',
          complemento: ab.complemento || '',
          telefono: ab.telefono || '',
          celular: ab.celular || '',
          email: ab.email || '',
          persona_contacto: ab.persona_contacto || '',
          id_zona: ab.id_zona,
          id_calle: ab.id_calle,
          numero_vivienda: ab.numero_vivienda || '',
          edificio: ab.edificio || '',
          departamento: ab.departamento || '',
          referencia_direccion: ab.referencia_direccion || '',
          id_categoria: ab.id_categoria,
          estado_servicio: ab.estado_servicio || 'ACTIVO',
          fecha_ingreso: ab.fecha_ingreso ? ab.fecha_ingreso.slice(0, 10) : '',
          id_medidor_actual: ab.id_medidor_actual,
          numero_medidor: ab.medidor_actual ? ab.medidor_actual.numero_serie : '',
          tiene_alcantarillado: !!ab.tiene_alcantarillado,
          es_tercera_edad: !!ab.es_tercera_edad,
          observaciones: ab.observaciones || '',
        };
        await this.cargarCallesPorZona(this.formAbonado.id_zona);
        this.modalForm = true;
      } catch (e) {
        console.error('Error al cargar abonado para editar:', e);
        alert('No se pudo cargar la información del abonado.');
      }
    },
    async guardarAbonado() {
      if (!this.formAbonado.nombre_completo || !this.formAbonado.id_zona || !this.formAbonado.id_categoria) {
        alert('Por favor complete los campos obligatorios (*).');
        return;
      }

      this.guardando = true;
      try {
        if (this.esEdicion) {
          await axios.put(`/api/comercial/abonados/${this.formAbonado.id}`, this.formAbonado);
        } else {
          await axios.post('/api/comercial/abonados', this.formAbonado);
        }
        this.modalForm = false;
        this.cargarAbonados();
        if (this.abonadoSeleccionado && this.abonadoSeleccionado.id === this.formAbonado.id) {
          this.verFicha(this.formAbonado);
        }
      } catch (e) {
        alert(e.response?.data?.message || 'Error al guardar abonado.');
      } finally {
        this.guardando = false;
      }
    },
    async verFicha(item) {
      try {
        const res = await axios.get(`/api/comercial/abonados/${item.id}`);
        this.abonadoSeleccionado = res.data.data;
        this.modalFicha = true;
      } catch (e) {
        console.error('Error cargando ficha:', e);
      }
    },
    descargarExtracto(item) {
      if (!item) return;
      this.urlVisorPdf = `/api/comercial/abonados/${item.id}/extracto/pdf`;
      this.tituloVisorPdf = `Extracto de Cuenta - Abonado #${item.codigo}`;
      this.subtituloVisorPdf = `${item.nombre_completo || ''} | NIT/CI: ${item.numero_documento || 'S/N'}`;
      this.mostrarVisorPdf = true;
    },
    abrirFacturaDesdeFicha(facturaId, numeroFactura) {
      this.urlVisorPdf = `/api/facturacion/facturas/${facturaId}/pdf?formato=rollo`;
      this.tituloVisorPdf = `Factura Electrónica SIAT #${numeroFactura}`;
      this.subtituloVisorPdf = 'Comprobante Oficial Autorizado por el SIN';
      this.mostrarVisorPdf = true;
    },
    abrirCambioMedidor(item) {
      this.formCambio = {
        lectura_final_anterior: item.medidor_actual?.lectura_inicial || 0,
        numero_serie_nuevo: '',
        marca: '',
        lectura_inicial_nuevo: 0,
        motivo: '',
      };
      this.modalCambioMedidor = true;
    },
    async confirmarCambioMedidor() {
      if (!this.formCambio.numero_serie_nuevo || !this.formCambio.motivo) {
        alert('Ingrese el nuevo número de serie y el motivo.');
        return;
      }
      this.guardandoCambio = true;
      try {
        await axios.post(`/api/comercial/abonados/${this.abonadoSeleccionado.id}/cambiar-medidor`, this.formCambio);
        this.modalCambioMedidor = false;
        await this.verFicha(this.abonadoSeleccionado);
        this.cargarAbonados();
        alert('Medidor reemplazado exitosamente.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al reemplazar medidor.');
      } finally {
        this.guardandoCambio = false;
      }
    },
    abrirDarBaja(item) {
      this.formBaja = {
        lectura_retiro: item.medidor_actual?.lectura_inicial || null,
        motivo: '',
      };
      this.modalDarBaja = true;
    },
    async confirmarDarBaja() {
      if (!this.formBaja.motivo) {
        alert('Debe ingresar un motivo para la baja definitiva.');
        return;
      }
      this.guardandoBaja = true;
      try {
        await axios.post(`/api/comercial/abonados/${this.abonadoSeleccionado.id}/dar-baja`, this.formBaja);
        this.modalDarBaja = false;
        await this.verFicha(this.abonadoSeleccionado);
        this.cargarAbonados();
        alert('Servicio dado de baja.');
      } catch (e) {
        alert(e.response?.data?.message || 'Error al dar de baja servicio.');
      } finally {
        this.guardandoBaja = false;
      }
    },
    formatearFecha(fecha) {
      if (!fecha) return '-';
      const f = fecha.toString().substring(0, 10);
      const partes = f.split('-');
      if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
      }
      return f;
    },
  },
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}
</style>
