<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calle extends Model
{
    protected $table = 'comercial.calles';

    public $timestamps = false;

    protected $fillable = [
        'id_zona',
        'nombre',
        'referencia',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }

    public function abonados(): HasMany
    {
        return $this->hasMany(Abonado::class, 'id_calle');
    }
}
