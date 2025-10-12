<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPrueba extends Model
{
    use HasFactory;
protected $table = 'tipos_pruebas';
    protected $fillable = ['nombre'];

    public function pruebas(): HasMany
    {
        return $this->hasMany(Prueba::class);
    }
}