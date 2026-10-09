<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\ConfiguracionLaboral;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\SolicitudSalida;
use App\Services\Contabilidad\ReporteFinancieroPdfService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReporteRrhhController extends Controller
{
    /**
     * Boleta oficial de salida imprimible (HTML / PDF Ready).
     */
    public function boletaSalidaHtml(int $id): JsonResponse
    {
        $solicitud = SolicitudSalida::with(['permiso'])->findOrFail($id);

        $asignacion = DB::table('rrhh.usuarios_solicitudes_salidas as uss')
            ->join('rrhh.personas as p', 'p.id', '=', 'uss.id_persona')
            ->leftJoin('rrhh.asignaciones_puestos as ap', function ($j) {
                $j->on('ap.id_persona', '=', 'p.id')->where('ap._estado', '=', 'ACTIVO');
            })
            ->leftJoin('rrhh.puestos as puesto', 'puesto.id', '=', 'ap.id_puesto')
            ->leftJoin('rrhh.unidades_organizacionales as uo', 'uo.id', '=', 'puesto.id_unidad_organizacional')
            ->where('uss.id_solicitud_salida', $id)
            ->select(
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nro_documento',
                'puesto.nombre as cargo',
                'uo.nombre as unidad',
                'uss.estado_aprobacion',
                'uss.fecha_revision'
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'solicitud' => $solicitud,
                'funcionario' => $asignacion,
                'fecha_emision' => now()->format('d/m/Y H:i:s'),
                'institucion' => 'EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA',
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Reporte consolidado de asistencia mensual.
     */
    public function asistenciaMensual(Request $request): JsonResponse
    {
        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));

        $personas = Persona::with([
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
            },
        ])->get();

        $reporte = $personas->map(function ($p) {
            $totalAsistencias = $p->asistencias->count();
            $totalMinutosAtraso = $p->asistencias->sum('minutos_de_atraso_primer_periodo') + $p->asistencias->sum('minutos_de_atraso_segundo_periodo');
            $totalRefrigerios = $p->asistencias->where('merece_refrigerio', true)->count();

            $puesto = $p->asignacionesPuestos->first();

            return [
                'id' => $p->id,
                'funcionario' => $p->nombre_completo,
                'nro_documento' => $p->nro_documento,
                'cargo' => $puesto?->puesto?->nombre ?? 'Sin Asignación',
                'unidad' => $puesto?->puesto?->unidadOrganizacional?->nombre ?? 'N/A',
                'dias_asistidos' => $totalAsistencias,
                'minutos_atraso' => $totalMinutosAtraso,
                'refrigerios_ganados' => $totalRefrigerios,
            ];
        });

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'data' => $reporte,
        ], Response::HTTP_OK);
    }

    /**
     * Planilla mensual de refrigerios con cálculo económico.
     */
    public function refrigerioMensual(Request $request): JsonResponse
    {
        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));
        $tarifaDiaria = (float) $request->query('tarifa', 18.0); // Tarifa oficial estándar Bs. 18

        $personas = Persona::with([
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes)->where('merece_refrigerio', true);
            },
        ])->get();

        $planilla = $personas->map(function ($p) use ($tarifaDiaria) {
            $diasRefrigerio = $p->asistencias->count();
            $totalBs = $diasRefrigerio * $tarifaDiaria;
            $puesto = $p->asignacionesPuestos->first();

            return [
                'id' => $p->id,
                'funcionario' => $p->nombre_completo,
                'ci' => $p->nro_documento,
                'cargo' => $puesto?->puesto?->nombre ?? 'N/A',
                'dias_efectivos' => $diasRefrigerio,
                'tarifa_diaria' => $tarifaDiaria,
                'monto_total_bs' => round($totalBs, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'tarifa_diaria' => $tarifaDiaria,
            'total_general_bs' => $planilla->sum('monto_total_bs'),
            'data' => $planilla,
        ], Response::HTTP_OK);
    }

    /**
     * Kardex y Saldo de Vacaciones según escala de antigüedad.
     */
    public function saldoVacaciones(): JsonResponse
    {
        $personas = Persona::with(['fichaPersonal.cas', 'asignacionesPuestos.puesto'])->get();

        $kardex = $personas->map(function ($p) {
            $cas = $p->fichaPersonal?->cas?->first();
            $aniosServicio = $cas ? (int) $cas->anios : 1;

            // Escala Legal Bolivia
            $diasDerecho = 15;
            if ($aniosServicio >= 5 && $aniosServicio < 10) {
                $diasDerecho = 20;
            } elseif ($aniosServicio >= 10) {
                $diasDerecho = 30;
            }

            // Consultar días ya tomados en solicitudes aprobadas
            $diasTomados = DB::table('rrhh.usuarios_solicitudes_salidas as uss')
                ->join('rrhh.solicitudes_salidas as ss', 'ss.id', '=', 'uss.id_solicitud_salida')
                ->join('rrhh.permisos as perm', 'perm.id', '=', 'ss.id_permiso')
                ->where('uss.id_persona', $p->id)
                ->where('perm.sigla', 'VAC')
                ->where('uss.estado_aprobacion', 'APROBADO')
                ->sum('ss.horas_solicitadas') / 8.0;

            $saldoDisponible = max(0, $diasDerecho - $diasTomados);

            return [
                'id' => $p->id,
                'funcionario' => $p->nombre_completo,
                'ci' => $p->nro_documento,
                'anios_antiguedad' => $aniosServicio,
                'dias_derecho_anual' => $diasDerecho,
                'dias_utilizados' => round((float) $diasTomados, 1),
                'saldo_disponible' => round($saldoDisponible, 1),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $kardex,
        ], Response::HTTP_OK);
    }

    /**
     * Planilla General Mensual de Sueldos y Salarios (Normativa Laboral Bolivia).
     * Si la planilla ya fue declarada/consolidada, devuelve el snapshot inmutable congelado.
     * Si no ha sido declarada, calcula la simulación en vivo con las paramétricas vigentes.
     */
    public function planillaSueldosMensual(Request $request): JsonResponse
    {
        $mes = (int) ($request->get('mes') ?: $request->query('mes', date('m')));
        $anio = (int) ($request->get('anio') ?: $request->query('anio', date('Y')));
        $tipoPlanilla = strtoupper((string) ($request->get('tipo_planilla') ?: $request->query('tipo_planilla', 'PLANTA_PERMANENTE')));

        // Normalizar tipos
        $tipoConsolidada = match ($tipoPlanilla) {
            'PERSONAL_EVENTUAL', 'EVENTUAL' => 'PERSONAL_EVENTUAL',
            'DIETAS_DIRECTORIO', 'DIRECTORIO' => 'DIETAS_DIRECTORIO',
            'TODOS', 'TODAS', 'CONSOLIDADO_GENERAL' => 'CONSOLIDADO_GENERAL',
            default => 'PLANTA_PERMANENTE',
        };

        $configLaboralCheck = ConfiguracionLaboral::obtenerVigente($anio);
        $isPercentScale = ((float)($configLaboralCheck?->porcentaje_gestora_total ?? 12.71) > 0.5);
        $normRate = fn($val, $fallback = 0.0) => (float) (
            $val !== null && $val !== ''
                ? ($isPercentScale ? round(((float)$val) / 100, 6) : round((float)$val, 6))
                : $fallback
        );

        // 1. Caso CONSOLIDADO_GENERAL (Todas las planillas): si ya existen planillas registradas para el mes
        if ($tipoConsolidada === 'CONSOLIDADO_GENERAL') {
            $planillasExistentes = DB::table('rrhh.planillas_consolidadas')
                ->where('gestion', $anio)
                ->where('mes', $mes)
                ->where('_estado', 'ACTIVO')
                ->get();

            $tiposEsperados = ['PLANTA_PERMANENTE', 'PERSONAL_EVENTUAL', 'DIETAS_DIRECTORIO'];
            $tiposPresentes = $planillasExistentes->pluck('tipo_planilla')->unique()->all();
            $todasCompletas = count(array_intersect($tiposEsperados, $tiposPresentes)) >= 3;
            $alMenosUnaDeclarada = $planillasExistentes->whereIn('estado', ['DECLARADA', 'CONSOLIDADA'])->isNotEmpty();

            if ($planillasExistentes->isNotEmpty() && ($todasCompletas || $alMenosUnaDeclarada)) {
                $ids = $planillasExistentes->pluck('id');
                $detalles = DB::table('rrhh.detalles_planillas')
                    ->whereIn('id_planilla_consolidada', $ids)
                    ->get();

                $totalGanadoPlanta = (float) $detalles->where('gestora_12_71', '>', 0)->sum('total_ganado');
                $configLaboral = ConfiguracionLaboral::obtenerVigente($anio);
                $rateCnsPat = $normRate($configLaboral?->porcentaje_patronal_cns, 0.10);
                $rateGestoraPatTotal = $normRate($configLaboral?->porcentaje_patronal_gestora_total, 0.0721);
                $rateRiesgoPat = $normRate($configLaboral?->porcentaje_patronal_gestora_riesgo, 0.0171);
                $rateViviendaPat = $normRate($configLaboral?->porcentaje_patronal_gestora_pro_vivienda, 0.02);
                $rateSolidarioPat = $normRate($configLaboral?->porcentaje_patronal_gestora_sol, 0.03);
                $rateComisionPat = 0.0050;

                $rateVejez = $normRate($configLaboral?->porcentaje_gestora_vejez, 0.10);
                $rateRiesgo = $normRate($configLaboral?->porcentaje_gestora_riesgo_comun, 0.0171);
                $rateComision = $normRate($configLaboral?->porcentaje_gestora_comision, 0.0050);
                $rateSolidario = $normRate($configLaboral?->porcentaje_gestora_solidario, 0.0050);

                $cnsPatronal = round($totalGanadoPlanta * $rateCnsPat, 2);
                $gestoraPatronal = round($totalGanadoPlanta * $rateGestoraPatTotal, 2);
                $patRiesgo = round($totalGanadoPlanta * $rateRiesgoPat, 2);
                $patVivienda = round($totalGanadoPlanta * $rateViviendaPat, 2);
                $patSol = round($totalGanadoPlanta * $rateSolidarioPat, 2);
                $patComision = round($totalGanadoPlanta * $rateComisionPat, 2);
                $totalPatronal = round($cnsPatronal + $gestoraPatronal, 2);

                $totalGanado = (float) $planillasExistentes->sum('total_ganado_bs');
                $totalDescuentos = (float) $planillasExistentes->sum('total_descuentos_bs');
                $totalLiquido = (float) $planillasExistentes->sum('total_liquido_pagable_bs');
                $costoTotalEmpresa = round($totalGanado + $totalPatronal, 2);
                $todasDeclaradas = $planillasExistentes->every(fn($p) => in_array($p->estado, ['DECLARADA', 'CONSOLIDADA']));

                $detalles = $detalles->map(function ($d) use ($rateVejez, $rateRiesgo, $rateComision, $rateSolidario) {
                    $tg = (float) $d->total_ganado;
                    $arr = (array) $d;
                    $arr['desglose_gestora'] = [
                        'vejez_10' => round($tg * $rateVejez, 2),
                        'riesgo_1_71' => round($tg * $rateRiesgo, 2),
                        'comision_0_5' => round($tg * $rateComision, 2),
                        'solidario_0_5' => round($tg * $rateSolidario, 2),
                    ];
                    return $arr;
                });

                return response()->json([
                    'success' => true,
                    'mes' => $mes,
                    'anio' => $anio,
                    'tipo_planilla' => 'CONSOLIDADO_GENERAL',
                    'es_declarada' => $todasDeclaradas,
                    'estado_planilla' => $todasDeclaradas ? 'DECLARADA' : 'BORRADOR',
                    'cite_oficial' => 'CONSOLIDADO-GENERAL-' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . "-{$anio}",
                    'total_planilla_bs' => $totalLiquido,
                    'total_liquido_salarial_bs' => (float) $detalles->sum('liquido_salarial'),
                    'total_ganado_bs' => $totalGanado,
                    'total_descuentos_bs' => $totalDescuentos,
                    'total_refrigerio_bs' => (float) $detalles->sum('refrigerio_bs'),
                    'totales' => [
                        'total_ganado_bs' => $totalGanado,
                        'total_descuentos_bs' => $totalDescuentos,
                        'total_liquido_bs' => (float) $detalles->sum('liquido_salarial'),
                        'total_refrigerio_bs' => (float) $detalles->sum('refrigerio_bs'),
                        'total_planilla_bs' => $totalLiquido,
                    ],
                    'patronal' => [
                        'cns_10_bs' => $cnsPatronal,
                        'gestora_7_21_bs' => $gestoraPatronal,
                        'riesgo_profesional_1_71_bs' => $patRiesgo,
                        'pro_vivienda_2_bs' => $patVivienda,
                        'solidario_patronal_3_bs' => $patSol,
                        'comision_patronal_0_5_bs' => $patComision,
                        'total_patronal_bs' => $totalPatronal,
                        'costo_total_empresa_bs' => $costoTotalEmpresa,
                    ],
                    'data' => $detalles->values()->all(),
                ], Response::HTTP_OK);
            }
        }

        // 2. Verificar si ya existe una planilla específica CONSOLIDADA / DECLARADA
        $planillaExistente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where(function ($q) use ($tipoConsolidada) {
                $q->where('tipo_planilla', $tipoConsolidada)
                  ->orWhere('tipo_planilla', 'SUELDOS_Y_SALARIOS');
            })
            ->where('_estado', 'ACTIVO')
            ->first();

        if ($planillaExistente) {
            $detalles = DB::table('rrhh.detalles_planillas')
                ->where('id_planilla_consolidada', $planillaExistente->id)
                ->get();

            $totalGanadoPlanta = (float) $detalles->where('gestora_12_71', '>', 0)->sum('total_ganado');
            if ($totalGanadoPlanta === 0.0 && $tipoConsolidada === 'PLANTA_PERMANENTE') {
                $totalGanadoPlanta = (float) $detalles->sum('total_ganado');
            }
            $configLaboral = ConfiguracionLaboral::obtenerVigente($anio);
            $rateCnsPat = $normRate($configLaboral?->porcentaje_patronal_cns, 0.10);
            $rateGestoraPatTotal = $normRate($configLaboral?->porcentaje_patronal_gestora_total, 0.0721);
            $rateRiesgoPat = $normRate($configLaboral?->porcentaje_patronal_gestora_riesgo, 0.0171);
            $rateViviendaPat = $normRate($configLaboral?->porcentaje_patronal_gestora_pro_vivienda, 0.02);
            $rateSolidarioPat = $normRate($configLaboral?->porcentaje_patronal_gestora_sol, 0.03);
            $rateComisionPat = 0.0050;

            $rateVejez = $normRate($configLaboral?->porcentaje_gestora_vejez, 0.10);
            $rateRiesgo = $normRate($configLaboral?->porcentaje_gestora_riesgo_comun, 0.0171);
            $rateComision = $normRate($configLaboral?->porcentaje_gestora_comision, 0.0050);
            $rateSolidario = $normRate($configLaboral?->porcentaje_gestora_solidario, 0.0050);

            $cnsPatronal = round($totalGanadoPlanta * $rateCnsPat, 2);
            $gestoraPatronal = round($totalGanadoPlanta * $rateGestoraPatTotal, 2);
            $patRiesgo = round($totalGanadoPlanta * $rateRiesgoPat, 2);
            $patVivienda = round($totalGanadoPlanta * $rateViviendaPat, 2);
            $patSol = round($totalGanadoPlanta * $rateSolidarioPat, 2);
            $patComision = round($totalGanadoPlanta * $rateComisionPat, 2);
            $totalPatronal = round($cnsPatronal + $gestoraPatronal, 2);
            $costoTotalEmpresa = round((float) $planillaExistente->total_ganado_bs + $totalPatronal, 2);

            $detalles = $detalles->map(function ($d) use ($rateVejez, $rateRiesgo, $rateComision, $rateSolidario) {
                $tg = (float) $d->total_ganado;
                $arr = (array) $d;
                $arr['desglose_gestora'] = [
                    'vejez_10' => round($tg * $rateVejez, 2),
                    'riesgo_1_71' => round($tg * $rateRiesgo, 2),
                    'comision_0_5' => round($tg * $rateComision, 2),
                    'solidario_0_5' => round($tg * $rateSolidario, 2),
                ];
                return $arr;
            });

            return response()->json([
                'success' => true,
                'mes' => $mes,
                'anio' => $anio,
                'tipo_planilla' => $tipoConsolidada,
                'es_declarada' => ($planillaExistente->estado === 'DECLARADA'),
                'estado_planilla' => $planillaExistente->estado,
                'cite_oficial' => $planillaExistente->cite_oficial ?: ($planillaExistente->estado === 'DECLARADA' ? "PLA-EMAPA-{$tipoConsolidada}-" . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . "-{$anio}" : null),
                'fecha_cierre' => $planillaExistente->fecha_cierre,
                'smn_aplicado' => (float) $planillaExistente->smn_aplicado,
                'total_planilla_bs' => (float) $planillaExistente->total_liquido_pagable_bs,
                'total_liquido_salarial_bs' => (float) $detalles->sum('liquido_salarial'),
                'total_ganado_bs' => (float) $planillaExistente->total_ganado_bs,
                'total_descuentos_bs' => (float) $planillaExistente->total_descuentos_bs,
                'total_refrigerio_bs' => (float) $detalles->sum('refrigerio_bs'),
                'totales' => [
                    'total_ganado_bs' => (float) $planillaExistente->total_ganado_bs,
                    'total_descuentos_bs' => (float) $planillaExistente->total_descuentos_bs,
                    'total_liquido_bs' => (float) $detalles->sum('liquido_salarial'),
                    'total_refrigerio_bs' => (float) $detalles->sum('refrigerio_bs'),
                    'total_planilla_bs' => (float) $planillaExistente->total_liquido_pagable_bs,
                ],
                'patronal' => [
                    'cns_10_bs' => $cnsPatronal,
                    'gestora_7_21_bs' => $gestoraPatronal,
                    'riesgo_profesional_1_71_bs' => $patRiesgo,
                    'pro_vivienda_2_bs' => $patVivienda,
                    'solidario_patronal_3_bs' => $patSol,
                    'comision_patronal_0_5_bs' => $patComision,
                    'total_patronal_bs' => $totalPatronal,
                    'costo_total_empresa_bs' => $costoTotalEmpresa,
                ],
                'data' => $detalles->values()->all(),
            ], Response::HTTP_OK);
        }

        // 2. Si no está cerrada, calcular la simulación en vivo con la configuración laboral de RRHH vigente
        $configLaboral = ConfiguracionLaboral::obtenerVigente($anio);

        $smn = (float) ($request->query('smn') ?: $configLaboral->salario_minimo_nacional);
        $porcentajeGestora = (float) $configLaboral->porcentaje_gestora_total;
        $tarifaRefrigerio = (float) ($request->query('tarifa') ?: $configLaboral->tarifa_refrigerio_diaria);
        $escalasBono = $configLaboral->escalas_bono_antiguedad ?? [];
        $diasLaboralesBase = (int) $configLaboral->dias_laborales_base;
        $horasJornada = (int) $configLaboral->horas_jornada_diaria;
        $multSmn = (int) ($configLaboral->multiplicador_smn_antiguedad ?: 3);

        $query = Persona::with([
            'fichaPersonal.cas',
            'fichaPersonal.datosLaborales',
            'asignacionesPuestos' => function ($q) {
                $q->where('_estado', 'ACTIVO');
            },
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
            },
        ]);

        if (in_array($tipoConsolidada, ['PLANTA_PERMANENTE'])) {
            $query->where(function ($q) {
                $q->whereHas('asignacionesPuestos', fn($ap) => $ap->where('asignacion', 'like', 'P-%'))
                  ->orWhereHas('fichaPersonal.datosLaborales', fn($dl) => $dl->where('tipo_funcionario', 'PLANTA'));
            });
        } elseif (in_array($tipoConsolidada, ['PERSONAL_EVENTUAL'])) {
            $query->where(function ($q) {
                $q->whereHas('asignacionesPuestos', fn($ap) => $ap->where('asignacion', 'like', 'E-%'))
                  ->orWhereHas('fichaPersonal.datosLaborales', fn($dl) => $dl->where('tipo_funcionario', 'EVENTUAL'));
            });
        } elseif (in_array($tipoConsolidada, ['DIETAS_DIRECTORIO'])) {
            $query->where(function ($q) {
                $q->whereHas('asignacionesPuestos', fn($ap) => $ap->where('asignacion', 'like', 'D-%'))
                  ->orWhereHas('fichaPersonal.datosLaborales', fn($dl) => $dl->where('tipo_funcionario', 'DIRECTORIO'));
            });
        }

        $personas = $query->get()->sortBy(function ($p) {
            $asig = $p->asignacionesPuestos->first();
            return $asig?->asignacion ?: 'ZZZ';
        })->values();

        $isPercentLive = ((float)($configLaboral?->porcentaje_gestora_total ?? 12.71) > 0.5);
        $normRate = fn($val, $fallback = 0.0) => (float) (
            $val !== null && $val !== ''
                ? ($isPercentLive ? round(((float)$val) / 100, 6) : round((float)$val, 6))
                : $fallback
        );

        $rateVejez = $normRate($configLaboral?->porcentaje_gestora_vejez, 0.10);
        $rateRiesgo = $normRate($configLaboral?->porcentaje_gestora_riesgo_comun, 0.0171);
        $rateComision = $normRate($configLaboral?->porcentaje_gestora_comision, 0.0050);
        $rateSolidario = $normRate($configLaboral?->porcentaje_gestora_solidario, 0.0050);
        $rateGestoraTotal = $normRate($configLaboral?->porcentaje_gestora_total, 0.1271);

        $rateCnsPat = $normRate($configLaboral?->porcentaje_patronal_cns, 0.10);
        $rateGestoraPatTotal = $normRate($configLaboral?->porcentaje_patronal_gestora_total, 0.0721);
        $rateRiesgoPat = $normRate($configLaboral?->porcentaje_patronal_gestora_riesgo, 0.0171);
        $rateViviendaPat = $normRate($configLaboral?->porcentaje_patronal_gestora_pro_vivienda, 0.02);
        $rateSolidarioPat = $normRate($configLaboral?->porcentaje_patronal_gestora_sol, 0.03);
        $rateComisionPat = 0.0050;

        $planilla = $personas->map(function ($p) use (
            $smn, $configLaboral, $tarifaRefrigerio, $escalasBono, $multSmn,
            $rateVejez, $rateRiesgo, $rateComision, $rateSolidario, $rateGestoraTotal
        ) {
            $puestoAsig = $p->asignacionesPuestos->first();
            $puesto = $puestoAsig?->puesto;
            $dl = $p->fichaPersonal?->datosLaborales?->first();
            $itemCodigo = $puestoAsig?->asignacion ?: ($puestoAsig?->nro_item ? 'P-' . str_pad((string) $puestoAsig->nro_item, 2, '0', STR_PAD_LEFT) : '-');

            $esDirectorio = str_starts_with($itemCodigo, 'D-') || ($dl?->tipo_funcionario === 'DIRECTORIO');
            $esEventual = str_starts_with($itemCodigo, 'E-') || ($dl?->tipo_funcionario === 'EVENTUAL');

            $haberBasico = 5200.0;
            if ($puesto && $puesto->id_escala_salarial) {
                $escala = DB::table('rrhh.escalas_salariales')->where('id', $puesto->id_escala_salarial)->first();
                if ($escala) {
                    $haberBasico = (float) ($escala->salario ?? $escala->salario_mensual ?? 5200.0);
                }
            } elseif ($dl && $dl->haber_basico) {
                $haberBasico = (float) $dl->haber_basico;
            }

            // Antigüedad CAS
            $cas = $p->fichaPersonal?->cas?->first();
            $anios = $cas ? (int) $cas->anios : 0;

            // Porcentaje bono de antigüedad
            $porcentajeBono = 0.0;
            if (!$esDirectorio && !$esEventual && $anios > 0) {
                if (!empty($escalasBono)) {
                    foreach ($escalasBono as $rango) {
                        $min = $rango['min'] ?? $rango['min_anios'] ?? $rango['anios_min'] ?? 0;
                        $max = $rango['max'] ?? $rango['max_anios'] ?? $rango['anios_max'] ?? 99;
                        $pct = (float) ($rango['porcentaje'] ?? 0);
                        if ($anios >= $min && $anios <= $max) {
                            $porcentajeBono = ($pct >= 1.0) ? ($pct / 100) : $pct;
                            break;
                        }
                    }
                } else {
                    if ($anios >= 2 && $anios <= 4) $porcentajeBono = 0.05;
                    elseif ($anios >= 5 && $anios <= 7) $porcentajeBono = 0.11;
                    elseif ($anios >= 8 && $anios <= 10) $porcentajeBono = 0.18;
                    elseif ($anios >= 11 && $anios <= 14) $porcentajeBono = 0.26;
                    elseif ($anios >= 15 && $anios <= 19) $porcentajeBono = 0.34;
                    elseif ($anios >= 20 && $anios <= 24) $porcentajeBono = 0.42;
                    elseif ($anios >= 25) $porcentajeBono = 0.50;
                }
            }

            if ($esDirectorio) {
                $diasTrabajados = 1; // 1 Sesión asistida
                $bonoAntiguedad = 0.0;
                $totalGanado = round($haberBasico, 2);
                $gestoraAporte = 0.0;
                $vejez10 = 0.0;
                $riesgo171 = 0.0;
                $comision05 = 0.0;
                $solidario05 = 0.0;
                $totalMinutosAtraso = 0;
                $descuentoAtraso = 0.0;
                $totalDescuentos = 0.0;
                $liquidoSalarial = $totalGanado;
                $diasRefrigerio = 0;
                $totalRefrigerio = 0.0;
                $totalAPagar = $totalGanado;
            } elseif ($esEventual) {
                $diasTrabajados = 30;
                $bonoAntiguedad = 0.0;
                $totalGanado = round($haberBasico, 2);
                $gestoraAporte = 0.0;
                $vejez10 = 0.0;
                $riesgo171 = 0.0;
                $comision05 = 0.0;
                $solidario05 = 0.0;
                $totalMinutosAtraso = 0;
                $descuentoAtraso = 0.0;
                $totalDescuentos = 0.0;
                $liquidoSalarial = $totalGanado;
                $diasRefrigerio = 0;
                $totalRefrigerio = 0.0;
                $totalAPagar = $totalGanado;
            } else {
                // PLANTA PERMANENTE
                $diasTrabajados = 30;
                $haberPorDias = round(($haberBasico / 30) * $diasTrabajados, 2);
                $horasExtras = 0;
                $importeHorasExtras = 0.0;
                $dominicales = 0;
                $importeDominicales = 0.0;

                $baseCalculo = $multSmn * $smn;
                $bonoAntiguedad = $anios > 0 ? round(((($baseCalculo * $porcentajeBono) / 31) * $diasTrabajados), 2) : 0.0;
                $totalGanado = round($haberPorDias + $importeHorasExtras + $importeDominicales + $bonoAntiguedad, 2);

                $vejez10 = round($totalGanado * $rateVejez, 2);
                $riesgo171 = round($totalGanado * $rateRiesgo, 2);
                $comision05 = round($totalGanado * $rateComision, 2);
                $solidario05 = round($totalGanado * $rateSolidario, 2);
                $gestoraAporte = round($totalGanado * $rateGestoraTotal, 2);

                $totalMinutosAtraso = $p->asistencias->sum('minutos_de_atraso_primer_periodo') + $p->asistencias->sum('minutos_de_atraso_segundo_periodo');
                $costoMinuto = ($haberBasico / 30) / (8 * 60);
                $descuentoAtraso = round($totalMinutosAtraso * $costoMinuto, 2);

                $totalDescuentos = round($gestoraAporte + $descuentoAtraso, 2);
                $liquidoSalarial = round($totalGanado - $totalDescuentos, 2);

                $diasRefrigerio = $p->asistencias->where('merece_refrigerio', true)->count();
                $totalRefrigerio = round($diasRefrigerio * $tarifaRefrigerio, 2);
                $totalAPagar = round($liquidoSalarial + $totalRefrigerio, 2);
            }

            return [
                'id' => $p->id,
                'id_persona' => $p->id,
                'funcionario' => $p->nombre_completo,
                'ci' => $p->nro_documento,
                'cargo' => $puesto?->nombre ?? ($dl?->cargo ?? 'Funcionario'),
                'item' => $itemCodigo,
                'haber_basico' => round($haberBasico, 2),
                'dias_trabajados' => $diasTrabajados,
                'anios_antiguedad' => $anios,
                'porcentaje_bono' => round($porcentajeBono * 100, 1),
                'bono_antiguedad' => round($bonoAntiguedad, 2),
                'total_ganado' => round($totalGanado, 2),
                'gestora_12_71' => round($gestoraAporte, 2),
                'desglose_gestora' => [
                    'vejez_10' => $vejez10,
                    'riesgo_1_71' => $riesgo171,
                    'comision_0_5' => $comision05,
                    'solidario_0_5' => $solidario05,
                ],
                'minutos_atraso' => $totalMinutosAtraso,
                'descuento_atraso' => round($descuentoAtraso, 2),
                'total_descuentos' => round($totalDescuentos, 2),
                'liquido_salarial' => round($liquidoSalarial, 2),
                'dias_refrigerio' => $diasRefrigerio,
                'refrigerio_bs' => round($totalRefrigerio, 2),
                'liquido_pagable_total' => round($totalAPagar, 2),
                'liquido_literal' => ReporteFinancieroPdfService::convertirNumeroALetras((float) $liquidoSalarial),
            ];
        });

        // Totales y Patronales
        $totalGanado = round((float) $planilla->sum('total_ganado'), 2);
        $totalGanadoPlanta = round((float) $planilla->where('gestora_12_71', '>', 0)->sum('total_ganado'), 2);
        if ($totalGanadoPlanta === 0.0 && $tipoConsolidada === 'PLANTA_PERMANENTE') {
            $totalGanadoPlanta = $totalGanado;
        }

        $cnsPatronal = round($totalGanadoPlanta * $rateCnsPat, 2);
        $gestoraPatronal = round($totalGanadoPlanta * $rateGestoraPatTotal, 2);
        $patRiesgo = round($totalGanadoPlanta * $rateRiesgoPat, 2);
        $patVivienda = round($totalGanadoPlanta * $rateViviendaPat, 2);
        $patSol = round($totalGanadoPlanta * $rateSolidarioPat, 2);
        $patComision = round($totalGanadoPlanta * $rateComisionPat, 2);
        $totalPatronal = round($cnsPatronal + $gestoraPatronal, 2);
        $costoTotalEmpresa = round($totalGanado + $totalPatronal, 2);

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'tipo_planilla' => $tipoConsolidada,
            'es_declarada' => false,
            'estado_planilla' => 'BORRADOR_SIMULACION',
            'smn_aplicado' => $smn,
            'total_planilla_bs' => round($planilla->sum('liquido_pagable_total'), 2),
            'total_liquido_salarial_bs' => round($planilla->sum('liquido_salarial'), 2),
            'total_ganado_bs' => $totalGanado,
            'total_descuentos_bs' => round($planilla->sum('total_descuentos'), 2),
            'total_refrigerio_bs' => round($planilla->sum('refrigerio_bs'), 2),
            'totales' => [
                'total_ganado_bs' => $totalGanado,
                'total_descuentos_bs' => round($planilla->sum('total_descuentos'), 2),
                'total_liquido_bs' => round($planilla->sum('liquido_salarial'), 2),
                'total_refrigerio_bs' => round($planilla->sum('refrigerio_bs'), 2),
                'total_planilla_bs' => round($planilla->sum('liquido_pagable_total'), 2),
            ],
            'patronal' => [
                'cns_10_bs' => $cnsPatronal,
                'gestora_7_21_bs' => $gestoraPatronal,
                'riesgo_profesional_1_71_bs' => $patRiesgo,
                'pro_vivienda_2_bs' => $patVivienda,
                'solidario_patronal_3_bs' => $patSol,
                'comision_patronal_0_5_bs' => round($totalGanadoPlanta * 0.005, 2),
                'total_patronal_bs' => $totalPatronal,
                'costo_total_empresa_bs' => $costoTotalEmpresa,
            ],
            'data' => $planilla,
        ], Response::HTTP_OK);
    }

    /**
     * Lista las planillas efectivamente generadas / registradas (Borradores y Declaradas).
     */
    public function listarPlanillasRegistradas(Request $request): JsonResponse
    {
        $gestion = $request->query('gestion');
        $tipoPlanilla = $request->query('tipo_planilla');

        $query = DB::table('rrhh.planillas_consolidadas as pc')
            ->where('pc._estado', 'ACTIVO')
            ->select([
                'pc.id',
                'pc.gestion',
                'pc.mes',
                'pc.tipo_planilla',
                'pc.cite_oficial',
                'pc.total_ganado_bs',
                'pc.total_descuentos_bs',
                'pc.total_liquido_pagable_bs',
                'pc.estado',
                'pc.fecha_cierre',
                'pc.smn_aplicado',
                'pc.tarifa_refrigerio_aplicada',
                'pc.porcentaje_gestora_aplicado',
                'pc.observaciones',
                'pc._fecha_creacion',
                DB::raw('(SELECT COUNT(*) FROM rrhh.detalles_planillas dp WHERE dp.id_planilla_consolidada = pc.id) as cantidad_funcionarios'),
            ]);

        if ($gestion) {
            $query->where('pc.gestion', (int) $gestion);
        }

        if ($tipoPlanilla && $tipoPlanilla !== 'TODOS' && $tipoPlanilla !== 'CONSOLIDADO_GENERAL') {
            $query->where('pc.tipo_planilla', $tipoPlanilla);
        }

        $planillas = $query->orderBy('pc.gestion', 'desc')
            ->orderBy('pc.mes', 'desc')
            ->orderBy('pc.id', 'desc')
            ->get();

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $tiposLabels = [
            'PLANTA_PERMANENTE' => 'Planta Permanente',
            'SUELDOS_Y_SALARIOS' => 'Planta Permanente',
            'PERSONAL_EVENTUAL' => 'Personal Eventual',
            'DIETAS_DIRECTORIO' => 'Dietas Directorio',
        ];

        $items = $planillas->map(function ($p) use ($mesesNombres, $tiposLabels) {
            $mesNum = (int) $p->mes;
            return [
                'id' => $p->id,
                'gestion' => (int) $p->gestion,
                'mes' => $mesNum,
                'mes_nombre' => $mesesNombres[$mesNum] ?? "Mes {$mesNum}",
                'tipo_planilla' => $p->tipo_planilla,
                'tipo_planilla_label' => $tiposLabels[$p->tipo_planilla] ?? $p->tipo_planilla,
                'cite_oficial' => $p->cite_oficial,
                'total_ganado_bs' => (float) $p->total_ganado_bs,
                'total_descuentos_bs' => (float) $p->total_descuentos_bs,
                'total_liquido_pagable_bs' => (float) $p->total_liquido_pagable_bs,
                'estado' => $p->estado,
                'es_declarada' => in_array($p->estado, ['DECLARADA', 'CONSOLIDADA', 'PAGADA']),
                'fecha_cierre' => $p->fecha_cierre,
                'fecha_creacion' => $p->_fecha_creacion,
                'cantidad_funcionarios' => (int) $p->cantidad_funcionarios,
                'observaciones' => $p->observaciones,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'total_registros' => $items->count(),
        ], Response::HTTP_OK);
    }

    /**
     * Genera una nueva planilla salarial (ya sea en estado BORRADOR o DECLARADA directamente).
     */
    public function generarPlanilla(Request $request): JsonResponse
    {
        $mes = (int) $request->input('mes', date('m'));
        $anio = (int) $request->input('anio', date('Y'));
        $tipo = strtoupper((string) $request->input('tipo_planilla', 'PLANTA_PERMANENTE'));
        $estadoDeseado = strtoupper((string) $request->input('estado', 'BORRADOR'));
        $observaciones = $request->input('observaciones');
        $esDeclarada = ($estadoDeseado === 'DECLARADA');

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        // CASO EN LOTE: Procesar simultáneamente todas las nóminas (Planta Permanente, Personal Eventual, Dietas Directorio)
        if (in_array($tipo, ['TODAS', 'TODOS', 'CONSOLIDADO_GENERAL'])) {
            $tiposAProcesar = ['PLANTA_PERMANENTE', 'PERSONAL_EVENTUAL', 'DIETAS_DIRECTORIO'];

            // 1. Verificar si alguna de las planillas ya se encuentra DECLARADA oficialmente
            $existentes = DB::table('rrhh.planillas_consolidadas')
                ->where('gestion', $anio)
                ->where('mes', $mes)
                ->whereIn('tipo_planilla', $tiposAProcesar)
                ->where('_estado', 'ACTIVO')
                ->get();

            $declaradas = $existentes->where('estado', 'DECLARADA');
            if ($declaradas->isNotEmpty()) {
                $nombresDeclaradas = $declaradas->pluck('tipo_planilla')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "La(s) planilla(s) de {$mes}/{$anio} [{$nombresDeclaradas}] ya se encuentra(n) declarada(s) oficialmente. Para modificarlas o regenerarlas debe reabrirlas primero.",
                ], Response::HTTP_CONFLICT);
            }

            // 2. Ejecutar generación en lote dentro de una transacción
            $planillasGeneradas = [];
            $totalFuncionariosLote = 0;
            $totalGanadoLote = 0;
            $totalDescuentosLote = 0;
            $totalLiquidoLote = 0;

            DB::beginTransaction();
            try {
                // Si existían como BORRADOR, eliminar snapshots previos para regenerar limpiamente
                $idsBorradores = $existentes->pluck('id');
                if ($idsBorradores->isNotEmpty()) {
                    DB::table('rrhh.detalles_planillas')->whereIn('id_planilla_consolidada', $idsBorradores)->delete();
                    DB::table('rrhh.planillas_consolidadas')->whereIn('id', $idsBorradores)->delete();
                }

                foreach ($tiposAProcesar as $subTipo) {
                    $subRequest = new Request([
                        'mes' => $mes,
                        'anio' => $anio,
                        'tipo_planilla' => $subTipo,
                    ]);
                    $calculo = $this->planillaSueldosMensual($subRequest)->getData(true);
                    $items = $calculo['data'] ?? [];

                    if (empty($items)) {
                        continue;
                    }

                    $cite = $esDeclarada
                        ? 'PLA-EMAPA-' . strtoupper($subTipo) . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '-' . $anio
                        : null;

                    $idPlanilla = DB::table('rrhh.planillas_consolidadas')->insertGetId([
                        'gestion' => $anio,
                        'mes' => $mes,
                        'tipo_planilla' => $subTipo,
                        'cite_oficial' => $cite,
                        'smn_aplicado' => $calculo['smn_aplicado'] ?? 3300.0,
                        'tarifa_refrigerio_aplicada' => $calculo['tarifa_refrigerio_aplicada'] ?? 18.0,
                        'porcentaje_gestora_aplicado' => 12.71,
                        'total_ganado_bs' => $calculo['total_ganado_bs'] ?? 0.0,
                        'total_descuentos_bs' => $calculo['total_descuentos_bs'] ?? 0.0,
                        'total_liquido_pagable_bs' => $calculo['total_planilla_bs'] ?? 0.0,
                        'estado' => $esDeclarada ? 'DECLARADA' : 'BORRADOR',
                        'fecha_cierre' => now(),
                        'id_usuario_cierre' => auth()->id() ?? 1,
                        'observaciones' => $observaciones ?: ($esDeclarada ? 'Planilla consolidada y declarada en lote' : 'Planilla borrador generada en lote'),
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CREAR',
                        '_usuario_creacion' => auth()->id() ?? 1,
                        '_fecha_creacion' => now(),
                    ]);

                    foreach ($items as $it) {
                        DB::table('rrhh.detalles_planillas')->insert([
                            'id_planilla_consolidada' => $idPlanilla,
                            'id_persona' => $it['id_persona'] ?? $it['id'],
                            'funcionario' => $it['funcionario'],
                            'ci' => $it['ci'],
                            'cargo' => $it['cargo'],
                            'item' => (string) ($it['item'] ?? '-'),
                            'haber_basico' => $it['haber_basico'],
                            'anios_antiguedad' => $it['anios_antiguedad'],
                            'porcentaje_bono' => $it['porcentaje_bono'],
                            'bono_antiguedad' => $it['bono_antiguedad'],
                            'total_ganado' => $it['total_ganado'],
                            'gestora_12_71' => $it['gestora_12_71'],
                            'minutos_atraso' => $it['minutos_atraso'],
                            'descuento_atraso' => $it['descuento_atraso'],
                            'total_descuentos' => $it['total_descuentos'],
                            'liquido_salarial' => $it['liquido_salarial'],
                            'dias_refrigerio' => $it['dias_refrigerio'],
                            'refrigerio_bs' => $it['refrigerio_bs'],
                            'liquido_pagable_total' => $it['liquido_pagable_total'],
                            '_estado' => 'ACTIVO',
                            '_fecha_creacion' => now(),
                        ]);
                    }

                    $planillasGeneradas[] = $subTipo;
                    $totalFuncionariosLote += count($items);
                    $totalGanadoLote += (float) ($calculo['total_ganado_bs'] ?? 0.0);
                    $totalDescuentosLote += (float) ($calculo['total_descuentos_bs'] ?? 0.0);
                    $totalLiquidoLote += (float) ($calculo['total_planilla_bs'] ?? 0.0);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar las planillas en lote: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (empty($planillasGeneradas)) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontraron funcionarios activos para generar planillas en {$mes}/{$anio}.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $mesNombre = $mesesNombres[$mes] ?? "Mes {$mes}";
            $cantPlanillas = count($planillasGeneradas);
            $mensaje = $esDeclarada
                ? "Se consolidaron y declararon exitosamente {$cantPlanillas} planillas ({$mesNombre}/{$anio}) con un total de {$totalFuncionariosLote} funcionarios."
                : "Se generaron exitosamente {$cantPlanillas} planillas como BORRADOR ({$mesNombre}/{$anio}) con un total de {$totalFuncionariosLote} funcionarios.";

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'planillas_generadas' => $planillasGeneradas,
                'total_funcionarios' => $totalFuncionariosLote,
                'total_ganado_bs' => $totalGanadoLote,
                'total_descuentos_bs' => $totalDescuentosLote,
                'total_liquido_bs' => $totalLiquidoLote,
                'estado' => $esDeclarada ? 'DECLARADA' : 'BORRADOR',
            ], Response::HTTP_CREATED);
        }

        $tipoConsolidada = match ($tipo) {
            'PERSONAL_EVENTUAL', 'EVENTUAL' => 'PERSONAL_EVENTUAL',
            'DIETAS_DIRECTORIO', 'DIRECTORIO' => 'DIETAS_DIRECTORIO',
            default => 'PLANTA_PERMANENTE',
        };

        // Verificar si ya existe
        $existente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', $tipoConsolidada)
            ->first();

        if ($existente) {
            if ($existente->estado === 'DECLARADA') {
                return response()->json([
                    'success' => false,
                    'message' => "La planilla de {$mes}/{$anio} ya se encuentra declarada oficialmente ({$existente->cite_oficial}). Para modificarla debe reabrirla primero.",
                ], Response::HTTP_CONFLICT);
            }

            // Si ya existía como BORRADOR, eliminar snapshot previo para regenerar limpiamente
            DB::table('rrhh.detalles_planillas')->where('id_planilla_consolidada', $existente->id)->delete();
            DB::table('rrhh.planillas_consolidadas')->where('id', $existente->id)->delete();
        }

        // Calcular la simulación calculada
        $request->merge(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => $tipoConsolidada]);
        $calculo = $this->planillaSueldosMensual($request)->getData(true);
        $items = $calculo['data'] ?? [];

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => "No se encontraron funcionarios activos para generar la planilla de {$tipoConsolidada} en {$mes}/{$anio}.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cite = $esDeclarada
            ? 'PLA-EMAPA-' . strtoupper($tipoConsolidada) . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '-' . $anio
            : null;

        $idPlanilla = DB::table('rrhh.planillas_consolidadas')->insertGetId([
            'gestion' => $anio,
            'mes' => $mes,
            'tipo_planilla' => $tipoConsolidada,
            'cite_oficial' => $cite,
            'smn_aplicado' => $calculo['smn_aplicado'] ?? 3300.0,
            'tarifa_refrigerio_aplicada' => $calculo['tarifa_refrigerio_aplicada'] ?? 18.0,
            'porcentaje_gestora_aplicado' => 12.71,
            'total_ganado_bs' => $calculo['total_ganado_bs'] ?? 0.0,
            'total_descuentos_bs' => $calculo['total_descuentos_bs'] ?? 0.0,
            'total_liquido_pagable_bs' => $calculo['total_planilla_bs'] ?? 0.0,
            'estado' => $esDeclarada ? 'DECLARADA' : 'BORRADOR',
            'fecha_cierre' => now(),
            'id_usuario_cierre' => auth()->id() ?? 1,
            'observaciones' => $observaciones,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        foreach ($items as $it) {
            DB::table('rrhh.detalles_planillas')->insert([
                'id_planilla_consolidada' => $idPlanilla,
                'id_persona' => $it['id_persona'] ?? $it['id'],
                'funcionario' => $it['funcionario'],
                'ci' => $it['ci'],
                'cargo' => $it['cargo'],
                'item' => (string) ($it['item'] ?? '-'),
                'haber_basico' => $it['haber_basico'],
                'anios_antiguedad' => $it['anios_antiguedad'],
                'porcentaje_bono' => $it['porcentaje_bono'],
                'bono_antiguedad' => $it['bono_antiguedad'],
                'total_ganado' => $it['total_ganado'],
                'gestora_12_71' => $it['gestora_12_71'],
                'minutos_atraso' => $it['minutos_atraso'],
                'descuento_atraso' => $it['descuento_atraso'],
                'total_descuentos' => $it['total_descuentos'],
                'liquido_salarial' => $it['liquido_salarial'],
                'dias_refrigerio' => $it['dias_refrigerio'],
                'refrigerio_bs' => $it['refrigerio_bs'],
                'liquido_pagable_total' => $it['liquido_pagable_total'],
                '_estado' => 'ACTIVO',
                '_fecha_creacion' => now(),
            ]);
        }

        $mensaje = $esDeclarada
            ? "Planilla {$cite} generada, consolidada y declarada exitosamente."
            : "Planilla de " . ($mesesNombres[$mes] ?? "Mes {$mes}") . "/{$anio} generada exitosamente como BORRADOR con " . count($items) . " funcionarios.";

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'id_planilla' => $idPlanilla,
            'cite_oficial' => $cite,
            'estado' => $esDeclarada ? 'DECLARADA' : 'BORRADOR',
        ], Response::HTTP_CREATED);
    }

    /**
     * Elimina una planilla en estado BORRADOR.
     */
    public function eliminarPlanillaBorrador(int $id): JsonResponse
    {
        $planilla = DB::table('rrhh.planillas_consolidadas')->where('id', $id)->first();
        if (!$planilla) {
            return response()->json(['success' => false, 'message' => 'Planilla no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if ($planilla->estado === 'DECLARADA') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una planilla DECLARADA directamente. Debe reabrirla primero.',
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::transaction(function () use ($id) {
            DB::table('rrhh.detalles_planillas')->where('id_planilla_consolidada', $id)->delete();
            DB::table('rrhh.planillas_consolidadas')->where('id', $id)->delete();
        });

        return response()->json(['success' => true, 'message' => 'Planilla borrador eliminada correctamente.'], Response::HTTP_OK);
    }

    /**
     * Cierra y declara formalmente la planilla salarial generando un snapshot inmutable.
     */
    public function cerrarYDeclararPlanilla(Request $request): JsonResponse
    {
        $mes = (int) $request->input('mes', date('m'));
        $anio = (int) $request->input('anio', date('Y'));
        $tipo = $request->input('tipo_planilla', 'PLANTA_PERMANENTE');

        if (in_array(strtoupper((string) $tipo), ['TODAS', 'TODOS', 'CONSOLIDADO_GENERAL'])) {
            $tiposAProcesar = ['PLANTA_PERMANENTE', 'PERSONAL_EVENTUAL', 'DIETAS_DIRECTORIO'];
            $planillas = DB::table('rrhh.planillas_consolidadas')
                ->where('gestion', $anio)
                ->where('mes', $mes)
                ->whereIn('tipo_planilla', $tiposAProcesar)
                ->where('_estado', 'ACTIVO')
                ->get();

            if ($planillas->isEmpty()) {
                $request->merge(['estado' => 'DECLARADA', 'tipo_planilla' => 'TODAS']);
                return $this->generarPlanilla($request);
            }

            DB::beginTransaction();
            try {
                foreach ($planillas as $p) {
                    if ($p->estado !== 'DECLARADA') {
                        $cite = 'PLA-EMAPA-' . strtoupper($p->tipo_planilla) . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '-' . $anio;
                        DB::table('rrhh.planillas_consolidadas')->where('id', $p->id)->update([
                            'estado' => 'DECLARADA',
                            'cite_oficial' => $cite,
                            'fecha_cierre' => now(),
                            'id_usuario_cierre' => auth()->id() ?? 1,
                            '_usuario_modificacion' => auth()->id() ?? 1,
                            '_fecha_modificacion' => now(),
                        ]);
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Error al declarar planillas: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'success' => true,
                'message' => "Todas las planillas de {$mes}/{$anio} fueron consolidadas y declaradas formalmente.",
            ], Response::HTTP_OK);
        }

        $existente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', $tipo)
            ->first();

        $cite = 'PLA-EMAPA-'.strtoupper($tipo).'-'.str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'-'.$anio;

        if ($existente) {
            if ($existente->estado === 'DECLARADA') {
                return response()->json([
                    'success' => false,
                    'message' => "La planilla ({$tipo}) de {$mes}/{$anio} ya se encuentra cerrada y declarada. No puede ser sobreescrita.",
                ], Response::HTTP_CONFLICT);
            }

            // Si estaba como BORRADOR, la consolidamos a DECLARADA
            DB::table('rrhh.planillas_consolidadas')->where('id', $existente->id)->update([
                'estado' => 'DECLARADA',
                'cite_oficial' => $cite,
                'fecha_cierre' => now(),
                'id_usuario_cierre' => auth()->id() ?? 1,
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Planilla {$cite} consolidada y declarada exitosamente como documento inmutable.",
                'cite_oficial' => $cite,
            ], Response::HTTP_OK);
        }

        // Si no existía, generar y declarar directamente
        $calculo = $this->planillaSueldosMensual($request)->getData(true);
        $items = $calculo['data'] ?? [];

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'No hay funcionarios para consolidar en la planilla.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPlanilla = DB::table('rrhh.planillas_consolidadas')->insertGetId([
            'gestion' => $anio,
            'mes' => $mes,
            'tipo_planilla' => $tipo,
            'cite_oficial' => $cite,
            'smn_aplicado' => $calculo['smn_aplicado'] ?? 3300.0,
            'tarifa_refrigerio_aplicada' => $calculo['tarifa_refrigerio_aplicada'] ?? 18.0,
            'porcentaje_gestora_aplicado' => 12.71,
            'total_ganado_bs' => $calculo['total_ganado_bs'] ?? 0.0,
            'total_descuentos_bs' => $calculo['total_descuentos_bs'] ?? 0.0,
            'total_liquido_pagable_bs' => $calculo['total_planilla_bs'] ?? 0.0,
            'estado' => 'DECLARADA',
            'fecha_cierre' => now(),
            'id_usuario_cierre' => auth()->id() ?? 1,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        foreach ($items as $it) {
            DB::table('rrhh.detalles_planillas')->insert([
                'id_planilla_consolidada' => $idPlanilla,
                'id_persona' => $it['id_persona'] ?? $it['id'],
                'funcionario' => $it['funcionario'],
                'ci' => $it['ci'],
                'cargo' => $it['cargo'],
                'item' => (string) ($it['item'] ?? '-'),
                'haber_basico' => $it['haber_basico'],
                'anios_antiguedad' => $it['anios_antiguedad'],
                'porcentaje_bono' => $it['porcentaje_bono'],
                'bono_antiguedad' => $it['bono_antiguedad'],
                'total_ganado' => $it['total_ganado'],
                'gestora_12_71' => $it['gestora_12_71'],
                'minutos_atraso' => $it['minutos_atraso'],
                'descuento_atraso' => $it['descuento_atraso'],
                'total_descuentos' => $it['total_descuentos'],
                'liquido_salarial' => $it['liquido_salarial'],
                'dias_refrigerio' => $it['dias_refrigerio'],
                'refrigerio_bs' => $it['refrigerio_bs'],
                'liquido_pagable_total' => $it['liquido_pagable_total'],
                '_estado' => 'ACTIVO',
                '_fecha_creacion' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Planilla {$cite} cerrada y declarada exitosamente como documento inmutable.",
            'cite_oficial' => $cite,
        ], Response::HTTP_CREATED);
    }

    /**
     * Reabre o desconsolida una planilla previamente declarada devolviéndola al estado de simulación.
     */
    public function reabrirPlanilla(Request $request): JsonResponse
    {
        $mes = (int) $request->input('mes');
        $anio = (int) $request->input('anio');
        $tipoPlanilla = strtoupper((string) $request->input('tipo_planilla', 'PLANTA_PERMANENTE'));

        if (in_array($tipoPlanilla, ['TODOS', 'TODAS', 'CONSOLIDADO_GENERAL'])) {
            $planillas = DB::table('rrhh.planillas_consolidadas')
                ->where('gestion', $anio)
                ->where('mes', $mes)
                ->where('_estado', 'ACTIVO')
                ->get();

            if ($planillas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existen planillas registradas para este periodo.',
                ], Response::HTTP_NOT_FOUND);
            }

            DB::transaction(function () use ($planillas) {
                $ids = $planillas->pluck('id');
                DB::table('rrhh.detalles_planillas')->whereIn('id_planilla_consolidada', $ids)->delete();
                DB::table('rrhh.planillas_consolidadas')->whereIn('id', $ids)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "Todas las planillas de {$mes}/{$anio} fueron reabiertas exitosamente. Ahora se encuentran en modo Simulación para realizar ajustes.",
            ], Response::HTTP_OK);
        }

        $tipoConsolidada = match ($tipoPlanilla) {
            'PERSONAL_EVENTUAL', 'EVENTUAL' => 'PERSONAL_EVENTUAL',
            'DIETAS_DIRECTORIO', 'DIRECTORIO' => 'DIETAS_DIRECTORIO',
            default => 'PLANTA_PERMANENTE',
        };

        $planilla = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where(function ($q) use ($tipoConsolidada) {
                $q->where('tipo_planilla', $tipoConsolidada)
                  ->orWhere('tipo_planilla', 'SUELDOS_Y_SALARIOS');
            })
            ->first();

        if (!$planilla) {
            return response()->json([
                'success' => false,
                'message' => 'No existe una planilla declarada para este periodo.',
            ], Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($planilla) {
            DB::table('rrhh.detalles_planillas')
                ->where('id_planilla_consolidada', $planilla->id)
                ->delete();

            DB::table('rrhh.planillas_consolidadas')
                ->where('id', $planilla->id)
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "La planilla de {$mes}/{$anio} fue reabierta exitosamente. Ahora se encuentra en modo Simulación para realizar ajustes.",
        ], Response::HTTP_OK);
    }

    /**
     * Boleta Individual Oficial de Pago Salarial (Papeleta de Pago Imprimible).
     */
    public function boletaPagoHtml(int $personaId, Request $request): JsonResponse
    {
        $idPlanilla = $request->query('id_planilla');
        if ($idPlanilla) {
            $planConsolidada = DB::table('rrhh.planillas_consolidadas')->where('id', $idPlanilla)->first();
            if ($planConsolidada) {
                $mes = (int) $planConsolidada->mes;
                $anio = (int) $planConsolidada->gestion;
                $request->merge(['mes' => $mes, 'anio' => $anio]);
            }
        }

        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));

        $persona = Persona::with([
            'fichaPersonal.cas',
            'fichaPersonal.datosLaborales',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
            },
        ])->findOrFail($personaId);

        // Verificar si la planilla está consolidada
        $planillaExistente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('_estado', 'ACTIVO')
            ->first();

        $detalleConsolidado = null;
        if ($planillaExistente) {
            $detalleConsolidado = DB::table('rrhh.detalles_planillas')
                ->where('id_planilla_consolidada', $planillaExistente->id)
                ->where('id_persona', $personaId)
                ->first();
        }

        if ($detalleConsolidado) {
            $data = (array) $detalleConsolidado;
            $cite = $planillaExistente->cite_oficial;
            $esCerrada = true;
        } else {
            // Calcular en vivo
            $calculoResponse = $this->planillaSueldosMensual(new Request(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => 'TODOS']))->getData(true);
            $items = $calculoResponse['data'] ?? [];
            $data = collect($items)->firstWhere('id_persona', $personaId) ?? collect($items)->firstWhere('id', $personaId) ?? [
                'funcionario' => $persona->nombre_completo,
                'ci' => $persona->nro_documento,
                'cargo' => 'Funcionario',
                'item' => '-',
                'haber_basico' => 5200.0,
                'anios_antiguedad' => 0,
                'porcentaje_bono' => 0,
                'bono_antiguedad' => 0,
                'total_ganado' => 5200.0,
                'gestora_12_71' => 660.92,
                'minutos_atraso' => 0,
                'descuento_atraso' => 0,
                'total_descuentos' => 660.92,
                'liquido_salarial' => 4539.08,
                'dias_refrigerio' => 20,
                'refrigerio_bs' => 360.0,
                'liquido_pagable_total' => 4899.08,
            ];
            $cite = 'BORRADOR-'.str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'/'.$anio;
            $esCerrada = false;
        }

        $mesesNombres = [1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'];
        $mesNombre = $mesesNombres[$mes] ?? 'MES';

        $puesto = $persona->asignacionesPuestos->first()?->puesto;
        $dl = $persona->fichaPersonal?->datosLaborales?->first();
        $unidad = $puesto?->unidadOrganizacional?->nombre ?? 'ADMINISTRACIÓN CENTRAL';
        $itemCodigo = $persona->asignacionesPuestos->first()?->asignacion ?: ($data['item'] ?? '-');

        $liquidoSalarial = (float) ($data['liquido_salarial'] ?? ($data['total_ganado'] - $data['total_descuentos']));
        $liquidoLiteral = ReporteFinancieroPdfService::convertirNumeroALetras($liquidoSalarial);

        $desgloseGestora = $data['desglose_gestora'] ?? [
            'vejez_10' => round(((float)$data['total_ganado']) * 0.10, 2),
            'riesgo_1_71' => round(((float)$data['total_ganado']) * 0.0171, 2),
            'comision_0_5' => round(((float)$data['total_ganado']) * 0.005, 2),
            'solidario_0_5' => round(((float)$data['total_ganado']) * 0.005, 2),
        ];

        $configLaboral = ConfiguracionLaboral::where('gestion', $anio)->first() ?: ConfiguracionLaboral::orderBy('id', 'desc')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'boleta_nro' => $cite,
                'es_cerrada' => $esCerrada,
                'periodo' => "{$mesNombre} {$anio}",
                'institucion' => 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - PATACAMAYA',
                'subtitulo' => ($configLaboral?->ubicacion_geografica ?? 'PATACAMAYA - LA PAZ - BOLIVIA') . ' | ' . ($configLaboral?->direccion_institucional ?? 'PLAZA BOLIVAR ZONA ESTACION'),
                'direccion' => $configLaboral?->direccion_institucional ?? 'PLAZA BOLIVAR - ZONA ESTACION',
                'ubicacion' => $configLaboral?->ubicacion_geografica ?? 'PATACAMAYA-LA PAZ-BOLIVIA',
                'patronal_cns' => 'C.N.S. ' . ($configLaboral?->nro_patronal_cns ?? '01-521-00002') . ' PATRONAL',
                'patronal_min_trabajo' => $configLaboral?->nro_patronal_min_trabajo ?? '1002393029-1',
                'nit' => $configLaboral?->nit_institucional ?? '1002393029',
                'funcionario' => [
                    'id' => $persona->id,
                    'nombre_completo' => $persona->nombre_completo,
                    'ci' => $persona->nro_documento,
                    'cargo' => $data['cargo'] ?? ($puesto?->nombre ?? ($dl?->cargo ?? 'Funcionario')),
                    'unidad' => $unidad,
                    'item' => $itemCodigo,
                    'antiguedad_anios' => $data['anios_antiguedad'] ?? 0,
                    'dias_trabajados' => $data['dias_trabajados'] ?? 30,
                ],
                'ingresos' => [
                    'haber_basico' => (float) ($data['haber_basico'] ?? 0),
                    'haber_dias_trabajados' => (float) ($data['haber_basico'] ?? 0),
                    'bono_antiguedad' => (float) ($data['bono_antiguedad'] ?? 0),
                    'porcentaje_antiguedad' => (float) ($data['porcentaje_bono'] ?? 0),
                    'horas_extras' => 0.0,
                    'dominicales' => 0.0,
                    'otros_bonos' => 0.0,
                    'total_ganado' => (float) ($data['total_ganado'] ?? 0),
                    'refrigerios_bs' => (float) ($data['refrigerio_bs'] ?? 0),
                    'dias_refrigerio' => (int) ($data['dias_refrigerio'] ?? 0),
                ],
                'descuentos' => [
                    'gestora_12_71' => (float) ($data['gestora_12_71'] ?? 0),
                    'vejez_10' => (float) ($desgloseGestora['vejez_10'] ?? 0),
                    'riesgo_1_71' => (float) ($desgloseGestora['riesgo_1_71'] ?? 0),
                    'comision_0_5' => (float) ($desgloseGestora['comision_0_5'] ?? 0),
                    'solidario_0_5' => (float) ($desgloseGestora['solidario_0_5'] ?? 0),
                    'minutos_atraso' => (int) ($data['minutos_atraso'] ?? 0),
                    'descuento_atraso' => (float) ($data['descuento_atraso'] ?? 0),
                    'anticipos' => 0.0,
                    'total_descuentos' => (float) ($data['total_descuentos'] ?? 0),
                ],
                'liquido_pagable' => $liquidoSalarial,
                'liquido_pagable_total' => (float) ($data['liquido_pagable_total'] ?? $liquidoSalarial),
                'liquido_literal' => $liquidoLiteral,
                'fecha_emision' => now()->format('d/m/Y H:i'),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Devuelve todas las boletas de pago del mes para impresión masiva.
     */
    public function boletasPagoMesHtml(Request $request): JsonResponse
    {
        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));
        $tipoPlanilla = $request->query('tipo_planilla', 'PLANTA_PERMANENTE');

        $calculo = $this->planillaSueldosMensual($request)->getData(true);
        $items = $calculo['data'] ?? [];

        $mesesNombres = [1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'];
        $mesNombre = $mesesNombres[$mes] ?? 'MES';

        $configLaboral = ConfiguracionLaboral::where('gestion', $anio)->first() ?: ConfiguracionLaboral::orderBy('id', 'desc')->first();

        $boletas = collect($items)->map(function ($it) use ($mes, $anio, $mesNombre, $configLaboral, $calculo) {
            $liq = (float) ($it['liquido_salarial'] ?? ($it['total_ganado'] - $it['total_descuentos']));
            $desglose = $it['desglose_gestora'] ?? [
                'vejez_10' => round(((float)$it['total_ganado']) * 0.10, 2),
                'riesgo_1_71' => round(((float)$it['total_ganado']) * 0.0171, 2),
                'comision_0_5' => round(((float)$it['total_ganado']) * 0.005, 2),
                'solidario_0_5' => round(((float)$it['total_ganado']) * 0.005, 2),
            ];

            return [
                'boleta_nro' => 'BOL-EMAPA-' . str_pad((string)($it['id_persona'] ?? $it['id']), 3, '0', STR_PAD_LEFT) . '-' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . '/' . $anio,
                'es_cerrada' => (bool) ($calculo['es_declarada'] ?? false),
                'periodo' => "{$mesNombre} {$anio}",
                'institucion' => 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - PATACAMAYA',
                'direccion' => $configLaboral?->direccion_institucional ?? 'PLAZA BOLIVAR - ZONA ESTACION',
                'ubicacion' => $configLaboral?->ubicacion_geografica ?? 'PATACAMAYA - LA PAZ - BOLIVIA',
                'subtitulo' => ($configLaboral?->ubicacion_geografica ?? 'PATACAMAYA - LA PAZ - BOLIVIA') . ' | ' . ($configLaboral?->direccion_institucional ?? 'PLAZA BOLIVAR ZONA ESTACION'),
                'patronal_cns' => 'C.N.S. ' . ($configLaboral?->nro_patronal_cns ?? '01-521-00002') . ' PATRONAL',
                'patronal_min_trabajo' => $configLaboral?->nro_patronal_min_trabajo ?? '1002393029-1',
                'nit' => $configLaboral?->nit_institucional ?? '1002393029',
                'funcionario' => [
                    'id' => $it['id_persona'] ?? $it['id'],
                    'nombre_completo' => $it['funcionario'],
                    'ci' => $it['ci'],
                    'cargo' => $it['cargo'],
                    'unidad' => $it['unidad'] ?? 'ADMINISTRACIÓN Y FINANZAS',
                    'item' => $it['item'],
                    'antiguedad_anios' => $it['anios_antiguedad'] ?? 0,
                    'dias_trabajados' => $it['dias_trabajados'] ?? 30,
                ],
                'ingresos' => [
                    'haber_basico' => (float) $it['haber_basico'],
                    'haber_dias_trabajados' => (float) $it['haber_basico'],
                    'bono_antiguedad' => (float) ($it['bono_antiguedad'] ?? 0),
                    'porcentaje_antiguedad' => (float) ($it['porcentaje_bono'] ?? 0),
                    'total_ganado' => (float) $it['total_ganado'],
                ],
                'descuentos' => [
                    'gestora_12_71' => (float) ($it['gestora_12_71'] ?? 0),
                    'vejez_10' => (float) ($desglose['vejez_10'] ?? 0),
                    'riesgo_1_71' => (float) ($desglose['riesgo_1_71'] ?? 0),
                    'comision_0_5' => (float) ($desglose['comision_0_5'] ?? 0),
                    'solidario_0_5' => (float) ($desglose['solidario_0_5'] ?? 0),
                    'descuento_atraso' => (float) ($it['descuento_atraso'] ?? 0),
                    'total_descuentos' => (float) $it['total_descuentos'],
                ],
                'liquido_pagable' => $liq,
                'liquido_literal' => ReporteFinancieroPdfService::convertirNumeroALetras($liq),
                'fecha_emision' => now()->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'tipo_planilla' => $tipoPlanilla,
            'total_boletas' => $boletas->count(),
            'data' => $boletas,
        ], Response::HTTP_OK);
    }

    /**
     * Padrón General de Personal Institucional con filtros avanzados.
     */
    public function padronPersonal(Request $request): JsonResponse
    {
        $unidadId = $request->query('id_unidad');
        $regionalId = $request->query('id_regional');
        $tipoContrato = $request->query('tipo_contrato');
        $search = $request->query('search');

        $query = Persona::with([
            'fichaPersonal.datosLaborales',
            'fichaPersonal.cas',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'user',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'ILIKE', "%{$search}%")
                    ->orWhere('primer_apellido', 'ILIKE', "%{$search}%")
                    ->orWhere('segundo_apellido', 'ILIKE', "%{$search}%")
                    ->orWhere('nro_documento', 'ILIKE', "%{$search}%");
            });
        }

        $personas = $query->get();

        $padron = $personas->map(function ($p) {
            $puestoAsig = $p->asignacionesPuestos->first();
            $puesto = $puestoAsig?->puesto;
            $unidad = $puesto?->unidadOrganizacional;
            $cas = $p->fichaPersonal?->cas?->first();
            $dl = $p->fichaPersonal?->datosLaborales?->first();

            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombre_completo,
                'ci' => $p->nro_documento,
                'genero' => $p->genero,
                'celular' => $p->telefono_celular ?? '-',
                'correo' => $p->correo_electronico_personal ?? '-',
                'cargo' => $puesto?->nombre ?? 'Sin Asignar',
                'item' => $puestoAsig?->nro_item ?? '-',
                'unidad_organizacional' => $unidad?->nombre ?? 'Sin Unidad',
                'tipo_contrato' => $dl?->tipo_funcionario ?? 'PLANTA',
                'fecha_ingreso' => $dl?->fecha_ingreso ?? ($p->created_at?->format('Y-m-d') ?? ($p->_fecha_creacion ?? date('Y-m-d'))),
                'anios_cas' => $cas ? (int) $cas->anios : 0,
                'tiene_acceso_erp' => $p->user ? 'SI' : 'NO',
            ];
        });

        if ($unidadId) {
            $padron = $padron->filter(fn ($item) => str_contains(strtolower($item['unidad_organizacional']), strtolower((string) $unidadId)));
        }

        return response()->json([
            'success' => true,
            'total' => $padron->count(),
            'data' => $padron->values(),
        ], Response::HTTP_OK);
    }

    /**
     * Kardex Integral / Hoja de Vida del Funcionario.
     */
    public function kardexFuncionarioHtml(int $personaId): JsonResponse
    {
        $persona = Persona::with([
            'fichaPersonal.estudiosAcademicos',
            'fichaPersonal.experienciaLaboral',
            'fichaPersonal.cas',
            'fichaPersonal.datosLaborales',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) {
                $q->latest('fecha')->limit(30);
            },
        ])->findOrFail($personaId);

        return response()->json([
            'success' => true,
            'data' => [
                'persona' => $persona,
                'ficha' => $persona->fichaPersonal,
                'puesto_actual' => $persona->asignacionesPuestos->first()?->puesto,
                'unidad' => $persona->asignacionesPuestos->first()?->puesto?->unidadOrganizacional,
                'estudios' => $persona->fichaPersonal?->estudiosAcademicos ?? [],
                'experiencia' => $persona->fichaPersonal?->experienciaLaboral ?? [],
                'cas' => $persona->fichaPersonal?->cas ?? [],
                'datos_laborales' => $persona->fichaPersonal?->datosLaborales ?? [],
                'fecha_emision' => now()->format('d/m/Y H:i:s'),
                'institucion' => 'EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA',
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Generador de Reportes Personalizados Dinámicos (Custom Report Builder).
     */
    public function generarReportePersonalizado(Request $request): JsonResponse
    {
        $columnas = $request->input('columnas', ['nombres', 'ci', 'cargo', 'unidad', 'tipo_contrato', 'celular']);
        $filtroUnidad = $request->input('unidad');
        $filtroTipoContrato = $request->input('tipo_contrato');
        $filtroGenero = $request->input('genero');

        $personas = Persona::with([
            'fichaPersonal.datosLaborales',
            'fichaPersonal.cas',
            'fichaPersonal.estudiosAcademicos',
            'asignacionesPuestos.puesto.unidadOrganizacional',
        ])->get();

        $filas = $personas->map(function ($p) use ($columnas) {
            $puestoAsig = $p->asignacionesPuestos->first();
            $puesto = $puestoAsig?->puesto;
            $unidad = $puesto?->unidadOrganizacional;
            $cas = $p->fichaPersonal?->cas?->first();
            $dl = $p->fichaPersonal?->datosLaborales?->first();
            $estudio = $p->fichaPersonal?->estudiosAcademicos?->first();

            $fila = ['id' => $p->id];

            if (in_array('nombres', $columnas)) {
                $fila['nombres'] = $p->nombre_completo;
            }
            if (in_array('ci', $columnas)) {
                $fila['ci'] = $p->nro_documento;
            }
            if (in_array('genero', $columnas)) {
                $fila['genero'] = $p->genero ?? 'MASCULINO';
            }
            if (in_array('celular', $columnas)) {
                $fila['celular'] = $p->telefono_celular ?? '-';
            }
            if (in_array('correo', $columnas)) {
                $fila['correo'] = $p->correo_electronico_personal ?? '-';
            }
            if (in_array('cargo', $columnas)) {
                $fila['cargo'] = $puesto?->nombre ?? 'Sin Asignar';
            }
            if (in_array('item', $columnas)) {
                $fila['item'] = $puestoAsig?->nro_item ?? '-';
            }
            if (in_array('unidad', $columnas)) {
                $fila['unidad'] = $unidad?->nombre ?? 'Sin Unidad';
            }
            if (in_array('tipo_contrato', $columnas)) {
                $fila['tipo_contrato'] = $dl?->tipo_funcionario ?? 'PLANTA';
            }
            if (in_array('fecha_ingreso', $columnas)) {
                $fila['fecha_ingreso'] = $dl?->fecha_ingreso ?? '-';
            }
            if (in_array('anios_cas', $columnas)) {
                $fila['anios_cas'] = $cas ? (int) $cas->anios : 0;
            }
            if (in_array('formacion', $columnas)) {
                $fila['formacion'] = $estudio ? "{$estudio->nivel_instruccion} ({$estudio->carrera})" : 'No registrada';
            }

            return $fila;
        });

        // Aplicar filtros opcionales
        if ($filtroGenero) {
            $filas = $filas->where('genero', $filtroGenero);
        }
        if ($filtroTipoContrato) {
            $filas = $filas->where('tipo_contrato', $filtroTipoContrato);
        }

        return response()->json([
            'success' => true,
            'total_registros' => $filas->count(),
            'columnas_seleccionadas' => $columnas,
            'data' => $filas->values(),
        ], Response::HTTP_OK);
    }

    /**
     * Certificado Laboral Oficial con CITE institucional.
     */
    public function certificadoTrabajoHtml(int $personaId): JsonResponse
    {
        $persona = Persona::with([
            'fichaPersonal.datosLaborales',
            'asignacionesPuestos.puesto.unidadOrganizacional',
        ])->findOrFail($personaId);

        $puesto = $persona->asignacionesPuestos->first()?->puesto;
        $unidad = $puesto?->unidadOrganizacional;
        $dl = $persona->fichaPersonal?->datosLaborales?->first();

        $cite = 'CERT-RRHH-'.str_pad((string) $persona->id, 4, '0', STR_PAD_LEFT).'-'.date('Y');

        return response()->json([
            'success' => true,
            'data' => [
                'cite' => $cite,
                'institucion' => 'EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA',
                'funcionario' => $persona->nombre_completo,
                'ci' => $persona->nro_documento,
                'cargo' => $puesto?->nombre ?? 'FUNCIONARIO PÚBLICO',
                'unidad' => $unidad?->nombre ?? 'ADMINISTRACIÓN CENTRAL',
                'fecha_ingreso' => $dl?->fecha_ingreso ?? '02/01/2024',
                'tipo_contrato' => $dl?->tipo_funcionario ?? 'PERSONAL DE PLANTA',
                'fecha_emision' => now()->translatedFormat('d \d\e F \d\e Y'),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Reporte Oficial de Kardex de Vacaciones Institucional (LGT Bolivia).
     */
    public function kardexVacaciones(): JsonResponse
    {
        $personas = Persona::with([
            'fichaPersonal.datosLaborales',
            'fichaPersonal.cas',
            'asignacionesPuestos.puesto',
        ])
        ->where('_estado', 'ACTIVO')
        ->orderBy('primer_apellido')
        ->orderBy('nombres')
        ->get();

        $hoy = now();

        $data = $personas->map(function ($p) use ($hoy) {
            $dl = $p->fichaPersonal?->datosLaborales?->first();
            $cas = $p->fichaPersonal?->cas?->first();
            $puesto = $p->asignacionesPuestos?->first()?->puesto;

            // Antigüedad en años
            $aniosAntiguedad = 0;
            if ($cas && $cas->anios > 0) {
                $aniosAntiguedad = (int) $cas->anios;
            } elseif ($dl && $dl->fecha_ingreso) {
                $aniosAntiguedad = (int) Carbon::parse($dl->fecha_ingreso)->diffInYears($hoy);
            }

            // Días de vacación solicitados y aprobados (Permiso ID 9: VACACION ANUAL)
            $diasSolicitados = (int) DB::table('rrhh.usuarios_solicitudes_salidas as us')
                ->join('rrhh.solicitudes_salidas as s', 's.id', '=', 'us.id_solicitud_salida')
                ->where('us.id_persona', $p->id)
                ->where('s.id_permiso', 9)
                ->where('us.estado_aprobacion', 'APROBADO')
                ->where('s._estado', 'ACTIVO')
                ->sum(DB::raw("CASE WHEN s.fecha_fin IS NOT NULL AND s.fecha_inicio IS NOT NULL THEN (s.fecha_fin - s.fecha_inicio + 1) ELSE 1 END"));

            return [
                'id' => $p->id,
                'nombres' => $p->nombres,
                'primer_apellido' => $p->primer_apellido,
                'segundo_apellido' => $p->segundo_apellido,
                'nro_documento' => $p->nro_documento,
                'cargo' => $puesto?->nombre ?? 'Funcionario',
                'fecha_ingreso' => $dl?->fecha_ingreso ?? ($p->_fecha_creacion ? Carbon::parse($p->_fecha_creacion)->format('Y-m-d') : null),
                'anios_antiguedad' => $aniosAntiguedad,
                'dias_solicitados' => $diasSolicitados,
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $data->count(),
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Exporta la planilla mensual completa en formato Excel multi-hoja (.xlsx).
     */
    public function exportarPlanillaExcel(Request $request)
    {
        $idPlanilla = $request->query('id_planilla');
        if ($idPlanilla) {
            $p = DB::table('rrhh.planillas_consolidadas')->where('id', $idPlanilla)->first();
            if ($p) {
                $mes = (int) $p->mes;
                $anio = (int) $p->gestion;
            }
        }
        $mes = $mes ?? (int) $request->query('mes', date('m'));
        $anio = $anio ?? (int) $request->query('anio', date('Y'));

        $exportService = app(\App\Services\Rrhh\PlanillaExcelExportService::class);
        return $exportService->exportarPlanillaMensual($mes, $anio);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF BINARIOS — Para ModalVisorPdf (igual que Facturación / Comercial)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera la Boleta Individual de Pago en PDF binario (para ModalVisorPdf).
     */
    public function boletaPagoPdf(int $personaId, Request $request)
    {
        // Reutilizamos el mismo cálculo del endpoint HTML
        $jsonResponse = $this->boletaPagoHtml($personaId, $request);
        $payload = $jsonResponse->getData(true);

        if (! ($payload['success'] ?? false)) {
            abort(404, 'No se encontraron datos para la boleta.');
        }

        $data = $payload['data'];

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.boleta-pago-pdf', [
            'data' => $data,
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', 4)
            ->setOption('margin-bottom', 4)
            ->setOption('margin-left', 6)
            ->setOption('margin-right', 6)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));
        $nombreFuncionario = str_replace(' ', '_', $data['funcionario']['nombre_completo'] ?? 'funcionario');
        $filename = "BOLETA_{$nombreFuncionario}_{$mes}_{$anio}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Genera la Planilla Mensual de Sueldos en PDF binario (para ModalVisorPdf).
     */
    public function planillaSueldosPdf(Request $request)
    {
        $idPlanilla = $request->query('id_planilla');
        if ($idPlanilla) {
            $planConsolidada = DB::table('rrhh.planillas_consolidadas')->where('id', $idPlanilla)->first();
            if ($planConsolidada) {
                $mes = (int) $planConsolidada->mes;
                $anio = (int) $planConsolidada->gestion;
                $tipoPlanilla = $planConsolidada->tipo_planilla;
                $request->merge(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => $tipoPlanilla]);
            }
        }

        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));
        $tipoPlanilla = $request->query('tipo_planilla', 'PLANTA_PERMANENTE');

        // Reutilizamos el cálculo de planilla
        $jsonResponse = $this->planillaSueldosMensual($request);
        $payload = $jsonResponse->getData(true);

        $items = $payload['data'] ?? [];

        // Buscar planilla declarada
        $planillaExistente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('_estado', 'ACTIVO')
            ->first();

        $esDeclarada = (bool) $planillaExistente && ($planillaExistente->estado === 'DECLARADA');
        $cite = $planillaExistente?->cite_oficial ?: ($esDeclarada ? "PLA-EMAPA-{$tipoPlanilla}-" . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . "-{$anio}" : "BORRADOR-{$mes}/{$anio}");

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $tipoPlanillaNombres = [
            'PLANTA_PERMANENTE' => 'Planta Permanente',
            'PERSONAL_EVENTUAL' => 'Personal Eventual',
            'DIRECTORIO'        => 'Directorio',
        ];

        $totales = [
            'total_haber_basico' => collect($items)->sum(fn($i) => (float) ($i['haber_basico'] ?? 0)),
            'total_bono'         => collect($items)->sum(fn($i) => (float) ($i['bono_antiguedad'] ?? 0)),
            'total_ganado'       => collect($items)->sum(fn($i) => (float) ($i['total_ganado'] ?? 0)),
            'total_gestora'      => collect($items)->sum(fn($i) => (float) ($i['gestora_12_71'] ?? 0)),
            'total_liquido'      => collect($items)->sum(fn($i) => (float) ($i['liquido_salarial'] ?? 0)),
        ];

        $configLaboral = ConfiguracionLaboral::orderBy('id', 'desc')->first();
        $patronalPct = $configLaboral ? (float) $configLaboral->porcentaje_aporte_patronal : 17.21;

        $patronal = $payload['patronal'] ?? [
            'cns_10_bs'                  => round($totales['total_ganado'] * 0.10, 2),
            'gestora_7_21_bs'            => round($totales['total_ganado'] * 0.0721, 2),
            'riesgo_profesional_1_71_bs' => round($totales['total_ganado'] * 0.0171, 2),
            'pro_vivienda_2_bs'          => round($totales['total_ganado'] * 0.02, 2),
            'solidario_patronal_3_bs'    => round($totales['total_ganado'] * 0.03, 2),
            'comision_patronal_0_5_bs'   => round($totales['total_ganado'] * 0.005, 2),
            'total_patronal_bs'          => round($totales['total_ganado'] * 0.1721, 2),
            'costo_total_empresa_bs'     => round($totales['total_ganado'] * 1.1721, 2),
        ];

        $logoPath = public_path('images/emapa_logo_horizontal.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.planilla-sueldos-pdf', [
            'items'                 => $items,
            'totales'               => $totales,
            'patronal'              => $patronal,
            'mes_nombre'            => $mesesNombres[$mes] ?? 'Mes',
            'anio'                  => $anio,
            'tipo_planilla_nombre'  => $tipoPlanillaNombres[$tipoPlanilla] ?? $tipoPlanilla,
            'es_declarada'          => $esDeclarada,
            'cite'                  => $cite,
            'total_liquido_literal' => ReporteFinancieroPdfService::convertirNumeroALetras((float)$totales['total_liquido']),
            'fecha_emision'         => now()->format('d/m/Y H:i'),
            'logo_base64'           => $logoBase64,
            'configLaboral'         => $configLaboral,
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', 8)
            ->setOption('margin-bottom', 8)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        $filename = "PLANILLA_EMAPAP_{$mes}_{$anio}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Genera todas las Boletas de Pago del mes en PDF binario multipágina (para ModalVisorPdf).
     */
    public function boletasMasivasPdf(Request $request)
    {
        $idPlanilla = $request->query('id_planilla');
        if ($idPlanilla) {
            $planConsolidada = DB::table('rrhh.planillas_consolidadas')->where('id', $idPlanilla)->first();
            if ($planConsolidada) {
                $mes = (int) $planConsolidada->mes;
                $anio = (int) $planConsolidada->gestion;
                $tipoPlanilla = $planConsolidada->tipo_planilla;
                $request->merge(['mes' => $mes, 'anio' => $anio, 'tipo_planilla' => $tipoPlanilla]);
            }
        }

        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));
        $tipoPlanilla = $request->query('tipo_planilla', 'PLANTA_PERMANENTE');

        $jsonResponse = $this->boletasPagoMesHtml($request);
        $payload = $jsonResponse->getData(true);
        $boletas = $payload['data'] ?? [];

        if (empty($boletas)) {
            abort(404, 'No hay boletas generadas para el período seleccionado.');
        }

        $mesesNombres = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.boletas-masivas-pdf', [
            'boletas'       => $boletas,
            'periodo'       => ($mesesNombres[$mes] ?? 'MES') . ' ' . $anio,
            'fecha_emision' => now()->format('d/m/Y H:i'),
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', 4)
            ->setOption('margin-bottom', 4)
            ->setOption('margin-left', 6)
            ->setOption('margin-right', 6)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        $filename = "BOLETAS_MASIVAS_{$mes}_{$anio}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Genera la Boleta Oficial de Salida / Permiso en PDF binario (para ModalVisorPdf).
     */
    public function boletaSalidaPdf(int $id)
    {
        $solicitud = SolicitudSalida::with(['permiso'])->findOrFail($id);

        $asignacion = DB::table('rrhh.usuarios_solicitudes_salidas as uss')
            ->join('rrhh.personas as p', 'p.id', '=', 'uss.id_persona')
            ->leftJoin('rrhh.asignaciones_puestos as ap', function ($j) {
                $j->on('ap.id_persona', '=', 'p.id')->where('ap._estado', '=', 'ACTIVO');
            })
            ->leftJoin('rrhh.puestos as puesto', 'puesto.id', '=', 'ap.id_puesto')
            ->leftJoin('rrhh.unidades_organizacionales as uo', 'uo.id', '=', 'puesto.id_unidad_organizacional')
            ->where('uss.id_solicitud_salida', $id)
            ->select(
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nro_documento',
                'puesto.nombre as cargo',
                'uo.nombre as unidad',
                'uss.estado_aprobacion',
                'uss.fecha_revision'
            )
            ->first();

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.boleta-salida-pdf', [
            'solicitud'     => $solicitud,
            'funcionario'   => $asignacion,
            'fecha_emision' => now()->format('d/m/Y H:i'),
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        $cleanCite = preg_replace('/[^A-Za-z0-9_-]/', '_', $solicitud->cite ?? 'BOLETA_SALIDA');
        $filename = "{$cleanCite}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Genera el Kardex Oficial de Vacaciones en PDF apaisado de alta resolución (para ModalVisorPdf).
     */
    public function kardexVacacionesPdf()
    {
        $raw = $this->kardexVacaciones()->getData(true);
        $items = $raw['data'] ?? [];

        $totalDerecho = 0;
        $totalGozados = 0;
        $totalSaldo = 0;

        $procesados = collect($items)->map(function ($it) use (&$totalDerecho, &$totalGozados, &$totalSaldo) {
            $anios = (int) ($it['anios_antiguedad'] ?? 0);
            $derecho = 15;
            $escala = '1 a 5 años (15 d.)';
            if ($anios >= 10) {
                $derecho = 30;
                $escala = '10+ años (30 d.)';
            } elseif ($anios >= 5) {
                $derecho = 20;
                $escala = '5 a 10 años (20 d.)';
            }

            $gozados = (int) ($it['dias_solicitados'] ?? 0);
            $saldo = max(0, $derecho - $gozados);

            $totalDerecho += $derecho;
            $totalGozados += $gozados;
            $totalSaldo += $saldo;

            $nom = trim(($it['nombres'] ?? '') . ' ' . ($it['primer_apellido'] ?? '') . ' ' . ($it['segundo_apellido'] ?? ''));

            return [
                'funcionario'      => $nom ?: 'Funcionario',
                'ci'               => $it['nro_documento'] ?? '-',
                'cargo'            => $it['cargo'] ?? 'Personal',
                'fecha_ingreso'    => $it['fecha_ingreso'] ? Carbon::parse($it['fecha_ingreso'])->format('d/m/Y') : '-',
                'antiguedad_anios' => $anios,
                'escala_texto'     => $escala,
                'dias_derecho'     => $derecho,
                'dias_gozados'     => $gozados,
                'saldo'            => $saldo,
            ];
        })->toArray();

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.kardex-vacaciones-pdf', [
            'data'          => $procesados,
            'total_derecho' => $totalDerecho,
            'total_gozados' => $totalGozados,
            'total_saldo'   => $totalSaldo,
            'fecha_emision' => now()->format('d/m/Y H:i'),
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', 8)
            ->setOption('margin-bottom', 8)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        $filename = "KARDEX_VACACIONES_EMAPAP_" . date('Y_m') . ".pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}

