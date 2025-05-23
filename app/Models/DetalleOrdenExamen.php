<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleOrdenExamen extends Model
{
    protected $table = 'detalle_orden_examens';

    protected $fillable = [
        'orden_id',
        'examen_id',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }
}
