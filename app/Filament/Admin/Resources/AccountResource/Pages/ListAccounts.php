<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\AccountResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('quid_usd')->form([
                Select::make('source_id')->options(User::WithAccount()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('من حساب')->required(),
                Select::make('target_id')->options(User::WithAccount()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('إلى حساب')->required(),
                TextInput::make('amount')->required()->numeric()->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        if ($value <= 0) {
                            $fail('يجب ان تكون القيمة أكبر من 0');
                        }
                    },
                ])->required()->label('القيمة'),
                TextInput::make('info')->label('ملاحظات')
            ])
                ->action(function ($data) {
                    DB::beginTransaction();
                    try {
                        $uuid = \Str::uuid();
                        Balance::create([
                            'user_id' => $data['target_id'],
                            'currency_id' => 1,
                            'pending' => false,
                            'is_complete' => true,
                            'info' => $data['info'],
                            'uuid' => $uuid,
                            'type' => BalanceTypeEnum::PUSH->value,
                            'credit' => $data['amount'],
                            'debit' => 0,
                            'customer_name' => User::find($data['source_id'])?->name,
                        ]);

                        Balance::create([
                            'user_id' => $data['source_id'],
                            'currency_id' => 1,
                            'pending' => false,
                            'is_complete' => true,
                            'info' => $data['info'],
                            'uuid' => $uuid,
                            'type' => BalanceTypeEnum::CATCH->value,
                            'credit' => 0,
                            'debit' => $data['amount'],
                            'customer_name' => User::find($data['target_id'])?->name,
                        ]);

                        DB::commit();
                        Notification::make('error')->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                    } catch (\Exception | \Error $e) {
                        DB::rollBack();
                        Notification::make('error')->danger()->title('خطأ في العملية')->body($e->getMessage())->send();
                    }
                })->label('سند قيدUSD'),
            Actions\Action::make('quid_try')->form([
                Select::make('source_id')->options(User::hideGlobal()->active()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('من حساب')->required(),
                Select::make('target_id')->options(User::active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('إلى حساب')->required(),
                TextInput::make('amount')->required()->numeric()->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        if ($value <= 0) {
                            $fail('يجب ان تكون القيمة أكبر من 0');
                        }
                    },
                ])->required()->label('القيمة'),
                TextInput::make('info')->label('ملاحظات')
            ])
                ->action(function ($data) {
                    DB::beginTransaction();
                    try {
                        $uuid = \Str::uuid();
                        Balance::create([
                            'user_id' => $data['target_id'],
                            'currency_id' => 2,
                            'pending' => false,
                            'is_complete' => true,
                            'info' => $data['info'],
                            'uuid' => $uuid,
                            'type' => BalanceTypeEnum::PUSH->value,
                            'credit' => $data['amount'],
                            'debit' => 0,
                            'customer_name' => User::find($data['source_id'])?->name,
                        ]);

                        Balance::create([
                            'user_id' => $data['source_id'],
                            'currency_id' => 2,
                            'pending' => false,
                            'is_complete' => true,
                            'info' => $data['info'],
                            'uuid' => $uuid,
                            'type' => BalanceTypeEnum::CATCH->value,
                            'credit' => 0,
                            'debit' => $data['amount'],
                            'customer_name' => User::find($data['target_id'])?->name,
                        ]);

                        DB::commit();
                        Notification::make('error')->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                    } catch (\Exception | \Error $e) {
                        DB::rollBack();
                        Notification::make('error')->danger()->title('خطأ في العملية')->body($e->getMessage())->send();
                    }
                })->label('سند قيدTRY'),
            Actions\Action::make('multi_Tr')->form([
               /*Grid::make()->schema([
                   DatePicker::make('date'),
               ]),*/
                Repeater::make('balances')->schema([
                    Grid::make(3)->schema([
                        Select::make('credit_id')->options(User::withAccount()->pluck('name', 'id'))->searchable()->required()->label('الحساب مدين'),
                        TextInput::make('credit_info')->label('البيان'),
                        TextInput::make('credit')->label('مدين')->default(0)->numeric(),


                    ]),
                    Grid::make(3)->schema([
                        Select::make('debit_id')->options(User::withAccount()->pluck('name', 'id'))->searchable()->required()->label('الحساب دائن'),
                        TextInput::make('debit_info')->label('البيان'),
                        TextInput::make('debit')->label('دائن')->default(0)->numeric(),
                    ])
                ])->label('قيد متعدد TR')
                    ->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        $credit = 0;
                        $debit = 0;
                        foreach ($value as $item) {


                            $debit = $item['debit'];
                            $credit = $item['credit'];
                            if ($credit != $debit) {
                                $fail(" القيد غير متوازن");
                                break;
                            }
                        }

                    }

                ])
            ])
                ->action(function ($data) {
                \DB::beginTransaction();
                try{
                    $uuid=\Str::uuid();
                    $currency=2;//TR
                    foreach ($data['balances'] as $item){
                        if($item['credit']==0 && $item['debit']==0){
                            continue;
                        }
                        $creditUser=User::find($item['credit_id']);
                        $debitUser=User::find($item['debit_id']);
                        Balance::create([
                            'uuid'=>$uuid,
                            'currency_id'=>$currency,
                            'debit'=>0,
                            'credit'=>$item['credit'],
                            'customer_name'=>$debitUser->name,
                            'info'=>$item['credit_info'],
                            'user_id'=>$creditUser->id,
                            'pending'=>false,
                            'is_complete'=>true,
                        ]);

                        Balance::create([
                            'uuid'=>$uuid,
                            'currency_id'=>$currency,
                            'debit'=>$item['debit'],
                            'credit'=>0,
                            'customer_name'=>$creditUser->name,
                            'info'=>$item['credit_info'],
                            'user_id'=>$debitUser->id,
                            'pending'=>false,
                            'is_complete'=>true,
                        ]);
                    }

                    DB::commit();
                    Notification::make()->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                }catch (\Exception|\Error $e){

                    DB::rollBack();

                }
            })->label('سند تركي متعدد'),
            Actions\Action::make('multi_Usd')
                ->form([
                    /*Grid::make()->schema([
                        DatePicker::make('date'),
                    ]),*/
                    Repeater::make('balances')->schema([
                        Grid::make(3)->schema([
                            Select::make('credit_id')->options(User::withAccount()->pluck('name', 'id'))->searchable()->required()->label('الحساب مدين'),
                            TextInput::make('credit_info')->label('البيان'),
                            TextInput::make('credit')->label('مدين')->default(0)->numeric(),


                        ]),
                        Grid::make(3)->schema([
                            Select::make('debit_id')->options(User::withAccount()->pluck('name', 'id'))->searchable()->required()->label('الحساب دائن'),
                            TextInput::make('debit_info')->label('البيان'),
                            TextInput::make('debit')->label('دائن')->default(0)->numeric(),
                        ])
                    ])->label('قيد متعدد TR')
                        ->rules([
                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                $credit = 0;
                                $debit = 0;
                                foreach ($value as $item) {


                                    $debit = $item['debit'];
                                    $credit = $item['credit'];
                                    if ($credit != $debit) {
                                        $fail(" القيد غير متوازن");
                                        break;
                                    }
                                }

                            }

                        ])
                ])
                ->action(function ($data) {
                    \DB::beginTransaction();
                    try{
                        $uuid=\Str::uuid();
                        $currency=1;//USD
                        foreach ($data['balances'] as $item){
                            if($item['credit']==0 && $item['debit']==0){
                                continue;
                            }
                            $creditUser=User::find($item['credit_id']);
                            $debitUser=User::find($item['debit_id']);
                            Balance::create([
                                'uuid'=>$uuid,
                                'currency_id'=>$currency,
                                'debit'=>0,
                                'credit'=>$item['credit'],
                                'customer_name'=>$debitUser->name,
                                'info'=>$item['credit_info'],
                                'user_id'=>$creditUser->id,
                                'pending'=>false,
                                'is_complete'=>true,
                            ]);

                            Balance::create([
                                'uuid'=>$uuid,
                                'currency_id'=>$currency,
                                'debit'=>$item['debit'],
                                'credit'=>0,
                                'customer_name'=>$creditUser->name,
                                'info'=>$item['credit_info'],
                                'user_id'=>$debitUser->id,
                                'pending'=>false,
                                'is_complete'=>true,
                            ]);
                        }

                        DB::commit();
                        Notification::make()->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                    }catch (\Exception|\Error $e){

                        DB::rollBack();

                    }
                })->label('سند دولار متعدد'),
        ];
    }
}
