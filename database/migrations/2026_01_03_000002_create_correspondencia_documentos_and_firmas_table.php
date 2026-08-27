<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA MAESTRA DE DOCUMENTOS OFICIALES
        Schema::create('correspondencia.documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cite', 100)->nullable()->unique();
            $table->string('codigo_verificacion', 12)->nullable()->unique()->index();
            $table->string('tipo_documento', 50)->index(); // MEMORANDUM, INFORME_TECNICO, NOTA_INTERNA, CIRCULAR, CARTA_EXTERNA, RESOLUCION
            $table->string('asunto', 500);
            $table->longText('contenido_html')->nullable();
            $table->jsonb('resumen')->nullable();
            $table->jsonb('config')->nullable(); // Configuración de márgenes y formato
            $table->string('estado', 30)->default('BORRADOR')->index(); // BORRADOR, EN_REVISION, APROBADO, FIRMADO, PUBLICADO, ANULADO

            $table->unsignedBigInteger('id_plantilla')->nullable();
            $table->unsignedBigInteger('id_unidad_generadora')->nullable()->index();
            $table->unsignedBigInteger('id_creador')->nullable()->index(); // ID Persona / Usuario
            $table->unsignedBigInteger('id_hoja_ruta')->nullable()->index();

            // Tamaños de archivos
            $table->bigInteger('tamanio_archivos_adjuntos')->default(0);
            $table->bigInteger('tamanio_archivo_generado')->default(0);
            $table->integer('cantidad_usuarios_compartidos')->default(0);
            $table->boolean('adjuntos_aprobados_firmados')->default(false);

            // Anulación
            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('id_usuario_anulacion')->nullable();
            $table->unsignedBigInteger('id_cargo_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_plantilla')->references('id')->on('correspondencia.plantillas_documentos')->nullOnDelete();
            $table->foreign('id_unidad_generadora')->references('id')->on('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->foreign('id_creador')->references('id')->on('rrhh.personas')->nullOnDelete();
        });

        // 2. PARTICIPANTES DEL DOCUMENTO (DE, A, VÍA, CC)
        Schema::create('correspondencia.participantes_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_puesto')->nullable();
            $table->string('tipo_participacion', 50); // REMITENTE_DE, DESTINATARIO_A, VIA, CON_COPIA_A
            $table->integer('orden_participacion')->default(0);
            $table->string('cargo_snapshot', 150)->nullable();
            $table->boolean('participante_interino')->default(false);
            $table->string('bandeja', 50)->default('BORRADOR'); // BORRADOR, EN_REVISION, APROBADOS, FIRMADOS, ARCHIVADOS

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->foreign('id_persona')->references('id')->on('rrhh.personas')->onDelete('cascade');
            $table->foreign('id_puesto')->references('id')->on('rrhh.puestos')->nullOnDelete();
            $table->unique(['id_documento', 'id_persona', 'tipo_participacion', 'id_puesto'], 'uk_part_doc_pers_tipo');
        });

        // 3. FIRMAS Y APROBACIONES ELECTRÓNICAS
        Schema::create('correspondencia.firmas_aprobaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento');
            $table->unsignedBigInteger('id_participante_doc');
            $table->unsignedBigInteger('id_persona');
            $table->string('estado', 30)->default('PENDIENTE')->index(); // PENDIENTE, FIRMADO, OBSERVADO_RECHAZADO
            $table->string('tipo_firma', 50)->default('PIN_ELECTRONICO'); // PIN_ELECTRONICO, FIRMA_DIGITAL_TOKEN, CIUDADANIA_DIGITAL
            $table->timestamp('fecha_firma_aprobacion')->nullable();
            $table->string('hash_documento_sha256', 100)->nullable();
            $table->timestamp('sello_tiempo')->nullable();
            $table->text('motivo_observacion')->nullable();
            $table->uuid('codigo_solicitud_aprobacion')->nullable();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->foreign('id_participante_doc')->references('id')->on('correspondencia.participantes_documentos')->onDelete('cascade');
            $table->foreign('id_persona')->references('id')->on('rrhh.personas')->onDelete('cascade');
            $table->unique(['id_documento', 'id_participante_doc'], 'uk_firma_doc_part');
        });

        // 4. CONTROL DE CICLOS DE REVISIÓN
        Schema::create('correspondencia.revisiones_doc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento');
            $table->integer('ciclo_actual')->default(1);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
        });

        Schema::create('correspondencia.revisiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_revision_doc');
            $table->unsignedBigInteger('id_participante_doc');
            $table->integer('nro_ciclo')->default(1);
            $table->integer('orden_participacion')->default(1);
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamp('fecha_revision')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_revision_doc')->references('id')->on('correspondencia.revisiones_doc')->onDelete('cascade');
            $table->foreign('id_participante_doc')->references('id')->on('correspondencia.participantes_documentos')->onDelete('cascade');
        });

        // 5. ARCHIVOS ADJUNTOS Y GENERADOS
        Schema::create('correspondencia.archivos_adjuntos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento')->nullable();
            $table->unsignedBigInteger('id_hoja_ruta')->nullable();
            $table->unsignedBigInteger('id_derivacion')->nullable();
            $table->string('nombre_original', 255);
            $table->string('ruta_almacenamiento', 500);
            $table->string('mime_type', 100);
            $table->bigInteger('tamanio_bytes')->default(0);
            $table->string('hash_sha256', 100)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
        });

        Schema::create('correspondencia.archivos_generados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento')->unique();
            $table->string('ruta_pdf', 500);
            $table->integer('version')->default(1);
            $table->string('hash_sha256', 100)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
        });

        // 6. REFERENCIAS CRUZADAS ENTRE DOCUMENTOS
        Schema::create('correspondencia.referencias_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_documento');
            $table->unsignedBigInteger('id_documento_referencia');
            $table->string('tipo_referencia', 50)->default('RESPUESTA_A'); // RESPUESTA_A, ANTECEDENTE_DE, COMPLEMENTO_DE
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->foreign('id_documento_referencia')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->unique(['id_documento', 'id_documento_referencia'], 'uk_ref_doc_par');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia.referencias_documentos');
        Schema::dropIfExists('correspondencia.archivos_generados');
        Schema::dropIfExists('correspondencia.archivos_adjuntos');
        Schema::dropIfExists('correspondencia.revisiones');
        Schema::dropIfExists('correspondencia.revisiones_doc');
        Schema::dropIfExists('correspondencia.firmas_aprobaciones');
        Schema::dropIfExists('correspondencia.participantes_documentos');
        Schema::dropIfExists('correspondencia.documentos');
    }
};
