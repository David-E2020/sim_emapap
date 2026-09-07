<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-inbox-multiple</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Bandeja de Hojas de Ruta</h2>
            <span class="text-caption text-secondary">Gestión de correspondencia institucional, derivaciones y control de plazos (Estándar Londra)</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="abrirModalNuevaHR()">
            <v-icon left small>mdi-plus-circle</v-icon> + Nueva Hoja de Ruta
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABS DE BANDEJAS (LONDRA: ENTRADA / SALIDA / AGRUPADOS / ARCHIVADOS) -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="primary" grow @change="cargarHojasRuta">
        <v-tab><v-icon left small>mdi-inbox-arrow-down</v-icon> Bandeja de Entrada ({{ totalEntrada }})</v-tab>
        <v-tab><v-icon left small>mdi-send-check</v-icon> Bandeja de Salida ({{ totalSalida }})</v-tab>
        <v-tab><v-icon left small>mdi-folder-multiple</v-icon> Expedientes Agrupados</v-tab>
        <v-tab><v-icon left small>mdi-archive-check</v-icon> Archivados / Concluidos</v-tab>
      </v-tabs>
    </v-card>

    <!-- FILTROS Y BÚSQUEDA -->
    <v-card rounded="lg" class="mb-5 pa-4 erp-card-elevated">
      <v-row dense align="center">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="busqueda"
            label="Buscar por CITE, Asunto o Referencia..."
            prepend-inner-icon="mdi-magnify"
            dense
            outlined
            hide-details
            clearable
            @keyup.enter="cargarHojasRuta"
            @click:clear="cargarHojasRuta"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filtroPrioridad"
            :items="['TODAS', 'URGENTE', 'ALTA', 'MEDIA', 'BAJA']"
            label="Prioridad"
            dense
            outlined
            hide-details
            @change="cargarHojasRuta"
          ></v-select>
        </v-col>
        <v-col cols="12" sm="6" md="3" class="d-flex justify-end">
          <v-btn color="primary" outlined class="text-capitalize rounded-pill mr-2" @click="cargarHojasRuta">
            <v-icon left small>mdi-filter</v-icon> Filtrar
          </v-btn>
          <v-btn icon color="secondary" @click="limpiarFiltros"><v-icon>mdi-refresh</v-icon></v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- TABLA DE HOJAS DE RUTA -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <v-data-table
        :headers="headers"
        :items="hojasRuta"
        :loading="cargando"
        class="erp-table"
        dense
        :items-per-page="15"
      >
        <!-- CITE Y ESTADO -->
        <template v-slot:item.nro_hoja_ruta="{ item }">
          <div class="d-flex align-center py-2">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold mr-2">
              <v-icon left x-small>mdi-file-document-outline</v-icon>
              {{ item.nro_hoja_ruta }}
            </v-chip>
            <v-chip x-small v-if="item.confidencial" color="red lighten-5" text-color="red" label class="font-weight-bold mr-1">
              CONFIDENCIAL
            </v-chip>
            <v-chip x-small v-if="item.estado === 'AGRUPADO'" color="purple lighten-5" text-color="purple darken-2" label class="font-weight-bold">
              AGRUPADO
            </v-chip>
          </div>
        </template>

        <!-- ASUNTO Y ORIGEN / ESTA CON -->
        <template v-slot:item.asunto="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2 text-truncate" style="max-width: 340px;">
              {{ item.asunto }}
            </div>
            <div class="text-caption text-secondary">
              <strong>De:</strong> {{ item.persona_origen ? item.persona_origen.nombre_completo : (item.remitente_externo || 'Externo') }}
              <span v-if="item.unidad_origen"> | {{ item.unidad_origen.nombre }}</span>
            </div>
            <div v-if="item.esta_con && tabActual === 1" class="text-caption primary--text">
              <strong>Custodia actual:</strong> {{ item.esta_con.funcionario }} ({{ item.esta_con.unidad }}) - 
              <span :class="item.esta_con.estado === 'RECIBIDO' ? 'green--text font-weight-bold' : 'orange--text font-weight-bold'">
                {{ item.esta_con.estado === 'RECIBIDO' ? 'Recibido' : 'En Tránsito' }}
              </span>
            </div>
          </div>
        </template>

        <!-- PLAZO Y SEMÁFORO -->
        <template v-slot:item.semaforo="{ item }">
          <div v-if="item.semaforo" class="d-flex align-center">
            <v-badge :color="item.semaforo.color" dot inline class="mr-1"></v-badge>
            <span class="text-caption font-weight-medium" :class="item.semaforo.color + '--text'">
              {{ item.semaforo.texto }}
            </span>
          </div>
          <span v-else class="text-caption text-secondary">-</span>
        </template>

        <!-- PRIORIDAD -->
        <template v-slot:item.prioridad="{ item }">
          <v-chip x-small label :color="getColorPrioridad(item.prioridad)" class="font-weight-bold white--text">
            {{ item.prioridad }}
          </v-chip>
        </template>

        <!-- FECHA -->
        <template v-slot:item.fecha_solicitud="{ item }">
          <span class="text-caption text-secondary">{{ formatFecha(item.fecha_solicitud) }}</span>
        </template>

        <!-- ACCIONES (LONDRA EXACT ENGINE) -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1 flex-wrap">
            <!-- 1. Botón Recibir (Bandeja Entrada) -->
            <v-btn
              v-if="item.acciones_permitidas && item.acciones_permitidas.puede_recibir"
              x-small
              color="success"
              class="text-capitalize rounded-pill mr-1"
              @click="recibirDerivacion(item.mi_derivacion ? item.mi_derivacion.id : item.id)"
            >
              <v-icon left x-small>mdi-inbox-arrow-down</v-icon> Recibir
            </v-btn>

            <!-- 2. Botón Derivar (Custodia activa) -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_derivar">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="abrirModalDerivar(item)">
                  <v-icon small>mdi-send</v-icon>
                </v-btn>
              </template>
              <span>Derivar Trámite</span>
            </v-tooltip>

            <!-- 3. Botón Devolver con Observaciones -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_devolver">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="orange darken-2" v-bind="attrs" v-on="on" @click="abrirModalDevolver(item)">
                  <v-icon small>mdi-undo-variant</v-icon>
                </v-btn>
              </template>
              <span>Devolver con Observaciones</span>
            </v-tooltip>

            <!-- 4. Botón Anular / Deshacer Derivación (Bandeja Salida - En Tránsito) -->
            <v-tooltip bottom v-if="tabActual === 1 && item.acciones_permitidas && item.acciones_permitidas.puede_anular_derivacion">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="red darken-1" v-bind="attrs" v-on="on" @click="confirmarAnularDerivacion(item)">
                  <v-icon small>mdi-cancel</v-icon>
                </v-btn>
              </template>
              <span>Anular / Deshacer Derivación en Tránsito</span>
            </v-tooltip>

            <!-- 5. Botón Agrupar Expediente -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_agrupar && tabActual === 0">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="purple" v-bind="attrs" v-on="on" @click="abrirModalAgrupar(item)">
                  <v-icon small>mdi-folder-plus-outline</v-icon>
                </v-btn>
              </template>
              <span>Agrupar con otro Expediente</span>
            </v-tooltip>

            <!-- 6. Botón Desagrupar (Pestaña Agrupados) -->
            <v-tooltip bottom v-if="tabActual === 2 && item.agrupaciones && item.agrupaciones.length">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="purple darken-2" v-bind="attrs" v-on="on" @click="desagruparExpediente(item.agrupaciones[0].id)">
                  <v-icon small>mdi-folder-remove-outline</v-icon>
                </v-btn>
              </template>
              <span>Desagrupar Expediente</span>
            </v-tooltip>

            <!-- 7. Concluir / Archivar -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_cerrar">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="teal darken-1" v-bind="attrs" v-on="on" @click="abrirModalCerrar(item)">
                  <v-icon small>mdi-archive-check-outline</v-icon>
                </v-btn>
              </template>
              <span>Concluir y Archivar</span>
            </v-tooltip>

            <!-- 8. Reabrir Trámite Concluido -->
            <v-tooltip bottom v-if="item.acciones_permitidas && item.acciones_permitidas.puede_reabrir">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="blue darken-2" v-bind="attrs" v-on="on" @click="abrirModalReabrir(item)">
                  <v-icon small>mdi-lock-open-outline</v-icon>
                </v-btn>
              </template>
              <span>Reabrir Trámite</span>
            </v-tooltip>

            <!-- 9. Trazabilidad / Seguimiento -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="indigo" v-bind="attrs" v-on="on" @click="verSeguimiento(item.id)">
                  <v-icon small>mdi-timeline-text-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Trazabilidad</span>
            </v-tooltip>

            <!-- 10. Carátula Oficial PDF / HTML con QR -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="blue-grey" v-bind="attrs" v-on="on" @click="imprimirCaratula(item.id)">
                  <v-icon small>mdi-printer</v-icon>
                </v-btn>
              </template>
              <span>Imprimir Carátula Oficial con QR</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL NUEVA HOJA DE RUTA -->
    <v-dialog v-model="dialogNuevaHR" max-width="720px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-plus-circle</v-icon> Generar Nueva Hoja de Ruta
        </v-card-title>
        <v-card-text class="pt-4">
          <v-form ref="formHRRef">
            <v-textarea
              v-model="formHR.asunto"
              label="Asunto / Referencia del Trámite *"
              rows="2"
              dense
              outlined
              class="mb-2"
              :rules="[v => !!v || 'El asunto es obligatorio']"
            ></v-textarea>

            <v-row dense>
              <v-col cols="12" md="4">
                <v-select
                  v-model="formHR.prioridad"
                  :items="['URGENTE', 'ALTA', 'MEDIA', 'BAJA']"
                  label="Prioridad"
                  dense
                  outlined
                ></v-select>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="formHR.nro_fojas"
                  label="Nro. Fojas"
                  type="number"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="formHR.nro_anexos"
                  label="Nro. Anexos"
                  type="number"
                  dense
                  outlined
                ></v-text-field>
              </v-col>
            </v-row>

            <v-divider class="my-3"></v-divider>
            <span class="text-subtitle-2 font-weight-bold primary--text">Derivación Inicial Inmediata</span>

            <v-select
              v-model="formHR.id_unidad_destino"
              :items="unidades"
              item-text="nombre"
              item-value="id"
              label="Unidad Destino *"
              dense
              outlined
              class="mt-2 mb-2"
              :rules="[v => !!v || 'Seleccione la unidad destino']"
              @change="cargarFuncionariosUnidad"
            ></v-select>

            <v-select
              v-model="formHR.id_funcionario_destino"
              :items="funcionariosDestino"
              item-text="nombre_completo"
              item-value="id"
              label="Funcionario Destinatario (Opcional)"
              dense
              outlined
              clearable
              class="mb-2"
            ></v-select>

            <v-select
              v-model="formHR.proveido"
              :items="proveidos"
              item-text="param_nombre"
              item-value="param_nombre"
              label="Proveído Oficial *"
              dense
              outlined
              class="mb-2"
            ></v-select>

            <v-text-field
              v-model="formHR.instruccion_detalle"
              label="Instrucción de Atención"
              dense
              outlined
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogNuevaHR = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="guardarNuevaHR">
            Generar CITE y Derivar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL DERIVAR HOJA DE RUTA (LONDRA: PRINCIPAL + MÚLTIPLES COPIAS CC) -->
    <v-dialog v-model="dialogDerivar" max-width="750px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-send</v-icon> Derivar: {{ hrSeleccionada.nro_hoja_ruta }}
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3 pa-2 rounded bg-grey-lighten-4 font-weight-medium text-caption">
            <strong>Asunto:</strong> {{ hrSeleccionada.asunto }}
          </div>

          <!-- DESTINATARIO PRINCIPAL -->
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-subtitle-2 font-weight-bold primary--text">Destinatario Principal</span>
          </div>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-select
                v-model="formDerivar.id_unidad_destino"
                :items="unidades"
                item-text="nombre"
                item-value="id"
                label="Unidad Destino *"
                dense
                outlined
                @change="cargarFuncionariosUnidadDerivar"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                v-model="formDerivar.id_funcionario_destino"
                :items="funcionariosDerivar"
                item-text="nombre_completo"
                item-value="id"
                label="Funcionario Destinatario"
                dense
                outlined
                clearable
              ></v-select>
            </v-col>
          </v-row>

          <!-- DESTINATARIOS EN COPIA (CC) -->
          <div class="d-flex align-center justify-space-between my-2">
            <span class="text-subtitle-2 font-weight-bold grey--text text--darken-2">Copias de Cortesía (CC)</span>
            <v-btn x-small color="secondary" outlined class="rounded-pill text-capitalize" @click="agregarCopia">
              <v-icon left x-small>mdi-plus</v-icon> + Agregar Copia
            </v-btn>
          </div>

          <div v-for="(copia, idx) in formDerivar.copias" :key="'copia-' + idx" class="pa-2 mb-2 grey lighten-4 rounded">
            <div class="d-flex align-center justify-space-between mb-1">
              <span class="text-caption font-weight-bold">Copia #{{ idx + 1 }}</span>
              <v-btn icon x-small color="red" @click="eliminarCopia(idx)"><v-icon x-small>mdi-delete</v-icon></v-btn>
            </div>
            <v-row dense>
              <v-col cols="12" md="6">
                <v-select
                  v-model="copia.id_unidad_destino"
                  :items="unidades"
                  item-text="nombre"
                  item-value="id"
                  label="Unidad Copia"
                  dense
                  outlined
                  hide-details
                  @change="cargarFuncionariosCopia(idx)"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="copia.id_funcionario_destino"
                  :items="copia.funcionarios || []"
                  item-text="nombre_completo"
                  item-value="id"
                  label="Funcionario Copia"
                  dense
                  outlined
                  hide-details
                  clearable
                ></v-select>
              </v-col>
            </v-row>
          </div>

          <v-divider class="my-3"></v-divider>

          <!-- PROVEÍDO Y PLAZO -->
          <v-select
            v-model="formDerivar.proveido"
            :items="proveidos"
            item-text="param_nombre"
            item-value="param_nombre"
            label="Proveído Oficial *"
            dense
            outlined
            class="mb-2"
          ></v-select>

          <v-row dense>
            <v-col cols="12" md="8">
              <v-text-field
                v-model="formDerivar.instruccion_detalle"
                label="Instrucción de Atención"
                dense
                outlined
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="formDerivar.dias_plazo"
                label="Días de Plazo"
                type="number"
                dense
                outlined
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogDerivar = false">Cancelar</v-btn>
          <v-btn color="primary" class="rounded-pill text-capitalize px-4" :loading="guardando" @click="confirmarDerivacion">
            Confirmar Derivación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL DEVOLVER CON OBSERVACIONES (LONDRA RETORNO) -->
    <v-dialog v-model="dialogDevolver" max-width="550px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 orange darken-3 white--text py-3">
          <v-icon left color="white">mdi-undo-variant</v-icon> Devolver Trámite Observado
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            Al devolver este trámite, retornará inmediatamente a la bandeja del remitente anterior con el motivo indicado.
          </p>
          <v-textarea
            v-model="motivoDevolucion"
            label="Motivo de Devolución / Observación Técnica *"
            rows="3"
            dense
            outlined
            placeholder="Especifique las razones por las cuales se devuelve el trámite..."
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogDevolver = false">Cancelar</v-btn>
          <v-btn color="orange darken-3" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="confirmarDevolucion">
            Confirmar Devolución
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL AGRUPAR HOJA DE RUTA -->
    <v-dialog v-model="dialogAgrupar" max-width="600px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 purple darken-2 white--text py-3">
          <v-icon left color="white">mdi-folder-plus-outline</v-icon> Agrupar Expedientes
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">
            Seleccione la Hoja de Ruta secundaria que desea anexar al expediente principal (<strong>{{ hrSeleccionada.nro_hoja_ruta }}</strong>).
          </p>
          <v-select
            v-model="idHrAnexada"
            :items="hojasRutaDisponiblesAgrupar"
            item-text="texto_display"
            item-value="id"
            label="Hoja de Ruta a Anexar *"
            dense
            outlined
            class="mb-2"
          ></v-select>
          <v-textarea
            v-model="motivoAgrupacion"
            label="Motivo de la Agrupación *"
            rows="2"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogAgrupar = false">Cancelar</v-btn>
          <v-btn color="purple darken-2" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="confirmarAgrupacion">
            Agrupar Expedientes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL CERRAR Y ARCHIVAR -->
    <v-dialog v-model="dialogCerrar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 teal darken-2 white--text py-3">
          <v-icon left color="white">mdi-archive-check-outline</v-icon> Concluir Trámite
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">Al concluir la hoja de ruta, se archivará el expediente y finalizarán los plazos de atención.</p>
          <v-textarea
            v-model="motivoCierre"
            label="Motivo de Conclusión / Observaciones *"
            rows="3"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogCerrar = false">Cancelar</v-btn>
          <v-btn color="teal darken-2" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="confirmarCierre">
            Concluir y Archivar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL REABRIR TRÁMITE -->
    <v-dialog v-model="dialogReabrir" max-width="500px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 blue darken-2 white--text py-3">
          <v-icon left color="white">mdi-lock-open-outline</v-icon> Reabrir Expediente
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-caption text-secondary">Especifique el motivo por el cual se reabre el expediente para continuar con su tramitación.</p>
          <v-textarea
            v-model="motivoReapertura"
            label="Motivo de Reapertura *"
            rows="3"
            dense
            outlined
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text class="rounded-pill text-capitalize" @click="dialogReabrir = false">Cancelar</v-btn>
          <v-btn color="blue darken-2" class="rounded-pill text-capitalize white--text px-4" :loading="guardando" @click="confirmarReapertura">
            Reabrir Trámite
          </v-btn>
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
  name: 'BandejaHojasRuta',
  data() {
    return {
      tabActual: 0,
      hojasRuta: [],
      cargando: false,
      guardando: false,
      busqueda: '',
      filtroPrioridad: 'TODAS',
      totalEntrada: 0,
      totalSalida: 0,

      headers: [
        { text: 'Código CITE', value: 'nro_hoja_ruta', width: '220px' },
        { text: 'Asunto / Origen', value: 'asunto' },
        { text: 'Plazo / Semáforo', value: 'semaforo', width: '170px' },
        { text: 'Prioridad', value: 'prioridad', width: '100px' },
        { text: 'Fecha Ingreso', value: 'fecha_solicitud', width: '130px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '240px', align: 'center' },
      ],

      unidades: [],
      proveidos: [],
      funcionariosDestino: [],
      funcionariosDerivar: [],

      dialogNuevaHR: false,
      formHR: {
        asunto: '',
        prioridad: 'MEDIA',
        nro_fojas: 1,
        nro_anexos: 0,
        id_unidad_destino: null,
        id_funcionario_destino: null,
        proveido: 'PASE A SUS EFECTOS',
        instruccion_detalle: '',
      },

      dialogDerivar: false,
      hrSeleccionada: null,
      formDerivar: {
        id_unidad_destino: null,
        id_funcionario_destino: null,
        proveido: 'PASE A SUS EFECTOS',
        instruccion_detalle: '',
        dias_plazo: 2,
        copias: [],
      },

      dialogDevolver: false,
      motivoDevolucion: '',

      dialogAgrupar: false,
      idHrAnexada: null,
      motivoAgrupacion: 'Agrupación de antecedentes y expedientes relacionados.',

      dialogCerrar: false,
      motivoCierre: 'Trámite finalizado y atendido a satisfacción.',

      dialogReabrir: false,
      motivoReapertura: 'Reapertura para diligencias complementarias.',

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    hojasRutaDisponiblesAgrupar() {
      if (!this.hrSeleccionada) return [];
      return this.hojasRuta
        .filter(x => x.id !== this.hrSeleccionada.id && x.estado !== 'CERRADO' && x.estado !== 'AGRUPADO')
        .map(x => ({
          id: x.id,
          texto_display: `${x.nro_hoja_ruta} - ${x.asunto.substring(0, 45)}...`,
        }));
    },
  },
  mounted() {
    this.cargarHojasRuta();
    this.cargarUnidades();
    this.cargarProveidos();
  },
  methods: {
    async cargarHojasRuta() {
      this.cargando = true;
      try {
        const bandejas = ['ENTRADA', 'SALIDA', 'AGRUPADOS', 'ARCHIVADOS'];
        const params = {
          bandeja: bandejas[this.tabActual] || 'ENTRADA',
          q: this.busqueda || undefined,
          prioridad: this.filtroPrioridad !== 'TODAS' ? this.filtroPrioridad : undefined,
        };
        const response = await window.axios.get('/api/correspondencia/hojas-ruta', { params });
        if (response.data && response.data.success) {
          this.hojasRuta = response.data.data;
          if (response.data.meta) {
            this.totalEntrada = response.data.meta.total_entrada || this.totalEntrada;
            this.totalSalida = response.data.meta.total_salida || this.totalSalida;
          }
        }
      } catch (e) {
        this.mostrarMensaje('Error al cargar hojas de ruta.', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async cargarUnidades() {
      try {
        const res = await window.axios.get('/api/rrhh/organigrama');
        if (res.data && res.data.success) {
          const planas = [];
          const aplanar = (items) => {
            items.forEach(u => {
              planas.push({ id: u.id, nombre: u.nombre, puestos: u.puestos || [] });
              if (u.dependencias && u.dependencias.length) aplanar(u.dependencias);
            });
          };
          aplanar(res.data.data);
          this.unidades = planas;
        }
      } catch (e) {}
    },
    async cargarProveidos() {
      try {
        const res = await window.axios.get('/api/correspondencia/configuracion/proveidos');
        if (res.data && res.data.success) {
          this.proveidos = res.data.data;
        }
      } catch (e) {}
    },
    cargarFuncionariosUnidad() {
      const u = this.unidades.find(x => x.id === this.formHR.id_unidad_destino);
      if (u && u.puestos) {
        const funcs = [];
        u.puestos.forEach(p => {
          if (p.asignaciones && p.asignaciones.length && p.asignaciones[0].persona) {
            funcs.push({
              id: p.asignaciones[0].persona.id,
              nombre_completo: `${p.asignaciones[0].persona.nombre_completo} (${p.nombre})`,
            });
          }
        });
        this.funcionariosDestino = funcs;
      } else {
        this.funcionariosDestino = [];
      }
    },
    cargarFuncionariosUnidadDerivar() {
      const u = this.unidades.find(x => x.id === this.formDerivar.id_unidad_destino);
      if (u && u.puestos) {
        const funcs = [];
        u.puestos.forEach(p => {
          if (p.asignaciones && p.asignaciones.length && p.asignaciones[0].persona) {
            funcs.push({
              id: p.asignaciones[0].persona.id,
              nombre_completo: `${p.asignaciones[0].persona.nombre_completo} (${p.nombre})`,
            });
          }
        });
        this.funcionariosDerivar = funcs;
      } else {
        this.funcionariosDerivar = [];
      }
    },
    cargarFuncionariosCopia(idx) {
      const copia = this.formDerivar.copias[idx];
      if (!copia) return;
      const u = this.unidades.find(x => x.id === copia.id_unidad_destino);
      if (u && u.puestos) {
        const funcs = [];
        u.puestos.forEach(p => {
          if (p.asignaciones && p.asignaciones.length && p.asignaciones[0].persona) {
            funcs.push({
              id: p.asignaciones[0].persona.id,
              nombre_completo: `${p.asignaciones[0].persona.nombre_completo} (${p.nombre})`,
            });
          }
        });
        this.$set(copia, 'funcionarios', funcs);
      } else {
        this.$set(copia, 'funcionarios', []);
      }
    },
    agregarCopia() {
      this.formDerivar.copias.push({
        id_unidad_destino: null,
        id_funcionario_destino: null,
        funcionarios: [],
      });
    },
    eliminarCopia(idx) {
      this.formDerivar.copias.splice(idx, 1);
    },
    abrirModalNuevaHR() {
      this.formHR = {
        asunto: '',
        prioridad: 'MEDIA',
        nro_fojas: 1,
        nro_anexos: 0,
        id_unidad_destino: null,
        id_funcionario_destino: null,
        proveido: 'PASE A SUS EFECTOS',
        instruccion_detalle: '',
      };
      this.dialogNuevaHR = true;
    },
    async guardarNuevaHR() {
      if (!this.formHR.asunto || !this.formHR.id_unidad_destino) {
        this.mostrarMensaje('Completa los campos obligatorios.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const payload = {
          asunto: this.formHR.asunto,
          prioridad: this.formHR.prioridad,
          nro_fojas: this.formHR.nro_fojas,
          nro_anexos: this.formHR.nro_anexos,
          proveido: this.formHR.proveido,
          instruccion_detalle: this.formHR.instruccion_detalle,
          destinatarios: [
            {
              id_unidad_destino: this.formHR.id_unidad_destino,
              id_funcionario_destino: this.formHR.id_funcionario_destino,
              es_copia: false,
            }
          ],
        };
        const res = await window.axios.post('/api/correspondencia/hojas-ruta', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Hoja de ruta generada exitosamente.', 'success');
          this.dialogNuevaHR = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al crear hoja de ruta.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async recibirDerivacion(idDerivacion) {
      try {
        const res = await window.axios.post(`/api/correspondencia/derivaciones/${idDerivacion}/recibir`);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Trámite recepcionado en bandeja.', 'success');
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al recibir trámite.', 'error');
      }
    },
    abrirModalDerivar(item) {
      this.hrSeleccionada = item;
      this.formDerivar = {
        id_unidad_destino: null,
        id_funcionario_destino: null,
        proveido: 'PASE A SUS EFECTOS',
        instruccion_detalle: '',
        dias_plazo: 2,
        copias: [],
      };
      this.dialogDerivar = true;
    },
    async confirmarDerivacion() {
      if (!this.formDerivar.id_unidad_destino) {
        this.mostrarMensaje('Selecciona la unidad destino principal.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const idDerivacionPadre = this.hrSeleccionada.mi_derivacion ? this.hrSeleccionada.mi_derivacion.id : null;
        
        const destinatarios = [
          {
            id_unidad_destino: this.formDerivar.id_unidad_destino,
            id_funcionario_destino: this.formDerivar.id_funcionario_destino,
            es_copia: false,
          }
        ];

        this.formDerivar.copias.forEach(c => {
          if (c.id_unidad_destino) {
            destinatarios.push({
              id_unidad_destino: c.id_unidad_destino,
              id_funcionario_destino: c.id_funcionario_destino,
              es_copia: true,
            });
          }
        });

        const payload = {
          id_hoja_ruta: this.hrSeleccionada.id,
          id_derivacion_padre: idDerivacionPadre,
          proveido: this.formDerivar.proveido,
          instruccion_detalle: this.formDerivar.instruccion_detalle,
          dias_plazo: this.formDerivar.dias_plazo,
          destinatarios,
        };

        const res = await window.axios.post('/api/correspondencia/derivaciones', payload);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Hoja de ruta derivada exitosamente.', 'success');
          this.dialogDerivar = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al derivar trámite.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    abrirModalDevolver(item) {
      this.hrSeleccionada = item;
      this.motivoDevolucion = '';
      this.dialogDevolver = true;
    },
    async confirmarDevolucion() {
      if (!this.motivoDevolucion.trim()) {
        this.mostrarMensaje('Debe especificar el motivo de devolución.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const idDer = this.hrSeleccionada.mi_derivacion ? this.hrSeleccionada.mi_derivacion.id : this.hrSeleccionada.id;
        const res = await window.axios.post(`/api/correspondencia/derivaciones/${idDer}/devolver`, {
          motivo: this.motivoDevolucion,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Trámite devuelto con observaciones.', 'success');
          this.dialogDevolver = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al devolver trámite.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async confirmarAnularDerivacion(item) {
      if (!confirm(`¿Está seguro de anular la derivación en tránsito de la Hoja de Ruta ${item.nro_hoja_ruta}?`)) return;
      try {
        const derivacionTransito = item.derivaciones ? item.derivaciones.find(d => d.estado_derivacion === 'PENDIENTE_RECEPCION') : null;
        const idDer = derivacionTransito ? derivacionTransito.id : (item.mi_derivacion ? item.mi_derivacion.id : item.id);
        
        const res = await window.axios.post(`/api/correspondencia/derivaciones/${idDer}/anular`);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Derivación anulada. El trámite retornó a su bandeja.', 'success');
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('No se pudo anular la derivación.', 'error');
      }
    },
    abrirModalAgrupar(item) {
      this.hrSeleccionada = item;
      this.idHrAnexada = null;
      this.motivoAgrupacion = 'Agrupación de antecedentes y expedientes relacionados.';
      this.dialogAgrupar = true;
    },
    async confirmarAgrupacion() {
      if (!this.idHrAnexada) {
        this.mostrarMensaje('Seleccione el expediente a anexar.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const res = await window.axios.post('/api/correspondencia/hojas-ruta/agrupar', {
          id_hoja_ruta_principal: this.hrSeleccionada.id,
          id_hoja_ruta_anexada: this.idHrAnexada,
          motivo: this.motivoAgrupacion,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Expedientes agrupados con éxito.', 'success');
          this.dialogAgrupar = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al agrupar expedientes.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    async desagruparExpediente(idAgrupacion) {
      if (!confirm('¿Desea desagrupar este expediente?')) return;
      try {
        const res = await window.axios.post(`/api/correspondencia/hojas-ruta/${idAgrupacion}/desagrupar`);
        if (res.data && res.data.success) {
          this.mostrarMensaje('Expediente desagrupado exitosamente.', 'success');
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al desagrupar expediente.', 'error');
      }
    },
    abrirModalCerrar(item) {
      this.hrSeleccionada = item;
      this.motivoCierre = 'Trámite finalizado y atendido a satisfacción.';
      this.dialogCerrar = true;
    },
    async confirmarCierre() {
      this.guardando = true;
      try {
        const res = await window.axios.post(`/api/correspondencia/hojas-ruta/${this.hrSeleccionada.id}/cerrar`, {
          motivo_cierre: this.motivoCierre,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Expediente concluido y archivado.', 'success');
          this.dialogCerrar = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al cerrar expediente.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    abrirModalReabrir(item) {
      this.hrSeleccionada = item;
      this.motivoReapertura = 'Reapertura para diligencias complementarias.';
      this.dialogReabrir = true;
    },
    async confirmarReapertura() {
      this.guardando = true;
      try {
        const res = await window.axios.post(`/api/correspondencia/hojas-ruta/${this.hrSeleccionada.id}/reabrir`, {
          motivo_reapertura: this.motivoReapertura,
        });
        if (res.data && res.data.success) {
          this.mostrarMensaje('Expediente reabierto exitosamente.', 'success');
          this.dialogReabrir = false;
          this.cargarHojasRuta();
        }
      } catch (e) {
        this.mostrarMensaje('Error al reabrir expediente.', 'error');
      } finally {
        this.guardando = false;
      }
    },
    verSeguimiento(id) {
      this.$router.push({ path: '/correspondencia/seguimiento', query: { id } });
    },
    imprimirCaratula(id) {
      window.open(`/api/correspondencia/hojas-ruta/${id}/caratula`, '_blank');
    },
    limpiarFiltros() {
      this.busqueda = '';
      this.filtroPrioridad = 'TODAS';
      this.cargarHojasRuta();
    },
    getColorPrioridad(p) {
      const map = { URGENTE: 'red darken-1', ALTA: 'orange darken-2', MEDIA: 'blue darken-1', BAJA: 'grey darken-1' };
      return map[p] || 'grey';
    },
    formatFecha(f) {
      if (!f) return '-';
      const d = new Date(f);
      return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
.erp-table {
  border-radius: 8px;
}
</style>
