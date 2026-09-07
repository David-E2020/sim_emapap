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
        // 1. CREAR EL ESQUEMA POSTGRESQL 'comercial'
        DB::statement('CREATE SCHEMA IF NOT EXISTS comercial');

        // 2. ZONAS (CATASTRO OPERATIVO)
        Schema::create('comercial.zonas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 20)->unique(); // ASUNCION, CENTRAL, etc.
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();

            // Auditoría SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. CALLES / RUTAS
        Schema::create('comercial.calles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_zona')->constrained('comercial.zonas')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->text('referencia')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->index(['id_zona', 'nombre'], 'idx_calles_zona_nombre');
        });

        // 4. CATEGORIAS TARIFARIAS
        Schema::create('comercial.categorias_tarifarias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 5)->unique(); // D, A, B, E, P, L
            $table->string('nombre', 50); // DOMICILIARIA, COMERCIAL A, etc.
            $table->decimal('volumen_base', 8, 2)->default(6.00); // 6 m³ base
            $table->decimal('tarifa_minima', 10, 2)->default(12.60); // Monto mínimo base en Bs
            $table->decimal('tarifa_excedente_base', 10, 2)->default(2.10); // Tarifa base por m³ excedente
            $table->decimal('tarifa_alcantarillado', 10, 2)->default(2.00); // Cuota fija alcantarillado
            $table->boolean('aplica_ley_1886')->default(false); // Descuento 20% tercera edad (Solo en Domiciliaria)
            $table->boolean('activo')->default(true);

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 5. TARIFAS ESCALONADAS POR EXCESO (Para categorías B, E, L que tienen escala)
        Schema::create('comercial.tarifas_escalonadas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_categoria')->constrained('comercial.categorias_tarifarias')->cascadeOnDelete();
            $table->decimal('desde_m3', 8, 2);
            $table->decimal('hasta_m3', 8, 2)->nullable(); // null si es en adelante
            $table->decimal('precio_m3', 10, 2);

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 6. MEDIDORES
        Schema::create('comercial.medidores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_serie', 50)->unique();
            $table->string('marca', 50)->default('Sensus');
            $table->string('modelo', 50)->nullable();
            $table->string('diametro', 20)->default('1/2"'); // 1/2", 3/4", 1"
            $table->decimal('lectura_inicial', 10, 2)->default(0.00);
            $table->string('estado', 20)->default('OPERATIVO')->index(); // OPERATIVO, AVERIADO, CAMBIADO, BAJA
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 7. ABONADOS (PADRON GENERAL)
        Schema::create('comercial.abonados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 10)->unique()->index(); // '00001', '05116'
            $table->string('tipo_persona', 20)->default('NATURAL'); // NATURAL, JURIDICA
            $table->string('primer_apellido', 50)->nullable();
            $table->string('segundo_apellido', 50)->nullable();
            $table->string('nombres', 60)->nullable();
            $table->string('nombre_completo', 150)->index();
            $table->string('numero_documento', 25)->nullable()->index();
            $table->string('complemento', 5)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();

            // Ubicación técnica
            $table->foreignId('id_zona')->constrained('comercial.zonas');
            $table->foreignId('id_calle')->nullable()->constrained('comercial.calles');
            $table->string('numero_vivienda', 20)->nullable();
            $table->string('edificio', 30)->nullable();
            $table->string('departamento', 15)->nullable();
            $table->text('referencia_direccion')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            // Servicios contratados
            $table->foreignId('id_categoria')->constrained('comercial.categorias_tarifarias');
            $table->boolean('tiene_alcantarillado')->default(true);
            $table->boolean('es_tercera_edad')->default(false); // Beneficio Ley 1886
            $table->boolean('tiene_medidor')->default(true);
            $table->foreignId('id_medidor_actual')->nullable()->constrained('comercial.medidores')->nullOnDelete();

            // Estado y fechas del servicio
            $table->string('estado_servicio', 20)->default('ACTIVO')->index(); // ACTIVO, CORTADO, SUSPENDIDO, BAJA
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_ultimo_corte')->nullable();
            $table->date('fecha_ultima_rehabilitacion')->nullable();

            // Saldos y mora
            $table->decimal('saldo_deuda', 12, 2)->default(0.00);
            $table->integer('meses_mora')->default(0)->index();
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 8. PERIODOS DE FACTURACION
        Schema::create('comercial.periodos_facturacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('periodo', 7)->unique()->index(); // 'MM/AAAA' ej. '08/2026'
            $table->integer('mes');
            $table->integer('gestion');
            $table->date('fecha_inicio_consumo');
            $table->date('fecha_fin_consumo');
            $table->date('fecha_vencimiento_pago');
            $table->string('estado', 20)->default('ABIERTO')->index(); // ABIERTO, EN_LECTURACION, FACTURADO, CERRADO
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 9. LECTURAS MENSUALES Y FACTURACION DE AGUA
        Schema::create('comercial.lecturas_mensuales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_periodo')->constrained('comercial.periodos_facturacion');
            $table->foreignId('id_abonado')->constrained('comercial.abonados');
            $table->foreignId('id_medidor')->nullable()->constrained('comercial.medidores');

            // Lecturación física
            $table->decimal('lectura_anterior', 10, 2)->default(0.00);
            $table->decimal('lectura_actual', 10, 2)->default(0.00);
            $table->decimal('consumo_m3', 10, 2)->default(0.00);
            $table->boolean('es_estimada')->default(false);
            $table->string('observacion_lectura', 150)->nullable(); // Medidor roto, sin acceso, fuga, etc.
            $table->timestamp('fecha_lectura')->nullable();
            $table->unsignedBigInteger('id_lecturador')->nullable();

            // Liquidación tarifaria
            $table->decimal('monto_agua', 12, 2)->default(0.00);
            $table->decimal('monto_alcantarillado', 12, 2)->default(0.00);
            $table->decimal('monto_descuento_ley1886', 12, 2)->default(0.00);
            $table->decimal('monto_otros', 12, 2)->default(0.00);
            $table->decimal('total_facturado', 12, 2)->default(0.00);

            // Estado de cobro y enlace con FACTURACION SIAT
            $table->string('estado_pago', 20)->default('PENDIENTE')->index(); // PENDIENTE, PAGADO, EN_CONVENIO, ANULADO
            $table->foreignId('id_factura')->nullable()->constrained('facturacion.facturas')->nullOnDelete();
            $table->timestamp('fecha_pago')->nullable();
            $table->unsignedBigInteger('id_cajero')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_periodo', 'id_abonado'], 'uk_periodo_abonado');
            $table->index(['id_abonado', 'estado_pago'], 'idx_abonado_estado_pago');
        });

        // 10. CONVENIOS DE PAGO
        Schema::create('comercial.convenios_pago', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_abonado')->constrained('comercial.abonados');
            $table->string('numero_convenio', 25)->unique(); // CONV-2026-0001
            $table->decimal('monto_deuda_total', 12, 2);
            $table->decimal('pago_inicial', 12, 2)->default(0.00);
            $table->decimal('saldo_financiado', 12, 2);
            $table->integer('plazo_meses');
            $table->decimal('monto_cuota_mensual', 12, 2);
            $table->date('fecha_suscripcion');
            $table->string('estado', 20)->default('VIGENTE')->index(); // VIGENTE, CUMPLIDO, INCUMPLIDO, ANULADO
            $table->text('glosa')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 11. DETALLE DE CUOTAS DE CONVENIO
        Schema::create('comercial.convenio_cuotas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_convenio')->constrained('comercial.convenios_pago')->cascadeOnDelete();
            $table->integer('numero_cuota'); // 1, 2, 3...
            $table->string('periodo', 7); // 'MM/AAAA'
            $table->decimal('monto_cuota', 12, 2);
            $table->date('fecha_vencimiento');
            $table->string('estado_pago', 20)->default('PENDIENTE')->index(); // PENDIENTE, PAGADO
            $table->foreignId('id_factura')->nullable()->constrained('facturacion.facturas')->nullOnDelete();
            $table->timestamp('fecha_pago')->nullable();
            $table->unsignedBigInteger('id_cajero')->nullable();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();

            $table->unique(['id_convenio', 'numero_cuota'], 'uk_convenio_cuota');
        });

        // 12. ORDENES DE TRABAJO (CORTES, RECONEXIONES, INSPECCIONES)
        Schema::create('comercial.ordenes_trabajo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_orden', 25)->unique(); // OT-2026-0001
            $table->foreignId('id_abonado')->constrained('comercial.abonados');
            $table->string('tipo_orden', 30)->index(); // CORTE_POR_MORA, RECONEXION, CAMBIO_MEDIDOR, INSPECCION_FUGA, BAJA_DEFINITIVA
            $table->text('motivo')->nullable();
            $table->unsignedBigInteger('id_tecnico_asignado')->nullable();
            $table->date('fecha_programada');
            $table->timestamp('fecha_ejecucion')->nullable();
            $table->decimal('lectura_en_corte', 10, 2)->nullable();
            $table->string('numero_precinto', 50)->nullable();
            $table->text('informe_tecnico')->nullable();
            $table->string('estado', 20)->default('PENDIENTE')->index(); // PENDIENTE, EN_PROCESO, EJECUTADO, CANCELADO

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 13. RECIBOS DE CAJA (INGRESOS NO SUJETOS A CREDITO FISCAL / APORTES / INSTALACIONES)
        Schema::create('comercial.recibos_caja', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_recibo', 25)->unique(); // REC-2026-00001
            $table->foreignId('id_abonado')->nullable()->constrained('comercial.abonados')->nullOnDelete();
            $table->string('nombre_cliente', 150);
            $table->string('documento_cliente', 25)->nullable();
            $table->string('concepto_tipo', 40)->index(); // DERECHO_CONEXION, INSTALACION, RECONEXION, CAMBIO_MEDIDOR, APORTE
            $table->text('descripcion');
            $table->decimal('monto_total', 12, 2);
            $table->timestamp('fecha_cobro')->useCurrent();
            $table->unsignedBigInteger('id_cajero')->default(1);
            $table->string('estado', 20)->default('VALIDO')->index(); // VALIDO, ANULADO

            // Auditoría
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
        Schema::dropIfExists('comercial.recibos_caja');
        Schema::dropIfExists('comercial.ordenes_trabajo');
        Schema::dropIfExists('comercial.convenio_cuotas');
        Schema::dropIfExists('comercial.convenios_pago');
        Schema::dropIfExists('comercial.lecturas_mensuales');
        Schema::dropIfExists('comercial.periodos_facturacion');
        Schema::dropIfExists('comercial.abonados');
        Schema::dropIfExists('comercial.medidores');
        Schema::dropIfExists('comercial.tarifas_escalonadas');
        Schema::dropIfExists('comercial.categorias_tarifarias');
        Schema::dropIfExists('comercial.calles');
        Schema::dropIfExists('comercial.zonas');
        DB::statement('DROP SCHEMA IF EXISTS comercial CASCADE');
    }
};
