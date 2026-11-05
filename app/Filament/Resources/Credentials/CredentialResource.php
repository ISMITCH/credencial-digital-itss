<?php

namespace App\Filament\Resources\Credentials;

use App\Filament\Resources\Credentials\Pages\CreateCredential;
use App\Filament\Resources\Credentials\Pages\EditCredential;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Filament\Resources\Credentials\Schemas\CredentialForm;
use App\Filament\Resources\Credentials\Tables\CredentialsTable;
use App\Models\Credential;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CredentialResource extends Resource
{
    protected static ?string $model = Credential::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup =
        'Control Escolar';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'credential_number';

    protected static ?string $modelLabel = 'credencial';

    protected static ?string $pluralModelLabel = 'credenciales';

    protected static ?string $navigationLabel = 'Credenciales';

    public static function form(Schema $schema): Schema
    {
        return CredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CredentialsTable::configure($table);
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
            'index' => ListCredentials::route('/'),
            'create' => CreateCredential::route('/create'),
            'edit' => EditCredential::route('/{record}/edit'),
        ];
    }
}