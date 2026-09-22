<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Crear tabla de Paquetes / Pliegos Tarifarios
        if (!Schema::hasTable('comercial.paquetes_tarifarios')) {
            Schema::create('comercial.paquetes_tarifarios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 150);
                $table->string('resolucion_legal', 120)->nullable();
                $table->date('fecha_inicio_vigencia')->nullable();
                $table->date('fecha_fin_vigencia')->nullable();
                $table->boolean('es_vigente')->default(false);
                $table->text('descripcion')->nullable();

                // Auditoría estándar
                $table->string('_estado', 20)->default('ACTIVO')->index();
                $table->string('_transaccion', 20)->default('CREAR');
                $table->unsignedBigInteger('_usuario_creacion')->default(1);
                $table->timestamp('_fecha_creacion')->useCurrent();
                $table->unsignedBigInteger('_usuario_modificacion')->nullable();
                $table->timestamp('_fecha_modificacion')->nullable();
                $table->timestamps();
            });
        }

        // 2. Agregar columna id_paquete a comercial.categorias_tarifarias si no existe
        if (Schema::hasTable('comercial.categorias_tarifarias')) {
            if (!Schema::hasColumn('comercial.categorias_tarifarias', 'id_paquete')) {
                Schema::table('comercial.categorias_tarifarias', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_paquete')->nullable()->after('id');
                    $table->foreign('id_paquete')
                          ->references('id')
                          ->on('comercial.paquetes_tarifarios')
                          ->onDelete('set null');
                });
            }

            // Quitar restricción UNIQUE en 'codigo' si existe, para permitir versionado por paquete
            try {
                DB::statement('ALTER TABLE comercial.categorias_tarifarias DROP CONSTRAINT IF EXISTS categorias_tarifarias_codigo_unique');
            } catch (\Throwable $e) {}
        }

        // 3. Crear paquete por defecto e indexar categorías existentes
        $existePaquete = DB::table('comercial.paquetes_tarifarios')->where('codigo', 'PLIEGO_EMAPAP_VIGENTE')->first();
        if (!$existePaquete) {
            $paqueteId = DB::table('comercial.paquetes_tarifarios')->insertGetId([
                'codigo' => 'PLIEGO_EMAPAP_VIGENTE',
                'nombre' => 'Pliego Tarifario Oficial EMAPAP (Vigente)',
                'resolucion_legal' => 'Resolución Administrativa Regulatoria AAPS / EMAPAP',
                'fecha_inicio_vigencia' => '2024-01-01',
                'es_vigente' => true,
                'descripcion' => 'Estructura tarifaria con 6 categorías oficiales y 11 escalas variables de consumo en m³ según FoxPro',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'MIGRACION',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('comercial.categorias_tarifarias')
                ->whereNull('id_paquete')
                ->update(['id_paquete' => $paqueteId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('comercial.categorias_tarifarias', 'id_paquete')) {
            Schema::table('comercial.categorias_tarifarias', function (Blueprint $table) {
                $table->dropForeign(['id_paquete']);
                $table->dropColumn('id_paquete');
            });
        }

        Schema::dropIfExists('comercial.paquetes_tarifarios');
    }
};
