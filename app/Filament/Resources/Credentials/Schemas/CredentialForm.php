<?php

namespace App\Filament\Resources\Credentials\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Alumno')
                    ->relationship('student', 'student_number')
                    ->getOptionLabelFromRecordUsing(
                        fn (Student $record): string =>
                            "{$record->student_number} - {$record->first_name} {$record->paternal_last_name}"
                    )
                    ->searchable([
                        'student_number',
                        'first_name',
                        'paternal_last_name',
                    ])
                    ->preload()
                    ->unique(ignoreRecord: true)
                    ->required(),

                TextInput::make('credential_number')
                    ->label('Número de credencial')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),

                DatePicker::make('issued_at')
                    ->label('Fecha de emisión')
                    ->default(now())
                    ->required(),

                DatePicker::make('expires_at')
                    ->label('Fecha de vencimiento')
                    ->afterOrEqual('issued_at'),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}