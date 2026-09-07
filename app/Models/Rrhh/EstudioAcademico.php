<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudioAcademico extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.estudios_academicos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'institucion',
        'carrera',
        'nivel_instruccion',
        'fecha_emision',
        'id_ficha_personal',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function fichaPersonal(): BelongsTo
    {
        return $this->belongsTo(FichaPersonal::class, 'id_ficha_personal', 'id');
    }
}
