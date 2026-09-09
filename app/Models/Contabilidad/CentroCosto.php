<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroCosto extends Model
{
    protected $table = 'contabilidad.centros_costo';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function comprobanteDetalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'id_centro_costo');
    }
}
