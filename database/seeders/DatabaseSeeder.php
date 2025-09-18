<?php

namespace Database\Seeders;

use App\Models\TipoExamen;
use App\Models\Examen;
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

        // Lista ordenada alfabéticamente y con tildes
        $tipos = [
            'Bactereología',
            'Coagulación',
            'Coprología',
            'Electrolitos',
            'Endocrinología',
            'Hematología',
            'Inmunología',
            'Marcadores Tumorales',
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

        // Obtener el tipo de examen "Bacteriología", FALTA CONFIRMACION----------------------------------------------------------
        $tipo = TipoExamen::where('nombre', 'Bactereología')->first();

        // Insertar exámenes de Bacteriología (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Baciloscopia-BAAR', 'precio' => 10, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Coloración de Gram', 'precio' => 10,'recipiente' => 'uroanalisis'],
            ['nombre' => 'Coprocultivo', 'precio' => 12, 'recipiente' => 'coprologia'],
            ['nombre' => 'Cultivo de hongos', 'precio' => 20, 'recipiente' => 'cultivo_secreciones'],
            ['nombre' => 'Cultivo de secreciones', 'precio' => 15, 'recipiente' => 'coprologia'],
            ['nombre' => 'Directo KOH', 'precio' => 15, 'recipiente' => 'cultivo_secreciones'],
            ['nombre' => 'Urocultivo', 'precio' => 10, 'recipiente' => 'uroanalisis'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Coagulación"
        $tipo = TipoExamen::where('nombre', 'Coagulación')->first();

        // Insertar exámenes de Coagulación (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Fibrinógeno', 'precio' => 15, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Retracción de coagulo', 'precio' => 20, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tempo de coagulación', 'precio' => 6, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tempo de sangramiento', 'precio' => 6, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tiempo de tromb. parcial Act.', 'precio' => 12, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tiempo de trombina', 'precio' => 12, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tiempo y valor de protrombina', 'precio' => 10, 'recipiente' => 'cuagulacion'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Coprología"
        $tipo = TipoExamen::where('nombre', 'Coprología')->first();

        // Insertar exámenes de Coprología (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Azul de metileno', 'precio' => 10, 'recipiente' => 'coprologia'],
            ['nombre' => 'General de coprologia', 'precio' => 2, 'recipiente' => 'coprologia'],
            ['nombre' => 'Helicobacter Pylori-Ag', 'precio' => 15, 'recipiente' => 'coprologia'],
            ['nombre' => 'Sangre oculta', 'precio' => 10, 'recipiente' => 'coprologia'],
            ['nombre' => 'Sustancia Reductora', 'precio' => 20, 'recipiente' => 'coprologia'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
            'nombre' => $examen['nombre'],
            'tipo_examen_id' => $tipo->id,
            'precio' => $examen['precio'],
            'recipiente' => $examen['recipiente'],
            'estado' => true
            ]);
        }

        // Obtener el tipo de examen "Electrolitos", 
        $tipo = TipoExamen::where('nombre', 'Electrolitos')->first();

        // Insertar exámenes de Electrolitos
        $examenes = [
            ['nombre' => 'Calcio', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Cloro', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fósforo', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Magnesio', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Potasio', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Sodio', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Endocrinología"
        $tipo = TipoExamen::where('nombre', 'Endocrinología')->first();

        // Insertar exámenes de Endocrinología
        $examenes = [
            ['nombre' => 'B-hcG-Cuant', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Cortisol', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'FSH', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hormona de crecimiento', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hormona paratiroidea PHT', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Insulina', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Insulina post-prandial', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'LH', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Progesterona', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Prolactina', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T3 libre', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T3 total', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T4 libre', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T4 total', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Testosterona', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'TSH 3ra Generacion', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Hematología"
        $tipo = TipoExamen::where('nombre', 'Hematología')->first();

        // Insertar exámenes de Hematología
        $examenes = [
            ['nombre' => 'Células L.E.', 'precio' => 25, 'recipiente' => 'hematologia'],
            ['nombre' => 'Concentrado straut (T.cruzi)', 'precio' => 15, 'recipiente' => 'hematologia'],
            ['nombre' => 'Eosinófilos sangre nasales', 'precio' => 10, 'recipiente' => 'hematologia'],
            ['nombre' => 'Eritrosedimentación', 'precio' => 6, 'recipiente' => 'hematologia'],
            ['nombre' => 'Fotis de sangre periférica', 'precio' => 10, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hb y Ht', 'precio' => 5, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hemograma', 'precio' => 5, 'recipiente' => 'hematologia'],
            ['nombre' => 'Leucograma', 'precio' => 6, 'recipiente' => 'hematologia'],
            ['nombre' => 'Plasmodium (gota gruesa)', 'precio' => 15, 'recipiente' => 'hematologia'],
            ['nombre' => 'Plaquetas', 'precio' => 5, 'recipiente' => 'hematologia'],
            ['nombre' => 'Reticulocitos', 'precio' => 10, 'recipiente' => 'hematologia'],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        $tipoInmunologia = TipoExamen::where('nombre', 'Inmunología')->first();

        $examenesInmunologia = [
            ['nombre' => 'Ac. Anti-tiroideoglobulina', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Ac. Anti-tiroideoperoxidada', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Antigeno Covid-19', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Antiestreptolisina O (ASO)', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Antígenos Febriles', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'AntimioticondrialesIgG', 'precio' => 60, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Anti-cardiolipinasLgM', 'precio' => 45, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Antinucleares Ac (ANA)', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Dengue IgG/IgM+Ag(DUO)', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Factor reumatoideo (latex RA)', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'FTA - ABS (treponema)', 'precio' => 130, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Helicobacter Pylori Ac. IgG', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis A Ac. IgM', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis B Ag. de superficie', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis C Ac', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'IgE total', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgA (microsomal)', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgG (microsomal)', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgM (microsomal)', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Monotest', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Prueba de embarazo sangre', 'precio' => 7, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Proteína C reactiva', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'RPR (prueba para sífilis)', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Tipeo sanguíneo y factor Rh', 'precio' => 5, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Toxoplasma IgM', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'VIH Ac. (3a generación)', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'VIH prueba rapida', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
        ];

        foreach ($examenesInmunologia as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoInmunologia->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        $tipoTumorales = TipoExamen::where('nombre', 'Marcadores Tumorales')->first();

        $examenesTumorales = [
            ['nombre' => 'ALFA FETO proteina', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'CA 125', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'CA 15-3', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'CA 19-9', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'CEA ag. Carcioembrionario', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'PSA libre', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'PSA total', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
        ];

        foreach ($examenesTumorales as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoTumorales->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        $tipoQuimica = TipoExamen::where('nombre', 'Química Sanguínea')->first();

        $examenesQuimica = [
            ['nombre' => 'Acido Úrico', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Albumina', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Amilasa', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Bilirrubina Total y Directa', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol Alta Densidad - HDL', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol Baja Densidad - LDL', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol total', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatin Fosfokinasa (CPK)', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatin Fosfokinasa (PKMB)', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatinina', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Deshidrogenasa', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Ferritina', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fosfatasa Acida', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fosfatasa Alcalina', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fracción MB', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Gamma Glutamil (GCT)', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa', 'precio' => 3, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Post Prandial', 'precio' => 3, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 2 Horas', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 3 Horas', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 5 Horas', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hemoglobina Glicosilada AIC', 'precio' => 15, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hierro Capacidad de Fijación', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hierro Sérico', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Láctida-LDH', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Lipasa', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Nitrógeno Ureico', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Proteina totales y Dif', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Proteína C Reactiva Cardiaca', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Test O\' Sulivan', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Transamidas Oxalacética', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Triglicéridos', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
        ];

        usort($examenesQuimica, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        foreach ($examenesQuimica as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoQuimica->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        $tipoQuimicaUrinaria = TipoExamen::where('nombre', 'Química Urinaria')->first();

        $examenesQuimicaUrinaria = [
            ['nombre' => 'Acido úrico uroanalisis 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Calcio uroanalisis de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Cloro uroanalisis de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Depuración de creatinina 24h', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fósforo uroanalisis 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Nitrógeno ureico uroanalisis de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Potasio uroanalisis de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Proteínas en uroanalisis de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
        ];

        usort($examenesQuimicaUrinaria, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        foreach ($examenesQuimicaUrinaria as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoQuimicaUrinaria->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }

        $tipoUroanalisis = TipoExamen::where('nombre', 'Uroanálisis')->first();

        $examenesUroanalisis = [
            ['nombre' => 'Examen general uroanalisis', 'precio' => 2, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Prueba de embarazo en uroanalisis', 'precio' => 5, 'recipiente' => 'uroanalisis'],
        ];

        foreach ($examenesUroanalisis as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoUroanalisis->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'recipiente' => $examen['recipiente'],
                'estado' => true,
            ]);
        }
    }
}
