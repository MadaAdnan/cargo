<?php

namespace App\Filament\Admin\Resources\AccountStatmentStaffResource\Pages;

use App\Filament\Admin\Resources\AccountStatmentStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountStatmentStaff extends ListRecords
{
    protected static string $resource = AccountStatmentStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
