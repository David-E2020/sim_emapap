<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienciaLaboral extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.experiencias_laborales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_institucion',
        'cargo',
        'fecha_inicio',
        'fecha_fin',
        'motivo_retiro',
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
