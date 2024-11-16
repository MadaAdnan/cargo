<?php

namespace App\Filament\User\Resources\BalanceResource\Pages;

use App\Filament\User\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBalance extends EditRecord
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
