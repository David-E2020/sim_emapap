<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Entidades
        Schema::create('rrhh.entidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('sigla', 20)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. Gestiones Anuales
        Schema::create('rrhh.gestiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('anio');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Regionales
        Schema::create('rrhh.regionales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('sigla', 20)->nullable();
            $table->foreignId('id_gestion')->nullable()->constrained('rrhh.gestiones')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. Niveles Jerárquicos
        Schema::create('rrhh.niveles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('nivel')->default(1);
            $table->foreignId('id_gestion')->nullable()->constrained('rrhh.gestiones')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Escalas Salariales
        Schema::create('rrhh.escalas_salariales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('salario', 12, 2)->default(0);
            $table->foreignId('id_gestion')->nullable()->constrained('rrhh.gestiones')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 6. Unidades Organizacionales (Organigrama en Árbol)
        Schema::create('rrhh.unidades_organizacionales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('nombre', 150);
            $table->string('sigla', 20)->nullable();
            $table->boolean('es_unidad_recursos_humanos')->default(false);
            $table->foreignId('id_organismo_padre')->nullable()->constrained('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->foreignId('padreId')->nullable()->constrained('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->string('mpath', 500)->nullable();
            $table->foreignId('id_regional')->nullable()->constrained('rrhh.regionales')->nullOnDelete();
            $table->foreignId('id_gestion')->nullable()->constrained('rrhh.gestiones')->nullOnDelete();
            $table->foreignId('id_nivel')->nullable()->constrained('rrhh.niveles')->nullOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 7. Puestos
        Schema::create('rrhh.puestos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('tipo_puesto', 50)->nullable();
            $table->foreignId('id_escala_salarial')->nullable()->constrained('rrhh.escalas_salariales')->nullOnDelete();
            $table->foreignId('id_unidad_organizacional')->constrained('rrhh.unidades_organizacionales')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 8. Puestos Eventuales
        Schema::create('rrhh.puestos_eventuales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->foreignId('id_escala_salarial')->nullable()->constrained('rrhh.escalas_salariales')->nullOnDelete();
            $table->foreignId('id_unidad_organizacional')->constrained('rrhh.unidades_organizacionales')->cascadeOnDelete();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 9. Asignaciones de Puestos (Ítems del Personal de Planta)
        Schema::create('rrhh.asignaciones_puestos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_asignacion', 50)->default('ITEM');
            $table->string('asignacion', 100)->nullable();
            $table->integer('nro_item');
            $table->foreignId('id_puesto')->constrained('rrhh.puestos')->cascadeOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->foreignId('id_asignacion_original')->nullable()->constrained('rrhh.asignaciones_puestos')->nullOnDelete();
            $table->foreignId('id_unidad_comision')->nullable()->constrained('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 10. Asignaciones de Puestos Eventuales (Consultores / Contratos)
        Schema::create('rrhh.asignaciones_puestos_eventuales', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_asignacion', 50)->default('EVENTUAL');
            $table->foreignId('id_puesto_eventual')->constrained('rrhh.puestos_eventuales')->cascadeOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 11. Flujo de Permiso Especial
        Schema::create('rrhh.flujos_permisos_especiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_unidad_org')->constrained('rrhh.unidades_organizacionales')->cascadeOnDelete();
            $table->jsonb('flujo')->nullable();
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
        Schema::dropIfExists('rrhh.flujos_permisos_especiales');
        Schema::dropIfExists('rrhh.asignaciones_puestos_eventuales');
        Schema::dropIfExists('rrhh.asignaciones_puestos');
        Schema::dropIfExists('rrhh.puestos_eventuales');
        Schema::dropIfExists('rrhh.puestos');
        Schema::dropIfExists('rrhh.unidades_organizacionales');
        Schema::dropIfExists('rrhh.escalas_salariales');
        Schema::dropIfExists('rrhh.niveles');
        Schema::dropIfExists('rrhh.regionales');
        Schema::dropIfExists('rrhh.gestiones');
        Schema::dropIfExists('rrhh.entidades');
    }
};
