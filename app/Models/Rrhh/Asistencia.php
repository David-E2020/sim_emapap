<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.asistencias';

    protected $primaryKey = 'id';

    protected $casts = [
        'fecha' => 'date',
        'array_observacion_entrada_primer_periodo' => 'array',
        'array_observacion_salida_primer_periodo' => 'array',
        'array_observacion_entrada_segundo_periodo' => 'array',
        'array_observacion_salida_segundo_periodo' => 'array',
        'solicitudes_salida' => 'array',
        'merece_refrigerio' => 'boolean',
    ];

    protected $fillable = [
        'fecha',
        'entrada_primer_periodo',
        'salida_primer_periodo',
        'entrada_segundo_periodo',
        'salida_segundo_periodo',
        'observacion_entrada_primer_periodo',
        'observacion_salida_primer_periodo',
        'observacion_entrada_segundo_periodo',
        'observacion_salida_segundo_periodo',
        'array_observacion_entrada_primer_periodo',
        'array_observacion_salida_primer_periodo',
        'array_observacion_entrada_segundo_periodo',
        'array_observacion_salida_segundo_periodo',
        'solicitudes_salida',
        'minutos_de_atraso_primer_periodo',
        'minutos_de_atraso_segundo_periodo',
        'minutos_de_salida_temprana_primer_periodo',
        'minutos_de_salida_temprana_segundo_periodo',
        'merece_refrigerio',
        'dia',
        'id_fecha_corte',
        'id_persona',
        'id_horario',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id');
    }
}
