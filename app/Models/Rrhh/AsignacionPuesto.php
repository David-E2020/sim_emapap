<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPuesto extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.asignaciones_puestos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipo_asignacion',
        'asignacion',
        'nro_item',
        'id_puesto',
        'id_persona',
        'id_asignacion_original',
        'id_unidad_comision',
        'fecha_inicio',
        'fecha_fin',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_puesto', 'id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
