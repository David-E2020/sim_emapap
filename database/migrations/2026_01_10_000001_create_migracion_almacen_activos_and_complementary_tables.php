<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Crear esquemas faltantes en PostgreSQL
        DB::statement('CREATE SCHEMA IF NOT EXISTS almacen');
        DB::statement('CREATE SCHEMA IF NOT EXISTS activos_fijos');
        DB::statement('CREATE SCHEMA IF NOT EXISTS migracion');

        // ==========================================
        // ESQUEMA MIGRACION: Bitácora y Auditoría
        // ==========================================
        if (!Schema::hasTable('migracion.logs')) {
            Schema::create('migracion.logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('job_id', 64)->index();
                $table->string('modulo', 50)->index();
                $table->string('tabla_origen', 100)->nullable();
                $table->string('tabla_destino', 100)->nullable();
                $table->integer('registros_procesados')->default(0);
                $table->integer('registros_correctos')->default(0);
                $table->integer('registros_erroneos')->default(0);
                $table->boolean('es_simulacion')->default(false);
                $table->text('mensaje')->nullable();
                $table->jsonb('detalles_json')->nullable();
                $table->timestamps();
            });
        }

        // ==========================================
        // ESQUEMA COMERCIAL: Tablas complementarias
        // ==========================================
        if (!Schema::hasTable('comercial.aportes_conexiones')) {
            Schema::create('comercial.aportes_conexiones', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('tipo_servicio', 20)->default('AGUA')->index(); // 'AGUA' o 'ALCANTARILLADO'
                $table->string('periodo', 20)->nullable();
                $table->string('codigo_socio', 50)->index();
                $table->string('nombre_socio', 255)->nullable();
                $table->string('zona', 50)->nullable();
                $table->string('estado', 20)->default('ACTIVO');
                $table->date('fecha')->nullable();
                $table->decimal('aporte', 12, 2)->default(0);
                $table->decimal('instalacion', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->decimal('abono', 12, 2)->default(0);
                $table->decimal('saldo', 12, 2)->default(0);
                $table->integer('plazo')->default(0);
                $table->boolean('pagado')->default(false);
                $table->date('fecha_pago')->nullable();
                $table->string('orden', 50)->nullable();
                $table->string('factura', 50)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('comercial.abonados_bajas')) {
            Schema::create('comercial.abonados_bajas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo_socio', 50)->index();
                $table->string('nombre_socio', 255)->nullable();
                $table->string('ci_ruc', 50)->nullable();
                $table->date('fecha_baja')->nullable();
                $table->text('motivo')->nullable();
                $table->string('factura', 50)->nullable();
                $table->decimal('importe', 12, 2)->default(0);
                $table->decimal('saldo', 12, 2)->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // ==========================================
        // ESQUEMA CONTABILIDAD: Facturas de Compra
        // ==========================================
        if (!Schema::hasTable('contabilidad.facturas_compra')) {
            Schema::create('contabilidad.facturas_compra', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('especificacion', 50)->nullable();
                $table->string('numero_factura', 50)->index();
                $table->date('fecha_factura');
                $table->string('nit_proveedor', 50)->index();
                $table->string('razon_social_proveedor', 255);
                $table->string('codigo_autorizacion', 255)->nullable();
                $table->string('codigo_control', 50)->nullable();
                $table->decimal('importe_total', 12, 2)->default(0);
                $table->decimal('importe_ice', 12, 2)->default(0);
                $table->decimal('importe_exento', 12, 2)->default(0);
                $table->decimal('importe_tasa_cero', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('descuentos', 12, 2)->default(0);
                $table->decimal('importe_base_cf', 12, 2)->default(0);
                $table->decimal('credito_fiscal', 12, 2)->default(0);
                $table->string('tipo_compra', 50)->default('1');
                $table->integer('gestion')->nullable();
                $table->integer('mes')->nullable();
                $table->timestamps();
            });
        }

        // ==========================================
        // ESQUEMA ALMACEN
        // ==========================================
        if (!Schema::hasTable('almacen.bodegas')) {
            Schema::create('almacen.bodegas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->string('ubicacion', 255)->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('almacen.grupos')) {
            Schema::create('almacen.grupos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('almacen.materiales')) {
            Schema::create('almacen.materiales', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo_item', 50)->unique();
                $table->string('nombre', 255);
                $table->string('unidad_medida', 50)->default('PZA');
                $table->decimal('stock_minimo', 12, 2)->default(0);
                $table->decimal('stock_actual', 12, 2)->default(0);
                $table->decimal('precio_promedio', 12, 4)->default(0);
                $table->decimal('precio_venta', 12, 4)->default(0);
                $table->string('moneda', 10)->default('BS');
                $table->string('grupo', 50)->nullable();
                $table->string('subgrupo', 50)->nullable();
                $table->foreignId('id_bodega_default')->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('almacen.kardex_movimientos')) {
            Schema::create('almacen.kardex_movimientos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_material')->index();
                $table->string('tipo_movimiento', 20); // INGRESO, EGRESO, AJUSTE, SALDO_INICIAL
                $table->date('fecha');
                $table->string('comprobante_origen', 50)->nullable();
                $table->decimal('cantidad', 12, 2);
                $table->decimal('costo_unitario', 12, 4)->default(0);
                $table->decimal('costo_total', 12, 2)->default(0);
                $table->decimal('saldo_cantidad', 12, 2)->default(0);
                $table->decimal('saldo_valorado', 12, 2)->default(0);
                $table->text('observacion')->nullable();
                $table->timestamps();
            });
        }

        // ==========================================
        // ESQUEMA ACTIVOS FIJOS
        // ==========================================
        if (!Schema::hasTable('activos_fijos.rubros')) {
            Schema::create('activos_fijos.rubros', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->decimal('tasa_depreciacion', 6, 2)->default(0);
                $table->integer('vida_util_meses')->default(0);
                $table->boolean('actualiza')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activos_fijos.grupos')) {
            Schema::create('activos_fijos.grupos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activos_fijos.bienes')) {
            Schema::create('activos_fijos.bienes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo_item', 50)->unique();
                $table->string('nombre', 255);
                $table->date('fecha_ingreso')->nullable();
                $table->integer('vida_util')->default(0);
                $table->string('unidad', 50)->default('PZA');
                $table->decimal('cantidad', 12, 2)->default(1);
                $table->decimal('valor_inicial', 14, 2)->default(0);
                $table->decimal('valor_actualizado', 14, 2)->default(0);
                $table->decimal('depreciacion_acumulada', 14, 2)->default(0);
                $table->decimal('valor_residual', 14, 2)->default(0);
                $table->unsignedBigInteger('id_rubro')->nullable();
                $table->string('estado', 20)->default('BUENO');
                $table->string('ubicacion_sitio', 150)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activos_fijos.bienes');
        Schema::dropIfExists('activos_fijos.grupos');
        Schema::dropIfExists('activos_fijos.rubros');

        Schema::dropIfExists('almacen.kardex_movimientos');
        Schema::dropIfExists('almacen.materiales');
        Schema::dropIfExists('almacen.grupos');
        Schema::dropIfExists('almacen.bodegas');

        Schema::dropIfExists('contabilidad.facturas_compra');
        Schema::dropIfExists('comercial.abonados_bajas');
        Schema::dropIfExists('comercial.aportes_conexiones');
        Schema::dropIfExists('migracion.logs');
    }
};
