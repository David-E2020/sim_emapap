<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtiquetaParticipante extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.etiquetas_participantes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_etiqueta',
        'id_hoja_ruta',
        'id_usuario',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(Etiqueta::class, 'id_etiqueta', 'id');
    }

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta', 'id');
    }
}
