<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaDocumento extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.plantillas_documentos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'sigla',
        'version',
        'param_tipo_plantilla',
        'param_validez_legal',
        'multiples_para',
        'cabecera_html',
        'cuerpo_base',
        'pie_html',
        'config_pagina',
        'hash_datos_plantilla',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'config_pagina' => 'array',
        'multiples_para' => 'boolean',
    ];

    public function componentes(): HasMany
    {
        return $this->hasMany(ComponentePlantilla::class, 'id_plantilla', 'id')->orderBy('orden');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'id_plantilla', 'id');
    }
}
