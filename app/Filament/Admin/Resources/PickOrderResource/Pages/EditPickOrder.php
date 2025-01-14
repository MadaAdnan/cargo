<?php

namespace App\Filament\Admin\Resources\PickOrderResource\Pages;

use App\Filament\Admin\Resources\PickOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPickOrder extends EditRecord
{
    protected static string $resource = PickOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
