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
        // 1. Crear el esquema rrhh en PostgreSQL
        DB::statement('CREATE SCHEMA IF NOT EXISTS rrhh');

        // 2. Departamentos de Bolivia
        Schema::create('rrhh.departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Provincias
        Schema::create('rrhh.provincias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->foreignId('id_departamento')->constrained('rrhh.departamentos')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. Municipios
        Schema::create('rrhh.municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('latitud', 255)->nullable();
            $table->string('longitud', 255)->nullable();
            $table->foreignId('id_provincia')->constrained('rrhh.provincias')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Feriados Nacionales y Departamentales
        Schema::create('rrhh.feriados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('dia_feriado');
            $table->integer('dia');
            $table->integer('mes');
            $table->integer('anio');
            $table->boolean('es_feriado_nacional')->default(false);
            $table->foreignId('id_departamento')->nullable()->constrained('rrhh.departamentos')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 6. Fechas de Corte
        Schema::create('rrhh.fechas_cortes', function (Blueprint $table) {
            $table->id();
            $table->integer('dia_inicio');
            $table->integer('dia_fin');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->integer('gestion');
            $table->integer('mes');
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
        Schema::dropIfExists('rrhh.fechas_cortes');
        Schema::dropIfExists('rrhh.feriados');
        Schema::dropIfExists('rrhh.municipios');
        Schema::dropIfExists('rrhh.provincias');
        Schema::dropIfExists('rrhh.departamentos');
    }
};
