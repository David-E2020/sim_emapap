<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;

class SiatCatalogo extends Model
{
    protected $table = 'facturacion.catalogos_sin';

    public $timestamps = false;

    protected $fillable = [
        'tipo_catalogo',
        'codigo',
        'descripcion',
        'codigo_padre',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];
}
