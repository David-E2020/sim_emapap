<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = 'rrhh.areas';
	protected $primaryKey = 'id';
	public $timestamps = true;
}
