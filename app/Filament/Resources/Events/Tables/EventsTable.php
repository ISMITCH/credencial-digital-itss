<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('careers_list')
                    ->label('Carreras permitidas')
                    ->getStateUsing(function (Event $record): string {
                        if ($record->all_careers) {
                            return 'Todas las carreras';
                        }

                        $careers = $record->careers
                            ->pluck('code')
                            ->filter()
                            ->implode(', ');

                        return $careers !== ''
                            ? $careers
                            : 'Sin carreras seleccionadas';
                    })
                    ->wrap(),

                TextColumn::make('semesters_list')
                    ->label('Semestres permitidos')
                    ->getStateUsing(function (Event $record): string {
                        if ($record->all_semesters) {
                            return 'Todos los semestres';
                        }

                        $semesters = $record->eventSemesters
                            ->pluck('semester')
                            ->sort()
                            ->map(function ($semester): string {
                                return match ((int) $semester) {
                                    1 => '1.er',
                                    3 => '3.er',
                                    default => $semester . '.º',
                                };
                            })
                            ->implode(', ');

                        return $semesters !== ''
                            ? $semesters
                            : 'Sin semestres seleccionados';
                    })
                    ->wrap(),

                TextColumn::make('location')
                    ->label('Lugar')
                    ->searchable(),

                TextColumn::make('event_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Hora de inicio')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Hora de finalización')
                    ->time('H:i')
                    ->placeholder('Sin definir'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Programado',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
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