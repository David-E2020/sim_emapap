<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgrupacionHojaRuta extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.agrupaciones_hojas_ruta';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_hoja_ruta_principal',
        'id_hoja_ruta_anexada',
        'motivo_agrupacion',
        'fecha_agrupacion',
        'fecha_desagrupacion',
        'id_usuario_agrupacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function hojaRutaPrincipal(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta_principal', 'id');
    }

    public function hojaRutaAnexada(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta_anexada', 'id');
    }
}
