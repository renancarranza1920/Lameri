<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 1. IMPORTAR LAS CLASES DE SPATIE Y RELACIONES FALTANTES
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\DetalleOrden;
use App\Models\Prueba;

class Resultado extends Model 
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;

    protected $guarded = [];

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function detalleOrden(): BelongsTo 
    { 
        return $this->belongsTo(DetalleOrden::class); 
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function prueba(): BelongsTo 
    { 
        return $this->belongsTo(Prueba::class); 
    }

    // 4. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Resultados') // Nombre del módulo
            
            ->setDescriptionForEvent(function(string $eventName) {
                // Traducimos el evento
                $eventoTraducido = match($eventName) {
                    'created' => 'ingresado',
                    'updated' => 'actualizado',
                    'deleted' => 'eliminado',
                    default => $eventName
                };
                
                // Creamos una descripción detallada
                return "Se ha {$eventoTraducido} un resultado para la prueba ID [{$this->prueba_id}] en la orden [ID: {$this->detalleOrden->orden_id}]. Nuevo valor: {$this->resultado}";
            })
            
            // Rastrear todos los campos (ya que usas $guarded)
            ->logUnguarded() 
            
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs();
    }
}