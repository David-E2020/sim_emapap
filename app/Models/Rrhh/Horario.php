<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horario extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.horarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'tipo',
        'dias_laborales',
        'tolerancia_minutos',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class, 'id_horario', 'id')->orderBy('orden');
    }
}
