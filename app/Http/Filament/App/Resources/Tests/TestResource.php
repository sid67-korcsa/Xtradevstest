<?php

namespace App\Filament\App\Resources\Tests;

use App\Filament\App\Resources\Tests\Pages\CreateTest;
use App\Filament\App\Resources\Tests\Pages\EditTest;
use App\Filament\App\Resources\Tests\Pages\ListTests;
use App\Filament\App\Resources\Tests\Pages\ViewTest;
use App\Filament\App\Resources\Tests\Schemas\TestForm;
use App\Filament\App\Resources\Tests\Schemas\TestInfolist;
use App\Filament\App\Resources\Tests\Tables\TestsTable;
use App\Models\Test;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Tests';

    protected static ?string $slug = '/test';

    public static function form(Schema $schema): Schema
    {
        return TestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestsTable::configure($table);
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
            'index' => ListTests::route('/'),
            'create' => CreateTest::route('/create'),
            'view' => ViewTest::route('/{record}'),
            'edit' => EditTest::route('/{record}/edit'),
        ];
    }
}
