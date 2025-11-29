<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 1. IMPORTAR LAS CLASES DE SPATIE Y RELACIONES FALTANTES
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Reactivo;
use App\Models\GrupoEtario;

class ValorReferencia extends Model
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    // 3. ESPECIFICAR EL TIPO DE RETORNO (ya estaba)
    public function reactivo(): BelongsTo
    {
        return $this->belongsTo(Reactivo::class);
    }
    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class);
    }
    // 3. ESPECIFICAR EL TIPO DE RETORNO (ya estaba)
    public function grupoEtario(): BelongsTo
    {
        return $this->belongsTo(GrupoEtario::class);
    }

    // 4. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Valores de Referencia') // Nombre del módulo
            
            ->setDescriptionForEvent(function(string $eventName) {
                // Traducimos el evento
                $eventoTraducido = match($eventName) {
                    'created' => 'creado',
                    'updated' => 'actualizado',
                    'deleted' => 'eliminado',
                    default => $eventName
                };
                
                // Creamos un nombre descriptivo
                return "Un valor de referencia (ID: {$this->id}) para el reactivo [ID: {$this->reactivo_id}] ha sido {$eventoTraducido}";
            })
            
            // Rastrear todos los campos (ya que usas $guarded)
            ->logUnguarded() 
            
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs();
    }


}