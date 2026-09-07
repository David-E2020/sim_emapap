<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadOrganizacional extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.unidades_organizacionales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'nombre',
        'sigla',
        'es_unidad_recursos_humanos',
        'id_organismo_padre',
        'padreId',
        'mpath',
        'id_regional',
        'id_gestion',
        'id_nivel',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class, 'id_regional', 'id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padreId', 'id');
    }

    public function dependencias(): HasMany
    {
        return $this->hasMany(self::class, 'padreId', 'id')->with([
            'puestos.asignaciones.persona',
            'dependencias',
        ]);
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class, 'id_unidad_organizacional', 'id');
    }
}
