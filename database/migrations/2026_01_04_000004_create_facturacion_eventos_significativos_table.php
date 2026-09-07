<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TABLA EVENTOS SIGNIFICATIVOS (Contingencias tributarias del SIN)
        Schema::create('facturacion.eventos_significativos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->foreignId('id_punto_venta')->nullable()->constrained('facturacion.puntos_venta')->cascadeOnDelete();
            $table->integer('codigo_evento')->index(); // 1 a 7 según catálogo SIN (corte de luz, internet, etc.)
            $table->string('descripcion', 255);
            $table->text('cufd_evento'); // CUFD activo con el que inició el evento
            $table->timestamp('fecha_inicio')->index();
            $table->timestamp('fecha_fin')->nullable()->index();
            $table->string('cafc', 50)->nullable()->index(); // Código de Autorización de Facturas de Contingencia
            $table->string('estado_evento', 30)->default('INICIADO')->index(); // INICIADO, CERRADO, ENVIADO, OBSERVADO

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
        Schema::dropIfExists('facturacion.eventos_significativos');
    }
};
