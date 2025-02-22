<?php

namespace App\Filament\Admin\Widgets;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BalanceEmployeeWidget extends BaseWidget
{
  protected static ?string $heading="أرصدة الموظفين USD";
  protected int | string | array $columnSpan=2;
    public static function canView(): bool
    {
        ;
        return !\Str::endsWith(request()->fullUrl(),'admin') && !\Str::endsWith(request()->fullUrl(),'admin/') ; // TODO: Change the autogenerated stub
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
               fn()=> User::select('users.*')
                   ->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->orWhere('level', LevelUserEnum::ADMIN->value)

                   ->where('level',LevelUserEnum::USER->value)
                   ->selectSub(function ($query) {
                       $query->from('balances')
                           ->selectRaw('SUM(credit - debit)')
                           ->whereColumn('user_id', 'users.id')
                           ->where('balances.is_complete', 1)
                           ->where('balances.pending', '=',false)
                           ->where('balances.currency_id', '=',1);
                   }, 'net_balance')
                   ->having('net_balance', '!=', 0),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المستخدم'),
                Tables\Columns\TextColumn::make('net_balance')->formatStateUsing(fn($record)=>HelperBalance::formatNumber($record->net_balance))->label('الرصيد الحالي')->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')->options(User::whereIn('level', [
                    LevelUserEnum::BRANCH->value,
                    LevelUserEnum::ADMIN->value,
                    LevelUserEnum::STAFF->value,
                ])->pluck('name','id'))->searchable()
            ])
            ;
    }
}
