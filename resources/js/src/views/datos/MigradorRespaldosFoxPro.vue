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
    <!-- 4. MODAL POPUP: ASISTENTE DE NUEVA MIGRACIÓN (3 PASOS)         -->
    <!-- ============================================================== -->
    <v-dialog v-model="modalNuevaMigracion" max-width="1280" scrollable persistent>
      <v-card class="modal-nueva-migracion">
        <!-- CABECERA DEL MODAL -->
        <v-card-title class="modal-header py-3 px-5 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="primary lighten-1" size="40" class="mr-3 text-white elevation-2">
              <v-icon color="white" size="22">mdi-database-sync</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold mb-0 white--text">Asistente de Migración de Respaldos FoxPro</h3>
              <span class="text-caption text-light-translucent">
                Diagnóstico volumétrico, correspondencia de esquemas y migración asíncrona asistida
              </span>
            </div>
          </div>

          <v-btn icon dark @click="modalNuevaMigracion = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <!-- NAVEGACIÓN POR PASOS / TABS DENTRO DEL MODAL -->
        <v-tabs
          v-model="pasoWizard"
          color="primary"
          slider-color="primary"
          background-color="transparent"
          class="border-bottom px-4 pt-1"
        >
          <v-tab class="text-capitalize font-weight-bold">
            <v-icon left small>mdi-folder-search-outline</v-icon>
            1. Origen y Respaldo
          </v-tab>
          <v-tab class="text-capitalize font-weight-bold">
            <v-icon left small>mdi-table-compare</v-icon>
            2. Comparativa por Esquemas
          </v-tab>
          <v-tab class="text-capitalize font-weight-bold">
            <v-icon left small>mdi-cog-play-outline</v-icon>
            3. Asistente y Ejecución
          </v-tab>
        </v-tabs>

        <!-- CONTENIDO SCROLLABLE DEL MODAL -->
        <v-card-text class="pa-5" style="max-height: 70vh;">
          <v-tabs-items v-model="pasoWizard">
            <!-- -------------------------------------------------------- -->
            <!-- PASO 1: ORIGEN DE DATOS Y CARGA DE ARCHIVO                -->
            <!-- -------------------------------------------------------- -->
            <v-tab-item>
              <v-row>
                <!-- Selector de Respaldo en Servidor -->
                <v-col cols="12" md="7">
                  <v-card outlined rounded="lg" class="pa-5 fill-height">
                    <div class="d-flex align-center mb-3">
                      <v-avatar color="primary lighten-5" size="36" class="mr-3">
                        <v-icon color="primary" small>mdi-harddisk</v-icon>
                      </v-avatar>
                      <div>
                        <h4 class="text-subtitle-1 font-weight-bold mb-0">Directorio de Respaldos en Servidor</h4>
                        <span class="text-caption text-secondary">Seleccione un respaldo detectado o especifique la ruta</span>
                      </div>
                    </div>

                    <v-divider class="my-3"></v-divider>

                    <label class="text-caption font-weight-bold text-secondary mb-1 d-block">
                      Respaldo Preconfigurado Detectado:
                    </label>
                    <v-select
                      v-model="rutaSeleccionada"
                      :items="rutasPredefinidas"
                      item-text="nombre"
                      item-value="ruta"
                      outlined
                      dense
                      prepend-inner-icon="mdi-folder-network"
                      :menu-props="{ offsetY: true, bottom: true, contentClass: 'elevation-4' }"
                      @change="onSeleccionarRuta"
                    >
                      <template v-slot:item="{ item }">
                        <v-list-item-content>
                          <v-list-item-title class="font-weight-medium">
                            {{ item.nombre }}
                            <v-chip x-small :color="item.existe ? 'success' : 'grey'" text-color="white" class="ml-2">
                              {{ item.existe ? 'Disponible' : 'No encontrado' }}
                            </v-chip>
                          </v-list-item-title>
                          <v-list-item-subtitle class="text-caption text-secondary">
                            {{ item.ruta }}
                          </v-list-item-subtitle>
                        </v-list-item-content>
                      </template>
                    </v-select>

                    <label class="text-caption font-weight-bold text-secondary mb-1 d-block">
                      Ruta Absoluta de la Carpeta DATA:
                    </label>
                    <v-text-field
                      v-model="rutaManual"
                      outlined
                      dense
                      prepend-inner-icon="mdi-folder-open"
                      placeholder="/ruta/al/respaldo/DATA"
                      class="mb-3"
                    ></v-text-field>

                    <div class="d-flex justify-end mt-2">
                      <v-btn
                        color="primary"
                        large
                        class="text-capitalize font-weight-bold rounded-pill px-6"
                        :loading="escaneando"
                        @click="escanearDirectorio"
                      >
                        <v-icon left>mdi-magnify-scan</v-icon>
                        Escanear y Diagnosticar
                      </v-btn>
                    </div>
                  </v-card>
                </v-col>

                <!-- Subir Archivo Comprimido .ZIP / .RAR -->
                <v-col cols="12" md="5">
                  <v-card outlined rounded="lg" class="pa-5 fill-height d-flex flex-column justify-space-between">
                    <div>
                      <div class="d-flex align-center mb-3">
                        <v-avatar color="indigo lighten-5" size="36" class="mr-3">
                          <v-icon color="indigo" small>mdi-cloud-upload-outline</v-icon>
                        </v-avatar>
                        <div>
                          <h4 class="text-subtitle-1 font-weight-bold mb-0">Cargar Paquete de Respaldo</h4>
                          <span class="text-caption text-secondary">Extrae un archivo .zip o .rar al servidor para su auditoría</span>
                        </div>
                      </div>

                      <v-divider class="my-3"></v-divider>

                      <label class="text-caption font-weight-bold text-secondary mb-1 d-block">
                        Archivo de Respaldo (.ZIP / .RAR):
                      </label>
                      <v-file-input
                        v-model="archivoSubida"
                        outlined
                        dense
                        show-size
                        accept=".zip,.rar,.tar,.gz"
                        prepend-inner-icon="mdi-archive"
                        prepend-icon=""
                        placeholder="Seleccione archivo comprimido..."
                        class="mb-2"
                      ></v-file-input>

                      <v-alert dense text color="info" class="text-caption mt-2 mb-0">
                        <v-icon left small color="info">mdi-information</v-icon>
                        Al subirlo, se descomprime automáticamente en <code>storage/app/respaldos_migracion/</code> y se autodiagnostica.
                      </v-alert>
                    </div>

                    <div class="d-flex justify-end mt-4">
                      <v-btn
                        color="indigo darken-1"
                        dark
                        large
                        class="text-capitalize font-weight-bold rounded-pill px-6"
                        :disabled="!archivoSubida"
                        :loading="subiendoArchivo"
                        @click="subirRespaldo"
                      >
                        <v-icon left>mdi-upload</v-icon>
                        Descomprimir y Analizar
                      </v-btn>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-tab-item>

            <!-- -------------------------------------------------------- -->
            <!-- PASO 2: COMPARATIVA POR ESQUEMAS (AUDITORÍA)             -->
            <!-- -------------------------------------------------------- -->
            <v-tab-item>
              <!-- Mini tarjetas por Esquema -->
              <v-row dense class="mb-3">
                <v-col cols="12" sm="6" md="2" v-for="(modInfo, modKey) in resumenModulos" :key="modKey">
                  <v-card outlined rounded="lg" class="pa-3 text-center fill-height" :class="getModuloBorderClass(modKey)">
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
                  <v-card outlined rounded="lg" class="pa-3 text-center fill-height bg-light">
                    <div class="text-caption text-uppercase font-weight-bold text-secondary mb-1">Archivos DBF</div>
                    <div class="text-h5 font-weight-bold primary--text">{{ totalDbfsEncontrados }}</div>
                    <div class="text-caption text-secondary">detectados</div>
                  </v-card>
                </v-col>
              </v-row>

              <!-- Filtro de esquemas -->
              <div class="d-flex align-center justify-space-between flex-wrap mb-3">
                <div class="d-flex align-center flex-wrap gap-2">
                  <span class="text-caption font-weight-bold text-secondary mr-2">FILTRAR POR ESQUEMA:</span>
                  <v-chip-group v-model="filtroEsquema" mandatory active-class="primary--text font-weight-bold">
                    <v-chip filter small value="todos">Todos ({{ tablasComparativa.length }})</v-chip>
                    <v-chip filter small value="comercial">Comercial</v-chip>
                    <v-chip filter small value="facturacion">Facturación</v-chip>
                    <v-chip filter small value="contabilidad">Contabilidad</v-chip>
                    <v-chip filter small value="almacen">Almacenes</v-chip>
                    <v-chip filter small value="activos_fijos">Activos Fijos</v-chip>
                  </v-chip-group>
                </div>

                <v-btn
                  color="primary"
                  small
                  class="rounded-pill font-weight-bold text-capitalize"
                  @click="pasoWizard = 2"
                >
                  Continuar al Asistente
                  <v-icon right small>mdi-arrow-right</v-icon>
                </v-btn>
              </div>

              <!-- Tabla Comparativa -->
              <v-data-table
                :headers="headersComparativa"
                :items="tablasFiltradas"
                :items-per-page="20"
                dense
                class="elevation-1 rounded-lg"
              >
                <template v-slot:item.label="{ item }">
                  <div class="d-flex align-center py-1">
                    <v-icon small :color="getModuloColor(item.modulo)" class="mr-2">{{ item.icono }}</v-icon>
                    <div>
                      <div class="font-weight-medium text-caption">{{ item.label }}</div>
                      <div class="text-caption text-secondary font-monospace">
                        {{ item.schema }}.{{ item.table }}
                      </div>
                    </div>
                  </div>
                </template>

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

                <template v-slot:item.dbf_registros="{ item }">
                  <span class="font-weight-bold font-monospace text-caption">
                    {{ formatearNumero(item.dbf_registros) }}
                  </span>
                </template>

                <template v-slot:item.pg_registros="{ item }">
                  <span class="font-weight-bold font-monospace text-caption teal--text text--darken-2">
                    {{ formatearNumero(item.pg_registros) }}
                  </span>
                </template>

                <template v-slot:item.diferencia="{ item }">
                  <div class="font-monospace text-caption font-weight-bold" :class="item.diferencia > 0 ? 'warning--text' : 'grey--text'">
                    {{ formatearNumero(item.diferencia) }}
                  </div>
                </template>

                <template v-slot:item.estado="{ item }">
                  <v-chip
                    x-small
                    :color="getEstadoColor(item.estado)"
                    text-color="white"
                    class="font-weight-bold"
                  >
                    {{ getEstadoTexto(item.estado) }}
                  </v-chip>
                </template>
              </v-data-table>
            </v-tab-item>

            <!-- -------------------------------------------------------- -->
            <!-- PASO 3: ASISTENTE Y EJECUCIÓN ASÍNCRONA                  -->
            <!-- -------------------------------------------------------- -->
            <v-tab-item>
              <v-row>
                <!-- Selección de Tablas / Módulos -->
                <v-col cols="12" md="7">
                  <v-card outlined rounded="lg" class="pa-4 fill-height">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <h4 class="text-subtitle-1 font-weight-bold mb-0">Seleccione Tablas para Sincronizar</h4>
                      <div>
                        <v-btn text x-small color="primary" @click="seleccionarTodosModulos">Seleccionar Todo</v-btn>
                        <v-btn text x-small color="grey" @click="deseleccionarTodosModulos">Limpiar</v-btn>
                      </div>
                    </div>

                    <v-divider class="my-2"></v-divider>

                    <v-row dense style="max-height: 280px; overflow-y: auto;">
                      <v-col cols="12" sm="6" v-for="t in tablasComparativa" :key="t.id">
                        <v-checkbox
                          v-model="modulosSeleccionados"
                          :value="t.id"
                          dense
                          hide-details
                          class="mt-1"
                        >
                          <template v-slot:label>
                            <span class="text-caption font-weight-medium">{{ t.label }}</span>
                            <span class="text-caption text-secondary font-monospace ml-1">({{ formatearNumero(t.dbf_registros) }})</span>
                          </template>
                        </v-checkbox>
                      </v-col>
                    </v-row>
                  </v-card>
                </v-col>

                <!-- Configuración y Botón de Inicio -->
                <v-col cols="12" md="5">
                  <v-card outlined rounded="lg" class="pa-4 fill-height d-flex flex-column justify-space-between">
                    <div>
                      <h4 class="text-subtitle-1 font-weight-bold mb-2">Parámetros de Ejecución</h4>
                      <v-divider class="my-2"></v-divider>

                      <!-- Switch Simulación -->
                      <v-card outlined class="pa-3 mb-3" rounded="lg" :color="esSimulacion ? 'amber lighten-5' : 'green lighten-5'">
                        <div class="d-flex align-center justify-space-between">
                          <div>
                            <div class="font-weight-bold text-caption d-flex align-center">
                              <v-icon left x-small :color="esSimulacion ? 'warning' : 'success'">
                                {{ esSimulacion ? 'mdi-shield-check' : 'mdi-database-edit' }}
                              </v-icon>
                              {{ esSimulacion ? 'Modo Simulación (Dry-Run)' : 'Modo Escritura Real' }}
                            </div>
                            <div class="text-caption text-secondary">
                              {{ esSimulacion ? 'Verifica consistencia sin modificar la base de datos' : '⚠️ Inserta y sincroniza registros reales en PostgreSQL' }}
                            </div>
                          </div>
                          <v-switch v-model="esSimulacion" :color="esSimulacion ? 'warning' : 'success'" hide-details dense></v-switch>
                        </div>
                      </v-card>

                      <!-- Límite de Prueba -->
                      <label class="text-caption font-weight-bold text-secondary mb-1 d-block">Límite de Registros por Tabla:</label>
                      <v-select
                        v-model="limiteRegistros"
                        :items="opcionesLimite"
                        item-text="texto"
                        item-value="valor"
                        outlined
                        dense
                        class="mb-2"
                      ></v-select>

                      <v-alert dense text :color="esSimulacion ? 'warning' : 'info'" class="text-caption mb-3">
                        <strong>{{ modulosSeleccionados.length }}</strong> módulos listos para procesar.
                      </v-alert>
                    </div>

                    <v-btn
                      :color="esSimulacion ? 'warning' : 'success'"
                      block
                      large
                      dark
                      class="text-capitalize font-weight-bold rounded-pill"
                      :loading="ejecutando"
                      :disabled="modulosSeleccionados.length === 0"
                      @click="ejecutarMigracionSecuencial"
                    >
                      <v-icon left>{{ esSimulacion ? 'mdi-play-circle-outline' : 'mdi-database-import' }}</v-icon>
                      {{ esSimulacion ? 'Iniciar Simulación (Dry-Run)' : 'Iniciar Migración Oficial' }}
                    </v-btn>

                    <v-btn
                      color="primary darken-1"
                      outlined
                      block
                      class="text-capitalize font-weight-bold rounded-pill mt-2"
                      :loading="vinculandoFacturas"
                      :disabled="ejecutando"
                      @click="vincularFacturasLecturas"
                    >
                      <v-icon left small>mdi-link-variant</v-icon>
                      Vincular Facturas & Lecturas Manualmente
                    </v-btn>
                  </v-card>
                </v-col>
              </v-row>

              <!-- Consola de Telemetría en Vivo -->
              <v-card outlined rounded="lg" class="mt-3">
                <v-card-title class="grey darken-4 white--text py-2 px-4 text-subtitle-2 d-flex justify-space-between flex-wrap">
                  <div class="d-flex align-center flex-wrap gap-2">
                    <v-icon left small color="green accent-3">mdi-console</v-icon>
                    <span>Terminal de Telemetría en Tiempo Real</span>
                    <v-chip v-if="ejecutando" x-small color="amber" class="ml-2 black--text font-weight-bold">
                      {{ progresoMigracion }}% ({{ moduloActualMigracion }})
                    </v-chip>
                  </div>

                  <div class="d-flex align-center">
                    <v-btn
                      v-if="ejecutando"
                      color="red lighten-1"
                      x-small
                      dark
                      class="mr-3 font-weight-bold text-capitalize"
                      @click="detenerMigracion"
                    >
                      <v-icon x-small left>mdi-stop-circle</v-icon>
                      Detener
                    </v-btn>
                    <div v-if="resultadoEjecucion" class="text-caption grey--text text--lighten-1">
                      <span>⏱️ {{ resultadoEjecucion.tiempo_segundos }}s</span> | 
                      <span>🧠 {{ resultadoEjecucion.memoria_pico }}</span>
                    </div>
                  </div>
                </v-card-title>

                <v-progress-linear
                  v-if="ejecutando"
                  :value="progresoMigracion"
                  color="green accent-3"
                  height="6"
                  striped
                ></v-progress-linear>

                <div
                  class="grey darken-4 pa-3 white--text font-monospace text-caption"
                  style="height: 180px; overflow-y: auto; line-height: 1.6;"
                  ref="terminalLogs"
                >
                  <div v-if="logsTerminal.length === 0" class="grey--text text--lighten-1 fst-italic">
                    Esperando inicio... Seleccione los módulos y haga clic en Iniciar Migración.
                  </div>
                  <div v-for="(log, idx) in logsTerminal" :key="idx" class="d-flex align-start mb-1">
                    <span class="grey--text mr-2">[{{ log.hora }}]</span>
                    <span :class="getLogColor(log.estado)" class="font-weight-bold mr-2">[{{ log.estado }}]</span>
                    <span class="mr-2 grey--text">[{{ log.id }}]:</span>
                    <span>{{ log.mensaje }}</span>
                  </div>
                </div>
              </v-card>
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>

        <!-- PIE DEL MODAL -->
        <v-divider></v-divider>
        <v-card-actions class="py-3 px-5 d-flex justify-space-between">
          <v-btn
            text
            class="text-capitalize rounded-pill"
            :disabled="pasoWizard === 0 || ejecutando"
            @click="pasoWizard--"
          >
            <v-icon left small>mdi-arrow-left</v-icon>
            Anterior
          </v-btn>

          <div>
            <v-btn
              text
              class="text-capitalize rounded-pill mr-2"
              @click="modalNuevaMigracion = false"
            >
              Cerrar
            </v-btn>

            <v-btn
              v-if="pasoWizard < 2"
              color="primary"
              class="text-capitalize rounded-pill font-weight-bold"
              @click="pasoWizard++"
            >
              Siguiente
              <v-icon right small>mdi-arrow-right</v-icon>
            </v-btn>

            <v-btn
              v-else
              color="primary"
              class="text-capitalize rounded-pill font-weight-bold"
              :disabled="ejecutando"
              @click="cerrarModalYRefrescar"
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
      pasoWizard: 0,

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

      // Parámetros de Migración
      esSimulacion: true,
      limiteRegistros: 0,
      progresoMigracion: 0,
      moduloActualMigracion: '',
      archivoSubida: null,
      vinculandoFacturas: false,

      rutaSeleccionada: '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',
      rutaManual: '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',

      rutasPredefinidas: [],
      tablasComparativa: [],
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

      filtroEsquema: 'todos',
      modulosSeleccionados: [
        'estados_abonado', 'conceptos_ingresos',
        'calles', 'zonas', 'tarifas', 'abonados', 'aportes_agua',
        'aportes_alcantarillado', 'bajas_socios', 'convenios', 'recibos',
        'facturas', 'lecturas', 'plan_cuentas', 'comprobantes', 'compras',
        'materiales_almacen', 'rubros_activos', 'bienes_activos'
      ],

      opcionesLimite: [
        { texto: 'Sin Límite (Migrar todo el respaldo)', valor: 0 },
        { texto: 'Probar 50 registros', valor: 50 },
        { texto: 'Probar 200 registros', valor: 200 },
        { texto: 'Probar 1.000 registros', valor: 1000 },
        { texto: 'Probar 5.000 registros', valor: 5000 },
      ],

      headersComparativa: [
        { text: 'Módulo / Tabla', value: 'label', sortable: true },
        { text: 'Archivo FoxPro', value: 'dbf_archivo', sortable: true },
        { text: 'Registros FoxPro', value: 'dbf_registros', sortable: true, align: 'end' },
        { text: 'Registros PostgreSQL', value: 'pg_registros', sortable: true, align: 'end' },
        { text: 'Diferencia', value: 'diferencia', sortable: true, align: 'end' },
        { text: 'Estado', value: 'estado', sortable: true, align: 'center' },
      ],

      logsTerminal: [],
      resultadoEjecucion: null,

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
    this.cargarRutasPredefinidas();
    this.escanearDirectorio();
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

    abrirModalNuevaMigracion() {
      this.pasoWizard = 0;
      this.modalNuevaMigracion = true;
    },

    cerrarModalYRefrescar() {
      this.modalNuevaMigracion = false;
      this.cargarHistorial();
      this.escanearDirectorio();
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
          this.escanearDirectorio();
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
    // ESCANEO Y SUBIDA DE RESPALDOS
    // ==========================================
    cargarRutasPredefinidas() {
      axios
        .get('api/datos/migracion/rutas-predefinidas')
        .then(res => {
          if (res.data.rutas) {
            this.rutasPredefinidas = res.data.rutas;
            const activa = this.rutasPredefinidas.find(r => r.existe);
            if (activa && !this.rutaManual) {
              this.rutaSeleccionada = activa.ruta;
              this.rutaManual = activa.ruta;
            }
          }
        })
        .catch(err => {
          console.error('Error rutas predefinidas:', err);
        });
    },

    onSeleccionarRuta(val) {
      this.rutaManual = val;
    },

    escanearDirectorio() {
      const ruta = this.rutaManual || this.rutaSeleccionada;
      if (!ruta) return;

      this.escaneando = true;
      axios
        .post('api/datos/migracion/escanear', { ruta })
        .then(res => {
          const d = res.data;
          this.tablasComparativa = d.tablas || [];
          this.totalDbfsEncontrados = d.total_dbfs_encontrados || 0;
          this.totalRegistrosDbf = d.total_registros_dbf || 0;
          this.totalRegistrosPg = d.total_registros_pg || 0;
          this.resumenModulos = d.resumen_modulos || this.resumenModulos;
        })
        .catch(err => {
          console.error('Error escaneando directorio:', err);
        })
        .finally(() => {
          this.escaneando = false;
        });
    },

    subirRespaldo() {
      if (!this.archivoSubida) return;

      this.subiendoArchivo = true;
      const formData = new FormData();
      formData.append('archivo', this.archivoSubida);

      axios
        .post('api/datos/migracion/subir-respaldo', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then(res => {
          this.mostrarMensaje(res.data.message || 'Respaldo extraído con éxito.', 'success', 'mdi-check-circle');
          if (res.data.ruta_extraida) {
            this.rutaManual = res.data.ruta_extraida;
            this.rutaSeleccionada = res.data.ruta_extraida;
            this.cargarRutasPredefinidas();
            this.escanearDirectorio();
            this.pasoWizard = 1;
          }
        })
        .catch(err => {
          const msg = err.response?.data?.message || 'Error al subir el archivo.';
          this.mostrarMensaje(msg, 'error', 'mdi-alert');
        })
        .finally(() => {
          this.subiendoArchivo = false;
        });
    },

    // ==========================================
    // EJECUCIÓN ASÍNCRONA SECUENCIAL
    // ==========================================
    async ejecutarMigracionSecuencial() {
      if (this.modulosSeleccionados.length === 0) {
        this.mostrarMensaje('Debe seleccionar al menos una tabla.', 'warning', 'mdi-alert');
        return;
      }

      this.ejecutando = true;
      this.cancelarMigracion = false;
      this.progresoMigracion = 0;
      this.logsTerminal = [];
      this.resultadoEjecucion = null;

      const ordenLogico = [
        'estados_abonado', 'conceptos_ingresos',
        'calles', 'zonas', 'tarifas', 'abonados', 'bajas_socios',
        'aportes_agua', 'aportes_alcantarillado', 'convenios', 'recibos',
        'facturas', 'lecturas', 'plan_cuentas', 'comprobantes', 'compras',
        'materiales_almacen', 'rubros_activos', 'bienes_activos'
      ];

      // Ordenar respetando el orden lógico preferente, pero NUNCA descartar ningún módulo seleccionado
      const seleccionadosOrdenados = [...this.modulosSeleccionados].sort((a, b) => {
        const idxA = ordenLogico.indexOf(a);
        const idxB = ordenLogico.indexOf(b);
        const valA = idxA === -1 ? 999 : idxA;
        const valB = idxB === -1 ? 999 : idxB;
        return valA - valB;
      });
      const ruta = this.rutaManual || this.rutaSeleccionada;
      const total = seleccionadosOrdenados.length;
      let procesados = 0;
      const inicioGlobal = Date.now();

      this.agregarLogTerminal('SISTEMA', 'INFO', `Iniciando migración secuencial (${total} módulos seleccionados)...`);

      for (const modulo of seleccionadosOrdenados) {
        if (this.cancelarMigracion) {
          this.agregarLogTerminal('SISTEMA', 'ADVERTENCIA', 'Migración detenida por el usuario.');
          break;
        }

        this.moduloActualMigracion = this.getLabelModulo(modulo);
        this.agregarLogTerminal(modulo, 'PROCESANDO', `Procesando módulo ${modulo}...`);

        try {
          const res = await axios.post('api/datos/migracion/ejecutar', {
            ruta,
            modulos: [modulo],
            es_simulacion: this.esSimulacion,
            limite: this.limiteRegistros,
          });

          const data = res.data;
          const logs = data.logs || [];
          logs.forEach(l => {
            this.agregarLogTerminal(l.id, l.estado, l.mensaje);
          });
        } catch (err) {
          const msg = err.response?.data?.message || err.message || 'Error de conexión';
          this.agregarLogTerminal(modulo, 'ERROR', `Fallo al procesar: ${msg}`);
        }

        procesados++;
        this.progresoMigracion = Math.round((procesados / total) * 100);
      }

      const duracionSeg = ((Date.now() - inicioGlobal) / 1000).toFixed(2);
      this.resultadoEjecucion = {
        tiempo_segundos: duracionSeg,
        memoria_pico: 'OK',
      };

      this.agregarLogTerminal('SISTEMA', 'EXITO', `Operación finalizada en ${duracionSeg}s.`);
      this.ejecutando = false;
      this.mostrarMensaje('Operación completada.', 'success', 'mdi-check-decagram');

      this.escanearDirectorio();
      this.cargarHistorial();
    },

    detenerMigracion() {
      this.cancelarMigracion = true;
      this.mostrarMensaje('Deteniendo después del módulo actual...', 'warning', 'mdi-stop');
    },

    agregarLogTerminal(id, estado, mensaje) {
      const ahora = new Date().toTimeString().split(' ')[0];
      this.logsTerminal.push({ id, estado, mensaje, hora: ahora });
      this.$nextTick(() => {
        const el = this.$refs.terminalLogs;
        if (el) el.scrollTop = el.scrollHeight;
      });
    },

    seleccionarTodosModulos() {
      this.modulosSeleccionados = this.tablasComparativa.map(t => t.id);
    },

    deseleccionarTodosModulos() {
      this.modulosSeleccionados = [];
    },

    async vincularFacturasLecturas() {
      this.vinculandoFacturas = true;
      const ruta = this.rutaManual || this.rutaSeleccionada;
      this.agregarLogTerminal('FACTURAS-LECTURAS', 'PROCESANDO', 'Iniciando vinculación de facturas con lecturas...');

      try {
        const res = await axios.post('api/datos/migracion/vincular-facturas-lecturas', { ruta });
        const data = res.data;
        this.agregarLogTerminal('FACTURAS-LECTURAS', 'EXITO', data.message);
        this.mostrarMensaje(data.message, 'success', 'mdi-check-decagram');
        this.escanearDirectorio();
      } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Error al vincular facturas';
        this.agregarLogTerminal('FACTURAS-LECTURAS', 'ERROR', msg);
        this.mostrarMensaje(msg, 'error', 'mdi-alert');
      } finally {
        this.vinculandoFacturas = false;
      }
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
</style>
