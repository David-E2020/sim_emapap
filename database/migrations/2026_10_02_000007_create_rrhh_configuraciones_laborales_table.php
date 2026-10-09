<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rrhh.configuraciones_laborales', function (Blueprint $table) {
            $table->id();
            $table->integer('gestion')->default(2026);
            $table->string('descripcion', 255)->default('Configuración Salarial y Laboral Oficial EMAPA Patacamaya');
            $table->string('nro_patronal_min_trabajo', 50)->default('1002393029-1');
            $table->string('nro_patronal_cns', 50)->default('01-521-00002');
            $table->string('nit_institucional', 50)->default('1002393029');
            $table->string('ubicacion_geografica', 100)->default('PATACAMAYA-LA PAZ-BOLIVIA');
            $table->string('direccion_institucional', 255)->default('PLAZA BOLIVAR - ZONA ESTACION');

            // 1. Salario Mínimo Nacional (D.S. Bolivia)
            $table->decimal('salario_minimo_nacional', 12, 2)->default(3300.00);

            // 2. Aportes Laborales Gestora Pública (12.71% Total)
            $table->decimal('porcentaje_gestora_vejez', 6, 4)->default(0.1000); // 10.00%
            $table->decimal('porcentaje_gestora_riesgo_comun', 6, 4)->default(0.0171); // 1.71%
            $table->decimal('porcentaje_gestora_comision', 6, 4)->default(0.0050); // 0.50%
            $table->decimal('porcentaje_gestora_solidario', 6, 4)->default(0.0050); // 0.50%
            $table->decimal('porcentaje_gestora_total', 6, 4)->default(0.1271); // 12.71%

            // 3. Aportes Patronales (17.21% Total)
            $table->decimal('porcentaje_patronal_cns', 6, 4)->default(0.1000); // 10.00% CNS Caja Nacional de Salud
            $table->decimal('porcentaje_patronal_gestora_sol', 6, 4)->default(0.0300); // 3.00% Aporte Patronal Solidario
            $table->decimal('porcentaje_patronal_gestora_riesgo', 6, 4)->default(0.0171); // 1.71% Riesgo Profesional
            $table->decimal('porcentaje_patronal_gestora_pro_vivienda', 6, 4)->default(0.0200); // 2.00% Pro-Vivienda
            $table->decimal('porcentaje_patronal_gestora_total', 6, 4)->default(0.0721); // 7.21% Total Gestora Patronal
            $table->decimal('porcentaje_patronal_total', 6, 4)->default(0.1721); // 17.21% Total Aporte Patronal

            // 4. Parámetros de Asistencia, Jornada y Refrigerio
            $table->decimal('tarifa_refrigerio_diaria', 12, 2)->default(18.00); // Bs. 18.00 por día trabajado
            $table->integer('dias_laborales_base')->default(30); // 30 días base comercial laboral
            $table->integer('horas_jornada_diaria')->default(8); // 8 horas jornada laboral
            $table->decimal('factor_hora_extra', 4, 2)->default(2.00); // 2.0x factor hora extra
            $table->decimal('factor_dominical', 4, 2)->default(3.00); // 3.0x factor dominical

            // 5. Escala de Bono de Antigüedad (D.S. 21060 Art. 60)
            $table->jsonb('escalas_bono_antiguedad')->nullable();
            $table->integer('multiplicador_smn_antiguedad')->default(3); // 3 Salarios Mínimos Nacionales

            // 6. Régimen Tributario RC-IVA
            $table->decimal('alicuota_rc_iva', 6, 4)->default(0.1300); // 13%
            $table->integer('minimos_no_imponibles_rc_iva')->default(2); // 2 SMN no imponibles

            // Auditoría
            $table->boolean('es_vigente')->default(true);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // Población inicial oficial según normativa laboral vigente
        $escalasIniciales = json_encode([
            ['min' => 2, 'max' => 4, 'porcentaje' => 0.05, 'descripcion' => 'De 2 a 4 años (5%)'],
            ['min' => 5, 'max' => 7, 'porcentaje' => 0.11, 'descripcion' => 'De 5 a 7 años (11%)'],
            ['min' => 8, 'max' => 10, 'porcentaje' => 0.18, 'descripcion' => 'De 8 a 10 años (18%)'],
            ['min' => 11, 'max' => 14, 'porcentaje' => 0.26, 'descripcion' => 'De 11 a 14 años (26%)'],
            ['min' => 15, 'max' => 19, 'porcentaje' => 0.34, 'descripcion' => 'De 15 a 19 años (34%)'],
            ['min' => 20, 'max' => 24, 'porcentaje' => 0.42, 'descripcion' => 'De 20 a 24 años (42%)'],
            ['min' => 25, 'max' => 99, 'porcentaje' => 0.50, 'descripcion' => 'De 25 a más años (50%)'],
        ]);

        DB::table('rrhh.configuraciones_laborales')->insert([
            'gestion' => 2026,
            'descripcion' => 'Parámetros Salariales y Laborales Oficiales EMAPA Patacamaya (Gestión 2026)',
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
            'escalas_bono_antiguedad' => $escalasIniciales,
            'multiplicador_smn_antiguedad' => 3,
            'alicuota_rc_iva' => 0.1300,
            'minimos_no_imponibles_rc_iva' => 2,
            'es_vigente' => true,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rrhh.configuraciones_laborales');
    }
};
