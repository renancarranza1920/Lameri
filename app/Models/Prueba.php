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

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function examen(): BelongsTo 
    { 
        return $this->belongsTo(Examen::class); 
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function reactivoEnUso(): HasOne
    {
        // Devuelve el único reactivo para esta prueba que está marcado como "en uso"
        return $this->hasOne(Reactivo::class)->where('en_uso', true);
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