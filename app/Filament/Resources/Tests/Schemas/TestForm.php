<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class TestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('data'),
                TextInput::make('comment'),
                FileUpload::make('attachment')
                    ->preserveFilenames(),
            ]);
    }
}
