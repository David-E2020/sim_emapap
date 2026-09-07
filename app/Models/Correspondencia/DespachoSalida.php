<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoSalida extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.despachos_salida';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_hoja_ruta',
        'id_documento',
        'nro_guia_despacho',
        'tipo_despacho',
        'empresa_courier',
        'destinatario_institucion',
        'destinatario_persona',
        'destinatario_direccion',
        'destinatario_ciudad',
        'fecha_despacho',
        'fecha_entrega',
        'estado_despacho',
        'ruta_archivo_acuse',
        'observaciones',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta', 'id');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id');
    }
}
