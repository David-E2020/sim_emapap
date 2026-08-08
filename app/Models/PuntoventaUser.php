<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PuntoventaUser extends Model {
	use HasFactory;

	use SoftDeletes;
	protected $table = "acopio.puntoventa_users";
	protected $fillable = [];

	public function puntoventa() {
		return $this->belongsTo(PuntoVenta::class, 'puntoventa_id', 'id');
	}

}
