<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. DESPACHO DE SALIDA EXTERNA (COURIER / MENSAJERÍA)
        Schema::create('correspondencia.despachos_salida', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta')->nullable()->index();
            $table->unsignedBigInteger('id_documento')->nullable()->index();
            $table->string('nro_guia_despacho', 100)->nullable();
            $table->string('tipo_despacho', 50)->default('MENSAJERIA_INTERNA'); // MENSAJERIA_INTERNA, COURIER_POSTAL, ENTREGA_DIRECTA
            $table->string('empresa_courier', 100)->nullable();
            $table->string('destinatario_institucion', 255);
            $table->string('destinatario_persona', 255)->nullable();
            $table->string('destinatario_direccion', 255)->nullable();
            $table->string('destinatario_ciudad', 100)->default('La Paz');

            $table->timestamp('fecha_despacho')->useCurrent();
            $table->timestamp('fecha_entrega')->nullable();
            $table->string('estado_despacho', 30)->default('PENDIENTE_DESPACHO')->index(); // PENDIENTE_DESPACHO, EN_CAMINO, ENTREGADO_CON_ACUSE, OBSERVADO
            $table->string('ruta_archivo_acuse', 500)->nullable();
            $table->text('observaciones')->nullable();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->nullOnDelete();
            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->nullOnDelete();
        });

        // 2. SOLICITUDES Y TRÁMITES CIUDADANOS (PORTAL DIGITAL EXTERNO)
        Schema::create('correspondencia.solicitudes_ciudadanas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_solicitud', 50)->unique()->index();
            $table->string('solicitante_nombre', 200);
            $table->string('solicitante_ci_nit', 50);
            $table->string('solicitante_telefono', 50)->nullable();
            $table->string('solicitante_correo', 150)->nullable();
            $table->string('tipo_solicitud', 100)->default('TRAMITE_GENERAL');
            $table->text('descripcion_solicitud');
            $table->jsonb('archivos_adjuntos')->nullable();
            $table->string('estado_solicitud', 30)->default('REGISTRADA')->index(); // REGISTRADA, EN_REVISION, HOJA_RUTA_GENERADA, RECHAZADA, ATENDIDA
            $table->unsignedBigInteger('id_hoja_ruta_generada')->nullable()->index();
            $table->text('motivo_rechazo_atencion')->nullable();
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_atencion')->nullable();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta_generada')->references('id')->on('correspondencia.hojas_ruta')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia.solicitudes_ciudadanas');
        Schema::dropIfExists('correspondencia.despachos_salida');
    }
};
