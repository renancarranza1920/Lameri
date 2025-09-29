<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reactivo extends Model
{
    use HasFactory;

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

    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class);
    }
    
    public function valoresReferencia(): HasMany
    {
        return $this->hasMany(ValorReferencia::class);
    }
}