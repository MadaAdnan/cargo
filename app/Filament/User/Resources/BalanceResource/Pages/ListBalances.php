<?php

namespace App\Filament\User\Resources\BalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\User\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListBalances extends ListRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }


}
