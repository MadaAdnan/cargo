<?php

namespace App\Filament\Admin\Widgets;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\User;
use App\Models\Balance;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;
class BalancesCustomerPublicReport extends BaseWidget
{
    use InteractsWithPageFilters;
  protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = " مفصل أرصدة شحنات العملاء";
    public static function canView(): bool
{
    return !request()->routeIs('filament.admin.pages.dashboard');
}
    public function table(Table $table): Table
    {
        $startDate = Carbon::parse($this->filters['start_date'] ?? now())->startOfDay();
        $endDate = Carbon::parse($this->filters['end_date'] ?? now())->endOfDay();

        return $table
        ->query(function () use ($startDate, $endDate) {
            return
            User::query()
            ->select([
                'users.id',
                'users.name',

                DB::raw("
                    SUM(
                        COALESCE(
                            CASE WHEN orders.status != 'canceled' THEN orders.price ELSE 0 END,
                        0)
                    ) AS price_usd
                "),
                DB::raw("
                    SUM(
                        COALESCE(
                            CASE WHEN orders.status != 'canceled' THEN orders.far ELSE 0 END,
                        0)
                    ) AS far_usd
                "),
                DB::raw("
                    SUM(
                        COALESCE(
                             CASE WHEN orders.status != 'canceled' THEN orders.price_tr ELSE 0 END,
                        0)
                    ) AS price_try
                "),
                DB::raw("
                    SUM(
                        COALESCE(
                             CASE WHEN orders.status != 'canceled' THEN orders.far_tr ELSE 0 END,
                        0)
                    ) AS far_try
                "),
                         // إضافة تعداد الشحنات
               DB::raw("
                        COUNT(
                            CASE WHEN orders.status != 'canceled' THEN 1 ELSE NULL END
                        ) AS no_order
                    "),

            ])
                ->join('orders', function($join) use ($startDate, $endDate) {
                    $join->on('users.id', '=', 'orders.sender_id')
                         ->whereBetween('orders.created_at', [$startDate, $endDate]);
                })
                ->where('users.level', LevelUserEnum::USER->value)
                ->groupBy('users.id', 'users.name');
                // ->havingRaw('SUM(balances.credit) > 0 OR SUM(balances.debit) > 0');
        })

        ->emptyStateHeading('لا توجد أرصدة في الفترة المحددة')
          ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),

                 Tables\Columns\TextColumn::make('no_order')
                ->label('عدد الشحنات')
                ->color('Primary')
                ->sortable(),

                // أرصدة الدولار
                Tables\Columns\TextColumn::make('price_usd')
                ->label('قيمة الشحنات دولار')
                ->prefix('$ ')
                ->color('success')
                ->sortable(),


                Tables\Columns\TextColumn::make('price_try')
                    ->label(' قيمة الشحنات تركي')
                    ->prefix('₺ ')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('far_usd')
                    ->label(' الاجور دولار')
                    ->prefix('$ ')
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('far_try')
                    ->label(' الاجور تركي')
                    ->prefix('₺ ')
                    ->color('warning')
                    ->sortable(),
            ]) ->paginated([10, 25, 50, 100,200, 'all'])
            // ->filters([
            //  Tables\Filters\SelectFilter::make('id')->options(User::where('level', LevelUserEnum::USER->value)->pluck('name', 'id'))->searchable() ->label('اسم العميل')

            // ])
                ->filters([
                    Tables\Filters\SelectFilter::make('users.id')
                        ->label('اسم العميل')
                        ->options(function () use ($startDate, $endDate) {
                            return User::query()
                                ->select(['users.id', 'users.name'])
                                ->join('orders', function($join) use ($startDate, $endDate) {
                                    $join->on('users.id', '=', 'orders.sender_id')
                                        ->whereBetween('orders.created_at', [$startDate, $endDate]);
                                })
                                ->where('users.level', LevelUserEnum::USER->value)
                                ->groupBy('users.id', 'users.name')
                                ->orderBy('users.name')
                                ->pluck('users.name', 'users.id');
                        })
                        ->searchable()
                ])

            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
            // ->actions([
            //     Tables\Actions\Action::make('statement')
            //         ->label('كشف حساب')
            //         ->icon('heroicon-o-document-text')
            //         ->url(fn (User $record) => route('user.statement', $record)),
            // ])
            ->bulkActions([
               ExportBulkAction::make()->exports([
                        ExcelExport::make()->withChunkSize(300)->fromTable()
        ]),
          Tables\Actions\BulkAction::make('generateReport')
                    ->label('تقرير الشحنات')
                    ->requiresConfirmation()
                    ->modalHeading('تقرير الشحنات')
                    ->modalDescription('عرض تقرير الشحنات.')
                    ->modalSubmitActionLabel('إغلاق')
                    ->form(fn($records) => static::getReportForm($records)),
                    // ->action(fn($records) => static::generateReport($records)),
            ]);
    }
    protected function paginateTableQuery(Builder $query): Paginator
{
    return $query->simplePaginate(
        ($this->getTableRecordsPerPage() === 'all')
            ? $query->count()
            : $this->getTableRecordsPerPage()
    );
}

// protected static function getReportForm($records): array
// {

//     // الحصول على بيانات العملاء مع شحناتهم باستخدام join
//     $users = User::whereIn('users.id', $records->pluck('id'))
//         ->select([
//             'users.id',
//             'users.name',
//             DB::raw("SUM(CASE WHEN orders.status != 'canceled' THEN orders.price ELSE 0 END) as price_usd"),
//             DB::raw("SUM(CASE WHEN orders.status != 'canceled' THEN orders.far ELSE 0 END) as far_usd"),
//             DB::raw("SUM(CASE WHEN orders.status != 'canceled' THEN orders.price_tr ELSE 0 END) as price_try"),
//             DB::raw("SUM(CASE WHEN orders.status != 'canceled' THEN orders.far_tr ELSE 0 END) as far_try"),
//             DB::raw("COUNT(CASE WHEN orders.status != 'canceled' THEN 1 ELSE NULL END) as no_order")
//         ])
//         ->join('orders', function ($join) {
//             $join->on('users.id', '=', 'orders.sender_id');
//         })
//         ->where('users.level', LevelUserEnum::USER->value)
//         ->groupBy('users.id', 'users.name')
//         ->get();

//     $reportText = "تقرير مفصل لأرصدة شحنات العملاء\n";
//     $reportText .= "==================================\n\n";

//     foreach ($users as $user) {
//         $reportText .= "👤 اسم العميل: {$user->name}\n";
//         $reportText .= "----------------------------------\n";
//         $reportText .= "📦 عدد الشحنات: " . $user->no_order . "\n";
//         $reportText .= "💰 قيمة الشحنات:\n";
//         $reportText .= "   - دولار: $" . number_format($user->price_usd, 2) . "\n";
//         $reportText .= "   - تركي: ₺" . number_format($user->price_try, 2) . "\n";
//         $reportText .= "💼 الأجور:\n";
//         $reportText .= "   - دولار: $" . number_format($user->far_usd, 2) . "\n";
//         $reportText .= "   - تركي: ₺" . number_format($user->far_try, 2) . "\n";
//         $reportText .= "----------------------------------\n\n";
//     }

//     // إضافة الإجمالي العام
//     // $reportText .= "الإجمالي العام:\n";
//     // $reportText .= "====================\n";
//     // $reportText .= "🔹 إجمالي عدد الشحنات: " . $users->sum('no_order') . "\n";
//     // $reportText .= "🔹 إجمالي قيمة الشحنات دولار: $" . number_format($users->sum('price_usd'), 2) . "\n";
//     // $reportText .= "🔹 إجمالي قيمة الشحنات تركي: ₺" . number_format($users->sum('price_try'), 2) . "\n";
//     // $reportText .= "🔹 إجمالي الأجور دولار: $" . number_format($users->sum('far_usd'), 2) . "\n";
//     // $reportText .= "🔹 إجمالي الأجور تركي: ₺" . number_format($users->sum('far_try'), 2) . "\n";
//     // $reportText .= "====================\n";

//     return [
//         Textarea::make('report')
//             ->label(false)
//             ->extraAttributes(['style' => 'border: none; background: transparent; direction: rtl; text-align: right;'])
//             ->default($reportText)
//             ->disabled()
//             ->rows(25),
//     ];
// }

protected static function getReportForm($records): array
{

    $reportText = "تقرير مفصل لأرصدة شحنات العملاء\n";
    $reportText .= "==================================\n\n";

    foreach ($records as $user) {
        $reportText .= "👤 اسم العميل: {$user->name}\n";
        $reportText .= "----------------------------------\n";
        $reportText .= "📦 عدد الشحنات: " . $user->no_order . "\n";
        $reportText .= "💰 قيمة الشحنات:\n";
        $reportText .= "   - دولار: $" . number_format($user->price_usd, 2) . "\n";
        $reportText .= "   - تركي: ₺" . number_format($user->price_try, 2) . "\n";
        $reportText .= "💼 الأجور:\n";
        $reportText .= "   - دولار: $" . number_format($user->far_usd, 2) . "\n";
        $reportText .= "   - تركي: ₺" . number_format($user->far_try, 2) . "\n";
        $reportText .= "----------------------------------\n\n";
    }

    return [
        Textarea::make('report')
            ->label(false)
            ->extraAttributes(['style' => 'border: none; background: transparent; direction: rtl; text-align: right;'])
            ->default($reportText)
            ->disabled()
            ->rows(25),
    ];
}
}
