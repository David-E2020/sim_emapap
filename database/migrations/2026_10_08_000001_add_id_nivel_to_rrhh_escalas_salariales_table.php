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
        if (!Schema::hasColumn('rrhh.escalas_salariales', 'id_nivel')) {
            Schema::table('rrhh.escalas_salariales', function (Blueprint $table) {
                $table->foreignId('id_nivel')->nullable()->after('salario')->constrained('rrhh.niveles')->nullOnDelete();
                $table->string('codigo', 50)->nullable()->after('id_nivel');
            });
        }

        // Obtener IDs de niveles estándar
        $nivelEjecutivo = DB::table('rrhh.niveles')->where('nombre', 'like', '%DIRECTIVO%')->orWhere('nivel', 1)->value('id');
        $nivelJefatura = DB::table('rrhh.niveles')->where('nombre', 'like', '%JEFATURA%')->orWhere('nivel', 2)->value('id');
        $nivelAdmin = DB::table('rrhh.niveles')->where('nombre', 'like', '%ADMINISTRATIVO%')->orWhere('nivel', 3)->value('id');
        $nivelOperativo = DB::table('rrhh.niveles')->where('nombre', 'like', '%OPERATIVO%')->orWhere('nivel', 4)->value('id');

        // Actualizar escalas existentes con denominaciones institucionales reales
        $escalasExistentes = DB::table('rrhh.escalas_salariales')->get();
        foreach ($escalasExistentes as $e) {
            $salario = (float) $e->salario;
            $nuevoNombre = $e->nombre;
            $idNivel = $e->id_nivel;
            $codigo = null;

            if ($salario >= 6500) {
                $nuevoNombre = 'Nivel 1: Gerencia General';
                $idNivel = $nivelEjecutivo;
                $codigo = 'NIV-01';
            } elseif ($salario >= 4000) {
                $nuevoNombre = 'Nivel 2: Jefatura de Unidad / Profesional';
                $idNivel = $nivelJefatura;
                $codigo = 'NIV-02';
            } elseif ($salario >= 3600) {
                $nuevoNombre = 'Nivel 3: Administrativo / Técnico de Apoyo';
                $idNivel = $nivelAdmin;
                $codigo = 'NIV-03';
            } elseif ($salario >= 3400) {
                $nuevoNombre = 'Nivel 4: Técnico I / Operativo Especializado';
                $idNivel = $nivelOperativo;
                $codigo = 'NIV-04';
            } elseif ($salario >= 3000) {
                $nuevoNombre = 'Nivel 5: Técnico II / Auxiliar Operativo';
                $idNivel = $nivelOperativo;
                $codigo = 'NIV-05';
            } elseif ($salario <= 1000) {
                $nuevoNombre = 'Dietas Directorio: Miembro de Directorio';
                $idNivel = $nivelEjecutivo;
                $codigo = 'DIR-01';
            }

            DB::table('rrhh.escalas_salariales')->where('id', $e->id)->update([
                'nombre' => $nuevoNombre,
                'id_nivel' => $idNivel,
                'codigo' => $codigo,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rrhh.escalas_salariales', 'id_nivel')) {
            Schema::table('rrhh.escalas_salariales', function (Blueprint $table) {
                $table->dropForeign(['id_nivel']);
                $table->dropColumn(['id_nivel', 'codigo']);
            });
        }
    }
};
