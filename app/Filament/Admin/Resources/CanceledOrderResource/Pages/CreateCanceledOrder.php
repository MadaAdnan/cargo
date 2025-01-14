<?php

namespace App\Filament\Admin\Resources\CanceledOrderResource\Pages;

use App\Filament\Admin\Resources\CanceledOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCanceledOrder extends CreateRecord
{
    protected static string $resource = CanceledOrderResource::class;
}
