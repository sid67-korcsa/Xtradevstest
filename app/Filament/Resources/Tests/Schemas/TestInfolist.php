<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Storage;


class TestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('data'),
                TextEntry::make('comment'),
                TextEntry::make('modify'),
                Action::make('download')
                    ->label('Letoltes')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        return Storage::disk('public')->download($record->attachment);
                    })
                //TextEntry::make('attachment'),
            ]);
    }
}
