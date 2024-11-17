<?php

namespace App\Filament\Branch\Resources\BalabceTRResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Branch\Resources\BalabceTRResource;
use App\Models\Balance;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListBalabceTRS extends ListRecords
{
    protected static string $resource = BalabceTRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            /**
             * Add credit
             */
            Actions\Action::make('create_balance_debit')
                ->form([
                    Grid::make(3)->schema([
                        Select::make('user_id')->options(User::get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                            ->label('المستخدم'),
                        TextInput::make('value')->required()->numeric()->label('القيمة'),
                        TextInput::make('info')->label('بيان'),
                    ])
                ])
                //
                ->action(function ($data) {
                    \DB::beginTransaction();
//                    if(auth()->user()->total_balance_tr  < $data['value'] ){
//                        Notification::make('error')->title('فشل العملية')->body('لا تملك رصيد كافي')->danger()->send();
//                        return ;
//                    }
                    $target=User::find($data['user_id']);
                    try {

                        Balance::create([
                            'type'=>BalanceTypeEnum::PUSH->value,
                            'user_id'=>$data['user_id'],
                            'debit'=>0,
                            'credit'=>$data['value'],
                            'info'=>$data['info'],
                            'is_complete'=>true,
                            'currency_id'=>2,
                            'customer_name'=>auth()->user()->name,
                        ]);
                        Balance::create([
                            'type'=>BalanceTypeEnum::CATCH->value,
                            'user_id'=>auth()->id(),
                            'debit'=>$data['value'],
                            'credit'=>0,
                            'customer_name'=>$target->name,
                            'info'=>$data['info'],
                            'is_complete'=>true,
                            'currency_id'=>2,
                        ]);

                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                    }

                })
                ->label('إضافة سند دفع'),
        ];
    }
protected function getTableQuery(): ?Builder
{
    return Balance::where('user_id', auth()->id())->where('currency_id', 2)
        ->with(['order' => fn($query) => $query->with('sender', 'sender')])
        ->latest(); // TODO: Change the autogenerated stub
}

}