<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuRol extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = "acopio.menu_roles";

	protected $fillable = [
		'menu_id',
		'rol_id',
		'check',
	];

	public function menu() {
		return $this->belongsTo(Menu::class, 'id');
	}

}
