<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Derivacion extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.derivaciones';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_hoja_ruta',
        'id_derivacion_padre',
        'mpath',
        'id_documento_principal',
        'id_unidad_origen',
        'id_funcionario_origen',
        'id_cargo_origen',
        'id_unidad_destino',
        'id_funcionario_destino',
        'id_cargo_destino',
        'proveido',
        'instruccion_detalle',
        'prioridad',
        'dias_plazo',
        'fecha_limite',
        'fecha_derivacion',
        'fecha_recepcion',
        'fecha_atencion',
        'id_usuario_atencion',
        'estado_derivacion',
        'es_copia',
        'participante_interino',
        'observacion_devolucion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'es_copia' => 'boolean',
        'participante_interino' => 'boolean',
        'dias_plazo' => 'integer',
        'fecha_derivacion' => 'datetime',
        'fecha_recepcion' => 'datetime',
        'fecha_atencion' => 'datetime',
        'fecha_limite' => 'date',
    ];

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta', 'id');
    }

    public function derivacionPadre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_derivacion_padre', 'id');
    }

    public function subDerivaciones(): HasMany
    {
        return $this->hasMany(self::class, 'id_derivacion_padre', 'id');
    }

    public function documentoPrincipal(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento_principal', 'id');
    }

    public function unidadOrigen(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_origen', 'id');
    }

    public function funcionarioOrigen(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_funcionario_origen', 'id');
    }

    public function cargoOrigen(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_cargo_origen', 'id');
    }

    public function unidadDestino(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_destino', 'id');
    }

    public function funcionarioDestino(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_funcionario_destino', 'id');
    }

    public function cargoDestino(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_cargo_destino', 'id');
    }
}
