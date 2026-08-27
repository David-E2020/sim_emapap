<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #reporte-print-area, #reporte-print-area * {
    visibility: visible;
  }
  #reporte-print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    background: white;
  }
}
.reporte-documento {
  border: 2px solid #1e293b;
  border-radius: 8px;
  padding: 28px;
  background: white;
  color: #0f172a;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.boleta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.firma-box {
  border-top: 1px solid #64748b;
  width: 220px;
  text-align: center;
  margin-top: 60px;
  padding-top: 8px;
  font-size: 0.8rem;
}
.tabla-reporte-print {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
.tabla-reporte-print th, .tabla-reporte-print td {
  border: 1px solid #cbd5e1;
  padding: 8px 10px;
  font-size: 0.85rem;
}
.tabla-reporte-print th {
  background-color: #f1f5f9;
  font-weight: bold;
}
</style>

<template>
  <div>
    <!-- CABECERA -->
    <v-card class="mb-5 py-3 px-4 erp-card-elevated" rounded="lg">
      <div class="d-flex align-center justify-space-between flex-wrap">
        <div class="d-flex align-center">
          <v-avatar color="primary" rounded="lg" class="mr-3 text-white elevation-2" size="46">
            <v-icon color="white">mdi-file-chart-outline</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-0">Centro de Reportes y Planillas Oficiales</h2>
            <span class="text-caption text-secondary">Planillas salariales, boletas de pago, padrón institucional, kardex, refrigerios y reportes personalizados</span>
          </div>
        </div>
      </div>
    </v-card>

    <!-- PESTAÑAS -->
    <v-card rounded="lg" class="erp-card-elevated">
      <v-tabs v-model="activeTab" color="primary" class="px-4 pt-2" show-arrows>
        <v-tab><v-icon left small>mdi-chart-box-outline</v-icon> Consolidado Asistencia</v-tab>
        <v-tab><v-icon left small>mdi-currency-usd</v-icon> Planilla de Sueldos</v-tab>
        <v-tab><v-icon left small>mdi-account-group</v-icon> Padrón de Personal</v-tab>
        <v-tab><v-icon left small>mdi-food</v-icon> Planilla Refrigerios</v-tab>
        <v-tab><v-icon left small>mdi-beach</v-icon> Kardex Vacaciones</v-tab>
        <v-tab><v-icon left small>mdi-tune</v-icon> Reportes Personalizados</v-tab>
      </v-tabs>

      <v-divider></v-divider>

      <v-card-text class="pa-4">
        <v-tabs-items v-model="activeTab">
          <!-- PESTAÑA 1: CONSOLIDADO DE ASISTENCIA -->
          <v-tab-item>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-center gap-2">
                <v-select v-model="filtroMes" :items="meses" item-text="nombre" item-value="id" label="Mes" outlined dense hide-details style="max-width: 140px;"></v-select>
                <v-text-field v-model="filtroAnio" label="Gestión" type="number" outlined dense hide-details style="max-width: 110px;"></v-text-field>
                <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarAsistenciaMensual()">
                  <v-icon left small>mdi-magnify</v-icon> Consultar
                </v-btn>
              </div>
            </div>

            <v-data-table :headers="headersAsistencia" :items="asistenciasConsolidado" :loading="loadingAsistencia" class="erp-table" dense>
              <template v-slot:item.dias_asistidos="{ item }">
                <v-chip small color="primary lighten-5" text-color="primary" label class="font-weight-bold">
                  {{ item.dias_asistidos }} / {{ item.dias_laborables }} días
                </v-chip>
              </template>
              <template v-slot:item.minutos_atraso="{ item }">
                <v-chip x-small :color="item.minutos_atraso > 0 ? 'error lighten-5 text--darken-2' : 'success lighten-5'" :text-color="item.minutos_atraso > 0 ? 'red' : 'green'" label class="font-weight-bold">
                  {{ item.minutos_atraso }} min
                </v-chip>
              </template>
            </v-data-table>
          </v-tab-item>

          <!-- PESTAÑA 2: PLANILLA DE SUELDOS Y SALARIOS -->
          <v-tab-item>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-center gap-2">
                <v-select v-model="filtroMes" :items="meses" item-text="nombre" item-value="id" label="Mes" outlined dense hide-details style="max-width: 140px;"></v-select>
                <v-text-field v-model="filtroAnio" label="Gestión" type="number" outlined dense hide-details style="max-width: 110px;"></v-text-field>
                <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarPlanillaSueldos()">
                  <v-icon left small>mdi-magnify</v-icon> Consultar
                </v-btn>

                <v-btn v-if="!esDeclarada && planillaSueldos.length > 0" color="purple" dark class="rounded-pill text-capitalize ml-2" :loading="cerrandoPlanilla" @click="cerrarYDeclarar()">
                  <v-icon left small>mdi-lock-check</v-icon> Cerrar y Declarar Planilla
                </v-btn>
              </div>

              <div class="d-flex align-center gap-3">
                <v-chip v-if="esDeclarada" color="purple lighten-5" text-color="purple darken-3" label class="font-weight-bold">
                  <v-icon left small color="purple darken-3">mdi-shield-lock</v-icon>
                  INMUTABLE (CITE: {{ citeOficial }})
                </v-chip>
                <v-chip v-else color="amber lighten-5" text-color="amber darken-4" label class="font-weight-bold">
                  <v-icon left small color="amber darken-4">mdi-timer-sand</v-icon>
                  BORRADOR (Simulación en Vivo)
                </v-chip>

                <v-card outlined class="pa-2 px-3 text-right bg-primary-light" rounded="lg">
                  <div class="text-caption text-secondary">Total Planilla Líquido:</div>
                  <div class="text-h6 font-weight-bold text-success">Bs. {{ Number(resumenSueldos.total_planilla_bs || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</div>
                </v-card>
              </div>
            </div>

            <v-data-table :headers="headersPlanillaSueldos" :items="planillaSueldos" :loading="loadingSueldos" class="erp-table" dense>
              <template v-slot:item.haber_basico="{ item }">
                <span>Bs. {{ Number(item.haber_basico).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
              </template>
              <template v-slot:item.bono_antiguedad="{ item }">
                <span class="text-primary font-weight-medium">Bs. {{ Number(item.bono_antiguedad).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }} ({{ item.anios_antiguedad }}a)</span>
              </template>
              <template v-slot:item.total_ganado="{ item }">
                <span class="font-weight-bold">Bs. {{ Number(item.total_ganado).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
              </template>
              <template v-slot:item.gestora_12_71="{ item }">
                <span class="error--text">Bs. {{ Number(item.gestora_12_71).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
              </template>
              <template v-slot:item.refrigerio_bs="{ item }">
                <span class="success--text font-weight-medium">+ Bs. {{ Number(item.refrigerio_bs).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
              </template>
              <template v-slot:item.liquido_pagable_total="{ item }">
                <v-chip small color="success lighten-5" text-color="green darken-3" label class="font-weight-bold">
                  Bs. {{ Number(item.liquido_pagable_total).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}
                </v-chip>
              </template>
              <template v-slot:item.acciones="{ item }">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon color="primary" small v-bind="attrs" v-on="on" @click="verBoletaPago(item)">
                      <v-icon small>mdi-receipt-text-outline</v-icon>
                    </v-btn>
                  </template>
                  <span>Ver / Imprimir Boleta de Pago</span>
                </v-tooltip>
              </template>
            </v-data-table>
          </v-tab-item>

          <!-- PESTAÑA 3: PADRÓN GENERAL DE PERSONAL -->
          <v-tab-item>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-center gap-2 flex-wrap">
                <v-text-field
                  v-model="busquedaPadron"
                  prepend-inner-icon="mdi-magnify"
                  placeholder="Buscar por nombre, CI o cargo..."
                  outlined
                  dense
                  hide-details
                  style="max-width: 280px;"
                  @input="cargarPadronPersonal()"
                ></v-text-field>

                <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarPadronPersonal()">
                  <v-icon left small>mdi-refresh</v-icon> Actualizar
                </v-btn>
              </div>

              <div>
                <v-btn color="secondary" outlined class="rounded-pill text-capitalize" @click="imprimirPadron()">
                  <v-icon left small>mdi-printer</v-icon> Imprimir Padrón
                </v-btn>
              </div>
            </div>

            <v-data-table :headers="headersPadron" :items="padronPersonal" :loading="loadingPadron" class="erp-table" dense>
              <template v-slot:item.tipo_contrato="{ item }">
                <v-chip small label :color="item.tipo_contrato === 'PLANTA' ? 'primary lighten-5' : 'orange lighten-5'" :text-color="item.tipo_contrato === 'PLANTA' ? 'primary' : 'orange darken-4'">
                  {{ item.tipo_contrato }}
                </v-chip>
              </template>
              <template v-slot:item.tiene_acceso_erp="{ item }">
                <v-chip x-small label :color="item.tiene_acceso_erp === 'SI' ? 'success lighten-5' : 'grey lighten-3'" :text-color="item.tiene_acceso_erp === 'SI' ? 'green darken-2' : 'grey darken-1'">
                  {{ item.tiene_acceso_erp === 'SI' ? 'ACTIVO' : 'SIN ACCESO' }}
                </v-chip>
              </template>
              <template v-slot:item.acciones="{ item }">
                <div class="d-flex">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon color="info" small v-bind="attrs" v-on="on" @click="verKardex(item)">
                        <v-icon small>mdi-card-account-details-outline</v-icon>
                      </v-btn>
                    </template>
                    <span>Ver Hoja de Vida / Kardex</span>
                  </v-tooltip>

                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon color="indigo" small v-bind="attrs" v-on="on" @click="verCertificadoTrabajo(item)">
                        <v-icon small>mdi-certificate-outline</v-icon>
                      </v-btn>
                    </template>
                    <span>Emitir Certificado Laboral</span>
                  </v-tooltip>
                </div>
              </template>
            </v-data-table>
          </v-tab-item>

          <!-- PESTAÑA 4: PLANILLA DE REFRIGERIOS -->
          <v-tab-item>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-center gap-2">
                <v-select v-model="filtroMes" :items="meses" item-text="nombre" item-value="id" label="Mes" outlined dense hide-details style="max-width: 140px;"></v-select>
                <v-text-field v-model="filtroAnio" label="Gestión" type="number" outlined dense hide-details style="max-width: 110px;"></v-text-field>
                <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarRefrigerioMensual()">
                  <v-icon left small>mdi-magnify</v-icon> Calcular
                </v-btn>
              </div>

              <div class="d-flex align-center gap-3">
                <v-card outlined class="pa-2 px-3 text-right bg-primary-light" rounded="lg">
                  <div class="text-caption text-secondary">Total Asignación Refrigerios:</div>
                  <div class="text-h6 font-weight-bold text-primary">Bs. {{ Number(totalRefrigerioBs || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</div>
                </v-card>
              </div>
            </div>

            <v-data-table :headers="headersRefrigerio" :items="planillaRefrigerio" :loading="loadingRefrigerio" class="erp-table" dense>
              <template v-slot:item.monto_total_bs="{ item }">
                <span class="font-weight-bold text-success">Bs. {{ Number(item.monto_total_bs).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</span>
              </template>
            </v-data-table>
          </v-tab-item>

          <!-- PESTAÑA 5: KARDEX DE VACACIONES -->
          <v-tab-item>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div class="d-flex align-center gap-2">
                <v-btn color="primary" class="rounded-pill text-capitalize" @click="cargarVacaciones()">
                  <v-icon left small>mdi-refresh</v-icon> Actualizar Saldos
                </v-btn>
              </div>
            </div>

            <v-data-table :headers="headersVacaciones" :items="kardexVacaciones" :loading="loadingVacaciones" class="erp-table" dense>
              <template v-slot:item.saldo_disponible="{ item }">
                <v-chip small color="primary lighten-5" text-color="primary" label class="font-weight-bold">
                  {{ item.saldo_disponible }} días disponibles
                </v-chip>
              </template>
            </v-data-table>
          </v-tab-item>

          <!-- PESTAÑA 6: GENERADOR DE REPORTES PERSONALIZADOS -->
          <v-tab-item>
            <v-card outlined class="pa-4 mb-4 rounded-lg">
              <div class="font-weight-bold text-subtitle-1 mb-2">
                <v-icon left color="primary">mdi-format-list-checks</v-icon>
                Constructor Dinámico: Seleccione los Campos del Reporte
              </div>
              <div class="text-caption text-secondary mb-3">Marque las columnas que desea incluir en la tabla personalizada y aplique filtros:</div>

              <div class="d-flex flex-wrap gap-2 mb-4">
                <v-chip
                  v-for="col in catalogoColumnas"
                  :key="col.id"
                  :input-value="columnasSeleccionadas.includes(col.id)"
                  filter
                  color="primary"
                  outlined
                  class="ma-1"
                  @click="toggleColumna(col.id)"
                >
                  {{ col.nombre }}
                </v-chip>
              </div>

              <div class="d-flex align-center gap-3 flex-wrap">
                <v-select
                  v-model="filtroPersonalizadoTipoContrato"
                  :items="['TODOS', 'PLANTA', 'EVENTUAL', 'CONSULTOR', 'PASANTE']"
                  label="Tipo de Contrato"
                  outlined
                  dense
                  hide-details
                  style="max-width: 180px;"
                ></v-select>

                <v-select
                  v-model="filtroPersonalizadoGenero"
                  :items="['TODOS', 'MASCULINO', 'FEMENINO']"
                  label="Género"
                  outlined
                  dense
                  hide-details
                  style="max-width: 160px;"
                ></v-select>

                <v-btn color="primary" class="rounded-pill text-capitalize" :loading="loadingPersonalizado" @click="ejecutarReportePersonalizado()">
                  <v-icon left small>mdi-play</v-icon> Generar Reporte
                </v-btn>

                <v-spacer></v-spacer>

                <v-btn v-if="datosPersonalizados.length > 0" color="secondary" outlined class="rounded-pill text-capitalize" @click="imprimirReportePersonalizado()">
                  <v-icon left small>mdi-printer</v-icon> Imprimir Reporte
                </v-btn>
              </div>
            </v-card>

            <v-data-table
              v-if="datosPersonalizados.length > 0"
              :headers="headersDinamicosPersonalizados"
              :items="datosPersonalizados"
              :loading="loadingPersonalizado"
              class="erp-table"
              dense
            ></v-data-table>
          </v-tab-item>
        </v-tabs-items>
      </v-card-text>
    </v-card>

    <!-- DIÁLOGO IMPRIMIBLE: BOLETA OFICIAL DE PAGO -->
    <v-dialog v-model="dialogBoletaPago" max-width="700">
      <v-card rounded="lg" v-if="boletaSeleccionada">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-receipt-text</v-icon>
          <span>Papeleta Oficial de Pago Salarial</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogBoletaPago = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento">
            <div class="d-flex align-center justify-space-between mb-3 border-b pb-2">
              <div>
                <div class="font-weight-bold text-subtitle-2">{{ boletaSeleccionada.institucion }}</div>
                <div class="text-caption text-secondary">BOLETA DE PAGO INDIVIDUAL - {{ boletaSeleccionada.periodo }}</div>
              </div>
              <div class="text-right">
                <div class="font-weight-bold text-caption text-primary">CITE: {{ boletaSeleccionada.boleta_nro }}</div>
                <div class="text-caption text-secondary">Emisión: {{ boletaSeleccionada.fecha_emision }}</div>
              </div>
            </div>

            <!-- DATOS DEL FUNCIONARIO -->
            <div class="pa-3 bg-light rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
              <div class="row dense">
                <div class="col-6"><strong>Funcionario:</strong> {{ boletaSeleccionada.funcionario.nombre_completo }}</div>
                <div class="col-6"><strong>C.I.:</strong> {{ boletaSeleccionada.funcionario.ci }}</div>
                <div class="col-6 mt-1"><strong>Cargo:</strong> {{ boletaSeleccionada.funcionario.cargo }}</div>
                <div class="col-6 mt-1"><strong>Ítem:</strong> {{ boletaSeleccionada.funcionario.item }}</div>
                <div class="col-12 mt-1"><strong>Unidad:</strong> {{ boletaSeleccionada.funcionario.unidad }}</div>
              </div>
            </div>

            <!-- DETALLE DE INGRESOS Y DESCUENTOS -->
            <div class="boleta-grid mt-3">
              <!-- INGRESOS -->
              <div>
                <div class="font-weight-bold text-caption text-primary mb-1 border-b pb-1">INGRESOS Y HABERES (Bs.)</div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Haber Básico:</span> <span>{{ Number(boletaSeleccionada.ingresos.haber_basico).toFixed(2) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Bono Antigüedad ({{ boletaSeleccionada.ingresos.porcentaje_antiguedad }}%):</span> <span>{{ Number(boletaSeleccionada.ingresos.bono_antiguedad).toFixed(2) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 font-weight-bold border-t mt-1 pt-1"><span>TOTAL GANADO:</span> <span>{{ Number(boletaSeleccionada.ingresos.total_ganado).toFixed(2) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 text-success"><span>+ Refrigerios ({{ boletaSeleccionada.ingresos.dias_refrigerio }} días):</span> <span>+ {{ Number(boletaSeleccionada.ingresos.refrigerios_bs).toFixed(2) }}</span></div>
              </div>

              <!-- DESCUENTOS -->
              <div>
                <div class="font-weight-bold text-caption text-error mb-1 border-b pb-1">DESCUENTOS DE LEY (Bs.)</div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Gestora Pública (12.71%):</span> <span>{{ Number(boletaSeleccionada.descuentos.gestora_12_71).toFixed(2) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1"><span>Descuento Atrasos ({{ boletaSeleccionada.descuentos.minutos_atraso }}m):</span> <span>{{ Number(boletaSeleccionada.descuentos.descuento_atraso).toFixed(2) }}</span></div>
                <div class="d-flex justify-space-between text-body-2 py-1 font-weight-bold border-t mt-1 pt-1 text-error"><span>TOTAL DESCUENTOS:</span> <span>{{ Number(boletaSeleccionada.descuentos.total_descuentos).toFixed(2) }}</span></div>
              </div>
            </div>

            <!-- TOTAL LIQUIDO -->
            <div class="pa-3 mt-4 text-center rounded" style="background-color: #ecfdf5; border: 2px solid #10b981;">
              <div class="text-caption text-secondary">LÍQUIDO TOTAL A PERCIBIR:</div>
              <div class="text-h5 font-weight-bold" style="color: #065f46;">Bs. {{ Number(boletaSeleccionada.liquido_pagable).toLocaleString('es-BO', { minimumFractionDigits: 2 }) }}</div>
            </div>

            <!-- FIRMAS -->
            <div class="d-flex justify-space-between mt-6 px-4">
              <div class="firma-box">Firma del Funcionario<br>C.I. {{ boletaSeleccionada.funcionario.ci }}</div>
              <div class="firma-box">Responsable de Recursos Humanos<br>EMAPA Central</div>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogBoletaPago = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Boleta
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO IMPRIMIBLE: CERTIFICADO DE TRABAJO -->
    <v-dialog v-model="dialogCertificado" max-width="700">
      <v-card rounded="lg" v-if="certificadoSeleccionado">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-certificate</v-icon>
          <span>Certificación Laboral Oficial</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogCertificado = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento text-justify" style="line-height: 1.8;">
            <div class="text-center mb-6">
              <div class="font-weight-bold text-subtitle-1">{{ certificadoSeleccionado.institucion }}</div>
              <div class="text-caption text-secondary">GERENCIA DE ADMINISTRACIÓN Y FINANZAS - RRHH</div>
              <div class="font-weight-bold text-h6 mt-4">CERTIFICADO DE TRABAJO</div>
              <div class="text-caption text-primary font-weight-bold">CITE: {{ certificadoSeleccionado.cite }}</div>
            </div>

            <p>La Responsable de Recursos Humanos de la <strong>Empresa de Apoyo a la Producción de Alimentos (EMAPA)</strong>, en uso de sus atribuciones y a solicitud de la parte interesada:</p>

            <p class="font-weight-bold text-center my-4">CERTIFICA:</p>

            <p>Que, revisados los antecedentes y el legajo digital institucional, se evidencia que el/la señor(a) <strong>{{ certificadoSeleccionado.funcionario }}</strong> con Cédula de Identidad N° <strong>{{ certificadoSeleccionado.ci }}</strong>, presta sus servicios en esta entidad desempeñando el cargo de <strong>{{ certificadoSeleccionado.cargo }}</strong> en la <strong>{{ certificadoSeleccionado.unidad }}</strong>, bajo la modalidad de <strong>{{ certificadoSeleccionado.tipo_contrato }}</strong> desde fecha <strong>{{ certificadoSeleccionado.fecha_ingreso }}</strong> hasta la fecha presente, habiendo demostrado idoneidad y responsabilidad en el ejercicio de sus funciones.</p>

            <p class="mt-4">Es cuanto se certifica en honor a la verdad y para los fines que convengan a la parte interesada.</p>

            <div class="text-right mt-6">La Paz, {{ certificadoSeleccionado.fecha_emision }}</div>

            <div class="d-flex justify-center mt-6">
              <div class="firma-box">Responsable de Recursos Humanos<br>EMAPA</div>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogCertificado = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Certificado
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIÁLOGO IMPRIMIBLE: KARDEX / HOJA DE VIDA -->
    <v-dialog v-model="dialogKardex" max-width="750">
      <v-card rounded="lg" v-if="kardexSeleccionado">
        <v-card-title class="primary white--text py-3">
          <v-icon left color="white">mdi-card-account-details</v-icon>
          <span>Kardex Institucional del Funcionario</span>
          <v-spacer></v-spacer>
          <v-btn icon dark x-small @click="dialogKardex = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <div id="reporte-print-area" class="reporte-documento">
            <div class="d-flex align-center justify-space-between mb-4 border-b pb-2">
              <div>
                <div class="font-weight-bold text-subtitle-1">{{ kardexSeleccionado.institucion }}</div>
                <div class="text-caption text-secondary">HOJA DE VIDA Y KARDEX INSTITUCIONAL</div>
              </div>
              <div class="text-right text-caption text-secondary">
                Fecha Emisión: {{ kardexSeleccionado.fecha_emision }}
              </div>
            </div>

            <!-- DATOS GENERALES -->
            <div class="pa-3 bg-light rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
              <div class="font-weight-bold text-caption text-primary mb-2">1. DATOS PERSONALES Y PUESTO ACTUAL</div>
              <div class="row dense">
                <div class="col-6"><strong>Nombre:</strong> {{ kardexSeleccionado.persona.nombre_completo }}</div>
                <div class="col-6"><strong>C.I.:</strong> {{ kardexSeleccionado.persona.nro_documento }}</div>
                <div class="col-6 mt-1"><strong>Cargo Actual:</strong> {{ kardexSeleccionado.puesto_actual ? kardexSeleccionado.puesto_actual.nombre : 'Sin Asignar' }}</div>
                <div class="col-6 mt-1"><strong>Unidad:</strong> {{ kardexSeleccionado.unidad ? kardexSeleccionado.unidad.nombre : 'Sin Unidad' }}</div>
                <div class="col-6 mt-1"><strong>Celular:</strong> {{ kardexSeleccionado.persona.telefono_celular || '-' }}</div>
                <div class="col-6 mt-1"><strong>Correo:</strong> {{ kardexSeleccionado.persona.correo_electronico_personal || '-' }}</div>
              </div>
            </div>

            <!-- ESTUDIOS ACADÉMICOS -->
            <div class="mb-3">
              <div class="font-weight-bold text-caption text-primary mb-1">2. FORMACIÓN ACADÉMICA</div>
              <table class="tabla-reporte-print">
                <thead><tr><th>Nivel</th><th>Carrera / Especialidad</th><th>Institución</th></tr></thead>
                <tbody>
                  <tr v-for="est in kardexSeleccionado.estudios" :key="est.id">
                    <td>{{ est.nivel_instruccion }}</td><td>{{ est.carrera }}</td><td>{{ est.institucion }}</td>
                  </tr>
                  <tr v-if="!kardexSeleccionado.estudios || kardexSeleccionado.estudios.length === 0"><td colspan="3" class="text-center font-italic text-secondary">Sin estudios registrados</td></tr>
                </tbody>
              </table>
            </div>

            <!-- AÑOS DE SERVICIO (CAS) -->
            <div class="mb-3">
              <div class="font-weight-bold text-caption text-primary mb-1">3. CALIFICACIÓN DE AÑOS DE SERVICIO (CAS)</div>
              <table class="tabla-reporte-print">
                <thead><tr><th>Resolución</th><th>Años Reconocidos</th><th>Meses</th><th>Fecha Emisión</th></tr></thead>
                <tbody>
                  <tr v-for="c in kardexSeleccionado.cas" :key="c.id">
                    <td>{{ c.nro_resolucion || 'N/D' }}</td><td>{{ c.anios }} años</td><td>{{ c.meses }} meses</td><td>{{ c.fecha_emision || '-' }}</td>
                  </tr>
                  <tr v-if="!kardexSeleccionado.cas || kardexSeleccionado.cas.length === 0"><td colspan="4" class="text-center font-italic text-secondary">Sin registros CAS</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="px-4 pb-3">
          <v-spacer></v-spacer>
          <v-btn text color="grey" @click="dialogKardex = false">Cerrar</v-btn>
          <v-btn color="primary" class="rounded-pill px-4" @click="imprimirArea()">
            <v-icon left small>mdi-printer</v-icon> Imprimir Kardex
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar.status" :color="snackbar.color" :timeout="4000" top right rounded="pill">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text small v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReportesRrhh',
  data() {
    const ahora = new Date();
    return {
      activeTab: 0,
      filtroMes: ahora.getMonth() + 1,
      filtroAnio: ahora.getFullYear(),
      meses: [
        { id: 1, nombre: 'Enero' }, { id: 2, nombre: 'Febrero' }, { id: 3, nombre: 'Marzo' },
        { id: 4, nombre: 'Abril' }, { id: 5, nombre: 'Mayo' }, { id: 6, nombre: 'Junio' },
        { id: 7, nombre: 'Julio' }, { id: 8, nombre: 'Agosto' }, { id: 9, nombre: 'Septiembre' },
        { id: 10, nombre: 'Octubre' }, { id: 11, nombre: 'Noviembre' }, { id: 12, nombre: 'Diciembre' },
      ],

      // TAB 1: Asistencias
      asistenciasConsolidado: [],
      loadingAsistencia: false,
      headersAsistencia: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Días Asistidos', value: 'dias_asistidos' },
        { text: 'Permisos', value: 'permisos' },
        { text: 'Comisiones', value: 'comisiones' },
        { text: 'Faltas', value: 'faltas' },
        { text: 'Minutos Atraso', value: 'minutos_atraso' },
      ],

      // TAB 2: Sueldos
      planillaSueldos: [],
      resumenSueldos: {},
      esDeclarada: false,
      citeOficial: '',
      loadingSueldos: false,
      cerrandoPlanilla: false,
      headersPlanillaSueldos: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Ítem', value: 'item' },
        { text: 'Haber Básico', value: 'haber_basico' },
        { text: 'Bono Antigüedad', value: 'bono_antiguedad' },
        { text: 'Total Ganado', value: 'total_ganado' },
        { text: 'Gestora (12.71%)', value: 'gestora_12_71' },
        { text: 'Refrigerios', value: 'refrigerio_bs' },
        { text: 'Líquido Pagable', value: 'liquido_pagable_total' },
        { text: 'Boleta', value: 'acciones', sortable: false, align: 'center', width: '70px' },
      ],

      // TAB 3: Padrón
      padronPersonal: [],
      loadingPadron: false,
      busquedaPadron: '',
      headersPadron: [
        { text: 'Funcionario', value: 'nombre_completo' },
        { text: 'C.I.', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Unidad Organizacional', value: 'unidad_organizacional' },
        { text: 'Tipo Contrato', value: 'tipo_contrato' },
        { text: 'Fecha Ingreso', value: 'fecha_ingreso' },
        { text: 'CAS', value: 'anios_cas' },
        { text: 'Acceso ERP', value: 'tiene_acceso_erp', align: 'center' },
        { text: 'Acciones', value: 'acciones', sortable: false, align: 'center', width: '100px' },
      ],

      // TAB 4: Refrigerios
      planillaRefrigerio: [],
      totalRefrigerioBs: 0,
      loadingRefrigerio: false,
      headersRefrigerio: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Cargo', value: 'cargo' },
        { text: 'Días Asistidos', value: 'dias_efectivos' },
        { text: 'Tarifa Diaria', value: 'tarifa_diaria' },
        { text: 'Total a Pagar (Bs.)', value: 'monto_total_bs' },
      ],

      // TAB 5: Vacaciones
      kardexVacaciones: [],
      loadingVacaciones: false,
      headersVacaciones: [
        { text: 'Funcionario', value: 'funcionario' },
        { text: 'CI', value: 'ci' },
        { text: 'Antigüedad (CAS)', value: 'anios_antiguedad' },
        { text: 'Días Derecho Anual', value: 'dias_derecho_anual' },
        { text: 'Días Utilizados', value: 'dias_utilizados' },
        { text: 'Saldo Disponible', value: 'saldo_disponible' },
      ],

      // TAB 6: Personalizados
      catalogoColumnas: [
        { id: 'nombres', nombre: 'Nombres y Apellidos' },
        { id: 'ci', nombre: 'Cédula de Identidad' },
        { id: 'cargo', nombre: 'Cargo / Puesto' },
        { id: 'item', nombre: 'N° de Ítem' },
        { id: 'unidad', nombre: 'Unidad Organizacional' },
        { id: 'tipo_contrato', nombre: 'Tipo de Contrato' },
        { id: 'genero', nombre: 'Género' },
        { id: 'celular', nombre: 'Teléfono / Celular' },
        { id: 'correo', nombre: 'Correo Electrónico' },
        { id: 'fecha_ingreso', nombre: 'Fecha de Ingreso' },
        { id: 'anios_cas', nombre: 'Años de Antigüedad (CAS)' },
        { id: 'formacion', nombre: 'Formación / Título' },
      ],
      columnasSeleccionadas: ['nombres', 'ci', 'cargo', 'unidad', 'tipo_contrato', 'celular'],
      filtroPersonalizadoTipoContrato: 'TODOS',
      filtroPersonalizadoGenero: 'TODOS',
      datosPersonalizados: [],
      loadingPersonalizado: false,

      // Modales y Reportes Imprimibles
      dialogBoletaPago: false,
      boletaSeleccionada: null,
      dialogCertificado: false,
      certificadoSeleccionado: null,
      dialogKardex: false,
      kardexSeleccionado: null,

      snackbar: { status: false, text: '', color: 'success' },
    };
  },
  computed: {
    headersDinamicosPersonalizados() {
      return this.columnasSeleccionadas.map(colId => {
        const cat = this.catalogoColumnas.find(c => c.id === colId);
        return {
          text: cat ? cat.nombre : colId,
          value: colId,
        };
      });
    },
  },
  mounted() {
    this.cargarAsistenciaMensual();
    this.cargarPlanillaSueldos();
    this.cargarPadronPersonal();
    this.cargarRefrigerioMensual();
    this.cargarVacaciones();
  },
  methods: {
    cargarAsistenciaMensual() {
      this.loadingAsistencia = true;
      axios.get(`/api/rrhh/reportes/asistencia-mensual?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) this.asistenciasConsolidado = res.data.data || [];
      }).finally(() => { this.loadingAsistencia = false; });
    },

    cargarPlanillaSueldos() {
      this.loadingSueldos = true;
      axios.get(`/api/rrhh/reportes/planilla-sueldos?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.planillaSueldos = res.data.data || [];
          this.esDeclarada = !!res.data.es_declarada;
          this.citeOficial = res.data.cite_oficial || '';
          this.resumenSueldos = {
            total_planilla_bs: res.data.total_planilla_bs,
            total_ganado_bs: res.data.total_ganado_bs,
            total_descuentos_bs: res.data.total_descuentos_bs,
          };
        }
      }).finally(() => { this.loadingSueldos = false; });
    },

    cerrarYDeclarar() {
      if (!confirm(`¿Confirma el cierre y declaración oficial de la planilla de ${this.filtroMes}/${this.filtroAnio}? Esta acción congelará los datos y generará un CITE inmutable.`)) {
        return;
      }
      this.cerrandoPlanilla = true;
      axios.post('/api/rrhh/reportes/cerrar-declarar-planilla', {
        mes: this.filtroMes,
        anio: this.filtroAnio,
      })
      .then(res => {
        this.showSnackbar(res.data.message || 'Planilla declarada y congelada exitosamente.', 'success');
        this.cargarPlanillaSueldos();
      })
      .catch(err => {
        this.showSnackbar(err.response?.data?.message || 'Error al cerrar planilla.', 'error');
      })
      .finally(() => {
        this.cerrandoPlanilla = false;
      });
    },

    cargarPadronPersonal() {
      this.loadingPadron = true;
      axios.get('/api/rrhh/reportes/padron-personal', {
        params: { search: this.busquedaPadron },
      }).then(res => {
        if (res.data && res.data.success) this.padronPersonal = res.data.data || [];
      }).finally(() => { this.loadingPadron = false; });
    },

    cargarRefrigerioMensual() {
      this.loadingRefrigerio = true;
      axios.get(`/api/rrhh/reportes/refrigerio-mensual?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.planillaRefrigerio = res.data.data || [];
          this.totalRefrigerioBs = res.data.total_general_bs || 0;
        }
      }).finally(() => { this.loadingRefrigerio = false; });
    },

    cargarVacaciones() {
      this.loadingVacaciones = true;
      axios.get('/api/rrhh/reportes/saldo-vacaciones').then(res => {
        if (res.data && res.data.success) this.kardexVacaciones = res.data.data || [];
      }).finally(() => { this.loadingVacaciones = false; });
    },

    toggleColumna(colId) {
      const idx = this.columnasSeleccionadas.indexOf(colId);
      if (idx > -1) {
        if (this.columnasSeleccionadas.length > 1) {
          this.columnasSeleccionadas.splice(idx, 1);
        }
      } else {
        this.columnasSeleccionadas.push(colId);
      }
    },

    ejecutarReportePersonalizado() {
      this.loadingPersonalizado = true;
      axios.post('/api/rrhh/reportes/generar-personalizado', {
        columnas: this.columnasSeleccionadas,
        tipo_contrato: this.filtroPersonalizadoTipoContrato === 'TODOS' ? null : this.filtroPersonalizadoTipoContrato,
        genero: this.filtroPersonalizadoGenero === 'TODOS' ? null : this.filtroPersonalizadoGenero,
      })
      .then(res => {
        if (res.data && res.data.success) {
          this.datosPersonalizados = res.data.data || [];
          this.showSnackbar(`Reporte generado: ${this.datosPersonalizados.length} registros`, 'success');
        }
      })
      .catch(() => {
        this.showSnackbar('Error al generar el reporte personalizado', 'error');
      })
      .finally(() => {
        this.loadingPersonalizado = false;
      });
    },

    verBoletaPago(item) {
      const id = item.id_persona || item.id;
      axios.get(`/api/rrhh/reportes/boleta-pago/${id}/html?mes=${this.filtroMes}&anio=${this.filtroAnio}`).then(res => {
        if (res.data && res.data.success) {
          this.boletaSeleccionada = res.data.data;
          this.dialogBoletaPago = true;
        }
      });
    },

    verCertificadoTrabajo(item) {
      axios.get(`/api/rrhh/reportes/certificado-trabajo/${item.id}/html`).then(res => {
        if (res.data && res.data.success) {
          this.certificadoSeleccionado = res.data.data;
          this.dialogCertificado = true;
        }
      });
    },

    verKardex(item) {
      axios.get(`/api/rrhh/reportes/kardex-funcionario/${item.id}/html`).then(res => {
        if (res.data && res.data.success) {
          this.kardexSeleccionado = res.data.data;
          this.dialogKardex = true;
        }
      });
    },

    imprimirArea() {
      window.print();
    },

    imprimirPadron() {
      window.print();
    },

    imprimirReportePersonalizado() {
      window.print();
    },

    showSnackbar(text, color = 'success') {
      this.snackbar = { status: true, text, color };
    },
  },
};
</script>
