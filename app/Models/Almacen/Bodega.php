<?php

declare(strict_types=1);

namespace App\Models\Almacen;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    protected $table = 'almacen.bodegas';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];
}
