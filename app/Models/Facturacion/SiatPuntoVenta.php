<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiatPuntoVenta extends Model
{
    protected $table = 'facturacion.puntos_venta';

    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'codigo_punto_venta',
        'nombre',
        'tipo_punto_venta',
        'descripcion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SiatSucursal::class, 'id_sucursal');
    }

    public function cuis(): HasMany
    {
        return $this->hasMany(SiatCuis::class, 'id_punto_venta');
    }

    public function cufd(): HasMany
    {
        return $this->hasMany(SiatCufd::class, 'id_punto_venta');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_punto_venta');
    }
}
