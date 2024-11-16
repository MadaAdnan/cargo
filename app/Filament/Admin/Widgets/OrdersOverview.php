<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Admin\Resources\OrderResource;

class OrdersOverview extends BaseWidget
{
    protected static bool $isLazy = true;
protected int | string | array $columnSpan=4;
    protected static ?string $pollingInterval = '10s';


    protected function getStats(): array
    {
        $waitingOrders=Order::where('status',OrderStatusEnum::PENDING)->count();
        $userCount=User::all()->count();

        return [

            Stat::make('إضافة طلب', ' ')->color('success')
                ->description(' اضغط هنا لإضافة الطلبات بسرعة')
                ->icon('heroicon-o-squares-plus')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',

                    'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",

                ])
                ->url(OrderResource::getUrl('index')),


            Stat::make('عدد الطلبات التي تحتاج تأكيد', $waitingOrders)->color('success')
                ->description(' ')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',

                    'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",

                ])
            ->url(OrderResource::getUrl('index')),




            Stat::make(' عدد المستخدمين', $userCount)
                ->icon('heroicon-o-user') ->color('info')
                ->url(UserResource::getUrl('index')),



        ];
    }




}
