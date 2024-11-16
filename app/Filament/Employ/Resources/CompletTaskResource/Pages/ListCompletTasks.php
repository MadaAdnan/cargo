<?php

namespace App\Filament\Employ\Resources\CompletTaskResource\Pages;

use App\Filament\Employ\Resources\CompletTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompletTasks extends ListRecords
{
    protected static string $resource = CompletTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
