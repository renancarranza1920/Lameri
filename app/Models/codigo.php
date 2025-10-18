<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;


class codigo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'codigo',
        'tipo_descuento',
        'valor_descuento',
        'limite_usos',
        'usos_actuales',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    /** Verifica si el código puede usarse */
    public function esValido(): bool
    {
        // Actualizar estado si venció
        if (Carbon::parse($this->fecha_vencimiento)->isPast() && $this->estado === 'Activo') {
            $this->update(['estado' => 'Expirado']);
        }

        // Actualizar estado si llegó al límite de usos
        if ($this->usos_actuales >= $this->limite_usos && $this->estado === 'Activo') {
            $this->update(['estado' => 'Inactivo']);
        }

        // Solo puede usarse si está activo
        return $this->estado === 'Activo';
    }

    protected static function booted()
{
    static::creating(function ($codigo) {
        $codigo->estado = 'Activo';
        $codigo->usos_actuales = 0;
    });
}


     /** Registra un uso del código */
    public function registrarUso(): void
    {
        $this->increment('usos_actuales');

        if ($this->usos_actuales >= $this->limite_usos) {
            $this->update(['estado' => 'Inactivo']);
        }
    }

    public static function desactivarVencidos(): void
    {
        static::where('fecha_vencimiento', '<', Carbon::today())
            ->where('estado', 'Activo')
            ->update(['estado' => 'Expirado']);
    }

    /** Aplica descuento al total */
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
