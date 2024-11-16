<?php

namespace App\Filament\Admin\Resources\RequestExchangeResource\Pages;

use App\Filament\Admin\Resources\RequestExchangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequestExchange extends EditRecord
{
    protected static string $resource = RequestExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
