<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\OrderResource;
use App\Helper\HelperBalance;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {


        $city_source=City::find($data['city_source_id']);
        $city_target=City::find($data['city_target_id']);
        $data['branch_source_id'] =$city_source->branch_id;
        $data['branch_target_id'] =$city_target->branch_id;
        $data['code'] = "AWB" . now()->format('YmdHis'); // الطابع الزمني بتنسيق قصير
        $data['shipping_date'] = now()->format('Y-m-d');

        return $data;



    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }



}
