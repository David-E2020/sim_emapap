<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuRol extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'menu_roles';

    protected $fillable = [
        'menu_id',
        'rol_id',
        'check',
        'usr_registrado',
        'usr_modificado',
        'usr_eliminado',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }
}
