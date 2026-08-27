<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Biometrico extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.biometricos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'url',
        'puerto',
        'tipo',
        'modelo',
        'usuario',
        'contrasenia',
        'ubicacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function marcaciones(): HasMany
    {
        return $this->hasMany(Marcacion::class, 'id_biometrico', 'id');
    }
}
