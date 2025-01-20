<?php

namespace App\Filament\Admin\Resources\SuccessOrderResource\Pages;

use App\Filament\Admin\Resources\SuccessOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuccessOrder extends EditRecord
{
    protected static string $resource = SuccessOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
