<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.personas';

    protected $primaryKey = 'id';

    protected $appends = ['nombre_completo'];

    protected $fillable = [
        'nombres',
        'primer_apellido',
        'segundo_apellido',
        'tipo_documento',
        'tipo_documento_otro',
        'nro_documento',
        'fecha_nacimiento',
        'correo_electronico_personal',
        'telefono_celular',
        'genero',
        'observacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'usr_externo_id', 'id');
    }

    public function fichaPersonal(): HasOne
    {
        return $this->hasOne(FichaPersonal::class, 'id_persona', 'id');
    }

    public function asignacionesPuestos(): HasMany
    {
        return $this->hasMany(AsignacionPuesto::class, 'id_persona', 'id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_persona', 'id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->primer_apellido} {$this->segundo_apellido}");
    }
}
