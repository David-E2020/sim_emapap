<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    use HasFactory;

    protected $table = 'facturacion.configuracion_empresa';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'nit',
        'telefono',
        'correo',
        'direccion',
        'municipio',
        'logo_path',
        'codigo_ambiente',
        'codigo_modalidad',
        'codigo_sistema',
        'token_delegado',
        'certificado_p12_path',
        'password_p12',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'codigo_ambiente' => 'integer',
        'codigo_modalidad' => 'integer',
    ];

    /**
     * Atributos ocultos en serializaciones de respuestas JSON (protección de secretos criptográficos).
     */
    protected $hidden = [
        'password_p12',
        'token_delegado',
    ];

    /**
     * Obtiene la configuración corporativa y SIAT activa del sistema.
     * Si no existe, genera la configuración por defecto de EMAPAP Patacamaya con fallback.
     */
    public static function getActiva(): self
    {
        $config = self::where('_estado', 'ACTIVO')->orderBy('id', 'asc')->first();

        if ($config) {
            return $config;
        }

        return self::create([
            'razon_social' => 'Empresa Municipal de Agua Potable y Alcantarillado Sanitario EMAPAP Patacamaya',
            'nombre_comercial' => 'EMAPAP - Patacamaya',
            'nit' => (string) config('siat.nit_emisor', '123456789'),
            'telefono' => '+591 2 2818000',
            'correo' => 'contacto@emapap-patacamaya.gob.bo',
            'direccion' => 'Plaza Principal 15 de Agosto s/n, Acera Norte',
            'municipio' => 'Patacamaya',
            'logo_path' => '/images/logos/logoEmapa2.png',
            'codigo_ambiente' => (int) config('siat.ambiente', 2),
            'codigo_modalidad' => (int) config('siat.modalidad', 1),
            'codigo_sistema' => (string) config('siat.codigo_sistema', 'EMAPA_SISTEMA'),
            'token_delegado' => (string) config('siat.token_delegado', ''),
            'certificado_p12_path' => config('siat.cert_path', 'storage/app/siat/certs/certificado.p12'),
            'password_p12' => config('siat.cert_pass', ''),
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
        ]);
    }
}
