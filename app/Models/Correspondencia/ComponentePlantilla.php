<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentePlantilla extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.componentes_plantillas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_plantilla',
        'nombre',
        'tipo_componente',
        'orden',
        'es_obligatorio',
        'config_inicial',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'config_inicial' => 'array',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaDocumento::class, 'id_plantilla', 'id');
    }
}
