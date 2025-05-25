<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Balance;
use App\Enums\OrderStatusEnum;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
class DailyOverview extends BaseWidget
{
    use InteractsWithPageFilters;

public static function canView(): bool
{
    return !request()->routeIs('filament.admin.pages.dashboard');
}

    protected static bool $shouldPersistFiltersInSession = true;

    protected ?string $heading = 'تقرير الشحنات اليومي';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // dd($this->filters['startDate']);
       // Directly access filters without session merging
    $startDate = Carbon::parse($this->filters['start_date'] ?? now());
    $endDate = Carbon::parse($this->filters['end_date'] ?? now());

    $branchTargetId = $this->filters['branch_target_id'] ?? null;
    $branchSourceId = $this->filters['branch_source_id'] ?? null;


        if ($startDate->equalTo($endDate)) {
            $ordersQuery = Order::query()
                ->whereDate('created_at', $startDate)
                ->where('status', '!=', OrderStatusEnum::CANCELED->value);
        } else {
            $ordersQuery = Order::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', OrderStatusEnum::CANCELED->value);
        }

        // تطبيق فلاتر الفرع المرسل
        if (!empty($branchSourceId)) {
            $ordersQuery->whereIn('branch_source_id', is_array($branchSourceId) ? $branchSourceId : [$branchSourceId]);
        }

        // تطبيق فلاتر الفرع المستلم
        if (!empty($branchTargetId)) {
            $ordersQuery->whereIn('branch_target_id', is_array($branchTargetId) ? $branchTargetId : [$branchTargetId]);
        }

        // نسخ الاستعلامات للعمليات المتنوعة
        $orders = clone $ordersQuery;

        $senderFar = clone $ordersQuery;
        $senderFar->where('far_sender', 1);

        $receiverFar = clone $ordersQuery;
        $receiverFar->where('far_sender', 0);
        // الاستعلام من جدول الارصدة
        $orderIds = $orders->pluck('id');

        $balancesQuery = Balance::whereIn('order_id', $orderIds);

        $usdCredit = (clone $balancesQuery)->where('currency_id', 1)->sum('credit');
        $tryCredit = (clone $balancesQuery)->where('currency_id', 2)->sum('credit');

        $usdDedit = (clone $balancesQuery)->where('currency_id', 1)->sum('debit');
        $tryDedit = (clone $balancesQuery)->where('currency_id', 2)->sum('debit');
        return [

            Stat::make('الشحنات المنشأة من ' . Carbon::parse($startDate)->format('Y/m/d') . ' إلى ' . Carbon::parse($endDate)->format('Y/m/d'), $orders->count()),
            Stat::make('إجمالي قيمة الشحنات USD', $orders->sum('price')),
            Stat::make('إجمالي قيمة الشحنات TRY', $orders->sum('price_tr')),
            Stat::make('أجور الشحنات USD على المرسل', $senderFar->sum('far')),
            Stat::make('أجور الشحنات USD على المستلم', $receiverFar->sum('far')),
            Stat::make('أجور الشحنات TRY على المرسل', $senderFar->sum('far_tr')),
            Stat::make('أجور الشحنات TRY على المستلم', $receiverFar->sum('far_tr')),
            Stat::make('اجمالي دائن USD', $usdCredit),
            Stat::make('اجمالي دائن TRY', $tryCredit),
            Stat::make('اجمالي مدين USD', $usdDedit),
            Stat::make('اجمالي مدين TRY', $tryDedit),
        ];
    }

}
