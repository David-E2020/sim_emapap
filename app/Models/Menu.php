<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'menus';

    protected $fillable = [
        'label',
        'icon',
        'route',
        'menu_id',
        'level',
        'order',
        'file',
        'estado',
        'usr_registrado',
        'usr_modificado',
        'usr_eliminado',
    ];

    protected $appends = ['icon_mdi', 'icon_menu'];

    public function getIconMdiAttribute()
    {
        $icon = $this->attributes['icon'] ?? '';

        return Str::kebab($icon);
    }

    public function getIconMenuAttribute()
    {
        $icon = $this->attributes['icon'] ?? '';

        return 'icons.'.$icon;
    }

    public function subMenuN1()
    {
        return $this->hasMany(Menu::class, 'menu_id', 'id')
            ->where('level', 1)
            ->orderBy('order', 'asc');
    }

    public function parentMenu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
