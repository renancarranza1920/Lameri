<?php

namespace App\Filament\Resources\PruebaResource\Pages;

use App\Filament\Resources\PruebaResource;
use App\Models\Prueba;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePrueba extends CreateRecord
{
    protected static string $resource = PruebaResource::class;

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();
        $successMessages = [];

        try {
            DB::transaction(function () use ($data, &$successMessages) {
                // --- PROCESAR PRUEBA UNITARIA ---
                if (!empty($data['nombre']) && !empty($data['examen_id'])) {
                    Prueba::create([
                        'nombre' => $data['nombre'],
                        'examen_id' => $data['examen_id'],
                        'tipo_prueba_id' => $data['tipo_prueba_id'] ?? null,
                    ]);
                    $successMessages[] = "Se creó la prueba unitaria '{$data['nombre']}'.";
                }

                // --- PROCESAR PRUEBAS CONJUNTAS (MATRIZ) ---
                if (!empty($data['examen_id_conjunto']) && !empty($data['filas']) && !empty($data['columnas'])) {
                    $filas = (int) $data['filas'];
                    $columnas = (int) $data['columnas'];
                    $examenId = $data['examen_id_conjunto'];
                    $nombresFilas = $data['nombres_filas'] ?? [];
                    $nombresColumnas = $data['nombres_columnas'] ?? [];
                    
                    $tipoConjuntoId = 'conjunto_' . uniqid();
                    $pruebasCreadas = 0;

                    for ($f = 1; $f <= $filas; $f++) {
                        for ($c = 1; $c <= $columnas; $c++) {
                            $nombreFila = trim($nombresFilas[$f] ?? "Fila {$f}");
                            $nombreColumna = trim($nombresColumnas[$c] ?? "Columna {$c}");
                            $nombrePrueba = "{$nombreFila}, {$nombreColumna}, ({$f}:{$c})";

                            Prueba::create([
                                'nombre' => $nombrePrueba,
                                'examen_id' => $examenId,
                                'tipo_prueba_id' => null,
                                'tipo_conjunto' => $tipoConjuntoId
                            ]);
                            $pruebasCreadas++;
                        }
                    }

                    if ($pruebasCreadas > 0) {
                        $successMessages[] = "Se generaron {$pruebasCreadas} pruebas en conjunto.";
                    }
                }
            });

            // Si no se procesó nada, informar al usuario.
             if (empty($successMessages)) {
                Notification::make()
                    ->title('No se Creó Nada')
                    ->body('Por favor, completa los campos para una prueba unitaria o una matriz.')
                    ->warning()
                    ->send();
               
                return;
            }

            // Si hubo éxito, mostrar un resumen de lo que se hizo.
            Notification::make()
                ->title('Creación Exitosa')
                ->body(implode("\n", $successMessages))
                ->success()
                ->send();

        } catch (Throwable $e) {
            // Si algo falla dentro de la transacción, se revierte y se muestra un error.
            Notification::make()
                ->title('Error al Guardar')
                ->body('Ocurrió un error inesperado. No se guardó ningún dato. Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
        }

        // Manejar la redirección o reseteo del formulario
        if ($another) {
            $this->form->fill();
        } else {
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }
}