<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\ConfiguracionLaboral;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ConfiguracionLaboralController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Obtiene la configuración laboral y salarial vigente para la gestión solicitada.
     */
    public function obtener(Request $request): JsonResponse
    {
        $gestion = (int) $request->query('gestion', date('Y'));
        $config = ConfiguracionLaboral::obtenerVigente($gestion);

        return response()->json([
            'success' => true,
            'data' => $config,
        ], Response::HTTP_OK);
    }

    /**
     * Actualiza los parámetros salariales y laborales oficiales.
     */
    public function guardar(Request $request): JsonResponse
    {
        $input = $request->all();

        // Asignar defaults y mapear alias del frontend
        if (!isset($input['gestion'])) {
            $input['gestion'] = (int) date('Y');
        }
        if (!isset($input['tarifa_refrigerio_diaria']) && isset($input['monto_refrigerio_diario'])) {
            $input['tarifa_refrigerio_diaria'] = $input['monto_refrigerio_diario'];
        }
        if (!isset($input['dias_laborales_base']) && isset($input['dias_laborables_mes'])) {
            $input['dias_laborales_base'] = $input['dias_laborables_mes'];
        }
        if (!isset($input['horas_jornada_diaria']) && isset($input['horas_jornada_ordinaria'])) {
            $input['horas_jornada_diaria'] = $input['horas_jornada_ordinaria'];
        }
        if (!isset($input['factor_hora_extra']) && isset($input['factor_horas_extra'])) {
            $input['factor_hora_extra'] = $input['factor_horas_extra'];
        }
        if (!isset($input['factor_dominical'])) {
            $input['factor_dominical'] = 2.0;
        }
        if (!isset($input['multiplicador_smn_antiguedad'])) {
            $input['multiplicador_smn_antiguedad'] = 3;
        }
        if (!isset($input['escalas_bono_antiguedad']) && isset($input['escala_antiguedad'])) {
            $input['escalas_bono_antiguedad'] = $input['escala_antiguedad'];
        }
        if (!isset($input['porcentaje_gestora_vejez']) && isset($input['gestora_vejez_porcentaje'])) {
            $input['porcentaje_gestora_vejez'] = $input['gestora_vejez_porcentaje'];
        }
        if (!isset($input['porcentaje_gestora_riesgo_comun']) && isset($input['gestora_riesgo_comun_porcentaje'])) {
            $input['porcentaje_gestora_riesgo_comun'] = $input['gestora_riesgo_comun_porcentaje'];
        }
        if (!isset($input['porcentaje_gestora_comision']) && isset($input['gestora_comision_porcentaje'])) {
            $input['porcentaje_gestora_comision'] = $input['gestora_comision_porcentaje'];
        }
        if (!isset($input['porcentaje_gestora_solidario']) && isset($input['gestora_laboral_solidario_porcentaje'])) {
            $input['porcentaje_gestora_solidario'] = $input['gestora_laboral_solidario_porcentaje'];
        }
        if (!isset($input['porcentaje_patronal_cns']) && isset($input['patronal_cns_porcentaje'])) {
            $input['porcentaje_patronal_cns'] = $input['patronal_cns_porcentaje'];
        }
        if (!isset($input['porcentaje_patronal_gestora_riesgo']) && isset($input['patronal_riesgo_profesional_porcentaje'])) {
            $input['porcentaje_patronal_gestora_riesgo'] = $input['patronal_riesgo_profesional_porcentaje'];
        }
        if (!isset($input['porcentaje_patronal_gestora_pro_vivienda']) && isset($input['patronal_pro_vivienda_porcentaje'])) {
            $input['porcentaje_patronal_gestora_pro_vivienda'] = $input['patronal_pro_vivienda_porcentaje'];
        }
        if (!isset($input['porcentaje_patronal_gestora_sol']) && isset($input['patronal_solidario_porcentaje'])) {
            $input['porcentaje_patronal_gestora_sol'] = $input['patronal_solidario_porcentaje'];
        }
        if (!isset($input['porcentaje_patronal_gestora_comision']) && isset($input['patronal_comision_porcentaje'])) {
            $input['porcentaje_patronal_gestora_comision'] = $input['patronal_comision_porcentaje'];
        }
        if (!isset($input['descripcion']) && isset($input['notas_resolucion'])) {
            $input['descripcion'] = $input['notas_resolucion'];
        }

        $request->merge($input);

        $validator = Validator::make($request->all(), [
            'gestion' => 'required|integer|min:2020|max:2050',
            'salario_minimo_nacional' => 'required|numeric|min:1000',
            'porcentaje_gestora_vejez' => 'required|numeric|min:0|max:100',
            'porcentaje_gestora_riesgo_comun' => 'required|numeric|min:0|max:100',
            'porcentaje_gestora_comision' => 'required|numeric|min:0|max:100',
            'porcentaje_gestora_solidario' => 'required|numeric|min:0|max:100',
            'porcentaje_patronal_cns' => 'required|numeric|min:0|max:100',
            'porcentaje_patronal_gestora_sol' => 'required|numeric|min:0|max:100',
            'porcentaje_patronal_gestora_riesgo' => 'required|numeric|min:0|max:100',
            'porcentaje_patronal_gestora_pro_vivienda' => 'required|numeric|min:0|max:100',
            'tarifa_refrigerio_diaria' => 'required|numeric|min:0',
            'dias_laborales_base' => 'required|integer|min:1|max:31',
            'horas_jornada_diaria' => 'required|integer|min:1|max:24',
            'factor_hora_extra' => 'required|numeric|min:1',
            'factor_dominical' => 'required|numeric|min:1',
            'escalas_bono_antiguedad' => 'required|array',
            'multiplicador_smn_antiguedad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $gestion = (int) $request->input('gestion');
        $rawVejez = (float) $request->input('porcentaje_gestora_vejez');
        $isPercentScale = ($rawVejez >= 1.0);
        $norm = fn($val) => (float) ($isPercentScale ? round(((float)$val) / 100, 6) : round((float)$val, 6));

        $vejez = $norm($request->input('porcentaje_gestora_vejez'));
        $riesgoLab = $norm($request->input('porcentaje_gestora_riesgo_comun'));
        $comision = $norm($request->input('porcentaje_gestora_comision'));
        $solLab = $norm($request->input('porcentaje_gestora_solidario'));
        $totalGestoraLaboral = round($vejez + $riesgoLab + $comision + $solLab, 4);

        $cns = $norm($request->input('porcentaje_patronal_cns'));
        $patSol = $norm($request->input('porcentaje_patronal_gestora_sol'));
        $patRiesgo = $norm($request->input('porcentaje_patronal_gestora_riesgo'));
        $patVivienda = $norm($request->input('porcentaje_patronal_gestora_pro_vivienda'));
        $patComision = $norm($request->input('porcentaje_patronal_gestora_comision', $request->input('patronal_comision_porcentaje', 0.50)));
        $totalGestoraPatronal = round($patSol + $patRiesgo + $patVivienda + $patComision, 4);
        $totalPatronal = round($cns + $totalGestoraPatronal, 4);

        $userId = auth()->id() ? (int) auth()->id() : 1;

        $config = DB::transaction(function () use ($request, $gestion, $vejez, $riesgoLab, $comision, $solLab, $totalGestoraLaboral, $cns, $patSol, $patRiesgo, $patVivienda, $totalGestoraPatronal, $totalPatronal, $userId) {
            $registro = ConfiguracionLaboral::where('gestion', $gestion)
                ->where('es_vigente', true)
                ->where('_estado', 'ACTIVO')
                ->first();

            $data = [
                'gestion' => $gestion,
                'descripcion' => $request->input('descripcion', "Parámetros Salariales Oficiales EMAPA ({$gestion})"),
                'nro_patronal_min_trabajo' => (string) $request->input('nro_patronal_min_trabajo', '1002393029-1'),
                'nro_patronal_cns' => (string) $request->input('nro_patronal_cns', '01-521-00002'),
                'nit_institucional' => (string) $request->input('nit_institucional', '1002393029'),
                'ubicacion_geografica' => (string) $request->input('ubicacion_geografica', 'PATACAMAYA-LA PAZ-BOLIVIA'),
                'direccion_institucional' => (string) $request->input('direccion_institucional', 'PLAZA BOLIVAR - ZONA ESTACION'),
                'salario_minimo_nacional' => (float) $request->input('salario_minimo_nacional'),
                'porcentaje_gestora_vejez' => $vejez,
                'porcentaje_gestora_riesgo_comun' => $riesgoLab,
                'porcentaje_gestora_comision' => $comision,
                'porcentaje_gestora_solidario' => $solLab,
                'porcentaje_gestora_total' => $totalGestoraLaboral,
                'porcentaje_patronal_cns' => $cns,
                'porcentaje_patronal_gestora_sol' => $patSol,
                'porcentaje_patronal_gestora_riesgo' => $patRiesgo,
                'porcentaje_patronal_gestora_pro_vivienda' => $patVivienda,
                'porcentaje_patronal_gestora_total' => $totalGestoraPatronal,
                'porcentaje_patronal_total' => $totalPatronal,
                'tarifa_refrigerio_diaria' => (float) $request->input('tarifa_refrigerio_diaria'),
                'dias_laborales_base' => (int) $request->input('dias_laborales_base'),
                'horas_jornada_diaria' => (int) $request->input('horas_jornada_diaria'),
                'factor_hora_extra' => (float) $request->input('factor_hora_extra'),
                'factor_dominical' => (float) $request->input('factor_dominical'),
                'escalas_bono_antiguedad' => $request->input('escalas_bono_antiguedad'),
                'multiplicador_smn_antiguedad' => (int) $request->input('multiplicador_smn_antiguedad'),
                'alicuota_rc_iva' => (float) $request->input('alicuota_rc_iva', 0.13),
                'minimos_no_imponibles_rc_iva' => (int) $request->input('minimos_no_imponibles_rc_iva', 2),
                'es_vigente' => true,
                '_usuario_modificacion' => $userId,
                '_fecha_modificacion' => now(),
            ];

            if ($registro) {
                $registro->update($data);
                $configFinal = $registro->fresh();
            } else {
                $data['_estado'] = 'ACTIVO';
                $data['_transaccion'] = 'CREAR';
                $data['_usuario_creacion'] = $userId;
                $data['_fecha_creacion'] = now();
                $configFinal = ConfiguracionLaboral::create($data);
            }

            $this->auditService->log(
                event: 'rrhh_configuracion_laboral_updated',
                model: $configFinal,
                newValues: $configFinal->toArray()
            );

            return $configFinal;
        });

        return response()->json([
            'success' => true,
            'message' => 'Parámetros salariales y laborales actualizados exitosamente.',
            'data' => $config,
        ], Response::HTTP_OK);
    }
}
