<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abonado extends Model
{
    protected $table = 'comercial.abonados';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'tipo_persona',
        'primer_apellido',
        'segundo_apellido',
        'nombres',
        'nombre_completo',
        'numero_documento',
        'complemento',
        'telefono',
        'celular',
        'email',
        'persona_contacto',
        'id_zona',
        'id_calle',
        'numero_vivienda',
        'edificio',
        'departamento',
        'referencia_direccion',
        'latitud',
        'longitud',
        'id_categoria',
        'tiene_alcantarillado',
        'es_tercera_edad',
        'tiene_medidor',
        'id_medidor_actual',
        'estado_servicio',
        'fecha_ingreso',
        'fecha_ultimo_corte',
        'fecha_ultima_rehabilitacion',
        'saldo_deuda',
        'meses_mora',
        'observaciones',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'tiene_alcantarillado' => 'boolean',
        'es_tercera_edad' => 'boolean',
        'tiene_medidor' => 'boolean',
        'saldo_deuda' => 'decimal:2',
        'meses_mora' => 'integer',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'fecha_ingreso' => 'date',
        'fecha_ultimo_corte' => 'date',
        'fecha_ultima_rehabilitacion' => 'date',
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }

    public function calle(): BelongsTo
    {
        return $this->belongsTo(Calle::class, 'id_calle');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaTarifaria::class, 'id_categoria');
    }

    public function medidorActual(): BelongsTo
    {
        return $this->belongsTo(Medidor::class, 'id_medidor_actual');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMensual::class, 'id_abonado');
    }

    public function convenios(): HasMany
    {
        return $this->hasMany(ConvenioPago::class, 'id_abonado');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'id_abonado');
    }

    public function recibosCaja(): HasMany
    {
        return $this->hasMany(ReciboCaja::class, 'id_abonado');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(\App\Models\Facturacion\Factura::class, 'id_abonado');
    }
}
