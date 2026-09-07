<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA CUIS (Código Único de Inicio de Sistemas - Vigencia 1 año)
        Schema::create('facturacion.cuis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->foreignId('id_punto_venta')->nullable()->constrained('facturacion.puntos_venta')->cascadeOnDelete();
            $table->string('codigo', 100)->index();
            $table->timestamp('fecha_vigencia')->index();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. TABLA CUFD (Código Único de Facturación Diaria - Vigencia 24 horas)
        Schema::create('facturacion.cufd', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->foreignId('id_punto_venta')->nullable()->constrained('facturacion.puntos_venta')->cascadeOnDelete();
            $table->text('codigo')->index();
            $table->string('codigo_control', 50)->index();
            $table->text('direccion')->nullable();
            $table->timestamp('fecha_vigencia')->index();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion.cufd');
        Schema::dropIfExists('facturacion.cuis');
    }
};
