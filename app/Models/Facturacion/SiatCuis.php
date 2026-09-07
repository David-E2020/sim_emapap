<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiatCuis extends Model
{
    protected $table = 'facturacion.cuis';

    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_punto_venta',
        'codigo',
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

    public function getCodigoCuisAttribute(): ?string
    {
        return $this->codigo;
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SiatSucursal::class, 'id_sucursal');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'id_punto_venta');
    }
}
