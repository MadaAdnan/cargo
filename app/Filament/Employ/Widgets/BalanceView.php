<?php

namespace App\Filament\Employ\Widgets;

use App\Helper\HelperBalance;
use App\Models\Balance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceView extends BaseWidget
{
    protected int | string | array $columnSpan=1;
    protected function getStats(): array
    {
        $balances=Balance::groupBy('currency_id')->where([
            'user_id'=>auth()->id(),
        'is_complete'=>1
        ])->selectRaw('SUM(credit) - SUM(debit) as totalBalance,currency_id')->get();
        $balances2=Balance::groupBy('currency_id')->where([
            'user_id'=>auth()->id(),
            'pending'=>1
        ])->selectRaw('SUM(credit) - SUM(debit) as totalBalance,currency_id')->get();
        $list=[];

        foreach ($balances as $balance){
            $list[]=    Stat::make('رصيد صندوق '.$balance->currency?->name,$balance->totalBalance);

        }
        foreach ($balances2 as $balance){
            $list[]=    Stat::make('رصيد قيد التحصيل صندوق '.$balance->currency?->name,$balance->totalBalance);

        }
        return $list;
    }
}
