<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\UserResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('user')->modifyQueryUsing(fn($query)=>$query->where('id','!=',''))->label('الكل'),

            Tab::make('staff')->modifyQueryUsing(fn($query)=>$query->where('level',LevelUserEnum::STAFF->value))->label('الموظفين'),
            Tab::make('branch')->modifyQueryUsing(fn($query)=>$query->where('level',LevelUserEnum::BRANCH->value))->label('مدراء الأفرع'),
            Tab::make('admin')->modifyQueryUsing(fn($query)=>$query->where('level',LevelUserEnum::ADMIN->value))->label('المدراء'),
            Tab::make('user')->modifyQueryUsing(fn($query)=>$query->where('level',LevelUserEnum::USER->value))->label('المستخدمين'),
        ];
    }
}
