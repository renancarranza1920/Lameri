<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 1. IMPORTAR LAS CLASES DE SPATIE Y FACTORY
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Models\Orden; 
use App\Models\GrupoEtario; 
use Carbon\Carbon; 

class Cliente extends Model
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;
    
    protected $table = 'clientes';
    protected $fillable = [
        'NumeroExp',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'correo',
        'direccion',
        'estado',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cliente) {
            if (!$cliente->NumeroExp) {
                $prefijo = strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido, 0, 1));
                $año = date('y');
                $base = $prefijo . $año;

                // Usamos 'self::' para referirnos a la clase actual (cliente)
                $cantidadExistentes = self::where('NumeroExp', 'LIKE', "$base%")->count();
                $correlativo = str_pad($cantidadExistentes + 1, 3, '0', STR_PAD_LEFT);
                $cliente->NumeroExp = $base . $correlativo;
            }

            if (!$cliente->estado) {
                $cliente->estado = 'Activo';
            }
        });
    }


    
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }

    // 3. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->uselogName('Clientes') // Nombre del módulo en la bitácora
            
            // Descripción más útil y en español
            ->setDescriptionForEvent(function(string $eventName) {
                $eventoTraducido = match($eventName) {
                    'created' => 'creado',
                    'updated' => 'actualizado',
                    'deleted' => 'eliminado',
                    default => $eventName
                };
                
                return "El cliente '{$this->nombre} {$this->apellido}' (ID: {$this->id}) ha sido {$eventoTraducido}";
            })
            
            // Rastrear todos los campos en $fillable automáticamente
            ->logFillable() 
            
            // Registrar solo los campos que realmente cambiaron
            ->logOnlyDirty() 
            
            // No guardar logs vacíos (ej. si solo se toca 'updated_at')
            ->dontSubmitEmptyLogs();
    }

   public function getGrupoEtario(): ?GrupoEtario
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $fechaNacimiento = Carbon::parse($this->fecha_nacimiento);
        $ahora = Carbon::now();
        
        // Calcular edades en distintas unidades
        $edadDias = $fechaNacimiento->diffInDays($ahora);
        $edadMeses = $fechaNacimiento->diffInMonths($ahora);
        $edadAnios = $fechaNacimiento->diffInYears($ahora);

        // 1. INTENTO POR DÍAS (Prioridad Alta: Neonatos)
        // Generalmente rangos de 0 a 30 días
        $grupo = GrupoEtario::where('unidad_tiempo', 'días')
            ->where('edad_min', '<=', $edadDias)
            ->where('edad_max', '>=', $edadDias)
            ->whereIn('genero', [$this->genero, 'Ambos']) // Filtramos por género
            ->first();

        if ($grupo) return $grupo;

        // 2. INTENTO POR MESES (Prioridad Media: Lactantes/Bebés)
        // Generalmente rangos de 1 a 12 o 24 meses
        // Solo buscamos aquí si no se encontró por días
        $grupo = GrupoEtario::where('unidad_tiempo', 'meses')
            ->where('edad_min', '<=', $edadMeses)
            ->where('edad_max', '>=', $edadMeses)
            ->whereIn('genero', [$this->genero, 'Ambos'])
            ->first();

        if ($grupo) return $grupo;

        // 3. INTENTO POR AÑOS (Prioridad Estándar: Niños, Adultos, etc.)
        // El resto de la población cae aquí
        $grupo = GrupoEtario::where('unidad_tiempo', 'años')
            ->where('edad_min', '<=', $edadAnios)
            ->where('edad_max', '>=', $edadAnios)
            ->whereIn('genero', [$this->genero, 'Ambos'])
            ->first();

        return $grupo; // Devuelve el grupo o null si no encaja en nada (ej. > 120 años)
    }
}