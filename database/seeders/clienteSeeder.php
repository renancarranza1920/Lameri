<?php

namespace Database\Seeders;

use App\Models\cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class clienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        cliente::insert([
            [
            'NumeroExp' => 'AD0425001',
            'nombre' => 'Erick Eduardo',
            'apellido' => 'Alonzo Dominguez',
            'telefono' => '1234567890',
            'correo' => 'ad18017@ues.edu.sv',
            'direccion' => 'Calle Falsa 123',
            'estado' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
                'NumeroExp' => 'CE0425001',
                'nombre' => 'Renan Gilberto',
                'apellido' => 'Carranza Estupinian',
                'telefono' => '0987654321',
                'correo' => 'ce18008@ues.edu.sv',
                'direccion' => 'Avenida Principal 456',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'NumeroExp' => 'DL0425001',
                'nombre' => 'Manuel Enrique',
                'apellido' => 'Dominguez Lopez',
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
