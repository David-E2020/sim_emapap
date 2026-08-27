<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tipos de Permiso (Médicos, Particulares, Duelo, etc.)
        Schema::create('rrhh.permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('sigla', 50)->nullable();
            $table->float('tiempo_maximo')->default(0);
            $table->string('tipo_tiempo', 20)->default('HORAS'); // HORAS, DIAS
            $table->boolean('es_con_goce_haberes')->default(true);
            $table->boolean('es_acumulativo')->default(false);
            $table->float('tiempo_maximo_acumulativo')->default(0);
            $table->float('tiempo_maximo_periodo')->default(0);
            $table->string('tipo_tiempo_periodo', 20)->nullable();
            $table->integer('cantidad_maxima_periodo')->default(0);
            $table->string('tipo_periodo', 20)->nullable();
            $table->integer('cantidad_dias_mes')->default(0);
            $table->float('tiempo_maximo_mes')->default(0);
            $table->string('tipo_tiempo_mes', 20)->nullable();
            $table->string('tipo_accion', 50)->nullable();
            $table->boolean('es_pago_refrigerio')->default(true);
            $table->text('descripcion')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. Justificaciones Laborales
        Schema::create('rrhh.justificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('sigla', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Relación Permisos - Justificaciones
        Schema::create('rrhh.permisos_justificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_permiso')->constrained('rrhh.permisos')->cascadeOnDelete();
            $table->foreignId('id_justificacion')->constrained('rrhh.justificaciones')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. Boletas / Solicitudes de Salida
        Schema::create('rrhh.solicitudes_salidas', function (Blueprint $table) {
            $table->id();
            $table->text('motivo')->nullable();
            $table->string('lugar', 255)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('hora_inicio', 20)->nullable();
            $table->string('hora_fin', 20)->nullable();
            $table->float('horas_solicitadas')->default(0);
            $table->boolean('dia_completo')->default(false);
            $table->boolean('horas_dinamicas')->default(false);
            $table->jsonb('array_horas_dinamicas')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->string('turno_periodo', 50)->nullable();
            $table->string('hora_marcado_omision', 20)->nullable();
            $table->string('periodo_omision', 50)->nullable();
            $table->boolean('es_justificada')->default(false);
            $table->string('cite', 100)->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->boolean('es_pago_refrigerio')->default(true);
            $table->foreignId('id_permiso')->constrained('rrhh.permisos')->cascadeOnDelete();
            $table->foreignId('id_justificacion')->nullable()->constrained('rrhh.justificaciones')->nullOnDelete();
            $table->text('justificacion_anulacion')->nullable();
            $table->string('tipo_accion', 50)->nullable();
            $table->foreignId('id_solicitud_referencia')->nullable()->constrained('rrhh.solicitudes_salidas')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Detalles de Solicitud de Salida
        Schema::create('rrhh.detalles_solicitudes_salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud_salida')->constrained('rrhh.solicitudes_salidas')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('hora_inicio', 20)->nullable();
            $table->string('hora_fin', 20)->nullable();
            $table->float('horas')->default(0);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 6. Detalles de Compensaciones de Horas
        Schema::create('rrhh.detalles_compensaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud_salida')->constrained('rrhh.solicitudes_salidas')->cascadeOnDelete();
            $table->date('fecha_compensacion');
            $table->float('horas_compensadas')->default(0);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 7. Saldos de Vacaciones y Permisos por Empleado
        Schema::create('rrhh.saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->foreignId('id_permiso')->constrained('rrhh.permisos')->cascadeOnDelete();
            $table->integer('gestion');
            $table->float('saldo_dias')->default(0);
            $table->float('saldo_horas')->default(0);
            $table->float('saldo_minutos')->default(0);
            $table->jsonb('array_gastos')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 8. Cites Oficiales
        Schema::create('rrhh.cites', function (Blueprint $table) {
            $table->id();
            $table->string('cite', 100)->unique();
            $table->integer('anio');
            $table->string('tipo', 50)->default('PERMISO');
            $table->integer('correlativo')->default(1);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 9. Asignación de Solicitudes a Funcionarios y Flujo de Aprobación
        Schema::create('rrhh.usuarios_solicitudes_salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud_salida')->constrained('rrhh.solicitudes_salidas')->cascadeOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->string('estado_aprobacion', 50)->default('PENDIENTE'); // PENDIENTE, APROBADO, RECHAZADO
            $table->timestamp('fecha_revision')->nullable();
            $table->text('observacion')->nullable();
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
        Schema::dropIfExists('rrhh.usuarios_solicitudes_salidas');
        Schema::dropIfExists('rrhh.cites');
        Schema::dropIfExists('rrhh.saldos');
        Schema::dropIfExists('rrhh.detalles_compensaciones');
        Schema::dropIfExists('rrhh.detalles_solicitudes_salidas');
        Schema::dropIfExists('rrhh.solicitudes_salidas');
        Schema::dropIfExists('rrhh.permisos_justificaciones');
        Schema::dropIfExists('rrhh.justificaciones');
        Schema::dropIfExists('rrhh.permisos');
    }
};
