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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use App\Enums\ActivateStatusEnum;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Actions\Action;
class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make(),
             // تجميع الأزرار في مجموعة واحدة
            Actions\ActionGroup::make([
            Actions\Action::make('quid_usd')->form([
                Select::make('source_id')->options(User::WithAccount()->active()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('من حساب')->required(),
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

                // سند قيد الليرة السورية

                Actions\Action::make('quid_syp')->form([
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
                                'currency_id' => 3,
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
                                'currency_id' => 3,
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
                    })->label('سند قيد SYP'),
                     ])
            ->label(' سندات قيد فردية') // عنوان المجموعة
            ->icon('heroicon-o-document-text') // أيقونة المجموعة
            ->button() // لجعلها تظهر كزر بدلاً من قائمة منسدلة مباشرة
            ->color('primary'), // لون الزر
           /* Actions\Action::make('multi_Tr')->form([

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
                })->label('سند دولار متعدد'),*/
        Actions\ActionGroup::make([
            Actions\Action::make('multi_Tr')->form([
                Repeater::make('balances')->schema([
                    Grid::make(4)->schema([
                        Select::make('user_id')->options(User::withAccount()->active()->pluck('name', 'id'))->searchable()->required()->label('الحساب'),
                        TextInput::make('info')->label('البيان'),
                        TextInput::make('credit')->label('مدين')->default(0)->numeric(),
                        TextInput::make('debit')->label('دائن')->default(0)->numeric(),

                    ]),

                ])->defaultItems(10)->label('قيد متعدد TR')
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            $credit = 0;
                            $debit = 0;
                            foreach ($value as $item) {


                                $debit += $item['debit'];
                                $credit += $item['credit'];

                            }
                            if ($credit != $debit) {
                                $fail(" القيد غير متوازن");
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
                            Balance::create([
                                'uuid'=>$uuid,
                                'currency_id'=>$currency,
                                'debit'=>$item['debit'],
                                'credit'=>$item['credit'],
                                'info'=>$item['info'],
                                'user_id'=>$item['user_id'],
                                'pending'=>false,
                                'is_complete'=>true,
                            ]);
                        }

                        DB::commit();
                    }catch (\Exception|\Error $e){
                        DB::rollBack();
                    }
                })->label('سند تركي متعدد'),
            Actions\Action::make('multi_Usd')->form([
                /*Grid::make()->schema([
                    DatePicker::make('date'),
                ]),*/
                Repeater::make('balances')->schema([
                    Grid::make(4)->schema([
                        Select::make('user_id')->options(User::withAccount()->active()->pluck('name', 'id'))->searchable()->required()->label('الحساب'),
                        TextInput::make('info')->label('البيان'),
                        TextInput::make('credit')->label('مدين')->default(0)->numeric(),
                        TextInput::make('debit')->label('دائن')->default(0)->numeric(),

                    ])
                ])->defaultItems(10)->label('قيد متعدد USD')
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            $credit = 0;
                            $debit = 0;
                            foreach ($value as $item) {


                                $debit += $item['debit'];
                                $credit += $item['credit'];

                            }
                            if ($credit != $debit) {
                                $fail(" القيد غير متوازن");
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
                            Balance::create([
                                'uuid'=>$uuid,
                                'currency_id'=>$currency,
                                'debit'=>$item['debit'],
                                'credit'=>$item['credit'],
                                'info'=>$item['info'],
                                'user_id'=>$item['user_id'],
                                'pending'=>false,
                                'is_complete'=>true,
                            ]);
                        }

                        DB::commit();
                    }catch (\Exception|\Error $e){
                        DB::rollBack();
                    }
                })->label('سند دولار متعدد'),
                // سند سوري متعددد
                Actions\Action::make('multi_Syp')->form([
                    Repeater::make('balances')->schema([
                        Grid::make(4)->schema([
                            Select::make('user_id')->options(User::withAccount()->active()->pluck('name', 'id'))->searchable()->required()->label('الحساب'),
                            TextInput::make('info')->label('البيان'),
                            TextInput::make('credit')->label('مدين')->default(0)->numeric(),
                            TextInput::make('debit')->label('دائن')->default(0)->numeric(),

                        ]),

                    ])->defaultItems(10)->label('قيد متعدد SYP')
                        ->rules([
                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                $credit = 0;
                                $debit = 0;
                                foreach ($value as $item) {


                                    $debit += $item['debit'];
                                    $credit += $item['credit'];

                                }
                                if ($credit != $debit) {
                                    $fail(" القيد غير متوازن");
                                }
                            }

                        ])
                ])
                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try{
                            $uuid=\Str::uuid();
                            $currency=3;//SYP
                            foreach ($data['balances'] as $item){
                                if($item['credit']==0 && $item['debit']==0){
                                    continue;
                                }
                                Balance::create([
                                    'uuid'=>$uuid,
                                    'currency_id'=>$currency,
                                    'debit'=>$item['debit'],
                                    'credit'=>$item['credit'],
                                    'info'=>$item['info'],
                                    'user_id'=>$item['user_id'],
                                    'pending'=>false,
                                    'is_complete'=>true,
                                ]);
                            }

                            DB::commit();
                        }catch (\Exception|\Error $e){
                            DB::rollBack();
                        }
                    })->label('سند سوري متعدد'),
                     ])
            ->label('سندات قيد متعددة') // عنوان المجموعة
            ->icon('heroicon-o-document-text') // أيقونة المجموعة
            ->button() // لجعلها تظهر كزر بدلاً من قائمة منسدلة مباشرة
            ->color('primary'), // لون الزر
                    Actions\Action::make(name: 'quid_pending')->form([
                        Select::make('source_id')->options(User::WithAccount()->active()->select('id', 'name')->pluck('name', 'id'))->searchable()->label(' الحساب')->required(),
                        // Select::make('target_id')->options(User::WithAccount()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('إلى حساب')->required(),
                        // TextInput::make('amount')->required()->numeric()->rules([
                        //     fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        //         if ($value <= 0) {
                        //             $fail('يجب ان تكون القيمة أكبر من 0');
                        //         }
                        //     },
                        // ])->required()->label('القيمة'),
                        TextInput::make('amount')
                        ->default(function () {
                            return 0; // يمكنك استبدال هذا بحساب ديناميكي
                        })
                        ->disabled()->label('القيمة'),
                        TextInput::make('info')->default('سند تعليق')->label('ملاحظات')
                   ])
                        ->action(function ($data) {
                            DB::beginTransaction();
                            try {
                                $uuid = \Str::uuid();
                                Balance::create([
                                    'user_id' => $data['source_id'],
                                    'currency_id' => 1,
                                    'pending' => false,
                                    'is_complete' => true,
                                    'info' => $data['info'],
                                    'uuid' => $uuid,
                                    'type' => BalanceTypeEnum::PUSH->value,
                                    'credit' => 0,
                                    'debit' => 0,
                                    'customer_name' => User::find($data['source_id'])?->name,
                                ]);

                                DB::commit();
                                Notification::make('error')->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                            } catch (\Exception | \Error $e) {
                                DB::rollBack();
                                Notification::make('error')->danger()->title('خطأ في العملية')->body($e->getMessage())->send();
                            }
                        })->label('سند تعليق'),
// سند قيد متعدد محسن
Actions\Action::make('multi_Quid')->form([
    Repeater::make('balances')->schema([
        Grid::make(8)->schema([
            Select::make('user_id')
                ->options(User::withAccount()->active()->pluck('name', 'id'))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    $set('required_fields', !empty($state));
                })
                ->columnSpan(2)
                ->label('الحساب'),

            TextInput::make('info')
                ->label('البيان')
                ->columnSpan(2),

            Select::make('currency_id')
                ->label('العملة')
                ->options([
                    1 => 'دولار',
                    2 => 'ليرة تركية',
                    3 => 'ليرة سورية',
                ])
                ->default(1)
                ->required(fn ($get) => !empty($get('user_id')))
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    if ($state == 1) {
                        $set('ex_cur', 1);
                    }
                }),

            TextInput::make('ex_cur')
                ->label('معامل الصرف')
                ->default(1)
                ->numeric()
                ->gt(0)
                ->minValue(0.0001)
                ->visible(fn ($get) => in_array($get('currency_id'), [2, 3]))
                ->required(fn ($get) => in_array($get('currency_id'), [2, 3]))
                ->rules([
                    function ($get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            if (in_array($get('currency_id'), [2, 3]) && (float)$value <= 0) {
                                $fail("معامل الصرف يجب أن يكون رقمًا موجبًا");
                            }
                        };
                    }
                ]),

            TextInput::make('credit')
                ->label('مدين')
                ->default(0)
                ->numeric()
                ->minValue(0)
                ->required(fn ($get) => !empty($get('user_id')))
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    if ((float)$state > 0) {
                        $set('debit', 0);
                    }
                }),

            TextInput::make('debit')
                ->label('دائن')
                ->default(0)
                ->numeric()
                ->minValue(0)
                ->required(fn ($get) => !empty($get('user_id')))
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    if ((float)$state > 0) {
                        $set('credit', 0);
                    }
                }),

            // // عرض القيمة بالدولار تلقائياً
            // TextInput::make('value_in_usd')
            //     ->label('القيمة بالدولار')
            //     ->disabled()
            //     ->dehydrated(false)
            //     ->formatStateUsing(function ($state, $get) {
            //         $currency = $get('currency_id') ?? 1;
            //         $amount = (float)($get('credit') ?? $get('debit') ?? 0);
            //         $rate = (float)($get('ex_cur') ?? 1);

            //         if ($currency == 1) return number_format($amount, 4);
            //         return number_format($amount / $rate, 4);
            //     }),
        ]),
    ])
    ->defaultItems(8)
    ->label('سند قيد متعدد')
    ->columns(10)
    ->reorderable(false)
    ->createItemButtonLabel('إضافة بند جديد')
    ->extraItemActions([
        Action::make('balance_row')
            ->label('موازنة السطر')
            ->icon('heroicon-o-scale')
            ->color('primary')
            ->action(function ($arguments, $get, $set) {
                $rowIndex = $arguments['item'];
                $balances = $get('balances');

                if (!isset($balances[$rowIndex])) {
                    return Notification::make()
                        ->title('خطأ في تحديد موضع السطر')
                        ->danger()
                        ->send();
                }

                $current = $balances[$rowIndex];
                $baseCurrency = 1; // الدولار

                // تحويل جميع الأرصدة إلى الدولار
                $totalCreditUSD = 0;
                $totalDebitUSD = 0;

                foreach ($balances as $i => $item) {
                    if (empty($item['user_id'])) continue;

                    $currency = $item['currency_id'] ?? $baseCurrency;
                    $rate = ($currency == $baseCurrency) ? 1 : (float)($item['ex_cur'] ?? 1);

                    $credit = (float)($item['credit'] ?? 0);
                    $debit = (float)($item['debit'] ?? 0);
                // حماية ضد القسمة على الصفر
                    if ($currency != 1 && $rate <= 0) {
                        Notification::make()
                            ->title('خطأ في معامل الصرف')
                            ->body('يجب أن يكون معامل الصرف رقمًا موجبًا في جميع البنود')
                            ->color('danger')
                            ->persistent()
                            ->send();

                        return;
                    }
                    if ($currency == $baseCurrency) {
                        $totalCreditUSD += $credit;
                        $totalDebitUSD += $debit;
                    } else {
                        $totalCreditUSD += $credit / $rate;
                        $totalDebitUSD += $debit / $rate;
                    }
                }

                // حساب الفرق الحالي بدون السطر الحالي
                $currentCredit = (float)($current['credit'] ?? 0);
                $currentDebit = (float)($current['debit'] ?? 0);
                $currentRate = ($current['currency_id'] == $baseCurrency) ? 1 : (float)($current['ex_cur'] ?? 1);
                 // حماية ضد القسمة على الصفر
                if ($currentRate <= 0 )
                 {
                    Notification::make()
                        ->title('خطأ في معامل الصرف')
                        ->body('يجب أن يكون معامل الصرف رقمًا موجبًا في جميع البنود')
                        ->color('danger')
                        ->persistent()
                        ->send();

                    return; // أو يمكنك استخدام continue لتخطي هذا البند فقط
                }

                if ($current['currency_id'] == $baseCurrency) {
                    $totalCreditUSD -= $currentCredit;
                    $totalDebitUSD -= $currentDebit;
                } else {
                    $totalCreditUSD -= $currentCredit / $currentRate;
                    $totalDebitUSD -= $currentDebit / $currentRate;
                }

                $diff = $totalCreditUSD - $totalDebitUSD;

                // إذا كان السند متوازن بالفعل
                if (abs($diff) < 0.0001) {
                    return Notification::make()
                        ->title('السند متوازن بالفعل')
                        ->success()
                        ->send();
                }

                // تحديد نوع الموازنة المطلوبة
                $newBalances = $balances;
                $currentCurrency = $current['currency_id'] ?? $baseCurrency;

                if ($currentCurrency != $baseCurrency && $current['ex_cur'] > 1) {
                    // حالة 1: موازنة باستخدام معامل الصرف الموجود
                    if ($diff > 0) {
                        // زيادة المدين
                        $newBalances[$rowIndex]['debit'] = round($diff * $currentRate, 4);
                        $newBalances[$rowIndex]['credit'] = 0;
                    } else {
                        // زيادة الدائن
                        $newBalances[$rowIndex]['credit'] = round(abs($diff) * $currentRate, 4);
                        $newBalances[$rowIndex]['debit'] = 0;
                    }
                } elseif (($current['credit'] > 0 || $current['debit'] > 0) && $current['ex_cur'] == 1 && $currentCurrency != $baseCurrency) {
                    // حالة 2: حساب معامل الصرف
                    if ($current['credit'] > 0 && $diff < 0) {
                        $newExRate = $current['credit'] / abs($diff);
                        $newBalances[$rowIndex]['ex_cur'] = round($newExRate, 6);
                    } elseif ($current['debit'] > 0 && $diff > 0) {
                        $newExRate = $current['debit'] / $diff;
                        $newBalances[$rowIndex]['ex_cur'] = round($newExRate, 6);
                    } else {
                        return Notification::make()
                            ->title('لا يمكن حساب المعامل في هذه الحالة')
                            ->danger()
                            ->send();
                    }
                } else {
                    return Notification::make()
                        ->title('حالة غير مدعومة للموازنة')
                        ->danger()
                        ->send();
                }

                $set('balances', $newBalances);

                return Notification::make()
                    ->title('تمت الموازنة بنجاح')
                    ->success()
                    ->send();
            }),

        // Action::make('balance_all')
        //     ->label('موازنة السند كاملاً')
        //     ->icon('heroicon-o-scale')
        //     ->color('success')
        //     ->action(function ($get, $set) {
        //         $balances = $get('balances');
        //         $baseCurrency = 1; // الدولار

        //         // حساب المجموع الكلي بالدولار
        //         $totalCreditUSD = 0;
        //         $totalDebitUSD = 0;
        //         $emptyRows = [];

        //         foreach ($balances as $i => $item) {
        //             if (empty($item['user_id'])) {
        //                 $emptyRows[] = $i;
        //                 continue;
        //             }

        //             $currency = $item['currency_id'] ?? $baseCurrency;
        //             $rate = ($currency == $baseCurrency) ? 1 : (float)($item['ex_cur'] ?? 1);

        //             $credit = (float)($item['credit'] ?? 0);
        //             $debit = (float)($item['debit'] ?? 0);

        //             if ($currency == $baseCurrency) {
        //                 $totalCreditUSD += $credit;
        //                 $totalDebitUSD += $debit;
        //             } else {
        //                 $totalCreditUSD += $credit / $rate;
        //                 $totalDebitUSD += $debit / $rate;
        //             }
        //         }

        //         $diff = $totalCreditUSD - $totalDebitUSD;

        //         if (abs($diff) < 0.0001) {
        //             return Notification::make()
        //                 ->title('السند متوازن بالفعل')
        //                 ->success()
        //                 ->send();
        //         }

        //         if (empty($emptyRows)) {
        //             return Notification::make()
        //                 ->title('لا يوجد أسطر فارغة للموازنة')
        //                 ->danger()
        //                 ->send();
        //         }

        //         // استخدام أول سطر فارغ للموازنة
        //         $rowIndex = $emptyRows[0];
        //         $newBalances = $balances;

        //         // تعيين العملة الأساسية للسطر الفارغ
        //         $newBalances[$rowIndex]['currency_id'] = $baseCurrency;
        //         $newBalances[$rowIndex]['ex_cur'] = 1;

        //         if ($diff > 0) {
        //             // زيادة الدائن لموازنة الفرق
        //             $newBalances[$rowIndex]['debit'] = 0;
        //             $newBalances[$rowIndex]['credit'] = round(abs($diff), 4);
        //         } else {
        //             // زيادة المدين لموازنة الفرق
        //             $newBalances[$rowIndex]['credit'] = 0;
        //             $newBalances[$rowIndex]['debit'] = round(abs($diff), 4);
        //         }

        //         $set('balances', $newBalances);

        //         return Notification::make()
        //             ->title('تمت موازنة السند كاملاً')
        //             ->success()
        //             ->send();
        //     }),
    ])
    ->rules([
        function () {
            return function (string $attribute, $value, Closure $fail) {
                $totalCreditUSD = 0;
                $totalDebitUSD = 0;
                $baseCurrency = 1;
                $hasEmptyUser = false;
                $hasInvalidExchange = false;

                foreach ($value as $index => $item) {
                    if (empty($item['user_id'])) {
                        $hasEmptyUser = true;
                        continue;
                    }

                    $currency = $item['currency_id'] ?? $baseCurrency;
                    $exchangeRate = ($currency == $baseCurrency) ? 1 : (float)($item['ex_cur'] ?? 1);

                    // التحقق من معامل الصرف
                    if ($currency != $baseCurrency && $exchangeRate <= 0) {
                        $hasInvalidExchange = true;
                        $fail("السطر " . ($index + 1) . ": معامل الصرف يجب أن يكون أكبر من الصفر");
                    }

                    $credit = (float)($item['credit'] ?? 0);
                    $debit = (float)($item['debit'] ?? 0);

                    // التحقق من أن أحد الحقلين فقط مدخل
                    if ($credit > 0 && $debit > 0) {
                        $fail("السطر " . ($index + 1) . ": يجب إدخال إما مدين أو دائن فقط");
                    }

                    // التحويل إلى الدولار
                    if ($currency == $baseCurrency) {
                        $totalCreditUSD += $credit;
                        $totalDebitUSD += $debit;
                    } else {
                        $totalCreditUSD += $credit / $exchangeRate;
                        $totalDebitUSD += $debit / $exchangeRate;
                    }
                }

                // if ($hasEmptyUser && count($value) > 1) {
                //     $fail("يوجد حسابات غير محددة. الرجاء تحديد الحسابات أو حذف الأسطر الفارغة");
                // }

                if ($hasInvalidExchange) {
                    return;
                }

                // مقارنة دقيقة جداً بدون تقريب
                if (abs($totalCreditUSD - $totalDebitUSD) > 0.0001) {
                    $diff = abs($totalCreditUSD - $totalDebitUSD);
                    $fail(sprintf(
                        "القيد غير متوازن. الفرق: %.4f دولار (المجموع المدين: %.4f - المجموع الدائن: %.4f)",
                        $diff,
                        $totalCreditUSD,
                        $totalDebitUSD
                    ));
                }
            };
        }
    ])
])
->modalWidth(MaxWidth::Full)
->action(function ($data) {
    \DB::beginTransaction();
    try {
        $uuid = \Str::uuid();
        $entries = [];

        foreach ($data['balances'] as $item) {
            if (empty($item['user_id']) || ($item['credit'] == 0 && $item['debit'] == 0)) {
                continue;
            }

            $entries[] = [
                'uuid' => $uuid,
                'currency_id' => $item['currency_id'],
                'ex_cur' => $item['ex_cur'],
                'debit' => $item['debit'],
                'credit' => $item['credit'],
                'type' => BalanceTypeEnum::SANADQUID->value,
                'info' => $item['info'] .
                         (in_array($item['currency_id'], [2, 3]) ? ' - معامل الصرف: ' . $item['ex_cur'] : ''),
                'user_id' => $item['user_id'],
                'pending' => false,
                'is_complete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

                     if (empty($entries)) {
                             Notification::make()
                            ->title('فارغ')
                            ->body('لم يتم القيام باي اجراء')
                            ->color('danger')
                            ->persistent()
                            ->send();

                        return;
                      }

        // إدخال جماعي لأفضل أداء
        Balance::insert($entries);

        \DB::commit();

        Notification::make()
            ->title('تم حفظ سند القيد بنجاح')
            ->success()
            ->send();

    } catch (\Exception | \Error $e) {
        \DB::rollBack();

        Notification::make()
            ->title('فشل في حفظ السند')
            ->body($e->getMessage())
            ->danger()
            ->send();

        throw $e;
    }
})
->label('سند قيد')
->modalSubmitActionLabel('حفظ السند')
->modalCancelActionLabel('إلغاء'),

// إضافة زر عرض السندات
Actions\Action::make('sanadat_view')
    ->label('عرض سندات القيد')
    ->url(AccountResource::getUrl('view-sanadat'))
    ->color('primary'),


        ];
    }
}
