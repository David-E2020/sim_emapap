<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear esquema contabilidad si no existe
        DB::statement('CREATE SCHEMA IF NOT EXISTS contabilidad');

        // 2. Gestiones Fiscales Anuales
        Schema::create('contabilidad.gestiones', function (Blueprint $table) {
            $table->id();
            $table->integer('gestion')->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('ABIERTA'); // ABIERTA, CERRADA
            $table->string('observaciones', 255)->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 30)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. Períodos Mensuales Contables (1 a 12)
        Schema::create('contabilidad.periodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_gestion');
            $table->smallInteger('mes');
            $table->string('nombre', 30);
            $table->string('estado', 20)->default('ABIERTO'); // ABIERTO, CERRADO
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 30)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_gestion')->references('id')->on('contabilidad.gestiones')->onDelete('cascade');
            $table->unique(['id_gestion', 'mes']);
        });

        // 4. Centros de Costo / Unidades Ejecutoras
        Schema::create('contabilidad.centros_costo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 30)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. Plan General de Cuentas (5 niveles jerárquicos)
        Schema::create('contabilidad.plan_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_cuenta_padre')->nullable();
            $table->smallInteger('nivel')->default(1); // 1: Grupo, 2: Subgrupo, 3: Cuenta, 4: Subcuenta, 5: Analítica
            $table->string('naturaleza', 20)->default('DEUDORA'); // DEUDORA, ACREEDORA
            $table->string('tipo', 30)->default('ACTIVO'); // ACTIVO, PASIVO, PATRIMONIO, RECURSO, GASTO
            $table->boolean('permite_movimiento')->default(false); // Solo true para cuentas imputables
            $table->string('codigo_mefp', 50)->nullable(); // Código correlativo presupuesto/MEFP
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 30)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_cuenta_padre')->references('id')->on('contabilidad.plan_cuentas')->onDelete('restrict');
            $table->index('codigo');
            $table->index('id_cuenta_padre');
            $table->index('tipo');
            $table->index('permite_movimiento');
        });

        // 6. Comprobantes Contables (CI, CE, CD)
        Schema::create('contabilidad.comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_comprobante', 50)->unique(); // ej: CI-2026-00001, CE-2026-00001, CD-2026-00001
            $table->string('tipo', 20); // INGRESO, EGRESO, DIARIO
            $table->date('fecha');
            $table->unsignedBigInteger('id_gestion');
            $table->smallInteger('mes');
            $table->text('glosa_principal');
            $table->string('beneficiario', 255)->nullable();
            $table->string('documento_beneficiario', 50)->nullable();
            $table->string('tipo_documento_respaldo', 100)->nullable();
            $table->string('numero_documento_respaldo', 100)->nullable();
            $table->decimal('total_debe', 14, 2)->default(0);
            $table->decimal('total_haber', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);
            $table->string('estado', 20)->default('BORRADOR'); // BORRADOR, APROBADO, CONTABILIZADO, ANULADO
            $table->string('origen_modulo', 50)->default('MANUAL'); // MANUAL, COMERCIAL_CAJA, COMERCIAL_DEVENGADO, FACTURACION_SIAT, RRHH_PLANILLA
            $table->unsignedBigInteger('id_referencia_origen')->nullable();
            $table->unsignedBigInteger('id_usuario_elaboracion')->nullable();
            $table->unsignedBigInteger('id_usuario_aprobacion')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->string('_transaccion', 30)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_gestion')->references('id')->on('contabilidad.gestiones')->onDelete('restrict');
            $table->foreign('id_usuario_elaboracion')->references('id')->on('users')->onDelete('set null');
            $table->foreign('id_usuario_aprobacion')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo', 'fecha']);
            $table->index(['origen_modulo', 'id_referencia_origen']);
            $table->index('estado');
        });

        // 7. Detalles del Comprobante (Asientos / Líneas de Imputación)
        Schema::create('contabilidad.comprobante_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_comprobante');
            $table->unsignedBigInteger('id_cuenta');
            $table->unsignedBigInteger('id_centro_costo')->nullable();
            $table->string('glosa_linea', 500)->nullable();
            $table->decimal('debe', 14, 2)->default(0);
            $table->decimal('haber', 14, 2)->default(0);
            $table->integer('orden')->default(1);
            $table->string('_estado', 20)->default('ACTIVO');
            $table->timestamp('_fecha_creacion')->useCurrent();

            $table->foreign('id_comprobante')->references('id')->on('contabilidad.comprobantes')->onDelete('cascade');
            $table->foreign('id_cuenta')->references('id')->on('contabilidad.plan_cuentas')->onDelete('restrict');
            $table->foreign('id_centro_costo')->references('id')->on('contabilidad.centros_costo')->onDelete('set null');
            $table->index('id_comprobante');
            $table->index('id_cuenta');
        });

        // 8. Mapeos de Parámetros de Enlace Automático
        Schema::create('contabilidad.mapeo_enlaces', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_enlace', 100)->unique();
            $table->string('descripcion', 255);
            $table->string('modulo', 50); // COMERCIAL, FACTURACION, RRHH, TESORERIA
            $table->unsignedBigInteger('id_cuenta_defecto');
            $table->unsignedBigInteger('id_centro_costo_defecto')->nullable();
            $table->string('_estado', 20)->default('ACTIVO');
            $table->unsignedBigInteger('_usuario_creacion')->nullable();
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->foreign('id_cuenta_defecto')->references('id')->on('contabilidad.plan_cuentas')->onDelete('restrict');
            $table->foreign('id_centro_costo_defecto')->references('id')->on('contabilidad.centros_costo')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabilidad.mapeo_enlaces');
        Schema::dropIfExists('contabilidad.comprobante_detalles');
        Schema::dropIfExists('contabilidad.comprobantes');
        Schema::dropIfExists('contabilidad.plan_cuentas');
        Schema::dropIfExists('contabilidad.centros_costo');
        Schema::dropIfExists('contabilidad.periodos');
        Schema::dropIfExists('contabilidad.gestiones');
        DB::statement('DROP SCHEMA IF EXISTS contabilidad CASCADE');
    }
};
