<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Persona;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Documento extends Model
{
    public $timestamps = false;
    protected $table = 'correspondencia.documentos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cite',
        'codigo_verificacion',
        'tipo_documento',
        'asunto',
        'contenido_html',
        'resumen',
        'config',
        'estado',
        'id_plantilla',
        'id_unidad_generadora',
        'id_creador',
        'id_hoja_ruta',
        'tamanio_archivos_adjuntos',
        'tamanio_archivo_generado',
        'cantidad_usuarios_compartidos',
        'adjuntos_aprobados_firmados',
        'fecha_anulacion',
        'id_usuario_anulacion',
        'id_cargo_anulacion',
        'motivo_anulacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'resumen' => 'array',
        'config' => 'array',
        'adjuntos_aprobados_firmados' => 'boolean',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_creador', 'id');
    }

    public function unidadGeneradora(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_generadora', 'id');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaDocumento::class, 'id_plantilla', 'id');
    }

    public function hojaRuta(): BelongsTo
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta', 'id');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(ParticipanteDocumento::class, 'id_documento', 'id')->orderBy('orden_participacion');
    }

    public function firmasAprobaciones(): HasMany
    {
        return $this->hasMany(FirmaAprobacion::class, 'id_documento', 'id');
    }

    public function archivosAdjuntos(): HasMany
    {
        return $this->hasMany(ArchivoAdjunto::class, 'id_documento', 'id');
    }

    public function archivoGenerado(): HasOne
    {
        return $this->hasOne(ArchivoGenerado::class, 'id_documento', 'id');
    }

    public function revisionDoc(): HasOne
    {
        return $this->hasOne(RevisionDocumento::class, 'id_documento', 'id');
    }
}
