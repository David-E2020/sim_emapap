<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiatCufd extends Model
{
    protected $table = 'facturacion.cufd';

    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_punto_venta',
        'codigo',
        'codigo_control',
        'direccion',
        'fecha_vigencia',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'fecha_vigencia' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SiatSucursal::class, 'id_sucursal');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'id_punto_venta');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_cufd');
    }
}
