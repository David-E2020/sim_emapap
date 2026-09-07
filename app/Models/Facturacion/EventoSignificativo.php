<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoSignificativo extends Model
{
    protected $table = 'facturacion.eventos_significativos';

    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_punto_venta',
        'codigo_evento',
        'descripcion',
        'cufd_evento',
        'fecha_inicio',
        'fecha_fin',
        'cafc',
        'estado_evento',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
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
        return $this->hasMany(Factura::class, 'id_evento_significativo');
    }

    public function paquetes(): HasMany
    {
        return $this->hasMany(FacturaPaquete::class, 'id_evento_significativo');
    }
}
