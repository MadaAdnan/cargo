<?php

namespace App\Filament\User\Resources\OrderResource\Pages;

use App\Enums\LevelUserEnum;
use App\Filament\User\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use function Symfony\Component\Translation\t;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;


    protected function beforeCreate(): void
    {
        // Runs before the form fields are saved to the database.

    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (isset($data['cheack2'])) {
            $user = User::where('level', LevelUserEnum::ADMIN)->first();
            $data['receive_id'] = $user->id;
            $data['branch_source_id'] = auth()->user()->branch_id;
            $data['sender_phone'] = auth()->user()->phone;
            $data['sender_address'] = auth()->user()->address;
            $data['city_source_id'] = auth()->user()->city_id;
            $data['sender_id'] = auth()->id();
            $data['code'] = "AWB" . now()->format('YmdHis'); // الطابع الزمني بتنسيق قصير
            unset($data['cheack2']);
            return $data;

        }


        if (isset($data['temp'])) {


            $reciver_id = User::where('iban', $data['temp'])->first();

            if (!$reciver_id->id) {
                Notification::make()->danger()
                    ->title('خطأ')
                    ->body('اسم المستلم غير صحيح')
                    ->send();
                $this->halt();

            } else {
//dd($reciver_id->id) ;

                $data['receive_id'] = $reciver_id->id;
                unset($data['temp']);
                $data['branch_source_id'] = auth()->user()->branch_id;
                $data['sender_phone'] = auth()->user()->phone;
                $data['sender_address'] = auth()->user()->address;
                $data['city_source_id'] = auth()->user()->city_id;
                $data['sender_id'] = auth()->id();

                $data['branch_target_id']=$reciver_id->branch_id;
                $data['city_target_id']=$reciver_id->city_id;


            $data['code'] = "AWB" . now()->format('YmdHis'); // الطابع الزمني بتنسيق قصير
            return $data;
        }
        }

        return [];


    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
