<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliar campos de facturacion.facturas para auditoría fiscal y RND 102600000025
        Schema::table('facturacion.facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturacion.facturas', 'es_anulacion_administrativa')) {
                $table->boolean('es_anulacion_administrativa')->default(false)->after('fecha_anulacion');
            }
            if (!Schema::hasColumn('facturacion.facturas', 'nro_resolucion_administrativa')) {
                $table->string('nro_resolucion_administrativa', 100)->nullable()->after('es_anulacion_administrativa');
            }
            if (!Schema::hasColumn('facturacion.facturas', 'fecha_resolucion_administrativa')) {
                $table->date('fecha_resolucion_administrativa')->nullable()->after('nro_resolucion_administrativa');
            }
            if (!Schema::hasColumn('facturacion.facturas', 'tiempo_respuesta_ms')) {
                $table->integer('tiempo_respuesta_ms')->nullable()->after('fecha_resolucion_administrativa');
            }
            if (!Schema::hasColumn('facturacion.facturas', 'reversion_anulacion_fecha')) {
                $table->timestamp('reversion_anulacion_fecha')->nullable()->after('tiempo_respuesta_ms');
            }
            if (!Schema::hasColumn('facturacion.facturas', 'reversion_anulacion_usuario')) {
                $table->unsignedBigInteger('reversion_anulacion_usuario')->nullable()->after('reversion_anulacion_fecha');
            }
        });

        // 2. Crear tabla de transacciones de Cobros QR Simple (BCB / ASOBAN Interoperable)
        if (!Schema::hasTable('facturacion.transacciones_qr')) {
            Schema::create('facturacion.transacciones_qr', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('uuid', 64)->unique()->index();
                $table->unsignedBigInteger('id_factura')->nullable()->index();
                $table->unsignedBigInteger('id_abonado')->nullable()->index();
                $table->unsignedBigInteger('id_caja_sesion')->nullable()->index();
                $table->unsignedBigInteger('id_usuario')->nullable()->index();
                
                $table->decimal('monto', 12, 2);
                $table->string('moneda', 3)->default('BOB');
                $table->string('glosa', 255);
                $table->text('qr_payload');
                $table->text('qr_imagen_base64')->nullable();
                $table->string('banco_destino', 60)->default('BANCO UNION S.A.');
                $table->string('cuenta_destino', 50)->default('1000004928192');
                
                // Estados: PENDING, COMPLETED, EXPIRED, CANCELLED
                $table->string('estado', 20)->default('PENDING')->index();
                $table->string('transaccion_banco_id', 100)->nullable();
                
                $table->timestamp('expira_at')->index();
                $table->timestamp('pagado_at')->nullable();
                $table->jsonb('metadata')->nullable();
                
                $table->timestamps();

                $table->foreign('id_factura')
                    ->references('id')
                    ->on('facturacion.facturas')
                    ->onDelete('set null');

                $table->foreign('id_abonado')
                    ->references('id')
                    ->on('comercial.abonados')
                    ->onDelete('set null');

                $table->foreign('id_caja_sesion')
                    ->references('id')
                    ->on('comercial.caja_sesiones')
                    ->onDelete('set null');

                $table->foreign('id_usuario')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturacion.transacciones_qr');

        Schema::table('facturacion.facturas', function (Blueprint $table) {
            $table->dropColumn([
                'es_anulacion_administrativa',
                'nro_resolucion_administrativa',
                'fecha_resolucion_administrativa',
                'tiempo_respuesta_ms',
                'reversion_anulacion_fecha',
                'reversion_anulacion_usuario',
            ]);
        });
    }
};
