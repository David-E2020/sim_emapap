<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.contributions';
	protected $primaryKey = 'id';
	public $timestamps = true;
}
