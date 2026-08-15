<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rol_users';

    protected $fillable = [
        'rol_id',
        'usuario_id',
        'estado',
        'usr_registrado',
        'usr_modificado',
        'usr_eliminado',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}
