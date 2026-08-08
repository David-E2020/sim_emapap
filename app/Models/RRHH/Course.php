<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model {

	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.courses';
	protected $primaryKey = 'id';
	public $timestamps = true;
}
