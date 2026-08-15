<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuario administrador base
        User::firstOrCreate(
            ['usr_usuario' => 'admin'],
            [
                'name' => 'Administrador General',
                'email' => 'admin@emapa.gob.bo',
                'password' => Hash::make('admin123456'),
                'usr_estado' => 'A',
                'usr_cargo_add' => 'Administrador del Sistema',
            ]
        );
    }
}
