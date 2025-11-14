<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Hash;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    /**
     * Sobrescribimos el formulario para cambiar el campo 'email'
     * por nuestro nuevo campo 'nickname'.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
              
                TextInput::make('name')
                    ->label('Nombre de Usuario') // O "Nickname", "Tagname", etc.
                    ->required()
                    ->autofocus(),
                // ---------------------
                
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/login.form.password.label'))
                    ->password()
                    ->required(),
                
                Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label')),
            ])
            ->statePath('data');
    }

   
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginField = 'name';

        
        return [
            $loginField => $data['name'],
            'password'  => $data['password'],
        ];
    }
    public function authenticate(): LoginResponse|null
    {
        // 1. Obtenemos los datos del formulario
        $data = $this->form->getState();
        $nickname = $data['name'];
        $password = $data['password'];

        // 2. Buscamos al usuario por el nickname
        // ¡OJO! Asegúrate de que 'nickname' sea el nombre correcto de la columna en tu tabla 'users'
        // Si usaste 'tagname', cámbialo aquí.
        $user = User::where('name', $nickname)->first();

        // 3. Verificamos si el usuario existe Y si la contraseña es correcta
        if (! $user || ! Hash::check($password, $user->password)) {
            
            // --- ¡ESTA ES LA LÍNEA QUE FALTABA! ---
            // Lanzamos la excepción que Filament SÍ sabe cómo manejar
            throw ValidationException::withMessages([
                'name' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        // 4. Si todo está bien, iniciamos la sesión
        auth()->login($user, $data['remember'] ?? false);
        
        return app(LoginResponse::class);
    }
    
}
