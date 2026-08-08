<?php

namespace App\Models;

use App\Models\Insumos\CategoriasArticulosInsumo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class PlantaUsuario extends Model {
	use HasFactory;
	public function planta() {
		return $this->hasMany(Planta::class, 'id', 'planta_id')->with('punto');
	}
	// public function categoria()
	// {
	// 	return $this->belongsTo(CategoriasArticulosInsumo::class, 'planta_id');
	// }
	public function role_user() {
		return $this->hasOne(RolUser::class, 'usuario_id', 'user_id')->with('usuario');
	}
	public function planta_usuario() {
        return $this->hasOne(Planta::class, 'id', 'planta_id')->select('id', 'nombre');
    }
}
