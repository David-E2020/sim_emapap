<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteTarifario extends Model
{
    protected $table = 'comercial.paquetes_tarifarios';

    protected $fillable = [
        'codigo',
        'nombre',
        'resolucion_legal',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'es_vigente',
        'descripcion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'es_vigente' => 'boolean',
        'fecha_inicio_vigencia' => 'date',
        'fecha_fin_vigencia' => 'date',
    ];

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaTarifaria::class, 'id_paquete')->orderBy('id');
    }
}
