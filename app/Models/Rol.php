<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function menu_roles()
    {
        return $this->hasMany(MenuRol::class, 'rol_id', 'id');
    }

    public function rol_users()
    {
        return $this->hasMany(RolUser::class, 'rol_id', 'id');
    }
}
