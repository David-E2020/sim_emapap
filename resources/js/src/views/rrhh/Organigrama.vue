<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-sitemap-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Estructura Organizacional</h2>
            <span class="text-caption text-secondary">Organigrama institucional, escalas salariales, puestos y regionales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="primary" outlined class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalPuesto()">
            <v-icon left small>mdi-briefcase-plus</v-icon> + Nuevo Puesto
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalUnidad()">
            <v-icon left small>mdi-domain-plus</v-icon> + Nueva Unidad
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TABS DE NAVEGACIÓN -->
    <v-card rounded="lg" class="mb-5 erp-card-elevated">
      <v-tabs v-model="tabActual" background-color="transparent" color="primary" grow>
        <v-tab><v-icon left small>mdi-sitemap</v-icon> Árbol de Organigrama y Puestos</v-tab>
        <v-tab><v-icon left small>mdi-cash-multiple</v-icon> Escalas Salariales y Niveles</v-tab>
        <v-tab><v-icon left small>mdi-map-marker-radius</v-icon> Regionales y Gestiones</v-tab>
      </v-tabs>
    </v-card>

    <v-tabs-items v-model="tabActual">
      <!-- TAB 1: ÁRBOL DE ORGANIGRAMA -->
      <v-tab-item>
        <v-row>
          <v-col cols="12" md="5">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Árbol de Unidades</span>
                <v-btn icon small @click="cargarOrganigrama()"><v-icon small>mdi-refresh</v-icon></v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>

              <v-treeview
                :items="unidades"
                item-key="id"
                item-text="nombre"
                item-children="dependencias"
                activatable
                hoverable
                color="primary"
                open-all
                @update:active="onSelectUnidad"
              >
                <template v-slot:prepend="{ item, open }">
                  <v-icon :color="item.es_unidad_recursos_humanos ? 'purple' : 'primary'">
                    {{ item.dependencias && item.dependencias.length > 0 ? (open ? 'mdi-domain' : 'mdi-domain') : 'mdi-office-building' }}
                  </v-icon>
                </template>
                <template v-slot:label="{ item }">
                  <span class="font-weight-medium">{{ item.nombre }}</span>
                  <v-chip x-small v-if="item.sigla" label color="grey lighten-3" class="ml-2 font-weight-bold">{{ item.sigla }}</v-chip>
                </template>
              </v-treeview>
            </v-card>
          </v-col>

          <v-col cols="12" md="7">
            <v-card rounded="lg" class="erp-card-elevated pa-4" v-if="unidadSeleccionada">
              <div class="d-flex align-center justify-space-between flex-wrap mb-2">
                <div>
                  <div class="d-flex align-center flex-wrap">
                    <h3 class="text-h6 font-weight-bold mb-0 text-primary mr-2">{{ unidadSeleccionada.nombre }}</h3>
                    <v-chip small v-if="unidadSeleccionada.sigla" label color="primary" class="font-weight-bold text-white mr-2">
                      {{ unidadSeleccionada.sigla }}
                    </v-chip>
                    <v-chip x-small color="grey lighten-3" class="text-caption">
                      {{ (unidadSeleccionada.puestos || []).length }} Puestos
                    </v-chip>
                  </div>
                  <span class="text-caption text-secondary">
                    Depende de: <strong>{{ obtenerNombrePadre(unidadSeleccionada.padreId) }}</strong>
                  </span>
                </div>

                <div class="d-flex align-center gap-1 my-1">
                  <v-btn small outlined color="primary" class="rounded-pill mr-1 text-capitalize" @click="editarUnidad(unidadSeleccionada)">
                    <v-icon left x-small>mdi-pencil</v-icon> Editar
                  </v-btn>
                  <v-btn small outlined color="error" class="rounded-pill mr-1 text-capitalize" @click="confirmarEliminarUnidad(unidadSeleccionada)">
                    <v-icon left x-small>mdi-delete</v-icon> Eliminar
                  </v-btn>
                  <v-btn small color="primary" class="rounded-pill text-capitalize font-weight-bold" @click="crearSubunidad(unidadSeleccionada)">
                    <v-icon left x-small>mdi-plus</v-icon> + Subunidad
                  </v-btn>
                </div>
              </div>
              <v-divider class="my-3"></v-divider>

              <div class="d-flex align-center justify-space-between mb-3 flex-wrap">
                <h4 class="text-subtitle-2 font-weight-bold mb-0 d-flex align-center">
                  <v-icon small class="mr-1 text-primary">mdi-account-tie</v-icon> Puestos de Trabajo e Ítems
                </h4>
                <v-btn small outlined color="primary" class="rounded-pill text-capitalize my-1" @click="abrirModalPuesto(unidadSeleccionada.id)">
                  <v-icon left x-small>mdi-briefcase-plus</v-icon> + Nuevo Puesto en esta Unidad
                </v-btn>
              </div>

              <v-simple-table dense class="erp-table">
                <thead>
                  <tr>
                    <th>Puesto / Cargo</th>
                    <th>Tipo</th>
                    <th>Funcionario Asignado</th>
                    <th>Ítem</th>
                    <th class="text-center" style="width: 120px;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in unidadSeleccionada.puestos || []" :key="p.id">
                    <td class="font-weight-medium">{{ p.nombre }}</td>
                    <td><v-chip x-small label color="blue lighten-5" text-color="primary">{{ p.tipo_puesto }}</v-chip></td>
                    <td>
                      <div v-if="p.asignaciones && p.asignaciones.length > 0 && p.asignaciones[0].persona" class="d-flex align-center">
                        <v-avatar size="24" color="primary" class="white--text mr-2 text-caption font-weight-bold">
                          {{ (p.asignaciones[0].persona.nombres || 'F').charAt(0) }}
                        </v-avatar>
                        <span class="text-caption font-weight-bold mr-2">{{ p.asignaciones[0].persona.nombres }} {{ p.asignaciones[0].persona.primer_apellido || '' }}</span>
                        <v-btn icon x-small color="error" title="Desvincular / Concluir funciones" @click="abrirModalDesvincular(p, p.asignaciones[0])">
                          <v-icon x-small>mdi-account-minus</v-icon>
                        </v-btn>
                      </div>
                      <div v-else class="d-flex align-center">
                        <v-chip x-small color="grey lighten-3" text-color="grey" class="mr-1">VACANTE</v-chip>
                        <v-btn text x-small color="primary" class="px-1 text-capitalize font-weight-bold" @click="abrirModalAsignar(p)">
                          <v-icon x-small left>mdi-account-plus</v-icon> Asignar
                        </v-btn>
                      </div>
                    </td>
                    <td>
                      <span v-if="p.asignaciones && p.asignaciones.length > 0" class="text-caption font-weight-bold">
                        #{{ p.asignaciones[0].nro_item }}
                      </span>
                      <span v-else class="text-caption text-secondary">-</span>
                    </td>
                    <td class="text-center">
                      <v-btn icon small color="teal" title="Ver historial de ocupantes" @click="abrirHistorialPuesto(p)">
                        <v-icon small>mdi-history</v-icon>
                      </v-btn>
                      <v-btn icon small color="primary" title="Editar puesto" @click="editarPuesto(p)">
                        <v-icon small>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn icon small color="error" title="Eliminar puesto" @click="confirmarEliminarPuesto(p)">
                        <v-icon small>mdi-delete</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                  <tr v-if="!unidadSeleccionada.puestos || unidadSeleccionada.puestos.length === 0">
                    <td colspan="5" class="text-center text-caption py-4 text-secondary">
                      No hay puestos registrados en esta unidad. Presiona "+ Nuevo Puesto".
                    </td>
                  </tr>
                </tbody>
              </v-simple-table>
            </v-card>
            <v-card v-else rounded="lg" class="erp-card-elevated pa-6 text-center">
              <v-icon size="48" color="grey lighten-1">mdi-cursor-default-click-outline</v-icon>
              <p class="text-caption text-secondary mt-2">Selecciona una unidad en el árbol de la izquierda para ver y gestionar sus puestos.</p>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>

      <!-- TAB 2: ESCALAS SALARIALES -->
      <v-tab-item>
        <v-card rounded="lg" class="erp-card-elevated pa-4">
          <div class="d-flex align-center justify-space-between mb-3">
            <div>
              <span class="font-weight-bold text-subtitle-1">Escalas Salariales Institucionales</span>
              <p class="text-caption text-secondary mb-0">Tabulador salarial oficial por puesto y jerarquía</p>
            </div>
            <v-btn color="primary" small class="rounded-pill text-capitalize" @click="dialogEscala = true">
              <v-icon left small>mdi-plus</v-icon> + Nueva Escala
            </v-btn>
          </div>
          <v-divider class="mb-4"></v-divider>

          <v-data-table :headers="headersEscalas" :items="escalasSalariales" class="erp-table" dense>
            <template v-slot:item.salario_mensual="{ item }">
              <span class="font-weight-bold text-success">Bs. {{ Number(item.salario_mensual).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
            </template>
            <template v-slot:item._estado="{ item }">
              <v-chip x-small color="green lighten-5" text-color="green" label>{{ item._estado }}</v-chip>
            </template>
          </v-data-table>
        </v-card>
      </v-tab-item>

      <!-- TAB 3: REGIONALES Y GESTIONES -->
      <v-tab-item>
        <v-row>
          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Regionales Institucionales</span>
                <v-btn color="primary" small outlined class="rounded-pill text-capitalize" @click="dialogRegional = true">
                  <v-icon left small>mdi-plus</v-icon> + Regional
                </v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>
              <v-simple-table dense class="erp-table">
                <thead><tr><th>Regional</th><th>Sigla</th><th>Estado</th></tr></thead>
                <tbody>
                  <tr v-for="r in regionales" :key="r.id">
                    <td class="font-weight-medium">{{ r.nombre }}</td>
                    <td><v-chip x-small label>{{ r.sigla || 'N/A' }}</v-chip></td>
                    <td><v-chip x-small color="green lighten-5" text-color="green" label>{{ r._estado }}</v-chip></td>
                  </tr>
                  <tr v-if="regionales.length === 0"><td colspan="3" class="text-center text-caption py-3 text-secondary">Sin regionales registradas</td></tr>
                </tbody>
              </v-simple-table>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card rounded="lg" class="erp-card-elevated pa-4">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="font-weight-bold text-subtitle-1">Gestiones Anuales</span>
                <v-btn color="primary" small outlined class="rounded-pill text-capitalize" @click="dialogGestion = true">
                  <v-icon left small>mdi-plus</v-icon> + Gestión
                </v-btn>
              </div>
              <v-divider class="mb-3"></v-divider>
              <v-simple-table dense class="erp-table">
                <thead><tr><th>Gestión</th><th>Descripción</th><th>Estado</th></tr></thead>
                <tbody>
                  <tr v-for="g in gestiones" :key="g.id">
                    <td class="font-weight-bold text-primary">{{ g.anio }}</td>
                    <td>{{ g.nombre || g.descripcion || 'Gestión Anual' }}</td>
                    <td><v-chip x-small color="green lighten-5" text-color="green" label>{{ g._estado }}</v-chip></td>
                  </tr>
                  <tr v-if="gestiones.length === 0"><td colspan="3" class="text-center text-caption py-3 text-secondary">Sin gestiones registradas</td></tr>
                </tbody>
              </v-simple-table>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>
    </v-tabs-items>

    <!-- DIALOG UNIDAD (CREAR / EDITAR) -->
    <v-dialog v-model="dialogUnidad" max-width="500px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">{{ esModoEdicionUnidad ? 'mdi-pencil' : 'mdi-domain-plus' }}</v-icon>
          {{ esModoEdicionUnidad ? 'Editar Unidad Organizacional' : 'Nueva Unidad Organizacional' }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formUnidad.nombre" label="Nombre de Unidad *" dense outlined class="mb-2" autofocus></v-text-field>
          <v-text-field v-model="formUnidad.sigla" label="Sigla (Ej: DAF)" dense outlined class="mb-2"></v-text-field>
          <v-select
            v-model="formUnidad.padreId"
            :items="unidadesPlanasParaPadre"
            item-text="nombre"
            item-value="id"
            label="Depende de (Unidad Padre)"
            dense
            outlined
            clearable
            hint="Dejar vacío si es la unidad raíz (MAE)"
            persistent-hint
          ></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogUnidad = false">Cancelar</v-btn>
          <v-btn color="primary" class="font-weight-bold" :loading="guardandoUnidad" @click="guardarUnidad()">
            {{ esModoEdicionUnidad ? 'Actualizar Unidad' : 'Guardar' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG PUESTO (CREAR / EDITAR) -->
    <v-dialog v-model="dialogPuesto" max-width="500px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">{{ esModoEdicionPuesto ? 'mdi-pencil' : 'mdi-briefcase-plus' }}</v-icon>
          {{ esModoEdicionPuesto ? 'Editar Puesto de Trabajo' : 'Nuevo Puesto de Trabajo' }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formPuesto.nombre" label="Nombre del Puesto / Cargo *" dense outlined class="mb-2" autofocus></v-text-field>
          <v-select v-model="formPuesto.id_unidad_organizacional" :items="unidadesPlanas" item-text="nombre" item-value="id" label="Unidad Organizacional *" dense outlined class="mb-2"></v-select>
          <v-select v-model="formPuesto.tipo_puesto" :items="['PLANTA', 'EVENTUAL', 'CONSULTOR']" label="Tipo de Contrato *" dense outlined class="mb-2"></v-select>
          <v-select
            v-model="formPuesto.id_escala_salarial"
            :items="escalasSalariales"
            item-text="nombre"
            item-value="id"
            label="Escala Salarial (Opcional)"
            dense
            outlined
            clearable
          ></v-select>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogPuesto = false">Cancelar</v-btn>
          <v-btn color="primary" class="font-weight-bold" :loading="guardandoPuesto" @click="guardarPuesto()">
            {{ esModoEdicionPuesto ? 'Actualizar Puesto' : 'Crear Puesto' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG ASIGNAR FUNCIONARIO A PUESTO -->
    <v-dialog v-model="dialogAsignar" max-width="600px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">
          <v-icon left color="white">mdi-account-plus</v-icon>
          Asignar Funcionario a Puesto
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3 d-flex align-center justify-space-between" v-if="puestoSeleccionadoParaAsignar">
            <div>
              <span class="text-caption text-secondary">Cargo a Asignar:</span>
              <div class="font-weight-bold text-primary text-subtitle-1">{{ puestoSeleccionadoParaAsignar.nombre }}</div>
              <div class="text-caption text-secondary" v-if="unidadSeleccionada">
                Unidad: {{ unidadSeleccionada.nombre }} • Modalidad: {{ puestoSeleccionadoParaAsignar.tipo_puesto }}
              </div>
            </div>
            <v-chip small color="blue lighten-5" text-color="primary" class="font-weight-bold">
              {{ puestoSeleccionadoParaAsignar.tipo_puesto || 'PLANTA' }}
            </v-chip>
          </div>

          <v-divider class="mb-3"></v-divider>

          <!-- FILTRO DISPONIBILIDAD -->
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold grey--text text--darken-2">Búsqueda de Personal:</span>
            <v-switch
              v-model="filtroSoloDisponibles"
              label="Mostrar sólo personal disponible (sin cargo activo)"
              dense
              hide-details
              class="mt-0 pt-0 text-caption"
            ></v-switch>
          </div>

          <v-autocomplete
            v-model="formAsignar.id_persona"
            :items="listaPersonalFiltrada"
            item-text="nombre_completo"
            item-value="id"
            label="Seleccionar Funcionario *"
            dense
            outlined
            class="mb-2"
            no-data-text="No hay personal disponible bajo este criterio"
            prepend-inner-icon="mdi-account-search"
          >
            <template v-slot:item="{ item }">
              <v-list-item-content>
                <v-list-item-title class="font-weight-bold">
                  {{ item.nombre_completo }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-caption">
                  C.I.: {{ item.nro_documento }}
                </v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action>
                <v-chip x-small v-if="item.puesto_actual" color="amber lighten-4" text-color="amber darken-4" class="font-weight-bold">
                  Ocupa: {{ item.puesto_actual.cargo }}
                </v-chip>
                <v-chip x-small v-else color="green lighten-5" text-color="green darken-2" class="font-weight-bold">
                  Disponible
                </v-chip>
              </v-list-item-action>
            </template>
          </v-autocomplete>

          <!-- ALERTA SI SE SELECCIONA UN FUNCIONARIO QUE YA TIENE CARGO -->
          <v-alert
            v-if="funcionarioSeleccionadoParaAsignar && funcionarioSeleccionadoParaAsignar.puesto_actual"
            dense
            outlined
            type="info"
            class="mb-3 text-caption"
          >
            <strong>TRANSFERENCIA / PROMOCIÓN:</strong> El funcionario actualmente ocupa el cargo de
            <strong>{{ funcionarioSeleccionadoParaAsignar.puesto_actual.cargo }}</strong> (Ítem #{{ funcionarioSeleccionadoParaAsignar.puesto_actual.nro_item }}) en {{ funcionarioSeleccionadoParaAsignar.puesto_actual.unidad }}.
            Al confirmar esta designación, su asignación anterior se dará por concluida automáticamente y se registrará la transferencia en su legajo e historial laboral.
          </v-alert>

          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formAsignar.nro_item"
                label="Número de Ítem *"
                type="number"
                dense
                outlined
                prepend-inner-icon="mdi-pound"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formAsignar.fecha_inicio"
                label="Fecha de Inicio / Posesión *"
                type="date"
                dense
                outlined
                prepend-inner-icon="mdi-calendar"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="formAsignar.tipo_movimiento"
                :items="['DESIGNACION', 'TRANSFERENCIA', 'PROMOCION']"
                label="Tipo de Movimiento *"
                dense
                outlined
                prepend-inner-icon="mdi-swap-horizontal-bold"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formAsignar.nro_documento"
                label="Memorándum / Resolución N°"
                placeholder="Ej: MEMO CITE-045/2026"
                dense
                outlined
                prepend-inner-icon="mdi-file-document-outline"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogAsignar = false">Cancelar</v-btn>
          <v-btn color="primary" class="font-weight-bold" :loading="guardandoAsignacion" @click="guardarAsignacion()">
            Confirmar Asignación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG DESVINCULAR / CONCLUIR FUNCIONES -->
    <v-dialog v-model="dialogDesvincular" max-width="580px" persistent>
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3 font-weight-bold text-h6">
          <v-icon left color="white">mdi-account-remove</v-icon>
          Desvincular / Conclusión de Funciones
        </v-card-title>
        <v-card-text class="pt-4">
          <v-alert dense color="red lighten-5" class="red--text text--darken-3 mb-3 text-caption">
            <strong>Atención:</strong> Esta acción dará por finalizada la asignación del funcionario en el cargo. El puesto pasará automáticamente a estado <strong>VACANTE</strong> y se registrará el cese en el historial laboral de su Legajo Digital.
          </v-alert>

          <v-card outlined class="pa-3 mb-3 grey lighten-5 rounded-lg">
            <div class="text-caption text-secondary">Funcionario:</div>
            <div class="font-weight-bold text-subtitle-2">{{ desvinculacionActual.persona_nombre }}</div>
            <div class="text-caption text-secondary mt-1">Cargo a Desasignar:</div>
            <div class="font-weight-medium">{{ desvinculacionActual.puesto_nombre }} (Ítem #{{ desvinculacionActual.nro_item }})</div>
            <div class="text-caption text-secondary">Unidad: {{ desvinculacionActual.unidad_nombre }} • En funciones desde: {{ desvinculacionActual.fecha_inicio || 'No registrada' }}</div>
          </v-card>

          <v-row dense>
            <v-col cols="12" sm="6">
              <v-select
                v-model="formDesvincular.motivo"
                :items="motivosDesvinculacion"
                item-text="texto"
                item-value="valor"
                label="Motivo / Tipo de Cese *"
                dense
                outlined
                prepend-inner-icon="mdi-tag-outline"
                @change="alCambiarMotivoDesvinculacion"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formDesvincular.fecha_desvinculacion"
                label="Fecha Efectiva de Cese *"
                type="date"
                dense
                outlined
                prepend-inner-icon="mdi-calendar-remove"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formDesvincular.nro_documento"
                label="Documento de Respaldo / Resolución *"
                placeholder="Ej: Res. Adm. N° 089/2026, Memo Cese N° 12, Nota de Renuncia"
                dense
                outlined
                prepend-inner-icon="mdi-file-certificate-outline"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="formDesvincular.observacion"
                label="Observación / Justificación del Cese"
                rows="2"
                dense
                outlined
                placeholder="Detalle o causa institucional del movimiento..."
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-checkbox
                v-model="formDesvincular.desactivar_acceso_erp"
                label="Desactivar inmediatamente la cuenta de acceso al ERP para este funcionario"
                dense
                color="error"
                hide-details
                class="mt-0"
              ></v-checkbox>
              <div class="text-caption grey--text pl-8">Bloquea el inicio de sesión del usuario en el sistema por motivos de seguridad.</div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogDesvincular = false">Cancelar</v-btn>
          <v-btn color="error" class="font-weight-bold" :loading="guardandoDesvinculacion" @click="confirmarGuardarDesvinculacion()">
            Confirmar Desvinculación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG HISTORIAL DE OCUPANTES DEL PUESTO -->
    <v-dialog v-model="dialogHistorialPuesto" max-width="700px" persistent scrollable>
      <v-card rounded="lg">
        <v-card-title class="teal white--text py-3 font-weight-bold text-h6">
          <v-icon left color="white">mdi-history</v-icon>
          Historial de Ocupantes del Puesto
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogHistorialPuesto = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="mb-3" v-if="historialPuestoSeleccionado">
            <div class="font-weight-bold text-subtitle-1 primary--text">{{ historialPuestoSeleccionado.nombre }}</div>
            <div class="text-caption text-secondary">
              Tipo: {{ historialPuestoSeleccionado.tipo_puesto }} • Unidad: {{ unidadSeleccionada ? unidadSeleccionada.nombre : '' }}
            </div>
          </div>

          <v-progress-linear v-if="cargandoHistorialPuesto" indeterminate color="teal" class="mb-3"></v-progress-linear>

          <v-simple-table v-if="itemsHistorialPuesto.length > 0" dense class="border rounded-lg">
            <thead>
              <tr class="grey lighten-4">
                <th>Funcionario</th>
                <th>Ítem</th>
                <th>Periodo</th>
                <th>Motivo Salida / Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="h in itemsHistorialPuesto" :key="h.id">
                <td class="font-weight-bold">
                  {{ h.persona ? (h.persona.nombre_completo || h.persona.nombres) : 'Sin datos' }}
                  <div class="text-caption text-secondary font-weight-regular" v-if="h.persona">
                    C.I.: {{ h.persona.nro_documento }}
                  </div>
                </td>
                <td>#{{ h.nro_item }}</td>
                <td class="text-caption">
                  {{ h.fecha_inicio }} al {{ h.fecha_fin || 'Actualmente' }}
                </td>
                <td>
                  <v-chip x-small label :color="h._estado === 'ACTIVO' && !h.fecha_fin ? 'green lighten-5 green--text text--darken-2' : 'grey lighten-3 grey--text text--darken-2'" class="font-weight-bold">
                    {{ h._estado === 'ACTIVO' && !h.fecha_fin ? 'ACTUAL' : (h.asignacion || 'FINALIZADO') }}
                  </v-chip>
                </td>
              </tr>
            </tbody>
          </v-simple-table>
          <div v-else-if="!cargandoHistorialPuesto" class="text-center py-6 text-caption text-secondary font-italic">
            No existen registros históricos de asignaciones para este puesto.
          </div>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="dialogHistorialPuesto = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG CONFIRMAR ACCIÓN (ELIMINAR) -->
    <v-dialog v-model="dialogConfirmar" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="error white--text py-3 font-weight-bold">
          <v-icon left color="white">mdi-alert-circle-outline</v-icon>
          {{ confirmarTitulo }}
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="mb-0 text-body-1">{{ confirmarMensaje }}</p>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogConfirmar = false">Cancelar</v-btn>
          <v-btn color="error" class="font-weight-bold" :loading="ejecutandoAccionConfirmada" @click="ejecutarAccionConfirmada()">
            Sí, Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA ESCALA SALARIAL -->
    <v-dialog v-model="dialogEscala" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Escala Salarial</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formEscala.nombre" label="Denominación de Escala *" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formEscala.salario_mensual" label="Salario Mensual (Bs.) *" type="number" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogEscala = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarEscala()">Guardar Escala</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA REGIONAL -->
    <v-dialog v-model="dialogRegional" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Regional</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formRegional.nombre" label="Nombre Regional (Ej: Regional Santa Cruz) *" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formRegional.sigla" label="Sigla (Ej: SCZ)" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogRegional = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarRegional()">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG NUEVA GESTIÓN -->
    <v-dialog v-model="dialogGestion" max-width="450px" persistent>
      <v-card rounded="lg">
        <v-card-title class="font-weight-bold text-h6 primary white--text py-3">Nueva Gestión Institucional</v-card-title>
        <v-card-text class="pt-4">
          <v-text-field v-model="formGestion.anio" label="Año / Gestión *" type="number" dense outlined class="mb-2"></v-text-field>
          <v-text-field v-model="formGestion.descripcion" label="Descripción *" dense outlined></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn text @click="dialogGestion = false">Cancelar</v-btn>
          <v-btn color="primary" @click="guardarGestion()">Crear Gestión</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3500" top right rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }"><v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn></template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'Organigrama',
  data() {
    return {
      tabActual: 0,
      unidades: [],
      unidadesPlanas: [],
      unidadSeleccionada: null,

      escalasSalariales: [],
      headersEscalas: [
        { text: 'Denominación de Escala', value: 'nombre' },
        { text: 'Salario Mensual', value: 'salario_mensual' },
        { text: 'Estado', value: '_estado' },
      ],

      regionales: [],
      gestiones: [],

      // Modal Unidad
      dialogUnidad: false,
      esModoEdicionUnidad: false,
      idUnidadEditar: null,
      guardandoUnidad: false,
      formUnidad: { nombre: '', sigla: '', padreId: null },

      // Modal Puesto
      dialogPuesto: false,
      esModoEdicionPuesto: false,
      idPuestoEditar: null,
      guardandoPuesto: false,
      formPuesto: { nombre: '', tipo_puesto: 'PLANTA', id_unidad_organizacional: null, id_escala_salarial: null },

      // Modal Asignar Funcionario
      dialogAsignar: false,
      puestoSeleccionadoParaAsignar: null,
      guardandoAsignacion: false,
      listaPersonal: [],
      filtroSoloDisponibles: true,
      formAsignar: {
        id_puesto: null,
        id_persona: null,
        nro_item: 1,
        fecha_inicio: new Date().toISOString().substring(0, 10),
        tipo_movimiento: 'DESIGNACION',
        nro_documento: '',
      },

      // Modal Desvincular / Cese de Funciones
      dialogDesvincular: false,
      guardandoDesvinculacion: false,
      desvinculacionActual: {
        id_asignacion: null,
        persona_nombre: '',
        puesto_nombre: '',
        unidad_nombre: '',
        nro_item: null,
        fecha_inicio: '',
      },
      formDesvincular: {
        motivo: 'RENUNCIA',
        fecha_desvinculacion: new Date().toISOString().substring(0, 10),
        nro_documento: '',
        observacion: '',
        desactivar_acceso_erp: true,
      },
      motivosDesvinculacion: [
        { valor: 'RENUNCIA', texto: 'Renuncia Voluntaria' },
        { valor: 'DESTITUCION', texto: 'Destitución' },
        { valor: 'DESPIDO', texto: 'Agradecimiento de Servicios / Despido' },
        { valor: 'CONCLUSION_CONTRATO', texto: 'Conclusión de Contrato / Cese de Periodo' },
        { valor: 'JUBILACION', texto: 'Jubilación / Retiro' },
        { valor: 'TRANSFERENCIA', texto: 'Transferencia Externa' },
        { valor: 'DESASIGNACION', texto: 'Desasignación Administrativa' },
      ],

      // Modal Historial Puesto
      dialogHistorialPuesto: false,
      historialPuestoSeleccionado: null,
      cargandoHistorialPuesto: false,
      itemsHistorialPuesto: [],

      // Modal Confirmar
      dialogConfirmar: false,
      confirmarTitulo: '',
      confirmarMensaje: '',
      ejecutandoAccionConfirmada: false,
      accionConfirmadaCallback: null,

      dialogEscala: false,
      formEscala: { nombre: '', salario_mensual: '' },

      dialogRegional: false,
      formRegional: { nombre: '', sigla: '' },

      dialogGestion: false,
      formGestion: { anio: new Date().getFullYear(), descripcion: 'Gestión Institucional' },

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    unidadesPlanasParaPadre() {
      if (!this.esModoEdicionUnidad || !this.idUnidadEditar) {
        return this.unidadesPlanas;
      }
      return this.unidadesPlanas.filter(u => u.id !== this.idUnidadEditar);
    },
    listaPersonalFiltrada() {
      if (this.filtroSoloDisponibles) {
        return this.listaPersonal.filter(p => !p.puesto_actual);
      }
      return this.listaPersonal;
    },
    funcionarioSeleccionadoParaAsignar() {
      if (!this.formAsignar.id_persona) return null;
      return this.listaPersonal.find(p => p.id === this.formAsignar.id_persona) || null;
    },
  },
  mounted() {
    this.cargarOrganigrama();
    this.cargarEscalas();
    this.cargarRegionales();
    this.cargarGestiones();
    this.cargarPersonal();
  },
  methods: {
    cargarOrganigrama() {
      axios.get('/api/rrhh/organigrama').then(res => {
        if (res.data && res.data.success) {
          this.unidades = res.data.data || [];
          this.unidadesPlanas = [];
          this.aplanarUnidades(this.unidades);
          if (this.unidades.length > 0) {
            if (this.unidadSeleccionada) {
              const encontrada = this.buscarUnidadPorId(this.unidades, this.unidadSeleccionada.id);
              this.unidadSeleccionada = encontrada || this.unidades[0];
            } else {
              this.unidadSeleccionada = this.unidades[0];
            }
          } else {
            this.unidadSeleccionada = null;
          }
        }
      });
    },

    cargarEscalas() {
      axios.get('/api/rrhh/escalas-salariales').then(res => {
        if (res.data && res.data.success) this.escalasSalariales = res.data.data || [];
      });
    },

    cargarRegionales() {
      axios.get('/api/rrhh/regionales').then(res => {
        if (res.data && res.data.success) this.regionales = res.data.data || [];
      });
    },

    cargarGestiones() {
      axios.get('/api/rrhh/gestiones').then(res => {
        if (res.data && res.data.success) this.gestiones = res.data.data || [];
      });
    },

    cargarPersonal() {
      axios.get('/api/rrhh/personal?per_page=150').then(res => {
        if (res.data?.data) {
          this.listaPersonal = res.data.data.data || res.data.data || [];
        }
      }).catch(() => {});
    },

    aplanarUnidades(lista) {
      lista.forEach(u => {
        this.unidadesPlanas.push({ id: u.id, nombre: u.nombre });
        if (u.dependencias && u.dependencias.length > 0) {
          this.aplanarUnidades(u.dependencias);
        }
      });
    },

    buscarUnidadPorId(lista, id) {
      for (const u of lista) {
        if (u.id === id) return u;
        if (u.dependencias && u.dependencias.length > 0) {
          const encontrada = this.buscarUnidadPorId(u.dependencias, id);
          if (encontrada) return encontrada;
        }
      }
      return null;
    },

    obtenerNombrePadre(padreId) {
      if (!padreId) return 'Ninguno (Máxima Autoridad - MAE)';
      const padre = this.unidadesPlanas.find(u => u.id === padreId);
      return padre ? padre.nombre : `Unidad #${padreId}`;
    },

    onSelectUnidad(activeKeys) {
      if (!activeKeys || activeKeys.length === 0) return;
      const id = activeKeys[0];
      this.unidadSeleccionada = this.buscarUnidadPorId(this.unidades, id);
    },

    // ================= UNIDADES CRUD =================
    abrirModalUnidad() {
      this.esModoEdicionUnidad = false;
      this.idUnidadEditar = null;
      this.formUnidad = {
        nombre: '',
        sigla: '',
        padreId: this.unidadSeleccionada ? this.unidadSeleccionada.id : null,
      };
      this.dialogUnidad = true;
    },

    editarUnidad(unidad) {
      this.esModoEdicionUnidad = true;
      this.idUnidadEditar = unidad.id;
      this.formUnidad = {
        nombre: unidad.nombre,
        sigla: unidad.sigla || '',
        padreId: unidad.padreId || null,
      };
      this.dialogUnidad = true;
    },

    crearSubunidad(unidad) {
      this.esModoEdicionUnidad = false;
      this.idUnidadEditar = null;
      this.formUnidad = {
        nombre: '',
        sigla: '',
        padreId: unidad.id,
      };
      this.dialogUnidad = true;
    },

    guardarUnidad() {
      if (!this.formUnidad.nombre) {
        this.showSnackbar('El nombre de la unidad es requerido.', 'error');
        return;
      }
      this.guardandoUnidad = true;

      const peticion = this.esModoEdicionUnidad
        ? axios.put(`/api/rrhh/unidades-organizacionales/${this.idUnidadEditar}`, this.formUnidad)
        : axios.post('/api/rrhh/unidades-organizacionales', this.formUnidad);

      peticion.then(res => {
        this.dialogUnidad = false;
        this.showSnackbar(res.data.message || 'Unidad guardada correctamente', 'success');
        this.cargarOrganigrama();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al guardar unidad', 'error');
      }).finally(() => {
        this.guardandoUnidad = false;
      });
    },

    confirmarEliminarUnidad(unidad) {
      this.confirmarTitulo = 'Eliminar Unidad Organizacional';
      this.confirmarMensaje = `¿Está seguro de que desea eliminar la unidad "${unidad.nombre}"?`;
      this.accionConfirmadaCallback = () => {
        return axios.delete(`/api/rrhh/unidades-organizacionales/${unidad.id}`).then(res => {
          this.showSnackbar(res.data.message || 'Unidad eliminada', 'success');
          this.unidadSeleccionada = null;
          this.cargarOrganigrama();
        });
      };
      this.dialogConfirmar = true;
    },

    // ================= PUESTOS CRUD =================
    abrirModalPuesto(unidadId = null) {
      this.esModoEdicionPuesto = false;
      this.idPuestoEditar = null;
      this.formPuesto = {
        nombre: '',
        tipo_puesto: 'PLANTA',
        id_unidad_organizacional: unidadId || (this.unidadSeleccionada ? this.unidadSeleccionada.id : null),
        id_escala_salarial: null,
      };
      this.dialogPuesto = true;
    },

    editarPuesto(puesto) {
      this.esModoEdicionPuesto = true;
      this.idPuestoEditar = puesto.id;
      this.formPuesto = {
        nombre: puesto.nombre,
        tipo_puesto: puesto.tipo_puesto || 'PLANTA',
        id_unidad_organizacional: puesto.id_unidad_organizacional,
        id_escala_salarial: puesto.id_escala_salarial || null,
      };
      this.dialogPuesto = true;
    },

    guardarPuesto() {
      if (!this.formPuesto.nombre || !this.formPuesto.id_unidad_organizacional) {
        this.showSnackbar('El nombre y la unidad son obligatorios.', 'error');
        return;
      }
      this.guardandoPuesto = true;

      const peticion = this.esModoEdicionPuesto
        ? axios.put(`/api/rrhh/puestos/${this.idPuestoEditar}`, this.formPuesto)
        : axios.post('/api/rrhh/puestos', this.formPuesto);

      peticion.then(res => {
        this.dialogPuesto = false;
        this.showSnackbar(res.data.message || 'Puesto guardado', 'success');
        this.cargarOrganigrama();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al guardar puesto', 'error');
      }).finally(() => {
        this.guardandoPuesto = false;
      });
    },

    confirmarEliminarPuesto(puesto) {
      this.confirmarTitulo = 'Eliminar Puesto de Trabajo';
      this.confirmarMensaje = `¿Está seguro de que desea eliminar el puesto "${puesto.nombre}"?`;
      this.accionConfirmadaCallback = () => {
        return axios.delete(`/api/rrhh/puestos/${puesto.id}`).then(res => {
          this.showSnackbar(res.data.message || 'Puesto eliminado', 'success');
          this.cargarOrganigrama();
        });
      };
      this.dialogConfirmar = true;
    },

    // ================= ASIGNACIÓN DE FUNCIONARIOS =================
    abrirModalAsignar(puesto) {
      this.puestoSeleccionadoParaAsignar = puesto;
      this.filtroSoloDisponibles = true;
      this.formAsignar = {
        id_puesto: puesto.id,
        id_persona: null,
        nro_item: (puesto.asignaciones && puesto.asignaciones.length > 0) ? puesto.asignaciones[0].nro_item : 1,
        fecha_inicio: new Date().toISOString().substring(0, 10),
        tipo_movimiento: 'DESIGNACION',
        nro_documento: '',
      };
      this.cargarPersonal();
      this.dialogAsignar = true;
    },

    guardarAsignacion() {
      if (!this.formAsignar.id_persona) {
        this.showSnackbar('Debe seleccionar un funcionario.', 'error');
        return;
      }

      if (this.funcionarioSeleccionadoParaAsignar?.puesto_actual && this.formAsignar.tipo_movimiento === 'DESIGNACION') {
        this.formAsignar.tipo_movimiento = 'TRANSFERENCIA';
      }

      this.guardandoAsignacion = true;
      axios.post('/api/rrhh/asignar-puesto', this.formAsignar).then(res => {
        this.dialogAsignar = false;
        this.showSnackbar(res.data.message || 'Funcionario asignado exitosamente.', 'success');
        this.cargarOrganigrama();
        this.cargarPersonal();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al asignar funcionario', 'error');
      }).finally(() => {
        this.guardandoAsignacion = false;
      });
    },

    // ================= DESVINCULACIÓN / CESE DE FUNCIONES =================
    abrirModalDesvincular(puesto, asignacion) {
      this.desvinculacionActual = {
        id_asignacion: asignacion.id,
        persona_nombre: asignacion.persona ? (asignacion.persona.nombre_completo || (asignacion.persona.nombres + ' ' + (asignacion.persona.primer_apellido || ''))) : 'Funcionario',
        puesto_nombre: puesto.nombre,
        unidad_nombre: this.unidadSeleccionada?.nombre || '',
        nro_item: asignacion.nro_item,
        fecha_inicio: asignacion.fecha_inicio,
      };
      this.formDesvincular = {
        motivo: 'RENUNCIA',
        fecha_desvinculacion: new Date().toISOString().substring(0, 10),
        nro_documento: '',
        observacion: '',
        desactivar_acceso_erp: true,
      };
      this.dialogDesvincular = true;
    },

    alCambiarMotivoDesvinculacion(val) {
      if (['RENUNCIA', 'DESTITUCION', 'DESPIDO', 'CONCLUSION_CONTRATO', 'JUBILACION'].includes(val)) {
        this.formDesvincular.desactivar_acceso_erp = true;
      } else {
        this.formDesvincular.desactivar_acceso_erp = false;
      }
    },

    confirmarGuardarDesvinculacion() {
      this.guardandoDesvinculacion = true;
      axios.post(`/api/rrhh/asignaciones-puestos/${this.desvinculacionActual.id_asignacion}/desvincular`, this.formDesvincular).then(res => {
        this.dialogDesvincular = false;
        this.showSnackbar(res.data.message || 'Desvinculación procesada exitosamente.', 'success');
        this.cargarOrganigrama();
        this.cargarPersonal();
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al procesar la desvinculación', 'error');
      }).finally(() => {
        this.guardandoDesvinculacion = false;
      });
    },

    // ================= HISTORIAL DEL PUESTO =================
    abrirHistorialPuesto(puesto) {
      this.historialPuestoSeleccionado = puesto;
      this.cargandoHistorialPuesto = true;
      this.itemsHistorialPuesto = [];
      this.dialogHistorialPuesto = true;
      axios.get(`/api/rrhh/puestos/${puesto.id}/historial`).then(res => {
        if (res.data && res.data.success) {
          this.itemsHistorialPuesto = res.data.data || [];
        }
      }).catch(() => {
        this.showSnackbar('Error al cargar historial del puesto', 'error');
      }).finally(() => {
        this.cargandoHistorialPuesto = false;
      });
    },

    // ================= EJECUTAR ACCIÓN CONFIRMADA =================
    ejecutarAccionConfirmada() {
      if (!this.accionConfirmadaCallback) {
        this.dialogConfirmar = false;
        return;
      }
      this.ejecutandoAccionConfirmada = true;
      this.accionConfirmadaCallback().then(() => {
        this.dialogConfirmar = false;
      }).catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al ejecutar la acción', 'error');
      }).finally(() => {
        this.ejecutandoAccionConfirmada = false;
      });
    },

    // ================= OTROS CATÁLOGOS =================
    guardarEscala() {
      if (!this.formEscala.nombre || !this.formEscala.salario_mensual) return;
      axios.post('/api/rrhh/escalas-salariales', this.formEscala).then(res => {
        this.dialogEscala = false;
        this.showSnackbar(res.data.message || 'Escala creada', 'success');
        this.cargarEscalas();
      });
    },

    guardarRegional() {
      if (!this.formRegional.nombre) return;
      axios.post('/api/rrhh/regionales', this.formRegional).then(res => {
        this.dialogRegional = false;
        this.showSnackbar(res.data.message || 'Regional creada', 'success');
        this.cargarRegionales();
      });
    },

    guardarGestion() {
      if (!this.formGestion.anio) return;
      axios.post('/api/rrhh/gestiones', this.formGestion).then(res => {
        this.dialogGestion = false;
        this.showSnackbar(res.data.message || 'Gestión creada', 'success');
        this.cargarGestiones();
      });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
