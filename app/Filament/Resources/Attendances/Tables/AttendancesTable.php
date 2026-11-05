<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.student_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.first_name')
                    ->label('Alumno')
                    ->formatStateUsing(
                        fn (Attendance $record): string =>
                            "{$record->student->first_name} {$record->student->paternal_last_name}"
                    )
                    ->searchable(),

                TextColumn::make('credential.credential_number')
                    ->label('Credencial')
                    ->placeholder('Registro manual'),

                TextColumn::make('registered_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('registration_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => 'Manual',
                        'qr' => 'Código QR',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Asistencia')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Presente',
                        'late' => 'Retardo',
                        'justified' => 'Justificada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'justified' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('registeredBy.name')
                    ->label('Registrado por')
                    ->placeholder('Sistema'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
     }
}