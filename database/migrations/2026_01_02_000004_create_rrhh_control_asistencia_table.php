<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Relojes Biométricos (ZKTeco / IP)
        Schema::create('rrhh.biometricos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('url', 100);
            $table->integer('puerto')->default(4370);
            $table->string('tipo', 50)->default('ZKTECO');
            $table->string('modelo', 50)->default('STANDALONE');
            $table->string('usuario', 50)->nullable();
            $table->string('contrasenia', 100)->default('');
            $table->string('ubicacion', 255)->default('');
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. Horarios de Trabajo
        Schema::create('rrhh.horarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tipo', 50)->default('CONTINUO'); // CONTINUO, DISCONTINUO, ESPECIAL
            $table->string('dias_laborales', 100)->default('1,2,3,4,5'); // Lunes a Viernes
            $table->integer('tolerancia_minutos')->default(10);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Períodos (Turnos de Entrada y Salida dentro del Horario)
        Schema::create('rrhh.periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_horario')->constrained('rrhh.horarios')->cascadeOnDelete();
            $table->string('hora_inicio', 10);
            $table->string('hora_fin', 10);
            $table->string('hora_inicio_tolerancia', 10)->nullable();
            $table->string('hora_fin_tolerancia', 10)->nullable();
            $table->integer('orden')->default(1);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. Asignaciones de Horarios a Empleados
        Schema::create('rrhh.asignaciones_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_horarios')->constrained('rrhh.horarios')->cascadeOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('permanente')->default(true);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Marcaciones (Logs Crudos del Reloj Biométrico)
        Schema::create('rrhh.marcaciones', function (Blueprint $table) {
            $table->id();
            $table->string('id_usuario_marcacion', 50);
            $table->foreignId('id_biometrico')->constrained('rrhh.biometricos')->cascadeOnDelete();
            $table->string('hora', 20);
            $table->string('fecha', 20);
            $table->string('hash', 255)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->index(['fecha', 'id_usuario_marcacion'], 'idx_marcaciones_fecha_usuario');
        });

        // 6. Asistencias Calculadas Diarias
        Schema::create('rrhh.asistencias', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('entrada_primer_periodo', 20)->nullable();
            $table->string('salida_primer_periodo', 20)->nullable();
            $table->string('entrada_segundo_periodo', 20)->nullable();
            $table->string('salida_segundo_periodo', 20)->nullable();
            $table->string('observacion_entrada_primer_periodo', 255)->nullable();
            $table->string('observacion_salida_primer_periodo', 255)->nullable();
            $table->string('observacion_entrada_segundo_periodo', 255)->nullable();
            $table->string('observacion_salida_segundo_periodo', 255)->nullable();
            $table->jsonb('array_observacion_entrada_primer_periodo')->nullable();
            $table->jsonb('array_observacion_salida_primer_periodo')->nullable();
            $table->jsonb('array_observacion_entrada_segundo_periodo')->nullable();
            $table->jsonb('array_observacion_salida_segundo_periodo')->nullable();
            $table->jsonb('solicitudes_salida')->nullable();
            $table->integer('minutos_de_atraso_primer_periodo')->default(0);
            $table->integer('minutos_de_atraso_segundo_periodo')->default(0);
            $table->integer('minutos_de_salida_temprana_primer_periodo')->default(0);
            $table->integer('minutos_de_salida_temprana_segundo_periodo')->default(0);
            $table->boolean('merece_refrigerio')->default(false);
            $table->string('dia', 20)->nullable();
            $table->foreignId('id_fecha_corte')->nullable()->constrained('rrhh.fechas_cortes')->nullOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->foreignId('id_horario')->nullable()->constrained('rrhh.horarios')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['fecha', 'id_persona'], 'unq_asistencia_fecha_persona');
        });

        // 7. Registros de Marcación Procesados
        Schema::create('rrhh.registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_marcacion')->nullable()->constrained('rrhh.marcaciones')->nullOnDelete();
            $table->foreignId('id_asistencia')->constrained('rrhh.asistencias')->cascadeOnDelete();
            $table->string('tipo_marcado', 50)->nullable(); // ENTRADA_1, SALIDA_1, ENTRADA_2, SALIDA_2
            $table->string('hora_marcado', 20)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 8. Sincronizaciones con Relojes Biométricos
        Schema::create('rrhh.sincronizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_biometrico')->constrained('rrhh.biometricos')->cascadeOnDelete();
            $table->timestamp('fecha_sincronizacion')->useCurrent();
            $table->integer('total_marcaciones')->default(0);
            $table->string('estado_sincronizacion', 50)->default('EXITOSO');
            $table->text('mensaje_error')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rrhh.sincronizaciones');
        Schema::dropIfExists('rrhh.registros');
        Schema::dropIfExists('rrhh.asistencias');
        Schema::dropIfExists('rrhh.marcaciones');
        Schema::dropIfExists('rrhh.asignaciones_horarios');
        Schema::dropIfExists('rrhh.periodos');
        Schema::dropIfExists('rrhh.horarios');
        Schema::dropIfExists('rrhh.biometricos');
    }
};
