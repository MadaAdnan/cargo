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
        //جلب الفلتر المطبق على الجدول فلتر المستخدم
        $userId = $this->getTablePageInstance()->getTableFilterState('user_id')['value'] ?? null;
        $activeTab = $this->getTablePageInstance()->activeTab ?? 'all';
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

        // $totalBalanceUsd = \App\Models\Balance::query()
        // ->where('user_id', $userId)
        // ->where('currency_id', 1)
        // ->where('is_complete', true)
        // ->where('pending', false)
        // ->selectRaw('SUM(credit - debit) as total')
        // ->value('total') ?? 0;
        // $totalBalancependingUsd = \App\Models\Balance::query()
        // ->where('user_id', $userId)
        // ->where('currency_id', 1)
        // ->where('is_complete', false)
        // ->where('pending', true)
        // ->selectRaw('SUM(credit - debit) as total')
        // ->value('total') ?? 0;
        // $totalBalanceTry = \App\Models\Balance::query()
        // ->where('user_id', $userId)
        // ->where('currency_id', 2)
        // ->where('is_complete', true)
        // ->where('pending', false)
        // ->selectRaw('SUM(credit - debit) as total')
        // ->value('total') ?? 0;
        // $totalBalancependingTry = \App\Models\Balance::query()
        // ->where('user_id', $userId)
        // ->where('currency_id', 2)
        // ->where('is_complete', false)
        // ->where('pending', true)
        // ->selectRaw('SUM(credit - debit) as total')
        // ->value('total') ?? 0;
        // استعلام واحد لجميع أرصدة العملات
        $balances = \App\Models\Balance::query()
        ->where('user_id', $userId)
        ->selectRaw("
            currency_id,
            SUM(CASE WHEN is_complete = true AND pending = false THEN credit - debit ELSE 0 END) as cleared_balance,
            SUM(CASE WHEN pending = true THEN credit - debit ELSE 0 END) as pending_balance
        ")
        ->groupBy('currency_id')
        ->get()
        ->keyBy('currency_id');

        // استخراج القيم
        $totalBalanceUsd = $balances->get(1)?->cleared_balance ?? 0;
        $totalBalancependingUsd = $balances->get(1)?->pending_balance ?? 0;
        $totalUsd = (double)$totalBalanceUsd + (double)$totalBalancependingUsd;
        $totalBalanceTry = $balances->get(2)?->cleared_balance ?? 0;
        $totalBalancependingTry = $balances->get(2)?->pending_balance ?? 0;
        $totlalTry = (double) $totalBalancependingTry + (double)$totalBalanceTry;

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
            // Stat::make('معرف الحساب', $userId),

            Stat::make('عدد الشحنات', $orderCounts->total),
            Stat::make('شحنات تم التسليم', $orderCounts->success_count),
            Stat::make('شحنات بالانتظار', $orderCounts->transfer_count),
            Stat::make('شحنات ملغاة', $orderCounts->canceled_count),
            Stat::make('شحنات مرتجعة', $orderCounts->returned_count),
            Stat::make('شحنات تم تاكيد تسليمها كمرتجعة', $orderCounts->confirm_returned_count),
            Stat::make('USD الرصيد ' ,$totalBalanceUsd ),
            Stat::make('USD  قيد التحصيل ' ,$totalBalancependingUsd ),
            Stat::make('USD  المحصلة  ' ,$totalUsd ),
            Stat::make('TRY الرصيد ' ,$totalBalanceTry ),
            Stat::make('TRY قيد التحصيل ' ,$totalBalancependingTry ),
            Stat::make('TRY المحصلة ' ,$totlalTry ),
        ];
    }

}
