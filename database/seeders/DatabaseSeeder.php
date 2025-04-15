<?php

namespace Database\Seeders;

use App\Models\TipoExamen;
use App\Models\Examen;
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

        // Obtener el tipo de examen "Bacteriología"
        $tipo = TipoExamen::where('nombre', 'Bacteriología')->first();

        // Insertar exámenes de Bacteriología (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Baciloscopia-BAAR', 'precio' => 10],
            ['nombre' => 'Coloración de Gram', 'precio' => 10],
            ['nombre' => 'Coprocultivo', 'precio' => 12],
            ['nombre' => 'Cultivo de hongos', 'precio' => 20],
            ['nombre' => 'Cultivo de secreciones', 'precio' => 15],
            ['nombre' => 'Directo KOH', 'precio' => 15],
            ['nombre' => 'Urocultivo', 'precio' => 10],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Coagulación"
        $tipo = TipoExamen::where('nombre', 'Coagulación')->first();

        // Insertar exámenes de Coagulación (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Fibrinógeno', 'precio' => 15],
            ['nombre' => 'Retracción de coagulo', 'precio' => 20],
            ['nombre' => 'Tempo de coagulación', 'precio' => 6],
            ['nombre' => 'Tempo de sangramiento', 'precio' => 6],
            ['nombre' => 'Tiempo de tromb. parcial Act.', 'precio' => 12],
            ['nombre' => 'Tiempo de trombina', 'precio' => 12],
            ['nombre' => 'Tiempo y valor de protrombina', 'precio' => 10],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Coprología"
        $tipo = TipoExamen::where('nombre', 'Coprología')->first();

        // Insertar exámenes de Coprología (ordenados alfabéticamente)
        $examenes = [
            ['nombre' => 'Azul de metileno', 'precio' => 10],
            ['nombre' => 'Genero de heces', 'precio' => 2],
            ['nombre' => 'Helicobacter Pylori-Ag', 'precio' => 15],
            ['nombre' => 'Sangre oculta', 'precio' => 10],
            ['nombre' => 'Sustancia Reductora', 'precio' => 20],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Electrolitos"
        $tipo = TipoExamen::where('nombre', 'Electrolitos')->first();

        // Insertar exámenes de Electrolitos
        $examenes = [
            ['nombre' => 'Calcio', 'precio' => 8],
            ['nombre' => 'Cloro', 'precio' => 8],
            ['nombre' => 'Fósforo', 'precio' => 8],
            ['nombre' => 'Magnesio', 'precio' => 8],
            ['nombre' => 'Potasio', 'precio' => 8],
            ['nombre' => 'Sodio', 'precio' => 8],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Endocrinología"
        $tipo = TipoExamen::where('nombre', 'Endocrinología')->first();

        // Insertar exámenes de Endocrinología
        $examenes = [
            ['nombre' => 'B-hcG-Cuant', 'precio' => 35],
            ['nombre' => 'Cortisol', 'precio' => 30],
            ['nombre' => 'FSH', 'precio' => 35],
            ['nombre' => 'Hormona de crecimiento', 'precio' => 50],
            ['nombre' => 'Hormona paratiroidea PHT', 'precio' => 50],
            ['nombre' => 'Insulina', 'precio' => 35],
            ['nombre' => 'Insulina post-prandial', 'precio' => 35],
            ['nombre' => 'LH', 'precio' => 15],
            ['nombre' => 'Progesterona', 'precio' => 30],
            ['nombre' => 'Prolactina', 'precio' => 35],
            ['nombre' => 'T3 libre', 'precio' => 12],
            ['nombre' => 'T3 total', 'precio' => 10],
            ['nombre' => 'T4 libre', 'precio' => 12],
            ['nombre' => 'T4 total', 'precio' => 10],
            ['nombre' => 'Testosterona', 'precio' => 35],
            ['nombre' => 'TSH 3ra Generacion', 'precio' => 15],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        // Obtener el tipo de examen "Hematología"
        $tipo = TipoExamen::where('nombre', 'Hematología')->first();

        // Insertar exámenes de Hematología
        $examenes = [
            ['nombre' => 'Células L.E.', 'precio' => 25],
            ['nombre' => 'Concentrado straut (T.cruzi)', 'precio' => 15],
            ['nombre' => 'Eosinófilos sangre nasales', 'precio' => 10],
            ['nombre' => 'Eritrosedimentación', 'precio' => 6],
            ['nombre' => 'Fotis de sangre periférica', 'precio' => 10],
            ['nombre' => 'Hb y Ht', 'precio' => 5],
            ['nombre' => 'Hemograma', 'precio' => 5],
            ['nombre' => 'Leucograma', 'precio' => 6],
            ['nombre' => 'Plasmodium (gota gruesa)', 'precio' => 15],
            ['nombre' => 'Plaquetas', 'precio' => 5],
            ['nombre' => 'Reticulocitos', 'precio' => 10],
        ];

        foreach ($examenes as $examen) {
            Examen::firstOrCreate([
                'nombre' => $examen['nombre'],
                'tipo_examen_id' => $tipo->id,
            ], [
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        $tipoInmunologia = TipoExamen::where('nombre', 'Inmunología')->first();

        $examenesInmunologia = [
            ['nombre' => 'Ac. Anti-tiroideoglobulina', 'precio' => 40],
            ['nombre' => 'Ac. Anti-tiroideoperoxidada', 'precio' => 50],
            ['nombre' => 'Antigeno Covid-19', 'precio' => 15],
            ['nombre' => 'Antiestreptolisina O (ASO)', 'precio' => 10],
            ['nombre' => 'Antígenos Febriles', 'precio' => 10],
            ['nombre' => 'AntimioticondrialesIgG', 'precio' => 60],
            ['nombre' => 'Anti-cardiolipinasLgM', 'precio' => 45],
            ['nombre' => 'Antinucleares Ac (ANA)', 'precio' => 40],
            ['nombre' => 'Dengue IgG/IgM+Ag(DUO)', 'precio' => 25],
            ['nombre' => 'Factor reumatoideo (latex RA)', 'precio' => 10],
            ['nombre' => 'FTA - ABS (treponema)', 'precio' => 130],
            ['nombre' => 'Helicobacter Pylori Ac. IgG', 'precio' => 15],
            ['nombre' => 'Hepatitis A Ac. IgM', 'precio' => 40],
            ['nombre' => 'Hepatitis B Ag. de superficie', 'precio' => 25],
            ['nombre' => 'Hepatitis C Ac', 'precio' => 25],
            ['nombre' => 'IgE total', 'precio' => 35],
            ['nombre' => 'InmunoglobulinasIgA (microsomal)', 'precio' => 30],
            ['nombre' => 'InmunoglobulinasIgG (microsomal)', 'precio' => 30],
            ['nombre' => 'InmunoglobulinasIgM (microsomal)', 'precio' => 30],
            ['nombre' => 'Monotest', 'precio' => 25],
            ['nombre' => 'Prueba de embarazo sangre', 'precio' => 7],
            ['nombre' => 'Proteína C reactiva', 'precio' => 10],
            ['nombre' => 'RPR (prueba para sífilis)', 'precio' => 10],
            ['nombre' => 'Tipeo sanguíneo y factor Rh', 'precio' => 5],
            ['nombre' => 'Toxoplasma IgM', 'precio' => 25],
            ['nombre' => 'VIH Ac. (3a generación)', 'precio' => 30],
            ['nombre' => 'VIH prueba rapida', 'precio' => 10],
        ];

        foreach ($examenesInmunologia as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoInmunologia->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        $tipoTumorales = TipoExamen::where('nombre', 'Marcadores Tumorales')->first();

        $examenesTumorales = [
            ['nombre' => 'ALFA FETO proteina', 'precio' => 35],
            ['nombre' => 'CA 125', 'precio' => 35],
            ['nombre' => 'CA 15-3', 'precio' => 35],
            ['nombre' => 'CA 19-9', 'precio' => 35],
            ['nombre' => 'CEA ag. Carcioembrionario', 'precio' => 35],
            ['nombre' => 'PSA libre', 'precio' => 35],
            ['nombre' => 'PSA total', 'precio' => 25],
        ];

        foreach ($examenesTumorales as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoTumorales->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        $tipoQuimica = TipoExamen::where('nombre', 'Química Sanguínea')->first();

        $examenesQuimica = [
            ['nombre' => 'Acido Úrico', 'precio' => 4],
            ['nombre' => 'Albumina', 'precio' => 8],
            ['nombre' => 'Amilasa', 'precio' => 12],
            ['nombre' => 'Bilirrubina Total y Directa', 'precio' => 12],
            ['nombre' => 'Colesterol Alta Densidad - HDL', 'precio' => 6],
            ['nombre' => 'Colesterol Baja Densidad - LDL', 'precio' => 6],
            ['nombre' => 'Colesterol total', 'precio' => 4],
            ['nombre' => 'Creatin Fosfokinasa (CPK)', 'precio' => 15],
            ['nombre' => 'Creatin Fosfokinasa (PKMB)', 'precio' => 20],
            ['nombre' => 'Creatinina', 'precio' => 4],
            ['nombre' => 'Deshidrogenasa', 'precio' => 15],
            ['nombre' => 'Ferritina', 'precio' => 30],
            ['nombre' => 'Fosfatasa Acida', 'precio' => 12],
            ['nombre' => 'Fosfatasa Alcalina', 'precio' => 8],
            ['nombre' => 'Fracción MB', 'precio' => 20],
            ['nombre' => 'Glucosa', 'precio' => 3],
            ['nombre' => 'Glucosa Post Prandial', 'precio' => 3],
            ['nombre' => 'Glucosa Tolerancia 2 Horas', 'precio' => 20],
            ['nombre' => 'Glucosa Tolerancia 3 Horas', 'precio' => 25],
            ['nombre' => 'Glucosa Tolerancia 5 Horas', 'precio' => 40],
            ['nombre' => 'Hemoglobina Glicosilada AIC', 'precio' => 15],
            ['nombre' => 'Hierro Capacidad de Fijación', 'precio' => 20],
            ['nombre' => 'Hierro Sérico', 'precio' => 10],
            ['nombre' => 'Láctida-LDH', 'precio' => 15],
            ['nombre' => 'Lipasa', 'precio' => 20],
            ['nombre' => 'Nitrógeno Ureico', 'precio' => 4],
            ['nombre' => 'Proteina totales y Dif', 'precio' => 12],
            ['nombre' => 'Proteína C Reactiva Cardiaca', 'precio' => 25],
            ['nombre' => 'Test O\' Sulivan', 'precio' => 20],
            ['nombre' => 'Transamidas Oxalacética', 'precio' => 6],
            ['nombre' => 'Triglicéridos', 'precio' => 4],
        ];

        usort($examenesQuimica, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        foreach ($examenesQuimica as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoQuimica->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        $tipoQuimicaUrinaria = TipoExamen::where('nombre', 'Química Urinaria')->first();

        $examenesQuimicaUrinaria = [
            ['nombre' => 'Acido úrico orina 24h', 'precio' => 15],
            ['nombre' => 'Calcio orina de 24h', 'precio' => 15],
            ['nombre' => 'Cloro orina de 24h', 'precio' => 15],
            ['nombre' => 'Depuración de creatinina 24h', 'precio' => 15],
            ['nombre' => 'Fósforo orina 24h', 'precio' => 15],
            ['nombre' => 'Nitrógeno ureico orina de 24h', 'precio' => 15],
            ['nombre' => 'Potasio orina de 24h', 'precio' => 15],
            ['nombre' => 'Proteínas en orina de 24h', 'precio' => 15],
        ];

        usort($examenesQuimicaUrinaria, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        foreach ($examenesQuimicaUrinaria as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoQuimicaUrinaria->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }

        $tipoUroanalisis = TipoExamen::where('nombre', 'Uroanálisis')->first();

        $examenesUroanalisis = [
            ['nombre' => 'Examen general orina', 'precio' => 2],
            ['nombre' => 'Prueba de embarazo en orina', 'precio' => 5],
        ];

        foreach ($examenesUroanalisis as $examen) {
            Examen::create([
                'tipo_examen_id' => $tipoUroanalisis->id,
                'nombre' => $examen['nombre'],
                'precio' => $examen['precio'],
                'estado' => true,
            ]);
        }
    }
}
