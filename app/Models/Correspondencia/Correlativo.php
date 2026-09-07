<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correlativo extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.correlativos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'gestion',
        'tipo_correlativo',
        'sigla_plantilla',
        'sigla_regional',
        'id_unidad_organizacional',
        'correlativo_actual',
        'formato_cite',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function unidadOrganizacional(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_organizacional', 'id');
    }
}
