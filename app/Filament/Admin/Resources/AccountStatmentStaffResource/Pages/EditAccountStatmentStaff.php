<?php

namespace App\Filament\Admin\Resources\AccountStatmentStaffResource\Pages;

use App\Filament\Admin\Resources\AccountStatmentStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountStatmentStaff extends EditRecord
{
    protected static string $resource = AccountStatmentStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
