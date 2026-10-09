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

    public static function getVigente(int $idSucursal = 1, ?int $idPuntoVenta = null, int $codigoPuntoVenta = 0): string
    {
        if ($codigoPuntoVenta === 0 || $idPuntoVenta === null) {
            $cuis = static::where('id_sucursal', $idSucursal)
                ->whereNull('id_punto_venta')
                ->latest('id')
                ->first();
        } else {
            $cuis = static::where('id_sucursal', $idSucursal)
                ->where('id_punto_venta', $idPuntoVenta)
                ->latest('id')
                ->first();
        }

        if (!$cuis) {
            $cuis = static::where('id_sucursal', $idSucursal)->latest('id')->first();
        }

        return $cuis ? (string) $cuis->codigo : '6D4A1883';
    }
}
