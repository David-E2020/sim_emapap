<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacation extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.vacations';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public function employee() {
		return $this->belongsTo('App\Employee')->with('position');
	}
}
