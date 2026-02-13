<?php

namespace App\Filament\Resources\Tests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")->searchable()->sortable()->summarize(Average::make()),
                TextColumn::make("created_at")->sortable(),
                TextColumn::make("updated_at")->sortable(),
                TextColumn::make("data")->color('primary')->searchable()->sortable(),
                TextColumn::make("comment")->searchable()->sortable(),
                TextColumn::make("attachment"),
                IconColumn::make('active')
                    ->color('success')
                /*IconColumn::make('active')
                    ->icon(fn (string $state): Heroicon => match (true) {
                        0 => Heroicon::OutlinedPencil,
                        1 => Heroicon::OutlinedClock,
                        default => throw new \Exception('Unexpected match value')
                    })*/
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
