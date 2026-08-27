<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera de Planillas Consolidadas y Declaradas
        Schema::create('rrhh.planillas_consolidadas', function (Blueprint $table) {
            $table->id();
            $table->integer('gestion');
            $table->integer('mes');
            $table->string('tipo_planilla', 30)->default('SUELDOS_Y_SALARIOS'); // SUELDOS_Y_SALARIOS, REFRIGERIOS, AGUINALDO
            $table->string('cite_oficial', 100)->nullable();
            $table->decimal('smn_aplicado', 12, 2)->default(2500.00);
            $table->decimal('tarifa_refrigerio_aplicada', 12, 2)->default(18.00);
            $table->decimal('porcentaje_gestora_aplicado', 6, 4)->default(0.1271);
            $table->decimal('total_ganado_bs', 14, 2)->default(0.00);
            $table->decimal('total_descuentos_bs', 14, 2)->default(0.00);
            $table->decimal('total_liquido_pagable_bs', 14, 2)->default(0.00);
            $table->string('estado', 20)->default('CONSOLIDADA'); // CONSOLIDADA, DECLARADA, PAGADA
            $table->timestamp('fecha_cierre')->useCurrent();
            $table->unsignedBigInteger('id_usuario_cierre')->default(1);
            $table->text('observaciones')->nullable();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['gestion', 'mes', 'tipo_planilla']);
        });

        // 2. Detalle de Funcionarios en la Planilla Consolidada (Snapshot Congelado Inmutable)
        Schema::create('rrhh.detalles_planillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_planilla_consolidada')->constrained('rrhh.planillas_consolidadas')->cascadeOnDelete();
            $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();

            // Datos congelados al momento del cierre
            $table->string('funcionario', 255);
            $table->string('ci', 50);
            $table->string('cargo', 200)->nullable();
            $table->string('item', 50)->nullable();
            $table->decimal('haber_basico', 12, 2);
            $table->integer('anios_antiguedad')->default(0);
            $table->decimal('porcentaje_bono', 5, 2)->default(0.00);
            $table->decimal('bono_antiguedad', 12, 2)->default(0.00);
            $table->decimal('total_ganado', 12, 2)->default(0.00);
            $table->decimal('gestora_12_71', 12, 2)->default(0.00);
            $table->integer('minutos_atraso')->default(0);
            $table->decimal('descuento_atraso', 12, 2)->default(0.00);
            $table->decimal('total_descuentos', 12, 2)->default(0.00);
            $table->decimal('liquido_salarial', 12, 2)->default(0.00);
            $table->integer('dias_refrigerio')->default(0);
            $table->decimal('refrigerio_bs', 12, 2)->default(0.00);
            $table->decimal('liquido_pagable_total', 12, 2)->default(0.00);

            $table->string('_estado', 20)->default('ACTIVO');
            $table->timestamp('_fecha_creacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rrhh.detalles_planillas');
        Schema::dropIfExists('rrhh.planillas_consolidadas');
    }
};
