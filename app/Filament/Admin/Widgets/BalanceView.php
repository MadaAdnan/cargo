<?php

namespace App\Filament\Admin\Widgets;

use App\Helper\HelperBalance;
use App\Models\Balance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceView extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected int|string|array $columnSpan = 4;




    protected function getStats(): array
    {
        $balances = Balance::groupBy('currency_id')->where([
            'user_id' => auth()->id(),
            'is_complete' => 1])->selectRaw('SUM(credit) - SUM(debit) as totalBalance,currency_id')->get();
        // dd($balances);
        $list = [];
        foreach ($balances as $balance) {
            $list[] = Stat::make('رصيد صندوق ' . $balance->currency?->name, $balance->totalBalance);

        }
        return $list;
    }
}
