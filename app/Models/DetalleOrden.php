<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class DetalleOrden extends Model implements Sortable
{
    use SortableTrait;
    protected $table = 'detalle_orden';

    protected $fillable = [
        'orden_id',
        'examen_id',
        'perfil_id',
        'nombre_examen',
        'nombre_perfil',
        'precio_examen',
        'precio_perfil',
        'status',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }
}



