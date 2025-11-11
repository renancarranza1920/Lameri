<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- AÑADIR

class Orden extends Model
{
    
    protected $table = 'ordens';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'cliente_id',
        'total',
        'fecha',
        'observaciones',
        'estado',
        'muestras_recibidas', 
        'toma_muestra_user_id', // <-- AÑADIR
        'fecha_toma_muestra',   // <-- AÑADIR
    ];

    protected $casts = [
        'muestras_recibidas' => 'array',
        'fecha_toma_muestra' => 'datetime', // <-- AÑADIR
    ];

    public function cliente(): BelongsTo // <-- Especificar tipo de retorno
    {
        return $this->belongsTo(Cliente::class);
    }

    // --- ¡AÑADIR ESTA NUEVA RELACIÓN! ---
    public function tomaMuestraUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'toma_muestra_user_id');
    }
    // --- FIN DEL BLOQUE ---

    public function detalleOrden()
    {
        return $this->hasMany(DetalleOrden::class);
    }

    public function resultados(): HasManyThrough
    {
        return $this->hasManyThrough(Resultado::class, DetalleOrden::class);
    }
}