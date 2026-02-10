<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class TestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('data'),
                TextEntry::make('comment'),
                TextEntry::make('modify'),
            ]);
    }
}
