<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.niveles';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'jerarquia',
        'id_gestion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];
}
