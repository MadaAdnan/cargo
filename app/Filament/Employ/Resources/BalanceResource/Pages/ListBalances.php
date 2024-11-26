<?php

namespace App\Filament\Employ\Resources\BalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Enums\LevelUserEnum;
use App\Filament\Employ\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBalances extends ListRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add')->form([
                Placeholder::make('type')->dehydrated(false)->content('سند دفع'),

                TextInput::make('value')->label('القيمة')->numeric()->required()
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value <= 0) {
                                $fail('يجب أن تكون القيمة أكبر من 0');
                            }
//                            if (auth()->user()->total_balance < $value) {
//                                $fail('لا تملك رصيد كافي');
//                            }
                        },
                    ]),


                Select::make('user_id')->options(User::pluck('name', 'id'))->searchable()->label('الطرف الثاني في القيد')->required(),
                TextInput::make('customer_name')->label('اسم المستلم'),
                TextInput::make('info')->label('ملاحظات')
            ])
                ->action(function ($data) {
                    $user = User::find($data['user_id']);
                    if (!$user) {
                        Notification::make('success')->title('فشل العملية')->body('لم يتم العثور على المستخدم')->danger()->send();

                        return;
                    }

//                    if (auth()->user()->total_balance < $data['value']) {
//                        Notification::make('success')->title('فشل العملية')->body('لا تملك رصيد كافي')->danger()->send();
//
//                        return;
//                    }

                    $customer=User::find( $data['user_id']);
                    \DB::beginTransaction();
                    try {
                        Balance::create([
                            'credit' => 0,
                            'debit' => $data['value'],
                            'type' => BalanceTypeEnum::PUSH->value,
                            'is_complete' => true,
                            'user_id' => auth()->id(),
                            'currency_id' => 1,
                            'info' => $data['info'],
                            'customer_name' => $user?->name,

                        ]);

                      $balance=  Balance::create([
                            'credit' => $data['value'],
                            'debit' => 0,
                            'type' => BalanceTypeEnum::CATCH->value,
                            'is_complete' => $customer?->level==LevelUserEnum::USER,
                            'user_id' => $data['user_id'],
                            'currency_id' => 1,
                            'info' => $data['info'],
                            'customer_name' => auth()->user()->name,

                        ]);
                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السند')->success()->send();
                        $this->redirect(BalanceResource::getUrl('view',['record'=>$balance->id]));
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('success')->title('فشل العملية')->body('لم يتم إضافة السند')->danger()->send();

                    }

                })->label('إضافة سند'),
            Actions\Action::make('create_balance_debit')
                ->form([
                    Grid::make(3)->schema([
                        Select::make('user_id')->options(User::where('level',LevelUserEnum::USER->value)->get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                            ->label('المستخدم'),
                        TextInput::make('value')->required()->numeric()->label('القيمة'),
                        TextInput::make('info')->label('بيان'),
                    ])
                ])
                //
                ->action(function ($data) {
                    \DB::beginTransaction();
                    if($data['value'] < 0 ){
                        Notification::make('error')->title('فشل العملية')->body('ادخل رصيد صحيح')->danger()->send();
                        return ;
                    }
                    $target=User::find($data['user_id']);
                    try {

                        Balance::create([
                            'type'=>BalanceTypeEnum::PUSH->value,
                            'user_id'=>$data['user_id'],
                            'debit'=>$data['value'],
                            'credit'=>0,
                            'info'=>$data['info'],
                            'is_complete'=>true,
                            'currency_id'=>1,
                            'customer_name'=>auth()->user()->name,
                        ]);
                        $balance= Balance::create([
                            'type'=>BalanceTypeEnum::CATCH->value,
                            'user_id'=>auth()->id(),
                            'debit'=>0,
                            'credit'=>$data['value'],
                            'customer_name'=>$target->name,
                            'info'=>$data['info'],
                            'is_complete'=>true,
                            'currency_id'=>1,
                        ]);

                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم إضافة السندات بنجاح')->success()->send();
                        $this->redirect(BalanceResource::getUrl('view',['record'=>$balance->id]));
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                    }

                })
                ->label('إضافة سند قبض'),

            Actions\Action::make('quid')->form([
                TextInput::make('amount')->label('القيمة')->required()->numeric(),
                TextInput::make('info')->label('البيان')
            ])
                //
                ->action(function ($data) {
                \DB::beginTransaction();
                try {
                    if($data['amount']<=0){
                        throw  new \Exception('لا يمكن إضافة قيمة أقل من 0');
                    }
//                    if(auth()->user()->total_balance<$data['amount']){
//                        throw  new \Exception('لا تملك رصيد كافي');
//                    }
                    $uuid = \Str::uuid();
                    Balance::create([
                        'user_id' => auth()->id(),
                        'uuid' => $uuid,
                        'debit' => $data['amount'],
                        'credit'=>0,
                        'currency_id' => 1,
                        'pending' => false,
                        'is_complete' => true,
                        'customer_name' => 'حساب مصاريف',
                        'info' => $data['info'],
                    ]);
                    Balance::create([
                        'user_id' => 908,
                        'uuid' => $uuid,
                        'debit' => 0,
                        'credit'=>$data['amount'],
                        'currency_id' => 1,
                        'pending' => false,
                        'is_complete' => false,
                        'customer_name' => auth()->user()->name,
                        'info' => $data['info'],
                    ]);
                    \DB::commit();
                    Notification::make('success')->success()->title('نجاح العملية')->body('تم إضافة المصاريف')->send();
                } catch (\Exception | \Error $e) {
                    \DB::rollBack();
                    Notification::make('error')->danger()->title('فشلت العملية')->body($e->getMessage())->send();
                }
            })->label('إضافة مصاريف'),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return Balance::where('user_id', auth()->id())->where('currency_id', 1)
            ->with(['order' => fn($query) => $query->with('sender', 'sender')])
            ->latest(); // TODO: Change the autogenerated stub
    }
}
