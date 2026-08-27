<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use Illuminate\Database\Seeder;

class RrhhMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartamentoSeeder::class,
            FeriadoSeeder::class,
            PermisoJustificacionSeeder::class,
        ]);
    }
}
