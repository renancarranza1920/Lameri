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
/**
     * Desactiva otros reactivos que compartan pruebas con este.
     */
  public function resolverConflictosDeUso(): int // <--- CAMBIO IMPORTANTE: int
    {
        // Solo ejecutamos si este reactivo está marcado como en uso
        if (!$this->en_uso) return 0;

        // 1. Obtener IDs de las pruebas de ESTE reactivo
        $misPruebasIds = $this->pruebas()->pluck('pruebas.id')->toArray();

        if (empty($misPruebasIds)) return 0;

        // 2. Usamos transaction y retornamos el resultado del update
        return \Illuminate\Support\Facades\DB::transaction(function () use ($misPruebasIds) {
            
            return self::where('en_uso', true)
                ->where('id', '!=', $this->id) // No desactivarme a mí mismo
                ->whereHas('pruebas', function ($q) use ($misPruebasIds) {
                    $q->whereIn('pruebas.id', $misPruebasIds);
                })
                ->update(['en_uso' => false]); // <--- Esto devuelve el número de filas afectadas
        });
    }
    protected static function booted(): void
    {
        static::saving(function (Reactivo $reactivo) {
            // REGLA DE ORO:
            // Si el estado NO es 'disponible', es imposible que esté 'en_uso'.
            if (in_array($reactivo->estado, ['agotado', 'caducado']) && $reactivo->en_uso) {
                $reactivo->en_uso = false;
            }
        });
    }

    // 3. ESPECIFICAR TIPO DE RETORNO
   public function pruebas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Prueba::class, 'prueba_reactivo');
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
            ->useLogName('Reactivos')
            ->setDescriptionForEvent(fn(string $e) => "El reactivo (ID: {$this->id}) ha sido {$e}")
            ->logUnguarded()
            ->logOnlyDirty();
    }
}