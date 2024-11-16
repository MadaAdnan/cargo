<?php

namespace App\Filament\Branch\Resources\PendingTaskResource\Pages;

use App\Filament\Branch\Resources\PendingTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingTask extends EditRecord
{
    protected static string $resource = PendingTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
