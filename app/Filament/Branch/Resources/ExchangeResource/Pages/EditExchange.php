<?php

namespace App\Filament\Branch\Resources\ExchangeResource\Pages;

use App\Filament\Branch\Resources\ExchangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExchange extends EditRecord
{
    protected static string $resource = ExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
