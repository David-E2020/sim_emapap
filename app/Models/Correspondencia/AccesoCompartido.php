<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccesoCompartido extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.accesos_compartidos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_hoja_ruta',
        'id_documento',
        'id_usuario_destinatario',
        'id_usuario_autorizador',
        'fecha_expiracion',
        'motivo',
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

    public function usuarioDestinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_destinatario', 'id');
    }

    public function usuarioAutorizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_autorizador', 'id');
    }
}
