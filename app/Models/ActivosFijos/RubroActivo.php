<?php

declare(strict_types=1);

namespace App\Models\ActivosFijos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubroActivo extends Model
{
    protected $table = 'activos_fijos.rubros';

    protected $fillable = [
        'codigo',
        'nombre',
        'tasa_depreciacion',
        'vida_util_meses',
        'actualiza',
    ];

    protected $casts = [
        'tasa_depreciacion' => 'decimal:2',
        'vida_util_meses' => 'integer',
        'actualiza' => 'boolean',
    ];

    public function bienes(): HasMany
    {
        return $this->hasMany(BienActivo::class, 'id_rubro');
    }
}
