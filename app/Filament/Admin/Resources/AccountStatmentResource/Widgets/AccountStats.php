<?php

namespace App\Filament\Admin\Resources\AccountStatmentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Filament\Admin\Resources\AccountStatmentResource\Pages\ListAccountStatments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Str;
class AccountStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListAccountStatments::class;
    }

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $userId = $this->getTablePageInstance()->getTableFilterState('user_id')['value'] ?? null;

        if (!$userId) {
            return [];
        }


        if (!$userId) {
            return []; // لا تعرض شيء إذا لم يتم اختيار فلتر المستخدم
        }

        // تحميل فقط عمود order_id لتقليل الحمل
        $orderIds = $this->getPageTableQuery()
            ->whereNotNull('order_id')
            ->select('order_id') // تحميل عمود واحد فقط
            ->distinct()
            ->pluck('order_id');

        // في حال عدم وجود بيانات، تجنب استعلام غير ضروري
        if ($orderIds->isEmpty()) {
            return [
                Stat::make('عدد الشحنات', 0),
                Stat::make('شحنات تم التسليم', 0),
                Stat::make('شحنات بالانتظار', 0),
                Stat::make('شحنات ملغاة', 0),
                Stat::make('شحنات مرتجعة', 0),
                Stat::make('شحنات تم تاكيد تسليمها كمرتجعة', 0),
            ];
        }

        // استعلام واحد فقط يحتوي على جميع الإحصائيات
        $orderCounts = Order::whereIn('id', $orderIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = 'transfer' THEN 1 ELSE 0 END) as transfer_count,
                SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled_count,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_count,
                SUM(CASE WHEN status = 'confirm_returned' THEN 1 ELSE 0 END) as confirm_returned_count
            ")
            ->first();

        return [
            Stat::make('معرف الحساب', $userId),

            Stat::make('عدد الشحنات', $orderCounts->total),
            Stat::make('شحنات تم التسليم', $orderCounts->success_count),
            Stat::make('شحنات بالانتظار', $orderCounts->transfer_count),
            Stat::make('شحنات ملغاة', $orderCounts->canceled_count),
            Stat::make('شحنات مرتجعة', $orderCounts->returned_count),
            Stat::make('شحنات تم تاكيد تسليمها كمرتجعة', $orderCounts->confirm_returned_count),
        ];
    }
}
