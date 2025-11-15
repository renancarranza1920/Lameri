<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\DetalleOrdenKanban;
use App\Filament\Resources\OrdenResource;
use App\Models\Orden;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Actions\Action;

class UltimasOrdenesWidget extends BaseWidget
{
    protected static ?int $sort = 0; // Para que aparezca al final de los widgets
    protected int | string | array $columnSpan = 'full'; // Ocupa todo el ancho
    protected static ?string $heading = 'Últimas Órdenes Pendientes o en Proceso';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Orden::query()
                    // Muestra solo órdenes pendientes o en proceso
                    ->whereIn('estado', ['pendiente', 'en proceso'])
                    ->latest() // Las más recientes primero
                    ->limit(5) // Limita a las 5 últimas
            )
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge() // Muestra el estado como un badge de color
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en proceso' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->since() // Muestra "hace X tiempo"
                    ->sortable(),
            ])
            ->actions([
                // Acción para ir a la página de etiquetas
                Action::make('etiquetas')
                    ->label('Etiquetas')
                    ->icon('heroicon-o-ticket')
                    ->color('gray')
                    ->url(fn (Orden $record): string => DetalleOrdenKanban::getUrl(['ordenId' => $record->id]))
                    ->openUrlInNewTab(), // Abre en una nueva pestaña
                
                // Acción para ir a la página de ingresar resultados
                Action::make('resultados')
                    ->label('Resultados')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    // Solo visible si la orden está en proceso
                    ->visible(fn (Orden $record): bool => $record->estado === 'en proceso')
                    ->url(fn (Orden $record): string => OrdenResource::getUrl('ingresar-resultados', ['record' => $record])),
            ]);
    }
}