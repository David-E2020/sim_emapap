<template>
  <div>
    <!-- CABECERA INSTITUCIONAL NATIVA MATERIO -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="48">
            <v-icon color="white">mdi-calendar-clock</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Jornadas, Horarios y Calendario</h2>
            <span class="text-caption text-secondary">
              Definición de turnos, tolerancias, asignación de personal, feriados oficiales y fechas de corte mensual
            </span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon color="primary" v-bind="attrs" v-on="on" @click="recargarTodo" :loading="cargandoGeneral" class="mr-1">
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>Actualizar datos</span>
          </v-tooltip>

          <v-btn
            color="primary"
            class="rounded-pill font-weight-bold text-capitalize px-4 elevation-1"
            @click="abrirModalHorario"
          >
            <v-icon left small>mdi-plus</v-icon> + Nuevo Horario
          </v-btn>

          <v-menu offset-y>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" outlined class="rounded-pill font-weight-bold text-capitalize px-3" v-bind="attrs" v-on="on">
                <v-icon left small>mdi-cog-outline</v-icon> Opciones <v-icon right small>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list dense class="py-1">
              <v-list-item @click="abrirModalFeriado">
                <v-list-item-icon class="mr-2"><v-icon small color="indigo">mdi-calendar-plus</v-icon></v-list-item-icon>
                <v-list-item-title class="font-weight-medium">Registrar Feriado Oficial</v-list-item-title>
              </v-list-item>
              <v-list-item @click="abrirModalCorte">
                <v-list-item-icon class="mr-2"><v-icon small color="amber darken-3">mdi-calendar-sync</v-icon></v-list-item-icon>
                <v-list-item-title class="font-weight-medium">Configurar Corte Mensual</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS KPIS DE HORARIOS Y CALENDARIO (ESTILO NATIVO CONTROL ASISTENCIA) -->
    <v-row dense class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-3 text-center erp-card-elevated"
          :class="{ 'card-kpi-activa': seccionActiva === 'horarios' }"
          rounded="lg"
          @click="seccionActiva = 'horarios'"
          style="cursor: pointer;"
        >
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Tipos de Horarios</div>
          <div class="text-h4 font-weight-black primary--text mt-1">{{ horarios.length }}</div>
          <div class="text-caption text-secondary">Turnos continuos y especiales</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-3 text-center erp-card-elevated"
          :class="{ 'card-kpi-activa': seccionActiva === 'asignaciones' }"
          rounded="lg"
          @click="seccionActiva = 'asignaciones'"
          style="cursor: pointer;"
        >
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Personal Asignado</div>
          <div class="text-h4 font-weight-black success--text mt-1">{{ totalPersonalAsignado }}</div>
          <div class="text-caption success--text text--darken-2 font-weight-medium">Funcionarios con turno activo</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-3 text-center erp-card-elevated"
          :class="{ 'card-kpi-activa': seccionActiva === 'feriados' }"
          rounded="lg"
          @click="seccionActiva = 'feriados'"
          style="cursor: pointer;"
        >
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Feriados de Ley</div>
          <div class="text-h4 font-weight-black indigo--text mt-1">{{ feriados.length }}</div>
          <div class="text-caption text-secondary">Gestión {{ anioSeleccionado }}</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card
          class="pa-3 text-center erp-card-elevated"
          :class="{ 'card-kpi-activa': seccionActiva === 'cortes' }"
          rounded="lg"
          @click="seccionActiva = 'cortes'"
          style="cursor: pointer;"
        >
          <div class="text-caption text-secondary font-weight-bold text-uppercase">Períodos de Corte</div>
          <div class="text-h4 font-weight-black amber--text text--darken-3 mt-1">{{ cortes.length }}</div>
          <div class="text-caption text-secondary">Cierres mensuales de asistencia</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- CARD PRINCIPAL: FILTROS Y MODALIDADES (EXACTO A LA IMAGEN ADJUNTA) -->
    <v-card rounded="lg" class="erp-card-elevated pa-4">
      <!-- BARRA DE MODALIDAD: IDÉNTICA A CONTROL DE ASISTENCIA Y LA IMAGEN ADJUNTA -->
      <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-3 pb-3 border-b">
        <div class="d-flex align-center gap-2 flex-wrap">
          <span class="text-caption font-weight-bold text-secondary mr-2">MODALIDAD DE CONSULTA:</span>
          <v-btn-toggle v-model="seccionActiva" mandatory dense color="primary" @change="alCambiarSeccion">
            <v-btn small value="horarios" class="text-capitalize">
              <v-icon left x-small>mdi-calendar-today</v-icon> Horarios y Turnos
            </v-btn>
            <v-btn small value="asignaciones" class="text-capitalize">
              <v-icon left x-small>mdi-account-clock</v-icon> Asignación a Personal
            </v-btn>
            <v-btn small value="feriados" class="text-capitalize">
              <v-icon left x-small>mdi-calendar-month</v-icon> Feriados Oficiales
            </v-btn>
            <v-btn small value="cortes" class="text-capitalize">
              <v-icon left x-small>mdi-calendar-sync</v-icon> Fechas de Corte
            </v-btn>
          </v-btn-toggle>
        </div>

        <!-- FILTROS COMPLEMENTARIOS SEGÚN LA SECCIÓN SELECCIONADA -->
        <div class="d-flex align-center gap-2 flex-wrap" v-if="seccionActiva === 'horarios'">
          <span class="text-caption font-weight-bold text-secondary mr-1">TIPO:</span>
          <v-btn-toggle v-model="filtroTipoHorario" mandatory dense color="primary">
            <v-btn small value="TODOS" class="text-capitalize">Todos</v-btn>
            <v-btn small value="CONTINUO" class="text-capitalize">Continuo</v-btn>
            <v-btn small value="DISCONTINUO" class="text-capitalize">Discontinuo</v-btn>
            <v-btn small value="ESPECIAL" class="text-capitalize">Especial</v-btn>
          </v-btn-toggle>
        </div>

        <div class="d-flex align-center gap-2" v-else-if="seccionActiva === 'asignaciones'">
          <v-text-field
            v-model="busquedaAsignacion"
            prepend-inner-icon="mdi-magnify"
            placeholder="Buscar funcionario o CI..."
            dense
            outlined
            hide-details
            clearable
            style="max-width: 260px;"
          ></v-text-field>
        </div>

        <div class="d-flex align-center gap-2" v-else-if="seccionActiva === 'feriados'">
          <span class="text-caption font-weight-bold text-secondary mr-1">GESTIÓN:</span>
          <v-select
            v-model="anioSeleccionado"
            :items="[2024, 2025, 2026, 2027]"
            outlined
            dense
            hide-details
            style="max-width: 105px;"
            @change="cargarFeriados"
          ></v-select>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODALIDAD 1: HORARIOS Y TURNOS LABORALES   -->
      <!-- ========================================== -->
      <div v-if="seccionActiva === 'horarios'">
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Estructura de Horarios y Turnos Laborales</h3>
            <p class="text-caption text-secondary mb-0">
              Cada horario define sus intervalos de ingreso/salida y permite asignar directamente a funcionarios
            </p>
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn color="primary" small class="rounded-pill font-weight-bold text-capitalize" @click="abrirModalHorario">
              <v-icon left small>mdi-plus</v-icon> Nuevo Horario
            </v-btn>
            <v-btn icon color="primary" :loading="loadingHorarios" @click="cargarHorarios">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </div>
        </div>

        <v-row v-if="horariosFiltrados.length > 0">
          <v-col cols="12" md="6" v-for="h in horariosFiltrados" :key="h.id">
            <v-card rounded="lg" class="pa-4 h-100 d-flex flex-column justify-space-between erp-card-elevated">
              <div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center">
                    <v-avatar color="primary" class="mr-3 text-white elevation-1" size="38">
                      <v-icon color="white" small>mdi-clock-outline</v-icon>
                    </v-avatar>
                    <div>
                      <div class="font-weight-bold text-subtitle-2 primary--text">{{ h.nombre }}</div>
                      <div class="text-caption text-secondary">Jornada ordinaria laboral</div>
                    </div>
                  </div>
                  <v-chip small :color="getColorTipoHorario(h.tipo)" label class="font-weight-bold text-white">
                    {{ h.tipo }}
                  </v-chip>
                </div>

                <div class="d-flex align-center gap-2 text-caption text-secondary mb-3 flex-wrap">
                  <v-chip x-small outlined color="secondary" class="font-weight-bold">
                    <v-icon x-small left>mdi-clock-alert-outline</v-icon>
                    Tolerancia: {{ h.tolerancia_minutos }} min
                  </v-chip>
                  <v-chip x-small outlined color="secondary" class="font-weight-bold">
                    <v-icon x-small left>mdi-calendar-week</v-icon>
                    Lunes a Viernes
                  </v-chip>
                </div>

                <v-divider class="mb-3"></v-divider>

                <!-- Turnos / Intervalos con diseño limpio adaptable a dark mode -->
                <div class="text-caption font-weight-bold text-secondary text-uppercase mb-2">Turnos de la Jornada:</div>
                <div
                  v-for="p in h.periodos"
                  :key="p.id || p.orden"
                  class="d-flex align-center justify-space-between py-2 px-3 mb-2 rounded turno-box"
                >
                  <span class="text-caption font-weight-bold">Turno #{{ p.orden }}:</span>
                  <div class="d-flex align-center gap-3 text-caption font-weight-medium">
                    <span class="success--text font-weight-bold">
                      <v-icon x-small color="success">mdi-login</v-icon> Entrada: {{ p.hora_inicio }}
                    </span>
                    <span class="text-secondary">&bull;</span>
                    <span class="info--text font-weight-bold">
                      <v-icon x-small color="info">mdi-logout</v-icon> Salida: {{ p.hora_fin }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Footer de Asignación de Personal Directa -->
              <div class="mt-4 pt-3 border-t d-flex align-center justify-space-between flex-wrap gap-2">
                <div class="d-flex align-center">
                  <v-icon small color="primary" left>mdi-account-group</v-icon>
                  <span class="text-caption font-weight-bold">
                    {{ (h.total_asignados || 0) }} funcionarios asignados
                  </span>
                </div>

                <v-btn
                  small
                  color="primary"
                  outlined
                  class="rounded-pill font-weight-bold text-capitalize"
                  @click="abrirAsignacionHorario(h)"
                >
                  <v-icon left x-small>mdi-account-multiple-check</v-icon> Asignar a Personal
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <div v-else-if="loadingHorarios" class="text-center py-12">
          <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </div>

        <div v-else class="text-center py-12 rounded turno-box">
          <v-icon size="48" color="grey lighten-1">mdi-timetable</v-icon>
          <div class="mt-2 font-weight-bold text-subtitle-1 text-secondary">No hay horarios registrados con este filtro</div>
          <v-btn color="primary" small class="rounded-pill font-weight-bold mt-2" @click="abrirModalHorario">
            + Registrar Nuevo Horario
          </v-btn>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- MODALIDAD 2: ASIGNACIONES DE PERSONAL (TABLA)  -->
      <!-- ============================================== -->
      <div v-else-if="seccionActiva === 'asignaciones'">
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Personal y Turnos Asignados</h3>
            <p class="text-caption text-secondary mb-0">
              Relación de funcionarios activos y el horario laboral al que se encuentran asignados actualmente
            </p>
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn color="primary" small class="rounded-pill font-weight-bold text-capitalize" @click="abrirModalAsignacionDirecta">
              <v-icon left small>mdi-account-plus</v-icon> Asignar Horario
            </v-btn>
            <v-btn icon color="primary" :loading="loadingAsignaciones" @click="cargarAsignaciones">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </div>
        </div>

        <v-data-table
          :headers="headersAsignaciones"
          :items="asignacionesFiltradas"
          :loading="loadingAsignaciones"
          dense
          no-data-text="No hay asignaciones de horario registradas"
          class="elevation-0"
        >
          <template v-slot:item.funcionario="{ item }">
            <div class="font-weight-bold">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido || '' }}</div>
            <div class="text-caption text-secondary">CI: {{ item.nro_documento }}</div>
          </template>

          <template v-slot:item.horario="{ item }">
            <v-chip small color="primary" outlined class="font-weight-bold">
              <v-icon left x-small>mdi-clock-outline</v-icon>
              {{ item.horario_nombre }} ({{ item.horario_tipo }})
            </v-chip>
          </template>

          <template v-slot:item.vigencia="{ item }">
            <v-chip x-small color="success" outlined class="font-weight-bold" v-if="item.permanente">
              <v-icon left x-small>mdi-infinity</v-icon> Indefinido / Permanente
            </v-chip>
            <span class="text-caption" v-else>
              {{ item.fecha_inicio }} al {{ item.fecha_fin || 'Vigente' }}
            </span>
          </template>

          <template v-slot:item.tolerancia="{ item }">
            <v-chip x-small outlined color="secondary" class="font-weight-bold">
              {{ item.tolerancia_minutos || 10 }} min
            </v-chip>
          </template>

          <template v-slot:item.acciones="{ item }">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon x-small color="primary" v-bind="attrs" v-on="on" @click="abrirReasignarPersonal(item)">
                  <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
              </template>
              <span>Cambiar horario asignado</span>
            </v-tooltip>
          </template>
        </v-data-table>
      </div>

      <!-- ============================================== -->
      <!-- MODALIDAD 3: FERIADOS OFICIALES (MINITARJETAS) -->
      <!-- ============================================== -->
      <div v-else-if="seccionActiva === 'feriados'">
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Feriados Oficiales y Asuetos de Ley</h3>
            <p class="text-caption text-secondary mb-0">
              Días no laborables y feriados computados para el calendario laboral de la gestión {{ anioSeleccionado }}
            </p>
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn color="indigo darken-1" dark small class="rounded-pill font-weight-bold text-capitalize" @click="abrirModalFeriado">
              <v-icon left small>mdi-plus</v-icon> Nuevo Feriado
            </v-btn>
            <v-btn icon color="primary" :loading="loadingFeriados" @click="cargarFeriados">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </div>
        </div>

        <v-row v-if="feriados.length > 0">
          <v-col cols="12" sm="6" md="4" lg="3" v-for="f in feriados" :key="f.id">
            <v-card rounded="lg" class="pa-3 h-100 d-flex align-center erp-card-elevated minitarjeta-feriado">
              <!-- INSIGNIA CALENDARIO ESTILO HOJA -->
              <div class="calendario-insignia text-center mr-3 overflow-hidden">
                <div class="calendario-mes text-caption text-uppercase font-weight-bold white--text primary px-1 py-0">
                  {{ getSiglaMes(f.mes) }}
                </div>
                <div class="calendario-dia text-h5 font-weight-black py-1">
                  {{ String(f.dia).padStart(2, '0') }}
                </div>
              </div>

              <!-- DETALLE DEL FERIADO -->
              <div class="flex-grow-1 overflow-hidden">
                <div class="d-flex align-center justify-space-between mb-1">
                  <v-chip x-small :color="f.es_feriado_nacional ? 'primary' : 'purple'" label class="font-weight-bold text-white">
                    {{ f.es_feriado_nacional ? 'NACIONAL' : (f.departamento ? f.departamento.nombre : 'DEPTAL') }}
                  </v-chip>
                </div>
                <div class="font-weight-bold text-body-2 text-truncate" :title="f.nombre">
                  {{ f.nombre }}
                </div>
                <div class="text-caption text-secondary mt-1">
                  <v-icon x-small color="grey" class="mr-1">mdi-calendar</v-icon>
                  {{ formatearDiaMes(f.dia, f.mes) }} {{ f.anio }}
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <div v-else-if="loadingFeriados" class="text-center py-12">
          <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </div>

        <div v-else class="text-center py-12 rounded turno-box">
          <v-icon size="48" color="grey lighten-1">mdi-calendar-blank</v-icon>
          <div class="mt-2 font-weight-bold text-subtitle-1 text-secondary">No hay feriados registrados para la gestión {{ anioSeleccionado }}</div>
          <v-btn color="indigo darken-1" dark small class="rounded-pill font-weight-bold mt-2" @click="abrirModalFeriado">
            + Registrar Feriado
          </v-btn>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- MODALIDAD 4: FECHAS DE CORTE MENSUAL       -->
      <!-- ========================================== -->
      <div v-else-if="seccionActiva === 'cortes'">
        <div class="d-flex align-center justify-space-between mb-3 flex-wrap gap-2">
          <div>
            <h3 class="text-subtitle-1 font-weight-bold mb-0">Fechas de Corte Mensual de Asistencia</h3>
            <p class="text-caption text-secondary mb-0">Períodos de cierre para cómputo de faltas, atrasos y refrigerios institucionales</p>
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn color="primary" small class="rounded-pill font-weight-bold text-capitalize" @click="abrirModalCorte">
              <v-icon left small>mdi-plus</v-icon> Configurar Corte
            </v-btn>
            <v-btn icon color="primary" :loading="loadingCortes" @click="cargarCortes">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </div>
        </div>

        <v-data-table
          :headers="headersCortes"
          :items="cortes"
          :loading="loadingCortes"
          dense
          no-data-text="No hay periodos de corte registrados"
          class="elevation-0"
        >
          <template v-slot:item.mes="{ item }">
            <span class="font-weight-bold primary--text">{{ getNombreMes(item.mes) }} {{ item.gestion }}</span>
          </template>
          <template v-slot:item.periodo="{ item }">
            <span class="text-caption font-weight-medium">
              <v-icon x-small color="grey" class="mr-1">mdi-calendar-range</v-icon>
              {{ item.fecha_inicio }} al {{ item.fecha_fin }}
            </span>
          </template>
          <template v-slot:item.dias_habiles="{ item }">
            <v-chip x-small outlined color="secondary" class="font-weight-bold">
              {{ item.dias_habiles || 22 }} días
            </v-chip>
          </template>
          <template v-slot:item.estado="{ item }">
            <v-chip x-small :color="item.cerrado ? 'grey darken-1' : 'success'" label class="font-weight-bold text-white">
              {{ item.cerrado ? 'CERRADO' : 'ABIERTO / VIGENTE' }}
            </v-chip>
          </template>
        </v-data-table>
      </div>
    </v-card>

    <!-- ========================================== -->
    <!-- DIÁLOGO: ASIGNAR HORARIO A PERSONAL        -->
    <!-- ========================================== -->
    <v-dialog v-model="dialogAsignar" max-width="600" persistent>
      <v-card rounded="lg" v-if="horarioSeleccionado">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-multiple-check</v-icon>
          <span>Asignar Horario: {{ horarioSeleccionado.nombre }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogAsignar = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-alert dense outlined type="info" class="text-caption mb-3">
            Seleccione uno o varios funcionarios a los que desea asignar este horario de forma masiva o individual.
          </v-alert>

          <v-autocomplete
            v-model="formAsig.personas_ids"
            :items="personalList"
            item-text="nombre_completo"
            item-value="id"
            label="Seleccionar Funcionarios *"
            multiple
            chips
            deletable-chips
            small-chips
            outlined
            dense
            prepend-inner-icon="mdi-account-multiple"
            hint="Puede buscar por nombre, apellido o CI"
            persistent-hint
          ></v-autocomplete>

          <v-row dense class="mt-3">
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formAsig.fecha_inicio"
                label="Fecha de Entrada en Vigencia *"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-checkbox
                v-model="formAsig.permanente"
                label="Asignación Permanente"
                dense
                hide-details
                class="mt-1"
              ></v-checkbox>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3 justify-end">
          <v-btn text class="text-capitalize rounded-pill" @click="dialogAsignar = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="text-capitalize rounded-pill px-4"
            :loading="guardandoAsig"
            @click="guardarAsignacionHorario"
          >
            Guardar Asignación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ========================================== -->
    <!-- DIÁLOGO: CREAR NUEVO HORARIO Y TURNOS      -->
    <!-- ========================================== -->
    <v-dialog v-model="dialogHorario" max-width="580" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-timetable</v-icon>
          <span>Nuevo Horario Laboral</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogHorario = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                v-model="formHorario.nombre"
                label="Nombre del Horario *"
                outlined
                dense
                placeholder="Ej. HORARIO CONTINUO SEDE CENTRAL"
              ></v-text-field>
            </v-col>

            <v-col cols="12" sm="6">
              <v-select
                v-model="formHorario.tipo"
                :items="['CONTINUO', 'DISCONTINUO', 'ESPECIAL']"
                label="Tipo de Jornada *"
                outlined
                dense
                @change="onTipoHorarioChange"
              ></v-select>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="formHorario.tolerancia_minutos"
                label="Tolerancia Ingreso (min) *"
                type="number"
                outlined
                dense
                min="0"
                max="60"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-divider class="my-3"></v-divider>
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold text-secondary text-uppercase">Intervalos y Turnos de la Jornada</span>
            <v-btn x-small color="primary" outlined class="rounded-pill" @click="agregarPeriodo">
              <v-icon x-small left>mdi-plus</v-icon> Agregar Turno
            </v-btn>
          </div>

          <div
            v-for="(p, idx) in formHorario.periodos"
            :key="idx"
            class="pa-3 mb-2 rounded turno-box"
          >
            <div class="d-flex align-center justify-space-between mb-1">
              <span class="text-caption font-weight-bold">Turno #{{ idx + 1 }}</span>
              <v-btn
                v-if="formHorario.periodos.length > 1"
                icon
                x-small
                color="error"
                @click="quitarPeriodo(idx)"
              >
                <v-icon x-small>mdi-trash-can-outline</v-icon>
              </v-btn>
            </div>
            <v-row dense>
              <v-col cols="6">
                <v-text-field
                  v-model="p.hora_inicio"
                  label="Hora Entrada"
                  type="time"
                  outlined
                  dense
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="p.hora_fin"
                  label="Hora Salida"
                  type="time"
                  outlined
                  dense
                  hide-details
                ></v-text-field>
              </v-col>
            </v-row>
          </div>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3 justify-end">
          <v-btn text class="text-capitalize rounded-pill" @click="dialogHorario = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="text-capitalize rounded-pill px-4"
            :loading="guardandoHorario"
            @click="guardarHorario"
          >
            Guardar Horario
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ========================================== -->
    <!-- DIÁLOGO: REGISTRAR FERIADO OFICIAL         -->
    <!-- ========================================== -->
    <v-dialog v-model="dialogFeriado" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calendar-star</v-icon>
          <span>Registrar Feriado Oficial</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogFeriado = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                v-model="formFer.nombre"
                label="Nombre del Feriado *"
                outlined
                dense
                placeholder="Ej. Día del Trabajo"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model.number="formFer.dia"
                label="Día *"
                type="number"
                outlined
                dense
                min="1"
                max="31"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-select
                v-model="formFer.mes"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes *"
                outlined
                dense
              ></v-select>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model.number="formFer.anio"
                label="Gestión (Año)"
                type="number"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-checkbox
                v-model="formFer.es_feriado_nacional"
                label="Feriado Nacional"
                dense
                hide-details
                class="mt-1"
              ></v-checkbox>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3 justify-end">
          <v-btn text class="text-capitalize rounded-pill" @click="dialogFeriado = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="text-capitalize rounded-pill px-4"
            :loading="guardandoFer"
            @click="guardarFeriado"
          >
            Guardar Feriado
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ========================================== -->
    <!-- DIÁLOGO: CONFIGURAR CORTE MENSUAL          -->
    <!-- ========================================== -->
    <v-dialog v-model="dialogCorte" max-width="500" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-calendar-sync</v-icon>
          <span>Configurar Período de Corte Mensual</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCorte = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-row dense>
            <v-col cols="6">
              <v-select
                v-model="formCor.mes"
                :items="meses"
                item-text="nombre"
                item-value="id"
                label="Mes *"
                outlined
                dense
              ></v-select>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model.number="formCor.gestion"
                label="Gestión *"
                type="number"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="formCor.fecha_inicio"
                label="Fecha Inicio *"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="formCor.fecha_fin"
                label="Fecha Fin *"
                type="date"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model.number="formCor.dias_habiles"
                label="Días Hábiles del Período"
                type="number"
                outlined
                dense
                min="1"
                max="31"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="px-4 py-3 justify-end">
          <v-btn text class="text-capitalize rounded-pill" @click="dialogCorte = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            class="text-capitalize rounded-pill px-4"
            :loading="guardandoCor"
            @click="guardarCorte"
          >
            Guardar Corte
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR DE NOTIFICACIONES -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="4000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn icon small text v-bind="attrs" @click="snackbar.status = false">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'JornadasCalendario',
  data() {
    return {
      cargandoGeneral: false,
      seccionActiva: 'horarios',
      filtroTipoHorario: 'TODOS',
      busquedaAsignacion: '',
      anioSeleccionado: new Date().getFullYear(),

      horarios: [],
      loadingHorarios: false,

      asignaciones: [],
      loadingAsignaciones: false,

      feriados: [],
      loadingFeriados: false,

      cortes: [],
      loadingCortes: false,

      personalList: [],

      // Modales y formularios
      dialogHorario: false,
      guardandoHorario: false,
      formHorario: {
        nombre: '',
        tipo: 'CONTINUO',
        tolerancia_minutos: 10,
        periodos: [
          { hora_inicio: '08:00', hora_fin: '16:30' },
        ],
      },

      dialogAsignar: false,
      guardandoAsig: false,
      horarioSeleccionado: null,
      formAsig: {
        id_horario: null,
        personas_ids: [],
        fecha_inicio: new Date().toISOString().substring(0, 10),
        permanente: true,
      },

      dialogFeriado: false,
      guardandoFer: false,
      formFer: {
        nombre: '',
        dia: 1,
        mes: new Date().getMonth() + 1,
        anio: new Date().getFullYear(),
        es_feriado_nacional: true,
      },

      dialogCorte: false,
      guardandoCor: false,
      formCor: {
        mes: new Date().getMonth() + 1,
        gestion: new Date().getFullYear(),
        fecha_inicio: '',
        fecha_fin: '',
        dias_habiles: 22,
      },

      headersAsignaciones: [
        { text: 'Funcionario', value: 'funcionario', sortable: true },
        { text: 'Horario Asignado', value: 'horario', sortable: true },
        { text: 'Vigencia de Turno', value: 'vigencia', sortable: false },
        { text: 'Tolerancia', value: 'tolerancia', sortable: false },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center' },
      ],

      headersCortes: [
        { text: 'Mes y Gestión', value: 'mes', sortable: true },
        { text: 'Período Computado', value: 'periodo', sortable: false },
        { text: 'Días Hábiles', value: 'dias_habiles', sortable: false },
        { text: 'Estado del Período', value: 'estado', sortable: true },
      ],

      meses: [
        { id: 1, nombre: 'Enero' },
        { id: 2, nombre: 'Febrero' },
        { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' },
        { id: 5, nombre: 'Mayo' },
        { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' },
        { id: 8, nombre: 'Agosto' },
        { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' },
        { id: 11, nombre: 'Noviembre' },
        { id: 12, nombre: 'Diciembre' },
      ],

      snackbar: {
        status: false,
        text: '',
        color: 'success',
      },
    };
  },

  computed: {
    totalPersonalAsignado() {
      return this.horarios.reduce((acc, h) => acc + (h.total_asignados || 0), 0);
    },

    horariosFiltrados() {
      if (this.filtroTipoHorario === 'TODOS') return this.horarios;
      return this.horarios.filter(h => h.tipo === this.filtroTipoHorario);
    },

    asignacionesFiltradas() {
      if (!this.busquedaAsignacion) return this.asignaciones;
      const q = this.busquedaAsignacion.toLowerCase();
      return this.asignaciones.filter(a => {
        const full = `${a.nombres || ''} ${a.primer_apellido || ''} ${a.segundo_apellido || ''} ${a.nro_documento || ''} ${a.horario_nombre || ''}`.toLowerCase();
        return full.includes(q);
      });
    },
  },

  mounted() {
    if (this.$route && this.$route.path.includes('feriados')) {
      this.seccionActiva = 'feriados';
    }
    this.recargarTodo();
    this.cargarPersonal();
  },

  methods: {
    recargarTodo() {
      this.cargarHorarios();
      this.cargarAsignaciones();
      this.cargarFeriados();
      this.cargarCortes();
    },

    alCambiarSeccion(val) {
      if (val === 'asignaciones' && this.asignaciones.length === 0) {
        this.cargarAsignaciones();
      } else if (val === 'feriados' && this.feriados.length === 0) {
        this.cargarFeriados();
      } else if (val === 'cortes' && this.cortes.length === 0) {
        this.cargarCortes();
      }
    },

    cargarHorarios() {
      this.loadingHorarios = true;
      axios.get('/api/rrhh/horarios')
        .then(res => {
          this.horarios = res.data?.data || [];
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar horarios.', 'error');
        })
        .finally(() => {
          this.loadingHorarios = false;
        });
    },

    cargarAsignaciones() {
      this.loadingAsignaciones = true;
      axios.get('/api/rrhh/asignaciones-horarios')
        .then(res => {
          this.asignaciones = res.data?.data || [];
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar asignaciones.', 'error');
        })
        .finally(() => {
          this.loadingAsignaciones = false;
        });
    },

    cargarFeriados() {
      this.loadingFeriados = true;
      axios.get(`/api/rrhh/feriados?anio=${this.anioSeleccionado}`)
        .then(res => {
          this.feriados = res.data?.data || [];
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar feriados.', 'error');
        })
        .finally(() => {
          this.loadingFeriados = false;
        });
    },

    cargarCortes() {
      this.loadingCortes = true;
      axios.get('/api/rrhh/fechas-corte')
        .then(res => {
          this.cortes = res.data?.data || [];
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al cargar fechas de corte.', 'error');
        })
        .finally(() => {
          this.loadingCortes = false;
        });
    },

    cargarPersonal() {
      axios.get('/api/rrhh/personal?limit=500')
        .then(res => {
          const list = res.data?.data?.data || res.data?.data || [];
          this.personalList = list.map(p => ({
            id: p.id,
            nombre_completo: `${p.primer_apellido || ''} ${p.segundo_apellido || ''} ${p.nombres || ''} (CI: ${p.nro_documento || 'S/N'})`.trim(),
          }));
        })
        .catch(() => {});
    },

    getColorTipoHorario(tipo) {
      switch (tipo) {
        case 'CONTINUO': return 'primary';
        case 'DISCONTINUO': return 'teal darken-1';
        case 'ESPECIAL': return 'deep-purple darken-1';
        default: return 'primary';
      }
    },

    getSiglaMes(mes) {
      const siglas = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
      return siglas[Number(mes) - 1] || 'MES';
    },

    abrirModalHorario() {
      this.formHorario = {
        nombre: '',
        tipo: 'CONTINUO',
        tolerancia_minutos: 10,
        periodos: [{ hora_inicio: '08:00', hora_fin: '16:30' }],
      };
      this.dialogHorario = true;
    },

    onTipoHorarioChange(val) {
      if (val === 'DISCONTINUO' && this.formHorario.periodos.length < 2) {
        this.formHorario.periodos = [
          { hora_inicio: '08:00', hora_fin: '12:00' },
          { hora_inicio: '14:00', hora_fin: '18:00' },
        ];
      } else if (val === 'CONTINUO') {
        this.formHorario.periodos = [
          { hora_inicio: '08:00', hora_fin: '16:30' },
        ];
      }
    },

    agregarPeriodo() {
      this.formHorario.periodos.push({ hora_inicio: '08:00', hora_fin: '12:00' });
    },

    quitarPeriodo(idx) {
      this.formHorario.periodos.splice(idx, 1);
    },

    guardarHorario() {
      if (!this.formHorario.nombre) {
        this.showSnackbar('Ingrese el nombre del horario.', 'warning');
        return;
      }
      this.guardandoHorario = true;
      axios.post('/api/rrhh/horarios', this.formHorario)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Horario creado exitosamente.', 'success');
          this.dialogHorario = false;
          this.cargarHorarios();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al guardar horario.', 'error');
        })
        .finally(() => {
          this.guardandoHorario = false;
        });
    },

    abrirAsignacionHorario(h) {
      this.horarioSeleccionado = h;
      this.formAsig.id_horario = h.id;
      this.formAsig.personas_ids = (h.asignados || []).map(p => p.id);
      this.formAsig.fecha_inicio = new Date().toISOString().substring(0, 10);
      this.formAsig.permanente = true;
      this.dialogAsignar = true;
    },

    abrirModalAsignacionDirecta() {
      if (this.horarios.length === 0) {
        this.showSnackbar('Debe crear al menos un horario antes de asignar personal.', 'warning');
        return;
      }
      this.abrirAsignacionHorario(this.horarios[0]);
    },

    abrirReasignarPersonal(item) {
      const h = this.horarios.find(x => x.nombre === item.horario_nombre) || this.horarios[0];
      if (h) {
        this.horarioSeleccionado = h;
        this.formAsig.id_horario = h.id;
        this.formAsig.personas_ids = [item.persona_id];
        this.formAsig.fecha_inicio = new Date().toISOString().substring(0, 10);
        this.formAsig.permanente = true;
        this.dialogAsignar = true;
      }
    },

    guardarAsignacionHorario() {
      if (!this.formAsig.personas_ids || this.formAsig.personas_ids.length === 0) {
        this.showSnackbar('Seleccione al menos un funcionario para asignar el horario.', 'warning');
        return;
      }
      this.guardandoAsig = true;
      axios.post('/api/rrhh/asignaciones-horarios', this.formAsig)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Horario asignado exitosamente.', 'success');
          this.dialogAsignar = false;
          this.cargarHorarios();
          this.cargarAsignaciones();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al asignar horario.', 'error');
        })
        .finally(() => {
          this.guardandoAsig = false;
        });
    },

    abrirModalFeriado() {
      this.dialogFeriado = true;
    },

    guardarFeriado() {
      if (!this.formFer.nombre) {
        this.showSnackbar('Ingrese el nombre del feriado.', 'warning');
        return;
      }
      this.guardandoFer = true;
      axios.post('/api/rrhh/feriados', this.formFer)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Feriado registrado exitosamente.', 'success');
          this.dialogFeriado = false;
          this.cargarFeriados();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al guardar feriado.', 'error');
        })
        .finally(() => {
          this.guardandoFer = false;
        });
    },

    abrirModalCorte() {
      this.dialogCorte = true;
    },

    guardarCorte() {
      this.guardandoCor = true;
      axios.post('/api/rrhh/fechas-corte', this.formCor)
        .then(res => {
          this.showSnackbar(res.data?.message || 'Periodo de corte guardado exitosamente.', 'success');
          this.dialogCorte = false;
          this.cargarCortes();
        })
        .catch(err => {
          this.showSnackbar(err.response?.data?.message || 'Error al registrar corte.', 'error');
        })
        .finally(() => {
          this.guardandoCor = false;
        });
    },

    formatearDiaMes(dia, mes) {
      const d = String(dia).padStart(2, '0');
      const m = this.getNombreMes(mes);
      return `${d} de ${m}`;
    },

    getNombreMes(idMes) {
      const item = this.meses.find(m => m.id === Number(idMes));
      return item ? item.nombre : 'Mes';
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
.gap-3 {
  gap: 12px;
}
.border-b {
  border-bottom: 1px solid rgba(128, 128, 128, 0.15);
}
.border-t {
  border-top: 1px solid rgba(128, 128, 128, 0.15);
}
.turno-box {
  background: rgba(0, 0, 0, 0.02);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.theme--dark .turno-box {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.07);
}
.card-kpi-activa {
  border: 2px solid var(--v-primary-base) !important;
}
.calendario-insignia {
  width: 52px;
  min-width: 52px;
  border-radius: 8px;
  border: 1px solid rgba(128, 128, 128, 0.2);
  background: rgba(0, 0, 0, 0.02);
}
.theme--dark .calendario-insignia {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.minitarjeta-feriado {
  transition: transform 0.15s ease-in-out;
}
.minitarjeta-feriado:hover {
  transform: translateY(-2px);
}
</style>
