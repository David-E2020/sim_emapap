<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feriado extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.feriados';
    protected $primaryKey = 'id';

    protected $casts = [
        'es_feriado_nacional' => 'boolean',
    ];

    protected $fillable = [
        'nombre',
        'dia',
        'mes',
        'dia_feriado',
        'anio',
        'es_feriado_nacional',
        'id_departamento',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id');
    }
}
