<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. VENTANILLAS FÍSICAS Y DIGITALES
        Schema::create('correspondencia.ventanillas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 100);
            $table->string('direccion', 200)->nullable();
            $table->string('tipo_atencion', 20)->default('FISICA'); // FISICA, DIGITAL
            $table->unsignedBigInteger('id_regional')->nullable()->index();
            $table->unsignedBigInteger('id_unidad_organizacional')->nullable()->index();

            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_regional')->references('id')->on('rrhh.regionales')->nullOnDelete();
            $table->foreign('id_unidad_organizacional')->references('id')->on('rrhh.unidades_organizacionales')->nullOnDelete();
            $table->unique(['nombre', 'id_regional', 'id_unidad_organizacional'], 'uk_ventanilla_reg_unid');
        });

        // 2. USUARIOS ASIGNADOS A VENTANILLA
        Schema::create('correspondencia.usuarios_ventanilla', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_ventanilla');

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_ventanilla')->references('id')->on('correspondencia.ventanillas')->onDelete('cascade');
            $table->unique(['id_usuario', 'id_ventanilla'], 'uk_user_ventanilla');
        });

        // 3. DELEGACIÓN SECRETARIAL Y ASISTENTES EJECUTIVOS
        Schema::create('correspondencia.usuarios_secretarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_usuario'); // Asistente / Secretario
            $table->unsignedBigInteger('id_puesto_titular'); // Puesto de la autoridad
            $table->jsonb('permisos')->nullable(); // { ver_bandeja: true, derivar: true, redactar: true, recibir: true }

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_puesto_titular')->references('id')->on('rrhh.puestos')->onDelete('cascade');
            $table->unique(['id_usuario', 'id_puesto_titular'], 'uk_secretario_puesto');
        });

        // 4. MATRIZ DE PERMISOS DE DERIVACIÓN
        Schema::create('correspondencia.permisos_derivacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_origen'); // ID Unidad / Puesto
            $table->string('tipo_origen', 30)->default('UNIDAD'); // UNIDAD, PUESTO, VENTANILLA
            $table->unsignedBigInteger('id_destino'); // ID Unidad / Puesto
            $table->string('tipo_destino', 30)->default('UNIDAD'); // UNIDAD, PUESTO, VENTANILLA

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_origen', 'tipo_origen', 'id_destino', 'tipo_destino'], 'uk_perm_deriv_orig_dest');
        });

        // 5. MATRIZ DE PERMISOS DE REDACCIÓN DE DOCUMENTOS
        Schema::create('correspondencia.permisos_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_origen'); // ID Unidad / Puesto
            $table->string('tipo_origen', 30)->default('UNIDAD');
            $table->unsignedBigInteger('id_destino'); // ID Unidad / Puesto
            $table->string('tipo_destino', 30)->default('UNIDAD');
            $table->string('tipo_documentos', 100)->default('TODOS'); // MEMORANDUM, INFORME, CIRCULAR, TODOS

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_origen', 'tipo_origen', 'id_destino', 'tipo_destino', 'tipo_documentos'], 'uk_perm_doc_orig_dest');
        });

        // 6. ETIQUETAS Y CLASIFICACIÓN VIRTUAL
        Schema::create('correspondencia.etiquetas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 50);
            $table->string('color', 20)->default('#1976D2'); // Código HEX de color
            $table->unsignedBigInteger('id_usuario')->index();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['nombre', 'id_usuario'], 'uk_etiqueta_user');
        });

        Schema::create('correspondencia.etiquetas_participantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_etiqueta');
            $table->unsignedBigInteger('id_hoja_ruta');
            $table->unsignedBigInteger('id_usuario');

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_etiqueta')->references('id')->on('correspondencia.etiquetas')->onDelete('cascade');
            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['id_etiqueta', 'id_hoja_ruta', 'id_usuario'], 'uk_etiq_hr_user');
        });

        // 7. ACCESOS COMPARTIDOS (EXPEDIENTES COMPARTIDOS EN MODO LECTURA)
        Schema::create('correspondencia.accesos_compartidos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_hoja_ruta')->nullable();
            $table->unsignedBigInteger('id_documento')->nullable();
            $table->unsignedBigInteger('id_usuario_destinatario');
            $table->unsignedBigInteger('id_usuario_autorizador');
            $table->timestamp('fecha_expiracion')->nullable();
            $table->text('motivo')->nullable();

            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_hoja_ruta')->references('id')->on('correspondencia.hojas_ruta')->onDelete('cascade');
            $table->foreign('id_documento')->references('id')->on('correspondencia.documentos')->onDelete('cascade');
            $table->foreign('id_usuario_destinatario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_usuario_autorizador')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia.accesos_compartidos');
        Schema::dropIfExists('correspondencia.etiquetas_participantes');
        Schema::dropIfExists('correspondencia.etiquetas');
        Schema::dropIfExists('correspondencia.permisos_documentos');
        Schema::dropIfExists('correspondencia.permisos_derivacion');
        Schema::dropIfExists('correspondencia.usuarios_secretarios');
        Schema::dropIfExists('correspondencia.usuarios_ventanilla');
        Schema::dropIfExists('correspondencia.ventanillas');
    }
};
