<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.departamentos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function feriados(): HasMany
    {
        return $this->hasMany(Feriado::class, 'id_departamento', 'id');
    }
}
