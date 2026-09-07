<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parametrica extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'parametricas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'param_nombre',
        'param_codigo',
        'param_descripcion',
        'param_valor',
        'param_tabla',
        'param_usr_registrado',
        'param_usr_modificado',
        'param_usr_eliminado',
        'param_estado',
    ];
}
