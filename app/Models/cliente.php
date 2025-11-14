<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 1. IMPORTAR LAS CLASES DE SPATIE Y FACTORY
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class cliente extends Model
{
    // 2. USAR LOS TRAITS
    use HasFactory, LogsActivity;
    
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

                // Usamos 'self::' para referirnos a la clase actual (cliente)
                $cantidadExistentes = self::where('NumeroExp', 'LIKE', "$base%")->count();
                $correlativo = str_pad($cantidadExistentes + 1, 3, '0', STR_PAD_LEFT);
                $cliente->NumeroExp = $base . $correlativo;
            }

            if (!$cliente->estado) {
                $cliente->estado = 'Activo';
            }
        });
    }

    // 3. AÑADIR EL MÉTODO DE CONFIGURACIÓN DE LA BITÁCORA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->uselogName('Clientes') // Nombre del módulo en la bitácora
            
            // Descripción más útil y en español
            ->setDescriptionForEvent(function(string $eventName) {
                $eventoTraducido = match($eventName) {
                    'created' => 'creado',
                    'updated' => 'actualizado',
                    'deleted' => 'eliminado',
                    default => $eventName
                };
                
                return "El cliente '{$this->nombre} {$this->apellido}' (ID: {$this->id}) ha sido {$eventoTraducido}";
            })
            
            // Rastrear todos los campos en $fillable automáticamente
            ->logFillable() 
            
            // Registrar solo los campos que realmente cambiaron
            ->logOnlyDirty() 
            
            // No guardar logs vacíos (ej. si solo se toca 'updated_at')
            ->dontSubmitEmptyLogs();
    }
}