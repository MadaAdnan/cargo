<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\City;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;


    public function mount($record): void
    {
        $user = User::findOrFail($record);
        // abort_if($user->user_id!=auth()->id()&& !auth()->user()->hasRole('super_admin'),404);
        parent::mount($record); // TODO: Change the autogenerated stub
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return array_merge(parent::getHeaderWidgets(), [UserResource\Widgets\BalanceView::class]); // TODO: Change the autogenerated stub
    }


    protected function mutateFormDataBeforeFill(array $data): array
    {

        if (isset($data['phone'])) {
            $temp =ltrim($data['phone'],'+');
            $data['country_code'] = substr($temp, 0, strpos($temp, ' ') ?: 3);

            // استخرج الرمز الدولي

            $data['phone_number'] = substr($data['phone'], strlen($data['country_code'])); // استخرج الرقم الفعلي

        }

        return $data;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        $temp = City::where('id', $data['city_id'])->pluck('branch_id')->first();



        $data['phone'] = $data['country_code'] . $data['phone_number'];
        unset($data['country_code'], $data['phone_number']); // حذف الحقول المنفصلة بعد الجمع


        return $data;


    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}