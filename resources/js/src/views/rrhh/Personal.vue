<style scoped>
.personal-card-hover {
  transition: all 0.2s ease-in-out;
}
.personal-card-hover:hover {
  border-color: var(--v-primary-base);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
</style>

<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Personal y Legajos Digitales</h2>
            <span class="text-caption text-secondary">Padrón oficial de funcionarios, cargos asignados y expedientes laborales</span>
          </div>
        </div>

        <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
          <v-btn color="success" class="text-capitalize font-weight-medium rounded-pill mr-2" @click="abrirModalMigracionExcel()">
            <v-icon left small>mdi-file-excel</v-icon> Sincronizar desde Excel
          </v-btn>
          <v-btn color="primary" class="text-capitalize font-weight-medium rounded-pill" @click="abrirModalNuevo()">
            <v-icon left small>mdi-account-plus</v-icon> + Nuevo Funcionario
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
    <v-row class="mb-2">
      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">TOTAL FUNCIONARIOS</div>
            <div class="text-h4 font-weight-bold primary--text">{{ totalPersonal }}</div>
          </div>
          <v-avatar color="primary lighten-5" size="48">
            <v-icon color="primary">mdi-account-multiple</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">CON CARGO ASIGNADO</div>
            <div class="text-h4 font-weight-bold success--text">{{ personalConPuesto }}</div>
          </div>
          <v-avatar color="success lighten-5" size="48">
            <v-icon color="success">mdi-briefcase-check</v-icon>
          </v-avatar>
        </v-card>
      </v-col>

      <v-col cols="12" sm="4">
        <v-card rounded="lg" class="pa-4 erp-card-elevated d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-secondary font-weight-bold">CON ACCESO AL ERP</div>
            <div class="text-h4 font-weight-bold info--text">{{ personalConUsuario }}</div>
          </div>
          <v-avatar color="info lighten-5" size="48">
            <v-icon color="info">mdi-shield-account</v-icon>
          </v-avatar>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLA PRINCIPAL DE PERSONAL -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-card-title class="py-3 px-4 d-flex align-center justify-space-between flex-wrap gap-2">
        <div class="d-flex align-center">
          <v-icon color="primary" left>mdi-format-list-bulleted</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Listado General de Personal</span>
        </div>

        <div class="d-flex align-center" style="max-width: 320px; width: 100%;">
          <v-text-field
            v-model="search"
            placeholder="Buscar por CI o Nombre..."
            dense
            outlined
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            @input="debouncedBuscar"
          ></v-text-field>
        </div>
      </v-card-title>

      <v-divider></v-divider>

      <v-data-table
        :headers="headers"
        :items="items"
        :loading="loading"
        :server-items-length="totalPersonal"
        :options.sync="options"
        class="elevation-0"
        loading-text="Cargando funcionarios..."
        no-data-text="No se encontraron registros de personal"
      >
        <!-- AVATAR Y NOMBRE COMPLETO -->
        <template v-slot:item.nombre_completo="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar size="36" color="primary lighten-5" class="mr-3 font-weight-bold primary--text">
              {{ item.nombres ? item.nombres.charAt(0) : 'F' }}
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-2">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido }}</div>
              <div class="text-caption text-secondary">{{ item.correo_electronico_personal || 'Sin correo personal' }}</div>
            </div>
          </div>
        </template>

        <!-- DOCUMENTO DE IDENTIDAD -->
        <template v-slot:item.nro_documento="{ item }">
          <v-chip small label color="grey lighten-3" class="font-weight-bold">
            {{ item.tipo_documento || 'CI' }}: {{ item.nro_documento }}
          </v-chip>
        </template>

        <!-- CARGO / PUESTO ASIGNADO -->
        <template v-slot:item.puesto="{ item }">
          <div v-if="item.asignaciones_puestos && item.asignaciones_puestos.length > 0">
            <div class="font-weight-medium text-caption primary--text">
              {{ item.asignaciones_puestos[0].puesto ? item.asignaciones_puestos[0].puesto.nombre : 'Ítem Asignado' }}
            </div>
            <div class="text-caption text-secondary" style="font-size: 0.72rem !important;" v-if="item.asignaciones_puestos[0].puesto && item.asignaciones_puestos[0].puesto.unidad_organizacional">
              {{ item.asignaciones_puestos[0].puesto.unidad_organizacional.nombre }}
            </div>
          </div>
          <span v-else class="text-caption text-secondary font-italic">Sin Ítem Asignado</span>
        </template>

        <!-- CUENTA ERP -->
        <template v-slot:item.user="{ item }">
          <div class="d-flex align-center justify-center">
            <v-chip x-small :color="item.user ? 'success lighten-5 success--text' : 'grey lighten-3 grey--text text--darken-2'" class="font-weight-bold mr-1">
              <v-icon x-small left :color="item.user ? 'success' : 'grey'">{{ item.user ? 'mdi-check-circle' : 'mdi-close-circle' }}</v-icon>
              {{ item.user ? item.user.usr_usuario : 'Sin Cuenta' }}
            </v-chip>

            <!-- Botón para crear cuenta si no tiene -->
            <v-tooltip bottom v-if="!item.user">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  x-small
                  color="primary"
                  v-bind="attrs"
                  v-on="on"
                  @click="crearCuentaErpFuncionario(item)"
                >
                  <v-icon x-small>mdi-account-plus</v-icon>
                </v-btn>
              </template>
              <span>Crear cuenta ERP institucional</span>
            </v-tooltip>
          </div>
        </template>

        <!-- ACCIONES -->
        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="primary"
                  v-bind="attrs"
                  v-on="on"
                  @click="abrirFichaLegajo(item)"
                  class="mr-1"
                >
                  <v-icon small>mdi-folder-account-outline</v-icon>
                </v-btn>
              </template>
              <span>Ver Ficha y Legajo Digital</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- DIÁLOGO: NUEVO FUNCIONARIO -->
    <v-dialog v-model="dialogNuevo" max-width="640" persistent>
      <v-form v-model="formValido" ref="formPersonal" @submit.prevent="guardarFuncionario">
        <v-card rounded="lg">
          <v-card-title class="primary white--text py-3">
            <v-icon left color="white">mdi-account-plus</v-icon>
            <span>Registrar Nuevo Funcionario</span>
            <v-spacer></v-spacer>
            <v-btn icon dark x-small @click="dialogNuevo = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-text class="pt-5">
            <v-row dense>
              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.nombres"
                  label="Nombres *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-account"
                  :rules="[v => !!v || 'Nombres requeridos']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.primer_apellido"
                  label="Primer Apellido"
                  outlined
                  dense
                  prepend-inner-icon="mdi-account-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="4">
                <v-text-field
                  v-model="form.segundo_apellido"
                  label="Segundo Apellido"
                  outlined
                  dense
                  prepend-inner-icon="mdi-account-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.nro_documento"
                  label="C.I. / Documento *"
                  outlined
                  dense
                  required
                  prepend-inner-icon="mdi-card-account-details-outline"
                  :rules="[v => !!v || 'Documento requerido']"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.genero"
                  :items="generosList"
                  item-text="nombre"
                  item-value="codigo"
                  label="Género"
                  outlined
                  dense
                  prepend-inner-icon="mdi-gender-male-female"
                ></v-select>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.telefono_celular"
                  label="Teléfono / Celular"
                  outlined
                  dense
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.correo_electronico_personal"
                  label="Correo Personal"
                  outlined
                  dense
                  prepend-inner-icon="mdi-email-outline"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-divider class="my-2"></v-divider>
                <v-checkbox
                  v-model="form.crear_usuario"
                  label="Crear automáticamente cuenta de acceso al ERP para este funcionario"
                  dense
                  hide-details
                  color="primary"
                ></v-checkbox>

                <!-- Vista previa de credenciales institucionales en tiempo real -->
                <v-alert
                  v-if="form.crear_usuario"
                  dense
                  text
                  color="primary"
                  icon="mdi-shield-key-outline"
                  class="mt-3 mb-1 text-caption"
                  border="left"
                >
                  <div class="font-weight-bold mb-1">Patrón de Credenciales Institucionales:</div>
                  <div class="d-flex flex-wrap align-center">
                    <span class="mr-3">
                      <strong>Usuario:</strong> 
                      <code class="font-weight-bold primary--text">{{ credencialesPreview.usuario || 'PQJ4589201' }}</code>
                    </span>
                    <span>
                      <strong>Contraseña inicial:</strong> 
                      <code class="font-weight-bold success--text">{{ credencialesPreview.password || 'Pqj4589201!!' }}</code>
                    </span>
                  </div>
                  <div class="text-caption grey--text text--darken-2 mt-1">
                    * Formato: Iniciales (Primer Ap. + Segundo Ap. + 1er Nombre) + CI. Contraseña con inicial mayúscula + CI + "!!".
                  </div>
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="px-4 py-3">
            <v-spacer></v-spacer>
            <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogNuevo = false">Cancelar</v-btn>
            <v-btn
              color="primary"
              elevation="1"
              :loading="guardando"
              :disabled="guardando || !formValido"
              type="submit"
              class="text-capitalize px-4"
            >
              Registrar
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- MODAL: CREDENCIALES INSTITUCIONALES GENERADAS -->
    <v-dialog v-model="dialogCredenciales" max-width="480" persistent>
      <v-card rounded="lg">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-shield-account-outline</v-icon>
          <span>Credenciales ERP Generadas</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCredenciales = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <div class="text-center mb-4">
            <v-avatar color="primary lighten-5" size="56" class="mb-2">
              <v-icon size="32" color="primary">mdi-account-key</v-icon>
            </v-avatar>
            <h3 class="text-subtitle-1 font-weight-bold">{{ credencialesGeneradas.nombre }}</h3>
            <span class="text-caption text-secondary">Acceso al Sistema ERP EMAPAP</span>
          </div>

          <v-sheet color="grey lighten-4" rounded="lg" class="pa-4 mb-3 border">
            <!-- USUARIO -->
            <div class="mb-3">
              <div class="text-caption grey--text text--darken-2 font-weight-bold mb-1">USUARIO INSTITUCIONAL:</div>
              <div class="d-flex align-center justify-space-between white pa-2 rounded border">
                <span class="text-h6 font-weight-bold primary--text font-monospace">{{ credencialesGeneradas.usuario }}</span>
                <v-btn icon small color="primary" @click="copiarTexto(credencialesGeneradas.usuario, 'Usuario copiado al portapapeles')">
                  <v-icon small>mdi-content-copy</v-icon>
                </v-btn>
              </div>
            </div>

            <!-- CONTRASEÑA -->
            <div>
              <div class="text-caption grey--text text--darken-2 font-weight-bold mb-1">CONTRASEÑA TEMPORAL:</div>
              <div class="d-flex align-center justify-space-between white pa-2 rounded border">
                <span class="text-subtitle-1 font-weight-bold font-monospace">
                  {{ mostrarPassword ? credencialesGeneradas.password : '••••••••••••' }}
                </span>
                <div>
                  <v-btn icon small @click="mostrarPassword = !mostrarPassword" class="mr-1">
                    <v-icon small>{{ mostrarPassword ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
                  </v-btn>
                  <v-btn icon small color="success" @click="copiarTexto(credencialesGeneradas.password, 'Contraseña copiada al portapapeles')">
                    <v-icon small>mdi-content-copy</v-icon>
                  </v-btn>
                </div>
              </div>
            </div>
          </v-sheet>

          <v-alert dense outlined type="info" class="text-caption mb-0">
            <strong>Instrucciones:</strong> Entregue estas credenciales iniciales al funcionario. El usuario deberá cambiar su contraseña en su primer inicio de sesión.
          </v-alert>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" class="px-5 rounded-pill" @click="dialogCredenciales = false">
            Entendido
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO NATIVO: CONFIRMAR CREACIÓN DE CUENTA ERP -->
    <v-dialog v-model="dialogConfirmarCrearCuenta" max-width="480" persistent>
      <v-card rounded="lg" v-if="personaParaCuenta">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-account-plus</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Crear Cuenta ERP Institucional</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogConfirmarCrearCuenta = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-5">
          <div class="d-flex align-center mb-4">
            <v-avatar color="primary lighten-5" size="48" class="mr-3">
              <v-icon color="primary">mdi-account-outline</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-subtitle-1">{{ personaParaCuenta.nombre_completo }}</div>
              <div class="text-caption text-secondary">
                Documento: <strong>{{ personaParaCuenta.nro_documento }}</strong>
              </div>
            </div>
          </div>

          <v-alert
            dense
            outlined
            type="info"
            class="text-body-2 mb-3"
          >
            Se generará automáticamente la cuenta de acceso al ERP EMAPAP bajo el <strong>estándar institucional</strong> (Iniciales + C.I.).
          </v-alert>

          <p class="text-caption grey--text text--darken-2 mb-0">
            Al confirmar, se mostrarán las credenciales generadas para que pueda copiarlas y entregarlas al funcionario.
          </p>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogConfirmarCrearCuenta = false">
            Cancelar
          </v-btn>
          <v-btn
            color="primary"
            elevation="1"
            class="text-capitalize px-4"
            :loading="creandoCuenta"
            @click="confirmarCrearCuentaErp"
          >
            <v-icon left small>mdi-account-plus</v-icon>
            Generar Cuenta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: FICHA Y LEGAJO DIGITAL -->
    <v-dialog v-model="dialogFicha" max-width="800" persistent scrollable>
      <v-card rounded="lg" v-if="personaSeleccionada">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-folder-account</v-icon>
          <span>Legajo Digital: {{ personaSeleccionada.nombres }} {{ personaSeleccionada.primer_apellido }}</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogFicha = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-tabs v-model="tabFicha" color="primary" grow>
            <v-tab><v-icon left small>mdi-account-details</v-icon> Datos Generales</v-tab>
            <v-tab><v-icon left small>mdi-briefcase</v-icon> Datos Laborales</v-tab>
            <v-tab><v-icon left small>mdi-school</v-icon> Estudios</v-tab>
            <v-tab><v-icon left small>mdi-certificate</v-icon> CAS</v-tab>
          </v-tabs>

          <v-tabs-items v-model="tabFicha" class="mt-4">
            <!-- TAB 1: DATOS GENERALES -->
            <v-tab-item>
              <v-row dense>
                <v-col cols="6"><div class="text-caption text-secondary">Nombres:</div><div class="font-weight-bold">{{ personaSeleccionada.nombres }}</div></v-col>
                <v-col cols="6"><div class="text-caption text-secondary">Apellidos:</div><div class="font-weight-bold">{{ personaSeleccionada.primer_apellido }} {{ personaSeleccionada.segundo_apellido }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">C.I.:</div><div class="font-weight-bold">{{ personaSeleccionada.nro_documento }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">Celular:</div><div class="font-weight-bold">{{ personaSeleccionada.telefono_celular || 'No registrado' }}</div></v-col>
                <v-col cols="6" class="mt-2"><div class="text-caption text-secondary">Correo:</div><div class="font-weight-bold">{{ personaSeleccionada.correo_electronico_personal || 'No registrado' }}</div></v-col>
                <v-col cols="6" class="mt-2">
                  <div class="text-caption text-secondary">Género:</div>
                  <div class="font-weight-bold">
                    {{ personaSeleccionada.genero === 'FEMENINO' || personaSeleccionada.genero === 'F' ? 'Femenino' : (personaSeleccionada.genero === 'MASCULINO' || personaSeleccionada.genero === 'M' ? 'Masculino' : (personaSeleccionada.genero || 'No especificado')) }}
                  </div>
                </v-col>
              </v-row>
            </v-tab-item>

            <!-- TAB 2: DATOS LABORALES (HISTORIAL DE CARGOS Y DESVINCULACIONES) -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.datos_laborales && personaSeleccionada.ficha_personal.datos_laborales.length > 0">
                <v-card v-for="d in personaSeleccionada.ficha_personal.datos_laborales" :key="d.id" outlined class="pa-3 mb-3 rounded-lg" :class="!d.es_puesto_anterior ? 'lighten-5' : ''">
                  <div class="d-flex align-center justify-space-between mb-1">
                    <div class="font-weight-bold text-subtitle-2 primary--text">{{ d.cargo }}</div>
                    <div>
                      <v-chip x-small label :color="!d.es_puesto_anterior ? 'success lighten-5 success--text' : 'grey lighten-3 grey--text text--darken-2'" class="font-weight-bold mr-1">
                        {{ !d.es_puesto_anterior ? 'ACTUAL / VIGENTE' : 'CONCLUIDO / ANTERIOR' }}
                      </v-chip>
                      <v-chip x-small label :color="getChipColorMovimiento(d.tipo_movimiento)" class="font-weight-bold">
                        {{ d.tipo_movimiento || 'DESIGNACIÓN' }}
                      </v-chip>
                    </div>
                  </div>
                  <div class="text-caption text-secondary">
                    <strong>Unidad:</strong> {{ d.unidad_organizacional || 'No especificada' }} • <strong>Tipo:</strong> {{ d.tipo_funcionario || 'PLANTA' }} • <strong>Ítem:</strong> #{{ d.nro_item || '-' }}
                  </div>
                  <div class="text-caption mt-1">
                    <v-icon x-small color="grey">mdi-calendar-range</v-icon>
                    <strong>Periodo:</strong> {{ d.fecha_ingreso || 'Sin fecha' }} al {{ d.fecha_desvinculacion || 'Presente (En funciones)' }}
                    <span v-if="d.nro_documento" class="ml-2 font-weight-medium">
                      • <strong>Doc. Respaldo:</strong> {{ d.nro_documento }}
                    </span>
                  </div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin historial laboral registrado.
              </div>
            </v-tab-item>

            <!-- TAB 3: ESTUDIOS -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.estudios_academicos && personaSeleccionada.ficha_personal.estudios_academicos.length > 0">
                <v-card v-for="e in personaSeleccionada.ficha_personal.estudios_academicos" :key="e.id" outlined class="pa-3 mb-2 rounded-lg">
                  <div class="font-weight-bold text-body-2">{{ e.carrera }} - {{ e.nivel_instruccion }}</div>
                  <div class="text-caption text-secondary">Institución: {{ e.institucion }}</div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin estudios académicos registrados.
              </div>
            </v-tab-item>

            <!-- TAB 4: CAS -->
            <v-tab-item>
              <div v-if="personaSeleccionada.ficha_personal && personaSeleccionada.ficha_personal.cas && personaSeleccionada.ficha_personal.cas.length > 0">
                <v-card v-for="c in personaSeleccionada.ficha_personal.cas" :key="c.id" outlined class="pa-3 mb-2 rounded-lg">
                  <div class="font-weight-bold text-body-2">Resolución: {{ c.nro_resolucion }}</div>
                  <div class="text-caption text-secondary">Años de Servicio: {{ c.anios }} años, {{ c.meses }} meses, {{ c.dias }} días</div>
                </v-card>
              </div>
              <div v-else class="text-center py-6 text-caption text-secondary font-italic">
                Sin certificación CAS registrada.
              </div>
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn color="primary" elevation="1" class="text-capitalize px-4" @click="dialogFicha = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO: SINCRONIZACIÓN Y MIGRACIÓN DESDE EXCEL DE PLANILLAS -->
    <v-dialog v-model="dialogExcel" max-width="950" persistent scrollable>
      <v-card rounded="lg">
        <v-card-title class="success white--text py-3 d-flex align-center">
          <v-icon left color="white">mdi-file-excel-box</v-icon>
          <span class="text-subtitle-1 font-weight-bold">Sincronización de Personal y Planilla desde Excel</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogExcel = false" :disabled="migrandoExcel">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-alert dense outlined type="info" class="text-caption mb-3">
            <strong>Garantía de Integridad:</strong> Esta herramienta detecta el personal, cargos, escalas salariales y fechas de ingreso desde el libro oficial de planillas. Aplica sincronización inteligente (<code>updateOrCreate</code>) identificando por C.I., asegurando <strong>cero pérdida de datos</strong> en funcionarios previamente registrados.
          </v-alert>

          <!-- SELECCIÓN DE ORIGEN DEL ARCHIVO -->
          <v-sheet outlined rounded="lg" class="pa-3 mb-4 grey lighten-5">
            <div class="text-caption font-weight-bold text-secondary mb-2">ORIGEN DEL ARCHIVO EXCEL (.XLSX)</div>
            <v-radio-group v-model="origenExcel" row dense hide-details class="mt-0" @change="onCambioOrigenExcel">
              <v-radio label="Archivo oficial del servidor (PLANILLA DE SUELDOS SEPTIEMBRE.xlsx)" value="servidor" color="success"></v-radio>
              <v-radio label="Subir otro archivo Excel (.xlsx)" value="archivo" color="success"></v-radio>
            </v-radio-group>

            <v-file-input
              v-if="origenExcel === 'archivo'"
              v-model="archivoExcelSeleccionado"
              accept=".xlsx,.xls"
              label="Seleccionar archivo Excel de Planilla"
              dense
              outlined
              prepend-icon="mdi-file-excel"
              class="mt-3"
              hide-details
              @change="onArchivoExcelCambiado"
            ></v-file-input>

            <div class="d-flex align-center mt-3">
              <v-btn
                color="success"
                small
                elevation="1"
                class="text-capitalize rounded-pill px-4"
                :loading="analizandoExcel"
                @click="previsualizarExcel"
              >
                <v-icon left small>mdi-table-search</v-icon> Analizar / Previsualizar
              </v-btn>
              <span class="text-caption text-secondary ml-3" v-if="datosExcelPrevia">
                Detectados: <strong>{{ datosExcelPrevia.totales.total_general_personas }} personas</strong> ({{ datosExcelPrevia.totales.planta_count }} planta, {{ datosExcelPrevia.totales.eventual_count }} eventual, {{ datosExcelPrevia.totales.directorio_count }} directorio).
              </span>
            </div>
          </v-sheet>

          <!-- CONTENIDO DE LA PREVISUALIZACIÓN -->
          <div v-if="analizandoExcel" class="text-center py-8">
            <v-progress-circular indeterminate color="success" size="48"></v-progress-circular>
            <div class="text-caption text-secondary mt-2">Analizando hojas, cargos, salarios y fórmulas del Excel...</div>
          </div>

          <div v-else-if="datosExcelPrevia">
            <!-- CHIPS DE RESUMEN -->
            <v-row dense class="mb-3">
              <v-col cols="12" sm="4">
                <v-card outlined class="pa-2 text-center rounded-lg green lighten-5">
                  <div class="text-caption font-weight-bold green--text text--darken-2">PLANTA PERMANENTE</div>
                  <div class="text-h6 font-weight-bold green--text text--darken-3">{{ datosExcelPrevia.totales.planta_count }} funcionarios</div>
                  <div class="text-caption text-secondary">Total Ganado: Bs {{ formatoMoneda(datosExcelPrevia.totales.total_ganado_planta) }}</div>
                </v-card>
              </v-col>
              <v-col cols="12" sm="4">
                <v-card outlined class="pa-2 text-center rounded-lg blue lighten-5">
                  <div class="text-caption font-weight-bold blue--text text--darken-2">PERSONAL EVENTUAL</div>
                  <div class="text-h6 font-weight-bold blue--text text--darken-3">{{ datosExcelPrevia.totales.eventual_count }} funcionario</div>
                  <div class="text-caption text-secondary">Total Ganado: Bs {{ formatoMoneda(datosExcelPrevia.totales.total_ganado_eventual) }}</div>
                </v-card>
              </v-col>
              <v-col cols="12" sm="4">
                <v-card outlined class="pa-2 text-center rounded-lg amber lighten-5">
                  <div class="text-caption font-weight-bold amber--text text--darken-3">DIRECTORIO EMAPA</div>
                  <div class="text-h6 font-weight-bold amber--text text--darken-4">{{ datosExcelPrevia.totales.directorio_count }} miembros</div>
                  <div class="text-caption text-secondary">Total Dietas: Bs {{ formatoMoneda(datosExcelPrevia.totales.total_dietas_directorio) }}</div>
                </v-card>
              </v-col>
            </v-row>

            <!-- TABS DE DETALLE PREVIO -->
            <v-tabs v-model="tabExcelPrevia" color="success" dense>
              <v-tab>Planta Permanente ({{ datosExcelPrevia.datos.planta.length }})</v-tab>
              <v-tab>Personal Eventual ({{ datosExcelPrevia.datos.eventual.length }})</v-tab>
              <v-tab>Directorio ({{ datosExcelPrevia.datos.directorio.length }})</v-tab>
            </v-tabs>

            <v-tabs-items v-model="tabExcelPrevia" class="pt-2">
              <v-tab-item>
                <v-simple-table dense class="elevation-1 rounded-lg">
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th>Ítem</th>
                        <th>Funcionario</th>
                        <th>C.I.</th>
                        <th>Cargo</th>
                        <th>Unidad Organizacional</th>
                        <th class="text-right">Haber Básico</th>
                        <th class="text-right">Líquido Pagable</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in datosExcelPrevia.datos.planta" :key="p.ci">
                        <td><v-chip x-small color="primary" class="font-weight-bold">{{ p.item }}</v-chip></td>
                        <td class="font-weight-medium">{{ p.nombre_completo }}</td>
                        <td>{{ p.ci }}</td>
                        <td>{{ p.cargo }}</td>
                        <td><span class="text-caption text-secondary">{{ p.unidad_nombre }}</span></td>
                        <td class="text-right font-weight-bold">Bs {{ formatoMoneda(p.haber_basico) }}</td>
                        <td class="text-right success--text font-weight-bold">Bs {{ formatoMoneda(p.liquido_pagable) }}</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <v-tab-item>
                <v-simple-table dense class="elevation-1 rounded-lg">
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th>Ítem</th>
                        <th>Funcionario</th>
                        <th>C.I.</th>
                        <th>Cargo</th>
                        <th>Unidad Organizacional</th>
                        <th class="text-right">Haber Básico</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in datosExcelPrevia.datos.eventual" :key="e.ci">
                        <td><v-chip x-small color="info" class="font-weight-bold">{{ e.item }}</v-chip></td>
                        <td class="font-weight-medium">{{ e.nombre_completo }}</td>
                        <td>{{ e.ci }}</td>
                        <td>{{ e.cargo }}</td>
                        <td><span class="text-caption text-secondary">{{ e.unidad_nombre }}</span></td>
                        <td class="text-right font-weight-bold">Bs {{ formatoMoneda(e.haber_basico) }}</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>

              <v-tab-item>
                <v-simple-table dense class="elevation-1 rounded-lg">
                  <template v-slot:default>
                    <thead>
                      <tr class="grey lighten-4">
                        <th>Ítem</th>
                        <th>Nombre</th>
                        <th>C.I.</th>
                        <th>Cargo</th>
                        <th class="text-right">Dieta Base</th>
                        <th class="text-right">Total Dietas</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="d in datosExcelPrevia.datos.directorio" :key="d.nombre_completo">
                        <td><v-chip x-small color="amber darken-2" dark class="font-weight-bold">{{ d.item }}</v-chip></td>
                        <td class="font-weight-medium">{{ d.nombre_completo }}</td>
                        <td>{{ d.ci || 'Ex-officio' }}</td>
                        <td>{{ d.cargo }}</td>
                        <td class="text-right">Bs {{ formatoMoneda(d.haber_basico) }}</td>
                        <td class="text-right font-weight-bold">Bs {{ formatoMoneda(d.total_dietas) }}</td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-tab-item>
            </v-tabs-items>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 py-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" class="text-capitalize" @click="dialogExcel = false" :disabled="migrandoExcel">
            Cerrar
          </v-btn>
          <v-btn
            color="success"
            elevation="1"
            class="text-capitalize px-4"
            :loading="migrandoExcel"
            :disabled="!datosExcelPrevia"
            @click="ejecutarMigracionExcel"
          >
            <v-icon left small>mdi-database-import</v-icon>
            Ejecutar Sincronización Sin Pérdida de Datos
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" timeout="3000" top right>
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
export default {
  name: 'Personal',
  data() {
    return {
      items: [],
      totalPersonal: 0,
      loading: false,
      search: '',
      options: {},
      debounceTimeout: null,

      dialogNuevo: false,
      formValido: false,
      guardando: false,
      form: {
        nombres: '',
        primer_apellido: '',
        segundo_apellido: '',
        nro_documento: '',
        genero: 'MASCULINO',
        telefono_celular: '',
        correo_electronico_personal: '',
        crear_usuario: true,
      },

      dialogFicha: false,
      personaSeleccionada: null,
      tabFicha: 0,

      generosList: [
        { codigo: 'MASCULINO', nombre: 'Masculino' },
        { codigo: 'FEMENINO', nombre: 'Femenino' },
      ],
      expedidosList: ['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD', 'EX'],
      nivelesInstruccionList: ['PRIMARIA', 'SECUNDARIA', 'TECNICO_MEDIO', 'TECNICO_SUPERIOR', 'LICENCIATURA', 'DIPLOMADO', 'MAESTRIA', 'DOCTORADO'],
      tiposContratoList: ['PLANTA', 'EVENTUAL', 'CONSULTOR', 'PASANTE'],

      headers: [
        { text: 'Funcionario', value: 'nombre_completo' },
        { text: 'Documento', value: 'nro_documento', width: '140px' },
        { text: 'Cargo / Puesto Actual', value: 'puesto' },
        { text: 'Teléfono', value: 'telefono_celular', width: '130px' },
        { text: 'Acceso ERP', value: 'user', width: '130px', align: 'center' },
        { text: 'Acciones', value: 'acciones', sortable: false, width: '90px', align: 'center' },
      ],

      dialogCredenciales: false,
      dialogConfirmarCrearCuenta: false,
      personaParaCuenta: null,
      creandoCuenta: false,
      mostrarPassword: true,
      credencialesGeneradas: {
        nombre: '',
        usuario: '',
        password: '',
      },

      // Sincronización Excel Planillas
      dialogExcel: false,
      origenExcel: 'servidor',
      archivoExcelSeleccionado: null,
      analizandoExcel: false,
      datosExcelPrevia: null,
      tabExcelPrevia: 0,
      migrandoExcel: false,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    personalConPuesto() {
      return this.items.filter(i => i.asignaciones_puestos && i.asignaciones_puestos.length > 0).length;
    },
    personalConUsuario() {
      return this.items.filter(i => i.user).length;
    },
    credencialesPreview() {
      const p1 = (this.form.primer_apellido || '').trim().charAt(0);
      const p2 = (this.form.segundo_apellido || '').trim().charAt(0);
      const nombres = (this.form.nombres || '').trim();
      const primerNombre = nombres ? nombres.split(/\s+/)[0] : '';
      const p3 = primerNombre ? primerNombre.charAt(0) : '';
      const iniciales = (p1 + p2 + p3).toUpperCase();
      const ci = (this.form.nro_documento || '').replace(/[^A-Za-z0-9]/g, '');
      if (!iniciales && !ci) return { usuario: '', password: '' };
      const user = (iniciales || 'USR') + (ci || '');
      const title = iniciales ? iniciales.charAt(0).toUpperCase() + iniciales.slice(1).toLowerCase() : 'Usr';
      const pass = title + (ci || '') + '!!';
      return { usuario: user, password: pass };
    },
  },
  watch: {
    options: {
      handler() {
        this.cargarPersonal();
      },
      deep: true,
    },
  },
  mounted() {
    this.cargarPersonal();
    this.cargarParametricas();
  },
  methods: {
    cargarParametricas() {
      axios.get('/api/parametrica-api/TABLA_RRHH_GENERO').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.generosList = res.data.map(c => {
            let code = c.param_codigo;
            if (code === 'M') code = 'MASCULINO';
            if (code === 'F') code = 'FEMENINO';
            return { codigo: code, nombre: c.param_nombre };
          });
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_EXPEDIDO_DOC').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.expedidosList = res.data.map(c => c.param_codigo);
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_NIVEL_INSTRUCCION').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.nivelesInstruccionList = res.data.map(c => c.param_codigo);
        }
      });
      axios.get('/api/parametrica-api/TABLA_RRHH_TIPO_CONTRATO').then(res => {
        if (Array.isArray(res.data) && res.data.length > 0) {
          this.tiposContratoList = res.data.map(c => c.param_codigo);
        }
      });
    },
    cargarPersonal() {
      this.loading = true;
      const { page, itemsPerPage } = this.options;

      axios
        .get('/api/rrhh/personal', {
          params: {
            page: page || 1,
            per_page: itemsPerPage || 15,
            search: this.search,
          },
        })
        .then(res => {
          this.loading = false;
          if (res.data && res.data.success) {
            this.items = res.data.data || [];
            this.totalPersonal = res.data.total || 0;
          }
        })
        .catch(() => {
          this.loading = false;
          this.showSnackbar('Error al cargar la lista de personal', 'error');
        });
    },

    debouncedBuscar() {
      clearTimeout(this.debounceTimeout);
      this.debounceTimeout = setTimeout(() => {
        this.options.page = 1;
        this.cargarPersonal();
      }, 400);
    },

    abrirModalNuevo() {
      this.form = {
        nombres: '',
        primer_apellido: '',
        segundo_apellido: '',
        nro_documento: '',
        genero: 'MASCULINO',
        telefono_celular: '',
        correo_electronico_personal: '',
        crear_usuario: true,
      };
      this.dialogNuevo = true;
    },

    guardarFuncionario() {
      if (!this.$refs.formPersonal.validate()) return;
      this.guardando = true;

      axios
        .post('/api/rrhh/personal', this.form)
        .then(res => {
          this.guardando = false;
          this.dialogNuevo = false;
          this.showSnackbar(res.data.message || 'Funcionario registrado', 'success');
          this.cargarPersonal();

          if (res.data && res.data.credenciales) {
            this.credencialesGeneradas = {
              nombre: (res.data.data ? res.data.data.nombre_completo : '') || 'Funcionario',
              usuario: res.data.credenciales.usuario,
              password: res.data.credenciales.password,
            };
            this.mostrarPassword = true;
            this.dialogCredenciales = true;
          }
        })
        .catch(err => {
          this.guardando = false;
          const msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Error al registrar funcionario';
          this.showSnackbar(msg, 'error');
        });
    },

    crearCuentaErpFuncionario(item) {
      this.personaParaCuenta = item;
      this.dialogConfirmarCrearCuenta = true;
    },

    confirmarCrearCuentaErp() {
      if (!this.personaParaCuenta) return;
      this.creandoCuenta = true;
      axios
        .post(`/api/rrhh/personal/${this.personaParaCuenta.id}/crear-usuario`)
        .then(res => {
          this.creandoCuenta = false;
          this.dialogConfirmarCrearCuenta = false;
          if (res.data && res.data.success) {
            this.showSnackbar(res.data.message, 'success');
            this.cargarPersonal();
            if (res.data.credenciales) {
              this.credencialesGeneradas = {
                nombre: res.data.credenciales.nombre || this.personaParaCuenta.nombre_completo,
                usuario: res.data.credenciales.usuario,
                password: res.data.credenciales.password,
              };
              this.mostrarPassword = true;
              this.dialogCredenciales = true;
            }
          }
        })
        .catch(err => {
          this.creandoCuenta = false;
          this.dialogConfirmarCrearCuenta = false;
          const msg = (err.response && err.response.data && err.response.data.message) || 'Error al crear usuario ERP';
          this.showSnackbar(msg, 'error');
        });
    },

    copiarTexto(texto, mensaje) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(() => {
          this.showSnackbar(mensaje, 'success');
        }).catch(() => {
          this.showSnackbar('No se pudo copiar automáticamente', 'warning');
        });
      } else {
        const input = document.createElement('textarea');
        input.value = texto;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        this.showSnackbar(mensaje, 'success');
      }
    },

    abrirFichaLegajo(item) {
      axios
        .get('/api/rrhh/personal/' + item.id)
        .then(res => {
          if (res.data && res.data.success) {
            this.personaSeleccionada = res.data.data;
            this.tabFicha = 0;
            this.dialogFicha = true;
          }
        })
        .catch(() => {
          this.showSnackbar('Error al cargar el legajo del funcionario', 'error');
        });
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },

    getChipColorMovimiento(mov) {
      const map = {
        DESIGNACION: 'primary lighten-5 primary--text',
        TRANSFERENCIA: 'info lighten-5 info--text',
        PROMOCION: 'purple lighten-5 purple--text',
        RENUNCIA: 'amber lighten-4 amber--text text--darken-4',
        DESTITUCION: 'red lighten-5 red--text',
        DESPIDO: 'red lighten-5 red--text',
        CONCLUSION_CONTRATO: 'orange lighten-5 orange--text',
        JUBILACION: 'teal lighten-5 teal--text',
      };
      return map[mov] || 'grey lighten-3 grey--text';
    },

    abrirModalMigracionExcel() {
      this.dialogExcel = true;
      this.origenExcel = 'servidor';
      this.archivoExcelSeleccionado = null;
      this.datosExcelPrevia = null;
      this.tabExcelPrevia = 0;
      this.previsualizarExcel();
    },

    onCambioOrigenExcel(val) {
      if (val === 'servidor') {
        this.archivoExcelSeleccionado = null;
        this.previsualizarExcel();
      } else {
        this.datosExcelPrevia = null;
      }
    },

    onArchivoExcelCambiado(file) {
      if (file) {
        this.previsualizarExcel();
      } else {
        this.datosExcelPrevia = null;
      }
    },

    previsualizarExcel() {
      this.analizandoExcel = true;
      const formData = new FormData();

      if (this.origenExcel === 'archivo') {
        if (!this.archivoExcelSeleccionado) {
          this.analizandoExcel = false;
          this.showSnackbar('Debe seleccionar un archivo Excel para analizar', 'warning');
          return;
        }
        formData.append('archivo_excel', this.archivoExcelSeleccionado);
      } else {
        formData.append('usar_archivo_servidor', '1');
      }

      axios
        .post('/api/rrhh/personal/previsualizar-excel', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then(res => {
          this.analizandoExcel = false;
          if (res.data && res.data.success) {
            this.datosExcelPrevia = res.data;
          } else {
            this.showSnackbar(res.data.message || 'Error al analizar el Excel', 'error');
          }
        })
        .catch(err => {
          this.analizandoExcel = false;
          const msg = err.response?.data?.message || 'Error al conectar con el analizador de Excel';
          this.showSnackbar(msg, 'error');
        });
    },

    ejecutarMigracionExcel() {
      if (!this.datosExcelPrevia) return;
      this.migrandoExcel = true;
      const formData = new FormData();

      if (this.origenExcel === 'archivo' && this.archivoExcelSeleccionado) {
        formData.append('archivo_excel', this.archivoExcelSeleccionado);
      } else {
        formData.append('usar_archivo_servidor', '1');
      }

      axios
        .post('/api/rrhh/personal/importar-excel', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then(res => {
          this.migrandoExcel = false;
          if (res.data && res.data.success) {
            this.dialogExcel = false;
            this.showSnackbar(res.data.message, 'success');
            this.cargarPersonal();
          } else {
            this.showSnackbar(res.data.message || 'Error al importar datos', 'error');
          }
        })
        .catch(err => {
          this.migrandoExcel = false;
          const msg = err.response?.data?.message || 'Error al ejecutar la sincronización';
          this.showSnackbar(msg, 'error');
        });
    },

    formatoMoneda(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00';
      return parseFloat(val).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
  },
};
</script>
