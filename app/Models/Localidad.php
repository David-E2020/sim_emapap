<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Localidad extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'public.localidades';
	protected $primaryKey = 'id';
	public $timestamps = true;
}
