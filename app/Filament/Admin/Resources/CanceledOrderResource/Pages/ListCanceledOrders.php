<?php

namespace App\Filament\Admin\Resources\CanceledOrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\CanceledOrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCanceledOrders extends ListRecords
{
    protected static string $resource = CanceledOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): ?Builder
    {
        return Order::with('cityTarget','citySource','sender','receive','pick','given','branchSource','branchTarget','unit'); // TODO: Change the autogenerated stub
    }

    public function getTabs(): array
    {
        return [
//            Tab::make('all')->modifyQueryUsing(fn($query)=>$query->where('status','!=' ,""))->badge(Order::all()->count())->label('الكل'),
//            Tab::make('pick')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::PICK->value))->badge(Order::where('status',OrderStatusEnum::PICK->value)->count())->label('تم الإلتقاط'),
//            Tab::make('transfer')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::TRANSFER->value))->badge(Order::where('status',OrderStatusEnum::TRANSFER->value)->count())->label('بإنتظار التسليم'),
            Tab::make('success')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::SUCCESS->value))/*->badge(Order::where('status','success')->count())*/->label('منتهي'),
            Tab::make('canceled')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::CANCELED->value))/*->badge(Order::where('status','success')->count())*/->label('ملغي'),
//            Tab::make('returned')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::RETURNED->value))/*->badge(Order::where('status','success')->count())*/->label('مرتجع'),
            Tab::make('confirm_returned')->modifyQueryUsing(fn($query)=>$query->where('status',OrderStatusEnum::CONFIRM_RETURNED->value))/*->badge(Order::where('status','success')->count())*/->label('مرتجع تم تسليمه'),

        ];
    }
}
