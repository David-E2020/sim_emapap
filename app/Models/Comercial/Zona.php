<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $table = 'comercial.zonas';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function calles(): HasMany
    {
        return $this->hasMany(Calle::class, 'id_zona');
    }

    public function abonados(): HasMany
    {
        return $this->hasMany(Abonado::class, 'id_zona');
    }
}
