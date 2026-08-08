<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model {

	protected $table = "acopio.roles";
	use HasFactory;
	use SoftDeletes;

	protected $fillable = [
		'name',
		'guard_name',
	];

	public function menus() {
		return $this->hasMany(Menu::class, 'id');
	}
	public function rol_user() {
		return $this->hasOne(PlantaUsuario::class, 'rol_id', 'id');
	}

}
