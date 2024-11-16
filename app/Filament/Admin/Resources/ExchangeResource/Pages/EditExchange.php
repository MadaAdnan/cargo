<?php

namespace App\Filament\Admin\Resources\ExchangeResource\Pages;

use App\Filament\Admin\Resources\ExchangeResource;
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
