<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;

class FacturaCompra extends Model
{
    protected $table = 'contabilidad.facturas_compra';

    protected $fillable = [
        'especificacion',
        'numero_factura',
        'fecha_factura',
        'nit_proveedor',
        'razon_social_proveedor',
        'codigo_autorizacion',
        'codigo_control',
        'importe_total',
        'importe_ice',
        'importe_exento',
        'importe_tasa_cero',
        'subtotal',
        'descuentos',
        'importe_base_cf',
        'credito_fiscal',
        'tipo_compra',
        'gestion',
        'mes',
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'importe_total' => 'decimal:2',
        'importe_ice' => 'decimal:2',
        'importe_exento' => 'decimal:2',
        'importe_tasa_cero' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuentos' => 'decimal:2',
        'importe_base_cf' => 'decimal:2',
        'credito_fiscal' => 'decimal:2',
        'gestion' => 'integer',
        'mes' => 'integer',
    ];
}
