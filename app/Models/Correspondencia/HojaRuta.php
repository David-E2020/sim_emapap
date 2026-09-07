<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Entidad;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaRuta extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.hojas_ruta';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nro_hoja_ruta',
        'gestion',
        'tipo_hr',
        'origen',
        'asunto',
        'referencia',
        'prioridad',
        'confidencial',
        'nro_fojas',
        'nro_anexos',
        'id_unidad_origen',
        'id_persona_origen',
        'id_cargo_origen',
        'remitente_externo',
        'id_entidad_externa',
        'id_ventanilla_origen',
        'estado',
        'fecha_solicitud',
        'esta_con',
        'datos_origen',
        'datos_solicitante',
        'fecha_cierre',
        'id_usuario_cierre',
        'id_cargo_cierre',
        'motivo_cierre',
        'fecha_reapertura',
        'id_usuario_reapertura',
        'motivo_reapertura',
        'fecha_anulacion',
        'id_usuario_anulacion',
        'motivo_anulacion',
        'id_hoja_ruta_padre',
        'cantidad_usuarios_compartidos',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'confidencial' => 'boolean',
        'esta_con' => 'array',
        'datos_origen' => 'array',
        'datos_solicitante' => 'array',
        'fecha_solicitud' => 'datetime',
        'fecha_cierre' => 'datetime',
        'fecha_reapertura' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    public function unidadOrigen(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_origen', 'id');
    }

    public function personaOrigen(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_origen', 'id');
    }

    public function cargoOrigen(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'id_cargo_origen', 'id');
    }

    public function entidadExterna(): BelongsTo
    {
        return $this->belongsTo(Entidad::class, 'id_entidad_externa', 'id');
    }

    public function derivaciones(): HasMany
    {
        return $this->hasMany(Derivacion::class, 'id_hoja_ruta', 'id')->orderBy('id');
    }

    public function documentos(): BelongsToMany
    {
        return $this->belongsToMany(Documento::class, 'correspondencia.hoja_ruta_documentos', 'id_hoja_ruta', 'id_documento')
            ->withPivot('es_documento_principal');
    }

    public function archivosAdjuntos(): HasMany
    {
        return $this->hasMany(ArchivoAdjunto::class, 'id_hoja_ruta', 'id');
    }

    public function agrupaciones(): HasMany
    {
        return $this->hasMany(AgrupacionHojaRuta::class, 'id_hoja_ruta_principal', 'id');
    }

    public function hojaRutaPadre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_hoja_ruta_padre', 'id');
    }

    public function hojasRutaHijas(): HasMany
    {
        return $this->hasMany(self::class, 'id_hoja_ruta_padre', 'id');
    }
}
