<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model {
	use HasFactory;

	use SoftDeletes;
	protected $table = "public.sucursals";
	protected $fillable = [];

	//puntoVenta

	public function planta() {
		return $this->hasMany(Planta::class, 'planta_id');
	}
}
