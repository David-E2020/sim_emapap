<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaTarifaria extends Model
{
    protected $table = 'comercial.categorias_tarifarias';

    public $timestamps = false;

    protected $fillable = [
        'id_paquete',
        'codigo',
        'nombre',
        'volumen_base',
        'tarifa_minima',
        'tarifa_excedente_base',
        'tarifa_alcantarillado',
        'aplica_ley_1886',
        'activo',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'volumen_base' => 'decimal:2',
        'tarifa_minima' => 'decimal:2',
        'tarifa_excedente_base' => 'decimal:2',
        'tarifa_alcantarillado' => 'decimal:2',
        'aplica_ley_1886' => 'boolean',
        'activo' => 'boolean',
    ];

    public function tarifasEscalonadas(): HasMany
    {
        return $this->hasMany(TarifaEscalonada::class, 'id_categoria')->orderBy('desde_m3');
    }

    public function abonados(): HasMany
    {
        return $this->hasMany(Abonado::class, 'id_categoria');
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(PaqueteTarifario::class, 'id_paquete');
    }
}
