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

}

