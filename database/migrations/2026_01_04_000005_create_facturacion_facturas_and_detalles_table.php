<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA CABECERA DE FACTURAS
        Schema::create('facturacion.facturas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->foreignId('id_punto_venta')->nullable()->constrained('facturacion.puntos_venta')->cascadeOnDelete();
            $table->foreignId('id_cliente')->nullable()->constrained('facturacion.clientes')->nullOnDelete();
            $table->foreignId('id_cufd')->constrained('facturacion.cufd')->cascadeOnDelete();
            $table->foreignId('id_evento_significativo')->nullable()->constrained('facturacion.eventos_significativos')->nullOnDelete();

            // Datos normativos de facturación del SIN
            $table->unsignedBigInteger('numero_factura')->index();
            $table->string('cuf', 100)->unique()->index();
            $table->text('cufd');
            $table->string('codigo_control', 50);
            $table->timestamp('fecha_emision')->index();
            $table->integer('codigo_modalidad')->default(1); // 1=Electrónica en Línea, 2=Computarizada
            $table->integer('tipo_emision')->default(1)->index(); // 1=En Línea, 2=Fuera de Línea (contingencia)
            $table->integer('tipo_factura_documento')->default(1); // 1=Con Crédito Fiscal
            $table->integer('codigo_documento_sector')->default(1); // 1=Compra-Venta estándar
            
            // Datos del comprador
            $table->string('nombre_razon_social', 255);
            $table->string('numero_documento', 50)->index();
            $table->string('complemento', 10)->nullable();
            $table->integer('codigo_tipo_documento_identidad')->default(1);
            $table->integer('codigo_metodo_pago')->default(1); // 1=Efectivo, etc.
            $table->string('numero_tarjeta', 20)->nullable();

            // Importes tributarios
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_total_sujeto_iva', 12, 2);
            $table->decimal('monto_descuento', 12, 2)->default(0.00);
            $table->decimal('monto_gift_card', 12, 2)->default(0.00);
            $table->integer('codigo_moneda')->default(1); // 1=Boliviano
            $table->decimal('tipo_cambio', 10, 2)->default(1.00);

            // Leyenda legal y metadatos
            $table->text('leyenda');
            $table->string('usuario_emision', 100);
            $table->string('estado_factura', 30)->default('VALIDADA')->index(); // VALIDADA, OBSERVADA, ANULADA, RECHAZADA, CONTINGENCIA
            $table->string('codigo_recepcion', 100)->nullable()->index(); // Código devuelto por el SIN
            $table->integer('codigo_motivo_anulacion')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();

            // Almacenamiento de archivos generados
            $table->string('xml_firmado_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->text('representacion_grafica_qr')->nullable();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_sucursal', 'id_punto_venta', 'numero_factura'], 'uk_sucursal_punto_nro_factura');
        });

        // 2. TABLA DETALLE DE FACTURA
        Schema::create('facturacion.factura_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_factura')->constrained('facturacion.facturas')->cascadeOnDelete();
            $table->foreignId('id_producto_servicio')->nullable()->constrained('facturacion.productos_servicios')->nullOnDelete();

            $table->string('codigo_actividad', 50);
            $table->string('codigo_producto_sin', 50);
            $table->string('codigo_producto_empresa', 50);
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 12, 4);
            $table->integer('codigo_unidad_medida')->default(58);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('monto_descuento', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->string('numero_serie', 100)->nullable();
            $table->string('numero_imei', 100)->nullable();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. TABLA PAQUETES DE FACTURAS EN CONTINGENCIA
        Schema::create('facturacion.factura_paquetes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_evento_significativo')->constrained('facturacion.eventos_significativos')->cascadeOnDelete();
            $table->string('codigo_recepcion_paquete', 100)->nullable()->index();
            $table->integer('cantidad_facturas')->default(0);
            $table->string('archivo_tar_gz_path', 255);
            $table->string('hash_archivo', 100);
            $table->string('estado_paquete', 30)->default('PENDIENTE')->index(); // PENDIENTE, ENVIADO, VALIDADO, OBSERVADO
            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('facturacion.factura_paquetes');
        Schema::dropIfExists('facturacion.factura_detalles');
        Schema::dropIfExists('facturacion.facturas');
    }
};
