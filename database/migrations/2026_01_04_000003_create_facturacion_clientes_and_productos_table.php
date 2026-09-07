<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA CLIENTES PARA FACTURACIÓN
        Schema::create('facturacion.clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('codigo_tipo_documento_identidad')->default(1)->index(); // 1=CI, 5=NIT, etc.
            $table->string('numero_documento', 50)->index();
            $table->string('complemento', 10)->nullable();
            $table->string('nombre_razon_social', 255)->index();
            $table->string('correo_electronico', 150)->nullable()->index();
            $table->string('telefono', 50)->nullable();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['codigo_tipo_documento_identidad', 'numero_documento', 'complemento'], 'uk_cliente_documento');
        });

        // 2. TABLA PRODUCTOS Y SERVICIOS HOMOLOGADOS CON EL SIN
        Schema::create('facturacion.productos_servicios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_producto_empresa', 50)->unique(); // Código interno de EMAPA
            $table->string('codigo_actividad', 50)->index(); // Código de actividad económica del SIN
            $table->string('codigo_producto_sin', 50)->index(); // Código producto según catálogo del SIN
            $table->string('descripcion', 255);
            $table->decimal('precio_unitario', 12, 2)->default(0.00);
            $table->integer('codigo_unidad_medida')->default(58); // 58 = UNIDAD según catálogo SIN

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
        Schema::dropIfExists('facturacion.productos_servicios');
        Schema::dropIfExists('facturacion.clientes');
    }
};
