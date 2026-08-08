<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Menu extends Model {
	protected $table = "acopio.menus";
	use HasFactory;
	use SoftDeletes;

	protected $fillable = [
		'label',
		'icon',
		'route',
		'menu_id',
		'level',
		'order',
	];

	protected $appends = ['icon_mdi', 'icon_menu'];

	public function getIconMdiAttribute() {
		$icon = $this->attributes['icon'];
		$slug = Str::kebab($icon, '_');
		return $slug;
	}

	public function getIconMenuAttribute() {
		$icon = $this->attributes['icon'];
		//$slug = Str::kebab($icon, '_');
		return "icons." . $icon;
	}

	public function subMenuN1() {
		return $this->hasMany(Menu::class)->where('level', '=', '1');
	}

}
