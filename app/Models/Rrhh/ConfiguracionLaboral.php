<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionLaboral extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.configuraciones_laborales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'gestion',
        'descripcion',
        'nro_patronal_min_trabajo',
        'nro_patronal_cns',
        'nit_institucional',
        'ubicacion_geografica',
        'direccion_institucional',
        'salario_minimo_nacional',
        'porcentaje_gestora_vejez',
        'porcentaje_gestora_riesgo_comun',
        'porcentaje_gestora_comision',
        'porcentaje_gestora_solidario',
        'porcentaje_gestora_total',
        'porcentaje_patronal_cns',
        'porcentaje_patronal_gestora_sol',
        'porcentaje_patronal_gestora_riesgo',
        'porcentaje_patronal_gestora_pro_vivienda',
        'porcentaje_patronal_gestora_total',
        'porcentaje_patronal_total',
        'tarifa_refrigerio_diaria',
        'dias_laborales_base',
        'horas_jornada_diaria',
        'factor_hora_extra',
        'factor_dominical',
        'escalas_bono_antiguedad',
        'multiplicador_smn_antiguedad',
        'alicuota_rc_iva',
        'minimos_no_imponibles_rc_iva',
        'es_vigente',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'gestion' => 'integer',
        'salario_minimo_nacional' => 'float',
        'porcentaje_gestora_vejez' => 'float',
        'porcentaje_gestora_riesgo_comun' => 'float',
        'porcentaje_gestora_comision' => 'float',
        'porcentaje_gestora_solidario' => 'float',
        'porcentaje_gestora_total' => 'float',
        'porcentaje_patronal_cns' => 'float',
        'porcentaje_patronal_gestora_sol' => 'float',
        'porcentaje_patronal_gestora_riesgo' => 'float',
        'porcentaje_patronal_gestora_pro_vivienda' => 'float',
        'porcentaje_patronal_gestora_total' => 'float',
        'porcentaje_patronal_total' => 'float',
        'tarifa_refrigerio_diaria' => 'float',
        'dias_laborales_base' => 'integer',
        'horas_jornada_diaria' => 'integer',
        'factor_hora_extra' => 'float',
        'factor_dominical' => 'float',
        'escalas_bono_antiguedad' => 'array',
        'multiplicador_smn_antiguedad' => 'integer',
        'alicuota_rc_iva' => 'float',
        'minimos_no_imponibles_rc_iva' => 'integer',
        'es_vigente' => 'boolean',
    ];

    /**
     * Obtiene la configuración laboral activa vigente (o crea una predeterminada si no existe).
     */
    public static function obtenerVigente(int $gestion = 2026): self
    {
        $config = self::where('gestion', $gestion)
            ->where('es_vigente', true)
            ->where('_estado', 'ACTIVO')
            ->orderBy('id', 'desc')
            ->first();

        if ($config) {
            return $config;
        }

        $fallback = self::where('_estado', 'ACTIVO')
            ->where('es_vigente', true)
            ->orderBy('gestion', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($fallback) {
            return $fallback;
        }

        return self::create([
            'gestion' => $gestion,
            'descripcion' => "Parámetros Salariales Oficiales EMAPA Patacamaya ({$gestion})",
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
            'multiplicador_smn_antiguedad' => 3,
            'escalas_bono_antiguedad' => [
                ['min' => 2, 'max' => 4, 'porcentaje' => 0.05, 'descripcion' => 'De 2 a 4 años (5%)'],
                ['min' => 5, 'max' => 7, 'porcentaje' => 0.11, 'descripcion' => 'De 5 a 7 años (11%)'],
                ['min' => 8, 'max' => 10, 'porcentaje' => 0.18, 'descripcion' => 'De 8 a 10 años (18%)'],
                ['min' => 11, 'max' => 14, 'porcentaje' => 0.26, 'descripcion' => 'De 11 a 14 años (26%)'],
                ['min' => 15, 'max' => 19, 'porcentaje' => 0.34, 'descripcion' => 'De 15 a 19 años (34%)'],
                ['min' => 20, 'max' => 24, 'porcentaje' => 0.42, 'descripcion' => 'De 20 a 24 años (42%)'],
                ['min' => 25, 'max' => 99, 'porcentaje' => 0.50, 'descripcion' => 'De 25 a más años (50%)'],
            ],
            'alicuota_rc_iva' => 0.1300,
            'minimos_no_imponibles_rc_iva' => 2,
            'es_vigente' => true,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);
    }
}
