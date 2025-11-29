<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 1. IMPORTAR LAS CLASES DE SPATIE Y RELACIONES FALTANTES
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\TipoPrueba;
use App\Models\Examen;
use App\Models\Reactivo;
use App\Models\Resultado;

class Prueba extends Model
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;
    
    protected $fillable = ['nombre', 'tipo_prueba_id', 'examen_id', 'tipo_conjunto'];

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function tipoPrueba(): BelongsTo 
    { 
        return $this->belongsTo(TipoPrueba::class); 
    }
    public function reactivos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Reactivo::class, 'prueba_reactivo');
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function examen(): BelongsTo 
    { 
        return $this->belongsTo(Examen::class); 
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
 public function reactivoEnUso(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        // Usamos hasOne a través de la tabla pivote simulando una relación directa
        // para que Eloquent devuelva un solo modelo, no una colección.
        return $this->hasOne(Reactivo::class, 'id', 'id') // Truco de Eloquent
            ->join('prueba_reactivo', 'reactivos.id', '=', 'prueba_reactivo.reactivo_id')
            ->whereColumn('prueba_reactivo.prueba_id', 'pruebas.id')
            ->where('reactivos.en_uso', true)
            ->select('reactivos.*'); // Aseguramos traer solo datos del reactivo
    }

    // 3. ESPECIFICAR TIPO DE RETORNO (ya estaba)
    public function resultados(): HasMany 
    { 
        return $this->hasMany(Resultado::class); 
    }

    // 4. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Pruebas') // Nombre del módulo
            
            ->setDescriptionForEvent(function(string $eventName) {
                // Traducimos el evento
                $eventoTraducido = match($eventName) {
                    'created' => 'creada',
                    'updated' => 'actualizada',
                    'deleted' => 'eliminada',
                    default => $eventName
                };
                
                return "La prueba '{$this->nombre}' ha sido {$eventoTraducido}";
            })
            
            // Rastrear todos los campos en $fillable automáticamente
            ->logFillable() 
            
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs();
    }
}