<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Personas (Datos Civiles y Demográficos de Empleados)
        Schema::create('rrhh.personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100)->nullable();
            $table->string('primer_apellido', 100)->nullable();
            $table->string('segundo_apellido', 100)->nullable();
            $table->string('tipo_documento', 15)->default('CI');
            $table->string('tipo_documento_otro', 50)->nullable();
            $table->string('nro_documento', 50)->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('correo_electronico_personal', 255)->nullable();
            $table->string('telefono_celular', 50)->nullable();
            $table->string('genero', 15)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. Ficha Personal (Cabecera del Legajo Digital)
        Schema::create('rrhh.fichas_personales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')->unique()->constrained('rrhh.personas')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Datos Laborales del Funcionario
        Schema::create('rrhh.datos_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ficha_personal')->constrained('rrhh.fichas_personales')->cascadeOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->string('tipo_funcionario', 100)->nullable();
            $table->integer('nro_programa')->nullable();
            $table->string('nro_contrato', 255)->nullable();
            $table->integer('nro_item')->nullable();
            $table->string('cargo', 255)->nullable();
            $table->date('fecha_desvinculacion')->nullable();
            $table->string('tipo_movimiento', 100)->nullable();
            $table->date('fecha_documento')->nullable();
            $table->string('nro_documento', 50)->nullable();
            $table->string('unidad_organizacional', 255)->nullable();
            $table->boolean('es_puesto_anterior')->default(false);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. Estudios Académicos
        Schema::create('rrhh.estudios_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ficha_personal')->constrained('rrhh.fichas_personales')->cascadeOnDelete();
            $table->string('nivel_instruccion', 100)->nullable();
            $table->string('carrera', 255)->nullable();
            $table->string('institucion', 255)->nullable();
            $table->string('grado_obtenido', 100)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->string('nro_titulo', 100)->nullable();
            $table->boolean('tiene_titulo_provision_nacional')->default(false);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Experiencia Laboral
        Schema::create('rrhh.experiencias_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ficha_personal')->constrained('rrhh.fichas_personales')->cascadeOnDelete();
            $table->string('empresa', 255)->nullable();
            $table->string('cargo', 255)->nullable();
            $table->text('funciones_principales')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('motivo_retiro', 255)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 6. Certificado de Años de Servicio (CAS)
        Schema::create('rrhh.cas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ficha_personal')->constrained('rrhh.fichas_personales')->cascadeOnDelete();
            $table->date('fecha_calificacion')->nullable();
            $table->integer('anios')->default(0);
            $table->integer('meses')->default(0);
            $table->integer('dias')->default(0);
            $table->string('nro_resolucion', 100)->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 7. Documentos Adjuntos / Escaneados
        Schema::create('rrhh.documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cas')->nullable()->constrained('rrhh.cas')->nullOnDelete();
            $table->foreignId('id_experiencia_laboral')->nullable()->constrained('rrhh.experiencias_laborales')->nullOnDelete();
            $table->foreignId('id_estudio_academico')->nullable()->constrained('rrhh.estudios_academicos')->nullOnDelete();
            $table->foreignId('id_dato_laboral')->nullable()->constrained('rrhh.datos_laborales')->nullOnDelete();
            $table->string('nombre_archivo', 255);
            $table->string('tipo_documento', 100)->nullable();
            $table->string('ruta_archivo', 500);
            $table->string('hash', 255)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 8. Huellas Biométricas Dactilares
        Schema::create('rrhh.huellas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->text('huella_template');
            $table->integer('dedo')->default(0);
            $table->integer('calidad')->default(100);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 9. Mapeo Usuario en Reloj Biométrico
        Schema::create('rrhh.usuario_biometricos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->string('id_usuario_reloj', 50);
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
        Schema::dropIfExists('rrhh.usuario_biometricos');
        Schema::dropIfExists('rrhh.huellas');
        Schema::dropIfExists('rrhh.documentos');
        Schema::dropIfExists('rrhh.cas');
        Schema::dropIfExists('rrhh.experiencias_laborales');
        Schema::dropIfExists('rrhh.estudios_academicos');
        Schema::dropIfExists('rrhh.datos_laborales');
        Schema::dropIfExists('rrhh.fichas_personales');
        Schema::dropIfExists('rrhh.personas');
    }
};
