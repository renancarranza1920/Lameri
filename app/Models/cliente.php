<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cliente extends Model
{
    
    protected $table = 'clientes';
    protected $fillable = [
        'NumeroExp',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'direccion',
        'estado',
    ];
}
