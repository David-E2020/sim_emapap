<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Regulariza las facturas históricas migradas desde FoxPro que fueron anuladas en ventanilla
     * (registradas con cliente 'ANULADO' o importe 0.00) pero que quedaron con estado 'VALIDADA'.
     */
    public function up(): void
    {
        if (Schema::hasTable('facturacion.facturas')) {
            DB::table('facturacion.facturas')
                ->where('estado_factura', 'VALIDADA')
                ->where(function ($query) {
                    $query->where('nombre_razon_social', 'ILIKE', '%ANULAD%')
                        ->orWhere(function ($sub) {
                            $sub->where('monto_total', 0)
                                ->where('nombre_razon_social', 'ILIKE', '%ANUL%');
                        });
                })
                ->update([
                    'estado_factura' => 'ANULADA',
                    'monto_total_sujeto_iva' => 0.00,
                    '_transaccion' => 'REG_ANULADA',
                ]);
        }
    }

    public function down(): void
    {
        // No es necesario revertir ya que son facturas efectivamente anuladas
    }
};
