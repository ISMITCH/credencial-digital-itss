<?php

namespace App\Filament\Resources\Careers;

use App\Filament\Resources\Careers\Pages\CreateCareer;
use App\Filament\Resources\Careers\Pages\EditCareer;
use App\Filament\Resources\Careers\Pages\ListCareers;
use App\Filament\Resources\Careers\Schemas\CareerForm;
use App\Filament\Resources\Careers\Tables\CareersTable;
use App\Models\Career;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CareerResource extends Resource
{
    protected static ?string $model = Career::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup =
        'Control Escolar';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'carrera';

    protected static ?string $pluralModelLabel = 'carreras';

    protected static ?string $navigationLabel = 'Carreras';

    public static function form(Schema $schema): Schema
    {
        return CareerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CareersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCareers::route('/'),
            'create' => CreateCareer::route('/create'),
            'edit' => EditCareer::route('/{record}/edit'),
        ];
    }
}