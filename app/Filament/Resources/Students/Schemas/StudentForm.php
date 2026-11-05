<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('career_id')
                    ->label('Carrera')
                    ->relationship('career', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                TextInput::make('student_number')
                    ->label('Matrícula')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->live(debounce: 500),

                TextInput::make('first_name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 500),

                TextInput::make('paternal_last_name')
                    ->label('Apellido paterno')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 500),

                TextInput::make('maternal_last_name')
                    ->label('Apellido materno')
                    ->maxLength(255)
                    ->live(debounce: 500),

                TextInput::make('institutional_email')
                    ->label('Correo institucional')
                    ->email()
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

                Select::make('semester')
                    ->label('Semestre')
                    ->options([
                        1 => '1.er semestre',
                        2 => '2.º semestre',
                        3 => '3.er semestre',
                        4 => '4.º semestre',
                        5 => '5.º semestre',
                        6 => '6.º semestre',
                        7 => '7.º semestre',
                        8 => '8.º semestre',
                        9 => '9.º semestre',
                    ])
                    ->required()
                    ->live(),

                TextInput::make('admission_year')
                    ->label('Año de ingreso')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue((int) date('Y'))
                    ->required(),

                FileUpload::make('photo_path')
                    ->label('Fotografía')
                    ->image()
                    ->imagePreviewHeight('120')
                    ->panelLayout('compact')
                    ->directory('students/photos')
                    ->live(),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])
                    ->default('active')
                    ->required()
                    ->live(),

                Section::make('Vista previa de la credencial')
                    ->description(
                        'La tarjeta se actualiza con los datos capturados en el formulario.'
                    )
                    ->schema([
                        ViewField::make('credential_preview')
                            ->view(
                                'filament.resources.students.credential-preview'
                            )
                            ->hiddenLabel()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}