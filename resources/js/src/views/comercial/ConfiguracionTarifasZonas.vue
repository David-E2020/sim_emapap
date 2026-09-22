<template>
  <div>
    <!-- CABECERA PRINCIPAL -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-table-large</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Registro de Categorías y Tarifas</h2>
            <span class="text-caption text-secondary">
              Estructura tarifaria oficial: volúmenes mínimos, 11 rangos de consumo variable y alcantarillado
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn
            color="primary"
            outlined
            class="text-capitalize rounded-pill"
            @click="agregarFilaCategoria()"
          >
            <v-icon left small>mdi-plus</v-icon> Nueva Categoría (Otro)
          </v-btn>

          <v-btn
            color="success"
            class="text-capitalize rounded-pill font-weight-bold px-4 elevation-2"
            :loading="guardando"
            @click="guardarCambios()"
          >
            <v-icon left small>mdi-content-save</v-icon> Guardar Cambios
          </v-btn>

          <v-btn
            color="secondary"
            outlined
            class="text-capitalize rounded-pill"
            @click="cargarDatos()"
            :loading="cargando"
          >
            <v-icon left small>mdi-refresh</v-icon> Restaurar
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- BANNER INFORMATIVO DEL PLIEGO / VIGENCIA -->
    <v-card class="mb-4 pa-3 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap text-caption">
        <div class="d-flex align-center gap-3">
          <v-chip small color="success" text-color="white" class="font-weight-bold">
            <v-icon left x-small>mdi-check-circle</v-icon> PLIEGO VIGENTE OFICIAL
          </v-chip>
          <div>
            <span class="font-weight-bold text-subtitle-2">{{ pliegoActivo ? pliegoActivo.nombre : 'Pliego EMAPAP' }}</span>
            <span class="text-secondary ml-2">| Res: {{ pliegoActivo ? (pliegoActivo.resolucion_legal || 'AAPS Oficial') : 'AAPS' }}</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <span class="text-secondary">Total categorías activas:</span>
          <v-chip x-small color="primary" outlined class="font-weight-bold">{{ categorias.length }}</v-chip>
        </div>
      </div>
    </v-card>

    <!-- GRILLA COMPLETA DE CATEGORÍAS TIPO SPREADSHEET / FOXPRO -->
    <v-card elevation="2" rounded="lg" class="erp-card-elevated mb-6">
      <v-card-text class="pa-0">
        <div class="table-container">
          <table class="grid-table">
            <thead>
              <!-- FILA 1 DE ENCABEZADOS -->
              <tr>
                <th rowspan="2" class="th-cell th-fixed text-center" style="width: 50px;">Cat</th>
                <th rowspan="2" class="th-cell th-fixed text-left" style="min-width: 170px;">Descripción</th>
                <th rowspan="2" class="th-cell th-fixed text-center" style="min-width: 85px;">
                  Volúmen (m³)<br><span class="text-caption">Mínimo</span>
                </th>
                <th rowspan="2" class="th-cell th-fixed text-center" style="min-width: 85px;">
                  Bs/m³<br><span class="text-caption">Mínimo</span>
                </th>
                <th rowspan="2" class="th-cell th-fixed text-center th-highlight" style="min-width: 95px;">
                  Tarifa (Bs)<br><span class="text-caption font-weight-bold">Mínimo</span>
                </th>
                <!-- GRUPO PRINCIPAL DE LOS 11 RANGOS ESCALONADOS -->
                <th colspan="11" class="th-cell th-group text-center">
                  CARGO VARIABLE POR RANGO DE CONSUMO EN (Bs/m³)
                </th>
                <th rowspan="2" class="th-cell th-fixed text-center" style="min-width: 95px;">
                  Tarifa (Bs)<br><span class="text-caption">Alcantarillado</span>
                </th>
                <th rowspan="2" class="th-cell th-fixed text-center" style="min-width: 80px;">
                  Ley 1886<br><span class="text-caption">(Adulto M.)</span>
                </th>
                <th rowspan="2" class="th-cell th-fixed text-center" style="width: 45px;"></th>
              </tr>

              <!-- FILA 2 DE ENCABEZADOS (LOS 11 RANGOS) -->
              <tr class="th-sub-row">
                <th class="th-sub" style="min-width: 65px;">De 7-10</th>
                <th class="th-sub" style="min-width: 65px;">De 11-15</th>
                <th class="th-sub" style="min-width: 65px;">De 16-20</th>
                <th class="th-sub" style="min-width: 65px;">De 21-25</th>
                <th class="th-sub" style="min-width: 65px;">De 26-30</th>
                <th class="th-sub" style="min-width: 65px;">De 31-35</th>
                <th class="th-sub" style="min-width: 65px;">De 36-40</th>
                <th class="th-sub" style="min-width: 65px;">De 41-50</th>
                <th class="th-sub" style="min-width: 65px;">De 51-55</th>
                <th class="th-sub" style="min-width: 65px;">De 56-60</th>
                <th class="th-sub" style="min-width: 65px;">+ de 61</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="cargando">
                <td colspan="19" class="text-center py-8">
                  <v-progress-circular indeterminate color="primary" size="36"></v-progress-circular>
                  <div class="text-caption mt-2 text-secondary">Cargando categorías y escalas...</div>
                </td>
              </tr>

              <tr v-else-if="categorias.length === 0">
                <td colspan="19" class="text-center py-10">
                  <v-icon size="48" color="secondary" class="mb-2">mdi-database-alert-outline</v-icon>
                  <div class="text-subtitle-1 font-weight-bold">No hay categorías tarifarias registradas</div>
                  <div class="text-caption text-secondary mb-4">
                    La base de datos se encuentra limpia. Puede sincronizarlas desde el archivo FoxPro (categor.dbf) o ingresar una nueva fila manualmente.
                  </div>
                  <div class="d-flex justify-center gap-3">
                    <v-btn
                      color="primary"
                      outlined
                      small
                      to="/datos/migrador-respaldos"
                      class="text-capitalize rounded-pill"
                    >
                      <v-icon left small>mdi-database-import</v-icon> Ir al Migrador FoxPro
                    </v-btn>
                    <v-btn
                      color="primary"
                      small
                      @click="agregarFilaCategoria()"
                      class="text-capitalize rounded-pill"
                    >
                      <v-icon left small>mdi-plus</v-icon> Nueva Categoría
                    </v-btn>
                  </div>
                </td>
              </tr>

              <tr
                v-else
                v-for="(row, idx) in categorias"
                :key="row.codigo + '-' + idx"
                class="grid-row"
              >
                <!-- 1. Código Cat -->
                <td class="td-cell text-center font-weight-bold">
                  <input
                    type="text"
                    v-model="row.codigo"
                    class="cell-input text-center text-uppercase font-weight-bold primary--text"
                    maxlength="5"
                  />
                </td>

                <!-- 2. Descripción / Nombre -->
                <td class="td-cell">
                  <input
                    type="text"
                    v-model="row.nombre"
                    class="cell-input text-left text-uppercase font-weight-medium"
                  />
                </td>

                <!-- 3. Volumen Mínimo (m3) -->
                <td class="td-cell">
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="row.volumen_base"
                    @input="recalcularMinimo(row)"
                    class="cell-input text-right font-mono"
                  />
                </td>

                <!-- 4. Bs/m3 Mínimo -->
                <td class="td-cell">
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="row.tarifa_excedente_base"
                    @input="recalcularMinimo(row)"
                    class="cell-input text-right font-mono font-weight-bold"
                  />
                </td>

                <!-- 5. Tarifa Mínima Bs (Auto-calculada) -->
                <td class="td-cell text-right font-weight-bold font-mono td-highlight pr-3">
                  {{ Number(row.tarifa_minima || 0).toFixed(2) }}
                </td>

                <!-- 6 al 16. LOS 11 RANGOS DE CONSUMO ESCALONADO -->
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa1" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa2" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa3" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa4" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa5" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa6" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa7" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa8" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa9" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa10" class="cell-input text-right font-mono" /></td>
                <td class="td-cell"><input type="number" step="0.01" v-model.number="row.tarifa11" class="cell-input text-right font-mono" /></td>

                <!-- 17. Alcantarillado -->
                <td class="td-cell">
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="row.tarifa_alcantarillado"
                    class="cell-input text-right font-mono font-weight-bold"
                  />
                </td>

                <!-- 18. Ley 1886 -->
                <td class="td-cell text-center">
                  <v-checkbox
                    v-model="row.aplica_ley_1886"
                    dense
                    hide-details
                    color="success"
                    class="ma-0 pa-0 justify-center d-inline-flex"
                  ></v-checkbox>
                </td>

                <!-- 19. Borrar fila -->
                <td class="td-cell text-center">
                  <v-btn icon x-small color="error" @click="eliminarFila(idx)" title="Borrar categoría">
                    <v-icon small>mdi-delete-outline</v-icon>
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <!-- BARRA INFERIOR DE ACCIONES -->
      <v-card-actions class="pa-4 d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center gap-2">
          <v-btn
            color="primary"
            outlined
            class="text-capitalize rounded-pill"
            @click="agregarFilaCategoria()"
          >
            <v-icon left small>mdi-plus</v-icon> Nueva Categoría (Otro)
          </v-btn>

          <v-btn
            color="warning"
            text
            class="text-capitalize rounded-pill"
            @click="cargarDatos()"
          >
            <v-icon left small>mdi-restore</v-icon> Descartar Cambios
          </v-btn>
        </div>

        <div class="d-flex align-center gap-2">
          <span class="text-caption text-secondary mr-2">Recuerde presionar Guardar para aplicar a la facturación</span>
          <v-btn
            color="success"
            class="text-capitalize rounded-pill font-weight-bold px-6 elevation-2"
            :loading="guardando"
            @click="guardarCambios()"
          >
            <v-icon left small>mdi-content-save</v-icon> Guardar Cambios
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>

    <!-- HISTORIAL Y GESTIÓN DE VERSIONES (EXPANSION PANEL OPCIONAL) -->
    <v-expansion-panels class="mb-6" rounded="lg">
      <v-expansion-panel>
        <v-expansion-panel-header class="py-2 px-4 text-subtitle-2 font-weight-bold">
          <div>
            <v-icon small color="primary" class="mr-2">mdi-history</v-icon>
            Historial de Pliegos y Versiones Regulatorias (AAPS)
          </div>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <div class="d-flex align-center justify-space-between mb-3 pt-2">
            <span class="text-caption text-secondary">
              Permite registrar resoluciones tarifarias históricas o crear copias para nuevas vigencias futuras.
            </span>
            <v-btn small color="primary" class="text-capitalize rounded-pill" @click="modalNuevoPliego = true">
              <v-icon left small>mdi-plus</v-icon> Nuevo Pliego Regulatorio
            </v-btn>
          </div>

          <v-data-table
            :headers="columnasPliegos"
            :items="pliegos"
            dense
            hide-default-footer
            class="elevation-0"
          >
            <template v-slot:item.es_vigente="{ item }">
              <v-chip small :color="item.es_vigente ? 'success' : 'default'">
                {{ item.es_vigente ? 'VIGENTE OFICIAL' : 'Histórico' }}
              </v-chip>
            </template>

            <template v-slot:item.acciones="{ item }">
              <v-btn
                v-if="!item.es_vigente"
                small
                text
                color="primary"
                class="text-capitalize rounded-pill"
                @click="activarPliego(item)"
              >
                <v-icon left small>mdi-star</v-icon> Cargar como Vigente
              </v-btn>
            </template>
          </v-data-table>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>

    <!-- MODAL NUEVO PLIEGO HISTÓRICO -->
    <v-dialog v-model="modalNuevoPliego" max-width="480px" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-file-document-plus</v-icon> Nuevo Pliego Regulatorio
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formPliego.codigo" label="Código *" dense outlined></v-text-field>
          <v-text-field v-model="formPliego.nombre" label="Nombre del Pliego *" dense outlined></v-text-field>
          <v-text-field v-model="formPliego.resolucion_legal" label="Resolución AAPS" dense outlined></v-text-field>
          <v-text-field v-model="formPliego.fecha_inicio_vigencia" label="Fecha Inicio" type="date" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevoPliego = false">Cancelar</v-btn>
          <v-btn color="primary" @click="crearNuevoPliego" :loading="guardandoPliego">Crear</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarMsj }}
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ConfiguracionTarifasZonas',
  data() {
    return {
      cargando: false,
      guardando: false,
      pliegos: [],
      pliegoActivo: null,
      categorias: [],

      // Modal pliegos
      modalNuevoPliego: false,
      guardandoPliego: false,
      formPliego: {
        codigo: '',
        nombre: '',
        resolucion_legal: '',
        fecha_inicio_vigencia: new Date().toISOString().substr(0, 10)
      },

      columnasPliegos: [
        { text: 'Estado', value: 'es_vigente', width: '140px' },
        { text: 'Nombre del Pliego', value: 'nombre' },
        { text: 'Resolución AAPS', value: 'resolucion_legal' },
        { text: 'Inicio Vigencia', value: 'fecha_inicio_vigencia', width: '130px' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '180px', align: 'end' }
      ],

      snackbar: false,
      snackbarMsj: '',
      snackbarColor: 'success'
    };
  },
  created() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.cargando = true;
      try {
        // 1. Obtener los pliegos
        const resPaquetes = await axios.get('/api/comercial/paquetes-tarifarios');
        this.pliegos = resPaquetes.data.data || [];
        this.pliegoActivo = this.pliegos.find(p => p.es_vigente) || this.pliegos[0] || null;

        if (this.pliegoActivo) {
          // 2. Obtener la matriz de categorías del pliego activo
          const resMatriz = await axios.get(`/api/comercial/paquetes-tarifarios/${this.pliegoActivo.id}/matriz`);
          this.categorias = resMatriz.data.data || [];
        }
      } catch (err) {
        this.mostrarMensaje('Error al cargar datos tarifarios', 'error');
      } finally {
        this.cargando = false;
      }
    },

    recalcularMinimo(row) {
      const vol = parseFloat(row.volumen_base) || 0;
      const base = parseFloat(row.tarifa_excedente_base) || 0;
      row.tarifa_minima = parseFloat((vol * base).toFixed(2));
    },

    agregarFilaCategoria() {
      this.categorias.push({
        id: null,
        codigo: 'X',
        nombre: 'NUEVA CATEGORÍA',
        volumen_base: 6.00,
        tarifa_excedente_base: 2.50,
        tarifa_minima: 15.00,
        tarifa1: 2.50,
        tarifa2: 2.50,
        tarifa3: 2.50,
        tarifa4: 2.50,
        tarifa5: 2.50,
        tarifa6: 2.60,
        tarifa7: 2.60,
        tarifa8: 2.60,
        tarifa9: 2.70,
        tarifa10: 2.70,
        tarifa11: 2.70,
        tarifa_alcantarillado: 2.00,
        aplica_ley_1886: false,
        activo: true
      });
    },

    eliminarFila(idx) {
      if (this.categorias.length <= 1) {
        alert('Debe conservar al menos una categoría en el sistema.');
        return;
      }
      if (confirm(`¿Desea eliminar la fila de categoría ${this.categorias[idx].nombre}?`)) {
        this.categorias.splice(idx, 1);
      }
    },

    async guardarCambios() {
      if (!this.pliegoActivo) return;
      this.guardando = true;
      try {
        await axios.put(`/api/comercial/paquetes-tarifarios/${this.pliegoActivo.id}/matriz`, {
          categorias: this.categorias
        });
        this.mostrarMensaje('Estructura tarifaria guardada exitosamente.');
        await this.cargarDatos();
      } catch (err) {
        this.mostrarMensaje(err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Error al guardar tarifas', 'error');
      } finally {
        this.guardando = false;
      }
    },

    async activarPliego(item) {
      if (!confirm(`¿Desea activar "${item.nombre}" como el pliego tarifario oficial?`)) return;
      try {
        await axios.post(`/api/comercial/paquetes-tarifarios/${item.id}/activar`);
        this.mostrarMensaje(`Pliego ${item.nombre} activado.`);
        await this.cargarDatos();
      } catch (err) {
        this.mostrarMensaje('Error activando pliego', 'error');
      }
    },

    async crearNuevoPliego() {
      if (!this.formPliego.codigo || !this.formPliego.nombre) {
        alert('Código y nombre son obligatorios.');
        return;
      }
      this.guardandoPliego = true;
      try {
        await axios.post('/api/comercial/paquetes-tarifarios', this.formPliego);
        this.mostrarMensaje('Nuevo pliego registrado.');
        this.modalNuevoPliego = false;
        await this.cargarDatos();
      } catch (err) {
        this.mostrarMensaje('Error al crear pliego', 'error');
      } finally {
        this.guardandoPliego = false;
      }
    },

    mostrarMensaje(msj, color = 'success') {
      this.snackbarMsj = msj;
      this.snackbarColor = color;
      this.snackbar = true;
    }
  }
};
</script>

<style scoped>
.erp-card-elevated {
  border: 1px solid rgba(128, 128, 128, 0.15);
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.05);
}

.table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* GRILLA TIPO SPREADSHEET / FOXPRO */
.grid-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
  min-width: 1280px;
}

/* ENCABEZADOS */
.th-cell {
  padding: 8px 4px;
  border: 1px solid rgba(128, 128, 128, 0.2);
  font-weight: 700;
  vertical-align: middle;
  line-height: 1.2;
}

.th-group {
  background-color: rgba(var(--v-primary-base), 0.12);
  color: var(--v-primary-base);
  font-size: 0.8rem;
  letter-spacing: 0.5px;
  border-bottom: 2px solid var(--v-primary-base);
}

.th-sub-row .th-sub {
  background-color: rgba(var(--v-primary-base), 0.05);
  padding: 6px 2px;
  border: 1px solid rgba(128, 128, 128, 0.2);
  font-size: 0.75rem;
  font-weight: 600;
  text-align: center;
}

.th-highlight {
  background-color: rgba(var(--v-primary-base), 0.08);
  color: var(--v-primary-base);
}

/* CELDAS Y FILAS */
.td-cell {
  padding: 2px 2px;
  border: 1px solid rgba(128, 128, 128, 0.15);
  vertical-align: middle;
}

.grid-row:hover {
  background-color: rgba(var(--v-primary-base), 0.04);
}

.td-highlight {
  background-color: rgba(var(--v-primary-base), 0.06);
  color: var(--v-primary-base);
}

/* INPUTS FLAT TIPO EXCEL / FOXPRO DENTRO DE LAS CELDAS */
.cell-input {
  width: 100%;
  height: 32px;
  padding: 4px 6px;
  border: 1px solid transparent;
  border-radius: 4px;
  background: transparent;
  color: inherit;
  font-family: inherit;
  font-size: 0.83rem;
  transition: all 0.15s ease-in-out;
}

.cell-input:hover {
  border-color: rgba(128, 128, 128, 0.35);
  background-color: rgba(128, 128, 128, 0.05);
}

.cell-input:focus {
  outline: none;
  background-color: rgba(var(--v-primary-base), 0.1);
  border-color: var(--v-primary-base);
  box-shadow: 0 0 0 1px var(--v-primary-base);
}

.font-mono {
  font-family: 'Roboto Mono', Consolas, Monaco, monospace !important;
  font-size: 0.82rem;
}
</style>
