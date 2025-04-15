<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\cliente;
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

        cliente::insert([
            [
            'NumeroExp' => 'EA25001',
            'nombre' => 'Erick Eduardo',
            'apellido' => 'Alonzo Dominguez',
            'fecha_nacimiento' => '1995-05-15',
            'telefono' => '1234567890',
            'correo' => 'ad18017@ues.edu.sv',
            'direccion' => 'Calle Falsa 123',
            'estado' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
                'NumeroExp' => 'RC25001',
                'nombre' => 'Renan Gilberto',
                'apellido' => 'Carranza Estupinian',
                'fecha_nacimiento' => '2000-06-15',
                'telefono' => '0987654321',
                'correo' => 'ce18008@ues.edu.sv',
                'direccion' => 'Avenida Principal 456',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'NumeroExp' => 'MD25001',
                'nombre' => 'Manuel Enrique',
                'apellido' => 'Dominguez Lopez',
                'fecha_nacimiento' => '2010-06-15',
                'telefono' => '1234567877',
                'correo' => 'ad14017@ues.edu.sv',
                'direccion' => 'Calle Secundaria 789',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
