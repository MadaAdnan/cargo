<?php

namespace App\Filament\Admin\Resources\AccountStatmentStaffResource\Pages;

use App\Enums\LevelUserEnum;
use App\Filament\Admin\Resources\AccountStatmentStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAccountStatmentStaff extends ListRecords
{
    protected static string $resource = AccountStatmentStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->whereHas('user',fn($query)=>$query
            ->where('level',LevelUserEnum::ADMIN->value)
            ->orWhere('level',LevelUserEnum::STAFF->value)
            ->orWhere('level',LevelUserEnum::BRANCH->value)

        )->where('is_complete',1)->latest(); // TODO: Change the autogenerated stub
    }
}
