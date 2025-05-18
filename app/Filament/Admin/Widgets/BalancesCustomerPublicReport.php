<?php

namespace App\Filament\Admin\Widgets;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\User;
use App\Models\Balance;
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
class BalancesCustomerPublicReport extends BaseWidget
{
    use InteractsWithPageFilters;
  protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = "أرصدة العملاء";
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
                        CASE WHEN orders.price > 0 OR orders.far > 0 THEN 1 END
                    ) AS no_order_usd
                "),
                DB::raw("
                    COUNT(
                        CASE WHEN orders.price_tr > 0 OR orders.far_tr > 0 THEN 1 END
                    ) AS no_order_try
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

                // أرصدة الدولار
                Tables\Columns\TextColumn::make('no_order_usd')
                ->label('عدد الشحنات دولار')
                ->color('success'),

                Tables\Columns\TextColumn::make('price_usd')
                ->label('قيمة الشحنات دولار')
                ->prefix('$ ')
                ->color('success'),

                Tables\Columns\TextColumn::make('far_usd')
                    ->label(' الاجور دولار')
                    ->prefix('$ ')
                    ->color('warning'),


                // أرصدة الليرة التركية
              Tables\Columns\TextColumn::make('no_order_try')
                ->label('عدد الشحنات تركي')
                ->color('success'),

                Tables\Columns\TextColumn::make('price_try')
                    ->label(' قيمة الشحنات تركي')
                    ->prefix('₺ ')
                    ->color('success'),

                Tables\Columns\TextColumn::make('far_try')
                    ->label(' الاجور تركي')
                    ->prefix('₺ ')
                    ->color('warning'),
            ])
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

            // ->headerActions([
            //     ExportAction::make()->exports([
            //         ExcelExport::make()->withChunkSize(100)->fromTable()
            //     ])
            // ])
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
            ]);
    }
}
