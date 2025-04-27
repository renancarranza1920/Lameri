<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoExamen extends Model

{
    protected $table = 'tipo_examens';
    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function examenes()
    {
        return $this->hasMany(Examen::class, 'tipo_examen_id');
    }
}

