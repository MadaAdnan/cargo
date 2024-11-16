<?php

namespace App\Filament\Branch\Resources\UsersResource\Pages;

use App\Filament\Branch\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;
}
