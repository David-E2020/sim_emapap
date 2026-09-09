<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoContable extends Model
{
    protected $table = 'contabilidad.periodos';

    public $timestamps = false;

    protected $fillable = [
        'id_gestion',
        'mes',
        'nombre',
        'estado',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'mes' => 'integer',
    ];

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionContable::class, 'id_gestion');
    }
}
