<?php

namespace App\Filament\Admin\Resources\PendingOrderResource\Pages;

use App\Filament\Admin\Resources\PendingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingOrder extends EditRecord
{
    protected static string $resource = PendingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
