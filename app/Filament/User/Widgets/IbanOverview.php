<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IbanOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //H: fixed the word Iban -> now its IBAN
            Stat::make('IBAN', auth()->user()->iban)
                ->description('الرمز الخاص بك')
                ->color('success'),

        ];
    }
}
