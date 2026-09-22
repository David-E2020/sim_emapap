<template>
  <div>
    <!-- CABECERA DE SECCIÓN -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-map-marker-multiple</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Zonas y Calles</h2>
            <span class="text-caption text-secondary">Zonificación territorial, sectores y catastro de vías para rutas de lectura</span>
          </div>
        </div>
        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" @click="abrirModalZona()" class="text-capitalize rounded-pill">
            <v-icon left small>mdi-plus</v-icon> Nueva Zona
          </v-btn>
          <v-btn color="secondary" outlined @click="cargarDatos()" :loading="cargando" class="text-capitalize rounded-pill">
            <v-icon left small>mdi-refresh</v-icon> Recargar
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- PANEL PRINCIPAL MAESTRO / DETALLE -->
    <v-row>
      <!-- COLUMNA IZQUIERDA: ZONAS -->
      <v-col cols="12" md="5">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between pb-2">
            <span class="text-subtitle-1 font-weight-bold">
              <v-icon small left color="primary">mdi-map-outline</v-icon> Sectores / Zonas ({{ zonas.length }})
            </span>
          </v-card-title>
          <v-divider></v-divider>

          <v-card-text class="pt-3">
            <div v-if="cargando" class="text-center py-8">
              <v-progress-circular indeterminate color="primary" size="36"></v-progress-circular>
              <div class="text-caption mt-2 text-secondary">Cargando sectores y zonas...</div>
            </div>

            <div v-else-if="zonas.length === 0" class="text-center py-8">
              <v-icon size="48" color="secondary" class="mb-2">mdi-map-marker-off-outline</v-icon>
              <div class="text-subtitle-2 font-weight-bold">No hay zonas registradas</div>
              <div class="text-caption text-secondary mb-4">
                La base de datos se encuentra limpia. Puede sincronizar los sectores/zonas desde FoxPro (zonas.dbf) o crear una manualmente.
              </div>
              <div class="d-flex justify-center gap-2">
                <v-btn small color="primary" outlined to="/datos/migrador-respaldos" class="text-capitalize rounded-pill">
                  <v-icon left small>mdi-database-import</v-icon> Migrador FoxPro
                </v-btn>
                <v-btn small color="primary" @click="abrirModalZona()" class="text-capitalize rounded-pill">
                  <v-icon left small>mdi-plus</v-icon> Nueva Zona
                </v-btn>
              </div>
            </div>

            <template v-else>
              <v-text-field
                v-model="filtroZona"
                placeholder="Buscar zona por nombre o código..."
                dense
                outlined
                rounded
                hide-details
                clearable
                prepend-inner-icon="mdi-magnify"
                class="mb-3"
              ></v-text-field>

              <v-list dense rounded class="pa-0">
                <v-list-item-group v-model="zonaSeleccionadaIndex" color="primary" mandatory>
                  <template v-for="(z, i) in zonasFiltradas">
                    <v-list-item :key="z.id" @click="seleccionarZona(z)" class="mb-1 py-1" :class="{'selected-zone-item': zonaActual && zonaActual.id === z.id}">
                      <v-list-item-avatar size="36" color="primary" class="my-1 white--text font-weight-bold text-caption">
                        {{ z.codigo }}
                      </v-list-item-avatar>

                      <v-list-item-content>
                        <v-list-item-title class="font-weight-bold">{{ z.nombre }}</v-list-item-title>
                        <v-list-item-subtitle class="text-caption">
                          <v-icon x-small color="grey">mdi-road</v-icon> {{ z.calles_count || 0 }} calles &bull;
                          <v-icon x-small color="grey">mdi-account-group</v-icon> {{ z.abonados_count || 0 }} abonados
                        </v-list-item-subtitle>
                      </v-list-item-content>

                      <v-list-item-action class="my-0">
                        <v-btn icon x-small @click.stop="editarZona(z)" title="Editar zona">
                          <v-icon small color="grey darken-1">mdi-pencil</v-icon>
                        </v-btn>
                      </v-list-item-action>
                    </v-list-item>
                    <v-divider v-if="i < zonasFiltradas.length - 1" :key="'div-' + z.id" class="my-1"></v-divider>
                  </template>
                </v-list-item-group>
              </v-list>
            </template>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- COLUMNA DERECHA: CALLES DE LA ZONA SELECCIONADA -->
      <v-col cols="12" md="7">
        <v-card elevation="2" rounded="lg" class="fill-height erp-card-elevated">
          <v-card-title class="d-flex align-center justify-space-between pb-2">
            <div>
              <span class="text-subtitle-1 font-weight-bold">
                <v-icon small left color="primary">mdi-road-variant</v-icon>
                Calles en: <span class="primary--text">{{ zonaActual ? zonaActual.nombre : 'Seleccione una zona' }}</span>
              </span>
              <div class="text-caption text-secondary" v-if="zonaActual">
                Código: <strong>{{ zonaActual.codigo }}</strong> | Total calles: <strong>{{ calles.length }}</strong>
              </div>
            </div>
            <v-btn
              color="primary"
              small
              class="text-capitalize rounded-pill"
              :disabled="!zonaActual"
              @click="abrirModalCalle()"
            >
              <v-icon left small>mdi-plus</v-icon> Nueva Calle
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>

          <v-card-text class="pt-3">
            <v-text-field
              v-model="filtroCalle"
              placeholder="Buscar calle o avenida..."
              dense
              outlined
              rounded
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
              class="mb-3"
            ></v-text-field>

            <v-data-table
              :headers="columnasCalles"
              :items="callesFiltradas"
              :loading="cargandoCalles"
              dense
              class="elevation-0 border-table"
              no-data-text="No hay calles registradas en esta zona."
            >
              <template v-slot:item.nombre="{ item }">
                <span class="font-weight-medium">{{ item.nombre }}</span>
              </template>
              <template v-slot:item.abonados_count="{ item }">
                <v-chip x-small color="primary" outlined class="font-weight-bold">
                  {{ item.abonados_count || 0 }} abonados
                </v-chip>
              </template>
              <template v-slot:item.acciones="{ item }">
                <v-btn icon x-small color="primary" @click="editarCalle(item)" title="Editar calle">
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- DIÁLOGO ZONA (CREAR / EDITAR) -->
    <v-dialog v-model="modalZona" max-width="480px" persistent>
      <v-card rounded="lg">
        <v-card-title class="headline font-weight-bold primary white--text">
          <v-icon left color="white">mdi-map-marker</v-icon>
          {{ formZona.id ? 'Editar Zona' : 'Nueva Zona' }}
        </v-card-title>
        <v-card-text class="pt-5">
          <v-form ref="formZonaRef" v-model="formZonaValido">
            <v-text-field
              v-model="formZona.codigo"
              label="Código de Zona *"
              placeholder="Ej. Z1, Z2, CENTRO"
              outlined
              dense
              :rules="[v => !!v || 'El código es requerido']"
            ></v-text-field>
            <v-text-field
              v-model="formZona.nombre"
              label="Nombre de la Zona *"
              placeholder="Ej. ZONA CENTRAL, ZONA ALTO PATACAMAYA"
              outlined
              dense
              :rules="[v => !!v || 'El nombre es requerido']"
            ></v-text-field>
            <v-textarea
              v-model="formZona.descripcion"
              label="Descripción o Referencia Geográfica"
              rows="3"
              outlined
              dense
            ></v-textarea>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn text @click="modalZona = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardandoZona" @click="guardarZona" class="rounded-pill px-4">
            Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO CALLE (CREAR / EDITAR) -->
    <v-dialog v-model="modalCalle" max-width="480px" persistent>
      <v-card rounded="lg">
        <v-card-title class="headline font-weight-bold primary white--text">
          <v-icon left color="white">mdi-road</v-icon>
          {{ formCalle.id ? 'Editar Calle' : 'Nueva Calle' }}
        </v-card-title>
        <v-card-text class="pt-5">
          <v-form ref="formCalleRef" v-model="formCalleValido">
            <v-select
              v-model="formCalle.id_zona"
              :items="zonas"
              item-text="nombre"
              item-value="id"
              label="Zona Pertenciente *"
              outlined
              dense
              :rules="[v => !!v || 'Debe seleccionar una zona']"
            ></v-select>
            <v-text-field
              v-model="formCalle.nombre"
              label="Nombre de Calle / Avenida *"
              placeholder="Ej. AV. PANAMERICANA, CALLE SUCRE"
              outlined
              dense
              :rules="[v => !!v || 'El nombre de calle es requerido']"
            ></v-text-field>
            <v-textarea
              v-model="formCalle.referencia"
              label="Referencia o Tramo"
              rows="2"
              outlined
              dense
            ></v-textarea>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn text @click="modalCalle = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardandoCalle" @click="guardarCalle" class="rounded-pill px-4">
            Guardar
          </v-btn>
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
  name: 'ZonasCalles',
  data() {
    return {
      cargando: false,
      cargandoCalles: false,
      zonas: [],
      calles: [],
      zonaActual: null,
      zonaSeleccionadaIndex: 0,
      filtroZona: '',
      filtroCalle: '',

      columnasCalles: [
        { text: 'Nombre de Calle / Avenida', value: 'nombre' },
        { text: 'Referencia', value: 'referencia' },
        { text: 'Abonados', value: 'abonados_count', align: 'center', width: '130px' },
        { text: 'Acciones', value: 'acciones', align: 'center', sortable: false, width: '90px' }
      ],

      // Modales y formularios
      modalZona: false,
      formZonaValido: false,
      guardandoZona: false,
      formZona: {
        id: null,
        codigo: '',
        nombre: '',
        descripcion: ''
      },

      modalCalle: false,
      formCalleValido: false,
      guardandoCalle: false,
      formCalle: {
        id: null,
        id_zona: null,
        nombre: '',
        referencia: ''
      },

      snackbar: false,
      snackbarMsj: '',
      snackbarColor: 'success'
    };
  },
  computed: {
    zonasFiltradas() {
      if (!this.filtroZona) return this.zonas;
      const q = this.filtroZona.toLowerCase();
      return this.zonas.filter(z =>
        (z.nombre && z.nombre.toLowerCase().includes(q)) ||
        (z.codigo && z.codigo.toLowerCase().includes(q))
      );
    },
    callesFiltradas() {
      if (!this.filtroCalle) return this.calles;
      const q = this.filtroCalle.toLowerCase();
      return this.calles.filter(c =>
        (c.nombre && c.nombre.toLowerCase().includes(q)) ||
        (c.referencia && c.referencia.toLowerCase().includes(q))
      );
    }
  },
  created() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.cargando = true;
      try {
        const res = await axios.get('/api/comercial/zonas');
        this.zonas = res.data.data || [];
        if (this.zonas.length > 0) {
          if (!this.zonaActual) {
            this.seleccionarZona(this.zonas[0]);
          } else {
            const reselect = this.zonas.find(z => z.id === this.zonaActual.id);
            this.seleccionarZona(reselect || this.zonas[0]);
          }
        } else {
          this.zonaActual = null;
          this.calles = [];
        }
      } catch (err) {
        this.mostrarMensaje('Error al cargar zonas: ' + (err.response?.data?.message || err.message), 'error');
      } finally {
        this.cargando = false;
      }
    },
    async seleccionarZona(zona) {
      this.zonaActual = zona;
      this.cargandoCalles = true;
      try {
        const res = await axios.get(`/api/comercial/calles?id_zona=${zona.id}`);
        this.calles = res.data.data || [];
      } catch (err) {
        this.mostrarMensaje('Error al cargar calles de la zona', 'error');
      } finally {
        this.cargandoCalles = false;
      }
    },
    abrirModalZona() {
      this.formZona = { id: null, codigo: '', nombre: '', descripcion: '' };
      this.modalZona = true;
    },
    editarZona(z) {
      this.formZona = { id: z.id, codigo: z.codigo, nombre: z.nombre, descripcion: z.descripcion || '' };
      this.modalZona = true;
    },
    async guardarZona() {
      if (!this.$refs.formZonaRef.validate()) return;
      this.guardandoZona = true;
      try {
        if (this.formZona.id) {
          await axios.put(`/api/comercial/zonas/${this.formZona.id}`, this.formZona);
          this.mostrarMensaje('Zona actualizada correctamente');
        } else {
          await axios.post('/api/comercial/zonas', this.formZona);
          this.mostrarMensaje('Zona creada correctamente');
        }
        this.modalZona = false;
        await this.cargarDatos();
      } catch (err) {
        this.mostrarMensaje(err.response?.data?.message || 'Error guardando zona', 'error');
      } finally {
        this.guardandoZona = false;
      }
    },
    abrirModalCalle() {
      this.formCalle = {
        id: null,
        id_zona: this.zonaActual ? this.zonaActual.id : null,
        nombre: '',
        referencia: ''
      };
      this.modalCalle = true;
    },
    editarCalle(c) {
      this.formCalle = {
        id: c.id,
        id_zona: c.id_zona,
        nombre: c.nombre,
        referencia: c.referencia || ''
      };
      this.modalCalle = true;
    },
    async guardarCalle() {
      if (!this.$refs.formCalleRef.validate()) return;
      this.guardandoCalle = true;
      try {
        if (this.formCalle.id) {
          await axios.put(`/api/comercial/calles/${this.formCalle.id}`, this.formCalle);
          this.mostrarMensaje('Calle actualizada correctamente');
        } else {
          await axios.post('/api/comercial/calles', this.formCalle);
          this.mostrarMensaje('Calle creada correctamente');
        }
        this.modalCalle = false;
        if (this.zonaActual) {
          await this.seleccionarZona(this.zonaActual);
        }
      } catch (err) {
        this.mostrarMensaje(err.response?.data?.message || 'Error guardando calle', 'error');
      } finally {
        this.guardandoCalle = false;
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
.selected-zone-item {
  background-color: rgba(var(--v-primary-base), 0.12) !important;
  border-left: 4px solid var(--v-primary-base) !important;
}
.border-table {
  border: 1px solid rgba(128, 128, 128, 0.15);
  border-radius: 8px;
}
</style>
