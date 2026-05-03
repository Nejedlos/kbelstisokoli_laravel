<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Schemas\Schema;

class TestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
