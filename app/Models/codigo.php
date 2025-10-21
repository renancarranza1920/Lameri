<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Codigo extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo', 'tipo_descuento', 'valor_descuento',
        'es_limitado', // Nuevo
        'limite_usos',
        'usos_actuales',
        'tiene_vencimiento', // Nuevo
        'fecha_vencimiento', 'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'es_limitado' => 'boolean', // Nuevo
        'tiene_vencimiento' => 'boolean', // Nuevo
    ];

    public function esValido(): bool
    {
        if ($this->estado !== 'Activo') return false;

        // Ahora la lógica revisa primero la bandera
        if ($this->tiene_vencimiento && $this->fecha_vencimiento && Carbon::parse($this->fecha_vencimiento)->isPast()) {
            $this->update(['estado' => 'Inactivo']);
            return false;
        }

        // Y aquí también
        if ($this->es_limitado && !is_null($this->limite_usos) && $this->usos_actuales >= $this->limite_usos) {
            $this->update(['estado' => 'Inactivo']);
            return false;
        }

        return true;
    }

    protected static function booted()
    {
        static::creating(function ($codigo) {
            $codigo->estado = 'Activo';
            $codigo->usos_actuales = 0;
        });
    }

    public function registrarUso(): void
    {
        // La lógica también se actualiza para usar la bandera
        if ($this->es_limitado) {
            $this->increment('usos_actuales');
            if (!is_null($this->limite_usos) && $this->usos_actuales >= $this->limite_usos) {
                $this->update(['estado' => 'Inactivo']);
            }
        }
    }

    public static function desactivarVencidos(): void
    {
        static::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', Carbon::today())
            ->where('estado', 'Activo')
            ->update(['estado' => 'Inactivo']);
    }

    public function aplicarDescuento(float $total): float
    {
        if ($this->tipo_descuento === 'porcentaje') {
            return max($total - ($total * $this->valor_descuento / 100), 0);
        }
        if ($this->tipo_descuento === 'monto') {
            return max($total - $this->valor_descuento, 0);
        }
        return $total;
    }
}

