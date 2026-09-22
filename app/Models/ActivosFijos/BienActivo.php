<?php

declare(strict_types=1);

namespace App\Models\ActivosFijos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BienActivo extends Model
{
    protected $table = 'activos_fijos.bienes';

    protected $fillable = [
        'codigo_item',
        'nombre',
        'fecha_ingreso',
        'vida_util',
        'unidad',
        'cantidad',
        'valor_inicial',
        'valor_actualizado',
        'depreciacion_acumulada',
        'valor_residual',
        'id_rubro',
        'estado',
        'ubicacion_sitio',
        'observaciones',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'vida_util' => 'integer',
        'cantidad' => 'decimal:2',
        'valor_inicial' => 'decimal:2',
        'valor_actualizado' => 'decimal:2',
        'depreciacion_acumulada' => 'decimal:2',
        'valor_residual' => 'decimal:2',
    ];

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(RubroActivo::class, 'id_rubro');
    }
}
