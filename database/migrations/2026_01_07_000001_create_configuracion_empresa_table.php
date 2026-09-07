<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion.configuracion_empresa', function (Blueprint $table) {
            $table->bigIncrements('id');

            // 1. Datos Institucionales de la Entidad
            $table->string('razon_social', 255)->default('Empresa Municipal de Agua Potable y Alcantarillado Sanitario EMAPAP Patacamaya');
            $table->string('nombre_comercial', 150)->default('EMAPAP - Patacamaya');
            $table->string('nit', 30)->default('123456789');
            $table->string('telefono', 50)->nullable()->default('+591 2 2818000');
            $table->string('correo', 100)->nullable()->default('contacto@emapap-patacamaya.gob.bo');
            $table->string('direccion', 255)->default('Plaza Principal 15 de Agosto s/n, Acera Norte');
            $table->string('municipio', 100)->default('Patacamaya');
            $table->text('logo_path')->nullable();

            // 2. Parámetros Tributarios SIAT / Impuestos Nacionales
            $table->integer('codigo_ambiente')->default(2); // 1 = Producción, 2 = Pruebas / Piloto
            $table->integer('codigo_modalidad')->default(1); // 1 = Electrónica en Línea, 2 = Computarizada en Línea
            $table->string('codigo_sistema', 100)->default('EMAPA_SISTEMA');
            $table->text('token_delegado')->nullable();
            $table->text('certificado_p12_path')->nullable();
            $table->text('password_p12')->nullable();

            // 3. Auditoría estándar SIM EMAPAP
            $table->string('_estado', 20)->default('ACTIVO')->index();
            $table->string('_transaccion', 20)->default('CREAR');
            $table->unsignedBigInteger('_usuario_creacion')->default(1);
            $table->timestamp('_fecha_creacion')->useCurrent();
            $table->unsignedBigInteger('_usuario_modificacion')->nullable();
            $table->timestamp('_fecha_modificacion')->nullable();
            $table->timestamps();
        });

        // Insertar registro inicial por defecto para EMAPAP Patacamaya
        DB::table('facturacion.configuracion_empresa')->insert([
            'razon_social' => 'Empresa Municipal de Agua Potable y Alcantarillado Sanitario EMAPAP Patacamaya',
            'nombre_comercial' => 'EMAPAP - Patacamaya',
            'nit' => '123456789',
            'telefono' => '+591 2 2818000',
            'correo' => 'contacto@emapap-patacamaya.gob.bo',
            'direccion' => 'Plaza Principal 15 de Agosto s/n, Acera Norte',
            'municipio' => 'Patacamaya',
            'logo_path' => '/images/logos/logoEmapa2.png',
            'codigo_ambiente' => 2, // 2 = Pruebas / Piloto por defecto
            'codigo_modalidad' => 1, // 1 = Electrónica en Línea con firma
            'codigo_sistema' => 'EMAPA_SISTEMA',
            'token_delegado' => null,
            'certificado_p12_path' => 'storage/app/siat/certs/certificado.p12',
            'password_p12' => null,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion.configuracion_empresa');
    }
};
