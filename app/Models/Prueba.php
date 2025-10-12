<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Cambia HasOne por HasMany

class Prueba extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'tipo_prueba_id'];

    public function tipoPrueba(): BelongsTo { return $this->belongsTo(TipoPrueba::class); }

    public function examen(): BelongsTo 
{ 
    return $this->belongsTo(Examen::class); 
}
public function reactivoEnUso()
{
    // Devuelve el único reactivo para esta prueba que está marcado como "en uso"
    return $this->hasOne(Reactivo::class)->where('en_uso', true);
}

public function resultados() { return $this->hasMany(Resultado::class); }
 }