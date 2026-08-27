<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivoGenerado extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.archivos_generados';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_documento',
        'ruta_pdf',
        'version',
        'hash_sha256',
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
}
