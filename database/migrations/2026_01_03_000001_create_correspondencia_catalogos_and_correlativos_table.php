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
        DB::statement('CREATE SCHEMA IF NOT EXISTS correspondencia');

        // 1. CORRELATIVOS DE CITES Y HOJAS DE RUTA
        Schema::create('correspondencia.correlativos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('gestion')->index();
            $table->string('tipo_correlativo', 50)->index(); // HOJA_RUTA, DOCUMENTO, VENTANILLA
            $table->string('sigla_plantilla', 50)->nullable()->index(); // MEM, INF, NOT, CIR, CE, RES
            $table->string('sigla_regional', 50)->nullable()->index(); // LPZ, SCZ, CBB, etc.
            $table->unsignedBigInteger('id_unidad_organizacional')->nullable()->index();
            $table->integer('correlativo_actual')->default(0);
            $table->string('formato_cite', 100)->nullable(); // Ej: EMAPA/{REG}/{UNIDAD}/{TIPO}/{NRO}/{GESTION}
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['gestion', 'tipo_correlativo', 'sigla_plantilla', 'id_unidad_organizacional', 'sigla_regional'], 'uk_correlativo_concurrente');
        });

        // 2. PLANTILLAS DE DOCUMENTOS OFICIALES
        Schema::create('correspondencia.plantillas_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 100);
            $table->string('sigla', 20); // MEM, INF, NI, CIR, CAR, RES
            $table->integer('version')->default(1);
            $table->string('param_tipo_plantilla', 50)->default('INTERNO'); // INTERNO, EXTERNO, CIRCULAR, RESOLUCION
            $table->string('param_validez_legal', 50)->default('PIN_ELECTRONICO'); // PIN_ELECTRONICO, FIRMA_DIGITAL, CIUDADANIA_DIGITAL
            $table->boolean('multiples_para')->default(true);
            $table->text('cabecera_html')->nullable();
            $table->longText('cuerpo_base')->nullable();
            $table->text('pie_html')->nullable();
            $table->jsonb('config_pagina')->nullable(); // Margenes, tamaño carta/oficio, orientación
            $table->text('hash_datos_plantilla')->nullable();
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['sigla', 'version'], 'uk_plantilla_sigla_version');
        });

        // 3. COMPONENTES REUTILIZABLES DE PLANTILLAS
        Schema::create('correspondencia.componentes_plantillas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_plantilla');
            $table->string('nombre', 100); // MEMBRETE_OFICIAL, DATOS_DESTINATARIO, CUADRO_REFERENCIA, CUERPO_HTML, BLOQUE_FIRMAS_QR
            $table->string('tipo_componente', 50); // EJS, HTML, TEXTO, DESTINATARIOS, FIRMAS
            $table->integer('orden')->default(1);
            $table->boolean('es_obligatorio')->default(true);
            $table->jsonb('config_inicial')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_plantilla')->references('id')->on('correspondencia.plantillas_documentos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia.componentes_plantillas');
        Schema::dropIfExists('correspondencia.plantillas_documentos');
        Schema::dropIfExists('correspondencia.correlativos');
    }
};
