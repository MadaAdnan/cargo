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
class BalancesCustomerPublicReport extends BaseWidget
{
  protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = "أرصدة العملاء";
    public static function canView(): bool
{
    return !request()->routeIs('filament.admin.pages.dashboard');
}
    public function table(Table $table): Table
    {
        return $table
              ->query(
               fn () =>User::query()
                ->where('level', LevelUserEnum::USER->value)
                ->addSelect([
                    'balance_usd' => Balance::query()
                        ->selectRaw('SUM(CASE WHEN is_complete = true AND pending = false THEN credit - debit ELSE 0 END)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('currency_id', 1),
                    'pending_usd' => Balance::query()
                        ->selectRaw('SUM(CASE WHEN pending = true THEN credit - debit ELSE 0 END)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('currency_id', 1),

                        // نفس الشيء للتركي
                    'balance_try' => Balance::query()
                        ->selectRaw('SUM(CASE WHEN is_complete = true AND pending = false THEN credit - debit ELSE 0 END)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('currency_id', 2),
                    'pending_try' => Balance::query()
                        ->selectRaw('SUM(CASE WHEN pending = true THEN credit - debit ELSE 0 END)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('currency_id', 2),
                        ])
            )
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
            ->filters([
             Tables\Filters\SelectFilter::make('id')->options(User::where('level', LevelUserEnum::USER->value)->pluck('name', 'id'))->searchable() ->label('اسم العميل')

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
