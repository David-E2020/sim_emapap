<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GestionContable extends Model
{
    protected $table = 'contabilidad.gestiones';

    public $timestamps = false;

    protected $fillable = [
        'gestion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'gestion' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function periodos(): HasMany
    {
        return $this->hasMany(PeriodoContable::class, 'id_gestion')->orderBy('mes');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class, 'id_gestion');
    }
}
