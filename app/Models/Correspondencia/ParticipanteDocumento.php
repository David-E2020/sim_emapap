<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParticipanteDocumento extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.participantes_documentos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_documento',
        'id_persona',
        'id_puesto',
        'tipo_participacion',
        'orden_participacion',
        'cargo_snapshot',
        'participante_interino',
        'bandeja',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'participante_interino' => 'boolean',
        'orden_participacion' => 'integer',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_puesto', 'id');
    }

    public function firmaAprobacion(): HasOne
    {
        return $this->hasOne(FirmaAprobacion::class, 'id_participante_doc', 'id');
    }
}
