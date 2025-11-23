<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Illuminate\Validation\ClosureValidationRule;
use PhpParser\Node\Stmt\Label;
use Savannabits\Filament\BladeField\Forms\Components\BladeField;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\Infolist;

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
    
    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Perfiles';
    //nombre plural
    protected static ?string $pluralModelLabel = 'Perfiles';
    //nombre singular
    protected static ?string $modelLabel = 'Perfil';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
          // Obtener el ID de la ruta
    $recordId = request()->route('record'); // Esto debería ser la forma correcta de obtener la ruta

    // Validar y castear el ID a un entero
    $perfilId = is_numeric($recordId) ? (int) $recordId : null;

    // Registrar en logs
    logger('Record ID:', ['recordId' => $recordId]);
    logger('Perfil ID:', ['perfilId' => $perfilId]);
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
                   



                

                       // Tu sección especial de exámenes
            Section::make('Exámenes')
            ->schema([
                ViewField::make('examenes_drag_drop')
                    ->view('partials.embed-livewire-examenes')
                    ->columnSpanFull()
                    ->viewData([
                        'perfilId' => $perfilId, // Pasamos el valor calculado aquí
                    ]),
            ]),

                    Forms\Components\Textarea::make('examenes_seleccionados')
                    ->rows(5)
                    ->label(' ')
                    ->default('[]')
                    ->reactive()

                    ->rules([
                        new ClosureValidationRule(function ($attribute, $value, $fail) {
                            $count = count(json_decode($value, true) ?? []);
                            if ($count < 2) {
                                $fail('Debes seleccionar al menos 2 exámenes. Actualmente tienes ' . $count . '.');
                            }
                        })
                    ])

                    ->afterStateUpdated(function ($state, $set) {
                        $decoded = json_decode($state, true) ?? [];
                        $set('examenes_seleccionados', json_encode(array_values($decoded)));
                    })
                    ->extraAttributes(['style' => 'display:none']),



                

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('toggleEstado')
                ->label(fn ($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                ->icon(fn ($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn ($record) => $record->estado ? 'danger' : 'success')
                ->visible(fn () => auth()->user()->can('cambiar_estado_perfiles'))
                ->tooltip(fn ($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                ->action(function ($record) {
                    $record->estado = $record->estado ? 0 : 1;
                    $record->save();
            
                    Notification::make()
                        ->title('Estado actualizado')
                        ->body('El perfil fue ' . ($record->estado ? 'activado' : 'dado de baja') . ' correctamente.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->iconButton(),
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
    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
      return $infolist
    ->schema([
       
        TextEntry::make('nombre_perfil')
            ->label('Nombre del Perfil')
            ->getStateUsing(fn($record) => $record->nombre),
        
        TextEntry::make('precio_perfil')
            ->label('Precio')
            ->getStateUsing(fn($record) => $record->precio)
            ->money('USD')
            ->color('success')
            ->extraAttributes(['class' => 'text-lg font-bold']),
        
        IconEntry::make('estado_perfil')
            ->label('Estado')
            ->getStateUsing(fn($record) => $record->estado)
            ->boolean(),
        
        ViewEntry::make('examenes')
            ->view('filament.resources.perfil-resource.partials.examenes')
            ->columnSpanFull()
            ->getStateUsing(function ($record) {
                return $record->examenes->map(fn ($examen) => [
                    'id' => $examen->id,
                    'nombre' => $examen->nombre,
                    'tipo' => $examen->tipoExamen->nombre ?? 'Sin tipo',
                ])->toArray();
            }),
    ]);

    }
    
    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with('examenes.tipoExamen');
}

    
}