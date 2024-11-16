<?php

namespace App\Filament\Branch\Resources\PendingTaskResource\Pages;

use App\Filament\Branch\Resources\PendingTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingTask extends CreateRecord
{
    protected static string $resource = PendingTaskResource::class;
}
