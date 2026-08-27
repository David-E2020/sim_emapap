<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;

class Gestion extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.gestiones';
    protected $primaryKey = 'id';

    protected $fillable = [
        'anio',
        'descripcion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];
}
