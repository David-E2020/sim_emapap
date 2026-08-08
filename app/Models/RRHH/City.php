<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'public.departamentos';
	protected $primaryKey = 'id';
	public $timestamps = true;
}
