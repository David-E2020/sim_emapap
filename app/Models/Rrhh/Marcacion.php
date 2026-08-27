<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marcacion extends Model
{
    public $timestamps = false;
    protected $table = 'rrhh.marcaciones';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_usuario_marcacion',
        'id_biometrico',
        'hora',
        'fecha',
        'hash',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function biometrico(): BelongsTo
    {
        return $this->belongsTo(Biometrico::class, 'id_biometrico', 'id');
    }
}
