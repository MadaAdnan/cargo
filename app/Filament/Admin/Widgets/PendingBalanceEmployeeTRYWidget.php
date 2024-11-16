<?php

namespace App\Filament\Admin\Widgets;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingBalanceEmployeeTRYWidget extends BaseWidget
{
    protected static ?string $heading="الأرصدة قيد التحصيل TRY";
    protected int | string | array $columnSpan=1;
    public static function canView(): bool
    {
        ;
        return !\Str::endsWith(request()->fullUrl(),'admin') && !\Str::endsWith(request()->fullUrl(),'admin/') ; // TODO: Change the autogenerated stub
    }
    public function table(Table $table): Table
    {
        return $table
            ->poll(10)
            ->query(
                fn()=> User::select('users.*')
                    ->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->orWhere('level', LevelUserEnum::ADMIN->value)


                    ->selectSub(function ($query) {
                        $query->from('balances') ->where('balances.pending', '=',true)
                            ->selectRaw('SUM(credit - debit)')
                            ->whereColumn('balances.user_id', 'users.id')
                            ->where('balances.currency_id',2)
                           ;
                    }, 'net_balance')
                    /*->orderByDesc('net_balance')*/
                    ->having('net_balance', '!=', 0),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المستخدم'),
                Tables\Columns\TextColumn::make('net_balance')->label('الرصيد الحالي')->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state))->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')->options(User::pluck('name','id'))->searchable()
            ])
            ;
    }
}
