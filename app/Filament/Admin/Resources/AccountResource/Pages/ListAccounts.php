<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\AccountResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
use Filament\Actions;
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
                Select::make('source_id')->options(User::withoutGlobalScopes()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('من حساب')->required(),
                Select::make('target_id')->options(User::withoutGlobalScopes()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('إلى حساب')->required(),
                TextInput::make('amount')->required()->numeric()->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        if ($value <= 0) {
                            $fail('يجب ان تكون القيمة أكبر من 0');
                        }
                    },
                ])->required()->label('القيمة'),
                TextInput::make('info')->label('ملاحظات')
            ])->action(function ($data) {
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
                        'debit' => 0
                    ]);

                    Balance::create([
                        'user_id' =>  $data['source_id'],
                        'currency_id' => 1,
                        'pending' => false,
                        'is_complete' => true,
                        'info' => $data['info'],
                        'uuid' => $uuid,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'credit' => 0,
                        'debit' => $data['amount']
                    ]);

                    DB::commit();
                    Notification::make('error')->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                } catch (\Exception | \Error $e) {
                    DB::rollBack();
                    Notification::make('error')->danger()->title('خطأ في العملية')->body($e->getMessage())->send();
                }
            })->label('سند قيدUSD'),
            Actions\Action::make('quid_try')->form([
                Select::make('source_id')->options(User::withoutGlobalScopes()->hideGlobal()->active()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('من حساب')->required(),
                Select::make('target_id')->options(User::withoutGlobalScopes()->active()->hideGlobal()->select('id', 'name')->pluck('name', 'id'))->searchable()->label('إلى حساب')->required(),
                TextInput::make('amount')->required()->numeric()->rules([
                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                        if ($value <= 0) {
                            $fail('يجب ان تكون القيمة أكبر من 0');
                        }
                    },
                ])->required()->label('القيمة'),
                TextInput::make('info')->label('ملاحظات')
            ])->action(function ($data) {
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
                        'debit' => 0
                    ]);

                    Balance::create([
                        'user_id' =>  $data['source_id'],
                        'currency_id' => 2,
                        'pending' => false,
                        'is_complete' => true,
                        'info' => $data['info'],
                        'uuid' => $uuid,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'credit' => 0,
                        'debit' => $data['amount']
                    ]);

                    DB::commit();
                    Notification::make('error')->success()->title('نجاح العملية')->body('تم إضافة السند بنجاح')->send();
                } catch (\Exception | \Error $e) {
                    DB::rollBack();
                    Notification::make('error')->danger()->title('خطأ في العملية')->body($e->getMessage())->send();
                }
            })->label('سند قيدTRY'),
        ];
    }
}
