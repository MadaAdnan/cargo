<?php

namespace App\Filament\Admin\Resources\AreaResource\Pages;

use App\Filament\Admin\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArea extends CreateRecord
{


    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['is_main'] = false;
        return $data;

    }

    protected static string $resource = AreaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
