<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model {
    protected $guarded = [];
    public function detalleOrden(): BelongsTo { return $this->belongsTo(DetalleOrden::class); }
    public function prueba(): BelongsTo { return $this->belongsTo(Prueba::class); }
}