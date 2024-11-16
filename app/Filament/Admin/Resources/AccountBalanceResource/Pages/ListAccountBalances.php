<?php

namespace App\Filament\Admin\Resources\AccountBalanceResource\Pages;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\AccountBalanceResource;
use App\Models\Balance;
use App\Models\User;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAccountBalances extends ListRecords
{
    protected static string $resource = AccountBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

        ];
    }
}
