<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model {
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['cli_id', 'cli_nombres', 'cli_paterno', 'cli_materno', 'cli_ci', 'cli_celular', 'cli_direccion', 'cli_razon_social', 'cli_ci_nit', 'cli_correo', 'cli_zona', 'cli_ciudad', 'cli_rubro', 'cli_estado', 'cli_usr_registrado', 'created_at', 'updated_at', 'deleted_at', 'cli_persona', 'cli_tipo_documento', 'cli_ci_complemento', 'cli_complemento', 'cli_tipo_identificacion', 'cli_nombre_representante', 'cli_paterno_representante', 'cli_materno_representante', 'cli_asociacion', 'cli_departamento_id', 'cli_provincia_id'];
    protected $table = 'public.clientes';
    protected $primaryKey = 'cli_id';
}
