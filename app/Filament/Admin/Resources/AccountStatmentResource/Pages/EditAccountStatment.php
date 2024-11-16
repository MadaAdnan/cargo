<?php

namespace App\Filament\Admin\Resources\AccountStatmentResource\Pages;

use App\Filament\Admin\Resources\AccountStatmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountStatment extends EditRecord
{
    protected static string $resource = AccountStatmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
