<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    protected $table = "examens";
    protected $fillable = [
        'tipo_examen_id',
        'nombre',
        'precio',
        'recipiente',
        'estado',
    ];
    public function tipoExamen()
    {
        return $this->belongsTo(TipoExamen::class, 'tipo_examen_id');
    }
    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'detalle_perfil', 'examen_id', 'perfil_id');
    }

    public function ordenes()
    {
        return $this->belongsToMany(Orden::class, 'detalle_orden_examens', 'examen_id', 'orden_id');
    }

      public function muestras()
    {
        return $this->belongsToMany(Muestra::class, 'examen_muestra', 'examen_id', 'muestra_id');
    }

    public function pruebas(): HasMany
{
    return $this->hasMany(Prueba::class);
}
}
