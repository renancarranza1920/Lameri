<?php

namespace Database\Seeders;

use App\Models\PlantillaReferencia;
use DB;
use Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\TipoExamen;
use App\Models\Examen;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Muestra;
use App\Models\Perfil;
use App\Models\DetallePerfil;
use Hash;
use Illuminate\Database\Seeder;
use Log;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Log::info('Iniciando el seeder...');

        // Crear usuario administrador
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'nickname' => 'admin',
            'password' => Hash::make('admin'),
        ]);

        Log::info('Usuario administrador creado:', ['email' => $admin->email]);

        // Crear rol "admin"
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Log::info('Rol admin creado o encontrado.');

        // Generar permisos automáticamente
        $this->generatePermissions();

        $this->createRolesAndAssignPermissions();

        // 🔑 Limpiar caché de permisos antes de asignar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Asignar todos los permisos al rol "admin"
        $adminRole->syncPermissions(Permission::all());

        // Asignar el rol "admin" al usuario
        $admin->assignRole($adminRole);

        Log::info('Rol y permisos asignados al usuario administrador.');



        cliente::insert([
            [
                'NumeroExp' => 'EA25001',
                'nombre' => 'Erick Eduardo',
                'apellido' => 'Alonzo Dominguez',
                'fecha_nacimiento' => '1995-05-15',
                'genero' => 'Masculino',
                'telefono' => '12345678',
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
                'genero' => 'Masculino',
                'fecha_nacimiento' => '2000-06-15',
                'telefono' => '09876543',
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
                'genero' => 'Masculino',
                'telefono' => '12345678',
                'correo' => 'ad14017@ues.edu.sv',
                'direccion' => 'Calle Secundaria 789',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'NumeroExp' => 'CC25001',
                'nombre' => 'Karla Melissa',
                'apellido' => 'Cortez Cardenas',
                'fecha_nacimiento' => '1950-06-15',
                'genero' => 'Femenino',
                'telefono' => '767685345',
                'correo' => 'cc14017@ues.edu.sv',
                'direccion' => 'Calle Tercearia 789',
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
            ['nombre' => 'Coloración de Gram', 'precio' => 10, 'recipiente' => 'uroanalisis'],
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
            ['nombre' => 'Tiempo de coagulación', 'precio' => 10, 'recipiente' => 'cuagulacion'],
            ['nombre' => 'Tiempo de sangramiento', 'precio' => 10, 'recipiente' => 'cuagulacion'],
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
            ['nombre' => 'General de heces', 'precio' => 2, 'recipiente' => 'coprologia'],
            ['nombre' => 'Helicobacter Pylori-Ag', 'precio' => 15, 'recipiente' => 'coprologia'],
            ['nombre' => 'Sangre oculta', 'precio' => 15, 'recipiente' => 'coprologia'],
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
            ['nombre' => 'Cortisol', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'FSH', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hormona de crecimiento', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hormona paratiroidea PHT', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Insulina', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Insulina post-prandial', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'LH', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Progesterona', 'precio' => 50, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Prolactina', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T3 libre', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T3 total', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T4 libre', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'T4 total', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
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
            ['nombre' => 'Frotis de sangre periférica', 'precio' => 10, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hb y Ht', 'precio' => 5, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hemograma', 'precio' => 5, 'recipiente' => 'hematologia'],
            ['nombre' => 'Leucograma', 'precio' => 5, 'recipiente' => 'hematologia'],
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
            ['nombre' => 'Anti-cardiolipinasIgM', 'precio' => 60, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Antinucleares Ac (ANA)', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Dengue IgG/IgM+Ag(DUO)', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Factor reumatoideo (latex RA)', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'FTA - ABS (treponema)', 'precio' => 130, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Helicobacter Pylori Ac. IgG', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis A Ac. IgM', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis B Ag. de superficie', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hepatitis C Ac', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'IgE total', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgA (microsomal)', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgG (microsomal)', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'InmunoglobulinasIgM (microsomal)', 'precio' => 35, 'recipiente' => 'quimica_sanguinea'],
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
            ['nombre' => 'Bilirrubina Total', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Bilirrubina Directa', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol Alta Densidad - HDL', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol Baja Densidad - LDL', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Colesterol total', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatin Fosfokinasa (CPK)', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatin Fosfokinasa (CPKMB)', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Creatinina', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Deshidrogenasa Lactida (LDH)', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Ferritina', 'precio' => 30, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fosfatasa Acida', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fosfatasa Alcalina', 'precio' => 8, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Gamma Glutamil (GCT)', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa', 'precio' => 3, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Post Prandial', 'precio' => 3, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 2 Horas', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 3 Horas', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Glucosa Tolerancia 5 Horas', 'precio' => 40, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hemoglobina Glicosilada AIC', 'precio' => 15, 'recipiente' => 'hematologia'],
            ['nombre' => 'Hierro Capacidad de Fijación', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Hierro Sérico', 'precio' => 10, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Lipasa', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Nitrógeno Ureico', 'precio' => 4, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Proteina totales y Dif', 'precio' => 12, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Proteína C Reactiva Cardiaca', 'precio' => 25, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Test O\' Sulivan', 'precio' => 20, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Transaminasa Oxalacética', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Transaminasa Pirúvica', 'precio' => 6, 'recipiente' => 'quimica_sanguinea'],
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
            ['nombre' => 'Acido úrico orina 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Calcio orina de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Cloro orina de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Depuración de creatinina 24h', 'precio' => 15, 'recipiente' => 'quimica_sanguinea'],
            ['nombre' => 'Fósforo orina 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Nitrógeno ureico orina de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Potasio orina de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Proteínas en orina de 24h', 'precio' => 15, 'recipiente' => 'uroanalisis'],
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
            ['nombre' => 'General orina', 'precio' => 2, 'recipiente' => 'uroanalisis'],
            ['nombre' => 'Prueba de embarazo en orina', 'precio' => 5, 'recipiente' => 'uroanalisis'],
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

        //PERFIL
        $perfiles = [
            ['nombre' => 'Perfil Hepático', 'precio' => 35, 'estado' => 1,],
            ['nombre' => 'Perfil de Rutina', 'precio' => 25, 'estado' => 1,],
            ['nombre' => 'Perfil Renal', 'precio' => 30, 'estado' => 1,],
            ['nombre' => 'Perfil Prenatal', 'precio' => 50, 'estado' => 1,],
            ['nombre' => 'Perfil Tiroideo Total', 'precio' => 25, 'estado' => 1,],
            ['nombre' => 'Perfil Tiroideo Libre', 'precio' => 25, 'estado' => 1,],
        ];
        foreach ($perfiles as $pf) {
            Perfil::firstOrCreate(
                ['nombre' => $pf['nombre']],
                [
                    'precio' => $pf['precio'],
                    'estado' => $pf['estado'],
                ]
            );
        }

        $detallehepatico = [
            ['perfil_id' => 1, 'examen_id' => 90],
            ['perfil_id' => 1, 'examen_id' => 91],
            ['perfil_id' => 1, 'examen_id' => 101],
            ['perfil_id' => 1, 'examen_id' => 116],
            ['perfil_id' => 1, 'examen_id' => 117],
        ];
        foreach ($detallehepatico as $dhep) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $dhep['perfil_id'], 'examen_id' => $dhep['examen_id']]
            );
        }

        $detallerutina = [
            ['perfil_id' => 2, 'examen_id' => 16],
            ['perfil_id' => 2, 'examen_id' => 127],
            ['perfil_id' => 2, 'examen_id' => 48],
            ['perfil_id' => 2, 'examen_id' => 87],
            ['perfil_id' => 2, 'examen_id' => 94],
            ['perfil_id' => 2, 'examen_id' => 97],
            ['perfil_id' => 2, 'examen_id' => 103],
            ['perfil_id' => 2, 'examen_id' => 112],
            ['perfil_id' => 2, 'examen_id' => 118]
        ];
        foreach ($detallerutina as $drut) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $drut['perfil_id'], 'examen_id' => $drut['examen_id']]
            );
        }

        $detallerenal = [
            ['perfil_id' => 3, 'examen_id' => 24],
            ['perfil_id' => 3, 'examen_id' => 25],
            ['perfil_id' => 3, 'examen_id' => 48],
            ['perfil_id' => 3, 'examen_id' => 87],
            ['perfil_id' => 3, 'examen_id' => 97],
            ['perfil_id' => 3, 'examen_id' => 112],
            ['perfil_id' => 3, 'examen_id' => 127]

        ];
        foreach ($detallerenal as $dren) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $dren['perfil_id'], 'examen_id' => $dren['examen_id']]
            );
        }

        $detalleprenatal = [
            ['perfil_id' => 4, 'examen_id' => 48],
            ['perfil_id' => 4, 'examen_id' => 75],
            ['perfil_id' => 4, 'examen_id' => 76],
            ['perfil_id' => 4, 'examen_id' => 77],
            ['perfil_id' => 4, 'examen_id' => 79],
            ['perfil_id' => 4, 'examen_id' => 103],
            ['perfil_id' => 4, 'examen_id' => 127]
        ];
        foreach ($detalleprenatal as $dpre) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $dpre['perfil_id'], 'examen_id' => $dpre['examen_id']]
            );
        }

        $detalletiroideototal = [
            ['perfil_id' => 5, 'examen_id' => 37],
            ['perfil_id' => 5, 'examen_id' => 39],
            ['perfil_id' => 5, 'examen_id' => 41],
        ];
        foreach ($detalletiroideototal as $dtot) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $dtot['perfil_id'], 'examen_id' => $dtot['examen_id']]
            );
        }

        $detalletiroideolibre = [
            ['perfil_id' => 6, 'examen_id' => 36],
            ['perfil_id' => 6, 'examen_id' => 38],
            ['perfil_id' => 6, 'examen_id' => 41],
        ];
        foreach ($detalletiroideolibre as $dtol) {
            DetallePerfil::firstOrCreate(
                ['perfil_id' => $dtol['perfil_id'], 'examen_id' => $dtol['examen_id']]
            );
        }

        // Insertar muestras
        $muestras = [
            ['nombre' => 'Baciloscopia', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Cabello', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Cultivo de Esputo', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Cultivo de Liquido Cefalorraquideo', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Flema', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Heces', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado Anal', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado Bucal', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado de Heridas', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado de Oido', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado Faringeo', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado Ocular', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Orina', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Plasma', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Sangre Completa', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Secreción de Absceso', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Secreciones Nasales', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Secreciones Uretrales', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Secreciones Vaginales', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Semen', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Suero', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Uñas', 'descripcion' => null, 'instrucciones_paciente' => null],
            ['nombre' => 'Hisopado Nasal', 'descripcion' => null, 'instrucciones_paciente' => null]
        ];

        foreach ($muestras as $muestra) {
            Muestra::firstOrCreate(
                ['nombre' => $muestra['nombre']],
                [
                    'descripcion' => $muestra['descripcion'],
                    'instrucciones_paciente' => $muestra['instrucciones_paciente'],
                ]
            );
        }

        Log::info('Muestras insertadas correctamente.');

        // Bactereología - Relación Examen-Muestra
        $relacionesMuestras = [
            1 => [5],
            2 => [16, 17, 18, 19, 12],
            3 => [6],
            4 => [22, 2],
            5 => [19, 7, 16, 17, 18, 23, 9],
            6 => [22, 2],
            7 => [13]
        ];

        foreach ($relacionesMuestras as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Coagulacion - Relación Examen-Muestra
        $relacionesMuestrasCoagulacion = [
            8 => [14],
            9 => [15],
            10 => [15],
            11 => [15],
            12 => [14],
            13 => [14],
            14 => [14]
        ];
        foreach ($relacionesMuestrasCoagulacion as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }
        
        // Coprologia - Relación Examen-Muestra
        $relacionesMuestrasCoprologia = [
            15 => [6],
            16 => [6],
            17 => [6],
            18 => [6],
            19 => [6]
        ];
        foreach ($relacionesMuestrasCoprologia as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Electrolitos - Relación Examen-Muestra
        $relacionesMuestrasElectrolitos = [
            20 => [21],
            21 => [21],
            22 => [21],
            23 => [21],
            24 => [21],
            25 => [21]
        ];
        foreach ($relacionesMuestrasElectrolitos as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }
        
        
        // Endocrinologia - Relación Examen-Muestra
        $relacionesMuestrasEndocrinologia = [
            26 => [21],
            27 => [21],
            28 => [21],
            29 => [21],
            30 => [21],
            31 => [21],
            32 => [21],
            33 => [21],
            34 => [21],
            35 => [21],
            36 => [21],
            37 => [21],
            38 => [21],
            39 => [21],
            40 => [21],
            41 => [21]
        ];
        foreach ($relacionesMuestrasEndocrinologia as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Hematologia - Relación Examen-Muestra
        $relacionesMuestrasHematologia = [
            42 => [15],
            43 => [15],
            44 => [15,23],
            45 => [15],
            46 => [15],
            47 => [15],
            48 => [15],
            49 => [15],
            50 => [15],
            51 => [15],
            52 => [15]
        ];
        foreach ($relacionesMuestrasHematologia as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        //Immunologia - Relación Examen-Muestra
        $relacionesMuestrasImmunologia = [
            53 => [21],
            54 => [21],
            55 => [21],
            56 => [23],
            57 => [21],
            58 => [21],
            59 => [21],
            60 => [21],
            61 => [21],
            62 => [21],
            63 => [21],
            64 => [21],
            65 => [21],
            66 => [21],
            67 => [21],
            68 => [21],
            69 => [21],
            70 => [21],
            71 => [21],
            72 => [21],
            73 => [21],
            74 => [21],
            75 => [21],
            76 => [21],
            77 => [21],
            78 => [21],
            79 => [21]
        ];
        foreach ($relacionesMuestrasImmunologia as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Tumorales - Relación Examen-Muestra
        $relacionesMuestrasTumorales = [
            80 => [21],
            81 => [21],
            82 => [21],
            83 => [21],
            84 => [21],
            85 => [21],
            86 => [21]
        ];
        foreach ($relacionesMuestrasTumorales as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Quimica Sanguinea - Relación Examen-Muestra 22
        $relacionesMuestrasQuimicaSanguinea = [
            87 => [21],
            88 => [21],
            89 => [21],
            90 => [21],
            91 => [21],
            92 => [21],
            93 => [21],
            94 => [21],
            95 => [21],
            96 => [21],
            97 => [21],
            98 => [21],
            99 => [21],
            100 => [21],
            101 => [21],
            102 => [21],
            103 => [21],
            104 => [21],
            105 => [21],
            106 => [21],
            107 => [21],
            108 => [15],
            109 => [21],
            110 => [21],
            111 => [21],
            112 => [21],
            113 => [21],
            114 => [21],
            115 => [21],
            116 => [21],
            117 => [21],
            118 => [21],
            119 => [21],
            120 => [21],
            121 => [21],
            122 => [21],
            123 => [21],
            124 => [21],
            125 => [21],
            126 => [21]
        ];
        foreach ($relacionesMuestrasQuimicaSanguinea as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        } 

        //Quimica Urinaria - Relación Examen-Muestra
        $relacionesMuestrasQuimicaUrinaria = [
            127 => [13],
            128 => [13],
            129 => [13],
            130 => [13],
            131 => [13],
            132 => [13],
            133 => [13],
            134 => [13]
        ];
        foreach ($relacionesMuestrasQuimicaUrinaria as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        // Uroanalisis - Relación Examen-Muestra
        $relacionesMuestrasUroanalisis = [
            135 => [13],
            136 => [13]
        ];
        foreach ($relacionesMuestrasUroanalisis as $examen_id => $muestra_ids) {
            $examen = Examen::find($examen_id);
            if ($examen) {
                // Sincronizar las muestras con el examen
                $examen->muestras()->sync($muestra_ids, false);
                Log::info("Examen {$examen_id} asociado con muestras", ['muestras' => $muestra_ids]);
            }
        }

        //grupos etarios
        DB::table('grupos_etarios')->insert([
            ['nombre' => 'Embarazo temprano', 'edad_min' => 0, 'edad_max' => 12, 'unidad_tiempo' => 'semanas', 'genero' => 'Femenino',  'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Embarazo medio', 'edad_min' => 13, 'edad_max' => 27, 'unidad_tiempo' => 'semanas', 'genero' => 'Femenino', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Embarazo tardío', 'edad_min' => 28, 'edad_max' => 42, 'unidad_tiempo' => 'semanas', 'genero' => 'Femenino', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Neonatos', 'edad_min' => 0, 'edad_max' => 28, 'unidad_tiempo' => 'días', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lactantes', 'edad_min' => 1, 'edad_max' => 12, 'unidad_tiempo' => 'meses', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Niños', 'edad_min' => 1, 'edad_max' => 12, 'unidad_tiempo' => 'años', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adolescentes', 'edad_min' => 13, 'edad_max' => 17, 'unidad_tiempo' => 'años', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adultos', 'edad_min' => 18, 'edad_max' => 64, 'unidad_tiempo' => 'años', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adultos mayores', 'edad_min' => 65, 'edad_max' => 120, 'unidad_tiempo' => 'años', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Todas las edades', 'edad_min' => 0, 'edad_max' => 120, 'unidad_tiempo' => 'años', 'genero' => 'Ambos', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);



    }
    private function generatePermissions()
    {
        $resources = [
            'clientes',
            'examen',
            'orden',
            'perfil',
            'role',
            'tipo::examen',
            'user',
            'activity::log',
            'codigo',
            'cotizacion',
            'grupo::etario',
            'muestra',
            'prueba',
            'reactivo',
            'tipo::prueba',

        ];




        //////////////////////////////////////////////PERMISOS GRANULARES AUTOMÁTICOS/////////////////////////////////////////////////////

        //--- Permisos específicos para clientes ----//
        $clienteActions = [
            'ver_detalle_clientes',    // Para el botón 'ver-modal'
            'cambiar_estado_clientes', // Para el botón 'cambiar_estado'
            'ver_expediente_clientes', // Para el botón 'expediente'
        ];

        foreach ($clienteActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /// ///// Permisos Granulares para COTIZACIONES (Herramienta)
        $cotizacionActions = [
            'access_cotizaciones',      // Para poder ver el menú y entrar a la pantalla
            'generar_pdf_cotizacion',   // Para el botón de generar/imprimir el PDF
            'enviar_cotizacion_email',  // (Opcional) Si tienes botón de enviar por correo
        ];

        foreach ($cotizacionActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /////////// --- Permisos Granulares para EXÁMENES ---
        $examenActions = [
            'ver_detalle_examenes',     // Para botón 'ver-modal'
            'agregar_pruebas_examenes', // Para botón 'addPruebas'
            'cambiar_estado_examenes',  // Para botón 'cambiar_estado'
        ];

        foreach ($examenActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //////////////////// --- Permisos Granulares para ÓRDENES ---
        $ordenActions = [
            'procesar_muestras_orden',   // Para "Gestionar Muestras"
            'ingresar_resultados_orden', // Para "Ingresar Resultados"
            'imprimir_etiquetas_orden',  // Para "Imprimir Etiquetas"
            'ver_pruebas_orden',         // Para "Ver Pruebas"
            'pausar_orden',              // Para "Pausar"
            'reanudar_orden',            // Para "Reanudar"
            'finalizar_orden',           // Para "Finalizar"
            'generar_reporte_orden',     // Para "Generar Reporte PDF"
            'cancelar_orden',            // Para "Cancelar"
        ];

        foreach ($ordenActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        ///// // --- Permisos Granulares para PERFILES ---
        $perfilActions = [
            'cambiar_estado_perfiles', // Para el botón 'toggleEstado'
        ];

        foreach ($perfilActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //// // --- Permisos Granulares para PRUEBAS ---
        $pruebaActions = [
            'ver_pruebas_conjuntas', // Para el botón de la cabecera "Ver Pruebas en Matriz"
        ];

        foreach ($pruebaActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //// // --- Permisos Granulares para REACTIVOS ---
        $reactivoActions = [
            'activar_reactivos',       // Para 'setActive'
            'gestionar_valores_ref',   // Para 'gestionarValores'
            'reabastecer_reactivos',   // Para 'restock'
            'agotar_reactivos',        // Para 'marcarAgotado'
        ];

        foreach ($reactivoActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        /// // --- Permisos Granulares para TIPOS DE EXAMEN ---
        $tipoExamenActions = [
            'cambiar_estado_tipo_examenes', // Para el botón 'toggleEstado'
        ];

        foreach ($tipoExamenActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /// // --- Permisos Granulares para PÁGINAS ---
        $paginasActions = [
            'acceder_buscador_expedientes', // Para poder entrar al menú "Buscar Expediente"
        ];

        foreach ($paginasActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //// --- Permisos Granulares para KANBAN ETIQUETAS ---
        $kanbanActions = [
            'imprimir_etiquetas_kanban', // Para todos los botones de imprimir (ZPL)
            'mover_etiquetas_kanban',    // Para poder arrastrar y soltar tarjetas
        ];

        foreach ($kanbanActions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //

        // Permisos por recurso
        foreach ($resources as $resource) {
            $permissions = [
                "view_{$resource}",
                "view_any_{$resource}",
                "create_{$resource}",
                "update_{$resource}",
                "restore_{$resource}",
                "restore_any_{$resource}",
                "replicate_{$resource}",
                "reorder_{$resource}",
                "delete_{$resource}",
                "delete_any_{$resource}",
                "force_delete_{$resource}",
                "force_delete_any_{$resource}",
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            Log::info("Permisos generados para el recurso: {$resource}");
        }

        // Permisos especiales/globales
        $specialPermissions = [
            'impersonate_user',
            'access_admin_panel',
            'manage_settings',
            'export_data',
            'import_data',
            'view_reports',
        ];

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Log::info('Permisos especiales generados.', ['count' => count($specialPermissions)]);

        Log::info('Permisos totales generados automáticamente:', ['total' => Permission::count()]);
    }
    private function createRolesAndAssignPermissions()
    {
        // =====================================================================
        // 1. ROL: RECEPCIÓN
        // =====================================================================
        $roleRecepcion = Role::firstOrCreate(['name' => 'recepcion']);
        $roleRecepcion->syncPermissions([
            // --- Acceso General ---
            'access_admin_panel',
            //iew_dashboard',
            //page_BuscarExpediente',
            'acceder_buscador_expedientes',

            // --- Clientes (Gestión completa pero sin borrar a lo loco) ---
            'view_any_clientes',
            'view_clientes',
            'create_clientes',
            'update_clientes',
            'ver_detalle_clientes',
            'ver_expediente_clientes',
            // NO le damos 'delete' ni 'cambiar_estado' para evitar errores, solo admin

            // --- Órdenes (Gestión administrativa) ---
            'view_any_orden',
            'view_orden',
            'create_orden',
            'update_orden',
            'imprimir_etiquetas_orden', // Para re-imprimir si hace falta
            'generar_reporte_orden',    // Para entregar al paciente
            'cancelar_orden',           // Si el paciente se arrepiente antes de pagar

            // --- Cotizaciones ---
            'view_any_cotizacion',
            'create_cotizacion',
            'access_cotizaciones',
            'generar_pdf_cotizacion',
            'enviar_cotizacion_email',

            // --- Catálogos (Solo lectura para consulta de precios) ---
            'view_any_examen',
            'view_examen',
            'view_any_perfil',
            'view_perfil',
            'view_any_codigo', // Ver cupones para aplicarlos
        ]);
        Log::info('Rol Recepción configurado.');

        // =====================================================================
        // 2. ROL: TOMA DE MUESTRAS
        // =====================================================================
        $roleTomaMuestras = Role::firstOrCreate(['name' => 'toma_muestras']);
        $roleTomaMuestras->syncPermissions([
            'access_admin_panel',

            // --- Órdenes (Solo ver para procesar) ---
            'view_any_orden',
            'view_orden',

            // --- Operativo Muestras ---
            //age_DetalleOrdenKanban',    // Acceso al tablero
            'imprimir_etiquetas_kanban',  // Imprimir stickers
            'mover_etiquetas_kanban',     // Cambiar estado de tubos
            'procesar_muestras_orden',    // Botón de recibir muestra en la lista
            'imprimir_etiquetas_orden',   // Botón en la lista

            // --- Consultas básicas ---
            'view_any_clientes', // Para confirmar identidad
            'view_clientes',
            'view_any_muestra',  // Ver catálogo de tubos
        ]);
        Log::info('Rol Toma de Muestras configurado.');

        // =====================================================================
        // 3. ROL: LABORATORISTA
        // =====================================================================
        $roleLaboratorista = Role::firstOrCreate(['name' => 'laboratorista']);
        $roleLaboratorista->syncPermissions([
            'access_admin_panel',
            // 'view_dashboard',

            // --- Procesamiento Analítico ---
            'view_any_orden',
            'view_orden',
            'ingresar_resultados_orden', // ¡Su función principal!
            'ver_pruebas_orden',         // Ver qué toca hacer
            'pausar_orden',              // Si falta muestra o reactivo
            'reanudar_orden',
            'finalizar_orden',           // Validación final técnica

            // --- Gestión Técnica (Catálogos) ---
            'view_any_reactivo',
            'view_reactivo',
            'create_reactivo',
            'update_reactivo',
            'activar_reactivos',
            'gestionar_valores_ref',
            'reabastecer_reactivos',
            'agotar_reactivos',

            'view_any_examen',
            'view_examen',
            'ver_detalle_examenes',
            'view_any_prueba',
            'view_prueba',
            'ver_pruebas_conjuntas',
            'view_any_muestra',
            // 'view_any_grupo_etario',

            // --- Bitácora (Auditoría) ---
            // Le damos acceso de lectura para que revise historial de cambios en resultados
            'view_any_activity::log',
            'view_activity::log',
        ]);
        Log::info('Rol Laboratorista configurado.');
    }

}


