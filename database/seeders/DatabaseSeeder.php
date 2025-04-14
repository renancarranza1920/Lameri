<?php

namespace Database\Seeders;

use App\Models\TipoExamen;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
        ]);

        // Lista ordenada alfabéticamente y con tildes
        $tipos = [
            'Bacteriología',
            'Coagulación',
            'Coprología',
            'Electrolitos',
            'Endocrinología',
            'Hematología',
            'Inmunología',
            'Marcadores Tumorales',
            'Perfil de Rutina',
            'Perfil Hepático',
            'Perfil Prenatal',
            'Perfil Renal',
            'Química Sanguínea',
            'Química Urinaria',
            'Uroanálisis',
        ];

        foreach ($tipos as $nombre) {
            TipoExamen::create([
                'nombre' => $nombre,
                'estado' => true,
            ]);
        }
    }
}
