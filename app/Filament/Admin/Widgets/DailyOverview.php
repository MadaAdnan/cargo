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
    protected static bool $shouldPersistFiltersInSession = true;

    protected ?string $heading = 'تقرير يومي';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_DailyOverview');
    }

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $filters = session('Dashboard_filters', $this->filters ?? []);
        $savedFilters = session('Dashboard_filters', []);

        $activeFilters = array_merge($this->filters ?? [], $savedFilters);

        $date = $activeFilters['date'] ?? now();
        $branchTargetId = $activeFilters['branch_target_id'] ?? null;
        $branchSourceId = $activeFilters['branch_source_id'] ?? null;

        $date = Carbon::parse($date)->format('Y-m-d');

        // الاستعلام الأساسي مع التاريخ والحالة
        $ordersQuery = Order::query()
            ->whereDate('created_at', $date)
            ->where('status', '!=', OrderStatusEnum::CANCELED->value);

        // تطبيق فلاتر الفرع المرسل
        if ($branchSourceId) {
            $ordersQuery->where('branch_source_id', $branchSourceId);
        }

        // تطبيق فلاتر الفرع المستلم
        if ($branchTargetId) {
            $ordersQuery->where('branch_target_id', $branchTargetId);
        }

        // نسخ الاستعلامات للعمليات المتنوعة
        $orders = clone $ordersQuery;

        $senderFar = clone $ordersQuery;
        $senderFar->where('far_sender', 1);

        $receiverFar = clone $ordersQuery;
        $receiverFar->where('far_sender', 0);

        return [
            Stat::make('الشحنات المنشأة بتاريخ ' . $date, $orders->count()),
            Stat::make('إجمالي قيمة الشحنات USD', $orders->sum('price')),
            Stat::make('إجمالي قيمة الشحنات TRY', $orders->sum('price_tr')),
            Stat::make('أجور الشحنات USD على المرسل', $senderFar->sum('far')),
            Stat::make('أجور الشحنات USD على المستلم', $receiverFar->sum('far')),
            Stat::make('أجور الشحنات TRY على المرسل', $senderFar->sum('far_tr')),
            Stat::make('أجور الشحنات TRY على المستلم', $receiverFar->sum('far_tr')),
        ];
    }
}
