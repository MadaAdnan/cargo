<?php

namespace App\Filament\Admin\Resources\ExchangeResource\Pages;

use App\Filament\Admin\Resources\ExchangeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListExchanges extends ListRecords
{
    protected static string $resource = ExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            Tab::make('USD')->modifyQueryUsing(fn($query)=>$query->where('currency_id',1))->label('من الدولار إلى التركي'),
            Tab::make('USD')->modifyQueryUsing(fn($query)=>$query->where('currency_id',2))->label('من التركي إلى الدولار'),
        ];
    }
}
