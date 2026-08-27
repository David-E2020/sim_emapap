<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\SolicitudSalida;
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
        $mes = (int)$request->query('mes', date('m'));
        $anio = (int)$request->query('anio', date('Y'));

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
        $mes = (int)$request->query('mes', date('m'));
        $anio = (int)$request->query('anio', date('Y'));
        $tarifaDiaria = (float)$request->query('tarifa', 18.0); // Tarifa oficial estándar Bs. 18

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
            $aniosServicio = $cas ? (int)$cas->anios : 1;

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
                'dias_utilizados' => round((float)$diasTomados, 1),
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
        $mes = (int)$request->query('mes', date('m'));
        $anio = (int)$request->query('anio', date('Y'));

        // 1. Verificar si ya existe una planilla CONSOLIDADA / DECLARADA (Snapshot Inmutable)
        $planillaExistente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', 'SUELDOS_Y_SALARIOS')
            ->where('_estado', 'ACTIVO')
            ->first();

        if ($planillaExistente) {
            $detalles = DB::table('rrhh.detalles_planillas')
                ->where('id_planilla_consolidada', $planillaExistente->id)
                ->get();

            return response()->json([
                'success' => true,
                'mes' => $mes,
                'anio' => $anio,
                'es_declarada' => true,
                'estado_planilla' => $planillaExistente->estado,
                'cite_oficial' => $planillaExistente->cite_oficial,
                'fecha_cierre' => $planillaExistente->fecha_cierre,
                'smn_aplicado' => (float)$planillaExistente->smn_aplicado,
                'total_planilla_bs' => (float)$planillaExistente->total_liquido_pagable_bs,
                'total_ganado_bs' => (float)$planillaExistente->total_ganado_bs,
                'total_descuentos_bs' => (float)$planillaExistente->total_descuentos_bs,
                'data' => $detalles,
            ], Response::HTTP_OK);
        }

        // 2. Si no está cerrada, calcular la simulación en vivo con las paramétricas actuales
        $configSalarial = DB::table('parametricas')
            ->where('param_tabla', 'TABLA_RRHH_CONFIGURACION_SALARIAL')
            ->where('param_estado', 'A')
            ->where('param_valor', '>', 0)
            ->pluck('param_descripcion', 'param_codigo');

        $smn = (float)($request->query('smn') ?: ($configSalarial['SMN_BOLIVIA'] ?? 2500.0));
        $porcentajeGestora = (float)($configSalarial['APORTE_GESTORA'] ?? 12.71) / 100.0;
        $tarifaRefrigerio = (float)($configSalarial['TARIFA_REFRIGERIO'] ?? 18.0);

        // Cargar rangos de Bono de Antigüedad desde TABLA_RRHH_BONO_ANTIGUEDAD
        $escalasParam = DB::table('parametricas')
            ->where('param_tabla', 'TABLA_RRHH_BONO_ANTIGUEDAD')
            ->where('param_estado', 'A')
            ->where('param_valor', '>', 0)
            ->get();

        $escalasBono = [];
        foreach ($escalasParam as $ep) {
            $meta = json_decode((string)$ep->param_descripcion, true);
            if ($meta && isset($meta['min'], $meta['max'], $meta['porcentaje'])) {
                $escalasBono[] = $meta;
            }
        }

        $personas = Persona::with([
            'fichaPersonal.cas',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
            },
        ])->get();

        $planilla = $personas->map(function ($p) use ($smn, $porcentajeGestora, $tarifaRefrigerio, $escalasBono) {
            $puestoAsig = $p->asignacionesPuestos->first();
            $puesto = $puestoAsig?->puesto;
            $haberBasico = 5200.0; // Base por defecto

            if ($puesto && $puesto->id_escala_salarial) {
                $escala = DB::table('rrhh.escalas_salariales')->where('id', $puesto->id_escala_salarial)->first();
                if ($escala) {
                    $haberBasico = (float)($escala->salario ?? $escala->salario_mensual ?? 5200.0);
                }
            }

            // Antigüedad CAS del funcionario
            $cas = $p->fichaPersonal?->cas?->first();
            $anios = $cas ? (int)$cas->anios : 0;

            // Porcentaje dinámico desde la tabla Paramétricas
            $porcentajeBono = 0.0;
            if (!empty($escalasBono)) {
                foreach ($escalasBono as $rango) {
                    if ($anios >= $rango['min'] && $anios <= $rango['max']) {
                        $porcentajeBono = (float)$rango['porcentaje'];
                        break;
                    }
                }
            } else {
                // Fallback legal oficial DS 21060
                if ($anios >= 2 && $anios <= 4) $porcentajeBono = 0.05;
                elseif ($anios >= 5 && $anios <= 7) $porcentajeBono = 0.11;
                elseif ($anios >= 8 && $anios <= 10) $porcentajeBono = 0.18;
                elseif ($anios >= 11 && $anios <= 14) $porcentajeBono = 0.26;
                elseif ($anios >= 15 && $anios <= 19) $porcentajeBono = 0.34;
                elseif ($anios >= 20 && $anios <= 24) $porcentajeBono = 0.42;
                elseif ($anios >= 25) $porcentajeBono = 0.50;
            }

            $bonoAntiguedad = (3 * $smn) * $porcentajeBono;
            $totalGanado = $haberBasico + $bonoAntiguedad;

            // Descuentos de Ley: Gestora Pública (12.71% configurable)
            $gestoraAporte = $totalGanado * $porcentajeGestora;

            // Descuentos por Atraso en minutos
            $totalMinutosAtraso = $p->asistencias->sum('minutos_de_atraso_primer_periodo') + $p->asistencias->sum('minutos_de_atraso_segundo_periodo');
            $costoMinuto = ($haberBasico / 30) / (8 * 60);
            $descuentoAtraso = $totalMinutosAtraso * $costoMinuto;

            $totalDescuentos = $gestoraAporte + $descuentoAtraso;
            $liquidoSalarial = $totalGanado - $totalDescuentos;

            // Refrigerios
            $diasRefrigerio = $p->asistencias->where('merece_refrigerio', true)->count();
            $totalRefrigerio = $diasRefrigerio * $tarifaRefrigerio;

            $totalAPagar = $liquidoSalarial + $totalRefrigerio;

            return [
                'id' => $p->id,
                'id_persona' => $p->id,
                'funcionario' => $p->nombre_completo,
                'ci' => $p->nro_documento,
                'cargo' => $puesto?->nombre ?? 'Funcionario',
                'item' => $puestoAsig?->nro_item ?? '-',
                'haber_basico' => round($haberBasico, 2),
                'anios_antiguedad' => $anios,
                'porcentaje_bono' => round($porcentajeBono * 100, 1),
                'bono_antiguedad' => round($bonoAntiguedad, 2),
                'total_ganado' => round($totalGanado, 2),
                'gestora_12_71' => round($gestoraAporte, 2),
                'minutos_atraso' => $totalMinutosAtraso,
                'descuento_atraso' => round($descuentoAtraso, 2),
                'total_descuentos' => round($totalDescuentos, 2),
                'liquido_salarial' => round($liquidoSalarial, 2),
                'dias_refrigerio' => $diasRefrigerio,
                'refrigerio_bs' => round($totalRefrigerio, 2),
                'liquido_pagable_total' => round($totalAPagar, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'es_declarada' => false,
            'estado_planilla' => 'BORRADOR_SIMULACION',
            'smn_aplicado' => $smn,
            'total_planilla_bs' => round($planilla->sum('liquido_pagable_total'), 2),
            'total_ganado_bs' => round($planilla->sum('total_ganado'), 2),
            'total_descuentos_bs' => round($planilla->sum('total_descuentos'), 2),
            'data' => $planilla,
        ], Response::HTTP_OK);
    }

    /**
     * Cierra y declara formalmente la planilla salarial generando un snapshot inmutable.
     */
    public function cerrarYDeclararPlanilla(Request $request): JsonResponse
    {
        $mes = (int)$request->input('mes', date('m'));
        $anio = (int)$request->input('anio', date('Y'));
        $tipo = $request->input('tipo_planilla', 'SUELDOS_Y_SALARIOS');

        $existe = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', $tipo)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => "La planilla de {$mes}/{$anio} ya se encuentra cerrada y declarada. No puede ser sobreescrita.",
            ], Response::HTTP_CONFLICT);
        }

        // Generar la simulación calculada
        $calculo = $this->planillaSueldosMensual($request)->getData(true);
        $items = $calculo['data'] ?? [];

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'No hay funcionarios para consolidar en la planilla.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cite = 'PLA-EMAPA-' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . '-' . $anio;

        $idPlanilla = DB::table('rrhh.planillas_consolidadas')->insertGetId([
            'gestion' => $anio,
            'mes' => $mes,
            'tipo_planilla' => $tipo,
            'cite_oficial' => $cite,
            'smn_aplicado' => $calculo['smn_aplicado'] ?? 2500.0,
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
                'item' => (string)($it['item'] ?? '-'),
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
     * Boleta Individual Oficial de Pago Salarial (Papeleta de Pago Imprimible).
     */
    public function boletaPagoHtml(int $personaId, Request $request): JsonResponse
    {
        $mes = (int)$request->query('mes', date('m'));
        $anio = (int)$request->query('anio', date('Y'));

        $persona = Persona::with([
            'fichaPersonal.cas',
            'asignacionesPuestos.puesto.unidadOrganizacional',
            'asistencias' => function ($q) use ($mes, $anio) {
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
            },
        ])->findOrFail($personaId);

        // Verificar si la planilla está consolidada
        $planillaExistente = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', 'SUELDOS_Y_SALARIOS')
            ->first();

        $detalleConsolidado = null;
        if ($planillaExistente) {
            $detalleConsolidado = DB::table('rrhh.detalles_planillas')
                ->where('id_planilla_consolidada', $planillaExistente->id)
                ->where('id_persona', $personaId)
                ->first();
        }

        if ($detalleConsolidado) {
            $data = (array)$detalleConsolidado;
            $cite = $planillaExistente->cite_oficial;
            $esCerrada = true;
        } else {
            // Calcular en vivo
            $calculoResponse = $this->planillaSueldosMensual(new Request(['mes' => $mes, 'anio' => $anio]))->getData(true);
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
            $cite = 'BORRADOR-' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . '/' . $anio;
            $esCerrada = false;
        }

        $mesesNombres = [1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'];
        $mesNombre = $mesesNombres[$mes] ?? 'MES';

        $puesto = $persona->asignacionesPuestos->first()?->puesto;
        $unidad = $puesto?->unidadOrganizacional?->nombre ?? 'ADMINISTRACIÓN CENTRAL';

        return response()->json([
            'success' => true,
            'data' => [
                'boleta_nro' => $cite,
                'es_cerrada' => $esCerrada,
                'periodo' => "{$mesNombre} {$anio}",
                'institucion' => 'EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA',
                'funcionario' => [
                    'id' => $persona->id,
                    'nombre_completo' => $persona->nombre_completo,
                    'ci' => $persona->nro_documento,
                    'cargo' => $data['cargo'] ?? ($puesto?->nombre ?? 'Funcionario'),
                    'unidad' => $unidad,
                    'item' => $data['item'] ?? '-',
                    'antiguedad_anios' => $data['anios_antiguedad'] ?? 0,
                ],
                'ingresos' => [
                    'haber_basico' => (float)($data['haber_basico'] ?? 0),
                    'bono_antiguedad' => (float)($data['bono_antiguedad'] ?? 0),
                    'porcentaje_antiguedad' => (float)($data['porcentaje_bono'] ?? 0),
                    'total_ganado' => (float)($data['total_ganado'] ?? 0),
                    'refrigerios_bs' => (float)($data['refrigerio_bs'] ?? 0),
                    'dias_refrigerio' => (int)($data['dias_refrigerio'] ?? 0),
                ],
                'descuentos' => [
                    'gestora_12_71' => (float)($data['gestora_12_71'] ?? 0),
                    'minutos_atraso' => (int)($data['minutos_atraso'] ?? 0),
                    'descuento_atraso' => (float)($data['descuento_atraso'] ?? 0),
                    'total_descuentos' => (float)($data['total_descuentos'] ?? 0),
                ],
                'liquido_pagable' => (float)($data['liquido_pagable_total'] ?? 0),
                'fecha_emision' => now()->format('d/m/Y H:i'),
            ],
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
            'user'
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
                'anios_cas' => $cas ? (int)$cas->anios : 0,
                'tiene_acceso_erp' => $p->user ? 'SI' : 'NO',
            ];
        });

        if ($unidadId) {
            $padron = $padron->filter(fn($item) => str_contains(strtolower($item['unidad_organizacional']), strtolower((string)$unidadId)));
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

            if (in_array('nombres', $columnas)) $fila['nombres'] = $p->nombre_completo;
            if (in_array('ci', $columnas)) $fila['ci'] = $p->nro_documento;
            if (in_array('genero', $columnas)) $fila['genero'] = $p->genero ?? 'MASCULINO';
            if (in_array('celular', $columnas)) $fila['celular'] = $p->telefono_celular ?? '-';
            if (in_array('correo', $columnas)) $fila['correo'] = $p->correo_electronico_personal ?? '-';
            if (in_array('cargo', $columnas)) $fila['cargo'] = $puesto?->nombre ?? 'Sin Asignar';
            if (in_array('item', $columnas)) $fila['item'] = $puestoAsig?->nro_item ?? '-';
            if (in_array('unidad', $columnas)) $fila['unidad'] = $unidad?->nombre ?? 'Sin Unidad';
            if (in_array('tipo_contrato', $columnas)) $fila['tipo_contrato'] = $dl?->tipo_funcionario ?? 'PLANTA';
            if (in_array('fecha_ingreso', $columnas)) $fila['fecha_ingreso'] = $dl?->fecha_ingreso ?? '-';
            if (in_array('anios_cas', $columnas)) $fila['anios_cas'] = $cas ? (int)$cas->anios : 0;
            if (in_array('formacion', $columnas)) $fila['formacion'] = $estudio ? "{$estudio->nivel_instruccion} ({$estudio->carrera})" : 'No registrada';

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

        $cite = 'CERT-RRHH-' . str_pad((string)$persona->id, 4, '0', STR_PAD_LEFT) . '-' . date('Y');

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
}

