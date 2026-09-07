<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-map-marker-radius-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Tarifas y Zonificación</h2>
            <span class="text-caption text-secondary">Catálogo oficial de categorías tarifarias de agua potable, alcantarillado, zonas y calles</span>
          </div>
        </div>
      </div>
    </v-card>

    <v-tabs v-model="tabActual" color="primary" class="mb-4">
      <v-tab><v-icon left small>mdi-cash</v-icon> Categorías Tarifarias</v-tab>
      <v-tab><v-icon left small>mdi-map</v-icon> Zonas y Calles de Patacamaya</v-tab>
    </v-tabs>

    <v-tabs-items v-model="tabActual">
      <!-- PESTAÑA 1: TARIFARIO -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated">
          <v-data-table :headers="columnasTarifas" :items="categorias" :loading="cargandoTarifas" hide-default-footer>
            <template v-slot:item.codigo="{ item }">
              <v-chip color="primary" outlined small class="font-weight-bold">{{ item.codigo }}</v-chip>
            </template>
            <template v-slot:item.volumen_base="{ item }">
              <span>{{ item.volumen_base }} m³</span>
            </template>
            <template v-slot:item.tarifa_minima="{ item }">
              <span class="font-weight-bold">Bs {{ parseFloat(item.tarifa_minima).toFixed(2) }}</span>
            </template>
            <template v-slot:item.tarifa_excedente_base="{ item }">
              <span>Bs {{ parseFloat(item.tarifa_excedente_base).toFixed(2) }} / m³</span>
            </template>
            <template v-slot:item.tarifa_alcantarillado="{ item }">
              <span>Bs {{ parseFloat(item.tarifa_alcantarillado).toFixed(2) }}</span>
            </template>
            <template v-slot:item.aplica_ley_1886="{ item }">
              <v-chip x-small :color="item.aplica_ley_1886 ? 'success' : 'grey'" text-color="white">
                {{ item.aplica_ley_1886 ? '20% Descto' : 'No Aplica' }}
              </v-chip>
            </template>
            <template v-slot:item.acciones="{ item }">
              <v-btn icon small color="primary" @click="editarTarifa(item)">
                <v-icon small>mdi-pencil</v-icon>
              </v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- PESTAÑA 2: ZONAS Y CALLES -->
      <v-tab-item>
        <v-row dense>
          <v-col cols="12" md="5">
            <v-card rounded="lg" class="erp-card-elevated">
              <v-card-title class="py-2 px-4 text-subtitle-1 font-weight-bold grey lighten-4">
                Zonas Registradas ({{ zonas.length }})
              </v-card-title>
              <v-list dense class="py-0">
                <v-list-item-group v-model="zonaSeleccionadaIndex" color="primary">
                  <v-list-item v-for="z in zonas" :key="z.id" @click="cargarCalles(z)">
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-bold">{{ z.nombre }}</v-list-item-title>
                      <v-list-item-subtitle>{{ z.calles_count || 0 }} calles | {{ z.abonados_count || 0 }} abonados</v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-icon small>mdi-chevron-right</v-icon>
                    </v-list-item-action>
                  </v-list-item>
                </v-list-item-group>
              </v-list>
            </v-card>
          </v-col>

          <v-col cols="12" md="7">
            <v-card rounded="lg" class="erp-card-elevated" v-if="zonaActiva">
              <v-card-title class="py-2 px-4 d-flex justify-space-between align-center grey lighten-4">
                <span class="text-subtitle-1 font-weight-bold">Calles en {{ zonaActiva.nombre }}</span>
                <v-btn small color="primary" class="rounded-pill" @click="modalNuevaCalle = true">
                  <v-icon left small>mdi-plus</v-icon> Agregar Calle
                </v-btn>
              </v-card-title>

              <v-simple-table dense>
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th>Nombre de la Calle / Avenida</th>
                      <th class="text-right">Abonados</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="calle in calles" :key="calle.id">
                      <td>{{ calle.nombre }}</td>
                      <td class="text-right font-weight-bold">{{ calle.abonados_count || 0 }}</td>
                    </tr>
                    <tr v-if="calles.length === 0">
                      <td colspan="2" class="text-center text-secondary py-3">No hay calles registradas en esta zona</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>
    </v-tabs-items>

    <!-- MODAL EDITAR TARIFA -->
    <v-dialog v-model="modalEditarTarifa" max-width="450">
      <v-card rounded="lg" v-if="tarifaEdit">
        <v-card-title class="primary white--text py-3">Editar Categoría: {{ tarifaEdit.nombre }}</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model.number="tarifaEdit.volumen_base" label="Volumen Base (m³)" type="number" dense outlined></v-text-field>
          <v-text-field v-model.number="tarifaEdit.tarifa_minima" label="Tarifa Mínima Base (Bs)" type="number" dense outlined></v-text-field>
          <v-text-field v-model.number="tarifaEdit.tarifa_excedente_base" label="Tarifa Excedente Base (Bs/m³)" type="number" dense outlined></v-text-field>
          <v-text-field v-model.number="tarifaEdit.tarifa_alcantarillado" label="Tarifa Fija Alcantarillado (Bs)" type="number" dense outlined></v-text-field>
          <v-checkbox v-model="tarifaEdit.aplica_ley_1886" label="Aplica 20% Descuento Ley 1886 (Tercera Edad)" dense hide-details></v-checkbox>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalEditarTarifa = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="guardandoTarifa" @click="guardarTarifa">Guardar Cambios</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL NUEVA CALLE -->
    <v-dialog v-model="modalNuevaCalle" max-width="400">
      <v-card rounded="lg" v-if="zonaActiva">
        <v-card-title class="primary white--text py-3">Nueva Calle en {{ zonaActiva.nombre }}</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="nuevaCalleNombre" label="Nombre de Calle / Avenida *" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="pa-3">
          <v-spacer></v-spacer>
          <v-btn text @click="modalNuevaCalle = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarNuevaCalle">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ConfiguracionTarifasZonas',
  data() {
    return {
      tabActual: 0,
      cargandoTarifas: false,
      categorias: [],
      zonas: [],
      calles: [],
      zonaSeleccionadaIndex: 0,
      zonaActiva: null,
      modalEditarTarifa: false,
      modalNuevaCalle: false,
      guardandoTarifa: false,
      tarifaEdit: null,
      nuevaCalleNombre: '',
      columnasTarifas: [
        { text: 'Código', value: 'codigo', width: '80px' },
        { text: 'Categoría', value: 'nombre' },
        { text: 'Base m³', value: 'volumen_base', width: '100px' },
        { text: 'Mínimo Bs', value: 'tarifa_minima', width: '120px' },
        { text: 'Excedente m³', value: 'tarifa_excedente_base', width: '140px' },
        { text: 'Alcantarillado', value: 'tarifa_alcantarillado', width: '130px' },
        { text: 'Ley 1886', value: 'aplica_ley_1886', width: '120px' },
        { text: 'Editar', value: 'acciones', sortable: false, width: '80px', align: 'center' },
      ],
    };
  },
  mounted() {
    this.cargarTarifas();
    this.cargarZonas();
  },
  methods: {
    async cargarTarifas() {
      this.cargandoTarifas = true;
      try {
        const res = await axios.get('/api/comercial/tarifas');
        this.categorias = res.data.data || [];
      } catch (e) {
        console.error('Error cargando tarifas:', e);
      } finally {
        this.cargandoTarifas = false;
      }
    },
    async cargarZonas() {
      try {
        const res = await axios.get('/api/comercial/zonas');
        this.zonas = res.data.data || [];
        if (this.zonas.length > 0) {
          this.cargarCalles(this.zonas[0]);
        }
      } catch (e) {
        console.error('Error cargando zonas:', e);
      }
    },
    async cargarCalles(zona) {
      this.zonaActiva = zona;
      try {
        const res = await axios.get('/api/comercial/calles', { params: { id_zona: zona.id } });
        this.calles = res.data.data || [];
      } catch (e) {
        console.error('Error cargando calles:', e);
      }
    },
    editarTarifa(item) {
      this.tarifaEdit = { ...item };
      this.modalEditarTarifa = true;
    },
    async guardarTarifa() {
      this.guardandoTarifa = true;
      try {
        await axios.put(`/api/comercial/tarifas/${this.tarifaEdit.id}`, this.tarifaEdit);
        this.modalEditarTarifa = false;
        this.cargarTarifas();
        alert('Tarifa actualizada correctamente.');
      } catch (e) {
        alert('Error al actualizar tarifa.');
      } finally {
        this.guardandoTarifa = false;
      }
    },
    async guardarNuevaCalle() {
      if (!this.nuevaCalleNombre) return;
      try {
        await axios.post('/api/comercial/calles', {
          id_zona: this.zonaActiva.id,
          nombre: this.nuevaCalleNombre,
        });
        this.modalNuevaCalle = false;
        this.nuevaCalleNombre = '';
        this.cargarCalles(this.zonaActiva);
      } catch (e) {
        alert('Error al registrar calle.');
      }
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
