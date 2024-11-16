<?php

namespace App\Filament\Employ\Resources\BalabceTRResource\Pages;

use App\Filament\Employ\Resources\BalabceTRResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBalabceTR extends EditRecord
{
    protected static string $resource = BalabceTRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
