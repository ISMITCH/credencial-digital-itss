<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Fotografía')
                    ->circular(),

                TextColumn::make('student_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('paternal_last_name')
                    ->label('Apellido paterno')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('maternal_last_name')
                    ->label('Apellido materno')
                    ->searchable(),

                TextColumn::make('career.name')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semestre')
                    ->sortable(),

                    TextColumn::make('admission_year')
    ->label('Año de ingreso')
    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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