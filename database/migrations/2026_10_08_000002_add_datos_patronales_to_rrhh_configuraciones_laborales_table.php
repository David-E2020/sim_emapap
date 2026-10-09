<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rrhh.configuraciones_laborales', function (Blueprint $table) {
            if (!Schema::hasColumn('rrhh.configuraciones_laborales', 'nro_patronal_min_trabajo')) {
                $table->string('nro_patronal_min_trabajo', 50)->nullable()->default('1002393029-1')->after('descripcion');
            }
            if (!Schema::hasColumn('rrhh.configuraciones_laborales', 'nro_patronal_cns')) {
                $table->string('nro_patronal_cns', 50)->nullable()->default('01-521-00002')->after('nro_patronal_min_trabajo');
            }
            if (!Schema::hasColumn('rrhh.configuraciones_laborales', 'nit_institucional')) {
                $table->string('nit_institucional', 50)->nullable()->default('1002393029')->after('nro_patronal_cns');
            }
            if (!Schema::hasColumn('rrhh.configuraciones_laborales', 'ubicacion_geografica')) {
                $table->string('ubicacion_geografica', 100)->nullable()->default('PATACAMAYA-LA PAZ-BOLIVIA')->after('nit_institucional');
            }
            if (!Schema::hasColumn('rrhh.configuraciones_laborales', 'direccion_institucional')) {
                $table->string('direccion_institucional', 255)->nullable()->default('PLAZA BOLIVAR - ZONA ESTACION')->after('ubicacion_geografica');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rrhh.configuraciones_laborales', function (Blueprint $table) {
            $table->dropColumn([
                'nro_patronal_min_trabajo',
                'nro_patronal_cns',
                'nit_institucional',
                'ubicacion_geografica',
                'direccion_institucional',
            ]);
        });
    }
};
