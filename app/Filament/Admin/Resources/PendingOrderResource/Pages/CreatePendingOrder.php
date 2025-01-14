<?php

namespace App\Filament\Admin\Resources\PendingOrderResource\Pages;

use App\Filament\Admin\Resources\PendingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingOrder extends CreateRecord
{
    protected static string $resource = PendingOrderResource::class;
}
