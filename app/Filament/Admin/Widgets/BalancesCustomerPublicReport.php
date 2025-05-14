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
            return User::query()
                ->select([
                    'users.id',
                    'users.name',
                    DB::raw("
                        COALESCE(SUM(CASE
                            WHEN balances.currency_id = 1
                                 AND balances.is_complete = true
                                 AND balances.pending = false
                            THEN balances.credit - balances.debit
                            ELSE 0
                        END), 0) AS balance_usd
                    "),
                    DB::raw("
                        COALESCE(SUM(CASE
                            WHEN balances.currency_id = 1
                                 AND balances.pending = true
                            THEN balances.credit - balances.debit
                            ELSE 0
                        END), 0) AS pending_usd
                    "),
                    DB::raw("
                        COALESCE(SUM(CASE
                            WHEN balances.currency_id = 2
                                 AND balances.is_complete = true
                                 AND balances.pending = false
                            THEN balances.credit - balances.debit
                            ELSE 0
                        END), 0) AS balance_try
                    "),
                    DB::raw("
                        COALESCE(SUM(CASE
                            WHEN balances.currency_id = 2
                                 AND balances.pending = true
                            THEN balances.credit - balances.debit
                            ELSE 0
                        END), 0) AS pending_try
                    "),
                ])
                ->join('balances', function($join) use ($startDate, $endDate) {
                    $join->on('users.id', '=', 'balances.user_id')
                         ->whereBetween('balances.created_at', [$startDate, $endDate]);
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
               Tables\Columns\TextColumn::make('balance_usd')
                ->label('الرصيد دولار')
                ->prefix('$ ')
                ->color('success'),

                Tables\Columns\TextColumn::make('pending_usd')
                    ->label('قيد التحصيل دولار')
                    ->prefix('$ ')
                    ->color('warning'),

               Tables\Columns\TextColumn::make('total_usd')
                ->label('المحصلة دولار')
                ->state(function ($record) {
                    return $record->balance_usd + $record->pending_usd;
                })
                ->prefix('$ ')
                ->color('primary'),

                // أرصدة الليرة التركية
                Tables\Columns\TextColumn::make('balance_try')
                    ->label('الرصيد تركي')
                    ->prefix('₺ ')
                    ->color('success'),

                Tables\Columns\TextColumn::make('pending_try')
                    ->label('قيد التحصيل تركي')
                    ->prefix('₺ ')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_try')
                    ->label('المحصلة تركي')
                    ->state(function ($record) {
                        return $record->balance_try + $record->pending_try;
                    })
                    ->prefix('₺ ')
                    ->color('primary'),
            ])
            // ->filters([
            //  Tables\Filters\SelectFilter::make('id')->options(User::where('level', LevelUserEnum::USER->value)->pluck('name', 'id'))->searchable() ->label('اسم العميل')

            // ])
                ->filters([
                    Tables\Filters\SelectFilter::make('id')
                        ->label('اسم العميل')
                        ->options(function () use ($startDate, $endDate) {
                            return User::query()
                                ->select(['users.id', 'users.name'])
                                ->join('balances', function($join) use ($startDate, $endDate) {
                                    $join->on('users.id', '=', 'balances.user_id')
                                        ->whereBetween('balances.created_at', [$startDate, $endDate]);
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
