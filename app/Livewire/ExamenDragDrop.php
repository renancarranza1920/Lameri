<?php

namespace App\Livewire;

use App\Models\DetallePerfil;
use App\Models\Examen;
// Removed duplicate import
use App\Models\Perfil;
use Livewire\Component;
use Illuminate\Support\Str;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use function dd;

class ExamenDragDrop extends Component implements HasForms
{
    use InteractsWithForms;
    public $data = []; // Añade esta línea para tener la propiedad data
    public $examenesDisponibles;  // Aquí guardamos todos los exámenes
    public $examenesSeleccionados = [];
    public $busquedaDisponible = '';
    public $busquedaSeleccionado = [];
    public $perfilId;


    public $colapsadoDisponible = false;
    public $colapsadoSeleccionado = false;
    public $colapsadoGlobal = false;

    public function mount($examenesIniciales = [], $perfilId = null)
    {
      //  dd($perfilId); // Esto mostrará el valor de $perfilId cuando el componente se monte
    $this->perfilId = $perfilId;
        
        // Si perfilId está disponible, carga los exámenes seleccionados
        if ($this->perfilId) {
            $this->examenesSeleccionados = $this->getExamenesPorPerfil($this->perfilId);
            // Llamar a la función de agrupar seleccionados
           // dd($this->examenesSeleccionados);
         //   $this->examenesSeleccionados = $this->getAgrupadosSeleccionados()->toArray();
        }
    
        // Si ya vienen exámenes iniciales desde la vista (como en la edición), cargalos
        if (!empty($examenesIniciales)) {
            $this->examenesSeleccionados = $examenesIniciales;
               
        }

         // Sincronizar con $data
    
        
        // Carga los exámenes disponibles
        $this->examenesDisponibles = Examen::with('tipoExamen')->get();
    }
    
    private function getExamenesPorPerfil($perfilId)
    {
        $perfil = Perfil::with('examenes.tipoExamen')->find($perfilId);
    
        if (!$perfil) {
            return [];
        }
    
        // Transformar los exámenes al formato esperado
        return $perfil->examenes->map(function ($examen) {
            return [
                'id' => $examen->id,
                'nombre' => $examen->nombre,
                'tipo' => $examen->tipoExamen->nombre ?? 'Sin Tipo', // Aseguramos que siempre haya un valor
            ];
        })->toArray();
       
    }
    

        // Método para obtener los exámenes seleccionados de un perfil

    // Método para obtener los exámenes disponibles filtrados
    public function getAgrupadosDisponibles()
    {
        $query = $this->examenesDisponibles;
    
        // Filtrado que ignora acentos
        if ($this->busquedaDisponible) {
            $searchTerm = Str::ascii($this->busquedaDisponible); // Convierte áéíóú a aeiou
            $query = $query->filter(function ($examen) use ($searchTerm) {
                $nombreAscii = Str::ascii($examen->nombre);
                $tipoAscii = Str::ascii($examen->tipoExamen->nombre ?? '');
                
                return Str::contains(strtolower($nombreAscii), strtolower($searchTerm)) || 
                       Str::contains(strtolower($tipoAscii), strtolower($searchTerm));
            });
        }
    
        // Agrupación por el nombre ORIGINAL pero usando el ascii para agrupar equivalentes
        return $query->filter(fn ($examen) => !in_array($examen->id, array_column($this->examenesSeleccionados, 'id')))
                     ->groupBy(function ($examen) {
                         // Usamos el nombre original para mostrar pero el ascii para agrupar
                         return $examen->tipoExamen->nombre ?? 'Sin Tipo';
                     }, preserveKeys: true);
    }
    

    // Método para obtener los exámenes seleccionados filtrados
    public function getAgrupadosSeleccionados()
    {
        return collect($this->examenesSeleccionados)
            ->filter(fn ($examen) => empty($this->busquedaSeleccionado) || str($examen['nombre'])->lower()->contains(str($this->busquedaSeleccionado)->lower()))
            ->groupBy(fn ($examen) => $examen['tipo'] ?? 'Sin Tipo');
    }

    // Método para agregar examen a la lista de seleccionados
    public function addExamen($id)
    {
        $examen = $this->examenesDisponibles->firstWhere('id', $id);
        if ($examen) {
            $this->examenesSeleccionados[] = [
                'id' => $examen->id,
                'nombre' => $examen->nombre,
                'tipo' => $examen->tipoExamen->nombre ?? 'Sin Tipo',
            ];
            $this->emitSelectionUpdated(); // Nuevo
        }
    }

    // Método para remover examen (actualizado)
    public function removeExamen($id)
    {
        $this->examenesSeleccionados = array_filter(
            $this->examenesSeleccionados, 
            fn ($examen) => $examen['id'] !== $id
        );
        $this->emitSelectionUpdated(); // Nuevo
    }

       // Nuevo método para emitir la actualización
       public function emitSelectionUpdated()
       {
        $this->dispatch('examenesSeleccionadosUpdated', examenes: $this->examenesSeleccionados);
    }
    // Método para alternar colapso de la lista de exámenes disponibles
    public function toggleDisponibles()
    {
        $this->colapsadoDisponible = !$this->colapsadoDisponible;
    }

    // Método para alternar colapso de la lista de exámenes seleccionados
    public function toggleSeleccionados()
    {
        $this->colapsadoSeleccionado = !$this->colapsadoSeleccionado;
    }

    // Método para colapsar ambas listas al mismo tiempo
    public function toggleAmbos()
    {
        $this->colapsadoGlobal = !$this->colapsadoGlobal;
        $this->colapsadoDisponible = $this->colapsadoGlobal;
        $this->colapsadoSeleccionado = $this->colapsadoGlobal;
    }

    // Método para actualizar los exámenes disponibles cuando se realiza una búsqueda
    public function updatedBusquedaDisponible($value)
    {
        logger("Nuevo valor búsqueda disponible: " . $value);
    }

    public function render()
    {
        return view('livewire.examen-drag-drop', [
            'agrupadosDisponibles' => $this->getAgrupadosDisponibles(),
            'agrupadosSeleccionados' => $this->getAgrupadosSeleccionados(),
        ]);
    }


    /////////////////////////////logica guardado
    // Agrega estos métodos al componente
    protected $listeners = [
        'examenesSeleccionadosUpdated' => 'updateExamenesSeleccionados',
        'saveProfile' => 'prepareForSave',
    ];
    


    public function updateExamenesSeleccionados($examenes)
{
    if (json_encode($this->examenesSeleccionados) !== json_encode($examenes)) {
        $this->examenesSeleccionados = $examenes;
        $this->dispatch('examenesSeleccionadosUpdated', examenes: $this->examenesSeleccionados);
    }
}
   // Método para preparar datos antes de guardar
   public function prepareForSave()
   {
       \Log::debug('Exámenes seleccionados ANTES de enviar:', $this->examenesSeleccionados);
       
       // Forzar refresco de datos antes de enviar
       $this->examenesSeleccionados = array_values($this->examenesSeleccionados);
       
       \Log::debug('Exámenes seleccionados DESPUÉS de limpiar:', $this->examenesSeleccionados);
       
       $this->dispatch('examenesSeleccionadosUpdated', 
           examenes: $this->examenesSeleccionados
       );
       
       return $this->examenesSeleccionados;
   }

   // Método para refrescar datos
   public function refreshExamenes()
   {
       $this->examenesDisponibles = Examen::with('tipoExamen')->get();
       $this->emitSelectionUpdated();
   }

   public function hydrate()
{
    $this->examenesSeleccionados = $this->examenesSeleccionados ?? [];
}

public function dehydrate()
{
    $this->examenesSeleccionados = $this->examenesSeleccionados ?? [];
}
   
}
