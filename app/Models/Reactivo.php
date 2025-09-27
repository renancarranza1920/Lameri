<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reactivo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'prueba_id', 'lote', 'fecha_caducidad', 'descripcion', 'is_active', 'plantilla_referencia_id'];

    public function prueba(): BelongsTo { return $this->belongsTo(Prueba::class); }
    public function plantillaReferencia(): BelongsTo { return $this->belongsTo(PlantillaReferencia::class, 'plantilla_referencia_id'); }
    public function valoresReferencia(): HasMany { return $this->hasMany(ValorReferencia::class); }
}