<?php

namespace App\Models;

use App\Models\Acopio\AcopioCapacidad;
use App\Models\Comercial\UsuarioSucursal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Planta extends Model {
	use HasFactory;
	use SoftDeletes;

	public function punto() {
		return $this->hasMany(Silo::class, 'planta_id', 'id');
	}
	public function sucursal() {
		return $this->belongsTo(Sucursal::class)->whereIn('tipo', ['Almacen', 'Silo']);
	}
	public function capacidad() {
		return $this->hasOne(AcopioCapacidad::class, 'cap_planta_id', 'id')->select('cap_planta_id', 'cap_punto_cantidad');
	}
	public function tipo_planta() {
		return $this->hasOne(Parametrica::class, 'param_valor', 'tipo_acopio_id')->where('param_tabla', 'TABLA_TIPO_PLANTA');
	}
}