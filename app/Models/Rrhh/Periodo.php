<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Periodo extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.periodos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_horario',
        'hora_inicio',
        'hora_fin',
        'hora_inicio_tolerancia',
        'hora_fin_tolerancia',
        'orden',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id');
    }
}
