<?php

namespace App\Filament\Resources\Tests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")->searchable()->sortable(),
                TextColumn::make("created_at")->sortable(),
                TextColumn::make("updated_at")->sortable(),
                TextColumn::make("data")->color('primary')->searchable()->sortable(),
                TextColumn::make("comment")->searchable()->sortable(),
                TextColumn::make("attachment"),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
