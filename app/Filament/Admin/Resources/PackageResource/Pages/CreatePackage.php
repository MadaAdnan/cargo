<?php

namespace App\Filament\Admin\Resources\PackageResource\Pages;

use App\Filament\Admin\Resources\PackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {


// $data['code'] = "FC" . now()->format('dHis'); // الطابع الزمني بتنسيق قصير

        return $data;

    }
}
