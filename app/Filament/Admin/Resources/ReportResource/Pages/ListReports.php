<?php

namespace App\Filament\Admin\Resources\ReportResource\Pages;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\ReportResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        return [
            Tab::make('user')->modifyQueryUsing(fn($query)=>$query->where('level',LevelUserEnum::USER->value))->label
            ('الزبائن')->badge(User::where('level',LevelUserEnum::USER->value)->count())->icon('heroicon-o-user-circle'),



            Tab::make('staff')
                ->modifyQueryUsing(fn($query) => $query->where(function($query) {
                    $query->where('level', LevelUserEnum::STAFF->value)
                        ->orWhere('level', LevelUserEnum::DRIVER->value); // الشرط الإضافي
                }))
                ->badge(User::where(function($query) {
                    $query->where('level', LevelUserEnum::STAFF->value)
                        ->orWhere('level', LevelUserEnum::DRIVER->value); // نفس الشرط لحساب الـ badge
                })->count())
                ->label('الموظفين')
                ->icon('heroicon-o-users'),

            Tab::make('manager')
                ->modifyQueryUsing(fn($query) => $query->where(function($query) {
                    $query->where('level', LevelUserEnum::ADMIN->value)
                        ->orWhere('level', LevelUserEnum::BRANCH->value); // الشرط الإضافي
                }))
                ->badge(User::where(function($query) {
                    $query->where('level', LevelUserEnum::ADMIN->value)
                        ->orWhere('level', LevelUserEnum::BRANCH->value); // نفس الشرط لحساب الـ badge
                })->count())
                ->label('المدراء')
                ->icon('heroicon-o-users'),




            Tab::make('branch')->modifyQueryUsing(fn($query)=>$query->where('level','!=',''))
                ->badge(User::all()->count())
                ->label(' الكل'),
        ];
    }




}
