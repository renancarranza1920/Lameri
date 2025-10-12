<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muestra extends Model
{
    use HasFactory;

    protected $table = 'muestras';

    protected $fillable = [
        'nombre',
        'descripcion',
        'instrucciones_paciente',
    ];

    /**
     * Define la relación de Muchos a Muchos con Examen.
     * Una muestra puede ser requerida por muchos exámenes.
     */
    public function examenes()
    {
        return $this->belongsToMany(Examen::class, 'examen_muestra', 'muestra_id', 'examen_id');
    }
}
