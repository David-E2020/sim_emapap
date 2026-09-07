<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $table = 'facturacion.factura_detalles';

    public $timestamps = false;

    protected $fillable = [
        'id_factura',
        'id_producto_servicio',
        'codigo_actividad',
        'codigo_producto_sin',
        'codigo_producto_empresa',
        'descripcion',
        'cantidad',
        'codigo_unidad_medida',
        'precio_unitario',
        'monto_descuento',
        'subtotal',
        'numero_serie',
        'numero_imei',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function productoServicio(): BelongsTo
    {
        return $this->belongsTo(ProductoServicioFactura::class, 'id_producto_servicio');
    }
}
