<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;

class PermisoDerivacion extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.permisos_derivacion';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_origen',
        'tipo_origen',
        'id_destino',
        'tipo_destino',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];
}
