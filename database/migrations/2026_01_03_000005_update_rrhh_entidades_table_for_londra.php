<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rrhh.entidades', function (Blueprint $table) {
            if (!Schema::hasColumn('rrhh.entidades', 'tipo_instancia')) {
                $table->string('tipo_instancia', 30)->default('NACIONAL')->after('sigla'); // NACIONAL, DEPARTAMENTAL, MUNICIPAL, PRIVADA
            }
            if (!Schema::hasColumn('rrhh.entidades', 'id_entidad_gob_bo')) {
                $table->string('id_entidad_gob_bo', 100)->nullable()->after('tipo_instancia');
            }
            if (!Schema::hasColumn('rrhh.entidades', 'direccion')) {
                $table->string('direccion', 255)->nullable()->after('id_entidad_gob_bo');
            }
            if (!Schema::hasColumn('rrhh.entidades', 'contactos')) {
                $table->jsonb('contactos')->nullable()->after('direccion'); // { telefono, correo, web, representante }
            }
            if (!Schema::hasColumn('rrhh.entidades', 'logo')) {
                $table->string('logo', 255)->nullable()->after('contactos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rrhh.entidades', function (Blueprint $table) {
            $table->dropColumn(['tipo_instancia', 'id_entidad_gob_bo', 'direccion', 'contactos', 'logo']);
        });
    }
};
