<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->autocomplete('off'),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->required(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    )
                    ->dehydrated(
                        fn (?string $state): bool =>
                            filled($state)
                    ),

                Select::make('role')
                    ->label('Tipo de usuario')
                    ->options([
                        'administrator' => 'Administrador',
                        'control_school' => 'Control Escolar',
                        'academic_division' => 'División Académica',
                    ])
                    ->default('control_school')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Usuario activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}