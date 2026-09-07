<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function unidadOrigen(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_origen', 'id');
    }

    public function unidadDestino(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_destino', 'id');
    }
}
