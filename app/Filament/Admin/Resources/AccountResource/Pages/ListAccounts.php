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
           // سند قيد متعدد
                Actions\Action::make('multi_Quid')->form([

                    Repeater::make('balances')->schema([
                        // Grid::make(columns: 8)->schema([ // زيادة عرض الشاشة
                        Select::make('user_id')->options(User::withAccount()->active()->pluck('name', 'id'))->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('required_fields', !empty($state));
                            })
                             ->columnSpan(2) // توسيع حقل ,
                            ->label('الحساب'),
                            TextInput::make('info')
                            ->label('البيان')
                            ->columnSpan(2), // توسيع حقل البيان,
                            Select::make('currency_id')
                            ->label('العملة')
                            ->options([
                                1 => 'دولار',
                                2 => 'تركي',
                                3 => 'سوري',
                            ])
                            ->default(1)
                            ->required(fn ($get) => !empty($get('user_id')))
                            ->reactive(), // مهم لتحديث القيم عند تغيير العملة
                            TextInput::make('ex_cur')
                            ->label('معامل الصرف')
                            ->default(1)->numeric()
                            ->visible(fn ($get) => in_array($get('currency_id'), [2, 3])),
                            TextInput::make('credit')->label('مدين')->default(0)->numeric()->required(fn ($get) => !empty($get('user_id'))),
                            TextInput::make('debit')->label('دائن')->default(0)->numeric()->required(fn ($get) => !empty($get('user_id'))),

                        // ]),

                    ])->defaultItems(9)
                    ->label('سند قيد متعدد')
                     ->columns(columns: 9)
                     ->reorderable(false)
                    //  ->deletable(false)
                     ->extraItemActions([
                            Action::make('balance_row')
                                ->label('موازنة')
                                ->icon('heroicon-o-scale')
                                ->color('primary')
                                // ->before(function ($state, array $arguments) {
                                // dd($arguments['item']);
                                // })
                                ->action(function ($state, array $arguments, callable $get, callable $set) {
                                        // dd($arguments['item'], $state);

                                    $rowIndex = $arguments['item'];
                                    if ($rowIndex === null) {
                                        return Notification::make()
                                            ->title('خطأ في تحديد موضع السطر')
                                            ->danger()
                                            ->send();
                                    }
                                    $balances = $get('balances');
                                    // dd($balances);
                                    $baseCurrency = 1; // الدولار

                                    $current = $balances[$rowIndex] ?? null;

                                    if (!$current || empty($current['user_id']) || !in_array($current['currency_id'], [2, 3])) {
                                        return Notification::make()
                                            ->title('لا يمكن موازنة هذا السطر')
                                            ->danger()
                                            ->send();
                                    }

                                    $ex_cur = (float)($current['ex_cur'] ?? 0);
                                    $credit = (float)($current['credit'] ?? 0);
                                    $debit = (float)($current['debit'] ?? 0);

                                    // ======= [ الحالة 1: موازنة أحد الحقول ] =======
                                    if ($ex_cur > 1 && ($credit == 0 || $debit == 0)) {
                                        $totalCredit = 0;
                                        $totalDebit = 0;

                                        foreach ($balances as $i => $item) {
                                            if ($i == $rowIndex || empty($item['user_id'])) continue;

                                            $currency = $item['currency_id'] ?? $baseCurrency;
                                            $rate = ($currency == $baseCurrency) ? 1 : (float)($item['ex_cur'] ?? 1);

                                            $totalCredit += ($currency == $baseCurrency)
                                                ? (float)($item['credit'] ?? 0)
                                                : (float)($item['credit'] ?? 0) / $rate;

                                            $totalDebit += ($currency == $baseCurrency)
                                                ? (float)($item['debit'] ?? 0)
                                                : (float)($item['debit'] ?? 0) / $rate;
                                        }

                                        $diff = $totalCredit - $totalDebit;

                                        if (abs($diff) < 0.0001) {
                                            return Notification::make()
                                                ->title('السند متوازن')
                                                ->success()
                                                ->send();
                                        }

                                        if ($diff > 0) {
                                            // نحتاج زيادة "مدين"
                                            $balances[$rowIndex]['debit'] = round($diff * $ex_cur, 2);
                                            $balances[$rowIndex]['credit'] = 0;
                                        } else {
                                            // نحتاج زيادة "دائن"
                                            $balances[$rowIndex]['credit'] = round(abs($diff) * $ex_cur, 2);
                                            $balances[$rowIndex]['debit'] = 0;
                                        }

                                        $set('balances', $balances);

                                        return Notification::make()
                                            ->title('تمت موازنة السطر')
                                            ->success()
                                            ->body('تم تعديل قيمة دائن أو مدين وفق الفرق.')
                                            ->send();
                                    }

                                    // ======= [ الحالة 2: حساب معامل الصرف ] =======
                                    if ($credit > 0 && $debit > 0 && $ex_cur == 1) {
                                        // هذا غير منطقي: الاثنين موجودين، والمفترض فقط أحدهما
                                        return Notification::make()
                                            ->title('يرجى ملء حقل واحد فقط: دائن أو مدين')
                                            ->danger()
                                            ->send();
                                    }

                                    if (($credit > 0 || $debit > 0) && $ex_cur == 1) {
                                        $totalCredit = 0;
                                        $totalDebit = 0;

                                        foreach ($balances as $i => $item) {
                                            if ($i == $rowIndex || empty($item['user_id'])) continue;

                                            $currency = $item['currency_id'] ?? $baseCurrency;
                                            $rate = ($currency == $baseCurrency) ? 1 : (float)($item['ex_cur'] ?? 1);

                                            $totalCredit += ($currency == $baseCurrency)
                                                ? (float)($item['credit'] ?? 0)
                                                : (float)($item['credit'] ?? 0) / $rate;

                                            $totalDebit += ($currency == $baseCurrency)
                                                ? (float)($item['debit'] ?? 0)
                                                : (float)($item['debit'] ?? 0) / $rate;
                                        }

                                        $diff = $totalCredit - $totalDebit;

                                        if (abs($diff) < 0.0001) {
                                            return Notification::make()
                                                ->title('السند متوازن')
                                                ->success()
                                                ->send();
                                        }

                                        if ($credit > 0 && $diff < 0) {

                                            // نحتاج حساب معامل الصرف للدائن
                                            $new_ex = abs($credit) / $diff;
                                            $balances[$rowIndex]['ex_cur'] = round($new_ex, 4);
                                        } elseif ($debit > 0 && $diff > 0) {
                                            // نحتاج حساب معامل الصرف للمدين
                                            $new_ex = abs($debit) / $diff;
                                            $balances[$rowIndex]['ex_cur'] = round($new_ex, 4);
                                        } else {
                                            return Notification::make()
                                                ->title('القيمة غير مناسبة لحساب المعامل')
                                                ->danger()
                                                ->send();
                                        }

                                        $set('balances', $balances);

                                        return Notification::make()
                                            ->title('تم حساب معامل الصرف')
                                            ->success()
                                            ->send();
                                    }

                                    // حالة غير معالجة
                                    return Notification::make()
                                        ->title('لم يتم استيفاء شروط الموازنة')
                                        ->danger()
                                        ->send();
                                })
                            // ->arguments([
                            //         'statepath' => fn ($livewire, $get, $set, $component) => $component->getState(),
                            //     ])
                        ])


                        ->rules([
                            fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                $totalCredit = 0;
                                $totalDebit = 0;
                                $baseCurrency = 1; // الدولار كعملة أساس
                                $hasError = false;

                                foreach ($value as $index => $item) {
                                    if (empty($item['user_id'])) {
                                        continue;
                                    }

                                    $currency = $item['currency_id'] ?? $baseCurrency;


                                    $exchangeRate = ($currency == $baseCurrency) ? 1 : (float)$item['ex_cur'];

                                    // التحقق من أن معامل الصرف موجب
                                    if ($exchangeRate <= 0) {
                                        $fail("السطر " . ($index + 1) . ": معامل الصرف يجب أن يكون أكبر من الصفر");
                                        $hasError = true;
                                    }

                                    $credit = (float)($item['credit'] ?? 0);
                                    $debit = (float)($item['debit'] ?? 0);
                                    if($exchangeRate == $baseCurrency){ // العملة دولار
                                    $totalCredit += $credit * $exchangeRate;
                                    $totalDebit += $debit * $exchangeRate;
                                    }else{ // العملة سوري او تركي
                                    $totalCredit += $credit / $exchangeRate;
                                    $totalDebit += $debit / $exchangeRate;
                                    }

                                }

                                if ($hasError) {
                                    return;
                                }

                                // مقارنة دقيقة جداً بدون تقريب
                                if (abs($totalCredit - $totalDebit) > 0.00001) {
                                    $diff = abs($totalCredit - $totalDebit);
                                    $fail(sprintf(
                                        "القيد غير متوازن. الفرق: %.8f دولار (المجموع المدين: %.8f - المجموع الدائن: %.8f)",
                                        $diff,
                                        $totalCredit,
                                        $totalDebit
                                    ));
                                }
                            }
                        ])

                 ]) ->modalWidth(MaxWidth::SevenExtraLarge)

                    ->action(function ($data) {
                        \DB::beginTransaction();
                        try{
                            $uuid=\Str::uuid();
                            foreach ($data['balances'] as $item){
                                if($item['credit']==0 && $item['debit']==0){
                                    continue;
                                }
                                Balance::create([
                                    'uuid'=>$uuid,
                                    'currency_id'=>$item['currency_id'],
                                    'ex_cur'=>$item['ex_cur'],
                                    'debit'=>$item['debit'],
                                    'credit'=>$item['credit'],
                                    'type' => BalanceTypeEnum::SANADQUID->value,
                                    'info'=>$item['info'] .
                                     (in_array($item['currency_id'], [2, 3]) ? ' - معامل الصرف: ' . ($item['ex_cur'] ?? 1) : ''),
                                    'user_id'=>$item['user_id'],
                                    'pending'=>false,
                                    'is_complete'=>true,
                                ]);
                            }

                            DB::commit();
                        }catch (\Exception|\Error $e){
                            DB::rollBack();
                        }
                    })->label('سند قيد '),

                       Actions\Action::make('sanadat_view')
                                ->label('عرض سندات القيد')
                                ->url(AccountResource::getUrl('view-sanadat'))
                                // ->icon('heroicon-o-document-text')
                                ->color('primary'),



        ];
    }
}
