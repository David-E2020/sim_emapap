<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['id' => 1, 'nombre' => 'Chuquisaca'],
            ['id' => 2, 'nombre' => 'La Paz'],
            ['id' => 3, 'nombre' => 'Cochabamba'],
            ['id' => 4, 'nombre' => 'Oruro'],
            ['id' => 5, 'nombre' => 'Potosí'],
            ['id' => 6, 'nombre' => 'Tarija'],
            ['id' => 7, 'nombre' => 'Santa Cruz'],
            ['id' => 8, 'nombre' => 'Beni'],
            ['id' => 9, 'nombre' => 'Pando'],
            ['id' => 10, 'nombre' => 'Internacional'],
        ];

        foreach ($departamentos as $d) {
            DB::table('rrhh.departamentos')->updateOrInsert(
                ['id' => $d['id']],
                [
                    'nombre' => $d['nombre'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        // Sincronizar secuencia
        DB::statement("SELECT setval(pg_get_serial_sequence('rrhh.departamentos', 'id'), coalesce(max(id), 1)) FROM rrhh.departamentos");
    }
}
