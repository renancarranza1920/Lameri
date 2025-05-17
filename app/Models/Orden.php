<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'cliente_id',
        'total',
        'fecha',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
