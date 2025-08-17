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
            ['nombre' => 'Baciloscopia-BAAR', 'precio' => 10, 'recipiente' => 'orina'],
            ['nombre' => 'Coloración de Gram', 'precio' => 10,'recipiente' => 'orina'],
            ['nombre' => 'Coprocultivo', 'precio' => 12, 'recipiente' => 'heces'],
            ['nombre' => 'Cultivo de hongos', 'precio' => 20, 'recipiente' => 'hisopado'],
            ['nombre' => 'Cultivo de secreciones', 'precio' => 15, 'recipiente' => 'heces'],
            ['nombre' => 'Directo KOH', 'precio' => 15, 'recipiente' => 'hisopado'],
            ['nombre' => 'Urocultivo', 'precio' => 10, 'recipiente' => 'orina'],
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
            ['nombre' => 'Fibrinógeno', 'precio' => 15, 'recipiente' => 'celeste'],
            ['nombre' => 'Retracción de coagulo', 'precio' => 20, 'recipiente' => 'celeste'],
            ['nombre' => 'Tempo de coagulación', 'precio' => 6, 'recipiente' => 'celeste'],
            ['nombre' => 'Tempo de sangramiento', 'precio' => 6, 'recipiente' => 'celeste'],
            ['nombre' => 'Tiempo de tromb. parcial Act.', 'precio' => 12, 'recipiente' => 'celeste'],
            ['nombre' => 'Tiempo de trombina', 'precio' => 12, 'recipiente' => 'celeste'],
            ['nombre' => 'Tiempo y valor de protrombina', 'precio' => 10, 'recipiente' => 'celeste'],
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
            ['nombre' => 'Azul de metileno', 'precio' => 10, 'recipiente' => 'heces'],
            ['nombre' => 'General de heces', 'precio' => 2, 'recipiente' => 'heces'],
            ['nombre' => 'Helicobacter Pylori-Ag', 'precio' => 15, 'recipiente' => 'heces'],
            ['nombre' => 'Sangre oculta', 'precio' => 10, 'recipiente' => 'heces'],
            ['nombre' => 'Sustancia Reductora', 'precio' => 20, 'recipiente' => 'heces'],
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
            ['nombre' => 'Calcio', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Cloro', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Fósforo', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Magnesio', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Potasio', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Sodio', 'precio' => 8, 'recipiente' => 'rojo'],
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
            ['nombre' => 'B-hcG-Cuant', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'Cortisol', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'FSH', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'Hormona de crecimiento', 'precio' => 50, 'recipiente' => 'rojo'],
            ['nombre' => 'Hormona paratiroidea PHT', 'precio' => 50, 'recipiente' => 'rojo'],
            ['nombre' => 'Insulina', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'Insulina post-prandial', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'LH', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Progesterona', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'Prolactina', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'T3 libre', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'T3 total', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'T4 libre', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'T4 total', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'Testosterona', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'TSH 3ra Generacion', 'precio' => 15, 'recipiente' => 'rojo'],
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
            ['nombre' => 'Células L.E.', 'precio' => 25, 'recipiente' => 'morado'],
            ['nombre' => 'Concentrado straut (T.cruzi)', 'precio' => 15, 'recipiente' => 'morado'],
            ['nombre' => 'Eosinófilos sangre nasales', 'precio' => 10, 'recipiente' => 'morado'],
            ['nombre' => 'Eritrosedimentación', 'precio' => 6, 'recipiente' => 'morado'],
            ['nombre' => 'Fotis de sangre periférica', 'precio' => 10, 'recipiente' => 'morado'],
            ['nombre' => 'Hb y Ht', 'precio' => 5, 'recipiente' => 'morado'],
            ['nombre' => 'Hemograma', 'precio' => 5, 'recipiente' => 'morado'],
            ['nombre' => 'Leucograma', 'precio' => 6, 'recipiente' => 'morado'],
            ['nombre' => 'Plasmodium (gota gruesa)', 'precio' => 15, 'recipiente' => 'morado'],
            ['nombre' => 'Plaquetas', 'precio' => 5, 'recipiente' => 'morado'],
            ['nombre' => 'Reticulocitos', 'precio' => 10, 'recipiente' => 'morado'],
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
            ['nombre' => 'Ac. Anti-tiroideoglobulina', 'precio' => 40, 'recipiente' => 'rojo'],
            ['nombre' => 'Ac. Anti-tiroideoperoxidada', 'precio' => 50, 'recipiente' => 'rojo'],
            ['nombre' => 'Antigeno Covid-19', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Antiestreptolisina O (ASO)', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'Antígenos Febriles', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'AntimioticondrialesIgG', 'precio' => 60, 'recipiente' => 'rojo'],
            ['nombre' => 'Anti-cardiolipinasLgM', 'precio' => 45, 'recipiente' => 'rojo'],
            ['nombre' => 'Antinucleares Ac (ANA)', 'precio' => 40, 'recipiente' => 'rojo'],
            ['nombre' => 'Dengue IgG/IgM+Ag(DUO)', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'Factor reumatoideo (latex RA)', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'FTA - ABS (treponema)', 'precio' => 130, 'recipiente' => 'rojo'],
            ['nombre' => 'Helicobacter Pylori Ac. IgG', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Hepatitis A Ac. IgM', 'precio' => 40, 'recipiente' => 'rojo'],
            ['nombre' => 'Hepatitis B Ag. de superficie', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'Hepatitis C Ac', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'IgE total', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'InmunoglobulinasIgA (microsomal)', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'InmunoglobulinasIgG (microsomal)', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'InmunoglobulinasIgM (microsomal)', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'Monotest', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'Prueba de embarazo sangre', 'precio' => 7, 'recipiente' => 'rojo'],
            ['nombre' => 'Proteína C reactiva', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'RPR (prueba para sífilis)', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'Tipeo sanguíneo y factor Rh', 'precio' => 5, 'recipiente' => 'rojo'],
            ['nombre' => 'Toxoplasma IgM', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'VIH Ac. (3a generación)', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'VIH prueba rapida', 'precio' => 10, 'recipiente' => 'rojo'],
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
            ['nombre' => 'ALFA FETO proteina', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'CA 125', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'CA 15-3', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'CA 19-9', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'CEA ag. Carcioembrionario', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'PSA libre', 'precio' => 35, 'recipiente' => 'rojo'],
            ['nombre' => 'PSA total', 'precio' => 25, 'recipiente' => 'rojo'],
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
            ['nombre' => 'Acido Úrico', 'precio' => 4, 'recipiente' => 'rojo'],
            ['nombre' => 'Albumina', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Amilasa', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'Bilirrubina Total y Directa', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'Colesterol Alta Densidad - HDL', 'precio' => 6, 'recipiente' => 'rojo'],
            ['nombre' => 'Colesterol Baja Densidad - LDL', 'precio' => 6, 'recipiente' => 'rojo'],
            ['nombre' => 'Colesterol total', 'precio' => 4, 'recipiente' => 'rojo'],
            ['nombre' => 'Creatin Fosfokinasa (CPK)', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Creatin Fosfokinasa (PKMB)', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Creatinina', 'precio' => 4, 'recipiente' => 'rojo'],
            ['nombre' => 'Deshidrogenasa', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Ferritina', 'precio' => 30, 'recipiente' => 'rojo'],
            ['nombre' => 'Fosfatasa Acida', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'Fosfatasa Alcalina', 'precio' => 8, 'recipiente' => 'rojo'],
            ['nombre' => 'Fracción MB', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Gamma Glutamil (GCT)', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Glucosa', 'precio' => 3, 'recipiente' => 'rojo'],
            ['nombre' => 'Glucosa Post Prandial', 'precio' => 3, 'recipiente' => 'rojo'],
            ['nombre' => 'Glucosa Tolerancia 2 Horas', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Glucosa Tolerancia 3 Horas', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'Glucosa Tolerancia 5 Horas', 'precio' => 40, 'recipiente' => 'rojo'],
            ['nombre' => 'Hemoglobina Glicosilada AIC', 'precio' => 15, 'recipiente' => 'morado'],
            ['nombre' => 'Hierro Capacidad de Fijación', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Hierro Sérico', 'precio' => 10, 'recipiente' => 'rojo'],
            ['nombre' => 'Láctida-LDH', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Lipasa', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Nitrógeno Ureico', 'precio' => 4, 'recipiente' => 'rojo'],
            ['nombre' => 'Proteina totales y Dif', 'precio' => 12, 'recipiente' => 'rojo'],
            ['nombre' => 'Proteína C Reactiva Cardiaca', 'precio' => 25, 'recipiente' => 'rojo'],
            ['nombre' => 'Test O\' Sulivan', 'precio' => 20, 'recipiente' => 'rojo'],
            ['nombre' => 'Transamidas Oxalacética', 'precio' => 6, 'recipiente' => 'rojo'],
            ['nombre' => 'Triglicéridos', 'precio' => 4, 'recipiente' => 'rojo'],
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
            ['nombre' => 'Acido úrico orina 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Calcio orina de 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Cloro orina de 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Depuración de creatinina 24h', 'precio' => 15, 'recipiente' => 'rojo'],
            ['nombre' => 'Fósforo orina 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Nitrógeno ureico orina de 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Potasio orina de 24h', 'precio' => 15, 'recipiente' => 'orina'],
            ['nombre' => 'Proteínas en orina de 24h', 'precio' => 15, 'recipiente' => 'orina'],
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
            ['nombre' => 'Examen general orina', 'precio' => 2, 'recipiente' => 'orina'],
            ['nombre' => 'Prueba de embarazo en orina', 'precio' => 5, 'recipiente' => 'orina'],
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
