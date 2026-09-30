<template>
  <div class="migrador-respaldos-wrapper">
    <!-- ============================================================== -->
    <!-- 1. TOPBAR ELEGANTE ADAPTABLE A AMBOS TEMAS (DARK / LIGHT)       -->
    <!-- ============================================================== -->
    <v-card class="mb-5 py-3 px-4 migrador-topbar" rounded="lg" elevation="2">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center my-1">
          <v-avatar class="mr-3 topbar-avatar elevation-2" size="48" rounded="lg">
            <v-icon color="white" size="26">mdi-database-sync-outline</v-icon>
          </v-avatar>
          <div>
            <div class="d-flex align-center gap-2">
              <h2 class="text-h5 font-weight-black mb-0 topbar-title">Centro de Control de Migraciones y Respaldos</h2>
              <v-chip x-small color="primary" outlined class="font-weight-bold ml-2">EMAPAP</v-chip>
            </div>
            <span class="text-caption topbar-subtitle">
              Bitácora histórica, auditoría multiesquema FoxPro / PostgreSQL y motor de reversión transaccional
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 my-1 flex-wrap">
          <v-btn
            outlined
            class="text-capitalize font-weight-medium rounded-pill mr-2 topbar-btn-alt"
            :loading="cargandoHistorial"
            @click="cargarHistorial"
            small
          >
            <v-icon left small>mdi-refresh</v-icon>
            Refrescar Bitácora
          </v-btn>

          <v-btn
            color="primary"
            dark
            class="text-capitalize font-weight-bold rounded-pill elevation-3 px-4"
            small
            @click="abrirModalNuevaMigracion"
          >
            <v-icon left small>mdi-plus-circle-outline</v-icon>
            ✨ Nueva Migración
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- ============================================================== -->
    <!-- 2. TARJETAS DE RESUMEN KPI (GLOBAL DASHBOARD)                  -->
    <!-- ============================================================== -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4 kpi-card" rounded="lg" outlined>
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase text-secondary font-weight-bold">Migraciones Registradas</div>
              <div class="text-h4 font-weight-black primary--text mt-1">{{ kpis.total_migraciones }}</div>
              <div class="text-caption text-secondary">Ejecuciones en bitácora</div>
            </div>
            <v-avatar color="primary lighten-5" rounded="lg" size="48">
              <v-icon color="primary" size="26">mdi-history</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4 kpi-card" rounded="lg" outlined>
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase text-secondary font-weight-bold">Total Procesados (Real)</div>
              <div class="text-h4 font-weight-black info--text mt-1">{{ formatearNumero(kpis.total_procesados) }}</div>
              <div class="text-caption text-secondary">Registros leídos en FoxPro</div>
            </div>
            <v-avatar color="blue lighten-5" rounded="lg" size="48">
              <v-icon color="info" size="26">mdi-database-arrow-right</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4 kpi-card" rounded="lg" outlined>
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase text-secondary font-weight-bold">Sincronizados en BD</div>
              <div class="text-h4 font-weight-black success--text mt-1">{{ formatearNumero(kpis.total_correctos) }}</div>
              <div class="text-caption text-secondary">Registros validados en PostgreSQL</div>
            </div>
            <v-avatar color="green lighten-5" rounded="lg" size="48">
              <v-icon color="success" size="26">mdi-check-decagram</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4 kpi-card" rounded="lg" outlined>
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-uppercase text-secondary font-weight-bold">Estado del Sistema</div>
              <div class="mt-2">
                <v-chip
                  small
                  :color="kpis.ultimo_estado === 'EXITO' ? 'success' : 'info'"
                  class="font-weight-bold text-uppercase"
                >
                  <v-icon left x-small>
                    {{ kpis.ultimo_estado === 'EXITO' ? 'mdi-check-circle' : 'mdi-information' }}
                  </v-icon>
                  {{ kpis.ultimo_estado === 'EXITO' ? 'Consistente / Operativo' : 'Activo' }}
                </v-chip>
              </div>
              <div class="text-caption text-secondary mt-1">
                Última acción: {{ formatearFechaRelativa(kpis.ultima_ejecucion) }}
              </div>
            </div>
            <v-avatar color="teal lighten-5" rounded="lg" size="48">
              <v-icon color="teal darken-1" size="26">mdi-shield-check-outline</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ============================================================== -->
    <!-- 3. TABLA PRINCIPAL: HISTORIAL Y BITÁCORA DE MIGRACIONES        -->
    <!-- ============================================================== -->
    <v-card class="erp-card-elevated" rounded="lg">
      <v-card-title class="d-flex align-center justify-space-between flex-wrap py-3 px-4 border-bottom">
        <div class="d-flex align-center">
          <v-icon color="primary" class="mr-2">mdi-format-list-bulleted-type</v-icon>
          <span class="text-h6 font-weight-bold">Bitácora de Ejecuciones y Trazabilidad</span>
        </div>

        <!-- Filtros Rápidos -->
        <div class="d-flex align-center gap-3 mt-2 mt-sm-0 flex-wrap">
          <v-text-field
            v-model="busquedaHistorial"
            placeholder="Buscar por Job ID, módulo o mensaje..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            style="min-width: 260px;"
          ></v-text-field>

          <v-select
            v-model="filtroTipoHistorial"
            :items="opcionesTipoHistorial"
            dense
            outlined
            hide-details
            style="max-width: 170px;"
          ></v-select>

          <v-btn
            color="primary"
            outlined
            small
            class="rounded-pill font-weight-bold text-capitalize"
            @click="abrirModalNuevaMigracion"
          >
            <v-icon left small>mdi-plus</v-icon>
            Nueva Migración
          </v-btn>
        </div>
      </v-card-title>

      <!-- Tabla de Logs -->
      <v-data-table
        :headers="headersHistorial"
        :items="logsFiltrados"
        :loading="cargandoHistorial"
        :search="busquedaHistorial"
        :items-per-page="15"
        class="elevation-0"
        no-data-text="No se encontraron registros en la bitácora de migraciones."
      >
        <!-- Columna: Fecha y Hora -->
        <template v-slot:item.created_at="{ item }">
          <div class="font-weight-medium text-caption">{{ formatearFecha(item.created_at) }}</div>
          <span class="text-caption text-secondary">{{ item.created_at ? item.created_at.substring(11, 19) : '' }}</span>
        </template>

        <!-- Columna: Job ID -->
        <template v-slot:item.job_id="{ item }">
          <div class="d-flex align-center">
            <v-chip x-small label outlined class="font-monospace mr-1">
              {{ item.job_id ? item.job_id.substring(0, 8) : '-' }}...
            </v-chip>
            <v-btn icon x-small @click="copiarTexto(item.job_id)" title="Copiar Job ID">
              <v-icon x-small>mdi-content-copy</v-icon>
            </v-btn>
          </div>
        </template>

        <!-- Columna: Módulo -->
        <template v-slot:item.modulo="{ item }">
          <v-chip small :color="getColorModulo(item.modulo)" outlined class="font-weight-bold text-capitalize">
            <v-icon left x-small>{{ getIconoModulo(item.modulo) }}</v-icon>
            {{ getLabelModulo(item.modulo) }}
          </v-chip>
        </template>

        <!-- Columna: Tipo (Simulación vs Real) -->
        <template v-slot:item.es_simulacion="{ item }">
          <v-chip
            x-small
            :color="item.es_simulacion ? 'warning' : 'success'"
            class="font-weight-bold text-uppercase"
            text-color="white"
          >
            <v-icon left x-small>{{ item.es_simulacion ? 'mdi-shield-outline' : 'mdi-database-edit' }}</v-icon>
            {{ item.es_simulacion ? 'Simulación (Dry-Run)' : 'Escritura Real' }}
          </v-chip>
        </template>

        <!-- Columna: Registros -->
        <template v-slot:item.registros_procesados="{ item }">
          <div class="d-flex align-center gap-1 justify-end font-monospace text-caption">
            <span class="font-weight-bold primary--text">{{ formatearNumero(item.registros_procesados) }}</span>
            <span class="text-secondary">/</span>
            <span class="success--text font-weight-bold" title="Insertados correctamente">{{ formatearNumero(item.registros_correctos) }}</span>
            <span v-if="item.registros_erroneos > 0" class="error--text font-weight-bold ml-1" title="Omitidos / Advertencias">
              ({{ formatearNumero(item.registros_erroneos) }})
            </span>
          </div>
        </template>

        <!-- Columna: Mensaje -->
        <template v-slot:item.mensaje="{ item }">
          <div class="text-caption text-truncate" style="max-width: 280px;" :title="item.mensaje">
            <span v-if="esRevertido(item.mensaje)" class="error--text font-weight-bold mr-1">[REVERTIDO]</span>
            {{ item.mensaje }}
          </div>
        </template>

        <!-- Columna: Acciones -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center justify-end gap-1">
            <v-btn
              icon
              small
              color="primary"
              title="Ver Detalles y Trazabilidad"
              @click="abrirDetalleLog(item)"
            >
              <v-icon small>mdi-file-document-outline</v-icon>
            </v-btn>

            <v-btn
              icon
              small
              color="error"
              :disabled="esRevertido(item.mensaje)"
              :title="esRevertido(item.mensaje) ? 'Esta migración ya fue revertida' : 'Revertir / Deshacer cambios (Rollback)'"
              @click="abrirDialogRollback(item)"
            >
              <v-icon small>mdi-backup-restore</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- ============================================================== -->
    <!-- 4. MODAL POPUP: MIGRACIÓN DIRECTA EN SEGUNDO PLANO             -->
    <!-- ============================================================== -->
    <v-dialog v-model="modalNuevaMigracion" :max-width="vistaModal === 'comparativa' ? 1220 : 850" persistent scrollable>
      <v-card class="rounded-xl overflow-hidden">
        <!-- CABECERA DEL MODAL -->
        <v-card-title class="modal-header py-3 px-5 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="primary lighten-1" size="40" class="mr-3 text-white elevation-2">
              <v-icon color="white" size="22">mdi-database-sync</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold mb-0 white--text">
                {{ vistaModal === 'origen' ? 'Nueva Migración de Respaldos FoxPro' : (vistaModal === 'comparativa' ? 'Auditoría Volumétrica: Qué y Cuánto se Migrará' : 'Centro de Control: Migración en Segundo Plano') }}
              </h3>
              <span class="text-caption text-light-translucent">
                {{ vistaModal === 'origen' ? 'Selección de respaldo y modo de operación — El ERP continuará 100% disponible' : (vistaModal === 'comparativa' ? 'Consulte tablas, registros pendientes por migrar y porcentaje de sincronización' : 'Ejecución desacoplada en hilo independiente — Sin lentitud para los usuarios') }}
              </span>
            </div>
          </div>

          <v-btn icon dark :disabled="cancelandoJob || subiendoArchivo" @click="cerrarModalNuevaMigracion">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <!-- INDICADOR DE ETAPAS (1. Respaldo -> 2. Qué y Cuánto se Migrará -> 3. Ejecución Hilo) -->
        <div class="modal-stepper-bar px-5 py-2 border-bottom d-flex align-center justify-center flex-wrap gap-2">
          <v-chip
            :color="vistaModal === 'origen' ? 'primary' : ''"
            small
            class="font-weight-bold"
            :outlined="vistaModal !== 'origen'"
            @click="ejecutandoFondo ? null : vistaModal = 'origen'"
          >
            <v-icon left x-small>{{ vistaModal === 'comparativa' || vistaModal === 'ejecucion' ? 'mdi-check-circle' : 'mdi-numeric-1-circle' }}</v-icon>
            1. Selección de Respaldo (.ZIP)
          </v-chip>

          <v-icon small color="grey lighten-1">mdi-chevron-right</v-icon>

          <v-chip
            :color="vistaModal === 'comparativa' ? 'primary' : ''"
            small
            class="font-weight-bold"
            :outlined="vistaModal !== 'comparativa'"
            :disabled="!totalDbfsEncontrados && vistaModal === 'origen'"
            @click="totalDbfsEncontrados && !ejecutandoFondo ? vistaModal = 'comparativa' : null"
          >
            <v-icon left x-small>{{ vistaModal === 'ejecucion' ? 'mdi-check-circle' : 'mdi-numeric-2-circle' }}</v-icon>
            2. Qué y Cuánto se Migrará (Auditoría)
          </v-chip>

          <v-icon small color="grey lighten-1">mdi-chevron-right</v-icon>

          <v-chip
            :color="vistaModal === 'ejecucion' ? 'primary' : ''"
            small
            class="font-weight-bold"
            :outlined="vistaModal !== 'ejecucion'"
            :disabled="!ejecutandoFondo && !jobCompletado"
          >
            <v-icon left x-small>mdi-numeric-3-circle</v-icon>
            3. Ejecución en Hilo Desacoplado
          </v-chip>
        </div>

        <v-card-text class="pa-5" style="max-height: 72vh;">
          <!-- ============================================================== -->
          <!-- ETAPA 1: SELECCIÓN Y SUBIDA DE RESPALDO                        -->
          <!-- ============================================================== -->
          <div v-if="vistaModal === 'origen'">
            <!-- SECCIÓN 1: SELECCIONAR RESPALDO -->
            <v-card outlined rounded="lg" class="pa-4 mb-4">
              <div class="d-flex align-center mb-2">
                <v-avatar color="primary lighten-5" size="36" class="mr-3">
                  <v-icon color="primary" small>mdi-cloud-upload-outline</v-icon>
                </v-avatar>
                <div>
                  <h4 class="text-subtitle-1 font-weight-bold mb-0">1. Seleccionar Respaldo FoxPro (.ZIP)</h4>
                  <span class="text-caption text-secondary">
                    Seleccione el archivo comprimido (.zip) desde su equipo con la carpeta DATA o tablas .DBF
                  </span>
                </div>
              </div>

              <v-file-input
                v-model="archivoSubida"
                outlined
                dense
                show-size
                clearable
                accept=".zip,.rar,.tar,.gz"
                prepend-icon=""
                prepend-inner-icon="mdi-folder-zip-outline"
                placeholder="Haga clic aquí para explorar y seleccionar su archivo de respaldo..."
                class="mt-2"
                :disabled="usarRutaLocal || subiendoArchivo"
                hide-details="auto"
              ></v-file-input>

              <!-- Barra de progreso de subida por chunks -->
              <div v-if="subiendoArchivo" class="mt-4 pa-3 bg-light rounded-lg border">
                <div class="d-flex justify-space-between text-caption font-weight-bold mb-1">
                  <span class="primary--text"><v-icon small left color="primary" class="spin-icon">mdi-loading</v-icon> {{ mensajeSubida }}</span>
                  <span>{{ progresoSubida }}%</span>
                </div>
                <v-progress-linear
                  :value="progresoSubida"
                  color="primary"
                  height="8"
                  rounded
                  striped
                ></v-progress-linear>
              </div>

              <!-- Opción alternativa: Servidor Local -->
              <div class="mt-3">
                <v-checkbox
                  v-model="usarRutaLocal"
                  dense
                  hide-details
                  class="mt-0"
                  :disabled="subiendoArchivo"
                >
                  <template v-slot:label>
                    <span class="text-caption text-secondary font-weight-medium">
                      O ingresar manualmente una ruta de carpeta local en el servidor
                    </span>
                  </template>
                </v-checkbox>

                <v-text-field
                  v-if="usarRutaLocal"
                  v-model="rutaManual"
                  label="Ruta absoluta de la carpeta DATA en el servidor"
                  outlined
                  dense
                  hide-details
                  prepend-inner-icon="mdi-folder-outline"
                  placeholder="/ruta/al/respaldo/DATA"
                  class="mt-2"
                ></v-text-field>
              </div>
            </v-card>

            <!-- SECCIÓN 2: MODO DE OPERACIÓN -->
            <v-card outlined rounded="lg" class="pa-4 mb-4">
              <div class="d-flex align-center mb-3">
                <v-avatar color="primary lighten-5" size="36" class="mr-3">
                  <v-icon color="primary" small>mdi-tune-vertical</v-icon>
                </v-avatar>
                <div>
                  <h4 class="text-subtitle-1 font-weight-bold mb-0">2. Modo de Operación</h4>
                  <span class="text-caption text-secondary">
                    Decida si desea hacer una prueba diagnóstica o sincronizar directamente
                  </span>
                </div>
              </div>

              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-card
                    outlined
                    rounded="lg"
                    class="pa-3 cursor-pointer transition-all fill-height mode-card"
                    :class="esSimulacion ? 'is-active-sim elevation-1' : ''"
                    @click="esSimulacion = true"
                  >
                    <div class="d-flex align-start">
                      <v-radio-group v-model="esSimulacion" hide-details class="ma-0 pa-0 mr-2 mt-1">
                        <v-radio :value="true" color="primary"></v-radio>
                      </v-radio-group>
                      <div>
                        <div class="font-weight-bold text-caption primary--text d-flex align-center">
                          <v-icon left x-small color="primary">mdi-shield-check</v-icon>
                          Simulación (Dry-Run)
                        </div>
                        <div class="text-caption text-secondary mt-1">
                          Verifica consistencia y lee los archivos DBF sin modificar la base de datos PostgreSQL.
                        </div>
                      </div>
                    </div>
                  </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                  <v-card
                    outlined
                    rounded="lg"
                    class="pa-3 cursor-pointer transition-all fill-height mode-card"
                    :class="!esSimulacion ? 'is-active-real elevation-1' : ''"
                    @click="esSimulacion = false"
                  >
                    <div class="d-flex align-start">
                      <v-radio-group v-model="esSimulacion" hide-details class="ma-0 pa-0 mr-2 mt-1">
                        <v-radio :value="false" color="success"></v-radio>
                      </v-radio-group>
                      <div>
                        <div class="font-weight-bold text-caption success--text d-flex align-center">
                          <v-icon left x-small color="success">mdi-database-import</v-icon>
                          Migración Oficial (Real)
                        </div>
                        <div class="text-caption text-secondary mt-1">
                          Inserta y sincroniza los abonados, lecturas, facturas y deudas en PostgreSQL.
                        </div>
                      </div>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-card>

            <!-- AVISO DE AISLAMIENTO DE SERVIDOR -->
            <v-alert dense text color="info" class="text-caption mb-0" rounded="lg">
              <div class="d-flex align-center">
                <v-icon left small color="info">mdi-information</v-icon>
                <span>
                  <strong>Aislamiento de Servidor:</strong> Esta migración se ejecuta en un proceso de fondo desacoplado. Mientras se ejecuta, usted y los demás usuarios pueden seguir usando la caja, consultas y facturación del ERP normalmente y sin lentitud.
                </span>
              </div>
            </v-alert>
          </div>

          <!-- ============================================================== -->
          <!-- ETAPA 2: AUDITORÍA VOLUMÉTRICA Y COMPARATIVA POR ESQUEMAS      -->
          <!-- ============================================================== -->
          <div v-else-if="vistaModal === 'comparativa'">
            <!-- BANNER DE RESUMEN VOLUMÉTRICO GENERAL -->
            <v-row dense class="mb-3">
              <v-col cols="12" sm="6" md="3">
                <v-card outlined rounded="lg" class="pa-3 text-center fill-height">
                  <div class="text-caption text-uppercase font-weight-bold text-secondary">FoxPro (Origen)</div>
                  <div class="text-h5 font-weight-black primary--text mt-1">{{ formatearNumero(totalRegistrosDbf) }}</div>
                  <div class="text-caption text-secondary">Registros en DBF</div>
                </v-card>
              </v-col>

              <v-col cols="12" sm="6" md="3">
                <v-card outlined rounded="lg" class="pa-3 text-center fill-height">
                  <div class="text-caption text-uppercase font-weight-bold text-secondary">PostgreSQL (Actual)</div>
                  <div class="text-h5 font-weight-black success--text mt-1">{{ formatearNumero(totalRegistrosPg) }}</div>
                  <div class="text-caption text-secondary">Registros en BD</div>
                </v-card>
              </v-col>

              <v-col cols="12" sm="6" md="3">
                <v-card outlined rounded="lg" class="pa-3 text-center fill-height" :class="totalFaltantePorMigrar > 0 ? 'amber lighten-5 border-warning' : 'green lighten-5 border-success'">
                  <div class="text-caption text-uppercase font-weight-bold text-secondary">Faltan por Migrar</div>
                  <div class="text-h5 font-weight-black warning--text text--darken-2 mt-1">{{ formatearNumero(totalFaltantePorMigrar) }}</div>
                  <div class="text-caption text-secondary">Pendientes de sincronización</div>
                </v-card>
              </v-col>

              <v-col cols="12" sm="6" md="3">
                <v-card outlined rounded="lg" class="pa-3 text-center fill-height">
                  <div class="text-caption text-uppercase font-weight-bold text-secondary">Tablas FoxPro</div>
                  <div class="text-h5 font-weight-black teal--text text--darken-1 mt-1">{{ totalDbfsEncontrados }}</div>
                  <div class="text-caption text-secondary">Archivos .DBF listos</div>
                </v-card>
              </v-col>
            </v-row>

            <!-- MINI TARJETAS POR ESQUEMA -->
            <v-row dense class="mb-3">
              <v-col cols="12" sm="6" md="2" v-for="(modInfo, modKey) in resumenModulos" :key="modKey">
                <v-card outlined rounded="lg" class="pa-2 text-center fill-height" :class="getModuloBorderClass(modKey)">
                  <div class="text-caption text-uppercase font-weight-bold text-secondary mb-1">
                    {{ modInfo.nombre }}
                  </div>
                  <div class="d-flex justify-space-between text-caption px-1">
                    <span class="text-secondary">FoxPro:</span>
                    <span class="font-weight-bold primary--text">{{ formatearNumero(modInfo.dbf) }}</span>
                  </div>
                  <div class="d-flex justify-space-between text-caption px-1">
                    <span class="text-secondary">PostgreSQL:</span>
                    <span class="font-weight-bold teal--text text--darken-2">{{ formatearNumero(modInfo.pg) }}</span>
                  </div>
                </v-card>
              </v-col>

              <v-col cols="12" sm="6" md="2">
                <v-card outlined rounded="lg" class="pa-2 d-flex flex-column justify-center align-center fill-height bg-light">
                  <span class="text-caption font-weight-bold text-secondary">Ruta Activa:</span>
                  <span class="text-caption text-truncate font-monospace" style="max-width: 150px;" :title="rutaManual">{{ rutaManual }}</span>
                  <v-btn text x-small color="primary" class="mt-1" @click="vistaModal = 'origen'">
                    <v-icon left x-small>mdi-pencil</v-icon> Cambiar
                  </v-btn>
                </v-card>
              </v-col>
            </v-row>

            <!-- FILTRO DE ESQUEMAS Y BOTONES DE SELECCIÓN -->
            <div class="d-flex align-center justify-space-between flex-wrap mb-2">
              <div class="d-flex align-center flex-wrap gap-2">
                <span class="text-caption font-weight-bold text-secondary mr-1">ESQUEMA:</span>
                <v-chip-group v-model="filtroEsquema" mandatory active-class="primary--text font-weight-bold">
                  <v-chip filter small value="todos">Todos ({{ tablasComparativa.length }})</v-chip>
                  <v-chip filter small value="comercial">Comercial</v-chip>
                  <v-chip filter small value="facturacion">Facturación</v-chip>
                  <v-chip filter small value="contabilidad">Contabilidad</v-chip>
                  <v-chip filter small value="almacen">Almacenes</v-chip>
                  <v-chip filter small value="activos_fijos">Activos Fijos</v-chip>
                </v-chip-group>
              </div>

              <div>
                <v-btn text x-small color="primary" class="font-weight-bold mr-1" @click="seleccionarTodosModulos">
                  Seleccionar Todo ({{ tablasComparativa.filter(t => t.dbf_existe).length }})
                </v-btn>
                <v-btn text x-small color="grey darken-1" class="font-weight-bold" @click="deseleccionarTodosModulos">
                  Deseleccionar
                </v-btn>
              </div>
            </div>

            <!-- TABLA COMPARATIVA AUDITADA -->
            <v-data-table
              :headers="headersComparativa"
              :items="tablasFiltradas"
              :items-per-page="15"
              dense
              class="elevation-1 rounded-lg mb-4"
            >
              <!-- Checkbox para seleccionar cada tabla -->
              <template v-slot:item.label="{ item }">
                <div class="d-flex align-center py-1">
                  <v-checkbox
                    v-model="modulosSeleccionados"
                    :value="item.id"
                    :disabled="!item.dbf_existe"
                    dense
                    hide-details
                    class="ma-0 pa-0 mr-2"
                  ></v-checkbox>
                  <v-icon small :color="getModuloColor(item.modulo)" class="mr-2">{{ item.icono }}</v-icon>
                  <div>
                    <div class="font-weight-bold text-caption">{{ item.label }}</div>
                    <div class="text-caption text-secondary font-monospace">{{ item.schema }}.{{ item.table }}</div>
                  </div>
                </div>
              </template>

              <!-- Archivo DBF -->
              <template v-slot:item.dbf_archivo="{ item }">
                <div class="d-flex align-center">
                  <v-icon x-small :color="item.dbf_existe ? 'success' : 'grey'" class="mr-1">
                    {{ item.dbf_existe ? 'mdi-file-check' : 'mdi-file-question' }}
                  </v-icon>
                  <span class="font-monospace text-caption">
                    {{ item.dbf_archivo || 'No detectado' }}
                  </span>
                </div>
              </template>

              <!-- Registros FoxPro -->
              <template v-slot:item.dbf_registros="{ item }">
                <span class="font-weight-bold font-monospace text-caption primary--text">
                  {{ formatearNumero(item.dbf_registros) }}
                </span>
              </template>

              <!-- Registros PostgreSQL -->
              <template v-slot:item.pg_registros="{ item }">
                <span class="font-weight-bold font-monospace text-caption teal--text text--darken-2">
                  {{ formatearNumero(item.pg_registros) }}
                </span>
              </template>

              <!-- Faltan Migrar (Diferencia) -->
              <template v-slot:item.diferencia="{ item }">
                <div class="font-monospace text-caption font-weight-bold" :class="item.diferencia > 0 ? 'warning--text text--darken-2' : 'grey--text'">
                  {{ formatearNumero(item.diferencia > 0 ? item.diferencia : 0) }}
                </div>
              </template>

              <!-- Estado / Avance y Porcentaje -->
              <template v-slot:item.estado="{ item }">
                <div class="d-flex align-center justify-center gap-1" style="min-width: 140px;">
                  <v-progress-linear
                    :value="getPorcentajeTabla(item)"
                    :color="getEstadoColor(item.estado)"
                    height="6"
                    rounded
                    style="max-width: 60px;"
                  ></v-progress-linear>
                  <v-chip x-small :color="getEstadoColor(item.estado)" text-color="white" class="font-weight-bold ml-1">
                    {{ getPorcentajeTabla(item) }}% · {{ getEstadoTexto(item.estado) }}
                  </v-chip>
                </div>
              </template>
            </v-data-table>

            <!-- MODO DE MIGRACIÓN: SIMULACIÓN VS REAL -->
            <v-card outlined rounded="lg" class="pa-3 mb-2" :color="esSimulacion ? 'amber lighten-5' : 'green lighten-5'">
              <div class="d-flex align-center justify-space-between flex-wrap">
                <div>
                  <div class="font-weight-bold text-caption d-flex align-center">
                    <v-icon left x-small :color="esSimulacion ? 'warning' : 'success'">
                      {{ esSimulacion ? 'mdi-shield-check' : 'mdi-database-import' }}
                    </v-icon>
                    {{ esSimulacion ? 'Modo Simulación (Dry-Run)' : 'Modo Escritura Oficial (Real)' }}
                  </div>
                  <div class="text-caption text-secondary">
                    {{ esSimulacion ? 'Verifica consistencia e integridad sin modificar las tablas en PostgreSQL' : '⚠️ Inserta y sincroniza datos reales en las tablas de PostgreSQL' }}
                  </div>
                </div>

                <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
                  <v-btn
                    small
                    :outlined="!esSimulacion"
                    :color="esSimulacion ? 'warning darken-1' : 'grey'"
                    class="text-capitalize font-weight-bold rounded-pill"
                    @click="esSimulacion = true"
                  >
                    Simulación
                  </v-btn>
                  <v-btn
                    small
                    :outlined="esSimulacion"
                    :color="!esSimulacion ? 'success' : 'grey'"
                    class="text-capitalize font-weight-bold rounded-pill"
                    @click="esSimulacion = false"
                  >
                    Escritura Real
                  </v-btn>
                </div>
              </div>
            </v-card>
          </div>

          <!-- ============================================================== -->
          <!-- ETAPA 3: MONITOREO EN SEGUNDO PLANO (DESACOPLADO)              -->
          <!-- ============================================================== -->
          <div v-else-if="vistaModal === 'ejecucion'">
            <v-card
              outlined
              class="pa-4 mb-4 job-status-card"
              rounded="lg"
              :class="[
                jobEstado === 'COMPLETADO' ? 'status-completado' : (jobEstado === 'CANCELADO' ? 'status-cancelado' : 'status-procesando')
              ]"
            >
              <div class="d-flex align-center justify-space-between flex-wrap">
                <div class="d-flex align-center">
                  <v-avatar
                    :color="jobEstado === 'COMPLETADO' ? 'success' : (jobEstado === 'CANCELADO' ? 'error' : 'primary')"
                    size="44"
                    class="mr-3 elevation-2"
                  >
                    <v-icon color="white" size="24">
                      {{ jobEstado === 'COMPLETADO' ? 'mdi-check-bold' : (jobEstado === 'CANCELADO' ? 'mdi-close-octagon' : 'mdi-cog-sync') }}
                    </v-icon>
                  </v-avatar>
                  <div>
                    <div class="font-weight-bold text-subtitle-1">
                      {{ jobEstado === 'COMPLETADO' ? '¡Migración Finalizada!' : (jobEstado === 'CANCELADO' ? 'Migración Cancelada' : 'Migración en Ejecución...') }}
                    </div>
                    <div class="text-caption text-secondary">
                      Módulo actual: <strong class="primary--text">{{ getLabelModulo(jobModuloActual) || jobModuloActual || 'Iniciando subproceso...' }}</strong>
                      <span v-if="jobActualId" class="ml-2 font-monospace text-caption">({{ jobActualId.substring(0, 8) }}...)</span>
                    </div>
                  </div>
                </div>

                <!-- Botón Cancelar Inmediato (si está en ejecución) -->
                <div v-if="ejecutandoFondo" class="mt-2 mt-sm-0">
                  <v-btn
                    color="error"
                    large
                    class="rounded-pill font-weight-bold text-capitalize elevation-2 px-5"
                    :loading="cancelandoJob"
                    @click="cancelarJobEnFondo"
                  >
                    <v-icon left>mdi-stop-circle</v-icon>
                    Detener Inmediatamente
                  </v-btn>
                </div>
              </div>

              <!-- BARRA DE PROGRESO ANIMADA Y DINÁMICA -->
              <div class="mt-4">
                <div class="d-flex justify-space-between align-center text-caption font-weight-bold mb-1">
                  <span class="d-flex align-center">
                    <v-icon v-if="ejecutandoFondo" x-small color="primary" class="spin-icon mr-1">mdi-loading</v-icon>
                    Progreso de sincronización
                  </span>
                  <span class="font-monospace text-subtitle-2 font-weight-black primary--text">{{ jobProgreso }}%</span>
                </div>

                <div class="progress-bar-container">
                  <v-progress-linear
                    :value="jobProgreso"
                    :color="jobEstado === 'COMPLETADO' ? 'success' : (jobEstado === 'CANCELADO' ? 'error' : 'primary')"
                    height="16"
                    rounded
                    :class="['animated-emapa-progress', ejecutandoFondo ? 'is-running' : '']"
                  >
                    <template v-slot:default="{ value }">
                      <span class="white--text font-weight-bold text-caption text-shadow">{{ Math.ceil(value) }}%</span>
                    </template>
                  </v-progress-linear>
                </div>
              </div>
            </v-card>

            <!-- TERMINAL DE TELEMETRÍA EN VIVO -->
            <v-card outlined rounded="lg" class="mb-4 terminal-card">
              <v-card-title class="terminal-header py-2 px-4 text-caption font-weight-bold d-flex justify-space-between">
                <span><v-icon left x-small color="green accent-3">mdi-console</v-icon> Bitácora en Vivo del Subproceso</span>
                <span class="text-caption text-secondary">{{ jobLogs.length }} eventos registrados</span>
              </v-card-title>
              <div
                class="terminal-body pa-3 font-monospace text-caption"
                style="height: 220px; overflow-y: auto; line-height: 1.6;"
                ref="terminalFondoLogs"
              >
                <div v-if="jobLogs.length === 0" class="grey--text text--lighten-1 fst-italic">
                  Lanzando subproceso en segundo plano...
                </div>
                <div v-for="(log, idx) in jobLogs" :key="idx" class="d-flex align-start mb-1">
                  <span class="grey--text mr-2">[{{ log.hora }}]</span>
                  <span :class="getLogColor(log.estado)" class="font-weight-bold mr-2">[{{ log.estado }}]</span>
                  <span class="mr-2 grey--text">[{{ log.id }}]:</span>
                  <span>{{ log.mensaje }}</span>
                </div>
              </div>
            </v-card>
          </div>
        </v-card-text>

        <!-- PIE DEL MODAL SEGÚN LA ETAPA -->
        <v-divider></v-divider>
        <v-card-actions class="modal-footer py-3 px-5 d-flex justify-space-between flex-wrap">
          <!-- Botón Cerrar / Volver -->
          <div>
            <v-btn
              v-if="vistaModal === 'comparativa'"
              text
              class="text-capitalize rounded-pill mr-2"
              @click="vistaModal = 'origen'"
            >
              <v-icon left small>mdi-arrow-left</v-icon>
              Cambiar Respaldo
            </v-btn>

            <v-btn
              text
              class="text-capitalize rounded-pill"
              :disabled="cancelandoJob || subiendoArchivo"
              @click="cerrarModalNuevaMigracion"
            >
              {{ ejecutandoFondo ? 'Cerrar Ventana (sigue en segundo plano)' : 'Cerrar' }}
            </v-btn>
          </div>

          <!-- Botón de Acción Principal -->
          <div>
            <!-- Si estamos en Etapa 1 (Origen) -->
            <v-btn
              v-if="vistaModal === 'origen'"
              :color="esSimulacion ? 'primary' : 'success'"
              class="text-capitalize rounded-pill font-weight-bold px-6 elevation-2"
              large
              :loading="subiendoArchivo || escaneando"
              :disabled="(!archivoSubida && !usarRutaLocal) || (usarRutaLocal && !rutaManual)"
              @click="procesarYAnalizarRespaldo"
            >
              <v-icon left>mdi-file-search-outline</v-icon>
              Analizar Respaldo y Ver Qué Falta Migrar
              <v-icon right small>mdi-arrow-right</v-icon>
            </v-btn>

            <!-- Si estamos en Etapa 2 (Comparativa) -->
            <v-btn
              v-else-if="vistaModal === 'comparativa'"
              :color="esSimulacion ? 'primary' : 'success'"
              class="text-capitalize rounded-pill font-weight-bold px-6 elevation-2"
              large
              :loading="iniciandoJob"
              :disabled="modulosSeleccionados.length === 0"
              @click="iniciarMigracionEnFondo"
            >
              <v-icon left>{{ esSimulacion ? 'mdi-shield-check' : 'mdi-rocket-launch' }}</v-icon>
              {{ esSimulacion ? `Iniciar Simulación en Fondo (${modulosSeleccionados.length} tablas)` : `Iniciar Migración Oficial en Fondo (${modulosSeleccionados.length} tablas)` }}
            </v-btn>

            <!-- Si estamos en Etapa 3 (Completado) -->
            <v-btn
              v-else-if="vistaModal === 'ejecucion' && jobCompletado"
              color="primary"
              class="text-capitalize rounded-pill font-weight-bold px-5"
              @click="cerrarModalNuevaMigracion"
            >
              <v-icon left small>mdi-check</v-icon>
              Finalizar y Ver Bitácora
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ============================================================== -->
    <!-- 5. MODAL DIALOG: CONFIRMAR REVERSIÓN (ROLLBACK)               -->
    <!-- ============================================================== -->
    <v-dialog v-model="modalRollback" max-width="580" persistent>
      <v-card rounded="lg" class="pa-2">
        <v-card-title class="d-flex align-center error--text">
          <v-avatar color="error lighten-5" size="40" class="mr-3">
            <v-icon color="error">mdi-alert-octagon-outline</v-icon>
          </v-avatar>
          <div>
            <h4 class="text-h6 font-weight-bold mb-0">Confirmar Reversión (Rollback)</h4>
            <span class="text-caption text-secondary">Deshacer registros migrados de forma transaccional</span>
          </div>
        </v-card-title>

        <v-card-text class="pt-3">
          <v-alert dense text color="warning" class="text-caption mb-3">
            <strong>Atención:</strong> Esta acción eliminará los registros introducidos en PostgreSQL durante el Job ID especificado respetando el orden inverso de dependencias relacionales (Foreign Keys).
          </v-alert>

          <v-simple-table dense class="border rounded-lg mb-3">
            <tbody>
              <tr>
                <td class="font-weight-bold text-caption grey--text">Job ID:</td>
                <td class="font-monospace text-caption">{{ jobSeleccionadoRollback ? jobSeleccionadoRollback.job_id : '' }}</td>
              </tr>
              <tr>
                <td class="font-weight-bold text-caption grey--text">Fecha de Ejecución:</td>
                <td class="text-caption">{{ jobSeleccionadoRollback ? formatearFecha(jobSeleccionadoRollback.created_at) : '' }}</td>
              </tr>
              <tr>
                <td class="font-weight-bold text-caption grey--text">Módulo / Tabla:</td>
                <td class="text-caption font-weight-bold text-capitalize">{{ jobSeleccionadoRollback ? getLabelModulo(jobSeleccionadoRollback.modulo) : '' }}</td>
              </tr>
              <tr>
                <td class="font-weight-bold text-caption grey--text">Modo:</td>
                <td class="text-caption">
                  <v-chip x-small :color="jobSeleccionadoRollback && jobSeleccionadoRollback.es_simulacion ? 'warning' : 'success'" text-color="white">
                    {{ jobSeleccionadoRollback && jobSeleccionadoRollback.es_simulacion ? 'Simulación (Dry-Run)' : 'Escritura Real' }}
                  </v-chip>
                </td>
              </tr>
            </tbody>
          </v-simple-table>

          <div v-if="resultadoRollback" class="mt-3">
            <v-alert dense text color="success" class="text-caption mb-0">
              <v-icon left small color="success">mdi-check-circle</v-icon>
              {{ resultadoRollback.mensaje }}
            </v-alert>
          </div>
        </v-card-text>

        <v-card-actions class="d-flex justify-end gap-2 pa-4 border-top">
          <v-btn text class="text-capitalize rounded-pill" :disabled="ejecutandoRollback" @click="modalRollback = false">
            Cancelar
          </v-btn>
          <v-btn
            color="error"
            class="text-capitalize rounded-pill font-weight-bold px-5"
            :loading="ejecutandoRollback"
            @click="confirmarRollback"
          >
            <v-icon left small>mdi-backup-restore</v-icon>
            Confirmar Reversión
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ============================================================== -->
    <!-- 6. MODAL DIALOG: DETALLES DEL LOG / TRAZABILIDAD               -->
    <!-- ============================================================== -->
    <v-dialog v-model="modalDetalleLog" max-width="700">
      <v-card rounded="lg" class="pa-2">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-text-box-search-outline</v-icon>
            <span class="text-h6 font-weight-bold">Detalle de Migración</span>
          </div>
          <v-btn icon small @click="modalDetalleLog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-2">
          <div v-if="logSeleccionado">
            <v-row dense class="mb-2">
              <v-col cols="6">
                <span class="text-caption grey--text">Job ID:</span>
                <div class="font-monospace text-caption font-weight-bold">{{ logSeleccionado.job_id }}</div>
              </v-col>
              <v-col cols="6">
                <span class="text-caption grey--text">Fecha y Hora:</span>
                <div class="text-caption font-weight-bold">{{ logSeleccionado.created_at }}</div>
              </v-col>
              <v-col cols="6">
                <span class="text-caption grey--text">Módulo:</span>
                <div class="text-caption font-weight-bold text-capitalize">{{ getLabelModulo(logSeleccionado.modulo) }}</div>
              </v-col>
              <v-col cols="6">
                <span class="text-caption grey--text">Tipo de Migración:</span>
                <div>
                  <v-chip x-small :color="logSeleccionado.es_simulacion ? 'warning' : 'success'" text-color="white">
                    {{ logSeleccionado.es_simulacion ? 'Simulación (Dry-Run)' : 'Escritura Real' }}
                  </v-chip>
                </div>
              </v-col>
            </v-row>

            <div class="text-caption grey--text mb-1 font-weight-bold">Mensaje / Resultado:</div>
            <v-alert dense outlined color="primary" class="text-caption mb-3">
              {{ logSeleccionado.mensaje }}
            </v-alert>

            <div class="text-caption grey--text mb-1 font-weight-bold">Metadatos y Telemetría JSON:</div>
            <pre class="grey darken-4 white--text pa-3 rounded-lg font-monospace text-caption" style="max-height: 240px; overflow-y: auto;">{{ logDetallesFormateado }}</pre>
          </div>
        </v-card-text>

        <v-card-actions class="d-flex justify-end pa-3 border-top">
          <v-btn color="primary" text class="text-capitalize rounded-pill" @click="modalDetalleLog = false">
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- NOTIFICACIÓN SNACKBAR GLOBAL -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000" top right rounded="pill">
      <v-icon left small color="white">{{ snackbar.icon }}</v-icon>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn icon small text v-bind="attrs" @click="snackbar.show = false">
          <v-icon small color="white">mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'MigradorRespaldosFoxPro',

  data() {
    return {
      // Estado de Carga
      cargandoHistorial: false,
      escaneando: false,
      subiendoArchivo: false,
      ejecutando: false,
      cancelarMigracion: false,
      ejecutandoRollback: false,

      // Modales
      modalNuevaMigracion: false,
      modalRollback: false,
      modalDetalleLog: false,

      // Ejecución Asíncrona en Segundo Plano
      usarRutaLocal: false,
      iniciandoJob: false,
      ejecutandoFondo: false,
      cancelandoJob: false,
      jobCompletado: false,
      jobActualId: null,
      jobProgreso: 0,
      jobEstado: 'INICIANDO',
      jobModuloActual: '',
      jobLogs: [],
      timerPolling: null,

      // Historial & Bitácora
      logsHistorial: [],
      busquedaHistorial: '',
      filtroTipoHistorial: 'todos',
      opcionesTipoHistorial: [
        { text: 'Todas las ejecuciones', value: 'todos' },
        { text: 'Solo Escritura Real', value: 'real' },
        { text: 'Solo Simulaciones', value: 'simulacion' },
      ],
      jobSeleccionadoRollback: null,
      resultadoRollback: null,
      logSeleccionado: null,

      kpis: {
        total_migraciones: 0,
        total_procesados: 0,
        total_correctos: 0,
        ultima_ejecucion: null,
        ultimo_estado: 'SIN_DATOS',
      },

      headersHistorial: [
        { text: 'Fecha / Hora', value: 'created_at', sortable: true, width: '130px' },
        { text: 'Job ID', value: 'job_id', sortable: false, width: '130px' },
        { text: 'Módulo / Tabla', value: 'modulo', sortable: true, width: '160px' },
        { text: 'Modo', value: 'es_simulacion', sortable: true, width: '150px' },
        { text: 'Leídos / Insertados', value: 'registros_procesados', sortable: true, align: 'end', width: '160px' },
        { text: 'Resumen', value: 'mensaje', sortable: false },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'end', width: '90px' },
      ],

      // Estado de navegación en el modal
      vistaModal: 'origen', // 'origen' | 'comparativa' | 'ejecucion'
      progresoSubida: 0,
      mensajeSubida: '',

      // Parámetros de Migración y Diagnóstico
      esSimulacion: false,
      archivoSubida: null,
      usarRutaLocal: false,
      rutaManual: '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',

      // Tablas y Auditoría
      tablasComparativa: [],
      filtroEsquema: 'todos',
      modulosSeleccionados: [],
      totalDbfsEncontrados: 0,
      totalRegistrosDbf: 0,
      totalRegistrosPg: 0,
      resumenModulos: {
        comercial: { nombre: 'Comercial', dbf: 0, pg: 0 },
        facturacion: { nombre: 'Facturación', dbf: 0, pg: 0 },
        contabilidad: { nombre: 'Contabilidad', dbf: 0, pg: 0 },
        almacen: { nombre: 'Almacenes', dbf: 0, pg: 0 },
        activos_fijos: { nombre: 'Activos Fijos', dbf: 0, pg: 0 },
      },
      headersComparativa: [
        { text: 'Módulo / Tabla', value: 'label', sortable: true },
        { text: 'Archivo FoxPro', value: 'dbf_archivo', sortable: true },
        { text: 'Registros FoxPro', value: 'dbf_registros', sortable: true, align: 'end' },
        { text: 'Registros PostgreSQL', value: 'pg_registros', sortable: true, align: 'end' },
        { text: 'Faltan Migrar', value: 'diferencia', sortable: true, align: 'end' },
        { text: 'Estado / Avance', value: 'estado', sortable: true, align: 'center' },
      ],

      snackbar: {
        show: false,
        text: '',
        color: 'success',
        icon: 'mdi-check-circle',
      },
    };
  },

  computed: {
    logsFiltrados() {
      if (this.filtroTipoHistorial === 'todos') {
        return this.logsHistorial;
      }
      if (this.filtroTipoHistorial === 'real') {
        return this.logsHistorial.filter(l => !l.es_simulacion);
      }
      if (this.filtroTipoHistorial === 'simulacion') {
        return this.logsHistorial.filter(l => !!l.es_simulacion);
      }
      return this.logsHistorial;
    },

    tablasFiltradas() {
      if (this.filtroEsquema === 'todos') {
        return this.tablasComparativa;
      }
      return this.tablasComparativa.filter(t => t.schema === this.filtroEsquema || t.modulo === this.filtroEsquema);
    },

    totalFaltantePorMigrar() {
      const faltante = this.totalRegistrosDbf - this.totalRegistrosPg;
      return faltante > 0 ? faltante : 0;
    },

    logDetallesFormateado() {
      if (!this.logSeleccionado || !this.logSeleccionado.detalles_json) {
        return 'Sin detalles adicionales.';
      }
      try {
        const parsed = typeof this.logSeleccionado.detalles_json === 'string'
          ? JSON.parse(this.logSeleccionado.detalles_json)
          : this.logSeleccionado.detalles_json;
        return JSON.stringify(parsed, null, 2);
      } catch (e) {
        return String(this.logSeleccionado.detalles_json);
      }
    },
  },

  mounted() {
    this.cargarHistorial();
  },

  beforeDestroy() {
    this.detenerPollingJob();
  },

  methods: {
    // ==========================================
    // HISTORIAL Y BITÁCORA
    // ==========================================
    cargarHistorial() {
      this.cargandoHistorial = true;
      axios
        .get('api/datos/migracion/historial')
        .then(res => {
          if (res.data.logs) {
            this.logsHistorial = res.data.logs;
          }
          if (res.data.kpis) {
            this.kpis = res.data.kpis;
          }
        })
        .catch(err => {
          console.error('Error cargando historial:', err);
        })
        .finally(() => {
          this.cargandoHistorial = false;
        });
    },

    abrirDetalleLog(item) {
      this.logSeleccionado = item;
      this.modalDetalleLog = true;
    },

    abrirDialogRollback(item) {
      this.jobSeleccionadoRollback = item;
      this.resultadoRollback = null;
      this.modalRollback = true;
    },

    confirmarRollback() {
      if (!this.jobSeleccionadoRollback) return;
      this.ejecutandoRollback = true;

      axios
        .post('api/datos/migracion/revertir', {
          job_id: this.jobSeleccionadoRollback.job_id,
        })
        .then(res => {
          this.resultadoRollback = res.data;
          this.mostrarMensaje('Reversión completada con éxito', 'success', 'mdi-check-circle');
          this.cargarHistorial();
          setTimeout(() => {
            this.modalRollback = false;
          }, 1500);
        })
        .catch(err => {
          const msg = err.response?.data?.message || err.message || 'Error revirtiendo migración';
          this.mostrarMensaje(msg, 'error', 'mdi-alert-circle');
        })
        .finally(() => {
          this.ejecutandoRollback = false;
        });
    },

    esRevertido(mensaje) {
      return typeof mensaje === 'string' && mensaje.includes('[REVERTIDO');
    },

    // ==========================================
    // EJECUCIÓN EN SEGUNDO PLANO Y COMPARATIVA
    // ==========================================
    abrirModalNuevaMigracion() {
      this.modalNuevaMigracion = true;
      if (this.ejecutandoFondo) {
        this.vistaModal = 'ejecucion';
      } else {
        this.vistaModal = 'origen';
        this.progresoSubida = 0;
        this.mensajeSubida = '';
      }
    },

    async procesarYAnalizarRespaldo() {
      if (!this.usarRutaLocal && this.archivoSubida) {
        await this.subirArchivoEnChunks(this.archivoSubida);
      } else {
        await this.escanearDirectorio(this.rutaManual);
      }
    },

    async subirArchivoEnChunks(file) {
      this.subiendoArchivo = true;
      this.progresoSubida = 0;
      this.mensajeSubida = 'Preparando archivo...';

      const chunkSize = 2 * 1024 * 1024; // 2 MB por fragmento (eficiente para redes locales/remotas)
      const totalChunks = Math.ceil(file.size / chunkSize);
      const fileId = 'up_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
      const mbTotal = (file.size / (1024 * 1024)).toFixed(1);

      for (let i = 0; i < totalChunks; i++) {
        const start = i * chunkSize;
        const end = Math.min(start + chunkSize, file.size);
        const chunk = file.slice(start, end);

        const formData = new FormData();
        formData.append('chunk', chunk, file.name);
        formData.append('chunk_index', i);
        formData.append('total_chunks', totalChunks);
        formData.append('file_id', fileId);
        formData.append('file_name', file.name);

        const mbEnviado = (start / (1024 * 1024)).toFixed(1);
        this.mensajeSubida = `Subiendo: ${mbEnviado} MB de ${mbTotal} MB (Fragmento ${i + 1}/${totalChunks})...`;

        try {
          // NOTA CRÍTICA: NO definir 'Content-Type': 'multipart/form-data' manualmente.
          // El navegador genera automáticamente el encabezado con el boundary multipart correspondiente.
          const res = await axios.post('/api/datos/migracion/subir-chunk', formData, {
            timeout: 120000,
            onUploadProgress: (progressEvent) => {
              if (progressEvent.total) {
                const fraction = progressEvent.loaded / progressEvent.total;
                const chunkPct = Math.round(((i + fraction) / totalChunks) * 100);
                this.progresoSubida = Math.min(chunkPct, 99);
                const bytesActuales = start + progressEvent.loaded;
                const mbAct = (bytesActuales / (1024 * 1024)).toFixed(1);
                this.mensajeSubida = `Subiendo: ${mbAct} MB de ${mbTotal} MB (Fragmento ${i + 1}/${totalChunks})...`;
              }
            },
          });

          this.progresoSubida = Math.round(((i + 1) / totalChunks) * 100);

          if (res.data && res.data.completado) {
            this.progresoSubida = 100;
            this.mensajeSubida = '¡Archivo subido y extraído! Analizando tablas...';
            this.rutaManual = res.data.ruta_extraida;
            this.subiendoArchivo = false;
            await this.escanearDirectorio(res.data.ruta_extraida);
            return;
          }
        } catch (err) {
          const msg = err.response?.data?.message || err.message || 'Error durante la subida del respaldo';
          this.mensajeSubida = `Error en fragmento ${i + 1}: ${msg}`;
          this.mostrarMensaje(`Error en fragmento ${i + 1}: ${msg}`, 'error', 'mdi-alert-circle');
          this.subiendoArchivo = false;
          return;
        }
      }
    },

    async escanearDirectorio(ruta) {
      if (!ruta) return;
      this.escaneando = true;
      try {
        const res = await axios.post('api/datos/migracion/escanear', { ruta });
        const d = res.data;
        this.tablasComparativa = d.tablas || [];
        this.totalDbfsEncontrados = d.total_dbfs_encontrados || 0;
        this.totalRegistrosDbf = d.total_registros_dbf || 0;
        this.totalRegistrosPg = d.total_registros_pg || 0;
        this.resumenModulos = d.resumen_modulos || this.resumenModulos;
        this.modulosSeleccionados = this.tablasComparativa.filter(t => t.dbf_existe).map(t => t.id);
        this.vistaModal = 'comparativa';
      } catch (err) {
        const msg = err.response?.data?.message || 'Error al analizar el directorio de datos.';
        this.mostrarMensaje(msg, 'error', 'mdi-alert-circle');
      } finally {
        this.escaneando = false;
      }
    },

    seleccionarTodosModulos() {
      this.modulosSeleccionados = this.tablasComparativa.filter(t => t.dbf_existe).map(t => t.id);
    },

    deseleccionarTodosModulos() {
      this.modulosSeleccionados = [];
    },

    getPorcentajeTabla(item) {
      if (!item.dbf_registros || item.dbf_registros === 0) {
        return item.pg_registros > 0 ? 100 : 0;
      }
      const pct = Math.round((item.pg_registros / item.dbf_registros) * 100);
      return Math.min(pct, 100);
    },

    iniciarMigracionEnFondo() {
      if (this.modulosSeleccionados.length === 0) {
        this.mostrarMensaje('Debe seleccionar al menos una tabla para sincronizar.', 'warning', 'mdi-alert');
        return;
      }

      this.iniciandoJob = true;
      const formData = new FormData();
      formData.append('ruta', this.rutaManual);
      formData.append('es_simulacion', this.esSimulacion ? 1 : 0);
      formData.append('limite', 0);
      this.modulosSeleccionados.forEach(m => {
        formData.append('modulos[]', m);
      });

      axios
        .post('api/datos/migracion/iniciar-fondo', formData)
        .then(res => {
          this.jobActualId = res.data.job_id;
          this.ejecutandoFondo = true;
          this.jobCompletado = false;
          this.jobProgreso = 0;
          this.jobEstado = 'PROCESANDO';
          this.jobModuloActual = 'Iniciando en segundo plano...';
          this.jobLogs = [];
          this.vistaModal = 'ejecucion';
          this.mostrarMensaje('Migración iniciada en segundo plano.', 'success', 'mdi-rocket-launch');
          this.iniciarPollingJob();
        })
        .catch(err => {
          const msg = err.response?.data?.message || 'Error al iniciar migración en segundo plano';
          this.mostrarMensaje(msg, 'error', 'mdi-alert-circle');
        })
        .finally(() => {
          this.iniciandoJob = false;
        });
    },

    iniciarPollingJob() {
      this.detenerPollingJob();
      this.timerPolling = setInterval(() => {
        if (!this.jobActualId) {
          this.detenerPollingJob();
          return;
        }

        axios
          .get(`api/datos/migracion/estado-job/${this.jobActualId}`)
          .then(res => {
            const data = res.data.data;
            if (!data) return;

            this.jobProgreso = data.progreso || 0;
            this.jobEstado = data.estado || 'PROCESANDO';
            this.jobModuloActual = data.modulo_actual || '';
            this.jobLogs = data.logs || [];

            this.$nextTick(() => {
              const el = this.$refs.terminalFondoLogs;
              if (el) el.scrollTop = el.scrollHeight;
            });

            if (data.estado === 'COMPLETADO' || data.estado === 'CANCELADO') {
              this.ejecutandoFondo = false;
              this.jobCompletado = true;
              this.detenerPollingJob();
              this.cargarHistorial();
            }
          })
          .catch(err => {
            console.error('Error polling job:', err);
          });
      }, 1500);
    },

    detenerPollingJob() {
      if (this.timerPolling) {
        clearInterval(this.timerPolling);
        this.timerPolling = null;
      }
    },

    cancelarJobEnFondo() {
      if (!this.jobActualId) return;
      this.cancelandoJob = true;

      axios
        .post(`api/datos/migracion/cancelar-job/${this.jobActualId}`)
        .then(() => {
          this.mostrarMensaje('Proceso cancelado inmediatamente.', 'warning', 'mdi-stop');
          this.jobEstado = 'CANCELADO';
          this.ejecutandoFondo = false;
          this.jobCompletado = true;
          this.detenerPollingJob();
          this.cargarHistorial();
        })
        .catch(err => {
          const msg = err.response?.data?.message || 'Error al cancelar proceso';
          this.mostrarMensaje(msg, 'error', 'mdi-alert-circle');
        })
        .finally(() => {
          this.cancelandoJob = false;
        });
    },

    cerrarModalNuevaMigracion() {
      if (!this.ejecutandoFondo) {
        this.modalNuevaMigracion = false;
        this.jobCompletado = false;
        this.jobActualId = null;
        this.archivoSubida = null;
        this.detenerPollingJob();
      } else {
        this.modalNuevaMigracion = false;
        this.mostrarMensaje('La migración continúa en segundo plano. Puede abrirla o ver la bitácora.', 'info', 'mdi-information');
      }
      this.cargarHistorial();
    },
    // ==========================================
    // HELPERS VISUALES Y FORMATO
    // ==========================================
    formatearNumero(num) {
      if (num === null || num === undefined) return '0';
      return new Intl.NumberFormat('es-BO').format(num);
    },

    formatearFecha(f) {
      if (!f) return '-';
      return f.substring(0, 10);
    },

    formatearFechaRelativa(f) {
      if (!f) return 'Sin registros';
      return f.substring(0, 16);
    },

    copiarTexto(txt) {
      if (!txt) return;
      navigator.clipboard.writeText(txt).then(() => {
        this.mostrarMensaje('Copiado al portapapeles', 'info', 'mdi-content-copy');
      });
    },

    mostrarMensaje(text, color = 'success', icon = 'mdi-check-circle') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.icon = icon;
      this.snackbar.show = true;
    },

    getModuloColor(mod) {
      const m = {
        comercial: 'primary',
        facturacion: 'teal',
        contabilidad: 'indigo',
        almacen: 'orange darken-2',
        activos_fijos: 'purple',
      };
      return m[mod] || 'grey';
    },

    getModuloBorderClass(mod) {
      const b = {
        comercial: 'border-primary-subtle',
        facturacion: 'border-teal-subtle',
        contabilidad: 'border-indigo-subtle',
        almacen: 'border-orange-subtle',
        activos_fijos: 'border-purple-subtle',
      };
      return b[mod] || '';
    },

    getEstadoColor(st) {
      const map = {
        SINCRONIZADO: 'success darken-1',
        PARCIAL: 'warning darken-1',
        PENDIENTE: 'error',
        VACIO: 'grey',
        SIN_ORIGEN: 'grey darken-1',
      };
      return map[st] || 'grey';
    },

    getEstadoTexto(st) {
      const map = {
        SINCRONIZADO: 'Sincronizado',
        PARCIAL: 'Parcial',
        PENDIENTE: 'Pendiente',
        VACIO: 'Vacío',
        SIN_ORIGEN: 'Sin Archivo',
      };
      return map[st] || st;
    },

    getLogColor(est) {
      const map = {
        EXITO: 'green--text text--accent-3',
        ADVERTENCIA: 'amber--text text--lighten-1',
        ERROR: 'red--text text--lighten-2',
        PROCESANDO: 'light-blue--text text--accent-2',
        INFO: 'grey--text text--lighten-1',
      };
      return map[est] || 'white--text';
    },

    getColorModulo(mod) {
      if (['calles', 'zonas', 'tarifas', 'abonados', 'aportes_agua', 'aportes_alcantarillado', 'bajas_socios', 'convenios', 'recibos', 'lecturas'].includes(mod)) {
        return 'primary';
      }
      if (['facturas'].includes(mod)) return 'teal';
      if (['plan_cuentas', 'comprobantes', 'compras'].includes(mod)) return 'indigo';
      if (['materiales_almacen'].includes(mod)) return 'orange darken-2';
      return 'purple';
    },

    getIconoModulo(mod) {
      const map = {
        calles: 'mdi-road-variant',
        zonas: 'mdi-map-marker-multiple',
        tarifas: 'mdi-currency-usd',
        abonados: 'mdi-account-group',
        aportes_agua: 'mdi-water',
        aportes_alcantarillado: 'mdi-water-check',
        bajas_socios: 'mdi-account-cancel',
        convenios: 'mdi-handshake-outline',
        recibos: 'mdi-receipt-text-outline',
        lecturas: 'mdi-gauge',
        facturas: 'mdi-file-document-check-outline',
        plan_cuentas: 'mdi-book-open-outline',
        comprobantes: 'mdi-book-edit-outline',
        compras: 'mdi-cart-outline',
        materiales_almacen: 'mdi-package-variant-closed',
        rubros_activos: 'mdi-domain',
        bienes_activos: 'mdi-office-building-marker',
      };
      return map[mod] || 'mdi-table';
    },

    getLabelModulo(mod) {
      const map = {
        estados_abonado: 'Estados de Abonado (Paramétrica)',
        conceptos_ingresos: 'Conceptos / Otros Ingresos (Paramétrica)',
        calles: 'Calles y Avenidas',
        zonas: 'Zonas Tarifarias',
        tarifas: 'Categorías y Tarifas',
        abonados: 'Abonados / Socios',
        aportes_agua: 'Aportes Agua',
        aportes_alcantarillado: 'Aportes Alcantarillado',
        bajas_socios: 'Abonados en Baja',
        convenios: 'Convenios de Pago',
        recibos: 'Recibos de Caja',
        lecturas: 'Lecturas y Consumos',
        facturas: 'Facturas Computarizadas',
        plan_cuentas: 'Plan de Cuentas',
        comprobantes: 'Comprobantes de Diario',
        compras: 'Facturas de Compra',
        materiales_almacen: 'Materiales Almacén',
        rubros_activos: 'Rubros Activos Fijos',
        bienes_activos: 'Bienes de Activos',
      };
      return map[mod] || mod;
    },
  },
};
</script>

<style scoped>
/* ============================================================== */
/* ESTILOS NATIVOS Y ADAPTATIVOS A AMBOS TEMAS (LIGHT / DARK)    */
/* ============================================================== */
.migrador-respaldos-wrapper {
  animation: fadeIn 0.3s ease-in-out;
}

/* TOPBAR ELEGANTE Y DE ALTO CONTRASTE */
.theme--light .migrador-topbar {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.05) !important;
}
.theme--light .topbar-title {
  color: #0f172a !important;
}
.theme--light .topbar-subtitle {
  color: #64748b !important;
}
.theme--light .topbar-btn-alt {
  border-color: #cbd5e1 !important;
  color: #334155 !important;
}

.theme--dark .migrador-topbar {
  background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%) !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5) !important;
}
.theme--dark .topbar-title {
  color: #f8fafc !important;
}
.theme--dark .topbar-subtitle {
  color: #94a3b8 !important;
}
.theme--dark .topbar-btn-alt {
  border-color: rgba(255, 255, 255, 0.2) !important;
  color: #e2e8f0 !important;
}

.topbar-avatar {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
}

/* MODAL DE NUEVA MIGRACIÓN */
.modal-header {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.text-light-translucent {
  color: rgba(255, 255, 255, 0.75) !important;
}

/* KPI CARDS */
.kpi-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.08);
}

/* BORDES TEMÁTICOS PARA ESQUEMAS */
.border-primary-subtle { border-color: rgba(24, 103, 192, 0.3) !important; }
.border-teal-subtle { border-color: rgba(0, 150, 136, 0.3) !important; }
.border-indigo-subtle { border-color: rgba(63, 81, 181, 0.3) !important; }
.border-orange-subtle { border-color: rgba(255, 152, 0, 0.3) !important; }
.border-purple-subtle { border-color: rgba(156, 39, 176, 0.3) !important; }

.font-monospace {
  font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace !important;
}

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

/* BARRA DE PASOS EN MODAL */
.theme--dark .modal-stepper-bar {
  background: rgba(15, 23, 42, 0.7) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.theme--light .modal-stepper-bar {
  background: #f8fafc !important;
  border-bottom: 1px solid #e2e8f0 !important;
}

/* TARJETAS DE MODO (SIMULACIÓN VS REAL) */
.theme--dark .mode-card {
  background: rgba(30, 41, 59, 0.5) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.theme--dark .mode-card.is-active-sim {
  background: rgba(30, 58, 138, 0.35) !important;
  border-color: #3b82f6 !important;
}
.theme--dark .mode-card.is-active-real {
  background: rgba(6, 78, 59, 0.35) !important;
  border-color: #10b981 !important;
}

.theme--light .mode-card {
  background: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
}
.theme--light .mode-card.is-active-sim {
  background: #eff6ff !important;
  border-color: #3b82f6 !important;
}
.theme--light .mode-card.is-active-real {
  background: #f0fdf4 !important;
  border-color: #10b981 !important;
}

/* TARJETA DE ESTADO DE JOB (PASO 3) */
.theme--dark .job-status-card {
  background: rgba(30, 41, 59, 0.6) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.theme--dark .job-status-card.status-procesando {
  background: linear-gradient(135deg, rgba(30, 58, 138, 0.25) 0%, rgba(15, 23, 42, 0.6) 100%) !important;
  border: 1px solid rgba(59, 130, 246, 0.4) !important;
}
.theme--dark .job-status-card.status-completado {
  background: linear-gradient(135deg, rgba(6, 78, 59, 0.25) 0%, rgba(15, 23, 42, 0.6) 100%) !important;
  border: 1px solid rgba(16, 185, 129, 0.4) !important;
}
.theme--dark .job-status-card.status-cancelado {
  background: linear-gradient(135deg, rgba(127, 29, 29, 0.25) 0%, rgba(15, 23, 42, 0.6) 100%) !important;
  border: 1px solid rgba(239, 68, 68, 0.4) !important;
}

.theme--light .job-status-card {
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
}
.theme--light .job-status-card.status-procesando {
  background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%) !important;
  border: 1px solid #bfdbfe !important;
}
.theme--light .job-status-card.status-completado {
  background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%) !important;
  border: 1px solid #bbf7d0 !important;
}
.theme--light .job-status-card.status-cancelado {
  background: linear-gradient(135deg, #fef2f2 0%, #f8fafc 100%) !important;
  border: 1px solid #fecaca !important;
}

/* CONTENEDOR Y BARRA DE PROGRESO ANIMADA PROFESIONAL */
.progress-bar-container {
  position: relative;
  border-radius: 9999px;
  overflow: hidden;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15);
}

.animated-emapa-progress .v-progress-linear__determinate {
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
  position: relative;
  overflow: hidden;
}

/* Rayas diagonales animadas ("Barber-pole stripes") */
.animated-emapa-progress.is-running .v-progress-linear__determinate {
  background-image: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.22) 25%,
    transparent 25%,
    transparent 50%,
    rgba(255, 255, 255, 0.22) 50%,
    rgba(255, 255, 255, 0.22) 75%,
    transparent 75%,
    transparent
  ) !important;
  background-size: 28px 28px !important;
  animation: barberPoleStripes 0.8s linear infinite !important;
}

@keyframes barberPoleStripes {
  0% {
    background-position: 0 0;
  }
  100% {
    background-position: 28px 0;
  }
}

/* Efecto haz de luz brillante / Shimmer que viaja por la barra */
.animated-emapa-progress.is-running .v-progress-linear__determinate::after {
  content: '';
  position: absolute;
  top: 0;
  left: -150%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.45) 50%,
    transparent 100%
  );
  animation: progressShimmer 1.8s ease-in-out infinite;
}

@keyframes progressShimmer {
  0% {
    left: -150%;
  }
  100% {
    left: 150%;
  }
}

.text-shadow {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
}

/* TERMINAL DE TELEMETRÍA */
.terminal-card {
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  background: #0f172a !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4) !important;
}
.terminal-header {
  background: #1e293b !important;
  color: #f8fafc !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.terminal-body {
  background: #090d16 !important;
  color: #e2e8f0 !important;
}

/* FOOTER DEL MODAL */
.theme--dark .modal-footer {
  background: rgba(15, 23, 42, 0.6) !important;
  border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.theme--light .modal-footer {
  background: #f8fafc !important;
  border-top: 1px solid #e2e8f0 !important;
}

.spin-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}
</style>
