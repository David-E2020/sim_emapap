<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoServicioFactura extends Model
{
    protected $table = 'facturacion.productos_servicios';

    public $timestamps = false;

    protected $fillable = [
        'codigo_producto_empresa',
        'codigo_actividad',
        'codigo_producto_sin',
        'descripcion',
        'precio_unitario',
        'codigo_unidad_medida',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'id_producto_servicio');
    }
}
