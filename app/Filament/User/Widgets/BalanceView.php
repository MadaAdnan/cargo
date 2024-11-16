<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceView extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('الرصيد قيد التحصيل', \DB::table('balances')->where('user_id',auth()->id())->where('balances.is_complete','=',0)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total),
            Stat::make('الرصيد الحالي', \DB::table('balances')->where('user_id',auth()->id())->where('balances.is_complete','=',true)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total),
        ];
    }
}
