<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.permisos';
    protected $primaryKey = 'id';

    protected $casts = [
        'es_con_goce_haberes' => 'boolean',
        'es_acumulativo' => 'boolean',
        'es_pago_refrigerio' => 'boolean',
    ];

    protected $fillable = [
        'nombre',
        'sigla',
        'tiempo_maximo',
        'tipo_tiempo',
        'es_con_goce_haberes',
        'es_acumulativo',
        'tiempo_maximo_acumulativo',
        'tiempo_maximo_periodo',
        'tipo_tiempo_periodo',
        'cantidad_maxima_periodo',
        'tipo_periodo',
        'cantidad_dias_mes',
        'tiempo_maximo_mes',
        'tipo_tiempo_mes',
        'tipo_accion',
        'es_pago_refrigerio',
        'descripcion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];
}
