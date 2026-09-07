<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatoLaboral extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.datos_laborales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cargo',
        'unidad_organizacional',
        'tipo_funcionario',
        'fecha_ingreso',
        'fecha_salida',
        'motivo_salida',
        'id_ficha_personal',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function fichaPersonal(): BelongsTo
    {
        return $this->belongsTo(FichaPersonal::class, 'id_ficha_personal', 'id');
    }
}
