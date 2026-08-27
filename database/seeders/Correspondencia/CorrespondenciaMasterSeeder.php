<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use Illuminate\Database\Seeder;

class CorrespondenciaMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CorrespondenciaParametricasSeeder::class,
            PlantillasDocumentosSeeder::class,
            CorrespondenciaMenuAndPermissionsSeeder::class,
            CorrespondenciaDemoDataSeeder::class,
        ]);
    }
}
