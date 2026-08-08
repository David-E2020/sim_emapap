<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryScale extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.salary_scales';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public function category() {
		return $this->belongsTo('App\Models\RRHH\Category');
	}
}
