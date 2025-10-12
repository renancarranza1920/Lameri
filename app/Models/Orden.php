<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }



        public function detalleOrden()
    {
        return $this->hasMany(DetalleOrden::class);
    }

    public function resultados(): HasManyThrough
    {
        return $this->hasManyThrough(Resultado::class, DetalleOrden::class);
    }
}
