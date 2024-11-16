<?php

namespace App\Filament\Branch\Resources\UserResource\Pages;

use App\Enums\LevelUserEnum;
use App\Filament\Branch\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('level',LevelUserEnum::BRANCH->value)
            ->orWhere('branch_id',auth()->user()->branch_id)->latest();
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
