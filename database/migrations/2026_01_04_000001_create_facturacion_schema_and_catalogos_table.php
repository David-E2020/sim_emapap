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
        // 1. CREAR EL ESQUEMA POSTGRESQL 'facturacion'
        DB::statement('CREATE SCHEMA IF NOT EXISTS facturacion');

        // 2. TABLA DE CATALOGOS DEL SIN (Sincronizables desde SIAT)
        Schema::create('facturacion.catalogos_sin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo_catalogo', 50)->index(); // ACTIVIDAD, PRODUCTO_SIN, TIPO_DOCUMENTO, METODO_PAGO, UNIDAD_MEDIDA, etc.
            $table->string('codigo', 50)->index();
            $table->text('descripcion');
            $table->string('codigo_padre', 50)->nullable()->index();
            
            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['tipo_catalogo', 'codigo'], 'uk_tipo_catalogo_codigo');
        });

        // 3. TABLA DE SUCURSALES AUTORIZADAS POR EL SIN
        Schema::create('facturacion.sucursales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('codigo_sucursal')->unique(); // 0 = Casa Matriz, 1, 2, ...
            $table->string('nombre', 150);
            $table->string('direccion', 255);
            $table->string('telefono', 50)->nullable();
            $table->string('municipio', 100)->default('La Paz');
            $table->string('departamento', 50)->default('La Paz');

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 4. TABLA DE PUNTOS DE VENTA AUTORIZADOS POR EL SIN
        Schema::create('facturacion.puntos_venta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->integer('codigo_punto_venta'); // 0, 1, 2...
            $table->string('nombre', 150);
            $table->integer('tipo_punto_venta')->default(0); // 0=Venta normal, 1=Punto móvil, etc.
            $table->text('descripcion')->nullable();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_sucursal', 'codigo_punto_venta'], 'uk_sucursal_punto_venta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion.puntos_venta');
        Schema::dropIfExists('facturacion.sucursales');
        Schema::dropIfExists('facturacion.catalogos_sin');
    }
};
