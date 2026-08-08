<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAlmacen extends Model {
	use HasFactory;
	protected $table      = "public.usuario_almacens";
	protected $primaryKey = 'id';
}
