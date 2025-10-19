<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Codigo extends Model
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

    /**
     * Determina si el código puede usarse.
     */
    public function esValido(): bool
    {
        // Si ya está inactivo, no es válido
        if ($this->estado !== 'Activo') {
            return false;
        }

        // Si tiene fecha de vencimiento y ya pasó, se desactiva
        if ($this->fecha_vencimiento && Carbon::parse($this->fecha_vencimiento)->isPast()) {
            $this->update(['estado' => 'Inactivo']);
            return false;
        }

        // Si tiene límite de usos y se alcanzó, se desactiva
        if ($this->limite_usos && $this->limite_usos > 0 && $this->usos_actuales >= $this->limite_usos) {
            $this->update(['estado' => 'Inactivo']);
            return false;
        }

        return true;
    }

    /**
     * Asigna valores predeterminados al crear.
     */
    protected static function booted()
    {
        static::creating(function ($codigo) {
            $codigo->estado = 'Activo';
            $codigo->usos_actuales = 0;
        });
    }

    /**
     * Registra un uso del código.
     */
    public function registrarUso(): void
    {
        // Solo incrementar si tiene límite
        if ($this->limite_usos && $this->limite_usos > 0) {
            $this->increment('usos_actuales');

            // Si alcanza el límite, se desactiva
            if ($this->usos_actuales >= $this->limite_usos) {
                $this->update(['estado' => 'Inactivo']);
            }
        }
    }

    /**
     * Desactiva los códigos vencidos (los que tienen fecha y ya pasaron).
     */
    public static function desactivarVencidos(): void
    {
        static::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', Carbon::today())
            ->where('estado', 'Activo')
            ->update(['estado' => 'Inactivo']);
    }

    /**
     * Aplica el descuento al total.
     */
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
