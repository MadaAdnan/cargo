<?php

namespace App\Filament\Admin\Resources\CanceledOrderResource\Pages;

use App\Filament\Admin\Resources\CanceledOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCanceledOrder extends EditRecord
{
    protected static string $resource = CanceledOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
