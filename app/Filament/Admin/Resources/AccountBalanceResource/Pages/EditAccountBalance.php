<?php

namespace App\Filament\Admin\Resources\AccountBalanceResource\Pages;

use App\Filament\Admin\Resources\AccountBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountBalance extends EditRecord
{
    protected static string $resource = AccountBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
