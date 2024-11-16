<?php

namespace App\Filament\Employ\Resources\PendingTaskResource\Pages;

use App\Filament\Employ\Resources\PendingTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingTask extends CreateRecord
{
    protected static string $resource = PendingTaskResource::class;
}
