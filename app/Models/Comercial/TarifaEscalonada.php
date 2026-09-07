<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaEscalonada extends Model
{
    protected $table = 'comercial.tarifas_escalonadas';

    public $timestamps = false;

    protected $fillable = [
        'id_categoria',
        'desde_m3',
        'hasta_m3',
        'precio_m3',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'desde_m3' => 'decimal:2',
        'hasta_m3' => 'decimal:2',
        'precio_m3' => 'decimal:2',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaTarifaria::class, 'id_categoria');
    }
}
