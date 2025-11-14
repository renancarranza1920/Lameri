<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // 👈 Importa esto
use App\Models\Orden; // 👈 Importa el modelo Orden

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

    protected static function booted(): void
    {
        static::creating(function ($cliente) {
            if (!$cliente->NumeroExp) {
                $prefijo = strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido, 0, 1));
                $año = date('y');
                $base = $prefijo . $año;

                $cantidadExistentes = Cliente::where('NumeroExp', 'LIKE', "$base%")->count();
                $correlativo = str_pad($cantidadExistentes + 1, 3, '0', STR_PAD_LEFT);
                $cliente->NumeroExp = $base . $correlativo;
            }

            if (!$cliente->estado) {
                $cliente->estado = 'Activo';
            }
        });
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }
}
