<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion.facturas', function (Blueprint $table) {
            // Permitir valores nulos para facturas históricas pre-SIAT
            $table->unsignedBigInteger('id_cufd')->nullable()->change();
            $table->text('cufd')->nullable()->change();
            $table->string('codigo_control', 50)->nullable()->change();

            // Vínculo directo con abonado comercial
            $table->foreignId('id_abonado')->nullable()->after('id_cliente')->constrained('comercial.abonados')->nullOnDelete();

            // Campos específicos para Documento Sector 13 (Servicios Básicos)
            $table->string('mes', 20)->nullable()->after('codigo_documento_sector');
            $table->integer('gestion')->nullable()->after('mes');
            $table->string('ciudad', 100)->nullable()->default('Patacamaya')->after('gestion');
            $table->string('zona', 100)->nullable()->after('ciudad');
            $table->string('numero_medidor', 50)->nullable()->after('zona');
            $table->string('domicilio_cliente', 255)->nullable()->after('numero_medidor');
            $table->decimal('consumo_periodo', 10, 2)->nullable()->after('domicilio_cliente');
            $table->boolean('beneficiario_ley_1886')->default(false)->after('consumo_periodo');
            $table->decimal('monto_descuento_ley_1886', 12, 2)->default(0.00)->after('beneficiario_ley_1886');
            $table->decimal('monto_descuento_tarifa_dignidad', 12, 2)->default(0.00)->after('monto_descuento_ley_1886');
            $table->decimal('tasa_aseo', 12, 2)->default(0.00)->after('monto_descuento_tarifa_dignidad');
            $table->decimal('tasa_alumbrado', 12, 2)->default(0.00)->after('tasa_aseo');
            $table->decimal('ajuste_no_sujeto_iva', 12, 2)->default(0.00)->after('tasa_alumbrado'); // Alcantarillado
            $table->text('detalle_ajuste_no_sujeto_iva')->nullable()->after('ajuste_no_sujeto_iva');
            $table->decimal('ajuste_sujeto_iva', 12, 2)->default(0.00)->after('detalle_ajuste_no_sujeto_iva');
            $table->text('detalle_ajuste_sujeto_iva')->nullable()->after('ajuste_sujeto_iva');
            $table->decimal('otros_pagos_no_sujeto_iva', 12, 2)->default(0.00)->after('detalle_ajuste_sujeto_iva');
            $table->text('detalle_otros_pagos_no_sujeto_iva')->nullable()->after('otros_pagos_no_sujeto_iva');
            $table->decimal('otras_tasas', 12, 2)->default(0.00)->after('detalle_otros_pagos_no_sujeto_iva');
            $table->string('codigo_autorizacion_sfv', 50)->nullable()->after('otras_tasas');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion.facturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_abonado');
            $table->dropColumn([
                'mes',
                'gestion',
                'ciudad',
                'zona',
                'numero_medidor',
                'domicilio_cliente',
                'consumo_periodo',
                'beneficiario_ley_1886',
                'monto_descuento_ley_1886',
                'monto_descuento_tarifa_dignidad',
                'tasa_aseo',
                'tasa_alumbrado',
                'ajuste_no_sujeto_iva',
                'detalle_ajuste_no_sujeto_iva',
                'ajuste_sujeto_iva',
                'detalle_ajuste_sujeto_iva',
                'otros_pagos_no_sujeto_iva',
                'detalle_otros_pagos_no_sujeto_iva',
                'otras_tasas',
                'codigo_autorizacion_sfv',
            ]);
        });
    }
};
