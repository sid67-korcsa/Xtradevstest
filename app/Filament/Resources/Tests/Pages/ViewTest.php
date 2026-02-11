<?php

namespace App\Filament\Resources\Tests\Pages;

use App\Filament\Resources\Tests\TestResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Resources\Pages\ViewRecord;

class ViewTest extends ViewRecord
{
    protected static string $resource = TestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ViewAction::make(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                'data',
                'comment',
                'attachement'
            ]);
    }
}
