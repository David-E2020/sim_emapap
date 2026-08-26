<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Rol extends SpatieRole
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
