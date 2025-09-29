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
 }