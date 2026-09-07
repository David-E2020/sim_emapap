<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Medidor extends Model
{
    protected $table = 'comercial.medidores';

    public $timestamps = false;

    protected $fillable = [
        'numero_serie',
        'marca',
        'modelo',
        'diametro',
        'lectura_inicial',
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
        'lectura_inicial' => 'decimal:2',
    ];

    public function abonadoActual(): HasOne
    {
        return $this->hasOne(Abonado::class, 'id_medidor_actual');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMensual::class, 'id_medidor');
    }
}
