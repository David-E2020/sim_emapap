<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapeoEnlace extends Model
{
    protected $table = 'contabilidad.mapeo_enlaces';

    public $timestamps = false;

    protected $fillable = [
        'codigo_enlace',
        'descripcion',
        'modulo',
        'id_cuenta_defecto',
        'id_centro_costo_defecto',
        '_estado',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function cuentaDefecto(): BelongsTo
    {
        return $this->belongsTo(PlanCuenta::class, 'id_cuenta_defecto');
    }

    public function centroCostoDefecto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'id_centro_costo_defecto');
    }
}
