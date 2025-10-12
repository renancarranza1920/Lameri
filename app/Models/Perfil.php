<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model

{
    protected $table = 'perfil';

    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'estado',
    ];

    public function examenes()
    {
        return $this->belongsToMany(Examen::class, 'detalle_perfil', 'perfil_id', 'examen_id')
                    ->withTimestamps();
    }

    public function ordenes()
    {
        return $this->belongsToMany(Orden::class, 'detalle_orden_perfils', 'perfil_id', 'orden_id')
                    ->withTimestamps();
    }
}
