<?php

namespace App\Filament\Resources;
use Savannabits\Filament\BladeField\Forms\Components\BladeField;
use Filament\Forms\Components\ViewField;

use App\Filament\Resources\PerfilResource\Pages;
use App\Filament\Resources\PerfilResource\RelationManagers;
use App\Models\Examen;
use App\Models\Perfil;
use App\Models\TipoExamen;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;
    //definiendo el nombre de la opción en el menu
    protected static ?string $navigationLabel = 'Perfiles';
    //nombre plural
    protected static ?string $pluralModelLabel = 'Perfiles';
    //nombre singular
    protected static ?string $modelLabel = 'Perfil';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Forms\Components\Toggle::make('estado')
                    ->label('Estado')
                    ->default(true),

                    Forms\Components\Hidden::make('examenes_seleccionados')
                    ->default('[]'), // Valor inicial como array vacío
              
               
        // Vista para los exámenes
        Forms\Components\ViewField::make('examenes_drag_drop')
        ->view('partials.embed-livewire-examenes')
        ->columnSpanFull()
        ->extraAttributes(['class' => 'bg-transparent p-0']),

                
                
            
         
      
         




                //hacer que el campo ocupe todo el ancho
               /* Forms\Components\TextInput::make('buscador_tipo')
                    ->label('Buscar tipo de examen')
                    ->placeholder('Escribe el nombre del tipo...')
                    ->reactive()
                    ->columnSpanFull()
                    ->afterStateUpdated(fn($state, callable $set) => $set('buscador_tipo', strtolower($state))),


                ...TipoExamen::with('examenes')->get()->map(function ($tipo) {
                    return Forms\Components\Section::make($tipo->nombre)
                        ->schema([
                            Forms\Components\CheckboxList::make('examenes_seleccionados_' . $tipo->id)
                                ->label(false)
                                ->options(function (callable $get) use ($tipo) {
                                    $busqueda = strtolower($get('buscador_tipo'));
                                    $examenes = $tipo->examenes()
                                        ->where('nombre', 'like', '%' . $busqueda . '%') // Filtro por el nombre
                                        ->get(); // Obtiene solo los exámenes que coinciden con la búsqueda
                    
                                    return $examenes->mapWithKeys(function ($examen) use ($busqueda) {
                                        $coincide = str_contains(strtolower($examen->nombre), $busqueda);
                                        $nombreEstilizado = $coincide
                                            ? '🔴 ' . $examen->nombre // Usa un emoji para destacar
                                            : $examen->nombre;

                                        return [$examen->id => $nombreEstilizado];
                                    });
                                })
                                //Habilitar HTML en las opciones
                                ->searchable()
                                ->columns(2),
                        ])
                        ->collapsible()
                        ->visible(function (callable $get) use ($tipo) {
                            $busqueda = strtolower($get('buscador_tipo'));

                            // Mostrar si no hay búsqueda, o si el tipo coincide, o algún examen coincide
                            return blank($busqueda)
                                || str_contains(strtolower($tipo->nombre), $busqueda)
                                || $tipo->examenes->contains(function ($examen) use ($busqueda) {
                                return str_contains(strtolower($examen->nombre), $busqueda);
                            });
                        });


                })->toArray(),
*/

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('precio')->money('USD')
                    ->color('success')->extraAttributes(['class' => 'text-lg font-bold']),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(function ($state) {
                        return $state
                            ? '✅ Activo'
                            : '❌ Inactivo';
                    })
                    ->badge() // opcional para que se vea como etiqueta
                    ->color(fn($state) => $state ? 'success' : 'danger'),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerfils::route('/'),
            'create' => Pages\CreatePerfil::route('/create'),
            'edit' => Pages\EditPerfil::route('/{record}/edit'),
        ];
    }
}
