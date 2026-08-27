<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Persona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaAprobacion extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.firmas_aprobaciones';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_documento',
        'id_participante_doc',
        'id_persona',
        'estado',
        'tipo_firma',
        'fecha_firma_aprobacion',
        'hash_documento_sha256',
        'sello_tiempo',
        'motivo_observacion',
        'codigo_solicitud_aprobacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(ParticipanteDocumento::class, 'id_participante_doc', 'id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
