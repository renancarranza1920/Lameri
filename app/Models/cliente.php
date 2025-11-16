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
            return null; // No podemos determinar si no hay fecha de nacimiento
        }

        $fechaNacimiento = Carbon::parse($this->fecha_nacimiento);
        $ahora = Carbon::now();

        // Calcular la edad en todas las unidades
        $edadEn = [
            'días' => $fechaNacimiento->diffInDays($ahora),
            'semanas' => $fechaNacimiento->diffInWeeks($ahora),
            'meses' => $fechaNacimiento->diffInMonths($ahora),
            'años' => $fechaNacimiento->diffInYears($ahora),
        ];

        // Buscar en la BD el grupo que coincida
        // Itera sobre las unidades de tiempo (años, meses, días...)
        foreach ($edadEn as $unidad => $edad) {
            
            $grupo = GrupoEtario::where('unidad_tiempo', $unidad)
                ->where('edad_min', '<=', $edad)
                ->where('edad_max', '>=', $edad)
                ->first();

            if ($grupo) {
                return $grupo; // Encontramos el grupo
            }
        }

        // Si no se encontró ningún grupo (ej. 121 años)
        return null; 
    }
}