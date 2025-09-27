<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorReferencia extends Model
{
    use HasFactory;
    protected $table = 'valores_referencia';
    protected $fillable = ['reactivo_id', 'grupo_etario_id', 'plantilla_referencia_id', 'datos_referencia'];
    protected $casts = ['datos_referencia' => 'array'];

    public function reactivo(): BelongsTo { return $this->belongsTo(Reactivo::class); }
    public function grupoEtario(): BelongsTo { return $this->belongsTo(GrupoEtario::class); }
    public function plantilla(): BelongsTo { return $this->belongsTo(PlantillaReferencia::class, 'plantilla_referencia_id'); }
}