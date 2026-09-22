<?php

declare(strict_types=1);

namespace App\Models\Almacen;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'almacen.materiales';

    protected $fillable = [
        'codigo_item',
        'nombre',
        'unidad_medida',
        'stock_minimo',
        'stock_actual',
        'precio_promedio',
        'precio_venta',
        'moneda',
        'grupo',
        'subgrupo',
        'id_bodega_default',
        'estado',
    ];

    protected $casts = [
        'stock_minimo' => 'decimal:2',
        'stock_actual' => 'decimal:2',
        'precio_promedio' => 'decimal:4',
        'precio_venta' => 'decimal:4',
        'estado' => 'boolean',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'id_material');
    }
}
