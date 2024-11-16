<?php

namespace App\Filament\Employ\Resources\ExchangeResource\Pages;

use App\Filament\Employ\Resources\ExchangeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExchange extends CreateRecord
{
    protected static string $resource = ExchangeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return parent::mutateFormDataBeforeCreate($data); // TODO: Change the autogenerated stub
    }
}
