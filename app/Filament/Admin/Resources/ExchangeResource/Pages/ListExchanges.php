<?php

namespace App\Filament\Admin\Resources\ExchangeResource\Pages;

use App\Filament\Admin\Resources\ExchangeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExchanges extends ListRecords
{
    protected static string $resource = ExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
