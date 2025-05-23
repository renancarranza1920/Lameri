<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    
    protected $table = 'ordens';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'cliente_id',
        'total',
        'fecha',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function examenes()
    {
        return $this->belongsToMany(Examen::class, 'detalle_orden_examens');
    }
    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'detalle_orden_perfils');
    }
}
