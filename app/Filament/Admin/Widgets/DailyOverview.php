<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyOverview extends BaseWidget
{
    protected ?string $heading='تقرير يومي';
    protected function getStats(): array
    {
        $ordersNum=Order::whereDate('created_at',now());
        return [
            Stat::make('الشحنات المنشأة اليوم', $ordersNum->count()),
            Stat::make('إجمالي قيمة الشحنات USD', $ordersNum->sum('price')),
            Stat::make('إجمالي قيمة الشحنات TRY', $ordersNum->sum('price_tr')),
            Stat::make('إجمالي أجور الشحنات USD  على المرسل', $ordersNum->where('orders.far_sender',1)->get()->sum('far')),
            Stat::make('إجمالي أجور الشحنات USD  على المستلم', $ordersNum->where('orders.far_sender',0)->get()->sum('far')),
            Stat::make('إجمالي أجور الشحنات TRY  على المرسل', $ordersNum->where('orders.far_sender',1)->get()->sum('far_tr')),
            Stat::make('إجمالي أجور الشحنات TRY  على المستلم', $ordersNum->where('orders.far_sender',0)->get()->sum('far_tr')),
        ];
    }
}
