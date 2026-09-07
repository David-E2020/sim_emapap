<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;

    protected $table = 'rrhh.entidades';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'sigla',
        'codigo_entidad',
        'tipo_entidad',
        'tipo_instancia',
        'id_entidad_gob_bo',
        'direccion',
        'contactos',
        'logo',
        'vigente',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'vigente' => 'boolean',
        'contactos' => 'array',
    ];
}
