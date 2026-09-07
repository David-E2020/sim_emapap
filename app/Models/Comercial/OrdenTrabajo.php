<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenTrabajo extends Model
{
    protected $table = 'comercial.ordenes_trabajo';

    public $timestamps = false;

    protected $fillable = [
        'numero_orden',
        'id_abonado',
        'tipo_orden',
        'motivo',
        'id_tecnico_asignado',
        'fecha_programada',
        'fecha_ejecucion',
        'lectura_en_corte',
        'numero_precinto',
        'informe_tecnico',
        'estado',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_ejecucion' => 'datetime',
        'lectura_en_corte' => 'decimal:2',
    ];

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(Abonado::class, 'id_abonado');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tecnico_asignado');
    }
}
