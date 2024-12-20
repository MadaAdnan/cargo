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

class BalanceCustomerTRWidget extends BaseWidget
{
  protected static ?string $heading="أرصدة الزبائن TRY";

   protected int | string | array $columnSpan=1;
    public static function canView(): bool
    {
        ;
        return !\Str::endsWith(request()->fullUrl(),'admin') && !\Str::endsWith(request()->fullUrl(),'admin/') ; // TODO: Change the autogenerated stub
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
               fn()=> User::select('users.*')
                   ->where('level',LevelUserEnum::USER->value)
                   ->selectSub(function ($query) {
                       $query->from('balances')
                           ->selectRaw('SUM(credit - debit)')
                           ->whereColumn('user_id', 'users.id')
                           ->where('balances.is_complete', 1)
                           ->where('balances.pending', '=',false)
                           ->where('balances.currency_id', '=',2);
                   }, 'net_balance')
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
                Tables\Filters\SelectFilter::make('id')->options(User::where('level', LevelUserEnum::USER->value)->pluck('name','id'))->searchable()
            ])

            ;
    }
}
