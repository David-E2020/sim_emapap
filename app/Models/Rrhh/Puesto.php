<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puesto extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.puestos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'tipo_puesto',
        'id_escala_salarial',
        'id_unidad_organizacional',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function unidadOrganizacional(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_organizacional', 'id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionPuesto::class, 'id_puesto', 'id');
    }
}
