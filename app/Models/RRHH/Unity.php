<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unity extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.unities';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function management() {
		return $this->belongsTo('App\Models\RRHH\Management', 'managament_id');
	}
}
