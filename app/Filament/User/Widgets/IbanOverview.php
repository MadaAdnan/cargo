<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IbanOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Iban', auth()->user()->iban)
                ->description('الرمز الخاص بك')
                ->color('success'),

        ];
    }
}
