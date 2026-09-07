<?php

declare(strict_types=1);

namespace App\Models\Correspondencia;

use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventanilla extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia.ventanillas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'direccion',
        'tipo_atencion',
        'id_regional',
        'id_unidad_organizacional',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class, 'id_regional', 'id');
    }

    public function unidadOrganizacional(): BelongsTo
    {
        return $this->belongsTo(UnidadOrganizacional::class, 'id_unidad_organizacional', 'id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuarioVentanilla::class, 'id_ventanilla', 'id');
    }
}
