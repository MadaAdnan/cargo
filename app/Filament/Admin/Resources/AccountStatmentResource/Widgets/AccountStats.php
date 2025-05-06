<?php

namespace App\Filament\Admin\Resources\AccountStatmentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Balance;
use App\Models\User;
use App\Models\Order;
use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\AccountStatmentResource\Pages\ListAccountStatments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
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
        $query = $this->getPageTableQuery();

        // $debitSum = $query->sum('debit');
        // $creditSum = $query->sum('credit');
        // $balance = $creditSum - $debitSum;

        // استخراج معرفات الشحنات من الكشوف فقط
        $orderIds = $query->whereNotNull('order_id')->pluck('order_id');

        $ordersQuery = Order::whereIn('id', $orderIds);

        return [
            // Stat::make('عدد السجلات', $query->count()),
            // Stat::make('إجمالي المدين', number_format($debitSum, 2)),
            // Stat::make('إجمالي الدائن', number_format($creditSum, 2)),
            // Stat::make('الرصيد', number_format($balance, 2)),

            Stat::make('عدد الشحنات', $ordersQuery->count()),
            Stat::make('شحنات تم التسليم', $ordersQuery->clone()->where('status', 'success')->count()),
            Stat::make('شحنات بالانتظار', $ordersQuery->clone()->where('status', 'transfer')->count()),
            Stat::make('شحنات ملغاة', $ordersQuery->clone()->where('status', 'canceled')->count()),
            Stat::make('شحنات مرتجعة', $ordersQuery->clone()->where('status', 'returned')->count()),
            Stat::make('شحنات تم تاكيد تسليمها كمرتجعة', $ordersQuery->clone()->where('status', 'confirm_returned')->count()),

        ];
    }
}
