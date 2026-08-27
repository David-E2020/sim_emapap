<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA MAESTRA DE HOJAS DE RUTA / EXPEDIENTES
        Schema::create('correspondencia.hojas_ruta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nro_hoja_ruta', 100)->unique()->index(); // Ej: HR-EMAPA-0001/2026
            $table->integer('gestion')->index();
            $table->string('tipo_hr', 50)->default('INTERNA')->index(); // INTERNA, EXTERNA
            $table->string('origen', 50)->default('INTERNO')->index(); // INTERNO, VENTANILLA_FISICA, VENTANILLA_DIGITAL
            $table->string('asunto', 500);
            $table->string('referencia', 500)->nullable();
            $table->string('prioridad', 30)->default('MEDIA')->index(); // URGENTE, ALTA, MEDIA, BAJA
            $table->boolean('confidencial')->default(false);
            $table->integer('nro_fojas')->default(1);
            $table->integer('nro_anexos')->default(0);

            // Origen y Remitente
            $table->unsignedBigInteger('id_unidad_origen')->nullable()->index();
            $table->unsignedBigInteger('id_persona_origen')->nullable()->index();
            $table->unsignedBigInteger('id_cargo_origen')->nullable()->index();
            $table->string('remitente_externo', 255)->nullable(); // Si viene de afuera
            $table->unsignedBigInteger('id_entidad_externa')->nullable()->index();
            $table->unsignedBigInteger('id_ventanilla_origen')->nullable()->index();

            // Estado del ciclo de vida
            $table->string('estado', 30)->default('NUEVO')->index(); // NUEVO, EN_PROCESO, CONCLUIDO, ARCHIVADO, ANULADO
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->jsonb('esta_con')->nullable(); // { id_usuario, id_cargo, id_unidad, nombre, fecha }
            $table->jsonb('datos_origen')->nullable();
            $table->jsonb('datos_solicitante')->nullable();

            // Cierre
            $table->timestamp('fecha_cierre')->nullable();
            $table->unsignedBigInteger('id_usuario_cierre')->nullable();
            $table->unsignedBigInteger('id_cargo_cierre')->nullable();
            $table->text('motivo_cierre')->nullable();

            // Reapertura
            $table->timestamp('fecha_reapertura')->nullable();
            $table->unsignedBigInteger('id_usuario_reapertura')->nullable();
            $table->unsignedBigInteger('id_cargo_reapertura')->nullable();
            $table->text('motivo_reapertura')->nullable();

            // Anulación
            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('id_usuario_anulacion')->nullable();
            $table->unsignedBigInteger('id_cargo_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();

            // Copia / Hijo
            $table->unsignedBigInteger('id_hoja_ruta_padre')->nullable()->index();
            $table->integer('cantidad_usuarios_compartidos')->default(0);

            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_unidad_origen')->references('id')->on('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->foreign('id_persona_origen')->references('id')->on('rrhh.personas')->nullOnDelete();
            $table->foreign('id_cargo_origen')->references('id')->on('rrhh.puestos')->nullOnDelete();
            $table->foreign('id_entidad_externa')->references('id')->on('rrhh.entidades')->nullOnDelete();
            $table->foreign('id_hoja_ruta_padre')->references('id')->on('correspondencia.hojas_ruta')->nullOnDelete();
        });

        // 2. VINCULACIÓN HOJA DE RUTA CON DOCUMENTOS OFICIALES
        Schema::create('correspondencia.hoja_ruta_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta');
            $table->unsignedBigInteger('id_documento');
            $table->boolean('es_documento_principal')->default(false);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->unique(['id_hoja_ruta', 'id_documento'], 'uk_hr_doc');
        });

        // 3. DERIVACIONES Y WORKFLOW TREE
        Schema::create('correspondencia.derivaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta');
            $table->unsignedBigInteger('id_derivacion_padre')->nullable()->index();
            $table->string('mpath', 500)->nullable(); // Materialized path para jerarquías

            $table->unsignedBigInteger('id_documento_principal')->nullable()->index();
            $table->unsignedBigInteger('id_unidad_origen')->nullable()->index();
            $table->unsignedBigInteger('id_funcionario_origen')->nullable()->index(); // ID Persona
            $table->unsignedBigInteger('id_cargo_origen')->nullable()->index();

            $table->unsignedBigInteger('id_unidad_destino')->nullable()->index();
            $table->unsignedBigInteger('id_funcionario_destino')->nullable()->index(); // ID Persona
            $table->unsignedBigInteger('id_cargo_destino')->nullable()->index();

            $table->string('proveido', 255); // Catálogo de proveídos: Para su conocimiento, Para informe, etc.
            $table->text('instruccion_detalle')->nullable();
            $table->string('prioridad', 30)->default('MEDIA');
            $table->integer('dias_plazo')->default(2);
            $table->date('fecha_limite')->nullable();

            $table->timestamp('fecha_derivacion')->useCurrent();
            $table->timestamp('fecha_recepcion')->nullable();
            $table->timestamp('fecha_atencion')->nullable();
            $table->unsignedBigInteger('id_usuario_atencion')->nullable();

            $table->string('estado_derivacion', 30)->default('PENDIENTE_RECEPCION')->index(); // PENDIENTE_RECEPCION, RECIBIDO, PROCESADO, DEVUELTO_OBSERVADO, ARCHIVADO
            $table->boolean('es_copia')->default(false);
            $table->boolean('participante_interino')->default(false);
            $table->text('observacion_devolucion')->nullable();

            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_derivacion_padre')->references('id')->on('correspondencia.derivaciones')->nullOnDelete();
            $table->foreign('id_documento_principal')->references('id')->on('correspondencia.documentos')->nullOnDelete();
            $table->foreign('id_unidad_origen')->references('id')->on('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->foreign('id_funcionario_origen')->references('id')->on('rrhh.personas')->nullOnDelete();
            $table->foreign('id_cargo_origen')->references('id')->on('rrhh.puestos')->nullOnDelete();
            $table->foreign('id_unidad_destino')->references('id')->on('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->foreign('id_funcionario_destino')->references('id')->on('rrhh.personas')->nullOnDelete();
            $table->foreign('id_cargo_destino')->references('id')->on('rrhh.puestos')->nullOnDelete();
        });

        // 4. AGRUPACIONES DE HOJAS DE RUTA (EXPEDIENTES ACUMULADOS)
        Schema::create('correspondencia.agrupaciones_hojas_ruta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta_principal');
            $table->unsignedBigInteger('id_hoja_ruta_anexada');
            $table->text('motivo_agrupacion');
            $table->timestamp('fecha_agrupacion')->useCurrent();
            $table->timestamp('fecha_desagrupacion')->nullable();
            $table->unsignedBigInteger('id_usuario_agrupacion')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta_principal')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_hoja_ruta_anexada')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->unique(['id_hoja_ruta_principal', 'id_hoja_ruta_anexada'], 'uk_agrup_hr_par');
        });

        // 5. REFERENCIAS DE ANTECEDENTES ENTRE HOJAS DE RUTA
        Schema::create('correspondencia.referencias_hojas_ruta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta');
            $table->unsignedBigInteger('id_hoja_ruta_referencia');
            $table->string('tipo_referencia', 50)->default('ANTECEDENTE');
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_hoja_ruta_referencia')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->unique(['id_hoja_ruta', 'id_hoja_ruta_referencia'], 'uk_ref_hr_par');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia.referencias_hojas_ruta');
        Schema::dropIfExists('correspondencia.agrupaciones_hojas_ruta');
        Schema::dropIfExists('correspondencia.derivaciones');
        Schema::dropIfExists('correspondencia.hoja_ruta_documentos');
        Schema::dropIfExists('correspondencia.hojas_ruta');
    }
};
