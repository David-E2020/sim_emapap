<?php

declare(strict_types=1);

namespace App\Models\Almacen;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexMovimiento extends Model
{
    protected $table = 'almacen.kardex_movimientos';

    protected $fillable = [
        'id_material',
        'tipo_movimiento',
        'fecha',
        'comprobante_origen',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'saldo_cantidad',
        'saldo_valorado',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'costo_unitario' => 'decimal:4',
        'costo_total' => 'decimal:2',
        'saldo_cantidad' => 'decimal:2',
        'saldo_valorado' => 'decimal:2',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material');
    }
}
