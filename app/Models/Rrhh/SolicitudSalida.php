<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudSalida extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.solicitudes_salidas';

    protected $primaryKey = 'id';

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'dia_completo' => 'boolean',
        'horas_dinamicas' => 'boolean',
        'es_justificada' => 'boolean',
        'es_pago_refrigerio' => 'boolean',
        'array_horas_dinamicas' => 'array',
        'metadata' => 'array',
    ];

    protected $fillable = [
        'motivo',
        'lugar',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'horas_solicitadas',
        'dia_completo',
        'horas_dinamicas',
        'array_horas_dinamicas',
        'metadata',
        'turno_periodo',
        'hora_marcado_omision',
        'periodo_omision',
        'es_justificada',
        'cite',
        'fecha_aprobacion',
        'es_pago_refrigerio',
        'id_permiso',
        'id_justificacion',
        'justificacion_anulacion',
        'tipo_accion',
        'id_solicitud_referencia',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'id_permiso', 'id');
    }
}
