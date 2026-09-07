<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaPaquete extends Model
{
    protected $table = 'facturacion.factura_paquetes';

    public $timestamps = false;

    protected $fillable = [
        'id_evento_significativo',
        'codigo_recepcion_paquete',
        'cantidad_facturas',
        'archivo_tar_gz_path',
        'hash_archivo',
        'estado_paquete',
        'observaciones',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function eventoSignificativo(): BelongsTo
    {
        return $this->belongsTo(EventoSignificativo::class, 'id_evento_significativo');
    }
}
