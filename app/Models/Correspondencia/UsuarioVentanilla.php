<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioVentanilla extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.usuarios_ventanilla';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_usuario',
        'id_ventanilla',
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

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class, 'id_ventanilla', 'id');
    }
}
