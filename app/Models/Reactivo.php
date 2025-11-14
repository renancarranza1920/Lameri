<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 1. IMPORTAR LAS CLASES DE SPATIE Y RELACIONES FALTANTES
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Prueba;
use App\Models\ValorReferencia;

class Reactivo extends Model
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'en_uso' => 'boolean', // Usamos el nombre de columna correcto
    ];

    protected static function booted(): void
    {
        static::saving(function (Reactivo $reactivo) {
            // Verificamos el atributo correcto
            if ($reactivo->en_uso) {
                // Actualizamos la columna correcta
                self::where('prueba_id', $reactivo->prueba_id)
                    ->where('id', '!=', $reactivo->id)
                    ->update(['en_uso' => false]);
            }
        });
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class);
    }
    
    // 3. ESPECIFICAR TIPO DE RETORNO
    public function valoresReferencia(): HasMany
    {
        return $this->hasMany(ValorReferencia::class);
    }

    // 4. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Reactivos') // Nombre del módulo
            
            ->setDescriptionForEvent(function(string $eventName) {
                // Traducimos el evento
                $eventoTraducido = match($eventName) {
                    'created' => 'creado',
                    'updated' => 'actualizado',
                    'deleted' => 'eliminado',
                    default => $eventName
                };
                
                // Creamos un nombre descriptivo, ya que reactivo no tiene "nombre"
                $pruebaNombre = $this->prueba ? $this->prueba->nombre : 'desconocida';
                return "El reactivo (ID: {$this->id}) para la prueba '{$pruebaNombre}' ha sido {$eventoTraducido}";
            })
            
            // Rastrear todos los campos (equivalente a logFillable() cuando se usa $guarded)
            ->logUnguarded() 
            
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs();
    }
}