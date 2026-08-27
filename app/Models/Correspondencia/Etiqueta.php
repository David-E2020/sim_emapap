<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Etiqueta extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.etiquetas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'color',
        'id_usuario',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
