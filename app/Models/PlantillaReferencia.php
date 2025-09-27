<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaReferencia extends Model
{
    use HasFactory;
    protected $table = 'plantillas_referencia';
    protected $fillable = ['nombre', 'descripcion', 'estructura_formulario'];
    protected $casts = ['estructura_formulario' => 'array'];
}