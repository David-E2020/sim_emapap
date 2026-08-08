<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Spatie\Permission\Models\Rol;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolUser extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = 'acopio.rol_users';
	protected $fillable = [
		'rol_id',
		'usuario_id',
	];

	public function rol() {
		return $this->hasOne(Rol::class, 'id');
	}
	public function usuario() {
		return $this->hasOne(User::class, 'id', 'usuario_id');
	}
	public function planta_usuario() {
		return $this->hasOne(PlantaUsuario::class, 'user_id', 'usuario_id');
	}
}
