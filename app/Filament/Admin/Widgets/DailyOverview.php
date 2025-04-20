<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\OrderStatusEnum;
use Illuminate\Support\Carbon;

class DailyOverview extends BaseWidget
{

    use InteractsWithPageFilters;
    protected ?string $heading = 'تقرير يومي';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_DailyOverview');
    }

    // لتفعيل استقبال الفلاتر من صفحة الداشبورد
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $date = $this->filters['date'] ?? now(); // اجلب التاريخ من الفلتر أو استخدم تاريخ اليوم

        // تأكد أن التاريخ كائن من نوع Carbon
        $date = Carbon::parse($date);

        $ordersNum = Order::whereDate('created_at', $date)
            ->where('status', '!=', OrderStatusEnum::CANCELED->value);

        $senderFar = Order::whereDate('created_at', $date)
            ->where('orders.far_sender', 1)
            ->where('status', '!=', OrderStatusEnum::CANCELED->value);

        $reciveFar = Order::whereDate('created_at', $date)
            ->where('orders.far_sender', 0)
            ->where('status', '!=', OrderStatusEnum::CANCELED->value);

        return [
            Stat::make('الشحنات المنشأة بتاريخ ' . $date->format('Y-m-d'), $ordersNum->count()),
            Stat::make('إجمالي قيمة الشحنات USD', $ordersNum->sum('price')),
            Stat::make('إجمالي قيمة الشحنات TRY', $ordersNum->sum('price_tr')),
            Stat::make('أجور الشحنات USD على المرسل', $senderFar->sum('far')),
            Stat::make('أجور الشحنات USD على المستلم', $reciveFar->sum('far')),
            Stat::make('أجور الشحنات TRY على المرسل', $senderFar->sum('far_tr')),
            Stat::make('أجور الشحنات TRY على المستلم', $reciveFar->sum('far_tr')),
        ];
    }
}
