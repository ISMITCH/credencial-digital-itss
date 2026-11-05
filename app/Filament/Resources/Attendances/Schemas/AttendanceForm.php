<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

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
                    ->required(),

                Select::make('credential_id')
                    ->label('Credencial utilizada')
                    ->relationship('credential', 'credential_number')
                    ->searchable()
                    ->preload()
                    ->helperText('Selecciona la credencial que pertenece al alumno.'),

                DateTimePicker::make('registered_at')
                    ->label('Fecha y hora del registro')
                    ->default(now())
                    ->seconds(false)
                    ->required(),

                Select::make('registration_method')
                    ->label('Método de registro')
                    ->options([
                        'manual' => 'Manual',
                        'qr' => 'Código QR',
                    ])
                    ->default('manual')
                    ->required(),

                Select::make('status')
                    ->label('Estado de asistencia')
                    ->options([
                        'present' => 'Presente',
                        'late' => 'Retardo',
                        'justified' => 'Justificada',
                    ])
                    ->default('present')
                    ->required(),

                Textarea::make('notes')
                    ->label('Observaciones')
                    ->rows(3),
            ]);
    }
}