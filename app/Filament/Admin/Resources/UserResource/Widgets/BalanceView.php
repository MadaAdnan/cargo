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
            Stat::make('رصيد قيد التحصيل', \DB::table('balances')->where('user_id',$this->record->id)->where('balances.pending','=',true)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
            Stat::make('الرصيد الحالي', \DB::table('balances')->where('user_id',$this->record->id)->where('balances.is_complete','=',true)
                    ->where('balances.pending','!=',true)
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total??0),
        ];
    }
}
