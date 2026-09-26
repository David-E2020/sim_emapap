<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FichaPersonal extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.fichas_personales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_persona',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }

    public function datosLaborales(): HasMany
    {
        return $this->hasMany(DatoLaboral::class, 'id_ficha_personal', 'id')->orderBy('id', 'desc');
    }

    public function estudiosAcademicos(): HasMany
    {
        return $this->hasMany(EstudioAcademico::class, 'id_ficha_personal', 'id');
    }

    public function experienciasLaborales(): HasMany
    {
        return $this->hasMany(ExperienciaLaboral::class, 'id_ficha_personal', 'id');
    }

    public function cas(): HasMany
    {
        return $this->hasMany(Cas::class, 'id_ficha_personal', 'id');
    }
}
