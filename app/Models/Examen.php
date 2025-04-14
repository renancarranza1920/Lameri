<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    protected $table = "examens";
    protected $fillable = [
        'tipo_examen_id',
        'nombre',
        'precio',
        'estado',
    ];
    public function tipoExamen()
    {
        return $this->belongsTo(TipoExamen::class, 'tipo_examen_id');
    }

}
