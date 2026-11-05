<?php

namespace App\Filament\Resources\Credentials\Tables;

use App\Models\Credential;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CredentialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.first_name')
                    ->label('Alumno')
                    ->formatStateUsing(
                        fn (Credential $record): string =>
                            "{$record->student->first_name} {$record->student->paternal_last_name}"
                    )
                    ->searchable(),

                TextColumn::make('credential_number')
                    ->label('Número de credencial')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('qr_token')
                    ->label('Código QR')
                    ->getStateUsing(
                        fn (Credential $record): string =>
                            route(
                                'credential.qr',
                                ['credential' => $record->qr_token]
                            )
                    )
                    ->square()
                    ->size(90),

                TextColumn::make('issued_at')
                    ->label('Fecha de emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Fecha de vencimiento')
                    ->date('d/m/Y')
                    ->placeholder('Sin vencimiento')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'active' => 'Activa',
                            'inactive' => 'Inactiva',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'active' => 'success',
                            'inactive' => 'danger',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
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