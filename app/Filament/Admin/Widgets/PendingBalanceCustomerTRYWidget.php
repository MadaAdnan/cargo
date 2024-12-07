<?php

namespace App\Filament\Admin\Widgets;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PendingBalanceCustomerTRYWidget extends BaseWidget
{
    protected static ?string $heading="الأرصدة قيد التحصيل TRY";
   protected int | string | array $columnSpan=1;
    public static function canView(): bool
    {
        ;
        return !\Str::endsWith(request()->fullUrl(),'admin') && !\Str::endsWith(request()->fullUrl(),'admin/') ; // TODO: Change the autogenerated stub
    }
    public function table(Table $table): Table
    {
        return $table
            ->poll(10)
            ->query(
                fn()=> User::select('users.*')
                    ->where('level',LevelUserEnum::USER->value)
                    ->selectSub(function ($query) {
                        $query->from('balances') ->where('balances.pending', '=',true)
                            ->selectRaw('SUM(credit - debit)')
                            ->whereColumn('balances.user_id', 'users.id')
                            ->where('balances.currency_id',2)
                           ;
                    }, 'net_balance')
                    /*->orderByDesc('net_balance')*/
                    ->having('net_balance', '!=', 0),
            )
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المستخدم'),
                Tables\Columns\TextColumn::make('net_balance')->label('الرصيد الحالي')->sortable()->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state))
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')->options(User::pluck('name','id'))->searchable()
            ])
            ;
    }
}
