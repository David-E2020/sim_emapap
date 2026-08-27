<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCiudadana extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.solicitudes_ciudadanas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_solicitud',
        'solicitante_nombre',
        'solicitante_ci_nit',
        'solicitante_telefono',
        'solicitante_correo',
        'tipo_solicitud',
        'descripcion_solicitud',
        'archivos_adjuntos',
        'estado_solicitud',
        'id_hoja_ruta_generada',
        'motivo_rechazo_atencion',
        'fecha_solicitud',
        'fecha_atencion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'archivos_adjuntos' => 'array',
        'fecha_solicitud' => 'datetime',
        'fecha_atencion' => 'datetime',
    ];

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta_generada', 'id');
    }
}
