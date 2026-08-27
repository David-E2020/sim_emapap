<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevisionDocumento extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.revisiones_doc';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_documento',
        'ciclo_actual',
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

    public function revisiones(): HasMany
    {
        return $this->hasMany(RevisionDetalle::class, 'id_revision_doc', 'id');
    }
}
