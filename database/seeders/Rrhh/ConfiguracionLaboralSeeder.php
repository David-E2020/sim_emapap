<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use App\Models\Rrhh\ConfiguracionLaboral;
use Illuminate\Database\Seeder;

class ConfiguracionLaboralSeeder extends Seeder
{
    /**
     * Siembra los parámetros laborales oficiales de EMAPAP Patacamaya para 2026 y 2025.
     */
    public function run(): void
    {
        $escalasAntiguedad = [
            ['min_anios' => 0, 'max_anios' => 1, 'porcentaje' => 0],
            ['min_anios' => 2, 'max_anios' => 4, 'porcentaje' => 5],
            ['min_anios' => 5, 'max_anios' => 7, 'porcentaje' => 11],
            ['min_anios' => 8, 'max_anios' => 10, 'porcentaje' => 18],
            ['min_anios' => 11, 'max_anios' => 14, 'porcentaje' => 26],
            ['min_anios' => 15, 'max_anios' => 19, 'porcentaje' => 34],
            ['min_anios' => 20, 'max_anios' => 24, 'porcentaje' => 42],
            ['min_anios' => 25, 'max_anios' => 99, 'porcentaje' => 50],
        ];

        // Gestión 2026 (Vigente Oficial)
        ConfiguracionLaboral::updateOrCreate(
            ['gestion' => 2026],
            [
                'descripcion' => 'Parámetros Laborales y Salariales Oficiales EMAPAP - Gestión 2026',
                'nro_patronal_min_trabajo' => '1002393029-1',
                'nro_patronal_cns' => '01-521-00002',
                'nit_institucional' => '1002393029',
                'ubicacion_geografica' => 'PATACAMAYA-LA PAZ-BOLIVIA',
                'direccion_institucional' => 'PLAZA BOLIVAR - ZONA ESTACION',
                'salario_minimo_nacional' => 3300.00,
                'porcentaje_gestora_vejez' => 0.1000,
                'porcentaje_gestora_riesgo_comun' => 0.0171,
                'porcentaje_gestora_comision' => 0.0050,
                'porcentaje_gestora_solidario' => 0.0050,
                'porcentaje_gestora_total' => 0.1271,
                'porcentaje_patronal_cns' => 0.1000,
                'porcentaje_patronal_gestora_sol' => 0.0300,
                'porcentaje_patronal_gestora_riesgo' => 0.0171,
                'porcentaje_patronal_gestora_pro_vivienda' => 0.0200,
                'porcentaje_patronal_gestora_total' => 0.0721,
                'porcentaje_patronal_total' => 0.1721,
                'tarifa_refrigerio_diaria' => 18.00,
                'dias_laborales_base' => 30,
                'horas_jornada_diaria' => 8,
                'factor_hora_extra' => 2.00,
                'factor_dominical' => 3.00,
                'escalas_bono_antiguedad' => $escalasAntiguedad,
                'multiplicador_smn_antiguedad' => 3,
                'alicuota_rc_iva' => 13.00,
                'minimos_no_imponibles_rc_iva' => 2,
                'es_vigente' => true,
                '_estado' => 'ACTIVO',
                '_fecha_creacion' => now(),
            ]
        );

        // Gestión 2025 (Histórico Referencial)
        ConfiguracionLaboral::updateOrCreate(
            ['gestion' => 2025],
            [
                'descripcion' => 'Parámetros Laborales EMAPAP - Gestión 2025',
                'nro_patronal_min_trabajo' => '1002393029-1',
                'nro_patronal_cns' => '01-521-00002',
                'nit_institucional' => '1002393029',
                'ubicacion_geografica' => 'PATACAMAYA-LA PAZ-BOLIVIA',
                'direccion_institucional' => 'PLAZA BOLIVAR - ZONA ESTACION',
                'salario_minimo_nacional' => 2500.00,
                'porcentaje_gestora_vejez' => 0.1000,
                'porcentaje_gestora_riesgo_comun' => 0.0171,
                'porcentaje_gestora_comision' => 0.0050,
                'porcentaje_gestora_solidario' => 0.0050,
                'porcentaje_gestora_total' => 0.1271,
                'porcentaje_patronal_cns' => 0.1000,
                'porcentaje_patronal_gestora_sol' => 0.0300,
                'porcentaje_patronal_gestora_riesgo' => 0.0171,
                'porcentaje_patronal_gestora_pro_vivienda' => 0.0200,
                'porcentaje_patronal_gestora_total' => 0.0721,
                'porcentaje_patronal_total' => 0.1721,
                'tarifa_refrigerio_diaria' => 18.00,
                'dias_laborales_base' => 30,
                'horas_jornada_diaria' => 8,
                'factor_hora_extra' => 2.00,
                'factor_dominical' => 3.00,
                'escalas_bono_antiguedad' => $escalasAntiguedad,
                'multiplicador_smn_antiguedad' => 3,
                'alicuota_rc_iva' => 13.00,
                'minimos_no_imponibles_rc_iva' => 2,
                'es_vigente' => false,
                '_estado' => 'ACTIVO',
                '_fecha_creacion' => now(),
            ]
        );
    }
}
