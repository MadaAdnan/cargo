<?php

namespace App\Filament\User\Resources\BalabceTRResource\Pages;

use App\Filament\User\Resources\BalabceTRResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalabceTRS extends ListRecords
{
    protected static string $resource = BalabceTRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
