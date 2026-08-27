<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivoAdjunto extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.archivos_adjuntos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_documento',
        'id_hoja_ruta',
        'id_derivacion',
        'nombre_original',
        'ruta_almacenamiento',
        'mime_type',
        'tamanio_bytes',
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

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta', 'id');
    }
}
