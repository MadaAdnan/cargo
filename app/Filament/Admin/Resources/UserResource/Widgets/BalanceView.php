<?php

namespace App\Filament\Admin\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class BalanceView extends BaseWidget
{
public ?Model $record;
    protected function getStats(): array
    {
        return [
            Stat::make('رصيد قيد التحصيلUSD', \DB::table('balances')->where('currency_id',1)->where('user_id',$this->record->id)->where('balances.pending','=',true)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
            Stat::make('رصيد قيد التحصيلTRY', \DB::table('balances')->where('currency_id',2)->where('user_id',$this->record->id)->where('balances.pending','=',true)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
            Stat::make('الرصيد الحاليUSD', \DB::table('balances')->where('user_id',$this->record->id)->where('currency_id',1)->where('balances.is_complete','=',true)
                    ->where('balances.pending','!=',true)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
            Stat::make('الرصيد الحاليTRY', \DB::table('balances')->where('user_id',$this->record->id)->where('currency_id',2)->where('balances.is_complete','=',true)
                    ->where('balances.pending','!=',true)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
        ];
    }
}
