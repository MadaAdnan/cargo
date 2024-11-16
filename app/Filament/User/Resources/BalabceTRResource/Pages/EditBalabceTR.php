<?php

namespace App\Filament\User\Resources\BalabceTRResource\Pages;

use App\Filament\User\Resources\BalabceTRResource;
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
