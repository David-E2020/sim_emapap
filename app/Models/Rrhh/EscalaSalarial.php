<?php

declare(strict_types=1);

namespace App\Models\Rrhh;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EscalaSalarial extends Model
{
    public $timestamps = false;

    protected $table = 'rrhh.escalas_salariales';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'salario',
        'salario_mensual',
        'id_nivel',
        'codigo',
        'id_gestion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $appends = ['salario_mensual'];

    public function getSalarioMensualAttribute(): float
    {
        return (float) ($this->attributes['salario'] ?? 0);
    }

    public function setSalarioMensualAttribute($value): void
    {
        $this->attributes['salario'] = (float) $value;
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id');
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class, 'id_escala_salarial', 'id');
    }
}
