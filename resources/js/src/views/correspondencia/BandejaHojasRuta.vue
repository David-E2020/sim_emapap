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
            <span class="text-caption text-secondary">Gestión de correspondencia institucional, derivaciones y control de plazos</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill elevation-2" @click="abrirModalNuevaHR()">
            <v-icon left small>mdi-plus-circle</v-icon> + Nueva Hoja de Ruta
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABS DE BANDEJAS -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="primary" grow @change="cargarHojasRuta">
        <v-tab><v-icon left small>mdi-inbox-arrow-down</v-icon> Bandeja de Entrada ({{ totalEntrada }})</v-tab>
        <v-tab><v-icon left small>mdi-send-check</v-icon> Bandeja de Salida (Derivados)</v-tab>
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
        <!-- CITE Y QR -->
        <template v-slot:item.nro_hoja_ruta="{ item }">
          <div class="d-flex align-center py-2">
            <v-chip label small color="blue lighten-5" text-color="primary" class="font-weight-bold mr-2">
              <v-icon left x-small>mdi-file-document-outline</v-icon>
              {{ item.nro_hoja_ruta }}
            </v-chip>
            <v-chip x-small v-if="item.confidencial" color="red lighten-5" text-color="red" label class="font-weight-bold">
              CONFIDENCIAL
            </v-chip>
          </div>
        </template>

        <!-- ASUNTO Y ORIGEN -->
        <template v-slot:item.asunto="{ item }">
          <div class="py-1">
            <div class="font-weight-bold text-subtitle-2 text-truncate" style="max-width: 320px;">
              {{ item.asunto }}
            </div>
            <div class="text-caption text-secondary">
              <strong>De:</strong> {{ item.persona_origen ? item.persona_origen.nombre_completo : (item.remitente_externo || 'Externo') }}
              <span v-if="item.unidad_origen"> | {{ item.unidad_origen.nombre }}</span>
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

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center gap-1">
            <!-- Botón Recibir si está pendiente -->
            <v-btn
              v-if="item.mi_derivacion && item.mi_derivacion.estado_derivacion === 'PENDIENTE_RECEPCION'"
              x-small
              color="success"
              class="text-capitalize rounded-pill mr-1"
              @click="recibirDerivacion(item.mi_derivacion.id)"
            >
              <v-icon left x-small>mdi-inbox-arrow-down</v-icon> Recibir
            </v-btn>

            <!-- Botón Derivar -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="abrirModalDerivar(item)">
                  <v-icon small>mdi-send</v-icon>
                </v-btn>
              </template>
              <span>Derivar Trámite</span>
            </v-tooltip>

            <!-- Botón Trazabilidad / Seguimiento -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="indigo" v-bind="attrs" v-on="on" @click="verSeguimiento(item.id)">
                  <v-icon small>mdi-timeline-text-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Trazabilidad</span>
            </v-tooltip>

            <!-- Botón Carátula Oficial PDF / HTML -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="teal" v-bind="attrs" v-on="on" @click="imprimirCaratula(item.id)">
                  <v-icon small>mdi-printer</v-icon>
                </v-btn>
              </template>
              <span>Imprimir Carátula Oficial con QR</span>
            </v-tooltip>

            <!-- Concluir / Archivar -->
            <v-tooltip bottom v-if="item.estado !== 'CONCLUIDO'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon small color="green darken-1" v-bind="attrs" v-on="on" @click="abrirModalCerrar(item)">
                  <v-icon small>mdi-check-circle-outline</v-icon>
                </v-btn>
              </template>
              <span>Concluir y Archivar</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- MODAL NUEVA HOJA DE RUTA -->
    <v-dialog v-model="dialogNuevaHR" max-width="700px" persistent>
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
              label="Funcionario Destinatario"
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

    <!-- MODAL DERIVAR HOJA DE RUTA -->
    <v-dialog v-model="dialogDerivar" max-width="650px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-send</v-icon> Derivar: {{ hrSeleccionada.nro_hoja_ruta }}
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3 pa-2 rounded bg-grey-lighten-4 font-weight-medium text-caption">
            <strong>Asunto:</strong> {{ hrSeleccionada.asunto }}
          </div>

          <v-select
            v-model="formDerivar.id_unidad_destino"
            :items="unidades"
            item-text="nombre"
            item-value="id"
            label="Unidad Destino *"
            dense
            outlined
            class="mb-2"
            @change="cargarFuncionariosUnidadDerivar"
          ></v-select>

          <v-select
            v-model="formDerivar.id_funcionario_destino"
            :items="funcionariosDerivar"
            item-text="nombre_completo"
            item-value="id"
            label="Funcionario Destinatario"
            dense
            outlined
            clearable
            class="mb-2"
          ></v-select>

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

    <!-- MODAL CERRAR Y ARCHIVAR -->
    <v-dialog v-model="dialogCerrar" max-width="500px" persistent>
      <v-card rounded="lg" v-if="hrSeleccionada">
        <v-card-title class="font-weight-bold text-h6 green darken-2 white--text py-3">
          <v-icon left color="white">mdi-check-circle</v-icon> Concluir Trámite
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
          <v-btn color="success" class="rounded-pill text-capitalize" :loading="guardando" @click="confirmarCierre">
            Concluir y Archivar
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

      headers: [
        { text: 'Código CITE', value: 'nro_hoja_ruta', width: '220px' },
        { text: 'Asunto / Origen', value: 'asunto' },
        { text: 'Plazo / Semáforo', value: 'semaforo', width: '180px' },
        { text: 'Prioridad', value: 'prioridad', width: '110px' },
        { text: 'Fecha Ingreso', value: 'fecha_solicitud', width: '140px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '220px', align: 'center' },
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
      },

      dialogCerrar: false,
      motivoCierre: 'Trámite finalizado y atendido a satisfacción.',

      snackbar: { status: false, text: '', color: 'success' },
    };
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
          if (this.tabActual === 0) {
            this.totalEntrada = response.data.meta ? response.data.meta.total : this.hojasRuta.length;
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
          // Aplanar unidades
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
      };
      this.dialogDerivar = true;
    },
    async confirmarDerivacion() {
      if (!this.formDerivar.id_unidad_destino) {
        this.mostrarMensaje('Selecciona la unidad destino.', 'warning');
        return;
      }
      this.guardando = true;
      try {
        const ultimaDer = this.hrSeleccionada.derivaciones ? this.hrSeleccionada.derivaciones[this.hrSeleccionada.derivaciones.length - 1] : null;
        const payload = {
          id_hoja_ruta: this.hrSeleccionada.id,
          id_derivacion_padre: ultimaDer ? ultimaDer.id : null,
          proveido: this.formDerivar.proveido,
          instruccion_detalle: this.formDerivar.instruccion_detalle,
          dias_plazo: this.formDerivar.dias_plazo,
          destinatarios: [
            {
              id_unidad_destino: this.formDerivar.id_unidad_destino,
              id_funcionario_destino: this.formDerivar.id_funcionario_destino,
              es_copia: false,
            }
          ],
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
