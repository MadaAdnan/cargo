<?php

namespace App\Filament\Admin\Resources\SuccessOrderResource\Pages;

use App\Enums\LevelUserEnum;
use App\Filament\Admin\Resources\SuccessOrderResource;
use App\Models\City;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSuccessOrder extends CreateRecord
{
    protected static string $resource = SuccessOrderResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {


        $city_source=City::find($data['city_source_id']);
        $city_target=City::find($data['city_target_id']);

        $data['branch_source_id'] =$city_source->branch_id;
        $data['branch_target_id'] =$city_target->branch_id;
        $target=User::where('level',LevelUserEnum::BRANCH->value)->where('branch_id',$data['branch_target_id'] )->first();
        /* $data['given_id']=$target?->id;
         $data['status']=OrderStatusEnum::TRANSFER->value;*/
        $data['code'] = "AWB" . now()->format('YmdHis'); // الطابع الزمني بتنسيق قصير
        // $data['shipping_date'] = now()->format('Y-m-d');

        return $data;



    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
