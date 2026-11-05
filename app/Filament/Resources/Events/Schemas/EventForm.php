<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del evento')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),

                TextInput::make('location')
                    ->label('Lugar')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('event_date')
                    ->label('Fecha del evento')
                    ->required(),

                TimePicker::make('start_time')
                    ->label('Hora de inicio')
                    ->seconds(false)
                    ->required(),

                TimePicker::make('end_time')
                    ->label('Hora de finalización')
                    ->seconds(false)
                    ->after('start_time'),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programado',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('scheduled')
                    ->required(),

                Toggle::make('all_careers')
                    ->label('Permitir todas las carreras')
                    ->helperText(
                        'Actívalo si podrán asistir alumnos de cualquier carrera.'
                    )
                    ->default(true)
                    ->live(),

                Select::make('careers')
                    ->label('Carreras permitidas')
                    ->relationship('careers', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => ! $get('all_careers'))
                    ->hidden(fn (Get $get): bool => $get('all_careers'))
                    ->helperText(
                        'Puedes seleccionar una o varias carreras.'
                    ),

                Toggle::make('all_semesters')
                    ->label('Permitir todos los semestres')
                    ->helperText(
                        'Actívalo si podrán asistir alumnos de cualquier semestre.'
                    )
                    ->default(true)
                    ->live(),

                Repeater::make('eventSemesters')
                    ->label('Semestres permitidos')
                    ->relationship()
                    ->schema([
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
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                    ])
                    ->addActionLabel('Agregar semestre')
                    ->defaultItems(0)
                    ->minItems(1)
                    ->required(fn (Get $get): bool => ! $get('all_semesters'))
                    ->hidden(fn (Get $get): bool => $get('all_semesters'))
                    ->helperText(
                        'Agrega uno o varios semestres permitidos.'
                    ),
            ]);
    }
}