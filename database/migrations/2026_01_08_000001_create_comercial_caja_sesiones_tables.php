<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA DE SESIONES / TURNOS DE CAJA
        Schema::create('comercial.caja_sesiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_sesion', 30)->unique()->index(); // ej: TURNO-2026-00001
            $table->foreignId('id_cajero')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('facturacion.sucursales')->cascadeOnDelete();
            $table->foreignId('id_punto_venta')->constrained('facturacion.puntos_venta')->cascadeOnDelete();

            $table->timestamp('fecha_apertura')->useCurrent()->index();
            $table->timestamp('fecha_cierre')->nullable()->index();

            // Control de dinero en efectivo y métodos electrónicos
            $table->decimal('monto_apertura', 12, 2)->default(0.00); // Saldo inicial para cambio
            $table->decimal('monto_ventas_efectivo', 12, 2)->default(0.00);
            $table->decimal('monto_ventas_qr_banco', 12, 2)->default(0.00);
            $table->decimal('monto_ingresos_extra', 12, 2)->default(0.00);
            $table->decimal('monto_egresos_extra', 12, 2)->default(0.00);

            // Arqueo y Cierre
            $table->decimal('monto_esperado_efectivo', 12, 2)->default(0.00);
            $table->decimal('monto_cierre_declarado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable(); // declarado - esperado (0=cuadrado, +sobrante, -faltante)
            $table->json('desglose_billetes')->nullable(); // Desglose de cortes (Bs 200, 100, 50, etc.)

            $table->string('estado', 20)->default('ABIERTA')->index(); // ABIERTA, CERRADA, ANULADA
            $table->text('observaciones_apertura')->nullable();
            $table->text('observaciones_cierre')->nullable();
            $table->unsignedBigInteger('id_supervisor_cierre')->nullable();

            // Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 2. TABLA DE MOVIMIENTOS MENORES DE CAJA CHICA / GAVETA
        Schema::create('comercial.caja_movimientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_sesion')->constrained('comercial.caja_sesiones')->cascadeOnDelete();
            $table->string('tipo', 20)->index(); // INGRESO, EGRESO
            $table->string('concepto', 150);
            $table->decimal('monto', 12, 2);
            $table->string('beneficiario', 150)->nullable();
            $table->string('comprobante', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha')->useCurrent();

            // Auditoría
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
        });

        // 3. AGREGAR CAJERO DEFECTO A PUNTOS DE VENTA
        Schema::table('facturacion.puntos_venta', function (Blueprint $table) {
            if (!Schema::hasColumn('facturacion.puntos_venta', 'id_cajero_defecto')) {
                $table->unsignedBigInteger('id_cajero_defecto')->nullable()->after('descripcion');
            }
        });

        // 4. VINCULAR COBROS A LA SESIÓN DE CAJA
        Schema::table('comercial.lecturas_mensuales', function (Blueprint $table) {
            if (!Schema::hasColumn('comercial.lecturas_mensuales', 'id_sesion_caja')) {
                $table->unsignedBigInteger('id_sesion_caja')->nullable()->after('id_cajero')->index();
            }
        });

        Schema::table('comercial.convenio_cuotas', function (Blueprint $table) {
            if (!Schema::hasColumn('comercial.convenio_cuotas', 'id_sesion_caja')) {
                $table->unsignedBigInteger('id_sesion_caja')->nullable()->after('id_cajero')->index();
            }
        });

        Schema::table('comercial.recibos_caja', function (Blueprint $table) {
            if (!Schema::hasColumn('comercial.recibos_caja', 'id_sesion_caja')) {
                $table->unsignedBigInteger('id_sesion_caja')->nullable()->after('id_cajero')->index();
            }
        });

        Schema::table('facturacion.facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturacion.facturas', 'id_sesion_caja')) {
                $table->unsignedBigInteger('id_sesion_caja')->nullable()->after('id_punto_venta')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturacion.facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturacion.facturas', 'id_sesion_caja')) {
                $table->dropColumn('id_sesion_caja');
            }
        });

        Schema::table('comercial.recibos_caja', function (Blueprint $table) {
            if (Schema::hasColumn('comercial.recibos_caja', 'id_sesion_caja')) {
                $table->dropColumn('id_sesion_caja');
            }
        });

        Schema::table('comercial.convenio_cuotas', function (Blueprint $table) {
            if (Schema::hasColumn('comercial.convenio_cuotas', 'id_sesion_caja')) {
                $table->dropColumn('id_sesion_caja');
            }
        });

        Schema::table('comercial.lecturas_mensuales', function (Blueprint $table) {
            if (Schema::hasColumn('comercial.lecturas_mensuales', 'id_sesion_caja')) {
                $table->dropColumn('id_sesion_caja');
            }
        });

        Schema::table('facturacion.puntos_venta', function (Blueprint $table) {
            if (Schema::hasColumn('facturacion.puntos_venta', 'id_cajero_defecto')) {
                $table->dropColumn('id_cajero_defecto');
            }
        });

        Schema::dropIfExists('comercial.caja_movimientos');
        Schema::dropIfExists('comercial.caja_sesiones');
    }
};
