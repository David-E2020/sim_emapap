<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Puesto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioSecretario extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.usuarios_secretarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_usuario',
        'id_puesto_titular',
        'permisos',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'permisos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function puestoTitular(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_puesto_titular', 'id');
    }
}
